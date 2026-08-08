<?php

namespace App\Controllers\Efsrt;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Efsrt/Programacion.php';

use App\Models\Efsrt\Programacion;

class ProgramacionController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Programacion();
    }

    public function index()
    {
        // Aquí puedes cargar la vista correspondiente
        $this->view('efsrt/programacion/index');
    }
    public function nuevo(){
        $this->view('efsrt/programacion/nuevo');
    }

}
