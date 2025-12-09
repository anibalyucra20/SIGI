<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminCaja()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Medios de Pago</h3>
        <div class="col-md-2 mb-2">
            <a href="<?= BASE_URL ?>/caja/mediosPago/nuevo" class="btn btn-success mt-2">Nuevo +</a>
        </div>
        <h5 class="mb-2">Filtros:</h5>
        <div class="row mb-3">
            <div class="col-md-3 text-center">
                <label>Descripción</label>
                <input type="text" id="filter-descripcion" class="form-control">
            </div>

        </div>
        <div class="table-responsive col-12">
            <table id="tabla-mediosPago" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables AJAX llenará esto -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtros dependientes
            $('#filter-descripcion').on('keyup', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-mediosPago').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/caja/mediosPago/data',
                    type: 'GET',
                    data: function(d) {
                        d.descripcion = $('#filter-descripcion').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'codigo'
                    },
                    {
                        data: 'descripcion'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.estado == 1) {
                                return `ACTIVO`;
                            } else {
                                return `INACTIVO`;
                            }

                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/caja/mediosPago/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                    `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>Usted no cuenta con el permiso necesario para acceder al modulo de Caja</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>