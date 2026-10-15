<?php

namespace App\Models\Evaluacion;

require_once __DIR__ . '/../Evaluacion/Banco.php';

use Core\Model;
use PDO;

class Encuesta extends Model
{
    /**
     * Obtiene el listado de encuestas con conteo de evaluaciones
     */
    public function listar($id_periodo = null, $id_programa = null)
    {
        $db = self::getDB();
        $sql = "SELECT e.*, 
                       p.nombre AS periodo_nombre,
                       prg.nombre AS programa_nombre,
                       prg.codigo AS programa_codigo,
                       u.apellidos_nombres AS creador_nombre,
                       COUNT(DISTINCT ee.id) AS total_asignadas,
                       COUNT(DISTINCT CASE WHEN ee.estado = 'COMPLETADO' THEN ee.id END) AS total_completadas,
                       COUNT(DISTINCT CASE WHEN ee.estado = 'PENDIENTE' THEN ee.id END) AS total_pendientes
                FROM eva_encuestas e
                INNER JOIN sigi_periodo_academico p ON e.id_periodo_academico = p.id
                LEFT JOIN sigi_programa_estudios prg ON e.id_programa_estudios = prg.id
                LEFT JOIN sigi_usuarios u ON e.creado_por = u.id
                LEFT JOIN eva_evaluaciones_estudiante ee ON ee.id_encuesta = e.id
                LEFT JOIN sigi_unidad_didactica ud ON ee.id_unidad_didactica = ud.id
                LEFT JOIN sigi_semestre sem ON ud.id_semestre = sem.id
                LEFT JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
                LEFT JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
                WHERE 1=1";

        $params = [];
        if ($id_periodo) {
            $sql .= " AND e.id_periodo_academico = ?";
            $params[] = (int)$id_periodo;
        }
        if ($id_programa) {
            $sql .= " AND (e.id_programa_estudios = ? OR (e.id_programa_estudios IS NULL AND pe.id_programa_estudios = ?))";
            $params[] = (int)$id_programa;
            $params[] = (int)$id_programa;
        }

        $sql .= " GROUP BY e.id ORDER BY e.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una encuesta por ID con sus datos relacionados
     */
    public function obtenerPorId($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT e.*, p.nombre AS periodo_nombre, prg.nombre AS programa_nombre
                              FROM eva_encuestas e
                              INNER JOIN sigi_periodo_academico p ON e.id_periodo_academico = p.id
                              LEFT JOIN sigi_programa_estudios prg ON e.id_programa_estudios = prg.id
                              WHERE e.id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una encuesta y opcionalmente puebla la rúbrica estándar MINEDU
     */
    public function crear($data, $conRubricaEstandar = true)
    {
        $db = self::getDB();
        $db->beginTransaction();

        try {
            $sql = "INSERT INTO eva_encuestas (
                        titulo, descripcion, id_periodo_academico, id_sede,
                        id_programa_estudios, fecha_inicio, fecha_fin, estado,
                        escala_min, escala_max, creado_por
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['titulo'],
                $data['descripcion'] ?? 'Encuesta oficial de evaluación del desempeño docente por los estudiantes.',
                (int)$data['id_periodo_academico'],
                !empty($data['id_sede']) ? (int)$data['id_sede'] : null,
                !empty($data['id_programa_estudios']) ? (int)$data['id_programa_estudios'] : null,
                $data['fecha_inicio'],
                $data['fecha_fin'],
                $data['estado'] ?? 'ACTIVA',
                $data['escala_min'] ?? 1,
                $data['escala_max'] ?? 5,
                (int)$data['creado_por']
            ]);

            $idEncuesta = (int)$db->lastInsertId();

            if ($conRubricaEstandar) {
                $this->insertarRubricaEstandar($idEncuesta, $db);
            }

            $db->commit();
            return $idEncuesta;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza datos básicos de la encuesta
     */
    public function actualizar($id, $data)
    {
        $db = self::getDB();
        $sql = "UPDATE eva_encuestas SET 
                    titulo = ?, 
                    descripcion = ?, 
                    fecha_inicio = ?, 
                    fecha_fin = ?, 
                    estado = ?
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'] ?? '',
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['estado'],
            (int)$id
        ]);
    }

    /**
     * Elimina encuesta si no tiene respuestas registradas
     */
    public function eliminar($id)
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM eva_encuestas WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Genera automáticamente las evaluaciones para los estudiantes matriculados
     * en el periodo académico de la encuesta.
     */
    public function generarEvaluacionesParaEstudiantes($idEncuesta)
    {
        $db = self::getDB();
        $encuesta = $this->obtenerPorId($idEncuesta);
        if (!$encuesta) {
            return ['ok' => false, 'error' => 'Encuesta no encontrada.'];
        }

        $idPeriodo  = (int)$encuesta['id_periodo_academico'];
        $idPrograma = !empty($encuesta['id_programa_estudios']) ? (int)$encuesta['id_programa_estudios'] : null;
        $idSede     = !empty($encuesta['id_sede']) ? (int)$encuesta['id_sede'] : null;

        // Query para encontrar todas las relaciones estudiante-docente-unidad didáctica activas en el periodo
        // Se une con acad_estudiante_programa (ep) para obtener ep.id_usuario (ID de sigi_usuarios del estudiante)
        // y garantizar que el estudiante pertenezca al programa de estudios programado.
        $sql = "
            SELECT DISTINCT 
                ep.id_usuario AS id_estudiante,
                pud.id_docente,
                pud.id AS id_programacion_ud,
                pud.id_unidad_didactica,
                m.id_periodo_academico,
                m.id_sede
            FROM acad_matricula m
            INNER JOIN acad_estudiante_programa ep ON ep.id = m.id_estudiante
            INNER JOIN sigi_planes_estudio pl_est ON ep.id_plan_estudio = pl_est.id
            INNER JOIN acad_detalle_matricula dm ON dm.id_matricula = m.id
            INNER JOIN acad_programacion_unidad_didactica pud ON dm.id_programacion_ud = pud.id
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
            INNER JOIN sigi_modulo_formativo mf ON sem.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
            WHERE m.id_periodo_academico = ?
              AND (m.licencia IS NULL OR m.licencia = '' OR m.licencia = 0)
        ";

        $params = [$idPeriodo];

        if ($idPrograma) {
            $sql .= " AND pl_est.id_programa_estudios = ? AND pe.id_programa_estudios = ?";
            $params[] = $idPrograma;
            $params[] = $idPrograma;
        } else {
            // Si es encuesta institucional, la carrera del estudiante debe coincidir con la carrera de la UD
            $sql .= " AND pl_est.id_programa_estudios = pe.id_programa_estudios";
        }

        if ($idSede) {
            $sql .= " AND m.id_sede = ?";
            $params[] = $idSede;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($asignaciones)) {
            return ['ok' => false, 'error' => 'No se encontraron matrículas activas con docentes programados para los criterios seleccionados.'];
        }

        // Insertar ignorando duplicados
        $sqlInsert = "
            INSERT IGNORE INTO eva_evaluaciones_estudiante 
            (id_encuesta, id_estudiante, id_docente, id_programacion_ud, id_unidad_didactica, id_periodo_academico, id_sede, estado, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW())
        ";

        $stmtInsert = $db->prepare($sqlInsert);
        $insertados = 0;

        foreach ($asignaciones as $asig) {
            $stmtInsert->execute([
                $idEncuesta,
                (int)$asig['id_estudiante'],
                (int)$asig['id_docente'],
                (int)$asig['id_programacion_ud'],
                (int)$asig['id_unidad_didactica'],
                (int)$asig['id_periodo_academico'],
                (int)$asig['id_sede']
            ]);
            if ($stmtInsert->rowCount() > 0) {
                $insertados++;
            }
        }

        return [
            'ok' => true,
            'total_encontrados' => count($asignaciones),
            'nuevos_asignados' => $insertados
        ];
    }

    /**
     * Inserta la rúbrica de evaluación docente institucional desde el Banco Maestro
     */
    private function insertarRubricaEstandar($idEncuesta, $db)
    {
        // 1. Intentar importar desde el Banco Maestro si existen registros
        $stmtCheckBanco = $db->query("SELECT COUNT(*) FROM eva_banco_criterios WHERE estado = 1");
        $totalBanco = (int)$stmtCheckBanco->fetchColumn();

        if ($totalBanco > 0) {
            $bancoModel = new \App\Models\Evaluacion\Banco();
            $bancoModel->copiarBancoAEncuesta($idEncuesta);
            return;
        }

        // 2. Si el banco está vacío, usar la rúbrica base predeterminada
        $rubrica = [
            [
                'nombre' => 'Planificación y Dominio de la Asignatura',
                'descripcion' => 'Evaluación de la preparación, puntualidad, dominio del tema y cumplimiento del sílabo.',
                'peso' => 1.00,
                'preguntas' => [
                    'Presenta y explica claramente los propósitos, contenidos y sistema de evaluación de la unidad didáctica (sílabo).',
                    'Demuestra dominio y conocimiento profundo y actualizado de los temas desarrollados.',
                    'Inicia y culmina sus sesiones puntualmente según el horario programado.'
                ]
            ],
            [
                'nombre' => 'Metodología y Estrategias Didácticas',
                'descripcion' => 'Uso de métodos pedagógicos, tecnologías, recursos y motivación al estudiante.',
                'peso' => 1.00,
                'preguntas' => [
                    'Explica las clases con claridad, dinamismo y utiliza ejemplos prácticos aplicados a la profesión.',
                    'Emplea adecuadamente recursos didácticos, plataformas digitales, tecnologías y material de apoyo.',
                    'Promueve la participación activa, el trabajo colaborativo y la formulación de preguntas en clase.'
                ]
            ],
            [
                'nombre' => 'Evaluación y Retroalimentación',
                'descripcion' => 'Criterios claros, entrega oportuna de calificaciones y acompañamiento en el aprendizaje.',
                'peso' => 1.00,
                'preguntas' => [
                    'Aplica instrumentos de evaluación claros y coherentes con los aprendizajes esperados.',
                    'Entrega y da a conocer oportunamente los resultados de las evaluaciones y tareas.',
                    'Brinda retroalimentación (orientación constructiva) para superar las dificultades académicas.'
                ]
            ],
            [
                'nombre' => 'Responsabilidad y Relaciones Interpersonales',
                'descripcion' => 'Ética profesional, respeto mutuo, empatía y fomento del clima institucional.',
                'peso' => 1.00,
                'preguntas' => [
                    'Mantiene un trato respetuoso, cordial, equitativo y sin discriminación hacia todos los estudiantes.',
                    'Demuestra disposición, accesibilidad y paciencia para atender consultas y dudas académicas.',
                    'Fomenta valores éticos, puntualidad y responsabilidad profesional.'
                ]
            ]
        ];

        $stmtCrit = $db->prepare("INSERT INTO eva_criterios (id_encuesta, nombre, descripcion, peso, orden) VALUES (?, ?, ?, ?, ?)");
        $stmtPreg = $db->prepare("INSERT INTO eva_preguntas (id_encuesta, id_criterio, enunciado, tipo_respuesta, es_obligatoria, orden) VALUES (?, ?, ?, 'ESCALA', 1, ?)");

        $ordenCrit = 1;
        $ordenPreg = 1;

        foreach ($rubrica as $crit) {
            $stmtCrit->execute([
                $idEncuesta,
                $crit['nombre'],
                $crit['descripcion'],
                $crit['peso'],
                $ordenCrit++
            ]);
            $idCriterio = (int)$db->lastInsertId();

            foreach ($crit['preguntas'] as $enunciado) {
                $stmtPreg->execute([
                    $idEncuesta,
                    $idCriterio,
                    $enunciado,
                    $ordenPreg++
                ]);
            }
        }
    }
}
