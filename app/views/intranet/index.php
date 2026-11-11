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
<h4 class="mb-3">Bienvenido, <?= htmlspecialchars($_SESSION['sigi_user_name']) ?></h4>

<?php if (!empty($ultimaVista['ruta'])): ?>
  <?php
  $colorTema = htmlspecialchars($datos_sistema['color_correo'] ?? '#0056b3', ENT_QUOTES, 'UTF-8');
  $fechaVista = !empty($ultimaVista['updated_at']) ? date('d/m/Y H:i', strtotime($ultimaVista['updated_at'])) : '';
  ?>
  <!--<div class="card shadow-sm mb-4" style="border-left: 4px solid <?= $colorTema ?>;">
    <div class="card-body py-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
      <div class="mb-2 mb-md-0 pr-md-3">
        <div class="small text-uppercase font-weight-bold text-muted mb-1">
          <i class="fas fa-history mr-1"></i> Continuar donde lo dejaste
          <?php if ($fechaVista !== ''): ?>
            <span class="font-weight-normal ml-1">(<?= $fechaVista ?>)</span>
          <?php endif; ?>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: 16px;">
          <?= htmlspecialchars($ultimaVista['titulo'] ?? 'Última vista trabajada') ?>
        </div>
        <div class="custom-control custom-switch mt-2">
          <input type="checkbox" class="custom-control-input" id="switch-auto-resume">
          <label class="custom-control-label small text-muted" for="switch-auto-resume">
            Abrir automáticamente al iniciar la app desde el celular
          </label>
        </div>
      </div>
      <div class="d-flex align-items-center w-100 w-md-auto justify-content-end">
        <a href="<?= BASE_URL ?>/intranet/home/continuar" class="btn btn-primary btn-sm px-3 mr-2 flex-fill flex-md-grow-0" id="btn-continuar-ultima-vista" style="background-color: <?= $colorTema ?>; border-color: <?= $colorTema ?>;">
          <i class="fas fa-arrow-right mr-1"></i> Continuar
        </a>
        <a href="<?= BASE_URL ?>/intranet/home/descartarUltimaVista" class="btn btn-outline-secondary btn-sm" title="Ocultar acceso rápido">
          <i class="fas fa-times"></i>
        </a>
      </div>
    </div>
  </div>
  <script>
    (function() {
      var sw = document.getElementById('switch-auto-resume');
      var continueUrl = '<?= BASE_URL ?>/intranet/home/continuar';
      var params = new URLSearchParams(window.location.search);

      // Si se abrió desde el icono de la app móvil (source=pwa) y está activado auto-abrir
      if (params.get('source') === 'pwa' && localStorage.getItem('sigi_auto_resume') === '1') {
        window.location.replace(continueUrl);
        return;
      }

      if (sw) {
        sw.checked = localStorage.getItem('sigi_auto_resume') === '1';
        sw.addEventListener('change', function() {
          localStorage.setItem('sigi_auto_resume', this.checked ? '1' : '0');
        });
      }
    })();
  </script>-->
<?php endif; ?>
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
      if (INTEGRACIONES_SYNC_ACTIVE == true && $id_moodle > 0) {
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