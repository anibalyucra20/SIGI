<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark font-weight-bold">
                <i class="fas fa-edit text-warning mr-2"></i>Editar Currículum
            </h4>
            <p class="text-muted small mb-0">Actualice el nombre de su currículum o sus secciones académicas y laborales.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $detalle['cv']['id'] ?>" target="_blank" class="btn btn-outline-danger btn-sm mr-1">
                <i class="fas fa-file-pdf mr-1"></i> Ver PDF
            </a>
            <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Currículums
            </a>
        </div>
    </div>
</div>

<form action="<?= BASE_URL ?>/bolsa/curriculum/guardar" method="post" autocomplete="off">
    <input type="hidden" name="id" value="<?= htmlspecialchars($detalle['cv']['id']) ?>">

    <?php include __DIR__ . '/form_fields.php'; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 text-right">
            <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-secondary px-4 mr-2">
                Cancelar
            </a>
            <button type="submit" class="btn btn-success px-4 font-weight-bold">
                <i class="fas fa-save mr-1"></i> Actualizar Currículum
            </button>
        </div>
    </div>
</form>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
