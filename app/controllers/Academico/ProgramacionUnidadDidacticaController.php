<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Sesiones.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';
require_once __DIR__ . '/../../../app/models/Sigi/UnidadDidactica.php';

use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Silabos;
use App\Models\Academico\Sesiones;
use App\Models\Sigi\Programa;
use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Competencias;
use App\Models\Sigi\Docente;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\IndicadorLogroCapacidad;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\UnidadDidactica;

class ProgramacionUnidadDidacticaController extends Controller
{
    protected $model;
    protected $objSilabo;
    protected $objSesiones;
    protected $objPeriodoAcademico;
    protected $objPrograma;
    protected $objModuloFormativo;
    protected $objCompetencia;
    protected $objDocente;
    protected $objUnidadDidactica;

    protected $objIndicadorLogroCapacidad;
    protected $objDatosSistema;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProgramacionUnidadDidactica();
        $this->objSilabo = new Silabos();
        $this->objSesiones = new Sesiones();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objPrograma = new Programa();
        $this->objModuloFormativo = new ModuloFormativo();
        $this->objCompetencia = new Competencias();
        $this->objDocente = new Docente();
        $this->objIndicadorLogroCapacidad = new IndicadorLogroCapacidad();
        $this->objDatosSistema = new DatosSistema();
        $this->objUnidadDidactica = new UnidadDidactica();
    }

    public function index()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $docentes = $this->objDocente->getDocentesPorSede($id_sede);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($id_periodo);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $this->view('academico/programacionUnidadDidactica/index', [
            'periodo_vigente' => $periodo_vigente,
            'programas' => $programas,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Programaciones de Unidades Didácticas'
        ]);
    }

    public function data()
    {
        if (\Core\Auth::esAdminAcademico()):
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
        endif;
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

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $this->view('academico/programacionUnidadDidactica/nuevo', [
            'periodo_vigente' => $periodo_vigente,
            'programas' => $programas,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Nueva Programación de Unidad Didáctica'
        ]);
    }
    public function guardar()
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);

        if (\Core\Auth::esAdminAcademico() && $periodo_vigente):
            $id_unidad_didactica = (int)($_POST['id_unidad_didactica'] ?? 0);
            $id_docente          = (int)($_POST['id_docente'] ?? 0);
            $turno               = trim($_POST['turno'] ?? '');
            $seccion             = trim($_POST['seccion'] ?? '');

            $errores = [];
            if (!$id_unidad_didactica || !$id_docente || $turno === '' || $seccion === '') {
                $errores[] = "Complete todos los campos obligatorios.";
            }
            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica/nuevo');
                exit;
            }

            try {
                $this->crearProgramacionCompleta($id_unidad_didactica, $id_docente, $turno, $seccion);
                $_SESSION['flash_success'] = "Programación y sílabo registrados exitosamente.";
                header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica/nuevo');
                exit;
            }
        endif;
        exit;
    }


    /**
     * Crea programación + sílabo + actividades + sesiones para una UD.
     * Reutilizable por guardar() individual y programacionMasiva().
     * Lanza \Exception con mensaje de negocio si algo falla.
     */
    private function crearProgramacionCompleta(
        int $id_unidad_didactica,
        int $id_docente,
        string $turno,
        string $seccion
    ): int {
        $id_sede = (int)($_SESSION['sigi_sede_actual'] ?? 0);
        $id_periodo_academico = (int)($_SESSION['sigi_periodo_actual_id'] ?? 0);

        // Validaciones mínimas
        if (!$id_unidad_didactica || !$id_docente || !$id_sede || !$id_periodo_academico) {
            throw new \Exception('Parámetros incompletos para crear la programación.');
        }

        // Evitar duplicados
        if ($this->model->existeProgramacion(
            $id_unidad_didactica,
            $id_sede,
            $id_periodo_academico,
            $turno,
            $seccion
        )) {
            throw new \Exception('Ya existe una programación con esos datos.');
        }

        $db = $this->model->getDB();
        $db->beginTransaction();

        try {
            // 1) Programación UD
            $id_prog_ud = $this->model->registrarProgramacion([
                'id_unidad_didactica'   => $id_unidad_didactica,
                'id_docente'            => $id_docente,
                'id_sede'               => $id_sede,
                'id_periodo_academico'  => $id_periodo_academico,
                'turno'                 => $turno,
                'seccion'               => $seccion,
                'supervisado'           => 0,
                'reg_evaluacion'        => 0,
                'reg_auxiliar'          => 0,
                'prog_curricular'       => 0,
                'otros'                 => 0,
                'logros_obtenidos'      => '',
                'dificultades'          => '',
                'sugerencias'           => '',
            ]);

            // 2) Sílabo
            $fecha_registro = date('Y-m-d');
            $id_silabo = $this->objSilabo->registrarSilabo([
                'id_prog_unidad_didactica'       => $id_prog_ud,
                'id_coordinador'                 => 0,
                'fecha_inicio'                   => $fecha_registro,
                'sumilla'                        => '',
                'horario'                        => '',
                'metodologia'                    => '',
                'recursos_didacticos'            => '',
                'sistema_evaluacion'             => '',
                'estrategia_evaluacion_indicadores' => '',
                'estrategia_evaluacion_tecnica'  => '',
                'promedio_indicadores_logro'     => '',
                'recursos_bibliograficos_impresos'  => '',
                'recursos_bibliograficos_digitales' => '',
            ]);

            // 3) Programación de actividades (semanas)
            $cant_semanas = $this->objDatosSistema->getCantidadSemanas();
            if (!$cant_semanas || $cant_semanas < 1) {
                throw new \Exception('No está configurada la cantidad de semanas del sistema.');
            }

            $id_ind_logro_capacidad = $this->objIndicadorLogroCapacidad->getPrimerIndLogroCapacidad($id_unidad_didactica);
            if (!$id_ind_logro_capacidad) {
                throw new \Exception('No hay capacidad asociada a la unidad didáctica.');
            }

            $actividades_ids = [];
            for ($i = 1; $i <= $cant_semanas; $i++) {
                $fecha_semana = date('Y-m-d', strtotime('+' . ($i - 1) . ' week', strtotime($fecha_registro)));
                $id_act = $this->objSilabo->registrarActividadSilabo([
                    'id_silabo'                  => $id_silabo,
                    'id_ind_logro_aprendizaje'   => $id_ind_logro_capacidad,
                    'semana'                     => $i,
                    'fecha'                      => $fecha_semana,
                    'elemento_capacidad'         => '',
                    'actividades_aprendizaje'    => '',
                    'contenidos_basicos'         => '',
                    'tareas_previas'             => '',
                ]);
                $actividades_ids[] = $id_act;
            }

            // 4) Sesiones + momentos + evaluaciones
            $id_modulo = $this->objModuloFormativo->getModuloByUnidadDidactica($id_unidad_didactica);
            $id_comp_transversal = $this->objCompetencia->getPrimerCompetenciaTransversal($id_modulo);
            if (!$id_comp_transversal) {
                throw new \Exception('No hay competencia TRANSVERSAL asociada al módulo formativo.');
            }
            foreach ($actividades_ids as $i => $id_actividad) {
                $id_sesion = $this->objSesiones->registrarSesionAprendizaje([
                    'id_prog_actividad_silabo'             => $id_actividad,
                    'tipo_actividad'                        => '',
                    'tipo_sesion'                           => '',
                    'denominacion'                          => '',
                    'fecha_desarrollo'                      => date('Y-m-d', strtotime('+' . $i . ' week', strtotime($fecha_registro))),
                    'id_ind_logro_competencia_vinculado'    => $id_comp_transversal,
                    'id_ind_logro_capacidad_vinculado'      => $id_ind_logro_capacidad,
                    'logro_sesion'                          => '',
                    'bibliografia_obligatoria_docente'      => '',
                    'bibliografia_opcional_docente'         => '',
                    'bibliografia_obligatoria_estudiante'   => '',
                    'bibliografia_opcional_estudiante'      => '',
                    'anexos'                                => '',
                ]);

                foreach (['INICIO', 'DESARROLLO', 'CIERRE'] as $momento) {
                    $this->objSesiones->registrarMomentoSesion([
                        'id_sesion_aprendizaje' => $id_sesion,
                        'momento'               => $momento,
                        'estrategia'            => '',
                        'actividad'             => '',
                        'recursos'              => '',
                        'tiempo'                => 20,
                    ]);
                }

                foreach (['INICIO', 'DESARROLLO', 'CIERRE'] as $momento) {
                    $this->objSesiones->registrarActividadEvaluacionSesion([
                        'id_sesion_aprendizaje'   => $id_sesion,
                        'indicador_logro_sesion'  => '',
                        'tecnica'                 => '',
                        'instrumentos'            => '',
                        'peso'                    => 33,
                        'momento'                 => $momento,
                    ]);
                }
            }

            $db->commit();
            return $id_prog_ud;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function editar($id)
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        // Cargar datos de la programación y combos
        $programacion = $this->model->getProgramacionById($id);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada.";
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        }
        $docentes = $this->objDocente->getDocentesPorSede($programacion['id_sede']);

        $this->view('academico/programacionUnidadDidactica/editar', [
            'periodo_vigente' => $periodo_vigente,
            'programacion' => $programacion,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Editar Programación de Unidad Didáctica'
        ]);
    }

    public function guardarEdicion()
    {
        if (\Core\Auth::esAdminAcademico()):
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
        endif;
        header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
        exit;
    }

    public function programacionMasiva()
    {
        if (!\Core\Auth::esAdminAcademico()) {
            exit;
        }

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id'] ?? 0);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        if (!$periodo_vigente) {
            $_SESSION['flash_error'] = 'El periodo académico ha finalizado.';
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        }

        // Datos del modal
        $id_programa_estudios = (int)($_POST['id_programa_estudios'] ?? 0);
        $id_plan_estudio      = (int)($_POST['id_plan_estudio'] ?? 0);
        $id_modulo_formativo  = (int)($_POST['id_modulo_formativo'] ?? 0);
        $id_semestre          = (int)($_POST['id_semestre'] ?? 0);
        $turno                = trim($_POST['turno'] ?? '');
        $seccion              = trim($_POST['seccion'] ?? '');
        $id_sede              = (int)($_SESSION['sigi_sede_actual'] ?? 0);

        if (!$id_semestre || $turno === '' || $seccion === '') {
            $_SESSION['flash_error'] = 'Complete todos los campos de la programación masiva.';
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        }

        // Obtener UDs del semestre
        // Usa el modelo de UD (debes tener método porSemestre/getBySemestre)
        $uds = $this->objUnidadDidactica->getUnidadesBySemestre($id_semestre); // <-- si tu modelo se llama distinto, ajusta aquí
        if (!$uds || !is_array($uds) || count($uds) === 0) {
            $_SESSION['flash_error'] = 'No hay unidades didácticas para el semestre seleccionado.';
            header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
            exit;
        }
        // Determinar docente por defecto:
        $id_docente_defecto = $_SESSION['sigi_user_id'];
        // Procesar en lote (por UD, con try/catch individual)
        $ok = 0;
        $skip = 0;
        $err = 0;
        $mensajesErr = [];

        foreach ($uds as $ud) {
            $id_ud = (int)$ud['id'];
            // Evitar duplicados previamente
            if ($this->model->existeProgramacion($id_ud, $id_sede, (int)$_SESSION['sigi_periodo_actual_id'], $turno, $seccion)) {
                $skip++;
                continue;
            }

            try {
                $this->crearProgramacionCompleta($id_ud, $id_docente_defecto, $turno, $seccion);
                $ok++;
            } catch (\Exception $e) {
                $err++;
                // Guardar detalle de error por UD
                $mensajesErr[] = ($ud['nombre'] ?? ('UD ' . $id_ud)) . ': ' . $e->getMessage();
            }
        }

        // Feedback
        $msg = "Masivo completado. Creadas: $ok. Existentes: $skip. Errores: $err.";
        if ($err && !empty($mensajesErr)) {
            $msg .= ' Detalles: ' . implode(' | ', array_slice($mensajesErr, 0, 5)); // limita output
        }

        $_SESSION['flash_' . ($err ? 'error' : 'success')] = $msg;
        header('Location: ' . BASE_URL . '/academico/programacionUnidadDidactica');
        exit;
    }
}
