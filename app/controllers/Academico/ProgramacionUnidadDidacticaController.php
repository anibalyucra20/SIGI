<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Sesiones.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';

use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Silabos;
use App\Models\Academico\Sesiones;
use App\Models\Sigi\Programa;
use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Competencias;
use App\Models\Sigi\Docente;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\IndicadorLogroCapacidad;

class ProgramacionUnidadDidacticaController extends Controller
{
    protected $model;
    protected $objSilabo;
    protected $objSesiones;
    protected $objPrograma;
    protected $objModuloFormativo;
    protected $objCompetencia;
    protected $objDocente;
    
    protected $objIndicadorLogroCapacidad;
    protected $objDatosSistema;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProgramacionUnidadDidactica();
        $this->objSilabo = new Silabos();
        $this->objSesiones = new Sesiones();
        $this->objPrograma = new Programa();
        $this->objModuloFormativo = new ModuloFormativo();
        $this->objCompetencia = new Competencias();
        $this->objDocente = new Docente();
        $this->objIndicadorLogroCapacidad = new IndicadorLogroCapacidad();
        $this->objDatosSistema = new DatosSistema();
    }

    public function index()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $docentes = $this->objDocente->getDocentesPorSede($id_sede);

        $this->view('academico/programacionUnidadDidactica/index', [
            'programas' => $programas,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Programaciones de Unidades Didácticas'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 0;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $filters = [
            'id_sede'   => $_SESSION['sigi_sede_actual'] ?? 0,
            'id_periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
            'programa'  => $_GET['filter_programa'] ?? null,
            'plan'      => $_GET['filter_plan'] ?? null,
            'modulo'    => $_GET['filter_modulo'] ?? null,
            'semestre'  => $_GET['filter_semestre'] ?? null,
            'docente'   => $_GET['filter_docente'] ?? null,
            'turno'     => $_GET['filter_turno'] ?? null,
            'seccion'   => $_GET['filter_seccion'] ?? null,
            'unidad'    => $_GET['filter_unidad'] ?? null,
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
    public function nuevo()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        // Obtener opciones iniciales
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $docentes = $this->objDocente->getDocentesPorSede(
            $_SESSION['sigi_sede_actual'] ?? 0,
        );

        $this->view('academico/programacionUnidadDidactica/nuevo', [
            'programas' => $programas,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Nueva Programación de Unidad Didáctica'
        ]);
    }
    public function guardar()
    {
        // Recoge los datos del formulario
        $id_unidad_didactica = $_POST['id_unidad_didactica'];
        $id_docente = $_POST['id_docente'];
        $turno = $_POST['turno'];
        $seccion = $_POST['seccion'];
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo_academico = $_SESSION['sigi_periodo_actual_id'] ?? 0;

        // Validar campos obligatorios
        $errores = [];
        if (empty($id_unidad_didactica) || empty($id_docente) || empty($turno) || empty($seccion)) {
            $errores[] = "Complete todos los campos obligatorios.";
        }

        // Restricción única por lógica PHP
        if ($this->model->existeProgramacion(
            $id_unidad_didactica,
            $id_sede,
            $id_periodo_academico,
            $turno,
            $seccion
        )) {
            $errores[] = "Ya existe una programación para esta combinación de unidad didáctica, docente, sede, periodo, turno y sección.";
        }

        if (!empty($errores)) {
            // Recarga la vista con errores
            $programas = $this->objPrograma->getProgramasPorSede($id_sede);
            $docentes = $this->objDocente->getDocentesPorSede($id_sede);
            $this->view('academico/programacionUnidadDidactica/nuevo', [
                'errores' => $errores,
                'programas' => $programas,
                'docentes' => $docentes
            ]);
            return;
        }

        // ---- INICIA LA TRANSACCIÓN ----
        try {
            $db = $this->model->getDB();
            $db->beginTransaction();

            // 1. Registrar programación
            $id_prog_ud = $this->model->registrarProgramacion([
                'id_unidad_didactica' => $id_unidad_didactica,
                'id_docente' => $id_docente,
                'id_sede' => $id_sede,
                'id_periodo_academico' => $id_periodo_academico,
                'turno' => $turno,
                'seccion' => $seccion,
                // valores automáticos
                'supervisado' => 0,
                'reg_evaluacion' => 0,
                'reg_auxiliar' => 0,
                'prog_curricular' => 0,
                'otros' => 0,
                'logros_obtenidos' => '',
                'dificultades' => '',
                'sugerencias' => ''
            ]);

            // 2. acad_silabos (campos automáticos)
            $fecha_registro = date('Y-m-d');
            $id_silabo = $this->objSilabo->registrarSilabo([
                'id_prog_unidad_didactica' => $id_prog_ud,
                'id_coordinador' => 0,
                'fecha_inicio' => $fecha_registro,
                // todos los demás campos ""
                'sumilla' => '',
                'horario' => '',
                'metodologia' => '',
                'recursos_didacticos' => '',
                'sistema_evaluacion' => '',
                'estrategia_evaluacion_indicadores' => '',
                'estrategia_evaluacion_tecnica' => '',
                'promedio_indicadores_logro' => '',
                'recursos_bibliograficos_impresos' => '',
                'recursos_bibliograficos_digitales' => ''
            ]);

            // 3. acad_programacion_actividades_silabo (N = cant_semanas)
            $cant_semanas = $this->objDatosSistema->getCantidadSemanas();
            if (!$cant_semanas || $cant_semanas < 1) {
                throw new \Exception("No está configurada la cantidad de semanas del sistema.");
            }
            $id_ind_logro_aprendizaje = $this->objIndicadorLogroCapacidad->getPrimerIndLogroCapacidad($id_unidad_didactica);
            if (!$id_ind_logro_aprendizaje) {
                throw new \Exception("No hay capacidad asociada a la unidad didáctica.");
            }
            $actividades_ids = [];
            for ($i = 1; $i <= $cant_semanas; $i++) {
                $fecha_semana = date('Y-m-d', strtotime("+" . ($i - 1) . " week", strtotime($fecha_registro)));
                $id_act = $this->objSilabo->registrarActividadSilabo([
                    'id_silabo' => $id_silabo,
                    'id_ind_logro_aprendizaje' => $id_ind_logro_aprendizaje,
                    'semana' => $i,
                    'fecha' => $fecha_semana,
                    'elemento_capacidad' => '',
                    'actividades_aprendizaje' => '',
                    'contenidos_basicos' => '',
                    'tareas_previas' => ''
                ]);
                $actividades_ids[] = $id_act;
            }

            // 4. acad_sesion_aprendizaje + momentos + evaluaciones
            $id_modulo = $this->objModuloFormativo->getModuloByUnidadDidactica($id_unidad_didactica);
            $id_competencia_transversal = $this->objCompetencia->getPrimerCompetenciaTransversal($id_modulo);
            if (!$id_competencia_transversal) {
                throw new \Exception("No hay competencia TRANSVERSAL asociada al módulo formativo.");
            }
            foreach ($actividades_ids as $i => $id_actividad) {
                // acad_sesion_aprendizaje
                $id_sesion = $this->objSesiones->registrarSesionAprendizaje([
                    'id_prog_actividad_silabo' => $id_actividad,
                    'tipo_actividad' => '',
                    'tipo_sesion' => '',
                    'denominacion' => '',
                    'fecha_desarrollo' => date('Y-m-d', strtotime("+" . $i . " week", strtotime($fecha_registro))),
                    'id_ind_logro_competencia_vinculado' => $id_competencia_transversal,
                    'id_ind_logro_capacidad_vinculado' => $id_ind_logro_aprendizaje,
                    'logro_sesion' => '',
                    'bibliografia_obligatoria_docente' => '',
                    'bibliografia_opcional_docente' => '',
                    'bibliografia_obligatoria_estudiante' => '',
                    'bibliografia_opcional_estudiante' => '',
                    'anexos' => ''
                ]);

                // acad_momentos_sesion_aprendizaje (INICIO, DESARROLLO, CIERRE)
                foreach (['INICIO', 'DESARROLLO', 'CIERRE'] as $momento) {
                    $this->objSesiones->registrarMomentoSesion([
                        'id_sesion_aprendizaje' => $id_sesion,
                        'momento' => $momento,
                        'estrategia' => '',
                        'actividad' => '',
                        'recursos' => '',
                        'tiempo' => 20
                    ]);
                }

                // acad_actividad_evaluacion_sesion_aprendizaje (INICIO, DESARROLLO, CIERRE)
                foreach (['INICIO', 'DESARROLLO', 'CIERRE'] as $momento) {
                    $this->objSesiones->registrarActividadEvaluacionSesion([
                        'id_sesion_aprendizaje' => $id_sesion,
                        'indicador_logro_sesion' => '',
                        'tecnica' => '',
                        'instrumentos' => '',
                        'peso' => 33,
                        'momento' => $momento
                    ]);
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = "Programación y sílabo registrados exitosamente, junto con actividades y sesiones.";
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        } catch (\Exception $e) {
            if (isset($db)) $db->rollBack();
            $mensaje = "Error: " . $e->getMessage();
            $_SESSION['flash_error'] = "No se pudo registrar la programación. $mensaje";
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica/nuevo');
            exit;
        }
    }




    public function editar($id)
    {
        // Cargar datos de la programación y combos
        $programacion = $this->model->getProgramacionById($id);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada.";
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        }
        $docentes = $this->objDocente->getDocentesPorSede($programacion['id_sede']);

        $this->view('academico/programacionUnidadDidactica/editar', [
            'programacion' => $programacion,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Editar Programación de Unidad Didáctica'
        ]);
    }

    public function guardarEdicion()
    {
        $id = $_POST['id'] ?? null;
        $id_docente = $_POST['id_docente'] ?? null;

        $errores = [];

        if (empty($id) || empty($id_docente)) {
            $errores[] = "Seleccione un docente.";
        }

        // Validar existencia de la programación
        $programacion = $this->model->getProgramacionById($id);
        if (!$programacion) {
            $errores[] = "No se encontró la programación a editar.";
        }

        // Validar existencia del docente en la sede
        if (!$this->objDocente->existeDocente($id_docente, $programacion['id_sede'])) {
            $errores[] = "El docente seleccionado no existe en la sede.";
        }

        if (!empty($errores)) {
            $docentes = $this->objDocente->getDocentesPorSede($programacion['id_sede']);
            $this->view('academico/programacionUnidadDidactica/editar', [
                'errores' => $errores,
                'programacion' => $programacion,
                'docentes' => $docentes,
                'module' => 'academico',
                'pageTitle' => 'Editar Programación de Unidad Didáctica'
            ]);
            return;
        }

        // Actualizar solo el campo docente
        $this->model->actualizarDocente($id, $id_docente);

        $_SESSION['flash_success'] = "Docente actualizado correctamente.";
        header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
        exit;
    }
}
