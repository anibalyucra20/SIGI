<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;

use App\Models\Tutoria\Programacion;
use App\Models\Tutoria\SesionIndividual;
use App\Models\Tutoria\SesionGrupal;
use App\Models\Tutoria\EstudianteTutoria;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PDO;

class ReportesController extends Controller
{
    protected $objProgramacion;
    protected $objSesionIndividual;
    protected $objSesionGrupal;
    protected $objEstudianteTutoria;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para ver reportes de tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->objProgramacion      = new Programacion();
        $this->objSesionIndividual  = new SesionIndividual();
        $this->objSesionGrupal      = new SesionGrupal();
        $this->objEstudianteTutoria = new EstudianteTutoria();
    }

    public function index()
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        }

        // Obtener todas las programaciones para consolidar
        $tutores = $this->objProgramacion->getPaginated([
            'id_sede'              => $id_sede,
            'id_periodo_academico' => $id_periodo,
            'id_docente'           => $id_docente
        ], 100, 0, 3, 'ASC')['data'] ?? [];

        // Lista de derivaciones a psicología/servicios
        $derivaciones = $this->objSesionIndividual->listarDerivaciones($id_periodo, $id_sede, $id_docente);

        // Métricas generales
        $totalTutores      = count($tutores);
        $totalTutorandos    = $this->objProgramacion->contarTutorandosTotales($id_periodo, $id_sede);
        $totalSesionesInd  = $this->objSesionIndividual->contarPorPeriodoSede($id_periodo, $id_sede, $id_docente);
        $totalSesionesGrup = $this->objSesionGrupal->contarPorPeriodoSede($id_periodo, $id_sede, $id_docente);

        $this->view('tutoria/reportes/index', [
            'tutores'           => $tutores,
            'derivaciones'      => $derivaciones,
            'totalTutores'      => $totalTutores,
            'totalTutorandos'   => $totalTutorandos,
            'totalSesionesInd'  => $totalSesionesInd,
            'totalSesionesGrup' => $totalSesionesGrup,
            'esAdmin'           => $esAdmin,
            'module'            => 'tutoria',
            'pageTitle'         => 'Reportes y Estadísticas de Tutoría'
        ]);
        exit;
    }

    /* =========================================================================
     * EXPORTACIONES EN FORMATO EXCEL (.XLSX)
     * ========================================================================= */

    /**
     * Genera y descarga el reporte de sesiones individuales en archivo Excel (.xlsx).
     */
    public function exportarIndividualesExcel()
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'    => $id_sede,
            'id_periodo' => $id_periodo,
            'id_docente' => $id_docente
        ];

        $sesiones = $this->objSesionIndividual->listarParaReporte($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tutorías Individuales');

        // Encabezado institucional
        $sheet->setCellValue('A1', 'INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO "HUANTA"');
        $sheet->mergeCells('A1:S1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'UNIDAD DE BIENESTAR Y EMPLEABILIDAD - REPORTE DE TUTORÍAS INDIVIDUALES');
        $sheet->mergeCells('A2:S2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0284C7'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $periodoNombre = !empty($sesiones[0]['periodo_nombre']) ? $sesiones[0]['periodo_nombre'] : 'ACTIVO';
        $sedeNombre    = !empty($sesiones[0]['sede_nombre']) ? $sesiones[0]['sede_nombre'] : 'HUANTA';
        $sheet->setCellValue('A3', "Periodo Lectivo: $periodoNombre  |  Sede: $sedeNombre  |  Fecha de Emisión: " . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A3:S3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Cabeceras de columnas
        $cabeceras = [
            'A5' => 'N°',
            'B5' => 'N° Sesión',
            'C5' => 'Fecha',
            'D5' => 'Hora',
            'E5' => 'Docente Tutor',
            'F5' => 'DNI Tutor',
            'G5' => 'DNI Estudiante',
            'H5' => 'Estudiante (Apellidos y Nombres)',
            'I5' => 'Programa de Estudios',
            'J5' => 'Semestre',
            'K5' => 'Solicitante',
            'L5' => 'Motivo de Atención',
            'M5' => 'Asistencia',
            'N5' => 'Aspectos y Temas Dialogados',
            'O5' => 'Recomendaciones del Tutor',
            'P5' => 'Compromiso del Estudiante',
            'Q5' => 'Seguimiento',
            'R5' => 'Próxima Sesión',
            'S5' => 'Derivación / Servicio'
        ];

        foreach ($cabeceras as $cell => $titulo) {
            $sheet->setCellValue($cell, $titulo);
        }

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ];
        $sheet->getStyle('A5:S5')->applyFromArray($styleHeader);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Llenado de filas
        $fila = 6;
        $i = 1;
        foreach ($sesiones as $s) {
            $fecha = !empty($s['fecha_hora']) ? date('d/m/Y', strtotime($s['fecha_hora'])) : '';
            $hora  = !empty($s['fecha_hora']) ? date('h:i A', strtotime($s['fecha_hora'])) : '';
            $asist = ($s['asistencia'] == 1) ? 'ASISTIÓ' : 'INASISTENCIA';
            $seg   = ($s['seguimiento'] == 1) ? 'SÍ' : 'NO';
            $prox  = ($s['seguimiento'] == 1 && !empty($s['fecha_prox_sesion']) && $s['fecha_prox_sesion'] !== '2000-01-01 00:00:00')
                ? date('d/m/Y', strtotime($s['fecha_prox_sesion'])) : '-';
            $der   = ($s['derivacion'] == 1) ? 'DERIVADO: ' . ($s['servicio_derivacion'] ?: 'Especialista') : 'NO DERIVADO';

            $sheet->setCellValue("A$fila", $i++);
            $sheet->setCellValue("B$fila", $s['nro']);
            $sheet->setCellValue("C$fila", $fecha);
            $sheet->setCellValue("D$fila", $hora);
            $sheet->setCellValue("E$fila", $s['docente_formato']);
            $sheet->setCellValue("F$fila", $s['docente_dni']);
            $sheet->setCellValue("G$fila", $s['estudiante_dni']);
            $sheet->setCellValue("H$fila", $s['estudiante_formato']);
            $sheet->setCellValue("I$fila", $s['programa_nombre'] ?: 'General');
            $sheet->setCellValue("J$fila", $s['semestre_nombre'] ?: '-');
            $sheet->setCellValue("K$fila", $s['solicitante']);
            $sheet->setCellValue("L$fila", $s['motivo']);
            $sheet->setCellValue("M$fila", $asist);
            $sheet->setCellValue("N$fila", $s['contenidos_tratados']);
            $sheet->setCellValue("O$fila", $s['recomendaciones']);
            $sheet->setCellValue("P$fila", $s['compromiso_estudiante']);
            $sheet->setCellValue("Q$fila", $seg);
            $sheet->setCellValue("R$fila", $prox);
            $sheet->setCellValue("S$fila", $der);

            $sheet->getStyle("A$fila:S$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet->getStyle("A$fila:D$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F$fila:G$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J$fila:K$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("Q$fila:R$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fila++;
        }

        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tutorias_Individuales_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Genera y descarga el reporte de sesiones grupales en archivo Excel (.xlsx).
     */
    public function exportarGrupalesExcel()
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'    => $id_sede,
            'id_periodo' => $id_periodo,
            'id_docente' => $id_docente
        ];

        $sesiones = $this->objSesionGrupal->listarParaReporte($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tutorías Grupales');

        // Encabezado institucional
        $sheet->setCellValue('A1', 'INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO "HUANTA"');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'UNIDAD DE BIENESTAR Y EMPLEABILIDAD - REPORTE DE SESIONES Y TALLERES GRUPALES');
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0E7490'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $periodoNombre = !empty($sesiones[0]['periodo_nombre']) ? $sesiones[0]['periodo_nombre'] : 'ACTIVO';
        $sedeNombre    = !empty($sesiones[0]['sede_nombre']) ? $sesiones[0]['sede_nombre'] : 'HUANTA';
        $sheet->setCellValue('A3', "Periodo Lectivo: $periodoNombre  |  Sede: $sedeNombre  |  Fecha de Emisión: " . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A3:L3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Cabeceras de columnas
        $cabeceras = [
            'A5' => 'N°',
            'B5' => 'Fecha',
            'C5' => 'Hora',
            'D5' => 'Docente Tutor',
            'E5' => 'DNI Tutor',
            'F5' => 'Tipo de Sesión',
            'G5' => 'Objetivo de la Sesión',
            'H5' => 'Contenidos Desarrollados',
            'I5' => 'Nivel de Logro',
            'J5' => 'Total Asistentes',
            'K5' => 'Observaciones del Tutor',
            'L5' => 'Recomendaciones'
        ];

        foreach ($cabeceras as $cell => $titulo) {
            $sheet->setCellValue($cell, $titulo);
        }

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0E7490']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($styleHeader);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Llenado de filas
        $fila = 6;
        $i = 1;
        foreach ($sesiones as $s) {
            $fecha = !empty($s['fecha_hora']) ? date('d/m/Y', strtotime($s['fecha_hora'])) : '';
            $hora  = !empty($s['fecha_hora']) ? date('h:i A', strtotime($s['fecha_hora'])) : '';

            $sheet->setCellValue("A$fila", $i++);
            $sheet->setCellValue("B$fila", $fecha);
            $sheet->setCellValue("C$fila", $hora);
            $sheet->setCellValue("D$fila", $s['docente_formato']);
            $sheet->setCellValue("E$fila", $s['docente_dni']);
            $sheet->setCellValue("F$fila", $s['tipo']);
            $sheet->setCellValue("G$fila", $s['objetivo_sesion']);
            $sheet->setCellValue("H$fila", $s['contenidos']);
            $sheet->setCellValue("I$fila", $s['logro_objetivo']);
            $sheet->setCellValue("J$fila", $s['total_asistentes']);
            $sheet->setCellValue("K$fila", $s['observaciones_sesion']);
            $sheet->setCellValue("L$fila", $s['recomendaciones']);

            $sheet->getStyle("A$fila:L$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet->getStyle("A$fila:C$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E$fila:F$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I$fila:J$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fila++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tutorias_Grupales_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Genera y descarga el reporte consolidado multi-hoja en archivo Excel (.xlsx).
     */
    public function exportarConsolidadoExcel()
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'              => $id_sede,
            'id_periodo'           => $id_periodo,
            'id_periodo_academico' => $id_periodo,
            'id_docente'           => $id_docente
        ];

        // 1. Datos para Hoja 1: Balance por Docente Tutor
        $tutores = $this->objProgramacion->getPaginated($filters, 200, 0, 3, 'ASC')['data'] ?? [];

        // 2. Datos para Hoja 2: Sesiones Individuales
        $sesionesInd = $this->objSesionIndividual->listarParaReporte($filters);

        // 3. Datos para Hoja 3: Sesiones Grupales
        $sesionesGrup = $this->objSesionGrupal->listarParaReporte($filters);

        // 4. Datos para Hoja 4: Casos Derivados
        $derivaciones = $this->objSesionIndividual->listarDerivaciones($id_periodo, $id_sede, $id_docente);

        $spreadsheet = new Spreadsheet();

        // -------------------------------------------------------------
        // HOJA 1: BALANCE POR TUTOR
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Balance por Tutor');

        $sheet1->setCellValue('A1', 'INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO "HUANTA"');
        $sheet1->mergeCells('A1:G1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->setCellValue('A2', 'INFORME CONSOLIDADO - CUMPLIMIENTO POR DOCENTE TUTOR');
        $sheet1->mergeCells('A2:G2');
        $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->setCellValue('A3', "Fecha de Emisión: " . date('d/m/Y H:i:s'));
        $sheet1->mergeCells('A3:G3');
        $sheet1->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cabs1 = ['A5' => 'N°', 'B5' => 'Docente Tutor', 'C5' => 'DNI', 'D5' => 'Correo Institucional', 'E5' => 'Tutorandos Asignados', 'F5' => 'Sesiones Grupales', 'G5' => 'Conclusiones / Informe Final'];
        foreach ($cabs1 as $c => $t) {
            $sheet1->setCellValue($c, $t);
        }
        $sheet1->getStyle('A5:G5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ]);
        $sheet1->getRowDimension(5)->setRowHeight(25);

        $f = 6;
        $i = 1;
        foreach ($tutores as $t) {
            $sheet1->setCellValue("A$f", $i++);
            $sheet1->setCellValue("B$f", $t['docente_nombre']);
            $sheet1->setCellValue("C$f", $t['docente_dni']);
            $sheet1->setCellValue("D$f", $t['docente_correo'] ?: 'No registrado');
            $sheet1->setCellValue("E$f", $t['total_estudiantes']);
            $sheet1->setCellValue("F$f", $t['total_sesiones_grupales']);
            $sheet1->setCellValue("G$f", $t['conclusiones'] ?: 'Pendiente');

            $sheet1->getStyle("A$f:G$f")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet1->getStyle("A$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("C$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("E$f:F$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $f++;
        }
        foreach (range('A', 'G') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // -------------------------------------------------------------
        // HOJA 2: TUTORÍAS INDIVIDUALES
        // -------------------------------------------------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Tutorías Individuales');

        $sheet2->setCellValue('A1', 'DETALLE DE SESIONES INDIVIDUALES DE TUTORÍA');
        $sheet2->mergeCells('A1:P1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0284C7'));

        $cabs2 = [
            'A3' => 'N°', 'B3' => 'N° Sesión', 'C3' => 'Fecha', 'D3' => 'Docente Tutor', 'E3' => 'DNI Estudiante',
            'F3' => 'Estudiante', 'G3' => 'Programa', 'H3' => 'Semestre', 'I3' => 'Solicitante',
            'J3' => 'Motivo', 'K3' => 'Asistencia', 'L3' => 'Contenidos Tratados', 'M3' => 'Recomendaciones',
            'N3' => 'Compromisos', 'O3' => 'Seguimiento', 'P3' => 'Derivación'
        ];
        foreach ($cabs2 as $c => $t) {
            $sheet2->setCellValue($c, $t);
        }
        $sheet2->getStyle('A3:P3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ]);
        $sheet2->getRowDimension(3)->setRowHeight(24);

        $f = 4;
        $i = 1;
        foreach ($sesionesInd as $s) {
            $fecha = !empty($s['fecha_hora']) ? date('d/m/Y', strtotime($s['fecha_hora'])) : '';
            $asist = ($s['asistencia'] == 1) ? 'ASISTIÓ' : 'INASISTENCIA';
            $seg   = ($s['seguimiento'] == 1) ? 'SÍ' : 'NO';
            $der   = ($s['derivacion'] == 1) ? $s['servicio_derivacion'] : 'NO';

            $sheet2->setCellValue("A$f", $i++);
            $sheet2->setCellValue("B$f", $s['nro']);
            $sheet2->setCellValue("C$f", $fecha);
            $sheet2->setCellValue("D$f", $s['docente_formato']);
            $sheet2->setCellValue("E$f", $s['estudiante_dni']);
            $sheet2->setCellValue("F$f", $s['estudiante_formato']);
            $sheet2->setCellValue("G$f", $s['programa_nombre'] ?: 'General');
            $sheet2->setCellValue("H$f", $s['semestre_nombre'] ?: '-');
            $sheet2->setCellValue("I$f", $s['solicitante']);
            $sheet2->setCellValue("J$f", $s['motivo']);
            $sheet2->setCellValue("K$f", $asist);
            $sheet2->setCellValue("L$f", $s['contenidos_tratados']);
            $sheet2->setCellValue("M$f", $s['recomendaciones']);
            $sheet2->setCellValue("N$f", $s['compromiso_estudiante']);
            $sheet2->setCellValue("O$f", $seg);
            $sheet2->setCellValue("P$f", $der);

            $sheet2->getStyle("A$f:P$f")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet2->getStyle("A$f:C$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("E$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("H$f:I$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("K$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("O$f:P$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $f++;
        }
        foreach (range('A', 'P') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // -------------------------------------------------------------
        // HOJA 3: TUTORÍAS GRUPALES
        // -------------------------------------------------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Tutorías Grupales');

        $sheet3->setCellValue('A1', 'DETALLE DE SESIONES Y TALLERES GRUPALES');
        $sheet3->mergeCells('A1:J1');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0E7490'));

        $cabs3 = [
            'A3' => 'N°', 'B3' => 'Fecha', 'C3' => 'Docente Tutor', 'D3' => 'Tipo de Sesión',
            'E3' => 'Objetivo de la Sesión', 'F3' => 'Contenidos', 'G3' => 'Nivel de Logro',
            'H3' => 'Total Asistentes', 'I3' => 'Observaciones', 'J3' => 'Recomendaciones'
        ];
        foreach ($cabs3 as $c => $t) {
            $sheet3->setCellValue($c, $t);
        }
        $sheet3->getStyle('A3:J3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0E7490']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ]);
        $sheet3->getRowDimension(3)->setRowHeight(24);

        $f = 4;
        $i = 1;
        foreach ($sesionesGrup as $s) {
            $fecha = !empty($s['fecha_hora']) ? date('d/m/Y', strtotime($s['fecha_hora'])) : '';

            $sheet3->setCellValue("A$f", $i++);
            $sheet3->setCellValue("B$f", $fecha);
            $sheet3->setCellValue("C$f", $s['docente_formato']);
            $sheet3->setCellValue("D$f", $s['tipo']);
            $sheet3->setCellValue("E$f", $s['objetivo_sesion']);
            $sheet3->setCellValue("F$f", $s['contenidos']);
            $sheet3->setCellValue("G$f", $s['logro_objetivo']);
            $sheet3->setCellValue("H$f", $s['total_asistentes']);
            $sheet3->setCellValue("I$f", $s['observaciones_sesion']);
            $sheet3->setCellValue("J$f", $s['recomendaciones']);

            $sheet3->getStyle("A$f:J$f")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet3->getStyle("A$f:B$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("D$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet3->getStyle("G$f:H$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $f++;
        }
        foreach (range('A', 'J') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // -------------------------------------------------------------
        // HOJA 4: CASOS DERIVADOS
        // -------------------------------------------------------------
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Casos Derivados');

        $sheet4->setCellValue('A1', 'RELACIÓN DE ESTUDIANTES DERIVADOS A SERVICIOS INSTITUCIONALES');
        $sheet4->mergeCells('A1:G1');
        $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('D97706'));

        $cabs4 = ['A3' => 'N°', 'B3' => 'Fecha Atención', 'C3' => 'DNI Estudiante', 'D3' => 'Estudiante Derivado', 'E3' => 'Programa de Estudios', 'F3' => 'Docente Tutor', 'G3' => 'Servicio Institucional de Destino'];
        foreach ($cabs4 as $c => $t) {
            $sheet4->setCellValue($c, $t);
        }
        $sheet4->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ]);
        $sheet4->getRowDimension(3)->setRowHeight(24);

        $f = 4;
        $i = 1;
        foreach ($derivaciones as $d) {
            $fecha = !empty($d['fecha_hora']) ? date('d/m/Y', strtotime($d['fecha_hora'])) : '';

            $sheet4->setCellValue("A$f", $i++);
            $sheet4->setCellValue("B$f", $fecha);
            $sheet4->setCellValue("C$f", $d['estudiante_dni']);
            $sheet4->setCellValue("D$f", $d['estudiante_formato']);
            $sheet4->setCellValue("E$f", $d['programa_nombre'] ?: 'General');
            $sheet4->setCellValue("F$f", $d['docente_formato']);
            $sheet4->setCellValue("G$f", $d['servicio_derivacion'] ?: 'Servicio Especializado');

            $sheet4->getStyle("A$f:G$f")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $sheet4->getStyle("A$f:C$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet4->getStyle("G$f")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $f++;
        }
        foreach (range('A', 'G') as $col) {
            $sheet4->getColumnDimension($col)->setAutoSize(true);
        }

        // Dejar activa la primera hoja al abrir
        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Informe_Consolidado_Tutoria_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Genera el Reporte Oficial de Tutoría en formato PDF con TCPDF.
     * Consolida las fichas oficiales (Anexo 05 - Ficha Individual y Anexo 04 - Ficha Grupal)
     * reproduciendo fielmente la estructura del documento oficial de referencia.
     */
    public function exportarReportePdf()
    {
        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();

        $id_docente = null;
        if (!$esAdmin && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
        } elseif (!empty($_GET['id_docente'])) {
            $id_docente = (int)$_GET['id_docente'];
        }

        $filters = [
            'id_sede'    => $id_sede,
            'id_periodo' => $id_periodo,
            'id_docente' => $id_docente
        ];

        // Obtener última sesión individual y última sesión grupal para el reporte muestra o según filtro
        $sesionesInd  = $this->objSesionIndividual->listarParaReporte($filters);
        $sesionesGrup = $this->objSesionGrupal->listarParaReporte($filters);

        if (!defined('K_PATH_CACHE')) {
            define('K_PATH_CACHE', sys_get_temp_dir() . '/');
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('IESTP Huanta - Unidad de Bienestar y Empleabilidad');
        $pdf->SetTitle('Reporte Oficial de Tutoría - IESTP Huanta');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 10, 12);
        $pdf->SetAutoPageBreak(true, 8);

        $rutaLogo = __DIR__ . '/../../../public/img/logo.png';

        // PÁGINA 1: FICHA DE TUTORÍA INDIVIDUAL (ANEXO 05)
        $sesion = !empty($sesionesInd) ? $this->objSesionIndividual->find($sesionesInd[0]['id']) : [
            'nro' => 1,
            'docente_formato' => 'Docente Tutor Asignado',
            'fecha_hora' => date('Y-m-d H:i:s'),
            'estudiante_formato' => 'Estudiante Tutorando',
            'estudiante_genero' => 'M',
            'edad' => '20',
            'programa_nombre' => 'Desarrollo de Sistemas de Información',
            'semestre_nombre' => 'I',
            'periodo_nombre' => date('Y'),
            'solicitante' => 'TUTOR',
            'motivo' => 'Académico y seguimiento de adaptación institucional',
            'contenidos_tratados' => "1. Orientación sobre metodologías de estudio y organización del tiempo.\n2. Revisión de avance académico en unidades didácticas.",
            'recomendaciones' => "1. Establecer un horario de estudio personal de 2 horas diarias.\n2. Asistir puntualmente a las sesiones de reforzamiento programadas.",
            'compromiso_estudiante' => "El estudiante se compromete a cumplir con la entrega oportuna de tareas y participar activamente en clases.",
            'seguimiento' => 1,
            'fecha_prox_sesion' => date('Y-m-d H:i:s', strtotime('+15 days')),
            'derivacion' => 0,
            'servicio_derivacion' => ''
        ];

        $pdf->AddPage();
        ob_start();
        include __DIR__ . '/../../views/tutoria/tutores/sesionesIndividuales/pdf_ficha_individual.php';
        $html1 = ob_get_clean();
        $pdf->writeHTML($html1, true, false, true, false, '');

        // PÁGINA 2: FICHA DE TUTORÍA GRUPAL (ANEXO 04)
        $sesionGrupItem = !empty($sesionesGrup) ? $sesionesGrup[0] : null;
        if ($sesionGrupItem) {
            $sesion = $this->objSesionGrupal->find($sesionGrupItem['id']);
            $estudiantes = $this->objEstudianteTutoria->listarPorProgramacion($sesion['id_tutoria']);
            $asistentesIds = json_decode($sesion['asistentes'] ?? '[]', true) ?: [];
        } else {
            $sesion = [
                'id' => 1,
                'docente_formato' => 'Docente Tutor Asignado',
                'fecha_hora' => date('Y-m-d H:i:s'),
                'programa_nombre' => 'Desarrollo de Sistemas de Información',
                'periodo_nombre' => date('Y'),
                'objetivo_sesion' => 'Inducción, orientación institucional y reforzamiento académico integral.',
                'contenidos' => "1. Presentación del plan de tutoría y reglamentos académicos.\n2. Estrategias de trabajo en equipo y habilidades blandas.",
                'logro_objetivo' => 'Logrado',
                'observaciones_sesion' => 'Participación activa y colaborativa de los estudiantes convocados.',
                'recomendaciones' => 'Continuar con el acompañamiento en las semanas posteriores de evaluación.'
            ];
            $estudiantes = [];
            $asistentesIds = [];
        }

        $pdf->AddPage();
        ob_start();
        include __DIR__ . '/../../views/tutoria/tutores/sesionesGrupales/pdf_ficha_grupal.php';
        $html2 = ob_get_clean();
        $pdf->writeHTML($html2, true, false, true, false, '');

        $pdf->Output('Reporte_Oficial_Tutoria_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }
}
