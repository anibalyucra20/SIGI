<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;

use App\Models\Tutoria\Programacion;
use App\Models\Tutoria\EstudianteTutoria;
use App\Models\Tutoria\SesionGrupal;
use App\Models\Tutoria\SesionIndividual;
use App\Models\Tutoria\RecojoInformacion;
use App\Models\Sigi\Docente;

class TutoresController extends Controller
{
    protected $objProgramacion;
    protected $objEstudianteTutoria;
    protected $objSesionGrupal;
    protected $objSesionIndividual;
    protected $objRecojoInformacion;
    protected $objDocente;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a esta sección de Tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->objProgramacion      = new Programacion();
        $this->objEstudianteTutoria = new EstudianteTutoria();
        $this->objSesionGrupal      = new SesionGrupal();
        $this->objSesionIndividual  = new SesionIndividual();
        $this->objRecojoInformacion = new RecojoInformacion();
        $this->objDocente           = new Docente();
    }

    /* =========================================================================
     * 1. SESIONES INDIVIDUALES (Menú: /tutoria/tutores/sesionesIndividuales)
     * ========================================================================= */

    public function sesionesIndividuales($id_programacion = null)
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();
        $esDocente  = Auth::esDocenteTutoria();

        if (!$esAdmin && !$esDocente) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a las sesiones individuales de tutoría.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        // Si es docente tutor y no es admin, fijar su propio filtro
        $id_docente_filtro = null;
        $miProg = null;
        $misTutorandos = [];

        if ($esDocente) {
            $id_docente_filtro = $_SESSION['sigi_user_id'] ?? 0;
            $miProg = $this->objProgramacion->getProgramacionPorDocente($id_docente_filtro, $id_periodo, $id_sede);
            if ($miProg) {
                $misTutorandos = $this->objEstudianteTutoria->listarPorProgramacion($miProg['id']);
            }
        }

        // Obtener lista de tutores programados para el selector de filtro y modal
        $tutores = $this->objProgramacion->getPaginated([
            'id_sede'              => $id_sede,
            'id_periodo_academico' => $id_periodo
        ], 100, 0, 3, 'ASC')['data'] ?? [];

        $this->view('tutoria/tutores/sesionesIndividuales/index', [
            'tutores'          => $tutores,
            'id_programacion'  => $id_programacion,
            'esAdmin'          => $esAdmin,
            'esDocente'        => $esDocente,
            'miProg'           => $miProg,
            'misTutorandos'    => $misTutorandos,
            'module'           => 'tutoria',
            'pageTitle'        => 'Sesiones Individuales de Tutoría'
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para obtener los estudiantes de una programación de tutoría.
     */
    public function tutorandosPorProgramacion($id_programacion)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!Auth::tieneRolEnTutoria()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $id_programacion = (int)$id_programacion;
        $estudiantes = $this->objEstudianteTutoria->listarPorProgramacion($id_programacion);
        echo json_encode($estudiantes, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function sesionesIndividualesData()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw     = $_GET['draw'] ?? 1;
        $start    = $_GET['start'] ?? 0;
        $length   = $_GET['length'] ?? 10;
        $orderCol = $_GET['order'][0]['column'] ?? 2;
        $orderDir = $_GET['order'][0]['dir'] ?? 'desc';

        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'              => $id_sede,
            'id_periodo'           => $id_periodo,
            'id_programacion'      => !empty($_GET['id_programacion']) ? (int)$_GET['id_programacion'] : null,
            'id_docente'           => $id_docente,
            'derivacion'           => isset($_GET['derivacion']) && $_GET['derivacion'] !== '' ? $_GET['derivacion'] : null,
            'buscar'               => trim($_GET['buscar'] ?? '')
        ];

        $result = $this->objSesionIndividual->getPaginated($filters, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* =========================================================================
     * 2. SESIONES GRUPALES (Menú: /tutoria/tutores/sesionesGrupales)
     * ========================================================================= */

    public function sesionesGrupales($id_programacion = null)
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();
        $esDocente  = Auth::esDocenteTutoria();

        if (!$esAdmin && !$esDocente) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a las sesiones grupales de tutoría.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }

        $tutores = $this->objProgramacion->getPaginated([
            'id_sede'              => $id_sede,
            'id_periodo_academico' => $id_periodo
        ], 100, 0, 3, 'ASC')['data'] ?? [];

        // Obtener la programación del usuario si es docente
        $miProg = null;
        if ($esDocente) {
            $miProg = $this->objProgramacion->getProgramacionPorDocente($_SESSION['sigi_user_id'] ?? 0, $id_periodo, $id_sede);
        }

        $this->view('tutoria/tutores/sesionesGrupales/index', [
            'tutores'         => $tutores,
            'miProg'          => $miProg,
            'id_programacion' => $id_programacion,
            'esAdmin'         => $esAdmin,
            'esDocente'       => $esDocente,
            'module'          => 'tutoria',
            'pageTitle'       => 'Sesiones Grupales de Tutoría'
        ]);
        exit;
    }

    public function sesionesGrupalesData()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw     = $_GET['draw'] ?? 1;
        $start    = $_GET['start'] ?? 0;
        $length   = $_GET['length'] ?? 10;
        $orderCol = $_GET['order'][0]['column'] ?? 1;
        $orderDir = $_GET['order'][0]['dir'] ?? 'desc';

        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'         => $id_sede,
            'id_periodo'      => $id_periodo,
            'id_programacion' => !empty($_GET['id_programacion']) ? (int)$_GET['id_programacion'] : null,
            'id_docente'      => $id_docente,
            'tipo'            => trim($_GET['tipo'] ?? '')
        ];

        $result = $this->objSesionGrupal->getPaginated($filters, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* =========================================================================
     * 3. FICHAS DE TUTORÍA (Menú: /tutoria/tutores/fichas)
     * ========================================================================= */

    public function fichas($id_programacion = null)
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        // Buscar tutores programados
        $tutores = $this->objProgramacion->getPaginated([
            'id_sede'              => $id_sede,
            'id_periodo_academico' => $id_periodo
        ], 100, 0, 3, 'ASC')['data'] ?? [];

        // Si no se pasó id_programacion y el usuario es docente, tomar su programación
        if (!$id_programacion && !$esAdmin && Auth::esDocenteTutoria()) {
            $miProg = $this->objProgramacion->getProgramacionPorDocente($_SESSION['sigi_user_id'], $id_periodo, $id_sede);
            $id_programacion = $miProg['id'] ?? null;
        } elseif (!$id_programacion && !empty($tutores[0]['id'])) {
            $id_programacion = $tutores[0]['id'];
        }

        $progActual = $id_programacion ? $this->objProgramacion->find($id_programacion) : null;
        $estudiantes = $id_programacion ? $this->objEstudianteTutoria->listarPorProgramacion($id_programacion) : [];
        $resumenDiag = $id_programacion ? $this->objRecojoInformacion->getResumenPorProgramacion($id_programacion) : [];

        $this->view('tutoria/tutores/fichas/index', [
            'tutores'          => $tutores,
            'id_programacion'  => $id_programacion,
            'progActual'       => $progActual,
            'estudiantes'      => $estudiantes,
            'resumenDiag'      => $resumenDiag,
            'esAdmin'          => $esAdmin,
            'module'           => 'tutoria',
            'pageTitle'        => 'Fichas de Diagnóstico de Tutoría'
        ]);
        exit;
    }
}
