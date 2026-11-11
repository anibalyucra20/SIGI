<?php
require_once dirname(__DIR__, 2) . '/helpers/PwaHelper.php';
$pwa = \App\Helpers\PwaHelper::getConfig($datosSistema ?? ($sistema ?? null));
?>
<meta name="theme-color" content="<?= htmlspecialchars($pwa['theme_color'], ENT_QUOTES, 'UTF-8') ?>">
<meta name="msapplication-TileColor" content="<?= htmlspecialchars($pwa['theme_color'], ENT_QUOTES, 'UTF-8') ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($pwa['short_name'], ENT_QUOTES, 'UTF-8') ?>">
<link rel="manifest" href="<?= htmlspecialchars($pwa['manifest_url'], ENT_QUOTES, 'UTF-8') ?>">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($pwa['apple_icon_url'], ENT_QUOTES, 'UTF-8') ?>">
<script src="<?= htmlspecialchars($pwa['pwa_js_url'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
