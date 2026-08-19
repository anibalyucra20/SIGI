<?php
namespace App\Models\Inventario;

use Core\Model;
use PDO;


class Bienes extends Model {


     public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_inv_ambiente',
            2 => 'codigo_patrimonial',
            3 => 'denominacion',
            4 => 'marca',
            5 => 'modelo',
            6 => 'color',
            7 => 'serie',
            8 => 'estado_bien'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $where  = '';
        $params = [];


        /*if (!empty($filters['codigo'])) {
            $where   .= " codigo LIKE :codigo ";
            $params[':codigo'] = "%{$filters['codigo']}%";
        }
        if (!empty($filters['nombre'])) {
            $where   .= ($where != '') ? 'AND' : 'WHERE';
            $where   .= " nombre LIKE :nombre ";
            $params[':nombre'] = "%{$filters['nombre']}%";
        }*/

        $sqlWhere = $where;

        $sql = "SELECT * FROM inventario_bienes
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total

        $sqlTotal = "SELECT COUNT(*) FROM inventario_bienes
            $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();


        return ['data' => $data, 'total' => $total];
    }

     public function find($id)
    {
        $sql = "SELECT * FROM inventario_bienes WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
        {
            if (!empty($data['id'])) {
                $sql = "UPDATE inventario_bienes SET 
                            id_inv_ambiente    = :id_inv_ambiente,
                            codigo_patrimonial = :codigo_patrimonial,
                            denominacion       = :denominacion,
                            marca              = :marca,
                            modelo             = :modelo,
                            color              = :color,
                            serie              = :serie,
                            estado_bien        = :estado_bien,
                            otros              = :otros,
                            observaciones      = :observaciones

                        WHERE id = :id";
                $params = [
                    ':id_inv_ambiente'           => $data['id_inv_ambiente'],
                    ':codigo_patrimonial'        => $data['codigo_patrimonial'],
                    ':denominacion'              => $data['denominacion'],
                    ':marca'                     => $data['marca'],
                    ':modelo'                    => $data['modelo'],
                    ':color'                     => $data['color'],
                    ':serie'                     => $data['serie'],
                    ':estado_bien'               => $data['estado_bien'],
                    ':otros'                     => $data['otros'],
                    ':observaciones'             => $data['observaciones'],
                    ':id'                        => $data['id']
                ];
            } else {
                $sql = "INSERT INTO inventario_bienes 
                            (id_inv_ambiente, codigo_patrimonial, denominacion, marca, modelo, color, serie, estado_bien, otros, observaciones    ) 
                        VALUES 
                            (:id_inv_ambiente, :codigo_patrimonial, :denominacion, :marca, :modelo, :color, :serie, :estado_bien, :otros, :observaciones)";
                $params = [
                    ':id_inv_ambiente'          => $data['id_inv_ambiente'],
                    ':codigo_patrimonial'       => $data['codigo_patrimonial'],
                    ':denominacion'             => $data['denominacion'],
                    ':marca'                    => $data['marca'],
                    ':modelo'                   => $data['modelo'],
                    ':color'                    => $data['color'],
                    ':serie'                    => $data['serie'],
                    ':estado_bien'              => $data['estado_bien'],
                    ':otros'                    => $data['otros'],
                    ':observaciones'            => $data['observaciones'] 
                ];
            }
            $stmt = self::$db->prepare($sql);
            $stmt->execute($params);
            if (empty($data['id'])) {
                return self::$db->lastInsertId();
            }
            return $data['id'];
        }


        public function eliminar($id)
        {
            $stmt = self::$db->prepare("DELETE FROM inventario_bienes WHERE id=?");
            $stmt->execute([$id]);
        }
    }
