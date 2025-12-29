<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Ambientes</h3>
        <div class="col-md-2 mb-2">
            <a href="<?= BASE_URL ?>/sigi/ambientes/nuevo" class="btn btn-success mt-2">Nuevo Ambiente</a>
        </div>
        <div class="row mb-3">
            <div class="col-md-2">
                <label>Nro de Ambiente</label>
                <input type="text" id="filter-nro" class="form-control" placeholder="Buscar nro">
            </div>
        </div>

        <div class="table-responsive ">
            <table id="tabla-ambientes" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Nro de Ambiente</th>
                        <th>Aforo</th>
                        <th>Piso</th>
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
            $('#filter-nro').on('keyup', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-ambientes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/ambientes/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_nro = $('#filter-nro').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'tipo_ambiente'
                    },
                    {
                        data: 'nro'
                    },
                    {
                        data: 'aforo'
                    },
                    {
                        data: 'piso'
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
                        <a href="<?= BASE_URL ?>/sigi/ambientes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
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
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>