<div class="mb-3">
    <label class="form-label">Programa de Estudio *</label>
    <select name="id_programa_estudios" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php foreach ($programas as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (isset($plan['id_programa_estudios']) && $plan['id_programa_estudios'] == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Nombre del Plan *</label>
    <input type="text" name="nombre" class="form-control" maxlength="20" required
        value="<?= htmlspecialchars($plan['nombre'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Resolución *</label>
    <input type="text" name="resolucion" class="form-control" maxlength="100" required
        value="<?= htmlspecialchars($plan['resolucion'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Perfil de Egresado *</label>
    <textarea name="perfil_egresado" class="form-control" rows="4" maxlength="3000" required><?= htmlspecialchars($plan['perfil_egresado'] ?? '') ?></textarea>
</div>
