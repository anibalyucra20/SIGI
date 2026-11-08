<?php require __DIR__ . '/../layouts/header.php'; ?>

<h3 class="mb-4">SISTEMA DE TUTORÍA</h3>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <?php if (\Core\Auth::tieneRolEnTutoria()): ?>
        <div class="col-md-3 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h5 class="card-title">Periodo actual</h5>
                    <p class="display-5"><?= htmlspecialchars($periodo) ?></p>
                </div>
            </div>
        </div>

        <?php if ($esAdmin): ?>
            <div class="col-md-3 mb-4">
                <a href="<?= BASE_URL ?>/tutoria/programacion" class="text-decoration-none text-dark">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="card-title">Docentes Tutores</h5>
                            <p class="display-5"><?= $totalTutores ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <div class="col-md-3 mb-4">
            <a href="<?= ($esDocente && !$esAdmin && $miProg) ? BASE_URL . '/tutoria/programacion/tutorandos/' . $miProg['id'] : BASE_URL . '/tutoria/tutores/fichas' ?>" class="text-decoration-none text-dark">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= ($esDocente && !$esAdmin) ? 'Mis Tutorandos' : 'Tutorandos' ?></h5>
                        <p class="display-5"><?= ($esDocente && !$esAdmin) ? count($misTutorandos) : $totalTutorandos ?></p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesIndividuales" class="text-decoration-none text-dark">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Sesiones Individuales</h5>
                        <p class="display-5"><?= $totalSesionesInd ?></p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="text-decoration-none text-dark">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Sesiones Grupales</h5>
                        <p class="display-5"><?= $totalSesionesGrup ?></p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="<?= BASE_URL ?>/tutoria/reportes" class="text-decoration-none text-dark">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Reportes</h5>
                        <p class="display-5"><?= htmlspecialchars($periodo) ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
