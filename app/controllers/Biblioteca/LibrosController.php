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
}
