<?php

namespace App\Controllers\Admision;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Admision/Vacantes.php';
require_once __DIR__ . '/../../../app/models/Admision/ProcesosAdmision.php';
require_once __DIR__ . '/../../../app/models/Admision/TiposModalidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';

use App\Models\Admision\Vacantes;
use App\Models\Admision\ProcesosAdmision;
use App\Models\Admision\TiposModalidades;
use App\Models\Sigi\Programa;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Sedes;

class VacantesController extends Controller
{
    protected $model;
    protected $periodos;
    protected $sedes;
    protected $tiposModalidades;
    protected $procesosAdmision;
    protected $programa;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Vacantes();
        $this->procesosAdmision = new ProcesosAdmision();
        $this->periodos = new PeriodoAcademico();
        $this->sedes = new Sedes();
        $this->tiposModalidades = new TiposModalidades();
        $this->programa = new Programa();
    }

    public function index()
    {
        if (\Core\Auth::esAdminAdmision()):
        endif;
        $this->view('admision/vacantes/index', [
            'module'    => 'admision',
            'pageTitle' => 'Vacantes'
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
            $programaSeleccionado = null;
            $programas = $this->programa->getTodosProgramas();
            $modalidadSeleccionado = null;
        endif;
        $this->view('admision/vacantes/nuevo', [
            'procesosAdmision' => $procesosAdmision,
            'procesoAdmisionSeleccionado' => $procesoAdmisionSeleccionado,
            'tipoModalidadSeleccionado' => $tipoModalidadSeleccionado,
            'tiposModalidades' => $tiposModalidades,
            'programaSeleccionado' => $programaSeleccionado,
            'programas' => $programas,
            'modalidadSeleccionado' => $modalidadSeleccionado,
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
                'id_modalidad_admision' => $_POST['id_modalidad_admision'],
                'id_programa_estudio' => $_POST['id_programa_estudio'],
                'cantidad' => $_POST['cantidad'],
            ];

            // Validar unicidad
            if ($this->model->checkDuplicate($data['id_proceso_admision'], $data['id_modalidad_admision'], $data['id_programa_estudio'], $data['id'])) {
                $_SESSION['flash_error'] = "Ya existe un registro con este Proceso y Modalidad.";
                if (!empty($data['id'])) {
                    header('Location: ' . BASE_URL . '/admision/vacantes/editar/' . $data['id']);
                } else {
                    header('Location: ' . BASE_URL . '/admision/vacantes/nuevo');
                }
                exit;
            }

            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Proceso de Modalidades guardado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/admision/vacantes');
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminAdmision()):
            $vacante = $this->model->find($id);

            $procesosAdmision = $this->procesosAdmision->getProcesosAdmisionSedePeriodo($_SESSION['sigi_sede_actual'], $_SESSION['sigi_periodo_actual_id']);
            $procesoAdmisionSeleccionado = $vacante['id_proceso_admision'];
            $tipoModalidadSeleccionado = $vacante['id_tipo_modalidad'];
            $modalidadSeleccionado = $vacante['id_modalidad_admision'];
            $tiposModalidades = $this->tiposModalidades->getTiposModalidades();
            $programaSeleccionado = $vacante['id_programa_estudio'];
            $programas = $this->programa->getTodosProgramas();
        endif;
        $this->view('admision/vacantes/editar', [
            'vacante'      => $vacante,
            'procesosAdmision' => $procesosAdmision,
            'tiposModalidades' => $tiposModalidades,
            'programas' => $programas,
            'programaSeleccionado' => $programaSeleccionado,
            'procesoAdmisionSeleccionado' => $procesoAdmisionSeleccionado,
            'tipoModalidadSeleccionado' => $tipoModalidadSeleccionado,
            'modalidadSeleccionado' => $modalidadSeleccionado,
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
