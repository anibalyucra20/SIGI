<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/DatosInstitucionales.php';

use App\Models\Sigi\DatosInstitucionales;

class DatosInstitucionalesController extends Controller
{
    protected $model;
    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::tieneRolEnSigi()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $this->model = new DatosInstitucionales();
    }
    public function index()
    {
        if (!\Core\Auth::esAdminSigi()) {
            // si no es administrador mostramos la vista vacia
            $this->view('sigi/datosInstitucionales/index', []);
            exit;
        }
        $institucion = $this->model->buscar();
        $this->view('sigi/datosInstitucionales/index', [
            'institucion' => $institucion,
            'module' => 'sigi',
            'pageTitle' => 'Editar datos institucionales'
        ]);
    }
    // Guardar los datos (POST)
    public function guardar()
    {
        $data = [
            'id'                 => $_POST['id'] ?? null,
            'cod_modular'        => $_POST['cod_modular'],
            'ruc'                => $_POST['ruc'],
            'nombre_institucion' => $_POST['nombre_institucion'],
            'dre'                => $_POST['dre'],
            'departamento'       => $_POST['departamento'],
            'provincia'          => $_POST['provincia'],
            'distrito'           => $_POST['distrito'],
            'direccion'          => $_POST['direccion'],
            'telefono'           => $_POST['telefono'],
            'correo'             => $_POST['correo'],
            'nro_resolucion'     => $_POST['nro_resolucion'],
            'estado'             => $_POST['estado'],
        ];

        // Puedes agregar validaciones extra aquí si deseas
        // Validación rápida de ejemplo
        foreach (['cod_modular', 'ruc', 'nombre_institucion', 'dre', 'departamento', 'provincia', 'distrito', 'direccion', 'telefono', 'correo', 'nro_resolucion'] as $campo) {
            if (empty($data[$campo])) {
                $_SESSION['flash_error'] = "Todos los campos son obligatorios.";
                header('Location: ' . BASE_URL . '/sigi/datosInstitucionales');
                exit;
            }
        }
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "Correo electrónico inválido.";
            header('Location: ' . BASE_URL . '/sigi/datosInstitucionales');
            exit;
        }

        $this->model->guardar($data);

        $_SESSION['flash_success'] = "Datos institucionales guardados correctamente.";
        header('Location: ' . BASE_URL . '/sigi/datosInstitucionales');
        exit;
    }
}
