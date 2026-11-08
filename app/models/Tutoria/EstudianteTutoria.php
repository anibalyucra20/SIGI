<?php

namespace App\Models\Tutoria;

use Core\Model;
use PDO;

class EstudianteTutoria extends Model
{
    protected $table = 'tutoria_estudiantes';

    /**
     * Obtiene los estudiantes asignados a una programación de tutoría.
     */
    public function listarPorProgramacion($id_programacion)
    {
        $sql = "SELECT te.id AS id_tutoria_estudiante,
                       te.id_programacion_tutoria,
                       te.id_estudiante,
                       te.observacion,
                       u.dni,
                       u.apellidos_nombres,
                       u.correo,
                       u.telefono,
                       u.estado,
                       pe.nombre AS programa_nombre,
                       (SELECT COUNT(*) FROM tutoria_sesion_individual tsi WHERE tsi.id_tutoria_estudiante = te.id) AS total_sesiones_ind,
                       (SELECT COUNT(*) FROM tutoria_recojo_informacion tri WHERE tri.id_tutoria_estudiante = te.id) AS total_fichas
                FROM tutoria_estudiantes te
                INNER JOIN sigi_usuarios u ON te.id_estudiante = u.id
                LEFT JOIN sigi_programa_estudios pe ON u.id_programa_estudios = pe.id
                WHERE te.id_programacion_tutoria = :id_programacion
                ORDER BY u.apellidos_nombres ASC";

        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_programacion', $id_programacion, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['ApellidoPaterno'] = $nombres[0] ?? '';
            $data[$key]['ApellidoMaterno'] = $nombres[1] ?? '';
            $data[$key]['Nombres']         = $nombres[2] ?? '';
            $data[$key]['nombre_completo'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
        }

        return $data;
    }

    /**
     * Busca un registro de asignación por ID.
     */
    public function find($id)
    {
        $sql = "SELECT te.*,
                       u.dni,
                       u.apellidos_nombres,
                       u.genero,
                       u.fecha_nacimiento,
                       u.distrito_nacimiento,
                       u.direccion,
                       u.discapacidad,
                       u.correo,
                       u.telefono,
                       pe.nombre AS programa_nombre,
                       tp.id_sede,
                       tp.id_periodo_academico,
                       tp.id_docente,
                       pa.nombre AS periodo_nombre,
                       s.nombre AS sede_nombre,
                       ud.apellidos_nombres AS docente_apellidos_nombres,
                       ud.dni AS docente_dni,
                       (SELECT ss.descripcion 
                        FROM acad_matricula am
                        INNER JOIN sigi_semestre ss ON am.id_semestre = ss.id
                        WHERE am.id_estudiante = te.id_estudiante 
                          AND am.id_periodo_academico = tp.id_periodo_academico 
                        ORDER BY am.id DESC LIMIT 1) AS semestre_nombre
                FROM tutoria_estudiantes te
                INNER JOIN sigi_usuarios u ON te.id_estudiante = u.id
                INNER JOIN tutoria_programacion tp ON te.id_programacion_tutoria = tp.id
                LEFT JOIN sigi_periodo_academico pa ON tp.id_periodo_academico = pa.id
                LEFT JOIN sigi_sedes s ON tp.id_sede = s.id
                LEFT JOIN sigi_usuarios ud ON tp.id_docente = ud.id
                LEFT JOIN sigi_programa_estudios pe ON u.id_programa_estudios = pe.id
                WHERE te.id = :id";

        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nombres = explode('_', trim($row['apellidos_nombres']));
            $row['ApellidoPaterno'] = $nombres[0] ?? '';
            $row['ApellidoMaterno'] = $nombres[1] ?? '';
            $row['Nombres']         = $nombres[2] ?? '';
            $row['nombre_completo'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));

            // Docente
            if (!empty($row['docente_apellidos_nombres'])) {
                $docPartes = explode('_', trim($row['docente_apellidos_nombres']));
                $row['docente_formato'] = trim(($docPartes[0] ?? '') . ' ' . ($docPartes[1] ?? '') . ', ' . ($docPartes[2] ?? ''));
            } else {
                $row['docente_formato'] = 'Docente Tutor';
            }

            // Calcular edad si fecha_nacimiento existe
            if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] !== '0000-00-00' && $row['fecha_nacimiento'] !== '2000-01-01') {
                try {
                    $fn = new \DateTime($row['fecha_nacimiento']);
                    $hoy = new \DateTime();
                    $row['edad'] = $hoy->diff($fn)->y;
                } catch (\Throwable $e) {
                    $row['edad'] = '';
                }
            } else {
                $row['edad'] = '';
            }
        }

        return $row;
    }

    /**
     * Asigna un estudiante a la programación de tutoría.
     */
    public function asignar($id_programacion, $id_estudiante, $observacion = '')
    {
        // Verificar si ya está asignado a esta programación
        $check = self::$db->prepare("SELECT id FROM tutoria_estudiantes WHERE id_programacion_tutoria = ? AND id_estudiante = ?");
        $check->execute([$id_programacion, $id_estudiante]);
        if ($check->fetch()) {
            return false; // Ya asignado
        }

        $sql = "INSERT INTO tutoria_estudiantes (id_programacion_tutoria, id_estudiante, observacion)
                VALUES (:id_prog, :id_est, :obs)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            ':id_prog' => $id_programacion,
            ':id_est'  => $id_estudiante,
            ':obs'     => $observacion
        ]);

        return self::$db->lastInsertId();
    }

    /**
     * Actualiza la observación de un tutorando.
     */
    public function actualizarObservacion($id, $observacion)
    {
        $stmt = self::$db->prepare("UPDATE tutoria_estudiantes SET observacion = ? WHERE id = ?");
        return $stmt->execute([$observacion, $id]);
    }

    /**
     * Elimina la asignación de un estudiante.
     */
    public function eliminar($id)
    {
        $stmt = self::$db->prepare("DELETE FROM tutoria_estudiantes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Busca estudiantes disponibles en la sede que no estén asignados a esta programación.
     */
    public function buscarEstudiantesDisponibles($id_sede, $filtro = '', $id_programacion = null, $id_programa = null)
    {
        $params = [':id_sede' => $id_sede];
        $whereProg = "";
        if ($id_programa) {
            $whereProg = " AND u.id_programa_estudios = :id_programa ";
            $params[':id_programa'] = $id_programa;
        }

        $notIn = "";
        if ($id_programacion) {
            $notIn = " AND u.id NOT IN (SELECT id_estudiante FROM tutoria_estudiantes WHERE id_programacion_tutoria = :id_prog) ";
            $params[':id_prog'] = $id_programacion;
        }

        $whereFiltro = "";
        if (!empty($filtro)) {
            $whereFiltro = " AND (u.dni LIKE :filtro OR u.apellidos_nombres LIKE :filtro) ";
            $params[':filtro'] = '%' . $filtro . '%';
        }

        $sql = "SELECT u.id, u.dni, u.apellidos_nombres, u.correo, pe.nombre AS programa_nombre
                FROM sigi_usuarios u
                INNER JOIN sigi_roles r ON u.id_rol = r.id
                LEFT JOIN sigi_programa_estudios pe ON u.id_programa_estudios = pe.id
                WHERE u.id_sede = :id_sede
                  AND r.nombre = 'ESTUDIANTE'
                  AND u.estado = 1
                  $whereProg
                  $notIn
                  $whereFiltro
                ORDER BY u.apellidos_nombres ASC
                LIMIT 50";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {
            $nombres = explode('_', trim($value['apellidos_nombres']));
            $data[$key]['nombre_completo'] = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
        }

        return $data;
    }

    /**
     * Conteo de estudiantes asignados a una tutoría.
     */
    public function contarPorProgramacion($id_programacion)
    {
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM tutoria_estudiantes WHERE id_programacion_tutoria = ?");
        $stmt->execute([$id_programacion]);
        return (int)$stmt->fetchColumn();
    }
}
