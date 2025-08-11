<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Listado de Matrículas</h3>
        <div class="mb-2">
            <?php if ($periodo_vigente): ?>
                <a href="<?= BASE_URL ?>/academico/matricula/nuevo" class="btn btn-success">+ Nueva Matrícula</a>
            <?php endif; ?>
        </div>
        <h5 class="mb-2">Filtros:</h5>
        <div class="row mb-3">
            <div class="col-md-2 mb-2">
                <label>DNI</label>
                <input type="text" id="filter-dni" class="form-control" maxlength="20" placeholder="DNI">
            </div>
            <div class="col-md-3 mb-2">
                <label>Apellidos y Nombres</label>
                <input type="text" id="filter-apellidos-nombres" class="form-control" maxlength="100" placeholder="Buscar">
            </div>
            <div class="col-md-3 mb-2">
                <label>Programa de Estudios</label>
                <select id="filter-programa" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($programas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label>Plan de Estudios</label>
                <select id="filter-plan" class="form-control">
                    <option value="">Todos</option>

                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>Semestre</label>
                <select id="filter-semestre" class="form-control">
                    <option value="">Todos</option>

                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>Turno</label>
                <select id="filter-turno" class="form-control">
                    <option value="">Todos</option>
                    <option value="M">Mañana</option>
                    <option value="T">Tarde</option>
                    <option value="N">Noche</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>Sección</label>
                <select id="filter-seccion" class="form-control">
                    <option value="">Todos</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tabla-matriculas" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nro</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Programa</th>
                        <th>Plan</th>
                        <th>Semestre</th>
                        <th>Turno</th>
                        <th>Sección</th>
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
            const tabla = $('#tabla-matriculas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/academico/matricula/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_dni = $('#filter-dni').val();
                        d.filter_apellidos_nombres = $('#filter-apellidos-nombres').val();
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                        d.filter_semestre = $('#filter-semestre').val();
                        d.filter_turno = $('#filter-turno').val();
                        d.filter_seccion = $('#filter-seccion').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'dni'
                    },
                    {
                        data: 'apellidos_nombres'
                    },
                    {
                        data: 'programa'
                    },
                    {
                        data: 'plan'
                    },
                    {
                        data: 'semestre'
                    },
                    {
                        data: 'turno'
                    },
                    {
                        data: 'seccion'
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/academico/matricula/ver/${row.id}" class="btn btn-primary btn-sm">Ver</a>
                    `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });

            // Filtros: recarga
            $('#filter-dni, #filter-apellidos-nombres, #filter-programa, #filter-plan, #filter-semestre, #filter-turno, #filter-seccion')
                .on('change keyup', function() {
                    tabla.ajax.reload();
                });
            $('#filter-programa').on('change', function() {
                let idPrograma = $(this).val();
                $('#filter-plan').html('<option value="">Todos</option>');
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
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idPlan) {
                    $.getJSON('<?= BASE_URL ?>/sigi/semestre/porPlan/' + idPlan, function(semestres) {
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

        });
    </script>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>