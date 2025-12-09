<div class="row">
  <div class="col-md-4 p-2">
    <label class="form-label">RUC *</label>
    <input type="text" name="ruc" class="form-control" maxlength="11" required value="<?= ($data['ruc'] ?? '') ?>">
  </div>
  <div class="col-md-8 p-2">
    <label class="form-label">Razón Social *</label>
    <input type="text" name="razon_social" class="form-control" maxlength="200" required value="<?= htmlspecialchars($data['razon_social'] ?? '') ?>">
  </div>
  <div class="col-md-5 p-2">
    <label class="form-label">Dirección *</label>
    <input type="text" name="direccion" class="form-control" maxlength="300" required value="<?= htmlspecialchars($data['direccion'] ?? '') ?>">
  </div>
  <div class="col-md-2 p-2">
    <label class="form-label">Telefono *</label>
    <input type="text" name="telefono" class="form-control" maxlength="20" required value="<?= ($data['telefono'] ?? '') ?>">
  </div>
  <div class="col-md-5 p-2">
    <label class="form-label">Correo Electrónico *</label>
    <input type="text" name="correo" class="form-control" maxlength="100" required value="<?= htmlspecialchars($data['correo'] ?? '') ?>">
  </div>
</div>
<div class="mb-2">
  <label class="form-label">Referencia de Contacto *</label>
  <textarea name="ref_contacto" class="form-control" maxlength="200" required rows="3"><?= htmlspecialchars($data['ref_contacto'] ?? '') ?></textarea>
</div>
<?php if ($isEdit): /* Solo al editar */ ?>
  <!-- Estado (Activo/Inactivo) -->
  <div class="mb-2">
    <label class="form-label">Estado</label>
    <select name="estado" class="form-control" required>
      <option value="1" <?= ($data['estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
      <option value="0" <?= ($data['estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
    </select>
  </div>
<?php endif; ?>