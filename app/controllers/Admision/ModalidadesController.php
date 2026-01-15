<?php

namespace App\Controllers\Admision;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Admision/Modalidades.php';
require_once __DIR__ . '/../../../app/models/Admision/TiposModalidades.php';

use App\Models\Admision\Modalidades;
use App\Models\Admision\TiposModalidades;

class ModalidadesController extends Controller
{
    protected $model;
    protected $objTiposModalidades;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Modalidades();
        $this->objTiposModalidades = new TiposModalidades();
    }

    public function index()
    {
        if (\Core\Auth::esAdminAdmision()):
        endif;
        $this->view('admision/modalidades/index', [
            'module'    => 'admision',
            'pageTitle' => 'Modalidades'
        ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminAdmision()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [];

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

    public function nuevo()
    {
        if (\Core\Auth::esAdminAdmision()):
            $tiposModalidad = $this->objTiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/modalidades/nuevo', [
            'tiposModalidad' => $tiposModalidad,
            'module'    => 'admision',
            'pageTitle' => 'Nueva Modalidad'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminAdmision()):
            $data = [
                'id' => $_POST['id'] ?? null,
                'id_tipo_modalidad' => $_POST['id_tipo_modalidad'],
                'nombre'      => trim($_POST['nombre']),
            ];
            $this->model->guardar($data);

            $_SESSION['flash_success'] = "Modalidad guardada correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/admision/modalidades');
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminAdmision()):
            $modalidad = $this->model->find($id);
            $tiposModalidad = $this->objTiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/modalidades/editar', [
            'modalidad'      => $modalidad,
            'tiposModalidad' => $tiposModalidad,
            'module'    => 'admision',
            'pageTitle' => 'Editar Modalidad'
        ]);
        exit;
    }
    public function porTipoModalidad($id_tipo_modalidad)
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            echo json_encode($this->model->getModalidadesByTipoModalidad($id_tipo_modalidad), JSON_UNESCAPED_UNICODE);
            exit;
        endif;
        exit;
    }
}
