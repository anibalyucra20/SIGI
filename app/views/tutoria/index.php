<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0 text-dark font-weight-bold">
                <i class="mdi mdi-account-group text-primary mr-2"></i>SISTEMA DE TUTORÍA Y ACOMPAÑAMIENTO INTEGRAL
            </h4>
            <div class="page-title-right">
                <span class="badge badge-soft-primary px-3 py-2 font-size-13">
                    <i class="mdi mdi-calendar-clock mr-1"></i> <?= htmlspecialchars($periodo) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?= $_SESSION['flash_success']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= $_SESSION['flash_error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Tarjetas de Métricas Principales -->
<div class="row">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 font-size-12">Tutores Asignados</h6>
                        <h3 class="mb-0 text-primary font-weight-bold"><?= $totalTutores ?></h3>
                        <small class="text-muted">Docentes con tutoría</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-soft-primary p-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-chalkboard-teacher fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 font-size-12">Total Tutorandos</h6>
                        <h3 class="mb-0 text-success font-weight-bold"><?= $totalTutorandos ?></h3>
                        <small class="text-muted">Estudiantes asignados</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-soft-success p-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-graduate fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 font-size-12">Sesiones Grupales</h6>
                        <h3 class="mb-0 text-info font-weight-bold"><?= $totalSesionesGrup ?></h3>
                        <small class="text-muted">Talleres e inducciones</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-soft-info p-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 font-size-12">Sesiones Individuales</h6>
                        <h3 class="mb-0 text-warning font-weight-bold"><?= $totalSesionesInd ?></h3>
                        <small class="text-muted">Atenciones registradas</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-soft-warning p-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Docente Tutor (Si aplica) -->
<?php if ($esDocente): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-soft-primary d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-primary mb-0">
                        <i class="fas fa-user-check mr-2"></i>Mi Rol como Tutor Asignado
                    </h5>
                    <?php if ($miProg): ?>
                        <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/<?= $miProg['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-tasks mr-1"></i> Gestionar mis Tutorandos
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($miProg): ?>
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <p class="mb-1">
                                    Tienes una programación activa para el periodo lectivo actual en la sede <strong><?= htmlspecialchars($miProg['sede_nombre'] ?? '') ?></strong>.
                                </p>
                                <p class="mb-0 text-muted">
                                    Total de tutorandos a tu cargo: <strong><?= count($misTutorandos) ?> estudiantes</strong>.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalNuevo/<?= $miProg['id'] ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fa fa-plus mr-1"></i> Nueva Sesión Grupal
                                </a>
                                <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/<?= $miProg['id'] ?>" class="btn btn-outline-success btn-sm ml-1">
                                    <i class="fas fa-users mr-1"></i> Ver Mis Alumnos
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-muted py-2">
                            <i class="fas fa-info-circle mr-1"></i> No cuentas con una programación de tutoría asignada para el periodo lectivo y sede activos. Si requieres asignación, coordina con la Unidad de Tutoría del IESP Huanta.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Accesos Rápidos del Módulo -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3 font-weight-bold text-dark"><i class="fas fa-th-large mr-2"></i>Módulos de Tutoría</h5>
    </div>

    <?php if ($esAdmin): ?>
    <div class="col-md-6 col-lg-4 mb-3">
        <a href="<?= BASE_URL ?>/tutoria/programacion" class="text-decoration-none">
            <div class="card shadow-sm h-100 hover-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
                    <h5 class="card-title text-dark">Programación de Tutores</h5>
                    <p class="text-muted font-size-13 mb-0">Designar docentes tutores y asignar tutorandos por sede y periodo.</p>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <div class="col-md-6 col-lg-4 mb-3">
        <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="text-decoration-none">
            <div class="card shadow-sm h-100 hover-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                    <h5 class="card-title text-dark">Sesiones Grupales</h5>
                    <p class="text-muted font-size-13 mb-0">Registro de talleres, charlas, inducciones y asistencia grupal.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesIndividuales" class="text-decoration-none">
            <div class="card shadow-sm h-100 hover-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-user-clock fa-3x text-warning mb-3"></i>
                    <h5 class="card-title text-dark">Sesiones Individuales</h5>
                    <p class="text-muted font-size-13 mb-0">Atención personalizada, compromisos y derivación a especialistas.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <a href="<?= BASE_URL ?>/tutoria/tutores/fichas" class="text-decoration-none">
            <div class="card shadow-sm h-100 hover-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-success mb-3"></i>
                    <h5 class="card-title text-dark">Fichas de Diagnóstico</h5>
                    <p class="text-muted font-size-13 mb-0">Recojo de información inicial socioemocional y académica.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-4 mb-3">
        <a href="<?= BASE_URL ?>/tutoria/reportes" class="text-decoration-none">
            <div class="card shadow-sm h-100 hover-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                    <h5 class="card-title text-dark">Reportes y Derivaciones</h5>
                    <p class="text-muted font-size-13 mb-0">Estadísticas consolidadas, casos derivados y balance final.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>