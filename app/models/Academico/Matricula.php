<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;
use Exception;

class Matricula extends Model
{
    protected $table = 'acad_matricula';

    // Paginar matrículas con filtros
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columns = [
            0 => 'm.id',
            1 => 'u.dni',
            2 => 'u.apellidos_nombres',
            3 => 'prog.nombre',
            4 => 'pl.nombre',
            5 => 's.descripcion',
            6 => 'm.turno',
            7 => 'm.seccion',
            8 => 'm.estado'
        ];
        $orderBy = $columns[$orderCol] ?? 'm.id';

        $where = [
            "m.id_periodo_academico = :periodo",
            "m.id_sede = :sede"
        ];
        $params = [
            ':periodo' => $filters['periodo'],
            ':sede' => $filters['sede']
        ];

        if (!empty($filters['dni'])) {
            $where[] = "u.dni LIKE :dni";
            $params[':dni'] = "%{$filters['dni']}%";
        }
        if (!empty($filters['apellidos_nombres'])) {
            $where[] = "u.apellidos_nombres LIKE :apellidos_nombres";
            $params[':apellidos_nombres'] = "%{$filters['apellidos_nombres']}%";
        }
        if (!empty($filters['programa'])) {
            $where[] = "prog.id = :programa";
            $params[':programa'] = $filters['programa'];
        }
        if (!empty($filters['plan'])) {
            $where[] = "pl.id = :plan";
            $params[':plan'] = $filters['plan'];
        }
        if (!empty($filters['semestre'])) {
            $where[] = "s.id = :semestre";
            $params[':semestre'] = $filters['semestre'];
        }
        if (!empty($filters['turno'])) {
            $where[] = "m.turno = :turno";
            $params[':turno'] = $filters['turno'];
        }
        if (!empty($filters['seccion'])) {
            $where[] = "m.seccion = :seccion";
            $params[':seccion'] = $filters['seccion'];
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT m.id, u.dni, u.apellidos_nombres, prog.nombre AS programa, pl.nombre AS plan, 
                    s.descripcion AS semestre, m.turno, m.seccion
                FROM acad_matricula m
                INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
                INNER JOIN sigi_usuarios u ON u.id = ep.id_usuario
                INNER JOIN sigi_planes_estudio pl ON pl.id = ep.id_plan_estudio
                INNER JOIN sigi_programa_estudios prog ON prog.id = pl.id_programa_estudios
                INNER JOIN sigi_semestre s ON s.id = m.id_semestre
                $sqlWhere
                ORDER BY $orderBy $orderDir
                LIMIT :limit OFFSET :offset";

        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total count
        $sqlTotal = "SELECT COUNT(*) FROM acad_matricula m
                INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
                INNER JOIN sigi_usuarios u ON u.id = ep.id_usuario
                INNER JOIN sigi_planes_estudio pl ON pl.id = ep.id_plan_estudio
                INNER JOIN sigi_programa_estudios prog ON prog.id = pl.id_programa_estudios
                INNER JOIN sigi_semestre s ON s.id = m.id_semestre
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    // Métodos para cargar combos de filtro
    public function getProgramas()
    {
        $sql = "SELECT id, nombre FROM sigi_programa_estudios ORDER BY nombre";
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPlanes()
    {
        $sql = "SELECT id, nombre FROM sigi_planes_estudio ORDER BY nombre";
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getSemestres()
    {
        $sql = "SELECT id, descripcion FROM sigi_semestre ORDER BY descripcion";
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDatosMatricula($id_matricula)
    {
        $sql = "SELECT m.*, 
                   pl.id as plan_id, 
                   pl.nombre as plan, 
                   prog.nombre as programa, 
                   m.turno, 
                   m.seccion
            FROM acad_matricula m
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
            INNER JOIN sigi_programa_estudios prog ON pl.id_programa_estudios = prog.id
            WHERE m.id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_matricula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function  buscarDetalleMatriculaByIdProgramacion($id_prog)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_detalle_matricula WHERE id_programacion_ud = ? ORDER BY orden LIMIT 1");
        $stmt->execute([$id_prog]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function buscarCalificacionByIdDetalleMatricula_nro($id_detalle, $nro_calificacion)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_calificacion WHERE id_detalle_matricula = ? AND nro_calificacion = ?");
        $stmt->execute([$id_detalle, $nro_calificacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function buscarEvaluacionByIdCalificacion_detalle($id, $detalle)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_evaluacion WHERE id_calificacion = ? AND detalle = ? ORDER BY id");
        $stmt->execute([$id, $detalle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    function buscarCriterioEvaluacionByEvaluacion($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_criterio_evaluacion WHERE id_evaluacion = ? ORDER BY id");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatriculaByDetalle($id_detalle)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_detalle_matricula WHERE id = ?");
        $stmt->execute([$id_detalle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getDetalleMatricula($id_matricula)
    {
        $sql = "SELECT dm.id, prog.nombre as programa, s.descripcion as semestre, ud.nombre as unidad_didactica
            FROM acad_detalle_matricula dm
            INNER JOIN acad_programacion_unidad_didactica pud ON dm.id_programacion_ud = pud.id
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
            INNER JOIN sigi_programa_estudios prog ON pl.id_programa_estudios = prog.id
            WHERE dm.id_matricula = ?
            ORDER BY dm.orden";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_matricula]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstudianteByMatricula($id_matricula)
    {
        $sql = "SELECT u.apellidos_nombres FROM acad_matricula m
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_usuarios u ON u.id = ep.id_usuario
            WHERE m.id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_matricula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // === Buscar estudiante activo, rol 7, en sede ===
    public function buscarEstudiantePorDNI($dni, $sede_actual)
    {
        $stmt = self::$db->prepare("SELECT id, apellidos_nombres, id_sede, estado FROM sigi_usuarios WHERE dni = ? AND id_rol = 7 AND estado = 1");
        $stmt->execute([$dni]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) return null;
        if ($usuario['id_sede'] != $sede_actual) return null;
        return $usuario;
    }

    // === Programas y Planes de Estudio del estudiante ===
    public function getProgramasEstudiante($id_usuario)
    {
        $sql = "SELECT DISTINCT pe.id_programa_estudios AS id, pro.nombre
            FROM acad_estudiante_programa ep
            INNER JOIN sigi_planes_estudio pe ON ep.id_plan_estudio = pe.id
            INNER JOIN sigi_programa_estudios pro ON pe.id_programa_estudios = pro.id
            WHERE ep.id_usuario = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getPlanesEstudiante($id_usuario)
    {
        $sql = "SELECT pe.id, pe.nombre, pe.id_programa_estudios
            FROM acad_estudiante_programa ep
            INNER JOIN sigi_planes_estudio pe ON ep.id_plan_estudio = pe.id
            WHERE ep.id_usuario = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // === Unidades Didácticas programadas según plan, semestre, periodo, sede ===
    public function getUDsProgramadas($idPlan, $idSemestre, $periodo, $sede, $turno, $seccion)
    {
        $sql = "SELECT pud.id, ud.nombre, u.apellidos_nombres as docente, pud.turno, pud.seccion, s.descripcion as semestre
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_usuarios u ON pud.id_docente = u.id
            INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            WHERE pud.id_sede = ? 
              AND pud.id_periodo_academico = ?
              AND pud.turno = ?
              AND pud.seccion = ?
              AND ud.id_semestre = ?
              AND mf.id_plan_estudio = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$sede, $periodo, $turno, $seccion, $idSemestre, $idPlan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // === Validación de matrícula duplicada ===
    public function validarMatricula($id_usuario, $id_plan_estudio, $periodo, $sede, $id_semestre)
    {
        // Buscar id_estudiante_programa
        $stmt = self::$db->prepare("SELECT id FROM acad_estudiante_programa WHERE id_usuario=? AND id_plan_estudio=?");
        $stmt->execute([$id_usuario, $id_plan_estudio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['ok' => false, 'msg' => "No existe inscripción previa a este plan y periodo."];

        // ¿Ya existe matrícula?
        $stmt2 = self::$db->prepare("SELECT id FROM acad_matricula WHERE id_estudiante=? AND id_periodo_academico=? AND id_sede=? AND id_semestre=?");
        $stmt2->execute([$row['id'], $periodo, $sede, $id_semestre]);
        if ($stmt2->fetch()) return ['ok' => false, 'msg' => "El estudiante ya está matriculado en este semestre, sede y periodo."];
        return ['ok' => true];
    }

    // === REGISTRAR MATRÍCULA Y TODA LA CADENA (TRANSACCIÓN) ===
    public function registrarMatriculaCompleta($id_usuario, $data, $periodo, $sede, &$errores)
    {
        try {
            self::$db->beginTransaction();

            // 1. Buscar id_estudiante_programa (debe existir)
            $stmt = self::$db->prepare("SELECT id FROM acad_estudiante_programa WHERE id_usuario=? AND id_plan_estudio=?");
            $stmt->execute([$id_usuario, $data['id_plan_estudio']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new Exception("No existe vínculo estudiante-plan-periodo.");

            $id_estudiante = $row['id'];

            // 2. Insertar matrícula
            $stmt = self::$db->prepare("INSERT INTO acad_matricula (id_periodo_academico, id_sede, id_semestre, turno, seccion, id_estudiante, licencia) VALUES (?, ?, ?, ?, ?, ?, '')");
            $stmt->execute([$periodo, $sede, $data['id_semestre'], $data['turno'], $data['seccion'], $id_estudiante]);
            $id_matricula = self::$db->lastInsertId();

            $this->registrar_detalle_matricula($id_matricula, $data);

            self::$db->commit();
            return true;
        } catch (Exception $e) {
            self::$db->rollBack();
            $errores[] = $e->getMessage();
            return false;
        }
    }

    // Obtener ID de unidad didáctica a partir de la programación UD
    private function getIdUnidadDidacticaByProgUD($id_prog_ud)
    {
        $stmt = self::$db->prepare("SELECT id_unidad_didactica FROM acad_programacion_unidad_didactica WHERE id = ?");
        $stmt->execute([$id_prog_ud]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id_unidad_didactica'] : 0;
    }


    public function getSemestresDisponibles($id_plan)
    {
        $stmt = self::$db->prepare("SELECT s.id, s.descripcion
        FROM sigi_semestre s
        INNER JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
        WHERE mf.id_plan_estudio = ?
        ORDER BY s.descripcion");
        $stmt->execute([$id_plan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnidadesDisponibles($id_matricula, $id_semestre)
    {
        // Busca unidades programadas para el semestre y plan, y filtra las ya seleccionadas
        $sql = "SELECT pud.id as id_programacion_ud, ud.nombre as unidad_didactica, mf.descripcion as modulo
        FROM acad_programacion_unidad_didactica pud
        INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
        INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
        INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
        WHERE s.id = ?
        AND pud.id NOT IN (
            SELECT id_programacion_ud FROM acad_detalle_matricula WHERE id_matricula = ?
        )
        ORDER BY ud.nombre";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_semestre, $id_matricula]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }







    public function eliminarDetalleMatricula($id_detalle)
    {
        try {
            self::$db->beginTransaction();

            // 1. Eliminar asistencias
            $stmt = self::$db->prepare("DELETE FROM acad_asistencia WHERE id_detalle_matricula = ?");
            $stmt->execute([$id_detalle]);

            // 2. Buscar todas las calificaciones
            $stmtCal = self::$db->prepare("SELECT id FROM acad_calificacion WHERE id_detalle_matricula = ?");
            $stmtCal->execute([$id_detalle]);
            $calificaciones = $stmtCal->fetchAll(PDO::FETCH_COLUMN);

            foreach ($calificaciones as $id_calificacion) {
                // 2.1 Buscar todas las evaluaciones de la calificación
                $stmtEval = self::$db->prepare("SELECT id FROM acad_evaluacion WHERE id_calificacion = ?");
                $stmtEval->execute([$id_calificacion]);
                $evaluaciones = $stmtEval->fetchAll(PDO::FETCH_COLUMN);

                foreach ($evaluaciones as $id_evaluacion) {
                    // 2.1.1 Eliminar criterios de evaluación
                    $stmtCriterios = self::$db->prepare("DELETE FROM acad_criterio_evaluacion WHERE id_evaluacion = ?");
                    $stmtCriterios->execute([$id_evaluacion]);
                }
                // 2.2 Eliminar evaluaciones
                $stmtDeleteEval = self::$db->prepare("DELETE FROM acad_evaluacion WHERE id_calificacion = ?");
                $stmtDeleteEval->execute([$id_calificacion]);
            }
            // 2.3 Eliminar calificaciones
            $stmtDeleteCal = self::$db->prepare("DELETE FROM acad_calificacion WHERE id_detalle_matricula = ?");
            $stmtDeleteCal->execute([$id_detalle]);

            // 3. Eliminar detalle de matrícula
            $stmtDeleteDet = self::$db->prepare("DELETE FROM acad_detalle_matricula WHERE id = ?");
            $stmtDeleteDet->execute([$id_detalle]);

            self::$db->commit();
            return true;
        } catch (\PDOException $e) {
            self::$db->rollBack();
            error_log("Error al eliminar detalle matricula: " . $e->getMessage());
            return false;
        }
    }








    public function agregarUnidadesDidacticas($id_matricula, $id_semestre, $unidades)
    {
        try {
            self::$db->beginTransaction();



            $this->registrar_detalle_matricula($id_matricula, $unidades);

            self::$db->commit();
            return ['ok' => true];
        } catch (\PDOException $e) {
            self::$db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }





    //---------------------------------------- FUNCIONES  PRINCIPALES ----------------------------
    public function calcular_cantidad_criterios($id_prog, $det_evaluacion, $nro_calif)
    {
        $b_det_mat = $this->buscarDetalleMatriculaByIdProgramacion($id_prog) ?? 0;
        $cantidad = count($b_det_mat);
        var_dump($b_det_mat);
        if ($cantidad < 1) {
            // si no hay ningun matriculado regresamos 2 como los criterios de evaluacion
            return 2;
        } else {
            $b_califacion = $this->buscarCalificacionByIdDetalleMatricula_nro($b_det_mat['id'], $nro_calif) ?? 0;

            $b_evaluacion = $this->buscarEvaluacionByIdCalificacion_detalle($b_califacion['id'], $det_evaluacion) ?? 0;

            $b_crit_evaluacion = $this->buscarCriterioEvaluacionByEvaluacion($b_evaluacion['id']) ?? 0;
            $cant_crit = count($b_crit_evaluacion);
            if ($cant_crit < 1) {
                return 2;
            } else {
                return $cant_crit;
            }
        }
    }

    public function registrar_detalle_matricula($id_matricula, $data)
    {
        // 1. Obtener correlativo actual de orden (continua el máximo)
        $stmt = self::$db->prepare("SELECT MAX(orden) FROM acad_detalle_matricula WHERE id_matricula = ?");
        $stmt->execute([$id_matricula]);
        $orden = (int)$stmt->fetchColumn();
        // 3. Insertar detalle_matricula y el resto

        foreach ($data['ud_programadas'] as $id_prog_ud) {
            // Detalle
            $stmt = self::$db->prepare("INSERT INTO acad_detalle_matricula (id_matricula, orden, id_programacion_ud, recuperacion, mostrar_calificacion) VALUES (?, ?, ?, '', 0)");
            $stmt->execute([$id_matricula, $orden++, $id_prog_ud]);
            $id_detalle_matricula = self::$db->lastInsertId();

            // 4. Acad ASISTENCIA (por cada sesión de aprendizaje de la programación UD)
            $stmtSes = self::$db->prepare("SELECT id FROM acad_sesion_aprendizaje WHERE id_prog_actividad_silabo IN 
            (SELECT aps.id FROM acad_programacion_actividades_silabo aps 
            WHERE aps.id_silabo = (SELECT s.id FROM acad_silabos s WHERE s.id_prog_unidad_didactica = ?))");
            $stmtSes->execute([$id_prog_ud]);
            $sesiones = $stmtSes->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sesiones as $ses) {
                $stmtA = self::$db->prepare("INSERT INTO acad_asistencia (id_sesion_aprendizaje, id_detalle_matricula, asistencia) VALUES (?, ?, '')");
                $stmtA->execute([$ses['id'], $id_detalle_matricula]);
            }

            // 5. Acad CALIFICACION (por cada indicador de logro de capacidad de la UD)
            $stmtCap = self::$db->prepare("SELECT c.id, ilc.id AS id_ind FROM sigi_capacidades c 
            INNER JOIN sigi_ind_logro_capacidad ilc ON ilc.id_capacidad = c.id 
            WHERE c.id_unidad_didactica = ?");
            $stmtCap->execute([$this->getIdUnidadDidacticaByProgUD($id_prog_ud)]);
            $ind_logros = $stmtCap->fetchAll(PDO::FETCH_ASSOC);

            $nro = 1;
            foreach ($ind_logros as $ilc) {
                $stmtCal = self::$db->prepare("INSERT INTO acad_calificacion (id_detalle_matricula, nro_calificacion, mostrar_calificacion) VALUES (?, ?, 0)");
                $stmtCal->execute([$id_detalle_matricula, $nro]);
                $id_calificacion = self::$db->lastInsertId();

                // 6. Acad EVALUACION (3 x calificación)
                $tipos = ['Conceptual', 'Procedimental', 'Actitudinal'];
                foreach ($tipos as $detalle) {
                    $stmtEv = self::$db->prepare("INSERT INTO acad_evaluacion (id_calificacion, detalle, ponderado) VALUES (?, ?, 33)");
                    $stmtEv->execute([$id_calificacion, $detalle]);
                    $id_evaluacion = self::$db->lastInsertId();

                    // 7. Acad CRITERIO_EVALUACION
                    $criterios_a_crear = $this->calcular_cantidad_criterios($id_prog_ud, $detalle, $nro);
                    for ($i = 0; $i < $criterios_a_crear; $i++) {
                        $stmtCri = self::$db->prepare("INSERT INTO acad_criterio_evaluacion (id_evaluacion, orden, detalle, calificacion) VALUES (?, ?, ?, '')");
                        $stmtCri->execute([$id_evaluacion, $i + 1, '']);
                    }
                } // foreach tipo
                $nro++;
            } // foreach ind_logros
        } // foreach ud_programadas
    }
}
