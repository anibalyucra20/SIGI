<?php

namespace App\Controllers\Biblioteca;

require_once __DIR__ . '/../../../app/models/Biblioteca/Lecturas.php';
require_once __DIR__ . '/../../../app/models/Biblioteca/Libros.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/UnidadDidactica.php';

use App\Models\Biblioteca\Lecturas;
use App\Models\Biblioteca\Libros;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Programa;
use App\Models\Sigi\UnidadDidactica;
use Core\Controller;

class LecturasController extends Controller
{
    protected Lecturas $model;
    protected Libros $librosModel;
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

        $this->model = new Lecturas();
        $this->librosModel = new Libros();
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

    private function apiBatch(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($n) => $n > 0));
        if (!$ids) return [];

        $apiBase = $this->apiBase();
        $sistema = $this->datosSistema->buscar();
        $apiKey  = $sistema['token_sistema'] ?? '';

        $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $apiHost     = parse_url($apiBase, PHP_URL_HOST) ?: '';

        if (!$apiHost || strtolower($apiHost) === $currentHost || in_array(strtolower($apiHost), ['localhost', '127.0.0.1'], true)) {
            error_log('[LecturasController@apiBatch] API_BASE_URL inválido o apunta al mismo host: ' . ($apiHost ?: '(empty)'));
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
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        curl_setopt_array($ch, $opts);
        $body   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            error_log("[LecturasController@apiBatch] HTTP $status :: $err :: $url");
            return [];
        }

        $json = json_decode($body, true);
        if (isset($json['data']) && is_array($json['data'])) return $json['data'];
        if (is_array($json)) return $json;
        return [];
    }

    /* ===================== Vista Principal ===================== */

    public function index()
    {
        $sistema  = $this->datosSistema->buscar();
        $usuarios = $this->model->getUsuarios();

        $this->view('biblioteca/lecturas/index', [
            'sistema'   => $sistema,
            'usuarios'  => $usuarios,
            'module'    => 'biblioteca',
            'pageTitle' => 'Lecturas',
        ]);
    }

    /* ===================== ENDPOINT DATA: Server-Side Reporte Logs CORREGIDO ===================== */
    public function data()
    {
        // Forzar salida limpia JSON
        header('Content-Type: application/json; charset=utf-8');

        if (!\Core\Auth::esAdminBiblioteca()) {
            echo json_encode(['error' => 'No autorizado', 'draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            exit;
        }

        // 1. Parámetros de DataTables
        $draw   = (int)($_GET['draw'] ?? 1);
        $start  = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);

        // 2. Mapeo de filtros de la interfaz
        $filtros = [
            'id_usuario' => !empty($_GET['filter_usuario']) ? (int)$_GET['filter_usuario'] : null,
            'fecha_ini'  => (!empty($_GET['filter_fecha_ini']) && $_GET['filter_fecha_ini'] !== 'undefined') ? trim($_GET['filter_fecha_ini']) : null,
            'fecha_fin'  => (!empty($_GET['filter_fecha_fin']) && $_GET['filter_fecha_fin'] !== 'undefined') ? trim($_GET['filter_fecha_fin']) : null,
        ];

        // 3. Consultas a la Base de Datos Local
        $totalRegistros = (int)$this->model->contarLogs(null);
        $totalFiltrados = (int)$this->model->contarLogs($filtros);
        $logs = ($totalFiltrados > 0) ? $this->model->listarLogs($filtros, $start, $length) : [];

        // 4. Batch Processing: Recolectar IDs usando el alias exacto del modelo 'id_registro'
        $idLibros = [];
        foreach ($logs as $log) {
            // Evaluamos ambas posibilidades por seguridad de la arquitectura
            $idActual = !empty($log['id_registro']) ? $log['id_registro'] : ($log['id_libro'] ?? 0);
            if ($idActual > 0) {
                $idLibros[] = (int)$idActual;
            }
        }
        $idLibrosUnicos = array_values(array_unique($idLibros));

        // Consumo eficiente de la API en un solo bloque
        $librosMaestros = $idLibrosUnicos ? $this->apiBatch($idLibrosUnicos) : [];
        $titulosPorId = array_column($librosMaestros, 'titulo', 'id');

        // 5. Construcción del Response estructurado para tus 4 columnas de la vista
        $dataResult = [];
        foreach ($logs as $log) {
            $idLibro = !empty($log['id_registro']) ? (int)$log['id_registro'] : (int)($log['id_libro'] ?? 0);

            // Si la API encuentra el título lo muestra, de lo contrario deja un fallback elegante
            $tituloLibro = isset($titulosPorId[$idLibro]) ? $titulosPorId[$idLibro] : 'Libro no especificado (ID: ' . $idLibro . ')';

            $dataResult[] = [
                'fecha'   => $log['fecha'] ?? '—',
                'rol'     => $log['rol'] ?? 'Estudiante',
                'usuario' => $log['usuario'] ?? 'Usuario',
                'libro'   => $tituloLibro // Debe llamarse 'libro' idéntico a tu columna JS { data: 'libro' }
            ];
        }

        // 6. Retorno de datos oficial para DataTables
        echo json_encode([
            "draw"            => $draw,
            "recordsTotal"    => $totalRegistros,
            "recordsFiltered" => $totalFiltrados,
            "data"            => $dataResult
        ]);
        exit;
    }
}
