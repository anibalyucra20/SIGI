<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Convenio extends Model
{
    public function getAll($id_empresa = null, $estado = null, $busqueda = null)
    {
        $sql = "SELECT c.*, 
                    e.razon_social AS empresa_nombre, 
                    e.ruc AS empresa_ruc,
                    u.apellidos_nombres AS usuario_registro
                FROM efsrt_convenios c
                INNER JOIN efsrt_empresas e ON c.id_empresa = e.id
                LEFT JOIN sigi_usuarios u ON c.created_by = u.id 
                WHERE 1=1 ";
        
        $params = [];

        if (!empty($id_empresa)) {
            $sql .= " AND c.id_empresa = ? ";
            $params[] = (int)$id_empresa;
        }

        if (!empty($estado)) {
            $sql .= " AND c.estado = ? ";
            $params[] = trim($estado);
        }

        if (!empty($busqueda)) {
            $sql .= " AND (c.numero_convenio LIKE ? OR e.razon_social LIKE ? OR e.ruc LIKE ? OR c.descripcion_objeto LIKE ?) ";
            $term = "%" . trim($busqueda) . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY c.fecha_fin DESC";

        if (!empty($params)) {
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return self::getDB()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeNumero($numero, $idExcluir = null)
    {
        if (empty($numero)) return false;
        $sql = "SELECT COUNT(*) FROM efsrt_convenios WHERE numero_convenio = ?";
        $params = [trim($numero)];
        if ($idExcluir) {
            $sql .= " AND id != ?";
            $params[] = (int)$idExcluir;
        }
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create($datos)
    {
        return $this->crear($datos);
    }

    public function update($id, $datos)
    {
        return $this->actualizar($id, $datos);
    }

    public function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT c.*, e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc 
                                       FROM efsrt_convenios c 
                                       INNER JOIN efsrt_empresas e ON c.id_empresa = e.id 
                                       WHERE c.id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivosPorEmpresa($id_empresa)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_convenios 
                                       WHERE id_empresa = ? AND estado = 'ACTIVO' AND fecha_fin >= CURDATE()
                                       ORDER BY fecha_fin DESC");
        $stmt->execute([(int)$id_empresa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO efsrt_convenios (
                    id_empresa, numero_convenio, tipo_convenio, fecha_firma, 
                    fecha_inicio, fecha_fin, archivo_convenio_pdf, descripcion_objeto, 
                    estado, created_by
                ) VALUES (
                    :id_empresa, :numero_convenio, :tipo_convenio, :fecha_firma, 
                    :fecha_inicio, :fecha_fin, :archivo_convenio_pdf, :descripcion_objeto, 
                    :estado, :created_by
                )";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([
            ':id_empresa' => (int)$datos['id_empresa'],
            ':numero_convenio' => trim($datos['numero_convenio']),
            ':tipo_convenio' => $datos['tipo_convenio'] ?? 'ESPECIFICO',
            ':fecha_firma' => $datos['fecha_firma'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin' => $datos['fecha_fin'],
            ':archivo_convenio_pdf' => $datos['archivo_convenio_pdf'] ?? null,
            ':descripcion_objeto' => !empty($datos['descripcion_objeto']) ? trim($datos['descripcion_objeto']) : null,
            ':estado' => $datos['estado'] ?? 'ACTIVO',
            ':created_by' => (int)$datos['created_by']
        ]);
        return self::getDB()->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $sql = "UPDATE efsrt_convenios SET 
                    id_empresa = :id_empresa,
                    numero_convenio = :numero_convenio,
                    tipo_convenio = :tipo_convenio,
                    fecha_firma = :fecha_firma,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    descripcion_objeto = :descripcion_objeto,
                    estado = :estado";
        
        $params = [
            ':id' => (int)$id,
            ':id_empresa' => (int)$datos['id_empresa'],
            ':numero_convenio' => trim($datos['numero_convenio']),
            ':tipo_convenio' => $datos['tipo_convenio'] ?? 'ESPECIFICO',
            ':fecha_firma' => $datos['fecha_firma'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin' => $datos['fecha_fin'],
            ':descripcion_objeto' => !empty($datos['descripcion_objeto']) ? trim($datos['descripcion_objeto']) : null,
            ':estado' => $datos['estado'] ?? 'ACTIVO'
        ];

        if (!empty($datos['archivo_convenio_pdf'])) {
            $sql .= ", archivo_convenio_pdf = :archivo_convenio_pdf";
            $params[':archivo_convenio_pdf'] = $datos['archivo_convenio_pdf'];
        }

        $sql .= " WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute($params);
    }

    public function actualizarEstadoVencidos()
    {
        $sql = "UPDATE efsrt_convenios SET estado = 'VENCIDO' WHERE fecha_fin < CURDATE() AND estado = 'ACTIVO'";
        return self::getDB()->exec($sql);
    }

    public function eliminar($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM efsrt_convenios WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
