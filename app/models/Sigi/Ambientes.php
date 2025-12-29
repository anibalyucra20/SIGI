<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Ambientes extends Model
{
    protected $table = 'sigi_ambientes';

    // PAGINADO Y FILTROS
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'nro',
            2 => 'aforo',
            3 => 'piso',
            4 => 'estado'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'nro';

        $where = [];
        $params = [];

        if (!empty($filters['nro'])) {
            $where[] = "nro LIKE :nro";
            $params[':nro'] = '%' . $filters['nro'] . '%';
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT * 
                FROM " . $this->table . " 
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total
        $sqlTotal = "SELECT COUNT(*) FROM " . $this->table . " 
            $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }


    // CRUD
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE " . $this->table . " SET 
                        tipo_ambiente = :tipo_ambiente,
                        nro = :nro, 
                        aforo = :aforo, 
                        piso = :piso, 
                        ubicacion = :ubicacion, 
                        observacion = :observacion, 
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':tipo_ambiente'      => $data['tipo_ambiente'],
                ':nro'              => $data['nro'],
                ':aforo'         => $data['aforo'],
                ':piso'         => $data['piso'],
                ':ubicacion'         => $data['ubicacion'],
                ':observacion'         => $data['observacion'],
                ':estado'         => $data['estado'],
                ':id'                  => $data['id']
            ];
        } else {
            $sql = "INSERT INTO " . $this->table . " 
                        (id_sede, tipo_ambiente, nro, aforo, piso, ubicacion, observacion, estado) 
                    VALUES 
                        (:id_sede, :tipo_ambiente, :nro, :aforo, :piso, :ubicacion, :observacion, :estado)";
            $params = [
                ':id_sede' => $data['id_sede'],
                ':tipo_ambiente'      => $data['tipo_ambiente'],
                ':nro'              => $data['nro'],
                ':aforo'         => $data['aforo'],
                ':piso'         => $data['piso'],
                ':ubicacion'         => $data['ubicacion'],
                ':observacion'         => $data['observacion'],
                ':estado'         => $data['estado']
            ];
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        // Si es registro nuevo, devuelve el id insertado
        if (empty($data['id'])) {
            return self::$db->lastInsertId();
        }
        return $data['id'];
    }
}
