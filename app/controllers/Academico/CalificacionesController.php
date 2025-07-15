<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Academico/Asistencia.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\Academico\Calificaciones;
use App\Models\Academico\Asistencia;
use App\Models\Academico\Silabos;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\IndicadorLogroCapacidad;
use TCPDF; // 

class CalificacionesController extends Controller
{
    protected $model;
    protected $objDatosSistema;
    protected $objAsistencia;
    protected $objIndLogroCapacidad;
    protected $objSilabo;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Calificaciones();
        $this->objDatosSistema = new DatosSistema();
        $this->objAsistencia = new Asistencia();
        $this->objIndLogroCapacidad = new IndicadorLogroCapacidad();
        $this->objSilabo = new Silabos();
    }
    public function evaluar($id_programacion_ud, $nro_calificacion)
    {
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);

        if (!$permitido) {
            $this->view('academico/calificaciones/evaluar', [
                'permitido' => false
            ]);
            return;
        }


        $datos = $this->model->getDatosEvaluacion($id_programacion_ud, $nro_calificacion);
        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }


        $this->view('academico/calificaciones/evaluar', array_merge([
            'id_programacion_ud' => $id_programacion_ud,
            'nro_calificacion' => $nro_calificacion,
            'estudiantes_inhabilitados' => $estudiantes_inhabilitados,
            'nota_inasistencia' => $nota_inasistencia,
            'permitido' => $permitido
        ], $datos));
    }

    public function guardarCriterio()
    {
        $id_criterio = $_POST['id_criterio'] ?? 0;
        $valor = trim($_POST['valor'] ?? '');
        $ok = $this->model->guardarCriterioEvaluacion($id_criterio, $valor);
        echo json_encode(['ok' => $ok]);
        exit;
    }


    public function ver($id_programacion_ud)
    {
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);

        if (!$permitido) {
            $this->view('academico/calificaciones/ver', [
                'permitido' => false
            ]);
            return;
        }

        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();

        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }

        $this->view('academico/calificaciones/ver', [
            'id_programacion_ud' => $id_programacion_ud,
            'permitido' => $permitido,
            'nombreUnidadDidactica' => $datos['nombreUnidadDidactica'],
            'periodo' => $datos['periodo'],
            'nros_calificacion' => $datos['nros_calificacion'],
            'mostrar_calificaciones' => $mostrar_calificaciones,
            'mostrar_promedio_todos' => $mostrar_promedio_todos,
            'estudiantes' => $datos['estudiantes'],
            'notas' => $datos['notas'],
            'promedios' => $datos['promedios'],
            'recuperaciones' => $datos['recuperaciones'],
            'estudiantes_inhabilitados' => $estudiantes_inhabilitados,
            'nota_inasistencia' => $nota_inasistencia,
        ]);
    }
    public function actualizarMostrar()
    {
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $nro_calificacion = $_POST['nro_calificacion'] ?? 0;
        $mostrar = $_POST['mostrar'] ?? 0;

        $ok = $this->model->actualizarMostrarCalificacion($id_programacion_ud, $nro_calificacion, $mostrar);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }
    public function actualizarMostrarPromedioTodos()
    {
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $mostrar = $_POST['mostrar'] ?? 0;

        $ok = $this->model->actualizarMostrarPromedioTodos($id_programacion_ud, $mostrar);
        echo json_encode(['ok' => $ok]);
        exit;
    }
    public function guardarPonderadoEvaluacionMasivo()
    {
        $ids_eval = $_POST['ids_eval'] ?? '';
        $ponderado = intval($_POST['ponderado'] ?? 0);
        $ok = $this->model->guardarPonderadoEvaluacionMasivo($ids_eval, $ponderado);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    public function guardarDetalleCriterioMasivo()
    {
        $ids_criterio = $_POST['ids_criterio'] ?? '';
        $detalle = trim($_POST['detalle'] ?? '');
        $ok = $this->model->guardarDetalleCriterioMasivo($ids_criterio, $detalle);
        echo json_encode(['ok' => $ok]);
        exit;
    }
    public function agregarCriterioMasivo()
    {
        $ids_eval = explode(',', $_POST['ids_eval'] ?? '');
        $ok = $this->model->agregarCriterioMasivo($ids_eval);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    public function guardarRecuperacion()
    {
        $id_detalle_mat = $_POST['id_detalle_mat'] ?? 0;
        $valor = trim($_POST['valor'] ?? '');
        $ok = $this->model->guardarRecuperacion($id_detalle_mat, $valor);
        echo json_encode(['ok' => $ok]);
        exit;
    }








    // IMPRESION DE REGISTRO OFICIALL
    public function registroOficial($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);

        if (!$permitido) {
            $this->view('academico/calificaciones/ver', [
                'permitido' => false
            ]);
            return;
        }
        // INFORMACION PARA ASISTENCIAS
        // Datos generales
        $datos_asistencia = $this->objAsistencia->getDatosAsistencia($id_programacion_ud);

        /*$this->view('academico/asistencia/index', [
            'nombreUnidadDidactica' => $datos_asistencia['nombreUnidadDidactica'],
            'periodo' => $datos_asistencia['periodo'],
            'sesiones_asistencia' => $datos_asistencia['sesiones'],
            'estudiantes_asistencia' => $datos_asistencia['estudiantes'],
            'asistencias' => $datos_asistencia['asistencias'],
        ]);*/

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $nros_calificacion = $datos['nros_calificacion'];
        $notas = $datos['notas'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        $ind_logro_capacidad = $this->objIndLogroCapacidad->getIndicadoresLogroCapacidad($id_unidad_didactica);
        foreach ($estudiantes as $est){
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Registro Oficial - '.$datosGenerales['unidad']);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_registro_oficial.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Output('registro oficial - '.$datosGenerales['unidad'].'.pdf', 'I');
    }
}
