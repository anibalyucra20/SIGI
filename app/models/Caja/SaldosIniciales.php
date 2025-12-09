<?php

namespace App\Models\Caja;

use Core\Model;
use PDO;

class SaldosIniciales extends Model
{
    public function listar()
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_saldos ORDER BY anio_mes DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'anio_mes',
            2 => 'saldo_inicial'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'anio_mes';

        $where  = '';
        $params = [];


        if (!empty($filters['anio_mes'])) {
            $where   .= " WHERE anio_mes LIKE :anio_mes ";
            $params[':anio_mes'] = "%{$filters['anio_mes']}%";
        }

        $sqlWhere = $where;

        $sql = "SELECT *
                FROM caja_saldos
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
        $sqlTotal = "SELECT COUNT(*) FROM caja_saldos 
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
        $stmt = self::$db->prepare("SELECT * FROM caja_saldos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE caja_saldos SET 
                        anio_mes = :anio_mes, 
                        saldo_inicial = :saldo_inicial
                    WHERE id = :id";
            $params = [
                ':anio_mes'                => $data['anio_mes'],
                ':saldo_inicial'           => $data['saldo_inicial'],
                ':id'                    => $data['id']
            ];
        } else {
            $sql = "INSERT INTO caja_saldos 
                        (anio_mes, saldo_inicial) 
                    VALUES 
                        (:anio_mes, :saldo_inicial)";
            $params = [
                ':anio_mes'                => $data['anio_mes'],
                ':saldo_inicial'           => $data['saldo_inicial'],
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
        $stmt = self::$db->prepare("DELETE FROM caja_saldos WHERE id=?");
        $stmt->execute([$id]);
    }
    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['anio_mes', 'saldo_inicial'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }


        // 3. Unicidad (codigo)
        if (!$isEdit || ($isEdit && $id)) {
            // DNI único
            $q = "SELECT COUNT(*) FROM caja_saldos WHERE anio_mes = :anio_mes";
            $params = [':anio_mes' => $data['anio_mes']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El año y mes ya está registrado.";
        }
        return $errores;
    }
}
