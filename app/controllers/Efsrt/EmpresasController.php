<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use App\Models\Efsrt\Empresa;

class EmpresasController extends Controller
{
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

        $this->empresaModel = new Empresa();
    }

    public function index()
    {
        $busqueda = isset($_GET['q']) ? trim($_GET['q']) : null;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;

        $empresas = $this->empresaModel->getAll($busqueda, $estado);

        $puedeGestionar = (Auth::esAdminEfsrt() || Auth::esSecretarioPEEfsrt() || Auth::esCoordinadorPEEfsrt() || Auth::esDirectorEfsrt() || Auth::esJUAEfsrt());

        $this->view('efsrt/empresas/index', [
            'module'             => 'efsrt',
            'pageTitle'          => 'Gestión de Empresas e Instituciones - EFSRT',
            'empresas'           => $empresas,
            'busqueda'           => $busqueda,
            'estado'             => $estado,
            'puedeGestionar'     => $puedeGestionar
        ]);
    }

    public function guardar()
    {
        // Solo roles administrativos y coordinadores pueden registrar/editar empresas
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/empresas');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/empresas');
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $ruc = !empty($_POST['ruc']) ? trim($_POST['ruc']) : null;
        $razon_social = trim($_POST['razon_social'] ?? '');
        $nombre_comercial = !empty($_POST['nombre_comercial']) ? trim($_POST['nombre_comercial']) : null;
        $direccion = trim($_POST['direccion'] ?? '');
        $distrito = !empty($_POST['distrito']) ? trim($_POST['distrito']) : null;
        $provincia = !empty($_POST['provincia']) ? trim($_POST['provincia']) : null;
        $departamento = !empty($_POST['departamento']) ? trim($_POST['departamento']) : null;
        $representante_legal = trim($_POST['representante_legal'] ?? '');
        $cargo_representante = !empty($_POST['cargo_representante']) ? trim($_POST['cargo_representante']) : null;
        $contacto_telefono = !empty($_POST['contacto_telefono']) ? trim($_POST['contacto_telefono']) : null;
        $contacto_correo = !empty($_POST['contacto_correo']) ? trim($_POST['contacto_correo']) : null;
        $rubro_sector = !empty($_POST['rubro_sector']) ? trim($_POST['rubro_sector']) : null;

        if (empty($razon_social) || empty($direccion) || empty($representante_legal)) {
            $_SESSION['flash_error'] = "Por favor complete los campos obligatorios (Razón Social, Dirección y Representante Legal).";
            header('Location: ' . BASE_URL . '/efsrt/empresas');
            exit;
        }

        if (!empty($ruc)) {
            if ($this->empresaModel->existeRuc($ruc, $id)) {
                $_SESSION['flash_error'] = "El RUC ingresado ya se encuentra registrado por otra empresa.";
                header('Location: ' . BASE_URL . '/efsrt/empresas');
                exit;
            }
        }

        $datos = [
            'ruc' => $ruc,
            'razon_social' => $razon_social,
            'nombre_comercial' => $nombre_comercial,
            'direccion' => $direccion,
            'distrito' => $distrito,
            'provincia' => $provincia,
            'departamento' => $departamento,
            'representante_legal' => $representante_legal,
            'cargo_representante' => $cargo_representante,
            'contacto_telefono' => $contacto_telefono,
            'contacto_correo' => $contacto_correo,
            'rubro_sector' => $rubro_sector,
            'estado' => 1
        ];

        try {
            if ($id) {
                $this->empresaModel->update($id, $datos);
                $_SESSION['flash_success'] = "Empresa actualizada correctamente.";
            } else {
                $this->empresaModel->create($datos);
                $_SESSION['flash_success'] = "Empresa registrada exitosamente.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al guardar empresa: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/empresas');
        exit;
    }

    public function cambiarEstado($id)
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/empresas');
            exit;
        }

        $empresa = $this->empresaModel->getById($id);
        if ($empresa) {
            $nuevoEstado = ($empresa['estado'] == 1) ? 0 : 1;
            $this->empresaModel->cambiarEstado($id, $nuevoEstado);
            $_SESSION['flash_success'] = "Estado de empresa modificado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "Empresa no encontrada.";
        }

        header('Location: ' . BASE_URL . '/efsrt/empresas');
        exit;
    }

    public function buscarAjax()
    {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $empresas = $this->empresaModel->getAll($q, 1);
        echo json_encode($empresas);
        exit;
    }
}
