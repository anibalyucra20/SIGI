<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Reportes extends Model
{
    // 1. Obtener cabecera informativa del reporte
    public function getCabeceraNomina($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id)
    {
        $sql = "
         SELECT 
                pe.nombre  AS programa,
                pl.nombre  AS plan,
                mf.descripcion AS modulo,
                s.descripcion  AS semestre,
                :turno   AS turno,
                :seccion AS seccion,
                pa.nombre AS periodo
            FROM sigi_semestre s
            INNER JOIN sigi_modulo_formativo   mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio     pl ON mf.id_plan_estudio   = pl.id
            INNER JOIN sigi_programa_estudios  pe ON pl.id_programa_estudios = pe.id
            INNER JOIN sigi_periodo_academico  pa ON pa.id = :periodo_id
            WHERE pe.id = :id_programa
              AND s.id  = :id_semestre
            LIMIT 1
    ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            ':turno'       => $turno,
            ':seccion'     => $seccion,
            ':periodo_id'  => $periodo_id,
            ':id_programa' => $id_programa,
            ':id_semestre' => $id_semestre
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    // 2. Obtener unidades didácticas del programa y semestre
    public function getUnidadesDidacticas($id_programa, $id_semestre)
    {
        $sql = "
        SELECT ud.id, ud.nombre,
        (ud.creditos_teorico + ud.creditos_practico) AS creditos
            FROM sigi_unidad_didactica ud
            INNER JOIN sigi_semestre          s  ON ud.id_semestre        = s.id
            INNER JOIN sigi_modulo_formativo  mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio    pl ON mf.id_plan_estudio    = pl.id
            WHERE pl.id_programa_estudios = ?  AND s.id = ?
            ORDER BY ud.orden
    ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programa, $id_semestre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 3. Obtener estudiantes matriculados en ese contexto
    public function getEstudiantesMatriculados($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id)
    {
        $sql = "
       SELECT dm.id AS id_detalle_matricula,
                u.id        AS id_usuario,
                   u.dni,
                   u.apellidos_nombres,
                   pud.id_unidad_didactica AS id_ud
            FROM acad_detalle_matricula dm
            INNER JOIN acad_matricula m        ON dm.id_matricula = m.id
            INNER JOIN acad_programacion_unidad_didactica pud
                                               ON dm.id_programacion_ud   = pud.id
            INNER JOIN acad_estudiante_programa ep ON m.id_estudiante = ep.id
            INNER JOIN sigi_usuarios u         ON ep.id_usuario   = u.id
            /* Validar programa por el plan/semestre */
            INNER JOIN sigi_semestre  s        ON m.id_semestre   = s.id
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pl  ON mf.id_plan_estudio    = pl.id
            WHERE pl.id_programa_estudios = ?
              AND s.id              = ?
              AND m.turno           = ?
              AND m.seccion         = ?
              AND m.id_periodo_academico = ?
              AND m.id_sede         = ?
            ORDER BY u.apellidos_nombres

    ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 4. Verificar si un estudiante está matriculado en una unidad
    public function getUnidadesPorEstudiante(int $id_detalle_matricula): array
    {
        $sql = "
        SELECT  pud.id_unidad_didactica
        FROM    acad_detalle_matricula dm
        INNER JOIN acad_programacion_unidad_didactica pud
               ON dm.id_programacion_ud = pud.id
        WHERE   dm.id = ?
    ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_detalle_matricula]);

        /* Devuelve un array plano con los IDs de unidad didáctica */
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    public function promedioFinalUD(int $id_detalle_matricula, int $id_ud): ?int
    {
        // 1) obtener id_programacion_ud correspondiente a la UD
        $sql = "SELECT id
                  FROM acad_programacion_unidad_didactica
                 WHERE id_unidad_didactica = ?
                   AND id_periodo_academico = (SELECT id_periodo_academico
                                                 FROM acad_matricula m
                                                 JOIN acad_detalle_matricula dm ON dm.id_matricula = m.id
                                                WHERE dm.id = ? LIMIT 1)
                   LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_ud, $id_detalle_matricula]);
        $progUd = $stmt->fetchColumn();
        if (!$progUd) return null;

        // 2) buscar la calificación principal (nro_calificacion=1 por ej.)
        $stmt2 = self::$db->prepare("
              SELECT id
                FROM acad_calificacion
               WHERE id_detalle_matricula = ?
                 AND nro_calificacion      = 1
                 LIMIT 1");
        $stmt2->execute([$id_detalle_matricula]);
        $idCalif = $stmt2->fetchColumn();
        if (!$idCalif) return null;

        // 3) invocar el modelo Calificaciones para calcular nota
        require_once __DIR__ . '/Calificaciones.php';
        $calModel = new \App\Models\Academico\Calificaciones();
        $nota = $calModel->notaCalificacion($idCalif);
        return ($nota === '' || $nota === null) ? null : (int)$nota;
    }

    public function getNrosCalificacionPorUd(int $id_ud): array
    {
        $sql = "
        SELECT DISTINCT c.nro_calificacion
        FROM   acad_calificacion c
        INNER JOIN acad_detalle_matricula dm  ON dm.id = c.id_detalle_matricula
        INNER JOIN acad_programacion_unidad_didactica pud
               ON pud.id = dm.id_programacion_ud
        WHERE  pud.id_unidad_didactica = ?
        ORDER  BY c.nro_calificacion
    ";
        $st = self::$db->prepare($sql);
        $st->execute([$id_ud]);

        return $st->fetchAll(PDO::FETCH_COLUMN);   // p.e. [1,2,3,4]
    }


    /* --------------- 1-A  :  evaluaciones vigentes por UD ---------------- */
    public function getNrosEvaluacionPorUd(int $id_programacion_ud): array
    {
        $sql = "SELECT DISTINCT nro_calificacion
              FROM acad_calificacion
             WHERE id_detalle_matricula IN (
                   SELECT id FROM acad_detalle_matricula
                    WHERE id_programacion_ud = ?)
          ORDER BY nro_calificacion";
        $st  = self::$db->prepare($sql);
        $st->execute([$id_programacion_ud]);
        return $st->fetchAll(PDO::FETCH_COLUMN);   // [1,2,3]
    }

    /* --------------- 1-B  :  calificaciones detalle --------------------- */
    /* ----------------------------------------------------------
 *  getCalifDetalladas  — devuelve 1 fila por:
 *      estudiante × UD × nro_calificacion
 * --------------------------------------------------------- */
    public function getCalifDetalladas(
        int    $id_programa,
        int    $id_sem,
        string $turno,
        string $secc,
        int    $periodo_id,
        int    $sede_id
    ) {
        $sql = "
      SELECT u.id               AS id_usuario,
             u.dni,
             u.apellidos_nombres,
             pud.id_unidad_didactica  AS id_ud,
             c.nro_calificacion,
             c.id                     AS id_calif            -- 🔑
      FROM   acad_detalle_matricula dm
      JOIN   acad_calificacion            c   ON c.id_detalle_matricula   = dm.id
      JOIN   acad_matricula               m   ON dm.id_matricula          = m.id
      JOIN   acad_estudiante_programa     ep  ON m.id_estudiante          = ep.id
      JOIN   sigi_usuarios                u   ON ep.id_usuario            = u.id
      JOIN   acad_programacion_unidad_didactica pud
                                   ON dm.id_programacion_ud    = pud.id
      JOIN   sigi_semestre                s   ON m.id_semestre            = s.id
      JOIN   sigi_modulo_formativo        mf  ON s.id_modulo_formativo    = mf.id
      JOIN   sigi_planes_estudio          pl  ON mf.id_plan_estudio       = pl.id
      WHERE  pl.id_programa_estudios = ?
        AND  s.id                  = ?
        AND  m.turno               = ?
        AND  m.seccion             = ?
        AND  m.id_periodo_academico= ?
        AND  m.id_sede             = ?
      ORDER  BY u.apellidos_nombres, id_ud, nro_calificacion";

        $st = self::$db->prepare($sql);
        $st->execute([
            $id_programa,
            $id_sem,
            $turno,
            $secc,
            $periodo_id,
            $sede_id
        ]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }



    public function idProgramacionUd(
        int    $id_ud,
        int    $periodo_id,
        int    $sede_id,
        string $turno,
        string $seccion
    ): ?int {
        $sql = "SELECT id
              FROM acad_programacion_unidad_didactica
             WHERE id_unidad_didactica   = ?
               AND id_periodo_academico  = ?
               AND id_sede               = ?
               AND turno                 = ?
               AND seccion               = ?
             LIMIT 1";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $id_ud,
            $periodo_id,
            $sede_id,
            $turno,
            $seccion
        ]);

        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    /* ─────────  Si un estudiante tiene al menos una UD marcada en “recuperación” ───────── */
    public function tieneRecuperacion(int $id_detalle_matricula): bool
    {
        // En acad_detalle_matricula el campo "recuperacion" guarda 'SI'/'NO'
        $sql = "SELECT recuperacion
              FROM acad_detalle_matricula
             WHERE id = ? LIMIT 1";
        $st  = self::$db->prepare($sql);
        $st->execute([$id_detalle_matricula]);
        return strtoupper(($st->fetchColumn() ?? 'NO')) === 'SI';
    }







    /* -------------- 5. LISTADO DE MATRÍCULAS PARA EL COORDINADOR -------------- */
    public function getMatriculasCoordinador(
        int $id_usuario,           // coordinador logueado
        int $periodo_id,
        int $sede_id,
        $id_programa = null,  // filtros opcionales
        $id_plan     = null,
        $id_sem      = null,
        $turno    = null,
        $seccion  = null
    ): array {
        $sql = "
      SELECT  m.id                AS id_matricula,
              u.dni,
              u.apellidos_nombres,
              pe.id               AS id_programa,
              pe.nombre           AS programa,
              pl.id               AS id_plan,
              pl.nombre           AS plan,
              s.id                AS id_semestre,
              s.descripcion       AS semestre,
              m.turno,
              m.seccion
      FROM    acad_matricula           m
      JOIN    acad_estudiante_programa aep ON aep.id = m.id_estudiante
      JOIN    sigi_usuarios            u   ON u.id = aep.id_usuario
      JOIN    sigi_semestre            s   ON s.id = m.id_semestre
      JOIN    sigi_modulo_formativo    mf  ON mf.id = s.id_modulo_formativo
      JOIN    sigi_planes_estudio      pl  ON pl.id = mf.id_plan_estudio
      JOIN    sigi_programa_estudios   pe  ON pe.id = pl.id_programa_estudios
      JOIN    sigi_coordinador_pe_periodo cpp
                ON cpp.id_programa_estudio = pe.id
               AND cpp.id_usuario          = :id_coord
               AND cpp.id_periodo          = :periodo
               AND cpp.id_sede             = :sede
      WHERE   m.id_periodo_academico = :periodo
        AND   m.id_sede              = :sede
    ";

        /* filtros adicionales dinámicos */
        $params = [
            ':id_coord' => $id_usuario,
            ':periodo'  => $periodo_id,
            ':sede'     => $sede_id
        ];
        if ($id_programa != null) {
            $sql .= " AND pe.id = :id_prog";
            $params[':id_prog'] = $id_programa;
        }
        if ($id_plan != null) {
            $sql .= " AND pl.id = :id_plan";
            $params[':id_plan'] = $id_plan;
        }
        if ($id_sem != null) {
            $sql .= " AND s.id  = :id_sem";
            $params[':id_sem']  = $id_sem;
        }
        if ($turno != null) {
            $sql .= " AND m.turno   = :turno";
            $params[':turno']   = $turno;
        }
        if ($seccion != null) {
            $sql .= " AND m.seccion = :seccion";
            $params[':seccion'] = $seccion;
        }

        $sql .= " ORDER BY u.apellidos_nombres";
        $st = self::$db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* -------------- 6. CALIFICACIONES VISIBLES POR ESTUDIANTE ------------------*/
    public function calificacionesVisibles(int $id_matricula): array
    {
        $sql = "
      SELECT  c.id                    AS id_calif,
              ud.id                   AS id_ud,
              ud.nombre               AS ud,
              c.nro_calificacion      AS nro,
              c.mostrar_calificacion,
              dm.mostrar_calificacion AS mostrar_prom,
              dm.recuperacion
      FROM  acad_calificacion              c
      JOIN  acad_detalle_matricula         dm  ON dm.id = c.id_detalle_matricula
      JOIN  acad_programacion_unidad_didactica pud ON pud.id = dm.id_programacion_ud
      JOIN  sigi_unidad_didactica          ud  ON ud.id = pud.id_unidad_didactica
      WHERE dm.id_matricula = :mat
        /*AND c.mostrar_calificacion = 1  */    /* solo visibles */
      ORDER BY ud.orden, c.nro_calificacion";
        $st = self::$db->prepare($sql);
        $st->execute([':mat' => $id_matricula]);
        $filas = $st->fetchAll(PDO::FETCH_ASSOC);

        /*  ─── Calcular la nota con el método existente notaCalificacion ─── */
        require_once __DIR__ . '/Calificaciones.php';
        $objCal = new \App\Models\Academico\Calificaciones();

        foreach ($filas as &$f) {
            $f['nota'] = $objCal->notaCalificacion($f['id_calif']);   // ← nuevo
        }
        unset($f);
        return $filas;
    }

    /* -------------- 7. PROMEDIO FINAL (visibilidad aplicada) ------------------ */
    public function promediosVisibles(int $id_matricula): array
    {
        /* 1) Traer todas las calificaciones visibles de esa matrícula */
        $sql = "
      SELECT dm.id                    AS id_dm,
             dm.recuperacion,
             dm.mostrar_calificacion,
             pud.id_unidad_didactica  AS id_ud,
             c.id                     AS id_calif
      FROM   acad_detalle_matricula         dm
      JOIN   acad_programacion_unidad_didactica pud ON pud.id = dm.id_programacion_ud
      JOIN   acad_calificacion               c  ON c.id_detalle_matricula = dm.id
      WHERE  dm.id_matricula = :mat
        AND  dm.mostrar_calificacion = 1 
      ORDER  BY pud.id_unidad_didactica";
        $st = self::$db->prepare($sql);
        $st->execute([':mat' => $id_matricula]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        /* 2) Usamos notaCalificacion() para cada calificación */
        require_once __DIR__ . '/Calificaciones.php';
        $objCal = new \App\Models\Academico\Calificaciones();

        $acum = [];                 // id_ud => [sum, n]
        $recupera = [];             // id_ud => valor recuperación
        $visible  = [];             // id_ud => mostrar_prom flag

        foreach ($rows as $r) {
            $ud = $r['id_ud'];
            if (!isset($acum[$ud])) {
                $acum[$ud] = [0, 0];
                $recupera[$ud] = $r['recuperacion'];
                $visible[$ud]  = $r['mostrar_calificacion'];   // 0|1
            }
            $nota = $objCal->notaCalificacion($r['id_calif']);
            if ($nota !== '') {
                $acum[$ud][0] += $nota;
                $acum[$ud][1] += 1;
            }
        }

        /* 3) Construir salida aplicando reglas de visibilidad/recuperación */
        $out = [];                               // id_ud => valor (''|int)
        foreach ($acum as $ud => [$suma, $n]) {
            if ($visible[$ud] != 1) {            // oculto
                $out[$ud] = '';
                continue;
            }
            // ¿recuperación numérica?
            if ($recupera[$ud] != '') {
                $out[$ud] = (int)$recupera[$ud];
            } else {
                $out[$ud] = ($n > 0) ? round($suma / $n) : '';
            }
        }
        return $out;
    }

    /* -------------- 8. ASISTENCIAS POR ESTUDIANTE ----------------------------- */
    public function asistenciasEstudiante(int $id_matricula): array
    {
        $sql = "
      SELECT  ud.nombre    AS ud,
              pas.semana   AS semana,
              a.asistencia
      FROM    acad_asistencia a
      JOIN    acad_sesion_aprendizaje sa      ON sa.id = a.id_sesion_aprendizaje
      JOIN    acad_programacion_actividades_silabo pas ON pas.id = sa.id_prog_actividad_silabo
      JOIN    acad_detalle_matricula dm       ON dm.id = a.id_detalle_matricula
      JOIN    acad_programacion_unidad_didactica pud ON pud.id = dm.id_programacion_ud
      JOIN    sigi_unidad_didactica ud        ON ud.id = pud.id_unidad_didactica
      WHERE   dm.id_matricula = :mat
      ORDER BY ud.orden, pas.semana";
        $st = self::$db->prepare($sql);
        $st->execute([':mat' => $id_matricula]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
