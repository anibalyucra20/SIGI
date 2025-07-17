<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Plan extends Model
{
    protected $table = 'sigi_planes_estudio';

    // Para DataTables AJAX paginación
    public function getPaginated($length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_programa_estudios',
            2 => 'nombre',
            3 => 'resolucion',
            4 => 'fecha_registro',
            5 => 'perfil_egresado',
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'nombre';

        $sql = "SELECT p.*, pr.nombre AS programa_nombre
                FROM sigi_planes_estudio p
                JOIN sigi_programa_estudios pr ON pr.id = p.id_programa_estudios
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = self::$db->query("SELECT COUNT(*) FROM sigi_planes_estudio")->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }


    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_planes_estudio WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_planes_estudio 
                    SET id_programa_estudios=:id_programa_estudios, nombre=:nombre, resolucion=:resolucion, perfil_egresado=:perfil_egresado 
                    WHERE id=:id";
            $params = [
                ':id_programa_estudios' => $data['id_programa_estudios'],
                ':nombre' => $data['nombre'],
                ':resolucion' => $data['resolucion'],
                ':perfil_egresado' => $data['perfil_egresado'],
                ':id' => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_planes_estudio (id_programa_estudios, nombre, resolucion, perfil_egresado) 
                    VALUES (:id_programa_estudios, :nombre, :resolucion, :perfil_egresado)";
            $params = [
                ':id_programa_estudios' => $data['id_programa_estudios'],
                ':nombre' => $data['nombre'],
                ':resolucion' => $data['resolucion'],
                ':perfil_egresado' => $data['perfil_egresado'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }
    public function getPlanes()
    {
        $stmt = self::$db->prepare("SELECT DISTINCT nombre FROM sigi_planes_estudio ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getPlanesByPrograma($id_programa)
    {
        $stmt = self::$db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre");
        $stmt->execute([$id_programa]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getPlanByProgramaAndPlanName($id_programa, $nombre)
    {
        $stmt = self::$db->prepare("SELECT id FROM sigi_planes_estudio WHERE id_programa_estudios = ? AND nombre = ? ORDER BY nombre");
        $stmt->execute([$id_programa, $nombre]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
