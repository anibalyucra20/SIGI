<div class="row">
  <div class="mb-2 col-md-12">
    <label class="form-label">Código *</label>
    <input type="text" name="codigo" class="form-control" maxlength="20" required value="<?= htmlspecialchars($data['codigo'] ?? '') ?>">
  </div>
  <div class="mb-2 col-md-12">
    <label class="form-label">Nombre de la Cuenta *</label>
    <input type="text" name="nombre" class="form-control" maxlength="200" required rows="3" value="<?= htmlspecialchars($data['nombre'] ?? '') ?>">
  </div>
  <div class="mb-2 col-md-4">
    <label class="form-label">Estado *</label>
    <input type="text" name="estado" class="form-control" maxlength="20" required value="<?= htmlspecialchars($data['estado'] ?? '') ?>">
  </div>
</div>