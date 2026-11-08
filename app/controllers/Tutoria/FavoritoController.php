<?php

namespace App\Controllers\Tutoria;

use Core\Controller;

class FavoritoController extends Controller
{
    public function index()
    {
        header('Location: ' . BASE_URL . '/tutoria');
        exit;
    }
}
