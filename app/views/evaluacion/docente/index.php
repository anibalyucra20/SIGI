<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-chart-line text-primary mr-2"></i> Mis Resultados de Evaluación Docente</h4>
            <div class="page-title-right">
                <span class="badge badge-soft-info p-2 font-size-13">
                    <i class="fas fa-calendar-alt mr-1"></i> <?= htmlspecialchars($periodoNombre) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (empty($detalleDocente) || empty($detalleDocente['criterios'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 py-5 text-center">
                <i class="fas fa-poll font-size-48 text-muted mb-3"></i>
                <h5 class="text-muted">Aún no se registran evaluaciones completadas para tus unidades didácticas en este periodo.</h5>
                <p class="text-muted small">Los resultados se mostrarán a medida que los estudiantes completen las encuestas.</p>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- Tarjetas Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #007bff !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Promedio Escala (1-5)</h6>
                    <h2 class="mb-0 font-weight-bold text-primary"><?= $detalleDocente['promedio_escala_5'] ?> <small class="text-muted font-size-14">/ 5.00</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #28a745 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Promedio Vigesimal</h6>
                    <h2 class="mb-0 font-weight-bold text-success"><?= $detalleDocente['promedio_vigesimal'] ?> <small class="text-muted font-size-14">/ 20.00</small></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #17a2b8 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Nivel de Desempeño</h6>
                    <span class="badge <?= $detalleDocente['nivel_desempeno']['badge'] ?> px-3 py-2 font-size-14 d-inline-block mt-1 font-weight-bold">
                        <?= $detalleDocente['nivel_desempeno']['etiqueta'] ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Participación Estudiantil</h6>
                    <h2 class="mb-0 font-weight-bold text-purple"><?= $detalleDocente['porcentaje_participacion'] ?>%</h2>
                    <small class="text-muted"><?= $detalleDocente['total_completados'] ?> de <?= $detalleDocente['total_asignados'] ?> alumnos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados por Criterios y por Unidades Didácticas -->
    <div class="row">
        <!-- Criterios / Dimensiones -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-layer-group text-primary mr-2"></i> Calificación por Dimensión / Criterio
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($detalleDocente['criterios'] as $c): ?>
                        <?php 
                        $pct5 = ((float)$c['promedio_criterio'] / 5) * 100;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-weight-semibold text-dark font-size-14"><?= htmlspecialchars($c['criterio_nombre']) ?></span>
                                <span class="badge badge-soft-primary font-weight-bold font-size-13"><?= $c['promedio_criterio'] ?> / 5.00</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct5 ?>%;" aria-valuenow="<?= $pct5 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">Equivalente vigesimal: <strong><?= $c['promedio_vigesimal'] ?></strong> / 20.00</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Unidades Didácticas -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-book-reader text-info mr-2"></i> Rendimiento por Unidad Didáctica
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Unidad Didáctica</th>
                                <th class="text-center">Semestre</th>
                                <th class="text-center">Respuestas</th>
                                <th class="text-right">Promedio (1-5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalleDocente['unidades'] as $u): ?>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold text-dark d-block"><?= htmlspecialchars($u['unidad_nombre']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($u['programa_nombre']) ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border">Sem. <?= htmlspecialchars($u['semestre_romano']) ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-soft-success"><?= $u['completados'] ?> / <?= $u['asignados'] ?></span>
                                    </td>
                                    <td class="text-right align-middle">
                                        <span class="badge badge-primary px-2 py-1 font-size-13 font-weight-bold"><?= $u['promedio_ud'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Comentarios Cualitativos de los Estudiantes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-comments text-success mr-2"></i> Sugerencias y Comentarios de Mejora de Estudiantes
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($detalleDocente['comentarios'])): ?>
                        <p class="text-muted mb-0 font-italic">No hay comentarios cualitativos registrados aún.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($detalleDocente['comentarios'] as $com): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 bg-light rounded border">
                                        <p class="mb-2 font-italic text-dark">"<?= htmlspecialchars($com['comentario_general']) ?>"</p>
                                        <small class="text-muted font-weight-bold">
                                            <i class="fas fa-book mr-1"></i> <?= htmlspecialchars($com['unidad_nombre']) ?> &bull; <?= date('d/m/Y', strtotime($com['fecha_completado'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
