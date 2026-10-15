<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use App\Models\Efsrt\Practica;
use App\Models\Efsrt\Documento;
use App\Models\Efsrt\HistorialDerivacion;

class DerivacionesController extends Controller
{
    protected $practicaModel;
    protected $documentoModel;
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

        $this->practicaModel = new Practica();
        $this->documentoModel = new Documento();
        $this->historialModel = new HistorialDerivacion();
    }

    /**
     * Visto Bueno del Supervisor y Derivación al Coordinador (Nivel 1)
     */
    public function supervisorAprobarYDerivar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $calificacion = isset($_POST['calificacion_final']) && $_POST['calificacion_final'] !== '' ? (float)$_POST['calificacion_final'] : null;
        $horas_convalidadas = !empty($_POST['horas_convalidadas']) ? (int)$_POST['horas_convalidadas'] : null;
        $comentarios = trim($_POST['comentarios'] ?? 'Visto Bueno de supervisión otorgado. Expediente derivado a Coordinación de Área Académica.');
        $id_user = $_SESSION['sigi_user_id'];

        $practica = $this->practicaModel->getById($id_practica);
        if (!$practica) {
            $_SESSION['flash_error'] = "Práctica no encontrada.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esDocente = Auth::esDocenteEfsrt();
        $esSupervisor = ($esDocente && $practica['supervisor_docente_id'] == $id_user);
        $esAdmin = Auth::esAdminEfsrt();

        if (!$esSupervisor && !$esAdmin) {
            $_SESSION['flash_error'] = "Solo el docente supervisor asignado o administrador puede derivar este expediente.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        // Verificar que todos los documentos obligatorios estén aprobados
        $estadoExpediente = $this->documentoModel->estadoExpedienteCompleto($id_practica);
        if (!$estadoExpediente['todos_aprobados'] && !$esAdmin) {
            $_SESSION['flash_error'] = "No se puede derivar el expediente: aún faltan documentos requeridos o existen observaciones pendientes de subsanar.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        try {
            // Actualizar estado de la práctica
            $this->practicaModel->actualizarEstado($id_practica, 'DERIVADO_COORDINADOR', $calificacion, $horas_convalidadas ?: $practica['horas_requeridas']);

            // Registrar en historial
            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => 'SUPERVISOR',
                'etapa_destino'     => 'COORDINADOR',
                'accion'            => 'VISTO_BUENO_DERIVADO',
                'id_usuario_accion' => $id_user,
                'comentarios'       => $comentarios
            ]);

            $_SESSION['flash_success'] = "Expediente con V°B° del Supervisor derivado exitosamente al Coordinador de Área.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al derivar expediente: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Observar y devolver al Estudiante (Supervisor o Coordinador)
     */
    public function observarYDevolver()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $comentarios = trim($_POST['comentarios'] ?? '');
        $id_user = $_SESSION['sigi_user_id'];

        if (empty($comentarios)) {
            $_SESSION['flash_error'] = "Debe ingresar el motivo de la observación.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $practica = $this->practicaModel->getById($id_practica);
        if (!$practica) {
            $_SESSION['flash_error'] = "Práctica no encontrada.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esSupervisor = (Auth::esDocenteEfsrt() && $practica['supervisor_docente_id'] == $id_user);
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esSecretario = Auth::esSecretarioPEEfsrt();
        $esAdmin = Auth::esAdminEfsrt();

        $nuevoEstado = 'OBSERVADO_SUPERVISOR';
        $etapaOrigen = 'SUPERVISOR';
        $etapaDestino = 'ESTUDIANTE';

        if ($esSecretario) {
            $nuevoEstado = 'OBSERVADO_SECRETARIA';
            $etapaOrigen = 'SECRETARIA';
            $etapaDestino = 'COORDINADOR';
        } elseif ($esCoordinador) {
            $nuevoEstado = 'OBSERVADO_COORDINADOR';
            $etapaOrigen = 'COORDINADOR';
            $etapaDestino = 'SUPERVISOR';
        }

        try {
            $this->practicaModel->actualizarEstado($id_practica, $nuevoEstado);

            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => $etapaOrigen,
                'etapa_destino'     => $etapaDestino,
                'accion'            => 'OBSERVADO_DEVUELTO',
                'id_usuario_accion' => $id_user,
                'comentarios'       => $comentarios
            ]);

            $_SESSION['flash_success'] = "Expediente observado y devuelto para corrección.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al observar expediente: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Visto Bueno del Coordinador y Derivación a Secretaría Académica (Nivel 2)
     */
    public function coordinadorAprobarYDerivar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $comentarios = trim($_POST['comentarios'] ?? 'Visto Bueno de Coordinación Académica otorgado. Expediente derivado a Secretaría Académica para emisión de constancia.');
        $id_user = $_SESSION['sigi_user_id'];

        if (!Auth::esCoordinadorPEEfsrt() && !Auth::esAdminEfsrt()) {
            $_SESSION['flash_error'] = "Acceso restringido a Coordinadores de Programa.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        try {
            $this->practicaModel->actualizarEstado($id_practica, 'DERIVADO_SECRETARIA');

            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => 'COORDINADOR',
                'etapa_destino'     => 'SECRETARIA',
                'accion'            => 'VISTO_BUENO_DERIVADO',
                'id_usuario_accion' => $id_user,
                'comentarios'       => $comentarios
            ]);

            $_SESSION['flash_success'] = "Expediente con V°B° del Coordinador derivado a Secretaría Académica.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al derivar expediente: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    /**
     * Subsanar observación y reenviar a la siguiente etapa (Coordinador -> Secretaría o Supervisor -> Coordinador)
     */
    public function subsanarYDerivar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $comentarios = trim($_POST['comentarios'] ?? '');
        $id_user = $_SESSION['sigi_user_id'];

        if (empty($comentarios)) {
            $_SESSION['flash_error'] = "Debe detallar el sustento o acciones de subsanación realizadas.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        }

        $practica = $this->practicaModel->getById($id_practica);
        if (!$practica) {
            $_SESSION['flash_error'] = "Práctica no encontrada.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esSupervisor = (Auth::esDocenteEfsrt() && $practica['supervisor_docente_id'] == $id_user);
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esAdmin = Auth::esAdminEfsrt();

        if ($practica['estado'] === 'OBSERVADO_SECRETARIA') {
            if (!$esCoordinador && !$esAdmin) {
                $_SESSION['flash_error'] = "Solo la Coordinación de Carrera o Administrador puede subsanar ante Secretaría.";
                header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                exit;
            }

            try {
                $this->practicaModel->actualizarEstado($id_practica, 'DERIVADO_SECRETARIA');

                $this->historialModel->registrar([
                    'id_practica'       => $id_practica,
                    'etapa_origen'      => 'COORDINADOR',
                    'etapa_destino'     => 'SECRETARIA',
                    'accion'            => 'OBSERVACION_SUBSANADA',
                    'id_usuario_accion' => $id_user,
                    'comentarios'       => $comentarios
                ]);

                $_SESSION['flash_success'] = "Observación subsanada exitosamente. Expediente reenviado a Secretaría Académica para emisión de constancia.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Error al subsanar observación: " . $e->getMessage();
            }
        } elseif ($practica['estado'] === 'OBSERVADO_COORDINADOR') {
            if (!$esSupervisor && !$esAdmin && !$esCoordinador) {
                $_SESSION['flash_error'] = "Solo el docente supervisor asignado, Coordinación o Administrador puede subsanar ante Coordinación.";
                header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
                exit;
            }

            try {
                $this->practicaModel->actualizarEstado($id_practica, 'DERIVADO_COORDINADOR');

                $this->historialModel->registrar([
                    'id_practica'       => $id_practica,
                    'etapa_origen'      => 'SUPERVISOR',
                    'etapa_destino'     => 'COORDINADOR',
                    'accion'            => 'OBSERVACION_SUBSANADA',
                    'id_usuario_accion' => $id_user,
                    'comentarios'       => $comentarios
                ]);

                $_SESSION['flash_success'] = "Observación subsanada exitosamente. Expediente reenviado a Coordinación de Carrera.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Error al subsanar observación: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "El estado actual del expediente no permite subsanación.";
        }

        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }
}
