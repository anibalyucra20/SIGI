<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label font-weight-bold">RUC</label>
        <div class="input-group">
            <input type="text" name="ruc" id="ruc" class="form-control" maxlength="11" minlength="11" pattern="[0-9]{11}" placeholder="" value="<?= htmlspecialchars($empresa['ruc'] ?? '') ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label font-weight-bold">Razón Social / Empresa</label>
        <div class="input-group">
            <input type="text" name="empresa" class="form-control" maxlength="200" placeholder="" value="<?= htmlspecialchars($empresa['empresa'] ?? '') ?>" required>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label font-weight-bold">Dirección Fiscal / Sede</label>
        <div class="input-group">
            <input type="text" name="direccion" class="form-control" maxlength="200" placeholder="" value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>" required>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label font-weight-bold">Teléfono / Celular</label>
        <div class="input-group">
            <input type="tel" name="telefono" class="form-control" maxlength="9" minlength="9"pattern="^9[0-9]{8}$"inputmode="numeric"oninput="this.value = this.value.replace(/[^0-9]/g, '')"placeholder="" title="Ingrese un número de celular válido de 9 dígitos que empiece con 9"value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>" required>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label font-weight-bold">Correo Electrónico</label>
        <div class="input-group">
            <input type="email" name="email" class="form-control" maxlength="200" placeholder="" value="<?= htmlspecialchars($empresa['email'] ?? '') ?>" required>
        </div>
    </div>

    <?php if (!empty($empresa['id'])): ?>
        <div class="col-md-4 mb-3">
            <label class="form-label font-weight-bold">Estado</label>
            <select name="estado" class="form-control" required>
                <option value="1" <?= (!isset($empresa['estado']) || (int)$empresa['estado'] === 1) ? 'selected' : '' ?>>
                    Activo
                </option>
                <option value="0" <?= (isset($empresa['estado']) && (int)$empresa['estado'] === 0) ? 'selected' : '' ?>>
                    Inactivo
                </option>
            </select>
        </div>
    <?php endif; ?>
</div>