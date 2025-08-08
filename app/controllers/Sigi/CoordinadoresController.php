<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/CoordinadorPeriodo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use App\Models\Sigi\CoordinadorPeriodo;
use App\Models\Sigi\Docente;
use App\Models\Sigi\Programa;
use App\Models\Sigi\PeriodoAcademico;

class CoordinadoresController extends Controller
{
    protected $model;
    protected $objDocente;
    protected $objPrograma;
    protected $objPeriodo;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CoordinadorPeriodo();
        $this->objDocente = new Docente();
        $this->objPrograma = new Programa();
        $this->objPeriodo = new PeriodoAcademico();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $id_periodo = $_SESSION['sigi_periodo_actual_id'];
            $id_sede    = $_SESSION['sigi_sede_actual'];
            $coordinadores = $this->model->listarPorPeriodoYSede($id_periodo, $id_sede);
        endif;
        $this->view('sigi/coordinadores/index', [
            'module' => 'sigi',
            'pageTitle' => 'Coordinadores por Programa de Estudio',
            'coordinadores' => $coordinadores
        ]);
        exit;
    }
    public function nuevo()
    {
        if (\Core\Auth::esAdminSigi()):
            $id_sede = $_SESSION['sigi_sede_actual'];

            $usuarios     = $this->objDocente->getAllDocente(); // Asumiendo que tienes este método
            $programas    = $this->objPrograma->getAllBySede($id_sede); // Lo implementamos abajo
        endif;
        $this->view('sigi/coordinadores/nuevo', [
            'usuarios'   => $usuarios,
            'programas'  => $programas,
            'pageTitle'  => 'Nuevo Coordinador PE - Periodo'
        ]);
        exit;
    }
    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $id_sede = $_SESSION['sigi_sede_actual'];
            $id_periodo = $_SESSION['sigi_periodo_actual_id'];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'id_usuario'          => $_POST['id_usuario'],
                    'id_programa_estudio' => $_POST['id_programa_estudio'],
                    'id_periodo'          => $id_periodo,
                    'id_sede'             => $id_sede,
                ];
                if ($this->model->existeRegistro($data)) {
                    // Puedes usar una variable de sesión o redirigir con error
                    $_SESSION['flash_error'] = 'Ya existe un registro con estos datos.';
                    header('Location: ' . BASE_URL . '/sigi/coordinadores');
                    return;
                }
                $this->model->registrar($data);
                header('Location: ' . BASE_URL . '/sigi/coordinadores');
            }
        endif;
        exit;
    }
    public function eliminar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $this->model->eliminar($id);
            header('Location: ' . BASE_URL . '/sigi/coordinadores'); // Ajusta la ruta si tu módulo tiene un nombre diferente
        endif;
        exit;
    }
}
