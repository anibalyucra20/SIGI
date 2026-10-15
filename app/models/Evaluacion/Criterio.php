<?php

namespace App\Models\Evaluacion;

use Core\Model;
use PDO;

class Criterio extends Model
{
    public function listarPorEncuesta($idEncuesta)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT c.*, COUNT(p.id) AS total_preguntas
                              FROM eva_criterios c
                              LEFT JOIN eva_preguntas p ON p.id_criterio = c.id
                              WHERE c.id_encuesta = ?
                              GROUP BY c.id
                              ORDER BY c.orden ASC, c.id ASC");
        $stmt->execute([(int)$idEncuesta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM eva_criterios WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        $db = self::getDB();
        if (!empty($data['id'])) {
            $stmt = $db->prepare("UPDATE eva_criterios SET nombre = ?, descripcion = ?, peso = ?, orden = ? WHERE id = ?");
            return $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['peso'] ?? 1.00,
                (int)($data['orden'] ?? 1),
                (int)$data['id']
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO eva_criterios (id_encuesta, nombre, descripcion, peso, orden) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([
                (int)$data['id_encuesta'],
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['peso'] ?? 1.00,
                (int)($data['orden'] ?? 1)
            ]);
        }
    }

    public function eliminar($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM eva_criterios WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
