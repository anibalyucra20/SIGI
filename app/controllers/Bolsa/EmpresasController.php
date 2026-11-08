<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

require_once __DIR__ . '/../../models/Bolsa/Empresas.php';

use App\Models\Bolsa\Empresas;

class EmpresasController extends Controller {

    protected $empresasModel;

    public function __construct() {
        parent::__construct();
        $this->empresasModel = new Empresas();

        // Asegurar contexto de Bolsa Laboral (módulo 7)
        $moduloBolsa = 7;
        if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa) {
            foreach (($_SESSION['sigi_permisos_usuario'] ?? []) as $permiso) {
                if ($permiso['id_sistema'] == $moduloBolsa) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = (int)$permiso['id_rol'];
                    break;
                }
            }
            if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa && isset($_SESSION['sigi_user_id'])) {
                $db = (new \Core\Model())->getDB();
                $idUser = (int)$_SESSION['sigi_user_id'];
                $rolUser = (int)$db->query("SELECT id_rol FROM sigi_usuarios WHERE id = $idUser")->fetchColumn();
                if (in_array($rolUser, [1, 7, 8])) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = $rolUser;
                }
            }
        }
    }
    
    /**
     * Vista principal del listado de empresas
     */
    public function index() {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: El módulo de gestión de empresas requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa');
            exit;
        }
        $this->view('bolsa/empresas/index', [
            'module'    => 'bolsa',
            'pageTitle' => 'Empresas'
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para DataTables (Server-side)
     */
    public function data() {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');
        $draw     = $_GET['draw'] ?? 1;
        $start    = $_GET['start'] ?? 0;
        $length   = $_GET['length'] ?? 10;
        $orderCol = $_GET['order'][0]['column'] ?? 3; // Por defecto ordenar por nombre
        $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
        $filters = [
            'ruc'     => $_GET['filter_ruc'] ?? null,
            'empresa' => $_GET['filter_empresa'] ?? null,
            'search'  => $_GET['search']['value'] ?? null
        ];
        $result = $this->empresasModel->obtenerEmpresas($filters, $length, $start, $orderCol, $orderDir);
        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Formulario de creación de una nueva empresa
     */
    public function nuevo()
    {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        $this->view('bolsa/empresas/nuevo', [
            'module'    => 'bolsa',
            'pageTitle' => 'Nueva Empresa',
            'empresa'   => []
        ]);
        exit;
    }

    /**
     * Formulario de edición de empresa existente
     */
    public function editar($id)
    {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        $id = (int)$id;
        $empresa = $this->empresasModel->find($id);
        if (!$empresa || (int)$empresa['estado'] !== 1) {
            $_SESSION['flash_error'] = "Empresa no encontrada o inactiva.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        $this->view('bolsa/empresas/editar', [
            'module'    => 'bolsa',
            'pageTitle' => 'Editar Empresa',
            'empresa'   => $empresa
        ]);
        exit;
    }

    /**
     * Procesa el guardado (Crear o Actualizar)
     */
    public function guardar()
    {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        $id        = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $ruc       = trim($_POST['ruc'] ?? '');
        $empresa   = trim($_POST['empresa'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $estado    = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;
        // Validaciones estrictas
        if (empty($ruc) || empty($empresa) || empty($direccion) || empty($telefono) || empty($email)) {
            $_SESSION['flash_error'] = "Todos los campos marcados con (*) son obligatorios.";
            if ($id) {
                header('Location: ' . BASE_URL . '/bolsa/empresas/editar/' . $id);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/empresas/nuevo');
            }
            exit;
        }

        // Validación de RUC (exactamente 11 dígitos numéricos en Perú)
        if (!preg_match('/^[0-9]{11}$/', $ruc)) {
            $_SESSION['flash_error'] = "El número de RUC debe contener exactamente 11 dígitos numéricos.";
            if ($id) {
                header('Location: ' . BASE_URL . '/bolsa/empresas/editar/' . $id);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/empresas/nuevo');
            }
            exit;
        }
        // Validación de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "El correo electrónico ingresado no tiene un formato válido.";
            if ($id) {
                header('Location: ' . BASE_URL . '/bolsa/empresas/editar/' . $id);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/empresas/nuevo');
            }
            exit;
        }
        // Validación de duplicidad de RUC (campo UNIQUE)
        $existeRuc = $this->empresasModel->findByRuc($ruc, $id);
        if ($existeRuc) {
            $_SESSION['flash_error'] = "El RUC [{$ruc}] ya se encuentra registrado por otra empresa.";
            if ($id) {
                header('Location: ' . BASE_URL . '/bolsa/empresas/editar/' . $id);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/empresas/nuevo');
            }
            exit;
        }

        $idUsuario = $_SESSION['sigi_user_id'] ?? 1;
        $data = [
            'id'         => $id,
            'ruc'        => $ruc,
            'empresa'    => $empresa,
            'direccion'  => $direccion,
            'telefono'   => $telefono,
            'email'      => $email,
            'id_usuario' => $idUsuario,
            'estado'     => $estado
        ];
        try {
            $this->empresasModel->guardar($data);
            $_SESSION['flash_success'] = $id ? "Empresa actualizada correctamente." : "Empresa registrada exitosamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al procesar la operación: " . $e->getMessage();
            if ($id) {
                header('Location: ' . BASE_URL . '/bolsa/empresas/editar/' . $id);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/empresas/nuevo');
            }
            exit;
        }
        header('Location: ' . BASE_URL . '/bolsa/empresas');
        exit;
    }

    /**
     * Eliminación lógica (cambia estado = 0)
     */
    public function eliminar($id)
    {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        $id = (int)$id;
        $empresa = $this->empresasModel->find($id);
        if (!$empresa) {
            $_SESSION['flash_error'] = "La empresa no existe.";
            header('Location: ' . BASE_URL . '/bolsa/empresas');
            exit;
        }
        try {
            $this->empresasModel->eliminar($id);
            $_SESSION['flash_success'] = "Empresa dada de baja correctamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo eliminar la empresa: verifique si tiene registros asociados.";
        }
        header('Location: ' . BASE_URL . '/bolsa/empresas');
        exit;
    }

}
