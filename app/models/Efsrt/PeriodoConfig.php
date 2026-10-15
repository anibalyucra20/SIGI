<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class PeriodoConfig extends Model
{
    public function getAll($id_periodo = null, $id_sede = null)
    {
        $sql = "SELECT pc.*, 
                    p.nombre AS periodo_nombre,
                    s.nombre AS sede_nombre,
                    pr.nombre AS programa_nombre,
                    pr.codigo AS programa_codigo,
                    u.apellidos_nombres AS configurado_por_nombre
                FROM efsrt_periodo_config pc
                INNER JOIN sigi_periodo_academico p ON pc.id_periodo_academico = p.id
                INNER JOIN sigi_sedes s ON pc.id_sede = s.id
                LEFT JOIN sigi_programa_estudios pr ON pc.id_programa_estudios = pr.id
                LEFT JOIN sigi_usuarios u ON pc.configurado_por = u.id
                WHERE 1=1 ";
        $params = [];
        if (!empty($id_periodo)) {
            $sql .= " AND pc.id_periodo_academico = ? ";
            $params[] = (int)$id_periodo;
        }
        if (!empty($id_sede)) {
            $sql .= " AND pc.id_sede = ? ";
            $params[] = (int)$id_sede;
        }
        $sql .= " ORDER BY pc.id DESC";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            if (!empty($r['configurado_por_nombre'])) {
                $r['configurado_por_nombre'] = \Core\Auth::formatearNombreUsuario($r['configurado_por_nombre']);
            }
        }

        return $rows;
    }

    public function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_periodo_config WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la configuración vigente para un periodo, sede y programa de estudios.
     * Implementa búsqueda jerárquica:
     * 1. Periodo + Sede + Programa específico
     * 2. Periodo + Sede + General (todos los programas)
     * 3. Periodo + Programa específico (cualquier sede)
     * 4. Periodo + General institucional
     */
    public function getConfigVigente($id_periodo, $id_sede = null, $id_programa = null)
    {
        if (empty($id_periodo)) {
            return null;
        }

        $id_periodo = (int)$id_periodo;
        $id_sede = !empty($id_sede) ? (int)$id_sede : null;
        $id_programa = !empty($id_programa) ? (int)$id_programa : null;

        // 1. Específica: Periodo + Sede + Programa
        if ($id_sede && $id_programa) {
            $sql = "SELECT * FROM efsrt_periodo_config 
                    WHERE id_periodo_academico = ? AND id_sede = ? AND id_programa_estudios = ? AND estado = 'HABILITADO'
                    ORDER BY id DESC LIMIT 1";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute([$id_periodo, $id_sede, $id_programa]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        }

        // 2. Sede específica + General (todos los programas)
        if ($id_sede) {
            $sql = "SELECT * FROM efsrt_periodo_config 
                    WHERE id_periodo_academico = ? AND id_sede = ? AND (id_programa_estudios IS NULL OR id_programa_estudios = 0) AND estado = 'HABILITADO'
                    ORDER BY id DESC LIMIT 1";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute([$id_periodo, $id_sede]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        }

        // 3. Programa específico en el periodo (si sede no coincide o es nula)
        if ($id_programa) {
            $sql = "SELECT * FROM efsrt_periodo_config 
                    WHERE id_periodo_academico = ? AND id_programa_estudios = ? AND estado = 'HABILITADO'
                    ORDER BY id DESC LIMIT 1";
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute([$id_periodo, $id_programa]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        }

        // 4. Configuración general del periodo (cualquier sede / programa)
        $sql = "SELECT * FROM efsrt_periodo_config 
                WHERE id_periodo_academico = ? AND estado = 'HABILITADO'
                ORDER BY id DESC LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([$id_periodo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Alias de getConfigVigente para obtener el cronograma activo
     */
    public function getActivo($id_periodo, $id_sede = null, $id_programa = null)
    {
        return $this->getConfigVigente($id_periodo, $id_sede, $id_programa);
    }

    /**
     * Verifica si la fecha actual está dentro del rango habilitado para el Estudiante.
     * Retorna boolean (true si está abierto, false si está cerrado).
     */
    public function estaAbiertoEstudiante($id_periodo, $id_sede = null, $id_programa = null)
    {
        $cfg = $this->getConfigVigente($id_periodo, $id_sede, $id_programa);
        if (!$cfg) {
            // Si aún no se ha programado restricción en el calendario, por defecto está habilitado
            return true;
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $cfg['fecha_apertura_estudiante']) {
            return false;
        }

        if ($now > $cfg['fecha_cierre_estudiante']) {
            return !empty($cfg['permite_extemporaneo']);
        }

        return true;
    }

    /**
     * Verifica si la fecha actual está dentro del rango habilitado para el Supervisor.
     * Retorna boolean (true si está abierto, false si está cerrado).
     */
    public function estaAbiertoSupervisor($id_periodo, $id_sede = null, $id_programa = null)
    {
        $cfg = $this->getConfigVigente($id_periodo, $id_sede, $id_programa);
        if (!$cfg) {
            // Si aún no se ha programado restricción en el calendario, por defecto está habilitado
            return true;
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $cfg['fecha_apertura_supervisor']) {
            return false;
        }

        if ($now > $cfg['fecha_cierre_supervisor']) {
            return !empty($cfg['permite_extemporaneo']);
        }

        return true;
    }

    /**
     * Método save unificado para guardar o actualizar la configuración
     */
    public function save($datos)
    {
        if (!empty($datos['id'])) {
            return $this->actualizar($datos['id'], $datos);
        }

        // Si no viene id explícito, verificar si ya existe configuración para este Periodo + Sede + Programa
        $id_prog = !empty($datos['id_programa_estudios']) ? (int)$datos['id_programa_estudios'] : null;
        $sql = "SELECT id FROM efsrt_periodo_config 
                WHERE id_periodo_academico = :per AND id_sede = :sede ";
        if ($id_prog !== null) {
            $sql .= " AND id_programa_estudios = :prog";
        } else {
            $sql .= " AND (id_programa_estudios IS NULL OR id_programa_estudios = 0)";
        }
        $sql .= " LIMIT 1";

        $stmt = self::getDB()->prepare($sql);
        $params = [
            ':per'  => (int)$datos['id_periodo_academico'],
            ':sede' => (int)$datos['id_sede']
        ];
        if ($id_prog !== null) {
            $params[':prog'] = $id_prog;
        }
        $stmt->execute($params);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            return $this->actualizar($existente['id'], $datos);
        }

        return $this->guardar($datos);
    }

    public function guardar($datos)
    {
        $sql = "INSERT INTO efsrt_periodo_config (
                    id_periodo_academico, id_sede, id_programa_estudios, 
                    fecha_apertura_estudiante, fecha_cierre_estudiante, 
                    fecha_apertura_supervisor, fecha_cierre_supervisor, 
                    permite_extemporaneo, estado, configurado_por
                ) VALUES (
                    :id_periodo_academico, :id_sede, :id_programa_estudios, 
                    :fecha_apertura_estudiante, :fecha_cierre_estudiante, 
                    :fecha_apertura_supervisor, :fecha_cierre_supervisor, 
                    :permite_extemporaneo, :estado, :configurado_por
                )";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([
            ':id_periodo_academico' => (int)$datos['id_periodo_academico'],
            ':id_sede' => (int)$datos['id_sede'],
            ':id_programa_estudios' => !empty($datos['id_programa_estudios']) ? (int)$datos['id_programa_estudios'] : null,
            ':fecha_apertura_estudiante' => $datos['fecha_apertura_estudiante'],
            ':fecha_cierre_estudiante' => $datos['fecha_cierre_estudiante'],
            ':fecha_apertura_supervisor' => $datos['fecha_apertura_supervisor'],
            ':fecha_cierre_supervisor' => $datos['fecha_cierre_supervisor'],
            ':permite_extemporaneo' => isset($datos['permite_extemporaneo']) ? (int)$datos['permite_extemporaneo'] : 0,
            ':estado' => $datos['estado'] ?? 'HABILITADO',
            ':configurado_por' => (int)$datos['configurado_por']
        ]);
        return self::getDB()->lastInsertId();
    }

    public function actualizar($id, $datos)
    {
        $sql = "UPDATE efsrt_periodo_config SET 
                    id_periodo_academico = :id_periodo_academico,
                    id_sede = :id_sede,
                    id_programa_estudios = :id_programa_estudios,
                    fecha_apertura_estudiante = :fecha_apertura_estudiante,
                    fecha_cierre_estudiante = :fecha_cierre_estudiante,
                    fecha_apertura_supervisor = :fecha_apertura_supervisor,
                    fecha_cierre_supervisor = :fecha_cierre_supervisor,
                    permite_extemporaneo = :permite_extemporaneo,
                    estado = :estado
                WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute([
            ':id' => (int)$id,
            ':id_periodo_academico' => (int)$datos['id_periodo_academico'],
            ':id_sede' => (int)$datos['id_sede'],
            ':id_programa_estudios' => !empty($datos['id_programa_estudios']) ? (int)$datos['id_programa_estudios'] : null,
            ':fecha_apertura_estudiante' => $datos['fecha_apertura_estudiante'],
            ':fecha_cierre_estudiante' => $datos['fecha_cierre_estudiante'],
            ':fecha_apertura_supervisor' => $datos['fecha_apertura_supervisor'],
            ':fecha_cierre_supervisor' => $datos['fecha_cierre_supervisor'],
            ':permite_extemporaneo' => isset($datos['permite_extemporaneo']) ? (int)$datos['permite_extemporaneo'] : 0,
            ':estado' => $datos['estado'] ?? 'HABILITADO'
        ]);
    }

    public function cambiarEstado($id, $estado)
    {
        $stmt = self::getDB()->prepare("UPDATE efsrt_periodo_config SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, (int)$id]);
    }

    public function eliminar($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM efsrt_periodo_config WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
