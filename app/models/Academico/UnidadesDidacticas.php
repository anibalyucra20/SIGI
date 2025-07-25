<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class UnidadesDidacticas extends Model
{
    protected $table = 'acad_programacion_unidad_didactica';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'pud.id',
            1 => 'pr.nombre',
            2 => 'pl.nombre',
            3 => 'mf.descripcion',
            4 => 's.descripcion',
            5 => 'ud.nombre',
            6 => 'pud.turno',
            7 => 'pud.seccion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'pud.id';

        $where = [
            "pud.id_sede = :id_sede",
            "pud.id_periodo_academico = :id_periodo",
            "pud.id_docente = :id_docente"
        ];
        $params = [
            ':id_sede' => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo'],
            ':id_docente' => $filters['id_docente']
        ];

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT pud.id, 
                       pr.nombre AS programa_nombre,
                       pl.nombre AS plan_nombre,
                       mf.descripcion AS modulo_nombre,
                       s.descripcion AS semestre_nombre,
                       ud.nombre AS unidad_nombre, 
                       pud.turno, pud.seccion
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
                INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total
        $sqlTotal = "SELECT COUNT(*)
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
                INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }
    public function actualizardatosInformeFinal($id_programacion_ud, $data)
    {
        // Actualiza acad_programacion_unidad_didactica
        $stmt = self::$db->prepare("UPDATE acad_programacion_unidad_didactica
        SET supervisado = :supervisado,
            reg_evaluacion = :reg_evaluacion,
            reg_auxiliar = :reg_auxiliar,
            prog_curricular = :prog_curricular,
            otros = :otros,
            logros_obtenidos= :logros_obtenidos,
            dificultades = :dificultades,
            sugerencias = :sugerencias
        WHERE id = :id");
        $stmt->execute([
            ':supervisado' => $data['supervisado'],
            ':reg_evaluacion' => $data['reg_evaluacion'],
            ':reg_auxiliar' => $data['reg_auxiliar'],
            ':prog_curricular' => $data['prog_curricular'],
            ':otros' => $data['otros'],
            ':logros_obtenidos' => $data['logros_obtenidos'],
            ':dificultades' => $data['dificultades'],
            ':sugerencias' => $data['sugerencias'],
            ':id' => $id_programacion_ud
        ]);
    }

    public function getPorcentajeAvanceCurricular($id_programacion)
    {
        $sql = "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN TRIM(sa.logro_sesion) != '' THEN 1 ELSE 0 END) AS desarrolladas
            FROM acad_sesion_aprendizaje sa
            INNER JOIN acad_programacion_actividades_silabo pas ON sa.id_prog_actividad_silabo = pas.id
            INNER JOIN acad_silabos s ON pas.id_silabo = s.id
            WHERE s.id_prog_unidad_didactica = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['total'] > 0) {
            return round(($row['desarrolladas'] / $row['total']) * 100, 2);
        }
        return 0;
    }

    public function getUltimaClaseDesarrollada($id_programacion)
    {
        $sql = "SELECT 
                sa.denominacion AS denominacion, 
                sa.logro_sesion, 
                pas.semana, 
                pas.fecha
            FROM acad_sesion_aprendizaje sa
            INNER JOIN acad_programacion_actividades_silabo pas ON sa.id_prog_actividad_silabo = pas.id
            INNER JOIN acad_silabos s ON pas.id_silabo = s.id
            WHERE s.id_prog_unidad_didactica = ? 
              AND TRIM(sa.logro_sesion) != ''
            ORDER BY pas.semana DESC, pas.fecha DESC
            LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return 'Semana ' . $row['semana'] . ' - ' . $row['denominacion'];
        }
        return 'No registrada';
    }

    public function getSesionesNoDesarrolladas($id_programacion)
    {
        $sql = "SELECT 
                pas.semana, 
                pas.fecha 
            FROM acad_sesion_aprendizaje sa
            INNER JOIN acad_programacion_actividades_silabo pas ON sa.id_prog_actividad_silabo = pas.id
            INNER JOIN acad_silabos s ON pas.id_silabo = s.id
            WHERE s.id_prog_unidad_didactica = ? 
              AND TRIM(sa.logro_sesion) = ''
            ORDER BY pas.semana ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            return implode(', ', array_map(function ($r) {
                return 'Semana ' . $r['semana'];
            }, $rows));
        }
        return 'Ninguna';
    }

    public function getResumenEstadisticoFinal($id_programacion, $objCalificacion)
    {
        $stmt = self::$db->prepare("
        SELECT 
            dm.id AS id_detalle_matricula,
            u.apellidos_nombres AS apellidos_nombres,
            u.genero AS genero,
            dm.id_programacion_ud,
            m.licencia
        FROM acad_detalle_matricula dm
        INNER JOIN acad_matricula m ON dm.id_matricula = m.id
        INNER JOIN acad_estudiante_programa aep ON m.id_estudiante = aep.id
        INNER JOIN sigi_usuarios u ON aep.id_usuario = u.id
        WHERE dm.id_programacion_ud = ?
    ");
        $stmt->execute([$id_programacion]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Configura aquí los valores que usas como nro_calificacion
        $nros_calificacion = [1, 2, 3]; // ejemplo: tres evaluaciones

        $resumen = [
            'total_hombres' => 0,
            'total_mujeres' => 0,
            'total_todos' => 0,
            'retirados_h' => 0,
            'retirados_m' => 0,
            'retirados_t' => 0,
            'aprobados_h' => 0,
            'aprobados_m' => 0,
            'aprobados_t' => 0,
            'desaprobados_h' => 0,
            'desaprobados_m' => 0,
            'desaprobados_t' => 0,
        ];
        //var_dump($estudiantes);
        foreach ($estudiantes as $e) {
            $genero = $e['genero']; // M o F
            $resumen['total_todos']++;

            if ($genero == 'M') $resumen['total_hombres']++;
            if ($genero == 'F') $resumen['total_mujeres']++;

            // Verifica si está retirado (licencia no vacía)
            if (trim($e['licencia']) !== '') {
                $resumen['retirados_t']++;
                if ($genero == 'M') $resumen['retirados_h']++;
                if ($genero == 'F') $resumen['retirados_m']++;
                continue; // no calcular nota
            }
            $promedio_final = $objCalificacion->promedioFinalEstudiante($e['id_detalle_matricula'], $nros_calificacion);
            if ($promedio_final >= 13) {
                $resumen['aprobados_t']++;
                if ($genero == 'M') $resumen['aprobados_h']++;
                if ($genero == 'F') $resumen['aprobados_m']++;
            } else {
                $resumen['desaprobados_t']++;
                if ($genero == 'M') $resumen['desaprobados_h']++;
                if ($genero == 'F') $resumen['desaprobados_m']++;
            }
        }

        // Cálculos de porcentajes
        $tt = $resumen['total_todos'];
        foreach (['h', 'm', 't'] as $sx) {
            $resumen["porc_hombres"]     = $tt ? round($resumen['total_hombres'] * 100 / $tt, 1) : 0;
            $resumen["porc_mujeres"]     = $tt ? round($resumen['total_mujeres'] * 100 / $tt, 1) : 0;

            $resumen["retirados_p$sx"]   = $tt ? round($resumen["retirados_$sx"] * 100 / $tt, 1) : 0;
            $resumen["aprobados_p$sx"]   = $tt ? round($resumen["aprobados_$sx"] * 100 / $tt, 1) : 0;
            $resumen["desaprobados_p$sx"] = $tt ? round($resumen["desaprobados_$sx"] * 100 / $tt, 1) : 0;
        }

        return $resumen;
    }
}
