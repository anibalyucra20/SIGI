<?php
namespace App\Models\Inventario;

use Core\Model;
use PDO;

class Reportes extends Model
{
    public function getBienesParaReporte($filtros)
    {
        // Consultamos directamente a inventario_bienes sin hacer JOIN a ambientes
        $sql = "SELECT * FROM inventario_bienes WHERE activo = 1";
        
        $params = [];

        // Filtramos directamente con la columna que ya existe en inventario_bienes
        if (!empty($filtros['id_ambiente'])) {
            $sql .= " AND id_inv_ambiente = :id_ambiente";
            $params[':id_ambiente'] = $filtros['id_ambiente'];
        }

        $sql .= " ORDER BY codigo_patrimonial ASC";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}