<?php

namespace App\Controllers\Intranet;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Rol.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Permiso.php';
// --- INTEGRACIÓN MOODLE: Incluir Helper ---
require_once __DIR__ . '/../../../app/helpers/Integrator.php';


use App\Models\Sigi\Docente;
use App\Models\Sigi\Rol;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Permiso;
// Usar el Integrador
use App\Helpers\Integrator;

class PerfilController extends Controller
{
    protected $objDocente;
    protected $objRol;
    protected $objSede;
    protected $objPeriodoAcademico;
    protected $objPrograma;
    protected $objPermiso;
    protected $objIntegrator;
    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::user()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $this->objDocente = new Docente();
        $this->objRol = new Rol();
        $this->objSede = new Sedes();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objPrograma = new Programa();
        $this->objPermiso = new Permiso();
        $this->objIntegrator = new Integrator();
    }
    public function index()
    {
        if (\Core\Auth::user()):
            $id_user = $_SESSION['sigi_user_id'];
            $usuario = $this->objDocente->find($id_user);
            if ($usuario['id_rol'] != 7) {
                $usuario['tipo_usuario'] = 'docente';
            } else {
                $usuario['tipo_usuario']  = 'estudiante';
            }

            $roles   = $this->objRol->getRolesDocente();
            $sedes   = $this->objSede->getSedes();
            $periodos = $this->objPeriodoAcademico->getPeriodos();
            $programas = $this->objPrograma->getAll();
            $permisos = $this->objDocente->obtenerPermisos($id_user);
        endif;
        $this->view('intranet/perfil/index', [
            'usuario' => $usuario,
            'permisos'  => $permisos,
            'roles'     => $this->objRol->getRolesDocente(),
            'sedes'     => $this->objSede->getSedes(),
            'programas' => $this->objPrograma->getAll(),
            'isEdit'    => false,
            'module' => 'intranet',
            'pageTitle' => 'Perfil'
        ]);
        exit;
    }
    // Guardar los datos (POST)
    public function guardar()
    {
        if (\Core\Auth::user()):

            $id_user = $_SESSION['sigi_user_id'];
            // Validar contra ids válidos
            $data = [
                'correo_personal'  => $_POST['correo_personal'] ?? '',
            ];

            // Validaciones rápidas de campos requeridos (puedes extender)
            foreach (
                [
                    'correo_personal',
                ] as $campo
            ) {
                if (empty($data[$campo])) {
                    $_SESSION['flash_error'] = "Todos los campos son obligatorios.";
                    header('Location: ' . BASE_URL . '/intranet/perfil/index');
                    exit;
                }
            }
            $this->objDocente->updateCorreo($id_user, $data['correo_personal']);
            $_SESSION['flash_success'] = "Correo personal actualizado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/intranet/perfil/index');
        exit;
    }
}
