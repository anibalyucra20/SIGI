<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Sigi/Sistema.php';

use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Sistema;

class DatosSistemaController extends Controller
{
    protected $model;
    protected $objSistema;
    public function __construct()
    {
        parent::__construct();

        if (!\Core\Auth::tieneRolEnSigi()) {
            $_SESSION['flash_error'] = "No tienes permisos para acceder a este módulo.";
            header('Location: ' . BASE_URL . '/intranet');
            exit;
        }
        $this->model = new DatosSistema();
        $this->objSistema = new Sistema();
    }
    public function index()
    {
        if (\Core\Auth::esAdminSigi()):
            $sistema = $this->model->buscar();
            $sistemas = $this->objSistema->getSistemas();
            $seleccionados = $this->model->decodePermisos($sistema['permisos_inicial_docente'] ?? '');
            $seleccionadosEst = $this->model->decodePermisos($sistema['permisos_inicial_estudiante'] ?? '');
        endif;
        $this->view('sigi/datosSistema/index', [
            'sistema' => $sistema,
            'sistemas' => $sistemas,
            'permisos_iniciales' => $seleccionados,
            'permisos_iniciales_estudiante' => $seleccionadosEst,
            'module' => 'sigi',
            'pageTitle' => 'Datos del Sistema'
        ]);
        exit;
    }
    // Guardar los datos (POST)
    public function guardar()
    {
        if (\Core\Auth::esAdminSigi()):

            // Permisos iniciales desde el form (checkbox[])
            $raw = $_POST['permisos_inicial_docente'] ?? []; // array de ids
            if (!is_array($raw)) $raw = [];

            // Permisos iniciales estudiante desde el form (checkbox[])
            $rawEst = $_POST['permisos_inicial_estudiante'] ?? []; // array de ids
            if (!is_array($rawEst)) $rawEst = [];

            // Validar contra ids válidos
            $validos = $this->objSistema->idsValidos();
            $seleccion = array_values(array_intersect(array_map('intval', $raw), $validos));
            $seleccionEst = array_values(array_intersect(array_map('intval', $rawEst), $validos));

            $permisosJson = $this->model->encodePermisos($seleccion);
            $permisosEstudianteJson = $this->model->encodePermisos($seleccionEst);

            $data = [
                'id'               => $_POST['id'] ?? 1,
                'dominio_pagina'   => $_POST['dominio_pagina'],
                'favicon'          => $_POST['favicon'] ?? '',
                'logo'             => $_POST['logo'] ?? '',
                'nombre_completo'  => $_POST['nombre_completo'],
                'nombre_corto'     => $_POST['nombre_corto'],
                'pie_pagina'       => $_POST['pie_pagina'],
                'host_mail'        => $_POST['host_mail'],
                'email_email'      => $_POST['email_email'],
                'password_email'   => $_POST['password_email'],
                'puerto_email'     => $_POST['puerto_email'],
                'color_correo'     => $_POST['color_correo'],
                'cant_semanas'     => $_POST['cant_semanas'],
                'nota_inasistencia' => $_POST['nota_inasistencia'],
                'duracion_sesion'  => $_POST['duracion_sesion'],
                'permisos_inicial_docente' => $permisosJson,
                'permisos_inicial_estudiante' => $permisosEstudianteJson,
                'token_sistema'    => $_POST['token_sistema'],
            ];

            // Validaciones rápidas de campos requeridos (puedes extender)
            foreach (
                [
                    'dominio_pagina',
                    'nombre_completo',
                    'nombre_corto',
                    'pie_pagina',
                    'host_mail',
                    'email_email',
                    'password_email',
                    'puerto_email',
                    'color_correo',
                    'cant_semanas',
                    'duracion_sesion',
                    'token_sistema'
                ] as $campo
            ) {
                if (empty($data[$campo])) {
                    $_SESSION['flash_error'] = "Todos los campos son obligatorios.";
                    header('Location: ' . BASE_URL . '/sigi/datosSistema/index');
                    exit;
                }
            }
            // Subida de Favicon
            if (!empty($_FILES['favicon_file']['name'])) {
                $ext = pathinfo($_FILES['favicon_file']['name'], PATHINFO_EXTENSION);
                $filename = 'favicon.' . $ext;
                move_uploaded_file($_FILES['favicon_file']['tmp_name'], __DIR__ . '/../../../public/images/' . $filename);
                $data['favicon'] = $filename;
            }
            // Subida de Logo
            if (!empty($_FILES['logo_file']['name'])) {
                $ext = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
                $filename = 'logo.' . $ext;
                move_uploaded_file($_FILES['logo_file']['tmp_name'], __DIR__ . '/../../../public/images/' . $filename);
                $data['logo'] = $filename;
            }
            $this->model->guardar($data);
            $_SESSION['flash_success'] = "Datos del sistema guardados correctamente.";
        endif;
        header('Location: ' . BASE_URL . '/sigi/datosSistema/index');
        exit;
    }
}
