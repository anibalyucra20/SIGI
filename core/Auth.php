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
        $_SESSION['sigi_user_id']   = $user['id'];
        $_SESSION['sigi_user_name'] = $user['apellidos_nombres'];
        // Sede por defecto = la que tiene el usuario
        $_SESSION['sigi_sede_actual'] = $user['id_sede'];
        // Periodo actual = último activo
        $_SESSION['sigi_periodo_actual_id'] = self::periodoActual();
    }

    public static function user(): ?array
    {
        self::start();
        return (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id']) ? $_SESSION : null;
    }

    /*
    public static function logout()
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"],
                //$params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }*/

    public static function logout()
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, '/SIGI_2025');
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
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
        if ($_SESSION['sigi_modulo_actual'] != 1) {
            return false;
        }
        if (empty($roles)) return true; // Cualquier rol en SIGI
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }

    // VALIDACION DE ROL Y PERMISOS ACADEMICO
    public static function esAdminAcademico()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 2    // ACADEMICO
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }

    public static function tieneRolEnAcademico($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        if ($_SESSION['sigi_modulo_actual'] != 2) {
            return false;
        }
        if (empty($roles)) return true; // Cualquier rol en ACADEMICO
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }

    // VALIDACION DE ROL Y PERMISOS ACADEMICO
}
