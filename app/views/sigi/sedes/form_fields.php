<div class="mb-3">
    <label class="form-label">Código Modular *</label>
    <input type="text" name="cod_modular" class="form-control" maxlength="20" required
        value="<?= htmlspecialchars($sede['cod_modular'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Nombre *</label>
    <input type="text" name="nombre" class="form-control" maxlength="200" required
        value="<?= htmlspecialchars($sede['nombre'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Departamento *</label>
    <input type="text" name="departamento" class="form-control" maxlength="50" required
        value="<?= htmlspecialchars($sede['departamento'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Provincia *</label>
    <input type="text" name="provincia" class="form-control" maxlength="50" required
        value="<?= htmlspecialchars($sede['provincia'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Distrito *</label>
    <input type="text" name="distrito" class="form-control" maxlength="50" required
        value="<?= htmlspecialchars($sede['distrito'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Dirección *</label>
    <input type="text" name="direccion" class="form-control" maxlength="200" required
        value="<?= htmlspecialchars($sede['direccion'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Teléfono *</label>
    <input type="text" name="telefono" class="form-control" maxlength="15" required
        value="<?= htmlspecialchars($sede['telefono'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Correo *</label>
    <input type="email" name="correo" class="form-control" maxlength="100" required
        value="<?= htmlspecialchars($sede['correo'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Responsable *</label>
    <select name="responsable" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php foreach ($responsables as $u): ?>
            <option value="<?= $u['id'] ?>" <?= (isset($sede['responsable']) && $sede['responsable'] == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['apellidos_nombres']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
