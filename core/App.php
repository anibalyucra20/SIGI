<?php

namespace Core;

class App
{
    protected  $module     = 'Sigi';
    protected $controller = 'HomeController';
    protected  $method     = 'index';
    protected  $params     = [];

    public function __construct()
    {
        date_default_timezone_set('America/Lima');

        // =================================================================
        // 🛑 INTERRUPTOR DE SUSPENSIÓN GLOBAL (SIGI)
        // Cambia a true para bloquear el sistema, false para uso normal.
        // =================================================================
        $fechaActual = date('Y-m-d H:i:s');
        $fechaInicioSuspension = '2026-01-31 23:59:59'; // Fecha y hora de inicio de la suspensión
        if ($fechaActual >= $fechaInicioSuspension) {
            $sistemaSuspendido = true;
        } else {
            $sistemaSuspendido = false;
        }

        if ($sistemaSuspendido) {
            $this->renderIntranetRedirect();
        }
        // =================================================================
        $segments = $this->parseUrl();   // ej: ['sigi','docentes','edit',5]
        if (!empty($segments[0]) && strtolower($segments[0]) === 'logout') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'salir';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->salir();
            exit;
        }
        if (!empty($segments[0]) && ($segments[0]) === 'recuperar') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'recuperar';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->recuperar();
            exit;
        }
        if (!empty($segments[0]) && ($segments[0]) === 'resetPassword') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'resetPassword';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->resetPassword();
            exit;
        }
        if (!empty($segments[0]) && ($segments[0]) === 'enviarRecuperacion') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'enviarRecuperacion';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->enviarRecuperacion();
            exit;
        }
        if (!empty($segments[0]) && strtolower($segments[0]) === 'restablecer') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'restablecer';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->restablecer();
            exit;
        }
        if (!empty($segments[0]) && ($segments[0]) === 'guardarNuevaPassword') {
            $this->module     = 'Auth';
            $this->controller = 'LoginController';
            $this->method     = 'guardarNuevaPassword';
            require_once __DIR__ . "/../app/controllers/Auth/LoginController.php";
            // Ejecuta y termina
            $class = "App\\Controllers\\Auth\\LoginController";
            $controller = new $class();
            $controller->guardarNuevaPassword();
            exit;
        }
        if (!empty($segments[0])) {
            // para poder hacer el logo dinamico segun el modulo donde se encuentre
            \Core\Auth::start();
            $_SESSION['modulo_vista'] = $segments[0];
            /* ➊ excepción para login / logout */
            if (in_array(strtolower($segments[0]), ['login', 'logout'])) {
                $this->module     = 'Auth';                // módulo Auth
                $segments[0]      = 'login';               // controlador LoginController
            }
            // resto igual ─ si el nombre es carpeta de módulo, cámbialo
            elseif (is_dir(__DIR__ . '/../app/controllers/' . ucfirst(strtolower($segments[0])))) {
                $this->module = ucfirst(strtolower(array_shift($segments)));
            }
        }
        /* ---------- 1. Módulo ---------- */
        if (
            !empty($segments[0]) &&
            is_dir(__DIR__ . '/../app/controllers/' . ucfirst(strtolower($segments[0])))
        ) {
            $this->module = ucfirst(strtolower(array_shift($segments)));
        }

        /* ---------- 2. Controlador ---------- */
        if (!empty($segments[0])) {
            $ctrlName = ucfirst($segments[0]) . 'Controller';
            //$ctrlName = ucfirst(strtolower($segments[0])) . 'Controller';
            $ctrlFile = __DIR__ . "/../app/controllers/{$this->module}/{$ctrlName}.php";
            if (file_exists($ctrlFile)) {
                $this->controller = $ctrlName;
                require_once $ctrlFile;
                array_shift($segments);
            } else {
                return $this->render404("Controlador no encontrado");
            }
        } else {
            // controlador por defecto dentro del módulo
            $ctrlFile = __DIR__ . "/../app/controllers/{$this->module}/HomeController.php";
            require_once $ctrlFile;
        }

        /* ---------- 3. Instancia ---------- */
        $class = "App\\Controllers\\{$this->module}\\{$this->controller}";
        if (!class_exists($class)) {
            return $this->render404("Clase {$class} no existe");
        }
        $this->controller = new $class();

        /* ---------- 4. Método ---------- */
        if (!empty($segments[0]) && method_exists($this->controller, $segments[0])) {
            $this->method = array_shift($segments);
        } elseif (!empty($segments[0])) {
            return $this->render404("Método {$segments[0]} no encontrado");
        }

        /* ---------- 5. Parámetros ---------- */
        $this->params = $segments;

        set_error_handler(function ($severity, $message, $file, $line) {
            error_log("[Error $severity] $message in $file on line $line");
        });
        /* ---------- 6. Ejecutar ---------- */
        return call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /* ------------------------------------ */
    /* Parseo de URL (sin /public)          */
    /* ------------------------------------ */
    private function parseUrl(): array
    {
        if (!empty($_GET['url'])) {
            $route = rtrim($_GET['url'], '/');
        } else {
            $base  = '/'; // ajusta si cambias folder
            $uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
            $route = trim(substr($uri, strlen($base)), '/');
        }
        $route = filter_var($route, FILTER_SANITIZE_URL);
        // Aquí agregas la redirección a intranet si está vacío
        if ($route === '' || $route === false) {
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        return $route === '' ? [] : explode('/', $route);
    }

    /* ------------------------------------ */
    private function render404(string $msg)
    {
        http_response_code(404);
        echo "<h1>404</h1><p>{$msg}</p>";
        exit;
    }

    /* ------------------------------------ */
    /* Renderiza la pantalla de suspensión   */
    /* ------------------------------------ */
    private function renderSuspensionPage()
    {
        http_response_code(503); // Service Unavailable
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>503 Service Temporarily Unavailable</title>
            <style>
                body {
                    background-color: #ffffff;
                    color: #000000;
                    font-family: Helvetica, Arial, sans-serif;
                    margin: 40px;
                }

                h1 {
                    font-size: 24px;
                    font-weight: normal;
                    margin-bottom: 4px;
                }

                p {
                    font-size: 14px;
                    line-height: 1.6;
                    margin-top: 15px;
                    margin-bottom: 15px;
                }

                hr {
                    border: 0;
                    border-top: 1px solid #cccccc;
                    margin-top: 20px;
                    margin-bottom: 10px;
                }

                .server-info {
                    font-size: 12px;
                    color: #555555;
                    font-style: italic;
                }

                strong {
                    color: #000000;
                }
            </style>
        </head>

        <body>
            <h1>Service Temporarily Unavailable</h1>
            <p>
                The server is temporarily unable to service your request due to maintenance downtime or capacity problems.
                Please try again later.
            </p>
            <p>
                ------------------------------------------------------------------------------------------------=====------------------------------------------------------------------------------------------------
            </p>
            <p>
                <strong>AVISO SIGI:</strong> El acceso al Sistema Integrado de Gestión Institucional se encuentra suspendido temporalmente.
            </p>
            <hr>
            <div class="server-info">
                Apache/2.4.62 (Debian) Server at <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?> Port 443
            </div>
        </body>

        </html>
    <?php
        exit;
    }

    private function renderIntranetRedirect()
    { ?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
        </head>

        <body>
            <style>
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    z-index: 10000;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .modal-content {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    text-align: center;
                    font-family: Arial, sans-serif;
                    max-width: 400px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
                }

                .btn-redireccion {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #28a745;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
            </style>

            <div class="modal-overlay">
                <div class="modal-content">
                    <h2>Actualización de Sistema</h2>
                    <p>El sistema SIGI ha migrado. Por favor, utiliza nuestro nuevo portal institucional para continuar con tus gestiones.</p>
                    <a href="https://intranet.iesphuanta.edu.pe" class="btn-redireccion">Ir al Nuevo Sistema</a>
                </div>
            </div>
        </body>
        </html>
<?php
        exit;
    }
}
