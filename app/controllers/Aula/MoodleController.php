<?php

namespace App\Controllers\Aula;

require_once __DIR__ . '/../../../app/helpers/MoodleIntegrator.php';

use Core\Controller;
use App\Helpers\MoodleIntegrator;
use Core\Auth;

class MoodleController extends Controller
{
    protected $moodleIntegrator;
    public function __construct()
    {
        parent::__construct();
        $this->moodleIntegrator = new MoodleIntegrator();
    }

    public function index()
    {
        $user = Auth::user();              // ya validado por middleware
        if ($user) {
            $a = isset($_GET['a']) ? $_GET['a'] : null;
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            // link de moodle // Generar link SSO
            $idUsuarioSigi = $_SESSION['sigi_user_id'];
            $urlAulaVirtual = $this->moodleIntegrator->getAutoLoginUrl($idUsuarioSigi, $a, $id);
            header("Location: " . $urlAulaVirtual);
            exit();
        } else {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
    }
}
