<?php

namespace App\Controllers\Tutoria;

use Core\Controller;
use Core\Auth;
use TCPDF;

use App\Models\Tutoria\SesionIndividual;
use App\Models\Tutoria\SesionGrupal;
use App\Models\Tutoria\EstudianteTutoria;
use App\Models\Tutoria\Programacion;

class SesionesController extends Controller
{
    protected $objSesionIndividual;
    protected $objSesionGrupal;
    protected $objEstudianteTutoria;
    protected $objProgramacion;

    public function __construct()
    {
        parent::__construct();

        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a las sesiones de tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->objSesionIndividual  = new SesionIndividual();
        $this->objSesionGrupal      = new SesionGrupal();
        $this->objEstudianteTutoria = new EstudianteTutoria();
        $this->objProgramacion      = new Programacion();
    }

    /* =========================================================================
     * CONTROL DE PERMISOS Y AUTORIZACIÓN
     * ========================================================================= */

    /**
     * Valida que el usuario tenga rol de Administrador o Docente Tutor.
     */
    protected function verificarPermisoTutor()
    {
        if (!Auth::tieneRolEnTutoria()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a las sesiones de tutoría.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        if (!Auth::esAdminTutoria() && !Auth::esDocenteTutoria()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requieren permisos de tutor o administrador para gestionar sesiones.";
            header('Location: ' . BASE_URL . '/tutoria');
            exit;
        }
    }

    /**
     * Valida que el estudiante pertenezca al tutor logueado (los administradores omiten esta restricción).
     */
    protected function validarPermisoTutorando($tutorando)
    {
        if (Auth::esAdminTutoria()) {
            return true;
        }

        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        if ((int)$tutorando['id_docente'] !== $idUsuario) {
            $_SESSION['flash_error'] = "Acceso denegado: El estudiante no está asignado a tu tutoría.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }
        return true;
    }

    /**
     * Valida que la programación de tutoría pertenezca al tutor logueado (los administradores omiten esta restricción).
     */
    protected function validarPermisoProgramacion($programacion)
    {
        if (Auth::esAdminTutoria()) {
            return true;
        }

        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        if ((int)$programacion['id_docente'] !== $idUsuario) {
            $_SESSION['flash_error'] = "Acceso denegado: La programación no corresponde a tu asignación como tutor.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }
        return true;
    }

    /* =========================================================================
     * SESIONES INDIVIDUALES
     * ========================================================================= */

    public function individuales($id_tutoria_estudiante)
    {
        $this->verificarPermisoTutor();

        $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "No se encontró el registro del estudiante tutoriado.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $this->validarPermisoTutorando($tutorando);

        $sesiones = $this->objSesionIndividual->listarPorTutorando($id_tutoria_estudiante);

        $this->view('tutoria/tutores/sesionesIndividuales/tutorando_sesiones', [
            'tutorando' => $tutorando,
            'sesiones'  => $sesiones,
            'module'    => 'tutoria',
            'pageTitle' => 'Sesiones Individuales - ' . $tutorando['nombre_completo']
        ]);
        exit;
    }

    public function individualNuevo($id_tutoria_estudiante = null)
    {
        $this->verificarPermisoTutor();

        $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
        $esAdmin    = Auth::esAdminTutoria();
        $id_usuario = $_SESSION['sigi_user_id'] ?? 0;

        if (empty($id_tutoria_estudiante)) {
            $id_tutoria_estudiante = $_GET['id_tutoria_estudiante'] ?? $_POST['id_tutoria_estudiante'] ?? null;
        }

        // Obtener la lista de estudiantes asignados al tutor actual (o de la sede/periodo si es admin)
        $tutorandosAsignados = [];
        $miProg = null;

        if ($esAdmin) {
            if (!empty($id_tutoria_estudiante)) {
                $tempTut = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
                if ($tempTut) {
                    $tutorandosAsignados = $this->objEstudianteTutoria->listarPorProgramacion($tempTut['id_programacion_tutoria']);
                }
            }
            if (empty($tutorandosAsignados)) {
                $tutores = $this->objProgramacion->getPaginated(['id_sede' => $id_sede, 'id_periodo_academico' => $id_periodo], 100, 0, 3, 'ASC')['data'] ?? [];
                if (!empty($tutores[0]['id'])) {
                    $tutorandosAsignados = $this->objEstudianteTutoria->listarPorProgramacion($tutores[0]['id']);
                }
            }
        } else {
            // Es Docente Tutor: Obtener su programación actual y sus tutorandos asignados
            $miProg = $this->objProgramacion->getProgramacionPorDocente($id_usuario, $id_periodo, $id_sede);
            if ($miProg) {
                $tutorandosAsignados = $this->objEstudianteTutoria->listarPorProgramacion($miProg['id']);
            }
        }

        // Si se envió id_tutoria_estudiante, buscar ese estudiante; sino, tomar el primero disponible
        $tutorando = null;
        if (!empty($id_tutoria_estudiante)) {
            $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
            if ($tutorando) {
                $this->validarPermisoTutorando($tutorando);
            }
        } elseif (!empty($tutorandosAsignados)) {
            $primerId = $tutorandosAsignados[0]['id_tutoria_estudiante'];
            $tutorando = $this->objEstudianteTutoria->find($primerId);
            $id_tutoria_estudiante = $primerId;
        }

        $siguienteNro = $tutorando ? $this->objSesionIndividual->getSiguienteNumero($tutorando['id']) : 1;

        $this->view('tutoria/tutores/sesionesIndividuales/nuevo', [
            'tutorando'    => $tutorando,
            'tutorandos'   => $tutorandosAsignados,
            'siguienteNro' => $siguienteNro,
            'isEdit'       => false,
            'module'       => 'tutoria',
            'pageTitle'    => 'Nueva Sesión Individual'
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para consultar el siguiente número de sesión y datos del estudiante seleccionado.
     */
    public function siguienteNumeroAjax($id_tutoria_estudiante)
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->verificarPermisoTutor();

        $id_tutoria_estudiante = (int)$id_tutoria_estudiante;
        $tutorando = $this->objEstudianteTutoria->find($id_tutoria_estudiante);
        if (!$tutorando) {
            http_response_code(404);
            echo json_encode(['error' => 'Estudiante no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->validarPermisoTutorando($tutorando);
        $siguienteNro = $this->objSesionIndividual->getSiguienteNumero($id_tutoria_estudiante);

        echo json_encode([
            'id_tutoria_estudiante' => $id_tutoria_estudiante,
            'siguienteNro'          => $siguienteNro,
            'dni'                   => $tutorando['dni'] ?? '',
            'programa_nombre'       => $tutorando['programa_nombre'] ?? 'General',
            'nombre_completo'       => $tutorando['nombre_completo'] ?? ''
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function individualEditar($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionIndividual->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión individual no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $tutorando = $this->objEstudianteTutoria->find($sesion['id_tutoria_estudiante']);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "Estudiante asociado a la sesión no encontrado.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $this->validarPermisoTutorando($tutorando);

        $this->view('tutoria/tutores/sesionesIndividuales/editar', [
            'sesion'    => $sesion,
            'tutorando' => $tutorando,
            'isEdit'    => true,
            'module'    => 'tutoria',
            'pageTitle' => 'Editar Sesión Individual N° ' . $sesion['nro']
        ]);
        exit;
    }

    public function individualVer($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionIndividual->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $tutorando = $this->objEstudianteTutoria->find($sesion['id_tutoria_estudiante']);
        if ($tutorando) {
            $this->validarPermisoTutorando($tutorando);
        }

        $this->view('tutoria/tutores/sesionesIndividuales/ver', [
            'sesion'    => $sesion,
            'module'    => 'tutoria',
            'pageTitle' => 'Ficha de Sesión Individual N° ' . $sesion['nro']
        ]);
        exit;
    }

    public function individualPdf($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionIndividual->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión individual no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $tutorando = $this->objEstudianteTutoria->find($sesion['id_tutoria_estudiante']);
        if ($tutorando) {
            $this->validarPermisoTutorando($tutorando);
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('IESTP Huanta - Unidad de Bienestar y Empleabilidad');
        $pdf->SetTitle('Ficha de Tutoría Individual N° ' . $sesion['nro']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();

        $rutaLogo = __DIR__ . '/../../../public/img/logo.png';

        ob_start();
        include __DIR__ . '/../../views/tutoria/tutores/sesionesIndividuales/pdf_ficha_individual.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');

        $nombreArchivo = 'Ficha_Tutoria_Individual_Nro_' . str_pad($sesion['nro'], 3, '0', STR_PAD_LEFT) . '_' . date('Ymd') . '.pdf';
        $pdf->Output($nombreArchivo, 'I');
        exit;
    }

    public function individualGuardar()
    {
        $this->verificarPermisoTutor();

        $id_te = (int)($_POST['id_tutoria_estudiante'] ?? 0);
        $id    = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (!$id_te) {
            $_SESSION['flash_error'] = "Datos de estudiante incompletos.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $tutorando = $this->objEstudianteTutoria->find($id_te);
        if (!$tutorando) {
            $_SESSION['flash_error'] = "Estudiante no encontrado en el sistema.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $this->validarPermisoTutorando($tutorando);

        if ($id) {
            $sesionExistente = $this->objSesionIndividual->find($id);
            if (!$sesionExistente || (int)$sesionExistente['id_tutoria_estudiante'] !== $id_te) {
                $_SESSION['flash_error'] = "La sesión a editar no coincide con el estudiante indicado.";
                header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
                exit;
            }
        }

        $fecha_hora = trim($_POST['fecha_hora'] ?? '');
        $motivo     = trim($_POST['motivo'] ?? '');

        if (empty($fecha_hora) || empty($motivo)) {
            $_SESSION['flash_error'] = "Por favor complete la fecha, hora y motivo de la sesión.";
            $back = $id ? "/tutoria/sesiones/individualEditar/$id" : "/tutoria/sesiones/individualNuevo/$id_te";
            header('Location: ' . BASE_URL . $back);
            exit;
        }

        try {
            $data = [
                'id'                    => $id,
                'id_tutoria_estudiante' => $id_te,
                'nro'                   => (int)($_POST['nro'] ?? 1),
                'fecha_hora'            => $fecha_hora,
                'solicitante'           => trim($_POST['solicitante'] ?? 'TUTOR'),
                'motivo'                => $motivo,
                'link_reunion'          => trim($_POST['link_reunion'] ?? ''),
                'contenidos_tratados'   => trim($_POST['contenidos_tratados'] ?? ''),
                'recomendaciones'       => trim($_POST['recomendaciones'] ?? ''),
                'compromiso_estudiante' => trim($_POST['compromiso_estudiante'] ?? ''),
                'seguimiento'           => isset($_POST['seguimiento']) ? 1 : 0,
                'fecha_prox_sesion'     => !empty($_POST['fecha_prox_sesion']) ? $_POST['fecha_prox_sesion'] : null,
                'derivacion'            => isset($_POST['derivacion']) ? 1 : 0,
                'servicio_derivacion'   => trim($_POST['servicio_derivacion'] ?? ''),
                'asistencia'            => isset($_POST['asistencia']) ? (int)$_POST['asistencia'] : 1
            ];

            $this->objSesionIndividual->guardar($data);
            $_SESSION['flash_success'] = $id
                ? "Sesión individual actualizada con éxito."
                : "Sesión individual registrada con éxito.";

            header('Location: ' . BASE_URL . '/tutoria/sesiones/individuales/' . $id_te);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al guardar la sesión: " . $e->getMessage();
            $back = $id ? "/tutoria/sesiones/individualEditar/$id" : "/tutoria/sesiones/individualNuevo/$id_te";
            header('Location: ' . BASE_URL . $back);
            exit;
        }
    }

    public function individualEliminar($id, $id_tutoria_estudiante = null)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionIndividual->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión individual no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesIndividuales');
            exit;
        }

        $tutorando = $this->objEstudianteTutoria->find($sesion['id_tutoria_estudiante']);
        if ($tutorando) {
            $this->validarPermisoTutorando($tutorando);
        }

        try {
            $this->objSesionIndividual->eliminar($id);
            $_SESSION['flash_success'] = "Sesión individual eliminada.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo eliminar la sesión individual.";
        }

        $redirect = $id_tutoria_estudiante
            ? "/tutoria/sesiones/individuales/$id_tutoria_estudiante"
            : "/tutoria/tutores/sesionesIndividuales";
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }

    /* =========================================================================
     * SESIONES GRUPALES
     * ========================================================================= */

    public function grupalNuevo($id_programacion = null)
    {
        $this->verificarPermisoTutor();

        if (empty($id_programacion)) {
            $id_programacion = $_GET['id_tutoria'] ?? null;
        }

        // Si es docente y no se pasó id_programacion, autodetectar su programación actual
        if (empty($id_programacion) && Auth::esDocenteTutoria()) {
            $id_docente = $_SESSION['sigi_user_id'] ?? 0;
            $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
            $id_sede    = $_SESSION['sigi_sede_actual'] ?? 0;
            $miProg     = $this->objProgramacion->getProgramacionPorDocente($id_docente, $id_periodo, $id_sede);
            if ($miProg) {
                $id_programacion = $miProg['id'];
            }
        }

        if (empty($id_programacion)) {
            $_SESSION['flash_error'] = "Debe especificar una programación de tutoría para registrar una sesión grupal.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($id_programacion);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $this->validarPermisoProgramacion($programacion);

        // Obtener los tutorandos asignados para la lista de asistencia
        $estudiantes = $this->objEstudianteTutoria->listarPorProgramacion($id_programacion);

        $this->view('tutoria/tutores/sesionesGrupales/nuevo', [
            'programacion' => $programacion,
            'estudiantes'  => $estudiantes,
            'isEdit'       => false,
            'module'       => 'tutoria',
            'pageTitle'    => 'Nueva Sesión Grupal'
        ]);
        exit;
    }

    public function grupalEditar($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionGrupal->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión grupal no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($sesion['id_tutoria']);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación asociada a la sesión no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $this->validarPermisoProgramacion($programacion);

        $estudiantes  = $this->objEstudianteTutoria->listarPorProgramacion($sesion['id_tutoria']);
        $asistentesGuardados = json_decode($sesion['asistentes'] ?? '[]', true) ?: [];

        $this->view('tutoria/tutores/sesionesGrupales/editar', [
            'sesion'              => $sesion,
            'programacion'        => $programacion,
            'estudiantes'         => $estudiantes,
            'asistentesGuardados' => $asistentesGuardados,
            'isEdit'              => true,
            'module'              => 'tutoria',
            'pageTitle'           => 'Editar Sesión Grupal'
        ]);
        exit;
    }

    public function grupalVer($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionGrupal->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($sesion['id_tutoria']);
        if ($programacion) {
            $this->validarPermisoProgramacion($programacion);
        }

        $estudiantes = $this->objEstudianteTutoria->listarPorProgramacion($sesion['id_tutoria']);
        $asistentesIds = json_decode($sesion['asistentes'] ?? '[]', true) ?: [];

        $this->view('tutoria/tutores/sesionesGrupales/ver', [
            'sesion'        => $sesion,
            'estudiantes'   => $estudiantes,
            'asistentesIds' => $asistentesIds,
            'module'        => 'tutoria',
            'pageTitle'     => 'Detalle de Sesión Grupal'
        ]);
        exit;
    }

    public function grupalPdf($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionGrupal->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión grupal no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($sesion['id_tutoria']);
        if ($programacion) {
            $this->validarPermisoProgramacion($programacion);
        }

        $estudiantes = $this->objEstudianteTutoria->listarPorProgramacion($sesion['id_tutoria']);
        $asistentesIds = json_decode($sesion['asistentes'] ?? '[]', true) ?: [];

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Sistema Integrado de Gestión Institucional');
        $pdf->SetAuthor('IESTP Huanta - Unidad de Bienestar y Empleabilidad');
        $pdf->SetTitle('Ficha de Tutoría Grupal N° ' . $sesion['id']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();

        $rutaLogo = __DIR__ . '/../../../public/img/logo.png';

        ob_start();
        include __DIR__ . '/../../views/tutoria/tutores/sesionesGrupales/pdf_ficha_grupal.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');

        $nombreArchivo = 'Ficha_Tutoria_Grupal_Nro_' . str_pad($sesion['id'], 3, '0', STR_PAD_LEFT) . '_' . date('Ymd') . '.pdf';
        $pdf->Output($nombreArchivo, 'I');
        exit;
    }

    public function grupalGuardar()
    {
        $this->verificarPermisoTutor();

        $id_tutoria = (int)($_POST['id_tutoria'] ?? 0);
        $id         = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (!$id_tutoria) {
            $_SESSION['flash_error'] = "Programación no especificada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($id_tutoria);
        if (!$programacion) {
            $_SESSION['flash_error'] = "Programación no encontrada en el sistema.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $this->validarPermisoProgramacion($programacion);

        if ($id) {
            $sesionExistente = $this->objSesionGrupal->find($id);
            if (!$sesionExistente || (int)$sesionExistente['id_tutoria'] !== $id_tutoria) {
                $_SESSION['flash_error'] = "La sesión a editar no coincide con la programación especificada.";
                header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
                exit;
            }
        }

        $tipo       = trim($_POST['tipo'] ?? '');
        $fecha_hora = trim($_POST['fecha_hora'] ?? '');
        $objetivo   = trim($_POST['objetivo_sesion'] ?? '');

        if (empty($tipo) || empty($fecha_hora) || empty($objetivo)) {
            $_SESSION['flash_error'] = "Complete los campos obligatorios (tipo, fecha/hora y objetivo).";
            $back = $id ? "/tutoria/sesiones/grupalEditar/$id" : "/tutoria/sesiones/grupalNuevo/$id_tutoria";
            header('Location: ' . BASE_URL . $back);
            exit;
        }

        try {
            $asistentes = $_POST['asistentes'] ?? [];
            $asistentesJson = json_encode(array_map('intval', $asistentes));

            $data = [
                'id'                   => $id,
                'id_tutoria'           => $id_tutoria,
                'tipo'                 => $tipo,
                'fecha_hora'           => $fecha_hora,
                'objetivo_sesion'      => $objetivo,
                'contenidos'           => trim($_POST['contenidos'] ?? ''),
                'link_reunion'         => trim($_POST['link_reunion'] ?? ''),
                'logro_objetivo'       => trim($_POST['logro_objetivo'] ?? 'En Proceso'),
                'asistentes'           => $asistentesJson,
                'observaciones_sesion' => trim($_POST['observaciones_sesion'] ?? ''),
                'recomendaciones'      => trim($_POST['recomendaciones'] ?? '')
            ];

            $this->objSesionGrupal->guardar($data);
            $_SESSION['flash_success'] = $id
                ? "Sesión grupal actualizada correctamente."
                : "Sesión grupal registrada correctamente.";

            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al guardar la sesión grupal: " . $e->getMessage();
            $back = $id ? "/tutoria/sesiones/grupalEditar/$id" : "/tutoria/sesiones/grupalNuevo/$id_tutoria";
            header('Location: ' . BASE_URL . $back);
            exit;
        }
    }

    public function grupalEliminar($id)
    {
        $this->verificarPermisoTutor();

        $sesion = $this->objSesionGrupal->find($id);
        if (!$sesion) {
            $_SESSION['flash_error'] = "Sesión no encontrada.";
            header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
            exit;
        }

        $programacion = $this->objProgramacion->find($sesion['id_tutoria']);
        if ($programacion) {
            $this->validarPermisoProgramacion($programacion);
        }

        try {
            $this->objSesionGrupal->eliminar($id);
            $_SESSION['flash_success'] = "Sesión grupal eliminada correctamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "No se pudo eliminar la sesión grupal.";
        }

        header('Location: ' . BASE_URL . '/tutoria/tutores/sesionesGrupales');
        exit;
    }
}
