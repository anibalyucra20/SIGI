<?php

namespace App\Models\Caja;

use Core\Model;
use PDO;

class Proveedores extends Model
{
    public function listar()
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_proveedores ORDER BY estado DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'ruc',
            2 => 'razon_social',
            3 => 'telefono',
            4 => 'estado'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'ruc';

        $where  = '';
        $params = [];


        if (!empty($filters['ruc'])) {
            $where   .= " WHERE ruc LIKE :ruc ";
            $params[':ruc'] = "%{$filters['ruc']}%";
        }
        if (!empty($filters['razon_social'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " razon_social LIKE :razon_social ";
            $params[':razon_social'] = "%{$filters['razon_social']}%";
        }


        $sqlWhere = $where;

        $sql = "SELECT *
                FROM caja_proveedores
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
        $sqlTotal = "SELECT COUNT(*) FROM caja_proveedores 
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
        $stmt = self::$db->prepare("SELECT * FROM caja_proveedores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE caja_proveedores SET 
                        razon_social = :razon_social, 
                        ruc = :ruc,
                        direccion = :direccion,
                        telefono = :telefono,
                        correo = :correo,
                        ref_contacto = :ref_contacto,
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':razon_social'        => $data['razon_social'],
                ':ruc'                 => $data['ruc'],
                ':direccion'           => $data['direccion'],
                ':telefono'            => $data['telefono'],
                ':correo'              => $data['correo'],
                ':ref_contacto'        => $data['ref_contacto'],
                ':estado'              => $data['estado'],
                ':id'                  => $data['id']
            ];
        } else {
            $sql = "INSERT INTO caja_proveedores 
                        (razon_social, ruc, direccion, telefono, correo, ref_contacto) 
                    VALUES 
                        (:razon_social, :ruc, :direccion, :telefono, :correo, :ref_contacto)";
            $params = [
                ':razon_social'        => $data['razon_social'],
                ':ruc'                 => $data['ruc'],
                ':direccion'           => $data['direccion'],
                ':telefono'            => $data['telefono'],
                ':correo'              => $data['correo'],
                ':ref_contacto'        => $data['ref_contacto']
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
        $stmt = self::$db->prepare("DELETE FROM caja_proveedores WHERE id=?");
        $stmt->execute([$id]);
    }
    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['razon_social', 'ruc', 'direccion', 'telefono', 'correo', 'ref_contacto'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }

        // 2. Formato
        if (!empty($data['ruc']) && !preg_match('/^\d{11}$/', $data['ruc'])) {
            $errores[] = "El RUC debe tener 11 dígitos numéricos.";
        }
        if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        // 3. Unicidad (RUC)
        if (!$isEdit || ($isEdit && $id)) {
            // DNI único
            $q = "SELECT COUNT(*) FROM caja_proveedores WHERE ruc = :ruc";
            $params = [':ruc' => $data['ruc']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El RUC ya está registrado.";
        }
        return $errores;
    }
}
