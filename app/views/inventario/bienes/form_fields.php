<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Ambiente *</label>
        <select name="id_inv_ambiente" class="form-control" required>
            <option value="">Seleccione un ambiente...</option>
            <option value="1" <?= (isset($bien['id_inv_ambiente']) && $bien['id_inv_ambiente'] == '1') ? 'selected' : '' ?>>Ambiente 1</option>
            <option value="2" <?= (isset($bien['id_inv_ambiente']) && $bien['id_inv_ambiente'] == '2') ? 'selected' : '' ?>>Ambiente 2</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Código Patrimonial *</label>
        <input type="text" name="codigo_patrimonial" class="form-control" value="<?= htmlspecialchars($bien['codigo_patrimonial'] ?? '') ?>" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Denominación *</label>
        <input type="text" name="denominacion" class="form-control" value="<?= htmlspecialchars($bien['denominacion'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Marca</label>
        <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($bien['marca'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Modelo</label>
        <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($bien['modelo'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($bien['color'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Serie</label>
        <input type="text" name="serie" class="form-control" value="<?= htmlspecialchars($bien['serie'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Estado del Bien</label>
        <select name="estado_bien" class="form-control" required>
            <option value="BUENO" <?= (isset($bien['estado_bien']) && $bien['estado_bien'] == 'BUENO') ? 'selected' : '' ?>>BUENO</option>
            <option value="REGULAR" <?= (isset($bien['estado_bien']) && $bien['estado_bien'] == 'REGULAR') ? 'selected' : '' ?>>REGULAR</option>
            <option value="MALO" <?= (isset($bien['estado_bien']) && $bien['estado_bien'] == 'MALO') ? 'selected' : '' ?>>MALO</option>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Otros</label>
        <input type="text" name="otros" class="form-control" value="<?= htmlspecialchars($bien['otros'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Observaciones</label>
        <input type="text" name="observaciones" class="form-control" value="<?= htmlspecialchars($bien['observaciones'] ?? '') ?>">
    </div>
</div>