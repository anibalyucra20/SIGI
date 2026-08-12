<?php

namespace App\Models\Bolsa;

use Core\Model;
use PDO;

class Ofertas extends Model {
    protected $table = 'bolsa_ofertas_laborales';

    public function guardar($data) {
        if (!empty($data['id'])) {

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
                ORDER BY {$orderColumnName} {$orderDir}
                LIMIT :start, :length";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalStmt = self::$db->query("SELECT COUNT(*) FROM {$this->table}");
        $total = (int)$totalStmt->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }
}