<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\Constancia;
use App\Models\Efsrt\Documento;
use TCPDF;
use ZipArchive;
use PDO;

class ReportesController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $moduloEfsrt = 11;
        if (!isset($_SESSION['sigi_modulo_actual']) || $_SESSION['sigi_modulo_actual'] != $moduloEfsrt) {
            $_SESSION['sigi_modulo_actual'] = $moduloEfsrt;
            $encontrado = false;
            if (isset($_SESSION['sigi_permisos_usuario']) && is_array($_SESSION['sigi_permisos_usuario'])) {
                foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                    if ($permiso['id_sistema'] == $moduloEfsrt) {
                        $_SESSION['sigi_modulo_actual'] = $permiso['id_sistema'];
                        $_SESSION['sigi_rol_actual']    = $permiso['id_rol'];
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) {
                    foreach ($_SESSION['sigi_permisos_usuario'] as $permiso) {
                        if ($permiso['id_rol'] == 1) {
                            $_SESSION['sigi_rol_actual'] = 1;
                            $encontrado = true;
                            break;
                        } elseif ($permiso['id_rol'] == 7) {
                            $_SESSION['sigi_rol_actual'] = 7;
                            $encontrado = true;
                            break;
                        } elseif ($permiso['id_rol'] == 6) {
                            $_SESSION['sigi_rol_actual'] = 6;
                            $encontrado = true;
                            break;
                        }
                    }
                }
            }
        }

        if (!Auth::tieneRolEnEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder al módulo de EFSRT.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        // Acceso exclusivo para Secretaría Académica, Director, JUA y Administradores
        if (!Auth::esSecretarioPEEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esJUAEfsrt() && !Auth::esAdminEfsrt()) {
            $_SESSION['flash_error'] = "El panel de reportes de culminación está reservado para Secretaría Académica y Administradores.";
            header('Location: ' . BASE_URL . '/efsrt');
            exit;
        }
    }

    /**
     * Panel principal de reportes y descarga masiva
     */
    public function index()
    {
        $db = (new Model())->getDB();

        // Obtener listas maestras para filtros
        $programas = $db->query("SELECT id, codigo, nombre, tipo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $periodos  = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sedes     = $db->query("SELECT id, nombre FROM sigi_sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Filtros capturados por GET
        $filtroPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;
        $filtroPlan     = !empty($_GET['id_plan']) ? (int)$_GET['id_plan'] : null;
        $filtroModulo   = !empty($_GET['id_modulo']) ? (int)$_GET['id_modulo'] : null;
        $filtroPeriodo  = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : null;
        $filtroSede     = !empty($_GET['id_sede']) ? (int)$_GET['id_sede'] : null;

        // Cargar planes si se eligió programa
        $planes = [];
        if ($filtroPrograma) {
            $stmtPlanes = $db->prepare("SELECT id, nombre, resolucion FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY id DESC");
            $stmtPlanes->execute([$filtroPrograma]);
            $planes = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);
        }

        // Cargar módulos si se eligió plan
        $modulos = [];
        if ($filtroPlan) {
            $stmtMod = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
            $stmtMod->execute([$filtroPlan]);
            $modulos = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
        }

        // Obtener los expedientes que terminaron el proceso exitosamente
        $expedientes = $this->obtenerExpedientesCulminados([
            'id_programa' => $filtroPrograma,
            'id_plan'     => $filtroPlan,
            'id_modulo'   => $filtroModulo,
            'id_periodo'  => $filtroPeriodo,
            'id_sede'     => $filtroSede
        ]);

        $this->view('efsrt/reportes/index', [
            'module'          => 'efsrt',
            'pageTitle'       => 'Reporte de Expedientes Culminados EFSRT',
            'programas'       => $programas,
            'periodos'        => $periodos,
            'sedes'           => $sedes,
            'planes'          => $planes,
            'modulos'         => $modulos,
            'filtroPrograma'  => $filtroPrograma,
            'filtroPlan'      => $filtroPlan,
            'filtroModulo'    => $filtroModulo,
            'filtroPeriodo'   => $filtroPeriodo,
            'filtroSede'      => $filtroSede,
            'expedientes'     => $expedientes
        ]);
    }

    /**
     * Generación del Reporte Oficial en formato PDF
     */
    public function pdf()
    {
        $filtroPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : (!empty($_POST['id_programa']) ? (int)$_POST['id_programa'] : null);
        $filtroPlan     = !empty($_GET['id_plan']) ? (int)$_GET['id_plan'] : (!empty($_POST['id_plan']) ? (int)$_POST['id_plan'] : null);
        $filtroModulo   = !empty($_GET['id_modulo']) ? (int)$_GET['id_modulo'] : (!empty($_POST['id_modulo']) ? (int)$_POST['id_modulo'] : null);
        $filtroPeriodo  = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : (!empty($_POST['id_periodo']) ? (int)$_POST['id_periodo'] : null);
        $filtroSede     = !empty($_GET['id_sede']) ? (int)$_GET['id_sede'] : (!empty($_POST['id_sede']) ? (int)$_POST['id_sede'] : null);

        $expedientes = $this->obtenerExpedientesCulminados([
            'id_programa' => $filtroPrograma,
            'id_plan'     => $filtroPlan,
            'id_modulo'   => $filtroModulo,
            'id_periodo'  => $filtroPeriodo,
            'id_sede'     => $filtroSede
        ]);

        $metadatos = $this->obtenerMetadatosFiltro($filtroPrograma, $filtroPlan, $filtroModulo, $filtroPeriodo, $filtroSede);

        $codigoProg = !empty($metadatos['programa']['codigo']) ? $metadatos['programa']['codigo'] : 'EFSRT';
        $nroMod = !empty($metadatos['modulo']['nro_modulo']) ? 'M' . $metadatos['modulo']['nro_modulo'] : 'GENERAL';
        $nombrePdf = "Reporte_EFSRT_{$codigoProg}_{$nroMod}.pdf";

        $pdf = $this->generarPdfObjeto($expedientes, $metadatos);
        $pdf->Output($nombrePdf, 'I');
        exit;
    }

    /**
     * Descarga de paquete ZIP que contiene todas las constancias de empresa renombradas + el Reporte PDF
     */
    public function descargarPaqueteZip()
    {
        $filtroPrograma = !empty($_GET['id_programa']) ? (int)$_GET['id_programa'] : (!empty($_POST['id_programa']) ? (int)$_POST['id_programa'] : null);
        $filtroPlan     = !empty($_GET['id_plan']) ? (int)$_GET['id_plan'] : (!empty($_POST['id_plan']) ? (int)$_POST['id_plan'] : null);
        $filtroModulo   = !empty($_GET['id_modulo']) ? (int)$_GET['id_modulo'] : (!empty($_POST['id_modulo']) ? (int)$_POST['id_modulo'] : null);
        $filtroPeriodo  = !empty($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : (!empty($_POST['id_periodo']) ? (int)$_POST['id_periodo'] : null);
        $filtroSede     = !empty($_GET['id_sede']) ? (int)$_GET['id_sede'] : (!empty($_POST['id_sede']) ? (int)$_POST['id_sede'] : null);

        $expedientes = $this->obtenerExpedientesCulminados([
            'id_programa' => $filtroPrograma,
            'id_plan'     => $filtroPlan,
            'id_modulo'   => $filtroModulo,
            'id_periodo'  => $filtroPeriodo,
            'id_sede'     => $filtroSede
        ]);

        if (empty($expedientes)) {
            $_SESSION['flash_error'] = "No se encontraron expedientes culminados con los filtros seleccionados para empaquetar.";
            header('Location: ' . BASE_URL . '/efsrt/reportes?' . http_build_query($_GET));
            exit;
        }

        $metadatos = $this->obtenerMetadatosFiltro($filtroPrograma, $filtroPlan, $filtroModulo, $filtroPeriodo, $filtroSede);
        $codigoProg = !empty($metadatos['programa']['codigo']) ? $metadatos['programa']['codigo'] : 'EFSRT';
        $nroMod = !empty($metadatos['modulo']['nro_modulo']) ? 'M' . $metadatos['modulo']['nro_modulo'] : 'TODOS';

        // 1. Crear el PDF del reporte en disco temporal
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'efsrt_zip_' . uniqid();
        mkdir($tempDir, 0777, true);

        $nombrePdfReporte = "00_Reporte_Consolidado_{$codigoProg}_{$nroMod}.pdf";
        $rutaPdfReporte = $tempDir . DIRECTORY_SEPARATOR . $nombrePdfReporte;

        $pdf = $this->generarPdfObjeto($expedientes, $metadatos);
        $pdf->Output($rutaPdfReporte, 'F');

        // 2. Crear el archivo ZIP
        $zipNombre = "Constancias_EFSRT_{$codigoProg}_{$nroMod}_" . date('Ymd_His') . ".zip";
        $zipRuta = $tempDir . DIRECTORY_SEPARATOR . $zipNombre;

        $zip = new ZipArchive();
        if ($zip->open($zipRuta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $_SESSION['flash_error'] = "No se pudo inicializar la creación del archivo comprimido.";
            header('Location: ' . BASE_URL . '/efsrt/reportes?' . http_build_query($_GET));
            exit;
        }

        // Agregar el reporte consolidado
        if (file_exists($rutaPdfReporte)) {
            $zip->addFile($rutaPdfReporte, $nombrePdfReporte);
        }

        // Agregar cada constancia de empresa con su nombre estricto [DNI]-[CODIGO_CARRERA]-M[MODULO].pdf
        $archivosAgregados = 0;
        foreach ($expedientes as $exp) {
            $nombreConstancia = $exp['nombre_archivo_descarga'];
            $rutaRelativa = $exp['constancia_ruta_archivo'];

            if (!empty($rutaRelativa)) {
                $rutaFisica = __DIR__ . '/../../../public/' . $rutaRelativa;
                if (file_exists($rutaFisica)) {
                    $zip->addFile($rutaFisica, "Constancias/" . $nombreConstancia);
                    $archivosAgregados++;
                }
            }
        }

        $zip->close();

        if (!file_exists($zipRuta) || filesize($zipRuta) === 0) {
            $_SESSION['flash_error'] = "Ocurrió un error al compilar el archivo ZIP.";
            header('Location: ' . BASE_URL . '/efsrt/reportes?' . http_build_query($_GET));
            exit;
        }

        // Enviar descarga ZIP al cliente
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipNombre . '"');
        header('Content-Length: ' . filesize($zipRuta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($zipRuta);

        // Limpiar temporales
        @unlink($rutaPdfReporte);
        @unlink($zipRuta);
        @rmdir($tempDir);
        exit;
    }

    /**
     * Descarga directa individual de la constancia de empresa formateada
     */
    public function descargarConstancia($id_practica)
    {
        $id_practica = (int)$id_practica;
        $db = (new Model())->getDB();

        $stmt = $db->prepare("SELECT p.id, u.dni AS estudiante_dni, prg.codigo AS programa_codigo, modf.nro_modulo AS modulo_nro,
                                     doc_emp.ruta_archivo
                              FROM efsrt_practicas p
                              INNER JOIN sigi_usuarios u ON p.id_estudiante = u.id
                              INNER JOIN sigi_programa_estudios prg ON p.id_programa_estudios = prg.id
                              INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                              LEFT JOIN efsrt_documentos doc_emp ON (doc_emp.id_practica = p.id AND doc_emp.tipo_documento = 'CONSTANCIA_EMPRESA' AND doc_emp.estado_archivo != 'ELIMINADO_PURGADO')
                              WHERE p.id = ?");
        $stmt->execute([$id_practica]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['ruta_archivo'])) {
            $_SESSION['flash_error'] = "Constancia de empresa no encontrada en este expediente.";
            header('Location: ' . BASE_URL . '/efsrt/reportes');
            exit;
        }

        $nombreArchivo = Constancia::construirNombreArchivo($row['estudiante_dni'], $row['programa_codigo'], $row['modulo_nro']);
        $rutaFisica = __DIR__ . '/../../../public/' . $row['ruta_archivo'];

        if (!file_exists($rutaFisica)) {
            $_SESSION['flash_error'] = "El archivo físico de la constancia no fue encontrado en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/reportes');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($rutaFisica);
        exit;
    }

    /**
     * Helper para obtener los expedientes validados y culminados según filtros
     */
    private function obtenerExpedientesCulminados($filtros = [])
    {
        $db = (new Model())->getDB();

        $sql = "SELECT p.*,
                       est.dni AS estudiante_dni,
                       est.apellidos_nombres AS estudiante_nombre,
                       est.tipo_doc AS estudiante_tipo_doc,
                       est.correo AS estudiante_correo,
                       est.telefono AS estudiante_telefono,
                       prg.nombre AS programa_nombre,
                       prg.codigo AS programa_codigo,
                       prg.tipo AS programa_tipo,
                       plan.nombre AS plan_nombre,
                       plan.resolucion AS plan_resolucion,
                       modf.descripcion AS modulo_nombre,
                       modf.nro_modulo AS modulo_nro,
                       emp.razon_social AS empresa_razon_social,
                       emp.ruc AS empresa_ruc,
                       emp.direccion AS empresa_direccion,
                       doc.apellidos_nombres AS supervisor_nombre,
                       doc.dni AS supervisor_dni,
                       per.nombre AS periodo_nombre,
                       sede.nombre AS sede_nombre,
                       doc_emp.ruta_archivo AS constancia_ruta_archivo,
                       cst.nombre_archivo_descarga,
                       cst.fecha_emision AS fecha_emision_constancia
                FROM efsrt_practicas p
                INNER JOIN sigi_usuarios est ON p.id_estudiante = est.id
                INNER JOIN sigi_programa_estudios prg ON p.id_programa_estudios = prg.id
                LEFT JOIN sigi_planes_estudio plan ON p.id_plan_estudio = plan.id
                INNER JOIN sigi_modulo_formativo modf ON p.id_modulo_formativo = modf.id
                INNER JOIN efsrt_empresas emp ON p.id_empresa = emp.id
                INNER JOIN sigi_periodo_academico per ON p.id_periodo_academico = per.id
                LEFT JOIN sigi_sedes sede ON est.id_sede = sede.id
                LEFT JOIN efsrt_supervisores sup ON p.id_supervisor = sup.id
                LEFT JOIN sigi_usuarios doc ON sup.id_docente = doc.id
                LEFT JOIN efsrt_documentos doc_emp ON (doc_emp.id_practica = p.id AND doc_emp.tipo_documento = 'CONSTANCIA_EMPRESA' AND doc_emp.estado_archivo != 'ELIMINADO_PURGADO')
                LEFT JOIN efsrt_constancias cst ON (cst.id_practica = p.id AND cst.estado = 'EMITIDA')
                WHERE p.estado IN ('FINALIZADO_EMITIDO', 'FINALIZADO')";

        $params = [];

        if (!empty($filtros['id_programa'])) {
            $sql .= " AND p.id_programa_estudios = ? ";
            $params[] = (int)$filtros['id_programa'];
        }

        if (!empty($filtros['id_plan'])) {
            $sql .= " AND p.id_plan_estudio = ? ";
            $params[] = (int)$filtros['id_plan'];
        }

        if (!empty($filtros['id_modulo'])) {
            $sql .= " AND p.id_modulo_formativo = ? ";
            $params[] = (int)$filtros['id_modulo'];
        }

        if (!empty($filtros['id_periodo'])) {
            $sql .= " AND p.id_periodo_academico = ? ";
            $params[] = (int)$filtros['id_periodo'];
        }

        if (!empty($filtros['id_sede'])) {
            $sql .= " AND est.id_sede = ? ";
            $params[] = (int)$filtros['id_sede'];
        }

        $sql .= " ORDER BY est.apellidos_nombres ASC, p.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['estudiante_nombre'] = Auth::formatearNombreUsuario($r['estudiante_nombre']);
            if (!empty($r['supervisor_nombre'])) {
                $r['supervisor_nombre'] = Auth::formatearNombreUsuario($r['supervisor_nombre']);
            }
            if (empty($r['nombre_archivo_descarga'])) {
                $r['nombre_archivo_descarga'] = Constancia::construirNombreArchivo(
                    $r['estudiante_dni'],
                    $r['programa_codigo'],
                    $r['modulo_nro']
                );
            }
            if (empty($r['estudiante_tipo_doc'])) {
                $r['estudiante_tipo_doc'] = 'D.N.I.';
            }
        }

        return $rows;
    }

    /**
     * Construye los metadatos institucionales del filtro seleccionado
     */
    private function obtenerMetadatosFiltro($id_prog, $id_plan, $id_modulo, $id_periodo, $id_sede)
    {
        $db = (new Model())->getDB();

        $meta = [
            'programa' => null,
            'plan'     => null,
            'modulo'   => null,
            'periodo'  => null,
            'sede'     => null
        ];

        if ($id_prog) {
            $st = $db->prepare("SELECT * FROM sigi_programa_estudios WHERE id = ?");
            $st->execute([(int)$id_prog]);
            $meta['programa'] = $st->fetch(PDO::FETCH_ASSOC);
        }

        if ($id_plan) {
            $st = $db->prepare("SELECT * FROM sigi_planes_estudio WHERE id = ?");
            $st->execute([(int)$id_plan]);
            $meta['plan'] = $st->fetch(PDO::FETCH_ASSOC);
        }

        if ($id_modulo) {
            $st = $db->prepare("SELECT * FROM sigi_modulo_formativo WHERE id = ?");
            $st->execute([(int)$id_modulo]);
            $meta['modulo'] = $st->fetch(PDO::FETCH_ASSOC);
        }

        if ($id_periodo) {
            $st = $db->prepare("SELECT * FROM sigi_periodo_academico WHERE id = ?");
            $st->execute([(int)$id_periodo]);
            $meta['periodo'] = $st->fetch(PDO::FETCH_ASSOC);
        }

        if ($id_sede) {
            $st = $db->prepare("SELECT * FROM sigi_sedes WHERE id = ?");
            $st->execute([(int)$id_sede]);
            $meta['sede'] = $st->fetch(PDO::FETCH_ASSOC);
        }

        return $meta;
    }

    /**
     * Genera la instancia TCPDF con la plantilla del reporte
     */
    private function generarPdfObjeto($expedientes, $metadatos)
    {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('Secretaría Académica IESTP Huanta');
        $pdf->SetTitle('Reporte de Expedientes Culminados EFSRT');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        ob_start();
        $reporteVars = [
            'expedientes' => $expedientes,
            'metadatos'   => $metadatos
        ];
        extract($reporteVars);
        include __DIR__ . '/../../views/efsrt/reportes/pdf_reporte.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf;
    }

    /**
     * Endpoints AJAX para selects dinámicos en cascada
     */
    public function getPlanesAjax($id_programa)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY id DESC");
        $stmt->execute([(int)$id_programa]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getModulosAjax($id_plan)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
        $stmt->execute([(int)$id_plan]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
