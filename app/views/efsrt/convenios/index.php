<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-file-contract mr-2"></i> Convenios Institucionales de EFSRT
        </h3>
        <p class="text-muted mb-0">Gestión y registro de convenios marco, específicos y acuerdos interinstitucionales.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if (!empty($puedeGestionar)): ?>
            <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalConvenio" onclick="limpiarFormularioConvenio()">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Convenio
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
        <form method="GET" action="<?= BASE_URL ?>/efsrt/convenios" class="form-row align-items-center">
            <div class="col-md-4 my-1">
                <input type="text" name="q" class="form-control" placeholder="Buscar por N° convenio o descripción..." value="<?= htmlspecialchars($busqueda ?? '') ?>">
            </div>
            <div class="col-md-4 my-1">
                <select name="id_empresa" class="form-control select2">
                    <option value="">Todas las Empresas</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= (isset($id_empresa) && $id_empresa == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['razon_social']) ?> (RUC: <?= htmlspecialchars($emp['ruc'] ?: 'S/N') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 my-1">
                <select name="estado" class="form-control">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" <?= (isset($estado) && $estado === 'ACTIVO') ? 'selected' : '' ?>>Activo</option>
                    <option value="VENCIDO" <?= (isset($estado) && $estado === 'VENCIDO') ? 'selected' : '' ?>>Vencido</option>
                    <option value="EN_RENOVACION" <?= (isset($estado) && $estado === 'EN_RENOVACION') ? 'selected' : '' ?>>En Renovación</option>
                    <option value="RESCINDIDO" <?= (isset($estado) && $estado === 'RESCINDIDO') ? 'selected' : '' ?>>Rescindido</option>
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

<!-- Tabla de Convenios -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-convenios">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>N° Convenio / Tipo</th>
                        <th>Empresa / Institución</th>
                        <th>Vigencia (Inicio - Fin)</th>
                        <th>Documento PDF</th>
                        <th class="text-center">Prácticas</th>
                        <th class="text-center">Estado</th>
                        <?php if (!empty($puedeGestionar)): ?>
                            <th class="text-right">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($convenios)): ?>
                        <?php foreach ($convenios as $idx => $conv): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-primary font-size-14"><?= htmlspecialchars($conv['numero_convenio']) ?></strong>
                                    <div class="font-size-12">
                                        <span class="badge badge-light border"><?= htmlspecialchars($conv['tipo_convenio']) ?></span>
                                    </div>
                                    <?php if (!empty($conv['descripcion_objeto'])): ?>
                                        <small class="text-muted d-block mt-1" style="max-width: 280px;"><?= htmlspecialchars(mb_strimwidth($conv['descripcion_objeto'], 0, 70, '...')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($conv['empresa_razon_social']) ?></strong>
                                    <div class="font-size-12 text-muted">RUC: <?= htmlspecialchars($conv['empresa_ruc'] ?: 'S/N') ?></div>
                                    <small class="text-muted"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($conv['empresa_representante']) ?></small>
                                </td>
                                <td>
                                    <div><i class="far fa-calendar-alt text-muted mr-1"></i> Firma: <?= date('d/m/Y', strtotime($conv['fecha_firma'])) ?></div>
                                    <div class="font-size-12 mt-1">
                                        <span class="text-success font-weight-bold"><?= date('d/m/Y', strtotime($conv['fecha_inicio'])) ?></span> 
                                        al 
                                        <span class="text-danger font-weight-bold"><?= date('d/m/Y', strtotime($conv['fecha_fin'])) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($conv['archivo_convenio_pdf'])): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/convenios/descargarPdf/<?= $conv['id'] ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-12">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary font-size-12 px-2 py-1">
                                        <?= (int)($conv['total_practicas'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $bCls = 'badge-secondary';
                                    if ($conv['estado'] === 'ACTIVO') $bCls = 'badge-success';
                                    elseif ($conv['estado'] === 'VENCIDO') $bCls = 'badge-danger';
                                    elseif ($conv['estado'] === 'EN_RENOVACION') $bCls = 'badge-warning';
                                    ?>
                                    <span class="badge <?= $bCls ?> px-2 py-1"><?= htmlspecialchars($conv['estado']) ?></span>
                                </td>
                                <?php if (!empty($puedeGestionar)): ?>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='editarConvenio(<?= json_encode($conv) ?>)' title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
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

<!-- Modal Registrar / Editar Convenio -->
<?php if (!empty($puedeGestionar)): ?>
<div class="modal fade" id="modalConvenio" tabindex="-1" role="dialog" aria-labelledby="modalConvenioTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/convenios/guardar" enctype="multipart/form-data" id="formConvenio">
            <input type="hidden" name="id" id="conv_id" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalConvenioTitle">
                        <i class="fas fa-file-contract mr-2"></i> Registrar Convenio Institucional
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Empresa / Institución Receptora <span class="text-danger">*</span></label>
                            <select name="id_empresa" id="conv_id_empresa" class="form-control" required>
                                <option value="">-- Seleccione Empresa --</option>
                                <?php foreach ($empresas as $emp): ?>
                                    <option value="<?= $emp['id'] ?>">
                                        <?= htmlspecialchars($emp['razon_social']) ?> (RUC: <?= htmlspecialchars($emp['ruc'] ?: 'S/N') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Tipo de Convenio <span class="text-danger">*</span></label>
                            <select name="tipo_convenio" id="conv_tipo_convenio" class="form-control" required>
                                <option value="ESPECIFICO">Específico</option>
                                <option value="MARCO">Marco</option>
                                <option value="CARTA_ENTENDIMIENTO">Carta de Entendimiento</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">N° de Convenio / Resolución <span class="text-danger">*</span></label>
                            <input type="text" name="numero_convenio" id="conv_numero_convenio" class="form-control" required placeholder="Ej. CONV-2026-001">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Estado del Convenio</label>
                            <select name="estado" id="conv_estado" class="form-control">
                                <option value="ACTIVO">Activo</option>
                                <option value="VENCIDO">Vencido</option>
                                <option value="EN_RENOVACION">En Renovación</option>
                                <option value="RESCINDIDO">Rescindido</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Fecha de Firma <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_firma" id="conv_fecha_firma" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" id="conv_fecha_inicio" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Fecha de Fin / Término <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" id="conv_fecha_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Archivo Escaneado del Convenio (PDF)</label>
                            <div class="custom-file">
                                <input type="file" name="archivo_pdf" id="conv_archivo_pdf" class="custom-file-input" accept=".pdf">
                                <label class="custom-file-label" for="conv_archivo_pdf">Seleccionar archivo PDF (máx. 15MB)...</label>
                            </div>
                            <small class="text-muted">Si no desea cambiar el archivo actual al editar, deje este campo vacío.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Descripción / Objeto del Convenio</label>
                            <textarea name="descripcion_objeto" id="conv_descripcion_objeto" class="form-control" rows="3" placeholder="Detalle de compromisos, carreras involucradas, beneficios para estudiantes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Convenio</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
<?php if (!empty($puedeGestionar)): ?>
function limpiarFormularioConvenio() {
    document.getElementById('modalConvenioTitle').innerHTML = '<i class="fas fa-file-contract mr-2"></i> Registrar Convenio Institucional';
    document.getElementById('formConvenio').reset();
    document.getElementById('conv_id').value = '';
}

function editarConvenio(conv) {
    document.getElementById('modalConvenioTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Editar Convenio: ' + conv.numero_convenio;
    document.getElementById('conv_id').value = conv.id;
    document.getElementById('conv_id_empresa').value = conv.id_empresa;
    document.getElementById('conv_tipo_convenio').value = conv.tipo_convenio;
    document.getElementById('conv_numero_convenio').value = conv.numero_convenio;
    document.getElementById('conv_estado').value = conv.estado;
    document.getElementById('conv_fecha_firma').value = conv.fecha_firma;
    document.getElementById('conv_fecha_inicio').value = conv.fecha_inicio;
    document.getElementById('conv_fecha_fin').value = conv.fecha_fin;
    document.getElementById('conv_descripcion_objeto').value = conv.descripcion_objeto || '';
    $('#modalConvenio').modal('show');
}
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-convenios')) {
        $('#tabla-convenios').DataTable({
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
