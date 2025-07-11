<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Rol extends Model
{
    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_roles';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function buscar()
    {
        $sql = "SELECT * FROM sigi_roles ORDER BY id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Listado de roles válidos (excluye ESTUDIANTE y EXTERNO)
    public function getRolesDocente()
    {
        $sql = "SELECT id, nombre FROM sigi_roles 
                WHERE nombre NOT IN ('ESTUDIANTE','EXTERNO')
                ORDER BY id";
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
