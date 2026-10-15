<?php

namespace App\Controllers\Evaluacion;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Evaluacion\EvaluacionEstudiante;
use App\Models\Evaluacion\Encuesta;
use App\Utils\Mailer;
use PDO;

class SeguimientoController extends Controller
{
    protected $evaluacionEstudianteModel;
    protected $encuestaModel;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::esAdminEvaluacion() && !Auth::esCoordinadorPEEvaluacion()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder al panel de seguimiento.";
            header('Location: ' . BASE_URL . '/evaluacion');
            exit;
        }

        $this->evaluacionEstudianteModel = new EvaluacionEstudiante();
        $this->encuestaModel = new Encuesta();
    }

    public function index()
    {
        $idUser     = $_SESSION['sigi_user_id'];
        $db         = (new Model())->getDB();

        // El periodo se toma estrictamente del selector de contexto del menú superior
        $idPeriodo  = $_SESSION['sigi_periodo_actual_id'] ?? null;
        if (!$idPeriodo) {
            $idPeriodo = (int)$db->query("SELECT id FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")->fetchColumn();
        }

        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . (int)$idPeriodo)->fetchColumn() ?: 'Periodo Actual';

        $idPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;
        $idPlan     = !empty($_GET['id_plan']) ? (int)$_GET['id_plan'] : null;
        $idModulo   = !empty($_GET['id_modulo']) ? (int)$_GET['id_modulo'] : null;
        $idSemestre = !empty($_GET['id_semestre']) ? (int)$_GET['id_semestre'] : null;
        $idEncuesta = !empty($_GET['id_encuesta']) ? (int)$_GET['id_encuesta'] : null;
        $idDocente  = !empty($_GET['id_docente']) ? (int)$_GET['id_docente'] : null;

        $puedeVerTodos = Auth::puedeVerTodosLosProgramasEvaluacion();

        // Roles: Director (2), JUA (4), Admin (1), Sec. Académico (3) ven todos los programas
        // Coordinador / Jefe de área (5) solo ve las carreras que tiene asignadas
        if ($puedeVerTodos) {
            $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtProg = $db->prepare("
                SELECT p.id, p.nombre, p.codigo 
                FROM sigi_programa_estudios p 
                INNER JOIN sigi_coordinador_pe_periodo c ON c.id_programa_estudio = p.id 
                WHERE c.id_usuario = ? AND c.id_periodo = ? 
                ORDER BY p.nombre ASC
            ");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $programas = $stmtProg->fetchAll(PDO::FETCH_ASSOC);

            $idsPermitidos = array_column($programas, 'id');
            if ($idPrograma && !in_array($idPrograma, $idsPermitidos)) {
                $idPrograma = !empty($idsPermitidos) ? $idsPermitidos[0] : null;
            } elseif (!$idPrograma && !empty($idsPermitidos)) {
                $idPrograma = $idsPermitidos[0];
            }
        }

        // Cargar listas dependientes si hay selección previa para mantener estado
        $planes = [];
        $modulos = [];
        $semestres = [];

        if ($idPrograma) {
            $stmtPlanes = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre DESC");
            $stmtPlanes->execute([$idPrograma]);
            $planes = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($idPlan) {
            $stmtMod = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
            $stmtMod->execute([$idPlan]);
            $modulos = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($idModulo) {
            $stmtSem = $db->prepare("SELECT id, descripcion FROM sigi_semestre WHERE id_modulo_formativo = ? ORDER BY id ASC");
            $stmtSem->execute([$idModulo]);
            $semestres = $stmtSem->fetchAll(PDO::FETCH_ASSOC);
        }

        $filtros = [
            'id_periodo'  => $idPeriodo,
            'id_programa' => $idPrograma,
            'id_plan'     => $idPlan,
            'id_modulo'   => $idModulo,
            'id_semestre' => $idSemestre,
            'id_encuesta' => $idEncuesta,
            'id_docente'  => $idDocente
        ];

        $pendientes = $this->evaluacionEstudianteModel->obtenerEstudiantesPendientes($filtros);
        $encuestas  = $this->encuestaModel->listar($idPeriodo, $idPrograma);

        $this->view('evaluacion/seguimiento/index', [
            'pageTitle'           => 'Seguimiento de Estudiantes Pendientes',
            'module'              => 'evaluacion',
            'pendientes'          => $pendientes,
            'periodoActualNombre' => $periodoNombre,
            'filtroPeriodo'       => $idPeriodo,
            'programas'           => $programas,
            'planes'              => $planes,
            'modulos'             => $modulos,
            'semestres'           => $semestres,
            'encuestas'           => $encuestas,
            'filtroPrograma'      => $idPrograma,
            'filtroPlan'          => $idPlan,
            'filtroModulo'        => $idModulo,
            'filtroSemestre'      => $idSemestre,
            'filtroEncuesta'      => $idEncuesta,
            'puedeVerTodos'       => $puedeVerTodos
        ]);
    }

    /**
     * AJAX: Obtiene los planes de estudio de un programa
     */
    public function ajaxPlanes($idPrograma)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre DESC");
        $stmt->execute([(int)$idPrograma]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * AJAX: Obtiene los módulos formativos de un plan de estudios
     */
    public function ajaxModulos($idPlan)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
        $stmt->execute([(int)$idPlan]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * AJAX: Obtiene los semestres de un módulo formativo
     */
    public function ajaxSemestres($idModulo)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion FROM sigi_semestre WHERE id_modulo_formativo = ? ORDER BY id ASC");
        $stmt->execute([(int)$idModulo]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * Envía recordatorio por correo a un estudiante en específico
     */
    public function notificarIndividual($idEvaluacion)
    {
        $idEvaluacion = (int)$idEvaluacion;
        $db = (new Model())->getDB();

        $stmt = $db->prepare("
            SELECT ee.*, 
                   u_est.apellidos_nombres AS estudiante_nombre,
                   u_est.correo AS estudiante_correo,
                   u_est.correo_personal AS estudiante_correo_personal,
                   u_doc.apellidos_nombres AS docente_nombre,
                   ud.nombre AS unidad_nombre,
                   enc.titulo AS encuesta_titulo,
                   enc.fecha_fin AS encuesta_fecha_fin
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN sigi_usuarios u_est ON ee.id_estudiante = u_est.id
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN eva_encuestas enc ON ee.id_encuesta = enc.id
            WHERE ee.id = ? AND ee.estado = 'PENDIENTE'
        ");
        $stmt->execute([$idEvaluacion]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['flash_error'] = "Evaluación no encontrada o ya se encuentra completada.";
            header('Location: ' . BASE_URL . '/evaluacion/seguimiento');
            exit;
        }

        $correoDestino = !empty($row['estudiante_correo_personal']) ? trim($row['estudiante_correo_personal']) : trim($row['estudiante_correo']);

        if (empty($correoDestino)) {
            $_SESSION['flash_error'] = "El estudiante no tiene registrado un correo personal o institucional válido.";
            header('Location: ' . BASE_URL . '/evaluacion/seguimiento');
            exit;
        }

        $nombreEstudiante = Auth::formatearNombreUsuario($row['estudiante_nombre']);
        $nombreDocente    = Auth::formatearNombreUsuario($row['docente_nombre']);

        $asunto = "Recordatorio: Encuesta de Evaluación Docente Pendiente";
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <div style='text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 15px;'>
                    <h2 style='color: #007bff; margin: 0;'>Recordatorio de Evaluación Docente</h2>
                    <p style='color: #666; font-size: 13px; margin: 5px 0 0 0;'>{$row['encuesta_titulo']}</p>
                </div>
                <div style='padding: 20px 0;'>
                    <p>Estimado(a) estudiante <strong>{$nombreEstudiante}</strong>,</p>
                    <p>Le recordamos que aún tiene pendiente completar la evaluación del desempeño docente para la siguiente unidad didáctica:</p>
                    <div style='background: #f8f9fa; border-left: 4px solid #007bff; padding: 12px 15px; margin: 15px 0;'>
                        <strong>Unidad Didáctica:</strong> {$row['unidad_nombre']}<br>
                        <strong>Docente a Evaluar:</strong> {$nombreDocente}<br>
                        <strong>Fecha Límite de Cierre:</strong> " . date('d/m/Y H:i', strtotime($row['encuesta_fecha_fin'])) . "
                    </div>
                    <p>Su opinión es fundamental para la mejora de la calidad académica de nuestra institución educativa.</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='" . BASE_URL . "/evaluacion/realizar/evaluar/{$idEvaluacion}' 
                           style='background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Completar Encuesta Ahora
                        </a>
                    </div>
                </div>
                <div style='text-align: center; border-top: 1px solid #eee; padding-top: 15px; font-size: 12px; color: #999;'>
                    Sistema Integrado de Gestión Institucional (SIGI)
                </div>
            </div>
        ";

        $res = Mailer::send($correoDestino, $nombreEstudiante, $asunto, $html);

        if ($res['ok']) {
            $this->evaluacionEstudianteModel->marcarNotificado($idEvaluacion);
            $_SESSION['flash_success'] = "Recordatorio enviado exitosamente a {$correoDestino}.";
        } else {
            $_SESSION['flash_error'] = "No se pudo enviar el correo: " . ($res['error'] ?? 'Error desconocido');
        }

        header('Location: ' . BASE_URL . '/evaluacion/seguimiento');
        exit;
    }

    /**
     * Envía recordatorio masivo a todos los estudiantes pendientes en el filtro actual
     */
    public function notificarMasivo()
    {
        $idUser     = $_SESSION['sigi_user_id'];
        $idPeriodo  = !empty($_POST['id_periodo']) ? (int)$_POST['id_periodo'] : ($_SESSION['sigi_periodo_actual_id'] ?? null);
        $idPrograma = !empty($_POST['id_programa']) ? (int)$_POST['id_programa'] : null;
        $idPlan     = !empty($_POST['id_plan']) ? (int)$_POST['id_plan'] : null;
        $idModulo   = !empty($_POST['id_modulo']) ? (int)$_POST['id_modulo'] : null;
        $idSemestre = !empty($_POST['id_semestre']) ? (int)$_POST['id_semestre'] : null;
        $idEncuesta = !empty($_POST['id_encuesta']) ? (int)$_POST['id_encuesta'] : null;

        $db = (new Model())->getDB();

        if (!Auth::puedeVerTodosLosProgramasEvaluacion()) {
            $stmtProg = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN) ?: [-1];
            if (!$idPrograma || !in_array($idPrograma, $progsCoord)) {
                $idPrograma = $progsCoord[0] ?? null;
            }
        }

        $filtros = [
            'id_periodo'  => $idPeriodo,
            'id_programa' => $idPrograma,
            'id_plan'     => $idPlan,
            'id_modulo'   => $idModulo,
            'id_semestre' => $idSemestre,
            'id_encuesta' => $idEncuesta
        ];

        $pendientes = $this->evaluacionEstudianteModel->obtenerEstudiantesPendientes($filtros);

        if (empty($pendientes)) {
            $_SESSION['flash_error'] = "No hay evaluaciones pendientes que notificar con los filtros seleccionados.";
            header('Location: ' . BASE_URL . '/evaluacion/seguimiento');
            exit;
        }

        $enviados = 0;
        $fallidos = 0;

        foreach ($pendientes as $p) {
            $correoDestino = !empty($p['estudiante_correo_personal']) ? trim($p['estudiante_correo_personal']) : trim($p['estudiante_correo']);
            if (empty($correoDestino)) {
                $fallidos++;
                continue;
            }

            $asunto = "Recordatorio: Encuesta de Evaluación Docente Pendiente";
            $html = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <div style='text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 15px;'>
                        <h2 style='color: #007bff; margin: 0;'>Recordatorio de Evaluación Docente</h2>
                        <p style='color: #666; font-size: 13px; margin: 5px 0 0 0;'>{$p['encuesta_titulo']}</p>
                    </div>
                    <div style='padding: 20px 0;'>
                        <p>Estimado(a) estudiante <strong>{$p['estudiante_nombre']}</strong>,</p>
                        <p>Le recordamos que aún tiene pendiente completar la evaluación docente de la unidad didáctica <strong>{$p['unidad_didactica_nombre']}</strong> con el docente <strong>{$p['docente_nombre']}</strong>.</p>
                        <p>Fecha límite: " . date('d/m/Y', strtotime($p['encuesta_fecha_fin'])) . "</p>
                        <div style='text-align: center; margin: 25px 0;'>
                            <a href='" . BASE_URL . "/evaluacion/realizar/evaluar/{$p['id_evaluacion']}' 
                               style='background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                                Completar Evaluación
                            </a>
                        </div>
                    </div>
                </div>
            ";

            $res = Mailer::send($correoDestino, $p['estudiante_nombre'], $asunto, $html);
            if ($res['ok']) {
                $this->evaluacionEstudianteModel->marcarNotificado($p['id_evaluacion']);
                $enviados++;
            } else {
                $fallidos++;
            }
        }

        $_SESSION['flash_success'] = "Proceso de notificación finalizado: {$enviados} recordatorios enviados a correos personales registrados (Fallidos/Sin correo: {$fallidos}).";
        header('Location: ' . BASE_URL . '/evaluacion/seguimiento');
        exit;
    }
}
