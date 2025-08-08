<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Academico/Asistencia.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Matricula.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\Academico\Calificaciones;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Asistencia;
use App\Models\Academico\Silabos;
use App\Models\Academico\Matricula;
use App\Models\Sigi\DatosInstitucionales;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\IndicadorLogroCapacidad;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use TCPDF;

class CalificacionesController extends Controller
{
    protected $model;
    protected $objProgramacionUD;
    protected $objDatosIes;
    protected $objDatosSistema;
    protected $objAsistencia;
    protected $objPeriodoAcademico;
    protected $objIndLogroCapacidad;
    protected $objSilabo;
    protected $objMatricula;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Calificaciones();
        $this->objDatosIes = new DatosInstitucionales();
        $this->objDatosSistema = new DatosSistema();
        $this->objAsistencia = new Asistencia();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objIndLogroCapacidad = new IndicadorLogroCapacidad();
        $this->objSilabo = new Silabos();
        $this->objMatricula = new Matricula();
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
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

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
            'periodo_vigente' => $periodo_vigente,
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

        $id_programacion_ud = $this->model->obtenerProgPorIdCriterio($id_criterio)['id_programacion_ud'];
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->guardarCriterioEvaluacion($id_criterio, $valor);
            echo json_encode(['ok' => $ok]);
        }
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
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

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
            'periodo_vigente' => $periodo_vigente,
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

        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->actualizarMostrarCalificacion($id_programacion_ud, $nro_calificacion, $mostrar);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }
    public function actualizarMostrarPromedioTodos()
    {
        $id_programacion_ud = $_POST['id_programacion_ud'] ?? 0;
        $mostrar = $_POST['mostrar'] ?? 0;

        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->actualizarMostrarPromedioTodos($id_programacion_ud, $mostrar);
        }
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

        $det_mat = $this->objMatricula->getMatriculaByDetalle($id_detalle_mat);
        $id_programacion_ud = $det_mat['id_programacion_ud'];
        $permitido = $this->model->puedeVerCalificaciones($id_programacion_ud);
        /*consultar si perdiodo esta vigente*/
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo_vigente = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        if ($permitido && $periodo_vigente) {
            $ok = $this->model->guardarRecuperacion($id_detalle_mat, $valor);
        }
        echo json_encode(['ok' => $ok]);
        exit;
    }






    // IMPRESION DE REGISTRO OFICIALL
    public function registroOficial($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        //var_dump($permitido);
        // INFORMACION PARA ASISTENCIAS
        $datos_asistencia = $this->objAsistencia->getDatosAsistencia($id_programacion_ud);

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
        $datosSistema = $this->objDatosSistema->buscar();
        $id_unidad_didactica = $datos['idUnidadDidactica'];
        $ind_logro_capacidad = $this->objIndLogroCapacidad->getIndicadoresLogroCapacidad($id_unidad_didactica);
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Registro Oficial - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_registro_oficial.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('registro oficial - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }


    //  =========================================== IMPRESION DE ACTA FINAL ==============================================
    public function actaFinal($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Acta Final - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_acta_final.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Acta Final - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }


    //  =========================================== IMPRESION DE ACTA DE RECUPERACION ==============================================
    public function actaRecuperacion($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Acta Final - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/calificaciones/pdf_acta_recuperacion.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Acta Final - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }




    //  =========================================== REPORTE PARA SISTEMA REGISTRA ==============================================
    public function reporteRegistra($id_programacion_ud)
    {
        // Obtener todos los datos igual que para la vista de edición
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgramacionUD->getIdDocente($id_programacion_ud));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        if (!$permitido) {
            $this->view('academico/calificaciones/ver/' . $id_programacion_ud, [
                'permitido' => false
            ]);
            return;
        }
        // INFORMACION PARA CALIFICACIONES
        $datos = $this->model->getDatosCalificaciones($id_programacion_ud);
        $mostrar_calificaciones = $this->model->getMostrarCalificaciones($id_programacion_ud, $datos['nros_calificacion']);
        $mostrar_promedio_todos = $this->model->todosMostrarPromedio($id_programacion_ud);

        $estudiantes = $datos['estudiantes'];
        $estudiantes_inhabilitados = [];
        $nota_inasistencia = $this->objDatosSistema->getNotaSiInasistencia();
        $datosSistema = $this->objDatosSistema->buscar();
        $nros_calificacion = $datos['nros_calificacion'];
        $recuperaciones = $datos['recuperaciones'];
        $promedios = $datos['promedios'];

        $id_unidad_didactica = $datos['idUnidadDidactica'];
        foreach ($estudiantes as $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $this->model->inhabilitadoPorInasistencia($id_detalle);
            $estudiantes_inhabilitados[$id_detalle] = $inhabilitado;
        }
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);
        $nombre_archivo = "registra_" . $datosGenerales['unidad'] . "_" . $datosGenerales['periodo_academico'] . "_" . date('Ymd_His');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla'); // Cambia el nombre de la hoja
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEEEEE'], // '#eee'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->setCellValue('A1', 'NRO');
        $sheet->setCellValue('B1', 'CÓDIGO ALUMNO');
        $sheet->setCellValue('C1', 'ALUMNO');
        $sheet->setCellValue('D1', 'NOTA');

        $fila = 2;
        foreach ($estudiantes as $i => $est) {
            $id_detalle = $est['id_detalle_matricula'];
            $recup = $recuperaciones[$id_detalle] ?? '';
            $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
            if ($recup != '') {
                $promedio_final = $recup;
            } else {
                $promedio_final = $promedios[$id_detalle];
            }
            if ($inhabilitado) {
                if (is_array($nota_inasistencia) && $est['licencia'] != '') {
                    $promedio_final = '';
                } else {
                    $promedio_final = reset($nota_inasistencia);
                }
            }

            $sheet->setCellValue("A$fila", $i + 1);
            $sheet->setCellValue("B$fila", $est['dni']);
            $sheet->setCellValue("C$fila", $est['apellidos_nombres']);
            $sheet->setCellValue("D$fila", $promedio_final);
            $fila++;
        }
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
