<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        <!-- Banner de Confirmación Exitosa -->
        <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #28a745 !important;">
            <div class="card-body p-4 text-center">
                <div class="avatar-md mx-auto mb-3 bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                    <i class="fas fa-check-circle display-4"></i>
                </div>
                <h3 class="font-weight-bold text-dark mb-1">¡Evaluación Docente Registrada!</h3>
                <p class="text-muted mb-2 font-size-15">
                    Tus respuestas han sido procesadas de forma confidencial y segura.
                </p>
                <div class="alert alert-info d-inline-block px-4 py-2 mb-0 font-size-13 text-left">
                    <i class="fas fa-lock mr-2"></i> <strong>Inmutabilidad garantizada:</strong> Esta evaluación fue finalizada el 
                    <strong><?= date('d/m/Y H:i:s', strtotime($evaluacion['fecha_completado'] ?? 'now')) ?></strong> y ya no puede ser modificada.
                </div>
            </div>
        </div>

        <!-- Ficha de la Evaluación -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-id-card text-primary mr-2"></i> Datos de la Evaluación Realizada
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold">Docente Evaluado:</small>
                        <span class="font-size-15 font-weight-bold text-dark">
                            <i class="fas fa-user-tie text-primary mr-1"></i> <?= htmlspecialchars($evaluacion['docente_nombre']) ?>
                        </span>
                        <small class="text-muted d-block">DNI: <?= htmlspecialchars($evaluacion['docente_dni']) ?></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold">Unidad Didáctica:</small>
                        <span class="font-size-15 font-weight-bold text-dark">
                            <i class="fas fa-book text-info mr-1"></i> <?= htmlspecialchars($evaluacion['unidad_didactica_nombre']) ?>
                        </span>
                        <small class="text-muted d-block">Semestre <?= htmlspecialchars($evaluacion['semestre_romano']) ?> - Sec. <?= htmlspecialchars($evaluacion['seccion']) ?></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold">Programa de Estudios:</small>
                        <span class="text-dark"><?= htmlspecialchars($evaluacion['programa_nombre']) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold">Notificación por Correo:</small>
                        <?php if (!empty($evaluacion['notificado_email'])): ?>
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-envelope-check mr-1"></i> Constancia enviada por correo</span>
                        <?php else: ?>
                            <span class="badge badge-light border text-muted"><i class="fas fa-envelope mr-1"></i> Registrado en sistema</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($evaluacion['comentario_general'])): ?>
                    <hr class="my-2">
                    <div class="mt-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold mb-1">Comentario / Sugerencia enviada:</small>
                        <div class="bg-light p-3 rounded border text-dark font-italic">
                            "<?= htmlspecialchars($evaluacion['comentario_general']) ?>"
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de Respuestas Emitidas -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-list-ol text-primary mr-2"></i> Resumen de Respuestas Emitidas
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="75%">Pregunta / Indicador</th>
                            <th width="25%" class="text-center">Valoración Asignada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $etiquetas = [
                            1 => ['txt' => 'Deficiente', 'badge' => 'badge-danger'],
                            2 => ['txt' => 'Regular', 'badge' => 'badge-warning text-dark'],
                            3 => ['txt' => 'Bueno', 'badge' => 'badge-info'],
                            4 => ['txt' => 'Muy Bueno', 'badge' => 'badge-primary'],
                            5 => ['txt' => 'Excelente', 'badge' => 'badge-success']
                        ];
                        ?>
                        <?php foreach ($respuestas as $r): ?>
                            <tr>
                                <td>
                                    <span class="d-block font-weight-semibold text-dark"><?= htmlspecialchars($r['enunciado']) ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($r['criterio_nombre']) ?></small>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($r['puntaje'] !== null): ?>
                                        <span class="badge <?= $etiquetas[$r['puntaje']]['badge'] ?? 'badge-secondary' ?> px-3 py-2 font-size-13 font-weight-bold">
                                            <?= $r['puntaje'] ?> - <?= $etiquetas[$r['puntaje']]['txt'] ?? '' ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted"><?= htmlspecialchars($r['respuesta_texto']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mb-5">
            <a href="<?= BASE_URL ?>/evaluacion/misEvaluaciones" class="btn btn-primary btn-lg px-5 font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Evaluaciones
            </a>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
