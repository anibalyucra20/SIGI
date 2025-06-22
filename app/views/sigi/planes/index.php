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
    <div class="card p-2">
        <h3 class="mb-4">Planes de Estudio</h3>
        <div class="mb-3 text-end">
            <a href="<?= BASE_URL ?>/sigi/planes/nuevo" class="btn btn-success">Nuevo Plan</a>
        </div>

        <div class="table-responsive">
            <table id="tabla-planes" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Programa de Estudio</th>
                        <th>Nombre</th>
                        <th>Resolución</th>
                        <th>Fecha de Registro</th>
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
            $('#tabla-planes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/planes/data',
                    type: 'GET'
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'programa_nombre'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'resolucion'
                    },
                    {
                        data: 'fecha_registro'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/sigi/planes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
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