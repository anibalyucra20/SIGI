<?php

namespace App\Controllers\Sigi;

use Core\Controller;
// Incluir manualmente el modelo
require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';

use App\Models\Sigi\DatosSistema;

class DatosSistemaController extends Controller
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
        $this->model = new DatosSistema();
    }
    public function index()
    {
        if (!\Core\Auth::esAdminSigi()) {
            // si no es administrador mostramos la vista vacia
            $this->view('sigi/datosSistema/index', []);
            exit;
        }
        $sistema = $this->model->buscar();
        $this->view('sigi/datosSistema/index', [
            'sistema' => $sistema,
            'module' => 'sigi',
            'pageTitle' => 'Datos del Sistema'
        ]);
    }
    // Guardar los datos (POST)
    public function guardar()
    {
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
            'nota_inasistencia'     => $_POST['nota_inasistencia'],
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
        header('Location: ' . BASE_URL . '/sigi/datosSistema/index');
        exit;
    }
}
