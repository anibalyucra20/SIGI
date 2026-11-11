<?php

namespace Core;

class Auth
{
    private const REMEMBER_COOKIE = 'sigi_remember';
    private const REMEMBER_DAYS = 30;
    private static bool $rememberTableChecked = false;

    public static function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
    }

    public static function formatearNombreUsuario($texto): string
    {
        if (empty($texto)) return '';
        $partes = explode('_', trim($texto));
        if (count($partes) >= 3) {
            $paterno = trim($partes[0]);
            $materno = trim($partes[1]);
            $nombres = trim(implode(' ', array_slice($partes, 2)));
            return "$paterno $materno, $nombres";
        } elseif (count($partes) === 2) {
            return trim($partes[0] . ' ' . $partes[1]);
        }
        return str_replace('_', ' ', trim($texto));
    }

    public static function login(array $user)
    {
        self::start();
        $_SESSION['sigi_session_id']   = $user['id_session'];
        $_SESSION['sigi_user_id']   = $user['id'];
        $_SESSION['sigi_user_name'] = self::formatearNombreUsuario($user['apellidos_nombres']);
        $_SESSION['sigi_token'] = $user['token'];
        // Sede por defecto = la que tiene el usuario
        $_SESSION['sigi_sede_actual'] = $user['id_sede'];
        // Periodo actual = último activo
        $_SESSION['sigi_periodo_actual_id'] = self::periodoActual();
        // Regenerar ID para evitar session fixation
        session_regenerate_id(true);
    }
    public static function crearPassword(int $longitud = 8)
    {
        $parteAleatoria = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $longitud);
        $password = 'Sigi.' . $parteAleatoria . '1';
        return $password;
    }

    public static function user(): ?array
    {
        self::start();
        return (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id']) ? $_SESSION : null;
    }

    public static function limpiarSesionPhp()
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
            setcookie(session_name(), '', time() - 42000, $params['path'] ?: '/');
        }
        session_destroy();
    }

    public static function logout()
    {
        self::start();
        self::revocarRememberTokenActual();
        self::limpiarSesionPhp();
    }

    public static function validarSesion()
    {
        self::start();
        if (!empty($_SESSION['sigi_session_id'])) {
            $session_id = $_SESSION['sigi_session_id'] ?? null;
            $user_id = $_SESSION['sigi_user_id'] ?? null;

            if (!$session_id || !$user_id) {
                self::limpiarSesionPhp();
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
                self::limpiarSesionPhp(); // sesión expirada o cerrada desde admin
                return false;
            }
            // Verificar inactividad (ej. 30 minutos)
            $ultima = strtotime($sesion['fecha_hora_fin']);
            if ((time() - $ultima) > $tiempoSession) {
                // Expira sesión PHP (conserva sigi_remember si existe para auto-login en móvil)
                $db->prepare("UPDATE sigi_sesiones SET estado = 0 WHERE id = ?")
                    ->execute([$session_id]);
                self::limpiarSesionPhp();
                return false;
            }
            // Actualizar última actividad
            $db->prepare("UPDATE sigi_sesiones SET fecha_hora_fin = NOW() WHERE id = ?")
                ->execute([$session_id]);
            return true;
        }
        return false;
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    private static function cookiePath(): string
    {
        $params = session_get_cookie_params();
        $path = $params['path'] ?? '/';
        return $path !== '' ? $path : '/';
    }

    private static function setRememberCookie(string $value, int $expiresAt): void
    {
        $_COOKIE[self::REMEMBER_COOKIE] = $value;
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires' => $expiresAt,
            'path' => self::cookiePath(),
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearRememberCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        }
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 42000,
            'path' => self::cookiePath(),
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function ensureRememberTable(\PDO $db): void
    {
        if (self::$rememberTableChecked) {
            return;
        }
        $db->exec("
            CREATE TABLE IF NOT EXISTS sigi_remember_tokens (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_usuario BIGINT(20) NOT NULL,
                selector VARCHAR(32) NOT NULL UNIQUE,
                validator_hash VARCHAR(64) NOT NULL,
                id_sistema_contexto INT(11) NOT NULL DEFAULT 0,
                id_rol_contexto INT(11) NOT NULL DEFAULT 0,
                id_sede_contexto INT(11) NOT NULL DEFAULT 0,
                id_periodo_contexto INT(11) NOT NULL DEFAULT 0,
                ultima_ruta VARCHAR(255) NULL DEFAULT NULL,
                ultimo_titulo VARCHAR(160) NULL DEFAULT NULL,
                user_agent VARCHAR(255) NULL DEFAULT NULL,
                expires_at DATETIME NOT NULL,
                last_used_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_remember_usuario (id_usuario),
                INDEX idx_remember_exp (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        self::$rememberTableChecked = true;
    }

    private static function parseRememberCookie(): ?array
    {
        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if ($raw === '' || strpos($raw, ':') === false) {
            return null;
        }
        [$selector, $validator] = explode(':', $raw, 2);
        if (!ctype_xdigit($selector) || !ctype_xdigit($validator) || strlen($selector) !== 16 || strlen($validator) !== 64) {
            return null;
        }
        return ['selector' => $selector, 'validator' => $validator];
    }

    private static function revocarRememberTokenActual(): void
    {
        $parsed = self::parseRememberCookie();
        self::clearRememberCookie();
        if (!$parsed) {
            return;
        }
        try {
            $db = (new Model())->getDB();
            self::ensureRememberTable($db);
            $stmt = $db->prepare("DELETE FROM sigi_remember_tokens WHERE selector = ?");
            $stmt->execute([$parsed['selector']]);
        } catch (\Throwable $e) {
            // No bloquear el cierre de sesión si falla la limpieza
        }
    }

    public static function emitirRememberToken(int $userId, int $moduloId = 0, int $rolId = 0, int $sedeId = 0, int $periodoId = 0): bool
    {
        try {
            $db = (new Model())->getDB();
            self::ensureRememberTable($db);

            // Limpiar tokens expirados y reemplazar el del dispositivo actual si existía
            $db->exec("DELETE FROM sigi_remember_tokens WHERE expires_at <= NOW()");
            $prev = self::parseRememberCookie();
            if ($prev) {
                $db->prepare("DELETE FROM sigi_remember_tokens WHERE selector = ?")->execute([$prev['selector']]);
            }

            $selector = bin2hex(random_bytes(8));
            $validator = bin2hex(random_bytes(32));
            $validatorHash = hash('sha256', $validator);
            $expiresTs = time() + (self::REMEMBER_DAYS * 86400);
            $expiresAt = date('Y-m-d H:i:s', $expiresTs);
            $now = date('Y-m-d H:i:s');
            $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);

            $stmt = $db->prepare("
                INSERT INTO sigi_remember_tokens
                    (id_usuario, selector, validator_hash, id_sistema_contexto, id_rol_contexto, id_sede_contexto, id_periodo_contexto, user_agent, expires_at, last_used_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $selector, $validatorHash, $moduloId, $rolId, $sedeId, $periodoId, $ua, $expiresAt, $now, $now]);

            self::setRememberCookie($selector . ':' . $validator, $expiresTs);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function intentarAutoLogin(): bool
    {
        $parsed = self::parseRememberCookie();
        if (!$parsed) {
            if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
                self::clearRememberCookie();
            }
            return false;
        }

        try {
            $db = (new Model())->getDB();
            self::ensureRememberTable($db);

            $stmt = $db->prepare("SELECT * FROM sigi_remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$parsed['selector']]);
            $tokenRow = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$tokenRow) {
                self::clearRememberCookie();
                return false;
            }

            if (!hash_equals($tokenRow['validator_hash'], hash('sha256', $parsed['validator']))) {
                // Posible manipulación del token: revocar sesiones persistentes del usuario
                $db->prepare("DELETE FROM sigi_remember_tokens WHERE id_usuario = ?")->execute([$tokenRow['id_usuario']]);
                self::clearRememberCookie();
                return false;
            }

            $stmtUser = $db->prepare("SELECT * FROM sigi_usuarios WHERE id = ? AND estado = 1 LIMIT 1");
            $stmtUser->execute([$tokenRow['id_usuario']]);
            $user = $stmtUser->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $db->prepare("DELETE FROM sigi_remember_tokens WHERE selector = ?")->execute([$parsed['selector']]);
                self::clearRememberCookie();
                return false;
            }

            // Crear registro de sesión estándar en sigi_sesiones
            $llave = bin2hex(random_bytes(5));
            $tokenSesion = password_hash($llave, PASSWORD_DEFAULT);
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $stmtSes = $db->prepare("INSERT INTO sigi_sesiones (id_usuario, fecha_hora_inicio, fecha_hora_fin, token, ip, estado) VALUES (?, NOW(), NOW(), ?, ?, 1)");
            $stmtSes->execute([$user['id'], $llave, $ip]);

            $user['id_session'] = $db->lastInsertId();
            $user['token'] = $tokenSesion;

            self::login($user);

            // Cargar permisos del usuario
            $sqlPerm = "SELECT psu.id_sistema, s.nombre as sistema, psu.id_rol, r.nombre as rol
                FROM sigi_permisos_usuarios psu
                INNER JOIN sigi_sistemas_integrados s ON s.id = psu.id_sistema
                INNER JOIN sigi_roles r ON r.id = psu.id_rol
                WHERE psu.id_usuario = ? ORDER BY r.id DESC";
            $stmtPerm = $db->prepare($sqlPerm);
            $stmtPerm->execute([$user['id']]);
            $permisos = $stmtPerm->fetchAll(\PDO::FETCH_ASSOC);
            $_SESSION['sigi_permisos_usuario'] = $permisos;

            // Restaurar contexto guardado si sigue vigente en sus permisos
            $moduloRestaurado = 0;
            $rolRestaurado = 0;
            $sisGuardado = (int)($tokenRow['id_sistema_contexto'] ?? 0);
            $rolGuardado = (int)($tokenRow['id_rol_contexto'] ?? 0);

            foreach ($permisos as $p) {
                if ((int)$p['id_sistema'] === $sisGuardado && (int)$p['id_rol'] === $rolGuardado) {
                    $moduloRestaurado = $sisGuardado;
                    $rolRestaurado = $rolGuardado;
                    break;
                }
            }
            if ($moduloRestaurado === 0 && !empty($permisos)) {
                $moduloRestaurado = (int)$permisos[0]['id_sistema'];
                $rolRestaurado = (int)$permisos[0]['id_rol'];
            }
            $_SESSION['sigi_modulo_actual'] = $moduloRestaurado;
            $_SESSION['sigi_rol_actual'] = $rolRestaurado;

            if ((int)($tokenRow['id_sede_contexto'] ?? 0) > 0) {
                $_SESSION['sigi_sede_actual'] = (int)$tokenRow['id_sede_contexto'];
            }
            if ((int)($tokenRow['id_periodo_contexto'] ?? 0) > 0) {
                $_SESSION['sigi_periodo_actual_id'] = (int)$tokenRow['id_periodo_contexto'];
            }

            if (!empty($tokenRow['ultima_ruta'])) {
                $_SESSION['sigi_ultima_vista'] = [
                    'ruta' => $tokenRow['ultima_ruta'],
                    'titulo' => $tokenRow['ultimo_titulo'] ?: 'Continuar trabajo',
                    'modulo_id' => $sisGuardado,
                    'rol_id' => $rolGuardado,
                    'updated_at' => $tokenRow['last_used_at'],
                ];
            }

            // Rotar validator en cada auto-login
            $newValidator = bin2hex(random_bytes(32));
            $newHash = hash('sha256', $newValidator);
            $expiresTs = time() + (self::REMEMBER_DAYS * 86400);
            $expiresAt = date('Y-m-d H:i:s', $expiresTs);

            $stmtUpd = $db->prepare("UPDATE sigi_remember_tokens SET validator_hash = ?, last_used_at = NOW(), expires_at = ? WHERE id = ?");
            $stmtUpd->execute([$newHash, $expiresAt, $tokenRow['id']]);

            self::setRememberCookie($parsed['selector'] . ':' . $newValidator, $expiresTs);
            (new Model())->log($user['id'], 'AUTO_LOGIN', 'Ingreso automático por sesión persistente');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function registrarUltimaVista(string $ruta, string $titulo = ''): void
    {
        self::start();
        $ruta = ltrim(trim($ruta), '/');
        if ($ruta === '' || strlen($ruta) > 250 || preg_match('/[\r\n]/', $ruta) || strpos($ruta, '://') !== false) {
            return;
        }

        $titulo = trim(mb_substr($titulo, 0, 155, 'UTF-8'));
        $moduloId = (int)($_SESSION['sigi_modulo_actual'] ?? 0);
        $rolId = (int)($_SESSION['sigi_rol_actual'] ?? 0);
        $sedeId = (int)($_SESSION['sigi_sede_actual'] ?? 0);
        $periodoId = (int)($_SESSION['sigi_periodo_actual_id'] ?? 0);

        $_SESSION['sigi_ultima_vista'] = [
            'ruta' => $ruta,
            'titulo' => $titulo !== '' ? $titulo : 'Última vista trabajada',
            'modulo_id' => $moduloId,
            'rol_id' => $rolId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $parsed = self::parseRememberCookie();
        if ($parsed && !empty($_SESSION['sigi_user_id'])) {
            try {
                $db = (new Model())->getDB();
                self::ensureRememberTable($db);
                $stmt = $db->prepare("
                    UPDATE sigi_remember_tokens
                    SET ultima_ruta = ?, ultimo_titulo = ?,
                        id_sistema_contexto = ?, id_rol_contexto = ?,
                        id_sede_contexto = ?, id_periodo_contexto = ?,
                        last_used_at = NOW()
                    WHERE selector = ? AND id_usuario = ?
                ");
                $stmt->execute([
                    $ruta,
                    $titulo,
                    $moduloId,
                    $rolId,
                    $sedeId,
                    $periodoId,
                    $parsed['selector'],
                    $_SESSION['sigi_user_id'],
                ]);
            } catch (\Throwable $e) {
                // No interrumpir renderizado si falla actualización opcional
            }
        }
    }

    public static function obtenerUltimaVista(): ?array
    {
        self::start();
        if (!empty($_SESSION['sigi_ultima_vista']['ruta'])) {
            return $_SESSION['sigi_ultima_vista'];
        }

        $parsed = self::parseRememberCookie();
        if ($parsed && !empty($_SESSION['sigi_user_id'])) {
            try {
                $db = (new Model())->getDB();
                self::ensureRememberTable($db);
                $stmt = $db->prepare("
                    SELECT ultima_ruta, ultimo_titulo, id_sistema_contexto, id_rol_contexto, last_used_at
                    FROM sigi_remember_tokens
                    WHERE selector = ? AND id_usuario = ? AND expires_at > NOW()
                    LIMIT 1
                ");
                $stmt->execute([$parsed['selector'], $_SESSION['sigi_user_id']]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['ultima_ruta'])) {
                    $_SESSION['sigi_ultima_vista'] = [
                        'ruta' => $row['ultima_ruta'],
                        'titulo' => $row['ultimo_titulo'] ?: 'Última vista trabajada',
                        'modulo_id' => (int)$row['id_sistema_contexto'],
                        'rol_id' => (int)$row['id_rol_contexto'],
                        'updated_at' => $row['last_used_at'],
                    ];
                    return $_SESSION['sigi_ultima_vista'];
                }
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    public static function limpiarUltimaVista(): void
    {
        self::start();
        unset($_SESSION['sigi_ultima_vista']);
        $parsed = self::parseRememberCookie();
        if ($parsed && !empty($_SESSION['sigi_user_id'])) {
            try {
                $db = (new Model())->getDB();
                self::ensureRememberTable($db);
                $db->prepare("UPDATE sigi_remember_tokens SET ultima_ruta = NULL, ultimo_titulo = NULL WHERE selector = ? AND id_usuario = ?")
                    ->execute([$parsed['selector'], $_SESSION['sigi_user_id']]);
            } catch (\Throwable $e) {
                // Ignorar fallo no crítico
            }
        }
    }

    public static function sincronizarContextoDesdeRuta(string $ruta, int $rolPreferido = 0): void
    {
        self::start();
        $ruta = ltrim(trim($ruta), '/');
        if ($ruta === '' || empty($_SESSION['sigi_permisos_usuario'])) {
            return;
        }
        $segmento = strtolower(explode('/', $ruta)[0] ?? '');
        if ($segmento === '' || in_array($segmento, ['intranet', 'auth', 'login', 'logout'], true)) {
            return;
        }

        try {
            $db = (new Model())->getDB();
            $stmt = $db->prepare("SELECT id FROM sigi_sistemas_integrados WHERE LOWER(codigo) = ? LIMIT 1");
            $stmt->execute([$segmento]);
            $idSistema = (int)($stmt->fetchColumn() ?: 0);
            if ($idSistema <= 0) {
                return;
            }

            $rolElegido = 0;
            foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                if ((int)$permiso['id_sistema'] === $idSistema) {
                    if ($rolPreferido > 0 && (int)$permiso['id_rol'] === $rolPreferido) {
                        $rolElegido = $rolPreferido;
                        break;
                    }
                    if ($rolElegido === 0) {
                        $rolElegido = (int)$permiso['id_rol'];
                    }
                }
            }

            if ($rolElegido > 0) {
                $_SESSION['sigi_modulo_actual'] = $idSistema;
                $_SESSION['sigi_rol_actual'] = $rolElegido;
            }
        } catch (\Throwable $e) {
            // Conservar contexto actual en caso de error
        }
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
        if (empty($roles)) return true; // Cualquier rol en EFSRT
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function esAdminEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 1);     // ADMINISTRADOR
    }
    public static function esDirectorEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 2);     // DIRECTOR
    }
    public static function esSecretarioPEEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && in_array($_SESSION['sigi_rol_actual'], [1, 3])); // SECRETARIO ACADEMICO O ADMIN
    }
    public static function esJUAEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && $_SESSION['sigi_rol_actual'] == 4);     // JEFE DE UNIDAD ACADEMICA
    }
    public static function esCoordinadorPEEfsrt()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 11    // EFSRT
            && in_array($_SESSION['sigi_rol_actual'], [1, 5])); // COORDINADOR ACADEMICO O ADMIN
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

    // ------------------------------------------ ROLES EVALUACION DOCENTE (MODULO 12: EVALUACION) --------------------
    public static function tieneRolEnEvaluacion($roles = [])
    {
        if (!isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])) {
            return false;
        }
        if (empty($roles)) return true;
        return in_array($_SESSION['sigi_rol_actual'], (array)$roles);
    }
    public static function tieneRolEnEvaluacionDocente($roles = [])
    {
        return self::tieneRolEnEvaluacion($roles);
    }

    public static function esAdminEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && $_SESSION['sigi_rol_actual'] == 1);
    }
    public static function esAdminEvaluacionDocente()
    {
        return self::esAdminEvaluacion();
    }

    public static function esDirectorEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && $_SESSION['sigi_rol_actual'] == 2);
    }
    public static function esDirectorEvaluacionDocente()
    {
        return self::esDirectorEvaluacion();
    }

    public static function esSecretarioAcadEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && in_array($_SESSION['sigi_rol_actual'], [1, 3]));
    }
    public static function esSecretarioAcadEvaluacionDocente()
    {
        return self::esSecretarioAcadEvaluacion();
    }

    public static function esJUAEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && $_SESSION['sigi_rol_actual'] == 4);
    }
    public static function esJUAEvaluacionDocente()
    {
        return self::esJUAEvaluacion();
    }

    public static function esCoordinadorPEEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && in_array($_SESSION['sigi_rol_actual'], [1, 2, 3, 4, 5]));
    }
    public static function esCoordinadorPEEvaluacionDocente()
    {
        return self::esCoordinadorPEEvaluacion();
    }

    public static function puedeVerTodosLosProgramasEvaluacion()
    {
        if (!isset($_SESSION['sigi_rol_actual'])) {
            return false;
        }
        // Roles 1 (Admin), 2 (Director), 3 (Secretario Académico), 4 (Jefe Unidad Académica)
        return in_array((int)$_SESSION['sigi_rol_actual'], [1, 2, 3, 4]);
    }

    public static function esCoordinadorExclusivoEvaluacion()
    {
        return (isset($_SESSION['sigi_rol_actual'])
            && (int)$_SESSION['sigi_rol_actual'] === 5);
    }

    public static function esDocenteEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && $_SESSION['sigi_rol_actual'] == 6);
    }
    public static function esDocenteEvaluacionDocente()
    {
        return self::esDocenteEvaluacion();
    }

    public static function esEstudianteEvaluacion()
    {
        return (isset($_SESSION['sigi_modulo_actual'], $_SESSION['sigi_rol_actual'])
            && $_SESSION['sigi_modulo_actual'] == 12
            && $_SESSION['sigi_rol_actual'] == 7);
    }
    public static function esEstudianteEvaluacionDocente()
    {
        return self::esEstudianteEvaluacion();
    }
}
