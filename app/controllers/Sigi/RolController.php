<?php

namespace App\Controllers\Sigi;
use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Rol.php';
use App\Models\Sigi\Rol;

class RolController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Rol();
    }
    public function index()
    {
        $roles = $this->model->buscar();
        $this->view('sigi/rol/index', [
            'roles'   => $roles,
            'module'  => 'sigi',
            'pageTitle' => 'Relación de Roles'
        ]);
    }
    public function cambiarSesion()
    {
        if (!empty($_GET['permiso'])) {
            list($id_sistema, $id_rol) = explode('-', $_GET['permiso']);
            $_SESSION['sigi_modulo_actual'] = $id_sistema;
            $_SESSION['sigi_rol_actual']    = $id_rol;
        }

        // Seguridad: sólo rutas internas
        if (!empty($_GET['redirect']) && strpos($_GET['redirect'], '/') === 0) {
            $redirect = $_GET['redirect'];
        } else {
            $redirect = BASE_URL . '/intranet';
        }

        header('Location: ' . $redirect);
        exit;
    }
}
