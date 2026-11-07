<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap">
            <h4 class="mb-0 font-size-18"><i class="fas fa-user-clock text-warning mr-2"></i> Seguimiento de Estudiantes Pendientes</h4>
            <div class="page-title-right mt-2 mt-md-0">
                <form action="<?= BASE_URL ?>/evaluacion/seguimiento/notificarMasivo" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Deseas enviar recordatorios por correo a todos los estudiantes pendientes con los filtros actuales?');">
                    <input type="hidden" name="id_periodo" value="<?= $filtroPeriodo ?>">
                    <input type="hidden" name="id_programa" value="<?= $filtroPrograma ?>">
                    <input type="hidden" name="id_plan" value="<?= $filtroPlan ?>">
                    <input type="hidden" name="id_modulo" value="<?= $filtroModulo ?>">
                    <input type="hidden" name="id_semestre" value="<?= $filtroSemestre ?>">
                    <input type="hidden" name="id_encuesta" value="<?= $filtroEncuesta ?>">
                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Notificar a Todos los Pendientes (Correo Personal)
                    </button>
                </form>
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

                <form method="GET" action="<?= BASE_URL ?>/evaluacion/seguimiento" id="formFiltrosSeguimiento" class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Programa de Estudios:</label>
                        <select name="id_programa" id="select_programa_seg" class="form-control form-control-sm" onchange="cargarPlanesSeg(this.value)">
                            <option value="">-- Todos los Programas --</option>
                            <?php foreach ($programas as $prg): ?>
                                <option value="<?= $prg['id'] ?>" <?= $prg['id'] == $filtroPrograma ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prg['codigo'] . ' - ' . $prg['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Plan de Estudios:</label>
                        <select name="id_plan" id="select_plan_seg" class="form-control form-control-sm" onchange="cargarModulosSeg(this.value)">
                            <option value="">-- Todos --</option>
                            <?php foreach ($planes as $pl): ?>
                                <option value="<?= $pl['id'] ?>" <?= $pl['id'] == $filtroPlan ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pl['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Módulo Formativo:</label>
                        <select name="id_modulo" id="select_modulo_seg" class="form-control form-control-sm" onchange="cargarSemestresSeg(this.value)">
                            <option value="">-- Todos --</option>
                            <?php foreach ($modulos as $mod): ?>
                                <option value="<?= $mod['id'] ?>" <?= $mod['id'] == $filtroModulo ? 'selected' : '' ?>>
                                    Módulo <?= $mod['nro_modulo'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Semestre:</label>
                        <select name="id_semestre" id="select_semestre_seg" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            <?php foreach ($semestres as $sem): ?>
                                <option value="<?= $sem['id'] ?>" <?= $sem['id'] == $filtroSemestre ? 'selected' : '' ?>>
                                    Semestre <?= htmlspecialchars($sem['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Encuesta:</label>
                        <select name="id_encuesta" class="form-control form-control-sm">
                            <option value="">-- Todas las Encuestas --</option>
                            <?php foreach ($encuestas as $e): ?>
                                <option value="<?= $e['id'] ?>" <?= $e['id'] == $filtroEncuesta ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['titulo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1 mb-2 text-right">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-sm btn-primary" title="Filtrar">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= BASE_URL ?>/evaluacion/seguimiento" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Estudiantes Pendientes -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-users text-primary mr-2"></i> Estudiantes con Evaluaciones Pendientes
                </h5>
                <span class="badge badge-warning px-3 py-2 font-weight-bold font-size-13 text-dark">
                    Total Pendientes: <?= count($pendientes) ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($pendientes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle font-size-48 text-success mb-3"></i>
                        <h5 class="text-success font-weight-bold">¡Excelente! No hay evaluaciones pendientes para los filtros seleccionados.</h5>
                        <p class="text-muted small">Todos los estudiantes han completado sus encuestas.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="tablaPendientes">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Estudiante</th>
                                    <th>DNI</th>
                                    <th>Correo Registrado</th>
                                    <th>Unidad Didáctica</th>
                                    <th>Docente a Evaluar</th>
                                    <th>Notificación</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n = 1; foreach ($pendientes as $p): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted"><?= $n++ ?></td>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block">
                                                <?= htmlspecialchars($p['estudiante_nombre']) ?>
                                            </span>
                                            <small class="text-muted"><?= htmlspecialchars($p['programa_nombre']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($p['estudiante_dni']) ?></td>
                                        <td>
                                            <?php if (!empty($p['estudiante_correo_personal'])): ?>
                                                <span class="d-block text-dark font-size-13" title="Correo Personal">
                                                    <i class="fas fa-envelope text-primary mr-1"></i> <?= htmlspecialchars($p['estudiante_correo_personal']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="d-block text-muted font-size-13" title="Correo Institucional">
                                                    <i class="fas fa-university text-secondary mr-1"></i> <?= htmlspecialchars($p['estudiante_correo']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="font-weight-semibold text-dark d-block">
                                                <?= htmlspecialchars($p['unidad_didactica_nombre']) ?>
                                            </span>
                                            <small class="badge badge-light border">Sem. <?= htmlspecialchars($p['semestre_romano']) ?> - Sec. <?= htmlspecialchars($p['seccion']) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-primary font-weight-semibold">
                                                <i class="fas fa-chalkboard-teacher mr-1"></i> <?= htmlspecialchars($p['docente_nombre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['notificado_email'])): ?>
                                                <span class="badge badge-soft-success px-2 py-1 font-size-11" title="<?= $p['fecha_notificacion'] ?>">
                                                    <i class="fas fa-check mr-1"></i> Notificado
                                                </span>
                                                <small class="text-muted d-block font-size-10">
                                                    <?= date('d/m/Y', strtotime($p['fecha_notificacion'])) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="badge badge-soft-secondary px-2 py-1 font-size-11">
                                                    Sin notificar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?= BASE_URL ?>/evaluacion/seguimiento/notificarIndividual/<?= $p['id_evaluacion'] ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Enviar Recordatorio por Correo">
                                                <i class="fas fa-paper-plane mr-1"></i> Recordar
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

<script>
function cargarPlanesSeg(idPrograma) {
    const selPlan = document.getElementById('select_plan_seg');
    const selMod = document.getElementById('select_modulo_seg');
    const selSem = document.getElementById('select_semestre_seg');

    selPlan.innerHTML = '<option value="">-- Todos --</option>';
    selMod.innerHTML = '<option value="">-- Todos --</option>';
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idPrograma) return;

    fetch('<?= BASE_URL ?>/evaluacion/seguimiento/ajaxPlanes/' + idPrograma)
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

function cargarModulosSeg(idPlan) {
    const selMod = document.getElementById('select_modulo_seg');
    const selSem = document.getElementById('select_semestre_seg');

    selMod.innerHTML = '<option value="">-- Todos --</option>';
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idPlan) return;

    fetch('<?= BASE_URL ?>/evaluacion/seguimiento/ajaxModulos/' + idPlan)
        .then(res => res.json())
        .then(data => {
            data.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = 'Módulo ' + m.nro_modulo;
                selMod.appendChild(opt);
            });
        })
        .catch(err => console.error('Error al cargar módulos:', err));
}

function cargarSemestresSeg(idModulo) {
    const selSem = document.getElementById('select_semestre_seg');
    selSem.innerHTML = '<option value="">-- Todos --</option>';

    if (!idModulo) return;

    fetch('<?= BASE_URL ?>/evaluacion/seguimiento/ajaxSemestres/' + idModulo)
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
    if ($.fn.DataTable && $('#tablaPendientes').length) {
        $('#tablaPendientes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 15,
            order: [[1, 'asc']]
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
