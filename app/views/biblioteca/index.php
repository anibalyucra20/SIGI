<?php require __DIR__ . '/../layouts/header.php'; ?>

<h3 class="mb-4">BIBLITOECA VRTUAL</h3>

<div class="row">
  <?php if (\Core\Auth::tieneRolEnBiblioteca()): ?>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Periodo actual</h5>
          <p class="display-5"><?= htmlspecialchars($periodo) ?></p>
        </div>
      </div>
    </div>
    <?php if (\Core\Auth::esDocenteAcademico()): ?>
      <div class="col-md-3">
        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Mis Unidades Didácticas</h5>
            <p class="display-5"><?= $uds_count ?></p>
          </div>
        </div>
        </a>
      </div>
    <?php endif; ?>
    <?php if (\Core\Auth::esCoordinadorPEAcademico()|| \Core\Auth::esDirectorAcademico()|| \Core\Auth::esJUAAcademico()|| \Core\Auth::esSecretarioAcadAcademico()|| \Core\Auth::esAdminAcademico()): ?>
      <div class="col-md-3">
        <a href="<?= BASE_URL ?>/academico/reportes">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">reportes</h5>
            <p class="display-5"><?= htmlspecialchars($periodo) ?></p>
          </div>
        </div>
        </a>
      </div>
    <?php endif; ?>
    <?php if (\Core\Auth::esAdminAcademico()): ?>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Sedes</h5>
            <p class="display-5"><?= $sedes_count ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Programas de estudio</h5>
            <p class="display-5"><?= $programas ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Docentes</h5>
            <p class="display-5"><?= $docentes ?></p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>