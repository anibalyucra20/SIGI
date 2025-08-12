<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if ($permitido): ?>
    <div class="card p-3">
        <h4 class="mb-3">Configuración de Unidad Didáctica</h4>

        <div class="alert alert-info">
            <b>Unidad Didáctica:</b> <?= htmlspecialchars($infoUD['unidad_nombre']) ?> |
            <b>Plan:</b> <?= htmlspecialchars($infoUD['plan_nombre']) ?> |
            <b>Módulo:</b> <?= htmlspecialchars($infoUD['nro_modulo']) ?> |
            <b>Semestre:</b> <?= htmlspecialchars($infoUD['semestre_nombre']) ?><br>
            <?php if ($silaboDestino): ?>
                <span class="badge bg-success">Esta programación ya tiene Sílabo</span>
            <?php else: ?>
                <span class="badge bg-secondary">Sin Sílabo en destino</span>
            <?php endif; ?>
        </div>

        <?php if (!$periodo_vigente): ?>
            <div class="alert alert-warning">El periodo académico del destino ya culminó. No se permite copiar.</div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/academico/unidadesDidacticas/copiarContenido" method="post"
            onsubmit="return confirm('¿Copiar Sílabo y Sesiones al destino? Esta acción puede reemplazar información.');">
            <input type="hidden" name="id_prog_dest" value="<?= (int)$programacion['id'] ?>">

            <!-- Filtros en cascada (reutilizando rutas existentes) -->
            <h6 class="mb-2">Filtros:</h6>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Programa de Estudios</label>
                    <select id="filter-programa" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($programas as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Plan de Estudios</label>
                    <select id="filter-plan" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Módulo Profesional</label>
                    <select id="filter-modulo" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Semestre</label>
                    <select id="filter-semestre" class="form-control">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tabla-candidatas" class="table table-bordered table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Sel.</th>
                            <th>Periodo</th>
                            <th>Docente</th>
                            <th>Programa de Estudios</th>
                            <th>Plan</th>
                            <th>Módulo</th>
                            <th>Semestre</th>
                            <th>UD</th>
                            <th>Sede</th>
                            <th>Sílabo</th>
                            <th>#Activ.</th>
                            <th>#Ses.</th>
                        </tr>
                    </thead>
                    <tbody><!-- DataTables --></tbody>
                </table>
            </div>

            <?php if ($periodo_vigente): ?>
                <button type="submit" class="btn btn-primary">Copiar Sílabo + Sesiones</button>
            <?php endif; ?>
            <?php if (\Core\Auth::esDocenteAcademico()): ?>
                <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
            <?php endif; ?>
            <?php if (\Core\Auth::esAdminAcademico()): ?>
                <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idProg = <?= (int)$programacion['id'] ?>;
            const urlBase = '<?= BASE_URL ?>';

            // ========= Filtros dependientes (reutilizando tus rutas) =========
            // (Si ya los usas globalmente en otra vista, puedes replicar el mismo snippet)
            $('#filter-programa').on('change', function() {
                let idPrograma = $(this).val();
                $('#filter-plan').html('<option value="">Todos</option>');
                $('#filter-modulo').html('<option value="">Todos</option>');
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idPrograma) {
                    $.getJSON(`${urlBase}/sigi/planes/porPrograma/${idPrograma}`, function(planes) {
                        planes.forEach(pl => $('#filter-plan').append(`<option value="${pl.id}">${pl.nombre}</option>`));
                    });
                }
                tabla.ajax.reload();
            });

            $('#filter-plan').on('change', function() {
                let idPlan = $(this).val();
                $('#filter-modulo').html('<option value="">Todos</option>');
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idPlan) {
                    $.getJSON(`${urlBase}/sigi/moduloFormativo/porPlan/${idPlan}`, function(modulos) {
                        modulos.forEach(m => $('#filter-modulo').append(`<option value="${m.id}">${m.descripcion}</option>`));
                    });
                }
                tabla.ajax.reload();
            });

            $('#filter-modulo').on('change', function() {
                let idModulo = $(this).val();
                $('#filter-semestre').html('<option value="">Todos</option>');
                if (idModulo) {
                    $.getJSON(`${urlBase}/sigi/semestre/porModulo/${idModulo}`, function(semestres) {
                        semestres.forEach(s => $('#filter-semestre').append(`<option value="${s.id}">${s.descripcion}</option>`));
                    });
                }
                tabla.ajax.reload();
            });

            $('#filter-semestre').on('change', function() {
                tabla.ajax.reload();
            });

            // ========= DataTable server-side =========
            const tabla = $('#tabla-candidatas').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthMenu: [10, 25, 50],
                order: [
                    [1, 'desc']
                ], // Periodo
                ajax: {
                    url: `${urlBase}/academico/unidadesDidacticas/candidatasData/${idProg}`,
                    type: 'GET',
                    data: function(d) {
                        d.filter_programa = $('#filter-programa').val();
                        d.filter_plan = $('#filter-plan').val();
                        d.filter_modulo = $('#filter-modulo').val();
                        d.filter_semestre = $('#filter-semestre').val();
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        render: (data, type, row) => {
                            const isSelf = Number(row.id) === Number(idProg);
                            const hasSyll = Number(row.tiene_silabo) === 1; // cuidado con "0" string
                            const disabled = isSelf || !hasSyll ? 'disabled' : '';
                            const title = isSelf ?
                                'No puedes seleccionar la misma programación' :
                                (!hasSyll ? 'La programación origen no tiene Sílabo' : 'Seleccionar esta programación');
                            return `<input type="radio" name="id_prog_origen" value="${row.id}" ${disabled} title="${title}">`;
                        }
                    },
                    {
                        data: 'periodo'
                    },
                    {
                        data: 'docente'
                    },
                    {
                        data: 'programa_nombre'
                    },
                    {
                        data: 'plan_nombre'
                    },
                    {
                        data: 'nro_modulo'
                    },
                    {
                        data: 'semestre_nombre'
                    },
                    {
                        data: 'unidad_nombre'
                    },
                    {
                        data: 'sede_nombre'
                    },
                    {
                        data: 'tiene_silabo',
                        defaultContent: 'No',
                        render: v => Number(v) === 1 ?
                            '<span class="badge bg-success">Sí</span>' :
                            '<span class="badge bg-danger">No</span>'
                    },
                    {
                        data: 'actividades'
                    },
                    {
                        data: 'sesiones'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <p>No autorizado.</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>