<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="card p-2">
        <h3 class="mb-2">Ofertas Laborales</h3>
        <div class="mb-2">
            <a href="<?= BASE_URL ?>/bolsa/ofertas/nuevo" class="btn btn-success">+ Nueva Oferta</a>
        </div>
        <div class="table-responsive">
            <table id="tabla-ofertas" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nro</th>
                        <th>Empresa</th>
                        <th>Programa de Estudio</th>
                        <th>Título</th>
                        <th>Detalle</th>
                        <th>F. Publicación</th>
                        <th>F. Cierre</th>
                        <th>Salario</th>
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
            const tabla = $('#tabla-ofertas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/bolsa/ofertas/data',
                    type: 'GET',
                    data: function(d) {
                        // Puedes agregar parámetros adicionales aquí si es necesario
                    }
                },
                columns: [
                    { data: null, render: function(data, type, row, meta) { return meta.row + 1} },
                    { data: 'empresa_nombre' },
                    { data: 'programa_nombre' },
                    { data: 'titulo' },
                    { data: 'detalle' },
                    { data: 'fecha_publicacion' },
                    { data: 'fecha_cierre' },
                    { data: 'salario' },
                    { data: null, orderable: false, searchable: false, render: function(data, type, row) {
                        return `
                            <a href="<?= BASE_URL ?>/bolsa/ofertas/editar/${row.id}" class="btn btn-sm btn-primary">Editar</a>
                            <button class="btn btn-sm btn-danger" onclick="eliminarOferta(${row.id})">Eliminar</button>
                        `;
                    }}
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>

<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>