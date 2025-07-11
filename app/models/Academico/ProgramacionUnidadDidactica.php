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
                       pud.turno, pud.seccion
                FROM acad_programacion_unidad_didactica pud
                INNER JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                INNER JOIN sigi_usuarios d ON pud.id_docente = d.id
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
             logros_obtenidos, dificultades, sugerencias)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
}
