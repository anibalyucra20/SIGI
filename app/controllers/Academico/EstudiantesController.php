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
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\Academico\Estudiantes;
use App\Models\Sigi\Docente;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\DatosSistema;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

use PhpOffice\PhpSpreadsheet\IOFactory;


class EstudiantesController extends Controller
{
    protected $model;
    protected $objDocente;
    protected $objSede;
    protected $objPrograma;
    protected $objPlan;
    protected $objPeriodoAcademico;
    protected $objDatosSistema;

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
        if ($periodo_vigente) {
            if (\Core\Auth::esAdminAcademico()):
                $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
                $errores = [];
                $isNuevo = empty($_POST['id']);

                $password = bin2hex(random_bytes(5));
                $password_secure = password_hash($password, PASSWORD_DEFAULT);
                $data = [
                    'id'                  => $_POST['id'] ?? null,
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
                $_SESSION['flash_success'] = "Estudiante guardado correctamente.";
            endif;
        }
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
        if ($periodo_vigente) {
            if (\Core\Auth::esAdminAcademico()):
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
                    $archivoTmp = $_FILES['archivo_excel']['tmp_name'];
                    $extension  = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
                    if (!in_array($extension, ['xlsx', 'xls'])) {
                        $_SESSION['flash_error'] = "Solo se permite archivos Excel (.xlsx, .xls)";
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    $spreadsheet = IOFactory::load($archivoTmp);
                    $sheet = $spreadsheet->getSheetByName('Estudiantes');
                    if (!$sheet) {
                        $_SESSION['flash_error'] = "Hoja 'Estudiantes' no encontrada en el archivo.";
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    $rows = $sheet->toArray(null, true, true, true); // Array asociativo por columnas: A, B, C...
                    $errores = [];
                    $datosAInsertar = [];
                    foreach ($rows as $i => $row) {
                        if ($i === 1) continue; // Saltar encabezados
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

                        $datos_validos = 0;
                        if ($dni != '' && $ApellidoPaterno != '' && $ApellidoMaterno != '' && $Nombres != '' && $genero != '') {
                            $datos_validos++;

                            // Validaciones
                            if (empty($dni) || !preg_match('/^\d{8,12}$/', $dni)) {
                                $errores[] = "Fila $i: DNI inválido.";
                            }
                            if (empty($ApellidoPaterno) || strlen($ApellidoPaterno) < 2) {
                                $errores[] = "Fila $i: Apellido Paterno inválido.";
                            }
                            if (empty($ApellidoMaterno) || strlen($ApellidoMaterno) < 2) {
                                $errores[] = "Fila $i: Apellido Materno inválido.";
                            }
                            if (empty($Nombres) || strlen($Nombres) < 2) {
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

                            // Si hay errores, saltar inserción
                            if (!empty($errores)) continue;

                            // Password aleatorio y token
                            $password = bin2hex(random_bytes(5));
                            $password_secure = password_hash($password, PASSWORD_DEFAULT);
                            $token    = '';

                            $datosAInsertar[] = [
                                'id'                   => ($id_usuario['id'] > 0) ? $id_usuario['id'] : null,
                                'dni'                  => $dni,
                                'apellidos_nombres'    => $ApellidoPaterno . '_' . $ApellidoMaterno . '_' . $Nombres,
                                'genero'               => $genero,
                                'fecha_nacimiento'     => $fecha_nac,
                                'direccion'            => $direccion,
                                'correo'               => $correo,
                                'telefono'             => $telefono,
                                'id_periodo'           => $id_periodo_registro,
                                'id_programa_estudios' => $id_programa_estudios,
                                'discapacidad'         => $discapacidad,
                                'id_rol'               => 7,
                                'id_sede'              => $id_sede,
                                'estado'               => 1,
                                'password'             => $password,
                                'passwords'             => $password_secure,
                                'reset_password'       => 0,
                                'token_password'       => $token,
                                'id_plan_estudio'      => $id_plan_estudio
                            ];
                        }
                    }
                    // Mostrar errores (puedes mostrar en vista, aquí solo ejemplo)
                    if ($errores) {
                        $_SESSION['flash_error'] = implode('<br>', $errores);
                        header('Location: ' . BASE_URL . '/academico/estudiantes');
                        exit;
                    }

                    // Insertar estudiantes
                    foreach ($datosAInsertar as $estudiante) {
                        $id_estudiante = $this->model->guardar($estudiante);
                        if ($id_estudiante > 0) {
                            $this->registrar_permiso_inicial($id_estudiante);
                        }
                    }
                    $_SESSION['flash_error'] = implode('<br>', $errores);
                    $_SESSION['flash_success'] = "Importación completada correctamente.";
                    header('Location: ' . BASE_URL . '/academico/estudiantes');
                    exit;
                }
            endif;
        }
        exit;
    }
}
