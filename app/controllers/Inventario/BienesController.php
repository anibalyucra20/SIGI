<?php
namespace App\Controllers\Inventario;

use Core\Controller;
use App\Models\Inventario\Bienes;

class BienesController extends Controller {
    
    public function index() {
        if (\Core\Auth::esAdminSigi()):
            // Listas iniciales para el filtro principal
        endif;

        $this->view('inventario/bienes/index', [
            'module'    => 'inventario',
            'pageTitle' => 'Gestión de Bienes'
        ]);
        
        exit;
    }

    public function data() {
        // Asegurar que la respuesta sea estrictamente JSON
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $model = new Bienes();
        $filters = [
            'codigo_patrimonial' => $_GET['codigo_patrimonial'] ?? '',
            'denominacion'       => $_GET['denominacion'] ?? '',
            'estado_bien'        => $_GET['estado_bien'] ?? ''
        ];

        $res = $model->getPaginated(
            $filters, 
            $_GET['length'] ?? 10, 
            $_GET['start'] ?? 0, 
            $_GET['order'][0]['column'] ?? 0, 
            $_GET['order'][0]['dir'] ?? 'ASC'
        );

        echo json_encode([
            "draw"            => intval($_GET['draw'] ?? 1),
            "recordsTotal"    => intval($res['total']),
            "recordsFiltered" => intval($res['total']),
            "data"            => $res['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // FORMULARIO NUEVO
    public function nuevo() {
        $this->view('inventario/bienes/nuevo', [
            'module'    => 'inventario',
            'pageTitle' => 'Registrar Nuevo Bien'
        ]);
    }
    
    public function guardar() {
        // Datos limpios sin id_periodo_registro para que coincidan con la tabla
        $data = [
            'id_inv_ambiente'    => $_POST['id_inv_ambiente'] ?? null,
            'codigo_patrimonial' => $_POST['codigo_patrimonial'] ?? '',
            'denominacion'       => $_POST['denominacion'] ?? '',
            'marca'              => $_POST['marca'] ?? '',
            'modelo'             => $_POST['modelo'] ?? '',
            'color'              => $_POST['color'] ?? '',
            'serie'              => $_POST['serie'] ?? '',
            'estado_bien'        => $_POST['estado_bien'] ?? 'BUENO',
            'otros'              => $_POST['otros'] ?? '',
            'observaciones'      => $_POST['observaciones'] ?? ''
        ];

        $model = new Bienes();
        $model->guardar($data);
        
        header('Location: ' . BASE_URL . '/inventario/bienes');
        exit;
    }
}