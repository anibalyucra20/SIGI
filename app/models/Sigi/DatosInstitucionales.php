<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class DatosInstitucionales extends Model
{

    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_datos_institucionales';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function buscar()
    {
        $sql = "SELECT * FROM sigi_datos_institucionales WHERE id = 1 ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Actualizar (o insertar si no existe aún)
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            // Actualiza el registro existente
            $sql = "UPDATE sigi_datos_institucionales SET 
                         cod_modular = :cod_modular,
                         ruc = :ruc,
                         nombre_institucion = :nombre_institucion,
                         departamento = :departamento,
                         provincia = :provincia,
                         distrito = :distrito,
                         direccion = :direccion,
                         telefono = :telefono,
                         correo = :correo,
                         nro_resolucion = :nro_resolucion,
                         estado = :estado
                     WHERE id = :id";
            $stmt = self::$db->prepare($sql);
            $data['id'] = (int)$data['id'];
            // registro de Log
            self::log($_SESSION['sigi_user_id'], 'EDITAR', 'Editó datos institucionales', 'sigi_datos_institucionales', $data['id']);
            return $stmt->execute($data);
        } else {
            // Inserta el primer registro (solo si está vacío)
            $sql = "INSERT INTO sigi_datos_institucionales (
                         cod_modular, ruc, nombre_institucion, departamento, provincia, distrito,
                         direccion, telefono, correo, nro_resolucion, estado
                     ) VALUES (
                         :cod_modular, :ruc, :nombre_institucion, :departamento, :provincia, :distrito,
                         :direccion, :telefono, :correo, :nro_resolucion, :estado
                     )";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute($data);
        }
    }
}
