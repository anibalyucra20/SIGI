<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Sesiones Grupales de Tutoría</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/reportes/exportarGrupalesExcel" class="btn btn-outline-success btn-sm mr-2" title="Descargar Reporte en Excel">
                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                </a>
                <?php if ($esAdmin): ?>
                    <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#modalNuevaSesionGrupal">
                        <i class="fa fa-plus mr-1"></i> Nuevo
                    </button>
                <?php elseif ($miProg): ?>
                    <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalNuevo/<?= $miProg['id'] ?>" class="btn btn-success btn-sm mr-2">
                        <i class="fa fa-plus mr-1"></i> Nuevo
                    </a>
                <?php elseif ($esDocente): ?>
                    <button type="button" class="btn btn-success btn-sm mr-2" disabled title="No tienes una programación activa para este periodo">
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

<!-- Filtros -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <?php if ($esAdmin): ?>
            <div class="col-md-5 mb-2">
                <label for="filtro_tutor_grup" class="small font-weight-bold">Filtrar por Tutor:</label>
                <select id="filtro_tutor_grup" class="form-control form-control-sm">
                    <option value="">-- Todos los Tutores --</option>
                    <?php foreach ($tutores as $t): ?>
                        <option value="<?= $t['id_docente'] ?>" <?= ($id_programacion && $t['id'] == $id_programacion) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['docente_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="col-md-4 mb-2">
                <label for="filtro_tipo" class="small font-weight-bold">Tipo de Sesión:</label>
                <select id="filtro_tipo" class="form-control form-control-sm">
                    <option value="">-- Todos los Tipos --</option>
                    <option value="Taller">Taller</option>
                    <option value="Charla">Charla Temática</option>
                    <option value="Inducción">Inducción / Bienvenida</option>
                    <option value="Orientación Vocacional">Orientación Vocacional</option>
                    <option value="Habilidades Blandas">Habilidades Blandas</option>
                    <option value="Técnicas de Estudio">Técnicas de Estudio</option>
                </select>
            </div>

            <div class="col-md-3 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-primary btn-sm btn-block" id="btn_filtrar_grup">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Sesiones Grupales -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-sesiones-grup" class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="40">N°</th>
                        <th width="120">Fecha / Hora</th>
                        <th width="140">Tipo</th>
                        <th>Docente Tutor</th>
                        <th>Objetivo de la Sesión</th>
                        <th width="100" class="text-center">Asistentes</th>
                        <th width="110" class="text-center">Logro</th>
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
    const tabla = $('#tabla-sesiones-grup').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= BASE_URL ?>/tutoria/tutores/sesionesGrupalesData',
            type: 'GET',
            data: function(d) {
                d.id_docente = $('#filtro_tutor_grup').val() || '';
                d.tipo       = $('#filtro_tipo').val() || '';
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data, type, row, meta) {
                    return meta.row + 1 + meta.settings._iDisplayStart;
                }
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
                data: 'tipo',
                render: function(data) {
                    return `<span class="badge badge-soft-info p-1">${data}</span>`;
                }
            },
            { data: 'docente_formato' },
            {
                data: 'objetivo_sesion',
                render: function(data) {
                    return `<span class="text-wrap">${data}</span>`;
                }
            },
            {
                data: 'total_asistentes',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge badge-success p-1"><i class="fas fa-users mr-1"></i>${data}</span>`;
                }
            },
            {
                data: 'logro_objetivo',
                className: 'text-center',
                render: function(data) {
                    const badge = (data === 'Logrado') ? 'badge-success' : (data === 'En Proceso' ? 'badge-warning' : 'badge-danger');
                    return `<span class="badge ${badge} p-1">${data}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalPdf/${row.id}" target="_blank" class="btn btn-secondary btn-sm" title="Descargar / Imprimir PDF (Anexo 04)">
                            <i class="fas fa-file-pdf text-danger"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalVer/${row.id}" class="btn btn-info btn-sm" title="Ver Detalle y Asistencia">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalEditar/${row.id}" class="btn btn-warning btn-sm" title="Editar Sesión">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalEliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta sesión grupal?');" title="Eliminar">
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

    $('#btn_filtrar_grup, #filtro_tutor_grup, #filtro_tipo').on('click change', function() {
        tabla.ajax.reload();
    });

    <?php if ($esAdmin): ?>
    $('#modal_grup_tutor_sel').on('change', function() {
        $('#btn_continuar_sesion_grup').prop('disabled', !$(this).val());
    });

    $('#btn_continuar_sesion_grup').on('click', function() {
        const idProg = $('#modal_grup_tutor_sel').val();
        if (idProg) {
            window.location.href = '<?= BASE_URL ?>/tutoria/sesiones/grupalNuevo/' + idProg;
        }
    });
    <?php endif; ?>
});
</script>

<!-- Modal para Iniciar Nueva Sesión Grupal (Admin) -->
<?php if ($esAdmin): ?>
<div class="modal fade" id="modalNuevaSesionGrupal" tabindex="-1" role="dialog" aria-labelledby="modalNuevaSesionGrupalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white font-weight-bold" id="modalNuevaSesionGrupalLabel">
                    <i class="fa fa-plus mr-2"></i>Nueva Sesión Grupal
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label for="modal_grup_tutor_sel" class="font-weight-bold text-dark">
                        <i class="fas fa-chalkboard-teacher mr-1 text-primary"></i>Seleccionar Tutor Programado:
                    </label>
                    <select id="modal_grup_tutor_sel" class="form-control form-control-sm">
                        <option value="">-- Seleccione una programación de tutor --</option>
                        <?php foreach ($tutores as $t): ?>
                            <option value="<?= $t['id'] ?>">
                                <?= htmlspecialchars($t['docente_nombre']) ?> (<?= htmlspecialchars($t['sede_nombre'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted mt-1 d-block">La sesión grupal se registrará vinculada a la programación del docente tutor seleccionado.</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_continuar_sesion_grup" class="btn btn-success btn-sm" disabled>
                    <i class="fa fa-plus mr-1"></i> Iniciar Sesión Grupal
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
