<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class Programacion extends Model
{
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'p.id',
            1 => 's.nombre',
            2 => 'pa.nombre',
            3 => 'u.apellidos_nombres'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'p.id';

        $where = [
            "p.id_periodo_academico = :id_periodo_academico",
            "p.id_sede = :id_sede"
        ];
        $params = [
            ':id_periodo_academico' => $filters['id_periodo_academico'],
            ':id_sede'              => $filters['id_sede']
        ];

        if (!empty($filters['id_docente'])) {
            $where[] = "p.id_docente = :id_docente";
            $params[':id_docente'] = $filters['id_docente'];
        }

        $sqlWhere = " WHERE " . implode(" AND ", $where);

        $sql = "SELECT s.nombre AS sede_nombre,
                       pa.nombre AS periodo_academico_nombre,
                       u.apellidos_nombres AS docente,
                       u.dni AS docente_dni,
                       u.correo AS docente_correo,
                       (SELECT COUNT(*) FROM tutoria_estudiantes te WHERE te.id_programacion_tutoria = p.id) AS total_estudiantes,
                       (SELECT COUNT(*) FROM tutoria_sesion_grupal sg WHERE sg.id_tutoria = p.id) AS total_sesiones_grupales,
                       p.*
                FROM tutoria_programacion p 
                INNER JOIN sigi_sedes s ON p.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON p.id_periodo_academico = pa.id
                INNER JOIN sigi_usuarios u ON p.id_docente = u.id 
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";

        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total
        $sqlTotal = "SELECT COUNT(*) FROM tutoria_programacion p
                     INNER JOIN sigi_sedes s ON p.id_sede = s.id
                     INNER JOIN sigi_periodo_academico pa ON p.id_periodo_academico = pa.id
                     INNER JOIN sigi_usuarios u ON p.id_docente = u.id 
                     $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['docente']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0] ?? '';
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1] ?? '';
            $data[$key]['Nombres']         = $apellidos_nombres[2] ?? '';
            $data[$key]['docente_nombre']  = trim(($apellidos_nombres[0] ?? '') . ' ' . ($apellidos_nombres[1] ?? '') . ', ' . ($apellidos_nombres[2] ?? ''));
        }

        return ['data' => $data, 'total' => (int)$total];
    }

    public function find($id)
    {
        $sql = "SELECT p.*,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre,
                       u.apellidos_nombres AS docente_nombre,
                       u.dni AS docente_dni,
                       u.correo AS docente_correo
                FROM tutoria_programacion p
                INNER JOIN sigi_sedes s ON p.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON p.id_periodo_academico = pa.id
                INNER JOIN sigi_usuarios u ON p.id_docente = u.id
                WHERE p.id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nombres = explode('_', trim($row['docente_nombre']));
            $row['docente_formato'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
        }

        return $row;
    }

    public function getProgramacionPorDocente($id_docente, $id_periodo, $id_sede)
    {
        $sql = "SELECT p.*,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre,
                       (SELECT COUNT(*) FROM tutoria_estudiantes te WHERE te.id_programacion_tutoria = p.id) AS total_estudiantes
                FROM tutoria_programacion p
                INNER JOIN sigi_sedes s ON p.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON p.id_periodo_academico = pa.id
                WHERE p.id_docente = ?
                  AND p.id_periodo_academico = ?
                  AND p.id_sede = ?
                LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_docente, $id_periodo, $id_sede]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE tutoria_programacion SET 
                        id_docente = :id_docente,
                        conclusiones = :conclusiones
                    WHERE id = :id";
            $params = [
                ':id_docente'   => $data['id_docente'],
                ':conclusiones' => $data['conclusiones'] ?? '',
                ':id'           => $data['id']
            ];
        } else {
            $sql = "INSERT INTO tutoria_programacion 
                        (id_sede, id_periodo_academico, id_docente, conclusiones) 
                    VALUES 
                        (:id_sede, :id_periodo_academico, :id_docente, :conclusiones)";
            $params = [
                ':id_sede'              => $data['id_sede'],
                ':id_periodo_academico' => $data['id_periodo_academico'],
                ':id_docente'           => $data['id_docente'],
                ':conclusiones'         => $data['conclusiones'] ?? ''
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        if (empty($data['id'])) {
            return self::$db->lastInsertId();
        }
        return $data['id'];
    }

    public function guardarConclusiones($id, $conclusiones)
    {
        $stmt = self::$db->prepare("UPDATE tutoria_programacion SET conclusiones = ? WHERE id = ?");
        return $stmt->execute([$conclusiones, $id]);
    }

    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM tutoria_programacion WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contarTutoresActivos($id_periodo, $id_sede)
    {
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM tutoria_programacion WHERE id_periodo_academico = ? AND id_sede = ?");
        $stmt->execute([$id_periodo, $id_sede]);
        return (int)$stmt->fetchColumn();
    }

    public function contarTutorandosTotales($id_periodo, $id_sede)
    {
        $sql = "SELECT COUNT(te.id)
                FROM tutoria_estudiantes te
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                WHERE tp.id_periodo_academico = ? AND tp.id_sede = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_periodo, $id_sede]);
        return (int)$stmt->fetchColumn();
    }
}
