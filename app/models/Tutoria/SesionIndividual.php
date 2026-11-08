<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class SesionIndividual extends Model
{
    protected $table = 'tutoria_sesion_individual';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'si.id',
            1 => 'si.nro',
            2 => 'si.fecha_hora',
            3 => 'est.apellidos_nombres',
            4 => 'doc.apellidos_nombres',
            5 => 'si.motivo',
            6 => 'si.derivacion'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'si.fecha_hora';

        $where = [
            "tp.id_sede = :id_sede",
            "tp.id_periodo_academico = :id_periodo"
        ];
        $params = [
            ':id_sede'    => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo']
        ];

        if (!empty($filters['id_tutoria_estudiante'])) {
            $where[] = "si.id_tutoria_estudiante = :id_te";
            $params[':id_te'] = $filters['id_tutoria_estudiante'];
        }

        if (!empty($filters['id_programacion'])) {
            $where[] = "tp.id = :id_programacion";
            $params[':id_programacion'] = $filters['id_programacion'];
        }

        if (!empty($filters['id_docente'])) {
            $where[] = "tp.id_docente = :id_docente";
            $params[':id_docente'] = $filters['id_docente'];
        }

        if (isset($filters['derivacion']) && $filters['derivacion'] !== '') {
            $where[] = "si.derivacion = :derivacion";
            $params[':derivacion'] = (int)$filters['derivacion'];
        }

        if (!empty($filters['buscar'])) {
            $where[] = " (est.dni LIKE :b OR est.apellidos_nombres LIKE :b OR si.motivo LIKE :b) ";
            $params[':b'] = '%' . $filters['buscar'] . '%';
        }

        $sqlWhere = " WHERE " . implode(" AND ", $where);

        $sql = "SELECT si.*,
                       te.id_estudiante,
                       te.id_programacion_tutoria,
                       est.dni AS estudiante_dni,
                       est.apellidos_nombres AS estudiante_nombre,
                       doc.apellidos_nombres AS docente_nombre,
                       pe.nombre AS programa_nombre
                FROM tutoria_sesion_individual si
                INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                INNER JOIN sigi_usuarios est ON te.id_estudiante = est.id
                INNER JOIN sigi_usuarios doc ON tp.id_docente = doc.id
                LEFT JOIN sigi_programa_estudios pe ON est.id_programa_estudios = pe.id
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";

        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*)
                     FROM tutoria_sesion_individual si
                     INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                     INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                     INNER JOIN sigi_usuarios est ON te.id_estudiante = est.id
                     $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        foreach ($data as $key => $value) {
            $nEst = explode('_', trim($value['estudiante_nombre']));
            $data[$key]['estudiante_formato'] = trim(($nEst[0] ?? '') . ' ' . ($nEst[1] ?? '') . ', ' . ($nEst[2] ?? ''));

            $nDoc = explode('_', trim($value['docente_nombre']));
            $data[$key]['docente_formato'] = trim(($nDoc[0] ?? '') . ' ' . ($nDoc[1] ?? '') . ', ' . ($nDoc[2] ?? ''));
        }

        return ['data' => $data, 'total' => (int)$total];
    }

    public function listarPorTutorando($id_tutoria_estudiante)
    {
        $sql = "SELECT si.*
                FROM tutoria_sesion_individual si
                WHERE si.id_tutoria_estudiante = ?
                ORDER BY si.nro ASC, si.fecha_hora DESC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_tutoria_estudiante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "SELECT si.*,
                       te.id_estudiante,
                       te.id_programacion_tutoria,
                       est.dni AS estudiante_dni,
                       est.apellidos_nombres AS estudiante_nombre,
                       est.genero AS estudiante_genero,
                       est.fecha_nacimiento AS estudiante_fecha_nacimiento,
                       est.correo AS estudiante_correo,
                       est.telefono AS estudiante_telefono,
                       doc.dni AS docente_dni,
                       doc.apellidos_nombres AS docente_nombre,
                       pe.nombre AS programa_nombre,
                       tp.id_sede,
                       tp.id_periodo_academico,
                       tp.id_docente,
                       pa.nombre AS periodo_nombre,
                       s.nombre AS sede_nombre,
                       mat.semestre_desc AS semestre_nombre
                FROM tutoria_sesion_individual si
                INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                INNER JOIN sigi_usuarios est ON te.id_estudiante = est.id
                INNER JOIN sigi_usuarios doc ON tp.id_docente = doc.id
                INNER JOIN sigi_sedes s ON tp.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
                LEFT JOIN sigi_programa_estudios pe ON est.id_programa_estudios = pe.id
                LEFT JOIN (
                    SELECT m.id_estudiante, m.id_periodo_academico, MAX(s2.descripcion) AS semestre_desc
                    FROM acad_matricula m
                    INNER JOIN sigi_semestre s2 ON m.id_semestre = s2.id
                    GROUP BY m.id_estudiante, m.id_periodo_academico
                ) mat ON mat.id_estudiante = est.id AND mat.id_periodo_academico = tp.id_periodo_academico
                WHERE si.id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nEst = explode('_', trim($row['estudiante_nombre']));
            $row['estudiante_formato'] = trim(($nEst[0] ?? '') . ' ' . ($nEst[1] ?? '') . ', ' . ($nEst[2] ?? ''));

            $nDoc = explode('_', trim($row['docente_nombre']));
            $row['docente_formato'] = trim(($nDoc[0] ?? '') . ' ' . ($nDoc[1] ?? '') . ', ' . ($nDoc[2] ?? ''));

            $row['edad'] = '';
            if (!empty($row['estudiante_fecha_nacimiento']) && $row['estudiante_fecha_nacimiento'] !== '0000-00-00') {
                try {
                    $fn = new \DateTime($row['estudiante_fecha_nacimiento']);
                    $hoy = new \DateTime();
                    $row['edad'] = $hoy->diff($fn)->y;
                } catch (\Throwable $t) {
                    $row['edad'] = '';
                }
            }
        }

        return $row;
    }

    public function getSiguienteNumero($id_tutoria_estudiante)
    {
        $stmt = self::$db->prepare("SELECT COALESCE(MAX(nro), 0) + 1 FROM tutoria_sesion_individual WHERE id_tutoria_estudiante = ?");
        $stmt->execute([$id_tutoria_estudiante]);
        return (int)$stmt->fetchColumn();
    }

    public function guardar($data)
    {
        $fechaProx = !empty($data['fecha_prox_sesion']) ? $data['fecha_prox_sesion'] : '2000-01-01 00:00:00';

        if (!empty($data['id'])) {
            $sql = "UPDATE tutoria_sesion_individual SET
                        id_tutoria_estudiante = :id_te,
                        nro = :nro,
                        fecha_hora = :fecha_hora,
                        solicitante = :solicitante,
                        motivo = :motivo,
                        link_reunion = :link_reunion,
                        contenidos_tratados = :contenidos_tratados,
                        recomendaciones = :recomendaciones,
                        compromiso_estudiante = :compromiso_estudiante,
                        seguimiento = :seguimiento,
                        fecha_prox_sesion = :fecha_prox_sesion,
                        derivacion = :derivacion,
                        servicio_derivacion = :servicio_derivacion,
                        asistencia = :asistencia
                    WHERE id = :id";
            $params = [
                ':id_te'                  => $data['id_tutoria_estudiante'],
                ':nro'                    => $data['nro'],
                ':fecha_hora'             => $data['fecha_hora'],
                ':solicitante'            => $data['solicitante'],
                ':motivo'                 => $data['motivo'],
                ':link_reunion'           => $data['link_reunion'] ?? '',
                ':contenidos_tratados'    => $data['contenidos_tratados'] ?? '',
                ':recomendaciones'        => $data['recomendaciones'] ?? '',
                ':compromiso_estudiante'  => $data['compromiso_estudiante'] ?? '',
                ':seguimiento'            => (int)($data['seguimiento'] ?? 0),
                ':fecha_prox_sesion'      => $fechaProx,
                ':derivacion'             => (int)($data['derivacion'] ?? 0),
                ':servicio_derivacion'    => $data['servicio_derivacion'] ?? '',
                ':asistencia'             => (int)($data['asistencia'] ?? 1),
                ':id'                     => $data['id']
            ];
        } else {
            $sql = "INSERT INTO tutoria_sesion_individual
                        (id_tutoria_estudiante, nro, fecha_hora, solicitante, motivo, link_reunion, contenidos_tratados, recomendaciones, compromiso_estudiante, seguimiento, fecha_prox_sesion, derivacion, servicio_derivacion, asistencia)
                    VALUES
                        (:id_te, :nro, :fecha_hora, :solicitante, :motivo, :link_reunion, :contenidos_tratados, :recomendaciones, :compromiso_estudiante, :seguimiento, :fecha_prox_sesion, :derivacion, :servicio_derivacion, :asistencia)";
            $params = [
                ':id_te'                  => $data['id_tutoria_estudiante'],
                ':nro'                    => $data['nro'],
                ':fecha_hora'             => $data['fecha_hora'],
                ':solicitante'            => $data['solicitante'],
                ':motivo'                 => $data['motivo'],
                ':link_reunion'           => $data['link_reunion'] ?? '',
                ':contenidos_tratados'    => $data['contenidos_tratados'] ?? '',
                ':recomendaciones'        => $data['recomendaciones'] ?? '',
                ':compromiso_estudiante'  => $data['compromiso_estudiante'] ?? '',
                ':seguimiento'            => (int)($data['seguimiento'] ?? 0),
                ':fecha_prox_sesion'      => $fechaProx,
                ':derivacion'             => (int)($data['derivacion'] ?? 0),
                ':servicio_derivacion'    => $data['servicio_derivacion'] ?? '',
                ':asistencia'             => (int)($data['asistencia'] ?? 1)
            ];
        }

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        return !empty($data['id']) ? $data['id'] : self::$db->lastInsertId();
    }

    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM tutoria_sesion_individual WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contarPorPeriodoSede($id_periodo, $id_sede, $id_docente = null)
    {
        $sql = "SELECT COUNT(si.id)
                FROM tutoria_sesion_individual si
                INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                WHERE tp.id_periodo_academico = :id_periodo
                  AND tp.id_sede = :id_sede";
        $params = [
            ':id_periodo' => $id_periodo,
            ':id_sede'    => $id_sede
        ];

        if ($id_docente) {
            $sql .= " AND tp.id_docente = :id_docente";
            $params[':id_docente'] = $id_docente;
        }

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function listarDerivaciones($id_periodo, $id_sede, $id_docente = null)
    {
        $sql = "SELECT si.*,
                       est.dni AS estudiante_dni,
                       est.apellidos_nombres AS estudiante_nombre,
                       doc.apellidos_nombres AS docente_nombre,
                       pe.nombre AS programa_nombre
                FROM tutoria_sesion_individual si
                INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                INNER JOIN sigi_usuarios est ON te.id_estudiante = est.id
                INNER JOIN sigi_usuarios doc ON tp.id_docente = doc.id
                LEFT JOIN sigi_programa_estudios pe ON est.id_programa_estudios = pe.id
                WHERE tp.id_periodo_academico = :id_periodo
                  AND tp.id_sede = :id_sede
                  AND si.derivacion = 1";
        $params = [
            ':id_periodo' => $id_periodo,
            ':id_sede'    => $id_sede
        ];

        if ($id_docente) {
            $sql .= " AND tp.id_docente = :id_docente";
            $params[':id_docente'] = $id_docente;
        }

        $sql .= " ORDER BY si.fecha_hora DESC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nEst = explode('_', trim($value['estudiante_nombre']));
            $data[$key]['estudiante_formato'] = trim(($nEst[0] ?? '') . ' ' . ($nEst[1] ?? '') . ', ' . ($nEst[2] ?? ''));

            $nDoc = explode('_', trim($value['docente_nombre']));
            $data[$key]['docente_formato'] = trim(($nDoc[0] ?? '') . ' ' . ($nDoc[1] ?? '') . ', ' . ($nDoc[2] ?? ''));
        }

        return $data;
    }

    public function listarParaReporte($filters = [])
    {
        $where = [
            "tp.id_sede = :id_sede",
            "tp.id_periodo_academico = :id_periodo"
        ];
        $params = [
            ':id_sede'    => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo']
        ];

        if (!empty($filters['id_docente'])) {
            $where[] = "tp.id_docente = :id_docente";
            $params[':id_docente'] = $filters['id_docente'];
        }

        if (!empty($filters['id_programacion'])) {
            $where[] = "tp.id = :id_programacion";
            $params[':id_programacion'] = $filters['id_programacion'];
        }

        if (isset($filters['derivacion']) && $filters['derivacion'] !== '') {
            $where[] = "si.derivacion = :derivacion";
            $params[':derivacion'] = (int)$filters['derivacion'];
        }

        $sqlWhere = " WHERE " . implode(" AND ", $where);

        $sql = "SELECT si.*,
                       te.id_estudiante,
                       est.dni AS estudiante_dni,
                       est.apellidos_nombres AS estudiante_nombre,
                       est.correo AS estudiante_correo,
                       est.telefono AS estudiante_telefono,
                       doc.dni AS docente_dni,
                       doc.apellidos_nombres AS docente_nombre,
                       pe.nombre AS programa_nombre,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre,
                       mat.semestre_desc AS semestre_nombre
                FROM tutoria_sesion_individual si
                INNER JOIN tutoria_estudiantes te ON si.id_tutoria_estudiante = te.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                INNER JOIN sigi_usuarios est ON te.id_estudiante = est.id
                INNER JOIN sigi_usuarios doc ON tp.id_docente = doc.id
                INNER JOIN sigi_sedes s ON tp.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
                LEFT JOIN sigi_programa_estudios pe ON est.id_programa_estudios = pe.id
                LEFT JOIN (
                    SELECT m.id_estudiante, m.id_periodo_academico, MAX(s2.descripcion) AS semestre_desc
                    FROM acad_matricula m
                    INNER JOIN sigi_semestre s2 ON m.id_semestre = s2.id
                    GROUP BY m.id_estudiante, m.id_periodo_academico
                ) mat ON mat.id_estudiante = est.id AND mat.id_periodo_academico = tp.id_periodo_academico
                $sqlWhere
                ORDER BY si.fecha_hora DESC, si.nro ASC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nEst = explode('_', trim($value['estudiante_nombre']));
            $data[$key]['estudiante_formato'] = trim(($nEst[0] ?? '') . ' ' . ($nEst[1] ?? '') . ', ' . ($nEst[2] ?? ''));

            $nDoc = explode('_', trim($value['docente_nombre']));
            $data[$key]['docente_formato'] = trim(($nDoc[0] ?? '') . ' ' . ($nDoc[1] ?? '') . ', ' . ($nDoc[2] ?? ''));
        }

        return $data;
    }
}
