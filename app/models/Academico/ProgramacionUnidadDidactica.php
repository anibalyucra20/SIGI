<?php
namespace App\Models\Academico;

use Core\Model;
use PDO;

class ProgramacionUnidadDidactica extends Model
{
    protected $table = 'acad_programacion_unidad_didactica';

    /**
     * Obtener registros paginados y filtrados para DataTables server-side
     */
    public function getDataTable($params, $periodo_academico_id)
    {
        $columns = [
            'pud.id',
            'ud.nombre',
            'd.apellidos_nombres',
            'pe.nombre',
            'mf.descripcion',
            's.descripcion',
            'pud.turno',
            'pud.seccion'
        ];
        
        $sql = "FROM acad_programacion_unidad_didactica pud
                JOIN sigi_unidad_didactica ud ON pud.id_unidad_didactica = ud.id
                JOIN sigi_usuarios d ON pud.id_docente = d.id
                JOIN sigi_semestre s ON ud.id_semestre = s.id
                JOIN sigi_modulo_formativo mf ON s.id_modulo_formativo = mf.id
                JOIN sigi_planes_estudio pe ON mf.id_plan_estudio = pe.id
                JOIN sigi_programa_estudios p ON pe.id_programa_estudios = p.id
                WHERE pud.id_periodo_academico = :periodo";

        $bindings = [':periodo' => $periodo_academico_id];

        // Filtros
        if (!empty($params['programa'])) {
            $sql .= " AND p.id = :programa";
            $bindings[':programa'] = $params['programa'];
        }
        if (!empty($params['plan'])) {
            $sql .= " AND pe.id = :plan";
            $bindings[':plan'] = $params['plan'];
        }
        if (!empty($params['modulo'])) {
            $sql .= " AND mf.id = :modulo";
            $bindings[':modulo'] = $params['modulo'];
        }
        if (!empty($params['semestre'])) {
            $sql .= " AND s.id = :semestre";
            $bindings[':semestre'] = $params['semestre'];
        }
        if (!empty($params['docente'])) {
            $sql .= " AND d.id = :docente";
            $bindings[':docente'] = $params['docente'];
        }
        if (!empty($params['unidad'])) {
            $sql .= " AND ud.id = :unidad";
            $bindings[':unidad'] = $params['unidad'];
        }
        if (!empty($params['search'])) {
            $sql .= " AND ud.nombre LIKE :search";
            $bindings[':search'] = '%' . $params['search'] . '%';
        }

        // Total records filtrados
        $stmt = self::$db->prepare("SELECT COUNT(*) $sql");
        $stmt->execute($bindings);
        $recordsFiltered = $stmt->fetchColumn();

        // Paginación
        $start = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;

        $stmt = self::$db->prepare("SELECT pud.*, 
                                           ud.nombre AS nombre_unidad_didactica,
                                           d.apellidos_nombres AS nombre_docente,
                                           p.nombre AS nombre_programa,
                                           pe.nombre AS nombre_plan,
                                           mf.descripcion AS nombre_modulo,
                                           s.descripcion AS nombre_semestre
                                    $sql
                                    ORDER BY pud.id DESC
                                    LIMIT $start, $length");
        $stmt->execute($bindings);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total records sin filtro
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM acad_programacion_unidad_didactica WHERE id_periodo_academico = :periodo");
        $stmt->execute([':periodo' => $periodo_academico_id]);
        $recordsTotal = $stmt->fetchColumn();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    // Métodos de filtros para select (AJAX)
    public function getProgramas() {
        return self::$db->query("SELECT id, nombre FROM sigi_programa_estudios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPlanes($programa = null) {
        $sql = "SELECT id, nombre FROM sigi_planes_estudio";
        if ($programa) {
            $sql .= " WHERE id_programa_estudios = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$programa]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getModulos($plan = null) {
        $sql = "SELECT id, descripcion FROM sigi_modulo_formativo";
        if ($plan) {
            $sql .= " WHERE id_plan_estudio = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$plan]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getSemestres($modulo = null) {
        $sql = "SELECT id, descripcion FROM sigi_semestre";
        if ($modulo) {
            $sql .= " WHERE id_modulo_formativo = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$modulo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDocentes() {
        $sql = "SELECT id, apellidos_nombres FROM sigi_usuarios WHERE id_rol = (SELECT id FROM sigi_roles WHERE nombre = 'DOCENTE')";
        return self::$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
