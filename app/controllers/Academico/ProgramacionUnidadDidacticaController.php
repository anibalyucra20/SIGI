<?php
namespace App\Controllers\Academico;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
use App\Models\Academico\ProgramacionUnidadDidactica;

class ProgramacionUnidadDidacticaController extends Controller
{
    public function index()
    {
        \Core\Auth::start();
        $periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $periodo_activo = $_SESSION['sigi_periodo_actual_id'] ?? true;

        $modelo = new ProgramacionUnidadDidactica();
        $programas = $modelo->getProgramas();
        $docentes = $modelo->getDocentes();

        // Listas iniciales para el filtro principal
        $this->view('academico/programacion_unidad_didactica/index', [
            'programas' => $programas,
            'docentes' => $docentes,
            'periodo_activo' => $periodo_activo,
            'module'    => 'sigi',
            'pageTitle' => 'Capacidades'
        ]);
    }

    public function listar()
    {
        \Core\Auth::start();
        $periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $params = [
            'programa' => $_POST['programa'] ?? null,
            'plan'     => $_POST['plan'] ?? null,
            'modulo'   => $_POST['modulo'] ?? null,
            'semestre' => $_POST['semestre'] ?? null,
            'docente'  => $_POST['docente'] ?? null,
            'unidad'   => $_POST['unidad'] ?? null,
            'search'   => $_POST['search']['value'] ?? null,
            'start'    => $_POST['start'] ?? 0,
            'length'   => $_POST['length'] ?? 10,
        ];
        $modelo = new ProgramacionUnidadDidactica();
        $result = $modelo->getDataTable($params, $periodo);

        echo json_encode([
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $result['recordsTotal'],
            "recordsFiltered" => $result['recordsFiltered'],
            "data" => $result['data']
        ]);
    }

    // Métodos para cargar filtros en cascada
    public function planesPorPrograma()
    {
        $id_programa = $_POST['id_programa'] ?? null;
        $modelo = new ProgramacionUnidadDidactica();
        echo json_encode($modelo->getPlanes($id_programa));
    }
    public function modulosPorPlan()
    {
        $id_plan = $_POST['id_plan'] ?? null;
        $modelo = new ProgramacionUnidadDidactica();
        echo json_encode($modelo->getModulos($id_plan));
    }
    public function semestresPorModulo()
    {
        $id_modulo = $_POST['id_modulo'] ?? null;
        $modelo = new ProgramacionUnidadDidactica();
        echo json_encode($modelo->getSemestres($id_modulo));
    }
}
