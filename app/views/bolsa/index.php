<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminBolsa() || \Core\Auth::esEstudianteBolsa()): ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h3 class="mb-1 text-dark font-weight-bold">
                Bolsa de Trabajo - Panel Principal
            </h3>
        </div>

        <div class="mt-2 mt-md-0">
            <span class="badge badge-light border text-dark px-3 py-2 font-weight-bold">
                <i class="fas fa-calendar-alt text-primary mr-1"></i>
                Periodo: <?= htmlspecialchars($periodo ?? 'Actual') ?>
            </span>
        </div>
    </div>
</div>

<div class="row">

    <!-- Card 1: Convocatorias vigentes -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?= BASE_URL ?>/bolsa/ofertas" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                 style="border-left: 4px solid #4e73df !important; transition: transform 0.2s;">

                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Convocatorias Vigentes
                            </div>

                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= (int)($totalOfertas ?? 0) ?>
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="far fa-address-book fa-2x text-primary"
                               style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 2: Exclusiva para el administrador -->
    <?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?= BASE_URL ?>/bolsa/empresas" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                 style="border-left: 4px solid #1cc88a !important; transition: transform 0.2s;">

                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Empresas Aliadas
                            </div>

                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                <?= (int)($totalEmpresas ?? 0) ?>
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-success"
                               style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php endif; ?>

    <!-- Card 2 alternativa: Exclusiva para el estudiante -->
    <?php if (\Core\Auth::esEstudianteBolsa()): ?>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?= BASE_URL ?>/bolsa/curriculum" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                 style="border-left: 4px solid #1cc88a !important; transition: transform 0.2s;">

                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Mis Currículums (CV)
                            </div>

                            <div class="h6 mb-0 font-weight-bold text-muted">
                                Gestionar mis perfiles
                            </div>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-success"
                               style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php endif; ?>

    <!-- Card 3: Postulaciones -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                 style="border-left: 4px solid #36b9cc !important; transition: transform 0.2s;">

                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= \Core\Auth::esAdminBolsa()
                                    ? 'Total Postulaciones'
                                    : 'Mis Postulaciones' ?>
                            </div>

                            <?php if (\Core\Auth::esAdminBolsa()): ?>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    <?= (int)($totalPostulaciones ?? 0) ?>
                                </div>
                            <?php else: ?>
                                <div class="h6 mb-0 font-weight-bold text-muted">
                                    Ver seguimiento
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-info"
                               style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4: Reportes para Administrador / Explorar cartelera para Estudiante -->
    <div class="col-xl-3 col-md-6 mb-4">
        <?php if (\Core\Auth::esAdminBolsa()): ?>
            <a href="<?= BASE_URL ?>/bolsa/reportes" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                     style="border-left: 4px solid #e74a3b !important; transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Reportes Oficiales
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-dark">
                                    Emitir Reportes PDF
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-pdf fa-2x text-danger" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/bolsa/ofertas" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 py-2 rounded-3"
                     style="border-left: 4px solid #f6c23e !important; transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Explorar Cartelera
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-muted">
                                    Buscar Convocatorias
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-search fa-2x text-warning" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div>

</div>

<?php else: ?>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    No tienes permisos para acceder al panel de la Bolsa de Trabajo.
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>