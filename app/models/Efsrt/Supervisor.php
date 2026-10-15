<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Supervisor extends Model
{
    public function getAll($id_periodo = null, $id_programa = null, $id_sede = null)
    {
        $sql = "SELECT s.*, 
                    u.apellidos_nombres AS docente_nombre,
                    u.dni AS docente_dni,
                    u.correo AS docente_correo,
                    u.telefono AS docente_telefono,
                    p.nombre AS periodo_nombre,
                    pr.nombre AS programa_nombre,
                    pr.codigo AS programa_codigo,
                    se.nombre AS sede_nombre,
                    (SELECT COUNT(*) FROM efsrt_practicas prc WHERE prc.id_supervisor = s.id) AS total_asignados,
                    (SELECT COUNT(*) FROM efsrt_practicas prc WHERE prc.id_supervisor = s.id AND prc.estado IN ('APROBADO_SUPERVISOR', 'DERIVADO_COORDINADOR', 'FINALIZADO_EMITIDO')) AS total_aprobados
                FROM efsrt_supervisores s
                INNER JOIN sigi_usuarios u ON s.id_docente = u.id
                INNER JOIN sigi_periodo_academico p ON s.id_periodo_academico = p.id
                INNER JOIN sigi_programa_estudios pr ON s.id_programa_estudios = pr.id
                INNER JOIN sigi_sedes se ON s.id_sede = se.id
                WHERE 1=1 ";
        $params = [];
        if ($id_periodo) {
            $sql .= " AND s.id_periodo_academico = ? ";
            $params[] = (int)$id_periodo;
        }
        if ($id_programa) {
            $sql .= " AND s.id_programa_estudios = ? ";
            $params[] = (int)$id_programa;
        }
        if ($id_sede) {
            $sql .= " AND s.id_sede = ? ";
            $params[] = (int)$id_sede;
        }
        $sql .= " ORDER BY u.apellidos_nombres ASC";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['docente_nombre'] = \Core\Auth::formatearNombreUsuario($r['docente_nombre'] ?? '');
        }
        return $rows;
    }

    public function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT s.*, u.apellidos_nombres AS docente_nombre, u.dni AS docente_dni 
                                       FROM efsrt_supervisores s 
                                       INNER JOIN sigi_usuarios u ON s.id_docente = u.id 
                                       WHERE s.id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['docente_nombre'] = \Core\Auth::formatearNombreUsuario($row['docente_nombre'] ?? '');
        }
        return $row;
    }

    public function getByDocente($id_docente, $id_periodo = null)
    {
        $sql = "SELECT s.*, 
                    p.nombre AS periodo_nombre,
                    pr.nombre AS programa_nombre,
                    pr.codigo AS programa_codigo
                FROM efsrt_supervisores s
                INNER JOIN sigi_periodo_academico p ON s.id_periodo_academico = p.id
                INNER JOIN sigi_programa_estudios pr ON s.id_programa_estudios = pr.id
                WHERE s.id_docente = ? AND s.estado = 1 ";
        $params = [(int)$id_docente];
        if ($id_periodo) {
            $sql .= " AND s.id_periodo_academico = ? ";
            $params[] = (int)$id_periodo;
        }
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocentesDisponibles($id_sede = null)
    {
        $sql = "SELECT u.id, u.dni, u.apellidos_nombres, u.correo, u.telefono 
                FROM sigi_usuarios u 
                WHERE u.id_rol = 6 AND u.estado = 1 ";
        $params = [];
        if ($id_sede) {
            $sql .= " AND (u.id_sede = ? OR u.id_sede IS NULL) ";
            $params[] = (int)$id_sede;
        }
        $sql .= " ORDER BY u.apellidos_nombres ASC";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['apellidos_nombres'] = \Core\Auth::formatearNombreUsuario($r['apellidos_nombres'] ?? '');
        }
        return $rows;
    }

    public function asignar($datos)
    {
        // Verificar si ya existe asignación del docente para ese periodo y programa
        $stmt = self::getDB()->prepare("SELECT id FROM efsrt_supervisores 
                                       WHERE id_periodo_academico = ? AND id_programa_estudios = ? AND id_sede = ? AND id_docente = ?");
        $stmt->execute([
            (int)$datos['id_periodo_academico'],
            (int)$datos['id_programa_estudios'],
            (int)$datos['id_sede'],
            (int)$datos['id_docente']
        ]);
        $existente = $stmt->fetchColumn();

        if ($existente) {
            // Actualizar estado a activo y actualizar resolución si cambió
            $update = self::getDB()->prepare("UPDATE efsrt_supervisores SET resolucion_designacion = ?, max_estudiantes = ?, estado = 1 WHERE id = ?");
            $update->execute([
                $datos['resolucion_designacion'] ?? null,
                (int)($datos['max_estudiantes'] ?? 30),
                $existente
            ]);
            return $existente;
        }

        $sql = "INSERT INTO efsrt_supervisores (
                    id_periodo_academico, id_programa_estudios, id_sede, 
                    id_docente, resolucion_designacion, max_estudiantes, estado
                ) VALUES (
                    :id_periodo_academico, :id_programa_estudios, :id_sede, 
                    :id_docente, :resolucion_designacion, :max_estudiantes, :estado
                )";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([
            ':id_periodo_academico' => (int)$datos['id_periodo_academico'],
            ':id_programa_estudios' => (int)$datos['id_programa_estudios'],
            ':id_sede' => (int)$datos['id_sede'],
            ':id_docente' => (int)$datos['id_docente'],
            ':resolucion_designacion' => $datos['resolucion_designacion'] ?? null,
            ':max_estudiantes' => (int)($datos['max_estudiantes'] ?? 30),
            ':estado' => 1
        ]);
        return self::getDB()->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $sql = "UPDATE efsrt_supervisores SET 
                    resolucion_designacion = :resolucion_designacion,
                    max_estudiantes = :max_estudiantes,
                    estado = :estado
                WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute([
            ':id' => (int)$id,
            ':resolucion_designacion' => $datos['resolucion_designacion'] ?? null,
            ':max_estudiantes' => (int)($datos['max_estudiantes'] ?? 30),
            ':estado' => (int)$datos['estado']
        ]);
    }

    public function buscarDocentesDisponibles($id_periodo = null, $id_sede = null, $busqueda = '')
    {
        return $this->getDocentesDisponibles($id_sede);
    }

    public function create($datos)
    {
        return $this->asignar($datos);
    }

    public function update($id, $datos)
    {
        return $this->actualizar($id, $datos);
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $stmt = self::getDB()->prepare("UPDATE efsrt_supervisores SET estado = ? WHERE id = ?");
        return $stmt->execute([(int)$nuevoEstado, (int)$id]);
    }

    public function eliminar($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM efsrt_supervisores WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
