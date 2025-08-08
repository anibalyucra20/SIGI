<?php

namespace App\Controllers\Sigi;

use Core\Controller;

// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Rol.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/PeriodoAcademico.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Permiso.php';

use App\Models\Sigi\Docente;
use App\Models\Sigi\Rol;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Sistema;
use App\Models\Sigi\Permiso;

class DocentesController extends Controller
{
    protected $model;
    protected $objRol;
    protected $objSede;
    protected $objPrograma;
    protected $objPeriodoAcademico;
    protected $objSistema;
    protected $objPermiso;

    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::tieneRolEnSigi()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $this->model = new Docente();
        $this->objRol = new Rol();
        $this->objSede = new Sedes();
        $this->objPeriodoAcademico = new PeriodoAcademico();
        $this->objPrograma = new Programa();
        $this->objSistema = new Sistema();
        $this->objPermiso = new Permiso();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $docentes = $this->model->getAllDocente();
        endif;
        $this->view('sigi/docentes/index', ['docentes' => $docentes]);
        exit;
    }


    public function nuevo()
    {
        if (\Core\Auth::esAdminSigi()):
            $roles   = $this->objRol->getRolesDocente();
            $sedes   = $this->objSede->getSedes();
            $periodos = $this->objPeriodoAcademico->getPeriodos();
            $programas = $this->objPrograma->getAll();
        endif;
        $this->view('sigi/docentes/nuevo', [
            'roles'     => $roles,
            'sedes'     => $sedes,
            'periodos'  => $periodos,
            'programas' => $programas,
            'module'    => 'sigi',
            'pageTitle' => 'Nuevo Docente'
        ]);
        exit;
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            \Core\Auth::start();
            $idPeriodo = $_SESSION['sigi_periodo_actual_id'] ?? null;
            $data = [
                'dni'                 => $_POST['dni'],
                'apellidos_nombres'   => $_POST['apellidos_nombres'],
                'correo'              => $_POST['correo'],
                'discapacidad'        => $_POST['discapacidad'],
                'genero'              => $_POST['genero'],
                'fecha_nacimiento'    => $_POST['fecha_nacimiento'],
                'direccion'           => $_POST['direccion'],
                'telefono'            => $_POST['telefono'],
                'id_rol'              => $_POST['id_rol'],
                'id_sede'             => $_POST['id_sede'],
                'id_programa_estudios' => $_POST['id_programa_registro'],
                'id_periodo_registro' => $idPeriodo,
            ];

            $errores = $this->model->validar($data);

            if ($errores) {
                $roles = $this->objRol->getRolesDocente();
                $sedes = $this->objSede->getSedes();
                $periodos = $this->objPeriodoAcademico->getPeriodos();
                $programas = $this->objPrograma->getAll();
                $this->view('sigi/docentes/nuevo', [
                    'roles'     => $roles,
                    'sedes'     => $sedes,
                    'periodos'  => $periodos,
                    'programas' => $programas,
                    'errores'   => $errores,
                    'docente'   => $data,
                    'module'    => 'sigi',
                    'pageTitle' => 'Nuevo Docente'
                ]);
                return;
            }

            // Si pasa la validación, genera y encripta la contraseña
            $password = bin2hex(random_bytes(5));
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            $data['reset_password'] = 0;
            $data['token_password'] = '';

            $this->model->nuevo($data);
            // Guardar contraseña en sesión temporal para mostrarla una sola vez
            $_SESSION['flash_success'] = "Docente registrado correctamente. La contraseña generada es: <strong>{$password}</strong>";
        endif;
        header('Location: ' . BASE_URL . '/sigi/docentes');
        exit;
    }

    // EDITAR
    public function editar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $docente = $this->model->find($id);
        endif;
        $this->view('sigi/docentes/editar', [
            'docente'   => $docente,
            'roles'     => $this->objRol->getRolesDocente(),
            'sedes'     => $this->objSede->getSedes(),
            'periodos'  => $this->objPeriodoAcademico->getPeriodos(),
            'programas' => $this->objPrograma->getAll(),
            'sistemas' => $this->objSistema->getSistemas(),
            'permisos' => $this->objPermiso->getPermisos($id),
            'isEdit'    => true,
            'module'    => 'sigi',
            'pageTitle' => 'Editar Docente'
        ]);
        exit;
    }

    public function actualizar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            \Core\Auth::start();
            $data = [
                'id'                  => $id,
                'dni'                  => $_POST['dni'],
                'apellidos_nombres'    => $_POST['apellidos_nombres'],
                'correo'               => $_POST['correo'],
                'discapacidad'         => $_POST['discapacidad'],
                'genero'               => $_POST['genero'],
                'fecha_nacimiento'     => $_POST['fecha_nacimiento'],
                'direccion'            => $_POST['direccion'],
                'telefono'             => $_POST['telefono'],
                'estado'               => $_POST['estado'],
                'id_rol'               => $_POST['id_rol'],
                'id_sede'              => $_POST['id_sede'],
                'id_programa_estudios' => $_POST['id_programa_registro']
            ];
            $errores = $this->model->validar($data, true, $id);

            if ($errores) {
                $this->view('sigi/docentes/editar', [
                    'errores'   => $errores,
                    'docente'   => $data,
                    'roles'     => $this->objRol->getRolesDocente(),
                    'sedes'     => $this->objSede->getSedes(),
                    'programas' => $this->objPrograma->getAll(),
                    'module'    => 'sigi',
                    'pageTitle' => 'Editar Docente',
                    'isEdit'    => true
                ]);
                return;
            }
            $model = new Docente();
            $model->update($id, $data);
            $_SESSION['flash_success'] = "Docente actualizado correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/docentes');
        exit;
    }


    public function ver($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $docente = $this->model->find($id);
            $permisos = $this->model->obtenerPermisos($id);
        endif;
        $this->view('sigi/docentes/ver', [
            'docente' => $docente,
            'permisos' => $permisos,
            'module'  => 'sigi',
            'pageTitle' => 'Detalles del Docente'
        ]);
        exit;
    }
    public function data()
    {
        if (\Core\Auth::esAdminSigi()):
            header('Content-Type: application/json; charset=utf-8');
            // variables estándar de DataTables
            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            // filtros personalizados
            $filters = [
                'dni'     => $_GET['filter_dni']     ?? '',
                'nombres' => $_GET['filter_nombres'] ?? '',
                'estado'  => $_GET['filter_estado']  ?? ''
            ];

            $model = new Docente();
            $result   = $model->getPaginated($filters, (int)$length, (int)$start, $orderCol, $orderDir);

            // respuesta JSON
            echo json_encode([
                'draw'            => (int)$draw,
                'recordsTotal'    => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data'            => $result['data']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        endif;
        exit;
    }
    public function editar_permisos($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $sistemas = $this->objSistema->getSistemas();
            $permisos = $this->objPermiso->getPermisos($id);
            $roles = $this->objRol->getRolesDocente();
        endif;
        $this->view('sigi/permisos/editar', [
            'id_usuario' => $id,
            'sistemas'   => $sistemas,
            'permisos'   => $permisos,
            'roles'      => $roles
        ]);
        exit;
    }
    public function permisos($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $docente = $this->model->find($id);
            $sistemas = $this->objSistema->getSistemas();
            $roles = $this->objRol->getRolesDocente();
            $permisos = $this->model->getPermisosUsuario($id);
        endif;
        $this->view('sigi/docentes/permisos', [
            'docente'   => $docente,
            'roles'     => $roles,
            'sistemas' => $sistemas,
            'permisos' => $permisos,
            'isEdit'    => true,
            'module'    => 'sigi',
            'pageTitle' => 'Editar Docente'
        ]);
        exit;
    }

    public function guardarPermisos()
    {
        if (\Core\Auth::esAdminSigi()):
            \Core\Auth::start();
            $id_usuario = $_POST['id_usuario'];
            $permisos = $_POST['permisos'] ?? [];

            // ¡No necesitas parsear aquí! El modelo lo hace.
            $this->model->actualizarPermisos($id_usuario, $permisos);

            $_SESSION['flash_success'] = "Permisos actualizados correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/docentes');
        exit;
    }
}
