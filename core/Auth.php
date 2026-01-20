<?php

namespace Core;

class Auth
{
    public static function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
    }

    public static function login(array $user)
    {
        self::start();
        $_SESSION['sigi_session_id']   = $user['id_session'];
        $_SESSION['sigi_user_id']   = $user['id'];
        $apellidos_nombres = explode('_', trim($user['apellidos_nombres']));
        $_SESSION['sigi_user_name'] = $apellidos_nombres[0] . ' ' . $apellidos_nombres[1] . ', ' . $apellidos_nombres[2];
        $_SESSION['sigi_token'] = $user['token'];
        // Sede por defecto = la que tiene el usuario
        $_SESSION['sigi_sede_actual'] = $user['id_sede'];
        // Periodo actual = último activo
        $_SESSION['sigi_periodo_actual_id'] = self::periodoActual();
        // Regenerar ID para evitar session fixation
        session_regenerate_id(true);
    }

    public static function user(): ?array
    {
        self::start();
        return (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id']) ? $_SESSION : null;
    }

    public static function logout()
    {
        self::start();

        if (!empty($_SESSION['sigi_session_id'])) {
            $db = (new Model())->getDB();
            $stmt = $db->prepare("UPDATE sigi_sesiones SET estado = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['sigi_session_id']]);
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }

    public static function validarSesion()
    {
        self::start();
        if (!empty($_SESSION['sigi_session_id'])) {
            $session_id = $_SESSION['sigi_session_id'] ?? null;
            $user_id = $_SESSION['sigi_user_id'] ?? null;

            if (!$session_id || !$user_id) {
                self::logout();
                return false;
            }
            $db = (new Model())->getDB();
            $stmt = $db->prepare("SELECT * FROM sigi_sesiones 
                           WHERE id = ? AND id_usuario = ? AND estado = 1");
            $stmt->execute([$session_id, $user_id]);
            $sesion = $stmt->fetch();

            $stmt = $db->prepare("SELECT duracion_sesion FROM sigi_datos_sistema LIMIT 1");
            $stmt->execute();
            $datosSistema = $stmt->fetch();
            $tiempoSession = $datosSistema['duracion_sesion'] * 60;

            if (!$sesion) {
                self::logout(); // sesión expirada o cerrada desde admin
                return false;
            }
            // Verificar inactividad (ej. 30 minutos)
            $ultima = strtotime($sesion['fecha_hora_fin']);
            if ((time() - $ultima) > $tiempoSession) {
                // Expira sesión
                $db->prepare("UPDATE sigi_sesiones SET estado = 0 WHERE id = ?")
                    ->execute([$session_id]);
                self::logout();
                return false;
            }
            // Actualizar última actividad
            $db->prepare("UPDATE sigi_sesiones SET fecha_hora_fin = NOW() WHERE id = ?")
                ->execute([$session_id]);
            return true;
        }
        return false;
    }


    private static function periodoActual(): int
    {
        $db = (new Model())->getDB();

        $sql = "SELECT id
            FROM sigi_periodo_academico
            ORDER BY fecha_inicio DESC
            LIMIT 1";                     // ← sin WHERE

        return (int) ($db->query($sql)->fetchColumn() ?: 0);
    }

    // VALIDACION DE ROL Y PERMISOS SIGI
    public static function esAdminSigi()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 1    // SIGI
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }

    public static function tieneRolEnSigi($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 1) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en SIGI
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }

    // VALIDACION DE ROL Y PERMISOS ACADEMICO

    // ------------------------------------------ ROLES ACADEMICO -------------------------------------------------------

    public static function tieneRolEnAcademico($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 2) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en ACADEMICO
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDirectorAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 2);     // DIRECTOR
    }
    public static function esSecretarioAcadAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 3);     // SECRETARIO ACADEMICO
    }
    public static function esJUAAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 4);     // JEFE DE UNIDAD ACADEMICA
    }
    public static function esCoordinadorPEAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 5);     // COORDINADOR ACADEMICO
    }
    public static function esDocenteAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }
    public static function esEstudianteAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 7);     // DOCENTE
    }


    // ------------------------------------------ ROLES TUTORIA -------------------------------------------------------
    public static function tieneRolEnTutoria($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 3) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en TUTORIA
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminTutoria()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 3    // TUTORIA
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDocenteTutoria()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 3    // TUTORIA
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }
    public static function esEstudianteTutoria()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 3    // TUTORIA
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }

    // ------------------------------------------ ROLES BIBLIOTECA -------------------------------------------------------
    public static function tieneRolEnBiblioteca($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 4) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en BIBLIOTECA
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminBiblioteca()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 4    // BIBLIOTECA
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDocenteBiblioteca()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 4    // BIBLIOTECA
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }
    public static function esEstudianteBiblioteca()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 4    // BIBLIOTECA
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }
    // ------------------------------------------ ROLES ADMISION -------------------------------------------------------
    public static function tieneRolEnAdmision($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 5) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en ADMISION
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminAdmision()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 5    // ADMISION
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDocenteAdmision()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 5    // ADMISION
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }


    // ------------------------------------------ ROLES EGRESADOS -------------------------------------------------------
    public static function tieneRolEnEgresados($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 6) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en EGRESADOS
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminEgresados()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 6    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esEstudianteEgresados()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 6    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }


    // ------------------------------------------ ROLES BOLSA LABORAL -------------------------------------------------------
    public static function tieneRolEnBolsa($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 7) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en BOLSA LABORAL
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminBolsa()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 7    // BOLSA LABORAL
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esEstudianteBolsa()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 7    // BOLSA LABORAL
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }
    public static function esExternoBolsa()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 7    // BOLSA LABORAL
            && $_SESSION['sigi_rol_actual'] == 8);     // EXTERNO
    }



    // ------------------------------------------ ROLES AULA -------------------------------------------------------
    public static function tieneRolEnAula($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 8) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en AULA
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminAula()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 8    // AULA
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esJUAAula()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 8    // AULA
            && $_SESSION['sigi_rol_actual'] == 4);     // JEFE DE UNIDAD ACADEMICA
    }
    public static function esCoordinadorPEAula()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 8    // AULA
            && $_SESSION['sigi_rol_actual'] == 5);     // COORDINADOR ACADEMICO
    }
    public static function esDocenteAula()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 8    // AULA
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }
    public static function esEstudianteAula()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 8    // AULA
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }


    // ------------------------------------------ ROLES INVENTARIO -------------------------------------------------------
    public static function tieneRolEnInventario($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 9) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en INVENTARIO
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminInventario()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 9    // INVENTARIO
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }

    public static function esCoordinadorPEInventario()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 9    // INVENTARIO
            && $_SESSION['sigi_rol_actual'] == 5);     // COORDINADOR ACADEMICO
    }
    public static function esDocenteInventario()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 9    // INVENTARIO
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }


    // ------------------------------------------ ROLES CAJA -------------------------------------------------------
    public static function tieneRolEnCaja($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 10) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en CAJA
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminCaja()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 10    // CAJA
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDocenteCaja()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 10    // CAJA
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }


    // ------------------------------------------ ROLES EFSRT -------------------------------------------------------
    public static function tieneRolEnEfsrt($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        /*if ($_SESSION['sigi_modulo_actual'] != 11) {
            return false;
        }*/
        if (empty($roles)) return true; // Cualquier rol en EFSRT
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esSecretarioPEEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 5);     // SECRETARIO ACADEMICO
    }
    public static function esCoordinadorPEEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 5);     // COORDINADOR ACADEMICO
    }
    public static function esDocenteEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 6);     // DOCENTE
    }
    public static function esEstudianteEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 7);     // ESTUDIANTE
    }
}
