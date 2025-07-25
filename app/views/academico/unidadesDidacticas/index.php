<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-2">
    <h3 class="mb-2">Mis Unidades Didácticas Programadas</h3>
    <div class="table-responsive col-12">
        <table id="tabla-unidades" class="table table-bordered table-hover table-sm align-middle col-12">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Programa</th>
                    <th>Plan</th>
                    <th>Módulo</th>
                    <th>Semestre</th>
                    <th>Unidad Didáctica</th>
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
    const tabla = $('#tabla-unidades').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: '<?= BASE_URL ?>/academico/unidadesDidacticas/data',
            type: 'GET'
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 + meta.settings._iDisplayStart },
            { data: 'programa_nombre' },
            { data: 'plan_nombre' },
            { data: 'modulo_nombre' },
            { data: 'semestre_nombre' },
            { data: 'unidad_nombre' },
            { data: 'turno', render: data => data === 'M' ? 'Mañana' : (data === 'T' ? 'Tarde' : 'Noche') },
            { data: 'seccion' },
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
                    let periodoVigente = <?= $periodo_vigente ? 'true' : 'false' ?>;

                    if (periodoVigente) {
                        acciones += `<a href="<?= BASE_URL ?>/academico/silabos/editar/${row.id}" class="btn btn-sm btn-outline-primary mb-1">Sílabo</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/ver/${row.id}" class="btn btn-sm btn-outline-secondary mb-1">Sesiones</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/calificaciones/ver/${row.id}" class="btn btn-sm btn-outline-success mb-1">Calificaciones</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/asistencia/ver/${row.id}" class="btn btn-sm btn-outline-warning mb-1">Asistencia</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/${row.id}" class="btn btn-sm btn-outline-dark mb-1">Informe Final</a> `;
                    } else {
                        acciones += `<a href="<?= BASE_URL ?>/academico/silabos/editar/${row.id}" class="btn btn-sm btn-outline-primary mb-1">Ver Sílabo</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/sesiones/ver/${row.id}" class="btn btn-sm btn-outline-secondary mb-1">Ver Sesiones</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/calificaciones/ver/${row.id}" class="btn btn-sm btn-outline-success mb-1">Ver Calificaciones</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/asistencia/ver/${row.id}" class="btn btn-sm btn-outline-warning mb-1">Ver Asistencia</a> `;
                        acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/${row.id}" class="btn btn-sm btn-outline-dark mb-1">Ver Informe Final</a> `;
                    }
                    acciones += `<a href="<?= BASE_URL ?>/academico/unidadesDidacticas/imprimirCaratula/${row.id}" target="_blank" class="btn btn-sm btn-outline-info mb-1">Imprimir Carátula</a> `;
                    return acciones;
                }
            }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' }
    });
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
