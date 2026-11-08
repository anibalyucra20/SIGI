<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row d-print-none">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Ficha Diagnóstica y Recojo de Información</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/fichas/editar/<?= $tutorando['id'] ?>" class="btn btn-warning btn-sm mr-2">
                    <i class="fas fa-edit mr-1"></i> Modificar Respuestas
                </a>
                <button type="button" class="btn btn-primary btn-sm mr-2" onclick="window.print();">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
                <a href="<?= BASE_URL ?>/tutoria/tutores/fichas?id_programacion=<?= $tutorando['id_programacion_tutoria'] ?>" class="btn btn-secondary btn-sm">
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
                <small class="text-muted">Ficha de Diagnóstico Inicial y Recojo de Información Socioeducativa</small>
            </div>
            <div class="col-md-4 text-md-right">
                <span class="badge badge-success font-size-14 p-2">Ficha de Tutorando</span>
            </div>
        </div>

        <!-- Datos del Alumno -->
        <div class="card bg-light border-0 mb-4 p-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="140" class="text-muted font-weight-bold">Estudiante:</td>
                            <td><strong><?= htmlspecialchars($tutorando['nombre_completo']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">DNI:</td>
                            <td><?= htmlspecialchars($tutorando['dni']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Programa:</td>
                            <td><?= htmlspecialchars($tutorando['programa_nombre'] ?: 'General') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 border-left">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="140" class="text-muted font-weight-bold">Correo:</td>
                            <td><?= htmlspecialchars($tutorando['correo'] ?: 'No registrado') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Teléfono:</td>
                            <td><?= htmlspecialchars($tutorando['telefono'] ?: 'No registrado') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Observación Inicial:</td>
                            <td><?= htmlspecialchars($tutorando['observacion'] ?: 'Sin observaciones registradas.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lista de Criterios Diagnósticos -->
        <h6 class="font-weight-bold text-dark mb-3">Resultados del Recojo de Información (Preguntas de Diagnóstico):</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Aspecto / Pregunta Diagnosticada</th>
                        <th width="160" class="text-center">Respuesta / Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="text-center font-weight-bold"><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($item['descripcion']) ?></td>
                            <td class="text-center">
                                <?php if ($item['valor'] == 1): ?>
                                    <span class="badge badge-success px-3 py-1 font-size-12"><i class="fas fa-check mr-1"></i>SÍ</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-3 py-1 font-size-12"><i class="fas fa-times mr-1"></i>NO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Firmas para Impresión -->
        <div class="row mt-5 pt-4 text-center d-print-block">
            <div class="col-6">
                <br><br>
                <div style="border-top: 1px solid #999; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Firma del Docente Tutor</strong><br>
                    <small class="text-muted">Responsable de Tutoría</small>
                </div>
            </div>
            <div class="col-6">
                <br><br>
                <div style="border-top: 1px solid #999; width: 80%; margin: 0 auto; padding-top: 5px;">
                    <strong>Firma del Estudiante</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($tutorando['nombre_completo']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
