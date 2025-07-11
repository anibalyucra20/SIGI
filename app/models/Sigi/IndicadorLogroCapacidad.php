<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class IndicadorLogroCapacidad extends Model
{
    protected $table = 'sigi_ind_logro_capacidad';

    public function getPaginated($id_capacidad, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'codigo',
            2 => 'descripcion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'codigo';

        $sql = "SELECT * FROM sigi_ind_logro_capacidad
                WHERE id_capacidad = :id_capacidad
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_capacidad', $id_capacidad, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_ind_logro_capacidad WHERE id_capacidad = :id_capacidad";
        $stmtTotal = self::$db->prepare($sqlTotal);
        $stmtTotal->bindValue(':id_capacidad', $id_capacidad, PDO::PARAM_INT);
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_ind_logro_capacidad WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_ind_logro_capacidad SET codigo=:codigo, descripcion=:descripcion WHERE id=:id";
            $params = [
                ':codigo'      => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':id'          => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_ind_logro_capacidad (id_capacidad, codigo, descripcion) VALUES (:id_capacidad, :codigo, :descripcion)";
            $params = [
                ':id_capacidad' => $data['id_capacidad'],
                ':codigo'       => $data['codigo'],
                ':descripcion'  => $data['descripcion'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM sigi_ind_logro_capacidad WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function getPrimerIndLogroCapacidad($id_unidad_didactica)
    {
        $sql = "SELECT ilc.id
            FROM sigi_unidad_didactica ud
            INNER JOIN sigi_capacidades c ON c.id_unidad_didactica = ud.id
            INNER JOIN sigi_ind_logro_capacidad ilc ON ilc.id_capacidad = c.id
            WHERE ud.id = ?
            ORDER BY ilc.id ASC
            LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_unidad_didactica]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

    // Lista de indicadores de logro de capacidad para el select
    public function getIndicadoresLogroCapacidad($id_unidad_didactica)
    {
        $sql = "SELECT ilc.id, ilc.codigo, ilc.descripcion
            FROM sigi_capacidades cap
            INNER JOIN sigi_ind_logro_capacidad ilc ON ilc.id_capacidad = cap.id
            WHERE cap.id_unidad_didactica = ?
            ORDER BY ilc.id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_unidad_didactica]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
