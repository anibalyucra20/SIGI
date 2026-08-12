<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminTutoria()): ?>
<div class="card p-2">
    <h4>Editar Programación de Tutoría</h4>

    <form action="<?= BASE_URL ?>/tutoria/programacion/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <?php include __DIR__ . '/form_fields.php'; ?>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success px-4">Guardar</button>
            <a href="<?= BASE_URL ?>/tutoria/programacion" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>