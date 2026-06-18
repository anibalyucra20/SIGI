<?php

namespace App\Models\Biblioteca;

use Core\Model;
use PDO;

class Lecturas extends Model
{
    /**
     * Cuenta el total de lecturas aplicando los filtros de la interfaz
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
     * Obtiene los registros paginados mapeando de forma nativa la columna id_libro
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

        // Eliminamos el alias conflictivo id_registro y seleccionamos id_libro de forma directa
        $sql = "SELECT 
                    bl.fecha_registro AS fecha, 
                    u.id_rol AS rol, 
                    u.apellidos_nombres AS usuario, 
                    bl.id_libro AS id_libro
                FROM biblioteca_lecturas bl
                INNER JOIN sigi_usuarios u ON bl.id_usuario = u.id
                WHERE $where 
                ORDER BY bl.fecha_registro DESC 
                LIMIT " . (int)$length . " OFFSET " . (int)$start;

        try {
            $st = self::$db->prepare($sql);
            $st->execute($parametros);
            $data = $st->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $key => $value) {
                $apellidos_nombres = explode('_', trim($value['usuario']));
                $data[$key]['usuario'] = implode(' ', array_filter([
                    $apellidos_nombres[0] ?? '',
                    $apellidos_nombres[1] ?? '',
                    $apellidos_nombres[2] ?? ''
                ]));
            }
            return $data;
        } catch (\PDOException $e) {
            error_log("[LecturasModel@listarLogs] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Carga y limpia los apellidos_nombres de la tabla sigi_usuarios
     */
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