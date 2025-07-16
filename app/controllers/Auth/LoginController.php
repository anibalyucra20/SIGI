<?php

namespace App\Controllers\Auth;

require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';

use Core\Controller;
use App\Models\Sigi\DatosSistema;
use PDO;

class LoginController extends Controller
{
    protected $objDatosSistema;

    public function __construct()
    {
        parent::__construct();
        $this->objDatosSistema = new DatosSistema();
    }
    /* Formulario */
    public function index()
    {
        $datosSistema = $this->objDatosSistema->buscar();
        $this->view('auth/login', [
            'datosSistema' => $datosSistema,
            'pageTitle' => 'Iniciar Sesión',
            'module'    => 'auth'
        ]);
    }

    /* POST credenciales */
    public function acceder()
    {

        $dni  = $_POST['dni']      ?? '';
        $pass = $_POST['password'] ?? '';

        $db = (new \Core\Model())->getDB();

        $sql = "SELECT * FROM sigi_usuarios WHERE dni = :dni AND estado = 1 LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':dni' => $dni]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($user && password_verify($pass, $user['password'])) {
            \Core\Auth::login($user);               // ← guarda sigi_user_id y sigi_user_name

            // --- CARGAR PERMISOS DEL USUARIO Y SETEAR MODULO/ROL ACTIVO ---
            $sql = "SELECT psu.id_sistema, s.nombre as sistema, psu.id_rol, r.nombre as rol
                FROM sigi_permisos_usuarios psu
                INNER JOIN sigi_sistemas_integrados s ON s.id = psu.id_sistema
                INNER JOIN sigi_roles r ON r.id = psu.id_rol
                WHERE psu.id_usuario = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['id']]);
            $_SESSION['permisos_usuario'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($_SESSION['permisos_usuario'])) {
                $_SESSION['modulo_actual'] = $_SESSION['permisos_usuario'][0]['id_sistema'];
                $_SESSION['rol_actual']    = $_SESSION['permisos_usuario'][0]['id_rol'];
            }
            // registro de log
            (new \Core\Model())->log($user['id'], 'LOGIN', 'Ingreso al sistema');
            // --- FIN BLOQUE CARGA DE PERMISOS ---

            header('Location: ' . BASE_URL . '/intranet');
            exit;                                   // ← imprescindible
        }

        header('Location: ' . BASE_URL . '/login?error=1');
        exit;
    }
    public function salir()
    {
        \Core\Auth::logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
