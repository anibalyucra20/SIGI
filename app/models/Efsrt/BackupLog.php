<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class BackupLog extends Model
{
    /**
     * Obtiene el historial de backups y purgas
     */
    public function getAll()
    {
        $sql = "SELECT b.*, 
                    u.apellidos_nombres AS ejecutado_por_nombre
                FROM efsrt_backup_logs b
                INNER JOIN sigi_usuarios u ON b.ejecutado_por = u.id
                ORDER BY b.id DESC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['ejecutado_por_nombre'] = \Core\Auth::formatearNombreUsuario($r['ejecutado_por_nombre'] ?? '');
        }
        return $rows;
    }

    /**
     * Obtiene detalle de un backup
     */
    public function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT b.*, u.apellidos_nombres AS ejecutado_por_nombre 
                                        FROM efsrt_backup_logs b 
                                        INNER JOIN sigi_usuarios u ON b.ejecutado_por = u.id 
                                        WHERE b.id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['ejecutado_por_nombre'] = \Core\Auth::formatearNombreUsuario($row['ejecutado_por_nombre'] ?? '');
        }
        return $row;
    }

    /**
     * Registra un log de backup ejecutado
     */
    public function registrar($data)
    {
        $db = self::getDB();
        $sql = "INSERT INTO efsrt_backup_logs (
                    fecha_proceso, ejecutado_por, total_archivos_procesados,
                    espacio_liberado_bytes, nombre_archivo_backup_zip, ruta_backup_zip,
                    criterio_antiguedad_anios, estado, detalles_json
                ) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            (int)$data['ejecutado_por'],
            (int)$data['total_archivos_procesados'],
            (int)$data['espacio_liberado_bytes'],
            $data['nombre_archivo_backup_zip'],
            $data['ruta_backup_zip'],
            !empty($data['criterio_antiguedad_anios']) ? (int)$data['criterio_antiguedad_anios'] : 5,
            !empty($data['estado']) ? $data['estado'] : 'EXITOSO',
            !empty($data['detalles_json']) ? $data['detalles_json'] : null
        ]);

        return $db->lastInsertId();
    }

    /**
     * Formatea bytes a KB, MB o GB
     */
    public static function formatearBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
