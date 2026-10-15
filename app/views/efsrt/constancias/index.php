<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-certificate mr-2"></i> Registro de Constancias Oficiales de EFSRT
        </h3>
        <p class="text-muted mb-0">Emisión digital, verificación con código QR y descarga en formato estandarizado.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-secondary shadow-sm">
            <i class="fas fa-folder-open mr-1"></i> Ir a Expedientes
        </a>
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
        <form method="GET" action="<?= BASE_URL ?>/efsrt/constancias" class="form-row align-items-center">
            <div class="col-md-5 my-1">
                <input type="text" name="q" class="form-control" placeholder="Buscar por N° de constancia, DNI o estudiante..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <?php if (!$esEstudiante): ?>
                <div class="col-md-4 my-1">
                    <select name="id_programa" class="form-control">
                        <option value="">Todos los Programas de Estudio</option>
                        <?php foreach ($programas as $prg): ?>
                            <option value="<?= $prg['id'] ?>" <?= (isset($_GET['id_programa']) && $_GET['id_programa'] == $prg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-<?= $esEstudiante ? '5' : '2' ?> my-1">
                <select name="id_periodo" class="form-control">
                    <option value="">Todos los Periodos</option>
                    <?php foreach ($periodos as $per): ?>
                        <option value="<?= $per['id'] ?>" <?= (isset($_GET['id_periodo']) && $_GET['id_periodo'] == $per['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($per['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 my-1">
                <button type="submit" class="btn btn-secondary btn-block">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Constancias -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-constancias">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>N° Constancia</th>
                        <th>Estudiante / DNI</th>
                        <th>Programa / Módulo</th>
                        <th>Nombre de Archivo Oficial</th>
                        <th>Fecha Emisión</th>
                        <th>Emitido Por</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($constancias)): ?>
                        <?php foreach ($constancias as $idx => $cst): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-primary font-size-14"><?= htmlspecialchars($cst['nro_constancia']) ?></strong>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($cst['estudiante_nombre']) ?></strong>
                                    <div class="font-size-12 text-muted">DNI: <?= htmlspecialchars($cst['estudiante_dni']) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($cst['programa_codigo']) ?></span>
                                    <strong class="font-size-13">Módulo <?= htmlspecialchars($cst['nro_modulo']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($cst['modulo_nombre']) ?></small>
                                </td>
                                <td>
                                    <code class="text-dark font-weight-bold font-size-12 bg-light px-2 py-1 border rounded">
                                        <?= htmlspecialchars($cst['nombre_archivo_descarga']) ?>
                                    </code>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($cst['fecha_emision'])) ?>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="fas fa-user-check mr-1"></i> <?= htmlspecialchars($cst['emitido_por_nombre']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1">Emitida</span>
                                </td>
                                <td class="text-right text-nowrap">
                                    <a href="<?= BASE_URL ?>/efsrt/constancias/verPdf/<?= $cst['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Ver en Navegador">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/efsrt/constancias/descargar/<?= $cst['id'] ?>" class="btn btn-sm btn-success shadow-sm ml-1" title="Descargar PDF (<?= $cst['nombre_archivo_descarga'] ?>)">
                                        <i class="fas fa-download mr-1"></i> Descargar
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-constancias')) {
        $('#tabla-constancias').DataTable({
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
