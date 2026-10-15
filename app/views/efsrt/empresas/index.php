<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-building mr-2"></i> Directorio de Empresas e Instituciones
        </h3>
        <p class="text-muted mb-0">Gestión de centros de prácticas, entidades receptoras y convenios de EFSRT.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if (!empty($puedeGestionar)): ?>
            <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalEmpresa" onclick="limpiarFormularioEmpresa()">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Empresa
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

<!-- Barra de búsqueda y filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/efsrt/empresas" class="form-row align-items-center">
            <div class="col-md-7 my-1">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="q" class="form-control border-left-0" placeholder="Buscar por RUC, razón social, representante legal..." value="<?= htmlspecialchars($busqueda ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3 my-1">
                <select name="estado" class="form-control">
                    <option value="">Todos los Estados</option>
                    <option value="1" <?= (isset($estado) && $estado === '1') ? 'selected' : '' ?>>Activas</option>
                    <option value="0" <?= (isset($estado) && $estado === '0') ? 'selected' : '' ?>>Inactivas</option>
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

<!-- Tabla de Empresas -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-empresas">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>RUC / Empresa</th>
                        <th>Representante Legal</th>
                        <th>Contacto</th>
                        <th>Dirección / Ubicación</th>
                        <th class="text-center">Convenios</th>
                        <th class="text-center">Estado</th>
                        <?php if (!empty($puedeGestionar)): ?>
                            <th class="text-right">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empresas)): ?>
                        <?php foreach ($empresas as $idx => $emp): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-size-14"><?= htmlspecialchars($emp['razon_social']) ?></strong>
                                    <?php if (!empty($emp['nombre_comercial'])): ?>
                                        <div class="font-size-12 text-muted font-italic"><?= htmlspecialchars($emp['nombre_comercial']) ?></div>
                                    <?php endif; ?>
                                    <div class="font-size-12 mt-1">
                                        <span class="badge badge-light border">
                                            <i class="fas fa-id-card-alt text-muted mr-1"></i> RUC: <?= htmlspecialchars($emp['ruc'] ?: 'S/N') ?>
                                        </span>
                                        <?php if (!empty($emp['rubro_sector'])): ?>
                                            <span class="badge badge-info ml-1"><?= htmlspecialchars($emp['rubro_sector']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($emp['representante_legal']) ?></strong>
                                    <?php if (!empty($emp['cargo_representante'])): ?>
                                        <div class="font-size-12 text-muted"><?= htmlspecialchars($emp['cargo_representante']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($emp['contacto_telefono'])): ?>
                                        <div><i class="fas fa-phone-alt font-size-12 text-muted mr-1"></i> <?= htmlspecialchars($emp['contacto_telefono']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($emp['contacto_correo'])): ?>
                                        <div><i class="fas fa-envelope font-size-12 text-muted mr-1"></i> <?= htmlspecialchars($emp['contacto_correo']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($emp['direccion']) ?></div>
                                    <?php if (!empty($emp['distrito']) || !empty($emp['provincia'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars(trim("{$emp['distrito']}, {$emp['provincia']}", ', ')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary font-size-12 px-2 py-1">
                                        <?= (int)($emp['total_convenios'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($emp['estado'] == 1): ?>
                                        <span class="badge badge-success px-2 py-1">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($puedeGestionar)): ?>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick='editarEmpresa(<?= json_encode($emp) ?>)' title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/efsrt/empresas/cambiarEstado/<?= $emp['id'] ?>" class="btn btn-sm <?= ($emp['estado'] == 1) ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= ($emp['estado'] == 1) ? 'Desactivar' : 'Activar' ?>" onclick="return confirm('¿Está seguro de cambiar el estado de esta empresa?')">
                                            <i class="fas <?= ($emp['estado'] == 1) ? 'fa-ban' : 'fa-check' ?>"></i>
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

<!-- Modal Registrar / Editar Empresa -->
<?php if (!empty($puedeGestionar)): ?>
<div class="modal fade" id="modalEmpresa" tabindex="-1" role="dialog" aria-labelledby="modalEmpresaTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/empresas/guardar" id="formEmpresa">
            <input type="hidden" name="id" id="emp_id" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalEmpresaTitle">
                        <i class="fas fa-building mr-2"></i> Registrar Empresa / Entidad Receptora
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">RUC</label>
                            <input type="text" name="ruc" id="emp_ruc" class="form-control" maxlength="11" placeholder="Ej. 20123456789">
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="razon_social" id="emp_razon_social" class="form-control" required placeholder="Nombre legal de la empresa">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nombre Comercial</label>
                            <input type="text" name="nombre_comercial" id="emp_nombre_comercial" class="form-control" placeholder="Marca o nombre comercial">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Rubro o Sector</label>
                            <input type="text" name="rubro_sector" id="emp_rubro_sector" class="form-control" placeholder="Ej. Tecnológico, Salud, Minería, Servicios">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold">Dirección Fiscal / Sede <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" id="emp_direccion" class="form-control" required placeholder="Av., Jr., Calle, Nro.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Distrito</label>
                            <input type="text" name="distrito" id="emp_distrito" class="form-control" placeholder="Distrito">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Provincia</label>
                            <input type="text" name="provincia" id="emp_provincia" class="form-control" placeholder="Provincia">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Departamento</label>
                            <input type="text" name="departamento" id="emp_departamento" class="form-control" placeholder="Departamento">
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="text-primary font-weight-bold mb-3"><i class="fas fa-user-tie mr-1"></i> Representante Legal y Contacto</h6>

                    <div class="row">
                        <div class="col-md-7 form-group">
                            <label class="font-weight-bold">Representante Legal <span class="text-danger">*</span></label>
                            <input type="text" name="representante_legal" id="emp_representante_legal" class="form-control" required placeholder="Nombres y Apellidos">
                        </div>
                        <div class="col-md-5 form-group">
                            <label>Cargo del Representante</label>
                            <input type="text" name="cargo_representante" id="emp_cargo_representante" class="form-control" placeholder="Ej. Gerente General, Director">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Teléfono de Contacto</label>
                            <input type="text" name="contacto_telefono" id="emp_contacto_telefono" class="form-control" placeholder="Celular o teléfono fijo">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Correo Electrónico de Contacto</label>
                            <input type="email" name="contacto_correo" id="emp_contacto_correo" class="form-control" placeholder="contacto@empresa.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Empresa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
<?php if (!empty($puedeGestionar)): ?>
function limpiarFormularioEmpresa() {
    document.getElementById('modalEmpresaTitle').innerHTML = '<i class="fas fa-building mr-2"></i> Registrar Empresa / Entidad Receptora';
    document.getElementById('formEmpresa').reset();
    document.getElementById('emp_id').value = '';
}

function editarEmpresa(emp) {
    document.getElementById('modalEmpresaTitle').innerHTML = '<i class="fas fa-edit mr-2"></i> Editar Empresa: ' + emp.razon_social;
    document.getElementById('emp_id').value = emp.id;
    document.getElementById('emp_ruc').value = emp.ruc || '';
    document.getElementById('emp_razon_social').value = emp.razon_social || '';
    document.getElementById('emp_nombre_comercial').value = emp.nombre_comercial || '';
    document.getElementById('emp_rubro_sector').value = emp.rubro_sector || '';
    document.getElementById('emp_direccion').value = emp.direccion || '';
    document.getElementById('emp_distrito').value = emp.distrito || '';
    document.getElementById('emp_provincia').value = emp.provincia || '';
    document.getElementById('emp_departamento').value = emp.departamento || '';
    document.getElementById('emp_representante_legal').value = emp.representante_legal || '';
    document.getElementById('emp_cargo_representante').value = emp.cargo_representante || '';
    document.getElementById('emp_contacto_telefono').value = emp.contacto_telefono || '';
    document.getElementById('emp_contacto_correo').value = emp.contacto_correo || '';
    $('#modalEmpresa').modal('show');
}
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-empresas')) {
        $('#tabla-empresas').DataTable({
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
