<?php

namespace App\Models\Evaluacion;

use Core\Model;
use PDO;

class Pregunta extends Model
{
    public function listarPorEncuesta($idEncuesta)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT p.*, c.nombre AS criterio_nombre
                              FROM eva_preguntas p
                              LEFT JOIN eva_criterios c ON p.id_criterio = c.id
                              WHERE p.id_encuesta = ?
                              ORDER BY c.orden ASC, p.orden ASC, p.id ASC");
        $stmt->execute([(int)$idEncuesta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAgrupadasPorCriterio($idEncuesta)
    {
        $db = self::getDB();
        
        // 1. Obtener criterios
        $stmtCrit = $db->prepare("SELECT * FROM eva_criterios WHERE id_encuesta = ? ORDER BY orden ASC, id ASC");
        $stmtCrit->execute([(int)$idEncuesta]);
        $criterios = $stmtCrit->fetchAll(PDO::FETCH_ASSOC);

        // 2. Obtener preguntas
        $stmtPreg = $db->prepare("SELECT * FROM eva_preguntas WHERE id_encuesta = ? ORDER BY orden ASC, id ASC");
        $stmtPreg->execute([(int)$idEncuesta]);
        $preguntas = $stmtPreg->fetchAll(PDO::FETCH_ASSOC);

        $agrupadas = [];
        $preguntasPorCriterio = [];

        foreach ($preguntas as $p) {
            $idCrit = $p['id_criterio'] ?: 0;
            $preguntasPorCriterio[$idCrit][] = $p;
        }

        foreach ($criterios as $c) {
            $c['preguntas'] = $preguntasPorCriterio[$c['id']] ?? [];
            $agrupadas[] = $c;
        }

        // Preguntas sin criterio asignado si las hubiera
        if (!empty($preguntasPorCriterio[0])) {
            $agrupadas[] = [
                'id' => 0,
                'nombre' => 'Preguntas Generales',
                'descripcion' => '',
                'peso' => 1.00,
                'orden' => 999,
                'preguntas' => $preguntasPorCriterio[0]
            ];
        }

        return $agrupadas;
    }

    public function obtenerPorId($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM eva_preguntas WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        $db = self::getDB();
        if (!empty($data['id'])) {
            $stmt = $db->prepare("UPDATE eva_preguntas SET 
                                    id_criterio = ?, 
                                    enunciado = ?, 
                                    tipo_respuesta = ?, 
                                    es_obligatoria = ?, 
                                    orden = ? 
                                  WHERE id = ?");
            return $stmt->execute([
                !empty($data['id_criterio']) ? (int)$data['id_criterio'] : null,
                $data['enunciado'],
                $data['tipo_respuesta'] ?? 'ESCALA',
                isset($data['es_obligatoria']) ? (int)$data['es_obligatoria'] : 1,
                (int)($data['orden'] ?? 1),
                (int)$data['id']
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO eva_preguntas (id_encuesta, id_criterio, enunciado, tipo_respuesta, es_obligatoria, orden) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                (int)$data['id_encuesta'],
                !empty($data['id_criterio']) ? (int)$data['id_criterio'] : null,
                $data['enunciado'],
                $data['tipo_respuesta'] ?? 'ESCALA',
                isset($data['es_obligatoria']) ? (int)$data['es_obligatoria'] : 1,
                (int)($data['orden'] ?? 1)
            ]);
        }
    }

    public function eliminar($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM eva_preguntas WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
