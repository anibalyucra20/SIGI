<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-poll-h text-primary mr-2"></i> Módulo de Evaluación del Desempeño Docente</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas/crear" class="btn btn-primary font-weight-bold shadow-sm mr-2">
                    <i class="fas fa-plus-circle mr-1"></i> Nueva Encuesta
                </a>
                <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfConsolidado?id_periodo=<?= $filtroPeriodo ?>&id_programa=<?= $filtroPrograma ?>" 
                   target="_blank" class="btn btn-danger font-weight-bold shadow-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Consolidado PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Contexto -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 mb-2 border-bottom">
                    <div>
                        <span class="badge badge-primary px-3 py-2 font-size-13">
                            <i class="fas fa-calendar-alt mr-1"></i> Periodo Académico (Contexto Activo): <strong><?= htmlspecialchars($periodoNombre) ?></strong>
                        </span>
                        <span class="badge badge-success px-2 py-2 font-size-12 ml-2">
                            <i class="fas fa-university mr-1"></i> Panel de Dirección / JUA / Administración
                        </span>
                    </div>
                </div>

                <form method="GET" action="<?= BASE_URL ?>/evaluacion" class="form-row align-items-center">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-0">Programa de Estudios:</label>
                        <select name="id_programa" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Todos los Programas de Estudios --</option>
                            <?php foreach ($programas as $prg): ?>
                                <option value="<?= $prg['id'] ?>" <?= $prg['id'] == $filtroPrograma ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prg['codigo'] . ' - ' . $prg['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 text-md-right mt-3 mt-md-0">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-sm btn-info px-3">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/evaluacion" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-undo mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas Resumen Métricas -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #007bff !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Encuestas</h6>
                        <h3 class="mb-0 font-weight-bold text-dark"><?= count($encuestas) ?></h3>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-clipboard-list font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Completadas</h6>
                        <h3 class="mb-0 font-weight-bold text-success"><?= $totalCompletadas ?></h3>
                        <small class="text-muted font-size-11">de <?= $totalAsignadas ?> asignadas</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-double font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Pendientes</h6>
                        <h3 class="mb-0 font-weight-bold text-warning"><?= $totalPendientes ?></h3>
                        <a href="<?= BASE_URL ?>/evaluacion/seguimiento<?= $filtroPrograma ? '?id_programa=' . $filtroPrograma : '' ?>" class="small text-warning font-weight-bold">Ver pendientes &rarr;</a>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-user-clock font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-3" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Participación</h6>
                        <h3 class="mb-0 font-weight-bold text-info"><?= $tasaParticipacion ?>%</h3>
                        <small class="text-muted font-size-11">Tasa institucional</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-light text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-percentage font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Encuestas Activas y Recientes -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-clipboard-check text-primary mr-2"></i> Encuestas del Periodo
                </h5>
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($encuestas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard font-size-36 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No hay encuestas configuradas para este periodo.</p>
                        <a href="<?= BASE_URL ?>/evaluacion/encuestas/crear" class="btn btn-sm btn-primary mt-2">Crear Encuesta</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th class="text-center">Respuestas</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($encuestas as $enc): ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block"><?= htmlspecialchars($enc['titulo']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($enc['programa_nombre'] ?? 'Todos los Programas') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($enc['estado'] === 'ACTIVA'): ?>
                                                <span class="badge badge-success px-2 py-1">ACTIVA</span>
                                            <?php elseif ($enc['estado'] === 'BORRADOR'): ?>
                                                <span class="badge badge-warning px-2 py-1 text-dark">BORRADOR</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">CERRADA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="font-weight-bold text-dark"><?= $enc['total_completadas'] ?></span>
                                            <small class="text-muted">/ <?= $enc['total_asignadas'] ?></small>
                                        </td>
                                        <td class="text-right align-middle">
                                            <a href="<?= BASE_URL ?>/evaluacion/encuestas/editar/<?= $enc['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-cog"></i>
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

    <!-- Ranking / Desempeño de Docentes -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-trophy text-warning mr-2"></i> Desempeño Docente Destacado
                </h5>
                <a href="<?= BASE_URL ?>/evaluacion/reportes" class="btn btn-sm btn-outline-primary">Ver Reporte Completo</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($docentes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-tie font-size-36 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No se registran evaluaciones aún en este periodo.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Docente</th>
                                    <th>Programa</th>
                                    <th class="text-center">Prom. (1-5)</th>
                                    <th class="text-center">Nivel</th>
                                    <th class="text-right">Ficha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($docentes, 0, 6) as $d): ?>
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block"><?= htmlspecialchars($d['docente_nombre']) ?></span>
                                            <small class="text-muted">DNI: <?= htmlspecialchars($d['docente_dni']) ?></small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border"><?= htmlspecialchars($d['programa_codigo']) ?></span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-primary font-size-13 px-2 py-1 font-weight-bold">
                                                <?= $d['promedio_escala_5'] ?: '0.00' ?>
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge <?= $d['nivel_desempeno']['badge'] ?> px-2 py-1 font-size-11">
                                                <?= $d['nivel_desempeno']['etiqueta'] ?>
                                            </span>
                                        </td>
                                        <td class="text-right align-middle">
                                            <a href="<?= BASE_URL ?>/evaluacion/reportes/docente/<?= $d['id_docente'] ?>?id_periodo=<?= $filtroPeriodo ?>" 
                                               class="btn btn-sm btn-info" title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
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
