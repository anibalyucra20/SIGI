<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Gestión de Tutorandos</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/programacion" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Programación
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

<!-- Tarjeta de Información del Tutor -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="text-primary mb-1">
                    <i class="fas fa-chalkboard-teacher mr-2"></i><?= htmlspecialchars($programacion['docente_formato']) ?>
                </h5>
                <p class="text-muted mb-0">
                    <strong>DNI:</strong> <?= htmlspecialchars($programacion['docente_dni']) ?> |
                    <strong>Correo:</strong> <?= htmlspecialchars($programacion['docente_correo'] ?: 'No registrado') ?> |
                    <strong>Sede:</strong> <?= htmlspecialchars($programacion['sede_nombre']) ?> |
                    <strong>Periodo:</strong> <?= htmlspecialchars($programacion['periodo_nombre']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <span class="badge badge-soft-primary p-2 font-size-14">
                    <i class="fas fa-user-graduate mr-1"></i> Tutorandos Asignados: <?= count($estudiantes) ?>
                </span>
                <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalNuevo/<?= $programacion['id'] ?>" class="btn btn-outline-info btn-sm ml-2" title="Crear Sesión Grupal">
                    <i class="fas fa-users mr-1"></i> Sesión Grupal
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Tutorandos Asignados -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Lista de Estudiantes Asignados</h5>
        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAsignarEstudiante" id="btn_abrir_asignar">
            <i class="fa fa-plus mr-1"></i> Asignar Estudiante
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla-tutorandos" class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="50">#</th>
                        <th width="100">DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Programa de Estudios</th>
                        <th width="90" class="text-center">Sesiones Ind.</th>
                        <th>Observación Inicial</th>
                        <th width="220" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estudiantes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mr-1"></i> No hay estudiantes asignados a este tutor todavía. Haz clic en <strong>+ Asignar Estudiante</strong> para comenzar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $i => $est): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><span class="badge badge-light"><?= htmlspecialchars($est['dni']) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($est['nombre_completo']) ?></strong>
                                    <?php if (!empty($est['telefono'])): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($est['telefono']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $est['total_sesiones_ind'] > 0 ? 'success' : 'secondary' ?>">
                                        <?= $est['total_sesiones_ind'] ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($est['observacion'] ?: 'Sin observaciones') ?></small>
                                </td>
                                <td class="text-center">
                                    <!-- Ver / Registrar Sesión Individual -->
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-primary btn-sm" title="Sesiones Individuales">
                                        <i class="fas fa-user-clock"></i> Sesiones
                                    </a>
                                    <!-- Ficha de Diagnóstico -->
                                    <a href="<?= BASE_URL ?>/tutoria/fichas/ver/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-info btn-sm" title="Ficha de Recojo de Información">
                                        <i class="fas fa-clipboard-list"></i> Ficha
                                    </a>
                                    <!-- Retirar -->
                                    <a href="<?= BASE_URL ?>/tutoria/programacion/eliminarTutorando/<?= $est['id_tutoria_estudiante'] ?>/<?= $programacion['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de retirar este estudiante de la tutoría?');" title="Retirar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tarjeta de Conclusiones Finales del Periodo -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-signature text-warning mr-2"></i>Conclusiones y Recomendaciones Finales del Tutor
        </h5>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/programacion/guardarConclusiones" method="POST">
            <input type="hidden" name="id_programacion" value="<?= $programacion['id'] ?>">
            <div class="form-group">
                <label for="conclusiones">Informe / Conclusiones del Periodo Académico:</label>
                <textarea name="conclusiones" id="conclusiones" rows="4" class="form-control" placeholder="Escriba aquí el balance general de la tutoría en el semestre, logros alcanzados y recomendaciones para el siguiente periodo..."><?= htmlspecialchars($programacion['conclusiones'] ?? '') ?></textarea>
                <small class="form-text text-muted">Este texto formará parte del informe final consolidado de tutoría institucional.</small>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i> Guardar Conclusiones
            </button>
        </form>
    </div>
</div>

<!-- Modal: Asignar Estudiante -->
<div class="modal fade" id="modalAsignarEstudiante" tabindex="-1" role="dialog" aria-labelledby="modalAsignarTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form action="<?= BASE_URL ?>/tutoria/programacion/guardarTutorando" method="POST" id="form_asignar_tutorando">
                <input type="hidden" name="id_programacion_tutoria" value="<?= $programacion['id'] ?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="modalAsignarTitle">
                        <i class="fa fa-plus mr-2"></i>Asignar Estudiante a Tutoría
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="filtro_programa" class="font-weight-bold">Filtrar por Programa de Estudios:</label>
                            <select id="filtro_programa" class="form-control form-control-sm">
                                <option value="">-- Todos los programas --</option>
                                <?php foreach ($programas as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="buscar_estudiante" class="font-weight-bold">Buscar por DNI o Apellidos:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="buscar_estudiante" class="form-control" placeholder="Ej. 70123456 o Pérez...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="btn_buscar_est">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label for="select_estudiante" class="font-weight-bold text-dark">
                            Seleccionar Estudiante Disponible: <span class="text-danger">*</span>
                        </label>
                        <select name="id_estudiante" id="select_estudiante" class="form-control" required size="7">
                            <option value="" disabled selected>-- Cargando estudiantes disponibles... --</option>
                        </select>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small id="estudiante_contador" class="text-muted">Cargando...</small>
                            <small class="text-info font-italic"><i class="fas fa-info-circle mr-1"></i>Haga clic sobre un estudiante para seleccionarlo</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="observacion" class="font-weight-bold">Observación Inicial / Diagnóstico Previo (Opcional):</label>
                        <textarea name="observacion" id="observacion" class="form-control" rows="2" placeholder="Motivo de asignación, antecedentes o condición especial..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btn_submit_asignar" disabled>
                        <i class="fa fa-plus mr-1"></i> Asignar a este Tutor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#tabla-tutorandos')) {
        $('#tabla-tutorandos').DataTable().destroy();
    }
    $('#tabla-tutorandos').DataTable({
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });

    // Carga de estudiantes disponibles vía AJAX
    function cargarEstudiantesDisponibles() {
        const progId = $('#filtro_programa').val();
        const query  = $('#buscar_estudiante').val();
        const idProg = '<?= $programacion['id'] ?>';

        const $select = $('#select_estudiante');
        const $btnSubmit = $('#btn_submit_asignar');
        $btnSubmit.prop('disabled', true);
        $select.html('<option value="" disabled selected>Cargando estudiantes disponibles...</option>');

        $.ajax({
            url: '<?= BASE_URL ?>/tutoria/programacion/buscarEstudiantesAjax',
            type: 'GET',
            data: {
                id_programacion: idProg,
                id_programa: progId,
                q: query
            },
            dataType: 'json',
            success: function(data) {
                $select.empty();
                if (!data || data.length === 0) {
                    $select.append('<option value="" disabled selected>No se encontraron estudiantes disponibles</option>');
                    $('#estudiante_contador').text('0 estudiantes disponibles.');
                } else {
                    data.forEach(function(item) {
                        $select.append(
                            `<option value="${item.id}">[${item.dni}] ${item.nombre_completo} - ${item.programa_nombre || 'General'}</option>`
                        );
                    });
                    $('#estudiante_contador').text(`${data.length} estudiantes disponibles para asignar.`);
                }
            },
            error: function() {
                $select.html('<option value="" disabled selected>Error al cargar estudiantes</option>');
                $('#estudiante_contador').text('Error de conexión al cargar estudiantes.');
            }
        });
    }

    // Habilitar botón de asignación al seleccionar estudiante
    $('#select_estudiante').on('change', function() {
        $('#btn_submit_asignar').prop('disabled', !$(this).val());
    });

    $('#btn_buscar_est').on('click', cargarEstudiantesDisponibles);
    $('#buscar_estudiante').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            cargarEstudiantesDisponibles();
        }
    });
    $('#buscar_estudiante').on('input', function() {
        clearTimeout(window.searchEstTimer);
        window.searchEstTimer = setTimeout(cargarEstudiantesDisponibles, 350);
    });
    $('#filtro_programa').on('change', cargarEstudiantesDisponibles);

    $('#btn_abrir_asignar').on('click', function() {
        cargarEstudiantesDisponibles();
    });
    $('#modalAsignarEstudiante').on('show.bs.modal shown.bs.modal', function() {
        cargarEstudiantesDisponibles();
    });
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
