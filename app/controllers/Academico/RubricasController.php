<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Rubrica.php';
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../helpers/Integrator.php';

use App\Models\Academico\Rubrica;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Helpers\Integrator;

class RubricasController extends Controller
{
    protected $model;
    protected $objProgramacionUD;
    protected $objIntegrator;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Rubrica();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objIntegrator = new Integrator();
    }

    /**
     * Vista principal: Muestra el banco personal de rúbricas del docente
     */
    public function index()
    {
        $this->view('academico/rubricas/index', [
            'module'    => 'academico',
            'pageTitle' => 'Banco de Rúbricas'
        ]);
    }

    /**
     * Endpoint DataTables para listar las rúbricas locales del docente
     */
    public function data()
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        $draw      = (int)($_GET['draw']    ?? 1);
        $start     = (int)($_GET['start']   ?? 0);
        $length    = (int)($_GET['length']  ?? 10);
        $orderCol  = (int)($_GET['order'][0]['column'] ?? 0);
        $orderDir  = (string)($_GET['order'][0]['dir'] ?? 'desc');
        $search    = trim($_GET['search']['value'] ?? '');

        $res = $this->model->getPaginatedPorDocente($idUsuario, $search, $length, $start, $orderCol, $orderDir);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $res['total'],
            'recordsFiltered' => $res['filtered'],
            'data'            => $res['data'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Vista para crear nueva rúbrica (Muestra el catálogo institucional del Master y opciones de clonación)
     */
    public function nuevo()
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        $idPeriodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        
        // Unidades didácticas del docente para asociar opcionalmente al clonar
        $programacionesDocente = $this->objProgramacionUD->getProgramacionesPorDocente($idUsuario, $idPeriodo);
        //var_dump($programacionesDocente); // Debugging line to check the fetched data
        $this->view('academico/rubricas/nuevo', [
            'module'    => 'academico',
            'pageTitle' => 'Clonar / Crear Rúbrica',
            'unidades'  => $programacionesDocente
        ]);
    }

    /**
     * Endpoint AJAX para que la vista 'nuevo' cargue el catálogo institucional directamente vía Integrator
     */
    public function catalogoMaster()
    {
        header('Content-Type: application/json; charset=utf-8');
        $respuesta = $this->objIntegrator->getRubricasMaster();
        echo json_encode($respuesta);
        exit;
    }

    /**
     * Endpoint AJAX para obtener el detalle/JSON de una rúbrica institucional específica
     */
    public function detalleMaster($id_master)
    {
        header('Content-Type: application/json; charset=utf-8');
        $respuesta = $this->objIntegrator->getRubricaDetalleMaster((int)$id_master);
        echo json_encode($respuesta);
        exit;
    }

    /**
     * Vista para editar una rúbrica propia existente
     */
    public function editar($id)
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        $idPeriodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;

        $rubrica = $this->model->findPropia((int)$id, $idUsuario);

        if (!$rubrica) {
            $_SESSION['flash_error'] = 'Rúbrica no encontrada o sin permisos.';
            header('Location: ' . BASE_URL . '/academico/rubricas');
            exit;
        }

        $programacionesDocente = $this->objProgramacionUD->getProgramacionesPorDocente($idUsuario, $idPeriodo);

        $this->view('academico/rubricas/editar', [
            'module'    => 'academico',
            'pageTitle' => 'Editar Rúbrica',
            'unidades'  => $programacionesDocente,
            'rubrica'   => $rubrica
        ]);
    }

    /**
     * Procesa el POST para guardar o clonar una rúbrica en la base de datos local
     */
    public function guardar()
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;

        $d = [
            'id'                  => $_POST['id'] ?? null,
            'master_rubrica_id'   => !empty($_POST['master_rubrica_id']) ? $_POST['master_rubrica_id'] : null,
            'unidad_didactica_id' => !empty($_POST['unidad_didactica_id']) ? $_POST['unidad_didactica_id'] : null,
            'nombre'              => trim($_POST['nombre'] ?? ''),
            'contenido_json'      => $_POST['contenido_json'] ?? '',
            'usuario_id'          => $idUsuario
        ];

        if (empty($d['nombre']) || empty($d['contenido_json'])) {
            $_SESSION['flash_error'] = 'Datos incompletos para guardar la rúbrica.';
            header('Location: ' . BASE_URL . '/academico/rubricas');
            exit;
        }

        $ok = $this->model->guardar($d);

        if ($ok) {
            $_SESSION['flash_success'] = 'Rúbrica almacenada correctamente en su banco personal.';
        } else {
            $_SESSION['flash_error'] = 'Error de BD: No se pudo almacenar la rúbrica.';
        }

        header('Location: ' . BASE_URL . '/academico/rubricas');
        exit;
    }

    /**
     * Soft delete de rúbrica local
     */
    public function eliminar($id)
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        $this->model->eliminarPropia((int)$id, $idUsuario);
        
        $_SESSION['flash_success'] = 'Rúbrica eliminada de su banco.';
        header('Location: ' . BASE_URL . '/academico/rubricas');
        exit;
    }
}