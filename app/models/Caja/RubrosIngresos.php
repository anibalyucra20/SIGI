<?php

namespace App\Models\Caja;

use Core\Model;
use PDO;

class RubrosIngresos extends Model
{
    public function listar()
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_rubros_ingresos ORDER BY estado DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'codigo',
            2 => 'descripcion',
            3 => 'clasificador_ingresos',
            4 => 'precio_unit',
            5 => 'estado'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'codigo';

        $where  = '';
        $params = [];


        if (!empty($filters['codigo'])) {
            $where   .= " WHERE codigo LIKE :codigo ";
            $params[':codigo'] = "%{$filters['codigo']}%";
        }
        if (!empty($filters['descripcion'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " descripcion LIKE :descripcion ";
            $params[':descripcion'] = "%{$filters['descripcion']}%";
        }


        $sqlWhere = $where;

        $sql = "SELECT *
                FROM caja_rubros_ingresos
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
        $sqlTotal = "SELECT COUNT(*) FROM caja_rubros_ingresos 
            $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_rubros_ingresos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE caja_rubros_ingresos SET 
                        codigo = :codigo, 
                        descripcion = :descripcion,
                        clasificador_ingresos = :clasificador_ingresos,
                        precio_unit = :precio_unit,
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':codigo'                => $data['codigo'],
                ':descripcion'           => $data['descripcion'],
                ':clasificador_ingresos' => $data['clasificador_ingresos'],
                ':precio_unit'           => $data['precio_unit'],
                ':estado'                => $data['estado'],
                ':id'                    => $data['id']
            ];
        } else {
            $sql = "INSERT INTO caja_rubros_ingresos 
                        (codigo, descripcion, clasificador_ingresos, precio_unit) 
                    VALUES 
                        (:codigo, :descripcion, :clasificador_ingresos, :precio_unit)";
            $params = [
                ':codigo'                => $data['codigo'],
                ':descripcion'           => $data['descripcion'],
                ':clasificador_ingresos' => $data['clasificador_ingresos'],
                ':precio_unit'           => $data['precio_unit'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        // Si es registro nuevo, devuelve el id insertado
        if (empty($data['id'])) {
            return self::$db->lastInsertId();
        }
        return $data['id'];
    }
    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM caja_rubros_ingresos WHERE id=?");
        $stmt->execute([$id]);
    }
    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['codigo', 'descripcion', 'clasificador_ingresos', 'precio_unit'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }


        // 3. Unicidad (codigo)
        if (!$isEdit || ($isEdit && $id)) {
            // DNI único
            $q = "SELECT COUNT(*) FROM caja_rubros_ingresos WHERE codigo = :codigo";
            $params = [':codigo' => $data['codigo']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El código ya está registrado.";
        }
        return $errores;
    }
}
