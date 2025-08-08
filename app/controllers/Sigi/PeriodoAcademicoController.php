<?php

namespace App\Controllers\Sigi;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';

use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Docente;
use Core\Controller;

class PeriodoAcademicoController extends Controller
{
    protected $model;
    protected $objDocente;
    public function __construct()
    {
        parent::__construct();
        $this->model = new PeriodoAcademico();
        $this->objDocente = new Docente();
    }
    public function index()
    {
        $this->view('sigi/periodoAcademico/index', [
            'module'    => 'sigi',
            'pageTitle' => 'Periodos Académicos'
        ]);
    }
    public function cambiarSesion()
    {
        if (!empty($_GET['periodo'])) {
            $_SESSION['sigi_periodo_actual_id'] = $_GET['periodo'];
        }
        // Seguridad: sólo rutas internas
        if (!empty($_GET['redirect']) && strpos($_GET['redirect'], '/') === 0) {
            $redirect = $_GET['redirect'];
        } else {
            $redirect = BASE_URL . '/intranet';
        }
        header('Location: ' . $redirect);
        exit;
    }
    public function data()
    {
        if (\Core\Auth::esAdminSigi()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 0;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'desc';

            $result = $this->model->getPaginated($length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }

    public function nuevo()
    {
        $directores = $this->objDocente->getDirectores();
        $this->view('sigi/periodoAcademico/nuevo', [
            'directores' => $directores,
            'periodo'    => [],
            'module'     => 'sigi',
            'pageTitle'  => 'Nuevo Periodo Académico'
        ]);
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $periodo    = $this->model->find($id);
            $directores = $this->objDocente->getDirectores();
        endif;
        $this->view('sigi/periodoAcademico/editar', [
            'directores' => $directores,
            'periodo'    => $periodo,
            'module'     => 'sigi',
            'pageTitle'  => 'Editar Periodo Académico'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $data = [
                'id'           => $_POST['id'] ?? null,
                'nombre'       => $_POST['nombre'],
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin'    => $_POST['fecha_fin'],
                'director'     => $_POST['director'],
                'fecha_actas'  => $_POST['fecha_actas']
            ];
            $id = $this->model->guardar($data);
            $_SESSION['flash_success'] = "Periodo guardado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/periodoAcademico');
        exit;
    }
}
