<?php

namespace App\Controllers\Intranet;

use Core\Controller;
use Core\Auth;
use Core\Model;
use PDO;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();              // ya validado por middleware
        $db   = (new Model())->getDB();
        $sql = "SELECT s.codigo, s.nombre
        FROM sigi_sistemas_integrados s
        JOIN sigi_permisos_usuarios pu ON pu.id_sistema = s.id
        WHERE pu.id_usuario = :uid
        GROUP BY s.id
        ORDER BY s.id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $user['sigi_user_id']]);
        $sistemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['sigi_modulo_actual'] = 0;
        $_SESSION['sigi_rol_actual']    = 0;

        $this->view('intranet/index', [
            'sistemas' => $sistemas,
            'pageTitle' => 'Panel principal',
            'module'   => 'intranet'
        ]);
    }
}
