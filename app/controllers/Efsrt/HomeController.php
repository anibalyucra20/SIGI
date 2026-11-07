<?php

namespace App\Controllers\Efsrt;

use Core\Controller;
use Core\Auth;
use Core\Model;
use App\Models\Efsrt\Practica;
use App\Models\Efsrt\Empresa;
use App\Models\Efsrt\Convenio;
use App\Models\Efsrt\Supervisor;
use App\Models\Efsrt\PeriodoConfig;
use App\Models\Efsrt\Constancia;
use PDO;

class HomeController extends Controller
{
    protected $practicaModel;
    protected $empresaModel;
    protected $convenioModel;
    protected $supervisorModel;
    protected $periodoConfigModel;
    protected $constanciaModel;

    public function __construct()
    {
        parent::__construct();

        $moduloEfsrt = 11; // ID del sistema EFSRT

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
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }

        $this->practicaModel = new Practica();
        $this->empresaModel = new Empresa();
        $this->convenioModel = new Convenio();
        $this->supervisorModel = new Supervisor();
        $this->periodoConfigModel = new PeriodoConfig();
        $this->constanciaModel = new Constancia();
    }

    public function index()
    {
        $id_user = $_SESSION['sigi_user_id'];
        $id_periodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
        $id_sede = $_SESSION['sigi_sede_actual'] ?? null;
        $db = (new Model())->getDB();

        // Obtener nombre del periodo académico actual
        $periodoActualNombre = $db->query("SELECT nombre FROM sigi_periodo_academico WHERE id = " . ((int)$id_periodo) . " LIMIT 1")->fetchColumn() ?: 'Periodo Vigente';

        $esEstudiante = Auth::esEstudianteEfsrt();
        $esDocente = Auth::esDocenteEfsrt();
        $esCoordinador = Auth::esCoordinadorPEEfsrt();
        $esSecretario = Auth::esSecretarioPEEfsrt();
        $esAdmin = Auth::esAdminEfsrt();
        $esDirector = Auth::esDirectorEfsrt();
        $esJUA = Auth::esJUAEfsrt();

        $datosDashboard = [
            'periodo_nombre'     => $periodoActualNombre,
            'esEstudiante'       => $esEstudiante,
            'esDocente'          => $esDocente,
            'esCoordinador'      => $esCoordinador,
            'esSecretario'       => $esSecretario,
            'esAdmin'            => $esAdmin,
            'esDirector'         => $esDirector,
            'esJUA'              => $esJUA,
            'totalEmpresas'      => 0,
            'totalConvenios'     => 0,
            'totalSupervisores'  => 0,
            'totalConstancias'   => 0,
            'estadisticas'       => [],
            'practicasRecientes' => [],
            'misPracticas'       => [],
            'pendientesAccion'   => []
        ];

        // 1. DASHBOARD ESTUDIANTE
        if ($esEstudiante) {
            $misPracticas = $this->practicaModel->getAll(['id_estudiante' => $id_user]);
            $datosDashboard['misPracticas'] = $misPracticas;

            // Verificar ventana de subida para el estudiante
            $cronograma = $this->periodoConfigModel->getActivo($id_periodo, $id_sede);
            $datosDashboard['cronograma'] = $cronograma;
            $datosDashboard['abiertoEstudiante'] = $this->periodoConfigModel->estaAbiertoEstudiante($id_periodo, $id_sede);
        }
        // 2. DASHBOARD DOCENTE SUPERVISOR
        elseif ($esDocente && !$esCoordinador && !$esAdmin && !$esSecretario) {
            $estadisticas = $this->practicaModel->getEstadisticas($id_periodo, null, $id_user);
            $pendientes = $this->practicaModel->getAll([
                'id_periodo' => $id_periodo,
                'id_docente' => $id_user,
                'estado'     => ['DOCUMENTACION_SUBIDA', 'REGISTRADO', 'OBSERVADO_SUPERVISOR']
            ]);

            $datosDashboard['estadisticas'] = $estadisticas;
            $datosDashboard['pendientesAccion'] = $pendientes;
            $datosDashboard['abiertoSupervisor'] = $this->periodoConfigModel->estaAbiertoSupervisor($id_periodo, $id_sede);
        }
        // 3. DASHBOARD COORDINADOR DE PROGRAMA
        elseif ($esCoordinador && !$esAdmin && !$esSecretario && !$esDirector && !$esJUA) {
            $stmtProg = $db->prepare("SELECT id_programa_estudio FROM sigi_coordinador_pe_periodo WHERE id_usuario = ? AND id_periodo = ?");
            $stmtProg->execute([$id_user, $id_periodo]);
            $progsCoord = $stmtProg->fetchAll(PDO::FETCH_COLUMN) ?: [-1];

            $estadisticas = $this->practicaModel->getEstadisticas($id_periodo, null, null, null, $progsCoord);
            $pendientes = $this->practicaModel->getAll([
                'id_periodo'   => $id_periodo,
                'programas_in' => $progsCoord,
                'estado'       => ['DERIVADO_COORDINADOR', 'APROBADO_SUPERVISOR']
            ]);

            $datosDashboard['estadisticas'] = $estadisticas;
            $datosDashboard['pendientesAccion'] = $pendientes;
            $datosDashboard['totalEmpresas'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_empresas WHERE estado = 1")->fetchColumn();
            $datosDashboard['totalConvenios'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_convenios WHERE estado = 'ACTIVO'")->fetchColumn();
        }
        // 4. DASHBOARD GENERAL (SECRETARIO, ADMIN, DIRECTOR, JUA)
        else {
            $filtroProg = isset($_GET['id_programa']) ? (int)$_GET['id_programa'] : null;
            $estadisticas = $this->practicaModel->getEstadisticas($id_periodo, $filtroProg);

            $pendientes = $this->practicaModel->getAll([
                'id_periodo'  => $id_periodo,
                'id_programa' => $filtroProg,
                'estado'      => ['DERIVADO_SECRETARIA', 'APROBADO_COORDINADOR']
            ]);

            $datosDashboard['estadisticas'] = $estadisticas;
            $datosDashboard['pendientesAccion'] = $pendientes;
            $datosDashboard['totalEmpresas'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_empresas WHERE estado = 1")->fetchColumn();
            $datosDashboard['totalConvenios'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_convenios WHERE estado = 'ACTIVO'")->fetchColumn();
            $datosDashboard['totalSupervisores'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_supervisores WHERE id_periodo_academico = " . ((int)$id_periodo) . " AND estado = 1")->fetchColumn();
            $datosDashboard['totalConstancias'] = (int)$db->query("SELECT COUNT(*) FROM efsrt_constancias WHERE estado = 'EMITIDA'")->fetchColumn();

            $programas = $db->query("SELECT id, nombre, codigo FROM sigi_programa_estudios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
            $datosDashboard['programas'] = $programas;
            $datosDashboard['filtroPrograma'] = $filtroProg;
        }

        $this->view('efsrt/index', array_merge($datosDashboard, [
            'pageTitle' => 'Panel de Control - Módulo EFSRT',
            'module'    => 'efsrt'
        ]));
    }
}
