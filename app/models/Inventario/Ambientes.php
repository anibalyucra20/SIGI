<?php
namespace App\Models\Inventario;

use Core\Model;
use PDO;


class Ambientes extends Model {

 public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'id_sigi_ambiente',
            2 => 'id_docente'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $where  = '';
        $params = [];

        $sqlWhere = $where;
            $sql = "SELECT a.id, ta.id AS id_sigi_ambiente, ta.tipo_ambiente AS ambiente_nombre, ta.nro, u.apellidos_nombres AS docente
                FROM sigi_ambientes ta
                INNER JOIN inventario_ambientes a ON ta.id = a.id_sigi_ambiente
                LEFT JOIN sigi_usuarios u ON a.id_docente = u.id
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
        $sqlTotal = "SELECT COUNT(*) FROM sigi_ambientes ta
                LEFT JOIN inventario_ambientes a ON ta.id = a.id_sigi_ambiente
                LEFT JOIN sigi_usuarios u ON a.id_docente = u.id
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['docente']));
            $nombre_docente_formateado = ($apellidos_nombres[0] ?? '') . ' ' . ($apellidos_nombres[1] ?? '') . ' ' . ($apellidos_nombres[2] ?? '');

            // Sobrescribimos los campos originales que lee tu JavaScript sin alterar la estructura
            $data[$key]['id_sigi_ambiente'] = $value['ambiente_nombre'] ?? $value['id_sigi_ambiente'];
            $data[$key]['id_docente'] = !empty(trim($nombre_docente_formateado)) ? $nombre_docente_formateado : $value['id_docente'];
        }


        return ['data' => $data, 'total' => $total];
    }

     public function find($id)
    {
        $sql = "SELECT * FROM inventario_ambientes WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar($data)
        {
            if (!empty($data['id'])) {
                $sql = "UPDATE inventario_ambientes SET 
                            id_sigi_ambiente    = :id_sigi_ambiente,
                            id_docente          = :id_docente

                        WHERE id = :id";
                $params = [
                    ':id_sigi_ambiente'           => $data['id_sigi_ambiente'],
                    ':id_docente'                 => $data['id_docente'],
                    ':id'                        => $data['id']
                ];
            } else {
                $sql = "INSERT INTO inventario_ambientes
                            (id_sigi_ambiente, id_docente   ) 
                        VALUES 
                            (:id_sigi_ambiente, :id_docente )";
                $params = [
                    ':id_sigi_ambiente'          => $data['id_sigi_ambiente'],
                    ':id_docente'                => $data['id_docente']
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
            $stmt = self::$db->prepare("DELETE FROM inventario_ambientes WHERE id=?");
            $stmt->execute([$id]);
        }

            // Listado de Ambientes (para asignar SELECT)
        public function getAmbientes()
        {
            return self::$db->query("
                SELECT 
                    a.id AS id_ambiente,
                    a.tipo_ambiente,
                    a.nro,
                    a.aforo,
                    a.piso,
                    a.ubicacion,
                    i.id AS id_inventario,
                    i.id_docente,
                    i.fecha_update
                FROM sigi_ambientes a
                INNER JOIN inventario_ambientes i ON a.id = i.id_sigi_ambiente
                ORDER BY a.tipo_ambiente, a.nro
            ")->fetchAll(PDO::FETCH_ASSOC);
        }
}