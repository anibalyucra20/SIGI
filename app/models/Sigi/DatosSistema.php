<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class DatosSistema extends Model
{
    //protected $table = 'sigi_usuarios';
    protected $table = 'sigi_datos_sistema';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function buscar()
    {
        $sql = "SELECT * FROM sigi_datos_sistema WHERE id = 1 ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Guardar (actualizar el registro único)
    public function guardar($data)
    {
        // Actualiza el registro existente
        $sql = "UPDATE sigi_datos_sistema SET 
                    dominio_pagina = :dominio_pagina,
                    favicon = :favicon,
                    logo = :logo,
                    nombre_completo = :nombre_completo,
                    nombre_corto = :nombre_corto,
                    pie_pagina = :pie_pagina,
                    host_mail = :host_mail,
                    email_email = :email_email,
                    password_email = :password_email,
                    puerto_email = :puerto_email,
                    color_correo = :color_correo,
                    cant_semanas = :cant_semanas,
                    token_sistema = :token_sistema
                WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $data['id'] = (int)$data['id'];
        // registro de Log
        self::log($_SESSION['sigi_user_id'], 'EDITAR', 'Editó datos de sistema', 'sigi_datos_sistema', $data['id']);
        return $stmt->execute($data);
    }
}
