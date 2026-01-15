<?php

namespace App\Controllers\Admision;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Admision/ProcesosModalidades.php';
require_once __DIR__ . '/../../../app/models/Admision/ProcesosAdmision.php';
require_once __DIR__ . '/../../../app/models/Admision/TiposModalidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';

use App\Models\Admision\ProcesosModalidades;
use App\Models\Admision\ProcesosAdmision;
use App\Models\Admision\TiposModalidades;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Sedes;

class ProcesosModalidadesController extends Controller
{
    protected $model;
    protected $periodos;
    protected $sedes;
    protected $tiposModalidades;
    protected $procesosAdmision;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProcesosModalidades();
        $this->procesosAdmision = new ProcesosAdmision();
        $this->periodos = new PeriodoAcademico();
        $this->sedes = new Sedes();
        $this->tiposModalidades = new TiposModalidades();
    }

    public function index()
    {
        if (\Core\Auth::esAdminAdmision()):
        endif;
        $this->view('admision/procesosModalidades/index', [
            'module'    => 'admision',
            'pageTitle' => 'Procesos de Modalidades'
        ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminAdmision()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';
            $filters = [
                'id_sede'       => $_SESSION['sigi_sede_actual'] ?? null,
                'id_periodo'    => $_SESSION['sigi_periodo_actual_id'] ?? null,
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
        if (\Core\Auth::esAdminAdmision()):
            $periodo = $_SESSION['sigi_periodo_actual_id'];
            $sede = $_SESSION['sigi_sede_actual'];
            $procesosAdmision = $this->procesosAdmision->getProcesosAdmisionSedePeriodo($sede, $periodo);
            $procesoAdmisionSeleccionado = null;
            $tipoModalidadSeleccionado = null;
            $tiposModalidades = $this->tiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/procesosModalidades/nuevo', [
            'procesosAdmision' => $procesosAdmision,
            'procesoAdmisionSeleccionado' => $procesoAdmisionSeleccionado,
            'tipoModalidadSeleccionado' => $tipoModalidadSeleccionado,
            'tiposModalidades' => $tiposModalidades,
            'isEdit'    => false,
            'module'    => 'admision',
            'pageTitle' => 'Nuevo Proceso de Modalidades'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminAdmision()):
            $data = [
                'id' => $_POST['id'] ?? null,
                'id_proceso_admision' => $_POST['id_proceso_admision'],
                'id_tipo_modalidad' => $_POST['id_tipo_modalidad'],
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'fecha_cierre_inscripcion' => $_POST['fecha_cierre_inscripcion'],
                'id_periodo' => $_SESSION['sigi_periodo_actual_id'],
                'id_sede' => $_SESSION['sigi_sede_actual'],
            ];

            // Validar unicidad
            if ($this->model->checkDuplicate($data['id_proceso_admision'], $data['id_tipo_modalidad'], $data['id'])) {
                $_SESSION['flash_error'] = "Ya existe un registro con este Proceso y Modalidad.";
                if (!empty($data['id'])) {
                    header('Location: ' . BASE_URL . '/admision/procesosModalidades/editar/' . $data['id']);
                } else {
                    header('Location: ' . BASE_URL . '/admision/procesosModalidades/nuevo');
                }
                exit;
            }

            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Proceso de Modalidades guardado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/admision/procesosModalidades');
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminAdmision()):
            $procesoModalidad = $this->model->find($id);

            $procesosAdmision = $this->procesosAdmision->getProcesosAdmisionSedePeriodo($_SESSION['sigi_sede_actual'], $_SESSION['sigi_periodo_actual_id']);
            $procesoAdmisionSeleccionado = $procesoModalidad['id_proceso_admision'];
            $tipoModalidadSeleccionado = $procesoModalidad['id_tipo_modalidad'];
            $tiposModalidades = $this->tiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/procesosModalidades/editar', [
            'procesoModalidad'      => $procesoModalidad,
            'procesosAdmision' => $procesosAdmision,
            'tiposModalidades' => $tiposModalidades,
            'procesoAdmisionSeleccionado' => $procesoAdmisionSeleccionado,
            'tipoModalidadSeleccionado' => $tipoModalidadSeleccionado,
            'isEdit'    => true,
            'module'    => 'admision',
            'pageTitle' => 'Editar Proceso de Modalidades'
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
