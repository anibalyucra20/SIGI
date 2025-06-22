<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Sedes extends Model
{
    protected $table = 'sigi_sedes';
    // Listar sedes (con filtros)
    public function getAll($filtros = [])
    {
        $sql = "SELECT * FROM sigi_sedes WHERE 1";
        $params = [];

        if (!empty($filtros['nombre'])) {
            $sql .= " AND nombre LIKE :nombre";
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }
        if (!empty($filtros['departamento'])) {
            $sql .= " AND departamento LIKE :departamento";
            $params[':departamento'] = '%' . $filtros['departamento'] . '%';
        }
        if (!empty($filtros['provincia'])) {
            $sql .= " AND provincia LIKE :provincia";
            $params[':provincia'] = '%' . $filtros['provincia'] . '%';
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        $sql .= " ORDER BY nombre ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Obtener una sede por ID
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_sedes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Guardar (nuevo o editar)
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            // UPDATE
            $sql = "UPDATE sigi_sedes SET cod_modular = :cod_modular, nombre = :nombre, departamento = :departamento, provincia = :provincia,
                    distrito = :distrito, direccion = :direccion, telefono = :telefono, correo = :correo, responsable = :responsable
                WHERE id = :id";
            $params = [
                ':cod_modular' => $data['cod_modular'],
                ':nombre' => $data['nombre'],
                ':departamento' => $data['departamento'],
                ':provincia' => $data['provincia'],
                ':distrito' => $data['distrito'],
                ':direccion' => $data['direccion'],
                ':telefono' => $data['telefono'],
                ':correo' => $data['correo'],
                ':responsable' => $data['responsable'],
                ':id' => $data['id']
            ];
        } else {
            // INSERT
            $sql = "INSERT INTO sigi_sedes (cod_modular, nombre, departamento, provincia, distrito, direccion, telefono, correo, responsable)
                VALUES (:cod_modular, :nombre, :departamento, :provincia, :distrito, :direccion, :telefono, :correo, :responsable)";
            $params = [
                ':cod_modular' => $data['cod_modular'],
                ':nombre' => $data['nombre'],
                ':departamento' => $data['departamento'],
                ':provincia' => $data['provincia'],
                ':distrito' => $data['distrito'],
                ':direccion' => $data['direccion'],
                ':telefono' => $data['telefono'],
                ':correo' => $data['correo'],
                ':responsable' => $data['responsable']
            ];
        }
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }


    // Eliminar sede
    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM sigi_sedes WHERE id = ?");
        return $stmt->execute([$id]);
    }
    

    

    // Guardar asociaciones (borra y crea)
    public function guardarProgramasSede($id_sede, $programas)
    {
        // Borra todas las asociaciones actuales
        $stmt = self::$db->prepare("DELETE FROM sigi_programa_sede WHERE id_sede = ?");
        $stmt->execute([$id_sede]);
        // Inserta nuevas asociaciones
        if (!empty($programas)) {
            $stmt = self::$db->prepare("INSERT INTO sigi_programa_sede (id_sede, id_programa_estudio, fecha_registro) VALUES (?, ?, ?)");
            $hoy = date('Y-m-d');
            foreach ($programas as $id_prog) {
                $stmt->execute([$id_sede, $id_prog, $hoy]);
            }
        }
    }
    // Listado de sedes (para asignar SELECT)
    public function getSedes()
    {
        return self::$db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre")
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}
