<?php

namespace App\Models\Evaluacion;

use Core\Model;
use PDO;

class Banco extends Model
{
    /**
     * Obtiene todos los criterios del banco con el conteo de preguntas
     */
    public function obtenerCriterios($soloActivos = false)
    {
        $db = self::getDB();
        $sql = "SELECT c.*, COUNT(p.id) AS total_preguntas
                FROM eva_banco_criterios c
                LEFT JOIN eva_banco_preguntas p ON p.id_banco_criterio = c.id " . ($soloActivos ? "AND p.estado = 1" : "") . "
                WHERE 1=1";
        if ($soloActivos) {
            $sql .= " AND c.estado = 1";
        }
        $sql .= " GROUP BY c.id ORDER BY c.orden ASC, c.id ASC";

        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un criterio por ID
     */
    public function obtenerCriterio($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM eva_banco_criterios WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene toda la estructura del banco (criterios con sus preguntas)
     */
    public function obtenerTodosConPreguntas($soloActivos = false)
    {
        $criterios = $this->obtenerCriterios($soloActivos);
        $db = self::getDB();

        foreach ($criterios as &$crit) {
            $sql = "SELECT * FROM eva_banco_preguntas WHERE id_banco_criterio = ?";
            if ($soloActivos) {
                $sql .= " AND estado = 1";
            }
            $sql .= " ORDER BY orden ASC, id ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute([(int)$crit['id']]);
            $crit['preguntas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $criterios;
    }

    /**
     * Guarda un nuevo criterio en el banco
     */
    public function guardarCriterio($data)
    {
        $db = self::getDB();
        $stmt = $db->prepare("
            INSERT INTO eva_banco_criterios (nombre, descripcion, peso, orden, estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['nombre']),
            trim($data['descripcion'] ?? ''),
            !empty($data['peso']) ? (float)$data['peso'] : 1.00,
            !empty($data['orden']) ? (int)$data['orden'] : 1,
            isset($data['estado']) ? (int)$data['estado'] : 1
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Actualiza un criterio del banco
     */
    public function actualizarCriterio($id, $data)
    {
        $db = self::getDB();
        $stmt = $db->prepare("
            UPDATE eva_banco_criterios 
            SET nombre = ?, descripcion = ?, peso = ?, orden = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($data['nombre']),
            trim($data['descripcion'] ?? ''),
            !empty($data['peso']) ? (float)$data['peso'] : 1.00,
            !empty($data['orden']) ? (int)$data['orden'] : 1,
            isset($data['estado']) ? (int)$data['estado'] : 1,
            (int)$id
        ]);
    }

    /**
     * Elimina un criterio del banco (y sus preguntas por FK ON DELETE CASCADE)
     */
    public function eliminarCriterio($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM eva_banco_criterios WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Obtiene una pregunta por ID
     */
    public function obtenerPregunta($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM eva_banco_preguntas WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda una pregunta en el banco
     */
    public function guardarPregunta($data)
    {
        $db = self::getDB();
        $stmt = $db->prepare("
            INSERT INTO eva_banco_preguntas (id_banco_criterio, enunciado, tipo_respuesta, es_obligatoria, orden, estado)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['id_banco_criterio'],
            trim($data['enunciado']),
            $data['tipo_respuesta'] ?? 'ESCALA',
            isset($data['es_obligatoria']) ? (int)$data['es_obligatoria'] : 1,
            !empty($data['orden']) ? (int)$data['orden'] : 1,
            isset($data['estado']) ? (int)$data['estado'] : 1
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Actualiza una pregunta del banco
     */
    public function actualizarPregunta($id, $data)
    {
        $db = self::getDB();
        $stmt = $db->prepare("
            UPDATE eva_banco_preguntas
            SET id_banco_criterio = ?, enunciado = ?, tipo_respuesta = ?, es_obligatoria = ?, orden = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$data['id_banco_criterio'],
            trim($data['enunciado']),
            $data['tipo_respuesta'] ?? 'ESCALA',
            isset($data['es_obligatoria']) ? (int)$data['es_obligatoria'] : 1,
            !empty($data['orden']) ? (int)$data['orden'] : 1,
            isset($data['estado']) ? (int)$data['estado'] : 1,
            (int)$id
        ]);
    }

    /**
     * Elimina una pregunta del banco
     */
    public function eliminarPregunta($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM eva_banco_preguntas WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Clona/Importa todo el banco activo en una encuesta específica
     */
    public function copiarBancoAEncuesta($idEncuesta)
    {
        $db = self::getDB();
        $banco = $this->obtenerTodosConPreguntas(true);

        if (empty($banco)) {
            return 0;
        }

        $stmtCrit = $db->prepare("INSERT INTO eva_criterios (id_encuesta, nombre, descripcion, peso, orden) VALUES (?, ?, ?, ?, ?)");
        $stmtPreg = $db->prepare("INSERT INTO eva_preguntas (id_encuesta, id_criterio, enunciado, tipo_respuesta, es_obligatoria, orden) VALUES (?, ?, ?, ?, ?, ?)");

        $totalPreguntas = 0;
        foreach ($banco as $crit) {
            $stmtCrit->execute([
                (int)$idEncuesta,
                $crit['nombre'],
                $crit['descripcion'],
                $crit['peso'],
                $crit['orden']
            ]);
            $idNuevoCrit = (int)$db->lastInsertId();

            if (!empty($crit['preguntas'])) {
                foreach ($crit['preguntas'] as $preg) {
                    $stmtPreg->execute([
                        (int)$idEncuesta,
                        $idNuevoCrit,
                        $preg['enunciado'],
                        $preg['tipo_respuesta'] ?? 'ESCALA',
                        $preg['es_obligatoria'] ?? 1,
                        $preg['orden'] ?? 1
                    ]);
                    $totalPreguntas++;
                }
            }
        }

        return $totalPreguntas;
    }
}
