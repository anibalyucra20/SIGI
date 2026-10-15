<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-chalkboard text-primary mr-2"></i> Evaluación Docente - Coordinación de Área</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas/crear" class="btn btn-primary font-weight-bold shadow-sm mr-2">
                    <i class="fas fa-plus-circle mr-1"></i> Nueva Encuesta
                </a>
                <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfConsolidado" target="_blank" class="btn btn-danger font-weight-bold shadow-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Reporte PDF de Carrera
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas Resumen Coordinador -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Encuestas de Carrera</h6>
                <h3 class="mb-0 font-weight-bold text-dark"><?= count($encuestas) ?></h3>
                <small class="text-muted"><?= htmlspecialchars($periodoNombre) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Evaluaciones Respondidas</h6>
                <h3 class="mb-0 font-weight-bold text-success"><?= $totalCompletadas ?></h3>
                <small class="text-muted">de <?= $totalAsignadas ?> asignadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Estudiantes Pendientes</h6>
                <h3 class="mb-0 font-weight-bold text-warning"><?= $totalPendientes ?></h3>
                <a href="<?= BASE_URL ?>/evaluacion/seguimiento" class="small text-warning font-weight-bold">Notificar por correo &rarr;</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Participación Carrera</h6>
                <h3 class="mb-0 font-weight-bold text-info"><?= $tasaParticipacion ?>%</h3>
                <small class="text-muted">Avance de encuestas</small>
            </div>
        </div>
    </div>
</div>

<!-- Docentes de la Carrera y sus Evaluaciones -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-user-check text-primary mr-2"></i> Resultados de Docentes de la Carrera
                </h5>
                <a href="<?= BASE_URL ?>/evaluacion/reportes" class="btn btn-sm btn-outline-primary">Ver Reporte Detallado</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($docentes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list font-size-36 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No hay evaluaciones registradas aún para los docentes de su programa.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Docente</th>
                                    <th>DNI</th>
                                    <th class="text-center">Unidades</th>
                                    <th class="text-center">Participación</th>
                                    <th class="text-center">Promedio (1-5)</th>
                                    <th class="text-center">Promedio (0-20)</th>
                                    <th class="text-center">Nivel</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n = 1; foreach ($docentes as $d): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted"><?= $n++ ?></td>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block"><?= htmlspecialchars($d['docente_nombre']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($d['programa_nombre']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($d['docente_dni']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-light border"><?= $d['total_unidades'] ?> UD</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-soft-info px-2 py-1 font-weight-bold">
                                                <?= $d['total_completados'] ?> / <?= $d['total_asignados'] ?> (<?= $d['porcentaje_participacion'] ?>%)
                                            </span>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary font-size-14">
                                            <?= $d['promedio_escala_5'] ?: '0.00' ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-success font-size-14">
                                            <?= $d['promedio_vigesimal'] ?: '0.00' ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $d['nivel_desempeno']['badge'] ?> px-2 py-1 font-size-11">
                                                <?= $d['nivel_desempeno']['etiqueta'] ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?= BASE_URL ?>/evaluacion/reportes/docente/<?= $d['id_docente'] ?>" 
                                               class="btn btn-sm btn-info" title="Ver Ficha">
                                                <i class="fas fa-eye mr-1"></i> Ficha
                                            </a>
                                            <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfDocente/<?= $d['id_docente'] ?>" 
                                               target="_blank" class="btn btn-sm btn-danger" title="Descargar PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
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
