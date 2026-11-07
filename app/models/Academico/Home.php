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

    /**
     * Formatea cadenas de apellidos y nombres separadas por guión bajo.
     */
    public function formatearNombreUsuario($nombre)
    {
        if (empty($nombre)) return 'Sin Asignar';
        if (strpos($nombre, '_') !== false) {
            $parts = explode('_', trim($nombre));
            if (count($parts) >= 3) {
                return $parts[0] . ' ' . $parts[1] . ', ' . $parts[2];
            }
            return implode(' ', $parts);
        }
        return $nombre;
    }

    /**
     * Obtiene los programas de estudio asignados al coordinador/jefe de área.
     * Prioriza la asignación en el periodo actual y sede, con fallback a cualquier asignación en la sede.
     */
    public function getProgramasCoordinador($id_usuario, $id_periodo, $id_sede)
    {
        // 1. Asignación directa en el periodo y sede de contexto
        $sql = "SELECT DISTINCT pe.id, pe.nombre, pe.codigo 
                FROM sigi_coordinador_pe_periodo cp 
                INNER JOIN sigi_programa_estudios pe ON cp.id_programa_estudio = pe.id 
                WHERE cp.id_usuario = ? AND cp.id_periodo = ? AND cp.id_sede = ?
                ORDER BY pe.nombre ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario, $id_periodo, $id_sede]);
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Si no hay registros para el periodo específico, buscar asignación en la sede
        if (empty($programas)) {
            $sqlFallback = "SELECT DISTINCT pe.id, pe.nombre, pe.codigo 
                            FROM sigi_coordinador_pe_periodo cp 
                            INNER JOIN sigi_programa_estudios pe ON cp.id_programa_estudio = pe.id 
                            WHERE cp.id_usuario = ? AND cp.id_sede = ?
                            ORDER BY pe.nombre ASC";
            $stmt = self::$db->prepare($sqlFallback);
            $stmt->execute([$id_usuario, $id_sede]);
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $programas;
    }

    /**
     * Obtiene los planes de estudio correspondientes a los programas dados.
     */
    public function getPlanesPorProgramas(array $programasIds)
    {
        if (empty($programasIds)) {
            return [];
        }
        $in = implode(',', array_fill(0, count($programasIds), '?'));
        $sql = "SELECT id, nombre, id_programa_estudios 
                FROM sigi_planes_estudio 
                WHERE id_programa_estudios IN ($in) 
                ORDER BY nombre DESC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute($programasIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el listado de periodos académicos para selector histórico.
     */
    public function getPeriodosAcademicos()
    {
        $sql = "SELECT id, nombre, fecha_inicio, fecha_fin 
                FROM sigi_periodo_academico 
                ORDER BY fecha_inicio DESC";
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los semestres registrados en el catálogo.
     */
    public function getSemestresCatalogo()
    {
        $sql = "SELECT DISTINCT descripcion FROM sigi_semestre ORDER BY id ASC";
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtiene los datos consolidados para el Dashboard de Coordinador / Jefe de Área.
     */
    public function getDatosDashboardCoordinador($id_usuario, $id_periodo_contexto, $id_sede, $periodo_filtro, Calificaciones $objCalificaciones)
    {
        $programas = $this->getProgramasCoordinador($id_usuario, $id_periodo_contexto, $id_sede);
        if (empty($programas)) {
            return [
                'esCoordinador' => false,
                'programasCoordinador' => [],
                'planesCoordinador' => [],
                'periodosAcademicos' => [],
                'semestresCatalogo' => [],
                'resumenCoordinador' => [
                    'total_programas' => 0,
                    'total_uds' => 0,
                    'total_docentes' => 0,
                    'total_estudiantes' => 0,
                    'sesiones_desarrolladas' => 0,
                    'sesiones_programadas' => 0,
                    'porcentaje_sesiones' => 0,
                    'notas_registradas' => 0,
                    'notas_esperadas' => 0,
                    'porcentaje_calificaciones' => 0,
                    'total_alertas_academicas' => 0,
                    'total_alertas_asistencia' => 0,
                ],
                'alertasAcademicasCoord' => [],
                'alertasAsistenciaCoord' => [],
                'chartLabelsCoord' => [],
                'chartSesionesCoord' => [],
                'chartCalificacionesCoord' => [],
                'chartRendimientoCoord' => [
                    'aprobados' => 0,
                    'en_riesgo' => 0,
                    'desaprobados' => 0,
                    'sin_calificar' => 0,
                ],
                'unidadesTablaCoord' => [],
                'periodoFiltroSeleccionado' => $id_periodo_contexto,
            ];
        }

        $progIds = array_column($programas, 'id');
        $inProgs = implode(',', array_fill(0, count($progIds), '?'));

        $planes = $this->getPlanesPorProgramas($progIds);
        $periodos = $this->getPeriodosAcademicos();
        $semestres = $this->getSemestresCatalogo();

        // 1. REPORTE ESTADÍSTICO Y ALERTAS EN EL PERIODO Y SEDE DE CONTEXTO
        $sqlContexto = "SELECT 
                            pud.id AS id_programacion_ud,
                            ud.id AS id_unidad_didactica,
                            ud.nombre AS unidad_nombre,
                            pud.turno,
                            pud.seccion,
                            pud.id_periodo_academico,
                            pa.nombre AS periodo_nombre,
                            pe.id AS id_programa_estudio,
                            pe.nombre AS programa_nombre,
                            pl.id AS id_plan_estudio,
                            pl.nombre AS plan_nombre,
                            s.id AS id_semestre,
                            s.descripcion AS semestre_nombre,
                            pud.id_docente,
                            COALESCE(u.apellidos_nombres, 'Sin Docente Asignado') AS docente_nombre,
                            COALESCE(sil.id, 0) AS id_silabo
                        FROM acad_programacion_unidad_didactica pud
                        INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                        LEFT JOIN sigi_semestre s ON ud.id_semestre = s.id
                        LEFT JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                        LEFT JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                        LEFT JOIN sigi_programa_estudios pe ON pl.id_programa_estudios = pe.id
                        LEFT JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
                        LEFT JOIN sigi_usuarios u ON pud.id_docente = u.id
                        LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                        WHERE pe.id IN ($inProgs)
                          AND pud.id_periodo_academico = ?
                          AND pud.id_sede = ?
                        ORDER BY s.id ASC, ud.nombre ASC";

        $paramsContexto = array_merge($progIds, [$id_periodo_contexto, $id_sede]);
        $stmtContexto = self::$db->prepare($sqlContexto);
        $stmtContexto->execute($paramsContexto);
        $udsContexto = $stmtContexto->fetchAll(PDO::FETCH_ASSOC);

        $pudIdsContexto = array_column($udsContexto, 'id_programacion_ud');
        $sesionesMap = [];
        $matriculadosMap = [];

        if (!empty($pudIdsContexto)) {
            $inContexto = implode(',', array_fill(0, count($pudIdsContexto), '?'));

            // Conteo de estudiantes por PUD
            $stmtMat = self::$db->prepare("SELECT id_programacion_ud, COUNT(DISTINCT id_matricula) as total 
                                           FROM acad_detalle_matricula 
                                           WHERE id_programacion_ud IN ($inContexto) 
                                           GROUP BY id_programacion_ud");
            $stmtMat->execute($pudIdsContexto);
            while ($rm = $stmtMat->fetch(PDO::FETCH_ASSOC)) {
                $matriculadosMap[$rm['id_programacion_ud']] = (int)$rm['total'];
            }

            // Avance de sesiones por PUD
            $stmtSes = self::$db->prepare("SELECT s.id_prog_unidad_didactica,
                                                  COUNT(pas.id) as total_actividades,
                                                  COUNT(sa.id) as total_sesiones
                                           FROM acad_silabos s
                                           LEFT JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
                                           LEFT JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id 
                                                 AND sa.logro_sesion <> '' 
                                                 AND sa.logro_sesion IS NOT NULL
                                           WHERE s.id_prog_unidad_didactica IN ($inContexto)
                                           GROUP BY s.id_prog_unidad_didactica");
            $stmtSes->execute($pudIdsContexto);
            while ($rs = $stmtSes->fetch(PDO::FETCH_ASSOC)) {
                $sesionesMap[$rs['id_prog_unidad_didactica']] = $rs;
            }
        }

        $docentesUnicos = [];
        $totalSesionesProg = 0;
        $totalSesionesDesarrolladas = 0;
        $totalNotasEsperadas = 0;
        $totalNotasRegistradas = 0;

        $globalAprobados = 0;
        $globalEnRiesgo = 0;
        $globalDesaprobados = 0;
        $globalSinCalificar = 0;

        $chartLabelsCoord = [];
        $chartSesionesCoord = [];
        $chartCalificacionesCoord = [];

        $alertasAcademicasCoord = [];
        $alertasAsistenciaCoord = [];

        // Conteo oficial de estudiantes matriculados únicos exclusivamente en los programas de estudio asignados
        $sqlMatriculados = "SELECT DISTINCT m.id_estudiante, ep.id_usuario 
                            FROM acad_matricula m
                            INNER JOIN acad_estudiante_programa ep ON m.id_estudiante = ep.id
                            INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
                            WHERE pl.id_programa_estudios IN ($inProgs)
                              AND m.id_periodo_academico = ?
                              AND m.id_sede = ?";
        $stmtMatUnicos = self::$db->prepare($sqlMatriculados);
        $paramsMat = array_merge($progIds, [$id_periodo_contexto, $id_sede]);
        $stmtMatUnicos->execute($paramsMat);
        $estudiantesMatriculadosProg = $stmtMatUnicos->fetchAll(PDO::FETCH_ASSOC);

        $totalEstudiantesContexto = count($estudiantesMatriculadosProg);

        // Mapa para consolidar el rendimiento académico por estudiante único del programa
        $studentPerformanceMap = [];
        foreach ($estudiantesMatriculadosProg as $estMat) {
            $studentPerformanceMap[$estMat['id_usuario']] = [
                'notas' => [],
                'has_desaprobado' => false,
                'has_riesgo' => false
            ];
        }

        foreach ($udsContexto as $u) {
            $idPud = (int)$u['id_programacion_ud'];
            if (!empty($u['id_docente'])) {
                $docentesUnicos[$u['id_docente']] = true;
            }

            // Sesiones
            $sData = $sesionesMap[$idPud] ?? ['total_actividades' => 0, 'total_sesiones' => 0];
            $actividadesUd = (int)$sData['total_actividades'];
            $sesionesUd = (int)$sData['total_sesiones'];
            $totalSesionesProg += $actividadesUd;
            $totalSesionesDesarrolladas += $sesionesUd;
            $pctSesionesUd = $actividadesUd > 0 ? min(100, round(($sesionesUd / $actividadesUd) * 100, 1)) : 0;

            // Calificaciones & Alertas
            $infoCalif = $objCalificaciones->getDatosCalificaciones($idPud);
            $nrosCalif = $infoCalif['nros_calificacion'] ?? [];
            $estudiantesUd = $infoCalif['estudiantes'] ?? [];
            $notasUd = $infoCalif['notas'] ?? [];
            $promediosUd = $infoCalif['promedios'] ?? [];
            $recuperacionesUD = $infoCalif['recuperaciones'] ?? [];

            $cantNros = count($nrosCalif);
            $cantEst = count($estudiantesUd);
            $notasEsperadasUd = $cantNros * $cantEst;
            $notasRegistradasUd = 0;

            foreach ($notasUd as $idDet => $mapNro) {
                foreach ($mapNro as $val) {
                    if ($val !== '' && $val !== null) {
                        $notasRegistradasUd++;
                    }
                }
            }

            $totalNotasEsperadas += $notasEsperadasUd;
            $totalNotasRegistradas += $notasRegistradasUd;
            $pctCalifUd = $notasEsperadasUd > 0 ? min(100, round(($notasRegistradasUd / $notasEsperadasUd) * 100, 1)) : 0;

            // Datos para gráficos comparativos
            $chartLabelsCoord[] = mb_substr($u['unidad_nombre'], 0, 22) . (mb_strlen($u['unidad_nombre']) > 22 ? '...' : '') . ' (' . $u['semestre_nombre'] . ')';
            $chartSesionesCoord[] = $pctSesionesUd;
            $chartCalificacionesCoord[] = $pctCalifUd;

            // Asistencias y alertas tempranas
            $idsDetalleUd = array_column($estudiantesUd, 'id_detalle_matricula');
            $asistenciasMapUd = $this->getAsistenciasPorDetalle($idsDetalleUd);
            $docenteNombreFmt = $this->formatearNombreUsuario($u['docente_nombre']);

            foreach ($estudiantesUd as $est) {
                $idDet = (int)$est['id_detalle_matricula'];
                $nombreEst = $est['apellidos_nombres'];
                $esInhabilitadoDPI = $objCalificaciones->inhabilitadoPorInasistencia($idDet);

                $promIndicadores = $promediosUd[$idDet] ?? '';
                $recup = $recuperacionesUD[$idDet] ?? '';

                $promedioFinal = null;
                if ($promIndicadores !== '' && is_numeric($promIndicadores)) {
                    $promedioFinal = (float)$promIndicadores;
                    if ($promedioFinal < 13 && $recup !== '' && is_numeric($recup)) {
                        $promedioFinal = (float)$recup;
                    }
                } elseif ($recup !== '' && is_numeric($recup)) {
                    $promedioFinal = (float)$recup;
                }

                if ($esInhabilitadoDPI && $promedioFinal !== null) {
                    $promedioFinal = 0;
                }

                $uId = (int)($est['id'] ?? 0);
                if (isset($studentPerformanceMap[$uId])) {
                    if ($promedioFinal !== null) {
                        $studentPerformanceMap[$uId]['notas'][] = $promedioFinal;
                        if ($promedioFinal < 10 || $esInhabilitadoDPI) {
                            $studentPerformanceMap[$uId]['has_desaprobado'] = true;
                        } elseif ($promedioFinal >= 10 && $promedioFinal < 13) {
                            $studentPerformanceMap[$uId]['has_riesgo'] = true;
                        }
                    }
                }

                if ($promedioFinal !== null) {
                    if ($promedioFinal >= 10 && $promedioFinal < 13 && !$esInhabilitadoDPI) {
                        $alertasAcademicasCoord[] = [
                            'id_pud' => $idPud,
                            'id_detalle_matricula' => $idDet,
                            'id_estudiante' => $est['id'] ?? 0,
                            'dni' => $est['dni'],
                            'nombre' => $nombreEst,
                            'unidad' => $u['unidad_nombre'],
                            'programa' => $u['programa_nombre'],
                            'semestre' => $u['semestre_nombre'],
                            'docente' => $docenteNombreFmt,
                            'promedio' => $promedioFinal,
                            'tipo' => 'recuperacion',
                            'mensaje' => 'Promedio de Indicadores en recuperación (10 - 12)'
                        ];
                    } elseif ($promedioFinal < 10 || $esInhabilitadoDPI) {
                        $alertasAcademicasCoord[] = [
                            'id_pud' => $idPud,
                            'id_detalle_matricula' => $idDet,
                            'id_estudiante' => $est['id'] ?? 0,
                            'dni' => $est['dni'],
                            'nombre' => $nombreEst,
                            'unidad' => $u['unidad_nombre'],
                            'programa' => $u['programa_nombre'],
                            'semestre' => $u['semestre_nombre'],
                            'docente' => $docenteNombreFmt,
                            'promedio' => $promedioFinal,
                            'tipo' => 'desaprobado',
                            'mensaje' => $esInhabilitadoDPI ? 'Inhabilitado por DPI (>30% inasistencias)' : 'Desaprobado en Promedio de Indicadores (< 10)'
                        ];
                    }
                }

                // Alerta Asistencia
                $asistInfo = $asistenciasMapUd[$idDet] ?? ['total' => 0, 'faltas' => 0];
                $totalSesAsist = $asistInfo['total'];
                $faltas = $asistInfo['faltas'];
                $porcFaltas = ($totalSesAsist > 0) ? round(($faltas / $totalSesAsist) * 100, 1) : 0;

                if ($esInhabilitadoDPI || $porcFaltas >= 20) {
                    $alertasAsistenciaCoord[] = [
                        'id_pud' => $idPud,
                        'id_detalle_matricula' => $idDet,
                        'id_estudiante' => $est['id'] ?? 0,
                        'dni' => $est['dni'],
                        'nombre' => $nombreEst,
                        'unidad' => $u['unidad_nombre'],
                        'programa' => $u['programa_nombre'],
                        'semestre' => $u['semestre_nombre'],
                        'docente' => $docenteNombreFmt,
                        'total_sesiones' => $totalSesAsist,
                        'faltas' => $faltas,
                        'porcentaje' => $porcFaltas,
                        'estado' => $esInhabilitadoDPI ? 'inhabilitado' : 'riesgo'
                    ];
                }
            }
        }

        // Consolidación de rendimiento por estudiante único
        foreach ($studentPerformanceMap as $uId => $perf) {
            if (empty($perf['notas'])) {
                $globalSinCalificar++;
            } elseif ($perf['has_desaprobado']) {
                $globalDesaprobados++;
            } elseif ($perf['has_riesgo']) {
                $globalEnRiesgo++;
            } else {
                $globalAprobados++;
            }
        }

        $pctSesionesGlobal = $totalSesionesProg > 0 ? min(100, round(($totalSesionesDesarrolladas / $totalSesionesProg) * 100, 1)) : 0;
        $pctCalificacionesGlobal = $totalNotasEsperadas > 0 ? min(100, round(($totalNotasRegistradas / $totalNotasEsperadas) * 100, 1)) : 0;

        $resumenCoordinador = [
            'total_programas' => count($programas),
            'total_uds' => count($udsContexto),
            'total_docentes' => count($docentesUnicos),
            'total_estudiantes' => $totalEstudiantesContexto,
            'sesiones_desarrolladas' => $totalSesionesDesarrolladas,
            'sesiones_programadas' => $totalSesionesProg,
            'porcentaje_sesiones' => $pctSesionesGlobal,
            'notas_registradas' => $totalNotasRegistradas,
            'notas_esperadas' => $totalNotasEsperadas,
            'porcentaje_calificaciones' => $pctCalificacionesGlobal,
            'total_alertas_academicas' => count($alertasAcademicasCoord),
            'total_alertas_asistencia' => count($alertasAsistenciaCoord),
        ];

        // 2. TABLA DE GESTIÓN DE UNIDADES DIDÁCTICAS (Filtrada por Periodo seleccionado)
        $periodoTabla = !empty($periodo_filtro) ? (int)$periodo_filtro : (int)$id_periodo_contexto;

        $sqlTabla = "SELECT 
                        pud.id AS id_programacion_ud,
                        ud.id AS id_unidad_didactica,
                        ud.nombre AS unidad_nombre,
                        pud.turno,
                        pud.seccion,
                        pud.id_periodo_academico,
                        pa.nombre AS periodo_nombre,
                        pe.id AS id_programa_estudio,
                        pe.nombre AS programa_nombre,
                        pl.id AS id_plan_estudio,
                        pl.nombre AS plan_nombre,
                        s.id AS id_semestre,
                        s.descripcion AS semestre_nombre,
                        pud.id_docente,
                        COALESCE(u.apellidos_nombres, 'Sin Asignar') AS docente_nombre,
                        COALESCE(sil.id, 0) AS id_silabo
                    FROM acad_programacion_unidad_didactica pud
                    INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                    LEFT JOIN sigi_semestre s ON ud.id_semestre = s.id
                    LEFT JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                    LEFT JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                    LEFT JOIN sigi_programa_estudios pe ON pl.id_programa_estudios = pe.id
                    LEFT JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
                    LEFT JOIN sigi_usuarios u ON pud.id_docente = u.id
                    LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                    WHERE pe.id IN ($inProgs)
                      AND pud.id_sede = ?";

        $paramsTabla = array_merge($progIds, [$id_sede]);
        if ($periodoTabla > 0) {
            $sqlTabla .= " AND pud.id_periodo_academico = ?";
            $paramsTabla[] = $periodoTabla;
        }
        $sqlTabla .= " ORDER BY pa.fecha_inicio DESC, s.id ASC, ud.nombre ASC";

        $stmtTabla = self::$db->prepare($sqlTabla);
        $stmtTabla->execute($paramsTabla);
        $udsTablaRaw = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);

        $pudIdsTabla = array_column($udsTablaRaw, 'id_programacion_ud');
        $matTablaMap = [];
        $sesTablaMap = [];

        if (!empty($pudIdsTabla)) {
            $inTabla = implode(',', array_fill(0, count($pudIdsTabla), '?'));

            // Conteo de estudiantes
            $stmtMatT = self::$db->prepare("SELECT id_programacion_ud, COUNT(DISTINCT id_matricula) as total 
                                            FROM acad_detalle_matricula 
                                            WHERE id_programacion_ud IN ($inTabla) 
                                            GROUP BY id_programacion_ud");
            $stmtMatT->execute($pudIdsTabla);
            while ($rm = $stmtMatT->fetch(PDO::FETCH_ASSOC)) {
                $matTablaMap[$rm['id_programacion_ud']] = (int)$rm['total'];
            }

            // Avance de sesiones
            $stmtSesT = self::$db->prepare("SELECT s.id_prog_unidad_didactica,
                                                   COUNT(pas.id) as total_actividades,
                                                   COUNT(sa.id) as total_sesiones
                                            FROM acad_silabos s
                                            LEFT JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
                                            LEFT JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id 
                                                  AND sa.logro_sesion <> '' 
                                                  AND sa.logro_sesion IS NOT NULL
                                            WHERE s.id_prog_unidad_didactica IN ($inTabla)
                                            GROUP BY s.id_prog_unidad_didactica");
            $stmtSesT->execute($pudIdsTabla);
            while ($rs = $stmtSesT->fetch(PDO::FETCH_ASSOC)) {
                $sesTablaMap[$rs['id_prog_unidad_didactica']] = $rs;
            }
        }

        $unidadesTablaCoord = [];
        foreach ($udsTablaRaw as $u) {
            $idPud = (int)$u['id_programacion_ud'];
            $matriculados = $matTablaMap[$idPud] ?? 0;

            // Sesiones
            $sData = $sesTablaMap[$idPud] ?? ['total_actividades' => 0, 'total_sesiones' => 0];
            $actividades = (int)$sData['total_actividades'];
            $sesiones = (int)$sData['total_sesiones'];
            $pctSesiones = $actividades > 0 ? min(100, round(($sesiones / $actividades) * 100, 1)) : 0;

            // Calificaciones
            $infoCal = $objCalificaciones->getDatosCalificaciones($idPud);
            $nCal = count($infoCal['nros_calificacion'] ?? []);
            $nEst = count($infoCal['estudiantes'] ?? []);
            $esp = $nCal * $nEst;
            $reg = 0;
            foreach (($infoCal['notas'] ?? []) as $nm) {
                foreach ($nm as $v) {
                    if ($v !== '' && $v !== null) $reg++;
                }
            }
            $pctCal = $esp > 0 ? min(100, round(($reg / $esp) * 100, 1)) : 0;

            // Formatear turno
            $turnoTexto = 'Mañana';
            if ($u['turno'] === 'T') $turnoTexto = 'Tarde';
            elseif ($u['turno'] === 'N') $turnoTexto = 'Noche';
            $turnoSec = $turnoTexto . ' - ' . ($u['seccion'] ?: 'A');

            $unidadesTablaCoord[] = [
                'id_programacion_ud' => $idPud,
                'id_unidad_didactica' => $u['id_unidad_didactica'],
                'unidad_nombre' => $u['unidad_nombre'],
                'matriculados' => $matriculados,
                'id_programa_estudio' => $u['id_programa_estudio'],
                'programa_nombre' => $u['programa_nombre'],
                'id_plan_estudio' => $u['id_plan_estudio'],
                'plan_nombre' => $u['plan_nombre'],
                'id_periodo_academico' => $u['id_periodo_academico'],
                'periodo_nombre' => $u['periodo_nombre'],
                'id_semestre' => $u['id_semestre'],
                'semestre_nombre' => $u['semestre_nombre'],
                'turno_seccion' => $turnoSec,
                'id_docente' => $u['id_docente'],
                'docente_nombre' => $this->formatearNombreUsuario($u['docente_nombre']),
                'silabo_registrado' => (bool)$u['id_silabo'],
                'sesiones_registradas' => $sesiones,
                'sesiones_totales' => $actividades,
                'porcentaje_sesiones' => $pctSesiones,
                'calificaciones_registradas' => $reg,
                'calificaciones_totales' => $esp,
                'porcentaje_calificaciones' => $pctCal,
            ];
        }

        return [
            'esCoordinador' => true,
            'programasCoordinador' => $programas,
            'planesCoordinador' => $planes,
            'periodosAcademicos' => $periodos,
            'semestresCatalogo' => $semestres,
            'resumenCoordinador' => $resumenCoordinador,
            'alertasAcademicasCoord' => $alertasAcademicasCoord,
            'alertasAsistenciaCoord' => $alertasAsistenciaCoord,
            'chartLabelsCoord' => $chartLabelsCoord,
            'chartSesionesCoord' => $chartSesionesCoord,
            'chartCalificacionesCoord' => $chartCalificacionesCoord,
            'chartRendimientoCoord' => [
                'aprobados' => $globalAprobados,
                'en_riesgo' => $globalEnRiesgo,
                'desaprobados' => $globalDesaprobados,
                'sin_calificar' => $globalSinCalificar,
            ],
            'unidadesTablaCoord' => $unidadesTablaCoord,
            'periodoFiltroSeleccionado' => $periodoTabla,
        ];
    }

    /**
     * Obtiene todos los programas de estudio registrados en la institución.
     */
    public function getProgramasInstitucion($id_sede = null)
    {
        $sql = "SELECT id, nombre, codigo, tipo FROM sigi_programa_estudios ORDER BY nombre ASC";
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los planes de estudio registrados.
     */
    public function getPlanesTodos()
    {
        $sql = "SELECT id, nombre, id_programa_estudios FROM sigi_planes_estudio ORDER BY nombre DESC";
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos consolidados del Dashboard para la Jefatura de Unidad Académica y Dirección.
     * Permite visualización institucional macro o filtrado por programa de estudios específico.
     */
    public function getDatosDashboardAutoridad($id_usuario, $id_periodo_contexto, $id_sede, $periodo_filtro, Calificaciones $objCalificaciones, $id_programa_filtro = null)
    {
        $programas = $this->getProgramasInstitucion($id_sede);
        $planes = $this->getPlanesTodos();
        $periodos = $this->getPeriodosAcademicos();
        $semestres = $this->getSemestresCatalogo();

        // 1. REPORTE ESTADÍSTICO EN EL PERIODO Y SEDE DE CONTEXTO
        $sqlContexto = "SELECT 
                            pud.id AS id_programacion_ud,
                            ud.id AS id_unidad_didactica,
                            ud.nombre AS unidad_nombre,
                            pud.turno,
                            pud.seccion,
                            pud.id_periodo_academico,
                            pa.nombre AS periodo_nombre,
                            pe.id AS id_programa_estudio,
                            pe.nombre AS programa_nombre,
                            pe.codigo AS programa_codigo,
                            pl.id AS id_plan_estudio,
                            pl.nombre AS plan_nombre,
                            s.id AS id_semestre,
                            s.descripcion AS semestre_nombre,
                            pud.id_docente,
                            COALESCE(u.apellidos_nombres, 'Sin Docente Asignado') AS docente_nombre,
                            COALESCE(sil.id, 0) AS id_silabo
                        FROM acad_programacion_unidad_didactica pud
                        INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                        LEFT JOIN sigi_semestre s ON ud.id_semestre = s.id
                        LEFT JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                        LEFT JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                        LEFT JOIN sigi_programa_estudios pe ON pl.id_programa_estudios = pe.id
                        LEFT JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
                        LEFT JOIN sigi_usuarios u ON pud.id_docente = u.id
                        LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                        WHERE pud.id_periodo_academico = ?
                          AND pud.id_sede = ?
                        ORDER BY pe.id ASC, s.id ASC, ud.nombre ASC";

        $stmtContexto = self::$db->prepare($sqlContexto);
        $stmtContexto->execute([$id_periodo_contexto, $id_sede]);
        $udsContexto = $stmtContexto->fetchAll(PDO::FETCH_ASSOC);

        $pudIdsContexto = array_column($udsContexto, 'id_programacion_ud');

        // Conteo y avance de sesiones por PUD
        $sesionesMap = [];
        if (!empty($pudIdsContexto)) {
            $inContexto = implode(',', array_fill(0, count($pudIdsContexto), '?'));
            $sqlSes = "SELECT s.id_prog_unidad_didactica,
                              COUNT(pas.id) as total_actividades,
                              COUNT(sa.id) as total_sesiones
                       FROM acad_silabos s
                       LEFT JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
                       LEFT JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id 
                             AND sa.logro_sesion <> '' 
                             AND sa.logro_sesion IS NOT NULL
                       WHERE s.id_prog_unidad_didactica IN ($inContexto)
                       GROUP BY s.id_prog_unidad_didactica";
            $stmtSes = self::$db->prepare($sqlSes);
            $stmtSes->execute($pudIdsContexto);
            while ($rs = $stmtSes->fetch(PDO::FETCH_ASSOC)) {
                $sesionesMap[$rs['id_prog_unidad_didactica']] = $rs;
            }
        }

        // Conteo oficial de matriculados únicos por programa
        $sqlMatUnicos = "SELECT DISTINCT m.id_estudiante, ep.id_usuario, pl.id_programa_estudios
                         FROM acad_matricula m
                         INNER JOIN acad_estudiante_programa ep ON m.id_estudiante = ep.id
                         INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
                         WHERE m.id_periodo_academico = ?
                           AND m.id_sede = ?";
        $stmtMat = self::$db->prepare($sqlMatUnicos);
        $stmtMat->execute([$id_periodo_contexto, $id_sede]);
        $matriculadosContexto = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

        $matriculadosPorProg = [];
        $studentPerformanceMap = [];
        foreach ($matriculadosContexto as $mc) {
            $progId = (int)$mc['id_programa_estudios'];
            $matriculadosPorProg[$progId][$mc['id_estudiante']] = true;
            $studentPerformanceMap[$mc['id_usuario']] = [
                'id_programa' => $progId,
                'notas' => [],
                'has_desaprobado' => false,
                'has_riesgo' => false
            ];
        }

        // Carga bulk de Detalle Matrícula, Asistencias y Calificaciones
        $detallesByPud = [];
        $detIdsContexto = [];
        if (!empty($pudIdsContexto)) {
            $inContexto = implode(',', array_fill(0, count($pudIdsContexto), '?'));
            $sqlDet = "SELECT dm.id, dm.id_programacion_ud, dm.id_matricula, dm.recuperacion,
                              u.id as id_usuario, u.dni, u.apellidos_nombres
                       FROM acad_detalle_matricula dm
                       INNER JOIN acad_matricula m ON m.id = dm.id_matricula
                       INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
                       INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
                       WHERE dm.id_programacion_ud IN ($inContexto)
                       ORDER BY TRIM(CONVERT(u.apellidos_nombres USING utf8mb4)) COLLATE utf8mb4_spanish_ci ASC";
            $stmtDet = self::$db->prepare($sqlDet);
            $stmtDet->execute($pudIdsContexto);
            while ($rd = $stmtDet->fetch(PDO::FETCH_ASSOC)) {
                $detallesByPud[$rd['id_programacion_ud']][] = $rd;
                $detIdsContexto[] = $rd['id'];
            }
        }

        // Asistencias bulk
        $asistenciasMap = [];
        if (!empty($detIdsContexto)) {
            $inDets = implode(',', array_fill(0, count($detIdsContexto), '?'));
            $sqlAsis = "SELECT id_detalle_matricula, 
                               COUNT(CASE WHEN asistencia <> '' AND asistencia IS NOT NULL THEN 1 ELSE NULL END) as total_sesiones, 
                               SUM(CASE WHEN asistencia = 'F' THEN 1 ELSE 0 END) as faltas 
                        FROM acad_asistencia 
                        WHERE id_detalle_matricula IN ($inDets) 
                        GROUP BY id_detalle_matricula";
            $stmtAsis = self::$db->prepare($sqlAsis);
            $stmtAsis->execute($detIdsContexto);
            while ($ra = $stmtAsis->fetch(PDO::FETCH_ASSOC)) {
                $asistenciasMap[$ra['id_detalle_matricula']] = [
                    'total' => (int)$ra['total_sesiones'],
                    'faltas' => (int)$ra['faltas']
                ];
            }
        }

        // Calificaciones bulk
        $califsByDetalle = [];
        $calIdsContexto = [];
        if (!empty($detIdsContexto)) {
            $inDets = implode(',', array_fill(0, count($detIdsContexto), '?'));
            $sqlCal = "SELECT id, id_detalle_matricula, nro_calificacion FROM acad_calificacion WHERE id_detalle_matricula IN ($inDets)";
            $stmtCal = self::$db->prepare($sqlCal);
            $stmtCal->execute($detIdsContexto);
            while ($rc = $stmtCal->fetch(PDO::FETCH_ASSOC)) {
                $califsByDetalle[$rc['id_detalle_matricula']][$rc['nro_calificacion']] = $rc['id'];
                $calIdsContexto[] = $rc['id'];
            }
        }

        // Evaluaciones & Criterios bulk
        $evalsByCal = [];
        if (!empty($calIdsContexto)) {
            $inCals = implode(',', array_fill(0, count($calIdsContexto), '?'));
            $sqlEv = "SELECT ev.id as id_evaluacion, ev.id_calificacion, ev.ponderado, ce.calificacion
                      FROM acad_evaluacion ev
                      LEFT JOIN acad_criterio_evaluacion ce ON ce.id_evaluacion = ev.id AND ce.detalle <> ''
                      WHERE ev.id_calificacion IN ($inCals)";
            $stmtEv = self::$db->prepare($sqlEv);
            $stmtEv->execute($calIdsContexto);
            while ($re = $stmtEv->fetch(PDO::FETCH_ASSOC)) {
                $idCal = $re['id_calificacion'];
                $idEv = $re['id_evaluacion'];
                if (!isset($evalsByCal[$idCal][$idEv])) {
                    $evalsByCal[$idCal][$idEv] = [
                        'ponderado' => (int)$re['ponderado'],
                        'criterios' => []
                    ];
                }
                if ($re['calificacion'] !== null && $re['calificacion'] !== '') {
                    $evalsByCal[$idCal][$idEv]['criterios'][] = intval($re['calificacion']);
                }
            }
        }

        // Estructura de comparativa por programa
        $progMetrics = [];
        foreach ($programas as $p) {
            $pId = (int)$p['id'];
            $progMetrics[$pId] = [
                'id' => $pId,
                'nombre' => $p['nombre'],
                'codigo' => $p['codigo'],
                'total_uds' => 0,
                'total_silabos' => 0,
                'total_actividades' => 0,
                'total_sesiones' => 0,
                'total_notas_esperadas' => 0,
                'total_notas_registradas' => 0,
                'total_matriculados' => count($matriculadosPorProg[$pId] ?? []),
                'alertas_academicas' => 0,
                'alertas_asistencia' => 0,
            ];
        }

        $docentesUnicos = [];
        $alertasAcademicasAutoridad = [];
        $alertasAsistenciaAutoridad = [];

        foreach ($udsContexto as $u) {
            $idPud = (int)$u['id_programacion_ud'];
            $pId = (int)$u['id_programa_estudio'];
            if (!empty($u['id_docente'])) {
                $docentesUnicos[$u['id_docente']] = true;
            }

            if (isset($progMetrics[$pId])) {
                $progMetrics[$pId]['total_uds']++;
                if (!empty($u['id_silabo'])) {
                    $progMetrics[$pId]['total_silabos']++;
                }
            }

            // Sesiones
            $sData = $sesionesMap[$idPud] ?? ['total_actividades' => 0, 'total_sesiones' => 0];
            $actividadesUd = (int)$sData['total_actividades'];
            $sesionesUd = (int)$sData['total_sesiones'];
            if (isset($progMetrics[$pId])) {
                $progMetrics[$pId]['total_actividades'] += $actividadesUd;
                $progMetrics[$pId]['total_sesiones'] += $sesionesUd;
            }

            // Estudiantes y notas de esta UD
            $dets = $detallesByPud[$idPud] ?? [];
            $nrosUd = [];
            foreach ($dets as $d) {
                $idDet = $d['id'];
                if (isset($califsByDetalle[$idDet])) {
                    foreach (array_keys($califsByDetalle[$idDet]) as $nro) {
                        $nrosUd[$nro] = true;
                    }
                }
            }
            $nrosList = array_keys($nrosUd);
            sort($nrosList);

            $cantNros = count($nrosList);
            $cantEst = count($dets);
            $notasEsperadasUd = $cantNros * $cantEst;
            $notasRegistradasUd = 0;

            $docenteNombreFmt = $this->formatearNombreUsuario($u['docente_nombre']);

            foreach ($dets as $d) {
                $idDet = $d['id'];
                $uId = (int)$d['id_usuario'];
                $sumNotas = 0;
                $cntNotas = 0;

                foreach ($nrosList as $nro) {
                    $idCal = $califsByDetalle[$idDet][$nro] ?? 0;
                    if ($idCal && isset($evalsByCal[$idCal])) {
                        $sumPond = 0;
                        $cntEval = 0;
                        foreach ($evalsByCal[$idCal] as $ev) {
                            $crits = $ev['criterios'];
                            if (!empty($crits)) {
                                $promEv = round(array_sum($crits) / count($crits) + 0.00001, 0, PHP_ROUND_HALF_UP);
                                $sumPond += $promEv * $ev['ponderado'] / 100;
                                $cntEval++;
                            }
                        }
                        if ($cntEval > 0) {
                            $notaFinal = round($sumPond + 0.00001, 0, PHP_ROUND_HALF_UP);
                            $sumNotas += $notaFinal;
                            $cntNotas++;
                            $notasRegistradasUd++;
                        }
                    }
                }

                $promIndicadores = ($cntNotas > 0) ? round($sumNotas / $cntNotas + 0.00001, 0, PHP_ROUND_HALF_UP) : null;
                $recup = $d['recuperacion'];

                // Inhabilitado por DPI (>30% inasistencias)
                $asist = $asistenciasMap[$idDet] ?? ['total' => 0, 'faltas' => 0];
                $totSesAsist = $asist['total'];
                $faltas = $asist['faltas'];
                $porcFaltas = ($totSesAsist > 0) ? round(($faltas / $totSesAsist) * 100, 1) : 0;
                $esInhabilitadoDPI = ($porcFaltas > 30);

                $promFinal = null;
                if ($promIndicadores !== null) {
                    $promFinal = (float)$promIndicadores;
                    if ($promFinal < 13 && $recup !== '' && $recup !== null && is_numeric($recup)) {
                        $promFinal = (float)$recup;
                    }
                } elseif ($recup !== '' && $recup !== null && is_numeric($recup)) {
                    $promFinal = (float)$recup;
                }

                if ($esInhabilitadoDPI && $promFinal !== null) {
                    $promFinal = 0;
                }

                if (isset($studentPerformanceMap[$uId])) {
                    if ($promFinal !== null) {
                        $studentPerformanceMap[$uId]['notas'][] = $promFinal;
                        if ($promFinal < 10 || $esInhabilitadoDPI) {
                            $studentPerformanceMap[$uId]['has_desaprobado'] = true;
                        } elseif ($promFinal >= 10 && $promFinal < 13) {
                            $studentPerformanceMap[$uId]['has_riesgo'] = true;
                        }
                    }
                }

                // Alerta académica
                $nombreEstFmt = $this->formatearNombreUsuario($d['apellidos_nombres']);
                if ($promFinal !== null) {
                    if ($promFinal >= 10 && $promFinal < 13 && !$esInhabilitadoDPI) {
                        if (isset($progMetrics[$pId])) $progMetrics[$pId]['alertas_academicas']++;
                        $alertasAcademicasAutoridad[] = [
                            'id_pud' => $idPud,
                            'id_detalle_matricula' => $idDet,
                            'id_programa_estudio' => $pId,
                            'programa' => $u['programa_nombre'],
                            'id_estudiante' => $uId,
                            'dni' => $d['dni'],
                            'nombre' => $nombreEstFmt,
                            'unidad' => $u['unidad_nombre'],
                            'semestre' => $u['semestre_nombre'],
                            'docente' => $docenteNombreFmt,
                            'promedio' => $promFinal,
                            'tipo' => 'recuperacion',
                            'mensaje' => 'Promedio de Indicadores en recuperación (10 - 12)'
                        ];
                    } elseif ($promFinal < 10 || $esInhabilitadoDPI) {
                        if (isset($progMetrics[$pId])) $progMetrics[$pId]['alertas_academicas']++;
                        $alertasAcademicasAutoridad[] = [
                            'id_pud' => $idPud,
                            'id_detalle_matricula' => $idDet,
                            'id_programa_estudio' => $pId,
                            'programa' => $u['programa_nombre'],
                            'id_estudiante' => $uId,
                            'dni' => $d['dni'],
                            'nombre' => $nombreEstFmt,
                            'unidad' => $u['unidad_nombre'],
                            'semestre' => $u['semestre_nombre'],
                            'docente' => $docenteNombreFmt,
                            'promedio' => $promFinal,
                            'tipo' => 'desaprobado',
                            'mensaje' => $esInhabilitadoDPI ? 'Inhabilitado por DPI (>30% inasistencias)' : 'Desaprobado en Promedio de Indicadores (< 10)'
                        ];
                    }
                }

                // Alerta asistencia
                if ($esInhabilitadoDPI || $porcFaltas >= 20) {
                    if (isset($progMetrics[$pId])) $progMetrics[$pId]['alertas_asistencia']++;
                    $alertasAsistenciaAutoridad[] = [
                        'id_pud' => $idPud,
                        'id_detalle_matricula' => $idDet,
                        'id_programa_estudio' => $pId,
                        'programa' => $u['programa_nombre'],
                        'id_estudiante' => $uId,
                        'dni' => $d['dni'],
                        'nombre' => $nombreEstFmt,
                        'unidad' => $u['unidad_nombre'],
                        'semestre' => $u['semestre_nombre'],
                        'docente' => $docenteNombreFmt,
                        'total_sesiones' => $totSesAsist,
                        'faltas' => $faltas,
                        'porcentaje' => $porcFaltas,
                        'estado' => $esInhabilitadoDPI ? 'inhabilitado' : 'riesgo'
                    ];
                }
            }

            if (isset($progMetrics[$pId])) {
                $progMetrics[$pId]['total_notas_esperadas'] += $notasEsperadasUd;
                $progMetrics[$pId]['total_notas_registradas'] += $notasRegistradasUd;
            }
        }

        // Porcentajes para cada programa
        foreach ($progMetrics as $pId => &$pm) {
            $pm['porcentaje_silabos'] = $pm['total_uds'] > 0 ? min(100, round(($pm['total_silabos'] / $pm['total_uds']) * 100, 1)) : 0;
            $pm['porcentaje_sesiones'] = $pm['total_actividades'] > 0 ? min(100, round(($pm['total_sesiones'] / $pm['total_actividades']) * 100, 1)) : 0;
            $pm['porcentaje_calificaciones'] = $pm['total_notas_esperadas'] > 0 ? min(100, round(($pm['total_notas_registradas'] / $pm['total_notas_esperadas']) * 100, 1)) : 0;
        }
        unset($pm);

        // Rendimiento por estudiante único
        $globalAprobados = 0;
        $globalEnRiesgo = 0;
        $globalDesaprobados = 0;
        $globalSinCalificar = 0;

        foreach ($studentPerformanceMap as $uId => $perf) {
            if ($id_programa_filtro && $perf['id_programa'] != $id_programa_filtro) {
                continue;
            }
            if (empty($perf['notas'])) {
                $globalSinCalificar++;
            } elseif ($perf['has_desaprobado']) {
                $globalDesaprobados++;
            } elseif ($perf['has_riesgo']) {
                $globalEnRiesgo++;
            } else {
                $globalAprobados++;
            }
        }

        // Totales globales o filtrados para las tarjetas KPI
        if ($id_programa_filtro && isset($progMetrics[$id_programa_filtro])) {
            $target = $progMetrics[$id_programa_filtro];
            $totUds = $target['total_uds'];
            $totSilReg = $target['total_silabos'];
            $totSilPend = max(0, $totUds - $totSilReg);
            $pctSil = $target['porcentaje_silabos'];
            $totSesDes = $target['total_sesiones'];
            $totSesProg = $target['total_actividades'];
            $pctSes = $target['porcentaje_sesiones'];
            $totNotReg = $target['total_notas_registradas'];
            $totNotEsp = $target['total_notas_esperadas'];
            $pctCal = $target['porcentaje_calificaciones'];
            $totEst = $target['total_matriculados'];
            $totAleAcad = $target['alertas_academicas'];
            $totAleAsis = $target['alertas_asistencia'];
        } else {
            $totUds = count($udsContexto);
            $totSilReg = array_sum(array_column($progMetrics, 'total_silabos'));
            $totSilPend = max(0, $totUds - $totSilReg);
            $pctSil = $totUds > 0 ? min(100, round(($totSilReg / $totUds) * 100, 1)) : 0;
            $totSesDes = array_sum(array_column($progMetrics, 'total_sesiones'));
            $totSesProg = array_sum(array_column($progMetrics, 'total_actividades'));
            $pctSes = $totSesProg > 0 ? min(100, round(($totSesDes / $totSesProg) * 100, 1)) : 0;
            $totNotReg = array_sum(array_column($progMetrics, 'total_notas_registradas'));
            $totNotEsp = array_sum(array_column($progMetrics, 'total_notas_esperadas'));
            $pctCal = $totNotEsp > 0 ? min(100, round(($totNotReg / $totNotEsp) * 100, 1)) : 0;
            $totEst = count($matriculadosContexto);
            $totAleAcad = count($alertasAcademicasAutoridad);
            $totAleAsis = count($alertasAsistenciaAutoridad);
        }

        $resumenAutoridad = [
            'total_programas' => count($programas),
            'total_uds' => $totUds,
            'total_docentes' => count($docentesUnicos),
            'total_estudiantes' => $totEst,
            'silabos_registrados' => $totSilReg,
            'silabos_pendientes' => $totSilPend,
            'porcentaje_silabos' => $pctSil,
            'sesiones_desarrolladas' => $totSesDes,
            'sesiones_programadas' => $totSesProg,
            'porcentaje_sesiones' => $pctSes,
            'notas_registradas' => $totNotReg,
            'notas_esperadas' => $totNotEsp,
            'porcentaje_calificaciones' => $pctCal,
            'total_alertas_academicas' => $totAleAcad,
            'total_alertas_asistencia' => $totAleAsis,
        ];

        // Gráficos comparativos
        $chartTipo = ($id_programa_filtro && isset($progMetrics[$id_programa_filtro])) ? 'unidades' : 'programas';
        $chartLabelsAutoridad = [];
        $chartSilabosAutoridad = [];
        $chartSesionesAutoridad = [];
        $chartCalificacionesAutoridad = [];

        if ($chartTipo === 'programas') {
            foreach ($progMetrics as $pm) {
                $label = !empty($pm['codigo']) ? $pm['codigo'] . ' - ' . $pm['nombre'] : $pm['nombre'];
                if (mb_strlen($label) > 25) {
                    $label = mb_substr($label, 0, 23) . '...';
                }
                $chartLabelsAutoridad[] = $label;
                $chartSilabosAutoridad[] = $pm['porcentaje_silabos'];
                $chartSesionesAutoridad[] = $pm['porcentaje_sesiones'];
                $chartCalificacionesAutoridad[] = $pm['porcentaje_calificaciones'];
            }
        } else {
            // Muestra UDs del programa seleccionado
            foreach ($udsContexto as $u) {
                if ((int)$u['id_programa_estudio'] == (int)$id_programa_filtro) {
                    $idPud = (int)$u['id_programacion_ud'];
                    $chartLabelsAutoridad[] = mb_substr($u['unidad_nombre'], 0, 22) . (mb_strlen($u['unidad_nombre']) > 22 ? '...' : '') . ' (' . $u['semestre_nombre'] . ')';

                    $sData = $sesionesMap[$idPud] ?? ['total_actividades' => 0, 'total_sesiones' => 0];
                    $act = (int)$sData['total_actividades'];
                    $ses = (int)$sData['total_sesiones'];
                    $chartSesionesAutoridad[] = $act > 0 ? min(100, round(($ses / $act) * 100, 1)) : 0;
                    $chartSilabosAutoridad[] = !empty($u['id_silabo']) ? 100 : 0;

                    $dets = $detallesByPud[$idPud] ?? [];
                    $reg = 0;
                    $esp = count($dets) * 3; // estimado base
                    $chartCalificacionesAutoridad[] = 0;
                }
            }
        }

        // 2. TABLA DE GESTIÓN DE UNIDADES DIDÁCTICAS (Filtrada por Periodo seleccionado)
        $periodoTabla = !empty($periodo_filtro) ? (int)$periodo_filtro : (int)$id_periodo_contexto;

        $sqlTabla = "SELECT 
                        pud.id AS id_programacion_ud,
                        ud.id AS id_unidad_didactica,
                        ud.nombre AS unidad_nombre,
                        pud.turno,
                        pud.seccion,
                        pud.id_periodo_academico,
                        pa.nombre AS periodo_nombre,
                        pe.id AS id_programa_estudio,
                        pe.nombre AS programa_nombre,
                        pl.id AS id_plan_estudio,
                        pl.nombre AS plan_nombre,
                        s.id AS id_semestre,
                        s.descripcion AS semestre_nombre,
                        pud.id_docente,
                        COALESCE(u.apellidos_nombres, 'Sin Asignar') AS docente_nombre,
                        COALESCE(sil.id, 0) AS id_silabo
                    FROM acad_programacion_unidad_didactica pud
                    INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                    LEFT JOIN sigi_semestre s ON ud.id_semestre = s.id
                    LEFT JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                    LEFT JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                    LEFT JOIN sigi_programa_estudios pe ON pl.id_programa_estudios = pe.id
                    LEFT JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
                    LEFT JOIN sigi_usuarios u ON pud.id_docente = u.id
                    LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                    WHERE pud.id_sede = ?";

        $paramsTabla = [$id_sede];
        if ($periodoTabla > 0) {
            $sqlTabla .= " AND pud.id_periodo_academico = ?";
            $paramsTabla[] = $periodoTabla;
        }
        $sqlTabla .= " ORDER BY pe.id ASC, pa.fecha_inicio DESC, s.id ASC, ud.nombre ASC";

        $stmtTabla = self::$db->prepare($sqlTabla);
        $stmtTabla->execute($paramsTabla);
        $udsTablaRaw = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);

        $pudIdsTabla = array_column($udsTablaRaw, 'id_programacion_ud');
        $matTablaMap = [];
        $sesTablaMap = [];

        if (!empty($pudIdsTabla)) {
            $inTabla = implode(',', array_fill(0, count($pudIdsTabla), '?'));

            // Conteo de estudiantes
            $stmtMatT = self::$db->prepare("SELECT id_programacion_ud, COUNT(DISTINCT id_matricula) as total 
                                            FROM acad_detalle_matricula 
                                            WHERE id_programacion_ud IN ($inTabla) 
                                            GROUP BY id_programacion_ud");
            $stmtMatT->execute($pudIdsTabla);
            while ($rm = $stmtMatT->fetch(PDO::FETCH_ASSOC)) {
                $matTablaMap[$rm['id_programacion_ud']] = (int)$rm['total'];
            }

            // Avance de sesiones
            $stmtSesT = self::$db->prepare("SELECT s.id_prog_unidad_didactica,
                                                   COUNT(pas.id) as total_actividades,
                                                   COUNT(sa.id) as total_sesiones
                                            FROM acad_silabos s
                                            LEFT JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
                                            LEFT JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id 
                                                  AND sa.logro_sesion <> '' 
                                                  AND sa.logro_sesion IS NOT NULL
                                            WHERE s.id_prog_unidad_didactica IN ($inTabla)
                                            GROUP BY s.id_prog_unidad_didactica");
            $stmtSesT->execute($pudIdsTabla);
            while ($rs = $stmtSesT->fetch(PDO::FETCH_ASSOC)) {
                $sesTablaMap[$rs['id_prog_unidad_didactica']] = $rs;
            }
        }

        $unidadesTablaAutoridad = [];
        foreach ($udsTablaRaw as $u) {
            $idPud = (int)$u['id_programacion_ud'];
            $matriculados = $matTablaMap[$idPud] ?? 0;

            // Sesiones
            $sData = $sesTablaMap[$idPud] ?? ['total_actividades' => 0, 'total_sesiones' => 0];
            $actividades = (int)$sData['total_actividades'];
            $sesiones = (int)$sData['total_sesiones'];
            $pctSesiones = $actividades > 0 ? min(100, round(($sesiones / $actividades) * 100, 1)) : 0;

            // Formatear turno
            $turnoTexto = 'Mañana';
            if ($u['turno'] === 'T') $turnoTexto = 'Tarde';
            elseif ($u['turno'] === 'N') $turnoTexto = 'Noche';
            $turnoSec = $turnoTexto . ' - ' . ($u['seccion'] ?: 'A');

            // Avance calificaciones
            $pctCal = 0;
            $regCal = 0;
            $espCal = 0;
            if (isset($detallesByPud[$idPud])) {
                $detsUd = $detallesByPud[$idPud];
                $nrosUd = [];
                foreach ($detsUd as $d) {
                    if (isset($califsByDetalle[$d['id']])) {
                        foreach (array_keys($califsByDetalle[$d['id']]) as $n) {
                            $nrosUd[$n] = true;
                        }
                    }
                }
                $espCal = count($nrosUd) * count($detsUd);
                foreach ($detsUd as $d) {
                    foreach (array_keys($nrosUd) as $n) {
                        $idCal = $califsByDetalle[$d['id']][$n] ?? 0;
                        if ($idCal && isset($evalsByCal[$idCal])) {
                            foreach ($evalsByCal[$idCal] as $ev) {
                                if (!empty($ev['criterios'])) {
                                    $regCal++;
                                    break;
                                }
                            }
                        }
                    }
                }
                $pctCal = $espCal > 0 ? min(100, round(($regCal / $espCal) * 100, 1)) : 0;
            }

            $unidadesTablaAutoridad[] = [
                'id_programacion_ud' => $idPud,
                'id_unidad_didactica' => $u['id_unidad_didactica'],
                'unidad_nombre' => $u['unidad_nombre'],
                'matriculados' => $matriculados,
                'id_programa_estudio' => $u['id_programa_estudio'],
                'programa_nombre' => $u['programa_nombre'],
                'id_plan_estudio' => $u['id_plan_estudio'],
                'plan_nombre' => $u['plan_nombre'],
                'id_periodo_academico' => $u['id_periodo_academico'],
                'periodo_nombre' => $u['periodo_nombre'],
                'id_semestre' => $u['id_semestre'],
                'semestre_nombre' => $u['semestre_nombre'],
                'turno_seccion' => $turnoSec,
                'id_docente' => $u['id_docente'],
                'docente_nombre' => $this->formatearNombreUsuario($u['docente_nombre']),
                'silabo_registrado' => (bool)$u['id_silabo'],
                'sesiones_registradas' => $sesiones,
                'sesiones_totales' => $actividades,
                'porcentaje_sesiones' => $pctSesiones,
                'calificaciones_registradas' => $regCal,
                'calificaciones_totales' => $espCal,
                'porcentaje_calificaciones' => $pctCal,
            ];
        }

        return [
            'esAutoridad' => true,
            'programasAutoridad' => $programas,
            'planesAutoridad' => $planes,
            'periodosAcademicos' => $periodos,
            'semestresCatalogo' => $semestres,
            'resumenAutoridad' => $resumenAutoridad,
            'programasComparativa' => array_values($progMetrics),
            'alertasAcademicasAutoridad' => $alertasAcademicasAutoridad,
            'alertasAsistenciaAutoridad' => $alertasAsistenciaAutoridad,
            'chartTipoAutoridad' => $chartTipo,
            'chartLabelsAutoridad' => $chartLabelsAutoridad,
            'chartSilabosAutoridad' => $chartSilabosAutoridad,
            'chartSesionesAutoridad' => $chartSesionesAutoridad,
            'chartCalificacionesAutoridad' => $chartCalificacionesAutoridad,
            'chartRendimientoAutoridad' => [
                'aprobados' => $globalAprobados,
                'en_riesgo' => $globalEnRiesgo,
                'desaprobados' => $globalDesaprobados,
                'sin_calificar' => $globalSinCalificar,
            ],
            'unidadesTablaAutoridad' => $unidadesTablaAutoridad,
            'periodoFiltroSeleccionado' => $periodoTabla,
            'programaFiltroSeleccionado' => $id_programa_filtro,
        ];
    }
}

