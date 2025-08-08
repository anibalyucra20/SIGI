<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/Sedes.php';
require_once __DIR__ . '/../../../app/models/Sigi/Docente.php';
require_once __DIR__ . '/../../../app/models/Sigi/Programa.php';

use App\Models\Sigi\Sedes;
use App\Models\Sigi\Docente;
use App\Models\Sigi\Programa;

class SedesController extends Controller
{
    protected $model;
    protected $objDocente;
    protected $objPrograma;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Sedes();
        $this->objDocente = new Docente();
        $this->objPrograma = new Programa();
    }

    // Vista de listado
    public function index()
    {
        $filtros = [
            'nombre'       => $_GET['filter_nombre'] ?? '',
            'departamento' => $_GET['filter_departamento'] ?? '',
            'provincia'    => $_GET['filter_provincia'] ?? '',
            'estado'       => $_GET['filter_estado'] ?? ''
        ];
        $sedes = $this->model->getAll($filtros);
        $this->view('sigi/sedes/index', [
            'sedes'   => $sedes,
            'filtros' => $filtros,
            'module'  => 'sigi',
            'pageTitle' => 'Sedes'
        ]);
    }
    public function data()
    {
        if (\Core\Auth::esAdminSigi()):
            header('Content-Type: application/json; charset=utf-8');

            $draw      = $_GET['draw']  ?? 1;
            $start     = $_GET['start'] ?? 0;
            $length    = $_GET['length'] ?? 10;
            $orderCol  = $_GET['order'][0]['column'] ?? 1;
            $orderDir  = $_GET['order'][0]['dir']    ?? 'asc';

            // Mapea los índices de columna con el nombre real en la tabla
            $columnas = [
                0 => 'id',
                1 => 'cod_modular',
                2 => 'nombre',
                3 => 'departamento',
                4 => 'provincia',
                5 => 'distrito',
                6 => 'direccion',
                7 => 'telefono',
                8 => 'correo',
                9 => 'responsable'
            ];
            $ordenarPor = $columnas[$orderCol] ?? 'nombre';

            $db = $this->model->getDB();
            $sql = "SELECT * FROM sigi_sedes ORDER BY $ordenarPor $orderDir LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$length, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $sql = "SELECT s.*, u.apellidos_nombres AS responsable_nombre
        FROM sigi_sedes s
        LEFT JOIN sigi_usuarios u ON s.responsable = u.id
        ORDER BY $ordenarPor $orderDir
        LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$length, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$start, \PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);


            // Total de registros
            $total = $db->query("SELECT COUNT(*) FROM sigi_sedes")->fetchColumn();

            echo json_encode([
                'draw' => (int)$draw,
                'recordsTotal' => (int)$total,
                'recordsFiltered' => (int)$total,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
        endif;
        exit;
    }


    public function nuevo()
    {
        $responsables = $this->objDocente->getDocentesAsignar();
        $this->view('sigi/sedes/nuevo', [
            'responsables' => $responsables,
            'module'       => 'sigi',
            'pageTitle'    => 'Nueva Sede'
        ]);
    }

    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):
            $data = [
                'id'          => $_POST['id'] ?? null,
                'cod_modular' => trim($_POST['cod_modular']),
                'nombre'      => trim($_POST['nombre']),
                'departamento' => trim($_POST['departamento']),
                'provincia'   => trim($_POST['provincia']),
                'distrito'    => trim($_POST['distrito']),
                'direccion'   => trim($_POST['direccion']),
                'telefono'    => trim($_POST['telefono']),
                'correo'      => trim($_POST['correo']),
                'responsable' => $_POST['responsable']
            ];
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Sede guardada correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/sedes');
        exit;
    }

    public function editar($id)
    {
        $sede = $this->model->find($id);
        $responsables = $this->objDocente->getDocentesAsignar();
        $this->view('sigi/sedes/editar', [
            'sede'         => $sede,
            'responsables' => $responsables,
            'module'       => 'sigi',
            'pageTitle'    => 'Editar Sede'
        ]);
    }
    // Vista para asociar programas a una sede
    public function programas($id)
    {
        // Sede actual
        $sede = $this->model->find($id);
        // Todos los programas posibles
        $programas = $this->objPrograma->getTodosProgramas();
        // Programas ya asociados a esta sede
        $programas_sede = $this->objPrograma->getProgramasPorSede($id); // ids asociados

        $this->view('sigi/sedes/programas', [
            'sede'           => $sede,
            'programas'      => $programas,
            'programas_sede' => $programas_sede,
            'module'         => 'sigi',
            'pageTitle'      => 'Programas de Estudio de la Sede'
        ]);
    }

    // Guardar asociación
    public function programasGuardar($id)
    {
        if (\Core\Auth::esAdminSigi()):
            $seleccionados = $_POST['programas'] ?? [];
            $this->model->guardarProgramasSede($id, $seleccionados);
            $_SESSION['flash_success'] = "Programas de estudio actualizados para la sede.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/sedes');
        exit;
    }


    public function cambiarSesion()
    {
        if (!empty($_GET['sede'])) {
            $_SESSION['sigi_sede_actual'] = $_GET['sede'];
        }
        // Seguridad: sólo rutas internas
        if (!empty($_GET['redirect']) && strpos($_GET['redirect'], '/') === 0) {
            $redirect = $_GET['redirect'];
        } else {
            $redirect = BASE_URL . '/intranet';
        }

        header('Location: ' . $redirect);
        exit;
    }
}
