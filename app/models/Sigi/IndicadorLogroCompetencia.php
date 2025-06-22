<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class IndicadorLogroCompetencia extends Model
{
    protected $table = 'sigi_ind_logro_competencia';

    public function getPaginated($id_competencia, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'correlativo',
            2 => 'descripcion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'correlativo';

        $sql = "SELECT * FROM sigi_ind_logro_competencia
                WHERE id_competencia = :id_competencia
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_competencia', $id_competencia, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_ind_logro_competencia WHERE id_competencia = :id_competencia";
        $stmtTotal = self::$db->prepare($sqlTotal);
        $stmtTotal->bindValue(':id_competencia', $id_competencia, PDO::PARAM_INT);
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_ind_logro_competencia WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_ind_logro_competencia SET correlativo=:correlativo, descripcion=:descripcion WHERE id=:id";
            $params = [
                ':correlativo' => $data['correlativo'],
                ':descripcion' => $data['descripcion'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_ind_logro_competencia (id_competencia, correlativo, descripcion) VALUES (:id_competencia, :correlativo, :descripcion)";
            $params = [
                ':id_competencia' => $data['id_competencia'],
                ':correlativo'    => $data['correlativo'],
                ':descripcion'    => $data['descripcion'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }
    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM sigi_ind_logro_competencia WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
