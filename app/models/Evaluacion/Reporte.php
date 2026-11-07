<?php

namespace App\Models\Evaluacion;

use Core\Model;
use Core\Auth;
use PDO;

class Reporte extends Model
{
    /**
     * Obtiene el resumen consolidado de evaluación por docente
     */
    public function obtenerResumenPorDocente($idPeriodo, $idPrograma = null, $idDocente = null, $idPlan = null, $idModulo = null, $idSemestre = null)
    {
        $db = self::getDB();

        $sql = "
            SELECT 
                u_doc.id AS id_docente,
                u_doc.dni AS docente_dni,
                u_doc.apellidos_nombres AS docente_nombre,
                prog.id AS id_programa_estudios,
                prog.nombre AS programa_nombre,
                prog.codigo AS programa_codigo,
                COUNT(DISTINCT ee.id) AS total_asignados,
                COUNT(DISTINCT CASE WHEN ee.estado = 'COMPLETADO' THEN ee.id END) AS total_completados,
                ROUND(AVG(CASE WHEN ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL THEN r.puntaje END), 2) AS promedio_escala_5,
                ROUND(AVG(CASE WHEN ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL THEN (r.puntaje * 4) END), 2) AS promedio_vigesimal,
                COUNT(DISTINCT ee.id_unidad_didactica) AS total_unidades
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            LEFT JOIN eva_respuestas r ON r.id_evaluacion_estudiante = ee.id
            WHERE ee.id_periodo_academico = ?
        ";

        $params = [(int)$idPeriodo];

        if ($idPrograma) {
            $sql .= " AND prog.id = ?";
            $params[] = (int)$idPrograma;
        }

        if ($idPlan) {
            $sql .= " AND pe.id = ?";
            $params[] = (int)$idPlan;
        }

        if ($idModulo) {
            $sql .= " AND mf.id = ?";
            $params[] = (int)$idModulo;
        }

        if ($idSemestre) {
            $sql .= " AND sem.id = ?";
            $params[] = (int)$idSemestre;
        }

        if ($idDocente) {
            $sql .= " AND u_doc.id = ?";
            $params[] = (int)$idDocente;
        }

        $sql .= " GROUP BY u_doc.id, prog.id ORDER BY promedio_escala_5 DESC, u_doc.apellidos_nombres ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($docentes as &$d) {
            $d['docente_nombre'] = Auth::formatearNombreUsuario($d['docente_nombre']);
            $prom = (float)($d['promedio_escala_5'] ?? 0);
            $d['nivel_desempeno'] = $this->clasificarDesempeno($prom);
            $totalAsig = (int)$d['total_asignados'];
            $totalComp = (int)$d['total_completados'];
            $d['porcentaje_participacion'] = $totalAsig > 0 ? round(($totalComp / $totalAsig) * 100, 1) : 0;
        }

        return $docentes;
    }

    /**
     * Obtiene el reporte detallado de un docente (por dimensiones, preguntas y comentarios)
     */
    public function obtenerDetalleDocente($idDocente, $idPeriodo, $idPrograma = null)
    {
        $db = self::getDB();

        // 1. Datos del docente
        $stmtDoc = $db->prepare("SELECT id, dni, apellidos_nombres, correo FROM sigi_usuarios WHERE id = ?");
        $stmtDoc->execute([(int)$idDocente]);
        $docente = $stmtDoc->fetch(PDO::FETCH_ASSOC);
        if (!$docente) return null;
        $docente['docente_nombre'] = Auth::formatearNombreUsuario($docente['apellidos_nombres']);

        // 2. Unidades didácticas evaluadas del docente
        $sqlUD = "
            SELECT 
                ud.id AS id_unidad_didactica,
                ud.nombre AS unidad_nombre,
                sem.descripcion AS semestre_romano,
                prog.nombre AS programa_nombre,
                COUNT(DISTINCT ee.id) AS asignados,
                COUNT(DISTINCT CASE WHEN ee.estado = 'COMPLETADO' THEN ee.id END) AS completados,
                ROUND(AVG(CASE WHEN ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL THEN r.puntaje END), 2) AS promedio_ud
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            LEFT JOIN eva_respuestas r ON r.id_evaluacion_estudiante = ee.id
            WHERE ee.id_docente = ? AND ee.id_periodo_academico = ?
        ";
        $paramsUD = [(int)$idDocente, (int)$idPeriodo];
        if ($idPrograma) {
            $sqlUD .= " AND prog.id = ?";
            $paramsUD[] = (int)$idPrograma;
        }
        $sqlUD .= " GROUP BY ud.id ORDER BY sem.id ASC, ud.nombre ASC";
        $stmtUD = $db->prepare($sqlUD);
        $stmtUD->execute($paramsUD);
        $unidades = $stmtUD->fetchAll(PDO::FETCH_ASSOC);

        // 3. Promedio por Criterios / Dimensiones
        $sqlCrit = "
            SELECT 
                c.id AS id_criterio,
                c.nombre AS criterio_nombre,
                c.peso,
                ROUND(AVG(r.puntaje), 2) AS promedio_criterio,
                ROUND(AVG(r.puntaje * 4), 2) AS promedio_vigesimal
            FROM eva_respuestas r
            INNER JOIN eva_evaluaciones_estudiante ee ON r.id_evaluacion_estudiante = ee.id
            INNER JOIN eva_preguntas p ON r.id_pregunta = p.id
            INNER JOIN eva_criterios c ON p.id_criterio = c.id
            WHERE ee.id_docente = ? AND ee.id_periodo_academico = ? AND ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL
            GROUP BY c.id
            ORDER BY c.orden ASC
        ";
        $stmtCrit = $db->prepare($sqlCrit);
        $stmtCrit->execute([(int)$idDocente, (int)$idPeriodo]);
        $criterios = $stmtCrit->fetchAll(PDO::FETCH_ASSOC);

        // 4. Promedio por Preguntas individuales
        $sqlPreg = "
            SELECT 
                p.id AS id_pregunta,
                p.enunciado,
                c.nombre AS criterio_nombre,
                ROUND(AVG(r.puntaje), 2) AS promedio_pregunta,
                COUNT(r.id) AS total_respuestas
            FROM eva_respuestas r
            INNER JOIN eva_evaluaciones_estudiante ee ON r.id_evaluacion_estudiante = ee.id
            INNER JOIN eva_preguntas p ON r.id_pregunta = p.id
            LEFT JOIN eva_criterios c ON p.id_criterio = c.id
            WHERE ee.id_docente = ? AND ee.id_periodo_academico = ? AND ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL
            GROUP BY p.id
            ORDER BY c.orden ASC, p.orden ASC
        ";
        $stmtPreg = $db->prepare($sqlPreg);
        $stmtPreg->execute([(int)$idDocente, (int)$idPeriodo]);
        $preguntas = $stmtPreg->fetchAll(PDO::FETCH_ASSOC);

        // 5. Comentarios y sugerencias de los estudiantes
        $sqlCom = "
            SELECT 
                ee.comentario_general,
                ee.fecha_completado,
                ud.nombre AS unidad_nombre
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            WHERE ee.id_docente = ? AND ee.id_periodo_academico = ? AND ee.estado = 'COMPLETADO'
              AND ee.comentario_general IS NOT NULL AND TRIM(ee.comentario_general) != ''
            ORDER BY ee.fecha_completado DESC
        ";
        $stmtCom = $db->prepare($sqlCom);
        $stmtCom->execute([(int)$idDocente, (int)$idPeriodo]);
        $comentarios = $stmtCom->fetchAll(PDO::FETCH_ASSOC);

        // Totales generales
        $totalAsignados   = 0;
        $totalCompletados = 0;
        foreach ($unidades as $u) {
            $totalAsignados   += (int)$u['asignados'];
            $totalCompletados += (int)$u['completados'];
        }

        $promedioGral5 = 0;
        if (!empty($criterios)) {
            $suma = 0;
            foreach ($criterios as $c) {
                $suma += (float)$c['promedio_criterio'];
            }
            $promedioGral5 = round($suma / count($criterios), 2);
        }

        return [
            'docente'                => $docente,
            'unidades'               => $unidades,
            'criterios'              => $criterios,
            'preguntas'              => $preguntas,
            'comentarios'            => $comentarios,
            'total_asignados'        => $totalAsignados,
            'total_completados'      => $totalCompletados,
            'porcentaje_participacion' => $totalAsignados > 0 ? round(($totalCompletados / $totalAsignados) * 100, 1) : 0,
            'promedio_escala_5'      => $promedioGral5,
            'promedio_vigesimal'     => round($promedioGral5 * 4, 2),
            'nivel_desempeno'        => $this->clasificarDesempeno($promedioGral5)
        ];
    }

    /**
     * Obtiene el resumen por Unidad Didáctica
     */
    public function obtenerResumenPorUnidadDidactica($idPeriodo, $idPrograma = null, $idPlan = null, $idModulo = null, $idSemestre = null)
    {
        $db = self::getDB();

        $sql = "
            SELECT 
                ud.id AS id_unidad_didactica,
                ud.nombre AS unidad_nombre,
                sem.descripcion AS semestre_romano,
                prog.id AS id_programa_estudios,
                prog.nombre AS programa_nombre,
                u_doc.id AS id_docente,
                u_doc.apellidos_nombres AS docente_nombre,
                pud.seccion,
                pud.turno,
                COUNT(DISTINCT ee.id) AS total_asignados,
                COUNT(DISTINCT CASE WHEN ee.estado = 'COMPLETADO' THEN ee.id END) AS total_completados,
                ROUND(AVG(CASE WHEN ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL THEN r.puntaje END), 2) AS promedio_escala_5,
                ROUND(AVG(CASE WHEN ee.estado = 'COMPLETADO' AND r.puntaje IS NOT NULL THEN (r.puntaje * 4) END), 2) AS promedio_vigesimal
            FROM eva_evaluaciones_estudiante ee
            INNER JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
            INNER JOIN acad_programacion_unidad_didactica pud ON ee.id_programacion_ud = pud.id
            INNER JOIN sigi_usuarios u_doc ON ee.id_docente = u_doc.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios prog ON pe.id_programa_estudios = prog.id
            LEFT JOIN eva_respuestas r ON r.id_evaluacion_estudiante = ee.id
            WHERE ee.id_periodo_academico = ?
        ";

        $params = [(int)$idPeriodo];

        if ($idPrograma) {
            $sql .= " AND prog.id = ?";
            $params[] = (int)$idPrograma;
        }

        if ($idPlan) {
            $sql .= " AND pe.id = ?";
            $params[] = (int)$idPlan;
        }

        if ($idModulo) {
            $sql .= " AND mf.id = ?";
            $params[] = (int)$idModulo;
        }

        if ($idSemestre) {
            $sql .= " AND sem.id = ?";
            $params[] = (int)$idSemestre;
        }

        $sql .= " GROUP BY pud.id ORDER BY prog.nombre ASC, sem.id ASC, ud.nombre ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filas as &$f) {
            $f['docente_nombre'] = Auth::formatearNombreUsuario($f['docente_nombre']);
            $prom = (float)($f['promedio_escala_5'] ?? 0);
            $f['nivel_desempeno'] = $this->clasificarDesempeno($prom);
            $totalAsig = (int)$f['total_asignados'];
            $totalComp = (int)$f['total_completados'];
            $f['porcentaje_participacion'] = $totalAsig > 0 ? round(($totalComp / $totalAsig) * 100, 1) : 0;
        }

        return $filas;
    }

    /**
     * Clasifica el desempeño según el promedio en escala de 1 a 5
     */
    public function clasificarDesempeno($promedio5)
    {
        if ($promedio5 >= 4.5) {
            return ['etiqueta' => 'Excelente / Destacado', 'badge' => 'badge-success', 'color' => '#28a745'];
        } elseif ($promedio5 >= 3.75) {
            return ['etiqueta' => 'Muy Bueno / Competente', 'badge' => 'badge-primary', 'color' => '#007bff'];
        } elseif ($promedio5 >= 3.25) {
            return ['etiqueta' => 'Bueno / Aceptable', 'badge' => 'badge-info', 'color' => '#17a2b8'];
        } elseif ($promedio5 >= 2.75) {
            return ['etiqueta' => 'Regular / En Mejora', 'badge' => 'badge-warning', 'color' => '#ffc107'];
        } else {
            return ['etiqueta' => 'Deficiente / Requiere Atención', 'badge' => 'badge-danger', 'color' => '#dc3545'];
        }
    }
}
