<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

class EmpresasController extends Controller {

    public function index() {
        if (!\Core\Auth::esAdminBolsa()):
        endif;
        $this->view('bolsa/empresas/index', [
            'module'    => 'bolsa',
            'pageTitle' => 'Empresas'
        ]);
        exit;
    }
    public function nuevo() {
        if (!\Core\Auth::esAdminBolsa()):
        endif;
        $this->view('bolsa/empresas/nuevo', [
            'module'    => 'bolsa',
            'pageTitle' => 'Nueva Empresas'
        ]);
        exit;
    }

}
