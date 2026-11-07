<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use App\Models\Efsrt\Convenio;
use App\Models\Efsrt\Empresa;

class ConveniosController extends Controller
{
    protected $convenioModel;
    protected $empresaModel;

    public function __construct()
    {
        parent::__construct();

        $moduloEfsrt = 11;
        if (!isset($_SESSION['sigi_modulo_actual']) || $_SESSION['sigi_modulo_actual'] != $moduloEfsrt) {
            $_SESSION['sigi_modulo_actual'] = $moduloEfsrt;
            $encontrado = false;
            if (isset($_SESSION['sigi_permisos_usuario']) && is_array($_SESSION['sigi_permisos_usuario'])) {
                foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                    if ($permiso['id_sistema'] == $moduloEfsrt) {
                        $_SESSION['sigi_modulo_actual'] = $permiso['id_sistema'];
                        $_SESSION['sigi_rol_actual']    = $permiso['id_rol'];
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) {
                    foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                        if ($permiso['id_rol'] == 1) {
                            $_SESSION['sigi_rol_actual'] = 1;
                            $encontrado = true;
                            break;
                        } elseif ($permiso['id_rol'] == 7) {
                            $_SESSION['sigi_rol_actual'] = 7;
                            $encontrado = true;
                            break;
                        } elseif ($permiso['id_rol'] == 6) {
                            $_SESSION['sigi_rol_actual'] = 6;
                            $encontrado = true;
                            break;
                        }
                    }
                }
            }
        }

        if (!Auth::tieneRolEnEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder al módulo de EFSRT.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->convenioModel = new Convenio();
        $this->empresaModel = new Empresa();
    }

    public function index()
    {
        $id_empresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $busqueda = isset($_GET['q']) ? trim($_GET['q']) : null;

        $convenios = $this->convenioModel->getAll($id_empresa, $estado, $busqueda);
        $empresas = $this->empresaModel->getAll(null, 1);

        $puedeGestionar = (Auth::esAdminEfsrt() || Auth::esSecretarioPEEfsrt() || Auth::esCoordinadorPEEfsrt() || Auth::esDirectorEfsrt() || Auth::esJUAEfsrt());

        $this->view('efsrt/convenios/index', [
            'module'             => 'efsrt',
            'pageTitle'          => 'Convenios Institucionales - EFSRT',
            'convenios'          => $convenios,
            'empresas'           => $empresas,
            'id_empresa'         => $id_empresa,
            'estado'             => $estado,
            'busqueda'           => $busqueda,
            'puedeGestionar'     => $puedeGestionar
        ]);
    }

    public function guardar()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $id_empresa = (int)($_POST['id_empresa'] ?? 0);
        $numero_convenio = trim($_POST['numero_convenio'] ?? '');
        $tipo_convenio = trim($_POST['tipo_convenio'] ?? 'ESPECIFICO');
        $fecha_firma = trim($_POST['fecha_firma'] ?? '');
        $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
        $fecha_fin = trim($_POST['fecha_fin'] ?? '');
        $descripcion_objeto = !empty($_POST['descripcion_objeto']) ? trim($_POST['descripcion_objeto']) : null;
        $estado = trim($_POST['estado'] ?? 'ACTIVO');

        if (empty($id_empresa) || empty($numero_convenio) || empty($fecha_firma) || empty($fecha_inicio) || empty($fecha_fin)) {
            $_SESSION['flash_error'] = "Por favor complete todos los campos obligatorios del convenio.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        if ($this->convenioModel->existeNumero($numero_convenio, $id)) {
            $_SESSION['flash_error'] = "El número de convenio ingresado ya existe.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        $archivoPdf = null;
        if ($id) {
            $actual = $this->convenioModel->getById($id);
            $archivoPdf = $actual['archivo_convenio_pdf'] ?? null;
        }

        // Subida de archivo PDF
        if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['archivo_pdf'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'pdf') {
                $_SESSION['flash_error'] = "El archivo del convenio debe estar en formato PDF.";
                header('Location: ' . BASE_URL . '/efsrt/convenios');
                exit;
            }

            if ($file['size'] > 15 * 1024 * 1024) { // 15MB max
                $_SESSION['flash_error'] = "El archivo PDF supera el tamaño máximo permitido (15MB).";
                header('Location: ' . BASE_URL . '/efsrt/convenios');
                exit;
            }

            $carpetaDestino = __DIR__ . '/../../../public/uploads/efsrt/convenios/';
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $nombreGuardado = 'convenio_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $numero_convenio) . '_' . time() . '.pdf';
            $rutaFisica = $carpetaDestino . $nombreGuardado;

            if (move_uploaded_file($file['tmp_name'], $rutaFisica)) {
                // Eliminar anterior si existe
                if ($archivoPdf && file_exists($carpetaDestino . $archivoPdf)) {
                    @unlink($carpetaDestino . $archivoPdf);
                }
                $archivoPdf = $nombreGuardado;
            } else {
                $_SESSION['flash_error'] = "Error al subir el archivo PDF del convenio.";
                header('Location: ' . BASE_URL . '/efsrt/convenios');
                exit;
            }
        }

        $datos = [
            'id_empresa' => $id_empresa,
            'numero_convenio' => $numero_convenio,
            'tipo_convenio' => $tipo_convenio,
            'fecha_firma' => $fecha_firma,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'archivo_convenio_pdf' => $archivoPdf,
            'descripcion_objeto' => $descripcion_objeto,
            'estado' => $estado,
            'created_by' => (int)$_SESSION['sigi_user_id']
        ];

        try {
            if ($id) {
                $this->convenioModel->update($id, $datos);
                $_SESSION['flash_success'] = "Convenio actualizado correctamente.";
            } else {
                $this->convenioModel->create($datos);
                $_SESSION['flash_success'] = "Convenio registrado exitosamente.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al guardar convenio: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/convenios');
        exit;
    }

    public function descargarPdf($id)
    {
        $convenio = $this->convenioModel->getById($id);
        if (!$convenio || empty($convenio['archivo_convenio_pdf'])) {
            $_SESSION['flash_error'] = "Archivo de convenio no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        $ruta = __DIR__ . '/../../../public/uploads/efsrt/convenios/' . $convenio['archivo_convenio_pdf'];
        if (!file_exists($ruta)) {
            $_SESSION['flash_error'] = "El archivo físico no existe en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($convenio['archivo_convenio_pdf']) . '"');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit;
    }

    public function cambiarEstado($id)
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/convenios');
            exit;
        }

        $estado = $_POST['estado'] ?? 'ACTIVO';
        $this->convenioModel->cambiarEstado($id, $estado);
        $_SESSION['flash_success'] = "Estado del convenio actualizado.";
        header('Location: ' . BASE_URL . '/efsrt/convenios');
        exit;
    }

    public function getConveniosEmpresaAjax($id_empresa)
    {
        header('Content-Type: application/json');
        $convenios = $this->convenioModel->getActivosPorEmpresa($id_empresa);
        echo json_encode($convenios);
        exit;
    }
}
