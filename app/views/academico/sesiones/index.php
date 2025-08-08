<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if ($permitido): ?>
    <div class="card p-2">
        <?php if (\Core\Auth::esDocenteAcademico()): ?>
            <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
        <?php endif; ?>
        <?php if (\Core\Auth::esAdminAcademico()): ?>
            <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
        <?php endif; ?>
        <h4>Sesiones de Aprendizaje - <?= htmlspecialchars($datosUnidad['unidad']) ?></h4>
        <div class="mb-3">
            <b>Docente:</b> <?= htmlspecialchars($datosUnidad['docente']) ?> |
            <b>Programa:</b> <?= htmlspecialchars($datosUnidad['programa']) ?> |
            <b>Periodo:</b> <?= htmlspecialchars($datosUnidad['periodo']) ?>
        </div>
        <div class="table-responsive">
            <table id="tabla-sesiones" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Semana</th>
                        <th>N° de Sesión</th>
                        <th>Denominación de la Sesión</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody><!-- DataTables llenará esto --></tbody>
            </table>
        </div>
        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-secondary mt-2">Volver</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#tabla-sesiones').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/academico/sesiones/data/<?= $id_programacion ?>',
                    type: 'GET'
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'semana'
                    },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            // Vamos a numerar correlativo por semana en el frontend
                            if (!window._sesionSemana) window._sesionSemana = {};
                            const semana = row.semana;
                            window._sesionSemana[semana] = (window._sesionSemana[semana] || 0) + 1;
                            row._nroSesion = window._sesionSemana[semana];
                            return row._nroSesion;
                        }
                    },
                    {
                        data: 'denominacion'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Preparamos un arreglo con todas las filas cargadas en esta página
                            let acciones = '';
                            <?php if ($periodo_vigente): ?>
                                acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/editar/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa fa-edit"></i></a> `;
                            <?php endif; ?>
                            acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/pdf/${row.id}" class="btn btn-sm btn-outline-secondary" title="Imprimir" target="_blank"><i class="fa fa-print"></i></a> `;
                            <?php if ($periodo_vigente && 100>1000): ?>
                                acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/duplicar/${row.id}" class="btn btn-sm btn-outline-success" title="Duplicar"  onclick="return confirm('¿Duplicar esta sesión?');"><i class="fa fa-copy"></i></a> `;
                            <?php endif; ?>
                            // Eliminar solo si es la 2da o mayor sesión de esa semana
                            if (row._nroSesion > 1) {
                                acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/eliminar/${row.id}" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta sesión extra?');"><i class="fa fa-trash"></i></a>`;
                            }
                            return acciones;
                        }
                    }
                ],
                drawCallback: function(settings) {
                    // Reset correlativo de sesiones por semana en cada redibujo/paginación
                    window._sesionSemana = {};
                },
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <div class="alert alert-danger mt-4">
        <b>Acceso denegado:</b> Solo puede visualizar las sesiones el administrador académico o el docente encargado de esta programación.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>