<?php

namespace App\Controllers\Bolsa;

use Core\Controller;

require_once __DIR__ . '/../../models/Bolsa/Curriculum.php';

use App\Models\Bolsa\Curriculum;

class CurriculumController extends Controller
{
    protected $curriculumModel;

    public function __construct()
    {
        parent::__construct();
        $this->curriculumModel = new Curriculum();

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

        // Validar que el usuario tenga sesión activa y pertenezca al módulo de Bolsa
        if (!\Core\Auth::user() || !\Core\Auth::tieneRolEnBolsa()) {
            $_SESSION['flash_error'] = "Debe iniciar sesión para acceder al módulo de Currículums.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Lista de currículums pertenecientes al usuario en sesión
     */
    public function index()
    {
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $curriculums = $this->curriculumModel->listarPorUsuario($idUsuario);
        $usuario = $this->curriculumModel->obtenerDatosUsuario($idUsuario);

        $this->view('bolsa/curriculum/index', [
            'module'      => 'bolsa',
            'pageTitle'   => 'Mis Currículums',
            'curriculums' => $curriculums,
            'usuario'     => $usuario
        ]);
        exit;
    }

    /**
     * Formulario para crear un nuevo currículum
     */
    public function nuevo()
    {
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);
        $usuario = $this->curriculumModel->obtenerDatosUsuario($idUsuario);

        $detalleVacio = [
            'cv' => [
                'id'          => '',
                'descripcion' => '',
                'archivo'     => ''
            ],
            'usuario'      => $usuario,
            'educacion'    => [],
            'experiencia'  => [],
            'habilidades'  => [],
            'idiomas'      => [],
            'certificados' => []
        ];

        $this->view('bolsa/curriculum/nuevo', [
            'module'    => 'bolsa',
            'pageTitle' => 'Nuevo Currículum',
            'detalle'   => $detalleVacio
        ]);
        exit;
    }

    /**
     * Formulario para editar un currículum propio existente
     */
    public function editar($id)
    {
        $id = (int)$id;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        $detalle = $this->curriculumModel->obtenerDetalleCompleto($id, $idUsuario);

        if (!$detalle) {
            $_SESSION['flash_error'] = "Currículum no encontrado o no tiene permisos para editarlo.";
            header('Location: ' . BASE_URL . '/bolsa/curriculum');
            exit;
        }

        $this->view('bolsa/curriculum/editar', [
            'module'    => 'bolsa',
            'pageTitle' => 'Editar Currículum',
            'detalle'   => $detalle
        ]);
        exit;
    }

    /**
     * Vista de detalle del currículum (perfil digital)
     */
    public function ver($id)
    {
        $id = (int)$id;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        // Si es admin puede ver cualquier CV; si es estudiante solo el suyo
        $usuarioVerificar = \Core\Auth::esAdminBolsa() ? null : $idUsuario;
        $detalle = $this->curriculumModel->obtenerDetalleCompleto($id, $usuarioVerificar);

        if (!$detalle) {
            $_SESSION['flash_error'] = "Currículum no encontrado o no tiene permisos para visualizarlo.";
            header('Location: ' . BASE_URL . '/bolsa/curriculum');
            exit;
        }

        $this->view('bolsa/curriculum/ver', [
            'module'    => 'bolsa',
            'pageTitle' => 'Detalle de Currículum',
            'detalle'   => $detalle
        ]);
        exit;
    }

    /**
     * Procesa el guardado (Crear o Actualizar) del CV y sus secciones
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/bolsa/curriculum');
            exit;
        }

        $idUsuario   = (int)($_SESSION['sigi_user_id'] ?? 0);
        $idCv        = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $archivo     = trim($_POST['archivo'] ?? ''); // Link web

        if (empty($descripcion)) {
            $_SESSION['flash_error'] = "El nombre del currículum es obligatorio.";
            if ($idCv) {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/editar/' . $idCv);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/nuevo');
            }
            exit;
        }

        // Si se ingresó link de CV, validar que tenga formato de URL básico
        if (!empty($archivo) && !filter_var($archivo, FILTER_VALIDATE_URL)) {
            $_SESSION['flash_error'] = "El enlace proporcionado no tiene un formato de URL válido (debe iniciar con http:// o https://).";
            if ($idCv) {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/editar/' . $idCv);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/nuevo');
            }
            exit;
        }

        $datosGenerales = [
            'descripcion' => $descripcion,
            'archivo'     => $archivo
        ];

        // Procesar arrays de secciones hijas enviados desde el formulario
        $secciones = [
            'educacion'    => isset($_POST['educacion']) && is_array($_POST['educacion']) ? $_POST['educacion'] : [],
            'experiencia'  => isset($_POST['experiencia']) && is_array($_POST['experiencia']) ? $_POST['experiencia'] : [],
            'habilidades'  => isset($_POST['habilidades']) && is_array($_POST['habilidades']) ? $_POST['habilidades'] : [],
            'idiomas'      => isset($_POST['idiomas']) && is_array($_POST['idiomas']) ? $_POST['idiomas'] : [],
            'certificados' => isset($_POST['certificados']) && is_array($_POST['certificados']) ? $_POST['certificados'] : []
        ];

        try {
            $nuevoId = $this->curriculumModel->guardarCompleto($idCv, $idUsuario, $datosGenerales, $secciones);
            $_SESSION['flash_success'] = !empty($idCv) ? "Currículum actualizado correctamente." : "Currículum creado exitosamente.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al guardar el currículum: " . $e->getMessage();
            if ($idCv) {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/editar/' . $idCv);
            } else {
                header('Location: ' . BASE_URL . '/bolsa/curriculum/nuevo');
            }
            exit;
        }

        header('Location: ' . BASE_URL . '/bolsa/curriculum');
        exit;
    }

    /**
     * Elimina un currículum propio respetando relaciones
     */
    public function eliminar($id)
    {
        $id = (int)$id;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        $resultado = $this->curriculumModel->eliminar($id, $idUsuario);

        if ($resultado['success']) {
            $_SESSION['flash_success'] = $resultado['message'];
        } else {
            $_SESSION['flash_error'] = $resultado['message'];
        }

        header('Location: ' . BASE_URL . '/bolsa/curriculum');
        exit;
    }

    /**
     * Genera y descarga el Currículum Vitae en formato PDF profesional con TCPDF
     */
    public function pdf($id)
    {
        $id = (int)$id;
        $idUsuario = (int)($_SESSION['sigi_user_id'] ?? 0);

        // Si es admin puede ver cualquier CV; si es estudiante solo el suyo
        $usuarioVerificar = \Core\Auth::esAdminBolsa() ? null : $idUsuario;
        $detalle = $this->curriculumModel->obtenerDetalleCompleto($id, $usuarioVerificar);

        if (!$detalle) {
            $_SESSION['flash_error'] = "Currículum no encontrado o no autorizado para generar PDF.";
            header('Location: ' . BASE_URL . '/bolsa/curriculum');
            exit;
        }

        // Cargar TCPDF desde vendor
        require_once __DIR__ . '/../../../vendor/autoload.php';

        $nombreCandidato = $detalle['usuario']['nombre_completo'] ?? 'Estudiante';
        $tituloArchivo = 'CV_' . preg_replace('/[^A-Za-z0-9_]/', '_', $nombreCandidato);

        // Configuración de TCPDF (A4 Vertical)
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('SIGI - Bolsa de Trabajo');
        $pdf->SetAuthor($nombreCandidato);
        $pdf->SetTitle('Curriculum Vitae - ' . $nombreCandidato);
        $pdf->SetSubject('Currículum Vitae');

        // Desactivar encabezado y pie predeterminados de TCPDF para un diseño moderno
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Márgenes limpios y salto de página automático
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Renderizar plantilla HTML para el PDF
        ob_start();
        include __DIR__ . '/../../views/bolsa/curriculum/pdf_template.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');

        // Salida inline para previsualizar / guardar en navegador
        $pdf->Output($tituloArchivo . '.pdf', 'I');
        exit;
    }
}
