<!-- editar.php -->
<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
  <div class="container mt-4">
    <h2>Editar Docente</h2>
    <div class="card p-2">
      <form action="<?= BASE_URL ?>/sigi/docentes/actualizar/<?= $docente['id'] ?>" method="post">
        <?php require __DIR__ . '/form_fields.php'; ?>
        <button class="btn btn-primary">Actualizar</button>
        <a href="<?= BASE_URL ?>/sigi/docentes" class="btn btn-secondary">Volver</a>
      </form>
    </div>
  </div>
  <br>
  <br>
<?php else: ?>
  <!-- Para director o coordinador en SIGI -->
  <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>