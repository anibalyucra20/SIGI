<?php

namespace App\Controllers\Sigi;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCompetencia.php';

use App\Models\Sigi\IndicadorLogroCompetencia;
use App\Models\Sigi\Competencias;

class IndicadorLogroCompetenciaController extends Controller
{
    protected $model;
    protected $objCompetencia;

    public function __construct()
    {
        parent::__construct();
        $this->model = new IndicadorLogroCompetencia();
        $this->objCompetencia = new Competencias();
    }

    public function index($id_competencia)
    {
        if (\Core\Auth::esAdminSigi()):
            // Busca nombre de competencia para mostrar en el título
            $comp = $this->objCompetencia->find($id_competencia);
        endif;
        $this->view('sigi/indicadorLogroCompetencia/index', [
            'id_competencia' => $id_competencia,
            'competencia'    => $comp,
            'module'         => 'sigi',
            'pageTitle'      => 'Indicadores de Logro de Competencia'
        ]);
        exit;
    }

    public function data($id_competencia)
    {
        if (\Core\Auth::esAdminSigi()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $result = $this->model->getPaginated($id_competencia, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }

    public function nuevo($id_competencia)
    {
        $this->view('sigi/indicadorLogroCompetencia/nuevo', [
            'id_competencia' => $id_competencia,
            'indicador'      => [],
            'module'         => 'sigi',
            'pageTitle'      => 'Nuevo Indicador de Logro'
        ]);
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $data = [
                'id'            => $_POST['id'] ?? null,
                'id_competencia' => $_POST['id_competencia'],
                'correlativo'   => trim($_POST['correlativo']),
                'descripcion'   => trim($_POST['descripcion']),
            ];
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Indicador guardado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/indicadorLogroCompetencia/index/' . $data['id_competencia']);
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $indicador = $this->model->find($id);
        endif;
        $this->view('sigi/indicadorLogroCompetencia/editar', [
            'indicador'      => $indicador,
            'id_competencia' => $indicador['id_competencia'],
            'module'         => 'sigi',
            'pageTitle'      => 'Editar Indicador de Logro'
        ]);
        exit;
    }

    public function eliminar($id, $id_competencia)
    {
        if (\Core\Auth::esAdminSigi()):
            $this->model->eliminar($id);
            $_SESSION['flash_success'] = "Indicador eliminado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/indicadorLogroCompetencia/index/' . $id_competencia);

        exit;
    }
}
