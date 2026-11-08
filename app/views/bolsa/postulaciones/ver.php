<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminBolsa()): ?>
    <!-- Encabezado de la Oferta -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1 text-dark font-weight-bold">Postulantes de la Convocatoria</h3>
                <p class="text-muted small mb-0">
                    Oferta: <strong class="text-dark"><?= htmlspecialchars($oferta['titulo']) ?></strong> — 
                    Empresa: <strong class="text-dark"><?= htmlspecialchars($oferta['empresa_nombre'] ?? 'Empresa Confidencial') ?></strong>
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>/bolsa/reportes/pdfOferta/<?= (int)$idOferta ?>" target="_blank" class="btn btn-danger shadow-sm mr-1">
                    <i class="fas fa-file-pdf mr-1"></i> Expediente PDF
                </a>
                <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary shadow-sm mr-1">
                    <i class="fas fa-list mr-1"></i> Todas las Postulaciones
                </a>
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Ofertas
                </a>
            </div>
        </div>
    </div>

    <!-- Filtro rápido de estados -->
    <div class="card p-3 shadow-sm rounded-3 mb-3 border-0">
        <div class="row align-items-center">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold">Filtrar por Estado de Postulación:</label>
                <select id="filter-estado" class="form-control">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $est): ?>
                        <option value="<?= $est['id'] ?>"><?= htmlspecialchars($est['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-9 text-md-right">
                <span class="badge badge-light border text-muted px-3 py-2 mr-2">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Cierre: <?= date('d/m/Y', strtotime($oferta['fecha_cierre'])) ?>
                </span>
                <span class="badge badge-light border text-dark px-3 py-2">
                    <i class="fas fa-graduation-cap text-info mr-1"></i> <?= htmlspecialchars($oferta['programa_nombre'] ?? 'Todas las carreras') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Tabla DataTables de Postulantes -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-postulantes" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">DNI</th>
                        <th class="text-center">Postulante</th>
                        <th class="text-center">Programa de Estudio</th>
                        <th class="text-center">Contacto</th>
                        <th class="text-center">CV Utilizado</th>
                        <th class="text-center">F. Postulación</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Llenado dinámico vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PARA ACTUALIZAR ESTADO DE LA POSTULACIÓN -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" role="dialog" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <form action="<?= BASE_URL ?>/bolsa/postulaciones/cambiarEstado" method="post" id="form-cambiar-estado">
                    <input type="hidden" name="id_oferta" value="<?= (int)$idOferta ?>">
                    <input type="hidden" name="id_postulacion" id="modal-id-postulacion" value="">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold text-dark" id="modalCambiarEstadoLabel">
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
                            <label class="font-weight-bold text-dark">Seleccione el nuevo estado:</label>
                            <select name="estado" id="modal-select-estado" class="form-control" required>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?= $est['id'] ?>"><?= htmlspecialchars($est['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer bg-light d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script de Inicialización de DataTables y Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = '<?= BASE_URL ?>';
            const idOferta = '<?= (int)$idOferta ?>';
            const tabla = $('#tabla-postulantes').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[6, 'desc']], // Ordenar por fecha descendente
                ajax: {
                    url: baseUrl + '/bolsa/postulaciones/data/' + idOferta,
                    type: 'GET',
                    data: function(d) {
                        d.filter_estado = $('#filter-estado').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {data: 'dni',},
                    {data: 'postulante_nombre',},
                    {data: 'programa_nombre',},
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="small">
                                    <div><i class="fas fa-phone text-muted mr-1"></i> ${row.telefono || '-'}</div>
                                    <div><i class="fas fa-envelope text-muted mr-1"></i> ${row.correo || '-'}</div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            let botonesCv = ``;
                            if (row.id_curriculum) {
                                botonesCv += `
                                        <a href="${baseUrl}/bolsa/curriculum/ver/${row.id_curriculum}" target="_blank" class="btn btn-sm btn-info" title="Ver ficha digital del CV"><i class="fas fa-id-card mr-1"></i></a>
                                        <a href="${baseUrl}/bolsa/curriculum/pdf/${row.id_curriculum}" target="_blank" class="btn btn-sm btn-danger" title="Ver CV en PDF"><i class="fas fa-file-pdf mr-1"></i></a>
                                `;
                                if (row.archivo_cv && row.archivo_cv.trim() !== '') {
                                    botonesCv += `
                                        <a href="${row.archivo_cv}" target="_blank" class="btn btn-sm btn-primary" title="Abrir enlace web del CV">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    `;
                                }
                            } else {
                                botonesCv = `<span class="text-muted small font-italic">No disponible</span>`;
                            }
                            return botonesCv;
                        }
                    },
                    {
                        data: 'fecha',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            const f = new Date(data.replace(' ', 'T'));
                            if (isNaN(f.getTime())) return data;
                            const d = String(f.getDate()).padStart(2, '0');
                            const m = String(f.getMonth() + 1).padStart(2, '0');
                            const y = f.getFullYear();
                            const hr = String(f.getHours()).padStart(2, '0');
                            const min = String(f.getMinutes()).padStart(2, '0');
                            return `${d}/${m}/${y} <br><small class="text-muted">${hr}:${min}</small>`;
                        }
                    },
                    {
                        data: 'estado',
                        className: 'text-center',
                        render: function(data) {
                            const est = parseInt(data);
                            switch(est) {
                                case 1:
                                    return `<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Pendiente</span>`;
                                case 2:
                                    return `<span class="badge badge-info px-2 py-1"><i class="fas fa-user-check mr-1"></i> En Evaluación</span>`;
                                case 3:
                                    return `<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Seleccionado</span>`;
                                case 4:
                                    return `<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> No Seleccionado</span>`;
                                default:
                                    return `<span class="badge badge-secondary px-2 py-1">Desconocido</span>`;
                            }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <button type="button" 
                                    class="btn btn-sm btn-primary btn-cambiar-estado" 
                                    data-id="${row.id}"
                                    data-nombre="${encodeURIComponent(row.postulante_nombre || row.apellidos_nombres)}"
                                    data-dni="${row.dni}"
                                    data-estado="${row.estado}"
                                    title="Actualizar estado de postulación"
                                >
                                    <i class="fas fa-edit mr-1"></i> Estado
                                </button>
                            `;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Filtro por estado
            $('#filter-estado').on('change', function() {
                tabla.ajax.reload();
            });
            // Abrir modal de cambio de estado
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
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> Esta sección solo se encuentra disponible para usuarios con rol de Administrador.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
