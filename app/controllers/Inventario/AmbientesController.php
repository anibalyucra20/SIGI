<?php

namespace App\Controllers\Inventario;

// incluir modelos necesarios
require_once __DIR__ . '/../../../app/models/Inventario/Ambientes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Ambientes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';




use Core\Controller;

use App\Models\Inventario\Ambientes ;
use App\Models\Sigi\Docente;
use App\Models\Sigi\Ambientes as AmbientesSigi;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\PeriodoAcademico;



class AmbientesController extends Controller
{
    protected $model;
    protected $objAmbientes;
    protected $objDocente;
    protected $objSedes;
    protected $objPeriodoAcademico;

    
     public function __construct()
    {
        parent::__construct();
        $this->model = new Ambientes();
        $this->objAmbientes = new AmbientesSigi();
        $this->objDocente = new Docente();
        $this->objSedes = new Sedes();
        $this->objPeriodoAcademico = new PeriodoAcademico();

    }

    /* ===================== Vista Principal ===================== */

    public function index()
    {
        $this->view('inventario/ambientes/index', [
            'module'    => 'inventario',
            'pageTitle' => 'Lista de Ambientes',

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
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        
        if (\Core\Auth::esAdminInventario()):
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;

            $ambientes = $this->model->getAmbientes();
            $docentes = $this->objDocente->getDocentesPorSede($id_sede);
        endif;

        $this->view('inventario/ambientes/nuevo', [
            'ambientes' => $ambientes,
            'docentes' => $docentes,
            'isEdit'    => false,
            'module'    => 'inventario',
            'pageTitle' => 'Registrar nuevo Ambiente',
        ]);

        exit;
    }

      public function editar($id){
        if (\Core\Auth::esAdminInventario()):
            $data = $this->model->find($id);

            if (!$data) {
                $_SESSION['flash_error'] = "No se encontró el ambiente en Inventario";
                header('Location: ' . BASE_URL . '/inventario/ambientes');
                exit;
            }

            //Cargar las listas para los selects ---
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $ambientes = $this->model->getAmbientes();
            $docentes = $this->objDocente->getDocentesPorSede($id_sede);
            
        endif;
        
        $this->view('inventario/ambientes/editar', [
            'data'      => $data,
            'ambientes' => $ambientes ?? [], // vista
            'docentes'  => $docentes ?? [],  // vista
            'isEdit'    => true,
            'module'    => 'inventario',
            'pageTitle' => 'Editar Ambientes de Inventario',
        ]);
        exit;
    }
    public function guardar (){
        /*$periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']); */

        
        if (\Core\Auth::esAdminInventario() /* && $periodo_vigente*/ ):
            $id_sigi_ambiente    = (int)($_POST['id_sigi_ambiente'] ?? 0);
            $id_docente          = (int)($_POST['id_docente'] ?? $_POST['docente'] ?? 0);

            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = [];
            if (!$id_sigi_ambiente ||!$id_docente  ) {
                $errores[] = "Complete todos los campos obligatorios.";
            }
            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/inventario/ambientes/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
            try {
                  $data = [
                    'id' => $_POST['id'] ?? null,
                    'id_sigi_ambiente' => $id_sigi_ambiente,
                    'id_docente' => $id_docente
                ];
                $id_bien_inventario = $this->model->guardar($data);
                $_SESSION['flash_success'] .= "Nuevo Ambiente guardado en inventario exitosamente.";
                header('Location: ' . BASE_URL . '/inventario/ambientes');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] .= "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/inventario/ambientes/nuevo');
                exit;
            }
        else:
            $_SESSION['flash_error'] .= "Error: No tiene permisos para realizar esta acción o el periodo académico no está vigente.";
            header('Location: ' . BASE_URL . '/inventario/ambientes/nuevo');
            exit;
        endif;

    }

    

    public function eliminar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            try {
                $this->model->eliminar($id);
                $_SESSION['flash_success'] = "El Ambiente fue eliminado exitosamente.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo eliminar: verificar que no tenga registros asociados." /*. $e->getMessage()*/;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/inventario/ambientes');
        exit;
    }

}
