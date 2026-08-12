<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class Programacion extends Model
{
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_sede',
            2 => 'id_periodo_academico',
            3 => 'id_docente'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $where  = '';
        $params = [];

        $where = [
            "p.id_periodo_academico = :id_periodo_academico",
            "p.id_sede = :id_sede"
        ];
        $params = [
            ':id_periodo_academico' => $filters['id_periodo_academico'],
            ':id_sede' => $filters['id_sede']
        ];

        /*if (!empty($filters['codigo'])) {
            $where   .= " codigo LIKE :codigo ";
            $params[':codigo'] = "%{$filters['codigo']}%";
        }
        if (!empty($filters['nombre'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " nombre LIKE :nombre ";
            $params[':nombre'] = "%{$filters['nombre']}%";
        }*/

        $sqlWhere = $where ? (" WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT s.nombre AS sede_nombre, pa.nombre AS periodo_academico_nombre, u.apellidos_nombres AS docente, p.*
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
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['docente_nombre'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $sql = "SELECT * FROM tutoria_programacion WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE tutoria_programacion SET 
                        id_docente = :id_docente
                    WHERE id = :id";
            $params = [
                ':id_docente'               => $data['id_docente'],
                ':id'                       => $data['id']
            ];
        } else {
            $sql = "INSERT INTO tutoria_programacion 
                        (id_sede, id_periodo_academico, id_docente, conclusiones) 
                    VALUES 
                        (:id_sede, :id_periodo_academico, :id_docente, :conclusiones)";
            $params = [
                ':id_sede'                  => $data['id_sede'],
                ':id_periodo_academico'     => $data['id_periodo_academico'],
                ':id_docente'               => $data['id_docente'],
                ':conclusiones'             => $data['conclusiones']
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        if (empty($data['id'])) {
            return self::$db->lastInsertId();
        }
        return $data['id'];
    }


    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM tutoria_programacion WHERE id=?");
        $stmt->execute([$id]);
    }
}
