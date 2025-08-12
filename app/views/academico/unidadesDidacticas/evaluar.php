<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Evaluaciones</h3>
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
                        render: function(data, type, row) {
                            let acciones = '';
                            <?php
                            // PHP determina si el periodo está vigente
                            $periodo_vigente = (isset($periodo['vigente']) && $periodo['vigente'] == 1);
                            ?>
                            // Este valor JS se setea dinámicamente según el backend, aquí solo a modo ejemplo:
                            let periodoVigente = <?= ($periodo_vigente) ? 'true' : 'false' ?>;

                            acciones += `<a href="<?= BASE_URL ?>/academico/silabos/editar/${row.id}" class="btn btn-sm btn-outline-warning mb-1" title="Sílabo"><i class="fa fa-book"></i></a> `;
                            acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/ver/${row.id}" class="btn btn-sm btn-outline-primary mb-1" title="Sesiones de Aprendizaje"><i class="fa fa-briefcase"></i></a> `;
                            acciones += `<a href="<?= BASE_URL ?>/academico/asistencia/ver/${row.id}" class="btn btn-sm btn-outline-success mb-1" title="Asistencia"><i class="fa fa-users"></i></a> `;
                            acciones += `<a href="<?= BASE_URL ?>/academico/calificaciones/ver/${row.id}" class="btn btn-sm btn-outline-info mb-1" title="Calificaciones"><i class="fa fa-edit"></i></a> `;
                            acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/${row.id}" class="btn btn-sm btn-outline-dark mb-1" title="Informe Final"><i class="fa fa-chart-bar"></i></a> `;
                            acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/imprimirCaratula/${row.id}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1" title="Imprimir Carátula"><i Class="fa fa-file"></i></a> `;
                            if (periodoVigente) {
                                acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/configuracion/${row.id}" class="btn btn-sm btn-outline-dark mb-1" title="Configuración"><i Class="fa fa-cog"></i></a> `;
                            }
                            return acciones;
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
    <div class="alert alert-danger mt-4">
        <p>El módulo solo es para rol de Administrador Académico.</p>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>