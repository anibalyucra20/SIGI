<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Empresa extends Model
{
    public function getAll($busqueda = null, $estado = null)
    {
        $sql = "SELECT e.*, 
                    (SELECT COUNT(*) FROM efsrt_convenios c WHERE c.id_empresa = e.id AND c.estado = 'ACTIVO') AS convenios_activos,
                    (SELECT COUNT(*) FROM efsrt_convenios c WHERE c.id_empresa = e.id) AS total_convenios,
                    (SELECT COUNT(*) FROM efsrt_practicas p WHERE p.id_empresa = e.id) AS total_practicantes
                FROM efsrt_empresas e WHERE 1=1 ";
        
        $params = [];

        // Si el primer parámetro era booleano (compatibilidad con $soloActivas = true)
        if ($busqueda === true || $busqueda === 1) {
            $estado = 1;
            $busqueda = null;
        }

        if ($estado !== null && $estado !== '') {
            $sql .= " AND e.estado = ? ";
            $params[] = (int)$estado;
        }

        if (!empty($busqueda)) {
            $sql .= " AND (e.razon_social LIKE ? OR e.ruc LIKE ? OR e.nombre_comercial LIKE ? OR e.representante_legal LIKE ?) ";
            $term = "%" . trim($busqueda) . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY e.razon_social ASC";
        
        if (!empty($params)) {
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return self::getDB()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeRuc($ruc, $idExcluir = null)
    {
        if (empty($ruc)) return false;
        $sql = "SELECT COUNT(*) FROM efsrt_empresas WHERE ruc = ?";
        $params = [trim($ruc)];
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
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_empresas WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByRuc($ruc)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_empresas WHERE ruc = ?");
        $stmt->execute([trim($ruc)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO efsrt_empresas (
                    ruc, razon_social, nombre_comercial, direccion, distrito, 
                    provincia, departamento, representante_legal, cargo_representante, 
                    contacto_telefono, contacto_correo, rubro_sector, estado
                ) VALUES (
                    :ruc, :razon_social, :nombre_comercial, :direccion, :distrito, 
                    :provincia, :departamento, :representante_legal, :cargo_representante, 
                    :contacto_telefono, :contacto_correo, :rubro_sector, :estado
                )";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([
            ':ruc' => !empty($datos['ruc']) ? trim($datos['ruc']) : null,
            ':razon_social' => trim($datos['razon_social']),
            ':nombre_comercial' => !empty($datos['nombre_comercial']) ? trim($datos['nombre_comercial']) : null,
            ':direccion' => trim($datos['direccion']),
            ':distrito' => !empty($datos['distrito']) ? trim($datos['distrito']) : null,
            ':provincia' => !empty($datos['provincia']) ? trim($datos['provincia']) : null,
            ':departamento' => !empty($datos['departamento']) ? trim($datos['departamento']) : null,
            ':representante_legal' => trim($datos['representante_legal']),
            ':cargo_representante' => !empty($datos['cargo_representante']) ? trim($datos['cargo_representante']) : null,
            ':contacto_telefono' => !empty($datos['contacto_telefono']) ? trim($datos['contacto_telefono']) : null,
            ':contacto_correo' => !empty($datos['contacto_correo']) ? trim($datos['contacto_correo']) : null,
            ':rubro_sector' => !empty($datos['rubro_sector']) ? trim($datos['rubro_sector']) : null,
            ':estado' => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
        return self::getDB()->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $sql = "UPDATE efsrt_empresas SET 
                    ruc = :ruc,
                    razon_social = :razon_social,
                    nombre_comercial = :nombre_comercial,
                    direccion = :direccion,
                    distrito = :distrito,
                    provincia = :provincia,
                    departamento = :departamento,
                    representante_legal = :representante_legal,
                    cargo_representante = :cargo_representante,
                    contacto_telefono = :contacto_telefono,
                    contacto_correo = :contacto_correo,
                    rubro_sector = :rubro_sector,
                    estado = :estado
                WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute([
            ':id' => (int)$id,
            ':ruc' => !empty($datos['ruc']) ? trim($datos['ruc']) : null,
            ':razon_social' => trim($datos['razon_social']),
            ':nombre_comercial' => !empty($datos['nombre_comercial']) ? trim($datos['nombre_comercial']) : null,
            ':direccion' => trim($datos['direccion']),
            ':distrito' => !empty($datos['distrito']) ? trim($datos['distrito']) : null,
            ':provincia' => !empty($datos['provincia']) ? trim($datos['provincia']) : null,
            ':departamento' => !empty($datos['departamento']) ? trim($datos['departamento']) : null,
            ':representante_legal' => trim($datos['representante_legal']),
            ':cargo_representante' => !empty($datos['cargo_representante']) ? trim($datos['cargo_representante']) : null,
            ':contacto_telefono' => !empty($datos['contacto_telefono']) ? trim($datos['contacto_telefono']) : null,
            ':contacto_correo' => !empty($datos['contacto_correo']) ? trim($datos['contacto_correo']) : null,
            ':rubro_sector' => !empty($datos['rubro_sector']) ? trim($datos['rubro_sector']) : null,
            ':estado' => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    public function cambiarEstado($id, $estado)
    {
        $stmt = self::getDB()->prepare("UPDATE efsrt_empresas SET estado = ? WHERE id = ?");
        return $stmt->execute([(int)$estado, (int)$id]);
    }

    public function eliminar($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM efsrt_empresas WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
