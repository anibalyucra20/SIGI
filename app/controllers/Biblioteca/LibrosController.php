<?php

namespace App\Controllers\Biblioteca;

require_once __DIR__ . '/../../../app/models/Biblioteca/Libros.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/UnidadDidactica.php';

use App\Models\Biblioteca\Libros;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Programa;
use App\Models\Sigi\UnidadDidactica;
use Core\Controller;

class LibrosController extends Controller
{
    protected Libros $model;
    protected DatosSistema $datosSistema;
    protected Programa $objPrograma;
    protected UnidadDidactica $objUds;

    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::tieneRolEnBiblioteca()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->model        = new Libros();
        $this->datosSistema = new DatosSistema();
        $this->objPrograma  = new Programa();
        $this->objUds  = new UnidadDidactica();
    }

    /* ===================== Helpers API Maestro ===================== */

    private function apiBase(): string
    {
        return rtrim(API_BASE_URL, '/') . '/api';
    }

    private function isDev(): bool
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        return $host === 'localhost' || $host === '127.0.0.1' || getenv('APP_ENV') === 'local';
    }

    /**
     * Llamada batch al Maestro: /library/batch?ids=1,2,3
     * Devuelve array de libros normalizados o [].
     */
    private function apiBatch(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($n) => $n > 0));
        if (!$ids) return [];

        $apiBase = $this->apiBase();
        $sistema = $this->datosSistema->buscar();
        $apiKey  = $sistema['token_sistema'] ?? '';

        $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $apiHost     = parse_url($apiBase, PHP_URL_HOST) ?: '';

        // Fusible anti-recursión: no llamar si apunta al mismo host o localhost
        if (!$apiHost || strtolower($apiHost) === $currentHost || in_array(strtolower($apiHost), ['localhost', '127.0.0.1'], true)) {
            error_log('[LibrosController@apiBatch] API_BASE_URL inválido o apunta al mismo host: ' . ($apiHost ?: '(empty)'));
            return [];
        }

        $url = $apiBase . '/library/batch?ids=' . implode(',', $ids);

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'X-Api-Key: ' . $apiKey,
                'Accept: application/json',
                'User-Agent: SIGI/1.0 (+curl)'
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => 20,
        ];

        if ($this->isDev()) {
            // En local, desactivar verificación SSL (mientras resuelves el cert intermedio)
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        curl_setopt_array($ch, $opts);
        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            error_log("[LibrosController@apiBatch] HTTP $status :: $err :: $url");
            return [];
        }

        $json = json_decode($body, true);
        if (isset($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }
        if (is_array($json)) {
            // Por si el batch devuelve array plano
            return $json;
        }
        return [];
    }

    /* ===================== Páginas principales ===================== */

    public function index()
    {
        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/index', [
            'sistema'   => $sistema,
            'module'    => 'biblioteca',
            'pageTitle' => 'libros'
        ]);
    }

    public function Vinculados()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            $planes = $modulos = $semestres = $unidades = $competencias = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/librosVinculados', [
            'sistema'               => $sistema,
            'programas'             => $programas ?? [],
            'planes'                => $planes ?? [],
            'modulos'               => $modulos ?? [],
            'semestres'             => $semestres ?? [],
            'unidades'              => $unidades ?? [],
            'competencias'          => $competencias ?? [],
            'id_programa_selected'  => $id_programa_selected ?? '',
            'id_plan_selected'      => $id_plan_selected ?? '',
            'id_modulo_selected'    => $id_modulo_selected ?? '',
            'id_semestre_selected'  => $id_semestre_selected ?? '',
            'module'                => 'biblioteca',
            'pageTitle'             => 'libros'
        ]);
    }

    public function Nuevo()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            $planes = $modulos = $semestres = $unidades = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/nuevo', [
            'sistema'               => $sistema,
            'programas'             => $programas ?? [],
            'planes'                => $planes ?? [],
            'modulos'               => $modulos ?? [],
            'semestres'             => $semestres ?? [],
            'unidades'              => $unidades ?? [],
            'id_programa_selected'  => $id_programa_selected ?? '',
            'id_plan_selected'      => $id_plan_selected ?? '',
            'id_modulo_selected'    => $id_modulo_selected ?? '',
            'id_semestre_selected'  => $id_semestre_selected ?? '',
            'module'                => 'biblioteca',
            'pageTitle'             => 'nuevo libro'
        ]);
    }

    public function Vincular()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            $planes = $modulos = $semestres = $unidades = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/vincular', [
            'sistema'               => $sistema,
            'programas'             => $programas ?? [],
            'planes'                => $planes ?? [],
            'modulos'               => $modulos ?? [],
            'semestres'             => $semestres ?? [],
            'unidades'              => $unidades ?? [],
            'id_programa_selected'  => $id_programa_selected ?? '',
            'id_plan_selected'      => $id_plan_selected ?? '',
            'id_modulo_selected'    => $id_modulo_selected ?? '',
            'id_semestre_selected'  => $id_semestre_selected ?? '',
            'module'                => 'biblioteca',
            'pageTitle'             => 'vincular libro'
        ]);
    }

    /* ===================== Vista detalle y lector ===================== */

    public function Ver($id)
    {
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        $esFavorito = false;

        $sistema = $this->datosSistema->buscar();
        $apiKey  = $sistema['token_sistema'] ?? '';
        $apiBase = $this->apiBase();
        $url     = $apiBase . '/library/show/' . rawurlencode((string)$id);

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $apiKey, 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => 'SIGI/1.0 (+curl)',
        ];
        if ($this->isDev()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $_SESSION['flash_error'] = "Error API (cURL): $err";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            $_SESSION['flash_error'] = "Error API ($status): Respuesta no válida del servidor.";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }
        if (isset($json['error'])) {
            $msg = $json['error']['message'] ?? 'Libro no encontrado.';
            $_SESSION['flash_error'] = "Error API ($status): $msg";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }

        $libro = $json['data'] ?? $json;
        if ($id_usuario) {
            $esFavorito = (bool) $this->model->buscarFavorito($id_usuario, (int)$id);
        }
        $libro['_es_favorito'] = $esFavorito;

        $this->view('biblioteca/libros/ver', [
            'sistema'   => $sistema,
            'libro'     => $libro,
            'module'    => 'biblioteca',
            'pageTitle' => 'Biblioteca - ' . ($libro['titulo'] ?? 'Libro'),
        ]);
    }

    public function Leer($id)
    {
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        $esFavorito = false;
        // Busca libro para lector igual que en Ver()
        $sistema = $this->datosSistema->buscar();
        $apiKey  = $sistema['token_sistema'] ?? '';
        $apiBase = $this->apiBase();
        $url     = $apiBase . '/library/show/' . rawurlencode((string)$id);

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $apiKey, 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => 'SIGI/1.0 (+curl)',
        ];
        if ($this->isDev()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $_SESSION['flash_error'] = "Error API (cURL): $err";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            $_SESSION['flash_error'] = "Error API ($status): Respuesta no válida del servidor.";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }
        if (isset($json['error'])) {
            $msg = $json['error']['message'] ?? 'Libro no encontrado.';
            $_SESSION['flash_error'] = "Error API ($status): $msg";
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }

        $libro = $json['data'] ?? $json;
        $esFavorito = (bool) $this->model->buscarFavorito($id_usuario, (int)$id);
        $libro['_es_favorito'] = $esFavorito;
        // registra lectura con regla de 3 días
        $this->registrarLectura((int)$id);

        $this->view('biblioteca/libros/leer', [
            'sistema'   => $sistema,
            'libro'     => $libro,
            'module'    => 'biblioteca',
            'pageTitle' => 'Biblioteca - ' . ($libro['titulo'] ?? 'Libro')
        ]);
    }

    /* ===================== Favoritos (Server-side batch) ===================== */

    public function Favoritos()
    {
        if (!\Core\Auth::tieneRolEnBiblioteca()) {
            $_SESSION['flash_error'] = "Inicia sesión para ver tus favoritos.";
            header('Location: ' . BASE_URL . '/auth/login');
            return;
        }

        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $sistema   = $this->datosSistema->buscar();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = min(24, max(1, (int)($_GET['per_page'] ?? 8)));
        $off  = ($page - 1) * $per;

        // 1) Contar y listar IDs locales
        $total = (int)$this->model->contarFavoritos($idUsuario);
        $rows  = ($total > 0) ? $this->model->listarFavoritos($idUsuario, $off, $per) : [];
        $ids   = array_values(array_filter(array_map(fn($r) => (int)($r['id_libro'] ?? 0), $rows), fn($n) => $n > 0));

        // 2) ÚNICA llamada batch al Maestro
        $libros = $ids ? $this->apiBatch($ids) : [];

        $programas = $this->objPrograma->getTodosProgramas();
        // Crea un array donde la clave es el id del programa
        $programasPorId = array_column($programas, null, 'id');

        $UDs = $this->objUds->getAll();
        // Crea un array donde la clave es el id del programa
        $UDsPorId = array_column($UDs, null, 'id');

        //agregamos datos al array de libros favoritos
        foreach ($libros as &$libro) {
            $idProg = $libro['id_programa_estudio'];
            $idUd = $libro['id_unidad_didactica'];
            $libro['programa_nombre'] = $programasPorId[$idProg]['nombre'] ?? null;
            $libro['ud_nombre'] = $UDsPorId[$idUd]['nombre'] ?? null;
        }
        // 3) Render vista con datos y paginador server-side
        $this->view('biblioteca/libros/favoritos', [
            'sistema'    => $sistema,
            'libros'     => $libros,                 // libros de esta página (8)
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $per,
            'module'     => 'biblioteca',
            'pageTitle'  => 'Mis favoritos'
        ]);
    }

    /* ===================== Acciones varias ===================== */

    public function ActualizarFavorito($id)
    {
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        if (!$id_usuario) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['ok' => false, 'error' => 'AUTH', 'message' => 'No autenticado']);
                return;
            }
            $_SESSION['flash_error'] = 'Inicia sesión para usar favoritos.';
            header('Location: ' . BASE_URL . '/auth/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = (int)$id;
        header('Content-Type: application/json; charset=utf-8');

        try {
            $favorito = $this->model->buscarFavorito($id_usuario, $id);
            if (!$favorito) {
                $this->model->registrarFavorito($id_usuario, $id);
                echo json_encode(['ok' => true, 'is_favorite' => true]);
            } else {
                $this->model->eliminarFavorito($id_usuario, $id);
                echo json_encode(['ok' => true, 'is_favorite' => false]);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error al actualizar favorito']);
        }
    }

    public function registrarLectura(int $idLibro): void
    {
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;
        if (!$id_usuario) return;

        $lectura = $this->model->buscarLectura($id_usuario, $idLibro);
        $hoy     = date('Y-m-d');

        // si no existe, registrar; si existe, registrar sólo cada 3 días
        if (!$lectura) {
            $this->model->registrarLectura($id_usuario, $idLibro);
        } else {
            $ultima = substr((string)($lectura['fecha_registro'] ?? ''), 0, 10);
            if ($ultima && (strtotime($hoy) - strtotime($ultima)) >= (3 * 24 * 60 * 60)) {
                $this->model->registrarLectura($id_usuario, $idLibro);
            }
        }
    }
}
