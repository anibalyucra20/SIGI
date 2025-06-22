<?php

namespace Core;

use PDO;

class Model
{
    protected static $db = null;

    public function __construct()
    {
        if (self::$db === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            // Crea tu conexión aquí solo si no existe
            self::$db = new \PDO($dsn, DB_USER, DB_PASS);
            self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$db;
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
}
