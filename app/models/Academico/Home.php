<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Home extends Model
{
    /**
     * Obtiene las unidades didácticas asignadas a un docente en el periodo y sede especificados.
     */
    public function getUnidadesDocente($id_docente, $id_periodo, $id_sede)
    {
        $sql = "SELECT 
                    pud.id AS id_programacion_ud,
                    ud.id AS id_unidad_didactica,
                    ud.nombre AS unidad_nombre,
                    pud.turno,
                    pud.seccion,
                    COALESCE(pr.nombre, 'General') AS programa_nombre,
                    COALESCE(s.descripcion, 'Semestre') AS semestre_nombre,
                    COALESCE(sil.id, 0) AS id_silabo
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                LEFT JOIN sigi_semestre s ON ud.id_semestre = s.id
                LEFT JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                LEFT JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                LEFT JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                WHERE pud.id_docente = ? 
                  AND pud.id_periodo_academico = ? 
                  AND pud.id_sede = ?
                ORDER BY ud.nombre ASC, pud.seccion ASC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_docente, $id_periodo, $id_sede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el avance de sesiones desarrolladas en base a las actividades del sílabo.
     */
    public function getAvanceSesionesSilabo($idPud)
    {
        $stmt = self::$db->prepare("SELECT COUNT(pas.id) as total_actividades, 
                                           COUNT(sa.id) as total_sesiones 
                                    FROM acad_programacion_actividades_silabo pas 
                                    INNER JOIN acad_silabos s ON pas.id_silabo = s.id 
                                    LEFT JOIN acad_sesion_aprendizaje sa 
                                           ON sa.id_prog_actividad_silabo = pas.id 
                                          AND sa.logro_sesion <> '' 
                                          AND sa.logro_sesion IS NOT NULL 
                                    WHERE s.id_prog_unidad_didactica = ?");
        $stmt->execute([$idPud]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalActividades = (int)($row['total_actividades'] ?? 0);
        $totalSesiones = (int)($row['total_sesiones'] ?? 0);
        $porcentaje = ($totalActividades > 0) ? min(100, round(($totalSesiones / $totalActividades) * 100, 1)) : 0;

        return [
            'total_actividades' => $totalActividades,
            'total_sesiones' => $totalSesiones,
            'porcentaje' => $porcentaje
        ];
    }

    /**
     * Obtiene los registros de asistencia agrupados por detalle de matrícula.
     */
    public function getAsistenciasPorDetalle(array $idsDetalle)
    {
        $asistencias = [];
        if (empty($idsDetalle)) {
            return $asistencias;
        }

        $inPlaceholders = implode(',', array_fill(0, count($idsDetalle), '?'));
        $sql = "SELECT id_detalle_matricula, 
                       COUNT(CASE WHEN asistencia <> '' AND asistencia IS NOT NULL THEN 1 ELSE NULL END) as total_sesiones, 
                       SUM(CASE WHEN asistencia = 'F' THEN 1 ELSE 0 END) as faltas 
                FROM acad_asistencia 
                WHERE id_detalle_matricula IN ($inPlaceholders) 
                GROUP BY id_detalle_matricula";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($idsDetalle);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asistencias[$r['id_detalle_matricula']] = [
                'total' => (int)$r['total_sesiones'],
                'faltas' => (int)$r['faltas']
            ];
        }
        return $asistencias;
    }

    /**
     * Obtiene el consolidado de datos para el Dashboard del Docente,
     * reutilizando el modelo Calificaciones para calcular el Promedio Final de Indicadores.
     */
    public function getDatosDashboardDocente($id_docente, $id_periodo, $id_sede, Calificaciones $objCalificaciones)
    {
        $unidadesDocente = $this->getUnidadesDocente($id_docente, $id_periodo, $id_sede);
        $uds_count = count($unidadesDocente);
        $esDocente = ($uds_count > 0 || (class_exists('\Core\Auth') && \Core\Auth::esDocenteAcademico()));

        $unidadesDetalle = [];
        $alertasAcademicas = [];
        $alertasAsistencia = [];

        $totalEstudiantesMatriculados = 0;
        $totalSilabosRegistrados = 0;
        $totalSilabosPendientes = 0;
        $sumaPorcAvanceSesiones = 0;
        $sumaPorcAvanceCalificaciones = 0;

        $globalAprobados = 0;
        $globalEnRiesgo = 0;
        $globalDesaprobados = 0;
        $globalSinCalificar = 0;

        $chartLabels = [];
        $chartSesiones = [];
        $chartCalificaciones = [];

        if ($uds_count > 0) {
            foreach ($unidadesDocente as $ud) {
                $idPud = (int)$ud['id_programacion_ud'];

                // 1. Estado del Sílabo y avance de sesiones
                $tieneSilabo = !empty($ud['id_silabo']);
                if ($tieneSilabo) {
                    $totalSilabosRegistrados++;
                } else {
                    $totalSilabosPendientes++;
                }

                $avanceSesiones = $this->getAvanceSesionesSilabo($idPud);
                $totalActividades = $avanceSesiones['total_actividades'];
                $totalSesiones = $avanceSesiones['total_sesiones'];
                $porcAvanceSesiones = $avanceSesiones['porcentaje'];
                $sumaPorcAvanceSesiones += $porcAvanceSesiones;

                // 2. Reutilización del Modelo Calificaciones para obtener datos oficiales de la UD
                $datosCalif = $objCalificaciones->getDatosCalificaciones($idPud);
                $estudiantes = $datosCalif['estudiantes'] ?? [];
                $nrosCalif = $datosCalif['nros_calificacion'] ?? [];
                $promediosIndicadores = $datosCalif['promedios'] ?? [];
                $recuperacionesUD = $datosCalif['recuperaciones'] ?? [];
                $notasIndicadores = $datosCalif['notas'] ?? [];

                $cantEstudiantes = count($estudiantes);
                $totalEstudiantesMatriculados += $cantEstudiantes;

                // Conteo de indicadores evaluados vs esperados
                $totalIndicadoresEsperados = count($nrosCalif) * $cantEstudiantes;
                $indicadoresEvaluados = 0;
                foreach ($estudiantes as $e) {
                    $idD = (int)$e['id_detalle_matricula'];
                    foreach ($nrosCalif as $nro) {
                        if (isset($notasIndicadores[$idD][$nro]) && $notasIndicadores[$idD][$nro] !== '') {
                            $indicadoresEvaluados++;
                        }
                    }
                }

                $porcAvanceCalif = ($totalIndicadoresEsperados > 0) ? min(100, round(($indicadoresEvaluados / $totalIndicadoresEsperados) * 100, 1)) : 0;
                $sumaPorcAvanceCalificaciones += $porcAvanceCalif;

                // 3. Asistencias para alertas de inasistencia (DPI)
                $idsDetalle = array_column($estudiantes, 'id_detalle_matricula');
                $asistenciasPorDetalle = $this->getAsistenciasPorDetalle($idsDetalle);

                // 4. Evaluación Estudiante por Estudiante basada en el Promedio Final de Indicadores
                $udAprobados = 0;
                $udEnRiesgo = 0;
                $udDesaprobados = 0;
                $udSinCalificar = 0;

                foreach ($estudiantes as $est) {
                    $idDet = (int)$est['id_detalle_matricula'];
                    $nombreEst = $est['apellidos_nombres'];

                    // Inhabilitación oficial por DPI (> 30% inasistencias)
                    $esInhabilitadoDPI = $objCalificaciones->inhabilitadoPorInasistencia($idDet);

                    // Promedio final de indicadores calculado por el sistema
                    $promIndicadores = $promediosIndicadores[$idDet] ?? '';
                    $recup = $recuperacionesUD[$idDet] ?? '';

                    $promedioFinal = null;
                    if ($promIndicadores !== '' && is_numeric($promIndicadores)) {
                        $promedioFinal = (float)$promIndicadores;
                        // Si el promedio de indicadores fue desaprobatorio (< 13) y el alumno rindió recuperación:
                        if ($promedioFinal < 13 && $recup !== '' && is_numeric($recup)) {
                            $promedioFinal = (float)$recup;
                        }
                    } elseif ($recup !== '' && is_numeric($recup)) {
                        $promedioFinal = (float)$recup;
                    }

                    // Si está inhabilitado por inasistencia (DPI), no puede aprobar
                    if ($esInhabilitadoDPI && $promedioFinal !== null) {
                        $promedioFinal = 0;
                    }

                    if ($promedioFinal !== null) {
                        if ($promedioFinal >= 13) {
                            $udAprobados++;
                            $globalAprobados++;
                        } elseif ($promedioFinal >= 10 && $promedioFinal < 13 && !$esInhabilitadoDPI) {
                            $udEnRiesgo++;
                            $globalEnRiesgo++;
                            $alertasAcademicas[] = [
                                'id_pud' => $idPud,
                                'id_estudiante' => $est['id'] ?? 0,
                                'dni' => $est['dni'],
                                'nombre' => $nombreEst,
                                'unidad' => $ud['unidad_nombre'],
                                'programa' => $ud['programa_nombre'],
                                'turno' => $ud['turno'],
                                'seccion' => $ud['seccion'],
                                'promedio' => $promedioFinal,
                                'tipo' => 'recuperacion',
                                'mensaje' => 'Promedio de Indicadores en recuperación (10 - 12)'
                            ];
                        } else {
                            $udDesaprobados++;
                            $globalDesaprobados++;
                            $alertasAcademicas[] = [
                                'id_pud' => $idPud,
                                'id_estudiante' => $est['id'] ?? 0,
                                'dni' => $est['dni'],
                                'nombre' => $nombreEst,
                                'unidad' => $ud['unidad_nombre'],
                                'programa' => $ud['programa_nombre'],
                                'turno' => $ud['turno'],
                                'seccion' => $ud['seccion'],
                                'promedio' => $promedioFinal,
                                'tipo' => 'desaprobado',
                                'mensaje' => $esInhabilitadoDPI ? 'Inhabilitado por DPI (>30% inasistencias)' : 'Desaprobado en Promedio de Indicadores (< 10)'
                            ];
                        }
                    } else {
                        $udSinCalificar++;
                        $globalSinCalificar++;
                    }

                    // Alertas de inasistencia (sobre sesiones reales evaluadas)
                    if (isset($asistenciasPorDetalle[$idDet])) {
                        $totSes = $asistenciasPorDetalle[$idDet]['total'];
                        $faltas = $asistenciasPorDetalle[$idDet]['faltas'];
                        if ($totSes > 0) {
                            $porcFaltas = round(($faltas / $totSes) * 100, 1);
                            if ($porcFaltas >= 20 || $esInhabilitadoDPI) {
                                $alertasAsistencia[] = [
                                    'id_pud' => $idPud,
                                    'id_estudiante' => $est['id'] ?? 0,
                                    'dni' => $est['dni'],
                                    'nombre' => $nombreEst,
                                    'unidad' => $ud['unidad_nombre'],
                                    'programa' => $ud['programa_nombre'],
                                    'turno' => $ud['turno'],
                                    'seccion' => $ud['seccion'],
                                    'total_sesiones' => $totSes,
                                    'faltas' => $faltas,
                                    'porcentaje' => $porcFaltas,
                                    'estado' => $esInhabilitadoDPI ? 'inhabilitado' : 'riesgo',
                                    'mensaje' => $esInhabilitadoDPI ? 'Inhabilitado por Inasistencia (> 30% DPI)' : 'Riesgo de Inhabilitación (≥ 20%)'
                                ];
                            }
                        }
                    }
                } // fin foreach estudiantes

                $nombreCortoUD = mb_strimwidth($ud['unidad_nombre'], 0, 22, '...') . ' (' . $ud['seccion'] . ')';
                $chartLabels[] = $nombreCortoUD;
                $chartSesiones[] = $porcAvanceSesiones;
                $chartCalificaciones[] = $porcAvanceCalif;

                $unidadesDetalle[] = [
                    'id_programacion_ud' => $idPud,
                    'unidad_nombre' => $ud['unidad_nombre'],
                    'programa_nombre' => $ud['programa_nombre'],
                    'semestre' => $ud['semestre_nombre'],
                    'turno' => $ud['turno'],
                    'seccion' => $ud['seccion'],
                    'tiene_silabo' => $tieneSilabo,
                    'total_actividades' => $totalActividades,
                    'total_sesiones' => $totalSesiones,
                    'porc_sesiones' => $porcAvanceSesiones,
                    'total_estudiantes' => $cantEstudiantes,
                    'total_criterios' => $totalIndicadoresEsperados,
                    'criterios_calificados' => $indicadoresEvaluados,
                    'porc_calificaciones' => $porcAvanceCalif,
                    'aprobados' => $udAprobados,
                    'en_riesgo' => $udEnRiesgo,
                    'desaprobados' => $udDesaprobados,
                    'sin_calificar' => $udSinCalificar,
                ];
            } // fin foreach unidadesDocente
        }

        $promedioGlobalSesiones = ($uds_count > 0) ? round($sumaPorcAvanceSesiones / $uds_count, 1) : 0;
        $promedioGlobalCalificaciones = ($uds_count > 0) ? round($sumaPorcAvanceCalificaciones / $uds_count, 1) : 0;

        $resumenDocente = [
            'total_estudiantes' => $totalEstudiantesMatriculados,
            'silabos_registrados' => $totalSilabosRegistrados,
            'silabos_pendientes' => $totalSilabosPendientes,
            'promedio_sesiones' => $promedioGlobalSesiones,
            'promedio_calificaciones' => $promedioGlobalCalificaciones,
            'aprobados' => $globalAprobados,
            'en_riesgo' => $globalEnRiesgo,
            'desaprobados' => $globalDesaprobados,
            'sin_calificar' => $globalSinCalificar,
            'total_alertas_academicas' => count($alertasAcademicas),
            'total_alertas_asistencia' => count($alertasAsistencia),
        ];

        $chartRendimiento = [
            'aprobados' => $globalAprobados,
            'en_riesgo' => $globalEnRiesgo,
            'desaprobados' => $globalDesaprobados,
            'sin_calificar' => $globalSinCalificar,
        ];

        return [
            'esDocente' => $esDocente,
            'uds_count' => $uds_count,
            'unidadesDetalle' => $unidadesDetalle,
            'alertasAcademicas' => $alertasAcademicas,
            'alertasAsistencia' => $alertasAsistencia,
            'resumenDocente' => $resumenDocente,
            'chartLabels' => $chartLabels,
            'chartSesiones' => $chartSesiones,
            'chartCalificaciones' => $chartCalificaciones,
            'chartRendimiento' => $chartRendimiento,
        ];
    }

    /**
     * Obtiene los indicadores institucionales generales para coordinadores y administradores.
     */
    public function getResumenInstitucional()
    {
        // 1. Periodo actual
        $periodo = self::$db->query("SELECT nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")
            ->fetchColumn();

        // 2. Cantidad de sedes
        $sedes_count = self::$db->query("SELECT COUNT(*) FROM sigi_sedes")->fetchColumn();

        // 3. Cantidad de programas de estudio
        $programas = self::$db->query("SELECT COUNT(*) FROM sigi_programa_estudios")->fetchColumn();

        // 4. Cantidad de docentes
        $docentes = self::$db->query("SELECT COUNT(*) FROM sigi_usuarios WHERE id_rol IN (SELECT id FROM sigi_roles WHERE nombre LIKE '%DOCENTE%')")->fetchColumn();

        // 5. Total estudiantes matriculados
        $estudiantes_count = self::$db->query("SELECT COUNT(DISTINCT id_estudiante) FROM acad_matricula")->fetchColumn();

        return [
            'periodo' => $periodo ?: 'Sin Periodo Activo',
            'sedes_count' => (int)$sedes_count,
            'programas' => (int)$programas,
            'docentes' => (int)$docentes,
            'estudiantes_count' => (int)$estudiantes_count,
        ];
    }
}
