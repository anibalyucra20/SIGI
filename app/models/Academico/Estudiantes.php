<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Estudiantes extends Model
{
    // Listado paginado y filtrado
    public function getPaginated($filters, $length, $start, $orderCol, $orderDir)
    {
        $columnas = [
            0 => 'u.id',
            1 => 'u.dni',
            2 => 'u.apellidos_nombres',
            3 => 's.nombre',
            4 => 'p.nombre',
            5 => 'pl.nombre',
            6 => 'per.nombre',
            7 => 'u.correo',
            8 => 'u.telefono',
            9 => 'u.estado'
        ];
        $ordenarPor = $columnas[$orderCol] ?? 'u.apellidos_nombres';

        $where = [
            "r.nombre = 'ESTUDIANTE'",
            "u.id_sede = :id_sede",
            "ep.id_periodo = :id_periodo"
        ];
        $params = [
            ':id_sede' => $filters['id_sede'],
            ':id_periodo' => $filters['id_periodo']
        ];

        if (!empty($filters['id_programa'])) {
            $where[] = "u.id_programa_estudios = :id_programa";
            $params[':id_programa'] = $filters['id_programa'];
        }
        if (!empty($filters['id_plan'])) {
            $where[] = "ep.id_plan_estudio = :id_plan";
            $params[':id_plan'] = $filters['id_plan'];
        }
        if (!empty($filters['dni'])) {
            $where[] = "u.dni LIKE :dni";
            $params[':dni'] = '%' . $filters['dni'] . '%';
        }
        if (!empty($filters['apellidos_nombres'])) {
            $where[] = "u.apellidos_nombres LIKE :apellidos_nombres";
            $params[':apellidos_nombres'] = '%' . $filters['apellidos_nombres'] . '%';
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "SELECT u.id, u.dni, u.apellidos_nombres, u.correo, u.telefono, u.estado,
                       s.nombre AS sede_nombre,
                       p.nombre AS programa_nombre,
                       pl.nombre AS plan_nombre,
                       per.nombre AS periodo_nombre,
                       u.fecha_nacimiento
                FROM sigi_usuarios u
                INNER JOIN sigi_roles r ON u.id_rol = r.id
                INNER JOIN sigi_sedes s ON u.id_sede = s.id
                INNER JOIN sigi_programa_estudios p ON u.id_programa_estudios = p.id
                INNER JOIN acad_estudiante_programa ep ON ep.id_usuario = u.id
                INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
                INNER JOIN sigi_periodo_academico per ON ep.id_periodo = per.id
                $sqlWhere
                ORDER BY $ordenarPor $orderDir
                LIMIT :limit OFFSET :offset";
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $tipo = (strpos($k, 'dni') !== false || strpos($k, 'apellidos_nombres') !== false) ? PDO::PARAM_STR : PDO::PARAM_INT;
            $stmt->bindValue($k, $v, $tipo);
        }
        // Asegúrate de tener valores enteros válidos:
        if (!is_numeric($length) || !is_numeric($start)) {
            $length = 10;
            $start = 0;
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // Conteo total
        $sqlTotal = "SELECT COUNT(*) 
                FROM sigi_usuarios u
                INNER JOIN sigi_roles r ON u.id_rol = r.id
                INNER JOIN sigi_sedes s ON u.id_sede = s.id
                INNER JOIN sigi_programa_estudios p ON u.id_programa_estudios = p.id
                INNER JOIN acad_estudiante_programa ep ON ep.id_usuario = u.id
                INNER JOIN sigi_planes_estudio pl ON ep.id_plan_estudio = pl.id
                INNER JOIN sigi_periodo_academico per ON ep.id_periodo = per.id
                $sqlWhere";
        $stmtTotal = self::$db->prepare($sqlTotal);
        foreach ($params as $k => $v) {
            $tipo = (strpos($k, 'dni') !== false || strpos($k, 'apellidos_nombres') !== false) ? PDO::PARAM_STR : PDO::PARAM_INT;
            $stmtTotal->bindValue($k, $v, $tipo);
        }
        //error_log($sql);
        //error_log(print_r($params, true));

        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return ['data' => $data, 'total' => $total];
    }
    // Validaciones duplicados
    public function existeDni($dni, $id_ignorar = null)
    {
        $sql = "SELECT id FROM sigi_usuarios WHERE dni = ?";
        $params = [$dni];
        if ($id_ignorar) {
            $sql .= " AND id != ?";
            $params[] = $id_ignorar;
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function existeEstudianteEnPlan($id_usuario, $id_plan_estudio, $id_ignorar = null)
    {
        $sql = "SELECT COUNT(*) FROM acad_estudiante_programa WHERE id_usuario = ? AND id_plan_estudio = ?";
        $params = [$id_usuario, $id_plan_estudio];
        if ($id_ignorar) {
            $sql .= " AND id != ?";
            $params[] = $id_ignorar;
        }
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // CRUD
    public function find($id)
    {
        $sql = "SELECT u.*, ep.id_plan_estudio, ep.id_periodo, ep.id as id_acad_est_prog
                FROM sigi_usuarios u
                INNER JOIN acad_estudiante_programa ep ON ep.id_usuario = u.id
                WHERE u.id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($data['apellidos_nombres']));
        $data['ApellidoPaterno'] = $apellidos_nombres[0];
        $data['ApellidoMaterno'] = $apellidos_nombres[1];
        $data['Nombres'] = $apellidos_nombres[2];
        $data['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }
    public function guardar($data)
    {
        if (!empty($data['id'])) {
            // UPDATE usuario
            $sql = "UPDATE sigi_usuarios SET 
                        dni = :dni,
                        apellidos_nombres = :apellidos_nombres,
                        genero = :genero,
                        fecha_nacimiento = :fecha_nacimiento,
                        direccion = :direccion,
                        correo = :correo,
                        telefono = :telefono,
                        id_sede = :id_sede,
                        id_programa_estudios = :id_programa_estudios,
                        discapacidad = :discapacidad,
                        estado = :estado
                    WHERE id = :id";
            $params = [
                ':dni' => $data['dni'],
                ':apellidos_nombres' => $data['apellidos_nombres'],
                ':genero' => $data['genero'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':direccion' => $data['direccion'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
                ':id_sede' => $data['id_sede'],
                ':id_programa_estudios' => $data['id_programa_estudios'],
                ':discapacidad' => $data['discapacidad'],
                ':estado' => $data['estado'],
                ':id' => $data['id']
            ];
            $stmt = self::$db->prepare($sql);
            $stmt->execute($params);

            if (!$this->existeEstudianteEnPlan(
                $data['id'],
                $data['id_plan_estudio']
            )) {
                //en caso que no exista registramos al estudsiante en el plan de estudios
                // INSERT acad_estudiante_programa
                $sql2 = "INSERT INTO acad_estudiante_programa (id_usuario, id_plan_estudio, id_periodo)
                     VALUES (?, ?, ?)";
                $stmt2 = self::$db->prepare($sql2);
                $stmt2->execute([$data['id'], $data['id_plan_estudio'], $data['id_periodo']]);
            }
            return $data['id'];
        } else {
            // INSERT usuario
            $sql = "INSERT INTO sigi_usuarios
                        (dni, apellidos_nombres, genero, fecha_nacimiento, direccion, correo, telefono, id_periodo_registro, id_programa_estudios, discapacidad, id_rol, id_sede, estado, password, reset_password, token_password)
                    VALUES
                        (:dni, :apellidos_nombres, :genero, :fecha_nacimiento, :direccion, :correo, :telefono, :id_periodo_registro, :id_programa_estudios, :discapacidad, :id_rol, :id_sede, :estado, :password, :reset_password, :token_password)";
            $params = [
                ':dni' => $data['dni'],
                ':apellidos_nombres' => $data['apellidos_nombres'],
                ':genero' => $data['genero'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':direccion' => $data['direccion'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
                ':id_periodo_registro' => $data['id_periodo'],
                ':id_programa_estudios' => $data['id_programa_estudios'],
                ':discapacidad' => $data['discapacidad'],
                ':id_rol' => $data['id_rol'],
                ':id_sede' => $data['id_sede'],
                ':estado' => $data['estado'],
                ':password' => $data['password'],
                ':reset_password' => $data['reset_password'],
                ':token_password' => $data['token_password']
            ];
            $stmt = self::$db->prepare($sql);
            $stmt->execute($params);
            $id_usuario = self::$db->lastInsertId();

            if (!$this->existeEstudianteEnPlan(
                $data['id'],
                $data['id_plan_estudio']
            )) {
                //en caso que no exista registramos al estudsiante en el plan de estudios
                // INSERT acad_estudiante_programa
                $sql2 = "INSERT INTO acad_estudiante_programa (id_usuario, id_plan_estudio, id_periodo)
                     VALUES (?, ?, ?)";
                $stmt2 = self::$db->prepare($sql2);
                $stmt2->execute([$id_usuario, $data['id_plan_estudio'], $data['id_periodo']]);
            }
            return $id_usuario;
        }
    }

    public function buscarUsuarioPorDni($dni)
    {
        $stmt = self::$db->prepare("SELECT * FROM sigi_usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($data)) {
            return null;
        }
        $apellidos_nombres = explode('_', trim($data['apellidos_nombres']));
        $data['ApellidoPaterno'] = $apellidos_nombres[0];
        $data['ApellidoMaterno'] = $apellidos_nombres[1];
        $data['Nombres'] = $apellidos_nombres[2];
        $data['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }
}
