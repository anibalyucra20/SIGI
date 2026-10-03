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

        $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;

        // 1. Obtener datos analíticos y de seguimiento para el docente desde el Modelo Home
        $datosDashboard = $this->model->getDatosDashboardDocente(
            $id_docente,
            $id_periodo,
            $id_sede,
            $this->objCalificaciones
        );

        // 2. Obtener métricas institucionales generales (para directores, coordinadores y administradores)
        $datosInstitucionales = $this->model->getResumenInstitucional();

        // 3. Fusionar datos y renderizar vista
        $vistaData = array_merge($datosDashboard, $datosInstitucionales, [
            'pageTitle' => 'Panel ACADEMICO',
            'module'    => 'academico'
        ]);

        $this->view('academico/index', $vistaData);
    }
}
