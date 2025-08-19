<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Silabos extends Model
{

    public function registrarSilabo($data)
    {
        $sql = "INSERT INTO acad_silabos
            (id_prog_unidad_didactica, id_coordinador, fecha_inicio,
             sumilla, horario, metodologia, recursos_didacticos, sistema_evaluacion,
             estrategia_evaluacion_indicadores, estrategia_evaluacion_tecnica, promedio_indicadores_logro,
             recursos_bibliograficos_impresos, recursos_bibliograficos_digitales)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_prog_unidad_didactica'],
            $data['id_coordinador'],
            $data['fecha_inicio'],
            $data['sumilla'],
            $data['horario'],
            $data['metodologia'],
            $data['recursos_didacticos'],
            $data['sistema_evaluacion'],
            $data['estrategia_evaluacion_indicadores'],
            $data['estrategia_evaluacion_tecnica'],
            $data['promedio_indicadores_logro'],
            $data['recursos_bibliograficos_impresos'],
            $data['recursos_bibliograficos_digitales']
        ]);
        return self::$db->lastInsertId();
    }
    // Trae el sílabo por programación
    public function getSilaboByProgramacion($id_prog_unidad_didactica)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_silabos WHERE id_prog_unidad_didactica = ? LIMIT 1");
        $stmt->execute([$id_prog_unidad_didactica]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Trae el sílabo por ID
    public function getSilaboById($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_silabos WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualiza los campos editables del sílabo
    public function actualizarSilabo($id, $data)
    {
        $sql = "UPDATE acad_silabos SET 
            horario = :horario,
            sumilla = :sumilla,
            metodologia = :metodologia,
            recursos_didacticos = :recursos_didacticos,
            sistema_evaluacion = :sistema_evaluacion,
            recursos_bibliograficos_impresos = :recursos_bibliograficos_impresos,
            recursos_bibliograficos_digitales = :recursos_bibliograficos_digitales
            WHERE id = :id";
        $data['id'] = $id;
        $stmt = self::$db->prepare($sql);
        $stmt->execute($data);
        return true;
    }

    // Trae los datos generales de la programación y docente
    public function getDatosGenerales($id_programacion)
    {
        $sql = "SELECT 
            pr.nombre AS programa,
            mf.descripcion AS modulo,
            mf.nro_modulo AS nro_modulo,
            ud.nombre AS unidad,
            s.descripcion AS periodo_academico,
            ud.creditos_teorico + ud.creditos_practico AS creditos,
            ((ud.creditos_teorico * 1) + (ud.creditos_practico * 2)) * 16 AS horas_totales,
            ((ud.creditos_teorico * 1) + (ud.creditos_practico * 2)) AS horas_semanales,
            pa.nombre AS periodo_lectivo,
            pa.id AS id_periodo_lectivo,
            pud.seccion AS seccion,
            pud.turno,
            pud.supervisado AS supervisado,
            pud.reg_evaluacion AS reg_evaluacion,
            pud.reg_auxiliar AS reg_auxiliar,
            pud.prog_curricular AS prog_curricular,
            pud.otros AS otros,
            pud.logros_obtenidos AS logros_obtenidos,
            pud.dificultades AS dificultades,
            pud.sugerencias AS sugerencias,
            u.apellidos_nombres AS docente,
            u.correo AS correo_docente
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
            INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
            INNER JOIN sigi_usuarios u ON pud.id_docente = u.id
            INNER JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
            WHERE pud.id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }





    // Trae las sesiones de aprendizaje para el silabo
    public function getSesionesSilabo($id_silabo)
    {
        $stmt = self::$db->prepare("SELECT id, semana, fecha, elemento_capacidad AS indicador, actividades_aprendizaje AS sesion, contenidos_basicos AS contenido, tareas_previas AS logro, '' AS instrumentos
                                    FROM acad_programacion_actividades_silabo
                                    WHERE id_silabo = ?
                                    ORDER BY semana ASC");
        $stmt->execute([$id_silabo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualiza la información editable de una sesión de silabo
    public function actualizarSesionSilabo($id, $info)
    {
        $stmt = self::$db->prepare("UPDATE acad_programacion_actividades_silabo SET 
            elemento_capacidad = :indicador,
            actividades_aprendizaje = :sesion,
            contenidos_basicos = :contenido,
            tareas_previas = :logro
            WHERE id = :id");
        $info['id'] = $id;
        $stmt->execute([
            ':indicador' => $info['indicador'] ?? '',
            ':sesion' => $info['sesion'] ?? '',
            ':contenido' => $info['contenido'] ?? '',
            ':logro' => $info['logro'] ?? '',
            ':id' => $id
        ]);
        return true;
    }






    // Trae la info detallada de cada sesión (con join entre actividades y sesiones de aprendizaje)
    public function getSesionesSilaboDetallado($id_silabo)
    {
        $sql = "SELECT pas.id AS id_actividad,
        pas.semana,
        pas.fecha,
        pas.id_ind_logro_aprendizaje,
        ilc.codigo AS codigo_ind_logro,
        ilc.descripcion AS desc_ind_logro,
        sa.id AS id_sesion,
        sa.tipo_actividad,
        sa.tipo_sesion,
        sa.denominacion,
        pas.contenidos_basicos,
        sa.logro_sesion,
        pas.tareas_previas
        FROM acad_programacion_actividades_silabo pas
        LEFT JOIN acad_sesion_aprendizaje sa 
            ON sa.id_prog_actividad_silabo = pas.id
        LEFT JOIN sigi_ind_logro_capacidad ilc 
            ON ilc.id = pas.id_ind_logro_aprendizaje
        WHERE pas.id_silabo = ?
        ORDER BY pas.semana ASC;
        ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_silabo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarSesionSilaboCompleto($id_actividad, $info)
    {
        // Actualiza actividad (fecha, indicador, contenidos_basicos, tareas_previas)
        $stmt1 = self::$db->prepare("UPDATE acad_programacion_actividades_silabo
        SET fecha = :fecha,
            id_ind_logro_aprendizaje = :id_ind_logro_aprendizaje,
            contenidos_basicos = :contenido,
            tareas_previas = :tareas_previas
        WHERE id = :id");
        $stmt1->execute([
            ':fecha' => $info['fecha'],
            ':id_ind_logro_aprendizaje' => $info['id_ind_logro_aprendizaje'],
            ':contenido' => $info['contenido'],
            ':tareas_previas' => $info['tareas_previas'],
            ':id' => $id_actividad
        ]);

        // Actualiza la sesión de aprendizaje correspondiente
        $stmt2 = self::$db->prepare("UPDATE acad_sesion_aprendizaje
        SET denominacion = :denominacion,
            fecha_desarrollo = :fecha,
            logro_sesion = :logro_sesion
        WHERE id_prog_actividad_silabo = :id_actividad");
        $stmt2->execute([
            ':denominacion' => $info['denominacion'],
            ':fecha' => $info['fecha'],
            ':logro_sesion' => $info['logro_sesion'],
            ':id_actividad' => $id_actividad
        ]);
    }





    // ----------------------------- PROGRAMACION ACTIVIDADES DE SILABO --------------------------
    public function registrarActividadSilabo($data)
    {
        $sql = "INSERT INTO acad_programacion_actividades_silabo
            (id_silabo, id_ind_logro_aprendizaje, semana, fecha, elemento_capacidad,
             actividades_aprendizaje, contenidos_basicos, tareas_previas)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_silabo'],
            $data['id_ind_logro_aprendizaje'],
            $data['semana'],
            $data['fecha'],
            $data['elemento_capacidad'],
            $data['actividades_aprendizaje'],
            $data['contenidos_basicos'],
            $data['tareas_previas']
        ]);
        return self::$db->lastInsertId();
    }




    public function clonarContenidoDesdeProgramacion(int $id_prog_origen, int $id_prog_dest, array $opts = []): array
    {
        $copiarFechas        = (bool)($opts['copiar_fechas'] ?? false);   // fechas en Silabo/PAS/sesión
        $copiarIndicadorILC  = (bool)($opts['copiar_indicador'] ?? true); // id_ind_logro_aprendizaje en PAS

        $silaboOrigen  = $this->getSilaboByProgramacion($id_prog_origen);
        if (!$silaboOrigen) throw new \Exception("La programación ORIGEN no tiene Sílabo.");
        $silaboDestino = $this->getSilaboByProgramacion($id_prog_dest);
        if (!$silaboDestino) throw new \Exception("La programación DESTINO no tiene Sílabo. Cree el Sílabo y su programación primero.");

        // Normalizador de 'momento' para el match
        $normMom = static function ($v) {
            return mb_strtolower(trim((string)$v));
        };

        try {
            self::$db->beginTransaction();

            // 1) Actualizar SOLO contenido del SÍLABO destino
            $setSil = "
            sumilla = :sumilla,
            metodologia = :metodologia,
            recursos_didacticos = :recursos_didacticos,
            sistema_evaluacion = :sistema_evaluacion,
            estrategia_evaluacion_indicadores = :estrategia_evaluacion_indicadores,
            estrategia_evaluacion_tecnica = :estrategia_evaluacion_tecnica,
            promedio_indicadores_logro = :promedio_indicadores_logro,
            recursos_bibliograficos_impresos = :recursos_bibliograficos_impresos,
            recursos_bibliograficos_digitales = :recursos_bibliograficos_digitales
        ";
            if ($copiarFechas) $setSil .= ", fecha_inicio = :fecha_inicio";

            $stSil = self::$db->prepare("UPDATE acad_silabos SET {$setSil} WHERE id = :id_dest");
            $paramsSil = [
                ':sumilla' => $silaboOrigen['sumilla'],
                ':metodologia' => $silaboOrigen['metodologia'],
                ':recursos_didacticos' => $silaboOrigen['recursos_didacticos'],
                ':sistema_evaluacion' => $silaboOrigen['sistema_evaluacion'],
                ':estrategia_evaluacion_indicadores' => $silaboOrigen['estrategia_evaluacion_indicadores'],
                ':estrategia_evaluacion_tecnica' => $silaboOrigen['estrategia_evaluacion_tecnica'],
                ':promedio_indicadores_logro' => $silaboOrigen['promedio_indicadores_logro'],
                ':recursos_bibliograficos_impresos' => $silaboOrigen['recursos_bibliograficos_impresos'],
                ':recursos_bibliograficos_digitales' => $silaboOrigen['recursos_bibliograficos_digitales'],
                ':id_dest' => $silaboDestino['id'],
            ];
            if ($copiarFechas) $paramsSil[':fecha_inicio'] = $silaboOrigen['fecha_inicio'];
            $stSil->execute($paramsSil);

            // 2) PAS origen por semana (primera ocurrencia de cada semana)
            $selPasOri = self::$db->prepare("
            SELECT * FROM acad_programacion_actividades_silabo
            WHERE id_silabo = ?
            ORDER BY semana, id
        ");
            $selPasOri->execute([$silaboOrigen['id']]);
            $oriByWeek = [];
            foreach ($selPasOri->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $w = (int)$row['semana'];
                if (!isset($oriByWeek[$w])) $oriByWeek[$w] = $row;
            }

            // 3) PAS destino
            $selPasDest = self::$db->prepare("
            SELECT id, semana FROM acad_programacion_actividades_silabo
            WHERE id_silabo = ?
            ORDER BY semana, id
        ");
            $selPasDest->execute([$silaboDestino['id']]);
            $pasDest = $selPasDest->fetchAll(PDO::FETCH_ASSOC);

            // --- PREPARES Sesión + Hijos ---
            // Sesiones del ORIGEN/DESTINO por lista
            $stSesOriList = self::$db->prepare("
            SELECT id, tipo_actividad, tipo_sesion, denominacion,
                   id_ind_logro_competencia_vinculado, id_ind_logro_capacidad_vinculado,
                   logro_sesion, bibliografia_obligatoria_docente, bibliografia_opcional_docente,
                   bibliografia_obligatoria_estudiante, bibliografia_opcional_estudiante,
                   anexos, fecha_desarrollo
            FROM acad_sesion_aprendizaje
            WHERE id_prog_actividad_silabo = ?
            ORDER BY fecha_desarrollo, id
        ");
            $stSesDestList = self::$db->prepare("
            SELECT id
            FROM acad_sesion_aprendizaje
            WHERE id_prog_actividad_silabo = ?
            ORDER BY fecha_desarrollo, id
        ");

            // Update sesión (si se copian fechas, ya va en el SET)
            $setSes = "
            tipo_actividad = :tipo_actividad,
            tipo_sesion = :tipo_sesion,
            denominacion = :denominacion,
            id_ind_logro_competencia_vinculado = :id_il_comp,
            id_ind_logro_capacidad_vinculado = :id_il_cap,
            logro_sesion = :logro_sesion,
            bibliografia_obligatoria_docente = :bod,
            bibliografia_opcional_docente = :bodop,
            bibliografia_obligatoria_estudiante = :boe,
            bibliografia_opcional_estudiante = :boeop,
            anexos = :anexos
        ";
            if ($copiarFechas) $setSes .= ", fecha_desarrollo = :fecha_desarrollo";
            $stUpdSes = self::$db->prepare("UPDATE acad_sesion_aprendizaje SET {$setSes} WHERE id = :id_ses");

            // Actividades (HIJO): leer y actualizar por 'momento' (no pisamos 'momento')
            $stActOri = self::$db->prepare("
            SELECT indicador_logro_sesion, tecnica, instrumentos, peso, momento
            FROM acad_actividad_evaluacion_sesion_aprendizaje
            WHERE id_sesion_aprendizaje = ?
            ORDER BY id
        ");
            $stActDest = self::$db->prepare("
            SELECT id, momento
            FROM acad_actividad_evaluacion_sesion_aprendizaje
            WHERE id_sesion_aprendizaje = ?
            ORDER BY id
        ");
            $updAct = self::$db->prepare("
            UPDATE acad_actividad_evaluacion_sesion_aprendizaje
            SET indicador_logro_sesion = :ind,
                tecnica                 = :tec,
                instrumentos            = :ins,
                peso                    = :peso
            WHERE id = :id
        ");

            // Momentos (HIJO): leer y actualizar por 'momento' (no pisamos 'momento')
            $stMomOri = self::$db->prepare("
            SELECT momento, estrategia, actividad, recursos, tiempo
            FROM acad_momentos_sesion_aprendizaje
            WHERE id_sesion_aprendizaje = ?
            ORDER BY id
        ");
            $stMomDest = self::$db->prepare("
            SELECT id, momento
            FROM acad_momentos_sesion_aprendizaje
            WHERE id_sesion_aprendizaje = ?
            ORDER BY id
        ");
            $updMom = self::$db->prepare("
            UPDATE acad_momentos_sesion_aprendizaje
            SET estrategia = :est,
                actividad  = :act,
                recursos   = :rec,
                tiempo     = :tiempo
            WHERE id = :id
        ");

            $contSemanas = 0;
            $contSesiones = 0;

            foreach ($pasDest as $pd) {
                $week = (int)$pd['semana'];
                if (!isset($oriByWeek[$week])) continue; // no hay matching de semana en origen
                $po = $oriByWeek[$week]; // PAS origen para esa semana

                // 3.1 Actualizar PAS destino (dinámico para no romper si no hay ILC)
                $baseSet = "
                elemento_capacidad = :elem,
                actividades_aprendizaje = :acts,
                contenidos_basicos = :cont,
                tareas_previas = :tareas
            ";
                $sqlPas = "UPDATE acad_programacion_actividades_silabo SET {$baseSet}";
                $paramsPas = [
                    ':elem' => $po['elemento_capacidad'],
                    ':acts' => $po['actividades_aprendizaje'],
                    ':cont' => $po['contenidos_basicos'],
                    ':tareas' => $po['tareas_previas'],
                ];
                if ($copiarIndicadorILC && !empty($po['id_ind_logro_aprendizaje'])) {
                    $sqlPas .= ", id_ind_logro_aprendizaje = :id_ilc";
                    $paramsPas[':id_ilc'] = (int)$po['id_ind_logro_aprendizaje'];
                }
                if ($copiarFechas) {
                    $sqlPas .= ", fecha = :fecha";
                    $paramsPas[':fecha'] = $po['fecha'];
                }
                $sqlPas .= " WHERE id = :id_pas";
                $paramsPas[':id_pas'] = $pd['id'];
                self::$db->prepare($sqlPas)->execute($paramsPas);
                $contSemanas++;

                // 3.2 Sesiones: 1↔1 (origen i -> destino i)
                $stSesOriList->execute([$po['id']]);
                $oriSes = $stSesOriList->fetchAll(PDO::FETCH_ASSOC);

                $stSesDestList->execute([$pd['id']]);
                $destSes = $stSesDestList->fetchAll(PDO::FETCH_ASSOC);

                if (!$oriSes || !$destSes) continue;

                $pairs = min(count($oriSes), count($destSes));
                for ($i = 0; $i < $pairs; $i++) {
                    $sesOri = $oriSes[$i];
                    $sd     = $destSes[$i];

                    // 3.2.1 Update sesión destino con todos los campos
                    $paramsSes = [
                        ':tipo_actividad' => $sesOri['tipo_actividad'],
                        ':tipo_sesion'    => $sesOri['tipo_sesion'],
                        ':denominacion'   => $sesOri['denominacion'],
                        ':id_il_comp'     => $sesOri['id_ind_logro_competencia_vinculado'],
                        ':id_il_cap'      => $sesOri['id_ind_logro_capacidad_vinculado'],
                        ':logro_sesion'   => $sesOri['logro_sesion'],
                        ':bod'            => $sesOri['bibliografia_obligatoria_docente'],
                        ':bodop'          => $sesOri['bibliografia_opcional_docente'],
                        ':boe'            => $sesOri['bibliografia_obligatoria_estudiante'],
                        ':boeop'          => $sesOri['bibliografia_opcional_estudiante'],
                        ':anexos'         => $sesOri['anexos'],
                        ':id_ses'         => $sd['id'],
                    ];
                    if ($copiarFechas && !empty($sesOri['fecha_desarrollo'])) {
                        $paramsSes[':fecha_desarrollo'] = $sesOri['fecha_desarrollo'];
                    }
                    $stUpdSes->execute($paramsSes);

                    // 3.2.2 HIJO: Actividades (match por 'momento', actualizar sin tocar 'momento')
                    $stActOri->execute([$sesOri['id']]);
                    $actsO = $stActOri->fetchAll(PDO::FETCH_ASSOC);
                    $stActDest->execute([$sd['id']]);
                    $actsD = $stActDest->fetchAll(PDO::FETCH_ASSOC);

                    if ($actsO && $actsD) {
                        $byMomO = [];
                        foreach ($actsO as $r) {
                            $byMomO[$normMom($r['momento'])][] = $r;
                        }
                        $byMomD = [];
                        foreach ($actsD as $r) {
                            $byMomD[$normMom($r['momento'])][] = $r;
                        }
                        $momentos = array_intersect(array_keys($byMomO), array_keys($byMomD));
                        foreach ($momentos as $mKey) {
                            $lo = $byMomO[$mKey];
                            $ld = $byMomD[$mKey];
                            $k  = min(count($lo), count($ld));
                            for ($j = 0; $j < $k; $j++) {
                                $o = $lo[$j];
                                $d = $ld[$j];
                                $updAct->execute([
                                    ':ind'  => $o['indicador_logro_sesion'],
                                    ':tec'  => $o['tecnica'],
                                    ':ins'  => $o['instrumentos'],
                                    ':peso' => $o['peso'],
                                    ':id'   => $d['id'],
                                ]);
                            }
                        }
                    }

                    // 3.2.3 HIJO: Momentos (match por 'momento', actualizar sin tocar 'momento')
                    $stMomOri->execute([$sesOri['id']]);
                    $momsO = $stMomOri->fetchAll(PDO::FETCH_ASSOC);
                    $stMomDest->execute([$sd['id']]);
                    $momsD = $stMomDest->fetchAll(PDO::FETCH_ASSOC);

                    if ($momsO && $momsD) {
                        $byMomO = [];
                        foreach ($momsO as $r) {
                            $byMomO[$normMom($r['momento'])][] = $r;
                        }
                        $byMomD = [];
                        foreach ($momsD as $r) {
                            $byMomD[$normMom($r['momento'])][] = $r;
                        }
                        $momentos = array_intersect(array_keys($byMomO), array_keys($byMomD));
                        foreach ($momentos as $mKey) {
                            $lo = $byMomO[$mKey];
                            $ld = $byMomD[$mKey];
                            $k  = min(count($lo), count($ld));
                            for ($j = 0; $j < $k; $j++) {
                                $o = $lo[$j];
                                $d = $ld[$j];
                                $updMom->execute([
                                    ':est'    => $o['estrategia'],
                                    ':act'    => $o['actividad'],
                                    ':rec'    => $o['recursos'],
                                    ':tiempo' => $o['tiempo'],
                                    ':id'     => $d['id'],
                                ]);
                            }
                        }
                    }

                    $contSesiones++;
                }
            }

            self::$db->commit();

            return [
                'silabo_actualizado'    => true,
                'semanas_actualizadas'  => $contSemanas,
                'sesiones_actualizadas' => $contSesiones
            ];
        } catch (\Throwable $e) {
            self::$db->rollBack();
            throw $e;
        }
    }
}
