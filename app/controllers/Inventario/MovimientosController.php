<?php

namespace App\Controllers\Inventario;

// incluir modelos necesarios
require_once __DIR__ . '/../../../app/models/Inventario/Bienes.php';
require_once __DIR__ . '/../../../app/models/Inventario/Ambientes.php'; 
require_once __DIR__ . '/../../../app/models/Inventario/Movimientos.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use Core\Controller;
use App\Models\Inventario\Bienes;
use App\Models\Inventario\Ambientes;
use App\Models\Inventario\Movimientos;
use App\Models\Sigi\PeriodoAcademico;

class MovimientosController extends Controller
{
    protected $model;
    protected $objAmbientes;
    protected $objPeriodoAcademico;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Movimientos();
        $this->objAmbientes = new Ambientes();
        $this->objPeriodoAcademico = new PeriodoAcademico();
    }

    /* ===================== Vista Principal ===================== */

    public function index()
    {
        $ambientes = $this->objAmbientes->getAmbientes();

        $this->view('inventario/movimientos/index', [
            'module'    => 'inventario',
            'pageTitle' => 'Lista de Movimientos',
            'ambientes' => $ambientes
        ]);
        exit;
    }

    public function data()
    {
        if (\Core\Auth::esAdminInventario()):
            header('Content-Type: application/json; charset=utf-8');
            $draw     = $_GET['draw']   ?? 1;
            $start    = $_GET['start']  ?? 0;
            $length   = $_GET['length'] ?? 10;
            $orderCol = $_GET['order'][0]['column'] ?? 1;
            $orderDir = $_GET['order'][0]['dir']    ?? 'desc'; 

            $filters = [
                'id_sede' => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
                'tipo' => $_GET['tipo'] ?? '', 
                'motivo' => $_GET['motivo'] ?? '',
                'documento_traslado' => $_GET['documento_traslado'] ?? ''
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            // Usamos TU método original, guardando tanto el ID de inventario como el ID de ambiente
            $ambientesList = $this->objAmbientes->getAmbientes();
            $ambientesMap = [];
            
            foreach ($ambientesList as $amb) {
                // Extraemos solo la palabra (ej. "AULA")
                $nombreAmb = trim($amb['tipo_ambiente'] ?? '');
                
                // Mapeamos por el ID del inventario
                if (!empty($amb['id_inventario'])) {
                    $ambientesMap[$amb['id_inventario']] = $nombreAmb;
                }
                
                // Mapeamos por el ID del ambiente original
                if (!empty($amb['id_ambiente'])) {
                    $ambientesMap[$amb['id_ambiente']] = $nombreAmb;
                }
            }

            foreach ($result['data'] as $key => $mov) {
                // Reemplazar número por nombre en ORIGEN
                $idOrigen = $mov['id_inv_ambiente_origen'] ?? null;
                if ($idOrigen && isset($ambientesMap[$idOrigen])) {
                    $result['data'][$key]['id_inv_ambiente_origen'] = $ambientesMap[$idOrigen];
                } else {
                    $result['data'][$key]['id_inv_ambiente_origen'] = !empty($idOrigen) ? $idOrigen : '-';
                }

                // Reemplazar número por nombre en DESTINO
                $idDestino = $mov['id_inv_ambiente_destino'] ?? null;
                if ($idDestino && isset($ambientesMap[$idDestino])) {
                    $result['data'][$key]['id_inv_ambiente_destino'] = $ambientesMap[$idDestino];
                } else {
                    $result['data'][$key]['id_inv_ambiente_destino'] = !empty($idDestino) ? $idDestino : '-';
                }
            }

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
            $ambientes = $this->objAmbientes->getAmbientes();
        endif;

        $this->view('inventario/movimientos/nuevo', [
            'ambientes' => $ambientes ?? [],
            'isEdit'    => false,
            'module'    => 'inventario',
            'pageTitle' => 'Registrar nuevo movimiento',
        ]);

        exit;
    }

    public function editar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            $data = $this->model->find($id);

            if (!$data) {
                $_SESSION['flash_error'] = "No se encontró el movimiento en Inventario";
                header('Location: ' . BASE_URL . '/inventario/movimientos');
                exit;
            }
            $ambientes = $this->objAmbientes->getAmbientes();
        endif;

        $this->view('inventario/movimientos/editar', [
            'data'      => $data ?? [],
            'ambientes' => $ambientes ?? [],
            'isEdit'    => true,
            'module'    => 'inventario',
            'pageTitle' => 'Editar Movimiento de Inventario',
        ]);
        exit;
    }

    public function guardar()
    {
        /*$periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);*/

        if (\Core\Auth::esAdminInventario() /*&& $periodo_vigente*/):
            
            $id_inv_ambiente_origen  = (int)($_POST['id_inv_ambiente_origen'] ?? 0); 
            $id_inv_ambiente_destino = (int)($_POST['id_inv_ambiente_destino'] ?? 0); 
            $tipo                    = trim($_POST['tipo'] ?? '');
            $motivo                  = trim($_POST['motivo'] ?? '');
            $documento_traslado      = trim($_POST['documento_traslado'] ?? '');

            $editar = (!empty($_POST['id'])) ? true  : false;
            $link   = (!empty($_POST['id'])) ? 'editar' : 'nuevo';

            $errores = [];
            
            if ($tipo === '' || $motivo === '') {
                $errores[] = "Complete todos los campos obligatorios (Tipo y Motivo).";
            }
            
            if ($id_inv_ambiente_origen > 0 && $id_inv_ambiente_origen === $id_inv_ambiente_destino) {
                $errores[] = "El ambiente de origen y destino no pueden ser el mismo.";
            }

            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/inventario/movimientos/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
            
            try {
                $data = [
                    'id'                      => $_POST['id'] ?? null,
                    'id_inv_ambiente_origen'  => $id_inv_ambiente_origen,
                    'id_inv_ambiente_destino' => $id_inv_ambiente_destino,
                    'tipo'                    => $tipo,
                    'motivo'                  => $motivo,
                    'documento_traslado'      => $documento_traslado
                ];
                
                $this->model->guardar($data);
                
                $_SESSION['flash_success'] = "Movimiento registrado exitosamente.";
                header('Location: ' . BASE_URL . '/inventario/movimientos');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/inventario/movimientos/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción o el periodo académico no está vigente.";
            header('Location: ' . BASE_URL . '/inventario/movimientos');
            exit;
        endif;
    }

    public function eliminar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            try {
                $this->model->eliminar($id);
                $_SESSION['flash_success'] = "Movimiento eliminado exitosamente.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo eliminar: verificar que no tenga registros asociados.";
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/inventario/movimientos');
        exit;
    }
}