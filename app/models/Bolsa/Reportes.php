<?php

namespace App\Models\Bolsa;

use Core\Model;
use PDO;

class Reportes extends Model
{
    /**
     * Obtiene el listado consolidado de postulaciones según filtros para la vista y el PDF
     */
    public function obtenerPostulaciones(array $filtros = []): array
    {
        $db = $this->getDB();
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['id_oferta'])) {
            $where[] = 'p.id_oferta_laboral = :id_oferta';
            $params[':id_oferta'] = (int)$filtros['id_oferta'];
        }

        if (!empty($filtros['id_empresa'])) {
            $where[] = 'o.id_empresa = :id_empresa';
            $params[':id_empresa'] = (int)$filtros['id_empresa'];
        }

        if (!empty($filtros['id_programa'])) {
            $where[] = '(u.id_programa_estudios = :id_programa OR o.programa_estudio = :id_programa)';
            $params[':id_programa'] = (int)$filtros['id_programa'];
        }

        if (!empty($filtros['estado'])) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = (int)$filtros['estado'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = 'DATE(p.fecha) >= :fecha_inicio';
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $where[] = 'DATE(p.fecha) <= :fecha_fin';
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['busqueda'])) {
            $where[] = '(u.dni LIKE :busq OR u.apellidos_nombres LIKE :busq OR o.titulo LIKE :busq OR e.empresa LIKE :busq)';
            $params[':busq'] = '%' . trim($filtros['busqueda']) . '%';
        }

        $sqlWhere = implode(' AND ', $where);

        $sql = "SELECT 
                    p.id,
                    p.id_oferta_laboral,
                    p.id_usuario,
                    p.id_curriculum,
                    p.tipo_curriculum,
                    p.fecha,
                    p.estado,
                    u.dni,
                    u.apellidos_nombres,
                    u.telefono,
                    u.correo,
                    pr.nombre AS programa_nombre,
                    pr.codigo AS programa_codigo,
                    o.titulo AS oferta_titulo,
                    o.salario AS oferta_salario,
                    o.tipo_contrato AS oferta_tipo_contrato,
                    o.ubicacion AS oferta_ubicacion,
                    o.fecha_cierre AS oferta_fecha_cierre,
                    e.id AS id_empresa,
                    e.empresa AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    cv.descripcion AS cv_descripcion,
                    cv.archivo AS cv_archivo
                FROM bolsa_postulaciones p
                INNER JOIN sigi_usuarios u ON u.id = p.id_usuario
                LEFT JOIN sigi_programa_estudios pr ON pr.id = u.id_programa_estudios
                INNER JOIN bolsa_ofertas_laborales o ON o.id = p.id_oferta_laboral
                LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                LEFT JOIN bolsa_curriculum cv ON cv.id = p.id_curriculum
                WHERE {$sqlWhere}
                ORDER BY p.fecha DESC, u.apellidos_nombres ASC";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Métricas estadísticas cuantitativas de postulaciones
     */
    public function obtenerMetricasPostulaciones(array $filtros = []): array
    {
        $db = $this->getDB();
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['id_oferta'])) {
            $where[] = 'p.id_oferta_laboral = :id_oferta';
            $params[':id_oferta'] = (int)$filtros['id_oferta'];
        }

        if (!empty($filtros['id_empresa'])) {
            $where[] = 'o.id_empresa = :id_empresa';
            $params[':id_empresa'] = (int)$filtros['id_empresa'];
        }

        if (!empty($filtros['id_programa'])) {
            $where[] = '(u.id_programa_estudios = :id_programa OR o.programa_estudio = :id_programa)';
            $params[':id_programa'] = (int)$filtros['id_programa'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = 'DATE(p.fecha) >= :fecha_inicio';
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $where[] = 'DATE(p.fecha) <= :fecha_fin';
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sqlWhere = implode(' AND ', $where);

        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN p.estado = 1 THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN p.estado = 2 THEN 1 ELSE 0 END) AS evaluacion,
                    SUM(CASE WHEN p.estado = 3 THEN 1 ELSE 0 END) AS seleccionados,
                    SUM(CASE WHEN p.estado = 4 THEN 1 ELSE 0 END) AS no_seleccionados
                FROM bolsa_postulaciones p
                INNER JOIN sigi_usuarios u ON u.id = p.id_usuario
                INNER JOIN bolsa_ofertas_laborales o ON o.id = p.id_oferta_laboral
                WHERE {$sqlWhere}";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'            => (int)($res['total'] ?? 0),
            'pendientes'       => (int)($res['pendientes'] ?? 0),
            'evaluacion'       => (int)($res['evaluacion'] ?? 0),
            'seleccionados'    => (int)($res['seleccionados'] ?? 0),
            'no_seleccionados' => (int)($res['no_seleccionados'] ?? 0)
        ];
    }

    /**
     * Reporte consolidado de Convocatorias / Ofertas Laborales
     */
    public function obtenerOfertas(array $filtros = []): array
    {
        $db = $this->getDB();
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['id_empresa'])) {
            $where[] = 'o.id_empresa = :id_empresa';
            $params[':id_empresa'] = (int)$filtros['id_empresa'];
        }

        if (!empty($filtros['id_programa'])) {
            $where[] = 'o.programa_estudio = :id_programa';
            $params[':id_programa'] = (int)$filtros['id_programa'];
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '' && $filtros['estado'] !== null) {
            $where[] = 'o.estado = :estado';
            $params[':estado'] = (int)$filtros['estado'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = 'o.fecha_publicacion >= :fecha_inicio';
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $where[] = 'o.fecha_cierre <= :fecha_fin';
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sqlWhere = implode(' AND ', $where);

        $sql = "SELECT 
                    o.id,
                    o.id_empresa,
                    o.programa_estudio,
                    o.titulo,
                    o.detalle,
                    o.requisitos,
                    o.fecha_publicacion,
                    o.fecha_cierre,
                    o.salario,
                    o.ubicacion,
                    o.tipo_contrato,
                    o.foto,
                    o.estado,
                    e.empresa AS empresa_nombre,
                    e.ruc AS empresa_ruc,
                    e.telefono AS empresa_telefono,
                    pr.nombre AS programa_nombre,
                    pr.codigo AS programa_codigo,
                    (SELECT COUNT(*) FROM bolsa_postulaciones bp WHERE bp.id_oferta_laboral = o.id) AS total_postulantes,
                    (SELECT COUNT(*) FROM bolsa_postulaciones bp WHERE bp.id_oferta_laboral = o.id AND bp.estado = 3) AS total_seleccionados
                FROM bolsa_ofertas_laborales o
                LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                LEFT JOIN sigi_programa_estudios pr ON pr.id = o.programa_estudio
                WHERE {$sqlWhere}
                ORDER BY o.fecha_publicacion DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte consolidado de Empresas Aliadas
     */
    public function obtenerEmpresas(array $filtros = []): array
    {
        $db = $this->getDB();
        $where = ['1=1'];
        $params = [];

        if (isset($filtros['estado']) && $filtros['estado'] !== '' && $filtros['estado'] !== null) {
            $where[] = 'e.estado = :estado';
            $params[':estado'] = (int)$filtros['estado'];
        }

        $sqlWhere = implode(' AND ', $where);

        $sql = "SELECT 
                    e.id,
                    e.ruc,
                    e.empresa,
                    e.direccion,
                    e.telefono,
                    e.email,
                    e.estado,
                    e.fecha_registro,
                    (SELECT COUNT(*) FROM bolsa_ofertas_laborales bo WHERE bo.id_empresa = e.id) AS total_ofertas,
                    (SELECT COUNT(*) FROM bolsa_ofertas_laborales bo WHERE bo.id_empresa = e.id AND bo.estado = 1) AS ofertas_vigentes,
                    (SELECT COUNT(*) FROM bolsa_postulaciones bp 
                     INNER JOIN bolsa_ofertas_laborales bo ON bo.id = bp.id_oferta_laboral 
                     WHERE bo.id_empresa = e.id) AS total_candidatos
                FROM bolsa_empresa e
                WHERE {$sqlWhere}
                ORDER BY e.empresa ASC";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene detalle de una oferta laboral con sus postulantes para reporte individual
     */
    public function obtenerDetalleOfertaConPostulantes(int $idOferta): ?array
    {
        $db = $this->getDB();
        $stmt = $db->prepare("SELECT 
                                o.*,
                                e.empresa AS empresa_nombre,
                                e.ruc AS empresa_ruc,
                                e.direccion AS empresa_direccion,
                                e.telefono AS empresa_telefono,
                                e.email AS empresa_email,
                                pr.nombre AS programa_nombre
                              FROM bolsa_ofertas_laborales o
                              LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                              LEFT JOIN sigi_programa_estudios pr ON pr.id = o.programa_estudio
                              WHERE o.id = ?");
        $stmt->execute([$idOferta]);
        $oferta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oferta) {
            return null;
        }

        $stmtPost = $db->prepare("SELECT 
                                    p.id,
                                    p.fecha,
                                    p.estado,
                                    p.tipo_curriculum,
                                    u.dni,
                                    u.apellidos_nombres,
                                    u.telefono,
                                    u.correo,
                                    pr.nombre AS programa_nombre,
                                    cv.descripcion AS cv_descripcion,
                                    cv.archivo AS cv_archivo
                                  FROM bolsa_postulaciones p
                                  INNER JOIN sigi_usuarios u ON u.id = p.id_usuario
                                  LEFT JOIN sigi_programa_estudios pr ON pr.id = u.id_programa_estudios
                                  LEFT JOIN bolsa_curriculum cv ON cv.id = p.id_curriculum
                                  WHERE p.id_oferta_laboral = ?
                                  ORDER BY p.fecha DESC, u.apellidos_nombres ASC");
        $stmtPost->execute([$idOferta]);
        $postulantes = $stmtPost->fetchAll(PDO::FETCH_ASSOC);

        return [
            'oferta'      => $oferta,
            'postulantes' => $postulantes
        ];
    }

    /**
     * Construye etiquetas legibles para los metadatos del PDF
     */
    public function obtenerMetadatosFiltros(array $filtros): array
    {
        $db = $this->getDB();
        $meta = [
            'oferta'   => 'TODAS LAS CONVOCATORIAS',
            'empresa'  => 'TODAS LAS EMPRESAS',
            'programa' => 'TODAS LAS CARRERAS',
            'estado'   => 'TODOS LOS ESTADOS',
            'periodo'  => 'HISTÓRICO COMPLETO'
        ];

        if (!empty($filtros['id_oferta'])) {
            $stmt = $db->prepare("SELECT titulo FROM bolsa_ofertas_laborales WHERE id = ?");
            $stmt->execute([(int)$filtros['id_oferta']]);
            $val = $stmt->fetchColumn();
            if ($val) $meta['oferta'] = mb_strtoupper($val);
        }

        if (!empty($filtros['id_empresa'])) {
            $stmt = $db->prepare("SELECT empresa FROM bolsa_empresa WHERE id = ?");
            $stmt->execute([(int)$filtros['id_empresa']]);
            $val = $stmt->fetchColumn();
            if ($val) $meta['empresa'] = mb_strtoupper($val);
        }

        if (!empty($filtros['id_programa'])) {
            $stmt = $db->prepare("SELECT nombre FROM sigi_programa_estudios WHERE id = ?");
            $stmt->execute([(int)$filtros['id_programa']]);
            $val = $stmt->fetchColumn();
            if ($val) $meta['programa'] = mb_strtoupper($val);
        }

        if (!empty($filtros['estado'])) {
            $estados = [
                1 => 'PENDIENTE / EN REVISIÓN',
                2 => 'EN EVALUACIÓN',
                3 => 'SELECCIONADO',
                4 => 'NO SELECCIONADO'
            ];
            $meta['estado'] = $estados[(int)$filtros['estado']] ?? 'ESTADO ' . $filtros['estado'];
        }

        if (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin'])) {
            $fInicio = !empty($filtros['fecha_inicio']) ? date('d/m/Y', strtotime($filtros['fecha_inicio'])) : 'INICIO';
            $fFin    = !empty($filtros['fecha_fin']) ? date('d/m/Y', strtotime($filtros['fecha_fin'])) : 'ACTUAL';
            $meta['periodo'] = "DEL {$fInicio} AL {$fFin}";
        }

        return $meta;
    }
}
