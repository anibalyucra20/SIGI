<?php

namespace App\Controllers\Bolsa;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Bolsa\Reportes;
use App\Models\Bolsa\Ofertas;
use App\Models\Bolsa\Empresas;
use App\Models\Bolsa\Postulaciones;
use PDO;

class ReportesController extends Controller
{
    protected $reportesModel;
    protected $ofertasModel;
    protected $empresasModel;

    public function __construct()
    {
        parent::__construct();

        // 1. Asegurar contexto del módulo de Bolsa Laboral (módulo 7)
        $moduloBolsa = 7;
        if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa) {
            foreach (($_SESSION['sigi_permisos_usuario'] ?? []) as $permiso) {
                if ($permiso['id_sistema'] == $moduloBolsa) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = (int)$permiso['id_rol'];
                    break;
                }
            }
            if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa && isset($_SESSION['sigi_user_id'])) {
                $db = (new Model())->getDB();
                $idUser = (int)$_SESSION['sigi_user_id'];
                $rolUser = (int)$db->query("SELECT id_rol FROM sigi_usuarios WHERE id = $idUser")->fetchColumn();
                if (in_array($rolUser, [1, 7, 8])) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = $rolUser;
                }
            }
        }

        // 2. Validar autenticación
        if (!Auth::user() || !Auth::tieneRolEnBolsa()) {
            $_SESSION['flash_error'] = "Debe iniciar sesión para acceder al módulo de Bolsa de Trabajo.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // 3. Acceso exclusivo para rol de Administrador
        if (!Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso restringido: El panel de reportes está reservado para el Administrador de Bolsa Laboral.";
            header('Location: ' . BASE_URL . '/bolsa');
            exit;
        }

        require_once __DIR__ . '/../../models/Bolsa/Reportes.php';
        require_once __DIR__ . '/../../models/Bolsa/Ofertas.php';
        require_once __DIR__ . '/../../models/Bolsa/Empresas.php';
        require_once __DIR__ . '/../../models/Bolsa/Postulaciones.php';

        $this->reportesModel = new Reportes();
        $this->ofertasModel  = new Ofertas();
        $this->empresasModel = new Empresas();
    }

    /**
     * Panel principal de reportes para el Administrador
     */
    public function index()
    {
        $db = (new Model())->getDB();

        // 1. Catálogos maestros para los filtros
        $ofertas = $db->query("SELECT id, titulo FROM bolsa_ofertas_laborales ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $empresas = $this->empresasModel->listar();
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $estados = Postulaciones::getEstados();

        // 2. Parámetros de filtrado
        $tipoReporte = !empty($_GET['tipo']) ? trim($_GET['tipo']) : 'postulaciones';
        $filtros = [
            'id_oferta'    => !empty($_GET['id_oferta']) ? (int)$_GET['id_oferta'] : null,
            'id_empresa'   => !empty($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null,
            'id_programa'  => !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null,
            'estado'       => (isset($_GET['estado']) && $_GET['estado'] !== '') ? (int)$_GET['estado'] : null,
            'fecha_inicio' => !empty($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : null,
            'fecha_fin'    => !empty($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : null,
            'busqueda'     => !empty($_GET['busqueda']) ? trim($_GET['busqueda']) : null
        ];

        // 3. Ejecutar consulta según el tipo de reporte solicitado
        $postulaciones = [];
        $metricas = [];
        $ofertasData = [];
        $empresasData = [];

        if ($tipoReporte === 'postulaciones') {
            $postulaciones = $this->reportesModel->obtenerPostulaciones($filtros);
            $metricas      = $this->reportesModel->obtenerMetricasPostulaciones($filtros);
        } elseif ($tipoReporte === 'ofertas') {
            $ofertasData   = $this->reportesModel->obtenerOfertas($filtros);
        } elseif ($tipoReporte === 'empresas') {
            $empresasData  = $this->reportesModel->obtenerEmpresas($filtros);
        }

        $this->view('bolsa/reportes/index', [
            'module'        => 'bolsa',
            'pageTitle'     => 'Reportes Oficiales de Bolsa Laboral',
            'tipoReporte'   => $tipoReporte,
            'filtros'       => $filtros,
            'ofertas'       => $ofertas,
            'empresas'      => $empresas,
            'programas'     => $programas,
            'estados'       => $estados,
            'postulaciones' => $postulaciones,
            'metricas'      => $metricas,
            'ofertasData'   => $ofertasData,
            'empresasData'  => $empresasData
        ]);
        exit;
    }

    /**
     * Generación del Reporte Consolidado de Postulaciones en formato PDF
     */
    public function pdf()
    {
        $this->pdfPostulaciones();
    }

    public function pdfPostulaciones()
    {
        $filtros = [
            'id_oferta'    => !empty($_GET['id_oferta']) ? (int)$_GET['id_oferta'] : null,
            'id_empresa'   => !empty($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null,
            'id_programa'  => !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null,
            'estado'       => (isset($_GET['estado']) && $_GET['estado'] !== '') ? (int)$_GET['estado'] : null,
            'fecha_inicio' => !empty($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : null,
            'fecha_fin'    => !empty($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : null,
            'busqueda'     => !empty($_GET['busqueda']) ? trim($_GET['busqueda']) : null
        ];

        $postulaciones = $this->reportesModel->obtenerPostulaciones($filtros);
        $metricas      = $this->reportesModel->obtenerMetricasPostulaciones($filtros);
        $metadatos     = $this->reportesModel->obtenerMetadatosFiltros($filtros);

        require_once __DIR__ . '/../../../vendor/autoload.php';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Bolsa de Trabajo');
        $pdf->SetAuthor('Coordinación de Bolsa de Trabajo - IESTP Huanta');
        $pdf->SetTitle('Reporte de Postulaciones Laborales');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/bolsa/reportes/pdf_postulaciones.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Reporte_Postulaciones_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    /**
     * Generación del Reporte Consolidado de Ofertas Laborales en formato PDF
     */
    public function pdfOfertas()
    {
        $filtros = [
            'id_empresa'   => !empty($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null,
            'id_programa'  => !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null,
            'estado'       => (isset($_GET['estado']) && $_GET['estado'] !== '') ? (int)$_GET['estado'] : null,
            'fecha_inicio' => !empty($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : null,
            'fecha_fin'    => !empty($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : null
        ];

        $ofertas   = $this->reportesModel->obtenerOfertas($filtros);
        $metadatos = $this->reportesModel->obtenerMetadatosFiltros($filtros);

        require_once __DIR__ . '/../../../vendor/autoload.php';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Bolsa de Trabajo');
        $pdf->SetAuthor('Coordinación de Bolsa de Trabajo - IESTP Huanta');
        $pdf->SetTitle('Reporte de Convocatorias Laborales');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/bolsa/reportes/pdf_ofertas.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Reporte_Convocatorias_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    /**
     * Generación del Reporte del Directorio de Empresas en formato PDF
     */
    public function pdfEmpresas()
    {
        $filtros = [
            'estado' => (isset($_GET['estado']) && $_GET['estado'] !== '') ? (int)$_GET['estado'] : null
        ];

        $empresas  = $this->reportesModel->obtenerEmpresas($filtros);
        $metadatos = $this->reportesModel->obtenerMetadatosFiltros($filtros);

        require_once __DIR__ . '/../../../vendor/autoload.php';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Bolsa de Trabajo');
        $pdf->SetAuthor('Coordinación de Bolsa de Trabajo - IESTP Huanta');
        $pdf->SetTitle('Directorio de Empresas Aliadas');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/bolsa/reportes/pdf_empresas.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Directorio_Empresas_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    /**
     * Generación del Expediente en PDF de postulantes para una Convocatoria Laboral específica
     */
    public function pdfOferta($idOferta)
    {
        $idOferta = (int)$idOferta;
        $detalle = $this->reportesModel->obtenerDetalleOfertaConPostulantes($idOferta);

        if (!$detalle) {
            $_SESSION['flash_error'] = "Convocatoria laboral no encontrada.";
            header('Location: ' . BASE_URL . '/bolsa/reportes');
            exit;
        }

        $oferta      = $detalle['oferta'];
        $postulantes = $detalle['postulantes'];

        require_once __DIR__ . '/../../../vendor/autoload.php';

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Bolsa de Trabajo');
        $pdf->SetAuthor('Coordinación de Bolsa de Trabajo - IESTP Huanta');
        $pdf->SetTitle('Expediente de Candidatos - ' . $oferta['titulo']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/bolsa/reportes/pdf_oferta_detalle.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Expediente_Candidatos_Oferta_' . $idOferta . '.pdf', 'I');
        exit;
    }
}
