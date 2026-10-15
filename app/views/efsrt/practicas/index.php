<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-folder-open mr-2"></i> Expedientes de EFSRT
        </h3>
        <p class="text-muted mb-0">Gestión, seguimiento y flujo de aprobación de prácticas pre-profesionales.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if ($esEstudiante || $esAdminOSecretario || $esCoordinador): ?>
            <a href="<?= BASE_URL ?>/efsrt/practicas/crear" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle mr-1"></i> <?= $esEstudiante ? 'Registrar Mi Práctica' : 'Nuevo Expediente' ?>
            </a>
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
        <form method="GET" action="<?= BASE_URL ?>/efsrt/practicas" class="form-row align-items-center">
            <div class="col-md-3 my-1">
                <input type="text" name="q" class="form-control" placeholder="Buscar por estudiante, DNI, empresa..." value="<?= htmlspecialchars($busqueda ?? '') ?>">
            </div>
            <div class="col-md-3 my-1">
                <select name="id_periodo" class="form-control">
                    <option value="">Todos los Periodos</option>
                    <?php foreach ($periodos as $per): ?>
                        <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($per['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!$esEstudiante): ?>
                <div class="col-md-3 my-1">
                    <select name="id_programa" class="form-control">
                        <option value="">Todos los Programas</option>
                        <?php foreach ($programas as $prg): ?>
                            <option value="<?= $prg['id'] ?>" <?= (isset($filtroPrograma) && $filtroPrograma == $prg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-<?= $esEstudiante ? '4' : '2' ?> my-1">
                <select name="estado" class="form-control">
                    <option value="">Todos los Estados</option>
                    <option value="REGISTRADO" <?= (isset($filtroEstado) && $filtroEstado === 'REGISTRADO') ? 'selected' : '' ?>>Registrado</option>
                    <option value="DOCUMENTACION_SUBIDA" <?= (isset($filtroEstado) && $filtroEstado === 'DOCUMENTACION_SUBIDA') ? 'selected' : '' ?>>Documentación Subida</option>
                    <option value="OBSERVADO_SUPERVISOR" <?= (isset($filtroEstado) && $filtroEstado === 'OBSERVADO_SUPERVISOR') ? 'selected' : '' ?>>Observado (Supervisor)</option>
                    <option value="DERIVADO_COORDINADOR" <?= (isset($filtroEstado) && $filtroEstado === 'DERIVADO_COORDINADOR') ? 'selected' : '' ?>>Derivado a Coordinador</option>
                    <option value="OBSERVADO_COORDINADOR" <?= (isset($filtroEstado) && $filtroEstado === 'OBSERVADO_COORDINADOR') ? 'selected' : '' ?>>Observado (Coordinador)</option>
                    <option value="DERIVADO_SECRETARIA" <?= (isset($filtroEstado) && $filtroEstado === 'DERIVADO_SECRETARIA') ? 'selected' : '' ?>>Derivado a Secretaría</option>
                    <option value="FINALIZADO_EMITIDO" <?= (isset($filtroEstado) && $filtroEstado === 'FINALIZADO_EMITIDO') ? 'selected' : '' ?>>Constancia Emitida</option>
                </select>
            </div>
            <div class="col-md-<?= $esEstudiante ? '2' : '1' ?> my-1">
                <button type="submit" class="btn btn-secondary btn-block">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Prácticas -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-practicas">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Estudiante / DNI</th>
                        <th>Programa / Módulo</th>
                        <th>Empresa Receptora</th>
                        <th>Supervisor Asignado</th>
                        <th>Fechas / Horas</th>
                        <th class="text-center">Docs</th>
                        <th class="text-center">Estado del Trámite</th>
                        <th class="text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($practicas)): ?>
                        <?php foreach ($practicas as $idx => $p): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-size-14"><?= htmlspecialchars($p['estudiante_nombre']) ?></strong>
                                    <div class="font-size-12 text-muted">
                                        <i class="fas fa-id-card mr-1"></i> DNI: <?= htmlspecialchars($p['estudiante_dni']) ?>
                                    </div>
                                    <?php if (!empty($p['estudiante_telefono'])): ?>
                                        <div class="font-size-12 text-muted">
                                            <i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($p['estudiante_telefono']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($p['programa_codigo']) ?></span>
                                    <strong class="font-size-13 text-primary">Módulo <?= htmlspecialchars($p['modulo_nro']) ?></strong>
                                    <div class="font-size-12 text-muted"><?= htmlspecialchars($p['modulo_nombre']) ?></div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['empresa_razon_social']) ?></strong>
                                    <div class="font-size-12 text-muted">
                                        Área: <?= htmlspecialchars($p['area_desempeno']) ?>
                                    </div>
                                    <?php if (!empty($p['numero_convenio'])): ?>
                                        <span class="badge badge-light border font-size-11 mt-1">
                                            <i class="fas fa-file-contract text-primary mr-1"></i> <?= htmlspecialchars($p['numero_convenio']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['supervisor_nombre'])): ?>
                                        <div class="font-weight-bold font-size-13 text-dark"><?= htmlspecialchars($p['supervisor_nombre']) ?></div>
                                        <small class="text-muted"><i class="fas fa-id-badge"></i> DNI: <?= htmlspecialchars($p['supervisor_dni']) ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i> Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-size-12">
                                        <i class="far fa-calendar-alt text-muted mr-1"></i> <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?>
                                    </div>
                                    <div class="font-size-12 mt-1">
                                        <strong><?= (int)$p['horas_requeridas'] ?> hrs.</strong>
                                        <?php if (!empty($p['calificacion_final'])): ?>
                                            | <span class="badge badge-success font-size-11">Nota: <?= number_format($p['calificacion_final'], 1) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center text-nowrap">
                                    <span class="badge badge-light border font-size-12 px-2 py-1" title="Documentos subidos">
                                        <i class="fas fa-paperclip text-muted mr-1"></i> <?= (int)$p['total_documentos'] ?>
                                    </span>
                                    <?php if ($p['total_docs_observados'] > 0): ?>
                                        <span class="badge badge-danger ml-1" title="Documentos observados"><?= (int)$p['total_docs_observados'] ?> obs.</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $bCls = 'badge-secondary';
                                    $lbl = $p['estado'];
                                    switch ($p['estado']) {
                                        case 'REGISTRADO': $bCls = 'badge-secondary'; $lbl = 'Registrado'; break;
                                        case 'DOCUMENTACION_SUBIDA': $bCls = 'badge-info'; $lbl = 'Docs Subidos'; break;
                                        case 'OBSERVADO_SUPERVISOR': $bCls = 'badge-danger'; $lbl = 'Obs. Supervisor'; break;
                                        case 'APROBADO_SUPERVISOR': $bCls = 'badge-primary'; $lbl = 'Aprob. Supervisor'; break;
                                        case 'DERIVADO_COORDINADOR': $bCls = 'badge-warning'; $lbl = 'En Coord. Área'; break;
                                        case 'OBSERVADO_COORDINADOR': $bCls = 'badge-danger'; $lbl = 'Obs. Coordinador'; break;
                                        case 'APROBADO_COORDINADOR': $bCls = 'badge-primary'; $lbl = 'Aprob. Coordinador'; break;
                                        case 'DERIVADO_SECRETARIA': $bCls = 'badge-warning'; $lbl = 'En Secretaría'; break;
                                        case 'OBSERVADO_SECRETARIA': $bCls = 'badge-danger'; $lbl = 'Obs. Secretaría'; break;
                                        case 'FINALIZADO_EMITIDO': $bCls = 'badge-success'; $lbl = 'Constancia Emitida'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $bCls ?> px-2 py-1 font-size-12">
                                        <?= htmlspecialchars($lbl) ?>
                                    </span>
                                </td>
                                <td class="text-right text-nowrap">
                                    <a href="<?= BASE_URL ?>/efsrt/practicas/detalle/<?= $p['id'] ?>" class="btn btn-sm btn-primary shadow-sm" title="Abrir Expediente">
                                        <i class="fas fa-folder-open mr-1"></i> Expediente
                                    </a>
                                    <?php if (!empty($p['nro_constancia'])): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/constancias/descargar/<?= $p['id'] ?>" class="btn btn-sm btn-success shadow-sm ml-1" title="Descargar Constancia Oficial (<?= $p['constancia_archivo'] ?>)">
                                            <i class="fas fa-certificate"></i>
                                        </a>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-practicas')) {
        $('#tabla-practicas').DataTable({
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
