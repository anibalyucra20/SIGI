<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row mb-3">
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
        <?= $_SESSION['flash_success']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Tarjeta de Información del Tutor -->
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1"><strong>Docente Tutor:</strong> <?= htmlspecialchars($programacion['docente_formato']) ?></h5>
                <p class="text-muted mb-0">
                    <strong>DNI:</strong> <?= htmlspecialchars($programacion['docente_dni']) ?> |
                    <strong>Correo:</strong> <?= htmlspecialchars($programacion['docente_correo'] ?: 'No registrado') ?> |
                    <strong>Sede:</strong> <?= htmlspecialchars($programacion['sede_nombre']) ?> |
                    <strong>Periodo:</strong> <?= htmlspecialchars($programacion['periodo_nombre']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <span class="badge badge-light border p-2">
                    Tutorandos Asignados: <?= count($estudiantes) ?>
                </span>
                <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalNuevo/<?= $programacion['id'] ?>" class="btn btn-outline-secondary btn-sm ml-2" title="Crear Sesión Grupal">
                    <i class="fas fa-users mr-1"></i> Sesión Grupal
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Tutorandos Asignados -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Lista de Estudiantes Asignados</h5>
        <a href="<?= BASE_URL ?>/tutoria/programacion/asignarEstudiante/<?= $programacion['id'] ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus mr-1"></i> Asignar Estudiante
        </a>
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
                        <th width="100" class="text-center">Sesiones Ind.</th>
                        <th>Observación Inicial</th>
                        <th width="200" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estudiantes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay estudiantes asignados a este tutor todavía. Haz clic en <strong>+ Asignar Estudiante</strong> para comenzar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $i => $est): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($est['dni']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($est['nombre_completo']) ?></strong>
                                    <?php if (!empty($est['telefono'])): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($est['telefono']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?></td>
                                <td class="text-center">
                                    <span class="badge badge-light border">
                                        <?= (int)($est['total_sesiones_ind'] ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($est['observacion']) ? htmlspecialchars($est['observacion']) : '<span class="text-muted small">-</span>' ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individualNuevo/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-outline-primary btn-sm" title="Nueva Sesión Individual">
                                        <i class="fas fa-plus"></i> Sesión
                                    </a>
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-outline-secondary btn-sm ml-1" title="Ver Historial de Sesiones">
                                        <i class="fas fa-history"></i> Historial
                                    </a>
                                    <a href="<?= BASE_URL ?>/tutoria/fichas/ver/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-outline-info btn-sm ml-1" title="Ver Ficha Diagnóstica">
                                        <i class="fas fa-file-alt"></i> Ficha
                                    </a>
                                    <?php if ($esAdmin): ?>
                                        <a href="<?= BASE_URL ?>/tutoria/programacion/eliminarTutorando/<?= $est['id_tutoria_estudiante'] ?>/<?= $programacion['id'] ?>"
                                           class="btn btn-outline-danger btn-sm ml-1"
                                           onclick="return confirm('¿Está seguro de desvincular a este estudiante del tutor? Esta acción no borra las sesiones ya registradas.');"
                                           title="Desvincular del Tutor">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Conclusiones Generales del Tutor -->
<div class="card mt-3">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Conclusiones Generales de la Tutoría (Periodo)</h5>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/programacion/guardarConclusiones" method="POST">
            <input type="hidden" name="id_programacion" value="<?= $programacion['id'] ?>">
            <div class="form-group">
                <textarea name="conclusiones" class="form-control" rows="3" placeholder="Registre un resumen, balance o conclusiones finales de las tutorías realizadas en este periodo..."><?= htmlspecialchars($programacion['conclusiones'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i> Guardar Conclusiones
            </button>
        </form>
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
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
