<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Semestre.php';
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use App\Models\Sigi\Programa;
use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Plan;
use App\Models\Sigi\Semestre;


class SemestreController extends Controller
{
    protected $model;
    protected $objModuloFormativo;
    protected $objPlan;
    protected $objPrograma;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Semestre();
        $this->objModuloFormativo = new ModuloFormativo();
        $this->objPlan = new Plan();
        $this->objPrograma = new Programa();
    }

    public function index()
    {
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/semestre/index', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Semestres'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 1;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $filters = [
            'id_programa_estudios' => $_GET['filter_programa'] ?? null,
            'id_plan_estudio'      => $_GET['filter_plan'] ?? null,
            'id_modulo_formativo'  => $_GET['filter_modulo'] ?? null,
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
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/semestre/nuevo', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Nuevo Semestre'
        ]);
    }

    public function guardar()
    {
        $data = [
            'id' => $_POST['id'] ?? null,
            'descripcion' => trim($_POST['descripcion']),
            'id_modulo_formativo' => $_POST['id_modulo_formativo'],
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Semestre guardado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/semestre');
        exit;
    }

    public function editar($id)
    {
        $semestre = $this->model->find($id);
        // Recuperar cadenas para selects dependientes:
        $modulo = null;
        $plan = null;
        $programa = null;
        $planes = [];
        $modulos = [];

        if (!empty($semestre['id_modulo_formativo'])) {
            $modulo = $this->objModuloFormativo->find($semestre['id_modulo_formativo']);
            if ($modulo) {
                $plan = $this->objPlan->find($modulo['id_plan_estudio']);
                if ($plan) {
                    $programa = $this->objPrograma->find($plan['id_programa_estudios']);
                    $planes = $this->objPlan->getPlanesByPrograma($plan['id_programa_estudios']);
                    $modulos = $this->objModuloFormativo->getModuloByPlan($plan['id']);
                }
            }
        }
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/semestre/editar', [
            'semestre'  => $semestre,
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'id_programa_selected' => $programa['id'] ?? '',
            'id_plan_selected'     => $plan['id'] ?? '',
            'module'    => 'sigi',
            'pageTitle' => 'Editar Semestre'
        ]);
    }
    public function porModulo($idModulo)
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->getSemestresByModulo($idModulo), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
