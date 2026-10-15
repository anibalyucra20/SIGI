<?php

namespace App\Models\Evaluacion;

use Core\Model;
use Core\Auth;
use PDO;

class EvaluacionEstudiante extends Model
{
    /**
     * Obtiene las evaluaciones asignadas a un estudiante agrupadas por estado
     */
    public function obtenerPorEstudiante($idEstudiante, $idPeriodo = null)
    {
        $db = self::getDB();
        $sql = "
            SELECT 
                ee.*,
                enc.titulo AS encuesta_titulo,
                enc.fecha_inicio AS encuesta_fecha_inicio,
                enc.fecha_fin AS encuesta_fecha_fin,
                enc.estado AS encuesta_estado,
                u_doc.apellidos_nombres AS docente_nombre,
                u_doc.dni AS docente_dni,
                ud.nombre AS unidad_didactica_nombre,
                pud.seccion AS seccion,
                pud.turno AS turno,
                sem.descripcion AS semestre_romano,
                prog.nombre AS programa_nombre
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN eva_encuestas enc ON ee.id_encuesta = enc.id
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN acad_programacion_unidad_didactica pud ON ee.id_programacion_ud = pud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            WHERE ee.id_estudiante = ?
        ";

        $params = [(int)$idEstudiante];
        if ($idPeriodo) {
            $sql .= " AND ee.id_periodo_academico = ?";
            $params[] = (int)$idPeriodo;
        }

        $sql .= " ORDER BY ee.estado ASC, enc.fecha_fin ASC, ud.nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filas as &$f) {
            $f['docente_nombre'] = Auth::formatearNombreUsuario($f['docente_nombre']);
        }

        return $filas;
    }

    /**
     * Obtiene el detalle de una evaluación para ser respondida por el estudiante
     */
    public function obtenerDetalleParaResponder($idEvaluacion, $idEstudiante)
    {
        $db = self::getDB();
        $sql = "
            SELECT 
                ee.*,
                enc.titulo AS encuesta_titulo,
                enc.descripcion AS encuesta_descripcion,
                enc.escala_min,
                enc.escala_max,
                enc.fecha_inicio AS encuesta_fecha_inicio,
                enc.fecha_fin AS encuesta_fecha_fin,
                enc.estado AS encuesta_estado,
                u_doc.apellidos_nombres AS docente_nombre,
                u_doc.dni AS docente_dni,
                ud.nombre AS unidad_didactica_nombre,
                pud.seccion AS seccion,
                pud.turno AS turno,
                sem.descripcion AS semestre_romano,
                prog.nombre AS programa_nombre
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN eva_encuestas enc ON ee.id_encuesta = enc.id
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN acad_programacion_unidad_didactica pud ON ee.id_programacion_ud = pud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            WHERE ee.id = ? AND ee.id_estudiante = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([(int)$idEvaluacion, (int)$idEstudiante]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['docente_nombre'] = Auth::formatearNombreUsuario($row['docente_nombre']);
        }
        return $row;
    }

    /**
     * Guarda las respuestas y bloquea la evaluación definitivamente
     */
    public function guardarRespuestas($idEvaluacion, $idEstudiante, $respuestas, $comentarioGeneral = null, $ip = null)
    {
        $db = self::getDB();

        // 1. Validar que la evaluación exista, pertenezca al estudiante y esté PENDIENTE
        $stmtCheck = $db->prepare("SELECT ee.*, enc.estado AS encuesta_estado, enc.fecha_fin
                                   FROM eva_evaluaciones_estudiante ee
                                   INNER JOIN eva_encuestas enc ON ee.id_encuesta = enc.id
                                   WHERE ee.id = ? AND ee.id_estudiante = ?");
        $stmtCheck->execute([(int)$idEvaluacion, (int)$idEstudiante]);
        $eval = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$eval) {
            return ['ok' => false, 'error' => 'La evaluación no existe o no pertenece a tu usuario.'];
        }

        if ($eval['estado'] === 'COMPLETADO') {
            return ['ok' => false, 'error' => 'Esta evaluación ya fue completada y no puede ser modificada.'];
        }

        if ($eval['encuesta_estado'] !== 'ACTIVA') {
            return ['ok' => false, 'error' => 'La encuesta se encuentra cerrada o inactiva.'];
        }

        $db->beginTransaction();

        try {
            // 2. Insertar cada respuesta
            $stmtResp = $db->prepare("INSERT INTO eva_respuestas (id_evaluacion_estudiante, id_pregunta, puntaje, respuesta_texto, created_at)
                                      VALUES (?, ?, ?, ?, NOW())");

            foreach ($respuestas as $idPregunta => $valor) {
                $puntaje = is_numeric($valor) ? (int)$valor : null;
                $texto   = !is_numeric($valor) ? trim($valor) : null;

                $stmtResp->execute([
                    (int)$idEvaluacion,
                    (int)$idPregunta,
                    $puntaje,
                    $texto
                ]);
            }

            // 3. Marcar evaluación como COMPLETADA
            $stmtUpdate = $db->prepare("UPDATE eva_evaluaciones_estudiante SET 
                                            estado = 'COMPLETADO',
                                            fecha_completado = NOW(),
                                            comentario_general = ?,
                                            ip_registro = ?
                                        WHERE id = ?");
            $stmtUpdate->execute([
                !empty($comentarioGeneral) ? trim($comentarioGeneral) : null,
                $ip,
                (int)$idEvaluacion
            ]);

            $db->commit();
            return ['ok' => true];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['ok' => false, 'error' => 'Error al registrar respuestas: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene la lista de estudiantes con evaluaciones pendientes para seguimiento
     */
    public function obtenerEstudiantesPendientes($filtros = [])
    {
        $db = self::getDB();
        $sql = "
            SELECT 
                ee.id AS id_evaluacion,
                ee.id_estudiante,
                ee.id_docente,
                ee.id_unidad_didactica,
                ee.id_periodo_academico,
                ee.estado,
                ee.notificado_email,
                ee.fecha_notificacion,
                u_est.apellidos_nombres AS estudiante_nombre,
                u_est.dni AS estudiante_dni,
                u_est.correo AS estudiante_correo,
                u_est.correo_personal AS estudiante_correo_personal,
                u_est.telefono AS estudiante_telefono,
                u_doc.apellidos_nombres AS docente_nombre,
                ud.nombre AS unidad_didactica_nombre,
                sem.descripcion AS semestre_romano,
                pud.seccion AS seccion,
                pud.turno AS turno,
                prog.id AS id_programa_estudios,
                prog.nombre AS programa_nombre,
                enc.id AS id_encuesta,
                enc.titulo AS encuesta_titulo,
                enc.fecha_fin AS encuesta_fecha_fin
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN eva_encuestas enc ON ee.id_encuesta = enc.id
            INNER JOIN sigi_usuarios u_est ON ee.id_estudiante = u_est.id
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN acad_programacion_unidad_didactica pud ON ee.id_programacion_ud = pud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            WHERE ee.estado = 'PENDIENTE'
        ";

        $params = [];

        if (!empty($filtros['id_encuesta'])) {
            $sql .= " AND ee.id_encuesta = ?";
            $params[] = (int)$filtros['id_encuesta'];
        }

        if (!empty($filtros['id_periodo'])) {
            $sql .= " AND ee.id_periodo_academico = ?";
            $params[] = (int)$filtros['id_periodo'];
        }

        if (!empty($filtros['id_programa'])) {
            $sql .= " AND (prog.id = ? OR enc.id_programa_estudios = ?)";
            $params[] = (int)$filtros['id_programa'];
            $params[] = (int)$filtros['id_programa'];
        }

        if (!empty($filtros['id_plan'])) {
            $sql .= " AND pe.id = ?";
            $params[] = (int)$filtros['id_plan'];
        }

        if (!empty($filtros['id_modulo'])) {
            $sql .= " AND mf.id = ?";
            $params[] = (int)$filtros['id_modulo'];
        }

        if (!empty($filtros['id_semestre'])) {
            $sql .= " AND sem.id = ?";
            $params[] = (int)$filtros['id_semestre'];
        }

        if (!empty($filtros['id_docente'])) {
            $sql .= " AND ee.id_docente = ?";
            $params[] = (int)$filtros['id_docente'];
        }

        if (!empty($filtros['id_unidad_didactica'])) {
            $sql .= " AND ee.id_unidad_didactica = ?";
            $params[] = (int)$filtros['id_unidad_didactica'];
        }

        $sql .= " ORDER BY prog.nombre ASC, sem.id ASC, u_est.apellidos_nombres ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filas as &$f) {
            $f['estudiante_nombre'] = Auth::formatearNombreUsuario($f['estudiante_nombre']);
            $f['docente_nombre']    = Auth::formatearNombreUsuario($f['docente_nombre']);
        }

        return $filas;
    }

    /**
     * Marca la evaluación como notificada por email
     */
    public function marcarNotificado($idEvaluacion)
    {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE eva_evaluaciones_estudiante 
                              SET notificado_email = 1, fecha_notificacion = NOW() 
                              WHERE id = ?");
        return $stmt->execute([(int)$idEvaluacion]);
    }
}
