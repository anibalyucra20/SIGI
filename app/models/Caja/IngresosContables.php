<?php

namespace App\Models\Caja;

use Core\Model;
use PDO;

class IngresosContables extends Model
{
    public function listar()
    {
        $stmt = self::$db->prepare("SELECT * FROM caja_ingreso_contable ORDER BY correlativo DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function utlimo_correlativo()
    {
        $stmt = self::$db->prepare("SELECT correlativo FROM caja_ingreso_contable ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'correlativo',
            1 => 'fecha',
            2 => 'id_rubro_ingreso_contable',
            3 => 'total_ingreso',
            4 => 'id_centro_costos_afectado ',
            5 => 'id_proveedor'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'correlativo';

        $where  = '';
        $params = [];

        if (!empty($filters['correlativo'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " correlativo LIKE :correlativo ";
            $params[':correlativo'] = "%{$filters['correlativo']}%";
        }
        if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta']) && ($filters['fecha_desde'] <= $filters['fecha_hasta'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " fecha BETWEEN :fecha_desde AND :fecha_hasta ";
            $params[':fecha_desde'] = "{$filters['fecha_desde']}";
            $params[':fecha_hasta'] = "{$filters['fecha_hasta']}";
        }
        if (!empty($filters['id_rubro_ingreso_contable'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " id_rubro_ingreso_contable = :id_rubro_ingreso_contable ";
            $params[':id_rubro_ingreso_contable'] = "{$filters['id_rubro_ingreso_contable']}";
        }
        if (!empty($filters['id_medio_pago'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " id_medio_pago = :id_medio_pago ";
            $params[':id_medio_pago'] = "{$filters['id_medio_pago']}";
        }
        if (!empty($filters['id_centro_costos_afectado'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " id_centro_costos_afectado = :id_centro_costos_afectado ";
            $params[':id_centro_costos_afectado'] = "{$filters['id_centro_costos_afectado']}";
        }
        if (!empty($filters['id_cuenta_afectada'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " id_cuenta_afectada = :id_cuenta_afectada ";
            $params[':id_cuenta_afectada'] = "{$filters['id_cuenta_afectada']}";
        }
        if (!empty($filters['id_proveedor'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " id_proveedor = :id_proveedor ";
            $params[':id_proveedor'] = "{$filters['id_proveedor']}";
        }


        $sqlWhere = $where;

        $sql = "SELECT cic.id, cic.correlativo, cic.fecha, cic.id_rubro_ingreso_contable, cic.id_medio_pago, cic.total_ingreso, cic.numero, cic.observacion, cic.id_centro_costos_afectado, cic.id_cuenta_afectada, cic.id_proveedor, cic.id_tipo_documento, cic.serie_documento, cic.numero_documento, cic.fecha_documento, cic.observacion_documento, cric.descripcion AS descripcion_rubro_ingreso_contable, ccc.descripcion AS descripcion_centro_costos, cp.razon_social AS rs_proveedor
                FROM caja_ingreso_contable cic
                INNER JOIN caja_rubros_ingresos_contab cric
                ON cic.id_rubro_ingreso_contable  = cric.id
                INNER JOIN caja_centro_costos ccc
                ON cic.id_centro_costos_afectado = ccc.id
                INNER JOIN caja_proveedores cp
                ON cic.id_proveedor = cp.id
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
        $sqlTotal = "SELECT COUNT(*) FROM caja_ingreso_contable 
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
        $stmt = self::$db->prepare("SELECT * FROM caja_ingreso_contable WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE caja_ingreso_contable SET 
                        fecha = :fecha, 
                        id_rubro_ingreso_contable = :id_rubro_ingreso_contable,
                        id_medio_pago = :id_medio_pago,
                        total_ingreso = :total_ingreso,
                        numero = :numero,
                        observacion = :observacion,
                        id_centro_costos_afectado = :id_centro_costos_afectado,
                        id_cuenta_afectada = :id_cuenta_afectada,
                        id_proveedor = :id_proveedor,
                        id_tipo_documento = :id_tipo_documento,
                        serie_documento = :serie_documento,
                        numero_documento = :numero_documento,
                        fecha_documento = :fecha_documento,
                        observacion_documento = :observacion_documento
                    WHERE id = :id";
            $params = [
                ':fecha'                    => $data['fecha'],
                ':id_rubro_ingreso_contable' => $data['id_rubro_ingreso_contable'],
                ':id_medio_pago'            => $data['id_medio_pago'],
                ':total_ingreso'             => $data['total_ingreso'],
                ':numero'                   => $data['numero'],
                ':observacion'              => $data['observacion'],
                ':id_centro_costos_afectado' => $data['id_centro_costos_afectado'],
                ':id_cuenta_afectada'       => $data['id_cuenta_afectada'],
                ':id_proveedor'             => $data['id_proveedor'],
                ':id_tipo_documento'        => $data['id_tipo_documento'],
                ':serie_documento'          => $data['serie_documento'],
                ':numero_documento'         => $data['numero_documento'],
                ':fecha_documento'          => $data['fecha_documento'],
                ':observacion_documento'    => $data['observacion_documento'],
                ':id'                       => $data['id']
            ];
        } else {
            $sql = "INSERT INTO caja_ingreso_contable 
                        (correlativo, fecha, id_rubro_ingreso_contable, id_medio_pago, total_ingreso, numero, observacion, id_centro_costos_afectado, id_cuenta_afectada, id_proveedor, id_tipo_documento, serie_documento, numero_documento, fecha_documento, observacion_documento) 
                    VALUES 
                        (:correlativo, :fecha, :id_rubro_ingreso_contable, :id_medio_pago, :total_ingreso, :numero, :observacion, :id_centro_costos_afectado, :id_cuenta_afectada, :id_proveedor, :id_tipo_documento, :serie_documento, :numero_documento, :fecha_documento, :observacion_documento)";
            $params = [
                ':correlativo'              => $data['correlativo'],
                ':fecha'                    => $data['fecha'],
                ':id_rubro_ingreso_contable' => $data['id_rubro_ingreso_contable'],
                ':id_medio_pago'            => $data['id_medio_pago'],
                ':total_ingreso'             => $data['total_ingreso'],
                ':numero'                   => $data['numero'],
                ':observacion'              => $data['observacion'],
                ':id_centro_costos_afectado' => $data['id_centro_costos_afectado'],
                ':id_cuenta_afectada'       => $data['id_cuenta_afectada'],
                ':id_proveedor'             => $data['id_proveedor'],
                ':id_tipo_documento'        => $data['id_tipo_documento'],
                ':serie_documento'          => $data['serie_documento'],
                ':numero_documento'         => $data['numero_documento'],
                ':fecha_documento'          => $data['fecha_documento'] ?? '0000-00-00',
                ':observacion_documento'    => $data['observacion_documento']
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
        $stmt = self::$db->prepare("DELETE FROM caja_ingreso_contable WHERE id=?");
        $stmt->execute([$id]);
    }
    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['fecha', 'id_rubro_ingreso_contable', 'id_medio_pago', 'total_ingreso', 'numero', 'id_centro_costos_afectado', 'id_cuenta_afectada', 'id_proveedor', 'id_tipo_documento', 'serie_documento', 'numero_documento'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }

        // 2. Formato
        /*if (!empty($data['ruc']) && !preg_match('/^\d{11}$/', $data['ruc'])) {
            $errores[] = "El RUC debe tener 11 dígitos numéricos.";
        }
        if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido.";
        }*/

        // 3. Unicidad (RUC)
        if (!$isEdit || ($isEdit && $id)) {
            // DNI único
            $q = "SELECT COUNT(*) FROM caja_ingreso_contable WHERE correlativo = :correlativo";
            $params = [':correlativo' => $data['correlativo']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El correlativo ya está registrado.";
        }
        return $errores;
    }
}
