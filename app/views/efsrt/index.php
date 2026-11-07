<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-graduation-cap mr-2"></i> Módulo de Experiencias Formativas en Situaciones Reales de Trabajo (EFSRT)
        </h3>
        <p class="text-muted mb-0">
            Periodo Académico: <span class="badge badge-primary px-2 py-1 font-size-13"><?= htmlspecialchars($periodo_nombre) ?></span>
            | Rol actual: <span class="badge badge-dark px-2 py-1"><?= htmlspecialchars($_SESSION['sigi_rol_actual_nombre'] ?? 'Usuario') ?></span>
        </p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if ($esEstudiante): ?>
            <a href="<?= BASE_URL ?>/efsrt/practicas/crear" class="btn btn-success shadow-sm">
                <i class="fas fa-plus-circle mr-1"></i> Registrar Nueva Práctica
            </a>
        <?php elseif ($esAdmin || $esCoordinador || $esSecretario): ?>
            <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-primary shadow-sm">
                <i class="fas fa-folder-open mr-1"></i> Ver Todos los Expedientes
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. VISTA DE ESTUDIANTE                      -->
<!-- ========================================== -->
<?php if ($esEstudiante): ?>
    
    <!-- Banner de Estado de Ventana de Subida -->
    <?php if (!empty($cronograma)): ?>
        <div class="alert <?= $abiertoEstudiante ? 'alert-info' : 'alert-warning' ?> d-flex align-items-center shadow-sm" role="alert">
            <i class="fas <?= $abiertoEstudiante ? 'fa-calendar-check' : 'fa-calendar-times' ?> fa-2x mr-3"></i>
            <div>
                <h5 class="alert-heading mb-1 font-weight-bold">
                    <?= $abiertoEstudiante ? 'Plazo de Subida de Documentos Habilitado' : 'Plazo de Subida de Documentos Cerrado' ?>
                </h5>
                <p class="mb-0 font-size-13">
                    Recepción de documentos para estudiantes: 
                    <strong><?= date('d/m/Y H:i', strtotime($cronograma['fecha_apertura_estudiante'])) ?></strong> 
                    hasta <strong><?= date('d/m/Y H:i', strtotime($cronograma['fecha_cierre_estudiante'])) ?></strong>.
                    <?= $abiertoEstudiante ? '<span class="text-success font-weight-bold ml-2"><i class="fas fa-check-circle"></i> Puedes subir y actualizar tus documentos escaneados.</span>' : '<span class="text-danger font-weight-bold ml-2"><i class="fas fa-lock"></i> La plataforma no admite nuevas subidas en este momento.</span>' ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="avatar-sm mx-auto mb-3 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 50px; height: 50px;">
                        <i class="fas fa-folder fa-2x"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2">Mis Prácticas Registradas</h6>
                    <h2 class="font-weight-bold mb-0 text-dark"><?= count($misPracticas) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="avatar-sm mx-auto mb-3 bg-soft-success rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 50px; height: 50px;">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2">Finalizadas / Emitidas</h6>
                    <h2 class="font-weight-bold mb-0 text-success">
                        <?= count(array_filter($misPracticas, function($p){ return $p['estado'] === 'FINALIZADO_EMITIDO'; })) ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="avatar-sm mx-auto mb-3 bg-soft-warning rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 50px; height: 50px;">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2">En Proceso / Trámite</h6>
                    <h2 class="font-weight-bold mb-0 text-warning">
                        <?= count(array_filter($misPracticas, function($p){ return $p['estado'] !== 'FINALIZADO_EMITIDO'; })) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de mis expedientes -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-list-ul mr-2 text-primary"></i> Mis Expedientes de EFSRT
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($misPracticas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5>No tienes expedientes de EFSRT registrados aún</h5>
                    <p class="text-muted">Inicia tu registro seleccionando el módulo formativo y la empresa donde realizas tus prácticas.</p>
                    <a href="<?= BASE_URL ?>/efsrt/practicas/crear" class="btn btn-primary mt-2">
                        <i class="fas fa-plus mr-1"></i> Registrar Primera Práctica
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-mis-practicas">
                        <thead class="bg-light">
                            <tr>
                                <th>Módulo</th>
                                <th>Empresa / Institución</th>
                                <th>Periodo</th>
                                <th>Docente Supervisor</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Constancia</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($misPracticas as $mp): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary">Módulo <?= htmlspecialchars($mp['modulo_nro']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($mp['modulo_nombre']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($mp['empresa_razon_social']) ?></strong><br>
                                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($mp['empresa_direccion']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">
                                            <?= date('d/m/Y', strtotime($mp['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($mp['fecha_fin'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($mp['supervisor_nombre']) ? htmlspecialchars($mp['supervisor_nombre']) : '<span class="text-muted font-italic">Pendiente asignación</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'badge-secondary';
                                        $estadoLabel = $mp['estado'];
                                        if ($mp['estado'] === 'FINALIZADO_EMITIDO') { $badgeClass = 'badge-success'; $estadoLabel = 'Constancia Emitida'; }
                                        elseif ($mp['estado'] === 'DOCUMENTACION_SUBIDA') { $badgeClass = 'badge-info'; $estadoLabel = 'Docs Subidos'; }
                                        elseif (strpos($mp['estado'], 'OBSERVADO') !== false) { $badgeClass = 'badge-danger'; $estadoLabel = 'Observado'; }
                                        elseif (strpos($mp['estado'], 'DERIVADO') !== false) { $badgeClass = 'badge-warning'; $estadoLabel = 'En Revisión / Derivado'; }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> px-2 py-1 font-size-12">
                                            <?= htmlspecialchars($estadoLabel) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($mp['nro_constancia'])): ?>
                                            <a href="<?= BASE_URL ?>/efsrt/constancias/descargar/<?= $mp['id'] ?>" class="btn btn-sm btn-success shadow-sm" title="Descargar Constancia Oficial [<?= $mp['constancia_archivo'] ?>]">
                                                <i class="fas fa-file-pdf mr-1"></i> Descargar
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted font-size-12">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= BASE_URL ?>/efsrt/practicas/detalle/<?= $mp['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye mr-1"></i> Ver Expediente
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- ========================================== -->
<!-- 2. VISTA DE SUPERVISOR / DOCENTE            -->
<!-- ========================================== -->
<?php elseif ($esDocente && !$esCoordinador && !$esAdmin && !$esSecretario): ?>

    <div class="row">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Total Asignados</h6>
                    <h2 class="font-weight-bold text-dark mb-0"><?= (int)($estadisticas['total_practicas'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Por Evaluar / Docs Subidos</h6>
                    <h2 class="font-weight-bold text-primary mb-0"><?= (int)($estadisticas['total_por_revisar_supervisor'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Observados</h6>
                    <h2 class="font-weight-bold text-danger mb-0"><?= (int)($estadisticas['total_obs_supervisor'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Aprobados / Derivados</h6>
                    <h2 class="font-weight-bold text-success mb-0"><?= (int)($estadisticas['total_por_revisar_coordinador'] ?? 0) + (int)($estadisticas['total_finalizados'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de practicantes que requieren atención -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-user-check mr-2 text-primary"></i> Practicantes Pendientes de Supervisión y V°B°
            </h5>
            <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-sm btn-outline-primary">
                Ver Todos mis Asignados
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pendientesAccion)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>¡Excelente trabajo! No tienes expedientes pendientes de revisión.</h5>
                    <p class="text-muted">Todos tus estudiantes asignados están al día o no han subido documentación aún.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-pendientes-supervisor">
                        <thead class="bg-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>Programa / Módulo</th>
                                <th>Empresa</th>
                                <th>Docs Subidos</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientesAccion as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($p['estudiante_nombre']) ?></strong><br>
                                        <small class="text-muted">DNI: <?= htmlspecialchars($p['estudiante_dni']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($p['programa_codigo']) ?></span>
                                        <strong>Módulo <?= htmlspecialchars($p['modulo_nro']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($p['modulo_nombre']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['empresa_razon_social']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">
                                            <?= $p['total_documentos'] ?> documento(s)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning px-2 py-1 font-size-12">
                                            <?= htmlspecialchars($p['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= BASE_URL ?>/efsrt/practicas/detalle/<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-clipboard-check mr-1"></i> Revisar y Validar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- ========================================== -->
<!-- 3. VISTA DE COORDINADOR, SECRETARIO, ADMIN -->
<!-- ========================================== -->
<?php else: ?>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Expedientes</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?= (int)($estadisticas['total_practicas'] ?? 0) ?></div>
                        </div>
                        <div class="text-primary"><i class="fas fa-folder-open fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?= $esCoordinador && !$esSecretario ? 'Pendiente V°B° Coord.' : 'Pendiente Emisión Secr.' ?>
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?= $esCoordinador && !$esSecretario ? (int)($estadisticas['total_por_revisar_coordinador'] ?? 0) : (int)($estadisticas['total_por_revisar_secretaria'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="text-warning"><i class="fas fa-stamp fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Constancias Emitidas</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?= (int)($estadisticas['total_finalizados'] ?? 0) ?></div>
                        </div>
                        <div class="text-success"><i class="fas fa-award fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Empresas / Convenios</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800"><?= $totalEmpresas ?> / <?= $totalConvenios ?></div>
                        </div>
                        <div class="text-info"><i class="fas fa-handshake fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expedientes pendientes de visto bueno según el nivel -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 font-weight-bold text-dark">
                <i class="fas fa-tasks mr-2 text-primary"></i> 
                <?= $esCoordinador && !$esSecretario ? 'Expedientes Derivados por Supervisores para V°B° de Carrera' : 'Expedientes Derivados a Secretaría para Emisión Oficial de Constancia' ?>
            </h5>
            <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-sm btn-outline-primary">
                Ver Todos los Expedientes
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pendientesAccion)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No hay expedientes pendientes de atención en esta bandeja.</h5>
                    <p class="text-muted">Todos los expedientes derivados han sido atendidos o están en etapas previas.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-pendientes-coordinador">
                        <thead class="bg-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>Programa de Estudios</th>
                                <th>Módulo</th>
                                <th>Empresa</th>
                                <th>Docente Supervisor</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientesAccion as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($p['estudiante_nombre']) ?></strong><br>
                                        <small class="text-muted">DNI: <?= htmlspecialchars($p['estudiante_dni']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($p['programa_codigo']) ?></span>
                                        <small class="text-muted d-block"><?= htmlspecialchars($p['programa_nombre']) ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-primary">Módulo <?= htmlspecialchars($p['modulo_nro']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($p['modulo_nombre']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['empresa_razon_social']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['supervisor_nombre'] ?? 'Sin supervisor') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning px-2 py-1 font-size-12">
                                            <?= htmlspecialchars($p['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= BASE_URL ?>/efsrt/practicas/detalle/<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-folder-open mr-1"></i> Abrir Expediente
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enlaces directos a gestión rápida -->
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <a href="<?= BASE_URL ?>/efsrt/supervisores" class="card border-0 shadow-sm text-decoration-none text-dark hover-shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-primary"><i class="fas fa-chalkboard-teacher fa-2x"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">Supervisores</h6>
                        <small class="text-muted">Asignar docentes</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="<?= BASE_URL ?>/efsrt/convenios" class="card border-0 shadow-sm text-decoration-none text-dark hover-shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-success"><i class="fas fa-file-contract fa-2x"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">Convenios</h6>
                        <small class="text-muted">Empresas e instituciones</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="<?= BASE_URL ?>/efsrt/constancias" class="card border-0 shadow-sm text-decoration-none text-dark hover-shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-info"><i class="fas fa-certificate fa-2x"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">Constancias</h6>
                        <small class="text-muted">Descarga y emisión oficial</small>
                    </div>
                </div>
            </a>
        </div>
        <?php if ($esAdmin || $esDirector || $esSecretario): ?>
        <div class="col-md-3 mb-3">
            <a href="<?= BASE_URL ?>/efsrt/backups" class="card border-0 shadow-sm text-decoration-none text-dark hover-shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-warning"><i class="fas fa-archive fa-2x"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">Mantenimiento</h6>
                        <small class="text-muted">Purga y ZIP (>5 años)</small>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dtConfig = {
        pageLength: 10,
        responsive: true,
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    };

    if (window.jQuery && $.fn.DataTable) {
        if (!$.fn.DataTable.isDataTable('#tabla-mis-practicas')) {
            $('#tabla-mis-practicas').DataTable(dtConfig);
        }
        if (!$.fn.DataTable.isDataTable('#tabla-pendientes-supervisor')) {
            $('#tabla-pendientes-supervisor').DataTable(dtConfig);
        }
        if (!$.fn.DataTable.isDataTable('#tabla-pendientes-coordinador')) {
            $('#tabla-pendientes-coordinador').DataTable(dtConfig);
        }
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>