<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/UnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Sigi/Semestre.php';
require_once __DIR__ . '/../../../app/models/Sigi/ModuloFormativo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Plan.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use App\Models\Sigi\UnidadDidactica;
use App\Models\Sigi\Semestre;
use App\Models\Sigi\ModuloFormativo;
use App\Models\Sigi\Plan;
use App\Models\Sigi\Programa;

class UnidadDidacticaController extends Controller
{
    protected $model;
    protected $objSemestre;
    protected $objModulo;
    protected $objPlan;
    protected $objPrograma;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UnidadDidactica();
        $this->objSemestre = new Semestre();
        $this->objModulo = new ModuloFormativo();
        $this->objPlan = new Plan();
        $this->objPrograma = new Programa();
    }

    public function index()
    {
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/unidadDidactica/index', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Unidades Didácticas'
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
            'nombre'               => $_GET['filter_nombre'] ?? null,
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

    // Endpoints para selects dependientes
    public function porSemestre($id_semestre)
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->getUnidadesBySemestre($id_semestre), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function nuevo()
    {
        $programas = $this->objPrograma->getTodosProgramas();
        $this->view('sigi/unidadDidactica/nuevo', [
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Nueva Unidad Didáctica'
        ]);
    }

    public function guardar()
    {
        $data = [
            'id'        => $_POST['id'] ?? null,
            'nombre'    => trim($_POST['nombre']),
            'id_semestre' => $_POST['id_semestre'],
            'creditos_teorico'  => $_POST['creditos_teorico'],
            'creditos_practico'     => $_POST['creditos_practico'],
            'tipo'      => $_POST['tipo'],
            'orden'     => $_POST['orden'],
        ];
        $this->model->guardar($data);
        $_SESSION['flash_success'] = "Unidad didáctica guardada correctamente.";
        header('Location: ' . BASE_URL . '/sigi/unidadDidactica');
        exit;
    }

    public function editar($id)
    {
        $ud = $this->model->find($id);
        // Para selects dependientes
        $semestre = null;
        $modulo = null;
        $plan = null;
        $programa = null;
        $planes = [];
        $modulos = [];
        $semestres = [];
        if (!empty($ud['id_semestre'])) {
            $semestre = $this->objSemestre->find($ud['id_semestre']);
            if ($semestre) {
                $modulo = $this->objModulo->find($semestre['id_modulo_formativo']);
                if ($modulo) {
                    $plan = $this->objPlan->find($modulo['id_plan_estudio']);
                    if ($plan) {
                        $programa = $this->objPrograma->find($plan['id_programa_estudios']);
                        $planes = $this->objPlan->getPlanesByPrograma($plan['id_programa_estudios']);
                        $modulos = $this->objModulo->getModuloByPlan($plan['id']);
                        $semestres = $this->objSemestre->getSemestresByModulo($modulo['id']);
                    }
                }
            }
        }
        $programas = $this->objPrograma->getTodosProgramas();

        $this->view('sigi/unidadDidactica/editar', [
            'ud'            => $ud,
            'programas'     => $programas,
            'planes'        => $planes,
            'modulos'       => $modulos,
            'semestres'     => $semestres,
            'id_programa_selected' => $programa['id'] ?? '',
            'id_plan_selected'     => $plan['id'] ?? '',
            'id_modulo_selected'   => $modulo['id'] ?? '',
            'module'        => 'sigi',
            'pageTitle'     => 'Editar Unidad Didáctica'
        ]);
    }
}
