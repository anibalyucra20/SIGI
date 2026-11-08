<?php

namespace App\Models\Bolsa;

use Core\Model;
use PDO;

class Empresas extends Model {

    protected $table = 'bolsa_empresa';
    /**
     * Busca una empresa por ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Busca si existe una empresa por RUC (útil para validar duplicados)
     */
    public function findByRuc($ruc, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ruc = :ruc";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':ruc', $ruc, PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':excludeId', (int)$excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas las empresas activas (ordenadas alfabéticamente)
     * Utilizado para selects en otros submódulos (ej. Ofertas)
     */
    public function listar()
    {
        $sql = "SELECT id, ruc, empresa FROM {$this->table} WHERE estado = 1 ORDER BY empresa ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene registros paginados con filtros para DataTables del lado del servidor.
     * Solo retorna empresas con estado = 1 (activas).
     */
    public function obtenerEmpresas($filters, $length, $start, $orderCol, $orderDir)
    {
        $columns = [
            0 => 'id',
            1 => 'id',
            2 => 'ruc',
            3 => 'empresa',
            4 => 'direccion',
            5 => 'telefono',
            6 => 'email',
            7 => 'fecha_registro'
        ];
        $orderColumnName = $columns[$orderCol] ?? 'empresa';
        $orderDir = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';
        // Regla estricta: Solo empresas activas
        $where = ['estado = 1'];
        $params = [];
        // Filtro específico por RUC
        if (!empty($filters['ruc'])) {
            $where[] = 'ruc LIKE :ruc';
            $params[':ruc'] = '%' . trim($filters['ruc']) . '%';
        }
        // Filtro específico por Razón Social / Nombre
        if (!empty($filters['empresa'])) {
            $where[] = 'empresa LIKE :empresa';
            $params[':empresa'] = '%' . trim($filters['empresa']) . '%';
        }

        // Filtro de búsqueda general de DataTables (search[value])
        if (!empty($filters['search'])) {
            $where[] = '(ruc LIKE :search OR empresa LIKE :search OR direccion LIKE :search OR telefono LIKE :search OR email LIKE :search)';
            $params[':search'] = '%' . trim($filters['search']) . '%';
        }
        $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        // Consulta de datos con paginación
        $sql = "SELECT id, ruc, empresa, direccion, telefono, email, id_usuario, estado, fecha_registro
                FROM {$this->table}
                {$sqlWhere}
                ORDER BY {$orderColumnName} {$orderDir}
                LIMIT :start, :length";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Conteo total de registros que cumplen los filtros
        $sqlTotal = "SELECT COUNT(*) FROM {$this->table} {$sqlWhere}";
        $totalStmt = self::$db->prepare($sqlTotal);
        foreach ($params as $key => $value) {
            $totalStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $totalStmt->execute();
        $total = (int)$totalStmt->fetchColumn();
        return ['data' => $data, 'total' => $total];
    }

    /**
     * Guarda una empresa (crea una nueva o actualiza existente)
     */
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE {$this->table} SET
                        ruc = :ruc,
                        empresa = :empresa,
                        direccion = :direccion,
                        telefono = :telefono,
                        email = :email,
                        id_usuario = :id_usuario,
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':id'         => (int)$data['id'],
                ':ruc'        => trim($data['ruc']),
                ':empresa'    => trim($data['empresa']),
                ':direccion'  => trim($data['direccion']),
                ':telefono'   => trim($data['telefono']),
                ':email'      => trim($data['email']),
                ':id_usuario' => (int)$data['id_usuario'],
                ':estado'     => isset($data['estado']) ? (int)$data['estado'] : 1
            ];
        } else {
            $sql = "INSERT INTO {$this->table} (
                        ruc, empresa, direccion, telefono, email, id_usuario, estado, fecha_registro
                    ) VALUES (
                        :ruc, :empresa, :direccion, :telefono, :email, :id_usuario, :estado, NOW()
                    )";
            $params = [
                ':ruc'        => trim($data['ruc']),
                ':empresa'    => trim($data['empresa']),
                ':direccion'  => trim($data['direccion']),
                ':telefono'   => trim($data['telefono']),
                ':email'      => trim($data['email']),
                ':id_usuario' => (int)$data['id_usuario'],
                ':estado'     => isset($data['estado']) ? (int)$data['estado'] : 1
            ];
        }

        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminación lógica (Soft delete):
     * Actualiza el estado a 0 sin borrar físicamente el registro.
     */
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->table} SET estado = 0 WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}