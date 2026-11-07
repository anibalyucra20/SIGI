<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Practica extends Model
{
    /**
     * Obtiene el listado de prácticas con soporte de filtros
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT p.*,
                    est.dni AS estudiante_dni,
                    est.apellidos_nombres AS estudiante_nombre,
                    est.correo AS estudiante_correo,
                    est.telefono AS estudiante_telefono,
                    est.id_sede AS estudiante_sede_id,
                    prg.nombre AS programa_nombre,
                    prg.codigo AS programa_codigo,
                    modf.descripcion AS modulo_nombre,
                    modf.nro_modulo AS modulo_nro,
                    emp.razon_social AS empresa_razon_social,
                    emp.ruc AS empresa_ruc,
                    emp.direccion AS empresa_direccion,
                    conv.numero_convenio,
                    doc.apellidos_nombres AS supervisor_nombre,
                    doc.dni AS supervisor_dni,
                    per.nombre AS periodo_nombre,
                    (SELECT COUNT(*) FROM efsrt_documentos docu WHERE docu.id_practica = p.id AND docu.estado_archivo != 'ELIMINADO_PURGADO') AS total_documentos,
                    (SELECT COUNT(*) FROM efsrt_documentos docu WHERE docu.id_practica = p.id AND docu.estado_validacion = 'APROBADO') AS total_docs_aprobados,
                    (SELECT COUNT(*) FROM efsrt_documentos docu WHERE docu.id_practica = p.id AND docu.estado_validacion = 'OBSERVADO') AS total_docs_observados,
                    (SELECT COUNT(*) FROM efsrt_documentos docu WHERE docu.id_practica = p.id AND docu.tipo_documento = 'FICHA_SUPERVISION') AS tiene_supervision,
                    cst.nro_constancia,
                    cst.nombre_archivo_descarga AS constancia_archivo,
                    cst.ruta_pdf AS constancia_ruta
                FROM efsrt_practicas p
                INNER JOIN sigi_usuarios est ON p.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prg ON p.id_programa_estudios = prg.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN efsrt_empresas emp ON p.id_empresa = emp.id
                INNER JOIN sigi_periodo_academico per ON p.id_periodo_academico = per.id
                LEFT JOIN efsrt_convenios conv ON p.id_convenio = conv.id
                LEFT JOIN efsrt_supervisores sup ON p.id_supervisor = sup.id
                LEFT JOIN sigi_usuarios doc ON sup.id_docente = doc.id
                LEFT JOIN efsrt_constancias cst ON p.id = cst.id_practica AND cst.estado = 'EMITIDA'
                WHERE 1=1 ";

        $params = [];

        if (!empty($filters['id_periodo'])) {
            $sql .= " AND p.id_periodo_academico = ? ";
            $params[] = (int)$filters['id_periodo'];
        }

        if (!empty($filters['id_programa'])) {
            $sql .= " AND p.id_programa_estudios = ? ";
            $params[] = (int)$filters['id_programa'];
        }

        if (!empty($filters['programas_in']) && is_array($filters['programas_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['programas_in']), '?'));
            $sql .= " AND p.id_programa_estudios IN ($placeholders) ";
            foreach ($filters['programas_in'] as $progId) {
                $params[] = (int)$progId;
            }
        }

        if (!empty($filters['id_estudiante'])) {
            $sql .= " AND p.id_estudiante = ? ";
            $params[] = (int)$filters['id_estudiante'];
        }

        if (!empty($filters['id_supervisor'])) {
            $sql .= " AND p.id_supervisor = ? ";
            $params[] = (int)$filters['id_supervisor'];
        }

        if (!empty($filters['id_docente'])) {
            $sql .= " AND sup.id_docente = ? ";
            $params[] = (int)$filters['id_docente'];
        }

        if (!empty($filters['id_empresa'])) {
            $sql .= " AND p.id_empresa = ? ";
            $params[] = (int)$filters['id_empresa'];
        }

        if (!empty($filters['id_modulo'])) {
            $sql .= " AND p.id_modulo_formativo = ? ";
            $params[] = (int)$filters['id_modulo'];
        }

        if (!empty($filters['estado'])) {
            if (is_array($filters['estado'])) {
                $placeholders = implode(',', array_fill(0, count($filters['estado']), '?'));
                $sql .= " AND p.estado IN ($placeholders) ";
                foreach ($filters['estado'] as $st) {
                    $params[] = $st;
                }
            } else {
                $sql .= " AND p.estado = ? ";
                $params[] = $filters['estado'];
            }
        }

        if (!empty($filters['busqueda'])) {
            $q = '%' . trim($filters['busqueda']) . '%';
            $sql .= " AND (est.apellidos_nombres LIKE ? OR est.dni LIKE ? OR emp.razon_social LIKE ? OR emp.ruc LIKE ?) ";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($r['estudiante_nombre'] ?? '');
            if (!empty($r['supervisor_nombre'])) {
                $r['supervisor_nombre'] = \Core\Auth::formatearNombreUsuario($r['supervisor_nombre']);
            }
        }
        return $rows;
    }

    /**
     * Obtiene una práctica por su ID con todo el detalle consolidado
     */
    public function getById($id)
    {
        $sql = "SELECT p.*,
                    est.dni AS estudiante_dni,
                    est.apellidos_nombres AS estudiante_nombre,
                    est.correo AS estudiante_correo,
                    est.telefono AS estudiante_telefono,
                    est.id_sede AS estudiante_sede_id,
                    prg.nombre AS programa_nombre,
                    prg.codigo AS programa_codigo,
                    plan.nombre AS plan_nombre,
                    modf.descripcion AS modulo_nombre,
                    modf.nro_modulo AS modulo_nro,
                    emp.razon_social AS empresa_razon_social,
                    emp.ruc AS empresa_ruc,
                    emp.direccion AS empresa_direccion,
                    emp.representante_legal AS empresa_representante,
                    emp.cargo_representante AS empresa_cargo_representante,
                    emp.contacto_telefono AS empresa_telefono,
                    emp.contacto_correo AS empresa_correo,
                    conv.numero_convenio,
                    conv.tipo_convenio,
                    conv.archivo_convenio_pdf,
                    sup.id_docente AS supervisor_docente_id,
                    doc.apellidos_nombres AS supervisor_nombre,
                    doc.dni AS supervisor_dni,
                    doc.correo AS supervisor_correo,
                    doc.telefono AS supervisor_telefono,
                    per.nombre AS periodo_nombre,
                    cst.nro_constancia,
                    cst.nombre_archivo_descarga AS constancia_archivo,
                    cst.ruta_pdf AS constancia_ruta,
                    cst.qr_token AS constancia_token,
                    cst.fecha_emision AS constancia_fecha
                FROM efsrt_practicas p
                INNER JOIN sigi_usuarios est ON p.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prg ON p.id_programa_estudios = prg.id
                LEFT JOIN sigi_planes_estudio plan ON p.id_plan_estudio = plan.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN efsrt_empresas emp ON p.id_empresa = emp.id
                INNER JOIN sigi_periodo_academico per ON p.id_periodo_academico = per.id
                LEFT JOIN efsrt_convenios conv ON p.id_convenio = conv.id
                LEFT JOIN efsrt_supervisores sup ON p.id_supervisor = sup.id
                LEFT JOIN sigi_usuarios doc ON sup.id_docente = doc.id
                LEFT JOIN efsrt_constancias cst ON p.id = cst.id_practica AND cst.estado = 'EMITIDA'
                WHERE p.id = ?";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($row['estudiante_nombre'] ?? '');
            if (!empty($row['supervisor_nombre'])) {
                $row['supervisor_nombre'] = \Core\Auth::formatearNombreUsuario($row['supervisor_nombre']);
            }
        }
        return $row;
    }

    /**
     * Verifica si el estudiante ya tiene una práctica activa o aprobada para el mismo módulo
     */
    public function verificarExistePractica($id_estudiante, $id_modulo_formativo, $exclude_id = null)
    {
        $sql = "SELECT id FROM efsrt_practicas WHERE id_estudiante = ? AND id_modulo_formativo = ? ";
        $params = [(int)$id_estudiante, (int)$id_modulo_formativo];
        if ($exclude_id) {
            $sql .= " AND id != ? ";
            $params[] = (int)$exclude_id;
        }
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear una nueva práctica / asignación de estudiante
     */
    public function create($data)
    {
        $db = self::getDB();
        $sql = "INSERT INTO efsrt_practicas (
                    id_estudiante, id_periodo_academico, id_programa_estudios, id_plan_estudio,
                    id_modulo_formativo, id_empresa, id_convenio, id_supervisor, area_desempeno,
                    fecha_inicio, fecha_fin, horas_requeridas, horas_convalidadas, estado, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            (int)$data['id_estudiante'],
            (int)$data['id_periodo_academico'],
            (int)$data['id_programa_estudios'],
            (int)$data['id_plan_estudio'],
            (int)$data['id_modulo_formativo'],
            (int)$data['id_empresa'],
            !empty($data['id_convenio']) ? (int)$data['id_convenio'] : null,
            !empty($data['id_supervisor']) ? (int)$data['id_supervisor'] : null,
            trim($data['area_desempeno']),
            $data['fecha_inicio'],
            $data['fecha_fin'],
            !empty($data['horas_requeridas']) ? (int)$data['horas_requeridas'] : 240,
            !empty($data['horas_convalidadas']) ? (int)$data['horas_convalidadas'] : 0,
            !empty($data['estado']) ? $data['estado'] : 'REGISTRADO'
        ]);

        return $db->lastInsertId();
    }

    /**
     * Actualizar una práctica existente
     */
    public function update($id, $data)
    {
        $db = self::getDB();
        $sql = "UPDATE efsrt_practicas SET 
                    id_empresa = ?,
                    id_convenio = ?,
                    id_supervisor = ?,
                    area_desempeno = ?,
                    fecha_inicio = ?,
                    fecha_fin = ?,
                    horas_requeridas = ?,
                    horas_convalidadas = ?,
                    calificacion_final = ?,
                    updated_at = NOW()
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            (int)$data['id_empresa'],
            !empty($data['id_convenio']) ? (int)$data['id_convenio'] : null,
            !empty($data['id_supervisor']) ? (int)$data['id_supervisor'] : null,
            trim($data['area_desempeno']),
            $data['fecha_inicio'],
            $data['fecha_fin'],
            (int)$data['horas_requeridas'],
            (int)$data['horas_convalidadas'],
            isset($data['calificacion_final']) && $data['calificacion_final'] !== '' ? (float)$data['calificacion_final'] : null,
            (int)$id
        ]);
    }

    /**
     * Cambiar el estado del expediente
     */
    public function actualizarEstado($id, $nuevo_estado, $calificacion = null, $horas_convalidadas = null)
    {
        $db = self::getDB();
        $fields = ["estado = ?", "updated_at = NOW()"];
        $params = [$nuevo_estado];

        if ($calificacion !== null && $calificacion !== '') {
            $fields[] = "calificacion_final = ?";
            $params[] = (float)$calificacion;
        }

        if ($horas_convalidadas !== null && $horas_convalidadas !== '') {
            $fields[] = "horas_convalidadas = ?";
            $params[] = (int)$horas_convalidadas;
        }

        $params[] = (int)$id;

        $sql = "UPDATE efsrt_practicas SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Asignar o cambiar de docente supervisor
     */
    public function asignarSupervisor($id_practica, $id_supervisor)
    {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE efsrt_practicas SET id_supervisor = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([(int)$id_supervisor, (int)$id_practica]);
    }

    /**
     * Obtener estadísticas para los dashboards según el rol y filtros
     */
    public function getEstadisticas($id_periodo = null, $id_programa = null, $id_docente = null, $id_estudiante = null, $programas_coordinador = [])
    {
        $db = self::getDB();
        $sql = "SELECT 
                    COUNT(*) AS total_practicas,
                    SUM(CASE WHEN p.estado = 'REGISTRADO' THEN 1 ELSE 0 END) AS total_registrados,
                    SUM(CASE WHEN p.estado = 'EN_PROCESO' THEN 1 ELSE 0 END) AS total_en_proceso,
                    SUM(CASE WHEN p.estado = 'DOCUMENTACION_SUBIDA' THEN 1 ELSE 0 END) AS total_por_revisar_supervisor,
                    SUM(CASE WHEN p.estado = 'OBSERVADO_SUPERVISOR' THEN 1 ELSE 0 END) AS total_obs_supervisor,
                    SUM(CASE WHEN p.estado IN ('APROBADO_SUPERVISOR', 'DERIVADO_COORDINADOR') THEN 1 ELSE 0 END) AS total_por_revisar_coordinador,
                    SUM(CASE WHEN p.estado = 'OBSERVADO_COORDINADOR' THEN 1 ELSE 0 END) AS total_obs_coordinador,
                    SUM(CASE WHEN p.estado IN ('APROBADO_COORDINADOR', 'DERIVADO_SECRETARIA') THEN 1 ELSE 0 END) AS total_por_revisar_secretaria,
                    SUM(CASE WHEN p.estado = 'OBSERVADO_SECRETARIA' THEN 1 ELSE 0 END) AS total_obs_secretaria,
                    SUM(CASE WHEN p.estado = 'FINALIZADO_EMITIDO' THEN 1 ELSE 0 END) AS total_finalizados
                FROM efsrt_practicas p
                LEFT JOIN efsrt_supervisores sup ON p.id_supervisor = sup.id
                WHERE 1=1 ";

        $params = [];

        if ($id_periodo) {
            $sql .= " AND p.id_periodo_academico = ? ";
            $params[] = (int)$id_periodo;
        }

        if ($id_programa) {
            $sql .= " AND p.id_programa_estudios = ? ";
            $params[] = (int)$id_programa;
        }

        if (!empty($programas_coordinador) && is_array($programas_coordinador)) {
            $ph = implode(',', array_fill(0, count($programas_coordinador), '?'));
            $sql .= " AND p.id_programa_estudios IN ($ph) ";
            foreach ($programas_coordinador as $progId) {
                $params[] = (int)$progId;
            }
        }

        if ($id_docente) {
            $sql .= " AND sup.id_docente = ? ";
            $params[] = (int)$id_docente;
        }

        if ($id_estudiante) {
            $sql .= " AND p.id_estudiante = ? ";
            $params[] = (int)$id_estudiante;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
