<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Competencias extends Model
{
    protected $table = 'sigi_competencias';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'codigo',
            2 => 'tipo',
            3 => 'descripcion',
            4 => 'id_plan_estudio'
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

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT c.*, pl.nombre AS plan_nombre, pr.nombre AS programa_nombre
                FROM sigi_competencias c
                JOIN sigi_planes_estudio pl ON pl.id = c.id_plan_estudio
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

        $sqlTotal = "SELECT COUNT(*) FROM sigi_competencias c
                     JOIN sigi_planes_estudio pl ON pl.id = c.id_plan_estudio
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
        $stmt = self::$db->prepare("SELECT * FROM sigi_competencias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_competencias SET id_plan_estudio=:id_plan_estudio, tipo=:tipo, codigo=:codigo, descripcion=:descripcion WHERE id=:id";
            $params = [
                ':id_plan_estudio' => $data['id_plan_estudio'],
                ':tipo' => $data['tipo'],
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_competencias (id_plan_estudio, tipo, codigo, descripcion) VALUES (:id_plan_estudio, :tipo, :codigo, :descripcion)";
            $params = [
                ':id_plan_estudio' => $data['id_plan_estudio'],
                ':tipo' => $data['tipo'],
                ':codigo' => $data['codigo'],
                ':descripcion' => $data['descripcion'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    // Métodos para relación con módulos formativos
    public function getModulosByCompetencia($id_competencia)
    {
        $stmt = self::$db->prepare("SELECT mf.id, mf.descripcion 
            FROM sigi_competencia_moduloFormativo cm
            JOIN sigi_modulo_formativo mf ON mf.id = cm.id_modulo_formativo
            WHERE cm.id_competencia = ?");
        $stmt->execute([$id_competencia]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateModulosCompetencia($id_competencia, $modulos)
    {
        // $modulos es array de IDs de módulos formativos
        self::$db->beginTransaction();
        self::$db->prepare("DELETE FROM sigi_competencia_moduloFormativo WHERE id_competencia = ?")->execute([$id_competencia]);
        if (!empty($modulos)) {
            $stmt = self::$db->prepare("INSERT INTO sigi_competencia_moduloFormativo (id_competencia, id_modulo_formativo) VALUES (?, ?)");
            foreach ($modulos as $id_modulo) {
                $stmt->execute([$id_competencia, $id_modulo]);
            }
        }
        self::$db->commit();
    }
    public function getCompetenciasByModulo($id_modulo)
    {
        // Si tienes la tabla intermedia sigi_competencia_moduloFormativo:
        $stmt = self::$db->prepare(
            "SELECT c.id, c.codigo, c.descripcion 
             FROM sigi_competencias c
             JOIN sigi_competencia_moduloFormativo cmf ON cmf.id_competencia = c.id
             WHERE cmf.id_modulo_formativo = ?
             ORDER BY c.codigo"
        );
        $stmt->execute([$id_modulo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
