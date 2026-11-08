<?php

namespace App\Models\Bolsa;

use Core\Model;
use PDO;

class Curriculum extends Model
{
    protected $table = 'bolsa_curriculum';

    /**
     * Busca un currículum por ID y opcionalmente verifica pertenencia al usuario
     */
    public function find($id, $idUsuario = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        if ($idUsuario !== null) {
            $sql .= " AND id_usuario = :id_usuario";
        }
        $sql .= " LIMIT 1";

        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        if ($idUsuario !== null) {
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos personales y académicos del usuario desde sigi_usuarios
     */
    public function obtenerDatosUsuario($idUsuario)
    {
        $sql = "SELECT u.id, u.dni, u.apellidos_nombres, u.genero, u.fecha_nacimiento, 
                       u.direccion, u.correo, u.telefono, u.grado_academico,
                       p.nombre AS programa_estudios
                FROM sigi_usuarios u
                LEFT JOIN sigi_programa_estudios p ON p.id = u.id_programa_estudios
                WHERE u.id = :id LIMIT 1";
        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Formatear apellidos y nombres si vienen con guiones bajos
            if (strpos($user['apellidos_nombres'], '_') !== false) {
                $partes = explode('_', trim($user['apellidos_nombres']));
                $paterno = $partes[0] ?? '';
                $materno = $partes[1] ?? '';
                $nombres = $partes[2] ?? '';
                $user['nombre_completo'] = trim("$paterno $materno $nombres");
            } else {
                $user['nombre_completo'] = trim($user['apellidos_nombres']);
            }
        }

        return $user;
    }

    /**
     * Lista todos los currículums registrados de un usuario específico
     * Incluye contadores de secciones y si ya postuló con él
     */
    public function listarPorUsuario($idUsuario)
    {
        $sql = "SELECT c.id, c.id_usuario, c.archivo, c.descripcion,
                       (SELECT COUNT(*) FROM bolsa_educacion WHERE id_cv = c.id) AS total_educacion,
                       (SELECT COUNT(*) FROM bolsa_experiencia WHERE id_cv = c.id) AS total_experiencia,
                       (SELECT COUNT(*) FROM bolsa_habilidades WHERE id_cv = c.id) AS total_habilidades,
                       (SELECT COUNT(*) FROM bolsa_idiomas WHERE id_cv = c.id) AS total_idiomas,
                       (SELECT COUNT(*) FROM bolsa_certificados WHERE id_cv = c.id) AS total_certificados,
                       (SELECT COUNT(*) FROM bolsa_postulaciones WHERE id_curriculum = c.id) AS total_postulaciones
                FROM {$this->table} c
                WHERE c.id_usuario = :id_usuario
                ORDER BY c.id DESC";

        $stmt = self::$db->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el detalle completo de un CV con todas sus secciones hijas
     */
    public function obtenerDetalleCompleto($id, $idUsuario = null)
    {
        $cv = $this->find($id, $idUsuario);
        if (!$cv) {
            return null;
        }

        // Obtener datos del usuario titular
        $usuario = $this->obtenerDatosUsuario($cv['id_usuario']);

        // Educación
        $stmtEdu = self::$db->prepare("SELECT * FROM bolsa_educacion WHERE id_cv = :id ORDER BY inicio DESC, id DESC");
        $stmtEdu->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtEdu->execute();
        $educacion = $stmtEdu->fetchAll(PDO::FETCH_ASSOC);

        // Experiencia laboral
        $stmtExp = self::$db->prepare("SELECT * FROM bolsa_experiencia WHERE id_cv = :id ORDER BY inicio DESC, id DESC");
        $stmtExp->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtExp->execute();
        $experiencia = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

        // Habilidades
        $stmtHab = self::$db->prepare("SELECT * FROM bolsa_habilidades WHERE id_cv = :id ORDER BY id ASC");
        $stmtHab->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtHab->execute();
        $habilidades = $stmtHab->fetchAll(PDO::FETCH_ASSOC);

        // Idiomas
        $stmtIdi = self::$db->prepare("SELECT * FROM bolsa_idiomas WHERE id_cv = :id ORDER BY id ASC");
        $stmtIdi->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtIdi->execute();
        $idiomas = $stmtIdi->fetchAll(PDO::FETCH_ASSOC);

        // Certificados / Cursos
        $stmtCert = self::$db->prepare("SELECT * FROM bolsa_certificados WHERE id_cv = :id ORDER BY inicio DESC, id DESC");
        $stmtCert->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtCert->execute();
        $certificados = $stmtCert->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cv'           => $cv,
            'usuario'      => $usuario,
            'educacion'    => $educacion,
            'experiencia'  => $experiencia,
            'habilidades'  => $habilidades,
            'idiomas'      => $idiomas,
            'certificados' => $certificados
        ];
    }

    /**
     * Guarda un currículum completo (cabecera y secciones) dentro de una transacción PDO
     */
    public function guardarCompleto($idCv, $idUsuario, array $datosGenerales, array $secciones = [])
    {
        self::$db->beginTransaction();

        try {
            $descripcion = trim($datosGenerales['descripcion'] ?? '');
            $archivo     = trim($datosGenerales['archivo'] ?? ''); // Link web

            if (!empty($idCv)) {
                // Verificar que pertenezca al usuario
                $sqlCheck = "SELECT id FROM {$this->table} WHERE id = :id AND id_usuario = :id_usuario LIMIT 1";
                $stmtCheck = self::$db->prepare($sqlCheck);
                $stmtCheck->bindValue(':id', (int)$idCv, PDO::PARAM_INT);
                $stmtCheck->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
                $stmtCheck->execute();
                if (!$stmtCheck->fetch()) {
                    throw new \Exception('No tiene permisos para modificar este currículum.');
                }

                // Actualizar cabecera
                $sql = "UPDATE {$this->table} SET descripcion = :descripcion, archivo = :archivo WHERE id = :id";
                $stmt = self::$db->prepare($sql);
                $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->bindValue(':archivo', $archivo, PDO::PARAM_STR);
                $stmt->bindValue(':id', (int)$idCv, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Insertar nueva cabecera
                $sql = "INSERT INTO {$this->table} (id_usuario, archivo, descripcion) VALUES (:id_usuario, :archivo, :descripcion)";
                $stmt = self::$db->prepare($sql);
                $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
                $stmt->bindValue(':archivo', $archivo, PDO::PARAM_STR);
                $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->execute();
                $idCv = (int)self::$db->lastInsertId();
            }

            // Sincronizar secciones hijas:
            // Eliminamos las anteriores para insertar las actualizadas
            $deleteTables = [
                'bolsa_educacion',
                'bolsa_experiencia',
                'bolsa_habilidades',
                'bolsa_idiomas',
                'bolsa_certificados'
            ];

            foreach ($deleteTables as $tbl) {
                $delStmt = self::$db->prepare("DELETE FROM {$tbl} WHERE id_cv = :id_cv");
                $delStmt->bindValue(':id_cv', (int)$idCv, PDO::PARAM_INT);
                $delStmt->execute();
            }

            // 1. Insertar Educación
            if (!empty($secciones['educacion']) && is_array($secciones['educacion'])) {
                $sqlEdu = "INSERT INTO bolsa_educacion (id_cv, nivel_academico, centro_estudios, especialidad, inicio, termino, archivo)
                           VALUES (:id_cv, :nivel, :centro, :especialidad, :inicio, :termino, :archivo)";
                $stmtEdu = self::$db->prepare($sqlEdu);

                foreach ($secciones['educacion'] as $item) {
                    $centro = trim($item['centro_estudios'] ?? '');
                    if (empty($centro)) continue; // Ignorar filas vacías

                    $inicio = !empty($item['inicio']) ? $item['inicio'] : date('Y-m-d');
                    $termino = !empty($item['termino']) ? $item['termino'] : date('Y-m-d');

                    $stmtEdu->execute([
                        ':id_cv'        => $idCv,
                        ':nivel'        => trim($item['nivel_academico'] ?? 'Superior'),
                        ':centro'       => $centro,
                        ':especialidad' => trim($item['especialidad'] ?? ''),
                        ':inicio'       => $inicio,
                        ':termino'      => $termino,
                        ':archivo'      => trim($item['archivo'] ?? '') // Link
                    ]);
                }
            }

            // 2. Insertar Experiencia
            if (!empty($secciones['experiencia']) && is_array($secciones['experiencia'])) {
                $sqlExp = "INSERT INTO bolsa_experiencia (id_cv, institucion, cargo, descripcion, inicio, termino, tiempo_cargo, archivo)
                           VALUES (:id_cv, :institucion, :cargo, :descripcion, :inicio, :termino, :tiempo_cargo, :archivo)";
                $stmtExp = self::$db->prepare($sqlExp);

                foreach ($secciones['experiencia'] as $item) {
                    $institucion = trim($item['institucion'] ?? '');
                    $cargo = trim($item['cargo'] ?? '');
                    if (empty($institucion) && empty($cargo)) continue;

                    $inicio = !empty($item['inicio']) ? $item['inicio'] : date('Y-m-d');
                    $termino = !empty($item['termino']) ? $item['termino'] : date('Y-m-d');

                    $stmtExp->execute([
                        ':id_cv'        => $idCv,
                        ':institucion'  => $institucion,
                        ':cargo'        => $cargo,
                        ':descripcion'  => trim($item['descripcion'] ?? ''),
                        ':inicio'       => $inicio,
                        ':termino'      => $termino,
                        ':tiempo_cargo' => trim($item['tiempo_cargo'] ?? ''),
                        ':archivo'      => trim($item['archivo'] ?? '') // Link
                    ]);
                }
            }

            // 3. Insertar Habilidades
            if (!empty($secciones['habilidades']) && is_array($secciones['habilidades'])) {
                $sqlHab = "INSERT INTO bolsa_habilidades (id_cv, titulo, nivel)
                           VALUES (:id_cv, :titulo, :nivel)";
                $stmtHab = self::$db->prepare($sqlHab);

                foreach ($secciones['habilidades'] as $item) {
                    $titulo = trim($item['titulo'] ?? '');
                    if (empty($titulo)) continue;

                    $stmtHab->execute([
                        ':id_cv'  => $idCv,
                        ':titulo' => $titulo,
                        ':nivel'  => trim($item['nivel'] ?? 'Intermedio')
                    ]);
                }
            }

            // 4. Insertar Idiomas
            if (!empty($secciones['idiomas']) && is_array($secciones['idiomas'])) {
                $sqlIdi = "INSERT INTO bolsa_idiomas (id_cv, idioma, nivel, archivo)
                           VALUES (:id_cv, :idioma, :nivel, :archivo)";
                $stmtIdi = self::$db->prepare($sqlIdi);

                foreach ($secciones['idiomas'] as $item) {
                    $idioma = trim($item['idioma'] ?? '');
                    if (empty($idioma)) continue;

                    $stmtIdi->execute([
                        ':id_cv'   => $idCv,
                        ':idioma'  => $idioma,
                        ':nivel'   => trim($item['nivel'] ?? 'Básico'),
                        ':archivo' => trim($item['archivo'] ?? '') // Link
                    ]);
                }
            }

            // 5. Insertar Certificados / Cursos
            if (!empty($secciones['certificados']) && is_array($secciones['certificados'])) {
                $sqlCert = "INSERT INTO bolsa_certificados (id_cv, nombre, institucion, tema, inicio, termino, horas, archivo)
                            VALUES (:id_cv, :nombre, :institucion, :tema, :inicio, :termino, :horas, :archivo)";
                $stmtCert = self::$db->prepare($sqlCert);

                foreach ($secciones['certificados'] as $item) {
                    $nombre = trim($item['nombre'] ?? '');
                    if (empty($nombre)) continue;

                    $inicio = !empty($item['inicio']) ? $item['inicio'] : date('Y-m-d');
                    $termino = !empty($item['termino']) ? $item['termino'] : date('Y-m-d');
                    $horas = !empty($item['horas']) ? (int)$item['horas'] : 0;

                    $stmtCert->execute([
                        ':id_cv'       => $idCv,
                        ':nombre'      => $nombre,
                        ':institucion' => trim($item['institucion'] ?? ''),
                        ':tema'        => trim($item['tema'] ?? ''),
                        ':inicio'      => $inicio,
                        ':termino'     => $termino,
                        ':horas'       => $horas,
                        ':archivo'     => trim($item['archivo'] ?? '') // Link
                    ]);
                }
            }

            self::$db->commit();
            return $idCv;
        } catch (\Throwable $e) {
            self::$db->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un currículum y todas sus secciones hijas, validando pertenencia y postulaciones asociadas
     */
    public function eliminar($id, $idUsuario)
    {
        $id = (int)$id;
        $idUsuario = (int)$idUsuario;

        // 1. Verificar existencia y pertenencia
        $cv = $this->find($id, $idUsuario);
        if (!$cv) {
            return [
                'success' => false,
                'message' => 'El currículum no existe o no tiene permisos para eliminarlo.'
            ];
        }

        // 2. Verificar si está asociado a alguna postulación
        $stmtPost = self::$db->prepare("SELECT COUNT(*) FROM bolsa_postulaciones WHERE id_curriculum = :id");
        $stmtPost->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtPost->execute();
        $totalPost = (int)$stmtPost->fetchColumn();

        if ($totalPost > 0) {
            return [
                'success' => false,
                'message' => 'No es posible eliminar este currículum porque ya ha sido utilizado para postular a una oferta laboral.'
            ];
        }

        // 3. Eliminación en cascada de sus secciones hijas
        self::$db->beginTransaction();
        try {
            $tablas = [
                'bolsa_educacion',
                'bolsa_experiencia',
                'bolsa_habilidades',
                'bolsa_idiomas',
                'bolsa_certificados'
            ];

            foreach ($tablas as $tbl) {
                $del = self::$db->prepare("DELETE FROM {$tbl} WHERE id_cv = :id");
                $del->bindValue(':id', $id, PDO::PARAM_INT);
                $del->execute();
            }

            $delCv = self::$db->prepare("DELETE FROM {$this->table} WHERE id = :id AND id_usuario = :id_usuario");
            $delCv->bindValue(':id', $id, PDO::PARAM_INT);
            $delCv->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $delCv->execute();

            self::$db->commit();
            return [
                'success' => true,
                'message' => 'Currículum eliminado correctamente.'
            ];
        } catch (\Throwable $e) {
            self::$db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar el currículum: ' . $e->getMessage()
            ];
        }
    }
}
