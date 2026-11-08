<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

require_once __DIR__ . '/../../models/Bolsa/Postulaciones.php';
require_once __DIR__ . '/../../models/Bolsa/Ofertas.php';
require_once __DIR__ . '/../../models/Bolsa/Curriculum.php';
require_once __DIR__ . '/../../models/Bolsa/Empresas.php';

use App\Models\Bolsa\Postulaciones;
use App\Models\Bolsa\Ofertas;
use App\Models\Bolsa\Curriculum;
use App\Models\Bolsa\Empresas;

class PostulacionesController extends Controller {

    protected $postulacionesModel;
    protected $ofertasModel;
    protected $curriculumModel;
    protected $empresasModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->postulacionesModel = new Postulaciones();
        $this->ofertasModel       = new Ofertas();
        $this->curriculumModel    = new Curriculum();
        $this->empresasModel      = new Empresas();

        // Asegurar contexto de Bolsa Laboral (módulo 7)
        $moduloBolsa = 7;
        if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa) {
            foreach (($_SESSION['sigi_permisos_usuario'] ?? []) as $permiso) {
                if ($permiso['id_sistema'] == $moduloBolsa) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = (int)$permiso['id_rol'];
                    break;
                }
            }
            if (($_SESSION['sigi_modulo_actual'] ?? 0) != $moduloBolsa && isset($_SESSION['sigi_user_id'])) {
                $db = (new \Core\Model())->getDB();
                $idUser = (int)$_SESSION['sigi_user_id'];
                $rolUser = (int)$db->query("SELECT id_rol FROM sigi_usuarios WHERE id = $idUser")->fetchColumn();
                if (in_array($rolUser, [1, 7, 8])) {
                    $_SESSION['sigi_modulo_actual'] = $moduloBolsa;
                    $_SESSION['sigi_rol_actual']    = $rolUser;
                }
            }
        }

        // Validar sesión activa y pertenencia al módulo de Bolsa
        if (!\Core\Auth::user() || !\Core\Auth::tieneRolEnBolsa()) {
            $_SESSION['flash_error'] = "Debe iniciar sesión para acceder al módulo de Bolsa de Trabajo.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Vista principal:
     * - Si es Administrador: Panel general de postulaciones con filtros.
     * - Si es Estudiante: "Mis Postulaciones Laborales".
     */
    public function index()
    {
        $esAdmin = \Core\Auth::esAdminBolsa();
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        if ($esAdmin) {
            $filtros = [
                'id_oferta'  => $_GET['filter_oferta'] ?? null,
                'id_empresa' => $_GET['filter_empresa'] ?? null,
                'estado'     => $_GET['filter_estado'] ?? null
            ];
            $postulaciones = $this->postulacionesModel->obtenerTodasPostulaciones();
            $empresas      = $this->empresasModel->listar();
            $ofertas       = $this->ofertasModel->getDB()->query("SELECT id, titulo FROM bolsa_ofertas_laborales ORDER BY id DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $metricas      = $this->postulacionesModel->obtenerMetricas();
            $this->view('bolsa/postulaciones/index', [
                'module'        => 'bolsa',
                'pageTitle'     => 'Gestión de Postulaciones Laborales',
                'esAdmin'       => true,
                'postulaciones' => $postulaciones,
                'empresas'      => $empresas,
                'ofertas'       => $ofertas,
                'filtros'       => $filtros,
                'metricas'      => $metricas,
                'estados'       => Postulaciones::getEstados()
            ]);
        } else {
            $misPostulaciones = $this->postulacionesModel->obtenerMisPostulaciones($idUsuario);
            $metricas         = $this->postulacionesModel->obtenerMetricas($idUsuario);
            $this->view('bolsa/postulaciones/index', [
                'module'           => 'bolsa',
                'pageTitle'        => 'Mis Postulaciones Laborales',
                'esAdmin'          => false,
                'misPostulaciones' => $misPostulaciones,
                'metricas'         => $metricas,
                'estados'          => Postulaciones::getEstados()
            ]);
        }
        exit;
    }

    /**
     * Vista de postulantes recibidos para una oferta laboral específica (Administrador)
     */
    public function ver($idOferta)
    {
        if (!\Core\Auth::esAdminBolsa()) {
            $_SESSION['flash_error'] = "Acceso denegado: Se requiere rol de Administrador.";
            header('Location: ' . BASE_URL . '/bolsa');
            exit;
        }
        $idOferta = (int)$idOferta;
        $oferta = $this->ofertasModel->find($idOferta);
        if (!$oferta) {
            $_SESSION['flash_error'] = "Oferta laboral no encontrada.";
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }

        // Obtener datos detallados de empresa y carrera
        $ofertaDetalle = $this->ofertasModel->obtenerDetallePorId($idOferta);
        $this->view('bolsa/postulaciones/ver', [
            'module'        => 'bolsa',
            'pageTitle'     => 'Postulantes - ' . ($oferta['titulo'] ?? 'Oferta'),
            'oferta'        => $ofertaDetalle ?: $oferta,
            'idOferta'      => $idOferta,
            'estados'       => Postulaciones::getEstados()
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para DataTables de postulantes de una oferta laboral (Administrador)
     */
    public function data($idOferta)
    {
        if (!\Core\Auth::esAdminBolsa()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');
        $draw         = $_GET['draw'] ?? 1;
        $start        = $_GET['start'] ?? 0;
        $length       = $_GET['length'] ?? 10;
        $orderCol     = $_GET['order'][0]['column'] ?? 5; // Fecha de postulación
        $orderDir     = $_GET['order'][0]['dir'] ?? 'desc';
        $filtroEstado = $_GET['filter_estado'] ?? null;
        $resultado = $this->postulacionesModel->ObtenerPostulacionesPorOferta(
            (int)$idOferta,
            $length,
            $start,
            $orderCol,
            $orderDir,
            $filtroEstado
        );

        echo json_encode([
            'draw'            => (int)$draw,
            'recordsTotal'    => (int)$resultado['total'],
            'recordsFiltered' => (int)$resultado['total'],
            'data'            => $resultado['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Vista interactiva para postular a una oferta laboral (Estudiante/Egresado)
     */
    public function postular($idOferta)
    {
        $idOferta  = (int)$idOferta;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $oferta = $this->ofertasModel->find($idOferta);
        if (!$oferta) {
            $_SESSION['flash_error'] = "La oferta laboral solicitada no existe.";
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        $ofertaDetalle = $this->ofertasModel->obtenerDetallePorId($idOferta);
        $ofertaInfo    = $ofertaDetalle ?: $oferta;

        // 1. Validar vigencia de la oferta
        $hoy = date('Y-m-d');
        $esVigente = ((int)$oferta['estado'] === 1) && (strtotime($oferta['fecha_cierre']) >= strtotime($hoy));

        // 2. Validar si ya postuló anteriormente
        $postulacionPrevia = $this->postulacionesModel->yaPostulo($idOferta, $idUsuario);

        // 3. Obtener currículums registrados del estudiante
        $curriculums = $this->curriculumModel->listarPorUsuario($idUsuario);

        // 4. Datos del usuario
        $usuario = $this->curriculumModel->obtenerDatosUsuario($idUsuario);

        $this->view('bolsa/postulaciones/postular', [
            'module'            => 'bolsa',
            'pageTitle'         => 'Postular a Convocatoria',
            'oferta'            => $ofertaInfo,
            'idOferta'          => $idOferta,
            'esVigente'         => $esVigente,
            'postulacionPrevia' => $postulacionPrevia,
            'curriculums'       => $curriculums,
            'usuario'           => $usuario,
            'estados'           => Postulaciones::getEstados()
        ]);
        exit;
    }

    /**
     * Procesa y guarda la postulación enviada por el estudiante
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $idOferta  = (int)($_POST['id_oferta_laboral'] ?? 0);
        $idCv      = (int)($_POST['id_curriculum'] ?? 0);
        if (empty($idOferta) || empty($idCv)) {
            $_SESSION['flash_error'] = "Debe seleccionar un currículum para completar su postulación.";
            header('Location: ' . BASE_URL . '/bolsa/postulaciones/postular/' . $idOferta);
            exit;
        }

        // 1. Validar existencia y vigencia de la oferta laboral
        $oferta = $this->ofertasModel->find($idOferta);
        if (!$oferta || (int)$oferta['estado'] !== 1) {
            $_SESSION['flash_error'] = "La convocatoria no se encuentra disponible para postulaciones.";
            header('Location: ' . BASE_URL . '/bolsa/ofertas');
            exit;
        }
        $hoy = date('Y-m-d');
        if (strtotime($oferta['fecha_cierre']) < strtotime($hoy)) {
            $_SESSION['flash_error'] = "La fecha límite para postular a esta convocatoria ha expirado el " . date('d/m/Y', strtotime($oferta['fecha_cierre'])) . ".";
            header('Location: ' . BASE_URL . '/bolsa/postulaciones/postular/' . $idOferta);
            exit;
        }
        // 2. Validar que no haya postulado previamente (Evitar duplicados)
        if ($this->postulacionesModel->yaPostulo($idOferta, $idUsuario)) {
            $_SESSION['flash_error'] = "Ya has registrado una postulación para esta oferta laboral.";
            header('Location: ' . BASE_URL . '/bolsa/postulaciones');
            exit;
        }

        // 3. Validar que el currículum pertenezca efectivamente al usuario
        $cv = $this->curriculumModel->find($idCv, $idUsuario);
        if (!$cv) {
            $_SESSION['flash_error'] = "El currículum seleccionado no es válido o no le pertenece.";
            header('Location: ' . BASE_URL . '/bolsa/postulaciones/postular/' . $idOferta);
            exit;
        }
        // Determinar tipo_curriculum (1 = Link externo, 2 = Generado en el sistema)
        $tipoCurriculum = !empty($cv['archivo']) ? 1 : 2;
        $data = [
            'id_oferta_laboral' => $idOferta,
            'id_usuario'        => $idUsuario,
            'id_curriculum'     => $idCv,
            'tipo_curriculum'   => $tipoCurriculum,
            'estado'            => Postulaciones::ESTADO_PENDIENTE
        ];
        try {
            $this->postulacionesModel->guardar($data);
            $_SESSION['flash_success'] = "¡Tu postulación ha sido enviada exitosamente a la empresa!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al registrar la postulación: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/bolsa/postulaciones/postular/' . $idOferta);
            exit;
        }
        header('Location: ' . BASE_URL . '/bolsa/postulaciones');
        exit;
    }

    /**
     * Actualiza el estado de una postulación (Solo Administrador)
     */
    public function cambiarEstado()
    {
        if (!\Core\Auth::esAdminBolsa()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }
            $_SESSION['flash_error'] = "Acceso denegado.";
            header('Location: ' . BASE_URL . '/bolsa');
            exit;
        }
        $idPostulacion = (int)($_POST['id_postulacion'] ?? 0);
        $nuevoEstado   = (int)($_POST['estado'] ?? 0);
        $idOferta      = (int)($_POST['id_oferta'] ?? 0);
        try {
            $this->postulacionesModel->actualizarEstado($idPostulacion, $nuevoEstado);
            if ($this->isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                $estadosInfo = Postulaciones::getEstados();
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente.',
                    'estado'  => $estadosInfo[$nuevoEstado] ?? []
                ]);
                exit;
            }

            $_SESSION['flash_success'] = "Estado de postulación actualizado correctamente.";
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = "Error: " . $e->getMessage();
        }
        $redir = !empty($idOferta) ? '/bolsa/postulaciones/ver/' . $idOferta : '/bolsa/postulaciones';
        header('Location: ' . BASE_URL . $redir);
        exit;
    }

    /**
     * Permite al estudiante cancelar su postulación si aún se encuentra Pendiente
     */
    public function cancelar($id)
    {
        $id = (int)$id;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $cancelado = $this->postulacionesModel->cancelarPostulacion($id, $idUsuario);
        if ($cancelado) {
            $_SESSION['flash_success'] = "La postulación ha sido cancelada correctamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo cancelar la postulación. Solo se permite anular postulaciones en estado Pendiente.";
        }
        header('Location: ' . BASE_URL . '/bolsa/postulaciones');
        exit;
    }

    /**
     * Helper para detectar petición AJAX
     */
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}