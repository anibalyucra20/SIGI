<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-chart-line text-primary mr-2"></i>Reportes y Estadísticas de Tutoría
            </h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Inicio
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TARJETAS DE EXPORTACIÓN EN FORMATO EXCEL (.XLSX) -->
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-lg-4 mb-2 mb-lg-0">
                <h5 class="text-dark font-weight-bold mb-1">
                    <i class="fas fa-file-excel text-success mr-2"></i>Generación de Reportes en Excel
                </h5>
                <p class="text-muted small mb-0">Descarga los datos oficiales y actualizados en formato compatible con Microsoft Excel (.xlsx).</p>
            </div>
            
            <div class="col-lg-8 text-lg-right">
                <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                    <?php if ($esAdmin): ?>
                        <div class="mr-2 mb-2">
                            <select id="filtro_tutor_reporte" class="form-control form-control-sm" style="min-width: 220px;">
                                <option value="">-- Todos los Tutores --</option>
                                <?php foreach ($tutores as $t): ?>
                                    <option value="<?= $t['id_docente'] ?>">
                                        <?= htmlspecialchars($t['docente_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="button" class="btn btn-danger btn-sm font-weight-bold mb-2 mr-2" id="btn_export_pdf">
                        <i class="fas fa-file-pdf mr-1"></i> Reporte Oficial PDF
                    </button>

                    <button type="button" class="btn btn-success btn-sm font-weight-bold mb-2 mr-2" id="btn_export_consolidado">
                        <i class="fas fa-file-excel mr-1"></i> Informe Consolidado (.xlsx)
                    </button>

                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mb-2 mr-2" id="btn_export_individuales">
                        <i class="fas fa-user-clock mr-1"></i> Tutorías Individuales (.xlsx)
                    </button>

                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold mb-2" id="btn_export_grupales">
                        <i class="fas fa-users mr-1"></i> Tutorías Grupales (.xlsx)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen Estadístico -->
<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm border text-center p-3 h-100">
            <h6 class="text-muted font-size-12 text-uppercase mb-1">Docentes Tutores</h6>
            <h3 class="text-primary font-weight-bold mb-0"><?= $totalTutores ?></h3>
            <small class="text-muted">Docentes asignados</small>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm border text-center p-3 h-100">
            <h6 class="text-muted font-size-12 text-uppercase mb-1">Total Tutorandos</h6>
            <h3 class="text-success font-weight-bold mb-0"><?= $totalTutorandos ?></h3>
            <small class="text-muted">Estudiantes atendidos</small>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm border text-center p-3 h-100">
            <h6 class="text-muted font-size-12 text-uppercase mb-1">Sesiones Grupales</h6>
            <h3 class="text-info font-weight-bold mb-0"><?= $totalSesionesGrup ?></h3>
            <small class="text-muted">Talleres e inducciones</small>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm border text-center p-3 h-100">
            <h6 class="text-muted font-size-12 text-uppercase mb-1">Sesiones Individuales</h6>
            <h3 class="text-warning font-weight-bold mb-0"><?= $totalSesionesInd ?></h3>
            <small class="text-muted">Atenciones registradas</small>
        </div>
    </div>
</div>

<!-- Tabla 1: Consolidado por Tutor -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-chalkboard-teacher text-primary mr-2"></i>1. Balance de Cumplimiento por Docente Tutor
        </h5>
        <span class="badge badge-light border text-muted">Periodo Lectivo Activo</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Docente Tutor</th>
                        <th width="120" class="text-center">DNI</th>
                        <th width="140" class="text-center">Tutorandos</th>
                        <th width="150" class="text-center">Sesiones Grupales</th>
                        <th>Informe / Conclusiones del Semestre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tutores)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No hay tutores registrados en este periodo.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tutores as $i => $t): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($t['docente_nombre']) ?></strong></td>
                                <td class="text-center"><?= htmlspecialchars($t['docente_dni']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-primary px-2 py-1"><?= $t['total_estudiantes'] ?> alumnos</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info px-2 py-1"><?= $t['total_sesiones_grupales'] ?> sesiones</span>
                                </td>
                                <td>
                                    <?php if (!empty($t['conclusiones'])): ?>
                                        <small class="text-dark"><?= htmlspecialchars($t['conclusiones']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted font-italic">Pendiente de registro de informe final</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabla 2: Registro de Derivaciones Especializadas -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-share-square text-warning mr-2"></i>2. Relación de Estudiantes Derivados a Servicios Institucionales (<?= count($derivaciones) ?>)
        </h5>
        <span class="badge badge-warning p-1">Atención Multidisciplinaria</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th width="110" class="text-center">Fecha</th>
                        <th>Estudiante Derivado</th>
                        <th>Programa de Estudios</th>
                        <th>Docente Tutor</th>
                        <th>Motivo de Atención</th>
                        <th width="200">Servicio de Destino</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($derivaciones)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="fas fa-check-circle text-success mr-1"></i> No se han registrado derivaciones a servicios externos en este periodo (todos los casos fueron atendidos en tutoría regular).
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($derivaciones as $idx => $d): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $idx + 1 ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($d['fecha_hora'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($d['estudiante_formato']) ?></strong><br>
                                    <small class="text-muted">DNI: <?= htmlspecialchars($d['estudiante_dni']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($d['programa_nombre'] ?: 'General') ?></td>
                                <td><?= htmlspecialchars($d['docente_formato']) ?></td>
                                <td><small><?= htmlspecialchars($d['motivo']) ?></small></td>
                                <td>
                                    <span class="badge badge-warning p-1">
                                        <i class="fas fa-hospital-user mr-1"></i><?= htmlspecialchars($d['servicio_derivacion'] ?: 'Derivado') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function getDocenteParam() {
        const sel = document.getElementById('filtro_tutor_reporte');
        return sel && sel.value ? '?id_docente=' + sel.value : '';
    }

    document.getElementById('btn_export_pdf').addEventListener('click', function() {
        window.open('<?= BASE_URL ?>/tutoria/reportes/exportarReportePdf' + getDocenteParam(), '_blank');
    });

    document.getElementById('btn_export_consolidado').addEventListener('click', function() {
        window.location.href = '<?= BASE_URL ?>/tutoria/reportes/exportarConsolidadoExcel' + getDocenteParam();
    });

    document.getElementById('btn_export_individuales').addEventListener('click', function() {
        window.location.href = '<?= BASE_URL ?>/tutoria/reportes/exportarIndividualesExcel' + getDocenteParam();
    });

    document.getElementById('btn_export_grupales').addEventListener('click', function() {
        window.location.href = '<?= BASE_URL ?>/tutoria/reportes/exportarGrupalesExcel' + getDocenteParam();
    });
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
