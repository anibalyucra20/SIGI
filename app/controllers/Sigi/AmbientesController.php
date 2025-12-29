<?php

namespace App\Controllers\Sigi;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Sigi/Ambientes.php';

use App\Models\Sigi\Ambientes;

class AmbientesController extends Controller
{
    protected $model;
    protected $tipos_ambiente = ['AUDITORIO', 'AULA', 'AULA VIRTUAL', 'BIBLIOTECA', 'LABORATORIO', 'OFICINA BIENESTAR SOCIAL', 'OFICINA ADMINISTRATIVAS', 'OTROS', 'TALLER', 'TOPICO'];

    public function __construct()
    {
        parent::__construct();
        $this->model = new Ambientes();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
        // Listas iniciales para el filtro principal
        endif;
        $this->view('sigi/ambientes/index', [
            'module'    => 'sigi',
            'pageTitle' => 'Ambientes'
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
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'nro'       => $_GET['filter-nro'] ?? null,
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
        if (\Core\Auth::esAdminSigi()):
        endif;
        $this->view('sigi/ambientes/nuevo', [
            'tipos_ambientes' => $this->tipos_ambiente,
            'ambientes' => [],
            'module' => 'sigi',
            'pageTitle' => 'Nuevo Ambiente'
        ]);
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $ambiente = $this->model->find($id);
        endif;
        $this->view('sigi/ambientes/editar', [
            'tipos_ambientes' => $this->tipos_ambiente,
            'isEdit' => true,
            'ambiente' => $ambiente,
            'module' => 'sigi',
            'pageTitle' => 'Editar Ambiente'
        ]);
        exit;
    }

    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $data = [
                'id'             => $_POST['id'] ?? null,
                'tipo_ambiente'  => $_POST['tipo_ambiente'],
                'nro'            => $_POST['nro'],
                'aforo'          => $_POST['aforo'],
                'piso'           => $_POST['piso'],
                'ubicacion'      => $_POST['ubicacion'],
                'observacion'    => $_POST['observacion'],
                'estado'         => $_POST['estado'] ?? '1',
            ];
            $data['id_sede'] = $_SESSION['sigi_sede_actual'];

            $id_ambiente = $this->model->guardar($data);

            $_SESSION['flash_success'] = "Ambiente guardado correctamente.";
        endif;
        // Redirige directamente a los indicadores de logro de capacidad
        header('Location: ' . BASE_URL . '/sigi/ambientes');
        exit;
    }
}
