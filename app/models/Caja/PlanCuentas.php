<?php

namespace App\Models\Caja;

use Core\Model;
use PDO;

class PlanCuentas extends Model
{
    public function listar()
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_cuentas ORDER BY estado DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'codigo',
            2 => 'nombre',
            3 => 'estado'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'codigo';

        $where  = '';
        $params = [];


        if (!empty($filters['codigo'])) {
            $where   .= " WHERE codigo LIKE :codigo ";
            $params[':codigo'] = "%{$filters['codigo']}%";
        }
        if (!empty($filters['nombre'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " nombre LIKE :nombre ";
            $params[':nombre'] = "%{$filters['nombre']}%";
        }

        $sqlWhere = $where;

        $sql = "SELECT *
                FROM caja_cuentas
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
        $sqlTotal = "SELECT COUNT(*) FROM caja_cuentas 
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
        $stmt = self::$db->prepare("SELECT * FROM caja_cuentas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE caja_cuentas SET 
                        codigo = :codigo, 
                        nombre = :nombre,
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':codigo'                => $data['codigo'],
                ':nombre'                => $data['nombre'],
                ':estado'                => $data['estado'],
                ':id'                    => $data['id']
            ];
        } else {
            $sql = "INSERT INTO caja_cuentas 
                        (codigo, nombre, estado) 
                    VALUES 
                        (:codigo, :nombre, :estado)";
            $params = [
                ':codigo'                => $data['codigo'],
                ':nombre'                => $data['nombre'],
                ':estado'                => $data['estado']
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
        $stmt = self::$db->prepare("DELETE FROM caja_cuentas WHERE id=?");
        $stmt->execute([$id]);
    }
    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['codigo', 'nombre', 'estado'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }


        // 3. Unicidad (codigo)
        if (!$isEdit || ($isEdit && $id)) {
            // codigo único
            $q = "SELECT COUNT(*) FROM caja_cuentas WHERE codigo = :codigo";
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
