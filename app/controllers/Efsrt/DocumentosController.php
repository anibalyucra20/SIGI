<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use App\Models\Efsrt\Documento;
use App\Models\Efsrt\Practica;
use App\Models\Efsrt\PeriodoConfig;
use App\Models\Efsrt\HistorialDerivacion;

class DocumentosController extends Controller
{
    protected $documentoModel;
    protected $practicaModel;
    protected $periodoConfigModel;
    protected $historialModel;

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

        $this->documentoModel = new Documento();
        $this->practicaModel = new Practica();
        $this->periodoConfigModel = new PeriodoConfig();
        $this->historialModel = new HistorialDerivacion();
    }

    /**
     * Sube un documento (Estudiante o Supervisor)
     */
    public function subir()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $tipo_documento = trim($_POST['tipo_documento'] ?? '');
        $id_user = $_SESSION['sigi_user_id'];

        $practica = $this->practicaModel->getById($id_practica);
        if (!$practica) {
            $_SESSION['flash_error'] = "Expediente de EFSRT no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esEstudiante = Auth::esEstudianteEfsrt();
        $esDocente = Auth::esDocenteEfsrt();
        $esSupervisor = ($esDocente && $practica['supervisor_docente_id'] == $id_user);
        $esAdmin = Auth::esAdminEfsrt();

        $docsEstudiante = [
            Documento::TIPO_DNI, 
            Documento::TIPO_FICHA_ACEPTACION, 
            Documento::TIPO_FICHA_EVALUACION, 
            Documento::TIPO_CONSTANCIA_EMPRESA
        ];
        $docsSupervisor = [Documento::TIPO_FICHA_SUPERVISION, Documento::TIPO_INFORME_FINAL_SUPERVISOR];

        // Validar permisos por rol y tipo de documento
        if (in_array($tipo_documento, $docsEstudiante)) {
            if (!$esEstudiante && !$esAdmin) {
                $_SESSION['flash_error'] = "Solo el estudiante titular o administrador puede subir este documento.";
                header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                exit;
            }

            // Verificar ventana de tiempo de subida
            if (!$esAdmin) {
                $id_periodo = $practica['id_periodo_academico'];
                $id_sede = !empty($practica['estudiante_sede_id']) ? (int)$practica['estudiante_sede_id'] : ($_SESSION['sigi_sede_actual'] ?? null);
                $id_prog = $practica['id_programa_estudios'];

                if (!$this->periodoConfigModel->estaAbiertoEstudiante($id_periodo, $id_sede, $id_prog)) {
                    $cfg = $this->periodoConfigModel->getConfigVigente($id_periodo, $id_sede, $id_prog);
                    $cierre = $cfg ? date('d/m/Y H:i', strtotime($cfg['fecha_cierre_estudiante'])) : '';
                    $_SESSION['flash_error'] = "El plazo límite para la subida de documentos del estudiante ha finalizado" . ($cierre ? " el $cierre" : "") . ". El sistema no permite entregas fuera de fecha.";
                    header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                    exit;
                }
            }
        } elseif (in_array($tipo_documento, $docsSupervisor)) {
            if (!$esSupervisor && !$esAdmin) {
                $_SESSION['flash_error'] = "Solo el docente supervisor asignado puede subir la ficha de supervisión.";
                header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                exit;
            }

            // Verificar ventana de tiempo de subida supervisor
            if (!$esAdmin) {
                $id_periodo = $practica['id_periodo_academico'];
                $id_sede = !empty($practica['estudiante_sede_id']) ? (int)$practica['estudiante_sede_id'] : ($_SESSION['sigi_sede_actual'] ?? null);
                $id_prog = $practica['id_programa_estudios'];

                if (!$this->periodoConfigModel->estaAbiertoSupervisor($id_periodo, $id_sede, $id_prog)) {
                    $cfg = $this->periodoConfigModel->getConfigVigente($id_periodo, $id_sede, $id_prog);
                    $cierre = $cfg ? date('d/m/Y H:i', strtotime($cfg['fecha_cierre_supervisor'])) : '';
                    $_SESSION['flash_error'] = "El plazo límite para la subida de fichas de supervisión ha finalizado" . ($cierre ? " el $cierre" : "") . ". El sistema no permite entregas fuera de fecha.";
                    header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                    exit;
                }
            }
        } else {
            $_SESSION['flash_error'] = "Tipo de documento no válido.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        // Validar archivo subido
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = "Por favor seleccione un archivo válido.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $file = $_FILES['archivo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $extPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $extPermitidas)) {
            $_SESSION['flash_error'] = "Solo se permiten documentos en formato PDF o imágenes (JPG, PNG).";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        if ($file['size'] > 12 * 1024 * 1024) { // 12 MB max
            $_SESSION['flash_error'] = "El archivo supera el tamaño máximo permitido (12 MB).";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $carpetaDestino = __DIR__ . '/../../../public/uploads/efsrt/documentos/';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreGuardado = strtolower($tipo_documento) . '_' . $id_practica . '_' . time() . '.' . $ext;
        $rutaFisica = $carpetaDestino . $nombreGuardado;

        if (!move_uploaded_file($file['tmp_name'], $rutaFisica)) {
            $_SESSION['flash_error'] = "Ocurrió un error al guardar el archivo en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $hashSha256 = hash_file('sha256', $rutaFisica);

        $datosDoc = [
            'id_practica'             => $id_practica,
            'tipo_documento'          => $tipo_documento,
            'subido_por'              => (int)$id_user,
            'nombre_archivo_original' => $file['name'],
            'nombre_archivo_guardado' => $nombreGuardado,
            'ruta_archivo'            => 'uploads/efsrt/documentos/' . $nombreGuardado,
            'tamano_bytes'            => (int)$file['size'],
            'extension'               => $ext,
            'hash_sha256'             => $hashSha256
        ];

        // Los archivos que el docente supervisor sube no requieren validación externa
        if (in_array($tipo_documento, $docsSupervisor)) {
            $datosDoc['estado_validacion'] = 'APROBADO';
            $datosDoc['validado_por'] = (int)$id_user;
        }

        try {
            $this->documentoModel->registrarOActualizar($datosDoc);

            // Si el estado de la práctica era REGISTRADO y el estudiante sube documentos, actualizar a DOCUMENTACION_SUBIDA
            if ($practica['estado'] === 'REGISTRADO' || $practica['estado'] === 'OBSERVADO_SUPERVISOR') {
                $this->practicaModel->actualizarEstado($id_practica, 'DOCUMENTACION_SUBIDA');
            }

            // Historial
            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => $esEstudiante ? 'ESTUDIANTE' : 'SUPERVISOR',
                'etapa_destino'     => 'SUPERVISOR',
                'accion'            => 'ENVIO',
                'id_usuario_accion' => $id_user,
                'comentarios'       => 'Subida / Actualización de archivo: ' . $tipo_documento
            ]);

            $_SESSION['flash_success'] = "Documento subido correctamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al registrar documento: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Valida individualmente un documento (Docente Supervisor)
     */
    public function validar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_documento = (int)($_POST['id_documento'] ?? 0);
        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $estado_val = trim($_POST['estado_validacion'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        $id_user = $_SESSION['sigi_user_id'];

        $practica = $this->practicaModel->getById($id_practica);
        if (!$practica) {
            $_SESSION['flash_error'] = "Práctica no encontrada.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esDocente = Auth::esDocenteEfsrt();
        $esSupervisor = ($esDocente && $practica['supervisor_docente_id'] == $id_user);
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esAdmin = Auth::esAdminEfsrt();

        if (!$esSupervisor && !$esCoordinador && !$esAdmin) {
            $_SESSION['flash_error'] = "No tienes permisos para evaluar este documento.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        if (!in_array($estado_val, ['APROBADO', 'OBSERVADO'])) {
            $_SESSION['flash_error'] = "Estado de validación no válido.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        if ($estado_val === 'OBSERVADO' && empty($observaciones)) {
            $_SESSION['flash_error'] = "Debe indicar las observaciones para devolver el documento.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        try {
            $this->documentoModel->validar($id_documento, $estado_val, $observaciones, $id_user);
            $_SESSION['flash_success'] = "Documento marcado como " . $estado_val . " exitosamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al validar documento: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Muestra el documento en línea (PDF o Imagen)
     */
    public function ver($id)
    {
        $id = (int)$id;
        $doc = $this->documentoModel->getById($id);

        if (!$doc) {
            $_SESSION['flash_error'] = "Documento no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $rutaFisica = __DIR__ . '/../../../public/' . $doc['ruta_archivo'];
        if (!file_exists($rutaFisica)) {
            $_SESSION['flash_error'] = "El archivo físico no existe en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $mime = 'application/pdf';
        if ($doc['extension'] === 'png') {
            $mime = 'image/png';
        } elseif (in_array($doc['extension'], ['jpg', 'jpeg'])) {
            $mime = 'image/jpeg';
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($doc['nombre_archivo_original']) . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }

    /**
     * Descarga el archivo
     */
    public function descargar($id)
    {
        $id = (int)$id;
        $doc = $this->documentoModel->getById($id);

        if (!$doc) {
            $_SESSION['flash_error'] = "Documento no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $rutaFisica = __DIR__ . '/../../../public/' . $doc['ruta_archivo'];
        if (!file_exists($rutaFisica)) {
            $_SESSION['flash_error'] = "El archivo físico no existe en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($doc['nombre_archivo_original']) . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }
}
