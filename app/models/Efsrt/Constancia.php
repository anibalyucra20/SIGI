<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Constancia extends Model
{
    /**
     * Construye el nombre estandarizado de archivo PDF exigido: [DNI]-[ABREV_CARRERA]-[NRO_MODULO].pdf
     */
    public static function construirNombreArchivo($dni, $abrev_carrera, $nro_modulo)
    {
        $dniClean = preg_replace('/[^0-9]/', '', $dni);
        $carreraClean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $abrev_carrera));
        $modClean = preg_replace('/[^0-9]/', '', $nro_modulo);

        return "{$dniClean}-{$carreraClean}-M{$modClean}.pdf";
    }

    /**
     * Genera un número correlativo oficial de constancia (ej. CST-2026-0001)
     */
    public function generarNumeroConstancia($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }

        $stmt = self::getDB()->prepare("SELECT COUNT(*) AS total FROM efsrt_constancias WHERE YEAR(fecha_emision) = ?");
        $stmt->execute([(int)$anio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $correlativo = ($row ? (int)$row['total'] : 0) + 1;

        return sprintf("CST-%s-%04d", $anio, $correlativo);
    }

    /**
     * Obtiene una constancia por ID de práctica
     */
    public function getByPractica($id_practica)
    {
        $stmt = self::getDB()->prepare("SELECT c.*, 
                                            u.apellidos_nombres AS estudiante_nombre,
                                            u.dni AS estudiante_dni,
                                            sec.apellidos_nombres AS emitido_por_nombre
                                        FROM efsrt_constancias c
                                        INNER JOIN sigi_usuarios u ON c.id_estudiante = u.id
                                        INNER JOIN sigi_usuarios sec ON c.emitido_por = sec.id
                                        WHERE c.id_practica = ? AND c.estado = 'EMITIDA'
                                        LIMIT 1");
        $stmt->execute([(int)$id_practica]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($row['estudiante_nombre'] ?? '');
            if (!empty($row['emitido_por_nombre'])) {
                $row['emitido_por_nombre'] = \Core\Auth::formatearNombreUsuario($row['emitido_por_nombre']);
            }
        }
        return $row;
    }

    /**
     * Obtiene por token de validación QR
     */
    public function getByToken($qr_token)
    {
        $sql = "SELECT c.*, 
                    est.apellidos_nombres AS estudiante_nombre,
                    est.dni AS estudiante_dni,
                    p.area_desempeno,
                    p.fecha_inicio,
                    p.fecha_fin,
                    p.horas_convalidadas,
                    p.calificacion_final,
                    prog.nombre AS programa_nombre,
                    prog.codigo AS programa_codigo,
                    modf.descripcion AS modulo_nombre,
                    modf.nro_modulo,
                    emp.razon_social AS empresa_nombre,
                    sec.apellidos_nombres AS emitido_por_nombre
                FROM efsrt_constancias c
                INNER JOIN efsrt_practicas p ON c.id_practica = p.id
                INNER JOIN sigi_usuarios est ON c.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prog ON p.id_programa_estudios = prog.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN efsrt_empresas emp ON p.id_empresa = emp.id
                INNER JOIN sigi_usuarios sec ON c.emitido_por = sec.id
                WHERE c.qr_token = ? AND c.estado = 'EMITIDA'
                LIMIT 1";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([trim($qr_token)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($row['estudiante_nombre'] ?? '');
            if (!empty($row['emitido_por_nombre'])) {
                $row['emitido_por_nombre'] = \Core\Auth::formatearNombreUsuario($row['emitido_por_nombre']);
            }
        }
        return $row;
    }

    /**
     * Obtiene constancia por ID
     */
    public function getById($id)
    {
        $sql = "SELECT c.*, 
                    est.apellidos_nombres AS estudiante_nombre,
                    est.dni AS estudiante_dni,
                    p.area_desempeno,
                    p.fecha_inicio,
                    p.fecha_fin,
                    p.horas_convalidadas,
                    p.calificacion_final,
                    prog.nombre AS programa_nombre,
                    prog.codigo AS programa_codigo,
                    modf.descripcion AS modulo_nombre,
                    modf.nro_modulo,
                    emp.razon_social AS empresa_nombre,
                    sec.apellidos_nombres AS emitido_por_nombre
                FROM efsrt_constancias c
                INNER JOIN efsrt_practicas p ON c.id_practica = p.id
                INNER JOIN sigi_usuarios est ON c.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prog ON p.id_programa_estudios = prog.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN efsrt_empresas emp ON p.id_empresa = emp.id
                INNER JOIN sigi_usuarios sec ON c.emitido_por = sec.id
                WHERE c.id = ?
                LIMIT 1";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($row['estudiante_nombre'] ?? '');
            if (!empty($row['emitido_por_nombre'])) {
                $row['emitido_por_nombre'] = \Core\Auth::formatearNombreUsuario($row['emitido_por_nombre']);
            }
        }
        return $row;
    }

    /**
     * Registra una nueva constancia oficial
     */
    public function registrar($data)
    {
        $db = self::getDB();
        $sql = "INSERT INTO efsrt_constancias (
                    id_practica, id_estudiante, nro_constancia, nombre_archivo_descarga,
                    ruta_pdf, fecha_emision, emitido_por, qr_token, estado, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'EMITIDA', NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            (int)$data['id_practica'],
            (int)$data['id_estudiante'],
            $data['nro_constancia'],
            $data['nombre_archivo_descarga'],
            $data['ruta_pdf'],
            $data['fecha_emision'],
            (int)$data['emitido_por'],
            $data['qr_token']
        ]);

        return $db->lastInsertId();
    }

    /**
     * Listado con filtros
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT c.*, 
                    est.apellidos_nombres AS estudiante_nombre,
                    est.dni AS estudiante_dni,
                    prog.nombre AS programa_nombre,
                    prog.codigo AS programa_codigo,
                    modf.descripcion AS modulo_nombre,
                    modf.nro_modulo,
                    sec.apellidos_nombres AS emitido_por_nombre
                FROM efsrt_constancias c
                INNER JOIN efsrt_practicas p ON c.id_practica = p.id
                INNER JOIN sigi_usuarios est ON c.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prog ON p.id_programa_estudios = prog.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN sigi_usuarios sec ON c.emitido_por = sec.id
                WHERE 1=1 ";

        $params = [];

        if (!empty($filters['id_periodo'])) {
            $sql .= " AND p.id_periodo_academico = ? ";
            $params[] = (int)$filters['id_periodo'];
        }

        if (!empty($filters['id_programa'])) {
            $sql .= " AND p.id_programa_estudios = ? ";
            $params[] = (int)$filters['id_programa'];
        }

        if (!empty($filters['programas_in']) && is_array($filters['programas_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['programas_in']), '?'));
            $sql .= " AND p.id_programa_estudios IN ($placeholders) ";
            foreach ($filters['programas_in'] as $prgId) {
                $params[] = (int)$prgId;
            }
        }

        if (!empty($filters['id_estudiante'])) {
            $sql .= " AND c.id_estudiante = ? ";
            $params[] = (int)$filters['id_estudiante'];
        }

        if (!empty($filters['busqueda'])) {
            $q = '%' . trim($filters['busqueda']) . '%';
            $sql .= " AND (c.nro_constancia LIKE ? OR est.apellidos_nombres LIKE ? OR est.dni LIKE ?) ";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($r['estudiante_nombre'] ?? '');
            if (!empty($r['emitido_por_nombre'])) {
                $r['emitido_por_nombre'] = \Core\Auth::formatearNombreUsuario($r['emitido_por_nombre']);
            }
        }
        return $rows;
    }
}
