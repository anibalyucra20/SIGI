<?php

namespace App\Helpers;

require_once __DIR__ . '/../models/Sigi/DatosSistema.php';
require_once __DIR__ . '/../models/Sigi/Docente.php';

use App\Models\Sigi\DatosSistema;
use App\Models\Sigi\Docente;

class Integrator
{
    private $token;
    private $objDatosSistema;
    private $objDocente;

    public function __construct()
    {
        // Asumiendo que definiste las constantes en config.php
        $this->objDatosSistema = new DatosSistema();
        $this->objDocente = new Docente();
    }
    public function sincronizarUsuarios(array $data)
    {
        if (INTEGRACIONES_SYNC_ACTIVE) {
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];
            $url = API_BASE_URL . "/api/integracion/syncUserIntegraciones";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        } else {
            return ['success' => false, 'details' => 'Integraciones no activadas'];
        }
    }
    public function loginMoodle($idUsuarioSigi, $a, $id)
    {
        if (INTEGRACIONES_SYNC_ACTIVE) {
            $ds = $this->objDatosSistema->buscar();
            $apiKey = $ds['token_sistema'];
            $url = API_BASE_URL . "/api/integracion/loginMoodle";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'idUsuarioSigi' => $idUsuarioSigi,
                'a' => $a,
                'id' => $id
            ]));
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        } else {
            return ['success' => false, 'details' => 'Integraciones no activadas'];
        }
    }
}
