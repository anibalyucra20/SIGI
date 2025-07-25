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
            DATE_FORMAT(pa.fecha_inicio, '%d/%m/%Y') AS fecha_inicio,
            DATE_FORMAT(pa.fecha_fin, '%d/%m/%Y') AS fecha_fin,
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
            logro_sesion = :logro_sesion
        WHERE id_prog_actividad_silabo = :id_actividad");
        $stmt2->execute([
            ':denominacion' => $info['denominacion'],
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
}
