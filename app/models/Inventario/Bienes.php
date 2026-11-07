<?php
namespace App\Models\Inventario;

use Core\Model;
use PDO;

class Bienes extends Model {

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_inv_ambiente',
            2 => 'codigo_patrimonial',
            3 => 'denominacion',
            4 => 'marca',
            5 => 'modelo',
            6 => 'color',
            7 => 'serie',
            8 => 'estado_bien'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        // Estructura base SQL con WHERE 1=1 al estilo del docente
        $sql = "SELECT * FROM inventario_bienes WHERE 1=1";
        $params = [];

        // Filtro por código patrimonial
        if (!empty($filters['codigo_patrimonial'])) {
            $sql .= " AND codigo_patrimonial LIKE :codigo_patrimonial";
            $params[':codigo_patrimonial'] = '%' . $filters['codigo_patrimonial'] . '%';
        }
        
        // Filtro por denominación
        if (!empty($filters['denominacion'])) {
            $sql .= " AND denominacion LIKE :denominacion";
            $params[':denominacion'] = '%' . $filters['denominacion'] . '%';
        }
        
        // Filtro por estado del bien
        if (!empty($filters['estado_bien'])) {
            $sql .= " AND estado_bien = :estado_bien";
            $params[':estado_bien'] = $filters['estado_bien'];
        }

        // Filtro de disponibilidad (Activo / Inactivo)
        if (isset($filters['activo']) && $filters['activo'] !== '') {
            $sql .= " AND activo = :activo";
            $params[':activo'] = $filters['activo'];
        }

        // Filtro por ambiente agregado
        if (!empty($filters['id_inv_ambiente'])) {
            $sql .= " AND id_inv_ambiente = :id_inv_ambiente";
            $params[':id_inv_ambiente'] = $filters['id_inv_ambiente'];
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

    // ¡Restaurada! Es necesaria para editar
    public function find($id)
    {
        $sql = "SELECT * FROM inventario_bienes WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE inventario_bienes SET 
                        id_inv_ambiente    = :id_inv_ambiente,
                        codigo_patrimonial = :codigo_patrimonial,
                        denominacion       = :denominacion,
                        marca              = :marca,
                        modelo             = :modelo,
                        color              = :color,
                        serie              = :serie,
                        estado_bien        = :estado_bien,
                        otros              = :otros,
                        observaciones      = :observaciones
                    WHERE id = :id";
            $params = [
                ':id_inv_ambiente'    => $data['id_inv_ambiente'],
                ':codigo_patrimonial' => $data['codigo_patrimonial'],
                ':denominacion'       => $data['denominacion'],
                ':marca'              => $data['marca'],
                ':modelo'             => $data['modelo'],
                ':color'              => $data['color'],
                ':serie'              => $data['serie'],
                ':estado_bien'        => $data['estado_bien'],
                ':otros'              => $data['otros'],
                ':observaciones'      => $data['observaciones'],
                ':id'                 => $data['id']
            ];
        } else {
            $sql = "INSERT INTO inventario_bienes 
                        (id_inv_ambiente, codigo_patrimonial, denominacion, marca, modelo, color, serie, estado_bien, otros, observaciones) 
                    VALUES 
                        (:id_inv_ambiente, :codigo_patrimonial, :denominacion, :marca, :modelo, :color, :serie, :estado_bien, :otros, :observaciones)";
            $params = [
                ':id_inv_ambiente'    => $data['id_inv_ambiente'],
                ':codigo_patrimonial' => $data['codigo_patrimonial'],
                ':denominacion'       => $data['denominacion'],
                ':marca'              => $data['marca'],
                ':modelo'             => $data['modelo'],
                ':color'              => $data['color'],
                ':serie'              => $data['serie'],
                ':estado_bien'        => $data['estado_bien'],
                ':otros'              => $data['otros'],
                ':observaciones'      => $data['observaciones'] 
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
        // Solo actualiza el campo, no borra nada físicamente
        $stmt = self::$db->prepare("UPDATE inventario_bienes SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function activar($id)
    {
        // Vuelve a poner el bien como ACTIVO (1)
        $stmt = self::$db->prepare("UPDATE inventario_bienes SET activo = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}