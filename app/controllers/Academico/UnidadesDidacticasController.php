<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/UnidadesDidacticas.php';
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';


use App\Models\Academico\UnidadesDidacticas;
use App\Models\Academico\Silabos;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Calificaciones;
use App\Models\Sigi\Programa;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\DatosInstitucionales;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Docente;
use TCPDF;

class UnidadesDidacticasController extends Controller
{
    protected $model;
    protected $objPeriodoAcademico;
    protected $objSilabo;
    protected $objProgramacionUD;
    protected $objCalificacion;
    protected $objPrograma;
    protected $objDatosIes;
    protected $objDatosSistema;
    protected $objDocente;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UnidadesDidacticas();
        $this->objSilabo = new Silabos();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objCalificacion = new Calificaciones();
        $this->objPrograma = new Programa();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objDatosIes = new DatosInstitucionales();
        $this->objDatosSistema = new DatosSistema();
        $this->objDocente = new Docente();
    }

    public function index()
    {
        // Puedes pasar $periodo si lo necesitas para la lógica JS de acciones.
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $this->view('academico/unidadesDidacticas/index', [
            'periodo' => $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id'] ?? 0),
            'periodo_vigente' => $periodo_vigente,
            'module' => 'academico',
            'pageTitle' => 'Mis Unidades Didácticas Programadas'
        ]);
    }

    public function data()
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw      = $_GET['draw']  ?? 1;
        $start     = $_GET['start'] ?? 0;
        $length    = $_GET['length'] ?? 10;
        $orderCol  = $_GET['order'][0]['column'] ?? 0;
        $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

        $filters = [
            'id_sede'    => $_SESSION['sigi_sede_actual'] ?? 0,
            'id_periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
            'id_docente' => $_SESSION['sigi_user_id'] ?? 0,
        ];

        $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data'            => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    public function informeFinal($id_programacion_ud)
    {
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = (($esDocenteAsignado || $esAdminAcademico));
        $periodo_vigente = ($periodo && $periodo['vigente']);


        $this->view('academico/unidadesDidacticas/informeFinal', [
            'id_programacion' => $id_programacion_ud,
            'permitido' => $permitido,
            'periodo_vigente' => $periodo_vigente,
            'silabo' => $this->objSilabo->getSilaboByProgramacion($id_programacion_ud),
            'datosGenerales' => $this->objSilabo->getDatosGenerales($id_programacion_ud),
            'module' => 'academico',
            'pageTitle' => 'Informe Final'
        ]);
    }

    public function guardarEdicionInformeFinal($id_programacion_ud)
    {
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = (($esDocenteAsignado || $esAdminAcademico) && ($periodo && $periodo['vigente']));

        if ($permitido) {
            $supervisado = $_POST['supervisado'] ?? '';
            $reg_evaluacion = $_POST['reg_evaluacion'] ?? '';
            $reg_auxiliar = $_POST['reg_auxiliar'] ?? '';
            $prog_curricular = $_POST['prog_curricular'] ?? '';
            $otros = $_POST['otros'] ?? '';
            $logros_obtenidos = trim($_POST['logros_obtenidos'] ?? '');
            $dificultades = trim($_POST['dificultades'] ?? '');
            $sugerencias = trim($_POST['sugerencias'] ?? '');

            $this->model->actualizardatosInformeFinal($id_programacion_ud, [
                'supervisado' => $supervisado,
                'reg_evaluacion' => $reg_evaluacion,
                'reg_auxiliar' => $reg_auxiliar,
                'prog_curricular' => $prog_curricular,
                'otros' => $otros,
                'logros_obtenidos' => $logros_obtenidos,
                'dificultades' => $dificultades,
                'sugerencias' => $sugerencias,
            ]);
            $_SESSION['flash_success'] = "Datos actualizados correctamente.";
        } else {
            $_SESSION['flash_error'] = "Error, No cuenta con los permisos para realizar la Operación";
        }
        header('Location: ' . BASE_URL . '/academico/unidadesDidacticas/informeFinal/' . $id_programacion_ud);
    }
    public function pdfInformeFinal($id_programacion)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        $programacion = $this->objProgramacionUD->find($id_programacion);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = (($esDocenteAsignado || $esAdminAcademico));
        $periodo_vigente = ($periodo && $periodo['vigente']);

        // 1. Datos generales de la programación
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion);

        $periodo_nombre = $datosGenerales['periodo_lectivo'];
        $institucion = 'INSTITUTO DE EDUCACIÓN SUPERIOR “NOMBRE”'; // Puedes reemplazar por $objDatosInstitucionales->buscar()['nombre_institucion'];
        $programa = $datosGenerales['programa'];
        $modulo = $datosGenerales['modulo'];
        $unidad = $datosGenerales['unidad'];
        $semestre = $datosGenerales['periodo_lectivo'];
        $docente = $datosGenerales['docente'];

        // 2. Porcentaje de avance
        $avance_curricular = $this->model->getPorcentajeAvanceCurricular($id_programacion); // debe contar sesiones con denominación no vacía / total
        // 3. Última clase desarrollada
        $ultima_clase = $this->model->getUltimaClaseDesarrollada($id_programacion); // devuelve denominación + semana/fecha
        // 4. Sesiones no desarrolladas
        $sesiones_no_desarrolladas = $this->model->getSesionesNoDesarrolladas($id_programacion); // lista de sesiones vacías
        // 5. Datos estadísticos (puedes tenerlo en un modelo "Matricula" o "ResumenNotas")
        $estadistica = $this->model->getResumenEstadisticoFinal($id_programacion, $this->objCalificacion);
        extract($this->model->getResumenEstadisticoFinal($id_programacion, $this->objCalificacion)); // esto debería retornar todas las variables estadísticas que necesitas
        // 6. Logo y configuración institucional
        $datosSistema = $this->objDatosSistema->buscar();
        // Llama a la vista TCPDF con $reporteVars o genera el HTML para TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Caratula - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(20, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/unidadesDidacticas/pdfInformeFinal.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Caratula - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }

    public function imprimirCaratula($id_programacion_ud)
    {
        $programacion = $this->objProgramacionUD->find($id_programacion_ud);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

        $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = (($esDocenteAsignado || $esAdminAcademico));
        $periodo_vigente = ($periodo && $periodo['vigente']);

        require_once __DIR__ . '/../../../vendor/autoload.php';
        // Obtener todos los datos igual que para la vista de edición
        $silabo = $this->objSilabo->getSilaboByProgramacion($id_programacion_ud);
        $datosSistema = $this->objDatosSistema->buscar();
        $datosGenerales = $this->objSilabo->getDatosGenerales($id_programacion_ud);
        $id_periodo_pud = $datosGenerales['id_periodo_lectivo'];
        $datoDirector = $this->objDocente->getDirectorPorPeriodo($id_periodo_pud);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Caratula - ' . $datosGenerales['unidad']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(8, 15, 8);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->AddPage();

        // --- GENERA EL HTML ---
        ob_start();
        include __DIR__ . '/../../views/academico/unidadesDidacticas/caratula.php';
        $html = ob_get_clean();
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('Caratula - ' . $datosGenerales['unidad'] . '.pdf', 'I');
    }

    public function evaluar()
    {
        $id_sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $programas = $this->objPrograma->getAllBySede($id_sede);
        $docentes = $this->objDocente->getDocentesPorSede($id_sede);
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id']);
        $periodo_vigente = ($periodo && $periodo['vigente']);
        $this->view('academico/unidadesDidacticas/evaluar', [
            'periodo' => $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id'] ?? 0),
            'periodo_vigente' => $periodo_vigente,
            'programas' => $programas,
            'docentes' => $docentes,
            'module' => 'academico',
            'pageTitle' => 'Programaciones de Unidades Didácticas'
        ]);
    }
    public function data_evaluar()
    {
        if (\Core\Auth::esAdminAcademico()):
            header('Content-Type: application/json; charset=utf-8');
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 0;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            $filters = [
                'id_sede'   => $_SESSION['sigi_sede_actual'] ?? 0,
                'id_periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
                'programa'  => $_GET['filter_programa'] ?? null,
                'plan'      => $_GET['filter_plan'] ?? null,
                'modulo'    => $_GET['filter_modulo'] ?? null,
                'semestre'  => $_GET['filter_semestre'] ?? null,
                'docente'   => $_GET['filter_docente'] ?? null,
                'turno'     => $_GET['filter_turno'] ?? null,
                'seccion'   => $_GET['filter_seccion'] ?? null,
                'unidad'    => $_GET['filter_unidad'] ?? null,
            ];

            $result = $this->objProgramacionUD->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }



    //======================PARA COPIARSILABO Y SESIONES=================================================

    public function configuracion($id_programacion)
    {
        $programacion = $this->objProgramacionUD->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada.";
            header('Location: ' . BASE_URL . '/academico/unidadesDidacticas');
            exit;
        }

        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
        $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = ($esDocenteAsignado || $esAdminAcademico);
        $periodo_vigente = ($periodo && $periodo['vigente']);

        if (!$permitido) {
            $_SESSION['flash_error'] = "No tiene permisos para configurar esta programación.";
            header('Location: ' . BASE_URL . '/academico/unidadesDidacticas');
            exit;
        }

        $sedeActual = (int)($_SESSION['sigi_sede_actual'] ?? $programacion['id_sede']);
        $infoUD = $this->objProgramacionUD->getInfoBasicaUD($id_programacion);
        $silaboDestino = $this->objSilabo->getSilaboByProgramacion($id_programacion);
        $programas = $this->objPrograma->getAllBySede($sedeActual);
        // ⚠️ La tabla se llenará por AJAX con server-side, no enviamos $candidatas aquí
        $this->view('academico/unidadesDidacticas/configuracion', [
            'programacion' => $programacion,
            'programas' => $programas,
            'infoUD' => $infoUD,
            'silaboDestino' => $silaboDestino,
            'periodo_vigente' => $periodo_vigente,
            'permitido' => $permitido,
            'module' => 'academico',
            'pageTitle' => 'Configuración de Unidad Didáctica'
        ]);
    }

    // DataTables server-side para candidatas paginadas + filtros
    // App/Controllers/Academico/UnidadesDidacticasController.php

    public function candidatasData($id_programacion)
    {
        header('Content-Type: application/json; charset=utf-8');

        $dest = $this->objProgramacionUD->find($id_programacion);
        if (!$dest) {
            echo json_encode([
                'draw' => (int)($_GET['draw'] ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
            return;
        }

        $sedeActual = (int)($_SESSION['sigi_sede_actual'] ?? $dest['id_sede']);

        // DataTables
        $draw   = (int)($_GET['draw'] ?? 0);
        $start  = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);

        $orderIdx = (int)($_GET['order'][0]['column'] ?? 1);
        $orderDir = strtoupper($_GET['order'][0]['dir'] ?? 'DESC');
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) $orderDir = 'DESC';

        // 👇 Reutilizamos los nombres de filtros del ejemplo
        $filters = [
            'programa_id' => ($_GET['filter_programa'] ?? '') !== '' ? (int)$_GET['filter_programa'] : null,
            'plan_id'     => ($_GET['filter_plan'] ?? '')     !== '' ? (int)$_GET['filter_plan']     : null,
            'modulo_id'   => ($_GET['filter_modulo'] ?? '')   !== '' ? (int)$_GET['filter_modulo']   : null,
            'semestre_id' => ($_GET['filter_semestre'] ?? '') !== '' ? (int)$_GET['filter_semestre'] : null,
        ];

        $result = $this->objProgramacionUD->getCandidatasPaged(
            $id_programacion,
            $sedeActual,
            $filters,
            $start,
            $length,
            $orderIdx,
            $orderDir
        );

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['rows']
        ]);
    }


    public function copiarContenido()
    {
        $id_prog_dest = (int)($_POST['id_prog_dest'] ?? 0);
        $id_prog_origen = (int)($_POST['id_prog_origen'] ?? 0);

        if (!$id_prog_dest || !$id_prog_origen) {
            $_SESSION['flash_error'] = "Faltan parámetros para copiar.";
            header('Location: ' . BASE_URL . '/academico/unidadesDidacticas');
            exit;
        }

        $dest = $this->objProgramacionUD->find($id_prog_dest);
        $origen = $this->objProgramacionUD->find($id_prog_origen);
        if (!$dest || !$origen) {
            $_SESSION['flash_error'] = "No se encontró la programación origen o destino.";
            header('Location: ' . BASE_URL . "/academico/unidadesDidacticas/configuracion/$id_prog_dest");
            exit;
        }

        // Permisos
        $periodo = $this->objPeriodoAcademico->getPeriodoVigente($dest['id_periodo_academico']);
        $esDocenteAsignado = ($dest['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
        $esAdminAcademico = (\Core\Auth::esAdminAcademico());
        $permitido = ($esDocenteAsignado || $esAdminAcademico);
        if (!$permitido || !($periodo && $periodo['vigente'])) {
            $_SESSION['flash_error'] = "No autorizado o el periodo ya culminó.";
            header('Location: ' . BASE_URL . "/academico/unidadesDidacticas/configuracion/$id_prog_dest");
            exit;
        }

        // Validar misma sede (sede actual) y mismo nombre de UD
        $sedeActual = (int)($_SESSION['sigi_sede_actual'] ?? $dest['id_sede']);
        $infoDest = $this->objProgramacionUD->getInfoBasicaUD($id_prog_dest);
        $infoOri = $this->objProgramacionUD->getInfoBasicaUD($id_prog_origen);
        if ($sedeActual !== (int)$origen['id_sede']) {
            $_SESSION['flash_error'] = "La programación origen no pertenece a la sede actual.";
            header('Location: ' . BASE_URL . "/academico/unidadesDidacticas/configuracion/$id_prog_dest");
            exit;
        }
        if (mb_strtolower(trim($infoDest['unidad_nombre'])) !== mb_strtolower(trim($infoOri['unidad_nombre']))) {
            $_SESSION['flash_error'] = "Las unidades didácticas no coinciden por nombre.";
            header('Location: ' . BASE_URL . "/academico/unidadesDidacticas/configuracion/$id_prog_dest");
            exit;
        }

        try {
            // Antes: $this->silabosModel->clonarDesdeProgramacion(...)
            $res = $this->objSilabo->clonarContenidoDesdeProgramacion(
                $id_prog_origen,
                $id_prog_dest,
                [
                    'copiar_fechas'   => false, // cámbialo a true si quieres también fechas
                    'copiar_indicador' => false   // clonar id_ind_logro_aprendizaje en PAS
                ]
            );
            $_SESSION['flash_success'] = "Contenido clonado. Semanas actualizadas: {$res['semanas_actualizadas']}, Sesiones actualizadas: {$res['sesiones_actualizadas']}.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo clonar: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . "/academico/unidadesDidacticas/configuracion/$id_prog_dest");
        exit;
    }
}
