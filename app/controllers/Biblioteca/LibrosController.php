<?php

namespace App\Controllers\Biblioteca;

require_once __DIR__ . '/../../../app/models/Biblioteca/Libros.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use App\Models\Biblioteca\Libros;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Programa;
use Core\Controller;

/**
 * Controlador de Biblioteca > Libros
 *
 * Notas clave:
 * - Acceso por rol SOLO para páginas de administración (Vinculados, Nuevo, Vincular).
 * - Favoritos y vista/lectura requieren usuario autenticado, pero no rol de biblioteca.
 * - Helper de API centralizado: firma X-Api-Key, ignora SSL en dev y evita bucles.
 */
class LibrosController extends Controller
{
    protected Libros $model;
    protected DatosSistema $datosSistema;
    protected Programa $objPrograma;

    public function __construct()
    {
        parent::__construct();
        $this->model        = new Libros();
        $this->datosSistema = new DatosSistema();
        $this->objPrograma  = new Programa();
    }

    /* ============================================================
     * Helpers de API
     * ============================================================ */

    /**
     * Devuelve la base de la API asegurando que termina en /api
     * Soporta que API_BASE_URL venga con o sin /api.
     */
    private function apiBase(): string
    {
        $base = rtrim(API_BASE_URL ?? '', '/');
        if ($base === '') return '';

        // Si NO tiene /api al final, lo agregamos
        if (substr($base, -4) !== '/api') {
            $base .= '/api';
        }
        return $base;
    }

    /** true en local/dev */
    private function isDev(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($host, ['localhost', '127.0.0.1'], true) || getenv('APP_ENV') === 'local';
    }

    /** X-Api-Key sacada de DatosSistema */
    private function apiKey(): string
    {
        $sys = $this->datosSistema->buscar();
        return $sys['token_sistema'] ?? '';
    }

    /**
     * GET a la API maestro.
     * Retorna array (decodificado) o null si hay error.
     */
    private function apiGet(string $path): ?array
    {
        $base = $this->apiBase();
        if ($base === '') {
            error_log('[API] API_BASE_URL vacío/indefinido.');
            return null;
        }

        $url = $base . '/' . ltrim($path, '/');

        // Anti-bucle: mismo host o localhost → aborta
        $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $apiHost     = strtolower(parse_url($base, PHP_URL_HOST) ?: '');
        if (!$apiHost || $apiHost === $currentHost || in_array($apiHost, ['localhost', '127.0.0.1'], true)) {
            error_log('[API] Host inválido o recursivo: ' . $base . ' (actual=' . $currentHost . ')');
            return null;
        }

        $ch   = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $this->apiKey(), 'Accept: application/json', 'User-Agent: SIGI/1.0'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ];
        if ($this->isDev()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $out  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($out === false || $code >= 400) {
            error_log("[API] GET $url -> HTTP $code :: $err");
            return null;
        }

        $j = json_decode($out, true);
        return is_array($j) ? $j : null;
    }

    /* ============================================================
     * Vistas públicas / usuario
     * ============================================================ */

    /** Página de búsqueda/exploración (pública o con login, según tu flujo) */
    public function index()
    {
        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/index', [
            'sistema'   => $sistema,
            'module'    => 'biblioteca',
            'pageTitle' => 'libros',
        ]);
    }

    /** Ver detalle de un libro */
    public function Ver($id)
    {
        $id_usuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        $json = $this->apiGet('library/show/' . rawurlencode((string)$id));
        if (!$json || isset($json['error'])) {
            $_SESSION['flash_error'] = 'Libro no encontrado.';
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }

        $libro = $json['data'] ?? $json;
        $libro['_es_favorito'] = $id_usuario ? (bool)$this->model->buscarFavorito($id_usuario, (int)$id) : false;

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/ver', [
            'sistema'   => $sistema,
            'libro'     => $libro,
            'module'    => 'biblioteca',
            'pageTitle' => 'Biblioteca - ' . ($libro['titulo'] ?? 'Libro'),
        ]);
    }

    /** Leer un libro (vista principal del lector) */
    public function Leer($id)
    {
        $id_usuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        $json = $this->apiGet('library/show/' . rawurlencode((string)$id));
        if (!$json || isset($json['error'])) {
            $_SESSION['flash_error'] = 'Libro no encontrado.';
            header('Location: ' . BASE_URL . '/biblioteca/busqueda');
            exit;
        }

        $libro = $json['data'] ?? $json;
        $libro['_es_favorito'] = $id_usuario ? (bool)$this->model->buscarFavorito($id_usuario, (int)$id) : false;

        // registra lectura (solo si pasaron >=3 días desde la última)
        $this->registrarLectura((int)$id);

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/leer', [
            'sistema'   => $sistema,
            'libro'     => $libro,
            'module'    => 'biblioteca',
            'pageTitle' => 'Biblioteca - ' . ($libro['titulo'] ?? 'Libro'),
        ]);
    }

    /** Agregar/Quitar favorito (AJAX POST) */
    public function ActualizarFavorito($id)
    {
        $id_usuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        // Requiere login
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

    /** Registrar lectura si han pasado >= 3 días desde la última */
    public function registrarLectura(int $id): void
    {
        $id_usuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        if (!$id_usuario || !$id) return;

        try {
            $lectura = $this->model->buscarLectura($id_usuario, $id);
            if (!$lectura || empty($lectura['fecha_registro'])) {
                $this->model->registrarLectura($id_usuario, $id);
                return;
            }

            $last = new \DateTime($lectura['fecha_registro']);
            $now  = new \DateTime('now');

            if ($last->diff($now)->days >= 3) {
                $this->model->registrarLectura($id_usuario, $id);
            }
        } catch (\Throwable $e) {
            error_log('[registrarLectura] ' . $e->getMessage());
        }
    }

    /** Vista de Favoritos (requiere login, no rol de biblioteca) */
    public function Favoritos()
    {
        if (empty($_SESSION['sigi_user_id'])) {
            $_SESSION['flash_error'] = "Inicia sesión para ver tus favoritos.";
            header('Location: ' . BASE_URL . '/auth/login');
            return;
        }

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/favoritos', [
            'sistema'   => $sistema,
            'module'    => 'biblioteca',
            'pageTitle' => 'Mis favoritos',
        ]);
    }

    /**
     * JSON: Lista paginada de libros favoritos del usuario logueado.
     * GET params: page, per_page
     */
    public function FavoritosList()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        if (!$idUsuario) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'AUTH', 'message' => 'No autenticado']);
            return;
        }

        // ---------- FUSIBLE ANTIRRECURSIÓN (por petición) ----------
        static $sentinel = 0;
        if ($sentinel > 0) {
            http_response_code(508); // Loop Detected
            echo json_encode([
                'ok' => false,
                'message' => 'Corte por posible recursión',
                'debug' => 'FavoritosList re-entró durante la misma petición'
            ]);
            return;
        }
        $sentinel++;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = min(24, max(1, (int)($_GET['per_page'] ?? 8)));
        $off  = ($page - 1) * $per;

        try {
            $total = (int)$this->model->contarFavoritos($idUsuario);
            if ($total === 0) {
                echo json_encode([
                    'ok' => true,
                    'data' => [],
                    'pagination' => ['page' => $page, 'per_page' => $per, 'total' => 0]
                ]);
                return;
            }

            $rows = $this->model->listarFavoritos($idUsuario, $off, $per); // [{id_libro}, ...]
            $ids  = array_values(array_filter(array_map(fn($r) => (int)($r['id_libro'] ?? 0), $rows)));
            $data = [];

            // Helper de normalización (mínimos que usa la vista)
            $normalize = function (array $b): array {
                return [
                    'id'              => $b['id'] ?? null,
                    'titulo'          => $b['titulo'] ?? '',
                    'autor'           => $b['autor'] ?? '',
                    'portada_url'     => $b['portada_url'] ?? '',
                    'archivo_url'     => $b['archivo_url'] ?? '',
                    'vinculo'         => $b['vinculo'] ?? null,
                    'programa_nombre' => $b['programa'] ?? ($b['programa_nombre'] ?? ''),
                    'ud_nombre'       => $b['unidad']  ?? ($b['ud_nombre'] ?? ''),
                ];
            };

            // ---------- PREFERIR BATCH EN EL MAESTRO ----------
            // Si tu API maestro soporta algo como /api/library/batch?ids=1,2,3 úsalo:
            $batchOk = false;
            if ($ids) {
                $apiBase = $this->apiBase();
                $apiHost = parse_url($apiBase, PHP_URL_HOST) ?: '';

                // Evita hacer llamadas al mismo host o a localhost → origen típico del loop
                $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
                $apiHostLower = strtolower($apiHost);
                if (!$apiHostLower || $apiHostLower === $currentHost || in_array($apiHostLower, ['localhost', '127.0.0.1'], true)) {
                    error_log('[FavoritosList] Evitada llamada al mismo host/localhost para prevenir recursión');
                } else {
                    // Primero intentamos batch
                    $batch = $this->apiGet('library/batch?ids=' . implode(',', $ids));
                    if (is_array($batch) && !empty($batch['data']) && is_array($batch['data'])) {
                        foreach ($batch['data'] as $b) {
                            $data[] = $normalize(is_array($b) ? $b : []);
                        }
                        $batchOk = true;
                    }

                    // Si no hay batch, hacemos un número limitado de llamadas uno-a-uno (tope duro)
                    if (!$batchOk) {
                        $limit = min(count($ids), 12); // tope para evitar tormenta de requests
                        for ($i = 0; $i < $limit; $i++) {
                            $j = $this->apiGet('library/show/' . urlencode((string)$ids[$i]));
                            if (!$j) continue;
                            $obj = $j['data'] ?? $j;
                            if (is_array($obj) && isset($obj['id'])) {
                                $data[] = $normalize($obj);
                            }
                        }
                    }
                }
            }

            // ---------- FALLBACK SIN API (evita romper la UI) ----------
            if (empty($data) && !empty($rows)) {
                // Devuelve algo utilizable sin tocar el maestro (p. ej., solo id y título si lo guardas en tu BD)
                // Ajusta si tu tabla de favoritos almacena snapshot del libro.
                foreach ($rows as $r) {
                    $data[] = [
                        'id'              => (int)($r['id_libro'] ?? 0),
                        'titulo'          => $r['titulo'] ?? '(sin título)',
                        'autor'           => $r['autor']  ?? '',
                        'portada_url'     => $r['portada_url'] ?? '',
                        'archivo_url'     => $r['archivo_url'] ?? '',
                        'vinculo'         => null,
                        'programa_nombre' => $r['programa_nombre'] ?? '',
                        'ud_nombre'       => $r['ud_nombre'] ?? '',
                    ];
                }
            }

            echo json_encode([
                'ok'         => true,
                'data'       => $data,
                'pagination' => ['page' => $page, 'per_page' => $per, 'total' => $total]
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log('[FavoritosList] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['ok' => false, 'message' => 'Error listando favoritos', 'debug' => $e->getMessage()]);
        } finally {
            $sentinel = 0; // libera sentinela por seguridad
        }
    }


    /* ============================================================
     * Páginas de administración (requieren rol Biblioteca)
     * ============================================================ */

    public function Vinculados()
    {
        if (!\Core\Auth::esAdminBiblioteca()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            return;
        }

        $programas = $this->objPrograma->getTodosProgramas();
        $planes = $modulos = $semestres = $unidades = $competencias = [];
        $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/librosVinculados', [
            'sistema'               => $sistema,
            'programas'             => $programas,
            'planes'                => $planes,
            'modulos'               => $modulos,
            'semestres'             => $semestres,
            'unidades'              => $unidades,
            'competencias'          => $competencias,
            'id_programa_selected'  => $id_programa_selected,
            'id_plan_selected'      => $id_plan_selected,
            'id_modulo_selected'    => $id_modulo_selected,
            'id_semestre_selected'  => $id_semestre_selected,
            'module'                => 'biblioteca',
            'pageTitle'             => 'libros',
        ]);
    }

    public function Nuevo()
    {
        if (!\Core\Auth::esAdminBiblioteca()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            return;
        }

        $programas = $this->objPrograma->getTodosProgramas();
        $planes = $modulos = $semestres = $unidades = [];
        $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/nuevo', [
            'sistema'               => $sistema,
            'programas'             => $programas,
            'planes'                => $planes,
            'modulos'               => $modulos,
            'semestres'             => $semestres,
            'unidades'              => $unidades,
            'id_programa_selected'  => $id_programa_selected,
            'id_plan_selected'      => $id_plan_selected,
            'id_modulo_selected'    => $id_modulo_selected,
            'id_semestre_selected'  => $id_semestre_selected,
            'module'                => 'biblioteca',
            'pageTitle'             => 'nuevo libro',
        ]);
    }

    public function Vincular()
    {
        if (!\Core\Auth::esAdminBiblioteca()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            return;
        }

        $programas = $this->objPrograma->getTodosProgramas();
        $planes = $modulos = $semestres = $unidades = [];
        $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';

        $sistema = $this->datosSistema->buscar();
        $this->view('biblioteca/libros/vincular', [
            'sistema'               => $sistema,
            'programas'             => $programas,
            'planes'                => $planes,
            'modulos'               => $modulos,
            'semestres'             => $semestres,
            'unidades'              => $unidades,
            'id_programa_selected'  => $id_programa_selected,
            'id_plan_selected'      => $id_plan_selected,
            'id_modulo_selected'    => $id_modulo_selected,
            'id_semestre_selected'  => $id_semestre_selected,
            'module'                => 'biblioteca',
            'pageTitle'             => 'vincular libro',
        ]);
    }
}
