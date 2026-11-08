<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row d-print-none">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Ficha de Atención Individual N° <?= htmlspecialchars($sesion['nro']) ?></h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/sesiones/individualPdf/<?= $sesion['id'] ?>" target="_blank" class="btn btn-danger btn-sm mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Generar / Imprimir PDF (Anexo 05)
                </a>
                <a href="<?= BASE_URL ?>/tutoria/sesiones/individuales/<?= $sesion['id_tutoria_estudiante'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border">
    <div class="card-body p-4">
        <!-- Encabezado Institucional -->
        <div class="row border-bottom pb-3 mb-3">
            <div class="col-md-8">
                <h5 class="text-primary font-weight-bold mb-1">INSTITUTO DE EDUCACIÓN SUPERIOR PEDAGÓGICO HUANTA</h5>
                <h6 class="text-dark font-weight-bold mb-0">UNIDAD DE TUTORÍA Y CONSEJERÍA ESTUDIANTIL</h6>
                <small class="text-muted">Registro Individual de Atención y Acompañamiento</small>
            </div>
            <div class="col-md-4 text-md-right">
                <span class="badge badge-primary font-size-14 p-2">Sesión N° <?= htmlspecialchars($sesion['nro']) ?></span><br>
                <small class="text-muted">Fecha: <?= date('d/m/Y H:i A', strtotime($sesion['fecha_hora'])) ?></small>
            </div>
        </div>

        <!-- Datos del Estudiante y Tutor -->
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="140" class="text-muted font-weight-bold">Estudiante:</td>
                        <td><strong><?= htmlspecialchars($sesion['estudiante_formato']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">DNI:</td>
                        <td><?= htmlspecialchars($sesion['estudiante_dni']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Programa:</td>
                        <td><?= htmlspecialchars($sesion['programa_nombre'] ?: 'General') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Contacto:</td>
                        <td><?= htmlspecialchars($sesion['estudiante_telefono'] ?: 'No registrado') ?> | <?= htmlspecialchars($sesion['estudiante_correo'] ?: 'No registrado') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6 border-left">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="140" class="text-muted font-weight-bold">Docente Tutor:</td>
                        <td><strong><?= htmlspecialchars($sesion['docente_formato']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Solicitante:</td>
                        <td><?= htmlspecialchars($sesion['solicitante']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Modalidad / Link:</td>
                        <td>
                            <?php if (!empty($sesion['link_reunion'])): ?>
                                <a href="<?= htmlspecialchars($sesion['link_reunion']) ?>" target="_blank" class="text-info"><?= htmlspecialchars($sesion['link_reunion']) ?></a>
                            <?php else: ?>
                                Presencial
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Asistencia:</td>
                        <td>
                            <?= ($sesion['asistencia'] == 1) ? '<span class="badge badge-success">Asistió</span>' : '<span class="badge badge-danger">Inasistencia</span>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Motivo -->
        <div class="alert alert-light border mb-3">
            <strong class="text-dark"><i class="fas fa-tag mr-1 text-primary"></i> Motivo de Atención:</strong>
            <p class="mb-0 mt-1"><?= htmlspecialchars($sesion['motivo']) ?></p>
        </div>

        <!-- Contenidos / Aspectos Tratados -->
        <div class="mb-3">
            <h6 class="font-weight-bold text-dark border-bottom pb-1">1. Aspectos y Temas Dialogados:</h6>
            <p class="text-justify mb-0"><?= nl2br(htmlspecialchars($sesion['contenidos_tratados'] ?: 'Sin detalle registrado.')) ?></p>
        </div>

        <!-- Recomendaciones del Tutor -->
        <div class="mb-3">
            <h6 class="font-weight-bold text-dark border-bottom pb-1">2. Orientaciones y Recomendaciones del Tutor:</h6>
            <p class="text-justify mb-0"><?= nl2br(htmlspecialchars($sesion['recomendaciones'] ?: 'Sin recomendaciones registradas.')) ?></p>
        </div>

        <!-- Compromisos del Estudiante -->
        <div class="mb-3">
            <h6 class="font-weight-bold text-dark border-bottom pb-1">3. Compromisos Asumidos por el Estudiante:</h6>
            <p class="text-justify mb-0 font-italic text-primary"><?= nl2br(htmlspecialchars($sesion['compromiso_estudiante'] ?: 'Sin compromisos registrados.')) ?></p>
        </div>

        <!-- Seguimiento y Derivación -->
        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-6 mb-2">
                <strong><i class="fas fa-clock mr-1 text-info"></i> Seguimiento:</strong>
                <?php if ($sesion['seguimiento'] == 1): ?>
                    <span class="badge badge-info">Programado</span>
                    <?php if (!empty($sesion['fecha_prox_sesion']) && $sesion['fecha_prox_sesion'] != '2000-01-01 00:00:00'): ?>
                        <br><small class="text-muted">Próxima sesión: <?= date('d/m/Y H:i A', strtotime($sesion['fecha_prox_sesion'])) ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">No requiere seguimiento próximo</span>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mb-2">
                <strong><i class="fas fa-share-square mr-1 text-warning"></i> Derivación:</strong>
                <?php if ($sesion['derivacion'] == 1): ?>
                    <span class="badge badge-warning">Derivado a <?= htmlspecialchars($sesion['servicio_derivacion']) ?></span>
                <?php else: ?>
                    <span class="text-muted">Caso resuelto en tutoría (No derivado)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Firmas para Impresión -->
        <div class="row mt-5 pt-4 text-center d-print-block">
            <div class="col-6">
                <br><br>
                <div style="border-top: 1px solid #999; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Firma del Docente Tutor</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($sesion['docente_formato']) ?></small>
                </div>
            </div>
            <div class="col-6">
                <br><br>
                <div style="border-top: 1px solid #999; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Firma del Estudiante</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($sesion['estudiante_formato']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
