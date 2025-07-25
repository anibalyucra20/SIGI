<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/ProgramacionUnidadDidactica.php';
require_once __DIR__ . '/../../../app/models/Academico/Sesiones.php';
require_once __DIR__ . '/../../../app/utils/MYPDF.php';

use App\Models\Academico\ProgramacionUnidadDidactica;
use App\Models\Academico\Sesiones;
use MYPDF;

class SesionesController extends Controller
{
    protected $model;
    protected $objProgracionUD;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Sesiones();
        $this->objProgracionUD = new ProgramacionUnidadDidactica();
    }

    public function ver($id_programacion)
    {
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);

        // Permisos
        $esAdminAcademico = (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        $this->view('academico/sesiones/index', [
            'datosUnidad' => $datosUnidad,
            'id_programacion' => $id_programacion,
            'permitido' => $permitido,
            'module' => 'academico',
            'pageTitle' => 'Sesiones de Aprendizaje'
        ]);
    }


    public function data($id_programacion)
    {
        header('Content-Type: application/json; charset=utf-8');
        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $orderCol = $_GET['order'][0]['column'] ?? 1;
        $orderDir = $_GET['order'][0]['dir'] ?? 'asc';

        $result = $this->model->getSesionesPaginadas($id_programacion, $length, $start, $orderCol, $orderDir);

        echo json_encode([
            'draw' => (int)$draw,
            'recordsTotal' => (int)$result['total'],
            'recordsFiltered' => (int)$result['total'],
            'data' => $result['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    public function editar($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];

        // Aquí pasas el id_ind_logro_aprendizaje de la sesión para obtener código/desc.
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);

        // Permisos
        $esAdminAcademico = (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;

        $momentos = $this->model->getMomentosSesion($sesion['id']);
        $activEval = $this->model->getActividadesEvaluacion($sesion['id']);

        $this->view('academico/sesiones/editar', [
            'sesion' => $sesion,
            'id_programacion' => $id_programacion,
            'datosUnidad' => $datosUnidad,
            'permitido' => $permitido,
            'momentos' => $momentos,
            'activEval' => $activEval,
            'errores' => [],
            'module' => 'academico',
            'pageTitle' => 'Editar Sesión de Aprendizaje'
        ]);
    }


    public function guardarEdicionSesion($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);

        $esAdminAcademico = (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        $permitido = $esAdminAcademico || $esDocenteEncargado;
        if (!$permitido) {
            $_SESSION['flash_error'] = 'No tiene permisos para editar esta sesión.';
            header('Location: ' . BASE_URL . "/academico/sesiones/{$id_programacion}");
            exit;
        }

        // Validaciones ()
        $errores = [];
        $fecha_desarrollo = $_POST['fecha_desarrollo'] ?? '';
        //$id_ind_logro_aprendizaje = $_POST['id_ind_logro_aprendizaje'] ?? '';
        $denominacion = trim($_POST['denominacion'] ?? '');
        $tipo_actividad = trim($_POST['tipo_actividad'] ?? '');
        $logro_sesion = trim($_POST['logro_sesion'] ?? '');
        $bibliografia = trim($_POST['bibliografia'] ?? '');

        if (!$fecha_desarrollo) $errores[] = "Debe ingresar la fecha de desarrollo.";
        //if (!$id_ind_logro_aprendizaje) $errores[] = "Debe seleccionar un indicador de logro.";
        if (!$denominacion) $errores[] = "Debe ingresar la denominación de la sesión.";
        if (!$tipo_actividad) $errores[] = "Debe seleccionar el tipo de actividad.";

        if ($errores) {
            //$indicadoresLogroCapacidad = $this->model->getIndicadoresLogroCapacidad($sesion['id_unidad_didactica']);
            $momentos = $this->model->getMomentosSesion($sesion['id']);
            $activEval = $this->model->getActividadesEvaluacion($sesion['id']);
            $this->view('academico/sesiones/editar', [
                'sesion' => array_merge($sesion, $_POST),
                'id_programacion' => $id_programacion,
                'datosUnidad' => $datosUnidad,
                'permitido' => $permitido,
                //'indicadoresLogroCapacidad' => $indicadoresLogroCapacidad,
                'momentos' => $momentos,
                'activEval' => $activEval,
                'errores' => $errores,
                'module' => 'academico',
                'pageTitle' => 'Editar Sesión de Aprendizaje'
            ]);
            return;
        }

        // Actualiza sesión principal y detalles
        $this->model->actualizarSesionCompleta($id_sesion, [
            'fecha_desarrollo' => $fecha_desarrollo,
            //'id_ind_logro_aprendizaje' => $id_ind_logro_aprendizaje,
            'denominacion' => $denominacion,
            'tipo_actividad' => $tipo_actividad,
            'logro_sesion' => $logro_sesion,
            'bibliografia' => $bibliografia,
            'momentos' => $_POST,
            'activEval' => $_POST
        ]);

        $_SESSION['flash_success'] = "Sesión actualizada correctamente.";
        //header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        $this->editar($id_sesion);
        exit;
    }
    public function duplicar($id_sesion)
    {
        // Permisos
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);

        $esAdminAcademico = (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        if (!($esAdminAcademico || $esDocenteEncargado)) {
            $_SESSION['flash_error'] = "No tiene permisos para duplicar esta sesión.";
            header('Location: ' . BASE_URL . "/academico/sesiones/{$id_programacion}");
            exit;
        }

        try {
            $newId = $this->model->duplicarSesion($id_sesion);
            $_SESSION['flash_success'] = "Sesión duplicada correctamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al duplicar: " . $e->getMessage();
        }
        header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        exit;
    }
    public function eliminar($id_sesion)
    {
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion);

        $esAdminAcademico = (isset($_SESSION['sigi_rol_actual']) && $_SESSION['sigi_rol_actual'] == 1);
        $esDocenteEncargado = (isset($_SESSION['sigi_user_id']) && $datosUnidad && $_SESSION['sigi_user_id'] == $this->objProgracionUD->getIdDocente($id_programacion));
        if (!($esAdminAcademico || $esDocenteEncargado)) {
            $_SESSION['flash_error'] = "No tiene permisos para eliminar esta sesión.";
            header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
            exit;
        }

        try {
            $this->model->eliminarSesion($id_sesion);
            $_SESSION['flash_success'] = "Sesión eliminada correctamente.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al eliminar: " . $e->getMessage();
        }
        header('Location: ' . BASE_URL . "/academico/sesiones/ver/{$id_programacion}");
        exit;
    }
    public function pdf($id_sesion)
    {
        require_once __DIR__ . '/../../../vendor/autoload.php'; // ruta según tu estructura

        // Recupera todos los datos igual que en imprimir()
        $sesion = $this->model->getSesionParaEditar($id_sesion);
        $id_programacion = $sesion['id_programacion'];
        $datosUnidad = $this->model->getDatosUnidad($id_programacion, $sesion['id_ind_logro_aprendizaje']);
        //var_dump($datosUnidad);
        $momentos = $this->model->getMomentosSesion($sesion['id']);
        $activEval = $this->model->getActividadesEvaluacion($sesion['id']);

        //var_dump($id_sesion);

        // Armamos el HTML igual que en tu vista pero SIN headers/footers de Bootstrap
        ob_start();
        include __DIR__ . '/../../views/academico/sesiones/plantilla_pdf.php'; // Tu vista PDF
        $html = ob_get_clean();
        // Crear PDF
        $pdf = new MYPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SIGI');
        $pdf->SetTitle('Sesión de Aprendizaje');
        $pdf->SetMargins(12, 30, 12, true);
        $pdf->SetAutoPageBreak(TRUE, 30);
        $pdf->AddPage();

        // Escribe el HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Nombre de archivo de descarga
        $filename = 'Sesion_Aprendizaje_' . $sesion['denominacion'] . '_' . $sesion['id'] . '.pdf';

        // Salida para descargar
        $pdf->Output($filename, 'I'); // 'D' para descarga directa; 'I' para inline
        exit;
    }
}
