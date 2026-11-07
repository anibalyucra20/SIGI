<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-archive mr-2"></i> Mantenimiento y Respaldo de Archivos (>5 Años)
        </h3>
        <p class="text-muted mb-0">Detección automática de expedientes antiguos, exportación comprimida en ZIP y depuración física del servidor.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <?php if (!empty($puedeEjecutar) && !empty($archivosCandidatos)): ?>
            <button type="button" class="btn btn-danger shadow-sm" data-toggle="modal" data-target="#modalEjecutarBackup">
                <i class="fas fa-file-archive mr-1"></i> Empaquetar en ZIP y Purgar Servidor
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_info'])): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-info-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_info']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_info']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Métricas de Almacenamiento -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted text-uppercase font-size-12 mb-1">Archivos Antiguos Detectados (> <?= (int)$anios ?> Años)</div>
                <h2 class="font-weight-bold text-danger mb-0"><?= (int)$totalArchivos ?></h2>
                <small class="text-muted">Elegibles para descarga y purga</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted text-uppercase font-size-12 mb-1">Espacio de Almacenamiento a Liberar</div>
                <h2 class="font-weight-bold text-primary mb-0"><?= htmlspecialchars($totalBytesFormato) ?></h2>
                <small class="text-muted">Reducción del uso de disco</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted text-uppercase font-size-12 mb-1">Respaldos Históricos Generados</div>
                <h2 class="font-weight-bold text-success mb-0"><?= count($historial) ?></h2>
                <small class="text-muted">Archivos ZIP preservados</small>
            </div>
        </div>
    </div>
</div>

<!-- Archivos Candidatos a Purga -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-clock mr-2 text-warning"></i> Archivos Activos con Antigüedad Mayor a <?= (int)$anios ?> Años
        </h5>
        <form method="GET" action="<?= BASE_URL ?>/efsrt/backups" class="form-inline">
            <label class="mr-2 font-size-13 text-muted">Criterio:</label>
            <select name="anios" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="1" <?= ($anios == 1) ? 'selected' : '' ?>>Más de 1 año</option>
                <option value="3" <?= ($anios == 3) ? 'selected' : '' ?>>Más de 3 años</option>
                <option value="5" <?= ($anios == 5) ? 'selected' : '' ?>>Más de 5 años (Estándar)</option>
                <option value="7" <?= ($anios == 7) ? 'selected' : '' ?>>Más de 7 años</option>
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-backups-candidatos">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Documento</th>
                        <th>Estudiante / DNI</th>
                        <th>Programa</th>
                        <th>Fecha de Subida</th>
                        <th class="text-right">Tamaño</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($archivosCandidatos)): ?>
                        <?php foreach ($archivosCandidatos as $idx => $arc): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($arc['tipo_documento']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($arc['nombre_archivo_original']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($arc['estudiante_nombre']) ?><br>
                                    <small class="text-muted">DNI: <?= htmlspecialchars($arc['estudiante_dni']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($arc['programa_codigo']) ?></span>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($arc['created_at'])) ?>
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-light border">
                                        <?= \App\Models\Efsrt\BackupLog::formatearBytes($arc['tamano_bytes']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Historial de Paquetes ZIP Generados -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-history mr-2 text-primary"></i> Historial de Paquetes ZIP Generados y Purgas Realizadas
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-backups-historial">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Fecha de Proceso</th>
                        <th>Archivo ZIP Generado</th>
                        <th>Archivos Comprimidos</th>
                        <th>Espacio Liberado</th>
                        <th>Ejecutado Por</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historial)): ?>
                        <?php foreach ($historial as $idx => $log): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= date('d/m/Y H:i', strtotime($log['fecha_proceso'])) ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-file-archive text-warning mr-1"></i>
                                    <code><?= htmlspecialchars($log['nombre_archivo_backup_zip']) ?></code>
                                </td>
                                <td>
                                    <span class="badge badge-light border font-weight-bold font-size-12 px-2 py-1">
                                        <?= (int)$log['total_archivos_procesados'] ?> archivos
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success"><?= \App\Models\Efsrt\BackupLog::formatearBytes($log['espacio_liberado_bytes']) ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="fas fa-user-shield mr-1"></i> <?= htmlspecialchars($log['ejecutado_por_nombre']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1">Exitoso</span>
                                </td>
                                <td class="text-right">
                                    <a href="<?= BASE_URL ?>/efsrt/backups/descargar/<?= $log['id'] ?>" class="btn btn-sm btn-primary shadow-sm" title="Descargar archivo ZIP">
                                        <i class="fas fa-download mr-1"></i> Descargar ZIP
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

<!-- Modal Confirmar Respaldo y Purga -->
<div class="modal fade" id="modalEjecutarBackup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/backups/ejecutar">
            <input type="hidden" name="anios" value="<?= (int)$anios ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Respaldo y Purga del Servidor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="font-size-14 text-dark">
                        Esta acción realizará las siguientes operaciones automáticas:
                    </p>
                    <ol class="font-size-13 text-muted pl-3">
                        <li>Empaquetará <strong><?= (int)$totalArchivos ?> documentos</strong> en un archivo comprimido <code>.ZIP</code> estructurado por carrera y estudiante.</li>
                        <li>Liberará <strong><?= htmlspecialchars($totalBytesFormato) ?></strong> de espacio físico en el disco duro del servidor.</li>
                        <li>Eliminará de forma segura los archivos físicos individuales para optimizar el almacenamiento del servidor.</li>
                        <li>El paquete ZIP generado permanecerá disponible para su descarga en cualquier momento.</li>
                    </ol>
                    <div class="alert alert-warning py-2 font-size-12 mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Asegúrese de descargar y custodiar el archivo ZIP una vez concluido el proceso.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Proceder con el Empaquetado y Purga
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

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
        if (!$.fn.DataTable.isDataTable('#tabla-backups-candidatos')) {
            $('#tabla-backups-candidatos').DataTable(dtConfig);
        }
        if (!$.fn.DataTable.isDataTable('#tabla-backups-historial')) {
            $('#tabla-backups-historial').DataTable(dtConfig);
        }
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
