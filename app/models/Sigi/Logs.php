<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Logs extends Model
{
    protected $table = 'sigi_logs';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'fecha',
            1 => 'usuario',
            2 => 'accion',
            3 => 'tabla_afectada',
            4 => 'id_registro',
            5 => 'descripcion',
            6 => 'ip_usuario'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'fecha';

        $sql = "SELECT l.*, u.apellidos_nombres AS usuario 
                FROM sigi_logs l
                LEFT JOIN sigi_usuarios u ON u.id = l.id_usuario
                WHERE 1=1";

        $params = [];

        // Búsqueda libre
        if (!empty($filters['search'])) {
            $sql .= " AND (
                u.apellidos_nombres LIKE :search
                OR l.accion LIKE :search
                OR l.descripcion LIKE :search
                OR l.tabla_afectada LIKE :search
                OR l.ip_usuario LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        // Filtro por usuario
        if (!empty($filters['usuario'])) {
            $sql .= " AND l.id_usuario = :usuario";
            $params[':usuario'] = $filters['usuario'];
        }
        // Filtro por acción
        if (!empty($filters['accion'])) {
            $sql .= " AND l.accion = :accion";
            $params[':accion'] = $filters['accion'];
        }
        // Filtro por tabla
        if (!empty($filters['tabla'])) {
            $sql .= " AND l.tabla_afectada = :tabla";
            $params[':tabla'] = $filters['tabla'];
        }
        // Filtro por fecha
        if (!empty($filters['fecha_ini'])) {
            $sql .= " AND l.fecha >= :fecha_ini";
            $params[':fecha_ini'] = $filters['fecha_ini'] . " 00:00:00";
        }
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND l.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'] . " 23:59:59";
        }

        // Total para paginación
        $sqlTotal = str_replace("SELECT l.*, u.apellidos_nombres AS usuario", "SELECT COUNT(*)", $sql);
        $stmtTotal = self::$db->prepare($sqlTotal);
        $stmtTotal->execute($params);

        $total = $stmtTotal->fetchColumn();

        // Orden y paginación
        $sql .= " ORDER BY l.$ordenarPor $orderDir LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['usuario']));
            $data[$key]['usuario'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return ['data' => $data, 'total' => $total];
    }

    public function getTablas()
    {
        $stmt = self::$db->query("SELECT DISTINCT tabla_afectada FROM sigi_logs WHERE tabla_afectada IS NOT NULL AND tabla_afectada <> '' ORDER BY tabla_afectada");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    public function getUsuarios()
    {
        $stmt = self::$db->query("SELECT DISTINCT u.id, u.apellidos_nombres FROM sigi_logs l LEFT JOIN sigi_usuarios u ON u.id = l.id_usuario ORDER BY u.apellidos_nombres");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $data;
    }

    public function getAcciones()
    {
        $stmt = self::$db->query("SELECT DISTINCT accion FROM sigi_logs ORDER BY accion");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
