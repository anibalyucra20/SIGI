<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../models/Academico/Home.php';
require_once __DIR__ . '/../../models/Academico/Calificaciones.php';

use App\Models\Academico\Home;
use App\Models\Academico\Calificaciones;

class HomeController extends Controller
{
    protected $model;
    protected $objCalificaciones;

    public function __construct()
    {
        parent::__construct();

        $moduloAcademico = 2;   // ID del sistema ACADEMICO
        if ($_SESSION['sigi_modulo_actual'] != $moduloAcademico) {
            foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                if ($permiso['id_sistema'] == $moduloAcademico) {
                    $_SESSION['sigi_modulo_actual'] = $permiso['id_sistema'];
                    $_SESSION['sigi_rol_actual']    = $permiso['id_rol'];
                    break;
                }
            }
            if (!\Core\Auth::tieneRolEnAcademico()) {
                $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
                header('Location: ' . BASE_URL . '/intranet');
                exit;
            }
        }

        $this->model = new Home();
        $this->objCalificaciones = new Calificaciones();
    }

    public function index()
    {
        if (!\Core\Auth::tieneRolEnAcademico()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $periodoFiltro = isset($_GET['periodo_filtro']) ? (int)$_GET['periodo_filtro'] : $id_periodo;
        $programaFiltro = (isset($_GET['programa_filtro']) && $_GET['programa_filtro'] !== 'todos' && is_numeric($_GET['programa_filtro'])) ? (int)$_GET['programa_filtro'] : null;

        // 1. Obtener datos analíticos y de seguimiento para el docente desde el Modelo Home
        $datosDashboard = $this->model->getDatosDashboardDocente(
            $id_usuario,
            $id_periodo,
            $id_sede,
            $this->objCalificaciones
        );

        // 2. Detección de Roles Académicos
        $esDirector = \Core\Auth::esDirectorAcademico();
        $esJUA = \Core\Auth::esJUAAcademico();
        $esAdmin = \Core\Auth::esAdminAcademico();
        $esAutoridad = $esDirector || $esJUA || $esAdmin;
        $esCoordinador = \Core\Auth::esCoordinadorPEAcademico() && !$esAutoridad;

        $datosAutoridad = [];
        $datosCoordinador = [];
        $programasReportes = [];

        if ($esAutoridad) {
            // Dashboard institucional para Jefe de Unidad Académica y Director (y Administrador)
            $datosAutoridad = $this->model->getDatosDashboardAutoridad(
                $id_usuario,
                $id_periodo,
                $id_sede,
                $periodoFiltro,
                $this->objCalificaciones,
                $programaFiltro
            );
            $programasReportes = $this->model->getProgramasInstitucion($id_sede);
        } elseif ($esCoordinador) {
            // Dashboard específico para Coordinador de Programa de Estudios
            $datosCoordinador = $this->model->getDatosDashboardCoordinador(
                $id_usuario,
                $id_periodo,
                $id_sede,
                $periodoFiltro,
                $this->objCalificaciones
            );
            $programasReportes = !empty($datosCoordinador['programasCoordinador']) 
                ? $datosCoordinador['programasCoordinador'] 
                : [];
        } else {
            $datosCoordinador = [
                'esCoordinador' => false,
                'programasCoordinador' => [],
                'planesCoordinador' => [],
                'periodosAcademicos' => [],
                'semestresCatalogo' => [],
                'unidadesTablaCoord' => [],
                'periodoFiltroSeleccionado' => $id_periodo
            ];
        }

        // Título descriptivo según el rol activo
        $tituloRol = 'Panel Académico';
        if ($esDirector) {
            $tituloRol = 'Panel de Dirección Académica';
        } elseif ($esJUA) {
            $tituloRol = 'Panel de Jefatura de Unidad Académica';
        } elseif ($esAdmin) {
            $tituloRol = 'Panel de Administración Académica';
        } elseif ($esCoordinador) {
            $tituloRol = 'Panel de Coordinación / Jefatura de Área';
        }

        // 3. Obtener métricas institucionales generales
        $datosInstitucionales = $this->model->getResumenInstitucional();

        // 4. Fusionar datos y renderizar vista
        $vistaData = array_merge($datosDashboard, $datosCoordinador, $datosAutoridad, $datosInstitucionales, [
            'pageTitle'         => 'Panel ACADEMICO',
            'module'            => 'academico',
            'esAutoridad'       => $esAutoridad,
            'esDirector'        => $esDirector,
            'esJUA'             => $esJUA,
            'esCoordinador'     => $esCoordinador,
            'esCoordinadorRol'  => $esCoordinador, // retrocompatibilidad
            'tituloRol'         => $tituloRol,
            'programas'         => $programasReportes,
            'periodoFiltro'     => $periodoFiltro,
            'programaFiltro'    => $programaFiltro,
            'id_periodo'        => $id_periodo
        ]);

        $this->view('academico/index', $vistaData);
    }
}
