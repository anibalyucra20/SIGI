<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminTutoria()): ?>

    <div class="card p-2">
        <h3 class="mb-2">Programación Tutoria</h3>
        <div class="mb-2">
            <a href="<?= BASE_URL ?>/tutoria/programacion/nuevo" class="btn btn-success">+ Nuevo</a>
        </div>
        <div class="table-responsive">
            <table id="tabla-programacion" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nro</th>
                        <th>Sede</th>
                        <th>Periodo Academico</th>
                        <th>Docente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable AJAX -->
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtros dependientes
            /*$('#filter-descripcion').on('keyup', function() {
                tabla.ajax.reload();
            });
            $('#filter-codigo').on('keyup', function() {
                tabla.ajax.reload();
            });*/

            const tabla = $('#tabla-programacion').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/tutoria/programacion/data',
                    type: 'GET',
                    data: function(d) {
                        /*d.descripcion = $('#filter-descripcion').val();
                        d.codigo = $('#filter-codigo').val();*/
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'sede_nombre'
                    },
                    {
                        data: 'periodo_academico_nombre'
                    },
                    {
                        data: 'docente_nombre'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/tutoria/programacion/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= BASE_URL ?>/tutoria/programacion/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar este Bien?');">Eliminar</a>
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
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>