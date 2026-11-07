<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\Constancia;
use App\Models\Efsrt\Practica;
use App\Models\Efsrt\Documento;
use App\Models\Efsrt\HistorialDerivacion;
use TCPDF;
use PDO;

class ConstanciasController extends Controller
{
    protected $constanciaModel;
    protected $practicaModel;
    protected $documentoModel;
    protected $historialModel;

    public function __construct()
    {
        parent::__construct();

        // Para la verificación pública por token no exigimos sesión de efsrt
        $action = $_GET['action'] ?? '';
        if ($action !== 'verificar') {
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
        }

        $this->constanciaModel = new Constancia();
        $this->practicaModel = new Practica();
        $this->documentoModel = new Documento();
        $this->historialModel = new HistorialDerivacion();
    }

    public function index()
    {
        $id_user = $_SESSION['sigi_user_id'];
        $esEstudiante = Auth::esEstudianteEfsrt();

        $filters = [];
        if ($esEstudiante) {
            $filters['id_estudiante'] = $id_user;
        }

        if (!empty($_GET['id_periodo'])) {
            $filters['id_periodo'] = (int)$_GET['id_periodo'];
        }

        if (!empty($_GET['id_programa'])) {
            $filters['id_programa'] = (int)$_GET['id_programa'];
        }

        if (!empty($_GET['q'])) {
            $filters['busqueda'] = trim($_GET['q']);
        }

        $db = (new Model())->getDB();
        $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        if (Auth::esCoordinadorPEEfsrt() && !Auth::esAdminEfsrt()) {
            $periodo = $_SESSION['sigi_periodo_actual_id'] ?? 1;
            $stmtCoord = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtCoord->execute([$id_user, $periodo]);
            $coordProgs = $stmtCoord->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($coordProgs)) {
                $filters['programas_in'] = $coordProgs;
                $inPl = implode(',', array_fill(0, count($coordProgs), '?'));
                $stmtProgList = $db->prepare("SELECT id, nombre, codigo FROM sigi_programa_estudios WHERE id IN ($inPl) ORDER BY nombre ASC");
                $stmtProgList->execute($coordProgs);
                $programas = $stmtProgList->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $filters['programas_in'] = [-1];
                $programas = [];
            }
        }

        $constancias = $this->constanciaModel->getAll($filters);

        $puedeGestionar = (Auth::esAdminEfsrt() || Auth::esSecretarioPEEfsrt());

        $this->view('efsrt/constancias/index', [
            'module'             => 'efsrt',
            'pageTitle'    => 'Constancias Oficiales de EFSRT',
            'constancias'  => $constancias,
            'periodos'     => $periodos,
            'programas'    => $programas,
            'esEstudiante' => $esEstudiante,
            'puedeGestionar' => $puedeGestionar
        ]);
    }

    /**
     * Validación y Finalización del Expediente (Secretaría Académica / Admin - Nivel 3)
     * La constancia oficial es la Constancia de la Empresa subida por el estudiante,
     * la cual se valida y queda lista para descargar con el formato [DNI]-[ABREV_CARRERA]-[NRO_MODULO].pdf
     */
    public function generar($id_practica)
    {
        if (!Auth::esSecretarioPEEfsrt() && !Auth::esAdminEfsrt()) {
            $_SESSION['flash_error'] = "Solo la Secretaría Académica o Administrador puede validar y finalizar expedientes.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)$id_practica;
        $practica = $this->practicaModel->getById($id_practica);

        if (!$practica) {
            $_SESSION['flash_error'] = "Expediente no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        // Obtener el documento de Constancia de Empresa subido por el estudiante
        $docEmpresa = $this->documentoModel->getByTipo($id_practica, Documento::TIPO_CONSTANCIA_EMPRESA);
        if (!$docEmpresa) {
            $_SESSION['flash_error'] = "El expediente no cuenta con la Constancia de la Empresa subida por el estudiante.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $id_user = $_SESSION['sigi_user_id'];
        $nroConstancia = "CST-EMP-" . $practica['id'];
        $qrToken = bin2hex(random_bytes(16));

        // Formato estricto solicitado: [DNI]-[ABREV_CARRERA]-[NRO_MODULO].pdf
        $nombreDescarga = Constancia::construirNombreArchivo(
            $practica['estudiante_dni'],
            $practica['programa_codigo'],
            $practica['modulo_nro']
        );

        $constanciaExistente = $this->constanciaModel->getByPractica($id_practica);

        $db = (new Model())->getDB();
        try {
            if ($constanciaExistente) {
                $stmtUpd = $db->prepare("UPDATE efsrt_constancias SET 
                                            nombre_archivo_descarga = ?, 
                                            ruta_pdf = ?, 
                                            estado = 'EMITIDA', 
                                            fecha_emision = NOW(), 
                                            emitido_por = ? 
                                        WHERE id = ?");
                $stmtUpd->execute([$nombreDescarga, $docEmpresa['ruta_archivo'], $id_user, (int)$constanciaExistente['id']]);
            } else {
                $this->constanciaModel->registrar([
                    'id_practica'             => $id_practica,
                    'id_estudiante'           => $practica['id_estudiante'],
                    'nro_constancia'          => $nroConstancia,
                    'nombre_archivo_descarga' => $nombreDescarga,
                    'ruta_pdf'                => $docEmpresa['ruta_archivo'],
                    'fecha_emision'           => date('Y-m-d'),
                    'emitido_por'             => $id_user,
                    'qr_token'                => $qrToken
                ]);
            }

            // Actualizar la práctica a estado finalizado
            $this->practicaModel->actualizarEstado($id_practica, 'FINALIZADO_EMITIDO');

            // Registrar en historial
            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => 'SECRETARIA',
                'etapa_destino'     => 'FINALIZADO',
                'accion'            => 'CONSTANCIA_EMITIDA',
                'id_usuario_accion' => $id_user,
                'comentarios'       => "Validación final y culminación exitosa del expediente. Constancia de empresa asignada como {$nombreDescarga}."
            ]);

            $_SESSION['flash_success'] = "Expediente validado y culminado exitosamente. La constancia de la empresa ha sido asignada para su descarga como {$nombreDescarga}.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al finalizar expediente: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Construye físicamente el documento PDF de la constancia oficial
     */
    private function construirPdfConstancia($practica, $nroConstancia, $qrToken, $rutaPdfFisica)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('Secretaría Académica IESTP Huanta');
        $pdf->SetTitle('Constancia EFSRT - ' . $practica['estudiante_nombre']);
        $pdf->SetSubject('Constancia de Experiencias Formativas en Situaciones Reales de Trabajo');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(22, 18, 22);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Logo del Instituto
        $rutaLogo = __DIR__ . '/../../../public/images/logo.png';
        if (file_exists($rutaLogo)) {
            $pdf->Image($rutaLogo, 22, 15, 26, 0, 'PNG');
        }

        // Membrete Oficial
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY(52, 15);
        $pdf->MultiCell(136, 6, "INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO\n\"HUANTA\"", 0, 'C', false, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetX(52);
        $pdf->MultiCell(136, 4, "Licenciado con R.M. N° 067-2020-MINEDU\nÁrea de Secretaría Académica - Coordinación de EFSRT", 0, 'C', false, 1);

        // Línea divisoria elegante
        $pdf->SetDrawColor(30, 41, 59);
        $pdf->SetLineWidth(0.6);
        $pdf->Line(22, 37, 188, 37);

        // Título del Documento
        $pdf->Ln(12);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 10, 'CONSTANCIA DE EFSRT', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 7, "N° {$nroConstancia}", 0, 1, 'C');

        $pdf->Ln(6);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(30, 41, 59);

        // Texto legal y declarativo
        $textoIntro = "LA SECRETARÍA ACADÉMICA DEL INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO \"HUANTA\", QUE SUSCRIBE;";
        $pdf->MultiCell(0, 7, $textoIntro, 0, 'J', false, 1);

        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'HACE CONSTAR QUE:', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $estudianteNombre = mb_strtoupper($practica['estudiante_nombre'], 'UTF-8');
        $dni = $practica['estudiante_dni'];
        $programa = mb_strtoupper($practica['programa_nombre'], 'UTF-8');
        $modulo = mb_strtoupper("MÓDULO {$practica['modulo_nro']}: {$practica['modulo_nombre']}", 'UTF-8');
        $empresa = mb_strtoupper($practica['empresa_razon_social'], 'UTF-8');
        $fechaIni = date('d/m/Y', strtotime($practica['fecha_inicio']));
        $fechaFin = date('d/m/Y', strtotime($practica['fecha_fin']));
        $horas = $practica['horas_convalidadas'] ?: $practica['horas_requeridas'];
        $nota = $practica['calificacion_final'] ? number_format($practica['calificacion_final'], 1) : '-';

        $cuerpo = "El/La estudiante <b>{$estudianteNombre}</b>, identificado(a) con DNI N° <b>{$dni}</b>, del Programa de Estudios de <b>{$programa}</b>, ha realizado y culminado satisfactoriamente las Experiencias Formativas en Situaciones Reales de Trabajo (EFSRT) correspondientes al:<br><br>"
            . "<b>{$modulo}</b><br><br>"
            . "Desarrollado en la empresa/institución <b>{$empresa}</b> en el área de <b>" . htmlspecialchars($practica['area_desempeno']) . "</b>, durante el periodo comprendido del <b>{$fechaIni}</b> al <b>{$fechaFin}</b>, acumulando un total de <b>{$horas} HORAS</b> efectivas, obteniendo la calificación final de <b>{$nota}</b>.<br><br>"
            . "Habiéndose revisado y validado la documentación respectiva (Ficha de Aceptación, Constancia de la Empresa y Ficha de Supervisión del Docente Supervisor), se expide la presente a solicitud del interesado para los fines que estime conveniente.";

        $pdf->writeHTMLCell(0, 0, '', '', $cuerpo, 0, 1, 0, true, 'J', true);

        $pdf->Ln(8);
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $dia = date('d');
        $mes = $meses[(int)date('m')];
        $anio = date('Y');
        $fechaLugar = "Huanta, {$dia} de {$mes} de {$anio}";
        $pdf->Cell(0, 8, $fechaLugar, 0, 1, 'R');

        // Código QR de Verificación Oficial
        $urlVerificacion = BASE_URL . '/efsrt/constancias/verificar/' . $qrToken;
        $styleQr = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [15, 23, 42],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        ];

        $pdf->SetXY(22, 225);
        $pdf->write2DBarcode($urlVerificacion, 'QRCODE,H', 22, 225, 28, 28, $styleQr, 'N');

        $pdf->SetXY(53, 228);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->MultiCell(60, 4, "Verificación oficial de autenticidad digital.\nEscanee este código QR para validar la constancia en el sistema SIGI.\nToken: " . substr($qrToken, 0, 16) . "...", 0, 'L', false, 1);

        // Bloque de Firmas y Sellos
        $pdf->SetXY(118, 240);
        $pdf->SetDrawColor(100, 116, 139);
        $pdf->Line(120, 248, 185, 248);
        $pdf->SetXY(120, 250);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(65, 4, 'SECRETARÍA ACADÉMICA', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 4, 'IESTP HUANTA', 0, 1, 'R');

        // Guardar el PDF en el disco
        $pdf->Output($rutaPdfFisica, 'F');
    }

    /**
     * Descarga de la constancia con el nombre estandarizado: [DNI]-[ABREV_CARRERA]-[NRO_MODULO].pdf
     */
    public function descargar($id)
    {
        $id = (int)$id;
        $constancia = $this->constanciaModel->getById($id);
        if (!$constancia) {
            $constancia = $this->constanciaModel->getByPractica($id);
        }

        if (!$constancia) {
            $practica = $this->practicaModel->getById($id);
            if ($practica) {
                $docEmp = $this->documentoModel->getByTipo($id, Documento::TIPO_CONSTANCIA_EMPRESA);
                if ($docEmp) {
                    $nombreArchivo = Constancia::construirNombreArchivo(
                        $practica['estudiante_dni'],
                        $practica['programa_codigo'],
                        $practica['modulo_nro']
                    );
                    $rutaFisica = __DIR__ . '/../../../public/' . $docEmp['ruta_archivo'];
                    if (file_exists($rutaFisica)) {
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
                        header('Content-Length: ' . filesize($rutaFisica));
                        header('Cache-Control: private, max-age=0, must-revalidate');
                        header('Pragma: public');
                        readfile($rutaFisica);
                        exit;
                    }
                }
            }
            $_SESSION['flash_error'] = "Constancia no encontrada.";
            header('Location: ' . BASE_URL . '/efsrt/constancias');
            exit;
        }

        $nombreArchivo = $constancia['nombre_archivo_descarga'];
        $rutaFisica = __DIR__ . '/../../../public/' . $constancia['ruta_pdf'];

        if (!file_exists($rutaFisica)) {
            $_SESSION['flash_error'] = "El archivo de la constancia no fue encontrado en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/constancias');
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
     * Visualización en el navegador
     */
    public function verPdf($id)
    {
        $constancia = $this->constanciaModel->getById((int)$id);
        if (!$constancia) {
            die("Constancia no encontrada");
        }

        $rutaFisica = __DIR__ . '/../../../public/' . $constancia['ruta_pdf'];
        if (!file_exists($rutaFisica)) {
            die("El archivo físico no existe");
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $constancia['nombre_archivo_descarga'] . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }

    /**
     * Verificación pública de constancia mediante el código QR
     */
    public function verificar($token = null)
    {
        $token = trim($token ?? ($_GET['token'] ?? ''));
        $constancia = $this->constanciaModel->getByToken($token);

        $this->view('efsrt/constancias/verificar', [
            'module'             => 'efsrt',
            'pageTitle'  => 'Verificación de Constancia Digital - IESTP Huanta',
            'constancia' => $constancia,
            'token'      => $token
        ]);
    }
}
