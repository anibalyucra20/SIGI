<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0 font-size-18"><i class="fas fa-id-card text-primary mr-2"></i> Ficha Individual de Evaluación Docente</h4>
                <small class="text-muted"><?= htmlspecialchars($detalle['docente']['docente_nombre']) ?> &bull; Periodo: <?= htmlspecialchars($periodoNombre) ?></small>
            </div>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/reportes?id_periodo=<?= $idPeriodo ?>" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Reportes
                </a>
                <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfDocente/<?= $detalle['docente']['id'] ?>?id_periodo=<?= $idPeriodo ?>" 
                   target="_blank" class="btn btn-danger font-weight-bold shadow-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Descargar Ficha en PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas Resumen del Docente -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Promedio Escala (1-5)</h6>
                <h2 class="mb-0 font-weight-bold text-primary"><?= $detalle['promedio_escala_5'] ?> <small class="text-muted font-size-14">/ 5.00</small></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Promedio Vigesimal</h6>
                <h2 class="mb-0 font-weight-bold text-success"><?= $detalle['promedio_vigesimal'] ?> <small class="text-muted font-size-14">/ 20.00</small></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Nivel de Desempeño</h6>
                <span class="badge <?= $detalle['nivel_desempeno']['badge'] ?> px-3 py-2 font-size-13 d-inline-block mt-1 font-weight-bold">
                    <?= $detalle['nivel_desempeno']['etiqueta'] ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #6f42c1 !important;">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Participación Estudiantil</h6>
                <h2 class="mb-0 font-weight-bold text-purple"><?= $detalle['porcentaje_participacion'] ?>%</h2>
                <small class="text-muted"><?= $detalle['total_completados'] ?> de <?= $detalle['total_asignados'] ?> alumnos</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Criterios / Dimensiones -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-layer-group text-primary mr-2"></i> Resultados por Criterio / Dimensión
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($detalle['criterios'] as $c): ?>
                    <?php $pct = ((float)$c['promedio_criterio'] / 5) * 100; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-semibold text-dark"><?= htmlspecialchars($c['criterio_nombre']) ?></span>
                            <span class="badge badge-soft-primary font-weight-bold font-size-13"><?= $c['promedio_criterio'] ?> / 5.00</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">Equivalente vigesimal: <strong><?= $c['promedio_vigesimal'] ?></strong> / 20.00 &bull; Peso: <?= $c['peso'] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Unidades Didácticas del Docente -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-book text-info mr-2"></i> Rendimiento por Asignatura / Unidad Didáctica
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
                        <?php foreach ($detalle['unidades'] as $u): ?>
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

<!-- Detalle por Pregunta / Indicador -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-tasks text-primary mr-2"></i> Detalle por Pregunta e Indicador Evaluado
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="60%">Pregunta / Indicador</th>
                            <th width="25%">Dimensión</th>
                            <th width="15%" class="text-center">Promedio (1-5)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle['preguntas'] as $p): ?>
                            <tr>
                                <td>
                                    <span class="font-weight-semibold text-dark"><?= htmlspecialchars($p['enunciado']) ?></span>
                                    <small class="text-muted d-block"><?= $p['total_respuestas'] ?> valoraciones registradas</small>
                                </td>
                                <td>
                                    <span class="badge badge-light border text-primary"><?= htmlspecialchars($p['criterio_nombre']) ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-primary font-weight-bold font-size-13 px-2 py-1">
                                        <?= $p['promedio_pregunta'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Comentarios Cualitativos -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-comments text-success mr-2"></i> Comentarios y Sugerencias de los Estudiantes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($detalle['comentarios'])): ?>
                    <p class="text-muted font-italic mb-0">No se registraron comentarios cualitativos para este docente.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($detalle['comentarios'] as $com): ?>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded border h-100">
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

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
