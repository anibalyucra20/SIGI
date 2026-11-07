<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Ambiente de Origen</label>
        <select name="id_inv_ambiente_origen" class="form-control" id="id_inv_ambiente_origen">
            <option value="0">Seleccione un ambiente de origen (Opcional si es entrada)</option>
            <?php
            foreach ($ambientes as $ambiente) {
                $selected = ($isEdit && isset($data['id_inv_ambiente_origen']) && $data['id_inv_ambiente_origen'] == $ambiente['id_inventario']) ? 'selected' : '';
            ?>
                <option value="<?= $ambiente['id_inventario'] ?>" <?= $selected ?>><?= htmlspecialchars($ambiente['tipo_ambiente']) ?></option>
            <?php
            }
            ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Ambiente de Destino</label>
        <select name="id_inv_ambiente_destino" class="form-control" id="id_inv_ambiente_destino">
            <option value="0">Seleccione un ambiente de destino (Opcional si es salida)</option>
            <?php
            foreach ($ambientes as $ambiente) {
                $selected = ($isEdit && isset($data['id_inv_ambiente_destino']) && $data['id_inv_ambiente_destino'] == $ambiente['id_inventario']) ? 'selected' : '';
            ?>
                <option value="<?= $ambiente['id_inventario'] ?>" <?= $selected ?>><?= htmlspecialchars($ambiente['tipo_ambiente']) ?></option>
            <?php
            }
            ?>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Tipo de Movimiento *</label>
        <select name="tipo" class="form-control" required>
            <option value="ENTRADA" <?= (isset($data['tipo']) && $data['tipo'] == 'ENTRADA') ? 'selected' : '' ?>>ENTRADA</option>
            <option value="SALIDA" <?= (isset($data['tipo']) && $data['tipo'] == 'SALIDA') ? 'selected' : '' ?>>SALIDA</option>
            <option value="TRASLADO" <?= (isset($data['tipo']) && $data['tipo'] == 'TRASLADO') ? 'selected' : '' ?>>TRASLADO</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Motivo *</label>
        <input type="text" name="motivo" class="form-control" value="<?= htmlspecialchars($data['motivo'] ?? '') ?>" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Documento de Traslado</label>
        <input type="text" name="documento_traslado" class="form-control" placeholder="Ej: Guía de remisión 001-234" value="<?= htmlspecialchars($data['documento_traslado'] ?? '') ?>">
    </div>
    
    <!-- Este campo oculto es importante para saber si estamos editando -->
    <?php if (isset($data['id'])): ?>
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
    <?php endif; ?>
</div>