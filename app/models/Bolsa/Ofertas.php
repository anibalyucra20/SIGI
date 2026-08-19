<?php

namespace App\Models\Bolsa;

use Core\Model;
use PDO;

class Ofertas extends Model {
    protected $table = 'bolsa_ofertas_laborales';

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data) {
        if (!empty($data['id'])) {
        $sql = "UPDATE {$this->table} SET
                    id_empresa = :id_empresa,
                    programa_estudio = :programa_estudio,
                    titulo = :titulo,
                    detalle = :detalle,
                    fecha_publicacion = :fecha_publicacion,
                    fecha_cierre = :fecha_cierre,
                    salario = :salario,
                    requisitos = :requisitos,
                    ubicacion = :ubicacion,
                    tipo_contrato = :tipo_contrato,
                    foto = :foto,
                    estado = :estado
                WHERE id = :id";

        $params = [
            ':id'                => $data['id'],
            ':id_empresa'        => $data['id_empresa'],
            ':programa_estudio'  => $data['programa_estudio'],
            ':titulo'            => $data['titulo'],
            ':detalle'           => $data['detalle'],
            ':fecha_publicacion' => $data['fecha_publicacion'],
            ':fecha_cierre'      => $data['fecha_cierre'],
            ':salario'           => $data['salario'],
            ':requisitos'        => $data['requisitos'],
            ':ubicacion'         => $data['ubicacion'],
            ':tipo_contrato'     => $data['tipo_contrato'],
            ':foto'              => $data['foto'],
            ':estado'            => $data['estado']
        ];
        }else {
        $sql = "INSERT INTO {$this->table} (id_empresa, programa_estudio, titulo, detalle, fecha_publicacion, fecha_cierre, salario, 
        requisitos, ubicacion, tipo_contrato, foto, estado) VALUES (:id_empresa, :programa_estudio, :titulo, :detalle, :fecha_publicacion, 
        :fecha_cierre, :salario, :requisitos, :ubicacion, :tipo_contrato, :foto, :estado)";
        $params = [
            ':id_empresa'        => $data['id_empresa'],
            ':programa_estudio'  => $data['programa_estudio'],
            ':titulo'            => $data['titulo'],
            ':detalle'           => $data['detalle'],
            ':fecha_publicacion' => $data['fecha_publicacion'],
            ':fecha_cierre'      => $data['fecha_cierre'],
            ':salario'           => $data['salario'],
            ':requisitos'        => $data['requisitos'],
            ':ubicacion'         => $data['ubicacion'],
            ':tipo_contrato'     => $data['tipo_contrato'],
            ':foto'              => $data['foto'],
            ':estado'            => $data['estado']
        ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    public function ObtenerOfertas($filters, $length, $start, $orderCol, $orderDir) {
        $columns = ['id', 'empresa_nombre', 'programa_nombre', 'titulo', 'detalle', 'fecha_publicacion', 'fecha_cierre', 'salario'];
        $orderColumnName = $columns[$orderCol] ?? 'empresa_nombre';
        $orderDir = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';
        // FILTROS
        $where = ['o.estado = 1'];
        $params = [];

        // Filtro por empresa
        if (!empty($filters['id_empresa'])) {
            $where[] = 'o.id_empresa = :id_empresa';
            $params[':id_empresa'] = $filters['id_empresa'];
        }

        // Filtro por programa
        if (!empty($filters['programa_estudio'])) {
            $where[] = 'o.programa_estudio = :programa_estudio';
            $params[':programa_estudio'] = $filters['programa_estudio'];
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        // CONSULTA PRINCIPAL
        $sql = "SELECT 
                    o.id,
                    o.id_empresa,
                    e.empresa AS empresa_nombre,
                    o.programa_estudio,
                    p.nombre AS programa_nombre,
                    o.titulo,
                    o.detalle,
                    o.fecha_publicacion,
                    o.fecha_cierre,
                    o.salario,
                    o.ubicacion,
                    o.tipo_contrato,
                    o.foto,
                    o.estado
                FROM {$this->table} o
                LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                LEFT JOIN sigi_programa_estudios p ON p.id = o.programa_estudio
                {$sqlWhere}
                ORDER BY {$orderColumnName} {$orderDir}
                LIMIT :start, :length";
        $stmt = self::$db->prepare($sql);
        // Parámetros de filtros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // TOTAL DE REGISTROS FILTRADOS
        $sqlTotal = "SELECT COUNT(*)
                    FROM {$this->table} o
                    LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                    LEFT JOIN sigi_programa_estudios p ON p.id = o.programa_estudio
                    {$sqlWhere}";
        $totalStmt = self::$db->prepare($sqlTotal);

        foreach ($params as $key => $value) {
            $totalStmt->bindValue($key, $value, PDO::PARAM_INT);
        }

        $totalStmt->execute();

        $total = (int)$totalStmt->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

    public function eliminar($id) {
        $sql = "UPDATE {$this->table} SET estado = 0 WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}