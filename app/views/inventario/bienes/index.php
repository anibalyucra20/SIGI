<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>
    
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Bienes</h1>
    
    <!-- Botón Nuevo -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/inventario/bienes/nuevo" class="btn btn-success">Nuevo +</a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Filtros:</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Código Patrimonial</label>
                    <input type="text" id="filter-codigo" class="form-control" placeholder="Buscar código...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Denominación</label>
                    <input type="text" id="filter-denominacion" class="form-control" placeholder="Buscar denominación...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado del Bien</label>
                    <select id="filter-estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="BUENO">BUENO</option>
                        <option value="REGULAR">REGULAR</option>
                        <option value="MALO">MALO</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Lista de Bienes Registrados
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-bienes" class="table table-bordered table-striped table-sm w-100 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>nro</th>
                            <th>Ambiente</th>
                            <th>Código Patrimonial</th>
                            <th>Denominación</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Serie</th>
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
            // Filtros dependientes
            /*$('#filter-codigo').on('keyup', function() {
                tabla.ajax.reload();
            });
            $('#filter-denominacion').on('keyup', function() {
                tabla.ajax.reload();
            });

            $('#filter-estado').on('change', function() {
            tabla.ajax.reload();
            });*/

            const tabla = $('#tabla-bienes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/inventario/bienes/data',
                    type: 'GET',
                    data: function(d) {
                         /* d.codigo_patrimonial = $('#filter-codigo').val();
                          d.denominacion       = $('#filter-denominacion').val();
                          d.estado_bien        = $('#filter-estado').val();*/
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'id_inv_ambiente'
                    },
                    {
                        data: 'codigo_patrimonial'
                    },
                    {
                        data: 'denominacion'
                    },
                    {
                        data: 'marca'
                    },
                    {
                        data: 'modelo'
                    },
                    {
                        data: 'color'
                    },{
                        data: 'serie'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/inventario/bienes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= BASE_URL ?>/inventario/bienes/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar esta programación?');">Eliminar</a>
                    `;
                        }
                    }
                ],
                language: {
                  //  url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>