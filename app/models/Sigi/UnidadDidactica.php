<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class UnidadDidactica extends Model
{
    protected $table = 'sigi_unidad_didactica';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'nombre',
            2 => 'id_semestre',
            3 => 'creditos_teorico',
            4 => 'creditos_practico',
            5 => 'tipo',
            6 => 'orden'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'nombre';

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
        if (!empty($filters['id_semestre'])) {
            $where[] = "s.id = :id_semestre";
            $params[':id_semestre'] = $filters['id_semestre'];
        }
        if (!empty($filters['nombre'])) {
            $where[] = "ud.nombre LIKE :nombre";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT ud.*, s.descripcion AS semestre_nombre, m.descripcion AS modulo_nombre, 
                       pl.nombre AS plan_nombre, pr.nombre AS programa_nombre
                FROM sigi_unidad_didactica ud
                JOIN sigi_semestre s ON s.id = ud.id_semestre
                JOIN sigi_modulo_formativo m ON m.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio pl ON pl.id = m.id_plan_estudio
                JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_unidad_didactica ud
                JOIN sigi_semestre s ON s.id = ud.id_semestre
                JOIN sigi_modulo_formativo m ON m.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio pl ON pl.id = m.id_plan_estudio
                JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_unidad_didactica WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_unidad_didactica SET nombre=:nombre, id_semestre=:id_semestre, creditos_teorico=:creditos_teorico, creditos_practico=:creditos_practico, tipo=:tipo, orden=:orden WHERE id=:id";
            $params = [
                ':nombre' => $data['nombre'],
                ':id_semestre' => $data['id_semestre'],
                ':creditos_teorico' => $data['creditos_teorico'],
                ':creditos_practico' => $data['creditos_practico'],
                ':tipo' => $data['tipo'],
                ':orden' => $data['orden'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_unidad_didactica (nombre, id_semestre, creditos_teorico, creditos_practico, tipo, orden) VALUES (:nombre, :id_semestre, :creditos_teorico, :creditos_practico, :tipo, :orden)";
            $params = [
                ':nombre' => $data['nombre'],
                ':id_semestre' => $data['id_semestre'],
                ':creditos_teorico' => $data['creditos_teorico'],
                ':creditos_practico' => $data['creditos_practico'],
                ':tipo' => $data['tipo'],
                ':orden' => $data['orden'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }
    public function getUnidadesBySemestre($id_semestre)
    {
        $stmt = self::$db->prepare("SELECT id, nombre FROM sigi_unidad_didactica WHERE id_semestre = ? ORDER BY id");
        $stmt->execute([$id_semestre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
