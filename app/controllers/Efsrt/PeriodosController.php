<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\PeriodoConfig;
use PDO;

class PeriodosController extends Controller
{
    protected $configModel;

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

        $this->configModel = new PeriodoConfig();
    }

    public function index()
    {
        // Solo Admin, Secretario Académico, JUA o Director pueden gestionar calendarios de subida
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "Acceso restringido a administradores y secretaría académica.";
            header('Location: ' . BASE_URL . '/efsrt');
            exit;
        }

        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $id_sede = $_SESSION['sigi_sede_actual'] ?? null;

        $db = (new Model())->getDB();
        $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sedes = $db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $filtroPeriodo = isset($_GET['id_periodo']) ? ($_GET['id_periodo'] !== '' ? (int)$_GET['id_periodo'] : null) : $id_periodo;
        $filtroSede = isset($_GET['id_sede']) ? ($_GET['id_sede'] !== '' ? (int)$_GET['id_sede'] : null) : null;

        $configuraciones = $this->configModel->getAll($filtroPeriodo, $filtroSede);

        $this->view('efsrt/periodos/index', [
            'module'             => 'efsrt',
            'pageTitle'        => 'Cronograma y Fechas Límite EFSRT',
            'configuraciones'  => $configuraciones,
            'periodos'         => $periodos,
            'sedes'            => $sedes,
            'programas'        => $programas,
            'filtroPeriodo'    => $filtroPeriodo,
            'filtroSede'       => $filtroSede
        ]);
    }

    public function guardar()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para configurar fechas de subida.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $id_periodo = (int)($_POST['id_periodo_academico'] ?? 0);
        $id_sede = (int)($_POST['id_sede'] ?? 0);
        $id_programa = !empty($_POST['id_programa_estudios']) ? (int)$_POST['id_programa_estudios'] : null;
        $raw_apertura_estudiante = trim($_POST['fecha_apertura_estudiante'] ?? '');
        $raw_cierre_estudiante = trim($_POST['fecha_cierre_estudiante'] ?? '');
        $raw_apertura_supervisor = trim($_POST['fecha_apertura_supervisor'] ?? '');
        $raw_cierre_supervisor = trim($_POST['fecha_cierre_supervisor'] ?? '');
        $permite_extemporaneo = isset($_POST['permite_extemporaneo']) ? 1 : 0;
        $estado = trim($_POST['estado'] ?? 'HABILITADO');

        if (empty($id_periodo) || empty($id_sede) || empty($raw_apertura_estudiante) || empty($raw_cierre_estudiante) || empty($raw_apertura_supervisor) || empty($raw_cierre_supervisor)) {
            $_SESSION['flash_error'] = "Por favor complete todas las fechas de apertura y cierre.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        $fecha_apertura_estudiante = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_apertura_estudiante)));
        $fecha_cierre_estudiante   = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_cierre_estudiante)));
        $fecha_apertura_supervisor = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_apertura_supervisor)));
        $fecha_cierre_supervisor   = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $raw_cierre_supervisor)));

        if ($fecha_cierre_estudiante < $fecha_apertura_estudiante) {
            $_SESSION['flash_error'] = "La fecha de cierre de estudiantes debe ser posterior a la fecha de apertura.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        if ($fecha_cierre_supervisor < $fecha_apertura_supervisor) {
            $_SESSION['flash_error'] = "La fecha de cierre de supervisores debe ser posterior a la fecha de apertura.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        $datos = [
            'id' => $id,
            'id_periodo_academico' => $id_periodo,
            'id_sede' => $id_sede,
            'id_programa_estudios' => $id_programa,
            'fecha_apertura_estudiante' => $fecha_apertura_estudiante,
            'fecha_cierre_estudiante' => $fecha_cierre_estudiante,
            'fecha_apertura_supervisor' => $fecha_apertura_supervisor,
            'fecha_cierre_supervisor' => $fecha_cierre_supervisor,
            'permite_extemporaneo' => $permite_extemporaneo,
            'estado' => $estado,
            'configurado_por' => (int)$_SESSION['sigi_user_id']
        ];

        try {
            $this->configModel->save($datos);
            $_SESSION['flash_success'] = "Cronograma de fechas guardado exitosamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al guardar cronograma: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/periodos');
        exit;
    }

    public function cambiarEstado($id)
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        $nuevoEstado = $_POST['estado'] ?? 'HABILITADO';
        $this->configModel->cambiarEstado((int)$id, $nuevoEstado);
        $_SESSION['flash_success'] = "Estado del cronograma actualizado.";
        header('Location: ' . BASE_URL . '/efsrt/periodos');
        exit;
    }

    public function eliminar($id)
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para eliminar este cronograma.";
            header('Location: ' . BASE_URL . '/efsrt/periodos');
            exit;
        }

        $this->configModel->eliminar((int)$id);
        $_SESSION['flash_success'] = "Configuración de cronograma eliminada.";
        header('Location: ' . BASE_URL . '/efsrt/periodos');
        exit;
    }
}
