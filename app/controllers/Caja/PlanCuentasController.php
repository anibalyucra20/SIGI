<?php

namespace App\Controllers\Caja;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Caja/PlanCuentas.php';

use App\Models\Caja\PlanCuentas;

class PlanCuentasController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PlanCuentas();
    }

    public function index()
    {
        if (\Core\Auth::esAdminCaja()):
        endif;
        // Listas iniciales para el filtro principal
        $this->view('caja/planCuentas/index', [
            'module'    => 'caja',
            'pageTitle' => 'Planes de Cuentas'
        ]);

        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminCaja()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'codigo' => $_GET['codigo'] ?? null,
                'nombre' => $_GET['nombre'] ?? null
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        endif;
        exit;
    }

    // --- FORMULARIO NUEVO
    public function nuevo()
    {
        if (\Core\Auth::esAdminCaja()):
        endif;
        $this->view('caja/planCuentas/nuevo', [
            //'programas' => $programas,
            'data' => [],
            'module' => 'caja',
            'pageTitle' => 'Nuevo Plan de Cuenta'
        ]);

        exit;
    }



    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        if (\Core\Auth::esAdminCaja()):

            $data = [
                'id'                    => $_POST['id'] ?? null,
                'codigo'                => trim($_POST['codigo']),
                'nombre'                => trim($_POST['nombre']),
                'estado'                => trim($_POST['estado'])
            ];
            $id = $_POST['id'] ?? null;

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = $this->model->validar($data, $editar, $id);
            if ($errores) {
                $this->view('caja/planCuentas/' . $link, [
                    'errores'   => $errores,
                    'data'   => $data,
                    'module'    => 'caja',
                    'pageTitle' => $title . ' Plan de Cuenta',
                    'isEdit'    => $editar
                ]);
                return;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Plan de Cuenta guardado correctamente";
        endif;
        header('Location: ' . BASE_URL . '/caja/planCuentas');
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminCaja()):
            $data = $this->model->find($id);
        endif;
        $this->view('caja/planCuentas/editar', [
            'data' => $data,
            'isEdit'    => true,
            'module' => 'caja',
            'pageTitle' => 'Editar Plan de Cuenta'
        ]);
        exit;
    }
}
