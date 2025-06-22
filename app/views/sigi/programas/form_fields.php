<div class="mb-3">
    <label class="form-label">Código *</label>
    <input type="text" name="codigo" class="form-control" maxlength="10" required
        value="<?= htmlspecialchars($programa['codigo'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Tipo *</label>
    <input type="text" name="tipo" class="form-control" maxlength="20" required
        value="<?= htmlspecialchars($programa['tipo'] ?? 'Modular') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Nombre *</label>
    <input type="text" name="nombre" class="form-control" maxlength="100" required
        value="<?= htmlspecialchars($programa['nombre'] ?? '') ?>">
</div>
