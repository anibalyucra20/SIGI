<div class="row">
  <div class="mb-2 col-md-12">
    <label class="form-label">Código *</label>
    <input type="text" name="codigo" class="form-control" maxlength="20" required value="<?= htmlspecialchars($data['codigo'] ?? '') ?>">
  </div>
  <div class="mb-2 col-md-12">
    <label class="form-label">Descripción *</label>
    <textarea name="descripcion" class="form-control" maxlength="200" required rows="3"><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
  </div>
  <div class="mb-2 col-md-4">
    <label class="form-label">Clasificador *</label>
    <input type="text" name="clasificador" class="form-control" maxlength="30" value="<?= htmlspecialchars($data['clasificador'] ?? '') ?>">
  </div>
  <?php if ($isEdit): /* Solo al editar */ ?>
    <!-- Estado (Activo/Inactivo) -->
    <div class="mb-2 col-md-4">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-control" required>
        <option value="1" <?= ($data['estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
        <option value="0" <?= ($data['estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </div>
  <?php endif; ?>
</div>