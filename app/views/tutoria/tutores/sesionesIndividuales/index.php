<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Sesiones Individuales de Tutoría</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/reportes/exportarIndividualesExcel" class="btn btn-outline-success btn-sm mr-2" title="Descargar Reporte en Excel">
                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                </a>
                <?php if ($esAdmin || $esDocente): ?>
                    <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#modalNuevaSesionInd">
                        <i class="fa fa-plus mr-1"></i> Nuevo
                    </button>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/tutoria" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Inicio
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?= $_SESSION['flash_success']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= $_SESSION['flash_error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Filtros de Búsqueda -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <?php if ($esAdmin): ?>
            <div class="col-md-4 mb-2">
                <label for="filtro_tutor" class="small font-weight-bold">Filtrar por Tutor:</label>
                <select id="filtro_tutor" class="form-control form-control-sm">
                    <option value="">-- Todos los Tutores --</option>
                    <?php foreach ($tutores as $t): ?>
                        <option value="<?= $t['id_docente'] ?>" <?= ($id_programacion && $t['id'] == $id_programacion) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['docente_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="col-md-3 mb-2">
                <label for="filtro_derivacion" class="small font-weight-bold">Estado de Derivación:</label>
                <select id="filtro_derivacion" class="form-control form-control-sm">
                    <option value="">-- Todos los Casos --</option>
                    <option value="1">Solo Casos Derivados (Psicología/Salud)</option>
                    <option value="0">Atención Regular (Sin derivación)</option>
                </select>
            </div>

            <div class="col-md-5 mb-2">
                <label for="buscar_texto" class="small font-weight-bold">Buscar por Estudiante o Motivo:</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="buscar_texto" class="form-control" placeholder="DNI, apellidos o palabra clave...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="btn_aplicar_filtro">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Sesiones Individuales -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-sesiones-ind" class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="40">N°</th>
                        <th width="120">Fecha / Hora</th>
                        <th>Estudiante Tutorando</th>
                        <th>Docente Tutor</th>
                        <th>Motivo de Atención</th>
                        <th width="130" class="text-center">Derivación</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabla = $('#tabla-sesiones-ind').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= BASE_URL ?>/tutoria/tutores/sesionesIndividualesData',
            type: 'GET',
            data: function(d) {
                d.id_docente  = $('#filtro_tutor').val() || '';
                d.derivacion  = $('#filtro_derivacion').val() || '';
                d.buscar      = $('#buscar_texto').val() || '';
            }
        },
        columns: [
            {
                data: 'nro',
                className: 'text-center font-weight-bold'
            },
            {
                data: 'fecha_hora',
                render: function(data) {
                    if (!data) return '-';
                    const parts = data.split(' ');
                    return `<strong>${parts[0]}</strong><br><small class="text-muted">${parts[1] || ''}</small>`;
                }
            },
            {
                data: 'estudiante_formato',
                render: function(data, type, row) {
                    return `<strong>${data}</strong><br><small class="text-muted">DNI: ${row.estudiante_dni || '-'} | ${row.programa_nombre || 'General'}</small>`;
                }
            },
            { data: 'docente_formato' },
            {
                data: 'motivo',
                render: function(data) {
                    return `<span class="badge badge-light border text-wrap text-left">${data}</span>`;
                }
            },
            {
                data: 'derivacion',
                className: 'text-center',
                render: function(data, type, row) {
                    if (data == 1) {
                        return `<span class="badge badge-warning p-1"><i class="fas fa-share-square mr-1"></i>${row.servicio_derivacion || 'Derivado'}</span>`;
                    }
                    return `<span class="badge badge-light border text-muted">No</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/individualPdf/${row.id}" target="_blank" class="btn btn-secondary btn-sm" title="Descargar / Imprimir PDF (Anexo 05)">
                            <i class="fas fa-file-pdf text-danger"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/individualVer/${row.id}" class="btn btn-info btn-sm" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/individualEditar/${row.id}" class="btn btn-warning btn-sm" title="Editar Sesión">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/individualEliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta sesión?');" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });

    $('#btn_aplicar_filtro').on('click', function() {
        tabla.ajax.reload();
    });
    $('#filtro_tutor, #filtro_derivacion').on('change', function() {
        tabla.ajax.reload();
    });
    $('#buscar_texto').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            tabla.ajax.reload();
        }
    });

    // Control de selección en el Modal de Nueva Sesión Individual
    $('#modal_ind_tutorando_sel').on('change', function() {
        const idTe = $(this).val();
        $('#btn_continuar_sesion_ind').prop('disabled', !idTe);
    });

    $('#btn_continuar_sesion_ind').on('click', function() {
        const idTe = $('#modal_ind_tutorando_sel').val();
        if (idTe) {
            window.location.href = '<?= BASE_URL ?>/tutoria/sesiones/individualNuevo/' + idTe;
        }
    });

    <?php if ($esAdmin): ?>
    // Si es administrador, al cambiar el tutor se obtienen sus tutorandos vía AJAX
    $('#modal_ind_tutor_sel').on('change', function() {
        const idProg = $(this).val();
        const $selectEst = $('#modal_ind_tutorando_sel');
        $selectEst.empty().append('<option value="">-- Cargando tutorandos... --</option>').prop('disabled', true);
        $('#btn_continuar_sesion_ind').prop('disabled', true);

        if (!idProg) {
            $selectEst.empty().append('<option value="">-- Primero seleccione un tutor --</option>');
            return;
        }

        $.getJSON('<?= BASE_URL ?>/tutoria/tutores/tutorandosPorProgramacion/' + idProg, function(data) {
            $selectEst.empty();
            if (data && data.length > 0) {
                $selectEst.append('<option value="">-- Seleccione un estudiante --</option>');
                $.each(data, function(i, item) {
                    const prog = item.programa_nombre || 'General';
                    $selectEst.append(`<option value="${item.id_tutoria_estudiante}">[${item.dni}] ${item.nombre_completo} - ${prog}</option>`);
                });
                $selectEst.prop('disabled', false);
            } else {
                $selectEst.append('<option value="">-- No hay estudiantes asignados a este tutor --</option>');
            }
        }).fail(function() {
            $selectEst.empty().append('<option value="">-- Error al cargar estudiantes --</option>');
        });
    });
    <?php endif; ?>
});
</script>

<!-- Modal para Registrar Nueva Sesión Individual -->
<?php if ($esAdmin || $esDocente): ?>
<div class="modal fade" id="modalNuevaSesionInd" tabindex="-1" role="dialog" aria-labelledby="modalNuevaSesionIndLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white font-weight-bold" id="modalNuevaSesionIndLabel">
                    <i class="fa fa-plus mr-2"></i>Nueva Sesión Individual
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if ($esAdmin): ?>
                    <div class="form-group mb-3">
                        <label for="modal_ind_tutor_sel" class="font-weight-bold text-dark">
                            <i class="fas fa-chalkboard-teacher mr-1 text-primary"></i>1. Seleccionar Tutor Programado:
                        </label>
                        <select id="modal_ind_tutor_sel" class="form-control form-control-sm">
                            <option value="">-- Seleccione un tutor --</option>
                            <?php foreach ($tutores as $t): ?>
                                <option value="<?= $t['id'] ?>">
                                    <?= htmlspecialchars($t['docente_nombre']) ?> (<?= htmlspecialchars($t['sede_nombre'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Al elegir un tutor se desplegarán sus tutorandos asignados.</small>
                    </div>
                <?php endif; ?>

                <div class="form-group mb-0">
                    <label for="modal_ind_tutorando_sel" class="font-weight-bold text-dark">
                        <i class="fas fa-user-graduate mr-1 text-success"></i><?= $esAdmin ? '2. ' : '' ?>Seleccionar Estudiante Tutorando:
                    </label>
                    <select id="modal_ind_tutorando_sel" class="form-control form-control-sm" <?= ($esAdmin ? 'disabled' : '') ?>>
                        <option value="">-- Seleccione un estudiante --</option>
                        <?php if ($esDocente && !empty($misTutorandos)): ?>
                            <?php foreach ($misTutorandos as $est): ?>
                                <option value="<?= $est['id_tutoria_estudiante'] ?>">
                                    [<?= htmlspecialchars($est['dni']) ?>] <?= htmlspecialchars($est['nombre_completo']) ?> - <?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <?php if ($esDocente && empty($misTutorandos)): ?>
                        <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                            <i class="fas fa-exclamation-triangle mr-1"></i>No tienes tutorandos asignados para este periodo lectivo y sede activa.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_continuar_sesion_ind" class="btn btn-success btn-sm" disabled>
                    <i class="fa fa-plus mr-1"></i> Iniciar Sesión
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
