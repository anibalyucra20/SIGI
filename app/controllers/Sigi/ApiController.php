<?php

namespace App\Controllers\Sigi;

require_once __DIR__ . '/../../../app/models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../../../app/models/Academico/Estudiantes.php';


use App\Models\Sigi\DatosSistema;
use App\Models\Academico\Estudiantes;
use Core\Controller;

class ApiController extends Controller
{
    protected $objDatosSistema;
    protected $objEstudiantes;
    public function __construct()
    {
        parent::__construct();
        $this->objDatosSistema = new DatosSistema();
        $this->objEstudiantes = new Estudiantes();
    }
    //-------------------------------------- BUSCAR USUARIO API --------------------------------------

    public function buscarUsuarioApi($dni)
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');

            // 1. Buscar localmente
            $localUser = $this->objEstudiantes->buscarUsuarioPorDni($dni);
            if ($localUser) {
                echo json_encode([
                    'success' => true,
                    'local' => true,
                    'data' => $localUser
                ]);
                exit;
            }

            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];
            // 2. Buscar en API externa
            $url = API_BASE_URL . "/api/consulta/dni/$dni";

            // Usar Curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $apiData = json_decode($response, true);

            // Parse response (Handling various formats)
            $datos = $apiData['data'] ?? $apiData; // Check if wrapped in data or root

            if ($datos['success']) {
                // Try snake_case then camelCase
                $paterno = $datos['data']['apellido_paterno'] ?? $datos['data']['apellidoPaterno'] ?? '';
                $materno = $datos['data']['apellido_materno'] ?? $datos['data']['apellidoMaterno'] ?? '';
                $nombres = $datos['data']['nombres'] ?? $datos['data']['Nombres'] ?? '';
                $nombres_apellidos = $paterno . ' ' . $materno . ', ' . $nombres;

                echo json_encode([
                    'success' => true,
                    'local' => false,
                    'data' => [
                        'dni' => $dni,
                        'apellidos_nombres' => trim($nombres_apellidos),
                        'ApellidoPaterno' => $paterno,
                        'ApellidoMaterno' => $materno,
                        'Nombres' => $nombres
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró información para el DNI ingresado (API).'
                ]);
                //echo  json_encode($datos);
            }
            exit;
        endif;
        exit;
    }
    public function apiDepartamentos()
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];
            $url = API_BASE_URL . "/api/consulta/departamentos"; // Use API_BASE_URL

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            echo $response;
            exit;
        endif;
        exit;
    }

    public function apiProvincias()
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];

            $departamento = $_GET['departamento'] ?? '';
            // Encode the value to be safe in the URL segment for the Local API
            $depEncoded = rawurlencode($departamento);

            // Correction: Send as Query Parameter to avoid WAF/Path issues
            $url = API_BASE_URL . "/api/consulta/provincias?departamento=$depEncoded";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            echo $response;
            exit;
        endif;
        exit;
    }

    public function apiDistritos()
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];

            $departamento = $_GET['departamento'] ?? '';
            $provincia = $_GET['provincia'] ?? '';

            $depEncoded = rawurlencode($departamento);
            $provEncoded = rawurlencode($provincia);

            // Send as Query Parameter
            $url = API_BASE_URL . "/api/consulta/distritos?departamento=$depEncoded&provincia=$provEncoded";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            echo $response;
            exit;
        endif;
        exit;
    }
    public function apiColegios($data = '')
    {
        if (\Core\Auth::user()):
            header('Content-Type: application/json');
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];
            $isphp = true;
            if ($data == '') {
                $data = $_GET['data'] ?? '';
                $isphp = false;
            }
            $page = $_GET['page'] ?? 1;
            $departamento = $_GET['departamento'] ?? '';
            $provincia = $_GET['provincia'] ?? '';
            $distrito = $_GET['distrito'] ?? '';
            $depEncoded = rawurlencode($departamento);
            $provEncoded = rawurlencode($provincia);
            $distEncoded = rawurlencode($distrito);
            $dataEncoded = rawurlencode($data);

            // Send as Query Parameter
            $url = API_BASE_URL . "/api/consulta/colegios?data=$dataEncoded&page=$page&departamento=$depEncoded&provincia=$provEncoded&distrito=$distEncoded";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            if ($isphp) {
                return $response;
            }
            echo $response;
            exit;
        endif;
        exit;
    }
}
