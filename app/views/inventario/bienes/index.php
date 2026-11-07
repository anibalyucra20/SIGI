<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>
    
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Bienes</h1>
    
    <!-- Botones de Acción -->
    <div class="mb-3 d-flex">
        <a href="<?= BASE_URL ?>/inventario/bienes/nuevo" class="btn btn-success mr-2">Nuevo +</a>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCargaMasiva">
            <i class="fas fa-upload mr-1"></i> Carga Masiva
        </button>
    </div>

    <!-- Modal para Carga Masiva -->
    <div class="modal fade" id="modalCargaMasiva" tabindex="-1" aria-labelledby="modalCargaMasivaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= BASE_URL ?>/inventario/bienes/cargaMasiva" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalCargaMasivaLabel"><i class="fas fa-file-excel me-2"></i>Carga Masiva de Bienes</h5>
                        
                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <p class="text-muted mb-4">Descargue la plantilla, llénela con los datos de los bienes y súbala aquí.</p>
                        
                        <!-- Botón para descargar plantilla -->
                        <!-- aqui se abre la descarga en una "pestaña oculta" para que no interfiera  -->
                        <a href="<?= BASE_URL ?>/inventario/bienes/descargarPlantilla" class="btn btn-outline-success mb-4" target="_blank">                            <i class="fas fa-download me-1"></i> Descargar Plantilla Excel
                        </a>
                        
                        <!-- Input de archivo -->
                        <div class="mb-3 text-start">
                            <label for="archivo_excel" class="form-label fw-bold">Seleccionar archivo Excel (.xlsx)</label>
                            <input class="form-control" type="file" id="archivo_excel" name="archivo_excel" accept=".xlsx, .xls" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        
                        <!-- CORRECCIÓN: data-dismiss (sin el -bs-) -->
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Cargar Plantilla</button>
                    </div>
                </form>
            </div>
        </div>
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
                <div class="col-md-2">
                    <label class="form-label">Estado del Bien</label>
                    <select id="filter-estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="BUENO">BUENO</option>
                        <option value="REGULAR">REGULAR</option>
                        <option value="MALO">MALO</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Disponibilidad</label>
                    <select id="filter-disponibilidad" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">ACTIVO</option>
                        <option value="0">INACTIVO</option>
                    </select>
                 </div>
                 <div class="col-md-4 mt-3">
                    <label class="form-label">Ambiente</label>
                    <select id="filter-ambiente" class="form-control">
                        <option value="">Todos</option>
                        <?php if (!empty($ambientes)): ?>
                            <?php foreach ($ambientes as $amb): ?>
                                <option value="<?= $amb['id_inventario'] ?>">
                                    <?= htmlspecialchars($amb['tipo_ambiente'] ?? '') ?>                                
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Lista de Bienes Registrados
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
                        <!-- DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#filter-codigo, #filter-denominacion').on('keyup', function() { tabla.ajax.reload(); });
        $('#filter-estado, #filter-disponibilidad, #filter-ambiente').on('change', function() { tabla.ajax.reload(); });

        const tabla = $('#tabla-bienes').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '<?= BASE_URL ?>/inventario/bienes/data',
                type: 'GET',
                data: function(d) {
                    d.codigo_patrimonial = $('#filter-codigo').val();
                    d.denominacion       = $('#filter-denominacion').val();
                    d.id_inv_ambiente    = $('#filter-ambiente').val();
                    d.estado_bien        = $('#filter-estado').val();
                    d.activo             = $('#filter-disponibilidad').val();
                }
            },
            columns: [
                { data: null, render: function(data, type, row, meta) { return meta.row + 1 + meta.settings._iDisplayStart; } },
                { data: 'id_inv_ambiente' },
                { data: 'codigo_patrimonial' },
                { data: 'denominacion' },
                { data: 'marca' },
                { data: 'modelo' },
                { data: 'color' },
                { data: 'serie' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap',
                    width: '160px',
                    render: function(data, type, row) {
                        let botones = `<a href="<?= BASE_URL ?>/inventario/bienes/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a> `;
                        if (row.activo == 1 || row.activo == null) {
                            botones += `<a href="<?= BASE_URL ?>/inventario/bienes/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea dar de baja este bien?');">Dar de baja</a>`;
                        } else {
                            botones += `<a href="<?= BASE_URL ?>/inventario/bienes/activar/${row.id}" class="btn btn-success btn-sm" onclick="return confirm('¿Desea volver a activar este bien en el sistema?');">Activar</a>`;
                        }
                        return botones;
                    }
                }
            ],
            language: { /* url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' */ }
        });
    });
</script>

<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>