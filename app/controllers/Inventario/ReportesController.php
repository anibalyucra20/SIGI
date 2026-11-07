<?php
namespace App\Controllers\Inventario;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../app/models/Inventario/Ambientes.php';
require_once __DIR__ . '/../../../app/models/Inventario/Reportes.php';
require_once __DIR__ . '/../../../app/models/Inventario/Bienes.php';
require_once __DIR__ . '/../../../app/models/Inventario/Movimientos.php';

use Core\Controller;
use App\Models\Inventario\Ambientes;
use App\Models\Inventario\Reportes;
use App\Models\Inventario\Bienes;
use App\Models\Inventario\Movimientos;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportesController extends Controller
{
    protected $modelReportes;
    protected $objAmbientes;
    protected $modelBienes;
    protected $modelMovimientos;

    public function __construct()
    {
        parent::__construct();
        $this->modelReportes = new Reportes();
        $this->objAmbientes = new Ambientes();
        $this->modelBienes = new Bienes();
        $this->modelMovimientos = new Movimientos();
    }

    public function index()
    {
        $ambientes = $this->objAmbientes->getAmbientes();

        $this->view('inventario/reportes/index', [
            'module'    => 'inventario',
            'pageTitle' => 'Módulo de Reportes Oficiales',
            'ambientes' => $ambientes
        ]);
        exit;
    }

    private function prepararFiltrosBienes()
    {
        $tipo_reporte = $_POST['tipo_reporte_bienes'] ?? 'general';
        $id_ambiente  = $_POST['id_ambiente'] ?? '';
        
        $filtros = [];
        if (!empty($id_ambiente)) {
            $filtros['id_inv_ambiente'] = $id_ambiente;
        }

        if ($tipo_reporte === 'activos') {
            $filtros['activo'] = '1';
        } elseif ($tipo_reporte === 'bajas') {
            $filtros['activo'] = '0';
            $fecha_desde = $_POST['fecha_desde'] ?? '';
            $fecha_hasta = $_POST['fecha_hasta'] ?? '';
            if (!empty($fecha_desde)) $filtros['fecha_desde'] = $fecha_desde;
            if (!empty($fecha_hasta)) $filtros['fecha_hasta'] = $fecha_hasta;
        }
        return $filtros;
    }

    // =========================================================================
    // 1. BIENES
    // =========================================================================
    public function generarPdfBienes()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $filtros = $this->prepararFiltrosBienes();
        $result = $this->modelBienes->getPaginated($filtros, 10000, 0, 1, 'asc');
        $datos = $result['data'] ?? [];

        $ambientesList = $this->objAmbientes->getAmbientes();
        $ambientesMap = [];
        foreach ($ambientesList as $amb) {
            $ambientesMap[$amb['id_inventario']] = trim(($amb['tipo_ambiente'] ?? '') . (!empty($amb['nro']) ? ' - ' . $amb['nro'] : ''));
        }

        $pdf = new \TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('SIGI');
        $pdf->SetTitle('Inventario de Bienes Patrimoniales');
        $pdf->SetMargins(10, 10, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage('L');

        $tipoStr = $_POST['tipo_reporte_bienes'] ?? 'general';
        $tituloReporte = "INVENTARIO GENERAL DE BIENES";
        if($tipoStr == 'activos') $tituloReporte = "REPORTE DE BIENES ACTIVOS";
        if($tipoStr == 'bajas') $tituloReporte = "REPORTE DE BIENES DADOS DE BAJA";

        $nombreAmbienteFiltro = 'REPORTE GENERAL';
        if (!empty($filtros['id_inv_ambiente']) && isset($ambientesMap[$filtros['id_inv_ambiente']])) {
            $nombreAmbienteFiltro = strtoupper($ambientesMap[$filtros['id_inv_ambiente']]);
        }

        $html = '
        <table width="100%" style="font-size: 8px; font-weight: bold;">
            <tr>
                <td width="30%">DIRECCION REGIONAL DE EDUCACION AYACUCHO</td>
                <td width="40%" style="text-align:center; font-size: 11px;">INVENTARIO DE BIENES PATRIMONIALES DEL INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLOGICO PUBLICO "HUANTA"</td>
                <td width="30%" style="text-align:right;">' . date('d/m/Y') . '</td>
            </tr>
        </table>
        <br>
        <table width="100%" style="font-size: 8px;">
            <tr>
                <td width="100%">
                    <b>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLOGICO "HUANTA"</b><br>
                    <b>AREA / AMBIENTE:</b> ' . $nombreAmbienteFiltro . '<br>
                    <b>TIPO DE VERIFICACION:</b> FISICA ( X ) DIGITAL (  )
                </td>
            </tr>
        </table>
        <br>';

        $html .= '
        <table border="1" cellpadding="3" cellspacing="0" style="font-size: 7px; text-align: center;">
            <tr style="background-color:#f2f2f2; font-weight:bold;">
                <th width="4%">N°<br>ORD</th>
                <th width="9%">CODIGO<br>PATRIMONIAL</th>
                <th width="17%">DENOMINACION</th>
                <th width="7%">MARCA</th>
                <th width="8%">MODELO</th>
                <th width="4%">TIPO</th>
                <th width="7%">COLOR</th>
                <th width="9%">SERIE</th>
                <th width="6%">DIMENSIONES</th>
                <th width="5%">VALOR</th>
                <th width="6%">SITUACION</th>
                <th width="8%">ESTADO<br>CONSERV.</th>
                <th width="10%">OBSERVACION</th>
            </tr>';
        
        $nro = 1;
        foreach ($datos as $row) {
            $html .= '<tr>
                <td>' . $nro++ . '</td>
                <td>' . $row['codigo_patrimonial'] . '</td>
                <td style="text-align:left;">' . $row['denominacion'] . '</td>
                <td>' . $row['marca'] . '</td>
                <td>' . $row['modelo'] . '</td>
                <td></td>
                <td>' . $row['color'] . '</td>
                <td>' . $row['serie'] . '</td>
                <td></td>
                <td></td>
                <td>U</td>
                <td>' . $row['estado_bien'] . '</td>
                <td style="text-align:left;">' . $row['observaciones'] . '</td>
            </tr>';
        }
        $html .= '</table>';

        $html .= '
        <br><br><br><br>
        <table width="100%" style="font-size: 8px; text-align: center;">
            <tr>
                <td width="25%">__________________________________<br><b>USUARIO/RESPONSABLE</b></td>
                <td width="25%">__________________________________<br><b>DIRECTOR GENERAL</b></td>
                <td width="25%">__________________________________<br><b>JEFE DE LA UNIDAD ADMINISTRATIVA</b></td>
                <td width="25%">__________________________________<br><b>CONTROL PATRIMONIAL</b></td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Inventario_Oficial_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    public function generarExcelBienes()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $filtros = $this->prepararFiltrosBienes();
        $result = $this->modelBienes->getPaginated($filtros, 10000, 0, 1, 'asc');
        $datos = $result['data'] ?? [];

        $ambientesList = $this->objAmbientes->getAmbientes();
        $ambientesMap = [];
        foreach ($ambientesList as $amb) {
            $ambientesMap[$amb['id_inventario']] = trim(($amb['tipo_ambiente'] ?? '') . (!empty($amb['nro']) ? ' - ' . $amb['nro'] : ''));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $tipoStr = $_POST['tipo_reporte_bienes'] ?? 'general';
        $tituloReporte = "Reporte de Bienes - " . strtoupper($tipoStr);

        $sheet->setCellValue('A1', $tituloReporte);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cabeceras = ['N°', 'Ambiente', 'Código Patrimonial', 'Denominación', 'Marca', 'Modelo', 'Color', 'Serie', 'Estado General'];
        $col = 'A';
        foreach ($cabeceras as $cab) {
            $sheet->setCellValue($col . '3', $cab);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $col++;
        }

        $fila = 4;
        $nro = 1;
        foreach ($datos as $row) {
            $nombreAmbiente = $ambientesMap[$row['id_inv_ambiente']] ?? 'GENERAL';
            $estadoStr = ($row['activo'] == 1 || is_null($row['activo'])) ? 'ACTIVO' : 'DADO DE BAJA';

            $sheet->setCellValue('A' . $fila, $nro++);
            $sheet->setCellValue('B' . $fila, $nombreAmbiente);
            $sheet->setCellValue('C' . $fila, $row['codigo_patrimonial']);
            $sheet->setCellValue('D' . $fila, $row['denominacion']);
            $sheet->setCellValue('E' . $fila, $row['marca']);
            $sheet->setCellValue('F' . $fila, $row['modelo']);
            $sheet->setCellValue('G' . $fila, $row['color']);
            $sheet->setCellValue('H' . $fila, $row['serie']);
            $sheet->setCellValue('I' . $fila, $estadoStr);
            $fila++;
        }

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Bienes_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

// =========================================================================
    // 2. AMBIENTES
    // =========================================================================
    public function generarPdfAmbientes()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $datos = $this->objAmbientes->getAmbientes();

        $pdf = new \TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('SIGI');
        $pdf->SetTitle('Listado de Ambientes Institucionales');
        $pdf->SetMargins(10, 10, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, 25); // Margen para las firmas
        $pdf->AddPage('L');

        // Membrete Oficial Institucional
        $html = '
        <table width="100%" style="font-size: 8px; font-weight: bold;">
            <tr>
                <td width="30%">DIRECCION REGIONAL DE EDUCACION AYACUCHO</td>
                <td width="40%" style="text-align:center; font-size: 10px;">LISTADO DE AMBIENTES INSTITUCIONALES DEL INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLOGICO PUBLICO "HUANTA"</td>
                <td width="30%" style="text-align:right;">' . date('d/m/Y') . '</td>
            </tr>
        </table>
        <br>
        <table width="100%" style="font-size: 8px;">
            <tr>
                <td width="100%">
                    <b>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLOGICO "HUANTA"</b><br>
                    <b>REPORTE:</b> LISTADO GENERAL DE AMBIENTES Y UBICACIONES<br>
                    <b>TIPO DE VERIFICACION:</b> FISICA ( X ) DIGITAL (  )
                </td>
            </tr>
        </table>
        <br>';

        // Tabla de Ambientes
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-size: 9px; text-align: center;">
            <tr style="background-color:#f2f2f2; font-weight:bold;">
                <th width="8%">N°</th>
                <th width="32%">TIPO DE AMBIENTE</th>
                <th width="15%">NÚMERO</th>
                <th width="15%">AFORO</th>
                <th width="15%">PISO</th>
                <th width="15%">UBICACIÓN</th>
            </tr>';

        $nro = 1;
        foreach ($datos as $row) {
            $html .= '<tr>
                <td>' . $nro++ . '</td>
                <td style="text-align:left;">' . ($row['tipo_ambiente'] ?? '') . '</td>
                <td>' . ($row['nro'] ?? '') . '</td>
                <td>' . ($row['aforo'] ?? '') . '</td>
                <td>' . ($row['piso'] ?? '') . '</td>
                <td>' . ($row['ubicacion'] ?? '') . '</td>
            </tr>';
        }
        $html .= '</table>';

        // Bloque de 4 Firmas Oficiales
        $html .= '
        <br><br><br><br>
        <table width="100%" style="font-size: 8px; text-align: center;">
            <tr>
                <td width="25%">__________________________________<br><b>USUARIO/RESPONSABLE</b></td>
                <td width="25%">__________________________________<br><b>DIRECTOR GENERAL</b></td>
                <td width="25%">__________________________________<br><b>JEFE DE LA UNIDAD ADMINISTRATIVA</b></td>
                <td width="25%">__________________________________<br><b>CONTROL PATRIMONIAL</b></td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Listado_Ambientes_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    public function generarExcelAmbientes()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $datos = $this->objAmbientes->getAmbientes();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezado institucional en el Excel
        $sheet->setCellValue('A1', 'INSTITUTO DE EDUCACIÓN SUPERIOR TECNÓLOGO PÚBLICO "HUANTA"');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'LISTADO DE AMBIENTES INSTITUCIONALES');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cabeceras = ['N°', 'Tipo de Ambiente', 'Número', 'Aforo', 'Piso', 'Ubicación'];
        $col = 'A';
        foreach ($cabeceras as $cab) {
            $sheet->setCellValue($col . '4', $cab);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $col++;
        }

        $fila = 5;
        $nro = 1;
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila, $nro++);
            $sheet->setCellValue('B' . $fila, $row['tipo_ambiente'] ?? '');
            $sheet->setCellValue('C' . $fila, $row['nro'] ?? '');
            $sheet->setCellValue('D' . $fila, $row['aforo'] ?? '');
            $sheet->setCellValue('E' . $fila, $row['piso'] ?? '');
            $sheet->setCellValue('F' . $fila, $row['ubicacion'] ?? '');

            // Aplicar bordes y alineaciones a la fila
            foreach (range('A', 'F') as $c) {
                $sheet->getStyle($c . $fila)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $fila . ':E' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fila++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Listado_Ambientes_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // 3. MOVIMIENTOS
    // =========================================================================
    public function generarPdfMovimientos()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $filters = [
            'id_sede' => $_SESSION['sigi_sede_actual'] ?? 0,
            'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
        ];
        $result = $this->modelMovimientos->getPaginated($filters, 10000, 0, 1, 'desc');
        $datos = $result['data'] ?? [];

        // Mapeo seguro de ambientes para traducir IDs a Nombres (ej. AULA)
        $ambientesList = $this->objAmbientes->getAmbientes();
        $ambientesMap = [];
        foreach ($ambientesList as $amb) {
            $nombreAmb = trim($amb['tipo_ambiente'] ?? '');
            if (!empty($amb['id_inventario'])) {
                $ambientesMap[$amb['id_inventario']] = $nombreAmb;
            }
            if (!empty($amb['id_ambiente'])) {
                $ambientesMap[$amb['id_ambiente']] = $nombreAmb;
            }
        }

        $pdf = new \TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('SIGI');
        $pdf->SetTitle('Historial de Movimientos de Bienes');
        $pdf->SetMargins(10, 10, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, 25); // Margen para las firmas
        $pdf->AddPage('L');

        // Membrete Oficial Institucional
        $html = '
        <table width="100%" style="font-size: 8px; font-weight: bold;">
            <tr>
                <td width="30%">DIRECCION REGIONAL DE EDUCACION AYACUCHO</td>
                <td width="40%" style="text-align:center; font-size: 11px;">HISTORIAL DE MOVIMIENTOS Y TRASLADOS DE BIENES PATRIMONIALES</td>
                <td width="30%" style="text-align:right;">' . date('d/m/Y') . '</td>
            </tr>
        </table>
        <br>
        <table width="100%" style="font-size: 8px;">
            <tr>
                <td width="100%">
                    <b>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLOGICO "HUANTA"</b><br>
                    <b>REPORTE:</b> HISTORIAL GENERAL DE TRASLADOS Y MOVIMIENTOS<br>
                    <b>TIPO DE VERIFICACION:</b> FISICA ( X ) DIGITAL (  )
                </td>
            </tr>
        </table>
        <br>';

        // Tabla de Movimientos con nombres de ambientes traducidos
        $html .= '
        <table border="1" cellpadding="4" cellspacing="0" style="font-size: 8px; text-align: center;">
            <tr style="background-color:#f2f2f2; font-weight:bold;">
                <th width="5%">N° ORD</th>
                <th width="20%">ORIGEN</th>
                <th width="20%">DESTINO</th>
                <th width="12%">TIPO</th>
                <th width="18%">MOTIVO</th>
                <th width="15%">DOCUMENTO</th>
                <th width="10%">FECHA Y HORA</th>
            </tr>';

        $nro = 1;
        foreach ($datos as $row) {
            // Traducir ID Origen a Nombre
            $idOrigen = $row['id_inv_ambiente_origen'] ?? null;
            $origenNombre = ($idOrigen && isset($ambientesMap[$idOrigen])) ? $ambientesMap[$idOrigen] : ($idOrigen ?: '-');

            // Traducir ID Destino a Nombre
            $idDestino = $row['id_inv_ambiente_destino'] ?? null;
            $destinoNombre = ($idDestino && isset($ambientesMap[$idDestino])) ? $ambientesMap[$idDestino] : ($idDestino ?: '-');

            $html .= '<tr>
                <td>' . $nro++ . '</td>
                <td>' . $origenNombre . '</td>
                <td>' . $destinoNombre . '</td>
                <td>' . ($row['tipo'] ?? '') . '</td>
                <td style="text-align:left;">' . ($row['motivo'] ?? '') . '</td>
                <td>' . ($row['documento_traslado'] ?? '') . '</td>
                <td>' . ($row['fecha_hora'] ?? '') . '</td>
            </tr>';
        }
        $html .= '</table>';

        // Bloque de 4 Firmas Oficiales
        $html .= '
        <br><br><br><br>
        <table width="100%" style="font-size: 8px; text-align: center;">
            <tr>
                <td width="25%">__________________________________<br><b>USUARIO/RESPONSABLE</b></td>
                <td width="25%">__________________________________<br><b>DIRECTOR GENERAL</b></td>
                <td width="25%">__________________________________<br><b>JEFE DE LA UNIDAD ADMINISTRATIVA</b></td>
                <td width="25%">__________________________________<br><b>CONTROL PATRIMONIAL</b></td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Historial_Movimientos_' . date('Ymd_His') . '.pdf', 'I');
        exit;
    }

    public function generarExcelMovimientos()
    {
        if (!\Core\Auth::esAdminInventario()) exit;

        $filters = [
            'id_sede' => $_SESSION['sigi_sede_actual'] ?? 0,
            'id_periodo_academico' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
        ];
        $result = $this->modelMovimientos->getPaginated($filters, 10000, 0, 1, 'desc');
        $datos = $result['data'] ?? [];

        // Mapeo seguro de ambientes para Excel
        $ambientesList = $this->objAmbientes->getAmbientes();
        $ambientesMap = [];
        foreach ($ambientesList as $amb) {
            $nombreAmb = trim($amb['tipo_ambiente'] ?? '');
            if (!empty($amb['id_inventario'])) {
                $ambientesMap[$amb['id_inventario']] = $nombreAmb;
            }
            if (!empty($amb['id_ambiente'])) {
                $ambientesMap[$amb['id_ambiente']] = $nombreAmb;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'HISTORIAL DE MOVIMIENTOS Y TRASLADOS DE BIENES');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cabeceras = ['N°', 'Origen', 'Destino', 'Tipo', 'Motivo', 'Documento de Traslado', 'Fecha y Hora'];
        $col = 'A';
        foreach ($cabeceras as $cab) {
            $sheet->setCellValue($col . '3', $cab);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $col++;
        }

        $fila = 4;
        $nro = 1;
        foreach ($datos as $row) {
            $idOrigen = $row['id_inv_ambiente_origen'] ?? null;
            $origenNombre = ($idOrigen && isset($ambientesMap[$idOrigen])) ? $ambientesMap[$idOrigen] : ($idOrigen ?: '-');

            $idDestino = $row['id_inv_ambiente_destino'] ?? null;
            $destinoNombre = ($idDestino && isset($ambientesMap[$idDestino])) ? $ambientesMap[$idDestino] : ($idDestino ?: '-');

            $sheet->setCellValue('A' . $fila, $nro++);
            $sheet->setCellValue('B' . $fila, $origenNombre);
            $sheet->setCellValue('C' . $fila, $destinoNombre);
            $sheet->setCellValue('D' . $fila, $row['tipo'] ?? '');
            $sheet->setCellValue('E' . $fila, $row['motivo'] ?? '');
            $sheet->setCellValue('F' . $fila, $row['documento_traslado'] ?? '');
            $sheet->setCellValue('G' . $fila, $row['fecha_hora'] ?? '');
            $fila++;
        }

        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Historial_Movimientos_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}