<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row d-print-none">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Ficha de Sesión Grupal: <?= htmlspecialchars($sesion['tipo']) ?></h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/sesiones/grupalPdf/<?= $sesion['id'] ?>" target="_blank" class="btn btn-danger btn-sm mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Generar / Imprimir PDF (Anexo 04)
                </a>
                <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Lista
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
                <small class="text-muted">Registro y Acta de Sesión Grupal de Tutoría</small>
            </div>
            <div class="col-md-4 text-md-right">
                <span class="badge badge-info font-size-14 p-2"><?= htmlspecialchars($sesion['tipo']) ?></span><br>
                <small class="text-muted">Fecha: <?= date('d/m/Y H:i A', strtotime($sesion['fecha_hora'])) ?></small>
            </div>
        </div>

        <!-- Datos del Tutor y Periodo -->
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="140" class="text-muted font-weight-bold">Docente Tutor:</td>
                        <td><strong><?= htmlspecialchars($sesion['docente_formato']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Sede / Periodo:</td>
                        <td><?= htmlspecialchars($sesion['sede_nombre']) ?> - <?= htmlspecialchars($sesion['periodo_nombre']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6 border-left">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="140" class="text-muted font-weight-bold">Logro de Objetivo:</td>
                        <td>
                            <span class="badge badge-<?= ($sesion['logro_objetivo'] === 'Logrado') ? 'success' : 'warning' ?> p-1">
                                <?= htmlspecialchars($sesion['logro_objetivo']) ?>
                            </span>
                        </td>
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
                </table>
            </div>
        </div>

        <!-- Objetivo de la Sesión -->
        <div class="alert alert-light border mb-3">
            <strong class="text-dark"><i class="fas fa-bullseye mr-1 text-primary"></i> Objetivo Pedagógico:</strong>
            <p class="mb-0 mt-1"><?= htmlspecialchars($sesion['objetivo_sesion']) ?></p>
        </div>

        <!-- Contenidos Desarrollados -->
        <div class="mb-3">
            <h6 class="font-weight-bold text-dark border-bottom pb-1">1. Contenidos y Actividades Desarrolladas:</h6>
            <p class="text-justify mb-0"><?= nl2br(htmlspecialchars($sesion['contenidos'] ?: 'Sin detalle registrado.')) ?></p>
        </div>

        <!-- Asistencia de Estudiantes -->
        <div class="mb-3">
            <h6 class="font-weight-bold text-dark border-bottom pb-1">
                2. Nómina de Asistencia de Tutorandos (<?= count($asistentesIds) ?> de <?= count($estudiantes) ?> asistieron):
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th width="100">DNI</th>
                            <th>Apellidos y Nombres</th>
                            <th>Programa de Estudios</th>
                            <th width="120" class="text-center">Asistencia</th>
                            <th width="150" class="text-center d-print-table-cell d-none">Firma del Alumno</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estudiantes)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay estudiantes en nómina.</td></tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $i => $est):
                                $asistio = in_array((int)$est['id_estudiante'], $asistentesIds);
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($est['dni']) ?></td>
                                    <td><?= htmlspecialchars($est['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?></td>
                                    <td class="text-center">
                                        <?= $asistio ? '<span class="badge badge-success">Asistió</span>' : '<span class="badge badge-secondary">Faltó</span>' ?>
                                    </td>
                                    <td class="d-print-table-cell d-none" style="height: 30px;"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Observaciones y Recomendaciones -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="font-weight-bold text-dark border-bottom pb-1">3. Observaciones del Tutor:</h6>
                <p class="text-justify mb-0 small"><?= nl2br(htmlspecialchars($sesion['observaciones_sesion'] ?: 'Sin observaciones.')) ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="font-weight-bold text-dark border-bottom pb-1">4. Recomendaciones y Tareas:</h6>
                <p class="text-justify mb-0 small"><?= nl2br(htmlspecialchars($sesion['recomendaciones'] ?: 'Sin recomendaciones.')) ?></p>
            </div>
        </div>

        <!-- Firma -->
        <div class="row mt-5 pt-4 text-center d-print-block">
            <div class="col-12">
                <br><br>
                <div style="border-top: 1px solid #999; width: 40%; margin: 0 auto; padding-top: 5px;">
                    <strong>Firma y Sello del Docente Tutor</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($sesion['docente_formato']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
