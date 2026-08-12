<?php

namespace App\Controllers\Tutoria;

use Core\Controller;

class ProgramacionController extends Controller {

    /* ===================== Vista Principal ===================== */

    public function index() {
        $this->view('tutoria/programacion/index', [
            'module'    => 'tutoria',
            'pageTitle' => 'programacion',
        ]);
        exit;
    }

    public function nuevo() {
        $this->view('tutoria/programacion/nuevo', [
            'module'    => 'tutoria',
            'pageTitle' => 'Nueva programacion',
        ]);
        exit;
    }

    public function guardar() {
        // Lógica para guardar la nueva programacion
    }
}