<?php

namespace App\Models\Biblioteca;

use Core\Model;
use PDO;

class Lecturas extends Model
{
    /**
     * Cuenta el total de registros aplicando filtros de forma segura
     */
    public function contarLogs(?array $filtros = null): int
    {
        $condiciones = ["1=1"];
        $parametros = [];

        if ($filtros) {
            if (!empty($filtros['id_usuario'])) {
                $condiciones[] = "bl.id_usuario = ?";
                $parametros[] = $filtros['id_usuario'];
            }
            // Solo aplicamos el filtro si el usuario interactuó cambiando las fechas
            if (!empty($filtros['fecha_ini'])) {
                $condiciones[] = "DATE(bl.fecha_registro) >= ?";
                $parametros[] = $filtros['fecha_ini'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $condiciones[] = "DATE(bl.fecha_registro) <= ?";
                $parametros[] = $filtros['fecha_fin'];
            }
        }

        $where = implode(' AND ', $condiciones);
        $sql = "SELECT COUNT(*) 
                FROM biblioteca_lecturas bl
                INNER JOIN sigi_usuarios u ON bl.id_usuario = u.id
                WHERE $where";

        try {
            $st = self::$db->prepare($sql);
            $st->execute($parametros);
            return (int)$st->fetchColumn();
        } catch (\PDOException $e) {
            error_log("[LecturasModel@contarLogs] " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lista los registros cruzando la fecha en formato DATE contra el TIMESTAMP
     */
    public function listarLogs(?array $filtros, int $start, int $length): array
    {
        $condiciones = ["1=1"];
        $parametros = [];

        if ($filtros) {
            if (!empty($filtros['id_usuario'])) {
                $condiciones[] = "bl.id_usuario = ?";
                $parametros[] = $filtros['id_usuario'];
            }
            if (!empty($filtros['fecha_ini'])) {
                $condiciones[] = "DATE(bl.fecha_registro) >= ?";
                $parametros[] = $filtros['fecha_ini'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $condiciones[] = "DATE(bl.fecha_registro) <= ?";
                $parametros[] = $filtros['fecha_fin'];
            }
        }

        $where = implode(' AND ', $condiciones);

        $sql = "SELECT 
                    bl.fecha_registro AS fecha, 
                    u.rol AS rol, 
                    u.apellidos_nombres AS usuario, 
                    bl.id_libro AS id_registro
                FROM biblioteca_lecturas bl
                INNER JOIN sigi_usuarios u ON bl.id_usuario = u.id
                WHERE $where 
                ORDER BY bl.fecha_registro DESC 
                LIMIT " . (int)$length . " OFFSET " . (int)$start;

        try {
            $st = self::$db->prepare($sql);
            $st->execute($parametros);
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("[LecturasModel@listarLogs] " . $e->getMessage());
            return [];
        }
    }

    public function getUsuarios(): array
    {
        try {
            $stmt = self::$db->query("SELECT DISTINCT u.id, u.apellidos_nombres FROM biblioteca_lecturas l LEFT JOIN sigi_usuarios u ON u.id = l.id_usuario ORDER BY u.apellidos_nombres");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $key => $value) {
                if (!empty($value['apellidos_nombres'])) {
                    $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
                    $data[$key]['apellidos_nombres'] = implode(' ', array_filter([
                        $apellidos_nombres[0] ?? '',
                        $apellidos_nombres[1] ?? '',
                        $apellidos_nombres[2] ?? ''
                    ]));
                }
            }
            return $data;
        } catch (\PDOException $e) {
            error_log("[LecturasModel@getUsuarios] " . $e->getMessage());
            return [];
        }
    }
}