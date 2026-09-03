<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

require_once __DIR__ . '/../../models/Bolsa/Ofertas.php';
require_once __DIR__ . '/../../models/Bolsa/Empresas.php';
require_once __DIR__ . '/../../models/Sigi/Programa.php';

use App\Models\Bolsa\Ofertas;
use App\Models\Bolsa\Empresas;
use App\Models\Sigi\Programa;

class OfertasController extends Controller {

    protected $ofertasModel;
    protected $empresaModel;
    protected $programaModel;

    public function __construct() {
        parent::__construct();
        $this->ofertasModel = new Ofertas();
        $this->empresaModel = new Empresas();
        $this->programaModel = new Programa();
    }

    public function index() {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }

        $empresas = $this->empresaModel->listar();
        $programas = $this->programaModel->getTodosProgramas();

        $this->view('bolsa/ofertas/index', [
            'module'    => 'bolsa',
            'pageTitle' => 'Ofertas',
            'empresas'  => $empresas,
            'programas' => $programas
        ]);
        exit;
    }

    public function data() {
        if (\Core\Auth::esAdminBolsa()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'id_empresa'       => $_GET['filter_empresa'] ?? null,
                'programa_estudio' => $_GET['filter_programa'] ?? null,
            ];

            $result = $this->ofertasModel->ObtenerOfertas($filters, $length, $start, $orderCol, $orderDir);

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

    public function nuevo() {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        
        $empresas = $this->empresaModel->listar();
        $programas = $this->programaModel->getTodosProgramas();

        $this->view('bolsa/ofertas/nuevo', [
            'isEdit'    => false,
            'module'    => 'bolsa',
            'pageTitle' => 'Nueva Oferta',
            'empresas'  => $empresas,
            'programas' => $programas,
            'oferta'    => []
        ]);
        exit;
    }

    public function editar($id) {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        $empresas = $this->empresaModel->listar();
        $programas = $this->programaModel->getTodosProgramas();
        $data = $this->ofertasModel->find($id);
        if (!$data) {
            $_SESSION['flash_error'] = "Oferta no encontrada.";
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        $this->view('bolsa/ofertas/editar', [
            'oferta' => $data,
            'empresas'  => $empresas,
            'programas' => $programas,
            'isEdit'    => true,
            'module' => 'bolsa',
            'pageTitle' => 'Editar Oferta',
        ]);
        exit;
    }

    public function guardar() {
        if (\Core\Auth::esAdminBolsa()):    
            $id = $_POST['id'] ?? '';

            // Carpeta física donde se guardan las imágenes
            $carpeta = dirname(__DIR__, 3) . '/public/images/ofertas_bolsa_laboral/';

            // Crear carpeta si no existe
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }

            //OBTENER IMAGEN ACTUAL
            $ofertaActual = null;
            $fotoAnterior = null;

            if (!empty($id)) {
                $ofertaActual = $this->ofertasModel->find($id);

                if (!$ofertaActual) {
                    $_SESSION['flash_error'] = "Oferta no encontrada.";
                    header('Location: ' . BASE_URL . '/bolsa/ofertas');
                    exit;
                }

                $fotoAnterior = $ofertaActual['foto'] ?? null;
            }

            //MANTENER IMAGEN ACTUAL POR DEFECTO

            $fotoRuta = $fotoAnterior;


            //SI SE SELECCIONÓ UNA NUEVA IMAGEN
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

                $archivo = $_FILES['foto'];

                // Extensiones permitidas
                $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

                $extension = strtolower(
                    pathinfo($archivo['name'], PATHINFO_EXTENSION)
                );

                if (!in_array($extension, $extensionesPermitidas)) {
                    die('Formato de imagen no permitido.');
                }

                // Validar que realmente sea una imagen
                $tipoImagen = getimagesize($archivo['tmp_name']);

                if ($tipoImagen === false) {
                    die('El archivo seleccionado no es una imagen válida.');
                }

                // Generar nombre único
                $nombreArchivo = 'oferta_' . uniqid() . '.' . $extension;

                // Ruta física de la nueva imagen
                $rutaFisica = $carpeta . $nombreArchivo;

                // Guardar nueva imagen
                if (!move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
                    die('No se pudo guardar la imagen.');
                }

                // Ruta que se guardará en BD
                $fotoRuta = 'images/ofertas_bolsa_laboral/' . $nombreArchivo;

                //ELIMINAR IMAGEN ANTERIOR

                if (!empty($fotoAnterior)) {
                    $rutaAnterior = dirname(__DIR__, 3) . '/public/' . $fotoAnterior;
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
            }
            $data = [
                'id'                => $_POST['id'],
                'id_empresa'        => $_POST['id_empresa'],
                'programa_estudio'  => $_POST['programa_estudio'],
                'titulo'            => $_POST['titulo'],
                'detalle'           => $_POST['detalle'],
                'fecha_publicacion' => $_POST['fecha_publicacion'],
                'fecha_cierre'      => $_POST['fecha_cierre'],
                'salario'           => $_POST['salario'],
                'requisitos'        => $_POST['requisitos'],
                'ubicacion'         => $_POST['ubicacion'],
                'tipo_contrato'     => $_POST['tipo_contrato'],
                'foto'              => $fotoRuta,
                'estado'            => $_POST['estado']
            ];
            $this->ofertasModel->guardar($data);
        endif;
        header('Location: ' . BASE_URL . '/bolsa/ofertas');
        exit;
    }

    public function eliminar($id) {
        if (\Core\Auth::esAdminBolsa()):
            try {
                $this->ofertasModel->eliminar($id);
                $_SESSION['flash_success'] = "Oferta eliminada exitosamente.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "No se pudo eliminar: verificar que no tenga registros asociados." /*. $e->getMessage()*/;
            }
        else:
            $_SESSION['flash_error'] = "Error: No tiene permisos para realizar esta acción.";
        endif;
        header('Location: ' . BASE_URL . '/bolsa/ofertas');
        exit;
    }
}