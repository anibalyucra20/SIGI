<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class HistorialDerivacion extends Model
{
    /**
     * Obtiene todo el historial de derivaciones de una práctica
     */
    public function getByPractica($id_practica)
    {
        $sql = "SELECT h.*, 
                    u.apellidos_nombres AS usuario_nombre,
                    u.dni AS usuario_dni
                FROM efsrt_historial_derivaciones h
                INNER JOIN sigi_usuarios u ON h.id_usuario_accion = u.id
                WHERE h.id_practica = ?
                ORDER BY h.id DESC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([(int)$id_practica]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['usuario_nombre'] = \Core\Auth::formatearNombreUsuario($r['usuario_nombre'] ?? '');
        }
        return $rows;
    }

    /**
     * Registra un paso en el flujo de derivación / visto bueno / observación
     */
    public function registrar($data)
    {
        $db = self::getDB();
        $sql = "INSERT INTO efsrt_historial_derivaciones (
                    id_practica, etapa_origen, etapa_destino, accion,
                    id_usuario_accion, comentarios, fecha_hora
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            (int)$data['id_practica'],
            $data['etapa_origen'],
            $data['etapa_destino'],
            $data['accion'],
            (int)$data['id_usuario_accion'],
            !empty($data['comentarios']) ? trim($data['comentarios']) : null
        ]);
    }
}
