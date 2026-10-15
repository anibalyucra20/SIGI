<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\Supervisor;
use PDO;

class SupervisoresController extends Controller
{
    protected $supervisorModel;

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

        $this->supervisorModel = new Supervisor();
    }

    public function index()
    {
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $id_sede = $_SESSION['sigi_sede_actual'] ?? null;

        $db = (new Model())->getDB();
        $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sedes = $db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $filtroPeriodo = isset($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : $id_periodo;
        $filtroSede = isset($_GET['id_sede']) ? (int)$_GET['id_sede'] : $id_sede;
        $filtroPrograma = isset($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        // Si es coordinador, filtrar por sus programas asignados
        if (Auth::esCoordinadorPEEfsrt() && !Auth::esAdminEfsrt()) {
            $user_id = $_SESSION['sigi_user_id'];
            $stmtProg = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$user_id, $filtroPeriodo]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($progsCoord) && !$filtroPrograma) {
                $filtroPrograma = $progsCoord[0];
            }
        }

        $supervisores = $this->supervisorModel->getAll($filtroPeriodo, $filtroPrograma, $filtroSede);
        $docentes = $this->supervisorModel->buscarDocentesDisponibles($filtroPeriodo, $filtroSede);

        $this->view('efsrt/supervisores/index', [
            'module'             => 'efsrt',
            'pageTitle'        => 'Docentes Supervisores EFSRT',
            'supervisores'     => $supervisores,
            'docentes'         => $docentes,
            'periodos'         => $periodos,
            'sedes'            => $sedes,
            'programas'        => $programas,
            'filtroPeriodo'    => $filtroPeriodo,
            'filtroSede'       => $filtroSede,
            'filtroPrograma'   => $filtroPrograma,
            'puedeGestionar'   => (Auth::esAdminEfsrt() || Auth::esDirectorEfsrt() || Auth::esJUAEfsrt() || Auth::esSecretarioPEEfsrt() || Auth::esCoordinadorPEEfsrt())
        ]);
    }

    public function guardar()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para asignar docentes supervisores.";
            header('Location: ' . BASE_URL . '/efsrt/supervisores');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/supervisores');
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $id_docente = (int)($_POST['id_docente'] ?? 0);
        $id_periodo = (int)($_POST['id_periodo_academico'] ?? 0);
        $id_programa = (int)($_POST['id_programa_estudios'] ?? 0);
        $id_sede = (int)($_POST['id_sede'] ?? 0);
        $resolucion = !empty($_POST['resolucion_designacion']) ? trim($_POST['resolucion_designacion']) : null;
        $max_estudiantes = !empty($_POST['max_estudiantes']) ? (int)$_POST['max_estudiantes'] : 30;

        if (empty($id_docente) || empty($id_periodo) || empty($id_programa) || empty($id_sede)) {
            $_SESSION['flash_error'] = "Por favor complete todos los datos del docente y programa.";
            header('Location: ' . BASE_URL . '/efsrt/supervisores');
            exit;
        }

        $datos = [
            'id_docente' => $id_docente,
            'id_periodo_academico' => $id_periodo,
            'id_programa_estudios' => $id_programa,
            'id_sede' => $id_sede,
            'resolucion_designacion' => $resolucion,
            'max_estudiantes' => $max_estudiantes,
            'estado' => 1
        ];

        try {
            if ($id) {
                $this->supervisorModel->update($id, $datos);
                $_SESSION['flash_success'] = "Asignación de supervisor actualizada correctamente.";
            } else {
                $this->supervisorModel->create($datos);
                $_SESSION['flash_success'] = "Docente supervisor asignado exitosamente.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al guardar supervisor: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/supervisores');
        exit;
    }

    public function cambiarEstado($id)
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/supervisores');
            exit;
        }

        $sup = $this->supervisorModel->getById($id);
        if ($sup) {
            $nuevo = ($sup['estado'] == 1) ? 0 : 1;
            $this->supervisorModel->cambiarEstado($id, $nuevo);
            $_SESSION['flash_success'] = "Estado de supervisor actualizado.";
        } else {
            $_SESSION['flash_error'] = "Supervisor no encontrado.";
        }

        header('Location: ' . BASE_URL . '/efsrt/supervisores');
        exit;
    }

    public function getSupervisoresPorProgramaAjax($id_programa, $id_periodo = null)
    {
        header('Content-Type: application/json');
        if (!$id_periodo) {
            $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        }
        $supervisores = $this->supervisorModel->getAll($id_periodo, $id_programa);
        echo json_encode($supervisores);
        exit;
    }
}
