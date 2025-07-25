<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Competencias</h3>
        <div class="col-md-3 mb-2">
            <a href="<?= BASE_URL ?>/sigi/competencias/nuevo" class="btn btn-success mt-2">Nueva Competencia</a>
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
            <div class="col-md-3">
                <label>Plan de Estudio</label>
                <select id="filter-plan" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabla-competencias" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Programa</th>
                        <th>Plan</th>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Descripción</th>
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
            // Filtros dependientes
            $('#filter-programa').on('change', function() {
                let idPrograma = $(this).val();
                $('#filter-plan').html('<option value="">Todos</option>');
                if (idPrograma) {
                    $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                        planes.forEach(function(pl) {
                            $('#filter-plan').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                        });
                    });
                }
                tabla.ajax.reload();
            });
            $('#filter-plan').on('change', function() {
                tabla.ajax.reload();
            });
            const tabla = $('#tabla-competencias').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/competencias/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                    }
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
                        data: 'plan_nombre'
                    },
                    {
                        data: 'tipo'
                    },
                    {
                        data: 'codigo'
                    },
                    {
                        data: 'descripcion'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/sigi/competencias/editar/${row.id}" class="btn btn-warning btn-sm m-1">Editar</a>
                        <a href="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/index/${row.id}" class="btn btn-primary btn-sm m-1"><i class="fa fa-folder"></i> Indicadores</a>
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