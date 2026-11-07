<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>
<div class="card p-2">
    <h4>Editar Ambiente de Inventario</h4>

    <form action="<?= BASE_URL ?>/inventario/ambientes/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
        <!-- Campo oculto para enviar el ID del ambiente que se está editando -->
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        
        <!-- Carga los campos del formulario (select de ambiente y docente) -->
        <?php include __DIR__ . '/form_fields.php'; ?>
        
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success px-4">Guardar</button>
            <a href="<?= BASE_URL ?>/inventario/ambientes" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>