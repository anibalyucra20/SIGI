<?php
namespace App\Models\Inventario;

use Core\Model;
use PDO;

class Movimientos extends Model {

public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_inv_ambiente_origen',
            2 => 'id_inv_ambiente_destino',
            3 => 'tipo',
            4 => 'motivo',
            5 => 'documento_traslado',
            6 => 'fecha_hora'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        // Estructura base SQL con WHERE 1=1 al estilo del docente
        $sql = "SELECT * FROM inventario_movimientos WHERE 1=1";
        $params = [];

        // Filtro por tipo de movimiento
        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        
        // Filtro por motivo
        if (!empty($filters['motivo'])) {
            $sql .= " AND motivo LIKE :motivo";
            $params[':motivo'] = '%' . $filters['motivo'] . '%';
        }
        
        // Filtro por documento de traslado
        if (!empty($filters['documento_traslado'])) {
            $sql .= " AND documento_traslado LIKE :documento_traslado";
            $params[':documento_traslado'] = '%' . $filters['documento_traslado'] . '%';
        }

        // Total para paginación (usando el str_replace del docente)
        $sqlTotal = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $stmtTotal = self::$db->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $total = $stmtTotal->fetchColumn();

        // Orden y paginación
        $sql .= " ORDER BY $ordenarPor $orderDir LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $total];
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            // Actualizar registro existente
            $sql = "UPDATE inventario_movimientos SET 
                        id_inv_ambiente_origen  = :id_inv_ambiente_origen,
                        id_inv_ambiente_destino = :id_inv_ambiente_destino,
                        tipo                    = :tipo,
                        motivo                  = :motivo,
                        documento_traslado      = :documento_traslado
                    WHERE id = :id";
                    
            $params = [
                ':id_inv_ambiente_origen'  => $data['id_inv_ambiente_origen'],
                ':id_inv_ambiente_destino' => $data['id_inv_ambiente_destino'],
                ':tipo'                    => $data['tipo'],
                ':motivo'                  => $data['motivo'],
                ':documento_traslado'      => $data['documento_traslado'],
                ':id'                      => $data['id']
            ];
        } else {
            // Insertar nuevo registro
            $sql = "INSERT INTO inventario_movimientos 
                        (id_inv_ambiente_origen, id_inv_ambiente_destino, tipo, motivo, documento_traslado) 
                    VALUES 
                        (:id_inv_ambiente_origen, :id_inv_ambiente_destino, :tipo, :motivo, :documento_traslado)";
                        
            $params = [
                ':id_inv_ambiente_origen'  => $data['id_inv_ambiente_origen'],
                ':id_inv_ambiente_destino' => $data['id_inv_ambiente_destino'],
                ':tipo'                    => $data['tipo'],
                ':motivo'                  => $data['motivo'],
                ':documento_traslado'      => $data['documento_traslado']
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
        $stmt = self::$db->prepare("DELETE FROM inventario_movimientos WHERE id=?");
        $stmt->execute([$id]);
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM inventario_movimientos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}