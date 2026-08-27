<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class MoodleVinculoCriterio extends Model
{
    private string $table = 'acad_moodle_vinculo_criterio';

    public function upsert(array $d): bool
    {
        $sql = "
            INSERT INTO {$this->table}
                (id_programacion_ud, nro_calificacion, evaluacion_detalle, criterio_orden,
                 moodle_course_id, moodle_cmid, graded, moodle_grade_item_id, last_sync_at)
            VALUES
                (:id_programacion_ud, :nro_calificacion, :evaluacion_detalle, :criterio_orden,
                 :moodle_course_id, :moodle_cmid, :graded, :moodle_grade_item_id, :last_sync_at)
            ON DUPLICATE KEY UPDATE
                moodle_course_id      = VALUES(moodle_course_id),
                moodle_cmid           = VALUES(moodle_cmid),
                graded                = VALUES(graded),
                moodle_grade_item_id  = VALUES(moodle_grade_item_id),
                last_sync_at          = VALUES(last_sync_at)
        ";

        $stmt = self::$db->prepare($sql);
        return $stmt->execute([
            ':id_programacion_ud'   => (int)$d['id_programacion_ud'],
            ':nro_calificacion'     => (int)$d['nro_calificacion'],
            ':evaluacion_detalle'   => (string)$d['evaluacion_detalle'],
            ':criterio_orden'       => (int)$d['criterio_orden'],
            ':moodle_course_id'     => (int)$d['moodle_course_id'],
            ':moodle_cmid'          => (int)$d['moodle_cmid'],
            ':graded'               => (int)($d['graded'] ?? 0),
            ':moodle_grade_item_id' => isset($d['moodle_grade_item_id']) ? (int)$d['moodle_grade_item_id'] : null,
            ':last_sync_at'         => $d['last_sync_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    public function getByProgramacion(int $id_programacion_ud, int $nro_calificacion): array
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE id_programacion_ud = ? AND nro_calificacion = ?
                ORDER BY evaluacion_detalle ASC, criterio_orden ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion_ud, $nro_calificacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getOne(int $id_programacion_ud, int $nro_calificacion, string $evaluacion_detalle, int $criterio_orden): ?array
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE id_programacion_ud = ? AND nro_calificacion = ? AND evaluacion_detalle = ? AND criterio_orden = ?
                LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion_ud, $nro_calificacion, $evaluacion_detalle, $criterio_orden]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function desvincular(int $id_programacion_ud, int $nro_calificacion, string $evaluacion_detalle, int $criterio_orden): bool
    {
        $sql = "UPDATE {$this->table}
                SET moodle_cmid = 0, graded = 0, moodle_grade_item_id = NULL, last_sync_at = NULL
                WHERE id_programacion_ud = ? AND nro_calificacion = ? AND evaluacion_detalle = ? AND criterio_orden = ?
                LIMIT 1";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id_programacion_ud, $nro_calificacion, $evaluacion_detalle, $criterio_orden]);
    }

    /**
     * Libera un cmid de Moodle de cualquier otro criterio en el curso.
     * Útil para la lógica de "reasignación" de actividades.
     */
    public function liberarCmidEnCurso(int $id_programacion_ud, int $moodle_cmid)
    {
        // Actualizamos los registros que tengan este cmid, poniéndolos a 0 o null
        $sql = "UPDATE {$this->table} 
                SET moodle_cmid = 0, 
                    graded = 0, 
                    moodle_grade_item_id = NULL, 
                    last_sync_at = NULL 
                WHERE id_programacion_ud = ? AND moodle_cmid = ?";
                
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id_programacion_ud, $moodle_cmid]);
    }

    public function getOneByDetalleYOrden($id_programacion_ud, $nro_calificacion, $detalle_criterio, $criterio_orden){
        
        $sql = "SELECT *
                FROM {$this->table}
                WHERE id_programacion_ud = ? AND nro_calificacion = ? AND evaluacion_detalle = ? AND criterio_orden = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion_ud, $nro_calificacion, $detalle_criterio, $criterio_orden]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
