<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18"><i class="fas fa-clipboard-list text-primary mr-2"></i> Gestión de Encuestas de Evaluación Docente</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas/crear" class="btn btn-primary font-weight-bold shadow-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Nueva Encuesta
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 mb-2 border-bottom">
                    <div>
                        <span class="badge badge-primary px-3 py-2 font-size-13">
                            <i class="fas fa-calendar-alt mr-1"></i> Periodo Académico (Contexto Activo): <strong><?= htmlspecialchars($periodoActualNombre) ?></strong>
                        </span>
                        <?php if (!$puedeVerTodos): ?>
                            <span class="badge badge-info px-2 py-2 font-size-12 ml-2">
                                <i class="fas fa-user-shield mr-1"></i> Vista de Coordinador: Solo carreras asignadas
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="GET" action="<?= BASE_URL ?>/evaluacion/encuestas" class="form-row align-items-center">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted mb-0">Programa de Estudios:</label>
                        <select name="id_programa" class="form-control form-control-sm" onchange="this.form.submit()">
                            <?php if ($puedeVerTodos): ?>
                                <option value="">-- Todos los Programas de Estudios --</option>
                            <?php endif; ?>
                            <?php foreach ($programas as $prg): ?>
                                <option value="<?= $prg['id'] ?>" <?= $prg['id'] == $filtroPrograma ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prg['codigo'] . ' - ' . $prg['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 text-md-right mt-3 mt-md-0">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-sm btn-info px-3">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-undo mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Encuestas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list-alt text-primary mr-2"></i> Encuestas Programadas
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($encuestas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-check font-size-48 text-muted mb-3"></i>
                        <h5 class="text-muted">No hay encuestas registradas para los filtros seleccionados.</h5>
                        <a href="<?= BASE_URL ?>/evaluacion/encuestas/crear" class="btn btn-primary mt-2">
                            <i class="fas fa-plus mr-1"></i> Crear la Primera Encuesta
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="tablaEncuestas">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Título de la Encuesta</th>
                                    <th>Periodo</th>
                                    <th>Vigencia</th>
                                    <th>Estado</th>
                                    <th class="text-center">Evaluaciones</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($encuestas as $enc): ?>
                                    <tr>
                                        <td class="font-weight-bold text-muted"><?= $enc['id'] ?></td>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block font-size-14">
                                                <?= htmlspecialchars($enc['titulo']) ?>
                                            </span>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($enc['programa_nombre'] ?? 'Todos los Programas de Estudios') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border font-weight-semibold">
                                                <?= htmlspecialchars($enc['periodo_nombre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">
                                                <i class="far fa-calendar-check text-success mr-1"></i> Inicio: <?= date('d/m/Y', strtotime($enc['fecha_inicio'])) ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="far fa-calendar-times text-danger mr-1"></i> Cierre: <?= date('d/m/Y', strtotime($enc['fecha_fin'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($enc['estado'] === 'ACTIVA'): ?>
                                                <span class="badge badge-success px-2 py-1">ACTIVA</span>
                                            <?php elseif ($enc['estado'] === 'BORRADOR'): ?>
                                                <span class="badge badge-warning px-2 py-1 text-dark">BORRADOR</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">CERRADA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="font-weight-bold text-success font-size-14"><?= $enc['total_completadas'] ?></span>
                                            <span class="text-muted">/ <?= $enc['total_asignadas'] ?></span>
                                            <?php if ($enc['total_pendientes'] > 0): ?>
                                                <small class="text-warning d-block font-weight-bold font-size-11">
                                                    (<?= $enc['total_pendientes'] ?> pendientes)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <?php 
                                            $puedeGestionar = $puedeVerTodos || (!empty($enc['id_programa_estudios']) && in_array($enc['id_programa_estudios'], $idsPermitidosCoord)); 
                                            ?>
                                            <?php if ($puedeGestionar): ?>
                                                <!-- Botón Generar Asignaciones -->
                                                <a href="<?= BASE_URL ?>/evaluacion/encuestas/generarAsignaciones/<?= $enc['id'] ?>" 
                                                   class="btn btn-sm btn-outline-success mr-1" 
                                                   title="Generar Asignaciones automáticas para alumnos matriculados"
                                                   onclick="return confirm('¿Deseas escanear y generar las asignaciones de evaluación para los estudiantes matriculados en este periodo?');">
                                                    <i class="fas fa-users-cog mr-1"></i> Asignar Alumnos
                                                </a>

                                                <!-- Botón Criterios / Rúbrica -->
                                                <a href="<?= BASE_URL ?>/evaluacion/encuestas/criterios/<?= $enc['id'] ?>" 
                                                   class="btn btn-sm btn-outline-info mr-1" 
                                                   title="Gestionar Criterios / Dimensiones">
                                                    <i class="fas fa-layer-group"></i> Criterios
                                                </a>

                                                <!-- Botón Preguntas -->
                                                <a href="<?= BASE_URL ?>/evaluacion/encuestas/preguntas/<?= $enc['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary mr-1" 
                                                   title="Gestionar Preguntas de la encuesta">
                                                    <i class="fas fa-question-circle"></i> Preguntas
                                                </a>

                                                <!-- Botón Editar -->
                                                <a href="<?= BASE_URL ?>/evaluacion/encuestas/editar/<?= $enc['id'] ?>" 
                                                   class="btn btn-sm btn-secondary mr-1" 
                                                   title="Editar Configuración">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botón Eliminar -->
                                                <a href="<?= BASE_URL ?>/evaluacion/encuestas/eliminar/<?= $enc['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   title="Eliminar Encuesta"
                                                   onclick="return confirm('¿Estás seguro de eliminar esta encuesta? Si tiene evaluaciones asignadas también se eliminarán.');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-light border text-muted px-2 py-1 font-size-12">
                                                    <i class="fas fa-lock mr-1"></i> Solo lectura
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable && $('#tablaEncuestas').length) {
        $('#tablaEncuestas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
