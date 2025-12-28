<?php

namespace Core;

use PDO;

class Model
{
    protected static $db = null;

    public function __construct()
    {
        // Solo si el model se instancia tras un dispatch MVC:
        if (defined('MVC_DISPATCHED') && ! Auth::user()) {
            throw new \Exception('Acceso no autorizado');
        }
        if (self::$db === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            // Crea tu conexión aquí solo si no existe
            self::$db = new \PDO($dsn, DB_USER, DB_PASS);
            self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$db;
    }
    /**
     * Mágico: intercepta llamadas a métodos de instancia y vuelve a chequear autenticación.
     */
    public function __call($name, $arguments)
    {
        if (! Auth::user()) {
            throw new \Exception('Acceso no autorizado. Debes iniciar sesión.');
        }
        return call_user_func_array([$this, $name], $arguments);
    }

    public static function getDB(): PDO
    {        // ← getter público
        return self::$db;
    }
    public static function log($id_usuario, $accion, $descripcion = '', $tabla = null, $id_registro = null)
    {
        $db = self::getDB();
        $sql = "INSERT INTO sigi_logs (id_usuario, accion, descripcion, tabla_afectada, id_registro, ip_usuario)
            VALUES (:id_usuario, :accion, :descripcion, :tabla_afectada, :id_registro, :ip_usuario)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':accion' => $accion,
            ':descripcion' => $descripcion,
            ':tabla_afectada' => $tabla,
            ':id_registro' => $id_registro,
            ':ip_usuario' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
    protected function registrarAuditoria($tabla, $id_registro, $campo, $anterior, $nuevo)
    {
        // Ignorar si no hubo cambios reales (optimización)
        if ($anterior === $nuevo) return;

        $sql = "INSERT INTO auditoria_acad_logs 
            (id_usuario, tabla_afectada, id_registro, campo_afectado, valor_anterior, valor_nuevo, ip_origen, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $_SESSION['sigi_user_id'] ?? 0,
            $tabla,
            $id_registro,
            $campo,
            (string)$anterior,
            (string)$nuevo,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'System'
        ]);
    }
}
