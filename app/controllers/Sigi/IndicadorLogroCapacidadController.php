<?php

namespace App\Controllers\Sigi;

use Core\Controller;
require_once __DIR__ . '/../../../app/models/Sigi/Capacidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';
use App\Models\Sigi\IndicadorLogroCapacidad;
use App\Models\Sigi\Capacidades;

class IndicadorLogroCapacidadController extends Controller
{
    protected $model;
    protected $objCapacidad;

    public function __construct()
    {
        parent::__construct();
        $this->model = new IndicadorLogroCapacidad();
        $this->objCapacidad = new Capacidades();
    }

    public function index($id_capacidad)
    {
        // Busca nombre de la capacidad para el título
        $cap = $this->objCapacidad->find($id_capacidad);

        $this->view('sigi/indicadorLogroCapacidad/index', [
            'id_capacidad' => $id_capacidad,
            'capacidad'    => $cap,
            'module'       => 'sigi',
            'pageTitle'    => 'Indicadores de Logro de Capacidad'
        ]);
    }

    public function data($id_capacidad)
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 1;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $result = $this->model->getPaginated($id_capacidad, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function nuevo($id_capacidad)
    {
        $this->view('sigi/indicadorLogroCapacidad/nuevo', [
            'id_capacidad' => $id_capacidad,
            'indicador'    => [],
            'module'       => 'sigi',
            'pageTitle'    => 'Nuevo Indicador de Logro'
        ]);
    }

    public function guardar()
    {
        $data = [
            'id'           => $_POST['id'] ?? null,
            'id_capacidad' => $_POST['id_capacidad'],
            'codigo'       => trim($_POST['codigo']),
            'descripcion'  => trim($_POST['descripcion']),
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Indicador guardado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/indicadorLogroCapacidad/index/' . $data['id_capacidad']);
        exit;
    }

    public function editar($id)
    {
        $indicador = $this->model->find($id);
        $this->view('sigi/indicadorLogroCapacidad/editar', [
            'indicador'    => $indicador,
            'id_capacidad' => $indicador['id_capacidad'],
            'module'       => 'sigi',
            'pageTitle'    => 'Editar Indicador de Logro'
        ]);
    }

    public function eliminar($id, $id_capacidad)
    {
        $this->model->eliminar($id);
        $_SESSION['flash_success'] = "Indicador eliminado correctamente.";
        header('Location: ' . BASE_URL . '/sigi/indicadorLogroCapacidad/index/' . $id_capacidad);
        exit;
    }
}
