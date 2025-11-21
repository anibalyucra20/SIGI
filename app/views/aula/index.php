<?php require __DIR__ . '/../layouts/header.php'; ?>

<h3 class="mb-4">AULA VIRTUAL</h3>

<div class="row">
  <?php if (\Core\Auth::tieneRolEnAula()): ?>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Periodo actual</h5>
          <p class="display-5"><?= htmlspecialchars($periodo) ?></p>
        </div>
      </div>
    </div>
    <?php if (\Core\Auth::tieneRolEnAula()|| \Core\Auth::esDirectorAcademico()|| \Core\Auth::esJUAAcademico()|| \Core\Auth::esSecretarioAcadAcademico()|| \Core\Auth::esAdminAcademico()): ?>
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
  <?php endif; ?>

</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>