<?php

namespace App\Helpers;

class PwaHelper
{
    private static ?array $cachedRow = null;

    private const ICON_SPECS = [
        'pwa-icon-192x192.png' => [192, 0.78],
        'pwa-icon-512x512.png' => [512, 0.78],
        'pwa-icon-maskable-512x512.png' => [512, 0.60],
        'pwa-apple-touch-icon.png' => [180, 0.76],
    ];

    /**
     * Devuelve la URL base configurada sin barra final.
     */
    public static function getBaseUrl(): string
    {
        if (defined('BASE_URL') && trim((string)BASE_URL) !== '') {
            return rtrim((string)BASE_URL, '/');
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'));
        $basePath = str_replace('\\', '/', $scriptDir);
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        return $scheme . '://' . $host . rtrim($basePath, '/');
    }

    /**
     * Devuelve el path base para el scope de la PWA ('/' o '/subcarpeta/').
     */
    public static function getBasePath(): string
    {
        $path = parse_url(self::getBaseUrl(), PHP_URL_PATH) ?? '';
        $trimmed = trim($path, '/');
        return $trimmed === '' ? '/' : '/' . $trimmed . '/';
    }

    /**
     * Ruta absoluta en disco hacia public/images/.
     */
    public static function getImagesDir(): string
    {
        return dirname(__DIR__, 2) . '/public/images';
    }

    /**
     * Normaliza un color hex a formato #rrggbb.
     */
    public static function normalizeHexColor(?string $color, string $fallback = '#0056b3'): string
    {
        $raw = trim((string)($color ?? ''));
        if ($raw === '') {
            return $fallback;
        }
        if ($raw[0] !== '#') {
            $raw = '#' . $raw;
        }
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $raw)) {
            return strtolower($raw);
        }
        if (preg_match('/^#([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f])$/', $raw, $m)) {
            return strtolower('#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3]);
        }
        return $fallback;
    }

    /**
     * Obtiene la configuración activa de sigi_datos_sistema (con caché en memoria por petición).
     */
    private static function fetchSystemRow(?array $sistema = null): array
    {
        if (is_array($sistema) && !empty($sistema)) {
            self::$cachedRow = $sistema;
            return $sistema;
        }
        if (self::$cachedRow !== null) {
            return self::$cachedRow;
        }

        try {
            $db = (new \Core\Model())->getDB();
            $stmt = $db->query('SELECT nombre_completo, nombre_corto, color_correo, logo, favicon FROM sigi_datos_sistema WHERE id = 1 LIMIT 1');
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
            self::$cachedRow = is_array($row) ? $row : [];
        } catch (\Throwable $e) {
            self::$cachedRow = [];
        }

        return self::$cachedRow;
    }

    /**
     * Indica si existen los íconos PWA generados a partir del logo subido en Datos del Sistema.
     */
    public static function tieneIconosPersonalizados(): bool
    {
        $dir = self::getImagesDir();
        foreach (array_keys(self::ICON_SPECS) as $filename) {
            if (!is_file($dir . '/' . $filename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Elimina íconos PWA generados previamente en public/images/.
     */
    public static function limpiarIconosPersonalizados(): void
    {
        $dir = self::getImagesDir();
        foreach (array_keys(self::ICON_SPECS) as $filename) {
            $path = $dir . '/' . $filename;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Devuelve los metadatos e íconos dinámicos de la PWA para la institución configurada.
     */
    public static function getConfig(?array $sistema = null): array
    {
        $row = self::fetchSystemRow($sistema);
        $baseUrl = self::getBaseUrl();
        $imagesDir = self::getImagesDir();

        $nombreCompleto = trim((string)($row['nombre_completo'] ?? ''));
        $nombreCorto = trim((string)($row['nombre_corto'] ?? ''));

        $name = $nombreCompleto !== '' ? $nombreCompleto : 'SIGI - Sistema de Gestión Institucional';
        $shortName = $nombreCorto !== '' ? $nombreCorto : 'SIGI';
        $themeColor = self::normalizeHexColor($row['color_correo'] ?? null, '#0056b3');

        $usaPersonalizados = self::tieneIconosPersonalizados();
        $version = $usaPersonalizados ? (string)(@filemtime($imagesDir . '/pwa-icon-192x192.png') ?: time()) : '1';

        if ($usaPersonalizados) {
            $icon192Rel = 'images/pwa-icon-192x192.png?v=' . $version;
            $icon512Rel = 'images/pwa-icon-512x512.png?v=' . $version;
            $iconMaskableRel = 'images/pwa-icon-maskable-512x512.png?v=' . $version;
            $appleIconRel = 'images/pwa-apple-touch-icon.png?v=' . $version;
        } else {
            $icon192Rel = 'img/icon-192x192.png';
            $icon512Rel = 'img/icon-512x512.png';
            $iconMaskableRel = 'img/icon-maskable-512x512.png';
            $appleIconRel = 'img/apple-touch-icon.png';
        }

        $logoArchivo = trim((string)($row['logo'] ?? ''));
        $logoRel = ($logoArchivo !== '' && is_file($imagesDir . '/' . $logoArchivo))
            ? 'images/' . $logoArchivo
            : 'img/logo_completo.png';

        return [
            'name' => $name,
            'short_name' => $shortName,
            'description' => 'SIGI - ' . $name,
            'theme_color' => $themeColor,
            'background_color' => '#ffffff',
            'base_url' => $baseUrl,
            'base_path' => self::getBasePath(),
            'manifest_url' => $baseUrl . '/manifest.json',
            'pwa_js_url' => $baseUrl . '/assets/js/pwa.js',
            'apple_icon_url' => $baseUrl . '/' . $appleIconRel,
            'logo_url' => $baseUrl . '/' . $logoRel,
            'icons' => [
                '192' => ['rel' => $icon192Rel, 'url' => $baseUrl . '/' . $icon192Rel],
                '512' => ['rel' => $icon512Rel, 'url' => $baseUrl . '/' . $icon512Rel],
                'maskable' => ['rel' => $iconMaskableRel, 'url' => $baseUrl . '/' . $iconMaskableRel],
                'apple' => ['rel' => $appleIconRel, 'url' => $baseUrl . '/' . $appleIconRel],
            ],
            'usa_personalizados' => $usaPersonalizados,
        ];
    }

    /**
     * Genera los íconos PNG requeridos por la PWA a partir del logo subido.
     */
    public static function generarIconosDesdeLogo(string $sourcePath): bool
    {
        if (!extension_loaded('gd') || !is_file($sourcePath)) {
            return false;
        }

        $raw = @file_get_contents($sourcePath);
        if ($raw === false) {
            return false;
        }

        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return false;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);
            return false;
        }

        $outDir = self::getImagesDir();
        if (!is_dir($outDir) && !@mkdir($outDir, 0755, true)) {
            imagedestroy($src);
            return false;
        }

        $allOk = true;
        foreach (self::ICON_SPECS as $filename => [$size, $scaleRatio]) {
            if (!self::writePaddedPng($src, $srcW, $srcH, $size, $scaleRatio, $outDir . '/' . $filename)) {
                $allOk = false;
                break;
            }
        }

        imagedestroy($src);
        if (!$allOk) {
            self::limpiarIconosPersonalizados();
        }

        return $allOk;
    }

    private static function writePaddedPng($src, int $srcW, int $srcH, int $size, float $scaleRatio, string $destPath): bool
    {
        $dst = imagecreatetruecolor($size, $size);
        if (!$dst) {
            return false;
        }

        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagealphablending($dst, true);
        imagesavealpha($dst, true);

        $maxDim = (int)round($size * $scaleRatio);
        $aspect = $srcW / $srcH;
        if ($aspect >= 1) {
            $targetW = $maxDim;
            $targetH = max(1, (int)round($maxDim / $aspect));
        } else {
            $targetH = $maxDim;
            $targetW = max(1, (int)round($maxDim * $aspect));
        }

        $dstX = (int)round(($size - $targetW) / 2);
        $dstY = (int)round(($size - $targetH) / 2);

        imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $targetW, $targetH, $srcW, $srcH);
        $ok = imagepng($dst, $destPath, 9);
        imagedestroy($dst);

        return $ok;
    }

    /**
     * Emite dinámicamente el Web App Manifest en JSON según los datos del sistema.
     */
    public static function renderManifest(): void
    {
        $cfg = self::getConfig();
        $manifest = [
            'id' => './',
            'name' => $cfg['name'],
            'short_name' => $cfg['short_name'],
            'description' => $cfg['description'],
            'start_url' => './intranet?source=pwa',
            'scope' => './',
            'display' => 'standalone',
            'background_color' => $cfg['background_color'],
            'theme_color' => $cfg['theme_color'],
            'orientation' => 'portrait-primary',
            'lang' => 'es',
            'icons' => [
                [
                    'src' => './' . $cfg['icons']['192']['rel'],
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => './' . $cfg['icons']['512']['rel'],
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => './' . $cfg['icons']['maskable']['rel'],
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Emite dinámicamente la vista offline usando el nombre corto y color institucional.
     */
    public static function renderOfflinePage(): void
    {
        $cfg = self::getConfig();
        $shortName = htmlspecialchars($cfg['short_name'], ENT_QUOTES, 'UTF-8');
        $themeColor = htmlspecialchars($cfg['theme_color'], ENT_QUOTES, 'UTF-8');

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="{$themeColor}">
    <title>Sin conexión - {$shortName}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }
        .offline-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 14px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border-top: 4px solid {$themeColor};
        }
        .offline-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #f3f4f6;
            color: {$themeColor};
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1 { font-size: 20px; margin: 0 0 8px; color: #111827; }
        p { font-size: 14px; line-height: 1.5; color: #4b5563; margin: 0 0 24px; }
        .btn-retry {
            display: inline-block;
            width: 100%;
            padding: 12px 16px;
            border: 0;
            border-radius: 8px;
            background: {$themeColor};
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="1" y1="1" x2="23" y2="23"></line>
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
                <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                <line x1="12" y1="20" x2="12.01" y2="20"></line>
            </svg>
        </div>
        <h1>Sin conexión a internet</h1>
        <p>{$shortName} necesita conexión a la red para consultar y guardar información en el sistema. Verifica tu Wi-Fi o datos móviles e intenta nuevamente.</p>
        <button type="button" class="btn-retry" onclick="window.location.reload()">Reintentar</button>
    </div>
</body>
</html>
HTML;
    }
}

