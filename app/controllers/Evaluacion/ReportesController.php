<?php

namespace App\Controllers\Evaluacion;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Evaluacion\Reporte;
use App\Models\Evaluacion\Encuesta;
use TCPDF;
use PDO;

class ReportesController extends Controller
{
    protected $reporteModel;
    protected $encuestaModel;

    public function __construct()
    {
        parent::__construct();

        // Acceso permitido para Administrador, Director, JUA y Coordinador
        if (!Auth::esAdminEvaluacion() && !Auth::esCoordinadorPEEvaluacion()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a los reportes de evaluación docente.";
            header('Location: ' . BASE_URL . '/evaluacion');
            exit;
        }

        $this->reporteModel = new Reporte();
        $this->encuestaModel = new Encuesta();
    }

    public function index()
    {
        $idUser     = $_SESSION['sigi_user_id'];
        $db         = (new Model())->getDB();

        // El periodo se toma estrictamente del selector de contexto del menú superior
        $idPeriodo  = $_SESSION['sigi_periodo_actual_id'] ?? null;
        if (!$idPeriodo) {
            $idPeriodo = (int)$db->query("SELECT id FROM sigi_periodo_academico ORDER BY fecha_inicio DESC LIMIT 1")->fetchColumn();
        }

        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . (int)$idPeriodo)->fetchColumn() ?: 'Periodo Actual';

        $idPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;
        $idPlan     = !empty($_GET['id_plan']) ? (int)$_GET['id_plan'] : null;
        $idModulo   = !empty($_GET['id_modulo']) ? (int)$_GET['id_modulo'] : null;
        $idSemestre = !empty($_GET['id_semestre']) ? (int)$_GET['id_semestre'] : null;

        $puedeVerTodos = Auth::puedeVerTodosLosProgramasEvaluacion();

        // Roles: Director (2), JUA (4), Admin (1), Sec. Académico (3) ven todos los programas
        // Coordinador / Jefe de área (5) solo ve las carreras que tiene asignadas
        if ($puedeVerTodos) {
            $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtProg = $db->prepare("
                SELECT p.id, p.nombre, p.codigo 
                FROM sigi_programa_estudios p 
                INNER JOIN sigi_coordinador_pe_periodo c ON c.id_programa_estudio = p.id 
                WHERE c.id_usuario = ? AND c.id_periodo = ? 
                ORDER BY p.nombre ASC
            ");
            $stmtProg->execute([$idUser, $idPeriodo]);
            $programas = $stmtProg->fetchAll(PDO::FETCH_ASSOC);

            $idsPermitidos = array_column($programas, 'id');
            if ($idPrograma && !in_array($idPrograma, $idsPermitidos)) {
                $idPrograma = !empty($idsPermitidos) ? $idsPermitidos[0] : null;
            } elseif (!$idPrograma && !empty($idsPermitidos)) {
                $idPrograma = $idsPermitidos[0];
            }
        }

        // Cargar listas dependientes si hay selección previa para mantener el estado del formulario
        $planes = [];
        $modulos = [];
        $semestres = [];

        if ($idPrograma) {
            $stmtPlanes = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre DESC");
            $stmtPlanes->execute([$idPrograma]);
            $planes = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($idPlan) {
            $stmtMod = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
            $stmtMod->execute([$idPlan]);
            $modulos = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($idModulo) {
            $stmtSem = $db->prepare("SELECT id, descripcion FROM sigi_semestre WHERE id_modulo_formativo = ? ORDER BY id ASC");
            $stmtSem->execute([$idModulo]);
            $semestres = $stmtSem->fetchAll(PDO::FETCH_ASSOC);
        }

        $docentes = $this->reporteModel->obtenerResumenPorDocente($idPeriodo, $idPrograma, null, $idPlan, $idModulo, $idSemestre);
        $unidades = $this->reporteModel->obtenerResumenPorUnidadDidactica($idPeriodo, $idPrograma, $idPlan, $idModulo, $idSemestre);

        $this->view('evaluacion/reportes/index', [
            'pageTitle'           => 'Reportes y Resultados de Evaluación Docente',
            'module'              => 'evaluacion',
            'docentes'            => $docentes,
            'unidades'            => $unidades,
            'periodoActualNombre' => $periodoNombre,
            'filtroPeriodo'       => $idPeriodo,
            'programas'           => $programas,
            'planes'              => $planes,
            'modulos'             => $modulos,
            'semestres'           => $semestres,
            'filtroPrograma'      => $idPrograma,
            'filtroPlan'          => $idPlan,
            'filtroModulo'        => $idModulo,
            'filtroSemestre'      => $idSemestre,
            'puedeVerTodos'       => $puedeVerTodos
        ]);
    }

    /**
     * AJAX: Obtiene los planes de estudio de un programa
     */
    public function ajaxPlanes($idPrograma)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre DESC");
        $stmt->execute([(int)$idPrograma]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * AJAX: Obtiene los módulos formativos de un plan de estudios
     */
    public function ajaxModulos($idPlan)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
        $stmt->execute([(int)$idPlan]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * AJAX: Obtiene los semestres de un módulo formativo
     */
    public function ajaxSemestres($idModulo)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion FROM sigi_semestre WHERE id_modulo_formativo = ? ORDER BY id ASC");
        $stmt->execute([(int)$idModulo]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    public function docente($idDocente)
    {
        $idDocente  = (int)$idDocente;
        $idPeriodo  = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : ($_SESSION['sigi_periodo_actual_id'] ?? null);
        $idPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        $detalle = $this->reporteModel->obtenerDetalleDocente($idDocente, $idPeriodo, $idPrograma);

        if (!$detalle) {
            $_SESSION['flash_error'] = "Docente no encontrado o sin evaluaciones registradas.";
            header('Location: ' . BASE_URL . '/evaluacion/reportes');
            exit;
        }

        $db = (new Model())->getDB();
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo))->fetchColumn() ?: '';

        $this->view('evaluacion/reportes/detalle_docente', [
            'pageTitle'     => 'Ficha de Evaluación: ' . $detalle['docente']['docente_nombre'],
            'module'        => 'evaluacion',
            'detalle'       => $detalle,
            'periodoNombre' => $periodoNombre,
            'idPeriodo'     => $idPeriodo
        ]);
    }

    /**
     * Genera PDF oficial individual de evaluación del docente
     */
    public function pdfDocente($idDocente)
    {
        $idDocente = (int)$idDocente;
        $idPeriodo = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : ($_SESSION['sigi_periodo_actual_id'] ?? null);

        $detalle = $this->reporteModel->obtenerDetalleDocente($idDocente, $idPeriodo);
        if (!$detalle) {
            die("Datos no encontrados.");
        }

        $db = (new Model())->getDB();
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo))->fetchColumn() ?: '';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema de Evaluación Docente');
        $pdf->SetAuthor('Coordinación Académica');
        $pdf->SetTitle('Reporte de Evaluación - ' . $detalle['docente']['docente_nombre']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Encabezado
        $html = "
            <table width='100%' cellpadding='3' style='border-bottom: 2px solid #003366;'>
                <tr>
                    <td width='75%'>
                        <h2 style='color:#003366; margin:0;'>INSTITUTO DE EDUCACIÓN SUPERIOR</h2>
                        <h4 style='color:#555; margin:0;'>REPORTE INDIVIDUAL DE EVALUACIÓN DE DESEMPEÑO DOCENTE</h4>
                        <span style='font-size:10px; color:#777;'>Periodo Académico: <strong>{$periodoNombre}</strong></span>
                    </td>
                    <td width='25%' align='right'>
                        <span style='font-size:9px; color:#666;'>Fecha: " . date('d/m/Y') . "</span>
                    </td>
                </tr>
            </table>
            <br><br>

            <table width='100%' cellpadding='5' style='background-color:#f8f9fa; border:1px solid #ddd; font-size:11px;'>
                <tr>
                    <td width='20%'><strong>Docente:</strong></td>
                    <td width='50%'>{$detalle['docente']['docente_nombre']}</td>
                    <td width='15%'><strong>DNI:</strong></td>
                    <td width='15%'>{$detalle['docente']['dni']}</td>
                </tr>
                <tr>
                    <td><strong>Participación:</strong></td>
                    <td>{$detalle['total_completados']} de {$detalle['total_asignados']} estudiantes ({$detalle['porcentaje_participacion']}%)</td>
                    <td><strong>Nivel:</strong></td>
                    <td><strong>{$detalle['nivel_desempeno']['etiqueta']}</strong></td>
                </tr>
                <tr>
                    <td><strong>Puntaje Escala 1-5:</strong></td>
                    <td style='font-size:14px; font-weight:bold; color:#003366;'>{$detalle['promedio_escala_5']} / 5.00</td>
                    <td><strong>Puntaje Vigesimal:</strong></td>
                    <td style='font-size:14px; font-weight:bold; color:#28a745;'>{$detalle['promedio_vigesimal']} / 20.00</td>
                </tr>
            </table>
            <br><br>

            <h4 style='color:#003366; border-bottom:1px solid #003366; padding-bottom:3px;'>1. RESULTADOS POR DIMENSIÓN / CRITERIO</h4>
            <table width='100%' cellpadding='5' border='1' style='border-collapse:collapse; font-size:10px;'>
                <tr style='background-color:#003366; color:#ffffff; font-weight:bold;'>
                    <th width='55%'>Dimensión / Criterio</th>
                    <th width='15%' align='center'>Peso</th>
                    <th width='15%' align='center'>Prom. (1-5)</th>
                    <th width='15%' align='center'>Prom. (0-20)</th>
                </tr>
        ";

        foreach ($detalle['criterios'] as $c) {
            $html .= "
                <tr>
                    <td>{$c['criterio_nombre']}</td>
                    <td align='center'>{$c['peso']}</td>
                    <td align='center'><strong>{$c['promedio_criterio']}</strong></td>
                    <td align='center'><strong>{$c['promedio_vigesimal']}</strong></td>
                </tr>
            ";
        }

        $html .= "
            </table>
            <br><br>

            <h4 style='color:#003366; border-bottom:1px solid #003366; padding-bottom:3px;'>2. EVALUACIÓN POR UNIDAD DIDÁCTICA</h4>
            <table width='100%' cellpadding='5' border='1' style='border-collapse:collapse; font-size:10px;'>
                <tr style='background-color:#f1f1f1; font-weight:bold;'>
                    <th width='50%'>Unidad Didáctica</th>
                    <th width='15%' align='center'>Semestre</th>
                    <th width='15%' align='center'>Evaluados</th>
                    <th width='20%' align='center'>Promedio (1-5)</th>
                </tr>
        ";

        foreach ($detalle['unidades'] as $u) {
            $html .= "
                <tr>
                    <td>{$u['unidad_nombre']}</td>
                    <td align='center'>Semestre {$u['semestre_romano']}</td>
                    <td align='center'>{$u['completados']} / {$u['asignados']}</td>
                    <td align='center'><strong>{$u['promedio_ud']}</strong></td>
                </tr>
            ";
        }

        $html .= "
            </table>
            <br><br>
        ";

        if (!empty($detalle['comentarios'])) {
            $html .= "
                <h4 style='color:#003366; border-bottom:1px solid #003366; padding-bottom:3px;'>3. COMENTARIOS Y SUGERENCIAS DE LOS ESTUDIANTES</h4>
                <ul style='font-size:9.5px; color:#444;'>
            ";
            foreach (array_slice($detalle['comentarios'], 0, 8) as $com) {
                $html .= "<li style='margin-bottom:5px;'><em>\"" . htmlspecialchars($com['comentario_general']) . "\"</em> <span style='color:#888;'>({$com['unidad_nombre']})</span></li>";
            }
            $html .= "</ul><br><br>";
        }

        $html .= "
            <br><br><br>
            <table width='100%' cellpadding='0'>
                <tr>
                    <td width='50%' align='center'>
                        ______________________________________<br>
                        <strong>Jefatura de Unidad Académica</strong><br>
                        Firma y Sello
                    </td>
                    <td width='50%' align='center'>
                        ______________________________________<br>
                        <strong>Coordinación de Carrera</strong><br>
                        Firma y Sello
                    </td>
                </tr>
            </table>
        ";

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Evaluacion_Docente_' . $detalle['docente']['dni'] . '.pdf', 'I');
        exit;
    }

    /**
     * Genera PDF consolidado de todos los docentes evaluados en el periodo
     */
    public function pdfConsolidado()
    {
        $idPeriodo  = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : ($_SESSION['sigi_periodo_actual_id'] ?? null);
        $idPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        $docentes = $this->reporteModel->obtenerResumenPorDocente($idPeriodo, $idPrograma);

        $db = (new Model())->getDB();
        $periodoNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$idPeriodo))->fetchColumn() ?: '';
        $programaNombre = $idPrograma ? $db->query("SELECT nombre FROM sigi_programa_estudios WHERE id = " . ((int)$idPrograma))->fetchColumn() : 'TODOS LOS PROGRAMAS DE ESTUDIOS';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI');
        $pdf->SetTitle('Reporte Consolidado de Evaluación Docente');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $html = "
            <table width='100%' cellpadding='3' style='border-bottom: 2px solid #003366;'>
                <tr>
                    <td width='75%'>
                        <h2 style='color:#003366; margin:0;'>REPORTE CONSOLIDADO DE EVALUACIÓN DOCENTE</h2>
                        <h4 style='color:#555; margin:0;'>{$programaNombre} - PERIODO ACADÉMICO {$periodoNombre}</h4>
                    </td>
                    <td width='25%' align='right'>
                        <span style='font-size:9px; color:#666;'>Fecha: " . date('d/m/Y') . "</span>
                    </td>
                </tr>
            </table>
            <br><br>

            <table width='100%' cellpadding='4' border='1' style='border-collapse:collapse; font-size:9px;'>
                <tr style='background-color:#003366; color:#ffffff; font-weight:bold;'>
                    <th width='5%' align='center'>N°</th>
                    <th width='10%' align='center'>DNI</th>
                    <th width='30%'>Docente</th>
                    <th width='20%'>Programa</th>
                    <th width='10%' align='center'>Participación</th>
                    <th width='12%' align='center'>Prom. (1-5)</th>
                    <th width='13%' align='center'>Nivel Desempeño</th>
                </tr>
        ";

        $i = 1;
        foreach ($docentes as $d) {
            $html .= "
                <tr>
                    <td align='center'>{$i}</td>
                    <td align='center'>{$d['docente_dni']}</td>
                    <td>{$d['docente_nombre']}</td>
                    <td>{$d['programa_codigo']} - {$d['programa_nombre']}</td>
                    <td align='center'>{$d['total_completados']} / {$d['total_asignados']} ({$d['porcentaje_participacion']}%)</td>
                    <td align='center'><strong>{$d['promedio_escala_5']}</strong></td>
                    <td align='center'>{$d['nivel_desempeno']['etiqueta']}</td>
                </tr>
            ";
            $i++;
        }

        $html .= "</table>";

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Consolidado_Evaluacion_Docente_' . date('Ymd') . '.pdf', 'I');
        exit;
    }
}
