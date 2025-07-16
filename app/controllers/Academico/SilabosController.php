<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Silabos.php';
require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Competencias.php';
require_once __DIR__ . '/../../../app/models/Sigi/Capacidades.php';
require_once __DIR__ . '/../../../app/models/Sigi/IndicadorLogroCapacidad.php';
require_once __DIR__ . '/../../../app/utils/MYPDF.php';

use App\Models\Academico\Silabos;
use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Sigi\DatosInstitucionales;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Competencias;
use App\Models\Sigi\Capacidades;
use App\Models\Sigi\IndicadorLogroCapacidad;
use MYPDF;

class SilabosController extends Controller
{
    protected $model;
    protected $objProgramacionUD;
    protected $objDatosIes;
    protected $objDatosSistema;
    protected $objPeriodoAcademico;
    protected $objCompetencia;
    protected $objCapacidad;
    protected $objIndicadorLogroCapacidad;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Silabos();
        $this->objProgramacionUD = new ProgramacionUnidadDidactica();
        $this->objDatosIes = new DatosInstitucionales();
        $this->objDatosSistema = new DatosSistema();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objCompetencia = new Competencias();
        $this->objCapacidad = new Capacidades();
        $this->objIndicadorLogroCapacidad = new IndicadorLogroCapacidad();
    }

    public function editar($id_programacion)
    {
        $silabo = $this->model->getSilaboByProgramacion($id_programacion);
        $permitido = false;
        $errores = [];

        if ($silabo) {
            $programacion = $this->objProgramacionUD->find($id_programacion);
            $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

            $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
            $esAdminAcademico = $this->esAdminAcademico();
            $permitido = (($esDocenteAsignado || $esAdminAcademico) && ($periodo && $periodo['vigente']));

            // DATOS GENERALES
            $datosGenerales = $this->model->getDatosGenerales($id_programacion);

            // SECCION III: COMPETENCIAS DEL MODULO
            $competenciasUnidadDidactica = $this->objCompetencia->getCompetenciasDeUnidadDidactica($programacion['id_unidad_didactica']);

            // SECCION IV: CAPACIDADES DE LA UD
            $capacidades = $this->objCapacidad->getCapacidadesUnidadDidactica($programacion['id_unidad_didactica']);

            // SECCION V: COMPETENCIAS TRANSVERSALES DEL MODULO
            $competenciasTransversales = $this->objCompetencia->getCompetenciasTransversalesByUD($programacion['id_unidad_didactica']);

            $indicadoresLogroCapacidad = $this->objIndicadorLogroCapacidad->getIndicadoresLogroCapacidad($programacion['id_unidad_didactica']);


            // SECCION VI: SESIONES DE APRENDIZAJE
            //$sesiones = $this->model->getSesionesSilabo($silabo['id']);
            $sesiones = $this->model->getSesionesSilaboDetallado($silabo['id']); // método especial, ver abajo
        } else {
            $errores[] = "No existe un sílabo registrado para esta programación.";
            $datosGenerales = $competenciasUnidadDidactica = $capacidades = $competenciasTransversales = $sesiones = [];
        }

        $this->view('academico/silabos/editar', [
            'silabo' => $silabo,
            'permitido' => $permitido,
            'errores' => $errores,
            'datosGenerales' => $datosGenerales ?? [],
            'competenciasUnidadDidactica' => $competenciasUnidadDidactica ?? [],
            'capacidades' => $capacidades ?? [],
            'competenciasTransversales' => $competenciasTransversales ?? [],
            'indicadoresLogroCapacidad' => $indicadoresLogroCapacidad ?? [],
            'sesiones' => $sesiones ?? [],
            'module' => 'academico',
            'pageTitle' => 'Editar Sílabo'
        ]);
    }

    public function guardarEdicion()
    {
        $id_silabo = $_POST['id_silabo'] ?? null;
        $errores = [];

        $silabo = $this->model->getSilaboById($id_silabo);
        $id_programacion = $silabo['id_prog_unidad_didactica'] ?? null;
        if (!$silabo) {
            $errores[] = "No se encontró el sílabo.";
        } else {
            $programacion = $this->objProgramacionUD->find($silabo['id_prog_unidad_didactica']);
            $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
            $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
            $esAdminAcademico = $this->esAdminAcademico();
            $permitido = (($esDocenteAsignado || $esAdminAcademico) && ($periodo && $periodo['vigente']));

            if (!$permitido) {
                $errores[] = "No tiene permisos para editar este sílabo o el periodo ya culminó.";
            }
        }

        // Validar campos requeridos
        $data = [
            'sumilla' => trim($_POST['sumilla'] ?? ''),
            'metodologia' => trim($_POST['metodologia'] ?? ''),
            'recursos_didacticos' => trim($_POST['recursos_didacticos'] ?? ''),
            'sistema_evaluacion' => trim($_POST['sistema_evaluacion'] ?? ''),
            'recursos_bibliograficos_impresos' => trim($_POST['recursos_bibliograficos_impresos'] ?? ''),
            'recursos_bibliograficos_digitales' => trim($_POST['recursos_bibliograficos_digitales'] ?? ''),
        ];
        /*foreach ($data as $campo => $valor) {
            if ($valor === '') {
                $errores[] = "El campo '$campo' es obligatorio.";
            }
        }*/

        // Guardar campos extra (puedes validar si son obligatorios o no)
        /*$data['horario'] = trim($_POST['horario'] ?? '');
        $data['estrategia_evaluacion_indicadores'] = trim($_POST['estrategia_evaluacion_indicadores'] ?? '');
        $data['estrategia_evaluacion_tecnica'] = trim($_POST['estrategia_evaluacion_tecnica'] ?? '');
        $data['promedio_indicadores_logro'] = trim($_POST['promedio_indicadores_logro'] ?? '');*/

        if (!empty($errores)) {
            // Recargar todos los datos necesarios para el formulario
            $id_programacion = $silabo['id_prog_unidad_didactica'] ?? null;
            $datosGenerales = $this->model->getDatosGenerales($id_programacion);
            //$competenciasModulo = $this->model->getCompetenciasModulo($id_programacion);
            $competenciasUnidadDidactica = $this->objCompetencia->getCompetenciasDeUnidadDidactica($programacion['id_unidad_didactica']);
            $capacidades = $this->objCapacidad->getCapacidadesUnidadDidactica($programacion['id_unidad_didactica']);
            $competenciasTransversales = $this->objCompetencia->getCompetenciasTransversalesByUD($programacion['id_unidad_didactica']);
            $indicadoresLogroCapacidad = $this->objIndicadorLogroCapacidad->getIndicadoresLogroCapacidad($programacion['id_unidad_didactica']);
            $sesiones = $this->model->getSesionesSilaboDetallado($silabo['id']);

            $this->view('academico/silabos/editar', [
                'errores' => $errores,
                'silabo' => $silabo,
                'permitido' => true,
                'datosGenerales' => $datosGenerales,
                'competenciasUnidadDidactica' => $competenciasUnidadDidactica,
                'capacidades' => $capacidades,
                'competenciasTransversales' => $competenciasTransversales,
                'indicadoresLogroCapacidad' => $indicadoresLogroCapacidad,
                'sesiones' => $sesiones,
                'module' => 'academico',
                'pageTitle' => 'Editar Sílabo'
            ]);
            return;
        }

        // Guardar cambios del sílabo
        $this->model->actualizarSilabo($id_silabo, $data);

        // Guardar cambios en sesiones de aprendizaje (opcional, si el usuario editó sesiones)
        if (!empty($_POST['sesiones']) && is_array($_POST['sesiones'])) {
            foreach ($_POST['sesiones'] as $id_actividad => $info) {
                $this->model->actualizarSesionSilaboCompleto($id_actividad, $info);
            }
        }
        $_SESSION['flash_success'] = "Sílabo actualizado correctamente.";
        $this->editar($id_programacion);
        //header('Location: ' . BASE_URL . '/academico/unidadesDidacticas');
        exit;
    }

    protected function esAdminAcademico()
    {
        // Asume que el rol de admin académico es 1 (ajusta si tienes otro)
        return (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
    }

    public function pdf($id_programacion)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        // Obtener todos los datos igual que para la vista de edición
        $silabo = $this->model->getSilaboByProgramacion($id_programacion);
        $permitido = false;
        $errores = [];

        if ($silabo) {
            $datosInstitucionales = $this->objDatosIes->buscar();
            $datosSistema = $this->objDatosSistema->buscar();
            $programacion = $this->objProgramacionUD->find($id_programacion);
            $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);

            $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
            $esAdminAcademico = $this->esAdminAcademico();
            $permitido = (($esDocenteAsignado || $esAdminAcademico) && ($periodo && $periodo['vigente']));
            // DATOS GENERALES DE SILABO
            $datosGenerales = $this->model->getDatosGenerales($id_programacion);
            // SECCION III: COMPETENCIAS DEL MODULO
            $competenciasUnidadDidactica = $this->objCompetencia->getCompetenciasDeUnidadDidactica($programacion['id_unidad_didactica']);
            // SECCION IV: CAPACIDADES DE LA UD
            $capacidades = $this->objCapacidad->getCapacidadesUnidadDidactica($programacion['id_unidad_didactica']);
            // SECCION V: COMPETENCIAS TRANSVERSALES DEL MODULO
            $competenciasTransversales = $this->objCompetencia->getCompetenciasTransversalesByUD($programacion['id_unidad_didactica']);
            $indicadoresLogroCapacidad = $this->objIndicadorLogroCapacidad->getIndicadoresLogroCapacidad($programacion['id_unidad_didactica']);
            // SECCION VI: SESIONES DE APRENDIZAJE
            //$sesiones = $this->model->getSesionesSilabo($silabo['id']);
            $sesiones = $this->model->getSesionesSilaboDetallado($silabo['id']); // método especial, ver abajo
        } else {
            $errores[] = "No existe un sílabo registrado para esta programación.";
            $datosGenerales = $competenciasUnidadDidactica = $capacidades = $competenciasTransversales = $sesiones = [];
        }
        // $datosGenerales, $silabo, $competenciasUnidadDidactica, $capacidades, $competenciasTransversales, $sesiones, etc.

        ob_start();
        include __DIR__ . '/../../views/academico/silabos/plantilla_pdf.php';
        $html = ob_get_clean();

        // Usar tu clase MYPDF (si quieres header/footer institucional)
        $pdf = new MYPDF();
        $pdf->SetMargins(12, 30, 12); // margen superior suficiente para header
        $pdf->SetAutoPageBreak(true, 32); // margen inferior suficiente para footer
        $pdf->SetTitle('Sílabo ' . $datosGenerales['unidad']);
        $pdf->AddPage("P");
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Silabo_' . $datosGenerales['unidad'] . '.pdf', 'I'); // 'I' para inline
        exit;
    }
}
