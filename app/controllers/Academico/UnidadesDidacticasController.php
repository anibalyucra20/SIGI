<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/UnidadesDidacticas.php';
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';


use App\Models\Academico\UnidadesDidacticas;
use App\Models\Academico\Silabos;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Calificaciones;
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
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objDatosIes = new DatosInstitucionales();
        $this->objDatosSistema = new DatosSistema();
        $this->objDocente = new Docente();
    }

    public function index()
    {
        // Puedes pasar $periodo si lo necesitas para la lógica JS de acciones.
        $this->view('academico/unidadesDidacticas/index', [
            'periodo' => $this->objPeriodoAcademico->getPeriodoVigente($_SESSION['sigi_periodo_actual_id'] ?? 0),
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
        $this->view('academico/unidadesDidacticas/informeFinal', [
            'id_programacion' => $id_programacion_ud,
            'silabo' => $this->objSilabo->getSilaboByProgramacion($id_programacion_ud),
            'datosGenerales' => $this->objSilabo->getDatosGenerales($id_programacion_ud),
            'module' => 'academico',
            'pageTitle' => 'Informe Final'
        ]);
    }
    public function guardarEdicionInformeFinal($id_programacion_ud)
    {
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
        header('Location: ' . BASE_URL . '/academico/unidadesDidacticas/informeFinal/' . $id_programacion_ud);
    }
    public function pdfInformeFinal($id_programacion)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

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
}
