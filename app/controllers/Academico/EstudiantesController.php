<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Estudiantes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use App\Models\Academico\Estudiantes;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;
use App\Models\Sigi\PeriodoAcademico;

class EstudiantesController extends Controller
{
    protected $model;
    protected $objSede;
    protected $objPrograma;
    protected $objPlan;
    protected $objPeriodoAcademico;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Estudiantes();
        $this->objSede = new Sedes();
        $this->objPrograma = new Programa();
        $this->objPlan = new Plan();
        $this->objPeriodoAcademico = new PeriodoAcademico();
    }

    public function index()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $this->view('academico/estudiantes/index', [
            'programas' => $programas,
            'module' => 'academico',
            'pageTitle' => 'Estudiantes'
        ]);
    }

    public function data()
    {
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
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $planes = [];
        $errores = $errores ?? [];

        $this->view('academico/estudiantes/nuevo', [
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
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $errores = [];
        $isNuevo = empty($_POST['id']);
        $data = [
            'id'                  => $_POST['id'] ?? null,
            'dni'                 => trim($_POST['dni']),
            'apellidos_nombres'   => trim($_POST['apellidos_nombres']),
            'genero'              => $_POST['genero'],
            'fecha_nacimiento'    => $_POST['fecha_nacimiento'],
            'direccion'           => trim($_POST['direccion']),
            'correo'              => trim($_POST['correo']),
            'telefono'            => trim($_POST['telefono']),
            'discapacidad'        => $_POST['discapacidad'],
            'id_programa_estudios' => $_POST['id_programa_estudios'],
            'id_plan_estudio'     => $_POST['id_plan_estudio'],
            'estado'              => $_POST['estado'] ?? 1,
            // Sede y periodo
            'id_sede'             => $isNuevo ? ($_SESSION['sigi_sede_actual'] ?? 0) : $_POST['id_sede'],
            'id_periodo'          => $isNuevo ? ($_SESSION['sigi_periodo_actual_id'] ?? 0) : $_POST['id_periodo'],
        ];

        // Validación de duplicados
        if ($this->model->existeDni($data['dni'], $data['id'])) {
            $errores[] = "Ya existe un estudiante registrado con este DNI.";
        }
        if (!$isNuevo && $this->model->existeEstudianteEnPlanPeriodo(
            $data['id'],
            $data['id_plan_estudio'],
            $data['id_periodo'],
            $_POST['id_acad_est_prog'] ?? $estudiante['id_acad_est_prog'] ?? null
        )) {
            $errores[] = "Este estudiante ya está registrado en este plan de estudios y periodo.";
        }


        if (!empty($errores)) {
            $programas = $this->objPrograma->getAllBySede($id_sede);
            $planes = $this->objPlan->getPlanesByPrograma($data['id_programa_estudios']);
            $periodos = $this->objPeriodoAcademico->getPeriodos();
            $sedes = $this->objSede->getSedes();
            $vars = [
                'errores' => $errores,
                'programas' => $programas,
                'planes' => $planes,
            ] + $data;
            if ($isNuevo) {
                $this->view('academico/estudiantes/nuevo', $vars);
            } else {
                $vars['periodos'] = $periodos;
                $vars['sedes'] = $sedes;
                $this->view('academico/estudiantes/editar', $vars);
            }
            return;
        }

        // Guardar normal si todo está OK
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Estudiante guardado correctamente.";
        header('Location: ' . BASE_URL . '/academico/estudiantes');
        exit;
    }
}
