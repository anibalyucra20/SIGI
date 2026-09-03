<?php

namespace App\Helpers;

class MoodleIntegrator
{
    private $token;
    private $domain;

    public function __construct()
    {
        // Asumiendo que definiste las constantes en config.php
    }

    /* =========================================================
       SECCIÓN 1: GESTIÓN DE CATEGORÍAS (JERARQUÍA 8 NIVELES)
       ========================================================= */

    /**
     * Construye o verifica el árbol de categorías completo.
     * @param array $jerarquia Array ordenado de nombres ['2024-I', 'Sede Central', 'Enfermería', ...]
     * @return int|false El ID de la categoría final (Nivel 8 - Sección) donde irá el curso.
     */
    public function ensureCategoryTree(array $jerarquia)
    {
        if (empty($this->token)) return false;

        $parentId = 0; // 0 es la raíz en Moodle

        foreach ($jerarquia as $nombreCategoria) {
            // Limpiamos el nombre para evitar problemas
            $nombreCategoria = trim($nombreCategoria);

            // 1. Buscamos si existe esta categoría DENTRO del padre actual
            $catId = $this->findCategory($nombreCategoria, $parentId);

            // 2. Si no existe, la creamos
            if (!$catId) {
                $catId = $this->createCategory($nombreCategoria, $parentId);
            }

            // 3. Si falló algo crítico, detenemos
            if (!$catId) return false;

            // 4. El hijo actual se convierte en el padre del siguiente nivel
            $parentId = $catId;
        }

        return $parentId; // Retorna el ID de la última categoría (Sección)
    }

    private function findCategory($name, $parentId)
    {
        // Moodle no tiene una búsqueda directa "get_category_by_name_and_parent" eficiente.
        // Usamos core_course_get_categories con filtros.
        $params = [
            'criteria' => [
                ['key' => 'parent', 'value' => $parentId],
                ['key' => 'name',  'value' => $name]
            ]
        ];
        $resp = $this->call('core_course_get_categories', $params);

        if (!empty($resp) && is_array($resp)) {
            return $resp[0]['id'];
        }
        return false;
    }

    private function createCategory($name, $parentId)
    {
        $params = [
            'categories' => [
                [
                    'name' => $name,
                    'parent' => $parentId,
                    // 'idnumber' => ... Opcional: podrías generar un ID único si quisieras
                ]
            ]
        ];
        $resp = $this->call('core_course_create_categories', $params);

        return isset($resp[0]['id']) ? $resp[0]['id'] : false;
    }

    /* =========================================================
       SECCIÓN 2: GESTIÓN DE DOCENTES Y CURSOS
       ========================================================= */

    /**
     * Crea el curso dentro de la categoría final (Sección).
     */
    public function createCourse($fullname, $shortname, $categoryId, $startDateTimestamp = null)
    {
        // Verificar si el curso ya existe por shortname
        $check = $this->call('core_course_get_courses_by_field', [
            'field' => 'shortname',
            'value' => $shortname
        ]);

        if (!empty($check['courses'])) {
            return $check['courses'][0]['id']; // Ya existe, devolvemos ID
        }

        // Crear curso
        $params = [
            'courses' => [
                [
                    'fullname' => $fullname,
                    'shortname' => $shortname,
                    'categoryid' => $categoryId,
                    'startdate' => $startDateTimestamp ?? time(),
                    'format' => 'topics',
                    'numsections' => 4 // Por defecto 4 temas
                ]
            ]
        ];

        $resp = $this->call('core_course_create_courses', $params);
        return isset($resp[0]['id']) ? $resp[0]['id'] : false;
    }

    /**
     * Sincroniza (Crea o Actualiza) un usuario de SIGI hacia Moodle.
     * Utiliza el ID de SIGI como 'idnumber' en Moodle para mantener el vínculo.
     * * @param int $sigiId El ID primario de la tabla sigi_usuarios
     * @param string $passwordPlano La contraseña SIN hash (solo si se está creando/cambiando)
     */
    public function syncUser($sigiId, $dni, $email, $nombres, $apellidos, $passwordPlano = null)
    {
        // 1. Buscamos en Moodle por tu ID de SIGI (campo 'idnumber')
        // Esto es mucho más seguro que buscar por DNI
        $moodleUser = $this->call('core_user_get_users_by_field', [
            'field' => 'idnumber',
            'values' => [(string)$sigiId]
        ]);

        $userPayload = [
            'username'      => $dni,
            'firstname'     => $nombres,
            'lastname'      => $apellidos,
            'email'         => $email,
            'idnumber'      => (string)$sigiId, // <--- AQUÍ ESTÁ TU VÍNCULO SAGRADO
            'auth'          => 'manual',
        ];


        // Si enviamos contraseña (creación o cambio de clave)
        if ($passwordPlano) {
            $userPayload['password'] = $passwordPlano;
            $userPayload['preferences'] = [['type' => 'auth_forcepasswordchange', 'value' => 0]];
        }

        // CASO A: ACTUALIZAR (Si ya existe)
        if (!empty($moodleUser) && !isset($moodleUser['exception'])) {
            $moodleInternalId = $moodleUser[0]['id'];

            // Para actualizar, Moodle exige el ID interno
            $userPayload['id'] = $moodleInternalId;

            // Llamamos a la función de UPDATE
            $this->call('core_user_update_users', ['users' => [$userPayload]]);
            $_SESSION['flash_success'] .= '<br>Usuario actualizado en Moodle.';
            return $moodleInternalId;
        }
        // Generar contraseña aleatoria en caso que no enviaron para actualizar y no enviaron contraseña
        if ($passwordPlano == null) {
            $parteAleatoria = bin2hex(random_bytes(6)); // Esta es la que enviaremos a Moodle
            $passwordPlano = 'Sigi.' . $parteAleatoria;
            $userPayload['password'] = $passwordPlano;
        }
        // CASO B: CREAR (Si no existe)
        if ($passwordPlano) {
            // Solo creamos si tenemos contraseña. Si no, fallará.
            $resp = $this->call('core_user_create_users', ['users' => [$userPayload]]);
            $_SESSION['flash_success'] .= '<br>Usuario creado en Moodle.';
            return isset($resp[0]['id']) ? $resp[0]['id'] : false;
        }
        $_SESSION['flash_error'] .= '<br>Usuario no creado en Moodle.';
        return false;
    }

    public function enroll($userId, $courseId, $roleId)
    {
        $resp = $this->call('enrol_manual_enrol_users', ['enrolments' => [[
            'roleid' => $roleId,
            'userid' => $userId,
            'courseid' => $courseId
        ]]]);
        return empty($resp);
    }

    /* Utilitario cURL Privado */
    private function call($function, $params)
    {
        $url = MOODLE_URL . '/webservice/rest/server.php' .
            '?wstoken=' . MOODLE_TOKEN .
            '&wsfunction=' . $function .
            '&moodlewsrestformat=json';


        // 2. Iniciar cURL con manejo de errores
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        // IMPORTANTE: http_build_query maneja correctamente los índices para arrays anidados
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        // Si estás en localhost sin SSL válido, descomenta la siguiente línea temporalmente:
        // ... configuración previa ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 👇 AGREGA ESTAS LÍNEAS PARA WAMP/LOCAL 👇
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * Genera la URL de auto-login hacia Moodle.
     * @param int $sigiUserId El ID del usuario en SIGI (que es el idnumber en Moodle)
     * @return string URL completa para redirigir
     */
    public function getAutoLoginUrl($sigiUserId, $hacia = null, $id = null)
    {
        if (!defined('MOODLE_SSO_KEY')) return '#';
        $datos = [
            'uid'  => $sigiUserId,
            'time' => time()
        ];
        if ($hacia) {
            $datos['hacia'] = $hacia;
        }
        if ($id) {
            $datos['id'] = $id;
        }
        // 1. Datos a encriptar: ID + Timestamp (para que el link caduque en 60 seg)
        $data = json_encode($datos);

        // 2. Encriptación AES-256-CBC
        $method = "AES-256-CBC";
        $key = hash('sha256', MOODLE_SSO_KEY);
        $iv = substr(hash('sha256', 'iv_secret'), 0, 16); // Vector de inicialización fijo o dinámico

        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);

        // 3. Generar URL segura (urlencode es vital)
        $token = urlencode(base64_encode($encrypted));

        // Asumimos que crearemos una carpeta "local/sigi" en moodle
        return MOODLE_URL . "/local/sigi/sso.php?token=" . $token;
    }



    /**
     * Extrae las calificaciones calculadas del Gradebook de Moodle para un curso.
     * @param int $courseId ID del curso en Moodle
     * @return array|false Retorna el array 'usergrades' o false en caso de error.
     */
    public function getCourseGrades(int $courseId)
    {
        $params = [
            'courseid' => $courseId
        ];

        $resp = $this->call('gradereport_user_get_grade_items', $params);

        // Moodle devuelve 'usergrades' si fue exitoso, o 'exception' si hubo error
        if (isset($resp['exception']) || empty($resp['usergrades'])) {
            error_log("Error Moodle getCourseGrades: " . json_encode($resp));
            return false;
        }

        return $resp['usergrades'];
    }

    /**
     * Obtiene los ítems de calificación (tareas, cuestionarios, foros) de un curso en Moodle.
     * Útil para poblar selects de vinculación.
     */
    public function getGradeItemsConfig(int $courseId)
    {
        $params = ['courseid' => $courseId];
        // core_grades_get_gradeitems es ideal para obtener la estructura del libro de calificaciones
        $resp = $this->call('core_grades_get_gradeitems', $params);

        if (isset($resp['exception']) || !isset($resp['gradeItems'])) {
            return false;
        }

        $actividades = [];
        foreach ($resp['gradeItems'] as $item) {
            // Filtramos: Solo queremos actividades reales (ignoramos promedios de categoría o curso)
            if ($item['itemtype'] === 'mod') {
                $actividades[] = [
                    'id' => $item['id'], // Este es el moodle_grade_item_id
                    'cmid' => $item['iteminstance'], // Usualmente la instancia o cmid según la versión
                    'nombre' => $item['itemname'],
                    'modulo' => $item['itemmodule'], // ej: 'assign', 'quiz'
                    'max_grade' => $item['grademax']
                ];
            }
        }
        return $actividades;
    }
}
