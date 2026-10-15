<?php

namespace App\Controllers\Evaluacion;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Evaluacion\Encuesta;
use App\Models\Evaluacion\EvaluacionEstudiante;
use App\Models\Evaluacion\Reporte;
use PDO;

class HomeController extends Controller
{
    protected $encuestaModel;
    protected $evaluacionEstudianteModel;
    protected $reporteModel;

    public function __construct()
    {
        parent::__construct();

        $moduloEvaluacion = 12; // ID del sistema EVALUACIÓN DOCENTE

        if (!isset($_SESSION['sigi_modulo_actual']) || $_SESSION['sigi_modulo_actual'] != $moduloEvaluacion) {
            $_SESSION['sigi_modulo_actual'] = $moduloEvaluacion;
            $encontrado = false;
            if (isset($_SESSION['sigi_permisos_usuario']) && is_array($_SESSION['sigi_permisos_usuario'])) {
                foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                    if ($permiso['id_sistema'] == $moduloEvaluacion) {
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
                        } elseif ($permiso['id_rol'] == 5) {
                            $_SESSION['sigi_rol_actual'] = 5;
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

        if (!Auth::tieneRolEnEvaluacion()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder al módulo de Evaluación Docente.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->encuestaModel = new Encuesta();
        $this->evaluacionEstudianteModel = new EvaluacionEstudiante();
        $this->reporteModel = new Reporte();
    }

    public function index()
    {
        $idUser     = $_SESSION['sigi_user_id'];
        $idPeriodo  = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $idSede     = $_SESSION['sigi_sede_actual'] ?? null;
        $db         = (new Model())->getDB();

        // Obtener nombre del periodo académico
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo) . " LIMIT 1")->fetchColumn() ?: 'Periodo Vigente';

        $esEstudiante  = Auth::esEstudianteEvaluacion();
        $esDocente     = Auth::esDocenteEvaluacion();
        $esCoordinador = Auth::esCoordinadorPEEvaluacion();
        $esAdmin       = Auth::esAdminEvaluacion();

        // 1. VISTA DE ESTUDIANTE
        if ($esEstudiante) {
            $evaluaciones = $this->evaluacionEstudianteModel->obtenerPorEstudiante($idUser, $idPeriodo);

            $totalAsignadas   = count($evaluaciones);
            $totalCompletadas = 0;
            $totalPendientes  = 0;

            foreach ($evaluaciones as $ev) {
                if ($ev['estado'] === 'COMPLETADO') {
                    $totalCompletadas++;
                } else {
                    $totalPendientes++;
                }
            }

            $porcentajeAvance = $totalAsignadas > 0 ? round(($totalCompletadas / $totalAsignadas) * 100) : 0;

            return $this->view('evaluacion/estudiante/index', [
                'pageTitle'        => 'Mis Evaluaciones Docentes',
                'module'           => 'evaluacion',
                'periodoNombre'    => $periodoNombre,
                'evaluaciones'     => $evaluaciones,
                'totalAsignadas'   => $totalAsignadas,
                'totalCompletadas' => $totalCompletadas,
                'totalPendientes'  => $totalPendientes,
                'porcentajeAvance' => $porcentajeAvance
            ]);
        }

        // 2. VISTA DE DOCENTE
        if ($esDocente && !$esCoordinador && !$esAdmin) {
            $detalleDocente = $this->reporteModel->obtenerDetalleDocente($idUser, $idPeriodo);

            return $this->view('evaluacion/docente/index', [
                'pageTitle'      => 'Mis Resultados de Evaluación Docente',
                'module'         => 'evaluacion',
                'periodoNombre'  => $periodoNombre,
                'detalleDocente' => $detalleDocente
            ]);
        }

        // 3. VISTA DE COORDINADOR (Solo rol 5 exclusivo)
        if (Auth::esCoordinadorExclusivoEvaluacion()) {
            $stmtProg = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN) ?: [-1];
            $idProgCoord = $progsCoord[0] ?? null;

            $encuestas = $this->encuestaModel->listar($idPeriodo, $idProgCoord);
            $docentes  = $this->reporteModel->obtenerResumenPorDocente($idPeriodo, $idProgCoord);
            $pendientes = $this->evaluacionEstudianteModel->obtenerEstudiantesPendientes([
                'id_periodo'  => $idPeriodo,
                'id_programa' => $idProgCoord
            ]);

            $totalAsignadas   = 0;
            $totalCompletadas = 0;
            foreach ($encuestas as $e) {
                $totalAsignadas   += (int)$e['total_asignadas'];
                $totalCompletadas += (int)$e['total_completadas'];
            }
            $tasaParticipacion = $totalAsignadas > 0 ? round(($totalCompletadas / $totalAsignadas) * 100, 1) : 0;

            return $this->view('evaluacion/coordinador/index', [
                'pageTitle'         => 'Panel de Evaluación Docente - Coordinación',
                'module'            => 'evaluacion',
                'periodoNombre'     => $periodoNombre,
                'encuestas'         => $encuestas,
                'docentes'          => $docentes,
                'totalPendientes'   => count($pendientes),
                'totalAsignadas'    => $totalAsignadas,
                'totalCompletadas'  => $totalCompletadas,
                'tasaParticipacion' => $tasaParticipacion
            ]);
        }

        // 4. VISTA INSTITUCIONAL (ADMINISTRADOR / DIRECTOR / JUA / SECRETARIO ACADÉMICO)
        $filtroPeriodo  = $idPeriodo;
        $filtroPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        $encuestas  = $this->encuestaModel->listar($filtroPeriodo, $filtroPrograma);
        $docentes   = $this->reporteModel->obtenerResumenPorDocente($filtroPeriodo, $filtroPrograma);
        $programas  = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $periodos   = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);

        $totalAsignadas   = 0;
        $totalCompletadas = 0;
        $totalPendientes  = 0;
        foreach ($encuestas as $e) {
            $totalAsignadas   += (int)$e['total_asignadas'];
            $totalCompletadas += (int)$e['total_completadas'];
            $totalPendientes  += (int)$e['total_pendientes'];
        }
        $tasaParticipacion = $totalAsignadas > 0 ? round(($totalCompletadas / $totalAsignadas) * 100, 1) : 0;

        return $this->view('evaluacion/admin/index', [
            'pageTitle'         => 'Panel General - Evaluación Docente',
            'module'            => 'evaluacion',
            'periodoNombre'     => $periodoNombre,
            'encuestas'         => $encuestas,
            'docentes'          => $docentes,
            'programas'         => $programas,
            'periodos'          => $periodos,
            'filtroPeriodo'     => $filtroPeriodo,
            'filtroPrograma'    => $filtroPrograma,
            'totalAsignadas'    => $totalAsignadas,
            'totalCompletadas'  => $totalCompletadas,
            'totalPendientes'   => $totalPendientes,
            'tasaParticipacion' => $tasaParticipacion
        ]);
    }

    public function misEvaluaciones()
    {
        $idUser    = $_SESSION['sigi_user_id'];
        $idPeriodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $db        = (new Model())->getDB();

        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo) . " LIMIT 1")->fetchColumn() ?: 'Periodo Vigente';
        $evaluaciones  = $this->evaluacionEstudianteModel->obtenerPorEstudiante($idUser, $idPeriodo);

        $totalAsignadas   = count($evaluaciones);
        $totalCompletadas = 0;
        $totalPendientes  = 0;

        foreach ($evaluaciones as $ev) {
            if ($ev['estado'] === 'COMPLETADO') {
                $totalCompletadas++;
            } else {
                $totalPendientes++;
            }
        }

        $porcentajeAvance = $totalAsignadas > 0 ? round(($totalCompletadas / $totalAsignadas) * 100) : 0;

        return $this->view('evaluacion/estudiante/index', [
            'pageTitle'        => 'Mis Evaluaciones Docentes',
            'module'           => 'evaluacion',
            'periodoNombre'    => $periodoNombre,
            'evaluaciones'     => $evaluaciones,
            'totalAsignadas'   => $totalAsignadas,
            'totalCompletadas' => $totalCompletadas,
            'totalPendientes'  => $totalPendientes,
            'porcentajeAvance' => $porcentajeAvance
        ]);
    }

    public function misResultados()
    {
        $idUser    = $_SESSION['sigi_user_id'];
        $idPeriodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $db        = (new Model())->getDB();

        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo) . " LIMIT 1")->fetchColumn() ?: 'Periodo Vigente';
        $detalleDocente = $this->reporteModel->obtenerDetalleDocente($idUser, $idPeriodo);

        return $this->view('evaluacion/docente/index', [
            'pageTitle'      => 'Mis Resultados de Evaluación Docente',
            'module'         => 'evaluacion',
            'periodoNombre'  => $periodoNombre,
            'detalleDocente' => $detalleDocente
        ]);
    }
}
