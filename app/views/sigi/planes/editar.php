<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h4>Editar Plan de Estudio</h4>
        <form action="<?= BASE_URL ?>/sigi/planes/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
            <input type="hidden" name="id" value="<?= htmlspecialchars($plan['id'] ?? '') ?>">
            <?php include __DIR__ . '/form_fields.php'; ?>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                <a href="<?= BASE_URL ?>/sigi/planes" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>