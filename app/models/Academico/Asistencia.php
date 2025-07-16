<?php
namespace App\Models\Academico;

use Core\Model;
use PDO;

class Asistencia extends Model
{
    // Trae toda la información necesaria para la vista
    public function getDatosAsistencia($id_programacion_ud)
    {
        // 1. Unidad didáctica programada y nombre
        $stmt = self::$db->prepare("SELECT ud.nombre AS nombreUnidadDidactica, pa.fecha_fin, pa.id as id_periodo, pa.nombre as periodo
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
            WHERE pud.id = ?");
        $stmt->execute([$id_programacion_ud]);
        $rowUnidad = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Sesiones de aprendizaje (ordenadas)
        $stmt = self::$db->prepare("SELECT sa.id, sa.fecha_desarrollo, pa.semana
            FROM acad_sesion_aprendizaje sa
            INNER JOIN acad_programacion_actividades_silabo pa ON sa.id_prog_actividad_silabo = pa.id
            INNER JOIN acad_silabos s ON pa.id_silabo = s.id
            WHERE s.id_prog_unidad_didactica = ?
            ORDER BY pa.semana, sa.fecha_desarrollo, sa.id");
        $stmt->execute([$id_programacion_ud]);
        $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Estudiantes matriculados (orden por apellidos)
        $stmt = self::$db->prepare("SELECT u.id, u.dni, u.apellidos_nombres, dm.id as id_detalle_matricula, m.licencia as licencia
            FROM acad_detalle_matricula dm
            INNER JOIN acad_matricula m ON m.id = dm.id_matricula
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_usuarios u ON ep.id_usuario = u.id
            WHERE dm.id_programacion_ud = ?
            ORDER BY u.apellidos_nombres");
        $stmt->execute([$id_programacion_ud]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Asistencias
        $asistencias = [];
        if ($sesiones) {
            $ids_detalle = array_column($estudiantes, 'id_detalle_matricula');
            $ids_sesiones = array_column($sesiones, 'id');
            if ($ids_detalle && $ids_sesiones) {
                $place_dm = implode(',', array_fill(0, count($ids_detalle), '?'));
                $place_ses = implode(',', array_fill(0, count($ids_sesiones), '?'));
                $sql = "SELECT id_detalle_matricula, id_sesion_aprendizaje, asistencia FROM acad_asistencia
                        WHERE id_detalle_matricula IN ($place_dm) AND id_sesion_aprendizaje IN ($place_ses)";
                $stmt = self::$db->prepare($sql);
                $params = array_merge($ids_detalle, $ids_sesiones);
                $stmt->execute($params);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $asistencias[$row['id_detalle_matricula']][$row['id_sesion_aprendizaje']] = $row['asistencia'];
                }
            }
        }

        return [
            'nombreUnidadDidactica' => $rowUnidad['nombreUnidadDidactica'] ?? '',
            'periodo' => [
                'fecha_fin' => $rowUnidad['fecha_fin'] ?? '',
                'nombre' => $rowUnidad['periodo'] ?? '',
            ],
            'sesiones' => $sesiones,
            'estudiantes' => $estudiantes,
            'asistencias' => $asistencias,
        ];
    }

    public function getPeriodoByProgramacion($id_programacion_ud)
    {
        $stmt = self::$db->prepare("SELECT pa.fecha_fin FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_periodo_academico pa ON pud.id_periodo_academico = pa.id
            WHERE pud.id = ?");
        $stmt->execute([$id_programacion_ud]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Guarda todas las asistencias (en bloque)
    public function guardarAsistencia($id_programacion_ud, $asistencia)
    {
        self::$db->beginTransaction();
        try {
            // Buscar todas las sesiones vinculadas a esta programación
            $stmt = self::$db->prepare("SELECT sa.id
                FROM acad_sesion_aprendizaje sa
                INNER JOIN acad_programacion_actividades_silabo pa ON sa.id_prog_actividad_silabo = pa.id
                INNER JOIN acad_silabos s ON pa.id_silabo = s.id
                WHERE s.id_prog_unidad_didactica = ?");
            $stmt->execute([$id_programacion_ud]);
            $idsSesiones = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');

            // Actualizar o insertar cada asistencia
            foreach ($asistencia as $id_detalle_matricula => $arr) {
                foreach ($arr as $id_sesion => $valor) {
                    if (!in_array($id_sesion, $idsSesiones)) continue;
                    // Si existe actualiza, sino inserta
                    $stmtExist = self::$db->prepare("SELECT id FROM acad_asistencia WHERE id_detalle_matricula=? AND id_sesion_aprendizaje=?");
                    $stmtExist->execute([$id_detalle_matricula, $id_sesion]);
                    $idAsis = $stmtExist->fetchColumn();
                    if ($idAsis) {
                        $stmtUpd = self::$db->prepare("UPDATE acad_asistencia SET asistencia=? WHERE id=?");
                        $stmtUpd->execute([$valor, $idAsis]);
                    } else {
                        $stmtIns = self::$db->prepare("INSERT INTO acad_asistencia (id_sesion_aprendizaje, id_detalle_matricula, asistencia) VALUES (?, ?, ?)");
                        $stmtIns->execute([$id_sesion, $id_detalle_matricula, $valor]);
                    }
                }
            }
            self::$db->commit();
            return true;
        } catch (\Exception $e) {
            self::$db->rollBack();
            return false;
        }
    }
}
