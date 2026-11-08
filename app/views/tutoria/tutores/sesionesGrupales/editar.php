<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Editar Sesión Grupal</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Sesiones Grupales
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body bg-light py-2">
        <strong class="text-primary font-size-15"><i class="fas fa-chalkboard-teacher mr-2"></i><?= htmlspecialchars($sesion['docente_formato']) ?></strong>
        <span class="text-muted ml-2">| Sede: <?= htmlspecialchars($sesion['sede_nombre']) ?> | Periodo: <?= htmlspecialchars($sesion['periodo_nombre']) ?></span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/sesiones/grupalGuardar" method="POST" autocomplete="off">
            <input type="hidden" name="id" value="<?= $sesion['id'] ?>">
            <input type="hidden" name="id_tutoria" value="<?= $sesion['id_tutoria'] ?>">

            <?php include __DIR__ . '/form_fields.php'; ?>

            <div class="mt-4 text-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="btn btn-secondary btn-sm mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-save mr-1"></i> Actualizar Sesión Grupal
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
