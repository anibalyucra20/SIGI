<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;
use Core\Model;

use App\Models\Tutoria\Programacion;
use App\Models\Tutoria\EstudianteTutoria;
use App\Models\Tutoria\SesionGrupal;
use App\Models\Tutoria\SesionIndividual;

class HomeController extends Controller
{
    protected $objProgramacion;
    protected $objEstudianteTutoria;
    protected $objSesionGrupal;
    protected $objSesionIndividual;

    public function __construct()
    {
        parent::__construct();

        $moduloTutoria = 3;   // ID del sistema TUTORIA
        if ($_SESSION['sigi_modulo_actual'] != $moduloTutoria) {
            foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                if ($permiso['id_sistema'] == $moduloTutoria) {
                    $_SESSION['sigi_modulo_actual'] = $permiso['id_sistema'];
                    $_SESSION['sigi_rol_actual']    = $permiso['id_rol'];
                    break;
                }
            }
            if (!Auth::tieneRolEnTutoria()) {
                $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
                header('Location: ' . BASE_URL . '/intranet');
                exit;
            }
        }

        $this->objProgramacion      = new Programacion();
        $this->objEstudianteTutoria = new EstudianteTutoria();
        $this->objSesionGrupal      = new SesionGrupal();
        $this->objSesionIndividual  = new SesionIndividual();
    }

    public function index()
    {
        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;

        $db = (new Model())->getDB();

        // 1. Nombre de periodo actual
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . (int)$id_periodo)
            ->fetchColumn() ?: 'Periodo Lectivo Actual';

        // 2. Métricas institucionales
        $totalTutores      = $this->objProgramacion->contarTutoresActivos($id_periodo, $id_sede);
        $totalTutorandos    = $this->objProgramacion->contarTutorandosTotales($id_periodo, $id_sede);
        $totalSesionesInd  = $this->objSesionIndividual->contarPorPeriodoSede($id_periodo, $id_sede);
        $totalSesionesGrup = $this->objSesionGrupal->contarPorPeriodoSede($id_periodo, $id_sede);

        // 3. Si el usuario logueado es docente, verificar si tiene tutoría asignada
        $esDocente = Auth::esDocenteTutoria();
        $esAdmin   = Auth::esAdminTutoria();
        $miProg    = null;
        $misTutorandos = [];

        if ($esDocente) {
            $miProg = $this->objProgramacion->getProgramacionPorDocente($id_usuario, $id_periodo, $id_sede);
            if ($miProg) {
                $misTutorandos = $this->objEstudianteTutoria->listarPorProgramacion($miProg['id']);
            }
        }

        $this->view('tutoria/index', [
            'periodo'               => $periodoNombre,
            'totalTutores'          => $totalTutores,
            'totalTutorandos'       => $totalTutorandos,
            'totalSesionesInd'      => $totalSesionesInd,
            'totalSesionesGrup'     => $totalSesionesGrup,
            'esDocente'             => $esDocente,
            'esAdmin'               => $esAdmin,
            'miProg'                => $miProg,
            'misTutorandos'         => $misTutorandos,
            'pageTitle'             => 'Panel de Tutoría - IESP Huanta',
            'module'                => 'tutoria'
        ]);
    }
}
