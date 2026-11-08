<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Fichas de Diagnóstico y Recojo de Información</h4>
            <div class="page-title-right">
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

<!-- Selector de Tutor (para Admin) -->
<?php if ($esAdmin): ?>
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/tutoria/tutores/fichas" class="form-inline">
            <label for="select_prog" class="mr-2 font-weight-bold">Seleccionar Tutor Asignado:</label>
            <select name="id_programacion" id="select_prog" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">-- Seleccionar Tutor --</option>
                <?php foreach ($tutores as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($id_programacion == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['docente_nombre']) ?> (<?= $t['total_estudiantes'] ?> tutorandos)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($progActual): ?>
    <!-- Información del Tutor Seleccionado -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2 bg-light">
            <strong class="text-primary font-size-15"><i class="fas fa-chalkboard-teacher mr-2"></i>Tutor: <?= htmlspecialchars($progActual['docente_formato']) ?></strong>
            <span class="text-muted ml-2">| Sede: <?= htmlspecialchars($progActual['sede_nombre']) ?> | Periodo: <?= htmlspecialchars($progActual['periodo_nombre']) ?></span>
        </div>
    </div>

    <!-- Tabla de Estudiantes y Estado de Ficha Diagnóstica -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Tutorandos Asignados y Estado del Diagnóstico (<?= count($estudiantes) ?>)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-fichas" class="table table-bordered table-hover table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th width="100">DNI</th>
                            <th>Apellidos y Nombres</th>
                            <th>Programa de Estudios</th>
                            <th width="140" class="text-center">Estado Ficha</th>
                            <th width="160" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estudiantes)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No hay estudiantes asignados a este tutor.</td></tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $i => $est): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><span class="badge badge-light border"><?= htmlspecialchars($est['dni']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($est['nombre_completo']) ?></strong></td>
                                    <td><?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?></td>
                                    <td class="text-center">
                                        <?php if ($est['total_fichas'] > 0): ?>
                                            <span class="badge badge-success p-1"><i class="fas fa-check-circle mr-1"></i>Completada</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning p-1"><i class="fas fa-clock mr-1"></i>Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/tutoria/fichas/ver/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-info btn-sm" title="Ver Ficha Diagnóstica">
                                            <i class="fas fa-eye mr-1"></i> Diagnóstico
                                        </a>
                                        <a href="<?= BASE_URL ?>/tutoria/fichas/editar/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-primary btn-sm ml-1" title="Llenar o Editar Diagnóstico">
                                            <i class="fas fa-edit mr-1"></i> <?= $est['total_fichas'] > 0 ? 'Editar' : 'Llenar' ?>
                                        </a>
                                        <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $est['id_tutoria_estudiante'] ?>" class="btn btn-warning btn-sm ml-1" title="Ver Historial de Fichas Individuales">
                                            <i class="fas fa-user-clock mr-1"></i> Sesiones (<?= $est['total_sesiones_ind'] ?? 0 ?>)
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
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i> Seleccione un tutor asignado arriba para visualizar y gestionar las fichas diagnósticas de sus estudiantes.
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#tabla-fichas')) {
        $('#tabla-fichas').DataTable().destroy();
    }
    $('#tabla-fichas').DataTable({
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });
});
</script>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
