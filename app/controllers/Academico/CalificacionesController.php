<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Academico/Asistencia.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Matricula.php';
require_once __DIR__ . '/../../../app/models/Academico/Reportes.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';

require_once __DIR__ . '/../../../vendor/autoload.php';
//integraciones
require_once __DIR__ . '/../../helpers/Integrator.php';

use App\Models\Academico\Calificaciones;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Asistencia;
use App\Models\Academico\Silabos;
use App\Models\Academico\Matricula;
use App\Models\Academico\Reportes;
use App\Models\Sigi\DatosInstitucionales;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\IndicadorLogroCapacidad;
use Complex\Functions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Helpers\Integrator;

use TCPDF;

class CalificacionesController extends Controller
{
    protected $model;
    protected $objProgramacionUD;
    protected $objDatosIes;
    protected $objDatosSistema;
    protected $objAsistencia;
    protected $objPeriodoAcademico;
    protected $objIndLogroCapacidad;
    protected $objSilabo;
    protected $objMatricula;
    protected $objReporte;
    protected $objIntegrator;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Calificaciones();
        $this->objDatosIes = new DatosInstitucionales();
        $this->objDatosSistema = new DatosSistema();
        $this->objAsistencia = new Asistencia();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objIndLogroCapacidad = new IndicadorLogroCapacidad();
        $this->objSilabo = new Silabos();
        $this->objMatricula = new Matricula();
        $this->objReporte = new Reportes();
        $this->objIntegrator = new Integrator();
    }
    public function evaluar($id_programacion_ud, $nro_calificacion)
    {
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);

        if (!$permitido) {
            $this->view('academico/calificaciones/evaluar', [
                'permitido' => false
            ]);
            return;
        }
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        $id_ud = $programacion['id_unidad_didactica'];
        $indicadores = $this->objIndLogroCapacidad->getIndicadoresLogroCapacidad($id_ud);
        $indicadores_capacidad = [];
        foreach ($indicadores as $value) {
            $indicadores_capacidad[$value['codigo']] = $value['descripcion'];
        }

        $datos = $this->model->getDatosEvaluacion($id_programacion_ud, $nro_calificacion);
        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $secciones_moodle = json_decode($programacion['secciones_moodle'], true);
        $id_seccion_moodle = 0;
        foreach ($secciones_moodle as $seccion) {
            if ($seccion['section'] == $nro_calificacion) {
                $id_seccion_moodle = $seccion['id'];
            }
        }
        $modulos_moodle_disponibles = $this->objIntegrator->getEnabledModules();
        if ($modulos_moodle_disponibles['success']) {
            $datos_modulos_disponibles = $modulos_moodle_disponibles['message']['modules'] ?? [];
        } else {
            $datos_modulos_disponibles = [];
        }

        $config_path = __DIR__ . '/../../../config/moodle_modules.php';
        if (!file_exists($config_path)) {
            return [
                'success' => false,
                'message' => 'Configuración de módulos no encontrada en SIGI'
            ];
        }
        $moodle_config = require $config_path;

        $final_modules = [];
        if (is_array($datos_modulos_disponibles)) {
            foreach ($datos_modulos_disponibles as $modData) {

                // EXTRAEMOS EL NOMBRE TÉCNICO (ej: 'assign', 'quiz')
                $modname = $modData['name'];

                $is_supported = isset($moodle_config['types'][$modname]);
                $campos_completos = [];

                if ($is_supported) {
                    $campos_completos = array_merge(
                        array_values($moodle_config['common']),
                        array_values($moodle_config['types'][$modname]['fields'])
                    );
                }

                $final_modules[] = [
                    'modname'   => $modname,
                    'label'     => $is_supported ? $moodle_config['types'][$modname]['label'] : $modData['label'],
                    'supported' => $is_supported,
                    'fields'    => $campos_completos
                ];
            }
        }

        // buscar seccion de moodle
        if ($id_seccion_moodle > 0 && $programacion['id_moodle'] > 0) {
            $datos_seccion_moodle = $this->objIntegrator->getSectionData($id_seccion_moodle, $programacion['id_moodle']);
        } else {
            $datos_seccion_moodle = [];
        }

        /*echo "<pre>";
        print_r($final_modules);
        echo "</pre>";
        
        echo "<pre>";
        print_r($datos_seccion_moodle);
        echo "</pre>";*/
        $this->view('academico/calificaciones/evaluar', array_merge([
            'module' => 'academico',
            'id_programacion_ud' => $id_programacion_ud,
            'programacion' => $programacion,
            'indicadores_capacidad' => $indicadores_capacidad,
            'periodo_vigente' => $periodo_vigente,
            'nro_calificacion' => $nro_calificacion,
            'estudiantes_inhabilitados' => $estudiantes_inhabilitados,
            'nota_inasistencia' => $nota_inasistencia,
            'permitido' => $permitido,
            'final_modules' => $final_modules,
            'datos_seccion_moodle' => $datos_seccion_moodle
        ], $datos));
    }

    public function guardarCriterio()
    {
        $id_criterio = $_POST['id_criterio'] ?? 0;
        $valor = trim($_POST['valor'] ?? '');

        $id_programacion_ud = $this->model->obtenerProgPorIdCriterio($id_criterio)['id_programacion_ud'];
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->guardarCriterioEvaluacion($id_criterio, $valor);
            echo json_encode(['ok' => $ok]);
        }
        exit;
    }


    public function ver($id_programacion_ud)
    {
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        if (!$permitido) {
            $this->view('academico/calificaciones/ver', [
                'permitido' => false
            ]);
            return;
        }
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $id_ud = $datos['idUnidadDidactica'];
        $indicadores = $this->objIndLogroCapacidad->getIndicadoresLogroCapacidad($id_ud);
        $indicadores_capacidad = [];
        foreach ($indicadores as $value) {
            $indicadores_capacidad[$value['codigo']] = $value['descripcion'];
        }

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }

        $this->view('academico/calificaciones/ver', [
            'id_programacion_ud' => $id_programacion_ud,
            'indicadores_capacidad' => $indicadores_capacidad,
            'permitido' => $permitido,
            'periodo_vigente' => $periodo_vigente,
            'nombreUnidadDidactica' => $datos['nombreUnidadDidactica'],
            'periodo' => $datos['periodo'],
            'nros_calificacion' => $datos['nros_calificacion'],
            'mostrar_calificaciones' => $mostrar_calificaciones,
            'mostrar_promedio_todos' => $mostrar_promedio_todos,
            'estudiantes' => $datos['estudiantes'],
            'notas' => $datos['notas'],
            'promedios' => $datos['promedios'],
            'recuperaciones' => $datos['recuperaciones'],
            'estudiantes_inhabilitados' => $estudiantes_inhabilitados,
            'nota_inasistencia' => $nota_inasistencia,
        ]);
    }
    public function actualizarMostrar()
    {

        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $nro_calificacion = $_POST['nro_calificacion'] ?? 0;
        $mostrar = $_POST['mostrar'] ?? 0;

        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->actualizarMostrarCalificacion($id_programacion_ud, $nro_calificacion, $mostrar);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }
    public function actualizarMostrarPromedioTodos()
    {
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $mostrar = $_POST['mostrar'] ?? 0;

        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->actualizarMostrarPromedioTodos($id_programacion_ud, $mostrar);
        }
        echo json_encode(['ok' => $ok]);
        exit;
    }
    public function guardarPonderadoEvaluacionMasivo()
    {
        $ids_eval = $_POST['ids_eval'] ?? '';
        $ponderado = intval($_POST['ponderado'] ?? 0);
        $ok = $this->model->guardarPonderadoEvaluacionMasivo($ids_eval, $ponderado);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    public function guardarDetalleCriterioMasivo()
    {
        $ids_criterio = $_POST['ids_criterio'] ?? '';
        $detalle = trim($_POST['detalle'] ?? '');
        $ok = $this->model->guardarDetalleCriterioMasivo($ids_criterio, $detalle);
        echo json_encode(['ok' => $ok]);
        exit;
    }
    public function agregarCriterioMasivo()
    {
        $ids_eval = explode(',', $_POST['ids_eval'] ?? '');
        $ok = $this->model->agregarCriterioMasivo($ids_eval);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    public function agregarCriterioConMoodle()
    {
        // 1. Recibir datos desde $_POST (necesario ya que el JS ahora envía FormData)
        if (empty($_POST)) {
            echo json_encode(['ok' => false, 'msg' => 'No se recibieron datos en el servidor']);
            exit;
        }

        $nombre         = trim($_POST['nombre'] ?? '');
        $ids_eval       = explode(',', $_POST['ids_eval'] ?? '');

        // FormData envía booleanos como strings "true"/"false", los convertimos a bool real
        $crear_moodle   = ($_POST['crear_moodle'] === 'true');
        $vincular_sigi  = ($_POST['vincular_sigi'] === 'true');
        $es_calificable = ($_POST['es_calificable'] === 'true');

        $section        = $_POST['section'] ?? 0;
        $moodle_type    = $_POST['moodle_type'] ?? '';

        $moodle_data    = json_decode($_POST['moodle_data'] ?? '{}', true);
        $courseid       = $_POST['courseid'] ?? 0;

        // 2. CREAR EL CRITERIO EN SIGI (Tu lógica intacta)
        if ($vincular_sigi) {
            $criterioGenerado = $this->model->agregarCriterioMasivoConNombre($ids_eval, $nombre);

            if (!$criterioGenerado) {
                echo json_encode(['ok' => false, 'msg' => 'Error al crear el criterio en SIGI']);
                exit;
            }
        }

        // 3. SI EL SWITCH MOODLE ESTÁ ACTIVO -> CREAR EN MOODLE
        if ($crear_moodle) {

            // --- INICIO PROCESAMIENTO DE ARCHIVO ---
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $itemid = $this->objIntegrator->uploadFile(
                    $_FILES['file']['tmp_name'],
                    $_FILES['file']['name']
                );

                if ($itemid) {
                    // Mapeo dinámico para inyectar el itemid en el campo que Moodle espera
                    $map = [
                        'assign'      => 'files_filemanager',
                        'h5pactivity' => 'packagepath',
                        'scorm'       => 'packagefile',
                        'resource'    => 'files_filemanager',
                        'imscp'       => 'package_filemanager',
                        'folder'      => 'files_filemanager'
                    ];

                    if (isset($map[$moodle_type])) {
                        $moodle_data[$map[$moodle_type]] = $itemid;
                        // si 'files_filemanager' no es procesado por el plugin local.
                        if ($map[$moodle_type] === 'files_filemanager') {
                            $moodle_data['files'] = $itemid;
                        }
                    }
                }
            }
            // --- FIN PROCESAMIENTO DE ARCHIVO ---

            // Obtener datos base para armar el IDnumber y el registro (Tu lógica intacta)
            $prog_data = $this->model->obtenerProgPorIdEvaluacion($ids_eval[0]);
            $id_programacion_ud = $prog_data['id_programacion_ud'];

            $programacion = $this->objProgramacionUD->find($id_programacion_ud);
            $secciones_moodle = json_decode($programacion['secciones_moodle'], true) ?? [];

            $realSectionId = 0;
            $id_seccion_seleccionada = (int)($_POST['section_moodle_id'] ?? 0);

            if ($id_seccion_seleccionada > 0) {
                // Si el usuario eligió una opción del select, usamos ese ID directamente
                $realSectionId = $id_seccion_seleccionada;
            } else {
                // Lógica de respaldo: buscar por nro_calificacion si no se envió un ID específico
                $sectionNumber = (int)($section ?? 0);
                foreach ($secciones_moodle as $sec) {
                    if ((int)$sec['section'] === $sectionNumber) {
                        $realSectionId = (int)$sec['id'];
                        break;
                    }
                }
            }

            if ($realSectionId <= 0) {
                echo json_encode([
                    'ok' => false,
                    'msg' => "Error: No se encontró el ID para la sección $sectionNumber."
                ]);
                exit;
            }

            // Solo generamos idnumber si existe vínculo con SIGI
            if ($vincular_sigi && $criterioGenerado) {
                $idnumber = "SIGI-UD{$id_programacion_ud}-C{$criterioGenerado['nro_calificacion']}-O{$criterioGenerado['orden']}";
                $moodle_data['idnumber'] = $idnumber;
            }
            $moodle_data['name'] = $nombre;

            // --- LIMPIEZA DE PARÁMETROS PARA MOODLE (Tu lógica intacta) ---
            $campos_fecha = [
                'allowsubmissionsfromdate',
                'duedate',
                'cutoffdate',
                'timeopen',
                'timeclose',
                'available',
                'deadline',
                'submissionstart',
                'submissionend',
                'assessmentstart',
                'assessmentend',
                'timeavailablefrom',
                'timeavailableto',
                'timeviewfrom',
                'timeviewto'
            ];

            foreach ($moodle_data as $key => $value) {
                if (in_array($key, $campos_fecha)) {
                    if (!empty($value)) {
                        $moodle_data[$key] = is_numeric($value) ? (int)$value : strtotime($value);
                    } else {
                        $moodle_data[$key] = 0;
                    }
                }
                if ($value === 'false' || $value === false) {
                    $moodle_data[$key] = 0;
                } elseif ($value === 'true' || $value === true) {
                    $moodle_data[$key] = 1;
                }
            }

            // Llamada a la API Moodle
            $resultado_moodle = $this->objIntegrator->createModuleMoodle($courseid, $realSectionId, $moodle_type, $moodle_data);

            if ($resultado_moodle['success']) {
                $moodle_resp = $resultado_moodle['data'] ?? $resultado_moodle['message'];

                if ($vincular_sigi && $criterioGenerado) {
                    $graded_final = $es_calificable ? 1 : 0;
                    $data_vinculo = [
                        'id_programacion_ud' => $id_programacion_ud,
                        'nro_calificacion'   => $criterioGenerado['nro_calificacion'],
                        'evaluacion_detalle' => $nombre,
                        'criterio_orden'     => $criterioGenerado['orden'],
                        'moodle_course_id'   => $courseid,
                        'moodle_cmid'        => $moodle_resp['cmid'],
                        'moodle_url'         => $moodle_resp['url'] ?? '',
                        'graded'             => $graded_final,
                        'moodle_grade_item_id' => $moodle_resp['gradeitemid'] ?? null,
                    ];
                    $this->model->registrarVinculoMoodle($data_vinculo);
                }
            } else {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Criterio creado en SIGI, pero falló Moodle: ' . ($resultado_moodle['details'] ?? ''),
                    'raw_response' => $resultado_moodle
                ]);
                exit;
            }
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    /**
     * Procesa la vinculación de un criterio SIGI con un módulo de Moodle
     * Maneja la creación en Moodle y el registro en la tabla acad_moodle_vinculo_criterio
     */
    public function vincularCriterioMoodle()
    {
        // 1. Validaciones de Seguridad y Periodo (Siguiendo tu estilo)
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        if (!$permitido || !$periodo_vigente) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos o el periodo no está vigente.']);
            exit;
        }

        // 2. Recolección de datos base
        $nro_calificacion   = $_POST['nro_calificacion'];
        $evaluacion_detalle = $_POST['evaluacion_detalle']; // Este es el nombre del criterio en SIGI
        $criterio_orden     = $_POST['criterio_orden'];
        $modname            = $_POST['modname'];
        $id_seccion_moodle  = $_POST['id_seccion_moodle'];

        // Puntos 2, 3 y 4 de tu lista
        $vincular_sigi  = isset($_POST['vincular_sigi']) && $_POST['vincular_sigi'] == '1';
        $es_calificable = isset($_POST['es_calificable']) && $_POST['es_calificable'] == '1' ? 1 : 0;

        // --- VALIDACIÓN PUNTO 4: Solo un calificable por criterio ---
        if ($vincular_sigi && $es_calificable == 1) {
            $existe = $this->model->existeModuloCalificable(
                $id_programacion_ud,
                $nro_calificacion,
                $evaluacion_detalle,
                $criterio_orden
            );
            if ($existe) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un módulo marcado como CALIFICABLE para este criterio.']);
                exit;
            }
        }

        // 3. Preparación de parámetros para Moodle (Punto 1 y conversión de fechas)
        $moodle_params = $this->prepararParametrosMoodle($_POST);

        // 4. Llamada al Integrator para crear el módulo en Moodle
        $resultado_moodle = $this->objIntegrator->createModuleMoodle(
            $programacion['id_moodle'], // id_course_moodle
            $id_seccion_moodle,
            $modname,
            $moodle_params
        );

        if ($resultado_moodle['success']) {
            $moodle_data = $resultado_moodle['message']; // Contiene cmid e id (instance)

            // 5. Registro en tu tabla acad_moodle_vinculo_criterio
            // Si vincular_sigi es falso (Punto 3), graded siempre va como 0
            $graded_final = ($vincular_sigi) ? $es_calificable : 0;


            $data_vinculo = [
                'id_programacion_ud' => $id_programacion_ud,
                'nro_calificacion'   => $nro_calificacion,
                'evaluacion_detalle' => $evaluacion_detalle,
                'criterio_orden'     => $criterio_orden,
                'moodle_course_id'   => $programacion['id_moodle'],
                'moodle_cmid'        => $moodle_data['cmid'],
                'graded'             => $graded_final,
                'moodle_grade_item_id' => $moodle_data['gradeitemid'] ?? null,
                'created_at'         => date('Y-m-d H:i:s')
            ];

            $ok = $this->model->registrarVinculoMoodle($data_vinculo);

            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Vinculación exitosa' : 'Error al registrar vínculo en SIGI',
                'moodle_cmid' => $moodle_data['cmid']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error Moodle: ' . $resultado_moodle['message']]);
        }
        exit;
    }

    /**
     * Limpia y convierte los parámetros del POST al formato que Moodle entiende
     */
    private function prepararParametrosMoodle($post)
    {
        $params = [];
        // Filtramos solo los campos que pertenecen al prefijo del formulario dinámico
        // Supongamos que en el JS los envías como moodle_field[nombre]
        if (isset($post['moodle_field'])) {
            foreach ($post['moodle_field'] as $key => $value) {
                // Punto 1: Convertir fechas a UNIX Timestamp
                if ($this->esCampoFecha($key) && !empty($value)) {
                    $params[$key] = strtotime($value);
                } else {
                    $params[$key] = $value;
                }
            }
        }
        return $params;
    }

    private function esCampoFecha($key)
    {
        $campos_fecha = [
            'allowsubmissionsfromdate',
            'duedate',
            'cutoffdate',
            'timeopen',
            'timeclose',
            'available',
            'deadline',
            'submissionstart',
            'submissionend',
            'assessmentstart',
            'assessmentend',
            'timeavailablefrom',
            'timeavailableto',
            'timeviewfrom',
            'timeviewto'
        ];
        return in_array($key, $campos_fecha);
    }


    public function guardarRecuperacion()
    {
        $id_detalle_mat = $_POST['id_detalle_mat'] ?? 0;
        $valor = trim($_POST['valor'] ?? '');

        $det_mat = $this->objMatricula->getMatriculaByDetalle($id_detalle_mat);
        $id_programacion_ud = $det_mat['id_programacion_ud'];
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->guardarRecuperacion($id_detalle_mat, $valor);
        }
        echo json_encode(['ok' => $ok]);
        exit;
    }

    public function registroAuxiliar($id_programacion_ud = null, $nro_calificacion = null)
    {
        if (empty($id_programacion_ud) || empty($nro_calificacion)) {
            $_SESSION['flash_error'] = "Datos Imcompletos para imprimir";
            header('Location: ' . BASE_URL . '/academico');
        }
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        // Periodo (por si lo usas para el título u otros datos)
        $programacion    = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        // Trae TODO lo necesario para la vista
        $datos                         = $this->model->getDatosEvaluacion($id_programacion_ud, $nro_calificacion);
        $estudiantes                   = $datos['estudiantes'] ?? [];
        $evaluacionesEstudiante        = $datos['evaluacionesEstudiante'] ?? [];
        $promediosEvaluacion           = $datos['promediosEvaluacion'] ?? [];
        $promedioFinal                 = $datos['promedioFinal'] ?? [];
        $nombreIndicador               = $datos['nombreIndicador'] ?? '';
        $nombreUnidadDidactica         = $datos['nombreUnidadDidactica'] ?? ''; // si no viene, tomamos del silabo abajo
        $estudiantes_inhabilitados     = [];
        $nota_inasistencia             = $this->objDatosSistema->getNotaSiInasistencia();
        $datosGenerales                = $this->objSilabo->getDatosGenerales($id_programacion_ud);
        $datosSistema = $this->objDatosSistema->buscar();
        if ($nombreUnidadDidactica === '' && !empty($datosGenerales['unidad'])) {
            $nombreUnidadDidactica = $datosGenerales['unidad'];
        }

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $estudiantes_inhabilitados[$id_detalle] = $this->model->inhabilitadoPorInasistencia($id_detalle);
        }

        // ---------- Plantilla robusta de evaluaciones/criterios para el PDF ----------
        $plantillaEvaluaciones = [];

        // 1) Intento: tomar el primer alumno que tenga evaluaciones
        if (!empty($estudiantes) && !empty($evaluacionesEstudiante)) {
            foreach ($estudiantes as $e) {
                $id_det = $e['id_detalle_matricula'] ?? null;
                if ($id_det && !empty($evaluacionesEstudiante[$id_det])) {
                    $plantillaEvaluaciones = array_values($evaluacionesEstudiante[$id_det]);
                    break;
                }
            }
        }

        // 2) Fallback: recorrer todo el arreglo y consolidar por id de evaluación
        if (empty($plantillaEvaluaciones) && !empty($evaluacionesEstudiante)) {
            $map = [];
            foreach ($evaluacionesEstudiante as $evalsAlumno) {
                if (!is_array($evalsAlumno)) continue;
                foreach ($evalsAlumno as $ev) {
                    if (isset($ev['id']) && !isset($map[$ev['id']])) {
                        $map[$ev['id']] = $ev;
                    }
                }
            }
            ksort($map); // orden estable
            $plantillaEvaluaciones = array_values($map);
        }

        // 3) Último recurso (si arriba no hubo datos): leer una calificación “representativa” de BD
        if (empty($plantillaEvaluaciones) && method_exists($this->model, 'getPlantillaEvaluaciones')) {
            $plantillaEvaluaciones = $this->model->getPlantillaEvaluaciones((int)$id_programacion_ud, (int)$nro_calificacion);
        }

        // ---------- Generar PDF ----------
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Registro Auxiliar - ' . ($datosGenerales['unidad'] ?? '') . ' - Calificación ' . $nro_calificacion);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // La vista usa las variables definidas arriba (están en el mismo scope)
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_registro_auxiliar.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Registro Auxiliar - ' . ($datosGenerales['unidad'] ?? '') . ' - Calificación ' . $nro_calificacion . '.pdf', 'I');
    }





    // IMPRESION DE REGISTRO OFICIALL
    public function registroOficial($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        //var_dump($permitido);
        // INFORMACION PARA ASISTENCIAS
        $datos_asistencia = $this->objAsistencia->getDatosAsistencia($id_programacion_ud);

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $nros_calificacion = $datos['nros_calificacion'];
        $notas = $datos['notas'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];
        $datosSistema = $this->objDatosSistema->buscar();
        $id_unidad_didactica = $datos['idUnidadDidactica'];
        $ind_logro_capacidad = $this->objIndLogroCapacidad->getIndicadoresLogroCapacidad($id_unidad_didactica);
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Registro Oficial - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_registro_oficial.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('registro oficial - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }


    //  =========================================== IMPRESION DE ACTA FINAL ==============================================
    public function actaFinal($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Acta Final - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_acta_final.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Acta Final - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }


    //  =========================================== IMPRESION DE ACTA DE RECUPERACION ==============================================
    public function actaRecuperacion($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Acta Final - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_acta_recuperacion.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Acta Final - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }




    //  =========================================== REPORTE PARA SISTEMA REGISTRA ==============================================
    public function reporteRegistra($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        if (!$permitido) {
            $this->view('academico/calificaciones/ver/' . $id_programacion_ud, [
                'permitido' => false
            ]);
            return;
        }
        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);
        $nombre_archivo = "registra_" . $datosGenerales['unidad'] . "_" . $datosGenerales['periodo_academico'] . "_" . date('Ymd_His');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla'); // Cambia el nombre de la hoja
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEEEEE'], // '#eee'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->setCellValue('A1', 'NRO');
        $sheet->setCellValue('B1', 'CÓDIGO ALUMNO');
        $sheet->setCellValue('C1', 'ALUMNO');
        $sheet->setCellValue('D1', 'NOTA');

        $fila = 2;
        foreach ($estudiantes as $i => $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $recup = $recuperaciones[$id_detalle] ?? '';
            $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
            if ($recup != '') {
                $promedio_final = $recup;
            } else {
                $promedio_final = $promedios[$id_detalle];
            }
            if ($inhabilitado) {
                if (is_array($nota_inasistencia) && $est['licencia'] != '') {
                    $promedio_final = '';
                } else {
                    $promedio_final = reset($nota_inasistencia);
                }
            }

            $sheet->setCellValue("A$fila", $i + 1);
            $sheet->setCellValue("B$fila", $est['dni']);
            $sheet->setCellValue("C$fila", $est['apellidos_nombres']);
            $sheet->setCellValue("D$fila", $promedio_final);
            $fila++;
        }
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    //========================================== APARTADO PARA ESTUDIANTES =========================================

    public function consulta()
    {
        $id_estudiante = $_SESSION['sigi_user_id'];
        $id_periodo = $_SESSION['sigi_periodo_actual_id'];
        $id_sede = $_SESSION['sigi_sede_actual'];

        $id_matricula = $this->objMatricula->getMatriculaByEstudiante($id_estudiante, $id_periodo, $id_sede);

        if (!$id_matricula) {
            $_SESSION['flash_error'] = "No cuenta con matricula en el periodo seleccionado";
            $this->view('academico/calificaciones/consulta', [
                'ver' => false,
                'module' => 'academico',
                'pageTitle' => 'Reporte Individual'
            ]);
            exit;
        }

        $califs  = $this->objReporte->calificacionesVisibles($id_matricula['id']);
        $proms   = $this->objReporte->promediosVisibles($id_matricula['id']);
        $asist   = $this->objReporte->asistenciasEstudiante($id_matricula['id']);

        /* organización de datos para la vista */
        $udOrder = [];  // [nombreUd] => idx
        $tablaCalif = []; // [ud][nro] => nota
        foreach ($califs as $c) {
            $ud = $c['ud'];
            if (!isset($udOrder[$ud])) $udOrder[$ud] = $c['id_ud'];
            if ($c['mostrar_calificacion']) { // si esta habilitado mostrar
                $tablaCalif[$ud][$c['nro']] = $c['nota'];
                $tablaCalif[$ud]['recuperacion'] = $c['recuperacion'];
            }
        }
        //var_dump($proms);

        /* asistencia re-mapeada */
        $tablaAsist = []; // [ud][semana] => 'P'|'F'
        foreach ($asist as $a) {
            $tablaAsist[$a['ud']][$a['semana']] = $a['asistencia'];
        }

        $this->view('academico/calificaciones/consulta', [
            'ver' => true,
            'udOrder' => $udOrder,
            'tablaCalif' => $tablaCalif,
            'proms' => $proms,
            'tablaAsist' => $tablaAsist,
            'module' => 'academico',
            'pageTitle' => 'Reporte Individual'
        ]);
        exit;
    }
}
