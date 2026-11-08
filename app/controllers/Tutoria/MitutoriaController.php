<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;

use App\Models\Tutoria\Programacion;

class MitutoriaController extends Controller
{
    protected $objProgramacion;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->objProgramacion = new Programacion();
    }

    public function index()
    {
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;

        $miProg = $this->objProgramacion->getProgramacionPorDocente($id_usuario, $id_periodo, $id_sede);

        if (!$miProg) {
            $_SESSION['flash_error'] = "No tienes una programación de tutoría asignada para el periodo lectivo y sede actuales.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        // Redirigir a la vista de tutorandos de esta programación
        header('Location: ' . BASE_URL . '/tutoria/programacion/tutorandos/' . $miProg['id']);
        exit;
    }
}
