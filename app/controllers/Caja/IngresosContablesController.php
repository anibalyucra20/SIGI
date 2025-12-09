<?php

namespace App\Controllers\Caja;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Caja/IngresosContables.php';
require_once __DIR__ . '/../../../app/models/Caja/RubrosIngresosContables.php';
require_once __DIR__ . '/../../../app/models/Caja/MediosPago.php';
require_once __DIR__ . '/../../../app/models/Caja/CentroCostos.php';
require_once __DIR__ . '/../../../app/models/Caja/PlanCuentas.php';
require_once __DIR__ . '/../../../app/models/Caja/Proveedores.php';
require_once __DIR__ . '/../../../app/models/Caja/TiposDocumentos.php';

use App\Models\Caja\IngresosContables;
use App\Models\Caja\RubrosIngresosContables;
use App\Models\Caja\MediosPago;
use App\Models\Caja\CentroCostos;
use App\Models\Caja\PlanCuentas;
use App\Models\Caja\Proveedores;
use App\Models\Caja\TiposDocumentos;

class IngresosContablesController extends Controller
{
    protected $model;
    protected $ObjRubrosIngresosContables;
    protected $ObjMediosPago;
    protected $ObjCentroCostos;
    protected $ObjPlanCuentas;
    protected $ObjProveedores;
    protected $ObjTiposDocumentos;

    public function __construct()
    {
        parent::__construct();
        $this->model = new IngresosContables();
        $this->ObjRubrosIngresosContables = new RubrosIngresosContables();
        $this->ObjMediosPago = new MediosPago();
        $this->ObjCentroCostos = new CentroCostos();
        $this->ObjPlanCuentas = new PlanCuentas();
        $this->ObjProveedores = new Proveedores();
        $this->ObjTiposDocumentos = new TiposDocumentos();
    }

    public function index()
    {
        if (\Core\Auth::esAdminCaja()):
            $RubrosIngreso = $this->ObjRubrosIngresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();
        endif;
        // Listas iniciales para el filtro principal
        $this->view('caja/ingresosContables/index', [
            'RubrosIngreso' => $RubrosIngreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'module'    => 'caja',
            'pageTitle' => 'Ingresos Contables'
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
                'id_rubro_ingreso_contable' => $_GET['id_rubro_ingreso_contable'] ?? null,
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
            $n_correlativo = ($this->model->utlimo_correlativo());
            $correlativo = $n_correlativo['correlativo'] + 1;
            $RubrosIngreso = $this->ObjRubrosIngresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();

            $data['correlativo'] = $correlativo;
            $data['fecha'] = date('Y-m-d');
        endif;
        $this->view('caja/ingresosContables/nuevo', [
            //'programas' => $programas,
            'data' => $data,
            'RubrosIngreso' => $RubrosIngreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'module' => 'caja',
            'pageTitle' => 'Nuevo Ingreso'
        ]);

        exit;
    }



    // --- GUARDAR (nuevo o editar)
    public function guardar()
    {
        if (\Core\Auth::esAdminCaja()):
            $RubrosIngreso = $this->ObjRubrosIngresosContables->listar();
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
                'id_rubro_ingreso_contable'  => ($_POST['id_rubro_ingreso_contable']),
                'id_medio_pago'             => ($_POST['id_medio_pago']),
                'total_ingreso'              => trim($_POST['total_ingreso']),
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
                $this->view('caja/ingresosContables/' . $link, [
                    'errores'   => $errores,
                    'data'   => $data,
                    'RubrosIngreso' => $RubrosIngreso,
                    'MediosPago' => $MediosPago,
                    'CentrosCostos' => $CentrosCostos,
                    'Cuentas' => $Cuentas,
                    'Proveedores' => $Proveedores,
                    'TiposDocumentos' => $TiposDocumentos,
                    'module'    => 'caja',
                    'pageTitle' => $title . ' Ingreso',
                    'isEdit'    => $editar
                ]);
                return;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Ingreso guardado correctamente";
        endif;
        header('Location: ' . BASE_URL . '/caja/ingresosContables');
        exit;
    }

    // --- FORMULARIO EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminCaja()):
            $data = $this->model->find($id);
            $RubrosIngreso = $this->ObjRubrosIngresosContables->listar();
            $MediosPago = $this->ObjMediosPago->listar();
            $CentrosCostos = $this->ObjCentroCostos->listar();
            $Cuentas = $this->ObjPlanCuentas->listar();
            $Proveedores = $this->ObjProveedores->listar();
            $TiposDocumentos = $this->ObjTiposDocumentos->listar();
        endif;
        $this->view('caja/ingresosContables/editar', [
            'data' => $data,
            'RubrosIngreso' => $RubrosIngreso,
            'MediosPago' => $MediosPago,
            'CentrosCostos' => $CentrosCostos,
            'Cuentas' => $Cuentas,
            'Proveedores' => $Proveedores,
            'TiposDocumentos' => $TiposDocumentos,
            'isEdit'    => true,
            'module' => 'caja',
            'pageTitle' => 'Editar Ingreso'
        ]);
        exit;
    }
}
