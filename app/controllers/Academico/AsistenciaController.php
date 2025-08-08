<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Asistencia.php';
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';

use App\Models\Academico\Asistencia;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Sigi\PeriodoAcademico;

class AsistenciaController extends Controller
{
    protected $model;
    protected $objProgramacionUD;
    protected $objPeriodo;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Asistencia();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objPeriodo = new PeriodoAcademico();
    }

    // Vista principal
    public function ver($id_programacion_ud)
    {
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = ($esDocenteEncargado || $esAdminAcademico);

        // Datos generales
        $datos = $this->model->getDatosAsistencia($id_programacion_ud);

        $this->view('academico/asistencia/index', [
            'permitido' => $permitido,
            'id_programacion_ud' => $id_programacion_ud,
            'nombreUnidadDidactica' => $datos['nombreUnidadDidactica'],
            'periodo' => $datos['periodo'],
            'sesiones' => $datos['sesiones'],
            'estudiantes' => $datos['estudiantes'],
            'asistencias' => $datos['asistencias'],
        ]);
    }

    // Guardar asistencia
    public function guardar()
    {
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $asistencia = $_POST['asistencia'] ?? [];

        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodo->getPeriodoVigente($programacion['id_periodo_academico']);


        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = (($esDocenteEncargado || $esAdminAcademico) && ($periodo_vigente && $periodo_vigente['vigente']));

        $periodo = $this->model->getPeriodoByProgramacion($id_programacion_ud);
        $periodoFinalizado = strtotime($periodo['fecha_fin']) < strtotime(date('Y-m-d'));

        if ($periodoFinalizado || !$permitido) {
            $_SESSION['flash_error'] = "No se puede editar la asistencia porque el periodo ha finalizado.";
            header("Location: " . BASE_URL . "/academico/asistencia/ver/$id_programacion_ud");
            exit;
        }

        $ok = $this->model->guardarAsistencia($id_programacion_ud, $asistencia);
        if ($ok) {
            $_SESSION['flash_success'] = "Asistencia guardada correctamente.";
        } else {
            $_SESSION['flash_error'] = "Error al guardar asistencia.";
        }
        header("Location: " . BASE_URL . "/academico/asistencia/ver/$id_programacion_ud");
        exit;
    }
}
