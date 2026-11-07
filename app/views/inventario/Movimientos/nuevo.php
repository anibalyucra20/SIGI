<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<div class="container mt-4">
    <h2>Registrar Nuevo Movimiento</h2>
    <div class="card p-3 shadow-sm">
        <form action="<?= BASE_URL ?>/inventario/movimientos/guardar" method="post">
            <?php require __DIR__ . '/form_fields.php'; ?>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="<?= BASE_URL ?>/inventario/movimientos" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
<br><br>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>