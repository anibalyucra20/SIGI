<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <h3 class="mb-4">Sedes</h3>
    <div class="mb-3 text-end">
        <a href="<?= BASE_URL ?>/sigi/sedes/nuevo" class="btn btn-primary">Nueva Sede</a>
    </div>

    <div class="card p-2">
        <table id="tabla-sedes" class="table table-striped table-bordered dt-responsive nowrap col-12">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Código Modular</th>
                    <th>Nombre</th>
                    <th>Distrito</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Responsable</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables AJAX llenará esto -->
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#tabla-sedes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/sedes/data',
                    type: 'GET'
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'cod_modular'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'distrito'
                    },
                    {
                        data: 'direccion'
                    },
                    {
                        data: 'telefono'
                    },
                    {
                        data: 'correo'
                    },
                    {
                        data: 'responsable_nombre',
                        title: 'Responsable'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/sigi/sedes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= BASE_URL ?>/sigi/sedes/programas/${row.id}" class="btn btn-info btn-sm">Programas de Estudio</a>
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