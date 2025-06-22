<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
<div class="card p-2">
    <h4>Programas de Estudio de la Sede: <span class="text-primary"><?= htmlspecialchars($sede['nombre']) ?></span></h4>
    <form action="<?= BASE_URL ?>/sigi/sedes/programasGuardar/<?= $sede['id'] ?>" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Seleccione los programas disponibles para esta sede:</label>
            <?php foreach ($programas as $prog): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="programas[]" value="<?= $prog['id'] ?>"
                        id="prog<?= $prog['id'] ?>"
                        <?= in_array($prog['id'], $programas_sede) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prog<?= $prog['id'] ?>">
                        <?= htmlspecialchars($prog['nombre']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
            <a href="<?= BASE_URL ?>/sigi/sedes" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php else: ?>
  <!-- Para director o coordinador en SIGI -->
  <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
