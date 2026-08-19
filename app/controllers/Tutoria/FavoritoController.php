<?php

namespace App\Controllers\Tutoria;

// incluir modelos necesarios
require_once __DIR__ . '/../../../app/models/Tutoria/Favoritos.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use Core\Controller;

use App\Models\Tutoria\Favoritos;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\Docente;
use App\Models\Sigi\PeriodoAcademico;

class FavoritoController extends Controller
{
    protected $model;
    protected $objSedes;
    protected $objDocente;
    protected $objPeriodoAcademico;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Favoritos();
        $this->objSedes = new Sedes();
        $this->objDocente = new Docente();
        $this->objPeriodoAcademico = new PeriodoAcademico();
    }
    /* ===================== Vista Principal ===================== */

    public function index()
    {
        $this->view('tutoria/favoritos/index', [
            'module'    => 'tutoria',
            'pageTitle' => 'favoritos',
        ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminTutoria()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'id_sede' => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
                /*'codigo' => $_GET['codigo'] ?? null,
                'nombre' => $_GET['nombre'] ?? null*/
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
        if (\Core\Auth::esAdminTutoria()):
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;

            $docentes = $this->objDocente->getDocentesPorSede($id_sede);
        endif;
        $this->view('tutoria/favoritos/nuevo', [
            'docentes' => $docentes,
            'isEdit'    => false,
            'module'    => 'tutoria',
            'pageTitle' => 'Nuevo favorito',
        ]);

        exit;
    }

    public function editar($id){
        if (\Core\Auth::esAdminTutoria()):
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $docentes = $this->objDocente->getDocentesPorSede($id_sede);
            $data = $this->model->find($id);
            if (!$data) {
                $_SESSION['flash_error'] = "No se encontró el favorito";
                header('Location: ' . BASE_URL . '/tutoria/favoritos');
                exit;
            }
        endif;
        $this->view('tutoria/favoritos/editar', [
            'data' => $data,
            'docentes' => $docentes,
            'isEdit'    => true,
            'module' => 'tutoria',
            'pageTitle' => 'Editar Favorito',
        ]);
        exit;
    }

    public function guardar()
    {
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        if (\Core\Auth::esAdminTutoria() && $periodo_vigente):
            $id_docente = (int)($_POST['docente'] ?? 0);
            $conclusiones = '';
            $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;

            //filtros para que sea dinamico 
            $editar = ($_POST['id']) ? true  : false;
            $link = ($_POST['id']) ? 'editar' : 'nuevo';
            $title = ($_POST['id']) ? 'Editar' : 'Nuevo';

            $errores = [];
            if (!$id_docente) {
                $errores[] = "Complete todos los campos obligatorios.";
            }
            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/tutoria/favoritos/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
            

            try {
                $data = [
                    'id' => $_POST['id'] ?? null,
                    'id_sede' => $id_sede,
                    'id_periodo_academico' => $id_periodo,
                    'id_docente' => $id_docente,
                    'conclusiones' => $conclusiones
                ];

            
                $id_prog_tutoria = $this->model->guardar($data);
                $_SESSION['flash_success'] .= "Favorito registrado exitosamente.";
                header('Location: ' . BASE_URL . '/tutoria/favoritos');
                exit;
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] .= "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/tutoria/favoritos/nuevo');
                exit;
            }
        else:
            $_SESSION['flash_error'] .= "Error: No tiene permisos para realizar esta acción o el periodo académico no está vigente.";
            header('Location: ' . BASE_URL . '/tutoria/favoritos/nuevo');
            exit;
        endif;
    }


    public function eliminar($id)
    {
        if (\Core\Auth::esAdminTutoria()):
            try {
                $this->model->eliminar($id);
                $_SESSION['flash_success'] = "Favorito eliminado exitosamente.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo eliminar: verificar que no tenga registros asociados." /*. $e->getMessage()*/;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/tutoria/favoritos');
        exit;
    }
}
