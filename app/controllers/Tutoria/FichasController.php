<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;

use App\Models\Tutoria\EstudianteTutoria;
use App\Models\Tutoria\RecojoInformacion;
use App\Models\Tutoria\Programacion;

class FichasController extends Controller
{
    protected $objEstudianteTutoria;
    protected $objRecojoInformacion;
    protected $objProgramacion;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a las fichas de tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->objEstudianteTutoria = new EstudianteTutoria();
        $this->objRecojoInformacion = new RecojoInformacion();
        $this->objProgramacion      = new Programacion();
    }

    public function ver($id_tutoria_estudiante)
    {
        $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "Estudiante no encontrado.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/fichas');
            exit;
        }

        $items = $this->objRecojoInformacion->obtenerPorTutorando($id_tutoria_estudiante);

        $this->view('tutoria/tutores/fichas/ver', [
            'tutorando' => $tutorando,
            'items'     => $items,
            'module'    => 'tutoria',
            'pageTitle' => 'Ficha Diagnóstica - ' . $tutorando['nombre_completo']
        ]);
        exit;
    }

    public function editar($id_tutoria_estudiante)
    {
        $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "Estudiante no encontrado.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/fichas');
            exit;
        }

        $items = $this->objRecojoInformacion->obtenerPorTutorando($id_tutoria_estudiante);

        $this->view('tutoria/tutores/fichas/editar', [
            'tutorando' => $tutorando,
            'items'     => $items,
            'module'    => 'tutoria',
            'pageTitle' => 'Registrar Diagnóstico - ' . $tutorando['nombre_completo']
        ]);
        exit;
    }

    public function guardar()
    {
        $id_te = (int)($_POST['id_tutoria_estudiante'] ?? 0);
        if (!$id_te) {
            $_SESSION['flash_error'] = "Identificador de estudiante inválido.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/fichas');
            exit;
        }

        $descripciones = $_POST['descripcion'] ?? [];
        $valores       = $_POST['valor'] ?? [];

        $items = [];
        foreach ($descripciones as $idx => $desc) {
            $items[] = [
                'descripcion' => trim($desc),
                'valor'       => (int)($valores[$idx] ?? 0)
            ];
        }

        try {
            $this->objRecojoInformacion->guardarLote($id_te, $items);
            $_SESSION['flash_success'] = "Ficha de diagnóstico guardada correctamente.";
            header('Location: ' . BASE_URL . '/tutoria/fichas/ver/' . $id_te);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al guardar el diagnóstico: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/tutoria/fichas/editar/' . $id_te);
            exit;
        }
    }

    public function individuales($id_programacion = null)
    {
        header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales' . ($id_programacion ? '/' . $id_programacion : ''));
        exit;
    }

    public function grupales($id_programacion = null)
    {
        header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales' . ($id_programacion ? '/' . $id_programacion : ''));
        exit;
    }

    public function individualPdf($id)
    {
        $ctrl = new SesionesController();
        $ctrl->individualPdf($id);
    }

    public function grupalPdf($id)
    {
        $ctrl = new SesionesController();
        $ctrl->grupalPdf($id);
    }

    /**
     * Genera e imprime la Ficha de Evaluación Socioeconómica (Anexo 03) en PDF con TCPDF.
     */
    public function pdf($id_tutoria_estudiante)
    {
        $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "Estudiante no encontrado para generar ficha.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/fichas');
            exit;
        }

        $items = $this->objRecojoInformacion->obtenerPorTutorando($id_tutoria_estudiante);

        if (!defined('K_PATH_CACHE')) {
            define('K_PATH_CACHE', sys_get_temp_dir() . '/');
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('IESTP Huanta - Unidad de Bienestar y Empleabilidad');
        $pdf->SetTitle('Ficha Socioeconómica - ' . $tutorando['nombre_completo']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 10, 12);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();

        $rutaLogo = __DIR__ . '/../../../public/img/logo.png';

        ob_start();
        include __DIR__ . '/../../views/tutoria/tutores/fichas/pdf_ficha_socioeconomica.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');

        $nombreArchivo = 'Ficha_Socioeconomica_Anexo03_' . preg_replace('/[^A-Za-z0-9_]/', '', $tutorando['dni']) . '_' . date('Ymd') . '.pdf';
        $pdf->Output($nombreArchivo, 'I');
        exit;
    }
}
