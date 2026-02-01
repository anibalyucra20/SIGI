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
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Permiso.php';

// --- INTEGRACIÓN MOODLE: Incluir Helper ---
require_once __DIR__ . '/../../../app/helpers/Integrator.php';

use App\Models\Sigi\Docente;
use App\Models\Sigi\Rol;
use App\Models\Sigi\Sedes;
use App\Models\Sigi\PeriodoAcademico;
use App\Models\Sigi\Programa;
use App\Models\Sigi\Sistema;
use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Permiso;
// Usar el Integrador
use App\Helpers\Integrator;

class DocentesController extends Controller
{
    protected $model;
    protected $objRol;
    protected $objSede;
    protected $objPrograma;
    protected $objPeriodoAcademico;
    protected $objSistema;
    protected $objDatosSistema;
    protected $objPermiso;
    protected $objIntegrator;

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
        $this->objDatosSistema = new DatosSistema();
        $this->objPermiso = new Permiso();
        $this->objIntegrator = new Integrator();
    }

    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $docentes = $this->model->getAllDocente();

            //para actualizar permisos de docente
            foreach ($docentes as $docente) {
                //var_dump($docente['id']);
                $this->registrar_permiso_inicial($docente['id']);
            }
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
                'apellidos_nombres'   => $_POST['ApellidoPaterno'] . '_' . $_POST['ApellidoMaterno'] . '_' . $_POST['Nombres'],
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
                'distrito_nacimiento' => '',
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
            // VARIABLES CRÍTICAS PARA MOODLE:
            $parteAleatoria = bin2hex(random_bytes(6)); // Esta es la que enviaremos a Moodle
            $passwordPlano = 'Sigi.' . $parteAleatoria;
            $data['password'] = password_hash($passwordPlano, PASSWORD_DEFAULT); // Hash para SIGI
            $data['reset_password'] = 0;
            $data['token_password'] = '';
            $data['estado'] = 1;

            $id_docente = $this->model->nuevo($data);
            // Guardar contraseña en sesión temporal para mostrarla una sola vez
            if ($id_docente > 0) {
                $_SESSION['flash_success'] = " Docente registrado correctamente. La contraseña generada es: <strong>{$passwordPlano}</strong>";
                $this->registrar_permiso_inicial($id_docente);
                // Separar Nombres y Apellidos (Heurística simple para Perú)
                $firstname = $_POST['Nombres'];
                $lastname = $_POST['ApellidoPaterno'] . ' ' . $_POST['ApellidoMaterno'];
                $estado = 1;
                // =======================================================
                // INICIO INTEGRACIÓN 
                // =======================================================
                if (INTEGRACIONES_SYNC_ACTIVE) {
                    try {
                        $programa_est =  $this->objPrograma->find($data['id_programa_estudios']);
                        $nombre_programa = $programa_est['nombre'];
                        $rol = $this->objRol->find($data['id_rol']);
                        $tipo_usuario = $rol['nombre'];

                        //peticion curl a API
                        $usuario = [
                            'id' => $id_docente,
                            'dni' => $data['dni'],
                            'nombres' => $firstname,
                            'apellidos' => $lastname,
                            'passwordPlano' => $passwordPlano,
                            'programa_estudios' => $nombre_programa,
                            'tipo_usuario' => $tipo_usuario,
                            'estado' => $estado
                        ];
                        $response = $this->objIntegrator->sincronizarUsuarios($usuario);
                        if ($response['data']['moodle']['message_success']) {
                            //actualizar usuarioen sigi
                            $this->model->updateUserMoodleId($id_docente, $response['data']['moodle']['id']);
                            $_SESSION['flash_success'] .= $response['data']['moodle']['message_success'];
                        } else {
                            $_SESSION['flash_error'] .= $response['data']['moodle']['message_error'];
                        }
                        if ($response['data']['microsoft']['success']) {
                            //actualizar usuario en sigi
                            $this->model->updateUserMicrosoftId($id_docente, $response['data']['microsoft']['id_microsoft']);
                            $_SESSION['flash_success'] .= '<br>  Usuario actualizado en Microsoft 365';
                            /*if ($response['data']['microsoft']['license']['success']) {
                                $_SESSION['flash_success'] .= '<br>  Licencia asignada en Microsoft 365';
                            } else {
                                $_SESSION['flash_error'] .= '<br>  Error al asignar licencia en Microsoft 365';
                            }*/
                        } else {
                            $_SESSION['flash_error'] .= '<br>  Error al actualizar usuario en Microsoft 365';
                        }
                    } catch (\Exception $e) {
                        error_log("Error Update Integraciones Docente: " . $e->getMessage());
                    }
                }
                // =======================================================
                // FIN INTEGRACIÓN 
                // =======================================================
            }

        endif;
        header('Location: ' . BASE_URL . '/sigi/docentes');
        exit;
    }

    protected function registrar_permiso_inicial($id_docente)
    {
        $idRolDocente = 6; // 'ESTUDIANTE' -- OJO: ¿Rol Docente es 6? Validar ID en tu DB
        //registrar los permisoselegidos por sistema para nuevos docentes
        $ds = $this->objDatosSistema->buscar();
        $idsSistemas = $this->objDatosSistema->decodePermisos($ds['permisos_inicial_docente'] ?? '');
        $this->model->asignarLote($id_docente, $idRolDocente, $idsSistemas);
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
                'dni'                 => $_POST['dni'],
                'apellidos_nombres'   => $_POST['ApellidoPaterno'] . '_' . $_POST['ApellidoMaterno'] . '_' . $_POST['Nombres'],
                'correo'              => $_POST['correo'],
                'discapacidad'        => $_POST['discapacidad'],
                'genero'              => $_POST['genero'],
                'fecha_nacimiento'    => $_POST['fecha_nacimiento'],
                'distrito_nacimiento' => $_POST['distrito_nacimiento'] ?? '',
                'grado_academico'     => $_POST['grado_academico'],
                'direccion'           => $_POST['direccion'],
                'telefono'            => $_POST['telefono'],
                'estado'              => $_POST['estado'],
                'id_rol'              => $_POST['id_rol'],
                'id_sede'             => $_POST['id_sede'],
                'id_programa_estudios' => $_POST['id_programa_registro']
            ];
            $errores = $this->model->validar($data, true, $id);
            $docente = $data;
            $apellidos_nombres = explode('_', $data['apellidos_nombres']);
            $docente['ApellidoPaterno'] = $apellidos_nombres[0];
            $docente['ApellidoMaterno'] = $apellidos_nombres[1];
            $docente['Nombres'] = $apellidos_nombres[2];
            if ($errores) {
                $this->view('sigi/docentes/editar', [
                    'errores'   => $errores,
                    'docente'   => $docente,
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
            $_SESSION['flash_success'] .= "Docente actualizado correctamente.";
            // Separar Nombres y Apellidos
            $parts = explode('_', $data['apellidos_nombres']);
            if (count($parts) >= 3) {
                $lastname = $parts[0] . ' ' . $parts[1];
                $firstname = $parts[2];
            } else {
                $lastname = $parts[0];
                $firstname = isset($parts[1]) ? $parts[1] : '-';
            }
            // =======================================================
            // INICIO INTEGRACIÓN
            // =======================================================
            if (INTEGRACIONES_SYNC_ACTIVE) {
                try {
                    $programa_est =  $this->objPrograma->find($data['id_programa_estudios']);
                    $nombre_programa = $programa_est['nombre'];
                    $rol = $this->objRol->find($data['id_rol']);
                    $tipo_usuario = $rol['nombre'];

                    //peticion curl a API
                    $data = [
                        'id' => $id,
                        'dni' => $data['dni'],
                        'nombres' => $firstname,
                        'apellidos' => $lastname,
                        'passwordPlano' => null,
                        'programa_estudios' => $nombre_programa,
                        'tipo_usuario' => $tipo_usuario,
                        'estado' => $data['estado']
                    ];
                    $response = $this->objIntegrator->sincronizarUsuarios($data);
                    if ($response['data']['moodle']['id'] > 0) {
                        //actualizar usuarioen sigi
                        $this->model->updateUserMoodleId($id, $response['data']['moodle']['id']);
                        $_SESSION['flash_success'] .= $response['data']['moodle']['message_success'];
                    } else {
                        $_SESSION['flash_error'] .= $response['data']['moodle']['message_error'];
                    }
                    if ($response['data']['microsoft']['success']) {
                        //actualizar usuario en sigi
                        $this->model->updateUserMicrosoftId($id, $response['data']['microsoft']['id_microsoft']);
                        $_SESSION['flash_success'] .= '<br>  Usuario actualizado en Microsoft 365';
                        /*if ($response['data']['microsoft']['license']['success']) {
                            $_SESSION['flash_success'] .= '<br>  Licencia asignada en Microsoft 365';
                        } else {
                            $_SESSION['flash_error'] .= '<br>  Error al asignar licencia en Microsoft 365';
                        }*/
                    } else {
                        $_SESSION['flash_error'] .= '<br>  Error al actualizar usuario en Microsoft 365';
                    }
                } catch (\Exception $e) {
                    error_log("Error Update Integraciones Docente: " . $e->getMessage());
                }
            }
        // =======================================================
        // FIN INTEGRACIÓN 
        // =======================================================

        endif;
        header('Location: ' . BASE_URL . '/sigi/docentes');
        exit;
    }

    // ... (El resto de métodos ver, data, editar_permisos, permisos, guardarPermisos se mantienen igual)

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

            $result = $this->model->getPaginated($filters, (int)$length, (int)$start, $orderCol, $orderDir);
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

    public function resetPassword()
    {
        if (!\Core\Auth::esAdminSigi() && !\Core\Auth::esAdminAcademico()) {
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $back = $_GET['back'];
        if ($back == '') {
            $back = "/intranet";
        }
        $data = $_GET['data'];
        if ($data == '') {
            header('Location: ' . BASE_URL . $back);
            exit;
        }
        $id = base64_decode($data);
        $docente = $this->model->find($id);
        if (!$docente) {
            header('Location: ' . BASE_URL . $back);
            exit;
        }

        // Separar Nombres y Apellidos
        $firstname = $docente['Nombres'];
        $lastname = $docente['ApellidoPaterno'] . ' ' . $docente['ApellidoMaterno'];
        //generar contraseña segura nueva para docente
        $passwordPlano = \Core\Auth::crearPassword(8);
        $password = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $this->model->updatePassword($id, $password);
        $_SESSION['flash_success'] .= "Contraseña actualizada correctamente para el docente " . $firstname . " " . $lastname . ". La contraseña es: " . $passwordPlano;
        // =======================================================
        // INICIO INTEGRACIÓN (Actualización)
        // =======================================================
        if (INTEGRACIONES_SYNC_ACTIVE) {
            try {
                //peticion curl a API
                $usuario = [
                    'id' => $id,
                    'dni' => $docente['dni'],
                    'nombres' => $firstname,
                    'apellidos' => $lastname,
                    'passwordPlano' => $passwordPlano,
                    'programa_estudios' => $docente['nombre_programa'],
                    'tipo_usuario' => $docente['nombre_rol'],
                    'estado' => $docente['estado']
                ];
                $response = $this->objIntegrator->sincronizarUsuarios($usuario);
                if ($response['data']['moodle']['message_success']) {
                    //actualizar usuarioen sigi
                    $this->model->updateUserMoodleId($id, $response['data']['moodle']['id']);
                    $_SESSION['flash_success'] .= $response['data']['moodle']['message_success'];
                } else {
                    $_SESSION['flash_error'] .= $response['data']['moodle']['message_error'];
                }
                if ($response['data']['microsoft']['success']) {
                    //actualizar usuario en sigi
                    $this->model->updateUserMicrosoftId($id, $response['data']['microsoft']['id_microsoft']);
                    $_SESSION['flash_success'] .= '<br>  Usuario actualizado en Microsoft 365';
                    /*if ($response['data']['microsoft']['license']['success']) {
                        $_SESSION['flash_success'] .= '<br>  Licencia asignada en Microsoft 365';
                    } else {
                        $_SESSION['flash_error'] .= '<br>  Error al asignar licencia en Microsoft 365';
                    }*/
                } else {
                    $_SESSION['flash_error'] .= '<br>  Error al actualizar usuario en Microsoft 365';
                }
            } catch (\Exception $e) {
                error_log("Error Update Integraciones Docente: " . $e->getMessage());
            }
        }
        // =======================================================
        // FIN INTEGRACIÓN 
        // =======================================================
        header('Location: ' . BASE_URL . $back);
        exit;
    }
    /*public function parseoNombres4()
    {
        $datos = $this->model->getAllUsuarios();
        foreach ($datos as $x) {
            $p = $this->model->parsePersona4($x);
            echo "Original: {$x['apellidos_nombres']}<br>";
            echo "Grado: {$p['grado']}<br>";
            echo "Ap. Paterno: {$p['apellido_paterno']}<br>";
            echo "Ap. Materno: {$p['apellido_materno']}<br>";
            echo "Nombres: {$p['nombres']}<br>";
            echo "Listo: {$p['apellidos_nombres']}<br>";
            echo "-------------------------<br>";
        }
    }*/
}
