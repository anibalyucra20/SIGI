<?php

namespace App\Controllers\Sigi;
use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
use App\Models\Sigi\Programa;

class ProgramasController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Programa();
    }

    public function index()
    {
        $this->view('sigi/programas/index', [
            'module' => 'sigi',
            'pageTitle' => 'Programas de Estudio'
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
        $this->view('sigi/programas/nuevo', [
            'module'    => 'sigi',
            'pageTitle' => 'Nuevo Programa'
        ]);
    }

    public function guardar()
    {
        $data = [
            'id'             => $_POST['id'] ?? null,
            'codigo'         => trim($_POST['codigo']),
            'tipo'           => trim($_POST['tipo']),
            'nombre'         => trim($_POST['nombre']),
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Programa guardado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/programas');
        exit;
    }

    public function editar($id)
    {
        $programa = $this->model->find($id);
        $this->view('sigi/programas/editar', [
            'programa'   => $programa,
            'module'     => 'sigi',
            'pageTitle'  => 'Editar Programa'
        ]);
    }
    
}
