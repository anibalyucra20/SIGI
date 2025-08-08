<?php

namespace App\Controllers\Academico;

require_once __DIR__ . '/../../../app/models/Academico/Licencias.php';

use Core\Controller;
use App\Models\Academico\Licencias;

class LicenciasController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Licencias();
    }

    public function index()
    {
        $programas = $this->model->getProgramas();
        $this->view('academico/licencias/index', [
            'programas' => $programas,
            'module' => 'academico',
            'pageTitle' => 'Licencias de estudios'
        ]);
    }

    // DataTables AJAX
    public function data()
    {
        if (\Core\Auth::esAdminAcademico()):
            header('Content-Type: application/json; charset=utf-8');
            $draw   = $_GET['draw']  ?? 1;
            $start  = $_GET['start'] ?? 0;
            $length = $_GET['length'] ?? 10;

            $filters = [
                'dni' => $_GET['dni'] ?? null,
                'apellidos_nombres' => $_GET['apellidos_nombres'] ?? null,
                'programa' => $_GET['programa'] ?? null,
                'plan' => $_GET['plan'] ?? null,
                'semestre' => $_GET['semestre'] ?? null,
                'turno' => $_GET['turno'] ?? null,
                'seccion' => $_GET['seccion'] ?? null,
                'periodo' => $_SESSION['sigi_periodo_actual_id'],
                'sede' => $_SESSION['sigi_sede_actual']
            ];

            $result = $this->model->getPaginated($filters, $length, $start);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminAcademico()):
            header('Content-Type: application/json; charset=utf-8');
            $id_matricula = $_POST['id_matricula'] ?? 0;
            $licencia = trim($_POST['licencia'] ?? '');
            if (!$id_matricula || $licencia == '') {
                echo json_encode(['success' => false, 'msg' => 'Datos incompletos']);
                exit;
            }
            $ok = $this->model->guardarLicencia($id_matricula, $licencia);
            echo json_encode(['success' => $ok]);
        endif;
        exit;
    }

    public function eliminar($id)
    {
        if (\Core\Auth::esAdminAcademico()):
            $this->model->eliminarLicencia($id);
        endif;
        header('Location: ' . BASE_URL . '/academico/licencias');
        exit;
    }

    // AJAX - Combos dependientes
    public function planesPorPrograma($id_programa)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->model->getPlanesByPrograma($id_programa));
        exit;
    }
    public function semestresPorPlan($id_plan)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->model->getSemestresByPlan($id_plan));
        exit;
    }

    // AJAX - Buscar matrícula por DNI, periodo y sede
    public function buscarMatriculaAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        $dni = $_GET['dni'] ?? '';
        $periodo = $_SESSION['sigi_periodo_actual_id'];
        $sede = $_SESSION['sigi_sede_actual'];
        $matricula = $this->model->buscarMatriculaPorDNI($dni, $periodo, $sede);
        if ($matricula) {
            echo json_encode(['success' => true, 'data' => $matricula]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'No se encontró matrícula activa para ese estudiante en el periodo y sede actual']);
        }
        exit;
    }
}
