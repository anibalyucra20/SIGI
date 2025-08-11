<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Programaciones de Unidades Didácticas</h3>
        <?php if ($periodo_vigente): ?>
            <div class="mb-2">
                <a href="<?= BASE_URL ?>/academico/programacionUnidadDidactica/nuevo" class="btn btn-success">+ Nueva Programación</a>
                <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target="#modal-prog-masiva">Programación Masiva</button>
            </div>
            <div class="modal fade" id="modal-prog-masiva" tabindex="-1" role="dialog" aria-labelledby="modal-carga-masiva" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-prog-masiva">Progrmación Masiva</h5>
                            <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="<?= BASE_URL ?>/academico/programacionUnidadDidactica/programacionMasiva" method="post">
                                <div class="col-md-12 mb-2">
                                    <label>Programa de Estudios *</label>
                                    <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($programas as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= (isset($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label>Plan de Estudios *</label>
                                    <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label>Módulo Profesional *</label>
                                    <select name="id_modulo_formativo" id="id_modulo_formativo" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label>Semestre *</label>
                                    <select name="id_semestre" id="id_semestre" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Turno *</label>
                                    <select name="turno" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="M">Mañana</option>
                                        <option value="T">Tarde</option>
                                        <option value="N">Noche</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Sección *</label>
                                    <select name="seccion" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                    </select>
                                </div>
                                <br>
                                <br>

                                <button type="submit" class="btn btn-primary waves-effect waves-light">Generar Programaciones</button>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect waves-light" data-dismiss="modal">Cancelar</button>

                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <h5 class="mb-2">Filtros:</h5>
        <div class="row mb-3">
            <div class="col-md-2">
                <label>Programa de Estudios</label>
                <select id="filter-programa" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($programas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Plan de Estudios</label>
                <select id="filter-plan" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Módulo Profesional</label>
                <select id="filter-modulo" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Semestre</label>
                <select id="filter-semestre" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Docente</label>
                <select id="filter-docente" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($docentes as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['apellidos_nombres']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Turno</label>
                <select id="filter-turno" class="form-control">
                    <option value="">Todos</option>
                    <option value="M">Mañana</option>
                    <option value="T">Tarde</option>
                    <option value="N">Noche</option>
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-4">
                <label>Unidad Didáctica</label>
                <input type="text" id="filter-unidad" class="form-control" placeholder="Nombre unidad didáctica">
            </div>
        </div>
        <div class="table-responsive">
            <table id="tabla-programaciones" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Programa</th>
                        <th>Plan</th>
                        <th>Módulo</th>
                        <th>Semestre</th>
                        <th>Unidad Didáctica</th>
                        <th>Docente</th>
                        <th>Turno</th>
                        <th>Sección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody><!-- DataTables AJAX llenará esto --></tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtros dependientes tabla 
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
            $('#filter-semestre, #filter-docente, #filter-turno, #filter-seccion, #filter-unidad').on('change input', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-programaciones').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/academico/programacionUnidadDidactica/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                        d.filter_modulo = $('#filter-modulo').val();
                        d.filter_semestre = $('#filter-semestre').val();
                        d.filter_docente = $('#filter-docente').val();
                        d.filter_turno = $('#filter-turno').val();
                        d.filter_seccion = $('#filter-seccion').val();
                        d.filter_unidad = $('#filter-unidad').val();
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1 + meta.settings._iDisplayStart
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
                        data: 'unidad_nombre'
                    },
                    {
                        data: 'docente_nombre'
                    },
                    {
                        data: 'turno',
                        render: data => data === 'M' ? 'Mañana' : (data === 'T' ? 'Tarde' : 'Noche')
                    },
                    {
                        data: 'seccion'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,

                        render: row => ` <?php if ($periodo_vigente): ?><a href="<?= BASE_URL ?>/academico/programacionUnidadDidactica/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a> <?php endif; ?>`

                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });


            // filtros modal
            // Cascada de selects dependientes
            $('#id_programa_estudios').on('change', function() {
                let idPrograma = $(this).val();
                $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
                $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
                $('#id_semestre').html('<option value="">Seleccione...</option>');
                if (idPrograma) {
                    $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                        planes.forEach(function(pl) {
                            $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                        });
                    });
                }
            });
            $('#id_plan_estudio').on('change', function() {
                let idPlan = $(this).val();
                $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
                $('#id_semestre').html('<option value="">Seleccione...</option>');
                if (idPlan) {
                    $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function(modulos) {
                        modulos.forEach(function(m) {
                            $('#id_modulo_formativo').append('<option value="' + m.id + '">' + m.descripcion + '</option>');
                        });
                    });
                }
            });
            $('#id_modulo_formativo').on('change', function() {
                let idModulo = $(this).val();
                $('#id_semestre').html('<option value="">Seleccione...</option>');
                if (idModulo) {
                    $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function(semestres) {
                        semestres.forEach(function(s) {
                            $('#id_semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                        });
                    });
                }
            });
        });
    </script>
<?php else: ?>
    <div class="alert alert-danger mt-4">
        <p>El módulo solo es para rol de Administrador Académico.</p>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>