<input type="hidden" name="id_competencia" value="<?= htmlspecialchars($id_competencia) ?>">
<div class="mb-3">
    <label class="form-label">Correlativo *</label>
    <input type="number" name="correlativo" class="form-control" min="1" max="99" required
        value="<?= htmlspecialchars($indicador['correlativo'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Descripción *</label>
    <textarea name="descripcion" class="form-control" maxlength="3000" required rows="3"><?= htmlspecialchars($indicador['descripcion'] ?? '') ?></textarea>
</div>
