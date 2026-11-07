<?php

namespace App\Controllers\Inventario;

require_once __DIR__ . '/../../../vendor/autoload.php';
// incluir modelos necesarios
require_once __DIR__ . '/../../../app/models/Inventario/Bienes.php';
require_once __DIR__ . '/../../../app/models/Inventario/Ambientes.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';


use Core\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Inventario\Bienes;
use App\Models\Inventario\Ambientes;
use App\Models\Sigi\PeriodoAcademico;


class BienesController extends Controller
{
    protected $model;
    protected $objAmbientes;
    protected $objPeriodoAcademico;

    
     public function __construct()
    {
        parent::__construct();
        $this->model = new Bienes();
        $this->objAmbientes = new Ambientes();
        $this->objPeriodoAcademico = new PeriodoAcademico();

    }

    /* ===================== Vista Principal ===================== */

    public function index()
    {   
        if (\Core\Auth::esAdminInventario()):
            $ambientes = $this->objAmbientes->getAmbientes();
        endif;

        $this->view('inventario/bienes/index', [
            'ambientes' => $ambientes ?? [],
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
                'estado_bien'        => $_GET['estado_bien'] ?? '',
                'activo'             => $_GET['activo'] ?? '',
                'id_inv_ambiente'    => $_GET['id_inv_ambiente'] ?? '' // Filtro que lee el modelo
            ];

            // 1. Obtenemos los datos ya filtrados por la BD
            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            // 2. Mapeamos los nombres de los ambientes para mostrarlos bonitos en la tabla
            $ambientesList = $this->objAmbientes->getAmbientes();
            $ambientesMap = [];
            foreach ($ambientesList as $amb) {
            $nombreAmb = trim($amb['tipo_ambiente'] ?? '');
                $ambientesMap[$amb['id_inventario']] = $nombreAmb;
            }

            foreach ($result['data'] as $key => $bien) {
                $idAmb = $bien['id_inv_ambiente'] ?? null;
                if ($idAmb && isset($ambientesMap[$idAmb])) {
                    $result['data'][$key]['id_inv_ambiente'] = $ambientesMap[$idAmb];
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
        $this->view('inventario/bienes/nuevo', [
            'ambientes' => $ambientes,
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
            $ambientes = $this->objAmbientes->getAmbientes();
        endif;
        $this->view('inventario/bienes/editar', [
            'data' => $data,
            'ambientes' => $ambientes,
            'isEdit'    => true,
            'module' => 'inventario',
            'pageTitle' => 'Editar Bienes de Inventario',
        ]);
        exit;
    }
  public function guardar (){
        /*$periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);*/

        if (\Core\Auth::esAdminInventario() /* && $periodo_vigente*/):
            // 1. CORRECCIÓN: Capturamos 'id_sigi_ambiente' tal como viene del HTML
            $id_sigi_ambiente   = (int)($_POST['id_sigi_ambiente'] ?? 0); 
            $codigo_patrimonial = trim($_POST['codigo_patrimonial'] ?? '');
            $denominacion       = trim($_POST['denominacion'] ?? '');
            $marca              = trim($_POST['marca'] ?? '');
            $modelo             = trim($_POST['modelo'] ?? '');
            $color              = trim($_POST['color'] ?? '');
            $serie              = trim($_POST['serie'] ?? '');
            $estado_bien        = trim($_POST['estado_bien'] ?? 'BUENO');
            $otros              = trim($_POST['otros'] ?? '');
            $observaciones      = trim($_POST['observaciones'] ?? '');

            $editar = (!empty($_POST['id'])) ? true  : false;
            $link = (!empty($_POST['id'])) ? 'editar' : 'nuevo';
            $title = (!empty($_POST['id'])) ? 'Editar' : 'Nuevo';

            $errores = [];
            
            if (!$id_sigi_ambiente || $codigo_patrimonial === '' || $denominacion === '' || $estado_bien === '') {
                $errores[] = "Complete todos los campos obligatorios.";
            }

            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode(' ', $errores);
                header('Location: ' . BASE_URL . '/inventario/bienes/' . $link . '/' . ($_POST['id'] ?? ''));
                exit;
            }
            
            try {
                $esEdicion = !empty($_POST['id']); // Identificamos si es edición o nuevo

                $data = [
                    'id' => $_POST['id'] ?? null,
                    'id_inv_ambiente' => $id_sigi_ambiente, 
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
                
                
                if ($esEdicion) {
                    $_SESSION['flash_success'] = "El bien de inventario fue actualizado exitosamente.";
                } else {
                    $_SESSION['flash_success'] = "Nuevo bien guardado en inventario exitosamente.";
                }

                header('Location: ' . BASE_URL . '/inventario/bienes');
                exit;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "No se pudo registrar: " . $e->getMessage();
                header('Location: ' . BASE_URL . '/inventario/bienes');
                exit;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción o el periodo académico no está vigente.";
            header('Location: ' . BASE_URL . '/inventario/bienes');
            exit;
        endif;
    }

    public function eliminar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            try {
                $this->model->eliminar($id);
                $_SESSION['flash_success'] = "El estado del bien cambió a Dado de Baja.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo dar de Baja: verificar que no tenga registros asociados." /*. $e->getMessage()*/;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/inventario/bienes');
        exit;
    }
    
    //PARA CAMBIAR EL ESTADO DE UN BIEN DADO DE BAJA A ACTIVO NUEVAMENTE
    public function activar($id)
    {
        if (\Core\Auth::esAdminInventario()):
            try {
                $this->model->activar($id);
                $_SESSION['flash_success'] = "El bien fue reactivado y ahora vuelve a estar Disponible.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo reactivar el bien.";
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/inventario/bienes');
        exit;               
    }

    public function descargarPlantilla()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Cabeceras de la plantilla
        $cabeceras = ['ID Ambiente', 'Código Patrimonial', 'Denominación', 'Marca', 'Modelo', 'Color', 'Serie', 'Estado (BUENO/REGULAR/MALO)', 'Observaciones'];
        $letra = 'A';
        foreach ($cabeceras as $cabecera) {
            $sheet->setCellValue($letra . '1', $cabecera);
            $sheet->getColumnDimension($letra)->setAutoSize(true);
            $sheet->getStyle($letra . '1')->getFont()->setBold(true);
            $letra++;
        }

        // Descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Plantilla_Carga_Masiva_Bienes.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function cargaMasiva()
    {
        if (!\Core\Auth::esAdminInventario()) {
            $_SESSION['flash_error'] = "No tiene permisos.";
            header('Location: ' . BASE_URL . '/inventario/bienes');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_excel'])) {
            $archivo = $_FILES['archivo_excel']['tmp_name'];
            
            if (empty($archivo)) {
                $_SESSION['flash_error'] = "Por favor seleccione un archivo Excel.";
                header('Location: ' . BASE_URL . '/inventario/bienes');
                exit;
            }

            try {
                // Leer el archivo Excel
                $spreadsheet = IOFactory::load($archivo);
                $hoja = $spreadsheet->getActiveSheet();
                $filas = $hoja->toArray();
                
                // Remover la fila de cabeceras
                array_shift($filas); 
                
                $registros_exitosos = 0;
                
                // Bucle de inserción masiva
                foreach ($filas as $fila) {
                    // Validar que la fila no esté completamente vacía
                    if (empty(trim($fila[0])) && empty(trim($fila[1])) && empty(trim($fila[2]))) {
                        continue;
                    }

                    $data = [
                        'id'                 => null, // Inserción nueva
                        'id_inv_ambiente'    => (int)$fila[0],
                        'codigo_patrimonial' => trim($fila[1]),
                        'denominacion'       => trim($fila[2]),
                        'marca'              => trim($fila[3] ?? ''),
                        'modelo'             => trim($fila[4] ?? ''),
                        'color'              => trim($fila[5] ?? ''),
                        'serie'              => trim($fila[6] ?? ''),
                        'estado_bien'        => strtoupper(trim($fila[7] ?? 'BUENO')),
                        'otros'              => '',
                        'observaciones'      => trim($fila[8] ?? '')
                    ];
                    
                    // Solo guarda si tiene código y denominación
                    if (!empty($data['codigo_patrimonial']) && !empty($data['denominacion'])) {
                        $this->model->guardar($data);
                        $registros_exitosos++;
                    }
                }

                $_SESSION['flash_success'] = "Carga masiva completada. Se registraron {$registros_exitosos} bienes exitosamente.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Error al procesar el archivo Excel: " . $e->getMessage();
            }
        }
        
        header('Location: ' . BASE_URL . '/inventario/bienes');
        exit;
    }
} 
