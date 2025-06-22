<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/SistemasIntegrados.php';

use App\Models\Sigi\SistemasIntegrados;

class SistemasIntegradosController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SistemasIntegrados();
    }

    public function index()
    {
        $this->view('sigi/sistemasIntegrados/index', [
            'module'    => 'sigi',
            'pageTitle' => 'Sistemas Integrados'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 0;
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
}
