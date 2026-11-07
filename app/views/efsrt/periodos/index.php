<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-calendar-alt mr-2"></i> Cronograma y Plazos de Subida EFSRT
        </h3>
        <p class="text-muted mb-0">Control de fechas de apertura y cierre para recepción de documentos de estudiantes y supervisores.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalCronograma" onclick="limpiarFormularioCronograma()">
            <i class="fas fa-clock mr-1"></i> Configurar Plazos
        </button>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/efsrt/periodos" class="row align-items-end">
            <div class="col-md-5 form-group mb-md-0">
                <label class="font-weight-bold font-size-13 text-muted">Periodo Académico</label>
                <select name="id_periodo" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los Periodos --</option>
                    <?php foreach ($periodos as $per): ?>
                        <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($per['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 form-group mb-md-0">
                <label class="font-weight-bold font-size-13 text-muted">Sede</label>
                <select name="id_sede" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Todas las Sedes --</option>
                    <?php foreach ($sedes as $se): ?>
                        <option value="<?= $se['id'] ?>" <?= ($filtroSede == $se['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($se['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 form-group mb-0 text-right">
                <a href="<?= BASE_URL ?>/efsrt/periodos" class="btn btn-sm btn-outline-secondary btn-block">
                    <i class="fas fa-sync-alt mr-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Cronogramas -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-periodos-efsrt">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Periodo / Sede / Programa</th>
                        <th>Plazo Estudiantes (Apertura - Cierre)</th>
                        <th>Plazo Docentes Supervisores</th>
                        <th class="text-center">Extemporáneo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($configuraciones)): ?>
                        <?php foreach ($configuraciones as $idx => $cfg): ?>
                            <?php
                            $ahora = date('Y-m-d H:i:s');
                            $estudianteAbierto = ($ahora >= $cfg['fecha_apertura_estudiante'] && $ahora <= $cfg['fecha_cierre_estudiante']);
                            $supervisorAbierto = ($ahora >= $cfg['fecha_apertura_supervisor'] && $ahora <= $cfg['fecha_cierre_supervisor']);
                            ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-primary font-size-14"><?= htmlspecialchars($cfg['periodo_nombre']) ?></strong>
                                    <div class="font-size-12 text-muted">Sede: <?= htmlspecialchars($cfg['sede_nombre']) ?></div>
                                    <div class="font-size-12">
                                        <?= !empty($cfg['programa_nombre']) ? '<span class="badge badge-info">' . htmlspecialchars($cfg['programa_codigo']) . ' - ' . htmlspecialchars($cfg['programa_nombre']) . '</span>' : '<span class="badge badge-secondary">Todos los Programas</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="far fa-calendar-check text-success mr-1"></i> Desde: <strong><?= date('d/m/Y H:i', strtotime($cfg['fecha_apertura_estudiante'])) ?></strong></div>
                                    <div><i class="far fa-calendar-times text-danger mr-1"></i> Hasta: <strong><?= date('d/m/Y H:i', strtotime($cfg['fecha_cierre_estudiante'])) ?></strong></div>
                                    <div class="mt-1">
                                        <?= $estudianteAbierto ? '<span class="badge badge-success px-2"><i class="fas fa-lock-open mr-1"></i> Abierto Actualmente</span>' : '<span class="badge badge-dark px-2"><i class="fas fa-lock mr-1"></i> Cerrado</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="far fa-calendar-check text-success mr-1"></i> Desde: <strong><?= date('d/m/Y H:i', strtotime($cfg['fecha_apertura_supervisor'])) ?></strong></div>
                                    <div><i class="far fa-calendar-times text-danger mr-1"></i> Hasta: <strong><?= date('d/m/Y H:i', strtotime($cfg['fecha_cierre_supervisor'])) ?></strong></div>
                                    <div class="mt-1">
                                        <?= $supervisorAbierto ? '<span class="badge badge-success px-2"><i class="fas fa-lock-open mr-1"></i> Abierto Actualmente</span>' : '<span class="badge badge-dark px-2"><i class="fas fa-lock mr-1"></i> Cerrado</span>' ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?= ($cfg['permite_extemporaneo'] == 1) ? '<span class="badge badge-warning px-2 py-1">Permitido</span>' : '<span class="badge badge-light border">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($cfg['estado'] === 'HABILITADO'): ?>
                                        <span class="badge badge-success px-2 py-1">Habilitado</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary px-2 py-1">Cerrado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick='editarCronograma(<?= json_encode($cfg) ?>)' title="Modificar Fechas">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/efsrt/periodos/eliminar/<?= $cfg['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de eliminar esta configuración de cronograma?')" title="Eliminar Cronograma">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Configurar Cronograma -->
<div class="modal fade" id="modalCronograma" tabindex="-1" role="dialog" aria-labelledby="modalCronogramaTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/periodos/guardar" id="formCronograma">
            <input type="hidden" name="id" id="cfg_id" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalCronogramaTitle">
                        <i class="fas fa-clock mr-2"></i> Configurar Plazos de Subida EFSRT
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Periodo Académico <span class="text-danger">*</span></label>
                            <select name="id_periodo_academico" id="cfg_id_periodo" class="form-control" required>
                                <?php foreach ($periodos as $per): ?>
                                    <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($per['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Sede <span class="text-danger">*</span></label>
                            <select name="id_sede" id="cfg_id_sede" class="form-control" required>
                                <?php foreach ($sedes as $se): ?>
                                    <option value="<?= $se['id'] ?>" <?= ($filtroSede == $se['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($se['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Programa de Estudios (Opcional)</label>
                            <select name="id_programa_estudios" id="cfg_id_programa" class="form-control">
                                <option value="">-- Aplica a Todos los Programas --</option>
                                <?php foreach ($programas as $prg): ?>
                                    <option value="<?= $prg['id'] ?>">
                                        <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Deje en blanco si desea aplicar estas fechas a todos los programas de la sede.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Estado del Cronograma</label>
                            <select name="estado" id="cfg_estado" class="form-control">
                                <option value="HABILITADO">Habilitado</option>
                                <option value="CERRADO">Cerrado</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-user-graduate mr-1"></i> Plazo para Estudiantes (DNI, Ficha de Aceptación, Constancia de Empresa)</h6>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Fecha y Hora de Apertura <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_apertura_estudiante" id="cfg_apertura_est" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Fecha y Hora de Cierre <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_cierre_estudiante" id="cfg_cierre_est" class="form-control" required>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-chalkboard-teacher mr-1"></i> Plazo para Docentes Supervisores (Ficha de Supervisión e Informes)</h6>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Fecha y Hora de Apertura <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_apertura_supervisor" id="cfg_apertura_sup" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Fecha y Hora de Cierre <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_cierre_supervisor" id="cfg_cierre_sup" class="form-control" required>
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="cfg_permite_extemporaneo" name="permite_extemporaneo" value="1">
                        <label class="custom-control-label font-weight-bold text-dark" for="cfg_permite_extemporaneo">
                            Permitir entrega extemporánea bajo autorización
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Cronograma</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function limpiarFormularioCronograma() {
    document.getElementById('modalCronogramaTitle').innerHTML = '<i class="fas fa-clock mr-2"></i> Configurar Plazos de Subida EFSRT';
    document.getElementById('formCronograma').reset();
    document.getElementById('cfg_id').value = '';
}

function editarCronograma(cfg) {
    document.getElementById('modalCronogramaTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Editar Cronograma';
    document.getElementById('cfg_id').value = cfg.id;
    document.getElementById('cfg_id_periodo').value = cfg.id_periodo_academico;
    document.getElementById('cfg_id_sede').value = cfg.id_sede;
    document.getElementById('cfg_id_programa').value = cfg.id_programa_estudios || '';
    document.getElementById('cfg_estado').value = cfg.estado;
    document.getElementById('cfg_apertura_est').value = cfg.fecha_apertura_estudiante ? cfg.fecha_apertura_estudiante.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('cfg_cierre_est').value = cfg.fecha_cierre_estudiante ? cfg.fecha_cierre_estudiante.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('cfg_apertura_sup').value = cfg.fecha_apertura_supervisor ? cfg.fecha_apertura_supervisor.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('cfg_cierre_sup').value = cfg.fecha_cierre_supervisor ? cfg.fecha_cierre_supervisor.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('cfg_permite_extemporaneo').checked = (cfg.permite_extemporaneo == 1);
    $('#modalCronograma').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-periodos-efsrt')) {
        $('#tabla-periodos-efsrt').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
