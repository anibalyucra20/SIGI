<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid px-4">
  <h4>Editar Movimiento</h4>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <form action="<?= BASE_URL ?>/inventario/movimientos/guardar" method="POST">
                <!-- ID Oculto para identificar que es una actualización -->
                <input type="hidden" name="id" value="<?= $data['id'] ?? '' ?>">

                <div class="row">
                    <!-- Ambiente Origen -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold text-dark">Ambiente Origen:</label>
                        <select name="id_inv_ambiente_origen" class="form-control">
                            <option value="">-- SELECCIONE ORIGEN --</option>
                            <?php foreach ($ambientes as $amb): ?>
                                <?php $id_amb = $amb['id_inventario'] ?? $amb['id'] ?? ''; ?>
                                <option value="<?= $id_amb ?>" <?= (isset($data['id_inv_ambiente_origen']) && $data['id_inv_ambiente_origen'] == $id_amb) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($amb['tipo_ambiente'] ?? '') . (!empty($amb['nro']) ? ' - ' . $amb['nro'] : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ambiente Destino -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold text-dark">Ambiente Destino:</label>
                        <select name="id_inv_ambiente_destino" class="form-control">
                            <option value="">-- SELECCIONE DESTINO --</option>
                            <?php foreach ($ambientes as $amb): ?>
                                <?php $id_amb = $amb['id_inventario'] ?? $amb['id'] ?? ''; ?>
                                <option value="<?= $id_amb ?>" <?= (isset($data['id_inv_ambiente_destino']) && $data['id_inv_ambiente_destino'] == $id_amb) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($amb['tipo_ambiente'] ?? '') . (!empty($amb['nro']) ? ' - ' . $amb['nro'] : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Tipo de Movimiento -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold text-dark">Tipo de Movimiento:</label>
                        <select name="tipo" class="form-control" required>
                            <option value="TRASLADO" <?= (isset($data['tipo']) && $data['tipo'] == 'TRASLADO') ? 'selected' : '' ?>>TRASLADO</option>
                            <option value="SALIDA" <?= (isset($data['tipo']) && $data['tipo'] == 'SALIDA') ? 'selected' : '' ?>>SALIDA</option>
                            <option value="INGRESO" <?= (isset($data['tipo']) && $data['tipo'] == 'INGRESO') ? 'selected' : '' ?>>INGRESO</option>
                        </select>
                    </div>

                    <!-- Motivo -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold text-dark">Motivo:</label>
                        <input type="text" name="motivo" class="form-control" value="<?= htmlspecialchars($data['motivo'] ?? '') ?>" required>
                    </div>

                    <!-- Documento de Traslado -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold text-dark">Documento de Traslado:</label>
                        <input type="text" name="documento_traslado" class="form-control" value="<?= htmlspecialchars($data['documento_traslado'] ?? '') ?>">
                    </div>
                </div>

                <div class="mt-4 d-flex" style="gap: 10px;">
                    <button type="submit" class="btn btn-success px-4">Guardar</button>
                    <a href="<?= BASE_URL ?>/inventario/movimientos"class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
 
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>