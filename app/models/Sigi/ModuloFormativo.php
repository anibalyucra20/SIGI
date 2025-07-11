<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class ModuloFormativo extends Model
{
    protected $table = 'sigi_modulo_formativo';

    // Para filtros en el DataTable
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'descripcion',
            2 => 'nro_modulo',
            3 => 'id_plan_estudio'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'nro_modulo';

        $where = [];
        $params = [];

        if (!empty($filters['id_programa_estudios'])) {
            $where[] = "pl.id_programa_estudios = :id_programa_estudios";
            $params[':id_programa_estudios'] = $filters['id_programa_estudios'];
        }
        if (!empty($filters['id_plan_estudio'])) {
            $where[] = "m.id_plan_estudio = :id_plan_estudio";
            $params[':id_plan_estudio'] = $filters['id_plan_estudio'];
        }
        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT m.*, pl.nombre AS plan_nombre, pr.nombre AS programa_nombre
                FROM sigi_modulo_formativo m
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

        $sqlTotal = "SELECT COUNT(*) FROM sigi_modulo_formativo m
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
        $stmt = self::$db->prepare("SELECT * FROM sigi_modulo_formativo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_modulo_formativo SET descripcion=:descripcion, nro_modulo=:nro_modulo, id_plan_estudio=:id_plan_estudio WHERE id=:id";
            $params = [
                ':descripcion' => $data['descripcion'],
                ':nro_modulo' => $data['nro_modulo'],
                ':id_plan_estudio' => $data['id_plan_estudio'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_modulo_formativo (descripcion, nro_modulo, id_plan_estudio)
                    VALUES (:descripcion, :nro_modulo, :id_plan_estudio)";
            $params = [
                ':descripcion' => $data['descripcion'],
                ':nro_modulo' => $data['nro_modulo'],
                ':id_plan_estudio' => $data['id_plan_estudio'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }
    public function getModuloByPlan($id_plan)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_modulo_formativo WHERE id_plan_estudio = ?");
        $stmt->execute([$id_plan]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getModuloByUnidadDidactica($id_unidad_didactica)
    {
        $sql = "SELECT s.id_modulo_formativo
            FROM sigi_unidad_didactica ud
            INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
            WHERE ud.id = ?
            LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_unidad_didactica]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id_modulo_formativo'] : null;
    }
}
