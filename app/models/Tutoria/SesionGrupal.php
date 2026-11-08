<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class SesionGrupal extends Model
{
    protected $table = 'tutoria_sesion_grupal';

    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'sg.id',
            1 => 'sg.fecha_hora',
            2 => 'sg.tipo',
            3 => 'u.apellidos_nombres',
            4 => 'sg.objetivo_sesion',
            5 => 'sg.logro_objetivo'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'sg.fecha_hora';

        $where = [
            "tp.id_sede = :id_sede",
            "tp.id_periodo_academico = :id_periodo"
        ];
        $params = [
            ':id_sede'    => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo']
        ];

        if (!empty($filters['id_programacion'])) {
            $where[] = "sg.id_tutoria = :id_programacion";
            $params[':id_programacion'] = $filters['id_programacion'];
        }

        if (!empty($filters['id_docente'])) {
            $where[] = "tp.id_docente = :id_docente";
            $params[':id_docente'] = $filters['id_docente'];
        }

        if (!empty($filters['tipo'])) {
            $where[] = "sg.tipo LIKE :tipo";
            $params[':tipo'] = '%' . $filters['tipo'] . '%';
        }

        $sqlWhere = " WHERE " . implode(" AND ", $where);

        $sql = "SELECT sg.*,
                       tp.id_sede,
                       tp.id_periodo_academico,
                       tp.id_docente,
                       u.apellidos_nombres AS docente_nombre,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre
                FROM tutoria_sesion_grupal sg
                INNER JOIN tutoria_programacion tp ON sg.id_tutoria = tp.id
                INNER JOIN sigi_usuarios u ON tp.id_docente = u.id
                INNER JOIN sigi_sedes s ON tp.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
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

        // Conteo total filtrado
        $sqlTotal = "SELECT COUNT(*)
                     FROM tutoria_sesion_grupal sg
                     INNER JOIN tutoria_programacion tp ON sg.id_tutoria = tp.id
                     $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $stmtTotal->bindValue($k, $v);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        foreach ($data as $key => $value) {
            $nombres = explode('_', trim($value['docente_nombre']));
            $data[$key]['docente_formato'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
            // Decodificar asistentes si vienen en JSON o conteo
            $asistentes = json_decode($value['asistentes'], true);
            $data[$key]['total_asistentes'] = is_array($asistentes) ? count($asistentes) : (!empty($value['asistentes']) ? 1 : 0);
        }

        return ['data' => $data, 'total' => (int)$total];
    }

    public function find($id)
    {
        $sql = "SELECT sg.*,
                       tp.id_sede,
                       tp.id_periodo_academico,
                       tp.id_docente,
                       u.dni AS docente_dni,
                       u.apellidos_nombres AS docente_nombre,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre
                FROM tutoria_sesion_grupal sg
                INNER JOIN tutoria_programacion tp ON sg.id_tutoria = tp.id
                INNER JOIN sigi_usuarios u ON tp.id_docente = u.id
                INNER JOIN sigi_sedes s ON tp.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
                WHERE sg.id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nombres = explode('_', trim($row['docente_nombre']));
            $row['docente_formato'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
        }

        return $row;
    }

    public function guardar($data)
    {
        if (!empty($data['id'])) {
            $sql = "UPDATE tutoria_sesion_grupal SET
                        id_tutoria = :id_tutoria,
                        tipo = :tipo,
                        fecha_hora = :fecha_hora,
                        objetivo_sesion = :objetivo_sesion,
                        contenidos = :contenidos,
                        link_reunion = :link_reunion,
                        logro_objetivo = :logro_objetivo,
                        asistentes = :asistentes,
                        observaciones_sesion = :observaciones_sesion,
                        recomendaciones = :recomendaciones
                    WHERE id = :id";
            $params = [
                ':id_tutoria'            => $data['id_tutoria'],
                ':tipo'                  => $data['tipo'],
                ':fecha_hora'            => $data['fecha_hora'],
                ':objetivo_sesion'       => $data['objetivo_sesion'],
                ':contenidos'            => $data['contenidos'],
                ':link_reunion'          => $data['link_reunion'] ?? '',
                ':logro_objetivo'        => $data['logro_objetivo'],
                ':asistentes'            => $data['asistentes'] ?? '',
                ':observaciones_sesion'  => $data['observaciones_sesion'] ?? '',
                ':recomendaciones'       => $data['recomendaciones'] ?? '',
                ':id'                    => $data['id']
            ];
        } else {
            $sql = "INSERT INTO tutoria_sesion_grupal
                        (id_tutoria, tipo, fecha_hora, objetivo_sesion, contenidos, link_reunion, logro_objetivo, asistentes, observaciones_sesion, recomendaciones)
                    VALUES
                        (:id_tutoria, :tipo, :fecha_hora, :objetivo_sesion, :contenidos, :link_reunion, :logro_objetivo, :asistentes, :observaciones_sesion, :recomendaciones)";
            $params = [
                ':id_tutoria'            => $data['id_tutoria'],
                ':tipo'                  => $data['tipo'],
                ':fecha_hora'            => $data['fecha_hora'],
                ':objetivo_sesion'       => $data['objetivo_sesion'],
                ':contenidos'            => $data['contenidos'],
                ':link_reunion'          => $data['link_reunion'] ?? '',
                ':logro_objetivo'        => $data['logro_objetivo'],
                ':asistentes'            => $data['asistentes'] ?? '',
                ':observaciones_sesion'  => $data['observaciones_sesion'] ?? '',
                ':recomendaciones'       => $data['recomendaciones'] ?? ''
            ];
        }

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        return !empty($data['id']) ? $data['id'] : self::$db->lastInsertId();
    }

    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM tutoria_sesion_grupal WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contarPorPeriodoSede($id_periodo, $id_sede, $id_docente = null)
    {
        $sql = "SELECT COUNT(sg.id)
                FROM tutoria_sesion_grupal sg
                INNER JOIN tutoria_programacion tp ON sg.id_tutoria = tp.id
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
            $where[] = "sg.id_tutoria = :id_programacion";
            $params[':id_programacion'] = $filters['id_programacion'];
        }

        if (!empty($filters['tipo'])) {
            $where[] = "sg.tipo LIKE :tipo";
            $params[':tipo'] = '%' . $filters['tipo'] . '%';
        }

        $sqlWhere = " WHERE " . implode(" AND ", $where);

        $sql = "SELECT sg.*,
                       tp.id_sede,
                       tp.id_periodo_academico,
                       tp.id_docente,
                       u.dni AS docente_dni,
                       u.apellidos_nombres AS docente_nombre,
                       s.nombre AS sede_nombre,
                       pa.nombre AS periodo_nombre
                FROM tutoria_sesion_grupal sg
                INNER JOIN tutoria_programacion tp ON sg.id_tutoria = tp.id
                INNER JOIN sigi_usuarios u ON tp.id_docente = u.id
                INNER JOIN sigi_sedes s ON tp.id_sede = s.id
                INNER JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
                $sqlWhere
                ORDER BY sg.fecha_hora DESC, sg.id DESC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nombres = explode('_', trim($value['docente_nombre']));
            $data[$key]['docente_formato'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));

            $asistentes = json_decode($value['asistentes'], true) ?: [];
            $data[$key]['total_asistentes'] = count($asistentes);
            $data[$key]['asistentes_ids'] = $asistentes;
        }

        return $data;
    }
}
