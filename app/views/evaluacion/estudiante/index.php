<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-clipboard-check text-primary mr-2"></i> Evaluación del Desempeño Docente</h4>
            <div class="page-title-right">
                <span class="badge badge-soft-info p-2 font-size-13">
                    <i class="fas fa-calendar-alt mr-1"></i> <?= htmlspecialchars($periodoNombre) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Progreso y Métricas del Estudiante -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Total Asignadas</h6>
                        <h3 class="mb-0 font-weight-bold text-dark"><?= $totalAsignadas ?></h3>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-tasks font-size-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Completadas</h6>
                        <h3 class="mb-0 font-weight-bold text-success"><?= $totalCompletadas ?></h3>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-circle font-size-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Pendientes</h6>
                        <h3 class="mb-0 font-weight-bold text-warning"><?= $totalPendientes ?></h3>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-clock font-size-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progreso General -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-muted small">Tu Avance en la Evaluación:</span>
                    <span class="font-weight-bold text-primary"><?= $porcentajeAvance ?>%</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar <?= $porcentajeAvance == 100 ? 'bg-success' : 'bg-primary' ?> progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: <?= $porcentajeAvance ?>%;" aria-valuenow="<?= $porcentajeAvance ?>" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Unidades Didácticas y Docentes a Evaluar -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list-ul mr-2 text-primary"></i> Unidades Didácticas Matriculadas
                </h5>
                <span class="badge badge-light border">Periodo: <?= htmlspecialchars($periodoNombre) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($evaluaciones)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No tienes evaluaciones docentes asignadas para este periodo.</h5>
                        <p class="text-muted small">Si estás matriculado, la coordinación académica activará las encuestas oportunamente.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Unidad Didáctica</th>
                                    <th>Docente</th>
                                    <th>Semestre / Sección</th>
                                    <th>Fecha Límite</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n = 1; foreach ($evaluaciones as $ev): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted"><?= $n++ ?></td>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block font-size-14">
                                                <?= htmlspecialchars($ev['unidad_didactica_nombre']) ?>
                                            </span>
                                            <small class="text-muted"><?= htmlspecialchars($ev['programa_nombre']) ?></small>
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-primary">
                                                <i class="fas fa-chalkboard-teacher mr-1"></i> <?= htmlspecialchars($ev['docente_nombre']) ?>
                                            </span>
                                            <small class="text-muted d-block">DNI: <?= htmlspecialchars($ev['docente_dni']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">Sem. <?= htmlspecialchars($ev['semestre_romano']) ?></span>
                                            <span class="badge badge-light border">Sec. <?= htmlspecialchars($ev['seccion']) ?></span>
                                            <span class="badge badge-light border"><?= htmlspecialchars($ev['turno']) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">
                                                <i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($ev['encuesta_fecha_fin'])) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ev['estado'] === 'COMPLETADO'): ?>
                                                <span class="badge badge-success px-3 py-2 font-size-12">
                                                    <i class="fas fa-check-circle mr-1"></i> Completado
                                                </span>
                                                <small class="text-muted d-block mt-1 font-size-11">
                                                    <?= date('d/m/Y H:i', strtotime($ev['fecha_completado'])) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="badge badge-warning px-3 py-2 font-size-12 text-dark font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if ($ev['estado'] === 'PENDIENTE'): ?>
                                                <?php if ($ev['encuesta_estado'] === 'ACTIVA'): ?>
                                                    <a href="<?= BASE_URL ?>/evaluacion/realizar/evaluar/<?= $ev['id'] ?>" 
                                                       class="btn btn-sm btn-primary shadow-sm font-weight-bold px-3">
                                                        <i class="fas fa-pencil-alt mr-1"></i> Evaluar Docente
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Encuesta Cerrada</button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>/evaluacion/realizar/ver/<?= $ev['id'] ?>" 
                                                   class="btn btn-sm btn-outline-info font-weight-semibold">
                                                    <i class="fas fa-eye mr-1"></i> Ver Constancia
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
