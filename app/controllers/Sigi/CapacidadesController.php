<?php

namespace App\Controllers\Sigi;

use Core\Controller;
require_once __DIR__ . '/../../../app/models/Sigi/Capacidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Semestre.php';
require_once __DIR__ . '/../../../app/models/Sigi/UnidadDidactica.php';
use App\Models\Sigi\Capacidades;
use App\Models\Sigi\Competencias;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Plan;
use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Semestre;
use App\Models\Sigi\UnidadDidactica;

class CapacidadesController extends Controller
{
    protected $model;
    protected $objCompetencia;
    protected $objPrograma;
    protected $objPlan;
    protected $objModulo;
    protected $objSemestre;
    protected $objUnidadDidactica;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Capacidades();
        $this->objCompetencia = new Competencias();
        $this->objPrograma = new Programa();
        $this->objPlan = new Plan();
        $this->objModulo = new ModuloFormativo();
        $this->objSemestre = new Semestre();
        $this->objUnidadDidactica = new UnidadDidactica();
    }

    public function index()
    {
        // Listas iniciales para el filtro principal
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/capacidades/index', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Capacidades'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 1;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $filters = [
            'id_programa_estudios' => $_GET['filter_programa'] ?? null,
            'id_plan_estudio'      => $_GET['filter_plan'] ?? null,
            'id_modulo_formativo'  => $_GET['filter_modulo'] ?? null,
            'id_semestre'          => $_GET['filter_semestre'] ?? null,
            'id_competencia'       => $_GET['filter_competencia'] ?? null,
        ];

        $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // --- FORMULARIO NUEVO
    public function nuevo()
    {
        $programas = $this->objPrograma->getTodosProgramas();
        // Al crear, no hay nada seleccionado aún
        $planes = $modulos = $semestres = $unidades = $competencias = [];
        $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';

        $this->view('sigi/capacidades/nuevo', [
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'semestres' => $semestres,
            'unidades'  => $unidades,
            'competencias' => $competencias,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected'     => $id_plan_selected,
            'id_modulo_selected'   => $id_modulo_selected,
            'id_semestre_selected' => $id_semestre_selected,
            'cap' => [],
            'module' => 'sigi',
            'pageTitle' => 'Nueva Capacidad'
        ]);
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        $cap = $this->model->find($id);

        // Carga jerarquía dependiente para editar
        // 1. Unidad didáctica -> semestre
        $unidad = $this->objUnidadDidactica->find($cap['id_unidad_didactica']);
        $id_semestre_selected = $unidad['id_semestre'] ?? '';

        // 2. Semestre -> módulo
        $semestre = $this->objSemestre->find($id_semestre_selected);
        $id_modulo_selected = $semestre['id_modulo_formativo'] ?? '';

        // 3. Módulo -> plan
        $modulo = $this->objModulo->find($id_modulo_selected);
        $id_plan_selected = $modulo['id_plan_estudio'] ?? '';

        // 4. Plan -> programa
        $plan = $this->objPlan->find($id_plan_selected);
        $id_programa_selected = $plan['id_programa_estudios'] ?? '';

        // Listados para selects dependientes (ya filtrados)
        $programas = $this->objPrograma->getTodosProgramas();
        $planes    = $this->objPlan->getPlanesByPrograma($id_programa_selected);
        $modulos   = $this->objModulo->getModuloByPlan($id_plan_selected);
        $semestres = $this->objSemestre->getSemestresByModulo($id_modulo_selected);
        $unidades  = $this->objUnidadDidactica->getUnidadesBySemestre($id_semestre_selected);
        $competencias = $this->objCompetencia->getCompetenciasByModulo($id_modulo_selected);

        $this->view('sigi/capacidades/editar', [
            'cap' => $cap,
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'semestres' => $semestres,
            'unidades'  => $unidades,
            'competencias' => $competencias,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected'     => $id_plan_selected,
            'id_modulo_selected'   => $id_modulo_selected,
            'id_semestre_selected' => $id_semestre_selected,
            'module' => 'sigi',
            'pageTitle' => 'Editar Capacidad'
        ]);
    }

    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        $data = [
            'id'                  => $_POST['id'] ?? null,
            'id_unidad_didactica' => $_POST['id_unidad_didactica'],
            'id_competencia'      => $_POST['id_competencia'],
            'codigo'              => trim($_POST['codigo']),
            'descripcion'         => trim($_POST['descripcion']),
        ];
        $id_capacidad = $this->model->guardar($data);

        $_SESSION['flash_success'] = "Capacidad guardada correctamente. Ahora registre los indicadores de logro.";

        // Redirige directamente a los indicadores de logro de capacidad
        header('Location: ' . BASE_URL . '/sigi/indicadorLogroCapacidad/index/' . $id_capacidad);
        exit;
    }

    // --- ENDPOINTS PARA SELECTS DEPENDIENTES ---
   
}