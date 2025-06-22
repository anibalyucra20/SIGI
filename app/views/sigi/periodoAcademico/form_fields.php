<div class="row">
    <div class="col-md-3 mb-2">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" class="form-control" maxlength="10" required value="<?= htmlspecialchars($periodo['nombre'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Fecha Inicio *</label>
        <input type="date" name="fecha_inicio" class="form-control" required value="<?= htmlspecialchars($periodo['fecha_inicio'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Fecha Fin *</label>
        <input type="date" name="fecha_fin" class="form-control" required value="<?= htmlspecialchars($periodo['fecha_fin'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Fecha Actas *</label>
        <input type="date" name="fecha_actas" class="form-control" required value="<?= htmlspecialchars($periodo['fecha_actas'] ?? '') ?>">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Director *</label>
        <select name="director" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($directores as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (!empty($periodo['director']) && $periodo['director'] == $d['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['apellidos_nombres']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
