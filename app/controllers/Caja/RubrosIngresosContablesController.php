<?php

namespace App\Controllers\Caja;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Caja/RubrosIngresosContables.php';

use App\Models\Caja\RubrosIngresosContables;

class RubrosIngresosContablesController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RubrosIngresosContables();
    }

    public function index()
    {
        if (\Core\Auth::esAdminCaja()):
        endif;
        // Listas iniciales para el filtro principal
        $this->view('caja/rubrosIngresosContables/index', [
            'module'    => 'caja',
            'pageTitle' => 'Rubro de Ingresos Contables'
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
                'descripcion' => $_GET['descripcion'] ?? null,
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
        $this->view('caja/rubrosIngresosContables/nuevo', [
            //'programas' => $programas,
            'data' => [],
            'module' => 'caja',
            'pageTitle' => 'Nuevo Rubro de Ingresos Contables'
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
                'descripcion'           => trim($_POST['descripcion']),
                'clasificador'          => trim($_POST['clasificador']),
                'estado'                => ($_POST['estado']),
            ];
            $id = $_POST['id'] ?? null;

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = $this->model->validar($data, $editar, $id);
            if ($errores) {
                $this->view('caja/rubrosIngresosContables/' . $link, [
                    'errores'   => $errores,
                    'data'   => $data,
                    'module'    => 'caja',
                    'pageTitle' => $title . ' Rubro de Ingresos Contables',
                    'isEdit'    => $editar
                ]);
                return;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Rubro de Ingresos Contable guardado correctamente";
        endif;
        header('Location: ' . BASE_URL . '/caja/rubrosIngresosContables');
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminCaja()):
            $data = $this->model->find($id);
        endif;
        $this->view('caja/rubrosIngresosContables/editar', [
            'data' => $data,
            'isEdit'    => true,
            'module' => 'caja',
            'pageTitle' => 'Editar Rubro de Ingresos Contables'
        ]);
        exit;
    }
}
