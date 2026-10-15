<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\Practica;
use App\Models\Efsrt\Empresa;
use App\Models\Efsrt\Convenio;
use App\Models\Efsrt\Supervisor;
use App\Models\Efsrt\Documento;
use App\Models\Efsrt\PeriodoConfig;
use App\Models\Efsrt\HistorialDerivacion;
use App\Models\Efsrt\Constancia;
use PDO;

class PracticasController extends Controller
{
    protected $practicaModel;
    protected $empresaModel;
    protected $convenioModel;
    protected $supervisorModel;
    protected $documentoModel;
    protected $periodoConfigModel;
    protected $historialModel;
    protected $constanciaModel;

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
        $this->empresaModel = new Empresa();
        $this->convenioModel = new Convenio();
        $this->supervisorModel = new Supervisor();
        $this->documentoModel = new Documento();
        $this->periodoConfigModel = new PeriodoConfig();
        $this->historialModel = new HistorialDerivacion();
        $this->constanciaModel = new Constancia();
    }

    public function index()
    {
        $id_user = $_SESSION['sigi_user_id'];
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $id_sede = $_SESSION['sigi_sede_actual'] ?? null;
        $db = (new Model())->getDB();

        $filters = [];
        $filtroPeriodo = isset($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : $id_periodo;
        if ($filtroPeriodo) {
            $filters['id_periodo'] = $filtroPeriodo;
        }

        if (!empty($_GET['id_programa'])) {
            $filters['id_programa'] = (int)$_GET['id_programa'];
        }

        if (!empty($_GET['estado'])) {
            $filters['estado'] = trim($_GET['estado']);
        }

        if (!empty($_GET['q'])) {
            $filters['busqueda'] = trim($_GET['q']);
        }

        $esEstudiante = Auth::esEstudianteEfsrt();
        $esDocente = Auth::esDocenteEfsrt();
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esAdminOSecretario = Auth::esAdminEfsrt() || Auth::esSecretarioPEEfsrt() || Auth::esDirectorEfsrt() || Auth::esJUAEfsrt();

        if ($esEstudiante) {
            $filters['id_estudiante'] = $id_user;
        } elseif ($esDocente && !$esCoordinador && !$esAdminOSecretario) {
            $filters['id_docente'] = $id_user;
        } elseif ($esCoordinador && !$esAdminOSecretario) {
            // Programas asignados al coordinador
            $stmtProg = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$id_user, $filtroPeriodo]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($progsCoord)) {
                $filters['programas_in'] = $progsCoord;
            } else {
                $filters['programas_in'] = [-1]; // Sin acceso si no tiene asignados
            }
        }

        $practicas = $this->practicaModel->getAll($filters);

        $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
        $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        if ($esCoordinador && !$esAdminOSecretario) {
            $stmtProgList = $db->prepare("SELECT p.id, p.nombre, p.codigo 
                                          FROM sigi_programa_estudios p 
                                          INNER JOIN sigi_coordinador_pe_periodo cp ON cp.id_programa_estudio = p.id 
                                          WHERE cp.id_usuario = ? AND cp.id_periodo = ? 
                                          ORDER BY p.nombre ASC");
            $stmtProgList->execute([$id_user, $filtroPeriodo]);
            $programas = $stmtProgList->fetchAll(PDO::FETCH_ASSOC);
        }

        $puedeCrear = ($esEstudiante || $esCoordinador || $esAdminOSecretario);

        $this->view('efsrt/practicas/index', [
            'module'             => 'efsrt',
            'pageTitle'          => 'Gestión de Prácticas Pre-Profesionales (EFSRT)',
            'practicas'          => $practicas,
            'periodos'           => $periodos,
            'programas'          => $programas,
            'filtroPeriodo'      => $filtroPeriodo,
            'filtroPrograma'     => $_GET['id_programa'] ?? null,
            'filtroEstado'       => $_GET['estado'] ?? null,
            'busqueda'           => $_GET['q'] ?? null,
            'esEstudiante'       => $esEstudiante,
            'esDocente'          => $esDocente,
            'esCoordinador'      => $esCoordinador,
            'esAdminOSecretario' => $esAdminOSecretario,
            'puedeCrear'         => $puedeCrear
        ]);
    }

    public function crear()
    {
        $id_user = $_SESSION['sigi_user_id'];
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $id_sede = $_SESSION['sigi_sede_actual'] ?? null;
        $db = (new Model())->getDB();

        $esEstudiante = Auth::esEstudianteEfsrt();
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esAdminOSecretario = Auth::esAdminEfsrt() || Auth::esSecretarioPEEfsrt() || Auth::esDirectorEfsrt() || Auth::esJUAEfsrt();

        if (!$esEstudiante && !$esCoordinador && !$esAdminOSecretario) {
            $_SESSION['flash_error'] = "No tienes permisos para registrar expedientes de prácticas.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $estudianteInfo = null;
        $programasDisponibles = [];
        $planesDisponibles = [];
        $modulosDisponibles = [];

        if ($esEstudiante) {
            // Obtener el programa de estudios y plan del estudiante
            $stmtEst = $db->prepare("SELECT e.*, ep.id_plan_estudio, ple.id_programa_estudios, p.nombre AS programa_nombre, p.codigo AS programa_codigo
                                     FROM sigi_usuarios e
                                     INNER JOIN acad_estudiante_programa ep ON e.id = ep.id_usuario
                                     INNER JOIN sigi_planes_estudio ple ON ep.id_plan_estudio = ple.id
                                     INNER JOIN sigi_programa_estudios p ON ple.id_programa_estudios = p.id
                                     WHERE e.id = ? LIMIT 1");
            $stmtEst->execute([$id_user]);
            $estudianteInfo = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if ($estudianteInfo) {
                $estudianteInfo['apellidos_nombres'] = Auth::formatearNombreUsuario($estudianteInfo['apellidos_nombres']);
                $stmtPlanes = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY nombre DESC");
                $stmtPlanes->execute([$estudianteInfo['id_programa_estudios']]);
                $planesDisponibles = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);

                $planIdActual = $estudianteInfo['id_plan_estudio'] ?? (!empty($planesDisponibles) ? $planesDisponibles[0]['id'] : 0);
                if ($planIdActual) {
                    $stmtMod = $db->prepare("SELECT id, descripcion AS nombre, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
                    $stmtMod->execute([$planIdActual]);
                    $modulosDisponibles = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } elseif ($esCoordinador && !$esAdminOSecretario) {
            $stmtProg = $db->prepare("SELECT p.id, p.nombre, p.codigo 
                                      FROM sigi_programa_estudios p 
                                      INNER JOIN sigi_coordinador_pe_periodo cp ON cp.id_programa_estudio = p.id 
                                      WHERE cp.id_usuario = ? AND cp.id_periodo = ? 
                                      ORDER BY p.nombre ASC");
            $stmtProg->execute([$id_user, $id_periodo]);
            $programasDisponibles = $stmtProg->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $programasDisponibles = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $empresas = $this->empresaModel->getAll(null, 1);
        $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('efsrt/practicas/crear', [
            'module'               => 'efsrt',
            'pageTitle'            => 'Registrar Expediente de EFSRT',
            'esEstudiante'         => $esEstudiante,
            'estudianteInfo'       => $estudianteInfo,
            'planesDisponibles'    => $planesDisponibles,
            'modulosDisponibles'   => $modulosDisponibles,
            'programasDisponibles' => $programasDisponibles,
            'empresas'             => $empresas,
            'periodos'             => $periodos,
            'id_periodo_actual'    => $id_periodo
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_user = $_SESSION['sigi_user_id'];
        $esEstudiante = Auth::esEstudianteEfsrt();

        $id_estudiante = $esEstudiante ? $id_user : (int)($_POST['id_estudiante'] ?? 0);
        $id_periodo = (int)($_POST['id_periodo_academico'] ?? 0);
        $id_programa = (int)($_POST['id_programa_estudios'] ?? 0);
        $id_plan = (int)($_POST['id_plan_estudio'] ?? 0);
        $id_modulo = (int)($_POST['id_modulo_formativo'] ?? 0);
        $id_empresa = (int)($_POST['id_empresa'] ?? 0);
        $id_convenio = !empty($_POST['id_convenio']) ? (int)$_POST['id_convenio'] : null;
        $id_supervisor = !empty($_POST['id_supervisor']) ? (int)$_POST['id_supervisor'] : null;
        $area_desempeno = trim($_POST['area_desempeno'] ?? '');
        $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
        $fecha_fin = trim($_POST['fecha_fin'] ?? '');
        $horas_requeridas = !empty($_POST['horas_requeridas']) ? (int)$_POST['horas_requeridas'] : 240;

        if (empty($id_estudiante) || empty($id_periodo) || empty($id_programa) || empty($id_modulo) || empty($id_empresa) || empty($area_desempeno) || empty($fecha_inicio) || empty($fecha_fin)) {
            $_SESSION['flash_error'] = "Por favor complete todos los campos obligatorios del registro.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/crear');
            exit;
        }

        // Validar si ya existe registro para este estudiante y módulo formativo
        $existe = $this->practicaModel->verificarExistePractica($id_estudiante, $id_modulo);
        if ($existe) {
            $_SESSION['flash_error'] = "Ya existe un registro de EFSRT para este estudiante en el módulo seleccionado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        // Si es estudiante y no especificó supervisor, intentar asignar automáticamente el supervisor activo de su programa y periodo
        if (empty($id_supervisor)) {
            $db = (new Model())->getDB();
            $stmtSup = $db->prepare("SELECT id FROM efsrt_supervisores WHERE id_periodo_academico = ? AND id_programa_estudios = ? AND estado = 1 LIMIT 1");
            $stmtSup->execute([$id_periodo, $id_programa]);
            $id_supervisor = $stmtSup->fetchColumn() ?: null;
        }

        $datos = [
            'id_estudiante'        => $id_estudiante,
            'id_periodo_academico' => $id_periodo,
            'id_programa_estudios' => $id_programa,
            'id_plan_estudio'      => $id_plan,
            'id_modulo_formativo'  => $id_modulo,
            'id_empresa'           => $id_empresa,
            'id_convenio'          => $id_convenio,
            'id_supervisor'        => $id_supervisor,
            'area_desempeno'       => $area_desempeno,
            'fecha_inicio'         => $fecha_inicio,
            'fecha_fin'            => $fecha_fin,
            'horas_requeridas'     => $horas_requeridas,
            'horas_convalidadas'   => 0,
            'estado'               => 'REGISTRADO'
        ];

        try {
            $id_practica = $this->practicaModel->create($datos);

            // Registrar en historial
            $this->historialModel->registrar([
                'id_practica'       => $id_practica,
                'etapa_origen'      => $esEstudiante ? 'ESTUDIANTE' : 'COORDINADOR',
                'etapa_destino'     => 'SUPERVISOR',
                'accion'            => 'ENVIO',
                'id_usuario_accion' => $id_user,
                'comentarios'       => 'Registro y apertura de expediente de EFSRT.'
            ]);

            $_SESSION['flash_success'] = "Expediente de EFSRT registrado exitosamente.";
            header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al registrar práctica: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/efsrt/practicas/crear');
            exit;
        }
    }

    public function detalle($id)
    {
        $id = (int)$id;
        $practica = $this->practicaModel->getById($id);

        if (!$practica) {
            $_SESSION['flash_error'] = "Expediente de EFSRT no encontrado.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_user = $_SESSION['sigi_user_id'];
        $esEstudiante = Auth::esEstudianteEfsrt();
        $esDocente = Auth::esDocenteEfsrt();
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esSecretario = Auth::esSecretarioPEEfsrt();
        $esAdmin = Auth::esAdminEfsrt();
        $esDirector = Auth::esDirectorEfsrt();
        $esJUA = Auth::esJUAEfsrt();

        // Control de permisos de acceso al expediente
        if ($esEstudiante && $practica['id_estudiante'] != $id_user) {
            $_SESSION['flash_error'] = "No tienes permiso para ver este expediente.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $esSupervisorAsignado = ($practica['supervisor_docente_id'] == $id_user);

        if ($esDocente && !$esCoordinador && !$esAdmin && !$esDirector && !$esJUA && !$esSecretario) {
            if (!$esSupervisorAsignado) {
                $_SESSION['flash_error'] = "No tienes permiso para ver expedientes no asignados a tu supervisión.";
                header('Location: ' . BASE_URL . '/efsrt/practicas');
                exit;
            }
        }

        $esCoordinadorDeEstePrograma = false;
        if ($esCoordinador) {
            $stmtProg = (new Model())->getDB()->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$id_user, $practica['id_periodo_academico']]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN);
            $esCoordinadorDeEstePrograma = in_array($practica['id_programa_estudios'], $progsCoord);

            if (!$esAdmin && !$esDirector && !$esJUA && !$esSecretario && !$esSupervisorAsignado && !$esCoordinadorDeEstePrograma) {
                $_SESSION['flash_error'] = "No tienes permiso para ver expedientes de programas no asignados a tu coordinación.";
                header('Location: ' . BASE_URL . '/efsrt/practicas');
                exit;
            }
        }

        // Obtener documentos de la práctica
        $documentos = $this->documentoModel->getByPractica($id);
        $documentosMap = [];
        foreach ($documentos as $d) {
            $documentosMap[$d['tipo_documento']] = $d;
        }

        // Historial de derivaciones
        $historial = $this->historialModel->getByPractica($id);

        // Constancia si existe
        $constancia = $this->constanciaModel->getByPractica($id);

        // Verificar ventanas de subida según el cronograma
        $id_periodo = $practica['id_periodo_academico'];
        $id_sede = !empty($practica['estudiante_sede_id']) ? (int)$practica['estudiante_sede_id'] : ($_SESSION['sigi_sede_actual'] ?? null);
        $id_programa = $practica['id_programa_estudios'];

        $abiertoEstudiante = $this->periodoConfigModel->estaAbiertoEstudiante($id_periodo, $id_sede, $id_programa);
        $abiertoSupervisor = $this->periodoConfigModel->estaAbiertoSupervisor($id_periodo, $id_sede, $id_programa);
        $cronogramaVigente = $this->periodoConfigModel->getConfigVigente($id_periodo, $id_sede, $id_programa);

        // Supervisores disponibles para asignación/cambio
        $supervisoresDisponibles = $this->supervisorModel->getAll($id_periodo, $id_programa, $id_sede);

        // Evaluación de completitud de expediente
        $estadoExpediente = $this->documentoModel->estadoExpedienteCompleto($id);

        $this->view('efsrt/practicas/detalle', [
            'module'                  => 'efsrt',
            'pageTitle'               => 'Expediente EFSRT - ' . $practica['estudiante_nombre'],
            'practica'                => $practica,
            'documentos'              => $documentos,
            'documentosMap'           => $documentosMap,
            'historial'               => $historial,
            'constancia'              => $constancia,
            'abiertoEstudiante'       => $abiertoEstudiante,
            'abiertoSupervisor'       => $abiertoSupervisor,
            'cronogramaVigente'       => $cronogramaVigente,
            'supervisoresDisponibles' => $supervisoresDisponibles,
            'estadoExpediente'        => $estadoExpediente,
            'esEstudiante'            => $esEstudiante,
            'esDocente'               => $esDocente,
            'esSupervisor'            => $esSupervisorAsignado,
            'esCoordinador'           => ($esCoordinadorDeEstePrograma || $esAdmin),
            'esSecretario'            => ($esSecretario || $esAdmin),
            'esAdmin'                 => $esAdmin,
            'esDirector'              => $esDirector,
            'esJUA'                   => $esJUA
        ]);
    }

    public function asignarSupervisor()
    {
        if (!Auth::esAdminEfsrt() && !Auth::esSecretarioPEEfsrt() && !Auth::esCoordinadorPEEfsrt()) {
            $_SESSION['flash_error'] = "No tienes permisos para asignar supervisor.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $id_practica = (int)($_POST['id_practica'] ?? 0);
        $id_supervisor = (int)($_POST['id_supervisor'] ?? 0);

        if (!$id_practica || !$id_supervisor) {
            $_SESSION['flash_error'] = "Datos insuficientes para la asignación.";
            header('Location: ' . BASE_URL . '/efsrt/practicas');
            exit;
        }

        $this->practicaModel->asignarSupervisor($id_practica, $id_supervisor);

        // Registrar en historial
        $this->historialModel->registrar([
            'id_practica'       => $id_practica,
            'etapa_origen'      => 'COORDINADOR',
            'etapa_destino'     => 'SUPERVISOR',
            'accion'            => 'ENVIO',
            'id_usuario_accion' => $_SESSION['sigi_user_id'],
            'comentarios'       => 'Docente supervisor asignado o actualizado.'
        ]);

        $_SESSION['flash_success'] = "Docente supervisor asignado exitosamente a la práctica.";
        header('Location: ' . BASE_URL . '/efsrt/practicas/detalle/' . $id_practica);
        exit;
    }

    public function buscarEstudiantesAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $id_programa = isset($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;

        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        $sede = $_SESSION['sigi_sede_actual'] ?? 0;
        $db = (new Model())->getDB();

        $sql = "SELECT u.id, u.dni, u.apellidos_nombres, 
                       ep.id_plan_estudio, ple.nombre AS plan_nombre,
                       ple.id_programa_estudios, p.nombre AS programa_nombre, p.codigo AS programa_codigo
                FROM sigi_usuarios u
                INNER JOIN acad_estudiante_programa ep ON u.id = ep.id_usuario
                INNER JOIN sigi_planes_estudio ple ON ep.id_plan_estudio = ple.id
                INNER JOIN sigi_programa_estudios p ON ple.id_programa_estudios = p.id
                WHERE u.id_rol = 7 
                  AND u.estado = 1 
                  AND (u.dni LIKE ? OR u.apellidos_nombres LIKE ?) ";

        $params = ["%$q%", "%$q%"];

        if (!empty($sede)) {
            $sql .= " AND (u.id_sede = ? OR u.id_sede IS NULL) ";
            $params[] = (int)$sede;
        }

        if ($id_programa) {
            $sql .= " AND ple.id_programa_estudios = ? ";
            $params[] = (int)$id_programa;
        }

        // Si es coordinador y no admin, filtrar por sus programas asignados
        if (Auth::esCoordinadorPEEfsrt() && !Auth::esAdminEfsrt()) {
            $user_id = $_SESSION['sigi_user_id'];
            $periodo = $_SESSION['sigi_periodo_actual_id'] ?? 1;
            $stmtCoord = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtCoord->execute([$user_id, $periodo]);
            $coordProgs = $stmtCoord->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($coordProgs)) {
                $inPl = implode(',', array_fill(0, count($coordProgs), '?'));
                $sql .= " AND ple.id_programa_estudios IN ($inPl) ";
                foreach ($coordProgs as $cp) {
                    $params[] = (int)$cp;
                }
            } else {
                $sql .= " AND 1=0 ";
            }
        }

        $sql .= " GROUP BY u.id LIMIT 15";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($estudiantes as &$est) {
            $est['apellidos_nombres'] = Auth::formatearNombreUsuario($est['apellidos_nombres']);
        }

        echo json_encode($estudiantes, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getModulosPorPlanAjax($id_plan)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = (new Model())->getDB();
        $stmt = $db->prepare("SELECT id, descripcion AS nombre, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
        $stmt->execute([(int)$id_plan]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getPlanesYModulosPorProgramaAjax($id_programa)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id_programa = (int)$id_programa;
        $db = (new Model())->getDB();

        $stmtPlanes = $db->prepare("SELECT id, nombre FROM sigi_planes_estudio WHERE id_programa_estudios = ? ORDER BY id DESC");
        $stmtPlanes->execute([$id_programa]);
        $planes = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);

        $modulos = [];
        if (!empty($planes)) {
            $planId = $planes[0]['id'];
            $stmtMod = $db->prepare("SELECT id, descripcion AS nombre, nro_modulo FROM sigi_modulo_formativo WHERE id_plan_estudio = ? ORDER BY nro_modulo ASC");
            $stmtMod->execute([$planId]);
            $modulos = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'planes' => $planes,
            'modulos' => $modulos
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
