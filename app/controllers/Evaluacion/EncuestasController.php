<?php

namespace App\Controllers\Evaluacion;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Evaluacion\Encuesta;
use App\Models\Evaluacion\Criterio;
use App\Models\Evaluacion\Pregunta;
use PDO;

class EncuestasController extends Controller
{
    protected $encuestaModel;
    protected $criterioModel;
    protected $preguntaModel;

    public function __construct()
    {
        parent::__construct();

        // Acceso permitido para Administrador, Director, JUA y Coordinador
        if (!Auth::esAdminEvaluacion() && !Auth::esCoordinadorPEEvaluacion()) {
            $_SESSION['flash_error'] = "No tienes permisos para gestionar encuestas.";
            header('Location: ' . BASE_URL . '/evaluacion');
            exit;
        }

        $this->encuestaModel = new Encuesta();
        $this->criterioModel = new Criterio();
        $this->preguntaModel = new Pregunta();
    }

    public function index()
    {
        $idUser     = $_SESSION['sigi_user_id'];
        $db         = (new Model())->getDB();

        // El periodo se toma prioritariamente del selector de contexto del menú superior
        $idPeriodo  = $_SESSION['sigi_periodo_actual_id'] ?? (!empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : null);
        if (!$idPeriodo) {
            $idPeriodo = (int)$db->query("SELECT id FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")->fetchColumn();
        }

        $idPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        $puedeVerTodos = Auth::puedeVerTodosLosProgramasEvaluacion();

        // Director (2), JUA (4), Admin (1), Sec. Académico (3) ven todos los programas
        // Coordinador (5) solo ve sus carreras asignadas
        if ($puedeVerTodos) {
            $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtProg = $db->prepare("
                SELECT p.id, p.nombre, p.codigo 
                FROM sigi_programa_estudios p 
                INNER JOIN sigi_coordinador_pe_periodo c ON c.id_programa_estudio = p.id 
                WHERE c.id_usuario = ? AND c.id_periodo = ? 
                ORDER BY p.nombre ASC
            ");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $programas = $stmtProg->fetchAll(PDO::FETCH_ASSOC);
            $progsCoord = array_column($programas, 'id');
            if ($idPrograma && !in_array($idPrograma, $progsCoord)) {
                $idPrograma = $progsCoord[0] ?? null;
            }
        }

        $encuestas = $this->encuestaModel->listar($idPeriodo, $idPrograma);
        $periodos  = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . (int)$idPeriodo)->fetchColumn() ?: 'Periodo Actual';

        $this->view('evaluacion/encuestas/index', [
            'pageTitle'           => 'Gestión de Encuestas de Evaluación Docente',
            'module'              => 'evaluacion',
            'encuestas'           => $encuestas,
            'periodos'            => $periodos,
            'programas'           => $programas,
            'filtroPeriodo'       => $idPeriodo,
            'filtroPrograma'      => $idPrograma,
            'periodoActualNombre' => $periodoNombre,
            'puedeVerTodos'       => $puedeVerTodos,
            'idsPermitidosCoord'  => $progsCoord ?? []
        ]);
    }

    public function crear()
    {
        $db = (new Model())->getDB();
        $idUser     = $_SESSION['sigi_user_id'];
        $idPeriodo  = $_SESSION['sigi_periodo_actual_id'] ?? null;
        if (!$idPeriodo) {
            $idPeriodo = (int)$db->query("SELECT id FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")->fetchColumn();
        }

        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . (int)$idPeriodo)->fetchColumn() ?: 'Periodo Actual';
        $puedeVerTodos = Auth::puedeVerTodosLosProgramasEvaluacion();

        if ($puedeVerTodos) {
            $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtProg = $db->prepare("
                SELECT p.id, p.nombre, p.codigo 
                FROM sigi_programa_estudios p 
                INNER JOIN sigi_coordinador_pe_periodo c ON c.id_programa_estudio = p.id 
                WHERE c.id_usuario = ? AND c.id_periodo = ? 
                ORDER BY p.nombre ASC
            ");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $programas = $stmtProg->fetchAll(PDO::FETCH_ASSOC);
        }

        $periodos   = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sedes      = $db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('evaluacion/encuestas/crear', [
            'pageTitle'           => 'Nueva Encuesta de Evaluación Docente',
            'module'              => 'evaluacion',
            'periodos'            => $periodos,
            'programas'           => $programas,
            'sedes'               => $sedes,
            'idPeriodo'           => $idPeriodo,
            'periodoActualNombre' => $periodoNombre,
            'puedeVerTodos'       => $puedeVerTodos
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $db                  = (new Model())->getDB();
        $titulo              = trim($_POST['titulo'] ?? '');
        $descripcion         = trim($_POST['descripcion'] ?? '');
        $idPeriodo          = !empty($_POST['id_periodo_academico']) ? (int)$_POST['id_periodo_academico'] : ($_SESSION['sigi_periodo_actual_id'] ?? null);
        $idPrograma          = !empty($_POST['id_programa_estudios']) ? (int)$_POST['id_programa_estudios'] : null;
        $idSede              = !empty($_POST['id_sede']) ? (int)$_POST['id_sede'] : null;
        $fechaInicio         = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin            = trim($_POST['fecha_fin'] ?? '');
        $estado              = trim($_POST['estado'] ?? 'ACTIVA');
        $conRubricaEstandar  = isset($_POST['rubrica_estandar']) && $_POST['rubrica_estandar'] == '1';
        $generarAsignaciones = isset($_POST['generar_asignaciones']) && $_POST['generar_asignaciones'] == '1';

        if (empty($titulo) || empty($idPeriodo) || empty($fechaInicio) || empty($fechaFin)) {
            $_SESSION['flash_error'] = "Por favor complete todos los campos obligatorios del formulario.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas/crear');
            exit;
        }

        // Restricción para Coordinador: Solo puede crear encuestas de sus programas asignados
        if (!Auth::puedeVerTodosLosProgramasEvaluacion()) {
            if (empty($idPrograma)) {
                $_SESSION['flash_error'] = "Como coordinador, debes seleccionar obligatoriamente una de tus carreras asignadas.";
                header('Location: ' . BASE_URL . '/evaluacion/encuestas/crear');
                exit;
            }
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ? AND id_programa_estudio = ?");
            $stmtCheck->execute([$_SESSION['sigi_user_id'], $idPeriodo, $idPrograma]);
            if ((int)$stmtCheck->fetchColumn() === 0) {
                $_SESSION['flash_error'] = "No tienes permisos para crear encuestas en un programa de estudios que no tienes asignado.";
                header('Location: ' . BASE_URL . '/evaluacion/encuestas/crear');
                exit;
            }
        }

        try {
            $idEncuesta = $this->encuestaModel->crear([
                'titulo'               => $titulo,
                'descripcion'          => $descripcion,
                'id_periodo_academico' => $idPeriodo,
                'id_programa_estudios' => $idPrograma,
                'id_sede'              => $idSede,
                'fecha_inicio'         => $fechaInicio . ' 00:00:00',
                'fecha_fin'            => $fechaFin . ' 23:59:59',
                'estado'               => $estado,
                'escala_min'           => 1,
                'escala_max'           => 5,
                'creado_por'           => $_SESSION['sigi_user_id']
            ], $conRubricaEstandar);

            $msg = "Encuesta creada con éxito.";

            if ($generarAsignaciones) {
                $resAsig = $this->encuestaModel->generarEvaluacionesParaEstudiantes($idEncuesta);
                if ($resAsig['ok']) {
                    $msg .= " Se asignaron automáticamente {$resAsig['nuevos_asignados']} evaluaciones para los estudiantes.";
                }
            }

            $_SESSION['flash_success'] = $msg;
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al crear la encuesta: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/evaluacion/encuestas/crear');
            exit;
        }
    }

    public function editar($id)
    {
        $id = (int)$id;
        $encuesta = $this->encuestaModel->obtenerPorId($id);
        if (!$encuesta) {
            $_SESSION['flash_error'] = "Encuesta no encontrada.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        if (!$this->verificarPermisoEncuesta($id)) {
            $_SESSION['flash_error'] = "No tienes permisos para editar encuestas de carreras que no tienes asignadas.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $db = (new Model())->getDB();
        $periodos  = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sedes     = $db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('evaluacion/encuestas/editar', [
            'pageTitle' => 'Editar Encuesta de Evaluación Docente',
            'module'    => 'evaluacion',
            'encuesta'  => $encuesta,
            'periodos'  => $periodos,
            'programas' => $programas,
            'sedes'     => $sedes
        ]);
    }

    public function actualizar($id)
    {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        if (!$this->verificarPermisoEncuesta($id)) {
            $_SESSION['flash_error'] = "No tienes permisos para modificar esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
        $fechaFin    = trim($_POST['fecha_fin'] ?? '');
        $estado      = trim($_POST['estado'] ?? 'ACTIVA');

        if (empty($titulo) || empty($fechaInicio) || empty($fechaFin)) {
            $_SESSION['flash_error'] = "Por favor complete los campos obligatorios.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas/editar/' . $id);
            exit;
        }

        $this->encuestaModel->actualizar($id, [
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'estado'       => $estado
        ]);

        $_SESSION['flash_success'] = "Encuesta actualizada correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas');
        exit;
    }

    public function eliminar($id)
    {
        $id = (int)$id;
        if (!$this->verificarPermisoEncuesta($id)) {
            $_SESSION['flash_error'] = "No tienes permisos para eliminar encuestas de carreras que no tienes asignadas.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $this->encuestaModel->eliminar($id);
        $_SESSION['flash_success'] = "Encuesta eliminada correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas');
        exit;
    }

    public function generarAsignaciones($id)
    {
        $id = (int)$id;
        if (!$this->verificarPermisoEncuesta($id)) {
            $_SESSION['flash_error'] = "No tienes permisos para gestionar asignaciones en esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $res = $this->encuestaModel->generarEvaluacionesParaEstudiantes($id);
        if ($res['ok']) {
            $_SESSION['flash_success'] = "¡Asignaciones generadas con éxito! Total procesado: {$res['total_encontrados']} matrículas, {$res['nuevos_asignados']} nuevas evaluaciones habilitadas.";
        } else {
            $_SESSION['flash_error'] = "No se pudieron generar asignaciones: " . ($res['error'] ?? 'Error desconocido');
        }
        header('Location: ' . BASE_URL . '/evaluacion/encuestas');
        exit;
    }

    public function criterios($idEncuesta)
    {
        $idEncuesta = (int)$idEncuesta;
        $encuesta = $this->encuestaModel->obtenerPorId($idEncuesta);
        if (!$encuesta) {
            $_SESSION['flash_error'] = "Encuesta no encontrada.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        if (!$this->verificarPermisoEncuesta($idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para ver o gestionar los criterios de esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $criterios = $this->criterioModel->listarPorEncuesta($idEncuesta);

        $this->view('evaluacion/encuestas/criterios', [
            'pageTitle'  => 'Criterios y Dimensiones - ' . $encuesta['titulo'],
            'module'     => 'evaluacion',
            'encuesta'   => $encuesta,
            'criterios'  => $criterios
        ]);
    }

    public function guardarCriterio()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $idEncuesta = (int)$_POST['id_encuesta'];
        if (!$this->verificarPermisoEncuesta($idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para agregar o modificar criterios en esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $nombre     = trim($_POST['nombre'] ?? '');
        $desc       = trim($_POST['descripcion'] ?? '');
        $peso       = !empty($_POST['peso']) ? (float)$_POST['peso'] : 1.00;
        $orden      = !empty($_POST['orden']) ? (int)$_POST['orden'] : 1;
        $id         = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (empty($nombre)) {
            $_SESSION['flash_error'] = "El nombre del criterio es obligatorio.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas/criterios/' . $idEncuesta);
            exit;
        }

        $this->criterioModel->guardar([
            'id'          => $id,
            'id_encuesta' => $idEncuesta,
            'nombre'      => $nombre,
            'descripcion' => $desc,
            'peso'        => $peso,
            'orden'       => $orden
        ]);

        $_SESSION['flash_success'] = "Criterio guardado correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas/criterios/' . $idEncuesta);
        exit;
    }

    public function eliminarCriterio($id, $idEncuesta)
    {
        if (!$this->verificarPermisoEncuesta((int)$idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para eliminar criterios de esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $this->criterioModel->eliminar((int)$id);
        $_SESSION['flash_success'] = "Criterio eliminado correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas/criterios/' . (int)$idEncuesta);
        exit;
    }

    public function preguntas($idEncuesta)
    {
        $idEncuesta = (int)$idEncuesta;
        $encuesta = $this->encuestaModel->obtenerPorId($idEncuesta);
        if (!$encuesta) {
            $_SESSION['flash_error'] = "Encuesta no encontrada.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        if (!$this->verificarPermisoEncuesta($idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para gestionar preguntas de esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $preguntas = $this->preguntaModel->listarPorEncuesta($idEncuesta);
        $criterios = $this->criterioModel->listarPorEncuesta($idEncuesta);

        $this->view('evaluacion/encuestas/preguntas', [
            'pageTitle' => 'Preguntas de la Encuesta - ' . $encuesta['titulo'],
            'module'    => 'evaluacion',
            'encuesta'  => $encuesta,
            'preguntas' => $preguntas,
            'criterios' => $criterios
        ]);
    }

    public function guardarPregunta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $idEncuesta = (int)$_POST['id_encuesta'];
        if (!$this->verificarPermisoEncuesta($idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para agregar preguntas en esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $idCriterio = !empty($_POST['id_criterio']) ? (int)$_POST['id_criterio'] : null;
        $enunciado  = trim($_POST['enunciado'] ?? '');
        $tipo       = trim($_POST['tipo_respuesta'] ?? 'ESCALA');
        $orden      = !empty($_POST['orden']) ? (int)$_POST['orden'] : 1;
        $id         = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (empty($enunciado)) {
            $_SESSION['flash_error'] = "El enunciado de la pregunta es obligatorio.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas/preguntas/' . $idEncuesta);
            exit;
        }

        $this->preguntaModel->guardar([
            'id'             => $id,
            'id_encuesta'    => $idEncuesta,
            'id_criterio'    => $idCriterio,
            'enunciado'      => $enunciado,
            'tipo_respuesta' => $tipo,
            'es_obligatoria' => 1,
            'orden'          => $orden
        ]);

        $_SESSION['flash_success'] = "Pregunta guardada correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas/preguntas/' . $idEncuesta);
        exit;
    }

    public function eliminarPregunta($id, $idEncuesta)
    {
        if (!$this->verificarPermisoEncuesta((int)$idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para eliminar preguntas de esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $this->preguntaModel->eliminar((int)$id);
        $_SESSION['flash_success'] = "Pregunta eliminada correctamente.";
        header('Location: ' . BASE_URL . '/evaluacion/encuestas/preguntas/' . (int)$idEncuesta);
        exit;
    }

    /**
     * Importa todos los criterios y preguntas activos del Banco Maestro a esta encuesta
     */
    public function importarDesdeBanco($idEncuesta)
    {
        $idEncuesta = (int)$idEncuesta;
        $encuesta = $this->encuestaModel->obtenerPorId($idEncuesta);
        if (!$encuesta) {
            $_SESSION['flash_error'] = "Encuesta no encontrada.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        if (!$this->verificarPermisoEncuesta($idEncuesta)) {
            $_SESSION['flash_error'] = "No tienes permisos para importar rúbricas en esta encuesta.";
            header('Location: ' . BASE_URL . '/evaluacion/encuestas');
            exit;
        }

        $bancoModel = new \App\Models\Evaluacion\Banco();
        $total = $bancoModel->copiarBancoAEncuesta($idEncuesta);

        if ($total > 0) {
            $_SESSION['flash_success'] = "Se importaron exitosamente {$total} preguntas y sus criterios correspondientes desde el Banco Maestro.";
        } else {
            $_SESSION['flash_error'] = "El Banco Maestro no tiene criterios o preguntas activas para importar.";
        }

        header('Location: ' . BASE_URL . '/evaluacion/encuestas/criterios/' . $idEncuesta);
        exit;
    }

    /**
     * Verifica si el usuario actual tiene permisos de gestión sobre la encuesta específica
     */
    protected function verificarPermisoEncuesta($idEncuesta)
    {
        if (Auth::puedeVerTodosLosProgramasEvaluacion()) {
            return true;
        }

        $encuesta = $this->encuestaModel->obtenerPorId((int)$idEncuesta);
        if (!$encuesta) {
            return false;
        }

        $idProgEncuesta = $encuesta['id_programa_estudios'];
        if (!$idProgEncuesta) {
            // Es una encuesta general de todos los programas, el coordinador exclusivo no puede modificarla
            return false;
        }

        $db = (new Model())->getDB();
        $idUser = $_SESSION['sigi_user_id'];
        $idPeriodo = $encuesta['id_periodo_academico'];
        $stmt = $db->prepare("SELECT COUNT(*) FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ? AND id_programa_estudio = ?");
        $stmt->execute([$idUser, $idPeriodo, $idProgEncuesta]);
        return ((int)$stmt->fetchColumn() > 0);
    }
}
