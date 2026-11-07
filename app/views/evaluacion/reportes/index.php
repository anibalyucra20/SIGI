<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-chart-bar text-primary mr-2"></i> Reportes y Resultados de Evaluación Docente</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfConsolidado?id_periodo=<?= $filtroPeriodo ?>&id_programa=<?= $filtroPrograma ?>" 
                   target="_blank" class="btn btn-danger font-weight-bold shadow-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Descargar Consolidado Oficial (PDF)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Contexto y Jerarquía Académica -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 mb-2 border-bottom">
                    <div>
                        <span class="badge badge-primary px-3 py-2 font-size-13">
                            <i class="fas fa-calendar-alt mr-1"></i> Periodo Académico (Contexto Activo): <strong><?= htmlspecialchars($periodoActualNombre) ?></strong>
                        </span>
                        <?php if (!$puedeVerTodos): ?>
                            <span class="badge badge-info px-2 py-2 font-size-12 ml-2">
                                <i class="fas fa-user-shield mr-1"></i> Vista de Coordinador: Carreras asignadas
                            </span>
                        <?php else: ?>
                            <span class="badge badge-success px-2 py-2 font-size-12 ml-2">
                                <i class="fas fa-globe mr-1"></i> Vista Institucional: Todos los programas
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="GET" action="<?= BASE_URL ?>/evaluacion/reportes" id="formFiltrosReportes" class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Programa de Estudios:</label>
                        <select name="id_programa" id="select_programa" class="form-control form-control-sm" onchange="cargarPlanes(this.value)">
                            <option value="">-- Todos los Programas --</option>
                            <?php foreach ($programas as $prg): ?>
                                <option value="<?= $prg['id'] ?>" <?= $prg['id'] == $filtroPrograma ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prg['codigo'] . ' - ' . $prg['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Plan de Estudios:</label>
                        <select name="id_plan" id="select_plan" class="form-control form-control-sm" onchange="cargarModulos(this.value)">
                            <option value="">-- Todos los Planes --</option>
                            <?php foreach ($planes as $pl): ?>
                                <option value="<?= $pl['id'] ?>" <?= $pl['id'] == $filtroPlan ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pl['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Módulo Formativo:</label>
                        <select name="id_modulo" id="select_modulo" class="form-control form-control-sm" onchange="cargarSemestres(this.value)">
                            <option value="">-- Todos los Módulos --</option>
                            <?php foreach ($modulos as $mod): ?>
                                <option value="<?= $mod['id'] ?>" <?= $mod['id'] == $filtroModulo ? 'selected' : '' ?>>
                                    Módulo <?= $mod['nro_modulo'] ?>: <?= htmlspecialchars($mod['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Semestre:</label>
                        <select name="id_semestre" id="select_semestre" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            <?php foreach ($semestres as $sem): ?>
                                <option value="<?= $sem['id'] ?>" <?= $sem['id'] == $filtroSemestre ? 'selected' : '' ?>>
                                    Semestre <?= htmlspecialchars($sem['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1 mb-2 text-right">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-sm btn-primary" title="Filtrar">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= BASE_URL ?>/evaluacion/reportes" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas de Reporte: Por Docente y Por Unidad Didáctica -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 px-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold py-3" data-toggle="tab" href="#tabDocentes" role="tab">
                            <i class="fas fa-user-tie mr-1 text-primary"></i> Reporte por Docente (<?= count($docentes) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold py-3" data-toggle="tab" href="#tabUnidades" role="tab">
                            <i class="fas fa-book mr-1 text-info"></i> Reporte por Unidad Didáctica (<?= count($unidades) ?>)
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB 1: POR DOCENTE -->
                    <div class="tab-pane active" id="tabDocentes" role="tabpanel">
                        <?php if (empty($docentes)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-times font-size-48 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay docentes evaluados para los filtros seleccionados.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle" id="tablaReporteDocentes">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Docente</th>
                                            <th>DNI</th>
                                            <th>Programa</th>
                                            <th class="text-center">Asignaturas</th>
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
                                                    <span class="font-weight-bold text-dark d-block">
                                                        <?= htmlspecialchars($d['docente_nombre']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($d['docente_dni']) ?></td>
                                                <td>
                                                    <span class="badge badge-light border">
                                                        <?= htmlspecialchars($d['programa_codigo']) ?>
                                                    </span>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($d['programa_nombre']) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary"><?= $d['total_unidades'] ?> UD</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-soft-info px-2 py-1 font-weight-bold">
                                                        <?= $d['total_completados'] ?> / <?= $d['total_asignados'] ?> (<?= $d['porcentaje_participacion'] ?>%)
                                                    </span>
                                                </td>
                                                <td class="text-center font-weight-bold text-primary font-size-15">
                                                    <?= $d['promedio_escala_5'] ?: '0.00' ?>
                                                </td>
                                                <td class="text-center font-weight-bold text-success font-size-15">
                                                    <?= $d['promedio_vigesimal'] ?: '0.00' ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= $d['nivel_desempeno']['badge'] ?> px-2 py-1 font-size-12">
                                                        <?= $d['nivel_desempeno']['etiqueta'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="<?= BASE_URL ?>/evaluacion/reportes/docente/<?= $d['id_docente'] ?>?id_periodo=<?= $filtroPeriodo ?>" 
                                                       class="btn btn-sm btn-info mr-1" title="Ver Detalle Completo">
                                                        <i class="fas fa-eye mr-1"></i> Ficha
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/evaluacion/reportes/pdfDocente/<?= $d['id_docente'] ?>?id_periodo=<?= $filtroPeriodo ?>" 
                                                       target="_blank" class="btn btn-sm btn-danger" title="Descargar Ficha PDF">
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

                    <!-- TAB 2: POR UNIDAD DIDÁCTICA -->
                    <div class="tab-pane" id="tabUnidades" role="tabpanel">
                        <?php if (empty($unidades)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book-open font-size-48 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay unidades didácticas evaluadas en este periodo.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle" id="tablaReporteUnidades">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Unidad Didáctica</th>
                                            <th>Docente a Cargo</th>
                                            <th>Programa</th>
                                            <th class="text-center">Sem / Sec</th>
                                            <th class="text-center">Participación</th>
                                            <th class="text-center">Prom. (1-5)</th>
                                            <th class="text-center">Prom. (0-20)</th>
                                            <th class="text-center">Nivel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $m = 1; foreach ($unidades as $u): ?>
                                            <tr>
                                                <td class="font-weight-bold text-muted"><?= $m++ ?></td>
                                                <td>
                                                    <span class="font-weight-bold text-dark d-block">
                                                        <?= htmlspecialchars($u['unidad_nombre']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-primary font-weight-semibold">
                                                        <i class="fas fa-chalkboard-teacher mr-1"></i> <?= htmlspecialchars($u['docente_nombre']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border"><?= htmlspecialchars($u['programa_nombre']) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light border">Sem. <?= htmlspecialchars($u['semestre_romano']) ?> - Sec. <?= htmlspecialchars($u['seccion']) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-soft-info px-2 py-1">
                                                        <?= $u['total_completados'] ?> / <?= $u['total_asignados'] ?> (<?= $u['porcentaje_participacion'] ?>%)
                                                    </span>
                                                </td>
                                                <td class="text-center font-weight-bold text-primary font-size-14">
                                                    <?= $u['promedio_escala_5'] ?: '0.00' ?>
                                                </td>
                                                <td class="text-center font-weight-bold text-success font-size-14">
                                                    <?= $u['promedio_vigesimal'] ?: '0.00' ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= $u['nivel_desempeno']['badge'] ?> px-2 py-1 font-size-11">
                                                        <?= $u['nivel_desempeno']['etiqueta'] ?>
                                                    </span>
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
    </div>
</div>

<script>
function cargarPlanes(idPrograma) {
    const selPlan = document.getElementById('select_plan');
    const selMod = document.getElementById('select_modulo');
    const selSem = document.getElementById('select_semestre');

    selPlan.innerHTML = '<option value="">-- Todos los Planes --</option>';
    selMod.innerHTML = '<option value="">-- Todos los Módulos --</option>';
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idPrograma) return;

    fetch('<?= BASE_URL ?>/evaluacion/reportes/ajaxPlanes/' + idPrograma)
        .then(res => res.json())
        .then(data => {
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nombre;
                selPlan.appendChild(opt);
            });
        })
        .catch(err => console.error('Error al cargar planes:', err));
}

function cargarModulos(idPlan) {
    const selMod = document.getElementById('select_modulo');
    const selSem = document.getElementById('select_semestre');

    selMod.innerHTML = '<option value="">-- Todos los Módulos --</option>';
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idPlan) return;

    fetch('<?= BASE_URL ?>/evaluacion/reportes/ajaxModulos/' + idPlan)
        .then(res => res.json())
        .then(data => {
            data.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = 'Módulo ' + m.nro_modulo + ': ' + m.descripcion;
                selMod.appendChild(opt);
            });
        })
        .catch(err => console.error('Error al cargar módulos:', err));
}

function cargarSemestres(idModulo) {
    const selSem = document.getElementById('select_semestre');
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idModulo) return;

    fetch('<?= BASE_URL ?>/evaluacion/reportes/ajaxSemestres/' + idModulo)
        .then(res => res.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = 'Semestre ' + s.descripcion;
                selSem.appendChild(opt);
            });
        })
        .catch(err => console.error('Error al cargar semestres:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        if ($('#tablaReporteDocentes').length) {
            $('#tablaReporteDocentes').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
                pageLength: 15,
                order: [[6, 'desc']] // Ordenar por promedio más alto primero
            });
        }
        if ($('#tablaReporteUnidades').length) {
            $('#tablaReporteUnidades').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
                pageLength: 15,
                order: [[6, 'desc']]
            });
        }
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
