<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;

use App\Models\Tutoria\Programacion;
use App\Models\Tutoria\EstudianteTutoria;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\Docente;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Programa;

class ProgramacionController extends Controller
{
    protected $model;
    protected $objEstudianteTutoria;
    protected $objSedes;
    protected $objDocente;
    protected $objPeriodoAcademico;
    protected $objPrograma;

    public function __construct()
    {
        parent::__construct();

        // Verificar que el usuario tenga acceso a Tutoría
        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder al módulo de Tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->model                = new Programacion();
        $this->objEstudianteTutoria = new EstudianteTutoria();
        $this->objSedes             = new Sedes();
        $this->objDocente           = new Docente();
        $this->objPeriodoAcademico  = new PeriodoAcademico();
        $this->objPrograma          = new Programa();
    }

    /* ===================== Vista Principal (Listado de Tutores) ===================== */

    public function index()
    {
        if (!Auth::esAdminTutoria()) {
            $_SESSION['flash_error'] = "Solo los administradores o coordinadores de tutoría pueden gestionar la programación.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        $this->view('tutoria/programacion/index', [
            'module'    => 'tutoria',
            'pageTitle' => 'Programación de Tutores'
        ]);
        exit;
    }

    public function data()
    {
        if (Auth::esAdminTutoria()) {
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 0;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'desc';

            $filters = [
                'id_sede'              => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    public function nuevo()
    {
        if (!Auth::esAdminTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para crear una programación.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $docentes = $this->objDocente->getDocentesPorSede($id_sede);

        $this->view('tutoria/programacion/nuevo', [
            'docentes'  => $docentes,
            'isEdit'    => false,
            'module'    => 'tutoria',
            'pageTitle' => 'Nueva Programación de Tutor'
        ]);
        exit;
    }

    public function editar($id)
    {
        if (!Auth::esAdminTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para editar la programación.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $data = $this->model->find($id);
        if (!$data) {
            $_SESSION['flash_error'] = "No se encontró la programación de tutoría solicitada.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $docentes = $this->objDocente->getDocentesPorSede($id_sede);

        $this->view('tutoria/programacion/editar', [
            'data'      => $data,
            'docentes'  => $docentes,
            'isEdit'    => true,
            'module'    => 'tutoria',
            'pageTitle' => 'Editar Programación de Tutoría'
        ]);
        exit;
    }

    public function guardar()
    {
        if (!Auth::esAdminTutoria()) {
            $_SESSION['flash_error'] = "Error: No tiene permisos de administrador para realizar esta acción.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $id_docente   = (int)($_POST['docente'] ?? 0);
        $conclusiones = trim($_POST['conclusiones'] ?? '');
        $id_sede      = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo   = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $id_editar    = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (!$id_docente) {
            $_SESSION['flash_error'] = "Debe seleccionar un docente para la tutoría.";
            $redirect = $id_editar ? "/tutoria/programacion/editar/$id_editar" : "/tutoria/programacion/nuevo";
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }

        try {
            $data = [
                'id'                   => $id_editar,
                'id_sede'              => $id_sede,
                'id_periodo_academico' => $id_periodo,
                'id_docente'           => $id_docente,
                'conclusiones'         => $conclusiones
            ];

            $this->model->guardar($data);
            $_SESSION['flash_success'] = $id_editar
                ? "Programación de tutoría actualizada exitosamente."
                : "Programación de tutoría registrada exitosamente.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo guardar la programación: " . $e->getMessage();
            $redirect = $id_editar ? "/tutoria/programacion/editar/$id_editar" : "/tutoria/programacion/nuevo";
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }
    }

    public function eliminar($id)
    {
        if (!Auth::esAdminTutoria()) {
            $_SESSION['flash_error'] = "Error: No tiene permisos para eliminar registros.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        try {
            $this->model->eliminar($id);
            $_SESSION['flash_success'] = "Programación de tutoría eliminada exitosamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo eliminar la programación. Verifique que no tenga estudiantes o sesiones asociadas.";
        }
        header('Location: ' . BASE_URL . '/tutoria/programacion');
        exit;
    }

    /* ===================== Gestión de Tutorandos (tutoria_estudiantes) ===================== */

    public function tutorandos($id_programacion)
    {
        $programacion = $this->model->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "La programación de tutoría no existe.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        // Permiso: Admin de tutoría O el propio docente asignado a esta tutoría
        $esAdmin = Auth::esAdminTutoria();
        $esSuTutoria = (Auth::esDocenteTutoria() && $programacion['id_docente'] == $_SESSION['sigi_user_id']);
        if (!$esAdmin && !$esSuTutoria) {
            $_SESSION['flash_error'] = "No tiene autorización para ver los tutorandos de esta programación.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        $estudiantesAsignados = $this->objEstudianteTutoria->listarPorProgramacion($id_programacion);
        $programasEstudio     = $this->objPrograma->getAll();

        $this->view('tutoria/programacion/tutorandos', [
            'programacion' => $programacion,
            'estudiantes'  => $estudiantesAsignados,
            'programas'    => $programasEstudio,
            'esAdmin'      => $esAdmin,
            'module'       => 'tutoria',
            'pageTitle'    => 'Tutorandos Asignados - ' . $programacion['docente_formato']
        ]);
        exit;
    }

    /**
     * Pantalla dedicada para asignar un nuevo estudiante a un tutor.
     */
    public function asignarEstudiante($id_programacion)
    {
        $programacion = $this->model->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "La programación de tutoría no existe.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $esAdmin = Auth::esAdminTutoria();
        $esSuTutoria = (Auth::esDocenteTutoria() && (int)$programacion['id_docente'] === (int)($_SESSION['sigi_user_id'] ?? 0));
        if (!$esAdmin && !$esSuTutoria) {
            $_SESSION['flash_error'] = "No tiene autorización para asignar estudiantes a esta tutoría.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        $programasEstudio = $this->objPrograma->getAll();

        $this->view('tutoria/programacion/asignar_estudiante', [
            'programacion' => $programacion,
            'programas'    => $programasEstudio,
            'esAdmin'      => $esAdmin,
            'module'       => 'tutoria',
            'pageTitle'    => 'Asignar Estudiante - ' . $programacion['docente_formato']
        ]);
        exit;
    }

    public function guardarTutorando()
    {
        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $id_programacion = (int)($_POST['id_programacion_tutoria'] ?? 0);
        $id_estudiante   = (int)($_POST['id_estudiante'] ?? 0);
        $observacion     = trim($_POST['observacion'] ?? '');

        if (!$id_programacion || !$id_estudiante) {
            $_SESSION['flash_error'] = "Debe seleccionar un estudiante válido para asignar a la tutoría.";
            header('Location: ' . BASE_URL . '/tutoria/programacion/asignarEstudiante/' . $id_programacion);
            exit;
        }

        $programacion = $this->model->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "La programación de tutoría no existe en el sistema.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        // Validación estricta de permisos: Administrador O tutor titular de la programación
        $esAdmin = Auth::esAdminTutoria();
        $esSuTutoria = (Auth::esDocenteTutoria() && (int)$programacion['id_docente'] === (int)($_SESSION['sigi_user_id'] ?? 0));
        if (!$esAdmin && !$esSuTutoria) {
            $_SESSION['flash_error'] = "Acceso denegado: No tiene autorización para asignar estudiantes a esta tutoría.";
            header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $id_programacion);
            exit;
        }

        $ok = $this->objEstudianteTutoria->asignar($id_programacion, $id_estudiante, $observacion);
        if ($ok) {
            $_SESSION['flash_success'] = "Estudiante asignado al tutor correctamente.";
            header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $id_programacion);
        } else {
            $_SESSION['flash_error'] = "El estudiante ya se encuentra asignado a esta programación de tutoría.";
            header('Location: ' . BASE_URL . '/tutoria/programacion/asignarEstudiante/' . $id_programacion);
        }
        exit;
    }

    public function eliminarTutorando($id_tutoria_estudiante, $id_programacion)
    {
        $id_programacion       = (int)$id_programacion;
        $id_tutoria_estudiante = (int)$id_tutoria_estudiante;

        $programacion = $this->model->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/programacion');
            exit;
        }

        $esAdmin = Auth::esAdminTutoria();
        $esSuTutoria = (Auth::esDocenteTutoria() && (int)$programacion['id_docente'] === (int)($_SESSION['sigi_user_id'] ?? 0));
        if (!$esAdmin && !$esSuTutoria) {
            $_SESSION['flash_error'] = "No tiene permisos para retirar estudiantes de esta tutoría.";
            header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $id_programacion);
            exit;
        }

        try {
            $this->objEstudianteTutoria->eliminar($id_tutoria_estudiante);
            $_SESSION['flash_success'] = "Estudiante retirado de la tutoría exitosamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo retirar el estudiante. Puede tener sesiones individuales o fichas asociadas.";
        }

        header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $id_programacion);
        exit;
    }

    public function buscarEstudiantesAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!Auth::tieneRolEnTutoria()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $id_programacion = (int)($_GET['id_programacion'] ?? 0);
        $id_programa     = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;
        $filtro          = trim($_GET['q'] ?? '');

        // Obtener la sede desde la programación seleccionada
        $id_sede = 0;
        if ($id_programacion > 0) {
            $prog = $this->model->find($id_programacion);
            if ($prog && !empty($prog['id_sede'])) {
                $id_sede = (int)$prog['id_sede'];
            }
        }
        if (!$id_sede && !empty($_SESSION['sigi_sede_actual'])) {
            $id_sede = (int)$_SESSION['sigi_sede_actual'];
        }

        // Búsqueda de estudiantes disponibles con vinculación a matricula/estudiante_programa y exclusión de ya asignados
        $estudiantes = $this->buscarEstudiantesDisponiblesQuery($id_sede, $filtro, $id_programacion, $id_programa);
        echo json_encode($estudiantes, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Consulta optimizada que busca estudiantes de la sede que no estén asignados a la tutoría.
     */
    protected function buscarEstudiantesDisponiblesQuery($id_sede, $filtro, $id_programacion, $id_programa)
    {
        $db = (new \Core\Model())->getDB();
        $params = [];
        $where = [];

        if ($id_sede > 0) {
            $where[] = "(u.id_sede = :id_sede OR m.id_sede = :id_sede)";
            $params[':id_sede'] = $id_sede;
        }

        if (!empty($id_programa)) {
            $where[] = "(u.id_programa_estudios = :id_programa OR ple.id_programa_estudios = :id_programa)";
            $params[':id_programa'] = $id_programa;
        }

        if (!empty($id_programacion)) {
            $where[] = "u.id NOT IN (SELECT id_estudiante FROM tutoria_estudiantes WHERE id_programacion_tutoria = :id_prog)";
            $params[':id_prog'] = $id_programacion;
        }

        if (!empty($filtro)) {
            $where[] = "(u.dni LIKE :filtro OR u.apellidos_nombres LIKE :filtro)";
            $params[':filtro'] = '%' . $filtro . '%';
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "WHERE 1=1";

        $sql = "SELECT DISTINCT u.id, u.dni, u.apellidos_nombres, u.correo,
                       COALESCE(pe.nombre, pe2.nombre, 'General') AS programa_nombre
                FROM sigi_usuarios u
                LEFT JOIN acad_estudiante_programa aep ON aep.id_usuario = u.id
                LEFT JOIN sigi_planes_estudio ple ON aep.id_plan_estudio = ple.id
                LEFT JOIN sigi_programa_estudios pe ON ple.id_programa_estudios = pe.id
                LEFT JOIN sigi_programa_estudios pe2 ON u.id_programa_estudios = pe2.id
                LEFT JOIN acad_matricula m ON m.id_estudiante = aep.id
                $whereClause
                  AND (u.id_rol = 7 OR aep.id IS NOT NULL)
                  AND u.estado = 1
                ORDER BY u.apellidos_nombres ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['nombre_completo'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
        }

        return $data;
    }

    public function guardarConclusiones()
    {
        $id_programacion = (int)($_POST['id_programacion'] ?? 0);
        $conclusiones    = trim($_POST['conclusiones'] ?? '');

        if ($id_programacion > 0) {
            $this->model->guardarConclusiones($id_programacion, $conclusiones);
            $_SESSION['flash_success'] = "Conclusiones finales de la tutoría guardadas con éxito.";
        }

        header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $id_programacion);
        exit;
    }
}
