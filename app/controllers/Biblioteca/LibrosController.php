<?php

namespace App\Controllers\Biblioteca;

require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use Core\Controller;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Programa;

class LibrosController extends Controller
{
    protected $model;
    protected $objPrograma;
    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::tieneRolEnBiblioteca()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $this->model = new DatosSistema();
        $this->objPrograma = new Programa();
    }




    public function index()
    {
        $sistema = $this->model->buscar();
        $this->view('biblioteca/libros/index', [
            'sistema' => $sistema,
            'module'    => 'biblioteca',
            'pageTitle' => 'libros'
        ]);
    }
    public function Vinculados()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            // Al crear, no hay nada seleccionado aún
            $planes = $modulos = $semestres = $unidades = $competencias = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;
        $sistema = $this->model->buscar();
        $this->view('biblioteca/libros/librosVinculados', [
            'sistema' => $sistema,
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'semestres' => $semestres,
            'unidades'  => $unidades,
            'competencias' => $competencias,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected'     => $id_plan_selected,
            'id_modulo_selected'   => $id_modulo_selected,
            'id_semestre_selected' => $id_semestre_selected,
            'module'    => 'biblioteca',
            'pageTitle' => 'libros'
        ]);
    }
    public function Nuevo()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            // Al crear, no hay nada seleccionado aún
            $planes = $modulos = $semestres = $unidades = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;
        $sistema = $this->model->buscar();
        $this->view('biblioteca/libros/nuevo', [
            'sistema' => $sistema,
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'semestres' => $semestres,
            'unidades'  => $unidades,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected'     => $id_plan_selected,
            'id_modulo_selected'   => $id_modulo_selected,
            'id_semestre_selected' => $id_semestre_selected,
            'module'    => 'biblioteca',
            'pageTitle' => 'nuevo libro'
        ]);
    }
    public function Vincular()
    {
        if (\Core\Auth::esAdminBiblioteca()):
            $programas = $this->objPrograma->getTodosProgramas();
            // Al crear, no hay nada seleccionado aún
            $planes = $modulos = $semestres = $unidades = [];
            $id_programa_selected = $id_plan_selected = $id_modulo_selected = $id_semestre_selected = '';
        endif;
        $sistema = $this->model->buscar();
        $this->view('biblioteca/libros/vincular', [
            'sistema' => $sistema,
            'programas' => $programas,
            'planes'    => $planes,
            'modulos'   => $modulos,
            'semestres' => $semestres,
            'unidades'  => $unidades,
            'id_programa_selected' => $id_programa_selected,
            'id_plan_selected'     => $id_plan_selected,
            'id_modulo_selected'   => $id_modulo_selected,
            'id_semestre_selected' => $id_semestre_selected,
            'module'    => 'biblioteca',
            'pageTitle' => 'vincular libro'
        ]);
    }


    public function Ver($id)
    {
        $sistema = $this->model->buscar();
        $apiKey  = $sistema['token_sistema'] ?? '';

        // Base del API (no forzamos http, ya no hace falta)
        $apiBase = rtrim(API_BASE_URL, '/') . '/api';
        $url     = $apiBase . '/library/show/' . rawurlencode((string)$id);

        // Detecta entorno local para RELAJAR SSL (solo DEV)
        $host  = $_SERVER['HTTP_HOST'] ?? '';
        $isDev = in_array($host, ['localhost', '127.0.0.1'], true) || getenv('APP_ENV') === 'local';

        $doCurl = function (string $u) use ($apiKey, $isDev) {
            $ch = curl_init($u);
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $apiKey, 'Accept: application/json'],
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_HEADER         => false,
                CURLOPT_USERAGENT      => 'SIGI/1.0 (+curl)',
                CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            ];

            // Clave: en DEV desactivar SIEMPRE la verificación SSL, aunque haya redirecciones
            if ($isDev) {
                $opts[CURLOPT_SSL_VERIFYPEER] = false;
                $opts[CURLOPT_SSL_VERIFYHOST] = 0;
            }

            curl_setopt_array($ch, $opts);
            $body   = curl_exec($ch);
            $err    = curl_error($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $info   = curl_getinfo($ch);
            curl_close($ch);
            return [$status, $body, $err, $info];
        };

        // 1er intento (FOLLOWLOCATION ya cubrirá 301/302/307/308)
        [$status, $body, $err, $info] = $doCurl($url);

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

        $libro = $json;

        $this->view('biblioteca/libros/ver', [
            'sistema'   => $sistema,
            'libro'     => $libro,
            'module'    => 'biblioteca',
            'pageTitle' => 'Biblioteca - '.$libro['titulo'] ?? 'Libro'
        ]);
    }
    

    
}
