<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Editar Sesión Individual N° <?= htmlspecialchars($sesion['nro']) ?></h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $sesion['id_tutoria_estudiante'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Sesiones
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body bg-light py-2">
        <strong class="text-primary font-size-15"><i class="fas fa-user-graduate mr-2"></i><?= htmlspecialchars($sesion['estudiante_formato']) ?></strong>
        <span class="text-muted ml-2">| DNI: <?= htmlspecialchars($sesion['estudiante_dni']) ?> | <?= htmlspecialchars($sesion['programa_nombre'] ?: 'General') ?></span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/sesiones/individualGuardar" method="POST" autocomplete="off">
            <input type="hidden" name="id" value="<?= $sesion['id'] ?>">
            <input type="hidden" name="id_tutoria_estudiante" value="<?= $sesion['id_tutoria_estudiante'] ?>">

            <?php include __DIR__ . '/form_fields.php'; ?>

            <div class="mt-4 text-right">
                <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $sesion['id_tutoria_estudiante'] ?>" class="btn btn-secondary btn-sm mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-save mr-1"></i> Actualizar Sesión
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
