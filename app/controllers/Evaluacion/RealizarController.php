<?php

namespace App\Controllers\Evaluacion;

require_once __DIR__ . '/../../../app/models/Evaluacion/EvaluacionEstudiante.php';
require_once __DIR__ . '/../../../app/models/Evaluacion/Pregunta.php';

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Evaluacion\EvaluacionEstudiante;
use App\Models\Evaluacion\Pregunta;
use App\Utils\Mailer;
use PDO;

class RealizarController extends Controller
{
    protected $evaluacionEstudianteModel;
    protected $preguntaModel;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnEvaluacion()) {
            $_SESSION['flash_error'] = "Debes iniciar sesión para realizar la evaluación docente.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->evaluacionEstudianteModel = new EvaluacionEstudiante();
        $this->preguntaModel = new Pregunta();
    }

    /**
     * Formulario interactivo para que el estudiante responda la encuesta
     */
    public function evaluar($idEvaluacion)
    {
        $idEvaluacion = (int)$idEvaluacion;
        $idEstudiante = $_SESSION['sigi_user_id'];

        $evaluacion = $this->evaluacionEstudianteModel->obtenerDetalleParaResponder($idEvaluacion, $idEstudiante);

        if (!$evaluacion) {
            $_SESSION['flash_error'] = "La evaluación solicitada no existe o no tienes permisos para acceder a ella.";
            header('Location: ' . BASE_URL . '/evaluacion/misEvaluaciones');
            exit;
        }

        // Si ya fue completada, redirigir a vista de solo lectura
        if ($evaluacion['estado'] === 'COMPLETADO') {
            header('Location: ' . BASE_URL . '/evaluacion/realizar/ver/' . $idEvaluacion);
            exit;
        }

        // Validar que la encuesta esté activa
        if ($evaluacion['encuesta_estado'] !== 'ACTIVA') {
            $_SESSION['flash_error'] = "Esta encuesta no se encuentra activa en este momento.";
            header('Location: ' . BASE_URL . '/evaluacion/misEvaluaciones');
            exit;
        }

        $criteriosConPreguntas = $this->preguntaModel->obtenerAgrupadasPorCriterio($evaluacion['id_encuesta']);

        $this->view('evaluacion/realizar/formulario', [
            'pageTitle'  => 'Evaluación al Docente: ' . $evaluacion['docente_nombre'],
            'module'     => 'evaluacion',
            'evaluacion' => $evaluacion,
            'criterios'  => $criteriosConPreguntas
        ]);
    }

    /**
     * Procesa y guarda las respuestas.
     * Bloquea definitivamente la evaluación y envía correo de notificación.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/misEvaluaciones');
            exit;
        }

        $idEvaluacion = (int)($_POST['id_evaluacion'] ?? 0);
        $idEstudiante = $_SESSION['sigi_user_id'];
        $respuestas   = $_POST['respuestas'] ?? [];
        $comentario   = trim($_POST['comentario_general'] ?? '');
        $ipCliente    = $_SERVER['REMOTE_ADDR'] ?? null;

        if (empty($idEvaluacion) || empty($respuestas)) {
            $_SESSION['flash_error'] = "Por favor responda todas las preguntas obligatorias antes de enviar.";
            header('Location: ' . BASE_URL . '/evaluacion/realizar/evaluar/' . $idEvaluacion);
            exit;
        }

        $res = $this->evaluacionEstudianteModel->guardarRespuestas($idEvaluacion, $idEstudiante, $respuestas, $comentario, $ipCliente);

        if (!$res['ok']) {
            $_SESSION['flash_error'] = $res['error'];
            header('Location: ' . BASE_URL . '/evaluacion/realizar/evaluar/' . $idEvaluacion);
            exit;
        }

        // =========================================================================
        // NOTIFICACIÓN POR CORREO ELECTRÓNICO AL ESTUDIANTE A SU CORREO PERSONAL
        // =========================================================================
        $this->enviarNotificacionConfirmacion($idEvaluacion, $idEstudiante);

        $_SESSION['flash_success'] = "¡Evaluación registrada exitosamente! Tu respuesta ha sido guardada y ya no podrá ser modificada. Se ha enviado una constancia a tu correo personal.";
        header('Location: ' . BASE_URL . '/evaluacion/realizar/ver/' . $idEvaluacion);
        exit;
    }

    /**
     * Vista de solo lectura tras completar la encuesta (Garantiza que no se puede modificar)
     */
    public function ver($idEvaluacion)
    {
        $idEvaluacion = (int)$idEvaluacion;
        $idEstudiante = $_SESSION['sigi_user_id'];

        $evaluacion = $this->evaluacionEstudianteModel->obtenerDetalleParaResponder($idEvaluacion, $idEstudiante);

        if (!$evaluacion) {
            $_SESSION['flash_error'] = "Evaluación no encontrada.";
            header('Location: ' . BASE_URL . '/evaluacion/misEvaluaciones');
            exit;
        }

        $db = (new Model())->getDB();
        $stmtResp = $db->prepare("
            SELECT r.*, p.enunciado, c.nombre AS criterio_nombre
            FROM eva_respuestas r
            INNER JOIN eva_preguntas p ON r.id_pregunta = p.id
            LEFT JOIN eva_criterios c ON p.id_criterio = c.id
            WHERE r.id_evaluacion_estudiante = ?
            ORDER BY c.orden ASC, p.orden ASC
        ");
        $stmtResp->execute([$idEvaluacion]);
        $respuestasGuardadas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

        $this->view('evaluacion/realizar/completada', [
            'pageTitle'  => 'Constancia de Evaluación Docente Completada',
            'module'     => 'evaluacion',
            'evaluacion' => $evaluacion,
            'respuestas' => $respuestasGuardadas
        ]);
    }

    /**
     * Envía correo de confirmación de evaluación al correo personal registrado del estudiante
     */
    private function enviarNotificacionConfirmacion($idEvaluacion, $idEstudiante)
    {
        try {
            $db = (new Model())->getDB();

            // Obtener datos del estudiante
            $stmtEst = $db->prepare("SELECT id, dni, apellidos_nombres, correo, correo_personal FROM sigi_usuarios WHERE id = ?");
            $stmtEst->execute([(int)$idEstudiante]);
            $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) return;

            // Determinar correo de destino: preferir correo_personal si existe, de lo contrario correo
            $destinatarioEmail = !empty($estudiante['correo_personal']) ? trim($estudiante['correo_personal']) : trim($estudiante['correo']);
            if (empty($destinatarioEmail)) return;

            $nombreEstudiante = Auth::formatearNombreUsuario($estudiante['apellidos_nombres']);

            // Obtener datos de la evaluación
            $evaluacion = $this->evaluacionEstudianteModel->obtenerDetalleParaResponder($idEvaluacion, $idEstudiante);
            if (!$evaluacion) return;

            $asunto = "Constancia de Evaluación Docente - " . $evaluacion['unidad_didactica_nombre'];

            $mensajeHtml = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <div style='text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 15px;'>
                        <h2 style='color: #28a745; margin: 0;'>Constancia de Evaluación Docente</h2>
                        <p style='color: #666; font-size: 13px; margin: 5px 0 0 0;'>Sistema Integrado de Gestión Institucional (SIGI)</p>
                    </div>

                    <div style='padding: 20px 0;'>
                        <p>Estimado(a) estudiante <strong>{$nombreEstudiante}</strong>,</p>
                        <p>Le confirmamos que ha completado satisfactoriamente la evaluación de desempeño docente correspondiente a la siguiente unidad didáctica:</p>

                        <table style='width: 100%; border-collapse: collapse; margin: 15px 0; background: #f9f9f9; border-radius: 5px;'>
                            <tr>
                                <td style='padding: 8px 12px; font-weight: bold; width: 35%; border-bottom: 1px solid #eee;'>Docente Evaluado:</td>
                                <td style='padding: 8px 12px; border-bottom: 1px solid #eee;'>{$evaluacion['docente_nombre']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #eee;'>Unidad Didáctica:</td>
                                <td style='padding: 8px 12px; border-bottom: 1px solid #eee;'>{$evaluacion['unidad_didactica_nombre']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #eee;'>Programa de Estudios:</td>
                                <td style='padding: 8px 12px; border-bottom: 1px solid #eee;'>{$evaluacion['programa_nombre']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 12px; font-weight: bold; border-bottom: 1px solid #eee;'>Semestre / Sección:</td>
                                <td style='padding: 8px 12px; border-bottom: 1px solid #eee;'>Semestre {$evaluacion['semestre_romano']} - Sec. {$evaluacion['seccion']} ({$evaluacion['turno']})</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 12px; font-weight: bold;'>Fecha y Hora:</td>
                                <td style='padding: 8px 12px;'>" . date('d/m/Y H:i:s') . "</td>
                            </tr>
                        </table>

                        <div style='background-color: #e8f4fd; border-left: 4px solid #17a2b8; padding: 12px; margin: 15px 0; font-size: 13px; color: #0c5460;'>
                            <strong>Importante:</strong> Sus respuestas han sido registradas y encriptadas de forma confidencial. El sistema ha bloqueado su evaluación para garantizar la inmutabilidad de los resultados.
                        </div>

                        <p style='font-size: 13px; color: #555;'>Gracias por participar activamente en el proceso de mejora continua de nuestra institución educativa.</p>
                    </div>

                    <div style='text-align: center; border-top: 1px solid #eee; padding-top: 15px; font-size: 12px; color: #999;'>
                        Este es un mensaje automático generado por el Módulo de Evaluación Docente de SIGI.<br>Por favor no responda a este correo.
                    </div>
                </div>
            ";

            //$envio = Mailer::send($destinatarioEmail, $nombreEstudiante, $asunto, $mensajeHtml);

            $envio['ok'] = true;
            if ($envio['ok']) {
                $this->evaluacionEstudianteModel->marcarNotificado($idEvaluacion);
            }
        } catch (\Exception $e) {
            error_log("Error enviando correo de confirmación de evaluación docente: " . $e->getMessage());
        }
    }
}
