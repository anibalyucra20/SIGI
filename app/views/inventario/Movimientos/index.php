<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>
    
<div class="container-fluid px-4">
    <h1 class="mt-4">Registro de Movimientos</h1>
    
    <!-- Botón Nuevo -->
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/inventario/movimientos/nuevo" class="btn btn-success">Nuevo +</a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Filtros:</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select id="filter-tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="ENTRADA">ENTRADA</option>
                        <option value="SALIDA">SALIDA</option>
                        <option value="TRASLADO">TRASLADO</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Motivo</label>
                    <input type="text" id="filter-motivo" class="form-control" placeholder="Buscar por motivo...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Documento de Traslado</label>
                    <input type="text" id="filter-documento" class="form-control" placeholder="Buscar documento...">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Lista de Movimientos Registrados
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-movimientos" class="table table-bordered table-striped table-sm w-100 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>nro</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Documento</th>
                            <th>Fecha y Hora</th>
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
        
        // Disparadores para recargar la tabla cuando se usen los filtros
        $('#filter-tipo').on('change', function() {
            tabla.ajax.reload();
        });
        $('#filter-motivo').on('keyup', function() {
            tabla.ajax.reload();
        });
        $('#filter-documento').on('keyup', function() {
            tabla.ajax.reload();
        });

        const tabla = $('#tabla-movimientos').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '<?= BASE_URL ?>/inventario/movimientos/data',
                type: 'GET',
                data: function(d) {
                    // Enviamos los valores de los filtros al controlador
                    d.tipo = $('#filter-tipo').val();
                    d.motivo = $('#filter-motivo').val();
                    d.documento_traslado = $('#filter-documento').val();
                }
            },
            columns: [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                },
                { data: 'id_inv_ambiente_origen' },
                { data: 'id_inv_ambiente_destino' },
                { data: 'tipo' },
                { data: 'motivo' },
                { data: 'documento_traslado' },
                { data: 'fecha_hora' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <a href="<?= BASE_URL ?>/inventario/movimientos/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= BASE_URL ?>/inventario/movimientos/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar este movimiento?');">Eliminar</a>
                        `;
                    }
                }
            ],
            language: {
                // url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            }
        });
    });
</script>

<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>