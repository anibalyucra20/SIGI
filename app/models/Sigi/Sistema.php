<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Sistema extends Model
{
    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_permisos_usuarios';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function buscar()
    {
        
    }
    // Listado de sistemas (para asignar)
    public function getSistemas()
    {
        return self::$db->query("SELECT id, nombre FROM sigi_sistemas_integrados ORDER BY nombre")
            ->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
