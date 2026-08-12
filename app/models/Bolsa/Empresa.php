<?php

namespace App\Models\Bolsa;

use Core\Model;

class Empresa extends Model {
    protected $table = 'bolsa_empresa';

    public function listar()
    {
        $sql = "SELECT id, empresa FROM {$this->table} ORDER BY empresa ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}