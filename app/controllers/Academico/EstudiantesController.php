<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Estudiantes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Rol.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

//integraciones
require_once __DIR__ . '/../../helpers/Integrator.php';


use App\Models\Academico\Estudiantes;
use App\Models\Sigi\Docente;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Rol;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

use PhpOffice\PhpSpreadsheet\IOFactory;
// Usar el Integrador
use App\Helpers\Integrator;


class EstudiantesController extends Controller
{
    protected $model;
    protected $objDocente;
    protected $objSede;
    protected $objPrograma;
    protected $objPlan;
    protected $objPeriodoAcademico;
    protected $objDatosSistema;
    protected $objRol;
    protected $objIntegrator;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Estudiantes();
        $this->objDocente = new Docente();
        $this->objSede = new Sedes();
        $this->objPrograma = new Programa();
        $this->objPlan = new Plan();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objDatosSistema = new DatosSistema();
        $this->objRol = new Rol();
        $this->objIntegrator = new Integrator();
    }


    public function index()
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $this->view('academico/estudiantes/index', [
            'periodo_vigente' => $periodo_vigente,
            'programas' => $programas,
            'module' => 'academico',
            'pageTitle' => 'Estudiantes'
        ]);
    }

    public function data()
    {
        if (\Core\Auth::esAdminAcademico()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 2; // Apellidos por defecto
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'id_sede'    => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
                'id_programa' => $_GET['filter_programa'] ?? null,
                'id_plan' => $_GET['filter_plan'] ?? null,
                'dni' => $_GET['filter_dni'] ?? null,
                'apellidos_nombres' => $_GET['filter_apellidos_nombres'] ?? null,
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);
            $estudiantes = $result['data'];
            //actualizamos los permisos elegidos en datos sistema 
            foreach ($estudiantes as $estudiante) {
                //var_dump($estudiante['id']);
                $id_estudiante = $estudiante['id'];
                $this->registrar_permiso_inicial($id_estudiante);
            }
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
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $planes = [];
        $errores = $errores ?? [];

        $this->view('academico/estudiantes/nuevo', [
            'periodo_vigente' => $periodo_vigente,
            'programas' => $programas,
            'planes' => $planes,
            'errores' => $errores,
            'module' => 'academico',
            'pageTitle' => 'Nuevo Estudiante'
        ]);
    }

    public function editar($id)
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $estudiante = $this->model->find($id);
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $planes = $this->objPlan->getPlanesByPrograma($estudiante['id_programa_estudios']);
        $periodos = $this->objPeriodoAcademico->getPeriodos();
        $sedes = $this->objSede->getSedes();
        $errores = $errores ?? [];

        $this->view('academico/estudiantes/editar', [
            'estudiante' => $estudiante,
            'programas' => $programas,
            'planes' => $planes,
            'periodos' => $periodos,
            'sedes' => $sedes,
            'errores' => $errores,
            'module' => 'academico',
            'pageTitle' => 'Editar Estudiante'
        ]);
    }

    public function guardar()
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        if (\Core\Auth::esAdminAcademico()):
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $errores = [];
            $isNuevo = empty($_POST['id']);

            $password = \Core\Auth::crearPassword(8);
            $password_secure = password_hash($password, PASSWORD_DEFAULT);
            $data = [
                'id'                  => $_POST['id'] ?? null,
                'tipo_doc'            => trim($_POST['tipo_doc']),
                'dni'                 => trim($_POST['dni']),
                'apellidos_nombres'   => trim($_POST['ApellidoPaterno']) . '_' . trim($_POST['ApellidoMaterno']) . '_' . trim($_POST['Nombres']),
                'genero'              => $_POST['genero'],
                'fecha_nacimiento'    => $_POST['fecha_nacimiento'],
                'direccion'           => trim($_POST['direccion']),
                'correo'              => trim($_POST['correo']),
                'telefono'            => trim($_POST['telefono']),
                'discapacidad'        => $_POST['discapacidad'],
                'id_programa_estudios' => $_POST['id_programa_estudios'],
                'id_plan_estudio'     => $_POST['id_plan_estudio'],
                'estado'              => $_POST['estado'] ?? 1,
                'id_rol'              => 7, // Ajusta si tu rol de ESTUDIANTE es otro id
                'password'             => $password_secure,
                'reset_password'       => 0,
                'token_password'       => '',
                // Sede y periodo
                'id_sede'             => $isNuevo ? ($_SESSION['sigi_sede_actual'] ?? 0) : $_POST['id_sede'],
                'id_periodo'          => $isNuevo ? ($_SESSION['sigi_periodo_actual_id'] ?? 0) : $_POST['id_periodo'],
            ];

            // Validación de duplicados
            if ($this->model->existeDni($data['dni'], $data['id'])) {
                $errores[] = "Ya existe un estudiante registrado con este DNI.";
            }
            if (!$isNuevo && $this->model->existeEstudianteEnPlan(
                $data['id'],
                $data['id_plan_estudio'],
                $_POST['id_acad_est_prog'] ?? $estudiante['id_acad_est_prog'] ?? null
            )) {
                $errores[] = "Este estudiante ya está registrado en este plan de estudios y periodo.";
            }


            if (!empty($errores)) {
                $programas = $this->objPrograma->getAllBySede($id_sede);
                $planes = $this->objPlan->getPlanesByPrograma($data['id_programa_estudios']);
                $periodos = $this->objPeriodoAcademico->getPeriodos();
                $sedes = $this->objSede->getSedes();
                $estudiante = $data;
                $estudiante['ApellidoPaterno'] = $_POST['ApellidoPaterno'];
                $estudiante['ApellidoMaterno'] = $_POST['ApellidoMaterno'];
                $estudiante['Nombres'] = $_POST['Nombres'];
                $vars = [
                    'errores' => $errores,
                    'programas' => $programas,
                    'planes' => $planes,
                    'periodos' => $periodos,
                    'sedes' => $sedes,
                    'estudiante' => $estudiante,
                    'periodos' => $periodos,
                    'sedes' => $sedes,
                    'module' => 'academico',
                    'pageTitle' => 'Nuevo Estudiante',
                    'periodo_vigente' => $periodo_vigente,
                ];
                if ($isNuevo) {
                    $this->view('academico/estudiantes/nuevo', $vars);
                } else {
                    $this->view('academico/estudiantes/editar', $vars);
                }
                return;
            }

            // Guardar normal si todo está OK
            $id_estudiante =  $this->model->guardar($data);
            if ($id_estudiante > 0) {
                $this->registrar_permiso_inicial($id_estudiante);
            }
            $_SESSION['flash_success'] .= "Estudiante guardado correctamente.";
            $parts = explode('_', $data['apellidos_nombres']);
            if (count($parts) >= 3) {
                $lastname = $parts[0] . ' ' . $parts[1];
                $firstname = $parts[2];
            } else {
                $lastname = $parts[0];
                $firstname = isset($parts[1]) ? $parts[1] : '-';
            }
            if ($isNuevo) {
                $passwordPlano = $password;
            } else {
                $passwordPlano = null;
            }
            // =======================================================
            // INICIO INTEGRACIÓN 
            // =======================================================
            if (INTEGRACIONES_SYNC_ACTIVE) {
                try {
                    $programa_est =  $this->objPrograma->find($data['id_programa_estudios']);
                    $nombre_programa = $programa_est['nombre'];
                    $rol = $this->objRol->find($data['id_rol']);
                    $tipo_usuario = $rol['nombre'];

                    //peticion curl a API
                    $usuario = [
                        'id' => $id_estudiante,
                        'dni' => $data['dni'],
                        'nombres' => $firstname,
                        'apellidos' => $lastname,
                        'passwordPlano' => $passwordPlano,
                        'programa_estudios' => $nombre_programa,
                        'tipo_usuario' => $tipo_usuario,
                        'estado' => 1
                    ];
                    $response = $this->objIntegrator->sincronizarUsuarios($usuario);
                    if ($response['data']['moodle']['message_success']) {
                        //actualizar usuarioen sigi
                        $this->objDocente->updateUserMoodleId($id_estudiante, $response['data']['moodle']['id']);
                        $_SESSION['flash_success'] .= $response['data']['moodle']['message_success'];
                    } else {
                        $_SESSION['flash_error'] .= $response['data']['moodle']['message_error'];
                    }
                    if ($response['data']['microsoft']['success']) {
                        //actualizar usuario en sigi
                        $this->objDocente->updateUserMicrosoftId($id_estudiante, $response['data']['microsoft']['id_microsoft']);
                        $_SESSION['flash_success'] .= '<br>  Usuario actualizado en Microsoft 365';
                        /*if ($response['data']['microsoft']['license']['success']) {
                            $_SESSION['flash_success'] .= '<br>  Licencia asignada en Microsoft 365';
                        } else {
                            $_SESSION['flash_error'] .= '<br>  Error al asignar licencia en Microsoft 365';
                        }*/
                    } else {
                        $_SESSION['flash_error'] .= '<br>  Error al actualizar usuario en Microsoft 365';
                    }
                } catch (\Exception $e) {
                    error_log("Error Update Integraciones Docente: " . $e->getMessage());
                }
            }
        // =======================================================
        // FIN INTEGRACIÓN 
        // =======================================================

        endif;
        header('Location: ' . BASE_URL . '/academico/estudiantes');
        exit;
    }

    protected function registrar_permiso_inicial($id_estudiante)
    {
        $idRolEstudiante = 7; // 'ESTUDIANTE'
        //registrar los permisoselegidos por sistema para nuevos docentes
        $ds = $this->objDatosSistema->buscar();
        $idsSistemas = $this->objDatosSistema->decodePermisos($ds['permisos_inicial_estudiante'] ?? '');
        $this->objDocente->asignarLote($id_estudiante, $idRolEstudiante, $idsSistemas);
    }


    // ================================= DESCARGA DE PLANTILLA PARA CARGA MASIVA ================================================
    public function descargarPlantillaCargaMasiva()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        // datos necesarios
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $planes_estudio = $this->objPlan->getPlanes();
        // 1. Crea el spreadsheet y las hojas
        $spreadsheet = new Spreadsheet();
        $mainSheet = $spreadsheet->getActiveSheet();
        $mainSheet->setTitle('Estudiantes');

        // 2. Escribe encabezados
        $mainSheet->fromArray([
            'DNI',
            'Apellido Paterno',
            'Apellido Materno',
            'Nombres',
            'Género',
            'Fecha Nac.',
            'Dirección',
            'Correo',
            'Teléfono',
            'Discapacidad',
            'Programa de Estudios',
            'Plan de Estudios'
        ], null, 'A1');
        $arr_pe = [['ID', 'Nombre']];
        foreach ($programas as $pe) {
            $ar_pe = [$pe['id'], $pe['nombre']];
            array_push($arr_pe, $ar_pe);
        };

        $arr_planes = [['Nombre']];
        foreach ($planes_estudio as $plan) {
            $ar_plan = [$plan['nombre']];
            array_push($arr_planes, $ar_plan);
        }


        //var_dump($arr_pe);
        // 3. Agrega hojas de referencia
        $programasSheet = $spreadsheet->createSheet();
        $programasSheet->setTitle('Programas');
        $programasSheet->fromArray($arr_pe);
        $programasSheet->getProtection()->setSheet(true);
        $programasSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $planSheet = $spreadsheet->createSheet();
        $planSheet->setTitle('Planes');
        $planSheet->fromArray($arr_planes);
        $planSheet->getProtection()->setSheet(true);
        $planSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // 4. Listas para género y discapacidad directo en arrays
        $mainSheet->setCellValue('E2', ''); // Género
        $mainSheet->setCellValue('J2', ''); // Discapacidad

        // 5. Agrega validación de datos (listas desplegables) en columnas seleccionadas
        for ($row = 2; $row <= 41; $row++) { // 100 filas para ejemplo
            // Género
            $validation = $mainSheet->getCell("E$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setFormula1('"M,F"');

            // Discapacidad
            $validation = $mainSheet->getCell("J$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setFormula1('"SI,NO"');

            // Programa Estudio
            $validation = $mainSheet->getCell("K$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setFormula1("=Programas!B2:B100");

            // Planes de estudio
            $validation = $mainSheet->getCell("L$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setFormula1("=Planes!A2:A1000");
        }
        $nombre_plantilla = "PlantillaCargaMasivaEstudiantes" . date("Ymd-H:i:s");
        // 6. Descarga el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_plantilla . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // =========================================== CARGA MASIVA DE ESTUDIANTES ===================================

    public function CargaMasivaEstudiantes()
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        // Detectar si viene por AJAX (tu caso con XHR)
        $esAjax = (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        if ($periodo_vigente) {
            if (\Core\Auth::esAdminAcademico()):
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {

                    $archivoTmp = $_FILES['archivo_excel']['tmp_name'];
                    $extension  = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));

                    if (!in_array($extension, ['xlsx', 'xls'])) {
                        if ($esAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => 'Solo se permite archivos Excel (.xlsx, .xls)']);
                            exit;
                        }
                        $_SESSION['flash_error'] = "Solo se permite archivos Excel (.xlsx, .xls)";
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    $spreadsheet = IOFactory::load($archivoTmp);
                    $sheet = $spreadsheet->getSheetByName('Estudiantes');
                    if (!$sheet) {
                        if ($esAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => "Hoja 'Estudiantes' no encontrada en el archivo."]);
                            exit;
                        }
                        $_SESSION['flash_error'] = "Hoja 'Estudiantes' no encontrada en el archivo.";
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    $rows = $sheet->toArray(null, true, true, true);
                    $errores = [];
                    $datosAInsertar = [];

                    foreach ($rows as $i => $row) {
                        if ($i === 1) continue; // encabezados

                        $dni                 = trim($row['A']);
                        $ApellidoPaterno     = trim($row['B']);
                        $ApellidoMaterno     = trim($row['C']);
                        $Nombres             = trim($row['D']);
                        $genero              = strtoupper(trim($row['E']));
                        $fecha_nac           = date('Y-m-d', strtotime($row['F']));
                        $direccion           = trim($row['G']);
                        $correo              = trim($row['H']);
                        $telefono            = trim($row['I']);
                        $discapacidad        = strtoupper(trim($row['J']));
                        $programa_estudios   = trim($row['K']);
                        $plan_estudio        = trim($row['L']);

                        // Solo procesar filas "mínimas"
                        if ($dni != '' && $ApellidoPaterno != '' && $ApellidoMaterno != '' && $Nombres != '' && $genero != '') {

                            if (empty($dni) || !preg_match('/^\d{8,12}$/', $dni)) {
                                $errores[] = "Fila $i: DNI inválido.";
                            }
                            if (empty($ApellidoPaterno) || strlen($ApellidoPaterno) < 1) {
                                $errores[] = "Fila $i: Apellido Paterno inválido.";
                            }
                            if (empty($ApellidoMaterno) || strlen($ApellidoMaterno) < 1) {
                                $errores[] = "Fila $i: Apellido Materno inválido.";
                            }
                            if (empty($Nombres) || strlen($Nombres) < 1) {
                                $errores[] = "Fila $i: Nombres inválido.";
                            }
                            if (!in_array($genero, ['M', 'F'])) {
                                $errores[] = "Fila $i: Género debe ser 'M' o 'F'.";
                            }
                            if (empty($fecha_nac) || !strtotime($fecha_nac)) {
                                $errores[] = "Fila $i: Fecha nacimiento inválida.";
                            }
                            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                                $errores[] = "Fila $i: Correo inválido.";
                            }
                            if (!in_array($discapacidad, ['SI', 'NO', ''])) {
                                $errores[] = "Fila $i: Discapacidad debe ser SI o NO.";
                            }
                            if (empty($programa_estudios)) {
                                $errores[] = "Fila $i: ID programa inválido.";
                            }

                            $datosPe = $this->objPrograma->getProgramaPorNombre($programa_estudios);
                            $id_programa_estudios = $datosPe['id'];
                            $id_periodo_registro = $_SESSION['sigi_periodo_actual_id'];
                            $id_sede = $_SESSION['sigi_sede_actual'];
                            $datos_plan = $this->objPlan->getPlanByProgramaAndPlanName($id_programa_estudios, $plan_estudio);
                            $id_plan_estudio = $datos_plan['id'];

                            if (!$id_plan_estudio) $errores[] = "Fila $i: Plan de Estudios inválido.";

                            $id_usuario = $this->model->existeDni($dni);

                            // Si hay errores globales, seguimos juntando pero no insertamos.
                            if (!empty($errores)) continue;

                            $password = \Core\Auth::crearPassword(8);
                            $password_secure = password_hash($password, PASSWORD_DEFAULT);
                            $token    = '';

                            $datosAInsertar[] = [
                                'id'                   => ($id_usuario['id'] > 0) ? $id_usuario['id'] : null,
                                'dni'                  => $dni,
                                'apellidos_nombres'    => $ApellidoPaterno . '_' . $ApellidoMaterno . '_' . $Nombres,
                                'apellido_paterno'     => $ApellidoPaterno,
                                'apellido_materno'     => $ApellidoMaterno,
                                'nombres'              => $Nombres,
                                'genero'               => $genero,
                                'fecha_nacimiento'     => $fecha_nac,
                                'direccion'            => $direccion,
                                'correo'               => $correo,
                                'telefono'             => $telefono,
                                'id_periodo'           => $id_periodo_registro,
                                'id_programa_estudios' => $id_programa_estudios,
                                'nombre_programa'      => $programa_estudios,
                                'discapacidad'         => $discapacidad,
                                'id_rol'               => 7,
                                'id_sede'              => $id_sede,
                                'estado'               => 1,
                                'password_plano'       => $password,
                                'password'             => $password_secure,
                                'reset_password'       => 0,
                                'token_password'       => $token,
                                'id_plan_estudio'      => $id_plan_estudio
                            ];
                        }
                    }

                    if ($errores) {
                        $msg = implode('<br>', $errores);
                        if ($esAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => $msg]);
                            exit;
                        }
                        $_SESSION['flash_error'] = $msg;
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    // Insertar estudiantes
                    $contador_insertados = 0;
                    $UsersForIntegrations = [];

                    foreach ($datosAInsertar as $estudiante) {
                        $id_estudiante = $this->model->guardar($estudiante);
                        if ($id_estudiante > 0) {
                            $this->registrar_permiso_inicial($id_estudiante);

                            $UsersForIntegrations[] = [
                                'id'                   => $id_estudiante,
                                'dni'                  => $estudiante['dni'],
                                'nombres'              => $estudiante['nombres'],
                                'apellidos'            => $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno'],
                                'passwordPlano'        => $estudiante['password_plano'],
                                'programa_estudios'    => $estudiante['nombre_programa'],
                                'tipo_usuario'         => 'ESTUDIANTE',
                                'estado'               => 1
                            ];
                            $contador_insertados++;
                        }
                    }

                    $_SESSION['flash_success'] = "Importación local completada correctamente. Se insertaron $contador_insertados estudiantes.";

                    $resultadoApi = $this->objIntegrator->sincronizarUsuariosMasivos($UsersForIntegrations);

                    $usersForExcel = [];
                    if ($resultadoApi['success']) {
                        $procesados_moodle = 0;
                        $procesados_microsoft = 0;
                        $errores_moodle = [];
                        $errores_microsoft = 0;
                        foreach ($resultadoApi['detalles_api'] as $lote) {
                            if ($lote['moodle_ok']) {
                                if ($lote['moodle']['ok']) {
                                    $procesados_moodle += $lote['moodle']['moodle_procesados'] ?? 0;
                                    $datosProcesados_moodle = $lote['moodle']['data'] ?? [];
                                    $errores_moodle += $lote['moodle']['errores_moodle_detalle'] ?? [];
                                    foreach ($datosProcesados_moodle as $value) {
                                        $this->objDocente->updateUserMoodleId($value['id_sigi'], $value['moodle_id']);
                                    }
                                } else {
                                    $_SESSION['flash_error'] .= "<br>Error al sincronizar con Moodle.";
                                }
                            }

                            if ($lote['microsoft_ok']) {
                                if ($lote['microsoft']['success']) {
                                    $reporte = $lote['microsoft']['reporte'];
                                    foreach ($reporte as $item) {
                                        if ($item['status']) {
                                            $this->objDocente->updateUserMicrosoftId($item['id_sigi'], $item['id_microsoft']);
                                            $usersForExcel[(int)$item['id_sigi']] = $item['correo'];
                                            $procesados_microsoft++;
                                        } else {
                                            $errores_microsoft++;
                                        }
                                    }
                                } else {
                                    $_SESSION['flash_error'] .= "<br>Error al sincronizar con Microsoft.";
                                }
                            }
                        }
                        if ($procesados_moodle > 0) {
                            $_SESSION['flash_success'] .= "<br>Moodle: $procesados_moodle procesados.";
                        }
                        if ($procesados_microsoft > 0) {
                            $_SESSION['flash_success'] .= "<br>Microsoft: $procesados_microsoft procesados.";
                        }
                        if (!empty($errores_moodle)) {
                            $_SESSION['flash_error'] .= "<br>Errores Moodle: " . implode('<br>', array_slice($errores_moodle, 0, 5));
                        }
                        if ($errores_microsoft > 0) {
                            $_SESSION['flash_error'] .= "<br>Errores Microsoft: $errores_microsoft errores.";
                        }
                        // Agregar correo a la lista (evitar notice con ?? '')
                        foreach ($UsersForIntegrations as &$u) {
                            $id = (int)($u['id'] ?? 0);
                            $u['correo'] = $usersForExcel[$id] ?? '';
                        }
                        unset($u);
                    } else {
                        $errores = $resultadoApi['errores'];
                        $_SESSION['flash_error'] .= "<br>Error al sincronizar con Usuarios. " . implode('<br>', array_slice($errores, 0, 5));
                    }
                    // Generar Excel (IMPORTANTE: si OK, esto termina la respuesta con el archivo)
                    $generar_excel = $this->exportarExcelUsuariosSincronizados($UsersForIntegrations);

                    if ($generar_excel) {
                        $_SESSION['flash_success'] .= "<br>Excel generado correctamente.";
                        exit; // no seguir renderizando nada
                    } else {
                        if ($esAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => 'Error al generar excel.']);
                            exit;
                        }
                        $_SESSION['flash_error'] .= "<br>Error al generar excel.";
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }
                }
            endif;
        }

        // Si no entra al POST o no tiene permisos, puedes redirigir:
        header('Location: ' . BASE_URL . '/academico/estudiantes');
        exit;
    }


    public function exportarExcelUsuariosSincronizados(array $usuarios): bool
    {
        if (headers_sent()) {
            return false;
        }

        try {
            $spreadsheet = new Spreadsheet();
            $mainSheet = $spreadsheet->getActiveSheet();
            $mainSheet->setTitle('Estudiantes');

            $mainSheet->fromArray([
                'DNI',
                'Apellidos',
                'Nombres',
                'Correo Institucional',
                'Programa de Estudios',
                'Usuario Moodle',
                'Usuario Microsoft',
                'Usuario Sigi',
                'Contraseña generada'
            ], null, 'A1');

            $row = 2;
            foreach ($usuarios as $usuario) {
                $mainSheet->fromArray([
                    $usuario['dni'] ?? '',
                    $usuario['apellidos'] ?? '',
                    $usuario['nombres'] ?? '',
                    $usuario['correo'] ?? '',
                    $usuario['programa_estudios'] ?? '',
                    $usuario['dni'] ?? '',
                    $usuario['correo'] ?? '',
                    $usuario['dni'] ?? '',
                    $usuario['passwordPlano'] ?? ''
                ], null, 'A' . $row);
                $row++;
            }

            $nombre_plantilla = "UsuariosSincronizados_" . date("Ymd_His");

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombre_plantilla . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            return true;
        } catch (\Throwable $e) {
            error_log("Excel export error: " . $e->getMessage());
            return false;
        }
    }
}
