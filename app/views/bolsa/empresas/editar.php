<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="mb-0 text-dark font-weight-bold">Editar Empresa</h3>
            <a href="<?= BASE_URL ?>/bolsa/empresas" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        </div>
    </div>
    
    <div class="card shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/bolsa/empresas/guardar" method="post" autocomplete="off">
                <input type="hidden" name="id" value="<?= htmlspecialchars($empresa['id'] ?? '') ?>">
                <?php include __DIR__ . '/form_fields.php'; ?>
                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Actualizar</button>
                    <a href="<?= BASE_URL ?>/bolsa/empresas" class="btn btn-secondary px-4"><i class="fas fa-times mr-1"></i>Cancelar</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> El módulo de empresas solo está disponible para usuarios con rol de Administrador.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>