<div class="row">
  <div class="mb-2 col-md-6">
    <label class="form-label">Año Mes *</label>
    <input type="text" name="anio_mes" class="form-control" maxlength="6" required value="<?= htmlspecialchars($data['anio_mes'] ?? '') ?>">
  </div>
  <div class="mb-2 col-md-6">
    <label class="form-label">Saldo Inicial *</label>
    <input type="number" name="saldo_inicial" class="form-control" maxlength="13" required rows="3" value="<?= htmlspecialchars($data['saldo_inicial'] ?? '') ?>">
  </div>
</div>