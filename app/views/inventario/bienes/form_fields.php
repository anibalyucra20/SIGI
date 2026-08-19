<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Ambiente *</label>
        <select name="id_inv_ambiente" class="form-control" required>
            <option value="">Seleccione un ambiente...</option>
            <option value="1" <?= (isset($data['id_inv_ambiente']) && $data['id_inv_ambiente'] == '1') ? 'selected' : '' ?>>Ambiente 1</option>
            <option value="2" <?= (isset($data['id_inv_ambiente']) && $data['id_inv_ambiente'] == '2') ? 'selected' : '' ?>>Ambiente 2</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Código Patrimonial *</label>
        <input type="text" name="codigo_patrimonial" class="form-control" value="<?= htmlspecialchars($data['codigo_patrimonial'] ?? '') ?>" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Denominación *</label>
        <input type="text" name="denominacion" class="form-control" value="<?= htmlspecialchars($data['denominacion'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Marca</label>
        <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($data['marca'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Modelo</label>
        <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($data['modelo'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($data['color'] ?? '') ?>">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Serie</label>
        <input type="text" name="serie" class="form-control" value="<?= htmlspecialchars($data['serie'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Estado del Bien</label>
        <select name="estado_bien" class="form-control" required>
            <option value="BUENO" <?= (isset($data['estado_bien']) && $data['estado_bien'] == 'BUENO') ? 'selected' : '' ?>>BUENO</option>
            <option value="REGULAR" <?= (isset($data['estado_bien']) && $data['estado_bien'] == 'REGULAR') ? 'selected' : '' ?>>REGULAR</option>
            <option value="MALO" <?= (isset($data['estado_bien']) && $data['estado_bien'] == 'MALO') ? 'selected' : '' ?>>MALO</option>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Otros</label>
        <input type="text" name="otros" class="form-control" value="<?= htmlspecialchars($data['otros'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Observaciones</label>
        <input type="text" name="observaciones" class="form-control" value="<?= htmlspecialchars($data['observaciones'] ?? '') ?>">
    </div>
</div>