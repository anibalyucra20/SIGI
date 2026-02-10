<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Programa extends Model
{
    protected $table = 'sigi_programa_estudios';

    public function getAll()
    {
        $sql = "SELECT * FROM sigi_programa_estudios ORDER BY nombre ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Traer todos los programas para asignar (SELECT)
    public function getTodosProgramas()
    {
        $sql = "SELECT id, nombre FROM sigi_programa_estudios ORDER BY nombre";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    // Traer programas asociados a una sede (retorna array de IDs)
    public function getProgramasPorSede($id_sede)
    {
        $sql = "SELECT id_programa_estudio FROM sigi_programa_sede WHERE id_sede = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_sede]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_programa_estudio');
    }
    public function getProgramasPorSedes($id_sede)
    {
        $sql = "SELECT pe.id, pe.nombre FROM sigi_programa_estudios pe 
        INNER JOIN sigi_programa_sede ps ON ps.id_programa_estudio = pe.id
        WHERE ps.id_sede = ?
        ORDER BY pe.nombre";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_sede]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_programa_estudios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getProgramaPorNombre($nombre)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_programa_estudios WHERE nombre = ?");
        $stmt->execute([$nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getAllBySede($id_sede)
    {
        $sql = "SELECT p.* 
            FROM sigi_programa_estudios p
            INNER JOIN sigi_programa_sede ps ON ps.id_programa_estudio = p.id
            WHERE ps.id_sede = ?
            ORDER BY p.nombre ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_sede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_programa_estudios SET codigo=:codigo, tipo=:tipo, nombre=:nombre WHERE id=:id";
            $params = [
                ':codigo' => $data['codigo'],
                ':tipo' => $data['tipo'],
                ':nombre' => $data['nombre'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_programa_estudios (codigo, tipo, nombre)
                    VALUES (:codigo, :tipo, :nombre)";
            $params = [
                ':codigo' => $data['codigo'],
                ':tipo' => $data['tipo'],
                ':nombre' => $data['nombre'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    // Para DataTables AJAX
    public function getPaginated($length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'codigo',
            2 => 'tipo',
            3 => 'nombre',
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'nombre';

        $sql = "SELECT * FROM sigi_programa_estudios ORDER BY $ordenarPor $orderDir LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':limit', (int)$length, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = self::$db->query("SELECT COUNT(*) FROM sigi_programa_estudios")->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }
}
