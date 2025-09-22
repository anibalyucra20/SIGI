<?php

namespace App\Controllers\Academico;

use App\Helpers\HorarioHelper;
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
            $esAdminAcademico = (\Core\Auth::esAdminAcademico());
            $permitido = (($esDocenteAsignado || $esAdminAcademico));
            $periodo_vigente = ($periodo && $periodo['vigente']);
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
            $cant_ses = count($sesiones) - 1;
            $datosGenerales['fecha_inicio'] = $sesiones[0]['fecha'];
            $datosGenerales['fecha_fin'] = $sesiones[$cant_ses]['fecha'];
        } else {
            $errores[] = "No existe un sílabo registrado para esta programación.";
            $datosGenerales = $competenciasUnidadDidactica = $capacidades = $competenciasTransversales = $sesiones = [];
        }
        $this->view('academico/silabos/editar', [
            'silabo' => $silabo,
            'permitido' => $permitido,
            'periodo_vigente' => $periodo_vigente,
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
        $errores   = [];

        // 1) Cargar sílabo y validar permisos
        $silabo = $this->model->getSilaboById($id_silabo);
        if (!$silabo) {
            $errores[] = "No se encontró el sílabo.";
        } else {
            $programacion = $this->objProgramacionUD->find($silabo['id_prog_unidad_didactica']);
            $periodo      = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
            $esDocente    = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
            $esAdmin      = (\Core\Auth::esAdminAcademico());
            $permitido    = (($esDocente || $esAdmin) && ($periodo && $periodo['vigente']));
            if (!$permitido) {
                $errores[] = "No tiene permisos para editar este sílabo o el periodo ya culminó.";
            }
        }

        // 2) Normalizar/validar HORARIO (se guarda en JSON uniforme si existe el helper)
        $horarioEntrada = $_POST['horario'] ?? '';
        $horarioGuardar = $horarioEntrada; // fallback: si no hay helper, guarda tal cual

        if (class_exists(\App\Helpers\HorarioHelper::class)) {
            // Si ya viene JSON válido lo dejamos; si no, lo parseamos desde texto
            if (!\App\Helpers\HorarioHelper::isJson($horarioEntrada)) {
                // Permitir horario vacío
                if (trim($horarioEntrada) !== '') {
                    $parsed = \App\Helpers\HorarioHelper::parseText($horarioEntrada);
                    if (isset($parsed['error'])) {
                        $_SESSION['flash_error'] = 'Horario inválido: ' . $parsed['error'];
                        header('Location: ' . BASE_URL . '/academico/silabos/editar/' . (int)($_POST['id_silabo'] ?? 0));
                        exit;
                    }
                    // Guardar como JSON normalizado
                    $horarioGuardar = \App\Helpers\HorarioHelper::toJson($parsed);
                } else {
                    // cadena vacía -> guarda vacío
                    $horarioGuardar = '';
                }
            } else {
                // Ya es JSON; lo puedes validar mínimamente si quieres
                $decoded = json_decode($horarioEntrada, true);
                if (!is_array($decoded)) {
                    $_SESSION['flash_error'] = 'El horario JSON no tiene un formato válido.';
                    header('Location: ' . BASE_URL . '/academico/silabos/editar/' . (int)($_POST['id_silabo'] ?? 0));
                    exit;
                }
                $horarioGuardar = $horarioEntrada; // dejar como vino
            }
        }

        // 3) Recolectar campos editables del sílabo
        $data = [
            'horario'                          => $horarioGuardar,
            'sumilla'                          => trim($_POST['sumilla'] ?? ''),
            'metodologia'                      => trim($_POST['metodologia'] ?? ''),
            'recursos_didacticos'              => trim($_POST['recursos_didacticos'] ?? ''),
            'sistema_evaluacion'               => trim($_POST['sistema_evaluacion'] ?? ''),
            'recursos_bibliograficos_impresos' => trim($_POST['recursos_bibliograficos_impresos'] ?? ''),
            'recursos_bibliograficos_digitales' => trim($_POST['recursos_bibliograficos_digitales'] ?? ''),
        ];

        // 4) Si hubo errores de permisos u otros, re-renderiza la vista con datos
        if (!empty($errores)) {
            $id_programacion            = $silabo['id_prog_unidad_didactica'] ?? null;
            $datosGenerales             = $this->model->getDatosGenerales($id_programacion);
            $competenciasUnidadDidactica = $this->objCompetencia->getCompetenciasDeUnidadDidactica($programacion['id_unidad_didactica']);
            $capacidades                = $this->objCapacidad->getCapacidadesUnidadDidactica($programacion['id_unidad_didactica']);
            $competenciasTransversales  = $this->objCompetencia->getCompetenciasTransversalesByUD($programacion['id_unidad_didactica']);
            $indicadoresLogroCapacidad  = $this->objIndicadorLogroCapacidad->getIndicadoresLogroCapacidad($programacion['id_unidad_didactica']);
            $sesiones                   = $this->model->getSesionesSilaboDetallado($silabo['id']);

            $this->view('academico/silabos/editar', [
                'errores'                    => $errores,
                'silabo'                     => $silabo,
                'permitido'                  => true,
                'datosGenerales'             => $datosGenerales,
                'competenciasUnidadDidactica' => $competenciasUnidadDidactica,
                'capacidades'                => $capacidades,
                'competenciasTransversales'  => $competenciasTransversales,
                'indicadoresLogroCapacidad'  => $indicadoresLogroCapacidad,
                'sesiones'                   => $sesiones,
                'module'                     => 'academico',
                'pageTitle'                  => 'Editar Sílabo'
            ]);
            return;
        }

        // 5) Guardar cambios del SÍLABO
        $this->model->actualizarSilabo($id_silabo, $data);

        // 6) Guardar cambios en SESIONES (si llegaron)
        if (!empty($_POST['sesiones']) && is_array($_POST['sesiones'])) {
            foreach ($_POST['sesiones'] as $id_actividad => $info) {
                // Espera keys: fecha, id_ind_logro_aprendizaje, denominacion, contenido, logro_sesion, tareas_previas
                $this->model->actualizarSesionSilaboCompleto($id_actividad, $info);
            }
        }

        // 7) Listo
        $_SESSION['flash_success'] = "Sílabo actualizado correctamente.";
        $id_programacion = $silabo['id_prog_unidad_didactica'] ?? 0;
        header('Location: ' . BASE_URL . '/academico/silabos/editar/' . (int)$id_programacion);
        exit;
    }



    public function pdf($id_silabo)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';


        // Obtener todos los datos igual que para la vista de edición
        $silabo = $this->model->getSilaboById($id_silabo);
        $id_programacion = $silabo['id_prog_unidad_didactica'];
        $permitido = false;
        $errores = [];

        if ($silabo) {
            $datosInstitucionales = $this->objDatosIes->buscar();
            $datosSistema = $this->objDatosSistema->buscar();

            $programacion = $this->objProgramacionUD->find($id_programacion);
            $periodo = $this->objPeriodoAcademico->getPeriodoVigente($programacion['id_periodo_academico']);
            $esDocenteAsignado = ($programacion['id_docente'] == ($_SESSION['sigi_user_id'] ?? -1));
            $esAdminAcademico = (\Core\Auth::esAdminAcademico());
            $permitido = (($esDocenteAsignado || $esAdminAcademico));
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
            $cant_ses = count($sesiones) - 1;
            $datosGenerales['fecha_inicio'] = $sesiones[0]['fecha'];
            $datosGenerales['fecha_fin'] = $sesiones[$cant_ses]['fecha'];
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
