<?php

namespace App\Controllers\Academico;

use Core\Controller;
use Core\Model;
use PDO;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $moduloAcademico = 2;   // ID del sistema ACADEMICO (ajusta si es diferente)
        $rolAdmin   = 1;   // ID del rol ADMINISTRADOR (ajusta si es diferente)
        // Si el contexto NO es ACADEMICO
        if ($_SESSION['sigi_modulo_actual'] != $moduloAcademico) {
            // Buscar si tiene algún rol en ACADEMICO
            $tieneRolEnAcademico = false;
            foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                if ($permiso['id_sistema'] == $moduloAcademico) {
                    // Cambia el contexto al primer rol disponible en SIGI
                    $_SESSION['sigi_modulo_actual'] = $permiso['id_sistema'];
                    $_SESSION['sigi_rol_actual']    = $permiso['id_rol'];
                    $tieneRolEnAcademico = true;
                    break;
                }
            }
            /*if (!$tieneRolEnSigi) {
                $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
                header('Location: ' . BASE_URL . '/intranet');
                exit;
            }*/
            if (!\Core\Auth::tieneRolEnAcademico()) {
                $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
                header('Location: ' . BASE_URL . '/intranet');
                exit;
            }
        }
    }

    public function index()
    {
        if (!\Core\Auth::tieneRolEnAcademico()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $db = (new Model())->getDB();
        // 1. Periodo actual
        $periodo = $db->query("SELECT nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")
            ->fetchColumn();
        // 2. Cantidad de sedes
        $sedes_count = $db->query("SELECT COUNT(*) FROM sigi_sedes")->fetchColumn();
        // 3. Cantidad de programas de estudio
        $programas = $db->query("SELECT COUNT(*) FROM sigi_programa_estudios")->fetchColumn();
        // 4. Cantidad de docentes (rol docente)
        $docentes = $db->query("SELECT COUNT(*) FROM sigi_usuarios WHERE id_rol IN (SELECT id FROM sigi_roles WHERE nombre LIKE '%DOCENTE%')")->fetchColumn();

        $this->view('academico/index', [
            'periodo'   => $periodo,
            'sedes_count'     => $sedes_count,
            'programas' => $programas,
            'docentes'  => $docentes,
            'pageTitle' => 'Panel ACADEMICO',
            'module'    => 'academico'
        ]);
    }
}
