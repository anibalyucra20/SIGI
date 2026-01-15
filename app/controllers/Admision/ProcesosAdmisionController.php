<?php

namespace App\Controllers\Admision;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Admision/ProcesosAdmision.php';
require_once __DIR__ . '/../../../app/models/Admision/TiposModalidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';

use App\Models\Admision\ProcesosAdmision;
use App\Models\Admision\TiposModalidades;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Sedes;

class ProcesosAdmisionController extends Controller
{
    protected $model;
    protected $periodos;
    protected $sedes;
    protected $tiposModalidades;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProcesosAdmision();
        $this->periodos = new PeriodoAcademico();
        $this->sedes = new Sedes();
        $this->tiposModalidades = new TiposModalidades();
    }

    public function index()
    {
        if (\Core\Auth::esAdminAdmision()):
        endif;
        $this->view('admision/procesosAdmision/index', [
            'module'    => 'admision',
            'pageTitle' => 'Procesos de Admision'
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
            $periodos = $this->periodos->getPeriodos();
            $sedes = $this->sedes->getSedes();
            $tiposModalidades = $this->tiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/procesosAdmision/nuevo', [
            'periodos' => $periodos,
            'sedes' => $sedes,
            'tiposModalidades' => $tiposModalidades,
            'isEdit'    => false,
            'periodoSeleccionado' => $_SESSION['sigi_periodo_actual_id'],
            'sedeSeleccionada' => $_SESSION['sigi_sede_actual'],
            'module'    => 'admision',
            'pageTitle' => 'Nuevo Proceso de Admision'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminAdmision()):
            // Convertir array de modalidades a string separado por comas
            $modalidades = $_POST['tipos_modalidades_ingreso'] ?? [];

            // Validar que se haya seleccionado al menos una modalidad
            if (empty($modalidades) || !is_array($modalidades)) {
                $_SESSION['flash_error'] = "Debe seleccionar al menos una modalidad de admision.";
                // Redirigir atrás para no perder los datos (idealmente) o al listado
                if (!empty($_POST['id'])) {
                    header('Location: ' . BASE_URL . '/admision/procesosAdmision/editar/' . $_POST['id']);
                } else {
                    header('Location: ' . BASE_URL . '/admision/procesosAdmision/nuevo');
                }
                exit;
            }

            $modalidadesStr = implode(',', $modalidades);

            $data = [
                'id' => $_POST['id'] ?? null,
                'nombre' => $_POST['nombre'],
                'id_periodo' => $_POST['id_periodo'],
                'id_sede' => $_POST['id_sede'],
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'tipos_modalidades_ingreso' => $modalidadesStr, // Guardar como string
            ];
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Proceso de Admision guardado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/admision/procesosAdmision');
        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminAdmision()):
            $proceso = $this->model->find($id);

            // Convertir string de modalidades a array para la vista
            if (!empty($proceso['tipos_modalidades_ingreso'])) {
                $proceso['tipos_modalidades_ingreso'] = array_map('intval', explode(',', $proceso['tipos_modalidades_ingreso']));
            } else {
                $proceso['tipos_modalidades_ingreso'] = [];
            }

            $periodos = $this->periodos->getPeriodos();
            $sedes = $this->sedes->getSedes();
            $tiposModalidades = $this->tiposModalidades->getTiposModalidades();
        endif;
        $this->view('admision/procesosAdmision/editar', [
            'proceso'      => $proceso,
            'periodos' => $periodos,
            'sedes' => $sedes,
            'tiposModalidades' => $tiposModalidades,
            'isEdit'    => true,
            'periodoSeleccionado' => $proceso['id_periodo'],
            'sedeSeleccionada' => $proceso['id_sede'],
            'module'    => 'admision',
            'pageTitle' => 'Editar Proceso de Admision'
        ]);
        exit;
    }

    public function modalidadesPorProceso($id_proceso)
    {
        if (\Core\Auth::esAdminAdmision()):
            header('Content-Type: application/json');
            $proceso = $this->model->find($id_proceso);
            if ($proceso && !empty($proceso['tipos_modalidades_ingreso'])) {
                $ids = explode(',', $proceso['tipos_modalidades_ingreso']);
                $modalidades = $this->tiposModalidades->getModalidadesByIds($ids);
                echo json_encode($modalidades, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([], JSON_UNESCAPED_UNICODE);
            }
            exit;
        endif;
        exit;
    }
}
