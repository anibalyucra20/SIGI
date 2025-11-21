<?php

namespace App\Controllers\Biblioteca;

require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use Core\Controller;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Programa;

class BusquedaController extends Controller
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
        $programas = $this->objPrograma->getTodosProgramas();
        $sistema = $this->model->buscar();
        $this->view('biblioteca/busqueda/index', [
            'sistema' => $sistema,
            'programas' => $programas,
            'module'    => 'biblioteca',
            'pageTitle' => 'busqueda de libros'
        ]);
    }
}
