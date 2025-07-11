<?php
namespace App\Models\Academico;

use Core\Model;
use PDO;

class UnidadesDidacticas extends Model
{
    protected $table = 'acad_programacion_unidad_didactica';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'pud.id',
            1 => 'pr.nombre',
            2 => 'pl.nombre',
            3 => 'mf.descripcion',
            4 => 's.descripcion',
            5 => 'ud.nombre',
            6 => 'pud.turno',
            7 => 'pud.seccion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'pud.id';

        $where = [
            "pud.id_sede = :id_sede",
            "pud.id_periodo_academico = :id_periodo",
            "pud.id_docente = :id_docente"
        ];
        $params = [
            ':id_sede' => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo'],
            ':id_docente' => $filters['id_docente']
        ];

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT pud.id, 
                       pr.nombre AS programa_nombre,
                       pl.nombre AS plan_nombre,
                       mf.descripcion AS modulo_nombre,
                       s.descripcion AS semestre_nombre,
                       ud.nombre AS unidad_nombre, 
                       pud.turno, pud.seccion
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
                INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total
        $sqlTotal = "SELECT COUNT(*)
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
                INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        return ['data' => $data, 'total' => $total];
    }

   
}
