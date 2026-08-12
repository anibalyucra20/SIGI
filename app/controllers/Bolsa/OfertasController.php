<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

require_once __DIR__ . '/../../models/Bolsa/Ofertas.php';
require_once __DIR__ . '/../../models/Bolsa/Empresa.php';
require_once __DIR__ . '/../../models/Sigi/Programa.php';

use App\Models\Bolsa\Ofertas;
use App\Models\Bolsa\Empresa;
use App\Models\Sigi\Programa;

class OfertasController extends Controller {

    protected $ofertasModel;
    protected $empresaModel;
    protected $programaModel;

    public function __construct() {
        parent::__construct();
        $this->ofertasModel = new Ofertas();
        $this->empresaModel = new Empresa();
        $this->programaModel = new Programa();
    }

    public function index() {
        $this->view('bolsa/ofertas/index', [
            'module'    => 'bolsa',
            'pageTitle' => 'Ofertas',
        ]);
        exit;
    }

    public function data() {
        if (\Core\Auth::esAdminBolsa()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [];

            $result = $this->ofertasModel->ObtenerOfertas($filters, $length, $start, $orderCol, $orderDir);

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

    public function nuevo() {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        
        $empresas = $this->empresaModel->listar();
        $programas = $this->programaModel->getTodosProgramas();

        $this->view('bolsa/ofertas/nuevo', [
            'module'    => 'bolsa',
            'pageTitle' => 'Nueva Oferta',
            'empresas'  => $empresas,
            'programas' => $programas,
            'oferta'    => []
        ]);
        exit;
    }

    public function guardar() {
        if (\Core\Auth::esAdminBolsa()):
            $data = [
                'id'                => $_POST['id'],
                'id_empresa'        => $_POST['id_empresa'],
                'programa_estudio'  => $_POST['programa_estudio'],
                'titulo'            => $_POST['titulo'],
                'detalle'           => $_POST['detalle'],
                'fecha_publicacion' => $_POST['fecha_publicacion'],
                'fecha_cierre'      => $_POST['fecha_cierre'],
                'salario'           => $_POST['salario'],
                'requisitos'        => $_POST['requisitos'],
                'ubicacion'         => $_POST['ubicacion'],
                'tipo_contrato'     => $_POST['tipo_contrato'],
                'foto'              => $_POST['foto'],
                'estado'            => $_POST['estado']
            ];
            $this->ofertasModel->guardar($data);
        endif;
        header('Location: ' . BASE_URL . '/bolsa/ofertas');
        exit;
    }
}