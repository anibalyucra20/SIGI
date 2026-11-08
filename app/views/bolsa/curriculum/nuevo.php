<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark font-weight-bold">Registrar Nuevo Currículum</h4>
        </div>
        <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Currículums
        </a>
    </div>
</div>

<form action="<?= BASE_URL ?>/bolsa/curriculum/guardar" method="post" autocomplete="off">
    <?php include __DIR__ . '/form_fields.php'; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 text-right">
            <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-secondary px-4 mr-2">
                Cancelar
            </a>
            <button type="submit" class="btn btn-success px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Guardar Currículum
            </button>
        </div>
    </div>
</form>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
