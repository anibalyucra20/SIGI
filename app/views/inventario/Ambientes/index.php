<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>
    
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Ambientes</h1>
    
    <!-- Botón Nuevo -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/inventario/ambientes/nuevo" class="btn btn-success">Nuevo +</a>
    </div>

    <!-- Tabla de Registros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Lista de Ambientes Registrados
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-ambientes" class="table table-bordered table-striped table-sm w-100 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>nro</th>
                            <th>Ambiente</th>
                            <th>Docente Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables AJAX llenará esto automáticamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = $('#tabla-ambientes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/inventario/ambientes/data',
                    type: 'GET',
                    data: function(d) {
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'id_sigi_ambiente'
                    },
                    {
                        data: 'id_docente'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/inventario/ambientes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= BASE_URL ?>/inventario/ambientes/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar esta programación?');">Eliminar</a>
                    `;
                        }
                    }
                ],
                language: {
                  //url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>