<input type="hidden" name="id_capacidad" value="<?= htmlspecialchars($id_capacidad) ?>">
<div class="mb-3">
    <label class="form-label">Código *</label>
    <input type="text" name="codigo" class="form-control" maxlength="10" required
        value="<?= htmlspecialchars($indicador['codigo'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Descripción *</label>
    <textarea name="descripcion" class="form-control" maxlength="3000" required rows="3"><?= htmlspecialchars($indicador['descripcion'] ?? '') ?></textarea>
</div>
