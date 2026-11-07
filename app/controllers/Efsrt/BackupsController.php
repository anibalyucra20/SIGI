<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use App\Models\Efsrt\BackupLog;
use App\Models\Efsrt\Documento;
use ZipArchive;

class BackupsController extends Controller
{
    protected $backupLogModel;
    protected $documentoModel;

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

        $this->backupLogModel = new BackupLog();
        $this->documentoModel = new Documento();
    }

    public function index()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esSecretarioPEEfsrt()) {
            $_SESSION['flash_error'] = "Acceso restringido a administradores y directivos.";
            header('Location: ' . BASE_URL . '/efsrt');
            exit;
        }

        $anios = isset($_GET['anios']) ? max(1, (int)$_GET['anios']) : 5;
        $archivosCandidatos = $this->documentoModel->obtenerArchivosAntiguosParaBackup($anios);

        $totalBytes = 0;
        foreach ($archivosCandidatos as $arc) {
            $totalBytes += (int)$arc['tamano_bytes'];
        }

        $historial = $this->backupLogModel->getAll();

        $puedeEjecutar = Auth::esAdminEfsrt() || Auth::esDirectorEfsrt() || Auth::esSecretarioPEEfsrt();

        $this->view('efsrt/backups/index', [
            'module'             => 'efsrt',
            'pageTitle'          => 'Mantenimiento y Respaldo de Archivos (>5 Años) - EFSRT',
            'anios'              => $anios,
            'archivosCandidatos' => $archivosCandidatos,
            'totalArchivos'      => count($archivosCandidatos),
            'totalBytes'         => $totalBytes,
            'totalBytesFormato'  => BackupLog::formatearBytes($totalBytes),
            'historial'          => $historial,
            'puedeEjecutar'      => $puedeEjecutar
        ]);
    }

    public function ejecutar()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esDirectorEfsrt() && !Auth::esSecretarioPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para ejecutar la purga de archivos.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        $anios = isset($_POST['anios']) ? max(1, (int)$_POST['anios']) : 5;
        $archivos = $this->documentoModel->obtenerArchivosAntiguosParaBackup($anios);

        if (empty($archivos)) {
            $_SESSION['flash_info'] = "No se encontraron archivos con más de {$anios} años de antigüedad para respaldar y depurar.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        if (!class_exists('ZipArchive')) {
            $_SESSION['flash_error'] = "La extensión PHP ZipArchive no está habilitada en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        $carpetaBackups = __DIR__ . '/../../../public/uploads/efsrt/backups/';
        if (!is_dir($carpetaBackups)) {
            mkdir($carpetaBackups, 0777, true);
        }

        $nombreZip = 'backup_efsrt_' . $anios . 'anios_' . date('Ymd_His') . '.zip';
        $rutaZipFisica = $carpetaBackups . $nombreZip;
        $rutaZipRelativa = 'uploads/efsrt/backups/' . $nombreZip;

        $zip = new ZipArchive();
        if ($zip->open($rutaZipFisica, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $_SESSION['flash_error'] = "No se pudo crear el archivo ZIP de respaldo en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        $idsProcesados = [];
        $totalBytesLiberados = 0;
        $detalles = [];

        foreach ($archivos as $doc) {
            $rutaFisicaDoc = __DIR__ . '/../../../public/' . $doc['ruta_archivo'];

            if (file_exists($rutaFisicaDoc)) {
                // Estructura interna ordenada en el ZIP
                $prog = $doc['programa_codigo'] ?: 'GENERAL';
                $carpetaEst = preg_replace('/[^a-zA-Z0-9_-]/', '_', $doc['estudiante_dni'] . '_' . $doc['estudiante_nombre']);
                $nombreEnZip = "{$prog}/{$carpetaEst}/{$doc['tipo_documento']}_{$doc['nombre_archivo_original']}";

                $zip->addFile($rutaFisicaDoc, $nombreEnZip);

                $idsProcesados[] = (int)$doc['id'];
                $totalBytesLiberados += (int)$doc['tamano_bytes'];
                $detalles[] = [
                    'id'       => $doc['id'],
                    'tipo'     => $doc['tipo_documento'],
                    'archivo'  => $doc['nombre_archivo_original'],
                    'dni'      => $doc['estudiante_dni'],
                    'tamano'   => $doc['tamano_bytes']
                ];
            }
        }

        $zip->close();

        // Eliminar archivos físicos del disco una vez empaquetados en el ZIP
        foreach ($archivos as $doc) {
            $rutaFisicaDoc = __DIR__ . '/../../../public/' . $doc['ruta_archivo'];
            if (file_exists($rutaFisicaDoc)) {
                @unlink($rutaFisicaDoc);
            }
        }

        // Marcar en la base de datos como ELIMINADO_PURGADO
        $this->documentoModel->marcarArchivosPurgados($idsProcesados);

        // Registrar log de backup
        $idLog = $this->backupLogModel->registrar([
            'ejecutado_por'              => (int)$_SESSION['sigi_user_id'],
            'total_archivos_procesados'  => count($idsProcesados),
            'espacio_liberado_bytes'     => $totalBytesLiberados,
            'nombre_archivo_backup_zip'  => $nombreZip,
            'ruta_backup_zip'            => $rutaZipRelativa,
            'criterio_antiguedad_anios'  => $anios,
            'estado'                     => 'EXITOSO',
            'detalles_json'              => json_encode($detalles)
        ]);

        $espacioFormateado = BackupLog::formatearBytes($totalBytesLiberados);
        $_SESSION['flash_success'] = "Proceso completado: Se respaldaron " . count($idsProcesados) . " archivos y se liberaron {$espacioFormateado} de almacenamiento del servidor. Puede descargar el paquete ZIP a continuación.";

        header('Location: ' . BASE_URL . '/efsrt/backups');
        exit;
    }

    public function descargar($id)
    {
        $log = $this->backupLogModel->getById((int)$id);
        if (!$log) {
            $_SESSION['flash_error'] = "Registro de backup no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        $rutaFisica = __DIR__ . '/../../../public/' . $log['ruta_backup_zip'];
        if (!file_exists($rutaFisica)) {
            $_SESSION['flash_error'] = "El archivo ZIP de respaldo no se encuentra en el servidor.";
            header('Location: ' . BASE_URL . '/efsrt/backups');
            exit;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $log['nombre_archivo_backup_zip'] . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }
}
