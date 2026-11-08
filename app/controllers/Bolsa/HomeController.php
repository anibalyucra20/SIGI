<?php

namespace App\Controllers\Bolsa;

use Core\Controller;
use Core\Model;
use PDO;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $moduloBolsa = 7; // Bolsa Laboral
        if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa) {
            $tieneRolEnBolsa = false;
            foreach (($_SESSION['sigi_permisos_usuario'] ?? []) as $permiso) {
                if ($permiso['id_sistema'] == $moduloBolsa) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = (int)$permiso['id_rol'];
                    $tieneRolEnBolsa = true;
                    break;
                }
            }
            if (!$tieneRolEnBolsa && isset($_SESSION['sigi_user_id'])) {
                $db = (new Model())->getDB();
                $idUser = (int)$_SESSION['sigi_user_id'];
                $rolUser = (int)$db->query("SELECT id_rol FROM sigi_usuarios WHERE id = $idUser")->fetchColumn();
                if (in_array($rolUser, [1, 7, 8])) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = $rolUser;
                    $tieneRolEnBolsa = true;
                }
            }

            if (!$tieneRolEnBolsa && !\Core\Auth::tieneRolEnBolsa()) {
                $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
                header('Location: ' . BASE_URL . '/intranet');
                exit;
            }
        }
    }

    public function index()
    {
        if (!\Core\Auth::tieneRolEnBolsa()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $db = (new Model())->getDB();

        // 1. Obtener Periodo Académico
        $periodo = 'Actual';
        try {
            $periodoQuery = $db->query("SELECT nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1");
            if ($periodoQuery) {
                $periodo = $periodoQuery->fetchColumn() ?: 'Actual';
            }
        } catch (\PDOException $e) {
            $periodo = 'Actual';
        }

        // 2. Conteo de ofertas vigentes (común para todos)
        $totalOfertas = 0;
        try {
            $totalOfertas = (int)$db->query("SELECT COUNT(*) FROM bolsa_ofertas_laborales WHERE estado = 1")->fetchColumn();
        } catch (\PDOException $e) {
            $totalOfertas = 0;
        }

        // 3. Métricas según rol (Admin vs Estudiante)
        $totalEmpresas = 0;
        $totalPostulaciones = 0;

        if (\Core\Auth::esAdminBolsa()) {
            try {
                $totalEmpresas = (int)$db->query("SELECT COUNT(*) FROM bolsa_empresa WHERE estado = 1")->fetchColumn();
                $totalPostulaciones = (int)$db->query("SELECT COUNT(*) FROM bolsa_postulaciones")->fetchColumn();
            } catch (\PDOException $e) {
                $totalEmpresas = 0;
                $totalPostulaciones = 0;
            }
        } else {
            // Lógica para estudiante: contar solo sus postulaciones
            $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM bolsa_postulaciones WHERE id_usuario = :id");
                $stmt->execute(['id' => $idUsuario]);
                $totalPostulaciones = (int)$stmt->fetchColumn();
            } catch (\PDOException $e) {
                $totalPostulaciones = 0;
            }
        }

        // 4. Renderizar la vista
        $this->view('bolsa/index', [
            'periodo'            => $periodo,
            'totalOfertas'       => $totalOfertas,
            'totalEmpresas'      => $totalEmpresas,
            'totalPostulaciones' => $totalPostulaciones,
            'pageTitle'          => 'Sistema de Bolsa Laboral',
            'module'             => 'bolsa'
        ]);
        exit;
    }
}
