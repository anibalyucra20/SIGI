<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class PeriodoAcademico extends Model
{
    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_periodo_academico';

    public function buscar()
    {
        $sql = "SELECT * FROM sigi_periodo_academico";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Listado de periodos académicos
    public function getPeriodos()
    {
        // id + etiqueta legible
        $sql = "SELECT id, nombre AS descripcion
        FROM sigi_periodo_academico
        ORDER BY fecha_inicio DESC
        ";

        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPaginated($length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'nombre',
            2 => 'fecha_inicio',
            3 => 'fecha_fin',
            4 => 'director',
            5 => 'fecha_actas'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $sql = "SELECT pa.*, u.apellidos_nombres AS director_nombre
                FROM sigi_periodo_academico pa
                LEFT JOIN sigi_usuarios u ON u.id = pa.director
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_periodo_academico";
        $total = self::$db->query($sqlTotal)->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_periodo_academico WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE sigi_periodo_academico SET 
                        nombre=:nombre, fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, 
                        director=:director, fecha_actas=:fecha_actas
                    WHERE id=:id";
            $params = [
                ':nombre'       => $data['nombre'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin'    => $data['fecha_fin'],
                ':director'     => $data['director'],
                ':fecha_actas'  => $data['fecha_actas'],
                ':id'           => $data['id'],
            ];
        } else {
            $sql = "INSERT INTO sigi_periodo_academico (nombre, fecha_inicio, fecha_fin, director, fecha_actas)
                    VALUES (:nombre, :fecha_inicio, :fecha_fin, :director, :fecha_actas)";
            $params = [
                ':nombre'       => $data['nombre'],
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin'    => $data['fecha_fin'],
                ':director'     => $data['director'],
                ':fecha_actas'  => $data['fecha_actas'],
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return !empty($data['id']) ? $data['id'] : self::$db->lastInsertId();
    }

    
}
