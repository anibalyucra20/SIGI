<?php
namespace App\Models\Sigi;

use Core\Model;
use PDO;

class SistemasIntegrados extends Model
{
    public function getPaginated($length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'id',
            1 => 'nombre',
            2 => 'codigo'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'id';

        $sql = "SELECT * FROM sigi_sistemas_integrados ORDER BY $ordenarPor $orderDir LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) FROM sigi_sistemas_integrados";
        $total = self::$db->query($sqlTotal)->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }
}
