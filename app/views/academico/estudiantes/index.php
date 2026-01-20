<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Estudiantes</h3>
        <?php if ($periodo_vigente): ?>
            <div class="mb-2">
                <a href="<?= BASE_URL ?>/academico/estudiantes/nuevo" class="btn btn-success">+ Nuevo Estudiante</a>
                <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target="#modal-carga-masiva">Carga Masiva</button>
            </div>
            <div class="modal fade" id="modal-carga-masiva" tabindex="-1" role="dialog" aria-labelledby="modal-carga-masiva" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-carga-masiva">Carga Masiva de Estudiantes</h5>
                            <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <a href="<?= BASE_URL ?>/academico/estudiantes/descargarPlantillaCargaMasiva" class="btn btn-success col-6">Descargar Plantilla Excel</a> <br><br>
                            <form action="<?= BASE_URL ?>/academico/estudiantes/CargaMasivaEstudiantes" method="post" enctype="multipart/form-data">
                                <input type="file" name="archivo_excel" class="form-control" required accept=".xlsx,.xls"><br><br>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Cargar Plantilla</button>
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
            <div class="col-md-3">
                <label>Programa</label>
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
            <div class="col-md-3">
                <label>DNI</label>
                <input type="text" id="filter-dni" class="form-control" placeholder="DNI">
            </div>
            <div class="col-md-3">
                <label>Apellidos y Nombres</label>
                <input type="text" id="filter-apellidos-nombres" class="form-control" placeholder="Apellidos o nombres">
            </div>
        </div>
        <div class="table-responsive">
            <table id="tabla-estudiantes" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Sede</th>
                        <th>Programa</th>
                        <th>Plan</th>
                        <th>Periodo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody><!-- DataTables AJAX llenará esto --></tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            $('#filter-dni, #filter-apellidos-nombres').on('input', function() {
                tabla.ajax.reload();
            });

            const tabla = $('#tabla-estudiantes').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/academico/estudiantes/data',
                    type: 'GET',
                    data: function(d) {
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                        d.filter_dni = $('#filter-dni').val();
                        d.filter_apellidos_nombres = $('#filter-apellidos-nombres').val();
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1 + meta.settings._iDisplayStart
                    },
                    {
                        data: 'dni'
                    },
                    {
                        data: 'apellidos_nombres'
                    },
                    {
                        data: 'sede_nombre'
                    },
                    {
                        data: 'programa_nombre'
                    },
                    {
                        data: 'plan_nombre'
                    },
                    {
                        data: 'periodo_nombre'
                    },
                    {
                        data: 'correo'
                    },
                    {
                        data: 'telefono'
                    },
                    {
                        data: 'estado',
                        render: data => data == 1 ? 'Activo' : 'Inactivo'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: row => `
                        <a href="<?= BASE_URL ?>/academico/estudiantes/editar/${row.id}" title="Editar" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        <a href="<?= BASE_URL ?>/resetPassword?data=${btoa(row.id)}&back=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Enviar Correo" class="btn btn-dark btn-sm"><i class="fa fa-envelope"></i></a>
                        <a href="<?= BASE_URL ?>/sigi/docentes/resetPassword?data=${btoa(row.id)}&back=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Resetear Contraseña" class="btn btn-success btn-sm"><i class="fa fa-key"></i></a>`
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>