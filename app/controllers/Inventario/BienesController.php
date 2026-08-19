<?php

namespace App\Controllers\Inventario;

// incluir modelos necesarios
require_once __DIR__ . '/../../../app/models/Inventario/Bienes.php';
//require_once __DIR__ . '/../../../app/models/Sigi/Ambientes.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';




use Core\Controller;

use App\Models\Inventario\Bienes;
use App\Models\Sigi\Ambientes;
//use App\Models\Sigi\Ambientes;
use App\Models\Sigi\PeriodoAcademico;



class BienesController extends Controller
{
    protected $model;
    //protected $objAmbientes;
    protected $objPeriodoAcademico;

    
     public function __construct()
    {
        parent::__construct();
        $this->model = new Bienes();
        //$this->objAmbientes = new Ambientes();
        $this->objPeriodoAcademico = new PeriodoAcademico();

    }

    /* ===================== Vista Principal ===================== */

    public function index()
    {
        $this->view('inventario/bienes/index', [
            'module'    => 'inventario',
            'pageTitle' => 'Lista de Bienes',

            ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminInventario()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'id_sede' => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0,

                'codigo_patrimonial' => $_GET['codigo_patrimonial'] ?? '',
                'denominacion'       => $_GET['denominacion'] ?? '',
                'estado_bien'        => $_GET['estado_bien'] ?? ''

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
        if (\Core\Auth::esAdminInventario()):
            //$ambientes = $this->objInventario->find($id);

        endif;
        $this->view('inventario/bienes/nuevo', [
            //'ambientes' => $ambientes,
            'isEdit'    => false,
            'module'    => 'inventario',
            'pageTitle' => 'Registrar nuevo bien',
        ]);

        exit;
    }

      public function editar($id){
        if (\Core\Auth::esAdminInventario()):
            $data = $this->model->find($id);

            if (!$data) {
                $_SESSION['flash_error'] = "No se encontró el bien en Inventario";
                header('Location: ' . BASE_URL . '/inventario/bienes');
                exit;
            }
        endif;
        $this->view('inventario/bienes/editar', [
            'data' => $data,
            'isEdit'    => true,
            'module' => 'inventario',
            'pageTitle' => 'Editar Bienes de Inventario',
        ]);
        exit;
    }

    public function guardar (){
        //$periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        //$periodo_vigente = ($periodo && $periodo['vigente']);

        if (\Core\Auth::esAdminInventario() /*&& $periodo_vigente*/):
            $id_inv_ambiente    = (int)($_POST['id_inv_ambiente'] ?? 0);
            $codigo_patrimonial = trim ($_POST['codigo_patrimonial'] ?? '');
            $denominacion       = trim($_POST['denominacion'] ?? '');
            $marca              = trim($_POST['marca'] ?? '');
            $modelo             = trim($_POST['modelo'] ?? '');
            $color              = trim($_POST['color'] ?? '');
            $serie              = trim($_POST['serie'] ?? '');
            $estado_bien        = trim($_POST['estado_bien'] ?? 'BUENO');
            $otros              = trim($_POST['otros'] ?? '');
            $observaciones      = trim($_POST['observaciones'] ?? '');

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = [];
            if (!$id_inv_ambiente ||$codigo_patrimonial === '' ||$denominacion === '' || $marca=== '' || $modelo=== '' || $color=== '' || $serie=== '' || $estado_bien=== '' || $otros=== '' || $observaciones=== ''   ) {
                $errores[] = "Complete todos los campos obligatorios.";
            }
            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/inventario/bienes/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
            try {
                  $data = [
                    'id' => $_POST['id'] ?? null,
                    'id_inv_ambiente' => $id_inv_ambiente,
                    'codigo_patrimonial' => $codigo_patrimonial,
                    'denominacion' => $denominacion,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'color' => $color,
                    'serie' => $serie,
                    'estado_bien' => $estado_bien,
                    'otros' => $otros,
                    'observaciones' => $observaciones
                ];
                $id_bien_inventario = $this->model->guardar($data);
                $_SESSION['flash_success'] .= "Nuevo bien guardado en inventario exitosamente.";
                header('Location: ' . BASE_URL . '/inventario/bienes');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] .= "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/inventario/bienes/nuevo');
                exit;
            }
        else:
            $_SESSION['flash_error'] .= "Error: No tiene permisos para realizar esta acción o el periodo académico no está vigente.";
            header('Location: ' . BASE_URL . '/inventario/bienes/nuevo');
            exit;
        endif;

    }

    

    public function eliminar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            try {
                $this->model->eliminar($id);
                $_SESSION['flash_success'] = "El Bien eliminada exitosamente.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo eliminar: verificar que no tenga registros asociados." /*. $e->getMessage()*/;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/inventario/bienes');
        exit;
    }
}
