<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use App\Models\Sigi\Plan;
use App\Models\Sigi\Programa;

class PlanesController extends Controller
{
    protected $model;
    protected $objPrograma;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Plan();
        $this->objPrograma = new Programa();
    }

    public function index()
    {
        $this->view('sigi/planes/index', [
            'module' => 'sigi',
            'pageTitle' => 'Planes de Estudio'
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

        $result = $this->model->getPaginated($length, $start, $orderCol, $orderDir);

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
        $this->view('sigi/planes/nuevo', [
            'programas' => $programas,
            'module' => 'sigi',
            'pageTitle' => 'Nuevo Plan de Estudio'
        ]);
    }
    public function guardar()
    {
        $data = [
            'id' => $_POST['id'] ?? null,
            'id_programa_estudios' => $_POST['id_programa_estudios'],
            'nombre' => trim($_POST['nombre']),
            'resolucion' => trim($_POST['resolucion']),
            'perfil_egresado' => trim($_POST['perfil_egresado']),
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Plan guardado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/planes');
        exit;
    }
    public function editar($id)
    {
        $plan = $this->model->find($id);
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/planes/editar', [
            'plan' => $plan,
            'programas' => $programas,
            'module' => 'sigi',
            'pageTitle' => 'Editar Plan de Estudio'
        ]);
    }
    public function porPrograma($id_programa)
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->getPlanesByPrograma($id_programa), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
