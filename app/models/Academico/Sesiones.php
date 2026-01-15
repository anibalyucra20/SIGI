<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Sesiones extends Model
{

    // 9. Registra sesión de aprendizaje
    public function registrarSesionAprendizaje($data)
    {
        $sql = "INSERT INTO acad_sesion_aprendizaje
            (id_prog_actividad_silabo, tipo_actividad, tipo_sesion, denominacion, fecha_desarrollo,
             id_ind_logro_competencia_vinculado, id_ind_logro_capacidad_vinculado,
             logro_sesion, bibliografia_obligatoria_docente, bibliografia_opcional_docente,
             bibliografia_obligatoria_estudiante, bibliografia_opcional_estudiante, anexos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_prog_actividad_silabo'],
            $data['tipo_actividad'],
            $data['tipo_sesion'],
            $data['denominacion'],
            $data['fecha_desarrollo'],
            $data['id_ind_logro_competencia_vinculado'],
            $data['id_ind_logro_capacidad_vinculado'],
            $data['logro_sesion'],
            $data['bibliografia_obligatoria_docente'],
            $data['bibliografia_opcional_docente'],
            $data['bibliografia_obligatoria_estudiante'],
            $data['bibliografia_opcional_estudiante'],
            $data['anexos']
        ]);
        return self::$db->lastInsertId();
    }

    // 10. Registra momento de sesión de aprendizaje
    public function registrarMomentoSesion($data)
    {
        $sql = "INSERT INTO acad_momentos_sesion_aprendizaje
            (id_sesion_aprendizaje, momento, estrategia, actividad, recursos, tiempo)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_sesion_aprendizaje'],
            $data['momento'],
            $data['estrategia'],
            $data['actividad'],
            $data['recursos'],
            $data['tiempo']
        ]);
        return self::$db->lastInsertId();
    }

    // 11. Registra actividad de evaluación de sesión de aprendizaje
    public function registrarActividadEvaluacionSesion($data)
    {
        $sql = "INSERT INTO acad_actividad_evaluacion_sesion_aprendizaje
             (id_sesion_aprendizaje, indicador_logro_sesion, tecnica, instrumentos, peso, momento)
             VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_sesion_aprendizaje'],
            $data['indicador_logro_sesion'],
            $data['tecnica'],
            $data['instrumentos'],
            $data['peso'],
            $data['momento']
        ]);
        return self::$db->lastInsertId();
    }


    public function getDatosUnidad($id_programacion, $id_ind_logro_aprendizaje = null)
    {
        // Trae datos principales
        $stmt = self::$db->prepare("
                SELECT 
            ud.nombre AS unidad,
            u.apellidos_nombres AS docente,
            pr.nombre AS programa,
            pa.nombre AS periodo,
            mf.descripcion AS modulo,
            uc.descripcion AS unidad_competencia,
            s.descripcion AS semestre,
            pl.nombre AS plan,
            pa.nombre AS periodo_academico,
            pa.fecha_inicio AS periodo_inicio,
            pa.fecha_fin AS periodo_fin,
            pud.turno,
            pud.seccion,
            cap.descripcion AS capacidad,
            ct.descripcion AS comp_transversal,
            pa.nombre AS periodo_lectivo
        FROM acad_programacion_unidad_didactica pud
        INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
        INNER JOIN sigi_usuarios u ON pud.id_docente = u.id
        INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
        INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
        INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
        INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
        INNER JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
        LEFT JOIN sigi_capacidades cap ON cap.id_unidad_didactica = ud.id
        LEFT JOIN sigi_competencias uc ON uc.id = cap.id_competencia          -- << corrección
        LEFT JOIN sigi_competencias ct ON ct.tipo = 'TRANSVERSAL' AND ct.id_plan_estudio = pl.id
        WHERE pud.id = ?
        LIMIT 1;
        ");
        $stmt->execute([$id_programacion]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($datos['docente']));
        $datos['docente'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];

        // Si te pasan el id del indicador de logro, traes su info
        if ($id_ind_logro_aprendizaje) {
            $stmt2 = self::$db->prepare("SELECT codigo, descripcion 
            FROM sigi_ind_logro_capacidad 
            WHERE id = ?");
            $stmt2->execute([$id_ind_logro_aprendizaje]);
            $indicador = $stmt2->fetch(PDO::FETCH_ASSOC);
            $datos['ind_logro_codigo'] = $indicador['codigo'] ?? '';
            $datos['ind_logro_descripcion'] = $indicador['descripcion'] ?? '';
        } else {
            $datos['ind_logro_codigo'] = '';
            $datos['ind_logro_descripcion'] = '';
        }

        return $datos;
    }



    public function getSesionesPaginadas($id_programacion, $length, $start, $orderCol, $orderDir)
    {
        $sql = "SELECT sa.id, pas.semana, sa.denominacion
        FROM acad_silabos s
        INNER JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
        INNER JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id
        WHERE s.id_prog_unidad_didactica = ?
        ORDER BY pas.semana ASC, sa.id ASC
        LIMIT ? OFFSET ?";

        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(1, $id_programacion, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*)
            FROM acad_silabos s
            INNER JOIN acad_programacion_actividades_silabo pas ON pas.id_silabo = s.id
            INNER JOIN acad_sesion_aprendizaje sa ON sa.id_prog_actividad_silabo = pas.id
            WHERE s.id_prog_unidad_didactica = ?";
        $stmtTotal = self::$db->prepare($sqlTotal);
        $stmtTotal->execute([$id_programacion]);
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }




    // Trae los datos de la sesión para edición
    public function getSesionParaEditar($id_sesion)
    {
        try {
            $sql = "
            SELECT 
                sa.id,
                sa.id_prog_actividad_silabo,
                pas.semana,
                pas.id_ind_logro_aprendizaje,
                sa.denominacion,
                sa.fecha_desarrollo,
                sa.tipo_actividad,
                sa.logro_sesion,
                sa.bibliografia_obligatoria_docente,
                s.id_prog_unidad_didactica as id_programacion,         -- id de la programación de unidad
                pu.id_unidad_didactica,             -- FK real a la unidad
                ud.nombre   AS nombre_unidad        -- datos de la unidad
            FROM acad_sesion_aprendizaje sa
            INNER JOIN acad_programacion_actividades_silabo pas 
                ON sa.id_prog_actividad_silabo = pas.id
            INNER JOIN acad_silabos s 
                ON pas.id_silabo = s.id
            INNER JOIN acad_programacion_unidad_didactica pu
                ON s.id_prog_unidad_didactica = pu.id
            INNER JOIN sigi_unidad_didactica ud
                ON pu.id_unidad_didactica = ud.id
            WHERE sa.id = ?
            LIMIT 1;

        ";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$id_sesion]);
            $row = $stmt->fetch();
            if (!$row) {
                error_log("getSesionParaEditar: no encontró registro para id_sesion=$id_sesion");
            }
            return $row;
        } catch (\PDOException $e) {
            error_log("Error getSesionParaEditar: " . $e->getMessage());
            return false;
        }
    }



    // Trae los 3 momentos para la sesión
    public function getMomentosSesion($id_sesion)
    {
        $stmt = self::$db->prepare("SELECT id, momento, actividad, recursos, tiempo
                                FROM acad_momentos_sesion_aprendizaje
                                WHERE id_sesion_aprendizaje = ?
                                ORDER BY FIELD(momento, 'Inicio', 'Desarrollo', 'Cierre'), id");
        $stmt->execute([$id_sesion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trae las actividades de evaluación para la sesión
    public function getActividadesEvaluacion($id_sesion)
    {
        $stmt = self::$db->prepare("SELECT id, indicador_logro_sesion, tecnica, instrumentos, momento
                                FROM acad_actividad_evaluacion_sesion_aprendizaje
                                WHERE id_sesion_aprendizaje = ?
                                ORDER BY FIELD(momento, 'Inicio', 'Desarrollo', 'Cierre'), id");
        $stmt->execute([$id_sesion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualiza TODO de la sesión, momentos y eval
    public function actualizarSesionCompleta($id_sesion, $data)
    {
        // Actualiza acad_sesion_aprendizaje
        $stmt = self::$db->prepare("UPDATE acad_sesion_aprendizaje
        SET denominacion = :denominacion,
            fecha_desarrollo = :fecha_desarrollo,
            tipo_actividad = :tipo_actividad,
            logro_sesion = :logro_sesion,
            bibliografia_obligatoria_docente = :bibliografia
        WHERE id = :id");
        $stmt->execute([
            ':denominacion' => $data['denominacion'],
            ':fecha_desarrollo' => $data['fecha_desarrollo'],
            ':tipo_actividad' => $data['tipo_actividad'],
            ':logro_sesion' => $data['logro_sesion'],
            ':bibliografia' => $data['bibliografia'],
            ':id' => $id_sesion
        ]);

        // Busca la actividad silabo vinculada
        $stmt = self::$db->prepare("SELECT id_prog_actividad_silabo FROM acad_sesion_aprendizaje WHERE id = ?");
        $stmt->execute([$id_sesion]);
        $id_actividad = $stmt->fetchColumn();

        // Actualiza acad_programacion_actividades_silabo
        /*$stmt2 = self::$db->prepare("UPDATE acad_programacion_actividades_silabo
        SET id_ind_logro_aprendizaje = :id_ind_logro_aprendizaje
        WHERE id = :id");
        $stmt2->execute([
            ':id_ind_logro_aprendizaje' => $data['id_ind_logro_aprendizaje'],
            ':id' => $id_actividad
        ]);*/

        // Actualizar momentos
        foreach ($this->getMomentosSesion($id_sesion) as $m) {
            $id_momento = $m['id'];
            $stmtM = self::$db->prepare("UPDATE acad_momentos_sesion_aprendizaje
            SET actividad = :actividad,
                recursos = :recursos,
                tiempo = :tiempo
            WHERE id = :id");
            $stmtM->execute([
                ':actividad' => $data['momentos']["actividad_$id_momento"] ?? '',
                ':recursos' => $data['momentos']["recursos_$id_momento"] ?? '',
                ':tiempo' => $data['momentos']["tiempo_$id_momento"] ?? 20,
                ':id' => $id_momento
            ]);
        }

        // Actualizar actividades de evaluación
        foreach ($this->getActividadesEvaluacion($id_sesion) as $a) {
            $id_eval = $a['id'];
            $stmtA = self::$db->prepare("UPDATE acad_actividad_evaluacion_sesion_aprendizaje
            SET indicador_logro_sesion = :indicador,
                tecnica = :tecnica,
                instrumentos = :instrumentos
            WHERE id = :id");
            $stmtA->execute([
                ':indicador' => $data['activEval']["indicador_$id_eval"] ?? '',
                ':tecnica' => $data['activEval']["tecnica_$id_eval"] ?? '',
                ':instrumentos' => $data['activEval']["instrumentos_$id_eval"] ?? '',
                ':id' => $id_eval
            ]);
        }
    }
    public function duplicarSesion($id_sesion)
    {
        self::$db->beginTransaction();
        try {
            // 1. Clonar la sesión principal
            $stmt = self::$db->prepare("SELECT * FROM acad_sesion_aprendizaje WHERE id = ?");
            $stmt->execute([$id_sesion]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            // Preparamos campos para el insert (puedes personalizar los que quieras "resetear")
            $stmt = self::$db->prepare("INSERT INTO acad_sesion_aprendizaje 
            (id_prog_actividad_silabo, tipo_actividad, tipo_sesion, fecha_desarrollo, id_ind_logro_competencia_vinculado, id_ind_logro_capacidad_vinculado, logro_sesion, bibliografia_obligatoria_docente, bibliografia_opcional_docente, bibliografia_obligatoria_estudiante, bibliografia_opcional_estudiante, anexos, denominacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $sesion['id_prog_actividad_silabo'],
                $sesion['tipo_actividad'],
                $sesion['tipo_sesion'],
                $sesion['fecha_desarrollo'], // puedes pedir una nueva fecha
                $sesion['id_ind_logro_competencia_vinculado'],
                $sesion['id_ind_logro_capacidad_vinculado'],
                $sesion['logro_sesion'],
                $sesion['bibliografia_obligatoria_docente'],
                $sesion['bibliografia_opcional_docente'],
                $sesion['bibliografia_obligatoria_estudiante'],
                $sesion['bibliografia_opcional_estudiante'],
                $sesion['anexos'],
                $sesion['denominacion'] . " (Copia)"
            ]);
            $newSesionId = self::$db->lastInsertId();

            // 2. Clonar momentos
            $stmtM = self::$db->prepare("SELECT * FROM acad_momentos_sesion_aprendizaje WHERE id_sesion_aprendizaje = ?");
            $stmtM->execute([$id_sesion]);
            $momentos = $stmtM->fetchAll(PDO::FETCH_ASSOC);
            foreach ($momentos as $m) {
                $stmtMI = self::$db->prepare("INSERT INTO acad_momentos_sesion_aprendizaje 
                (id_sesion_aprendizaje, momento, estrategia, actividad, recursos, tiempo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtMI->execute([
                    $newSesionId,
                    $m['momento'],
                    $m['estrategia'],
                    $m['actividad'],
                    $m['recursos'],
                    $m['tiempo']
                ]);
            }

            // 3. Clonar actividades de evaluación
            $stmtA = self::$db->prepare("SELECT * FROM acad_actividad_evaluacion_sesion_aprendizaje WHERE id_sesion_aprendizaje = ?");
            $stmtA->execute([$id_sesion]);
            $acts = $stmtA->fetchAll(PDO::FETCH_ASSOC);
            foreach ($acts as $a) {
                $stmtAI = self::$db->prepare("INSERT INTO acad_actividad_evaluacion_sesion_aprendizaje 
                (id_sesion_aprendizaje, indicador_logro_sesion, tecnica, instrumentos, peso, momento) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtAI->execute([
                    $newSesionId,
                    $a['indicador_logro_sesion'],
                    $a['tecnica'],
                    $a['instrumentos'],
                    $a['peso'],
                    $a['momento']
                ]);
            }

            self::$db->commit();
            return $newSesionId;
        } catch (\Exception $e) {
            self::$db->rollBack();
            throw $e;
        }
    }
    public function eliminarSesion($id_sesion)
    {
        self::$db->beginTransaction();
        try {
            // 1. Eliminar momentos
            $stmt = self::$db->prepare("DELETE FROM acad_momentos_sesion_aprendizaje WHERE id_sesion_aprendizaje = ?");
            $stmt->execute([$id_sesion]);

            // 2. Eliminar actividades de evaluación
            $stmt = self::$db->prepare("DELETE FROM acad_actividad_evaluacion_sesion_aprendizaje WHERE id_sesion_aprendizaje = ?");
            $stmt->execute([$id_sesion]);

            // 3. Eliminar la sesión principal
            $stmt = self::$db->prepare("DELETE FROM acad_sesion_aprendizaje WHERE id = ?");
            $stmt->execute([$id_sesion]);

            self::$db->commit();
            return true;
        } catch (\Exception $e) {
            self::$db->rollBack();
            throw $e;
        }
    }
}
