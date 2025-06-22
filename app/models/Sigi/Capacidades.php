<?php
namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Capacidades extends Model
{
    protected $table = 'sigi_capacidades';

    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'cap.id',
            1 => 'pr.nombre',
            2 => 'pl.nombre',
            3 => 'mf.descripcion',
            4 => 's.descripcion',
            5 => 'ud.nombre',
            6 => 'c.codigo',
            7 => 'cap.codigo',
            8 => 'cap.descripcion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'cap.codigo';

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
            $where[] = "mf.id = :id_modulo_formativo";
            $params[':id_modulo_formativo'] = $filters['id_modulo_formativo'];
        }
        if (!empty($filters['id_semestre'])) {
            $where[] = "s.id = :id_semestre";
            $params[':id_semestre'] = $filters['id_semestre'];
        }
        if (!empty($filters['id_competencia'])) {
            $where[] = "c.id = :id_competencia";
            $params[':id_competencia'] = $filters['id_competencia'];
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT cap.*, 
                    ud.nombre AS unidad_nombre, 
                    s.descripcion AS semestre_nombre,
                    mf.descripcion AS modulo_nombre,
                    pl.nombre AS plan_nombre,
                    pr.nombre AS programa_nombre,
                    c.codigo AS competencia_codigo,
                    c.descripcion AS competencia_descripcion
                FROM sigi_capacidades cap
                JOIN sigi_unidad_didactica ud ON ud.id = cap.id_unidad_didactica
                JOIN sigi_semestre s ON s.id = ud.id_semestre
                JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio pl ON pl.id = mf.id_plan_estudio
                JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
                JOIN sigi_competencias c ON c.id = cap.id_competencia
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

        // Conteo total
        $sqlTotal = "SELECT COUNT(*) FROM sigi_capacidades cap
            JOIN sigi_unidad_didactica ud ON ud.id = cap.id_unidad_didactica
            JOIN sigi_semestre s ON s.id = ud.id_semestre
            JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
            JOIN sigi_planes_estudio pl ON pl.id = mf.id_plan_estudio
            JOIN sigi_programa_estudios pr ON pr.id = pl.id_programa_estudios
            JOIN sigi_competencias c ON c.id = cap.id_competencia
            $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    // FILTROS DEPENDIENTES
    
    public function getModulosByPlan($id_plan)
    {
        $stmt = self::$db->prepare("SELECT id, descripcion FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo");
        $stmt->execute([$id_plan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    

    // CRUD
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_capacidades WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_capacidades SET 
                        id_unidad_didactica = :id_unidad_didactica, 
                        id_competencia = :id_competencia,
                        codigo = :codigo, 
                        descripcion = :descripcion
                    WHERE id = :id";
            $params = [
                ':id_unidad_didactica' => $data['id_unidad_didactica'],
                ':id_competencia'      => $data['id_competencia'],
                ':codigo'              => $data['codigo'],
                ':descripcion'         => $data['descripcion'],
                ':id'                  => $data['id']
            ];
        } else {
            $sql = "INSERT INTO sigi_capacidades 
                        (id_unidad_didactica, id_competencia, codigo, descripcion) 
                    VALUES 
                        (:id_unidad_didactica, :id_competencia, :codigo, :descripcion)";
            $params = [
                ':id_unidad_didactica' => $data['id_unidad_didactica'],
                ':id_competencia'      => $data['id_competencia'],
                ':codigo'              => $data['codigo'],
                ':descripcion'         => $data['descripcion']
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        // Si es registro nuevo, devuelve el id insertado
        if (empty($data['id'])) {
            return self::$db->lastInsertId();
        }
        return $data['id'];
    }
}
