<?php

namespace App\Controllers\Aula;

require_once __DIR__ . '/../../../app/helpers/Integrator.php';

use Core\Controller;
use App\Helpers\Integrator;
use Core\Auth;

class MoodleController extends Controller
{
    protected $integrator;
    public function __construct()
    {
        parent::__construct();
        $this->integrator = new Integrator();
    }

    public function index()
    {
        $user = Auth::user();              // ya validado por middleware
        if ($user) {
            $a = isset($_GET['a']) ? $_GET['a'] : null;
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            // link de moodle // Generar link SSO
            $idUsuarioSigi = $_SESSION['sigi_user_id'];
            $respuesta = $this->integrator->loginMoodle($idUsuarioSigi, $a, $id);
            if ($respuesta['ok']) {
                header("Location: " . $respuesta['url']);
            } else {
                $_SESSION['flash_error'] = $respuesta['message_error'];
                header("Location: " . BASE_URL . "/intranet");
            }
        } else {
            header("Location: " . BASE_URL . "/login");
        }
    }
}
