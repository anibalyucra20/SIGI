<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="card p-2">
        <h3 class="mb-2">Ofertas Laborales</h3>
        <div class="mb-2">
            <a href="<?= BASE_URL ?>/bolsa/ofertas/nuevo" class="btn btn-success">+ Nueva Oferta</a>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Programa de Estudio</label>
                <select id="filter-programa" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($programas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Empresa</label>
                <select id="filter-empresa" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($empresas as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['empresa']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
            $('#filter-programa, #filter-empresa').on('change', function() {
            tabla.ajax.reload();
            });
            const tabla = $('#tabla-ofertas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/bolsa/ofertas/data',
                    type: 'GET',
                    data: function(d) {
                        // Puedes agregar parámetros adicionales aquí si es necesario
                        d.filter_empresa = $('#filter-empresa').val();
                        d.filter_programa = $('#filter-programa').val();
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
                    <?php
                        if(\Core\Auth::esAdminBolsa()):{

                        }
                    ?>
                        return `
                            <a href="" class="btn btn-sm btn-info" title="ver postulaciones"><i class="fas fa-eye"></i></a>
                            <a href="<?= BASE_URL ?>/bolsa/ofertas/editar/${row.id}" class="btn btn-sm btn-primary">Editar</a>
                            <a href="<?= BASE_URL ?>/bolsa/ofertas/eliminar/${row.id}" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar esta oferta?');">Eliminar</a>
                        `;
                    <?php
                        elseif(\Core\Auth::esEstudianteBolsa()):{

                        }
                        ?>
                        return `
                            <a href="<?= BASE_URL ?>/bolsa/ofertas/postular/${row.id}" class="btn btn-sm btn-primary">Postular</a>
                        `;
                    <?php

                        endif;
                    ?>

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