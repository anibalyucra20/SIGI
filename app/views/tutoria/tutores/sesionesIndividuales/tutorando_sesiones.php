<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Historial de Sesiones Individuales</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/<?= $tutorando['id_programacion_tutoria'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Tutorandos
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

<!-- Tarjeta del Tutorando -->
<div class="card shadow-sm border-left-primary mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="text-primary mb-1">
                    <i class="fas fa-user-graduate mr-2"></i><?= htmlspecialchars($tutorando['nombre_completo']) ?>
                </h5>
                <p class="text-muted mb-0">
                    <strong>DNI:</strong> <?= htmlspecialchars($tutorando['dni']) ?> |
                    <strong>Programa:</strong> <?= htmlspecialchars($tutorando['programa_nombre'] ?: 'General') ?> |
                    <strong>Correo:</strong> <?= htmlspecialchars($tutorando['correo'] ?: 'No registrado') ?> |
                    <strong>Teléfono:</strong> <?= htmlspecialchars($tutorando['telefono'] ?: 'No registrado') ?>
                </p>
                <?php if (!empty($tutorando['observacion'])): ?>
                    <p class="mt-2 mb-0 small text-dark bg-light p-2 rounded">
                        <strong><i class="fas fa-info-circle text-info mr-1"></i>Diagnóstico previo / Observación:</strong> <?= htmlspecialchars($tutorando['observacion']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>/tutoria/sesiones/individualNuevo/<?= $tutorando['id'] ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus mr-1"></i> Nueva Sesión Individual
                </a>
                <a href="<?= BASE_URL ?>/tutoria/fichas/ver/<?= $tutorando['id'] ?>" class="btn btn-outline-info btn-sm ml-1" title="Ver Ficha Diagnóstica">
                    <i class="fas fa-clipboard-list mr-1"></i> Ficha
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Sesiones Individuales -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Sesiones Individuales Realizadas (<?= count($sesiones) ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="40" class="text-center">N°</th>
                        <th width="120">Fecha / Hora</th>
                        <th width="110">Solicitante</th>
                        <th>Motivo de Atención</th>
                        <th>Compromiso del Estudiante</th>
                        <th width="100" class="text-center">Seguimiento</th>
                        <th width="110" class="text-center">Derivación</th>
                        <th width="140" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sesiones)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times mr-1"></i> No se han registrado sesiones individuales con este estudiante aún.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sesiones as $s): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $s['nro'] ?></td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($s['fecha_hora'])) ?></strong><br>
                                    <small class="text-muted"><?= date('H:i a', strtotime($s['fecha_hora'])) ?></small>
                                </td>
                                <td><span class="badge badge-light border"><?= htmlspecialchars($s['solicitante']) ?></span></td>
                                <td><strong><?= htmlspecialchars($s['motivo']) ?></strong></td>
                                <td>
                                    <small><?= htmlspecialchars($s['compromiso_estudiante'] ?: 'Sin compromisos registrados') ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['seguimiento'] == 1): ?>
                                        <span class="badge badge-info p-1"><i class="fas fa-clock mr-1"></i>Sí</span>
                                        <?php if (!empty($s['fecha_prox_sesion']) && $s['fecha_prox_sesion'] != '2000-01-01 00:00:00'): ?>
                                            <br><small class="text-muted"><?= date('d/m/Y', strtotime($s['fecha_prox_sesion'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-light border text-muted">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['derivacion'] == 1): ?>
                                        <span class="badge badge-warning p-1">
                                            <i class="fas fa-share-square mr-1"></i><?= htmlspecialchars($s['servicio_derivacion'] ?: 'Derivado') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-light border text-muted">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individualPdf/<?= $s['id'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Descargar / Imprimir PDF (Anexo 05)">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individualVer/<?= $s['id'] ?>" class="btn btn-info btn-sm" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individualEditar/<?= $s['id'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/tutoria/sesiones/individualEliminar/<?= $s['id'] ?>/<?= $tutorando['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta sesión?');" title="Eliminar">
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

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
