<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Permiso extends Model
{
    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_permisos_usuarios';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function buscar()
    {
        
    }
    // Listado de sistemas (para asignar)
    public function getPermisos($id)
    {
        $sql = "SELECT spu.id, si.nombre AS sistema, sr.nombre AS rol
            FROM sigi_permisos_usuarios spu
            INNER JOIN sigi_sistemas_integrados si ON spu.id_sistema = si.id
            INNER JOIN sigi_roles sr ON spu.id_rol = sr.id
            WHERE spu.id_usuario = ?";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
