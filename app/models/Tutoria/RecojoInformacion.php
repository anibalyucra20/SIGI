<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class RecojoInformacion extends Model
{
    protected $table = 'tutoria_recojo_informacion';

    /**
     * Catálogo estándar de preguntas/criterios para el diagnóstico del tutorando.
     */
    public static function getPlantillaEstandar(): array
    {
        return [
            1 => '¿Cuenta con equipo de cómputo (PC/Laptop) para sus actividades académicas?',
            2 => '¿Dispone de servicio de internet continuo en su lugar de residencia?',
            3 => '¿Trabaja actualmente para solventar sus estudios o gastos familiares?',
            4 => '¿Vive actualmente con sus padres o familiares directos?',
            5 => '¿Tiene personas dependientes económicamente a su cargo (hijos, padres)?',
            6 => '¿Presenta alguna condición de salud o necesidad educativa particular?',
            7 => '¿Manifiesta dificultades en técnicas de estudio, lectura u organización del tiempo?',
            8 => '¿Se siente satisfecho y motivado con el programa de estudios que cursa?'
        ];
    }

    /**
     * Obtiene los items de diagnóstico registrados para un tutorando.
     */
    public function obtenerPorTutorando($id_tutoria_estudiante)
    {
        $sql = "SELECT * FROM tutoria_recojo_informacion
                WHERE id_tutoria_estudiante = ?
                ORDER BY id ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_tutoria_estudiante]);
        $registrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si ya tiene registros guardados, devolverlos
        if (!empty($registrados)) {
            return $registrados;
        }

        // Si no tiene registros previos, devolver la plantilla por defecto con valor 0
        $plantilla = self::getPlantillaEstandar();
        $resultado = [];
        foreach ($plantilla as $key => $descripcion) {
            $resultado[] = [
                'id'                    => 0,
                'id_tutoria_estudiante' => $id_tutoria_estudiante,
                'descripcion'           => $descripcion,
                'valor'                 => 0
            ];
        }
        return $resultado;
    }

    /**
     * Guarda en lote los items de diagnóstico del tutorando.
     */
    public function guardarLote($id_tutoria_estudiante, array $items)
    {
        self::$db->beginTransaction();
        try {
            // Eliminar registros anteriores para este tutorando
            $del = self::$db->prepare("DELETE FROM tutoria_recojo_informacion WHERE id_tutoria_estudiante = ?");
            $del->execute([$id_tutoria_estudiante]);

            // Insertar nuevos registros
            $ins = self::$db->prepare("INSERT INTO tutoria_recojo_informacion 
                                       (id_tutoria_estudiante, descripcion, valor) 
                                       VALUES (?, ?, ?)");

            foreach ($items as $item) {
                $descripcion = trim($item['descripcion'] ?? '');
                $valor = (int)($item['valor'] ?? 0);
                if ($descripcion !== '') {
                    $ins->execute([$id_tutoria_estudiante, $descripcion, $valor]);
                }
            }

            self::$db->commit();
            return true;
        } catch (\Throwable $e) {
            self::$db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas agregadas de diagnóstico para una programación.
     */
    public function getResumenPorProgramacion($id_programacion)
    {
        $sql = "SELECT tri.descripcion,
                       SUM(CASE WHEN tri.valor = 1 THEN 1 ELSE 0 END) AS total_si,
                       SUM(CASE WHEN tri.valor = 0 THEN 1 ELSE 0 END) AS total_no,
                       COUNT(tri.id) AS total_evaluados
                FROM tutoria_recojo_informacion tri
                INNER JOIN tutoria_estudiantes te ON tri.id_tutoria_estudiante = te.id
                WHERE te.id_programacion_tutoria = ?
                GROUP BY tri.descripcion";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_programacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
