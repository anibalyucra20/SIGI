<?php

namespace App\Controllers\Caja;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Caja/Proveedores.php';

use App\Models\Caja\Proveedores;

class ProveedoresController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Proveedores();
    }

    public function index()
    {
        if (\Core\Auth::esAdminCaja()):
        endif;
        // Listas iniciales para el filtro principal
        $this->view('caja/proveedores/index', [
            'module'    => 'caja',
            'pageTitle' => 'proveedores'
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
                'ruc' => $_GET['ruc'] ?? null,
                'razon_social' => $_GET['razon_social'] ?? null,
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
        $this->view('caja/proveedores/nuevo', [
            //'programas' => $programas,
            'data' => [],
            'module' => 'caja',
            'pageTitle' => 'Nuevo Medio de Pago'
        ]);

        exit;
    }



    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        if (\Core\Auth::esAdminCaja()):

            $data = [
                'id'                  => $_POST['id'] ?? null,
                'ruc'                 => trim($_POST['ruc']),
                'razon_social'        => trim($_POST['razon_social']),
                'direccion'           => trim($_POST['direccion']),
                'telefono'            => trim($_POST['telefono']),
                'correo'              => trim($_POST['correo']),
                'ref_contacto'        => trim($_POST['ref_contacto']),
                'estado'              => ($_POST['estado']),
            ];
            $id = $_POST['id'] ?? null;

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = $this->model->validar($data, $editar, $id);
            if ($errores) {
                $this->view('caja/proveedores/' . $link, [
                    'errores'   => $errores,
                    'data'   => $data,
                    'module'    => 'caja',
                    'pageTitle' => $title . ' Proveedor',
                    'isEdit'    => $editar
                ]);
                return;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Proveedor guardado correctamente";
        endif;
        header('Location: ' . BASE_URL . '/caja/proveedores');
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminCaja()):
            $data = $this->model->find($id);
        endif;
        $this->view('caja/proveedores/editar', [
            'data' => $data,
            'isEdit'    => true,
            'module' => 'caja',
            'pageTitle' => 'Editar Proveedor'
        ]);
        exit;
    }
}
