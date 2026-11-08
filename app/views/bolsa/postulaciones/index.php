<?php require __DIR__ . '/../../layouts/header.php'; ?>
<!-- ============================================================== -->
<!-- 2. VISTA PARA ADMINISTRADOR ("GESTIÓN GENERAL DE POSTULACIONES") -->
<!-- ============================================================== -->
<?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1 text-dark font-weight-bold">Gestión General de Postulaciones
                </h3>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-primary shadow-sm mr-1">
                    <i class="far fa-address-book mr-1"></i> Ver Convocatorias
                </a>
                <a href="<?= BASE_URL ?>/bolsa/empresas" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-building mr-1"></i> Empresas
                </a>
            </div>
        </div>
    </div>

    <!-- Cajas de métricas institucionales -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #4e73df !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Total Candidatos</div>
                        <h4 class="font-weight-bold mb-0 text-dark"><?= (int)($metricas['total'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-users fa-2x text-primary" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #f6c23e !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Pendientes</div>
                        <h4 class="font-weight-bold mb-0 text-warning"><?= (int)($metricas['pendientes'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #36b9cc !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">En Evaluación</div>
                        <h4 class="font-weight-bold mb-0 text-info"><?= (int)($metricas['evaluacion'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-user-check fa-2x text-info" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #1cc88a !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Seleccionados</div>
                        <h4 class="font-weight-bold mb-0 text-success"><?= (int)($metricas['seleccionados'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-check-double fa-2x text-success" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="card p-3 shadow-sm rounded-3 mb-3">
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold">Convocatoria</label>
                <select id="filter-oferta" class="form-control">
                    <option value="">Todas las convocatorias</option>
                    <?php foreach ($ofertas as $of): ?>
                        <option value="<?= $of['id'] ?>" <?= (($filtros['id_oferta'] ?? '') == $of['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($of['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold">Empresa</label>
                <select id="filter-empresa" class="form-control">
                    <option value="">Todas las empresas</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= (($filtros['id_empresa'] ?? '') == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['empresa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold">Estado</label>
                <select id="filter-estado" class="form-control">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= (($filtros['estado'] ?? '') == $est['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla General para Administradores -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-admin-postulaciones" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">DNI</th>
                        <th class="text-center">Postulante</th>
                        <th class="text-center">Oferta Laboral</th>
                        <th class="text-center">Empresa</th>
                        <th class="text-center">Currículum</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postulaciones as $idx => $row): 
                        $est = (int)$row['estado'];
                        $estadoInfo = $estados[$est] ?? [
                            'nombre' => 'En Proceso', 
                            'badge'  => 'badge-secondary', 
                            'icono'  => 'fas fa-info'
                        ];
                    ?>
                        <tr data-oferta="<?= $row['id_oferta_laboral'] ?>" data-empresa="<?= htmlspecialchars($row['id_empresa'] ?? '') ?>" data-estado="<?= $row['estado'] ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['dni'] ?? '-') ?></td>
                            <td>
                                <?= htmlspecialchars($row['postulante_nombre'] ?? $row['apellidos_nombres']) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/bolsa/postulaciones/ver/<?= $row['id_oferta_laboral'] ?>">
                                    <?= htmlspecialchars($row['oferta_titulo'] ?? '-') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($row['empresa_nombre'] ?? '-') ?></td>
                            <td class="text-center">
                                    <a href="<?= BASE_URL ?>/bolsa/curriculum/ver/<?= $row['id_curriculum'] ?>" target="_blank" class="btn btn-info btn-sm py-0 px-2" title="Ver ficha">
                                        <i class="fas fa-id-card"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $row['id_curriculum'] ?>" target="_blank" class="btn btn-danger btn-sm py-0 px-2" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <?php if (!empty($row['cv_archivo'])): ?>
                                        <a href="<?= htmlspecialchars($row['cv_archivo']) ?>" target="_blank" class="btn btn-primary btn-sm py-0 px-2" title="Link web">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= date('d/m/Y', strtotime($row['fecha'])) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $estadoInfo['badge'] ?>">
                                    <?= htmlspecialchars($estadoInfo['nombre']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                    class="btn btn-sm btn-primary btn-cambiar-estado" 
                                    data-id="<?= $row['id'] ?>"
                                    data-nombre="<?= rawurlencode($row['postulante_nombre'] ?? $row['apellidos_nombres']) ?>"
                                    data-dni="<?= $row['dni'] ?>"
                                    data-estado="<?= $row['estado'] ?>"
                                    title="Actualizar estado"
                                >
                                    <i class="fas fa-edit mr-1"></i> Estado
                                </button>
                                <a href="<?= BASE_URL ?>/bolsa/postulaciones/ver/<?= $row['id_oferta_laboral'] ?>" class="btn btn-sm btn-secondary" title="Ver candidatos de la oferta">
                                    <i class="fas fa-users"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DE CAMBIO DE ESTADO -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <form action="<?= BASE_URL ?>/bolsa/postulaciones/cambiarEstado" method="post">
                    <input type="hidden" name="id_postulacion" id="modal-id-postulacion" value="">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold text-dark">
                            <i class="fas fa-tasks text-primary mr-2"></i>Actualizar Estado de Postulación
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded border mb-3">
                            <small class="text-muted d-block">Postulante:</small>
                            <strong id="modal-postulante-nombre" class="font-size-15 text-dark d-block"></strong>
                            <span id="modal-postulante-dni" class="small text-muted"></span>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Nuevo estado:</label>
                            <select name="estado" id="modal-select-estado" class="form-control" required>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?= $est['id'] ?>"><?= htmlspecialchars($est['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtro personalizado reactivo para DataTables
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'tabla-admin-postulaciones') {
                    return true;
                }
                const $row = $(settings.aoData[dataIndex].nTr);
                const oferta = $('#filter-oferta').val();
                const empresa = $('#filter-empresa').val();
                const estado = $('#filter-estado').val();

                if (oferta && String($row.attr('data-oferta') || '') !== String(oferta)) {
                    return false;
                }
                if (empresa && String($row.attr('data-empresa') || '') !== String(empresa)) {
                    return false;
                }
                if (estado && String($row.attr('data-estado') || '') !== String(estado)) {
                    return false;
                }
                return true;
            });

            const tablaAdmin = $('#tabla-admin-postulaciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                order: [[6, 'desc']]
            });

            // Disparar filtrado reactivo al cambiar selects (igual que en ofertas y sin botón)
            $('#filter-oferta, #filter-empresa, #filter-estado').on('change', function() {
                tablaAdmin.draw();
            });

            if ($('#filter-oferta').val() || $('#filter-empresa').val() || $('#filter-estado').val()) {
                tablaAdmin.draw();
            }

            $(document).on('click', '.btn-cambiar-estado', function() {
                const idPostulacion = $(this).data('id');
                const nombre = decodeURIComponent($(this).data('nombre'));
                const dni = $(this).data('dni');
                const estadoActual = $(this).data('estado');
                $('#modal-id-postulacion').val(idPostulacion);
                $('#modal-postulante-nombre').text(nombre);
                $('#modal-postulante-dni').text('DNI: ' + dni);
                $('#modal-select-estado').val(estadoActual);
                $('#modalCambiarEstado').modal('show');
            });
        });
    </script>

<!-- ============================================================== -->
<!-- 1. VISTA PARA ESTUDIANTE / EGRESADO ("MIS POSTULACIONES")       -->
<!-- ============================================================== -->
<?php elseif (\Core\Auth::esEstudianteBolsa()): ?>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1 text-dark font-weight-bold">
                    <i class="fas fa-clipboard-list text-primary mr-2"></i>Mis Postulaciones Laborales
                </h3>
                <p class="text-muted small mb-0">Consulte el estado y seguimiento en tiempo real de sus postulaciones enviadas.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-outline-info btn-sm mr-1">
                    <i class="fas fa-id-card mr-1"></i> Mis Currículums
                </a>
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-success btn-sm font-weight-bold">
                    <i class="fas fa-search mr-1"></i> Explorar Ofertas Laborales
                </a>
            </div>
        </div>
    </div>

    <!-- Cajas de métricas del estudiante -->
    <div class="row mb-3">
        <div class="col-md-3 col-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #4e73df !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Total Enviadas</div>
                        <h4 class="font-weight-bold mb-0 text-dark"><?= (int)($metricas['total'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-paper-plane fa-2x text-primary" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #f6c23e !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Pendientes</div>
                        <h4 class="font-weight-bold mb-0 text-warning"><?= (int)($metricas['pendientes'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #36b9cc !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">En Evaluación</div>
                        <h4 class="font-weight-bold mb-0 text-info"><?= (int)($metricas['evaluacion'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-user-clock fa-2x text-info" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #1cc88a !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Seleccionadas</div>
                        <h4 class="font-weight-bold mb-0 text-success"><?= (int)($metricas['seleccionados'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla o Estado Vacío -->
    <?php if (empty($misPostulaciones)): ?>
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center my-3">
            <div class="my-4">
                <i class="fas fa-briefcase fa-4x text-muted mb-3" style="opacity: 0.35;"></i>
                <h4 class="font-weight-bold text-dark mb-2">Aún no has postulado a ninguna oferta</h4>
                <p class="text-muted max-w-500 mx-auto mb-4" style="max-width: 500px; margin: 0 auto;">
                    Explora las convocatorias laborales vigentes de las empresas aliadas y postula con el currículum que mejor se adapte a tu perfil.
                </p>
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-search mr-1"></i> Ver Convocatorias Disponibles
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card p-3 shadow-sm border-0 mb-4">
            <div class="table-responsive">
                <table id="tabla-mis-postulaciones" class="table table-bordered table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Nro</th>
                            <th class="text-center">Puesto / Oferta Laboral</th>
                            <th class="text-center">Empresa</th>
                            <th class="text-center">Fecha Postulación</th>
                            <th class="text-center">Currículum Utilizado</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misPostulaciones as $idx => $p): 
                            $est = (int)$p['estado'];
                            $estadoInfo = $estados[$est] ?? [
                                'nombre' => 'En Proceso', 
                                'badge'  => 'badge-secondary', 
                                'icono'  => 'fas fa-info'
                            ];
                        ?>
                            <tr>
                                <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-size-14 d-block"><?= htmlspecialchars($p['oferta_titulo']) ?></strong>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= htmlspecialchars($p['oferta_ubicacion'] ?? 'Huanta') ?>
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-primary font-size-14"><?= htmlspecialchars($p['empresa_nombre'] ?? 'Empresa Confidencial') ?></strong>
                                </td>
                                <td class="text-center small">
                                    <?= date('d/m/Y', strtotime($p['fecha'])) ?><br>
                                    <small class="text-muted"><?= date('H:i', strtotime($p['fecha'])) ?> hrs</small>
                                </td>
                                <td>
                                    <div class="small">
                                        <?php if (!empty($p['cv_descripcion'])): ?>
                                            <span class="d-block text-dark font-weight-bold mb-1">
                                                <?= htmlspecialchars(mb_substr($p['cv_descripcion'], 0, 45) . (mb_strlen($p['cv_descripcion']) > 45 ? '…' : '')) ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/bolsa/curriculum/ver/<?= $p['id_curriculum'] ?>" target="_blank" class="btn btn-outline-info btn-sm py-0 px-2" title="Ver ficha del CV">
                                                <i class="fas fa-id-card"></i> Ficha
                                            </a>
                                            <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $p['id_curriculum'] ?>" target="_blank" class="btn btn-outline-danger btn-sm py-0 px-2" title="Ver PDF del CV">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <?php if (!empty($p['cv_archivo'])): ?>
                                                <a href="<?= htmlspecialchars($p['cv_archivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" title="Abrir enlace web">
                                                    <i class="fas fa-external-link-alt"></i> Link
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $estadoInfo['badge'] ?> px-2 py-1 font-size-12">
                                        <i class="<?= $estadoInfo['icono'] ?> mr-1"></i> <?= htmlspecialchars($estadoInfo['nombre']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>/bolsa/postulaciones/postular/<?= $p['id_oferta_laboral'] ?>" class="btn btn-info btn-sm" title="Ver convocatoria">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($est === 1): ?>
                                            <button type="button" 
                                                class="btn btn-danger btn-sm btn-cancelar-postulacion" 
                                                data-id="<?= $p['id'] ?>"
                                                data-oferta="<?= htmlspecialchars($p['oferta_titulo']) ?>"
                                                title="Cancelar postulación"
                                            >
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#tabla-mis-postulaciones').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    order: [[3, 'desc']]
                });
                // Confirmación para cancelar postulación
                $(document).on('click', '.btn-cancelar-postulacion', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const oferta = $(this).data('oferta');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¿Cancelar esta postulación?',
                            text: `Se anulará tu postulación para la oferta "${oferta}". Esta acción no se puede deshacer.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, cancelar postulación',
                            cancelButtonText: 'Mantener postulación',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `<?= BASE_URL ?>/bolsa/postulaciones/cancelar/${id}`;
                            }
                        });
                    } else {
                        if (confirm(`¿Está seguro de cancelar su postulación a "${oferta}"?`)) {
                            window.location.href = `<?= BASE_URL ?>/bolsa/postulaciones/cancelar/${id}`;
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>No cuentas con permisos para visualizar este módulo.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>