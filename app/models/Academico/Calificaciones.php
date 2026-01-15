<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Calificaciones extends Model
{
    // ¿Puede ver calificaciones?
    public function puedeVerCalificaciones($id_programacion_ud)
    {
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        if (\Core\Auth::esAdminAcademico()) return true;
        $stmt = self::$db->prepare("SELECT id_docente FROM acad_programacion_unidad_didactica WHERE id = ?");
        $stmt->execute([$id_programacion_ud]);
        $idDocente = $stmt->fetchColumn();
        if ($idDocente && $idDocente == $idUsuario) return true;
        return false;
    }

    // Promedio de criterios para una evaluación
    public function promedioCriterios($id_evaluacion)
    {
        $stmt = self::$db->prepare("SELECT calificacion FROM acad_criterio_evaluacion WHERE id_evaluacion = ? AND detalle <> ''");
        $stmt->execute([$id_evaluacion]);
        $criterios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cantidad = count($criterios);

        if ($cantidad === 0) return '';

        $suma = 0;
        foreach ($criterios as $c) {
            if (is_numeric($c) && $c !== '') {
                $suma += intval($c);
            }
        }
        return ($cantidad > 0) ? round($suma / $cantidad + 0.00001, 0, PHP_ROUND_HALF_UP) : '';
    }

    // Nota final de una calificación (ponderación de evaluaciones)
    public function notaCalificacion($id_calificacion)
    {
        $stmt = self::$db->prepare("SELECT id, ponderado FROM acad_evaluacion WHERE id_calificacion = ?");
        $stmt->execute([$id_calificacion]);
        $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $suma_ponderado = 0;
        $cantidad = 0;

        foreach ($evaluaciones as $ev) {
            $prom_eval = $this->promedioCriterios($ev['id']);
            if ($prom_eval !== '') {
                $suma_ponderado += $prom_eval * $ev['ponderado'] / 100;
                $cantidad++;
            }
        }
        return ($cantidad > 0) ? round($suma_ponderado  + 0.00001, 0, PHP_ROUND_HALF_UP) : '';
    }

    // Promedio final de un estudiante (por sus calificaciones)
    public function promedioFinalEstudiante($id_detalle_matricula, $nros_calificacion)
    {
        $suma = 0;
        $conteo = 0;
        foreach ($nros_calificacion as $nro) {
            $stmt = self::$db->prepare("SELECT id FROM acad_calificacion WHERE id_detalle_matricula = ? AND nro_calificacion = ? LIMIT 1");
            $stmt->execute([$id_detalle_matricula, $nro]);
            $calif = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($calif && $calif['id']) {
                $nota = $this->notaCalificacion($calif['id']);
                if ($nota !== '') {
                    $suma += $nota;
                    $conteo++;
                }
            }
        }
        return ($conteo > 0) ? round($suma / $conteo + 0.00001, 0, PHP_ROUND_HALF_UP) : '';
    }

    // ¿Recuperación habilitada?
    public function recuperacionHabilitada($promedio_final)
    {
        return in_array($promedio_final, [10, 11, 12]);
    }
    public function guardarRecuperacion($id_detalle_mat, $valor)
    {
        // 1. Obtener el valor actual (Snapshot)
        // Es vital hacer esto ANTES del update
        $stmtGet = self::$db->prepare("SELECT recuperacion FROM acad_detalle_matricula WHERE id = ?");
        $stmtGet->execute([$id_detalle_mat]);
        $valor_anterior = $stmtGet->fetchColumn();
        // Normalización para comparación (evitar que "10" sea diferente de 10)
        if ((string)$valor_anterior === (string)$valor) {
            return true; // No gastamos recursos en update ni log
        }
        // 2. Ejecutar la actualización
        $stmt = self::$db->prepare("UPDATE acad_detalle_matricula SET recuperacion = ? WHERE id = ?");
        $resultado = $stmt->execute([$valor, $id_detalle_mat]);

        // 3. Auditoría (Solo si el update fue exitoso)
        if ($resultado) {
            // Usamos el método del padre (o el helper local si no modificaste Core)
            $this->registrarAuditoria(
                'acad_detalle_matricula',
                $id_detalle_mat,
                'recuperacion',
                $valor_anterior,
                $valor
            );
        }
        return $resultado;
    }


    // =========================
    //      VISTA GENERAL
    // =========================
    public function getDatosCalificaciones($id_programacion_ud)
    {
        // 1. Nombre UD, periodo
        $stmt = self::$db->prepare("SELECT ud.nombre AS nombreUnidadDidactica, ud.id as id_unidad_didactica, pa.nombre as periodo
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
            WHERE pud.id = ?");
        $stmt->execute([$id_programacion_ud]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Calificaciones (los nros de calificación a mostrar en columnas)
        $stmt = self::$db->prepare("SELECT DISTINCT nro_calificacion FROM acad_calificacion 
            WHERE id_detalle_matricula IN (SELECT id FROM acad_detalle_matricula WHERE id_programacion_ud = ?)
            ORDER BY nro_calificacion ASC");
        $stmt->execute([$id_programacion_ud]);
        $nros_calificacion = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Estudiantes matriculados
        $stmt = self::$db->prepare("SELECT u.id, u.dni, u.apellidos_nombres, dm.id as id_detalle_matricula, m.licencia as licencia
            FROM acad_detalle_matricula dm
            INNER JOIN acad_matricula m ON m.id = dm.id_matricula
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
            WHERE dm.id_programacion_ud = ?
            ORDER BY TRIM(CONVERT(u.apellidos_nombres USING utf8mb4)) COLLATE utf8mb4_spanish_ci ASC");
        $stmt->execute([$id_programacion_ud]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($estudiantes as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $estudiantes[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }

        // 4. Notas y promedios individuales
        $notas = [];
        $promedios = [];
        if ($estudiantes && $nros_calificacion) {
            foreach ($estudiantes as $est) {
                $id_detalle = $est['id_detalle_matricula'];
                foreach ($nros_calificacion as $nro) {
                    $stmtN = self::$db->prepare("SELECT id FROM acad_calificacion WHERE id_detalle_matricula = ? AND nro_calificacion = ? LIMIT 1");
                    $stmtN->execute([$id_detalle, $nro]);
                    $calif = $stmtN->fetch(PDO::FETCH_ASSOC);
                    $nota_final = '';
                    if ($calif && $calif['id']) {
                        $nota_final = $this->notaCalificacion($calif['id']);
                    }
                    $notas[$id_detalle][$nro] = $nota_final;
                }
                // Promedio final de ese estudiante
                $promedios[$id_detalle] = $this->promedioFinalEstudiante($id_detalle, $nros_calificacion);
            }
        }

        // 5. Recuperaciones
        $recuperaciones = [];
        if ($estudiantes) {
            foreach ($estudiantes as $est) {
                $id_detalle = $est['id_detalle_matricula'];
                $stmtR = self::$db->prepare("SELECT recuperacion FROM acad_detalle_matricula WHERE id = ?");
                $stmtR->execute([$id_detalle]);
                $recuperaciones[$id_detalle] = $stmtR->fetchColumn();
            }
        }

        return [
            'nombreUnidadDidactica' => $info['nombreUnidadDidactica'] ?? '',
            'idUnidadDidactica' => $info['id_unidad_didactica'] ?? '',
            'periodo' => $info['periodo'] ?? '',
            'nros_calificacion' => $nros_calificacion,
            'estudiantes' => $estudiantes,
            'notas' => $notas,
            'promedios' => $promedios,
            'recuperaciones' => $recuperaciones,
        ];
    }

    // Actualizar el campo mostrar_calificacion de una calificación específica
    public function actualizarMostrarCalificacion($id_programacion_ud, $nro_calificacion, $mostrar)
    {
        $stmt = self::$db->prepare("
            UPDATE acad_calificacion cal
            INNER JOIN acad_detalle_matricula det ON cal.id_detalle_matricula = det.id
            SET cal.mostrar_calificacion = ?
            WHERE det.id_programacion_ud = ? AND cal.nro_calificacion = ?
        ");
        return $stmt->execute([$mostrar, $id_programacion_ud, $nro_calificacion]);
    }

    // Mostrar calificaciones (array por nro_calificacion)
    public function getMostrarCalificaciones($id_programacion_ud, $calificaciones)
    {
        $mostrar = [];
        foreach ($calificaciones as $nro) {
            $stmt = self::$db->prepare("
                SELECT cal.mostrar_calificacion
                FROM acad_calificacion cal
                INNER JOIN acad_detalle_matricula det ON cal.id_detalle_matricula = det.id
                WHERE det.id_programacion_ud = ? AND cal.nro_calificacion = ?
                LIMIT 1
            ");
            $stmt->execute([$id_programacion_ud, $nro]);
            $mostrar[$nro] = (int)($stmt->fetchColumn() ?? 0);
        }
        return $mostrar;
    }

    // ¿Todos mostrar el promedio?
    public function todosMostrarPromedio($id_programacion_ud)
    {
        $stmt = self::$db->prepare("SELECT COUNT(*) as total, SUM(mostrar_calificacion=1) as marcados
            FROM acad_detalle_matricula
            WHERE id_programacion_ud = ?");
        $stmt->execute([$id_programacion_ud]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($row && $row['total'] > 0 && $row['total'] == $row['marcados']) ? 1 : 0;
    }

    // Actualizar mostrar_calificacion para TODOS
    public function actualizarMostrarPromedioTodos($id_programacion_ud, $mostrar)
    {
        $stmt = self::$db->prepare("UPDATE acad_detalle_matricula SET mostrar_calificacion = ? WHERE id_programacion_ud = ?");
        return $stmt->execute([$mostrar, $id_programacion_ud]);
    }

    // =========================
    //    VISTA DE EVALUACIÓN
    // =========================
    public function getDatosEvaluacion($id_programacion_ud, $nro_calificacion)
    {
        // 1. Nombre UD
        $stmt = self::$db->prepare("SELECT ud.nombre AS nombreUnidadDidactica
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            WHERE pud.id = ?");
        $stmt->execute([$id_programacion_ud]);
        $nombreUnidadDidactica = $stmt->fetchColumn();

        // 2. Estudiantes
        $stmt = self::$db->prepare("SELECT u.id, u.dni, u.apellidos_nombres, dm.id as id_detalle_matricula, m.licencia as licencia
            FROM acad_detalle_matricula dm
            INNER JOIN acad_matricula m ON m.id = dm.id_matricula
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
            WHERE dm.id_programacion_ud = ?
            ORDER BY u.apellidos_nombres");
        $stmt->execute([$id_programacion_ud]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Evaluaciones y criterios de CADA estudiante (lo que soluciona el bug visual)
        $evaluacionesEstudiante = [];
        $valores = [];
        $promediosEvaluacion = [];
        $promedioFinal = [];

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            // Calificación para ese nro_calificacion y ese estudiante
            $stmtCalif = self::$db->prepare("SELECT id FROM acad_calificacion WHERE id_detalle_matricula = ? AND nro_calificacion = ?");
            $stmtCalif->execute([$id_detalle, $nro_calificacion]);
            $rowCalif = $stmtCalif->fetch(PDO::FETCH_ASSOC);
            $id_calif = $rowCalif['id'] ?? 0;

            $evaluaciones = [];
            if ($id_calif) {
                $stmtE = self::$db->prepare("SELECT id, detalle, ponderado FROM acad_evaluacion WHERE id_calificacion = ? ORDER BY id");
                $stmtE->execute([$id_calif]);
                $evs = $stmtE->fetchAll(PDO::FETCH_ASSOC);

                foreach ($evs as $ev) {
                    // Criterios de esa evaluación
                    $criterios = [];
                    $stmtC = self::$db->prepare("SELECT id, detalle, calificacion FROM acad_criterio_evaluacion WHERE id_evaluacion = ? ORDER BY orden");
                    $stmtC->execute([$ev['id']]);
                    while ($cr = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                        $criterios[] = [
                            'id' => $cr['id'],
                            'detalle' => $cr['detalle'],
                            'calificacion' => $cr['calificacion']
                        ];
                        $valores[$id_detalle][$ev['id']][$cr['id']] = $cr['calificacion'];
                    }
                    $evaluaciones[] = [
                        'id' => $ev['id'],
                        'detalle' => $ev['detalle'],
                        'ponderado' => $ev['ponderado'],
                        'criterios' => $criterios
                    ];
                }
            }
            $evaluacionesEstudiante[$id_detalle] = $evaluaciones;

            // Promedios por evaluación (solo sus criterios)
            $sum_prom = 0;
            $count_prom = 0;
            foreach ($evaluaciones as $ev) {
                $prom = $this->promedioCriterios($ev['id']);
                $promediosEvaluacion[$id_detalle][$ev['id']] = $prom;
                if ($prom !== '') {
                    $sum_prom += $prom;
                    $count_prom++;
                }
            }
            //$promedioFinal[$id_detalle] = ($count_prom > 0) ? round($sum_prom / $count_prom + 0.00001, 0, PHP_ROUND_HALF_UP) : '';
            $promedioFinal[$id_detalle] = $this->notaCalificacion($id_calif);
            //var_dump($promedioFinal);
        }


        return [
            'nombreUnidadDidactica' => $nombreUnidadDidactica,
            'nombreIndicador' => '',
            'evaluacionesEstudiante' => $evaluacionesEstudiante,
            'estudiantes' => $estudiantes,
            'valores' => $valores,
            'promediosEvaluacion' => $promediosEvaluacion,
            'promedioFinal' => $promedioFinal,
        ];
    }

    // Guardar criterio individual
    public function guardarCriterioEvaluacion($id_criterio, $valor)
    {
        // 1. Obtener el valor actual (Snapshot)
        // Es vital hacer esto ANTES del update
        $stmtGet = self::$db->prepare("SELECT calificacion FROM acad_criterio_evaluacion WHERE id = ?");
        $stmtGet->execute([$id_criterio]);
        $valor_anterior = $stmtGet->fetchColumn();

        // Normalización para comparación (evitar que "10" sea diferente de 10)
        if ((string)$valor_anterior === (string)$valor) {
            return true; // No gastamos recursos en update ni log
        }
        // 2. Ejecutar la actualización
        $stmt = self::$db->prepare("UPDATE acad_criterio_evaluacion SET calificacion = ? WHERE id = ?");
        $resultado = $stmt->execute([$valor, $id_criterio]);

        // 3. Auditoría (Solo si el update fue exitoso)
        if ($resultado) {
            // Usamos el método del padre (o el helper local si no modificaste Core)
            $this->registrarAuditoria(
                'acad_criterio_evaluacion',
                $id_criterio,
                'calificacion',
                $valor_anterior,
                $valor
            );
        }

        return $resultado;
    }

    public function guardarDetalleCriterioMasivo($ids_criterio, $detalle)
    {
        $ids = explode(',', $ids_criterio);
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = self::$db->prepare("UPDATE acad_criterio_evaluacion SET detalle = ? WHERE id IN ($in)");
        return $stmt->execute(array_merge([$detalle], $ids));
    }
    public function guardarPonderadoEvaluacionMasivo($ids_eval, $ponderado)
    {
        // $ids_eval: string con ids separados por coma
        $idsArr = array_filter(array_map('intval', explode(',', $ids_eval)));
        if (empty($idsArr)) return false;
        $in  = str_repeat('?,', count($idsArr) - 1) . '?';
        $sql = "UPDATE acad_evaluacion SET ponderado = ? WHERE id IN ($in)";
        $params = array_merge([$ponderado], $idsArr);
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    public function agregarCriterioMasivo($ids_eval)
    {
        $ok = true;
        foreach ($ids_eval as $id_eval) {
            // Obtener el mayor 'orden' actual para esa evaluación
            $stmt = self::$db->prepare("SELECT MAX(orden) FROM acad_criterio_evaluacion WHERE id_evaluacion = ?");
            $stmt->execute([$id_eval]);
            $max_orden = (int)$stmt->fetchColumn();
            $nuevo_orden = $max_orden + 1;

            // Inserta el nuevo criterio con el orden correcto
            $stmtIns = self::$db->prepare("INSERT INTO acad_criterio_evaluacion (id_evaluacion, detalle, orden, calificacion) VALUES (?, '', ?, '')");
            $ok = $ok && $stmtIns->execute([$id_eval, $nuevo_orden]);
        }
        return $ok;
    }




    // Devuelve true si el estudiante no puede ser evaluado por inasistencia
    public function inhabilitadoPorInasistencia($id_detalle_matricula)
    {
        // Total de asistencias del estudiante en la UD
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM acad_asistencia WHERE id_detalle_matricula = ?");
        $stmt->execute([$id_detalle_matricula]);
        $total = $stmt->fetchColumn();

        if ($total == 0) return false; // No hay asistencias registradas, dejar evaluar

        // Total de faltas
        $stmtF = self::$db->prepare("SELECT COUNT(*) FROM acad_asistencia WHERE id_detalle_matricula = ? AND asistencia = 'F'");
        $stmtF->execute([$id_detalle_matricula]);
        $faltas = $stmtF->fetchColumn();

        $porc_faltas = $faltas / $total;

        return $porc_faltas > 0.3;
    }

    public function obtenerProgPorIdCriterio($id_criterio)
    {
        $stmt = self::$db->prepare("SELECT adm.id_programacion_ud 
        FROM acad_detalle_matricula adm 
        INNER JOIN acad_calificacion ac ON ac.id_detalle_matricula = adm.id
        INNER JOIN acad_evaluacion ae ON ae.id_calificacion = ac.id
        INNER JOIN acad_criterio_evaluacion ace ON ace.id_evaluacion =ae.id
        WHERE ace.id = ?");
        $stmt->execute([$id_criterio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function obtenerProgPorIdEvaluacion($id_evaluacion)
    {
        $stmt = self::$db->prepare("SELECT adm.id_programacion_ud 
        FROM acad_detalle_matricula adm 
        INNER JOIN acad_calificacion ac ON ac.id_detalle_matricula = adm.id
        INNER JOIN acad_evaluacion ae ON ae.id_calificacion = ac.id
        WHERE ae.id = ?");
        $stmt->execute([$id_evaluacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
