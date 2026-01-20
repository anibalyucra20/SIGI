<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php if (!empty($_SESSION['flash_error'])) : ?>
  <div class="alert alert-danger alert-dismissible">
    <?= $_SESSION['flash_error'] ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<h4 class="mb-4">Bienvenido, <?= htmlspecialchars($_SESSION['sigi_user_name']) ?></h4>
<?php
/*
$apiKey = 'mi-clave-super'; // Reemplaza con tu clave API
$url = API_BASE_URL.'/api/health'; // Reemplaza con la URL de la API

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Recibir respuesta como string
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'X-API-KEY: ' . $apiKey, // Añadir la cabecera X-API-KEY
  'Content-Type: application/json' // O el tipo de contenido que espera la API
));

$response = curl_exec($ch);

if (curl_errno($ch)) {
  echo 'Error en la solicitud cURL: ' . curl_error($ch);
} else {
  echo 'Respuesta de la API: ' . $response;
}

curl_close($ch);
*/
?>

<div class="row">
  <?php foreach ($sistemas as $s) {
    if ($s['codigo'] == 'AULA') {
      if (MOODLE_SYNC_ACTIVE == true && $id_moodle > 0) {
  ?>
        <div class="col-md-4 col-lg-3 mb-4">
          <a href="<?= BASE_URL . '/aula/moodle' ?>" class="text-decoration-none" target="_blank">
            <div class="card shadow-sm h-100 text-center py-4">
              <i class="<?= htmlspecialchars($s['icono']) ?> display-4 active mb-3" style="color: <?= $datos_sistema['color_correo']; ?>;"></i>
              <h5 class="card-title"><?= htmlspecialchars($s['nombre']) ?></h5>
            </div>
          </a>
        </div>
      <?php
      }
    } else {
      ?>
      <div class="col-md-4 col-lg-3 mb-4">
        <a href="<?= BASE_URL ?>/<?= strtolower($s['codigo']) ?>" class="text-decoration-none">
          <div class="card shadow-sm h-100 text-center py-4">
            <i class="<?= htmlspecialchars($s['icono']) ?> display-4 active mb-3" style="color: <?= $datos_sistema['color_correo']; ?>;"></i>
            <h5 class="card-title"><?= htmlspecialchars($s['nombre']) ?></h5>
          </div>
        </a>
      </div>
  <?php
    }
  }; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>