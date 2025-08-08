<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';

use App\Models\Sigi\Competencias;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;
use App\Models\Sigi\ModuloFormativo;

class CompetenciasController extends Controller
{
    protected $model;
    protected $objPrograma;
    protected $objPlan;
    protected $objModulo;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Competencias();
        $this->objPrograma = new Programa();
        $this->objPlan = new Plan();
        $this->objModulo = new ModuloFormativo();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $programas = $this->objPrograma->getTodosProgramas();
        endif;
        $this->view('sigi/competencias/index', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Competencias'
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
                'id_programa_estudios' => $_GET['filter_programa'] ?? null,
                'id_plan_estudio'      => $_GET['filter_plan'] ?? null,
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

    public function nuevo()
    {
        if (\Core\Auth::esAdminSigi()):
            $programas = $this->objPrograma->getTodosProgramas();
        endif;
        $this->view('sigi/competencias/nuevo', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Nueva Competencia'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $data = [
                'id' => $_POST['id'] ?? null,
                'id_plan_estudio' => $_POST['id_plan_estudio'],
                'tipo'      => trim($_POST['tipo']),
                'codigo'    => trim($_POST['codigo']),
                'descripcion' => trim($_POST['descripcion']),
            ];
            $this->model->guardar($data);

            // Guardar relación módulos formativos si es parte del formulario
            if (isset($_POST['modulos'])) {
                $id_competencia = $_POST['id'] ?? $this->model->getDB()->lastInsertId();
                $this->model->updateModulosCompetencia($id_competencia, $_POST['modulos']);
            }
            $_SESSION['flash_success'] = "Competencia guardada correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/competencias');
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $comp = $this->model->find($id);

            $plan = $this->objPlan->find($comp['id_plan_estudio']);
            $programa = $this->objPrograma->find($plan['id_programa_estudios']);

            $programas = $this->objPrograma->getTodosProgramas();
            $planes = $this->objPlan->getPlanesByPrograma($plan['id_programa_estudios']);

            // Para módulos seleccionados
            $modulosSeleccionados = array_column($this->model->getModulosByCompetencia($id), 'id');
            $modulosAll = $this->objModulo->getModuloByPlan($plan['id']);
        endif;
        $this->view('sigi/competencias/editar', [
            'comp'      => $comp,
            'programas' => $programas,
            'planes'    => $planes,
            'modulosAll' => $modulosAll,
            'modulosSeleccionados' => $modulosSeleccionados,
            'id_programa_selected' => $programa['id'] ?? '',
            'id_plan_selected'     => $plan['id'] ?? '',
            'module'    => 'sigi',
            'pageTitle' => 'Editar Competencia'
        ]);
        exit;
    }
    public function porModulo($id_modulo)
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            echo json_encode($this->model->getCompetenciasByModulo($id_modulo), JSON_UNESCAPED_UNICODE);
            exit;
        endif;
        exit;
    }
}
