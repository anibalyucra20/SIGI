<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Asistencia.php';

use App\Models\Academico\Asistencia;

class AsistenciaController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Asistencia();
    }

    // Vista principal
    public function ver($id_programacion_ud)
    {
        // Datos generales
        $datos = $this->model->getDatosAsistencia($id_programacion_ud);

        $this->view('academico/asistencia/index', [
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

        $periodo = $this->model->getPeriodoByProgramacion($id_programacion_ud);
        $periodoFinalizado = strtotime($periodo['fecha_fin']) < strtotime(date('Y-m-d'));

        if ($periodoFinalizado) {
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
