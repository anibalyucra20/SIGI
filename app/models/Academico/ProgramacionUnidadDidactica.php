<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class ProgramacionUnidadDidactica extends Model
{
    protected $table = 'acad_programacion_unidad_didactica';

    // Devuelve los datos de la programación
    public function find($id)
    {
        $stmt = self::$db->prepare("SELECT * FROM acad_programacion_unidad_didactica WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'pud.id',
            1 => 'pr.nombre',
            2 => 'pl.nombre',
            3 => 'mf.descripcion',
            4 => 's.descripcion',
            5 => 'ud.nombre',
            6 => 'd.apellidos_nombres',
            7 => 'pud.turno',
            8 => 'pud.seccion',
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'pud.id';

        $where = [
            "pud.id_sede = :id_sede",
            "pud.id_periodo_academico = :id_periodo"
        ];
        $params = [
            ':id_sede' => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo']
        ];

        if (!empty($filters['programa'])) {
            $where[] = "pr.id = :programa";
            $params[':programa'] = $filters['programa'];
        }
        if (!empty($filters['plan'])) {
            $where[] = "pl.id = :plan";
            $params[':plan'] = $filters['plan'];
        }
        if (!empty($filters['modulo'])) {
            $where[] = "mf.id = :modulo";
            $params[':modulo'] = $filters['modulo'];
        }
        if (!empty($filters['semestre'])) {
            $where[] = "s.id = :semestre";
            $params[':semestre'] = $filters['semestre'];
        }
        if (!empty($filters['docente'])) {
            $where[] = "pud.id_docente = :docente";
            $params[':docente'] = $filters['docente'];
        }
        if (!empty($filters['turno'])) {
            $where[] = "pud.turno = :turno";
            $params[':turno'] = $filters['turno'];
        }
        if (!empty($filters['seccion'])) {
            $where[] = "pud.seccion = :seccion";
            $params[':seccion'] = $filters['seccion'];
        }
        if (!empty($filters['unidad'])) {
            $where[] = "ud.nombre LIKE :unidad";
            $params[':unidad'] = '%' . $filters['unidad'] . '%';
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT pud.id, 
                       pr.nombre AS programa_nombre,
                       pl.nombre AS plan_nombre,
                       mf.descripcion AS modulo_nombre,
                       s.descripcion AS semestre_nombre,
                       ud.nombre AS unidad_nombre, 
                       d.apellidos_nombres AS docente_nombre,
                       pud.turno, pud.seccion,
                       COUNT(dm.id) AS cantidad_detalle_matricula
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_usuarios d ON pud.id_docente = d.id
                INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
                INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                LEFT JOIN acad_detalle_matricula dm ON pud.id = dm.id_programacion_ud
                $sqlWhere
                GROUP BY 
                    pud.id, pr.nombre, pl.nombre, mf.descripcion, s.descripcion, ud.nombre, d.apellidos_nombres,
                    pud.turno, pud.seccion
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
                INNER JOIN sigi_usuarios d ON pud.id_docente = d.id
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
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['docente_nombre']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['docente_nombre'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return ['data' => $data, 'total' => $total];
    }




    // 1. Verifica existencia de programación duplicada (combinación única)
    public function existeProgramacion($id_unidad_didactica, $id_sede, $id_periodo_academico, $turno, $seccion)
    {
        $sql = "SELECT COUNT(*) FROM acad_programacion_unidad_didactica
            WHERE id_unidad_didactica = ? AND id_sede = ? AND id_periodo_academico = ? AND turno = ? AND seccion = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_unidad_didactica, $id_sede, $id_periodo_academico, $turno, $seccion]);
        return $stmt->fetchColumn() > 0;
    }

    // 2. Registra programación principal
    public function registrarProgramacion($data)
    {
        $sql = "INSERT INTO acad_programacion_unidad_didactica
            (id_unidad_didactica, id_docente, id_sede, id_periodo_academico, turno, seccion,
             supervisado, reg_evaluacion, reg_auxiliar, prog_curricular, otros,
             logros_obtenidos, dificultades, sugerencias, plantilla_silabo, plantilla_sesion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_unidad_didactica'],
            $data['id_docente'],
            $data['id_sede'],
            $data['id_periodo_academico'],
            $data['turno'],
            $data['seccion'],
            $data['supervisado'],
            $data['reg_evaluacion'],
            $data['reg_auxiliar'],
            $data['prog_curricular'],
            $data['otros'],
            $data['logros_obtenidos'],
            $data['dificultades'],
            $data['sugerencias'],
            $data['plantilla_silabo'],
            $data['plantilla_sesion']
        ]);
        return self::$db->lastInsertId();
    }

    // 3. Registra sílabo
    // en el modelo de silabos.php


    // 4. Devuelve cantidad de semanas (sigi_datos_sistema.cant_semanas)
    // desde modelo DatosSistenma.php

    // 5. Devuelve primer id_ind_logro_capacidad según unidad didáctica
    //desde modelo Indicador logro capacidad

    // 6. Registra actividad silabo
    // DESDE MODELO SILABOS

    // 7. Devuelve id_modulo_formativo de la unidad didáctica


    // Trae los datos completos para la edición (incluyendo campos para mostrar en el formulario)
    public function getProgramacionById($id)
    {
        $sql = "SELECT pud.*, 
                   ud.nombre AS unidad_nombre,
                   s.descripcion AS semestre_nombre,
                   mf.descripcion AS modulo_nombre,
                   pl.nombre AS plan_nombre,
                   pr.nombre AS programa_nombre
            FROM acad_programacion_unidad_didactica pud
            INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
            INNER JOIN sigi_semestre s ON ud.id_semestre = s.id
            INNER JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
            INNER JOIN sigi_planes_estudio pl ON mf.id_plan_estudio = pl.id
            INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
            WHERE pud.id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualiza solo el docente asignado
    public function actualizarDocente($id_programacion, $id_docente)
    {
        $sql = "UPDATE acad_programacion_unidad_didactica SET id_docente = ? WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id_docente, $id_programacion]);
    }
    public function actualizarIdMoodle($id_programacion, $id_moodle)
    {
        $sql = "UPDATE acad_programacion_unidad_didactica SET id_moodle = ? WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id_moodle, $id_programacion]);
    }

    public function getIdDocente($id_programacion)
    {
        $stmt = self::$db->prepare("SELECT id_docente FROM acad_programacion_unidad_didactica WHERE id = ?");
        $stmt->execute([$id_programacion]);
        return $stmt->fetchColumn();
    }

    public function puedeVerCalificaciones($id_programacion)
    {
        // Supón que el rol de Administrador Académico es 1 (ajusta según tu sistema)
        $idUsuario = $_SESSION['sigi_user_id'] ?? 0;
        $rolActual = $_SESSION['sigi_rol_actual'] ?? 0;

        // Administrador académico
        if ($rolActual == 1) return true;

        // Docente encargado de la programación
        $stmt = self::$db->prepare("SELECT id_docente FROM acad_programacion_unidad_didactica WHERE id = ?");
        $stmt->execute([$id_programacion]);
        $idDocente = $stmt->fetchColumn();

        if ($idDocente && $idDocente == $idUsuario) return true;

        // (Puedes añadir aquí otras reglas, como coordinadores, etc.)
        return false;
    }


    public function eliminarProgramacionCompleta(int $id_programacion_ud): bool
    {
        // 1) Bloqueo si hay matrículas (FK acad_detalle_matricula.id_programacion_ud)
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM acad_detalle_matricula WHERE id_programacion_ud = ?");
        $stmt->execute([$id_programacion_ud]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }

        self::$db->beginTransaction();
        try {
            // 2) Obtener IDs de silabos asociados
            $stmt = self::$db->prepare("SELECT id FROM acad_silabos WHERE id_prog_unidad_didactica = ?");
            $stmt->execute([$id_programacion_ud]);
            $silabos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($silabos)) {
                // 3) Obtener IDs de actividades del sílabo
                $inSilabos = implode(',', array_fill(0, count($silabos), '?'));
                $stmt = self::$db->prepare("SELECT id FROM acad_programacion_actividades_silabo WHERE id_silabo IN ($inSilabos)");
                $stmt->execute($silabos);
                $acts = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($acts)) {
                    $inActs = implode(',', array_fill(0, count($acts), '?'));

                    // 4) Obtener IDs de sesiones
                    $stmt = self::$db->prepare("SELECT id FROM acad_sesion_aprendizaje WHERE id_prog_actividad_silabo IN ($inActs)");
                    $stmt->execute($acts);
                    $sesiones = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($sesiones)) {
                        $inSes = implode(',', array_fill(0, count($sesiones), '?'));

                        // 5) Eliminar dependientes de sesión
                        $stmt = self::$db->prepare("DELETE FROM acad_actividad_evaluacion_sesion_aprendizaje WHERE id_sesion_aprendizaje IN ($inSes)");
                        $stmt->execute($sesiones);

                        $stmt = self::$db->prepare("DELETE FROM acad_momentos_sesion_aprendizaje WHERE id_sesion_aprendizaje IN ($inSes)");
                        $stmt->execute($sesiones);

                        $stmt = self::$db->prepare("DELETE FROM acad_asistencia WHERE id_sesion_aprendizaje IN ($inSes)");
                        $stmt->execute($sesiones);

                        // (si tienes más tablas hijas de acad_sesion_aprendizaje, agrégalas aquí)
                    }

                    // 6) Eliminar sesiones
                    $stmt = self::$db->prepare("DELETE FROM acad_sesion_aprendizaje WHERE id_prog_actividad_silabo IN ($inActs)");
                    $stmt->execute($acts);

                    // 7) Eliminar actividades del silabo
                    $stmt = self::$db->prepare("DELETE FROM acad_programacion_actividades_silabo WHERE id_silabo IN ($inSilabos)");
                    $stmt->execute($silabos);
                }

                // 8) Eliminar silabos
                $stmt = self::$db->prepare("DELETE FROM acad_silabos WHERE id_prog_unidad_didactica = ?");
                $stmt->execute([$id_programacion_ud]);
            }

            // 9) Finalmente eliminar la programación
            $stmt = self::$db->prepare("DELETE FROM acad_programacion_unidad_didactica WHERE id = ?");
            $stmt->execute([$id_programacion_ud]);

            self::$db->commit();
            return true;
        } catch (\Throwable $e) {
            self::$db->rollBack();
            throw $e;
        }
    }



    // Info básica de la UD de una programación
    public function getInfoBasicaUD(int $id_programacion): ?array
    {
        $sql = "SELECT 
                    pud.id,
                    pud.id_sede,
                    ud.id AS id_ud,
                    ud.nombre AS unidad_nombre,
                    s.descripcion AS semestre_nombre,
                    mf.nro_modulo,
                    ple.nombre AS plan_nombre,
                    pe.nombre AS programa_nombre
                FROM acad_programacion_unidad_didactica pud
                JOIN sigi_unidad_didactica ud ON ud.id = pud.id_unidad_didactica
                JOIN sigi_semestre s ON s.id = ud.id_semestre
                JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio ple ON ple.id = mf.id_plan_estudio
                JOIN sigi_programa_estudios pe ON pe.id = ple.id_programa_estudios
                WHERE pud.id = ?";
        $st = self::$db->prepare($sql);
        $st->execute([$id_programacion]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Programaciones candidatas: misma sede actual y mismo nombre de la UD
    public function getCandidatasMismoNombreYSede(int $id_prog_dest, int $id_sede_actual): array
    {
        // Obtener nombre de UD destino
        $info = $this->getInfoBasicaUD($id_prog_dest);
        if (!$info) return [];
        $sql = "SELECT 
                    pud.id,
                    pa.nombre AS periodo,
                    pa.fecha_inicio,
                    u.apellidos_nombres AS docente,
                    ple.nombre AS plan_nombre,
                    pe.nombre AS programa_nombre,
                    mf.nro_modulo,
                    s.descripcion AS semestre_nombre,
                    ud.nombre AS unidad_nombre,
                    se.nombre AS sede_nombre,
                    sil.id AS id_silabo,
                    (SELECT COUNT(*) FROM acad_programacion_actividades_silabo pas WHERE pas.id_silabo = sil.id) AS actividades,
                    (SELECT COUNT(*) 
                       FROM acad_sesion_aprendizaje sa
                       JOIN acad_programacion_actividades_silabo pas2 ON pas2.id = sa.id_prog_actividad_silabo
                      WHERE pas2.id_silabo = sil.id) AS sesiones
                FROM acad_programacion_unidad_didactica pud
                JOIN sigi_unidad_didactica ud ON ud.id = pud.id_unidad_didactica
                JOIN sigi_semestre s ON s.id = ud.id_semestre
                JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
                JOIN sigi_planes_estudio ple ON ple.id = mf.id_plan_estudio
                JOIN sigi_programa_estudios pe ON pe.id = ple.id_programa_estudios
                JOIN sigi_periodo_academico pa ON pa.id = pud.id_periodo_academico
                JOIN sigi_sedes se ON se.id = pud.id_sede
                JOIN sigi_usuarios u ON u.id = pud.id_docente
                LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
                WHERE pud.id <> :dest
                  AND pud.id_sede = :sede
                  AND ud.nombre COLLATE utf8mb3_spanish2_ci = :ud_nombre
                ORDER BY pa.fecha_inicio DESC, pa.id DESC";
        $st = self::$db->prepare($sql);
        $st->execute([
            ':dest' => $id_prog_dest,
            ':sede' => $id_sede_actual,
            ':ud_nombre' => $info['unidad_nombre'],
        ]);
        $st = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($st as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['docente']));
            $st[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $st[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $st[$key]['Nombres'] = $apellidos_nombres[2];
            $st[$key]['docente'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $st;
    }

    private function candidatasOrderableColumns(): array
    {
        // Índices según la tabla que definimos en la vista:
        // 0 Sel (no ordenable)
        // 1 Periodo
        // 2 Docente
        // 3 Plan
        // 4 Módulo
        // 5 Semestre
        // 6 UD
        // 7 Sede
        // 8 Sílabo (Sí/No) -> ordenamos por id_silabo IS NOT NULL
        // 9 #Activ.
        //10 #Ses.
        return [
            1 => 'pa.fecha_inicio',          // o pa.nombre
            2 => 'u.apellidos_nombres',
            3 => 'ple.nombre',
            4 => 'mf.nro_modulo',
            5 => 's.descripcion',
            6 => 'ud.nombre',
            7 => 'se.nombre',
            8 => 'sil.id',                  // null/valor
            9 => 'actividades',
            10 => 'sesiones',
        ];
    }

    public function getCandidatasPaged(int $id_prog_dest, int $id_sede_actual, array $filters, int $start, int $length, int $orderIdx, string $orderDir): array
    {
        $info = $this->getInfoBasicaUD($id_prog_dest);
        if (!$info) {
            return ['rows' => [], 'total' => 0, 'filtered' => 0];
        }

        $baseFrom = "
            FROM acad_programacion_unidad_didactica pud
            JOIN sigi_unidad_didactica ud ON ud.id = pud.id_unidad_didactica
            JOIN sigi_semestre s ON s.id = ud.id_semestre
            JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
            JOIN sigi_planes_estudio ple ON ple.id = mf.id_plan_estudio
            JOIN sigi_programa_estudios pe ON pe.id = ple.id_programa_estudios
            JOIN sigi_periodo_academico pa ON pa.id = pud.id_periodo_academico
            JOIN sigi_sedes se ON se.id = pud.id_sede
            JOIN sigi_usuarios u ON u.id = pud.id_docente
            LEFT JOIN acad_silabos sil ON sil.id_prog_unidad_didactica = pud.id
        ";

        $where = " WHERE pud.id <> :dest AND pud.id_sede = :sede AND ud.nombre COLLATE utf8mb3_spanish2_ci = :ud_nombre ";

        $params = [
            ':dest' => $id_prog_dest,
            ':sede' => $id_sede_actual,
            ':ud_nombre' => $info['unidad_nombre'],
        ];

        if (!empty($filters['programa_id'])) {
            $where .= " AND pe.id = :programa_id ";
            $params[':programa_id'] = $filters['programa_id'];
        }
        if (!empty($filters['plan_id'])) {
            $where .= " AND ple.id = :plan_id ";
            $params[':plan_id'] = $filters['plan_id'];
        }
        if (!empty($filters['modulo_id'])) {
            $where .= " AND mf.id = :modulo_id ";
            $params[':modulo_id'] = $filters['modulo_id'];
        }
        if (!empty($filters['semestre_id'])) {
            $where .= " AND s.id = :semestre_id ";
            $params[':semestre_id'] = $filters['semestre_id'];
        }

        // Total (sin filtros)
        $sqlTotal = "SELECT COUNT(*) " . $baseFrom . " WHERE pud.id <> :dest AND pud.id_sede = :sede AND ud.nombre COLLATE utf8mb3_spanish2_ci = :ud_nombre";
        $stT = self::$db->prepare($sqlTotal);
        $stT->execute([
            ':dest' => $id_prog_dest,
            ':sede' => $id_sede_actual,
            ':ud_nombre' => $info['unidad_nombre'],
        ]);
        $total = (int)$stT->fetchColumn();

        // Filtrado (con filtros)
        $sqlFiltered = "SELECT COUNT(*) " . $baseFrom . $where;
        $stF = self::$db->prepare($sqlFiltered);
        $stF->execute($params);
        $filtered = (int)$stF->fetchColumn();

        // Orden seguro
        $orderCols = $this->candidatasOrderableColumns();
        $orderBy = $orderCols[$orderIdx] ?? 'pa.fecha_inicio';
        $orderSql = " ORDER BY $orderBy $orderDir, pa.id DESC ";

        // Data con agregados (#activ, #ses)
        $sqlData = "
            SELECT 
                pud.id,
                pa.nombre AS periodo,
                pa.fecha_inicio,
                u.apellidos_nombres AS docente,
                ple.id AS plan_id, ple.nombre AS plan_nombre,
                mf.id AS modulo_id, mf.nro_modulo,
                s.id AS semestre_id, s.descripcion AS semestre_nombre,
                ud.nombre AS unidad_nombre,
                pe.nombre AS programa_nombre,
                se.nombre AS sede_nombre,
                sil.id AS id_silabo,
                (SELECT COUNT(*) FROM acad_programacion_actividades_silabo pas WHERE pas.id_silabo = sil.id) AS actividades,
                (SELECT COUNT(*) 
                   FROM acad_sesion_aprendizaje sa
                   JOIN acad_programacion_actividades_silabo pas2 ON pas2.id = sa.id_prog_actividad_silabo
                  WHERE pas2.id_silabo = sil.id) AS sesiones
            " . $baseFrom . $where . $orderSql . " LIMIT :start, :length";

        $st = self::$db->prepare($sqlData);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':start', $start, PDO::PARAM_INT);
        $st->bindValue(':length', $length, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['docente']));
            $rows[$key]['docente'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }

        // Adaptar algunos campos para DataTables (checkbox/radio se arma en la vista)
        foreach ($rows as &$r) {
            $r['tiene_silabo'] = $r['id_silabo'] ? 1 : 0;
        }

        return ['rows' => $rows, 'total' => $total, 'filtered' => $filtered];
    }

    public function getCandidatasFilterOptions(int $id_prog_dest, int $id_sede_actual): array
    {
        $info = $this->getInfoBasicaUD($id_prog_dest);
        if (!$info) return ['programas' => [], 'planes' => [], 'modulos' => [], 'semestres' => []];

        $base = "
            FROM acad_programacion_unidad_didactica pud
            JOIN sigi_unidad_didactica ud ON ud.id = pud.id_unidad_didactica
            JOIN sigi_semestre s ON s.id = ud.id_semestre
            JOIN sigi_modulo_formativo mf ON mf.id = s.id_modulo_formativo
            JOIN sigi_planes_estudio ple ON ple.id = mf.id_plan_estudio
            JOIN sigi_programa_estudios pe ON pe.id = ple.id_programa_estudios
        ";
        $where = " WHERE pud.id <> :dest AND pud.id_sede = :sede AND ud.nombre COLLATE utf8mb3_spanish2_ci = :ud_nombre ";
        $params = [':dest' => $id_prog_dest, ':sede' => $id_sede_actual, ':ud_nombre' => $info['unidad_nombre']];

        // Programas
        $q1 = self::$db->prepare("SELECT DISTINCT pe.id, pe.nombre $base $where ORDER BY pe.nombre");
        $q1->execute($params);
        $programas = $q1->fetchAll(PDO::FETCH_ASSOC);

        // Planes
        $q2 = self::$db->prepare("SELECT DISTINCT ple.id, ple.nombre $base $where ORDER BY ple.nombre");
        $q2->execute($params);
        $planes = $q2->fetchAll(PDO::FETCH_ASSOC);

        // Módulos
        $q3 = self::$db->prepare("SELECT DISTINCT mf.id, mf.nro_modulo $base $where ORDER BY mf.nro_modulo");
        $q3->execute($params);
        $modulos = $q3->fetchAll(PDO::FETCH_ASSOC);

        // Semestres
        $q4 = self::$db->prepare("SELECT DISTINCT s.id, s.descripcion $base $where ORDER BY s.descripcion");
        $q4->execute($params);
        $semestres = $q4->fetchAll(PDO::FETCH_ASSOC);

        return compact('programas', 'planes', 'modulos', 'semestres');
    }

    //============= datos para vinculacion con moodle por programacion
    public function obtenerJerarquiaCompletaPorProgramacion($id_programacion)
    {
        $sql = "SELECT 
                    prog.id AS id_programacion,
                    prog.turno,
                    prog.seccion,
                    prog.id_docente,
                    -- usuario docente
                    us.moodle_user_id,
                    us.microsoft_user_id,
                    -- Periodo
                    per.id AS id_periodo,
                    per.nombre AS nombre_periodo,
                    -- Sede
                    sede.id AS id_sede,
                    sede.nombre AS nombre_sede,
                    -- Programa
                    pr.id AS id_programa,
                    pr.nombre AS nombre_programa,
                    pr.codigo AS codigo_programa, 
                    -- Plan
                    pl.id AS id_plan,
                    pl.nombre AS nombre_plan,
                    -- Modulo
                    modf.id AS id_modulo,
                    modf.descripcion AS nombre_modulo,
                    modf.nro_modulo,
                    -- Semestre
                    sem.id AS id_semestre,
                    sem.descripcion AS nombre_semestre,
                    -- Unidad Didactica (Curso)
                    ud.id AS id_ud,
                    ud.nombre AS nombre_ud
                FROM acad_programacion_unidad_didactica prog
                INNER JOIN sigi_usuarios us ON prog.id_docente = us.id
                INNER JOIN sigi_periodo_academico per ON prog.id_periodo_academico = per.id
                INNER JOIN sigi_sedes sede ON prog.id_sede = sede.id
                INNER JOIN sigi_unidad_didactica ud ON prog.id_unidad_didactica = ud.id
                INNER JOIN sigi_semestre sem ON ud.id_semestre = sem.id
                INNER JOIN sigi_modulo_formativo modf ON sem.id_modulo_formativo = modf.id
                INNER JOIN sigi_planes_estudio pl ON modf.id_plan_estudio = pl.id
                INNER JOIN sigi_programa_estudios pr ON pl.id_programa_estudios = pr.id
                WHERE prog.id = :id_programacion
                ORDER BY 
                    sede.nombre,
                    pr.nombre,
                    pl.nombre,
                    modf.nro_modulo,
                    sem.descripcion,
                    prog.turno,
                    prog.seccion";
        $db = self::getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([':id_programacion' => $id_programacion]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
