<?php

namespace App\Models\Bolsa;
use Core\Model;
use PDO;
class Postulaciones extends Model {

    protected $table = 'bolsa_postulaciones';

    // Constantes de estados de postulación admitidos por el sistema
    const ESTADO_PENDIENTE       = 1; // Registrado / En revisión inicial
    const ESTADO_EN_EVALUACION   = 2; // Preseleccionado / En evaluación
    const ESTADO_SELECCIONADO    = 3; // Aceptado / Seleccionado
    const ESTADO_NO_SELECCIONADO = 4; // Descartado / No seleccionado

    /**
     * Retorna array asociativo con etiquetas y clases visuales de los estados
     */
    public static function getEstados()
    {
        return [
            self::ESTADO_PENDIENTE => [
                'id'     => 1,
                'nombre' => 'Pendiente',
                'badge'  => 'badge-warning',
                'icono'  => 'fas fa-clock',
                'color'  => '#f39c12'
            ],
            self::ESTADO_EN_EVALUACION => [
                'id'     => 2,
                'nombre' => 'En Evaluación',
                'badge'  => 'badge-info',
                'icono'  => 'fas fa-user-check',
                'color'  => '#17a2b8'
            ],
            self::ESTADO_SELECCIONADO => [
                'id'     => 3,
                'nombre' => 'Seleccionado',
                'badge'  => 'badge-success',
                'icono'  => 'fas fa-check-circle',
                'color'  => '#28a745'
            ],
            self::ESTADO_NO_SELECCIONADO => [
                'id'     => 4,
                'nombre' => 'No Seleccionado',
                'badge'  => 'badge-danger',
                'icono'  => 'fas fa-times-circle',
                'color'  => '#dc3545'
            ]
        ];
    }

    /**
     * Obtiene los postulantes de una oferta laboral con paginación server-side para DataTables
     */
    public function ObtenerPostulacionesPorOferta($idOferta, $length, $start, $orderCol, $orderDir, $filtroEstado = null)
    {
        $columns = [
            0 => 'p.id',
            1 => 'u.dni',
            2 => 'u.apellidos_nombres',
            3 => 'pr.nombre',
            4 => 'u.telefono',
            5 => 'p.fecha',
            6 => 'p.estado',
            7 => 'p.id'
        ];
        $orderColumnName = $columns[$orderCol] ?? 'p.fecha';
        $orderDir = strtolower($orderDir) === 'asc' ? 'ASC' : 'DESC';
        $where = ['p.id_oferta_laboral = :id_oferta'];
        $params = [':id_oferta' => (int)$idOferta];
        if (!empty($filtroEstado)) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = (int)$filtroEstado;
        }
        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT 
                    p.id,
                    p.id_usuario,
                    p.id_oferta_laboral,
                    p.id_curriculum,
                    p.tipo_curriculum,
                    p.fecha,
                    p.estado,
                    u.dni,
                    u.apellidos_nombres,
                    u.telefono,
                    u.correo,
                    pr.nombre AS programa_nombre,
                    cv.archivo AS archivo_cv,
                    cv.descripcion AS cv_descripcion
                FROM {$this->table} p
                INNER JOIN sigi_usuarios u ON u.id = p.id_usuario
                LEFT JOIN sigi_programa_estudios pr ON pr.id = u.id_programa_estudios
                LEFT JOIN bolsa_curriculum cv ON cv.id = p.id_curriculum
                {$sqlWhere}
                ORDER BY {$orderColumnName} {$orderDir}
                LIMIT :start, :length";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Formatear nombres limpios si tienen guiones bajos
        foreach ($data as &$row) {
            if (strpos($row['apellidos_nombres'], '_') !== false) {
                $p = explode('_', trim($row['apellidos_nombres']));
                $row['postulante_nombre'] = trim(($p[0] ?? '') . ' ' . ($p[1] ?? '') . ' ' . ($p[2] ?? ''));
            } else {
                $row['postulante_nombre'] = trim($row['apellidos_nombres']);
            }
        }

        // Conteo del total filtrado
        $sqlTotal = "SELECT COUNT(*) FROM {$this->table} p {$sqlWhere}";
        $totalStmt = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $totalStmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $totalStmt->execute();
        $total = (int)$totalStmt->fetchColumn();
        return ['data' => $data, 'total' => $total];
    }

    /**
     * Obtiene el listado de todas las postulaciones realizadas por un estudiante
     */
    public function obtenerMisPostulaciones($idUsuario)
    {
        $sql = "SELECT 
                    p.id,
                    p.id_oferta_laboral,
                    p.id_curriculum,
                    p.tipo_curriculum,
                    p.fecha,
                    p.estado,
                    o.titulo AS oferta_titulo,
                    o.salario AS oferta_salario,
                    o.ubicacion AS oferta_ubicacion,
                    o.tipo_contrato AS oferta_tipo_contrato,
                    o.fecha_publicacion,
                    o.fecha_cierre,
                    o.estado AS oferta_estado,
                    e.empresa AS empresa_nombre,
                    cv.descripcion AS cv_descripcion,
                    cv.archivo AS cv_archivo
                FROM {$this->table} p
                INNER JOIN bolsa_ofertas_laborales o ON o.id = p.id_oferta_laboral
                LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                LEFT JOIN bolsa_curriculum cv ON cv.id = p.id_curriculum
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.fecha DESC";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las postulaciones registradas en el sistema para vista del Administrador
     */
    public function obtenerTodasPostulaciones(array $filtros = [])
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filtros['id_oferta'])) {
            $where[] = 'p.id_oferta_laboral = :id_oferta';
            $params[':id_oferta'] = (int)$filtros['id_oferta'];
        }
        if (!empty($filtros['estado'])) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = (int)$filtros['estado'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = 'o.id_empresa = :id_empresa';
            $params[':id_empresa'] = (int)$filtros['id_empresa'];
        }
        $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT 
                    p.id,
                    p.id_usuario,
                    p.id_oferta_laboral,
                    p.id_curriculum,
                    p.tipo_curriculum,
                    p.fecha,
                    p.estado,
                    o.id_empresa,
                    o.titulo AS oferta_titulo,
                    o.fecha_cierre AS oferta_fecha_cierre,
                    e.empresa AS empresa_nombre,
                    u.dni,
                    u.apellidos_nombres,
                    u.telefono,
                    u.correo,
                    pr.nombre AS programa_nombre,
                    cv.archivo AS cv_archivo,
                    cv.descripcion AS cv_descripcion
                FROM {$this->table} p
                INNER JOIN bolsa_ofertas_laborales o ON o.id = p.id_oferta_laboral
                LEFT JOIN bolsa_empresa e ON e.id = o.id_empresa
                INNER JOIN sigi_usuarios u ON u.id = p.id_usuario
                LEFT JOIN sigi_programa_estudios pr ON pr.id = u.id_programa_estudios
                LEFT JOIN bolsa_curriculum cv ON cv.id = p.id_curriculum
                {$sqlWhere}
                ORDER BY p.fecha DESC";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as &$row) {
            if (strpos($row['apellidos_nombres'], '_') !== false) {
                $p = explode('_', trim($row['apellidos_nombres']));
                $row['postulante_nombre'] = trim(($p[0] ?? '') . ' ' . ($p[1] ?? '') . ' ' . ($p[2] ?? ''));
            } else {
                $row['postulante_nombre'] = trim($row['apellidos_nombres']);
            }
        }
        return $data;
    }

    /**
     * Verifica si un estudiante ya postuló a una oferta laboral específica
     */
    public function yaPostulo($idOferta, $idUsuario)
    {
        $sql = "SELECT p.*, o.titulo AS oferta_titulo 
                FROM {$this->table} p
                INNER JOIN bolsa_ofertas_laborales o ON o.id = p.id_oferta_laboral
                WHERE p.id_oferta_laboral = :id_oferta 
                  AND p.id_usuario = :id_usuario 
                LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_oferta', (int)$idOferta, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda una nueva postulación laboral
     */
    public function guardar(array $data)
    {
        $sql = "INSERT INTO {$this->table} (
                    id_oferta_laboral, id_usuario, id_curriculum, tipo_curriculum, fecha, estado
                ) VALUES (
                    :id_oferta_laboral, :id_usuario, :id_curriculum, :tipo_curriculum, NOW(), :estado
                )";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_oferta_laboral', (int)$data['id_oferta_laboral'], PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', (int)$data['id_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':id_curriculum', (int)$data['id_curriculum'], PDO::PARAM_INT);
        $stmt->bindValue(':tipo_curriculum', (int)$data['tipo_curriculum'], PDO::PARAM_INT);
        $stmt->bindValue(':estado', isset($data['estado']) ? (int)$data['estado'] : self::ESTADO_PENDIENTE, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualiza el estado de una postulación (Administrador)
     */
    public function actualizarEstado($idPostulacion, $nuevoEstado)
    {
        $estadosPermitidos = [
            self::ESTADO_PENDIENTE,
            self::ESTADO_EN_EVALUACION,
            self::ESTADO_SELECCIONADO,
            self::ESTADO_NO_SELECCIONADO
        ];
        if (!in_array((int)$nuevoEstado, $estadosPermitidos, true)) {
            throw new \InvalidArgumentException('El estado especificado no es válido.');
        }
        $sql = "UPDATE {$this->table} SET estado = :estado WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':estado', (int)$nuevoEstado, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$idPostulacion, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Permite al estudiante cancelar su postulación si aún se encuentra en estado Pendiente
     */
    public function cancelarPostulacion($idPostulacion, $idUsuario)
    {
        // Solo se permite cancelar si el estado es 1 (Pendiente) y pertenece al usuario
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id 
                  AND id_usuario = :id_usuario 
                  AND estado = :estado";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', (int)$idPostulacion, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':estado', self::ESTADO_PENDIENTE, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna el conteo y métricas de postulaciones por estado
     */
    public function obtenerMetricas($idUsuario = null)
    {
        $where = $idUsuario ? "WHERE id_usuario = " . (int)$idUsuario : "";
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN estado = 2 THEN 1 ELSE 0 END) AS evaluacion,
                    SUM(CASE WHEN estado = 3 THEN 1 ELSE 0 END) AS seleccionados,
                    SUM(CASE WHEN estado = 4 THEN 1 ELSE 0 END) AS rechazados
                FROM {$this->table} {$where}";
        $stmt = self::$db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total'         => 0,
            'pendientes'    => 0,
            'evaluacion'    => 0,
            'seleccionados' => 0,
            'rechazados'    => 0
        ];
    }
}