<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-chalkboard-teacher mr-2"></i> Docentes Supervisores de EFSRT
        </h3>
        <p class="text-muted mb-0">Designación de docentes para la supervisión y validación de expedientes por programa de estudios.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if (!empty($puedeGestionar)): ?>
            <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalSupervisor" onclick="limpiarFormularioSupervisor()">
                <i class="fas fa-user-plus mr-1"></i> Asignar Supervisor
            </button>
        <?php endif; ?>
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

<!-- Barra de Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/efsrt/supervisores" class="form-row align-items-center">
            <div class="col-md-4 my-1">
                <select name="id_periodo" class="form-control">
                    <?php foreach ($periodos as $per): ?>
                        <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                            Periodo: <?= htmlspecialchars($per['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 my-1">
                <select name="id_programa" class="form-control">
                    <option value="">Todos los Programas de Estudio</option>
                    <?php foreach ($programas as $prg): ?>
                        <option value="<?= $prg['id'] ?>" <?= ($filtroPrograma == $prg['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 my-1">
                <select name="id_sede" class="form-control">
                    <?php foreach ($sedes as $se): ?>
                        <option value="<?= $se['id'] ?>" <?= ($filtroSede == $se['id']) ? 'selected' : '' ?>>
                            Sede: <?= htmlspecialchars($se['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 my-1">
                <button type="submit" class="btn btn-secondary btn-block">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Supervisores -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-supervisores">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Docente Supervisor</th>
                        <th>Programa de Estudios</th>
                        <th>Resolución</th>
                        <th class="text-center">Capacidad Máx.</th>
                        <th class="text-center">Estudiantes Asignados</th>
                        <th class="text-center">Aprobados</th>
                        <th class="text-center">Estado</th>
                        <?php if (!empty($puedeGestionar)): ?>
                            <th class="text-right">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($supervisores)): ?>
                        <?php foreach ($supervisores as $idx => $sup): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-size-14"><?= htmlspecialchars($sup['docente_nombre']) ?></strong>
                                    <div class="font-size-12 text-muted">
                                        <i class="fas fa-id-card mr-1"></i> DNI: <?= htmlspecialchars($sup['docente_dni']) ?>
                                        <?php if (!empty($sup['docente_telefono'])): ?>
                                            | <i class="fas fa-phone-alt ml-1 mr-1"></i> <?= htmlspecialchars($sup['docente_telefono']) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($sup['programa_codigo']) ?></span>
                                    <strong class="font-size-13"><?= htmlspecialchars($sup['programa_nombre']) ?></strong>
                                    <div class="font-size-12 text-muted"><?= htmlspecialchars($sup['sede_nombre']) ?></div>
                                </td>
                                <td>
                                    <?= !empty($sup['resolucion_designacion']) ? htmlspecialchars($sup['resolucion_designacion']) : '<span class="text-muted font-italic font-size-12">Sin resolución</span>' ?>
                                </td>
                                <td class="text-center font-weight-bold">
                                    <?= (int)$sup['max_estudiantes'] ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary font-size-12 px-2 py-1">
                                        <?= (int)$sup['total_asignados'] ?> / <?= (int)$sup['max_estudiantes'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-success font-size-12 px-2 py-1">
                                        <?= (int)$sup['total_aprobados'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($sup['estado'] == 1): ?>
                                        <span class="badge badge-success px-2 py-1">Habilitado</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($puedeGestionar)): ?>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='editarSupervisor(<?= json_encode($sup) ?>)' title="Editar Asignación">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/efsrt/supervisores/cambiarEstado/<?= $sup['id'] ?>" class="btn btn-sm <?= ($sup['estado'] == 1) ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= ($sup['estado'] == 1) ? 'Inhabilitar' : 'Habilitar' ?>" onclick="return confirm('¿Está seguro de cambiar el estado de este supervisor?')">
                                            <i class="fas <?= ($sup['estado'] == 1) ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Asignar / Editar Supervisor -->
<?php if (!empty($puedeGestionar)): ?>
<div class="modal fade" id="modalSupervisor" tabindex="-1" role="dialog" aria-labelledby="modalSupervisorTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/supervisores/guardar" id="formSupervisor">
            <input type="hidden" name="id" id="sup_id" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalSupervisorTitle">
                        <i class="fas fa-user-plus mr-2"></i> Asignar Docente Supervisor de EFSRT
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Docente <span class="text-danger">*</span></label>
                            <select name="id_docente" id="sup_id_docente" class="form-control select2" required>
                                <option value="">-- Seleccionar Docente --</option>
                                <?php foreach ($docentes as $doc): ?>
                                    <option value="<?= $doc['id'] ?>">
                                        <?= htmlspecialchars($doc['apellidos_nombres']) ?> (DNI: <?= htmlspecialchars($doc['dni']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Periodo Académico <span class="text-danger">*</span></label>
                            <select name="id_periodo_academico" id="sup_id_periodo" class="form-control" required>
                                <?php foreach ($periodos as $per): ?>
                                    <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($per['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Sede <span class="text-danger">*</span></label>
                            <select name="id_sede" id="sup_id_sede" class="form-control" required>
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
                            <label class="font-weight-bold">Programa de Estudios <span class="text-danger">*</span></label>
                            <select name="id_programa_estudios" id="sup_id_programa" class="form-control" required>
                                <option value="">-- Seleccionar Programa --</option>
                                <?php foreach ($programas as $prg): ?>
                                    <option value="<?= $prg['id'] ?>">
                                        <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Capacidad Máx. Estudiantes</label>
                            <input type="number" name="max_estudiantes" id="sup_max_estudiantes" class="form-control" min="1" max="150" value="30" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Resolución / Documento de Designación</label>
                            <input type="text" name="resolucion_designacion" id="sup_resolucion" class="form-control" placeholder="Ej. R.D. N° 045-2026-DG-IESTP-H">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Asignación</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
<?php if (!empty($puedeGestionar)): ?>
function limpiarFormularioSupervisor() {
    document.getElementById('modalSupervisorTitle').innerHTML = '<i class="fas fa-user-plus mr-2"></i> Asignar Docente Supervisor de EFSRT';
    document.getElementById('formSupervisor').reset();
    document.getElementById('sup_id').value = '';
    document.getElementById('sup_max_estudiantes').value = '30';
}

function editarSupervisor(sup) {
    document.getElementById('modalSupervisorTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Editar Supervisor: ' + sup.docente_nombre;
    document.getElementById('sup_id').value = sup.id;
    document.getElementById('sup_id_docente').value = sup.id_docente;
    document.getElementById('sup_id_periodo').value = sup.id_periodo_academico;
    document.getElementById('sup_id_sede').value = sup.id_sede;
    document.getElementById('sup_id_programa').value = sup.id_programa_estudios;
    document.getElementById('sup_max_estudiantes').value = sup.max_estudiantes;
    document.getElementById('sup_resolucion').value = sup.resolucion_designacion || '';
    $('#modalSupervisor').modal('show');
}
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-supervisores')) {
        $('#tabla-supervisores').DataTable({
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
