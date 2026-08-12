<?php
namespace App\Models\Inventario;

use Core\Model;

class Bienes extends Model {

    public function __construct() {
        parent::__construct();
        if (!$this->db) {
            try {
                if (isset($GLOBALS['db'])) {
                    $this->db = $GLOBALS['db'];
                } else {
                    $host = 'localhost';
                    $dbname = 'sigi';
                    $username = 'root';
                    $password = '';
                    $this->db = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                    $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }
            } catch (\PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir) {
        $columns = [
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

        $orderBy = $columns[$orderCol] ?? 'id';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT *, id_inv_ambiente as ambiente_nombre 
                FROM inventario_bienes 
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['codigo_patrimonial'])) {
            $sql .= " AND codigo_patrimonial LIKE ?";
            $params[] = "%" . $filters['codigo_patrimonial'] . "%";
        }
        if (!empty($filters['denominacion'])) {
            $sql .= " AND denominacion LIKE ?";
            $params[] = "%" . $filters['denominacion'] . "%";
        }
        if (!empty($filters['estado_bien']) && $filters['estado_bien'] !== 'Todos') {
            $sql .= " AND estado_bien = ?";
            $params[] = $filters['estado_bien'];
        }

        // Conteo total para DataTables
        $countSql = "SELECT COUNT(*) as total FROM inventario_bienes WHERE 1=1";
        $totalResult = $this->db->query($countSql)->fetch(\PDO::FETCH_ASSOC);
        $total = $totalResult['total'] ?? 0;

        $start = (int)$start;
        $length = (int)$length;
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $length";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'data'  => $data
        ];
    }

    public function guardar($data) {
        if (!empty($data['id'])) {
            $sql = "UPDATE inventario_bienes SET 
                    id_inv_ambiente = ?, 
                    codigo_patrimonial = ?, 
                    denominacion = ?, 
                    marca = ?, 
                    modelo = ?, 
                    color = ?, 
                    serie = ?, 
                    estado_bien = ?, 
                    otros = ?, 
                    observaciones = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['id_inv_ambiente'],
                $data['codigo_patrimonial'],
                $data['denominacion'],
                $data['marca'],
                $data['modelo'],
                $data['color'],
                $data['serie'],
                $data['estado_bien'],
                $data['otros'],
                $data['observaciones'],
                $data['id']
            ]);
        } else {
            $sql = "INSERT INTO inventario_bienes (id_inv_ambiente, codigo_patrimonial, denominacion, marca, modelo, color, serie, estado_bien, otros, observaciones) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['id_inv_ambiente'],
                $data['codigo_patrimonial'],
                $data['denominacion'],
                $data['marca'],
                $data['modelo'],
                $data['color'],
                $data['serie'],
                $data['estado_bien'],
                $data['otros'],
                $data['observaciones']
            ]);
        }
    }
}