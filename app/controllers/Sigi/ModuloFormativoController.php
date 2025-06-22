<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';

use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;

class ModuloFormativoController extends Controller
{
    protected $model;
    protected $objPrograma;
    protected $objPlan;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ModuloFormativo();
        $this->objPrograma = new Programa();
        $this->objPlan = new Plan();
    }

    public function index()
    {

        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/moduloFormativo/index', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Módulos Formativos'
        ]);
    }

    // DataTables AJAX
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
        $this->view('sigi/moduloFormativo/nuevo', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Nuevo Módulo Formativo'
        ]);
    }

    public function guardar()
    {
        $data = [
            'id' => $_POST['id'] ?? null,
            'descripcion' => trim($_POST['descripcion']),
            'nro_modulo' => trim($_POST['nro_modulo']),
            'id_plan_estudio' => $_POST['id_plan_estudio'],
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Módulo formativo guardado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/moduloFormativo');
        exit;
    }

    public function editar($id)
    {
        $modulo = $this->model->find($id);
        $programas = $this->objPrograma->getTodosProgramas();
        // Suponiendo que $modulo tiene el campo 'id_plan_estudio'
        $id_programa_selected = '';
        $planes = [];
        $id_plan_actual = $modulo['id_plan_estudio'];
        if (!empty($modulo['id_plan_estudio'])) {
            // Busca el plan actual
            $planActual = $this->objPlan->find($modulo['id_plan_estudio']);
            if ($planActual) {
                $id_programa_selected = $planActual['id_programa_estudios'];
                $planes = $this->objPlan->getPlanesByPrograma($id_programa_selected);
            }
        }

        $this->view('sigi/moduloFormativo/editar', [
            'modulo'    => $modulo,
            'programas' => $programas,
            'planes'    => $planes,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected' => $id_plan_actual,
            'module'    => 'sigi',
            'pageTitle' => 'Editar Módulo Formativo'
        ]);
    }
    public function porPlan($id_plan)
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->getModuloByPlan($id_plan), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
