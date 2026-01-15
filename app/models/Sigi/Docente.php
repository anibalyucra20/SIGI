<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class Docente extends Model
{
    protected $table = 'sigi_usuarios';

    // Obtener todos los docentes (roles distintos de ESTUDIANTE y EXTERNO)
    public function getAllDocente()
    {
        $sql = "
            SELECT 
              u.id,
              u.dni,
              u.apellidos_nombres,
              u.correo,
              r.nombre   AS nombre_rol,
              s.nombre   AS nombre_sede,
              p.nombre   AS nombre_periodo     -- Aquí usamos p.nombre
            FROM sigi_usuarios u
            JOIN sigi_roles             r ON u.id_rol              = r.id
            JOIN sigi_sedes             s ON u.id_sede             = s.id
            JOIN sigi_periodo_academico p ON u.id_periodo_registro = p.id
            WHERE r.nombre NOT IN ('ESTUDIANTE', 'EXTERNO')
            ORDER BY u.apellidos_nombres
        ";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $data;
    }
    // Lista de docentes para asignar (SELECT)
    public function getDocentesAsignar()
    {
        $sql = "SELECT u.id, u.apellidos_nombres
             FROM sigi_usuarios u
             JOIN sigi_roles r ON r.id = u.id_rol
             WHERE u.estado = 1 AND r.nombre NOT IN ('ESTUDIANTE', 'EXTERNO')
             ORDER BY u.apellidos_nombres ASC";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $data;
    }
    public function getDocentesPorSede($id_sede)
    {
        $sql = "SELECT id, apellidos_nombres 
            FROM sigi_usuarios
            WHERE id_sede = ?
              AND id_rol BETWEEN 1 AND 6
              AND estado = 1
            ORDER BY apellidos_nombres";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_sede]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $data;
    }
    // Para el select de directores (usuarios activos, solo rol Director)
    public function getDirectores()
    {
        $stmt = self::$db->query("SELECT id, apellidos_nombres FROM sigi_usuarios WHERE id_rol = 2 AND estado = 1 ORDER BY apellidos_nombres");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }
        return $data;
    }

    // Obtener un docente por ID
    public function find($id)
    {
        $sql = "SELECT 
                u.*, 
                r.nombre AS nombre_rol, 
                s.nombre AS nombre_sede,
                p.nombre AS nombre_programa
            FROM sigi_usuarios u
            LEFT JOIN sigi_roles r ON u.id_rol = r.id
            LEFT JOIN sigi_sedes s ON u.id_sede = s.id
            LEFT JOIN sigi_programa_estudios p ON u.id_programa_estudios = p.id
            WHERE u.id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($data['apellidos_nombres']));
        $data['ApellidoPaterno'] = $apellidos_nombres[0];
        $data['ApellidoMaterno'] = $apellidos_nombres[1];
        $data['Nombres'] = $apellidos_nombres[2];
        $data['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }
    public function obtenerPermisos($id_usuario)
    {
        $sql = "SELECT spu.id, si.nombre AS sistema, sr.nombre AS rol
            FROM sigi_permisos_usuarios spu
            INNER JOIN sigi_sistemas_integrados si ON spu.id_sistema = si.id
            INNER JOIN sigi_roles sr ON spu.id_rol = sr.id
            WHERE spu.id_usuario = ?";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo docente
    public function nuevo($data)
    {
        $sql = "INSERT INTO sigi_usuarios
        (dni, apellidos_nombres, correo, password,
         discapacidad, genero, fecha_nacimiento,distrito_nacimiento, direccion, telefono,
         id_rol, id_sede, id_programa_estudios, id_periodo_registro,
         reset_password, token_password, estado)
        VALUES
        (:dni, :apellidos_nombres, :correo, :password,
         :discapacidad, :genero, :fecha_nacimiento,:distrito_nacimiento, :direccion, :telefono,
         :id_rol, :id_sede, :id_programa_estudios, :id_periodo_registro,
         :reset_password, :token_password, :estado)";
        $stmt = self::$db->prepare($sql);
        if ($stmt->execute($data)) {
            return self::$db->lastInsertId(); // Retorna el ID del nuevo usuario
        } else {
            return false; // O lanza una excepción, según tu manejo de errores
        }
    }

    // Actualizar docente
    public function update($id, $data)
    {
        $sql = "UPDATE sigi_usuarios SET
          dni = :dni,
          apellidos_nombres = :apellidos_nombres,
          correo = :correo,
          discapacidad = :discapacidad,
          genero = :genero,
          fecha_nacimiento = :fecha_nacimiento,
          distrito_nacimiento = :distrito_nacimiento,
          direccion = :direccion,
          telefono = :telefono,
          estado = :estado,                         -- ← aquí
          id_rol = :id_rol,
          id_sede = :id_sede,
          id_programa_estudios = :id_programa_estudios
        WHERE id = :id";

        $stmt = self::$db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }
    public function updateInAdmision($id, $data)
    {
        $sql = "UPDATE sigi_usuarios SET
          dni = :dni,
          apellidos_nombres = :apellidos_nombres,
          correo = :correo,
          discapacidad = :discapacidad,
          genero = :genero,
          fecha_nacimiento = :fecha_nacimiento,
          distrito_nacimiento = :distrito_nacimiento,
          direccion = :direccion,
          telefono = :telefono,
          id_sede = :id_sede
        WHERE id = :id";

        $stmt = self::$db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }



    public function getPaginated(array $filters, int $limit, int $offset, string $orderCol, string $orderDir)
    {
        // Mapear columna DataTables → columna BD
        $cols = [
            0 => 'u.id',
            1 => 'u.dni',
            2 => 'u.apellidos_nombres',
            3 => 'u.estado'
        ];
        $orderBy = $cols[$orderCol] ?? 'u.apellidos_nombres';

        // Filtros dinámicos
        $where  = "WHERE r.nombre NOT IN ('ESTUDIANTE','EXTERNO')";
        $params = [];

        // DNI
        if (!empty($filters['dni'])) {
            $where   .= " AND u.dni LIKE :dni";
            $params[':dni'] = "%{$filters['dni']}%";
        }
        // Apellidos y nombres
        if (!empty($filters['nombres'])) {
            $where   .= " AND u.apellidos_nombres LIKE :nom";
            $params[':nom'] = "%{$filters['nombres']}%";
        }
        // Estado
        if ($filters['estado'] !== '') {   // '' = “todos”
            $where .= " AND u.estado = :est";
            $params[':est'] = (int)$filters['estado'];   // 0 ó 1
        }

        /* --- total sin limitar (para DataTables) --- */
        $countSql = "
        SELECT COUNT(*) as total
        FROM sigi_usuarios u
        JOIN sigi_roles r ON u.id_rol = r.id
        $where
        ";
        $stmt = self::$db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        /* --- consulta paginada --- */
        $sql = "
            SELECT 
                u.id,
                u.dni,
                u.apellidos_nombres,
                u.correo,
                u.estado,                               -- numérico (0 / 1) si lo necesitas en la app
                CASE WHEN u.estado = 1 
                    THEN 'ACTIVO' 
                    ELSE 'INACTIVO' 
                END   AS estado_text,                   -- ← etiqueta legible
                r.nombre AS nombre_rol
            FROM sigi_usuarios u
            JOIN sigi_roles r ON u.id_rol = r.id
            $where
            ORDER BY $orderBy $orderDir
            LIMIT :limit OFFSET :offset
        ";
        if ($filters['estado'] !== '') {            // '' => Todos
            $where .= " AND u.estado = :est";
            $params[':est'] = $filters['estado'];   // 1 o 0
        }
        $stmt = self::$db->prepare($sql);
        foreach ($params as $k => $v)    $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $apellidos_nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $apellidos_nombres[0];
            $data[$key]['ApellidoMaterno'] = $apellidos_nombres[1];
            $data[$key]['Nombres'] = $apellidos_nombres[2];
            $data[$key]['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        }

        return ['data' => $data, 'total' => $total];
    }


    public function validar($data, $isEdit = false, $id = null)
    {
        $errores = [];
        $db = self::$db;

        // 1. Campos obligatorios
        $campos_obligatorios = ['dni', 'apellidos_nombres', 'correo', 'genero', 'fecha_nacimiento', 'id_rol', 'id_sede', 'id_programa_estudios'];
        foreach ($campos_obligatorios as $campo) {
            if (empty($data[$campo])) {
                $errores[] = "El campo " . $campo . " es obligatorio.";
            }
        }

        // 2. Formato
        if (!empty($data['dni']) && !preg_match('/^\d{8}$/', $data['dni'])) {
            $errores[] = "El DNI debe tener 8 dígitos numéricos.";
        }
        if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        // 3. Unicidad (DNI y correo)
        if (!$isEdit || ($isEdit && $id)) {
            // DNI único
            $q = "SELECT COUNT(*) FROM sigi_usuarios WHERE dni = :dni";
            $params = [':dni' => $data['dni']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El DNI ya está registrado.";

            // Correo único
            $q = "SELECT COUNT(*) FROM sigi_usuarios WHERE correo = :correo";
            $params = [':correo' => $data['correo']];
            if ($isEdit && $id) {
                $q .= " AND id <> :id";
                $params[':id'] = $id;
            }
            $stmt = self::$db->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) $errores[] = "El correo ya está registrado.";
        }

        // 4. Claves foráneas válidas
        foreach (
            [
                'id_rol' => 'sigi_roles',
                'id_sede' => 'sigi_sedes',
                'id_programa_registro' => 'sigi_programa_estudios',
                'id_periodo_registro' => 'sigi_periodo_academico'
            ] as $campo => $tabla
        ) {
            if (!empty($data[$campo])) {
                $exists = self::$db->query("SELECT id FROM $tabla WHERE id = " . (int)$data[$campo])->fetchColumn();
                if (!$exists) $errores[] = "Valor inválido para $campo";
            }
        }

        return $errores;
    }
    public function correoExiste($correo, $id_excluir = null)
    {
        $sql = "SELECT COUNT(*) FROM sigi_usuarios WHERE correo = ?";

        $params = [$correo];

        if ($id_excluir !== null) {
            $sql .= " AND id != ?";
            $params[] = $id_excluir;
        }

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }


    public function getPermisosUsuario($id_usuario)
    {
        $sql = "SELECT * FROM sigi_permisos_usuarios WHERE id_usuario = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarLote(int $id_usuario, int $id_rol, array $ids_sistema): void
    {
        if (empty($ids_sistema)) return;
        $sql = "INSERT IGNORE INTO sigi_permisos_usuarios (id_usuario, id_sistema, id_rol) VALUES (?, ?, ?)";
        $st = self::$db->prepare($sql);
        /*foreach ($ids_sistema as $id_sis) {
            $st->execute([$id_usuario, (int)$id_sis, (int)$id_rol]);
        }*/
        foreach ($ids_sistema as $id_sis) {
            $b = "SELECT * FROM sigi_permisos_usuarios WHERE id_usuario = ? AND id_sistema = ? AND id_rol= ?";
            $rb = self::$db->prepare($b);
            $rb->execute([$id_usuario, (int)$id_sis, (int)$id_rol]);
            $cont = $rb->rowCount();
            if ($cont == 0) {
                $st->execute([$id_usuario, (int)$id_sis, (int)$id_rol]);
            }
        }
    }

    public function actualizarPermisos($id_usuario, $permisos)
    {
        // Formatea array ["1-2", "2-4"] a [['id_sistema'=>1,'id_rol'=>2], ...] si fuera necesario
        if (isset($permisos[0]) && is_string($permisos[0])) {
            $permisosParsed = [];
            foreach ($permisos as $perm) {
                [$id_sistema, $id_rol] = explode('-', $perm);
                $permisosParsed[] = [
                    'id_sistema' => (int)$id_sistema,
                    'id_rol'     => (int)$id_rol
                ];
            }
            $permisos = $permisosParsed;
        }

        $db = $this->getDB();

        // 1. Borra todos los permisos anteriores
        $stmt = self::$db->prepare("DELETE FROM sigi_permisos_usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);

        // 2. Inserta los nuevos permisos
        $sql = "INSERT INTO sigi_permisos_usuarios (id_usuario, id_sistema, id_rol) VALUES (?, ?, ?)";
        $stmt = self::$db->prepare($sql);

        foreach ($permisos as $perm) {
            if (!empty($perm['id_sistema']) && !empty($perm['id_rol'])) {
                $stmt->execute([$id_usuario, $perm['id_sistema'], $perm['id_rol']]);
            }
        }
    }

    // (Ya existente, pero útil aquí)
    public function existeDocente($id_docente, $id_sede)
    {
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM sigi_usuarios WHERE id = ? AND id_sede = ? AND id_rol BETWEEN 2 AND 6 AND estado = 1");
        $stmt->execute([$id_docente, $id_sede]);
        return $stmt->fetchColumn() > 0;
    }

    public function getDirectorPorPeriodo($id_periodo)
    {
        $stmt = self::$db->prepare("SELECT u.id as id_director, u.apellidos_nombres as director FROM sigi_usuarios u LEFT JOIN sigi_periodo_academico pa ON pa.director = u.id WHERE pa.id = ?");
        $stmt->execute([$id_periodo]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($data['director']));
        $data['director'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }




    //===================================== PARA RESETEAR PASSWORD ==============================
    public function findByEmailAndDni(string $email, $dni)
    {
        $email = trim(mb_strtolower($email));
        $dni   = trim($dni);
        $sql = "SELECT * FROM sigi_usuarios WHERE LOWER(correo) = ? AND dni = ? AND estado = 1 LIMIT 1";
        $st = self::$db->prepare($sql);
        $st->execute([$email, $dni]);
        $data = $st->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($data['apellidos_nombres']));
        $data['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }

    public function setResetToken(int $idUsuario, string $token): bool
    {
        $sql = "UPDATE sigi_usuarios SET token_password = ?, reset_password = 1 WHERE id = ?";
        $st = self::$db->prepare($sql);
        $st->execute([$token, $idUsuario]);
        return $st->rowCount() > 0;
    }

    public function findByResetToken(string $token, $id_user)
    {
        $sql = "SELECT * FROM sigi_usuarios WHERE token_password = ? AND reset_password = 1  AND estado = 1 AND id = ? LIMIT 1";
        $st = self::$db->prepare($sql);
        $st->execute([$token, $id_user]);
        $data = $st->fetch(PDO::FETCH_ASSOC);
        $apellidos_nombres = explode('_', trim($data['apellidos_nombres']));
        $data['apellidos_nombres'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ' ' . $apellidos_nombres[2];
        return $data;
    }

    public function updatePassword(int $idUsuario, string $hash): bool
    {
        $sql = "UPDATE sigi_usuarios SET password = ?, reset_password = 0, token_password = '' WHERE id = ?";
        $st = self::$db->prepare($sql);
        return $st->execute([$hash, $idUsuario]);
    }

    public function getAllUsuarios()
    {
        $sql = "SELECT * FROM sigi_usuarios";
        $st = self::$db->prepare($sql);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }




    //-------------- HELPER PARA CAMBIO DE NOMBRE  DE USUARIOS-----------------------------
    function parsePersona4($usuario): array
    {
        $raw = $usuario['apellidos_nombres'];
        $raw = trim(preg_replace('/\s+/', ' ', $raw));

        // 1) Grado/Título al inicio
        $grado = '';
        $grados = ['Tec.', 'Tec', 'Téc.', 'Téc', 'CPC.', 'CPC', 'CP.', 'CP', 'Ing.', 'Ing', 'Lic.', 'Lic', 'Dr.', 'Dr', 'Dra.', 'Dra', 'Mag.', 'Mag', 'Obst.', 'Obst'];

        foreach ($grados as $g) {
            if (preg_match('/^' . preg_quote($g, '/') . '\s+/i', $raw)) {
                $grado = rtrim($g, '.'); // guarda sin punto (opcional)
                $raw = preg_replace('/^' . preg_quote($g, '/') . '\s+/i', '', $raw);
                $raw = trim($raw);
                break;
            }
        }

        $apPat = '';
        $apMat = '';
        $nombres = '';

        // Partículas comunes en apellidos compuestos
        $particulas = ['DE', 'DEL', 'LA', 'LAS', 'LOS', 'Y'];

        // 2) Caso con coma: "APELLIDOS, NOMBRES"
        if (strpos($raw, ',') !== false) {
            [$apellidosStr, $nombresStr] = array_map('trim', explode(',', $raw, 2));
            $nombres = $nombresStr;

            $aTokens = preg_split('/\s+/', $apellidosStr, -1, PREG_SPLIT_NO_EMPTY);
            $aN = count($aTokens);

            if ($aN === 1) {
                $apPat = $aTokens[0];
                $apMat = '';
            } elseif ($aN >= 2) {
                // Heurística simple: 1er token = paterno, resto = materno (para casos como "LOPEZ DE YUCRA")
                $apPat = $aTokens[0];
                $apMat = implode(' ', array_slice($aTokens, 1));
            }
        } else {
            // 3) Sin coma: asumimos últimos 2 tokens como nombres (si hay suficientes)
            $tokens = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            $n = count($tokens);

            if ($n === 0) {
                return ['grado' => $grado, 'apellido_paterno' => '', 'apellido_materno' => '', 'nombres' => ''];
            }

            if ($n >= 4) {
                $nombresTokens = array_slice($tokens, -2);
                $apellidosTokens = array_slice($tokens, 0, -2);
            } else {
                // pocos tokens: último = nombre, resto = apellidos
                $nombresTokens = [end($tokens)];
                $apellidosTokens = array_slice($tokens, 0, -1);
            }

            $nombres = implode(' ', $nombresTokens);

            // Separar apellidosTokens en paterno y materno
            $aN = count($apellidosTokens);

            if ($aN === 1) {
                $apPat = $apellidosTokens[0];
                $apMat = '';
            } elseif ($aN === 2) {
                $apPat = $apellidosTokens[0];
                $apMat = $apellidosTokens[1];
            } else {
                // Heurística:
                // - Paterno = primer token
                // - Materno = resto, pero si el segundo token es partícula (DE/DEL/LA/...), lo pegamos al paterno.
                $apPatTokens = [$apellidosTokens[0]];
                $idx = 1;

                // Si hay partículas inmediatamente después del paterno, se agregan al paterno
                while ($idx < $aN && in_array(mb_strtoupper($apellidosTokens[$idx]), $particulas, true)) {
                    $apPatTokens[] = $apellidosTokens[$idx];
                    $idx++;
                    if ($idx < $aN) {
                        // si después de la partícula viene palabra, también podría ser parte del paterno compuesto (ej: "DE LA")
                        // pero esto es opcional; aquí solo pegamos la partícula.
                    }
                }

                $apPat = implode(' ', $apPatTokens);
                $apMat = implode(' ', array_slice($apellidosTokens, $idx));
            }
        }
        $sql = "UPDATE sigi_usuarios SET apellidos_nombres = ?, grado_academico = ? WHERE id = ?";
        $st = self::$db->prepare($sql);
        $st->execute([$apPat . '_' . $apMat . '_' . $nombres, $grado, $usuario['id']]);
        return [
            'grado'            => $grado,
            'apellido_paterno' => trim($apPat),
            'apellido_materno' => trim($apMat),
            'nombres'          => trim($nombres),
            'apellidos_nombres' => trim($apPat . '_' . $apMat . '_' . $nombres),
        ];
    }
}
