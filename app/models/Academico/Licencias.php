<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Licencias extends Model
{
    // DataTable paginado + filtros
    public function getPaginated($filters, $length, $start)
    {
        $sql = "SELECT m.id, u.dni, u.apellidos_nombres, prog.nombre as programa, pl.nombre as plan,
                        s.descripcion as semestre, m.turno, m.seccion, m.licencia
                FROM acad_matricula m
                INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
                INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
                INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios prog ON pl.id_programa_estudios = prog.id
                INNER JOIN sigi_semestre s ON m.id_semestre = s.id
                WHERE m.licencia <> ''
                AND m.id_periodo_academico = :periodo
                AND m.id_sede = :sede";

        $params = [
            ':periodo' => $filters['periodo'],
            ':sede' => $filters['sede'],
        ];
        if (!empty($filters['dni'])) {
            $sql .= " AND u.dni LIKE :dni";
            $params[':dni'] = "%{$filters['dni']}%";
        }
        if (!empty($filters['apellidos_nombres'])) {
            $sql .= " AND u.apellidos_nombres LIKE :apellidos_nombres";
            $params[':apellidos_nombres'] = '%' . $filters['apellidos_nombres'] . '%';
        }
        if (!empty($filters['programa'])) {
            $sql .= " AND prog.id = :programa";
            $params[':programa'] = $filters['programa'];
        }
        if (!empty($filters['plan'])) {
            $sql .= " AND pl.id = :plan";
            $params[':plan'] = $filters['plan'];
        }
        if (!empty($filters['semestre'])) {
            $sql .= " AND s.id = :semestre";
            $params[':semestre'] = $filters['semestre'];
        }
        if (!empty($filters['turno'])) {
            $sql .= " AND m.turno = :turno";
            $params[':turno'] = $filters['turno'];
        }
        if (!empty($filters['seccion'])) {
            $sql .= " AND m.seccion = :seccion";
            $params[':seccion'] = $filters['seccion'];
        }
        $sqlTotal = "SELECT COUNT(*) FROM (" . $sql . ") as tmp";
        $sql .= " ORDER BY TRIM(CONVERT(u.apellidos_nombres USING utf8mb4)) COLLATE utf8mb4_spanish_ci ASC LIMIT :limit OFFSET :offset";

        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$length, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return ['data' => $data, 'total' => $total];
    }

    public function getProgramas()
    {
        return self::$db->query("SELECT id, nombre FROM sigi_programa_estudios ORDER BY nombre")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPlanesByPrograma($id_programa)
    {
        $stmt = self::$db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre");
        $stmt->execute([$id_programa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSemestresByPlan($id_plan)
    {
        $stmt = self::$db->prepare("SELECT s.id, s.descripcion FROM sigi_semestre s
                                    INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                                    WHERE mf.id_plan_estudio = ? ORDER BY s.descripcion");
        $stmt->execute([$id_plan]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function guardarLicencia($id_matricula, $licencia)
    {
        $stmt = self::$db->prepare("UPDATE acad_matricula SET licencia = ? WHERE id = ?");
        return $stmt->execute([$licencia, $id_matricula]);
    }

    public function eliminarLicencia($id)
    {
        $stmt = self::$db->prepare("UPDATE acad_matricula SET licencia = '' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Devuelve matrícula y datos, por dni+periodo+sede actual (AJAX)
    public function buscarMatriculaPorDNI($dni, $periodo, $sede)
    {
        $stmt = self::$db->prepare("SELECT m.id as id_matricula, u.apellidos_nombres, prog.nombre as programa, pl.nombre as plan,
                                            s.descripcion as semestre, m.turno, m.seccion
            FROM acad_matricula m
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
            INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
            INNER JOIN sigi_programa_estudios prog ON pl.id_programa_estudios = prog.id
            INNER JOIN sigi_semestre s ON m.id_semestre = s.id
            WHERE m.id_periodo_academico = ? AND m.id_sede = ? AND u.dni = ? LIMIT 1");
        $stmt->execute([$periodo, $sede, $dni]);
        $estudiante = $stmt->fetch(\PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($estudiante['apellidos_nombres']));
        $estudiante['ApellidoPaterno'] = $apellidos_nombres[0];
        $estudiante['ApellidoMaterno'] = $apellidos_nombres[1];
        $estudiante['Nombres'] = $apellidos_nombres[2];
        $estudiante['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $estudiante;
    }
}
