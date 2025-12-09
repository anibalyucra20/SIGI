<?php

namespace App\Controllers\Caja;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Caja/EgresosGastos.php';
require_once __DIR__ . '/../../../app/models/Caja/RubrosEgresosContables.php';
require_once __DIR__ . '/../../../app/models/Caja/MediosPago.php';
require_once __DIR__ . '/../../../app/models/Caja/CentroCostos.php';
require_once __DIR__ . '/../../../app/models/Caja/PlanCuentas.php';
require_once __DIR__ . '/../../../app/models/Caja/Proveedores.php';
require_once __DIR__ . '/../../../app/models/Caja/TiposDocumentos.php';

use App\Models\Caja\EgresosGastos;
use App\Models\Caja\RubrosEgresosContables;
use App\Models\Caja\MediosPago;
use App\Models\Caja\CentroCostos;
use App\Models\Caja\PlanCuentas;
use App\Models\Caja\Proveedores;
use App\Models\Caja\TiposDocumentos;

class EgresosGastosController extends Controller
{
    protected $model;
    protected $ObjRubrosEgresosContables;
    protected $ObjMediosPago;
    protected $ObjCentroCostos;
    protected $ObjPlanCuentas;
    protected $ObjProveedores;
    protected $ObjTiposDocumentos;

    public function __construct()
    {
        parent::__construct();
        $this->model = new EgresosGastos();
        $this->ObjRubrosEgresosContables = new RubrosEgresosContables();
        $this->ObjMediosPago = new MediosPago();
        $this->ObjCentroCostos = new CentroCostos();
        $this->ObjPlanCuentas = new PlanCuentas();
        $this->ObjProveedores = new Proveedores();
        $this->ObjTiposDocumentos = new TiposDocumentos();
    }

    public function index()
    {
        if (\Core\Auth::esAdminCaja()):
            $RubrosEgreso = $this->ObjRubrosEgresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();
        endif;
        // Listas iniciales para el filtro principal
        $this->view('caja/egresosGastos/index', [
            'RubrosEgreso' => $RubrosEgreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'module'    => 'caja',
            'pageTitle' => 'Egresos/Gastos'
        ]);

        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminCaja()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'fecha_desde' => $_GET['fecha_desde'] ?? null,
                'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
                'correlativo' => $_GET['correlativo'] ?? null,
                'id_rubro_egreso_contable' => $_GET['id_rubro_egreso_contable'] ?? null,
                'id_medio_pago' => $_GET['id_medio_pago'] ?? null,
                'id_centro_costos_afectado' => $_GET['id_centro_costos_afectado'] ?? null,
                'id_cuenta_afectada' => $_GET['id_cuenta_afectada'] ?? null,
                'id_proveedor' => $_GET['id_proveedor'] ?? null,
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

    // --- FORMULARIO NUEVO
    public function nuevo()
    {
        $data = array();
        if (\Core\Auth::esAdminCaja()):
            $n_correlativo = $this->model->utlimo_correlativo();
            $correlativo = $n_correlativo['correlativo'] + 1;
            
            $RubrosEgreso = $this->ObjRubrosEgresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();

            $data = [
                'correlativo'               => $correlativo,
                'fecha'                     =>  date('Y-m-d'),
            ];
        endif;
        $this->view('caja/egresosGastos/nuevo', [
            //'programas' => $programas,
            'data' => $data,
            'RubrosEgreso' => $RubrosEgreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'module' => 'caja',
            'pageTitle' => 'Nuevo Egreso'
        ]);

        exit;
    }



    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        if (\Core\Auth::esAdminCaja()):
            $RubrosEgreso = $this->ObjRubrosEgresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();
            $n_correlativo = $this->model->utlimo_correlativo();
            $correlativo = $n_correlativo['correlativo'] + 1;
            $fecha_doc = (!empty($_POST['fecha_documento'])) ? $_POST['fecha_documento'] : '0000-00-00';
            $data = [
                'id'                        => $_POST['id'] ?? null,
                'correlativo'               => $correlativo,
                'fecha'                     => ($_POST['fecha']),
                'id_rubro_egreso_contable'  => ($_POST['id_rubro_egreso_contable']),
                'id_medio_pago'             => ($_POST['id_medio_pago']),
                'total_egreso'              => trim($_POST['total_egreso']),
                'numero'                    => trim($_POST['numero']),
                'observacion'               => trim($_POST['observacion']),
                'id_centro_costos_afectado' => ($_POST['id_centro_costos_afectado']),
                'id_cuenta_afectada'        => ($_POST['id_cuenta_afectada']),
                'id_proveedor'              => ($_POST['id_proveedor']),
                'id_tipo_documento'         => ($_POST['id_tipo_documento']),
                'serie_documento'           => trim($_POST['serie_documento']),
                'numero_documento'          => trim($_POST['numero_documento']),
                'fecha_documento'           => $fecha_doc,
                'observacion_documento'     => $_POST['observacion_documento'],
            ];
            $id = $_POST['id'] ?? null;

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = $this->model->validar($data, $editar, $id);
            if ($errores) {
                $this->view('caja/egresosGastos/' . $link, [
                    'errores'   => $errores,
                    'data'   => $data,
                    'RubrosEgreso' => $RubrosEgreso,
                    'MediosPago' => $MediosPago,
                    'CentrosCostos' => $CentrosCostos,
                    'Cuentas' => $Cuentas,
                    'Proveedores' => $Proveedores,
                    'TiposDocumentos' => $TiposDocumentos,
                    'module'    => 'caja',
                    'pageTitle' => $title . ' Egreso',
                    'isEdit'    => $editar
                ]);
                return;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Egreso guardado correctamente";
        endif;
        header('Location: ' . BASE_URL . '/caja/egresosGastos');
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminCaja()):
            $data = $this->model->find($id);
            $RubrosEgreso = $this->ObjRubrosEgresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();
        endif;
        $this->view('caja/egresosGastos/editar', [
            'data' => $data,
            'RubrosEgreso' => $RubrosEgreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'isEdit'    => true,
            'module' => 'caja',
            'pageTitle' => 'Editar Egreso'
        ]);
        exit;
    }
}
