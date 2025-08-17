<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Sesiones.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/utils/MYPDF.php';

use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Sesiones;
use App\Models\Sigi\PeriodoAcademico;
use MYPDF;

class SesionesController extends Controller
{
    protected $model;
    protected $objProgracionUD;
    protected $objPeriodoAcademico;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Sesiones();
        $this->objProgracionUD = new ProgramacionUnidadDidactica();
        $this->objPeriodoAcademico = new PeriodoAcademico();
    }

    public function ver($id_programacion)
    {
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);
        $programacion = $this->objProgracionUD->find($id_programacion);

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        // Permisos
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        $periodo_vigente = ($periodo && $periodo['vigente']);

        $this->view('academico/sesiones/index', [
            'datosUnidad' => $datosUnidad,
            'id_programacion' => $id_programacion,
            'permitido' => $permitido,
            'periodo_vigente' => $periodo_vigente,
            'module' => 'academico',
            'pageTitle' => 'Sesiones de Aprendizaje'
        ]);
    }


    public function data($id_programacion)
    {
        // Permisos
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;
        if ($permitido):
            header('Content-Type: application/json; charset=utf-8');
            $draw = $_GET['draw'] ?? 1;
            $start = $_GET['start'] ?? 0;
            $length = $_GET['length'] ?? 10;
            $orderCol = $_GET['order'][0]['column'] ?? 1;
            $orderDir = $_GET['order'][0]['dir'] ?? 'asc';

            $result = $this->model->getSesionesPaginadas($id_programacion, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw' => (int)$draw,
                'recordsTotal' => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data' => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }


    public function editar($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];

        // Aquí pasas el id_ind_logro_aprendizaje de la sesión para obtener código/desc.
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);
        $programacion = $this->objProgracionUD->find($id_programacion);

        // Permisos
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;
        $periodo_vigente = ($periodo && $periodo['vigente']);

        $momentos = $this->model->getMomentosSesion($sesion['id']);
        $activEval = $this->model->getActividadesEvaluacion($sesion['id']);

        $this->view('academico/sesiones/editar', [
            'sesion' => $sesion,
            'id_programacion' => $id_programacion,
            'datosUnidad' => $datosUnidad,
            'permitido' => $permitido,
            'periodo_vigente' => $periodo_vigente,
            'momentos' => $momentos,
            'activEval' => $activEval,
            'errores' => [],
            'module' => 'academico',
            'pageTitle' => 'Editar Sesión de Aprendizaje'
        ]);
    }


    public function guardarEdicionSesion($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);

        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;
        if (!$permitido) {
            $_SESSION['flash_error'] = 'No tiene permisos para editar esta sesión.';
            header('Location: ' . BASE_URL . "/academico/sesiones/{$id_programacion}");
            exit;
        }

        // Validaciones ()
        $errores = [];
        $fecha_desarrollo = $_POST['fecha_desarrollo'] ?? '';
        //$id_ind_logro_aprendizaje = $_POST['id_ind_logro_aprendizaje'] ?? '';
        $denominacion = trim($_POST['denominacion'] ?? '');
        $tipo_actividad = trim($_POST['tipo_actividad'] ?? '');
        $logro_sesion = trim($_POST['logro_sesion'] ?? '');
        $bibliografia = trim($_POST['bibliografia'] ?? '');

        if (!$fecha_desarrollo) $errores[] = "Debe ingresar la fecha de desarrollo.";
        //if (!$id_ind_logro_aprendizaje) $errores[] = "Debe seleccionar un indicador de logro.";
        if (!$denominacion) $errores[] = "Debe ingresar la denominación de la sesión.";
        if (!$tipo_actividad) $errores[] = "Debe seleccionar el tipo de actividad.";

        if ($errores) {
            //$indicadoresLogroCapacidad = $this->model->getIndicadoresLogroCapacidad($sesion['id_unidad_didactica']);
            $momentos = $this->model->getMomentosSesion($sesion['id']);
            $activEval = $this->model->getActividadesEvaluacion($sesion['id']);
            $this->view('academico/sesiones/editar', [
                'sesion' => array_merge($sesion, $_POST),
                'id_programacion' => $id_programacion,
                'datosUnidad' => $datosUnidad,
                'permitido' => $permitido,
                //'indicadoresLogroCapacidad' => $indicadoresLogroCapacidad,
                'momentos' => $momentos,
                'activEval' => $activEval,
                'errores' => $errores,
                'module' => 'academico',
                'pageTitle' => 'Editar Sesión de Aprendizaje'
            ]);
            return;
        }

        // Actualiza sesión principal y detalles
        $this->model->actualizarSesionCompleta($id_sesion, [
            'fecha_desarrollo' => $fecha_desarrollo,
            //'id_ind_logro_aprendizaje' => $id_ind_logro_aprendizaje,
            'denominacion' => $denominacion,
            'tipo_actividad' => $tipo_actividad,
            'logro_sesion' => $logro_sesion,
            'bibliografia' => $bibliografia,
            'momentos' => $_POST,
            'activEval' => $_POST
        ]);

        $_SESSION['flash_success'] = "Sesión actualizada correctamente.";
        //header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        $this->editar($id_sesion);
        exit;
    }
    public function duplicar($id_sesion)
    {
        // Permisos
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);

        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        if (!($esAdminAcademico || $esDocenteEncargado)) {
            $_SESSION['flash_error'] = "No tiene permisos para duplicar esta sesión.";
            header('Location: ' . BASE_URL . "/academico/sesiones/{$id_programacion}");
            exit;
        }

        try {
            $newId = $this->model->duplicarSesion($id_sesion);
            $_SESSION['flash_success'] = "Sesión duplicada correctamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al duplicar: " . $e->getMessage();
        }
        header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        exit;
    }
    public function eliminar($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);

        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        if (!($esAdminAcademico || $esDocenteEncargado)) {
            $_SESSION['flash_error'] = "No tiene permisos para eliminar esta sesión.";
            header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
            exit;
        }

        try {
            $this->model->eliminarSesion($id_sesion);
            $_SESSION['flash_success'] = "Sesión eliminada correctamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al eliminar: " . $e->getMessage();
        }
        header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        exit;
    }


    // GET /academico/sesiones/listarPorProgramacion/{id_programacion}
    public function listarPorProgramacion($id_programacion)
    {
        header('Content-Type: application/json; charset=utf-8');

        $prog = $this->objProgracionUD->find((int)$id_programacion);
        if (!$prog) {
            echo json_encode([]);
            return;
        }

        $esDocenteAsignado = ($prog['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico  = (\Core\Auth::esAdminAcademico());
        if (!($esDocenteAsignado || $esAdminAcademico)) {
            echo json_encode([]);
            return;
        }

        // Reutiliza getSesionesPaginadas con un límite grande
        $result = $this->model->getSesionesPaginadas((int)$id_programacion, 1000, 0, 0, 'ASC');
        $rows   = $result['data'] ?? [];

        // Añadimos fecha si te interesa (opcional)
        // (si deseas fecha, puedes extender getSesionesPaginadas en el modelo)
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }

    // POST /academico/sesiones/copiarDesde
    public function copiarDesde()
    {
        header('Content-Type: application/json; charset=utf-8');

        $id_origen      = (int)($_POST['id_origen'] ?? 0);
        $id_destino     = (int)($_POST['id_destino'] ?? 0);
        $copiar_fechas  = (int)($_POST['copiar_fechas'] ?? 1) === 1;

        if (!$id_origen || !$id_destino) {
            echo json_encode(['ok' => false, 'msg' => 'Parámetros incompletos.']);
            return;
        }

        // Info mínima de las sesiones para validar misma programación
        $sesOri = $this->model->getSesionParaEditar($id_origen);
        $sesDes = $this->model->getSesionParaEditar($id_destino);
        if (!$sesOri || !$sesDes) {
            echo json_encode(['ok' => false, 'msg' => 'Sesión origen/destino no encontrada.']);
            return;
        }
        if ((int)$sesOri['id_programacion'] !== (int)$sesDes['id_programacion']) {
            echo json_encode(['ok' => false, 'msg' => 'Las sesiones no pertenecen a la misma programación.']);
            return;
        }

        // Permisos + periodo vigente
        $prog = $this->objProgracionUD->find((int)$sesDes['id_programacion']);
        if (!$prog) {
            echo json_encode(['ok' => false, 'msg' => 'Programación no encontrada.']);
            return;
        }

        $esDocenteAsignado = ($prog['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico  = (\Core\Auth::esAdminAcademico());
        if (!($esDocenteAsignado || $esAdminAcademico)) {
            echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
            return;
        }

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($prog['id_periodo_academico']);
        if (!($periodo && $periodo['vigente'])) {
            echo json_encode(['ok' => false, 'msg' => 'El periodo académico ya culminó.']);
            return;
        }

        // 1) Datos principales de la sesión de origen (reutiliza tu método)
        //    getSesionParaEditar ya trae: denominacion, fecha_desarrollo, tipo_actividad, logro_sesion, bibliografia_...
        $data = [
            'denominacion'     => $sesOri['denominacion'] ?? '',
            'fecha_desarrollo' => $copiar_fechas ? ($sesOri['fecha_desarrollo'] ?? null) : ($sesDes['fecha_desarrollo'] ?? null),
            'tipo_actividad'   => $sesOri['tipo_actividad'] ?? '',
            'logro_sesion'     => $sesOri['logro_sesion'] ?? '',
            'bibliografia'     => $sesOri['bibliografia_obligatoria_docente'] ?? '',
            'momentos'         => [],
            'activEval'        => [],
            // 'id_ind_logro_aprendizaje' => $sesOri['id_ind_logro_aprendizaje'] ?? null, // si quisieras actualizar PAS (lo tienes comentado en tu modelo)
        ];

        // 2) Pareo de momentos por índice (orden ya está definido en tus queries)
        $momOri  = $this->model->getMomentosSesion($id_origen);
        $momDes  = $this->model->getMomentosSesion($id_destino);
        $kM      = min(count($momOri), count($momDes));
        for ($i = 0; $i < $kM; $i++) {
            $o = $momOri[$i];
            $d = $momDes[$i];
            $data['momentos']["actividad_{$d['id']}"] = $o['actividad'] ?? '';
            $data['momentos']["recursos_{$d['id']}"]   = $o['recursos'] ?? '';
            $data['momentos']["tiempo_{$d['id']}"]     = $o['tiempo'] ?? 20;
        }

        // 3) Pareo de actividades de evaluación por índice (orden por momento, luego id)
        $aeOri = $this->model->getActividadesEvaluacion($id_origen);
        $aeDes = $this->model->getActividadesEvaluacion($id_destino);
        $kA    = min(count($aeOri), count($aeDes));
        for ($i = 0; $i < $kA; $i++) {
            $o = $aeOri[$i];
            $d = $aeDes[$i];
            $data['activEval']["indicador_{$d['id']}"]    = $o['indicador_logro_sesion'] ?? '';
            $data['activEval']["tecnica_{$d['id']}"]      = $o['tecnica'] ?? '';
            $data['activEval']["instrumentos_{$d['id']}"] = $o['instrumentos'] ?? '';
        }

        try {
            // 4) Actualización usando tu método existente
            $this->model->actualizarSesionCompleta($id_destino, $data);

            echo json_encode([
                'ok'  => true,
                'msg' => 'Sesión actualizada desde el origen.',
                'res' => [
                    'momentos_actualizados'      => $kM,
                    'activ_eval_actualizadas'    => $kA
                ]
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }



    public function pdf($id_sesion)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php'; // ruta según tu estructura

        // Recupera todos los datos igual que en imprimir()
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $programacion = $this->objProgracionUD->find($id_programacion);
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);
        //var_dump($datosUnidad);
        $momentos = $this->model->getMomentosSesion($sesion['id']);
        $activEval = $this->model->getActividadesEvaluacion($sesion['id']);

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;
        $periodo_vigente = ($periodo && $periodo['vigente']);
        //var_dump($id_sesion);

        // Armamos el HTML igual que en tu vista pero SIN headers/footers de Bootstrap
        ob_start();
        include __DIR__ . '/../../views/academico/sesiones/plantilla_pdf.php'; // Tu vista PDF
        $html = ob_get_clean();
        // Crear PDF
        $pdf = new MYPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SIGI');
        $pdf->SetTitle('Sesión de Aprendizaje');
        $pdf->SetMargins(12, 30, 12, true);
        $pdf->SetAutoPageBreak(TRUE, 30);
        $pdf->AddPage();

        // Escribe el HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Nombre de archivo de descarga
        $filename = 'Sesion_Aprendizaje_' . $sesion['denominacion'] . '_' . $sesion['id'] . '.pdf';

        // Salida para descargar
        $pdf->Output($filename, 'I'); // 'D' para descarga directa; 'I' para inline
        exit;
    }
}
