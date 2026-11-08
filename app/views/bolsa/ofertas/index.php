<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminBolsa() || \Core\Auth::esEstudianteBolsa()): ?>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-dark font-weight-bold">Ofertas Laborales</h3>
            <div>
                <?php if (\Core\Auth::esAdminBolsa()): ?>
                    <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary shadow-sm mr-1">
                        <i class="fas fa-list mr-1"></i> Todas las Postulaciones
                    </a>
                    <a href="<?= BASE_URL ?>/bolsa/ofertas/nuevo" class="btn btn-success shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Nueva Oferta
                    </a>
                <?php elseif (\Core\Auth::esEstudianteBolsa()): ?>
                    <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary shadow-sm mr-1">
                        <i class="fas fa-clipboard-list mr-1"></i> Mis Postulaciones
                    </a>
                    <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-info shadow-sm">
                        <i class="fas fa-id-card mr-1"></i> Mis Currículums
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card p-3 shadow-sm rounded-3 mb-3">
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold">Programa de Estudio</label>
                <select id="filter-programa" class="form-control">
                    <option value="">Todos los programas</option>
                    <?php foreach ($programas as$p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold">Empresa</label>
                <select id="filter-empresa" class="form-control">
                    <option value="">Todas las empresas</option>
                    <?php foreach ($empresas as$e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['empresa']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-ofertas" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">Empresa</th>
                        <th class="text-center">Programa de Estudio</th>
                        <th class="text-center">Título</th>
                        <th class="text-center">F. Publicación</th>
                        <th class="text-center">F. Cierre</th>
                        <th class="text-center">Salario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DE DETALLE DE LA OFERTA -->
    <div class="modal fade" id="modalDetalleOferta" tabindex="-1" role="dialog" aria-labelledby="modalDetalleOfertaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalDetalleOfertaLabel">
                        <i class="fas fa-info-circle text-primary mr-1"></i> Detalle de la Convocatoria
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">

                    <!-- Encabezado de la Oferta -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center flex-wrap mb-2">
                                <span id="m-estado" class="badge px-3 py-2 font-weight-normal mr-2 mb-1"></span>
                                <span class="badge badge-light border text-muted px-3 py-2 font-weight-normal mr-2 mb-1">
                                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Publicado: <span id="m-fecha-publicacion"></span>
                                </span>
                                <span class="badge badge-light border text-danger px-3 py-2 font-weight-normal mb-1">
                                    <i class="fas fa-clock text-danger mr-1"></i> Cierre: <span id="m-fecha-cierre"></span>
                                </span>
                            </div>
                            <h4 id="m-titulo" class="font-weight-bold text-dark mb-1"></h4>
                            <p class="text-secondary mb-0">
                                <i class="fas fa-building text-primary mr-1"></i> <strong id="m-empresa"></strong>
                            </p>
                        </div>
                    </div>

                    <!-- Fila 1: 4 Cajas de métricas clave -->
                    <div class="row mb-3">
                        <div class="col-xl-3 col-md-6 mb-2">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #007bff !important;">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 mr-3 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 11px;">Programa / Carrera</small>
                                        <span id="m-programa" class="font-weight-bold text-dark d-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-2">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 mr-3 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 11px;">Remuneración</small>
                                        <span id="m-salario" class="font-weight-bold text-dark d-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-2">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #17a2b8 !important;">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 mr-3 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 11px;">Tipo de Contrato</small>
                                        <span id="m-tipo-contrato" class="font-weight-bold text-dark d-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-2">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 mr-3 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 11px;">Ubicación</small>
                                        <span id="m-ubicacion" class="font-weight-bold text-dark d-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Dos columnas simétricas -->
                    <div class="row">
                        <!-- Columna Izquierda: Funciones y Requisitos del mismo tamaño -->
                        <div class="col-lg-6 mb-3 d-flex flex-column">
                            <!-- Funciones del puesto (flex-fill d-flex flex-column para tomar el 50%) -->
                            <div class="card border-0 shadow-sm mb-3 flex-fill d-flex flex-column">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="card-title mb-0 font-weight-bold text-dark">
                                        <i class="fas fa-briefcase text-primary mr-2"></i> Funciones y Detalle del Puesto
                                    </h6>
                                </div>
                                <div class="card-body flex-fill" style="overflow-y: auto;">
                                    <p id="m-detalle" class="text-dark mb-0 text-justify" style="white-space: pre-line; line-height: 1.6;"></p>
                                </div>
                            </div>

                            <!-- Requisitos del Postulante (flex-fill d-flex flex-column para tomar el otro 50%) -->
                            <div class="card border-0 shadow-sm flex-fill d-flex flex-column">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="card-title mb-0 font-weight-bold text-dark">
                                        <i class="fas fa-clipboard-check text-primary mr-2"></i> Requisitos del Postulante
                                    </h6>
                                </div>
                                <div class="card-body flex-fill" style="overflow-y: auto;">
                                    <p id="m-requisitos" class="text-dark mb-0 text-justify" style="white-space: pre-line; line-height: 1.6;"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Ficha de la Empresa y Afiche -->
                        <div class="col-lg-6 mb-3 d-flex flex-column">
                            <!-- Ficha de la Empresa -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="card-title mb-0 font-weight-bold text-dark">
                                        <i class="fas fa-id-card text-primary mr-2"></i> Ficha de la Empresa
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <td class="px-3 py-2 text-muted" style="width: 35%;"><i class="fas fa-building mr-1"></i> Razón Social</td>
                                                <td class="px-3 py-2 font-weight-bold text-dark" id="m-empresa-nombre">-</td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <td class="px-3 py-2 text-muted"><i class="fas fa-fingerprint mr-1"></i> RUC</td>
                                                <td class="px-3 py-2 font-weight-bold text-dark" id="m-empresa-ruc">-</td>
                                            </tr>
                                            <tr>
                                                <td class="px-3 py-2 text-muted"><i class="fas fa-map-marked-alt mr-1"></i> Dirección</td>
                                                <td class="px-3 py-2 text-dark" id="m-empresa-direccion">-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Imagen / Flyer de la Oferta -->
                            <div class="card border-0 shadow-sm flex-fill d-flex flex-column">
                                <div class="card-header bg-white border-bottom py-2">
                                    <h6 class="card-title mb-0 font-weight-bold text-dark">
                                        <i class="fas fa-image text-primary mr-2"></i> Imagen de la Convocatoria
                                    </h6>
                                </div>
                                <div class="card-body p-3 text-center bg-white flex-fill d-flex flex-column justify-content-center align-items-center" id="m-foto-contenedor">
                                    <!-- Inyección de la foto por JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                    <div id="m-acciones-footer">
                        <!-- Inyección de botones según rol -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = '<?= BASE_URL ?>';
            const esAdmin = <?= \Core\Auth::esAdminBolsa() ? 'true' : 'false' ?>;
            const esEstudiante = <?= \Core\Auth::esEstudianteBolsa() ? 'true' : 'false' ?>;

            $('#filter-programa, #filter-empresa').on('change', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-ofertas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: baseUrl + '/bolsa/ofertas/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_empresa = $('#filter-empresa').val();
                        d.filter_programa = $('#filter-programa').val();
                    }
                },
                columns: [
                    { data: null, className: 'text-center', render: function(data, type, row, meta) { return meta.row + 1; } },
                    { data: 'empresa_nombre' },
                    { data: 'programa_nombre' },
                    { data: 'titulo' },
                    { data: 'fecha_publicacion' },
                    { data: 'fecha_cierre' },
                    { 
                        data: 'salario',
                        render: function(data) {
                            return data && !isNaN(data) ? 'S/ ' + parseFloat(data).toFixed(2) : (data || 'A Tratar');
                        }
                    },
                    { 
                        data: null, 
                        orderable: false, 
                        searchable: false, 
                        render: function(data, type, row) {
                            let botones = `<button type="button" class="btn btn-sm btn-info btn-ver-detalle" title="Ver detalle en modal"><i class="fas fa-eye"></i></button> `;
                            
                            if (esAdmin) {
                                botones += `
                                    <a href="${baseUrl}/bolsa/postulaciones/ver/${row.id}" class="btn btn-sm btn-secondary" title="Ver postulaciones"><i class="fas fa-users"></i></a>
                                    <a href="${baseUrl}/bolsa/ofertas/editar/${row.id}" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="${baseUrl}/bolsa/ofertas/eliminar/${row.id}" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta oferta?');"><i class="fas fa-trash"></i></a>
                                `;
                            } else if (esEstudiante && row.estado == 1) {
                                botones += `<a href="${baseUrl}/bolsa/postulaciones/postular/${row.id}" class="btn btn-sm btn-success" title="Postular"><i class="fas fa-paper-plane"></i> Postular</a>`;
                            }
                            return botones;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });

            // Abrir modal usando los datos de la fila de DataTables
            $('#tabla-ofertas tbody').on('click', '.btn-ver-detalle', function() {
                const tr = $(this).closest('tr');
                const oferta = tabla.row(tr).data();

                if (!oferta) {
                    return;
                }

                // 1. Encabezado principal
                $('#m-titulo').text(oferta.titulo || '');
                $('#m-empresa').text(oferta.empresa_nombre || 'Empresa Confidencial');
                $('#m-fecha-publicacion').text(oferta.fecha_publicacion || '');
                $('#m-fecha-cierre').text(oferta.fecha_cierre || '');

                // 2. Métricas clave
                $('#m-programa').text(oferta.programa_nombre || 'Todas las carreras');
                $('#m-salario').text(oferta.salario && !isNaN(oferta.salario) ? 'S/ ' + parseFloat(oferta.salario).toFixed(2) : (oferta.salario || 'A Tratar'));
                $('#m-tipo-contrato').text(oferta.tipo_contrato || 'No especificado');
                $('#m-ubicacion').text(oferta.ubicacion || 'No especificada');

                // 3. Funciones y requisitos
                $('#m-detalle').text(oferta.detalle || 'Sin detalle disponible');
                $('#m-requisitos').text(oferta.requisitos || 'Sin requisitos definidos');

                // 4. Ficha de la empresa
                $('#m-empresa-nombre').text(oferta.empresa_nombre || 'No especificada');
                $('#m-empresa-ruc').text(oferta.empresa_ruc || 'No registrado');
                $('#m-empresa-direccion').text(oferta.empresa_direccion || 'No registrada');

                // 5. Estado de la convocatoria
                const $badgeEstado =$('#m-estado');
                if (oferta.estado == 1) {
                    $badgeEstado.removeClass('badge-secondary').addClass('badge-success').html('<i class="fas fa-check-circle mr-1"></i> Convocatoria Vigente');
                } else {
                    $badgeEstado.removeClass('badge-success').addClass('badge-secondary').html('<i class="fas fa-times-circle mr-1"></i> Convocatoria Finalizada');
                }

                // 6. Contenedor de Flyer / Afiche
                const $fotoCont =$('#m-foto-contenedor');
                if (oferta.foto && oferta.foto.trim() !== '') {
                    $fotoCont.html(`
                        <a href="${baseUrl}/${oferta.foto}" target="_blank" class="d-block w-100">
                            <img src="${baseUrl}/${oferta.foto}" alt="Afiche de Oferta" class="img-fluid rounded border shadow-sm" style="max-height: 280px; width: auto; object-fit: contain;">
                        </a>
                        <small class="text-muted mt-2 d-block"><i class="fas fa-search-plus mr-1"></i> Clic para ampliar en ventana nueva</small>
                    `);
                } else {
                    $fotoCont.html(`
                        <div class="py-4 text-muted">
                            <i class="fas fa-image fa-3x mb-2 text-black-50"></i>
                            <p class="mb-0">Esta convocatoria no incluye imagen adjunta.</p>
                        </div>
                    `);
                }

                // 7. Acciones dinámicas en el pie del modal
                const $footerAcciones =$('#m-acciones-footer');
                $footerAcciones.empty();
                if (esEstudiante && oferta.estado == 1) {
                    $footerAcciones.html(`
                        <a href="${baseUrl}/bolsa/postulaciones/postular/${oferta.id}" class="btn btn-success shadow-sm">
                            <i class="fas fa-paper-plane mr-1"></i> Postular a esta Oferta
                        </a>
                    `);
                } else if (esAdmin) {
                    $footerAcciones.html(`
                        <a href="${baseUrl}/bolsa/ofertas/editar/${oferta.id}" class="btn btn-warning shadow-sm">
                            <i class="fas fa-edit mr-1"></i> Editar Oferta
                        </a>
                    `);
                }

                $('#modalDetalleOferta').modal('show');
            });
        });
    </script>

<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i> No cuentas con permisos para visualizar este módulo.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>