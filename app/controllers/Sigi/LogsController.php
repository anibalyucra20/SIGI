<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Logs.php';

use App\Models\Sigi\Logs;

class LogsController extends Controller
{
    protected $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = new Logs();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $usuarios = $this->model->getUsuarios();
            $acciones = $this->model->getAcciones();
            $tablas   = $this->model->getTablas();
        endif;
        $this->view('sigi/logs/index', [
            'usuarios' => $usuarios,
            'acciones' => $acciones,
            'tablas'   => $tablas,
            'module'   => 'sigi',
            'pageTitle' => 'Auditoría del Sistema'
        ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminSigi()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 0;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'desc';

            $filters = [
                'usuario'   => $_GET['filter_usuario'] ?? '',
                'accion'    => $_GET['filter_accion']  ?? '',
                'tabla'     => $_GET['filter_tabla']   ?? '',
                'fecha_ini' => $_GET['filter_fecha_ini'] ?? '',
                'fecha_fin' => $_GET['filter_fecha_fin'] ?? '',
                'search'    => $_GET['search']['value'] ?? '',
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }
}
