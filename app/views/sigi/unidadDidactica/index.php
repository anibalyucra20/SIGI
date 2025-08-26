<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Unidades Didácticas</h3>
        <div class="col-md-2 mb-2">
            <a href="<?= BASE_URL ?>/sigi/unidadDidactica/nuevo" class="btn btn-success mt-2">Nueva Unidad</a>
        </div>
        <div class="row mb-3">
            <div class="col-md-2">
                <label>Programa de Estudio</label>
                <select id="filter-programa" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($programas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Plan de Estudio</label>
                <select id="filter-plan" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Módulo Formativo</label>
                <select id="filter-modulo" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Periodo Académico</label>
                <select id="filter-semestre" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Nombre</label>
                <input type="text" id="filter-nombre" class="form-control" placeholder="Buscar nombre">
            </div>

        </div>

        <div class="table-responsive">
            <table id="tabla-unidad" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Programa</th>
                        <th>Plan</th>
                        <th>Módulo</th>
                        <th>Periodo Académico</th>
                        <th>Nombre</th>
                        <th>C.T.</th>
                        <th>C.P.</th>
                        <th>Tipo</th>
                        <th>Orden</th>
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
                $('#filter-modulo').html('<option value="">Todos</option>');
                $('#filter-semestre').html('<option value="">Todos</option>');
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
                let idPlan = $(this).val();
                $('#filter-modulo').html('<option value="">Todos</option>');
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idPlan) {
                    $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function(modulos) {
                        modulos.forEach(function(m) {
                            $('#filter-modulo').append('<option value="' + m.id + '">' + m.descripcion + '</option>');
                        });
                    });
                }
                tabla.ajax.reload();
            });
            $('#filter-modulo').on('change', function() {
                let idModulo = $(this).val();
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idModulo) {
                    $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function(semestres) {
                        semestres.forEach(function(s) {
                            $('#filter-semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                        });
                    });
                }
                tabla.ajax.reload();
            });
            $('#filter-semestre').on('change', function() {
                tabla.ajax.reload();
            });
            $('#filter-nombre').on('keyup', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-unidad').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/unidadDidactica/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                        d.filter_modulo = $('#filter-modulo').val();
                        d.filter_semestre = $('#filter-semestre').val();
                        d.filter_nombre = $('#filter-nombre').val();
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
                        data: 'modulo_nombre'
                    },
                    {
                        data: 'semestre_nombre'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'creditos_teorico'
                    },
                    {
                        data: 'creditos_practico'
                    },
                    {
                        data: 'tipo'
                    },
                    {
                        data: 'orden'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/sigi/unidadDidactica/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
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