<?php
namespace App\Controllers\Academico;

use Core\Controller;
require_once __DIR__ . '/../../../app/models/Academico/UnidadesDidacticas.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use App\Models\Academico\UnidadesDidacticas;
use App\Models\Sigi\PeriodoAcademico;

class UnidadesDidacticasController extends Controller
{
    protected $model;
    protected $objPeriodoAcademico;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UnidadesDidacticas();
        $this->objPeriodoAcademico = new PeriodoAcademico();
    }

    public function index()
    {
        // Puedes pasar $periodo si lo necesitas para la lógica JS de acciones.
        $this->view('academico/unidadesDidacticas/index', [
            'periodo' => $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id'] ?? 0),
            'module' => 'academico',
            'pageTitle' => 'Mis Unidades Didácticas Programadas'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 0;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $filters = [
            'id_sede'    => $_SESSION['sigi_sede_actual'] ?? 0,
            'id_periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
            'id_docente' => $_SESSION['sigi_user_id'] ?? 0,
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
}
