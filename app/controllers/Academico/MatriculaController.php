<?php

namespace App\Controllers\Academico;

use Core\Controller;
use PDO;
use Exception;

require_once __DIR__ . '/../../../app/models/Academico/Matricula.php';
require_once __DIR__ . '/../../../app/models/Sigi/Semestre.php';

use App\Models\Academico\Matricula;
use App\Models\Sigi\Semestre;

class MatriculaController extends Controller
{
    protected $model;
    protected $objSemestre;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Matricula();
        $this->objSemestre = new Semestre();
    }

    public function index()
    {
        $programas = $this->model->getProgramas();
        $planes = $this->model->getPlanes();
        $semestres = $this->model->getSemestres();

        $this->view('academico/matricula/index', [
            'programas' => $programas,
            'planes' => $planes,
            'semestres' => $semestres,
            'module' => 'academico',
            'pageTitle' => 'Listado de Matrículas'
        ]);
    }

    public function data()
    {
        if (\Core\Auth::esAdminAcademico()):
            header('Content-Type: application/json; charset=utf-8');
            $draw = $_GET['draw'] ?? 1;
            $start = $_GET['start'] ?? 0;
            $length = $_GET['length'] ?? 10;
            $orderCol = $_GET['order'][0]['column'] ?? 0;
            $orderDir = $_GET['order'][0]['dir'] ?? 'asc';

            // Filtros
            $filters = [
                'periodo' => $_SESSION['sigi_periodo_actual_id'] ?? 0,
                'sede' => $_SESSION['sigi_sede_actual'] ?? 0,
                'dni' => $_GET['filter_dni'] ?? null,
                'apellidos_nombres' => $_GET['filter_apellidos_nombres'] ?? null,
                'programa' => $_GET['filter_programa'] ?? null,
                'plan' => $_GET['filter_plan'] ?? null,
                'semestre' => $_GET['filter_semestre'] ?? null,
                'turno' => $_GET['filter_turno'] ?? null,
                'seccion' => $_GET['filter_seccion'] ?? null,
            ];

            $result = $this->model->getPaginated($filters, $length, $start, $orderCol, $orderDir);

            echo json_encode([
                'draw' => (int)$draw,
                'recordsTotal' => (int)$result['total'],
                'recordsFiltered' => (int)$result['total'],
                'data' => $result['data']
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }

    public function ver($id_matricula)
    {
        $detalle = $this->model->getDetalleMatricula($id_matricula);
        $estudiante = $this->model->getEstudianteByMatricula($id_matricula);

        $this->view('academico/matricula/ver', [
            'detalle' => $detalle,
            'estudiante' => $estudiante,
            'id_matricula' => $id_matricula,
            'module' => 'academico',
            'pageTitle' => 'Detalle de Matrícula'
        ]);
    }


    public function nuevo()
    {
        $this->view('academico/matricula/nuevo', [
            'errores' => [],
            'module' => 'academico',
            'pageTitle' => 'Nueva Matrícula'
        ]);
    }

    // AJAX: Buscar estudiante por DNI
    public function buscarEstudiante()
    {
        if (\Core\Auth::esAdminAcademico()):
            $dni = $_GET['dni'] ?? '';
            header('Content-Type: application/json; charset=utf-8');
            if (!$dni) {
                echo json_encode(['error' => 'Debe ingresar un DNI']);
                exit;
            }
            $sede_actual = $_SESSION['sigi_sede_actual'] ?? 0;
            $res = $this->model->buscarEstudiantePorDNI($dni, $sede_actual);
            if (!$res) {
                echo json_encode(['error' => 'No se encontró estudiante con ese DNI en la sede actual.']);
                exit;
            }
            // Cargar programas y planes disponibles para el estudiante
            $res['programas'] = $this->model->getProgramasEstudiante($res['id']);
            $res['planes'] = $this->model->getPlanesEstudiante($res['id']);
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }



    // AJAX: Obtener UDs programadas por plan y semestre
    public function udsProgramadas()
    {
        if (\Core\Auth::esAdminAcademico()):
            $idPlan = $_GET['plan'] ?? 0;
            $idSemestre = $_GET['semestre'] ?? 0;
            $turno = $_GET['turno'] ?? 0;
            $seccion = $_GET['seccion'] ?? 0;
            header('Content-Type: application/json; charset=utf-8');
            if (!$idPlan || !$idSemestre) {
                echo json_encode([]);
                exit;
            }
            $periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
            $sede = $_SESSION['sigi_sede_actual'] ?? 0;
            $uds = $this->model->getUDsProgramadas($idPlan, $idSemestre, $periodo, $sede, $turno, $seccion);
            echo json_encode($uds, JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }

    // POST: Guardar la matrícula y todo el árbol dependiente
    public function guardar()
    {
        if (\Core\Auth::esAdminAcademico()):
            $errores = [];
            try {
                $data = [
                    'dni' => $_POST['dni'] ?? '',
                    'id_programa_estudios' => $_POST['id_programa_estudios'] ?? '',
                    'id_plan_estudio' => $_POST['id_plan_estudio'] ?? '',
                    'id_semestre' => $_POST['id_semestre'] ?? '',
                    'turno' => $_POST['turno'] ?? '',
                    'seccion' => $_POST['seccion'] ?? '',
                    'ud_programadas' => $_POST['ud_programadas'] ?? [],
                ];
                $periodo = $_SESSION['sigi_periodo_actual_id'] ?? 0;
                $sede = $_SESSION['sigi_sede_actual'] ?? 0;
                $usuario = $this->model->buscarEstudiantePorDNI($data['dni'], $sede);
                if (!$usuario) throw new Exception("Estudiante no encontrado o no está en esta sede.");

                // Validaciones (programa/plan corresponde a estudiante, no matrícula duplicada, etc.)
                $esValido = $this->model->validarMatricula($usuario['id'], $data['id_plan_estudio'], $periodo, $sede, $data['id_semestre']);
                if (!$esValido['ok']) throw new Exception($esValido['msg']);

                // El proceso de matrícula completo (transacción)
                $exito = $this->model->registrarMatriculaCompleta($usuario['id'], $data, $periodo, $sede, $errores);
                if (!$exito) throw new Exception("No se pudo registrar la matrícula." . (isset($errores[0]) ? ' Motivo: ' . $errores[0] : ''));

                $_SESSION['flash_success'] = "¡Matrícula registrada correctamente!";
                header('Location: ' . BASE_URL . '/academico/matricula');
                exit;
            } catch (Exception $e) {
                $errores[] = $e->getMessage();
                $nombre_sede_actual = $_SESSION['sigi_sede_nombre'] ?? '';
                $this->view('academico/matricula/nuevo', [
                    'nombre_sede_actual' => $nombre_sede_actual,
                    'errores' => $errores,
                    'module' => 'academico',
                    'pageTitle' => 'Nueva Matrícula'
                ]);
            }
        endif;
        exit;
    }

    public function agregarUd($id_matricula)
    {
        if (\Core\Auth::esAdminAcademico()):
            $matricula = $this->model->getDatosMatricula($id_matricula);
            $estudiante = $this->model->getEstudianteByMatricula($id_matricula);
            $semestresDisponibles = $this->model->getSemestresDisponibles($matricula['plan_id']);
            $semestreActual = null;
            $unidadesDisponibles = [];
        endif;
        $this->view('academico/matricula/agregarUd', [
            'matricula' => $matricula,
            'estudiante' => $estudiante,
            'semestresDisponibles' => $semestresDisponibles,
            'semestreActual' => $semestreActual,
            'unidadesDisponibles' => $unidadesDisponibles,
            'module' => 'academico',
            'pageTitle' => 'Agregar Unidad Didáctica'
        ]);
        exit;
    }

    // AJAX para cargar UDs programadas del semestre seleccionado y que no estén ya en el detalle
    public function unidadesDisponiblesAjax($id_matricula, $id_semestre)
    {
        $uds = $this->model->getUnidadesDisponibles($id_matricula, $id_semestre);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($uds, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function guardarUnidadDidactica($id_matricula)
    {
        if (\Core\Auth::esAdminAcademico()):
            if (empty($_POST['unidades'])) {
                $_SESSION['flash_error'] = "Debe seleccionar al menos una Unidad Didáctica para agregar.";
                header('Location: ' . BASE_URL . "/academico/matricula/agregarUd/$id_matricula");
                exit;
            }

            $id_semestre = $_POST['id_semestre'] ?? null;
            $model = $this->model;

            $data = [
                'id_semestre' => $_POST['id_semestre'] ?? '',
                'ud_programadas' => $_POST['unidades'] ?? [],
            ];

            $resultado = $model->agregarUnidadesDidacticas($id_matricula, $id_semestre, $data);

            if ($resultado['ok']) {
                $_SESSION['flash_success'] = "Unidades Didácticas agregadas correctamente.";
                header('Location: ' . BASE_URL . "/academico/matricula/ver/$id_matricula");
                exit;
            } else {
                $_SESSION['flash_error'] = "No se pudo agregar las Unidades Didácticas. Error: " . $resultado['error'];
                header('Location: ' . BASE_URL . "/academico/matricula/agregarUd/$id_matricula");
                exit;
            }
        endif;
        exit;
    }




    public function eliminarDetalle($id_detalle)
    {
        if (\Core\Auth::esAdminAcademico()):
            // 1. Obtener el id_matricula antes de borrar
            $stmt = $this->model->getMatriculaByDetalle($id_detalle);
            $id_matricula = $stmt ? $stmt['id_matricula'] : null;

            // 2. Eliminar el detalle y registros dependientes
            $res = $this->model->eliminarDetalleMatricula($id_detalle);

            if ($res) {
                $_SESSION['flash_success'] = "Unidad Didáctica eliminada correctamente.";
            } else {
                $_SESSION['flash_error'] = "Error al eliminar el detalle. Intente nuevamente o contacte soporte.";
            }
        endif;
        // 3. Redirige a la vista del detalle de la matrícula
        header('Location: ' . BASE_URL . '/academico/matricula/ver/' . $id_matricula);
        exit;
    }
}
