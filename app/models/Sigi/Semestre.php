<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Semestre extends Model
{
    protected $table = 'sigi_semestre';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'descripcion',
            2 => 'id_modulo_formativo'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $where = [];
        $params = [];

        if (!empty($filters['id_programa_estudios'])) {
            $where[] = "pr.id = :id_programa_estudios";
            $params[':id_programa_estudios'] = $filters['id_programa_estudios'];
        }
        if (!empty($filters['id_plan_estudio'])) {
            $where[] = "pl.id = :id_plan_estudio";
            $params[':id_plan_estudio'] = $filters['id_plan_estudio'];
        }
        if (!empty($filters['id_modulo_formativo'])) {
            $where[] = "m.id = :id_modulo_formativo";
            $params[':id_modulo_formativo'] = $filters['id_modulo_formativo'];
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT s.*, m.descripcion AS modulo_nombre, pl.nombre AS plan_nombre, pr.nombre AS programa_nombre
                FROM sigi_semestre s
                JOIN sigi_modulo_formativo m ON m.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio pl ON pl.id = m.id_plan_estudio
                JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_semestre s
                     JOIN sigi_modulo_formativo m ON m.id = s.id_modulo_formativo
                     JOIN sigi_planes_estudio pl ON pl.id = m.id_plan_estudio
                     JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
                     $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_semestre WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_semestre SET descripcion=:descripcion, id_modulo_formativo=:id_modulo_formativo WHERE id=:id";
            $params = [
                ':descripcion' => $data['descripcion'],
                ':id_modulo_formativo' => $data['id_modulo_formativo'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_semestre (descripcion, id_modulo_formativo) VALUES (:descripcion, :id_modulo_formativo)";
            $params = [
                ':descripcion' => $data['descripcion'],
                ':id_modulo_formativo' => $data['id_modulo_formativo'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }
    public function getSemestresByModulo($id_modulo)
    {
        $stmt = self::$db->prepare("SELECT id, descripcion FROM sigi_semestre WHERE id_modulo_formativo = ? ORDER BY id");
        $stmt->execute([$id_modulo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getSemestresPorPlan($id_plan_estudio)
    {
        // Cada semestre está vinculado a un módulo del plan
        $sql = "SELECT s.id, s.descripcion
            FROM sigi_semestre s
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            WHERE mf.id_plan_estudio = ?
            ORDER BY s.id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_plan_estudio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
