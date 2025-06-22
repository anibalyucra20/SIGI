<?php
// Si llega sin definir, asume "false"
$isEdit = $isEdit ?? false;
?>

<!-- DNI -->
<div class="mb-3">
  <label class="form-label">DNI</label>
  <input type="text" name="dni" class="form-control"
    maxlength="8" pattern="\d{8}"
    value="<?= $docente['dni'] ?? '' ?>" required>
</div>

<!-- Apellidos y Nombres -->
<div class="mb-3">
  <label class="form-label">Apellidos y Nombres</label>
  <input type="text" name="apellidos_nombres" class="form-control"
    maxlength="120"
    value="<?= $docente['apellidos_nombres'] ?? '' ?>" required>
</div>

<!-- Género -->
<div class="mb-3">
  <label class="form-label">Género</label>
  <select name="genero" class="form-control" required>
    <option value="" selected disabled>Seleccione</option>
    <option value="M" <?= (isset($docente) && $docente['genero'] == 'M') ? 'selected' : '' ?>>Masculino</option>
    <option value="F" <?= (isset($docente) && $docente['genero'] == 'F') ? 'selected' : '' ?>>Femenino</option>
  </select>
</div>

<!-- Fecha de nacimiento -->
<div class="mb-3">
  <label class="form-label">Fecha de Nacimiento</label>
  <input type="date" name="fecha_nacimiento" class="form-control"
    value="<?= $docente['fecha_nacimiento'] ?? '' ?>" required>
</div>

<!-- Dirección -->
<div class="mb-3">
  <label class="form-label">Dirección</label>
  <input type="text" name="direccion" class="form-control"
    maxlength="150"
    value="<?= $docente['direccion'] ?? '' ?>">
</div>

<!-- Correo -->
<div class="mb-3">
  <label class="form-label">Correo electrónico</label>
  <input type="email" name="correo" class="form-control"
    maxlength="120"
    value="<?= $docente['correo'] ?? '' ?>" required>
</div>

<!-- Teléfono -->
<div class="mb-3">
  <label class="form-label">Teléfono</label>
  <input type="text" name="telefono" class="form-control"
    maxlength="15"
    value="<?= $docente['telefono'] ?? '' ?>">
</div>

<!-- Discapacidad -->
<div class="mb-3">
  <label class="form-label">Discapacidad</label>
  <select name="discapacidad" class="form-control" required>
    <option value="NO" <?= (isset($docente) && $docente['discapacidad'] == 'NO') ? 'selected' : '' ?>>No</option>
    <option value="SI" <?= (isset($docente) && $docente['discapacidad'] == 'SI') ? 'selected' : '' ?>>Sí</option>
  </select>
</div>

<!-- Estado -->
<?php if ($isEdit): /* Solo al editar */ ?>
  <!-- Estado (Activo/Inactivo) -->
  <div class="mb-3">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-control" required>
      <option value="1" <?= ($docente['estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
      <option value="0" <?= ($docente['estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
    </select>
  </div>
<?php endif; ?>
<!-- Rol -->
<div class="mb-3">
  <label class="form-label">Rol</label>
  <select name="id_rol" class="form-control" required>
    <option value="" selected disabled>Seleccione</option>
    <?php foreach ($roles as $r): ?>
      <option value="<?= $r['id'] ?>"
        <?= isset($docente) && $docente['id_rol'] == $r['id'] ? 'selected' : '' ?>>
        <?= $r['nombre'] ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Sede -->
<div class="mb-3">
  <label class="form-label">Sede</label>
  <select name="id_sede" class="form-control" required>
    <option value="" selected disabled>Seleccione</option>
    <?php foreach ($sedes as $s): ?>
      <option value="<?= $s['id'] ?>"
        <?= isset($docente) && $docente['id_sede'] == $s['id'] ? 'selected' : '' ?>>
        <?= $s['nombre'] ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Programa de Estudio -->
<div class="mb-3">
  <label class="form-label">Programa de Estudio</label>
  <select name="id_programa_registro" class="form-control" required>
    <option value="" selected disabled>Seleccione</option>
    <?php foreach ($programas as $p): ?>
      <option value="<?= $p['id'] ?>"
        <?= isset($docente) && $docente['id_programa_estudios'] == $p['id'] ? 'selected' : '' ?>>
        <?= $p['nombre'] ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
