<?php require __DIR__ . '/../../layouts/header.php'; ?>

<!-- Encabezado del Expediente -->
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/efsrt">EFSRT</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/efsrt/practicas">Expedientes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Expediente #<?= $practica['id'] ?></li>
            </ol>
        </nav>
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-folder-open mr-2"></i> Expediente Digital de EFSRT
        </h3>
        <p class="text-muted mb-0">
            Estudiante: <strong class="text-dark font-size-15"><?= htmlspecialchars($practica['estudiante_nombre']) ?></strong>
            | DNI: <span class="badge badge-light border"><?= htmlspecialchars($practica['estudiante_dni']) ?></span>
            | Carrera: <span class="badge badge-info"><?= htmlspecialchars($practica['programa_codigo']) ?></span>
        </p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-secondary shadow-sm mr-1">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <?php if (!empty($constancia)): ?>
            <a href="<?= BASE_URL ?>/efsrt/constancias/descargar/<?= $practica['id'] ?>" class="btn btn-success shadow-sm" title="Descargar Constancia de Empresa [<?= htmlspecialchars($constancia['nombre_archivo_descarga']) ?>]">
                <i class="fas fa-file-download mr-1"></i> Descargar Constancia de Empresa (<?= htmlspecialchars($constancia['nombre_archivo_descarga']) ?>)
            </a>
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

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
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

<!-- Barra de Estado del Flujo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center my-1">
                <span class="mr-2 text-muted font-weight-bold">ESTADO ACTUAL:</span>
                <?php
                $badgeCls = 'badge-secondary';
                $estadoTexto = $practica['estado'];
                switch ($practica['estado']) {
                    case 'REGISTRADO': $badgeCls = 'badge-secondary'; $estadoTexto = 'Registrado'; break;
                    case 'DOCUMENTACION_SUBIDA': $badgeCls = 'badge-info'; $estadoTexto = 'Documentos Subidos'; break;
                    case 'OBSERVADO_SUPERVISOR': $badgeCls = 'badge-danger'; $estadoTexto = 'Observado por Docente Supervisor'; break;
                    case 'APROBADO_SUPERVISOR': 
                    case 'DERIVADO_COORDINADOR': $badgeCls = 'badge-warning'; $estadoTexto = 'V°B° Supervisor otorgado - En Coordinación de Carrera'; break;
                    case 'OBSERVADO_COORDINADOR': $badgeCls = 'badge-danger'; $estadoTexto = 'Observado por Coordinador'; break;
                    case 'APROBADO_COORDINADOR':
                    case 'DERIVADO_SECRETARIA': $badgeCls = 'badge-warning'; $estadoTexto = 'V°B° Coordinador otorgado - En Secretaría Académica'; break;
                    case 'OBSERVADO_SECRETARIA': $badgeCls = 'badge-danger'; $estadoTexto = 'Observado por Secretaría Académica'; break;
                    case 'FINALIZADO_EMITIDO': $badgeCls = 'badge-success'; $estadoTexto = 'Finalizado - Constancia Oficial Emitida'; break;
                }
                $ultimaObservacion = null;
                if (!empty($historial)) {
                    foreach ($historial as $h) {
                        if (in_array($h['accion'], ['OBSERVADO_DEVUELTO', 'OBSERVADO']) || (isset($h['etapa_origen']) && strpos($h['etapa_origen'], 'OBSERV') !== false)) {
                            $ultimaObservacion = $h;
                            break;
                        }
                    }
                }
                ?>
                <span class="badge <?= $badgeCls ?> px-3 py-2 font-size-14 font-weight-bold">
                    <?= htmlspecialchars($estadoTexto) ?>
                </span>
            </div>

            <!-- Botones de Acción de Flujo según el Rol y Estado -->
            <div class="my-1">
                
                <!-- ESTADO OBSERVADO POR SECRETARÍA ACADÉMICA -->
                <?php if ($practica['estado'] === 'OBSERVADO_SECRETARIA'): ?>
                    <?php if ($esCoordinador || $esAdmin): ?>
                        <button type="button" class="btn btn-warning shadow-sm mr-1" data-toggle="modal" data-target="#modalObservar">
                            <i class="fas fa-undo-alt mr-1"></i> Devolver Observado a Docente Supervisor
                        </button>
                        <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalSubsanarSecretaria">
                            <i class="fas fa-reply mr-1"></i> Subsanar y Reenviar a Secretaría Académica
                        </button>
                    <?php endif; ?>

                <!-- ESTADO OBSERVADO POR COORDINADOR DE CARRERA -->
                <?php elseif ($practica['estado'] === 'OBSERVADO_COORDINADOR'): ?>
                    <?php if ($esSupervisor || $esAdmin || $esCoordinador): ?>
                        <button type="button" class="btn btn-warning shadow-sm mr-1" data-toggle="modal" data-target="#modalObservar">
                            <i class="fas fa-undo-alt mr-1"></i> Devolver Observado al Estudiante
                        </button>
                        <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalSubsanarCoordinador">
                            <i class="fas fa-reply mr-1"></i> Subsanar y Reenviar a Coordinación
                        </button>
                    <?php endif; ?>

                <!-- ACCIÓN NIVEL 1: DOCENTE SUPERVISOR -->
                <?php elseif (($esSupervisor || $esAdmin) && in_array($practica['estado'], ['REGISTRADO', 'DOCUMENTACION_SUBIDA', 'OBSERVADO_SUPERVISOR'])): ?>
                    <button type="button" class="btn btn-warning shadow-sm mr-1" data-toggle="modal" data-target="#modalObservar">
                        <i class="fas fa-undo-alt mr-1"></i> Observar y Devolver
                    </button>
                    <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalSupervisorAprobar">
                        <i class="fas fa-check-circle mr-1"></i> Otorgar V°B° y Derivar a Coordinador
                    </button>

                <!-- ACCIÓN NIVEL 2: COORDINADOR DE CARRERA -->
                <?php elseif (($esCoordinador || $esAdmin) && in_array($practica['estado'], ['DERIVADO_COORDINADOR', 'APROBADO_SUPERVISOR'])): ?>
                    <button type="button" class="btn btn-warning shadow-sm mr-1" data-toggle="modal" data-target="#modalObservar">
                        <i class="fas fa-undo-alt mr-1"></i> Observar y Devolver
                    </button>
                    <form method="POST" action="<?= BASE_URL ?>/efsrt/derivaciones/coordinadorAprobarYDerivar" class="d-inline" onsubmit="return confirm('¿Confirma otorgar el V°B° de Coordinación y derivar el expediente a Secretaría Académica?')">
                        <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
                        <button type="submit" class="btn btn-success shadow-sm">
                            <i class="fas fa-stamp mr-1"></i> Otorgar V°B° y Derivar a Secretaría
                        </button>
                    </form>

                <!-- ACCIÓN NIVEL 3: SECRETARÍA ACADÉMICA -->
                <?php elseif (($esSecretario || $esAdmin) && in_array($practica['estado'], ['DERIVADO_SECRETARIA', 'APROBADO_COORDINADOR'])): ?>
                    <button type="button" class="btn btn-warning shadow-sm mr-1" data-toggle="modal" data-target="#modalObservar">
                        <i class="fas fa-undo-alt mr-1"></i> Observar
                    </button>
                    <a href="<?= BASE_URL ?>/efsrt/constancias/generar/<?= $practica['id'] ?>" class="btn btn-success shadow-sm" onclick="return confirm('¿Confirma validar el expediente y finalizar el proceso de EFSRT del estudiante?')">
                        <i class="fas fa-check-circle mr-1"></i> Validar y Finalizar Expediente
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php if ($practica['estado'] === 'OBSERVADO_SECRETARIA'): ?>
    <div class="alert alert-danger shadow-sm border-left-danger mb-4">
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading font-weight-bold mb-1">Expediente Observado por Secretaría Académica</h5>
                <p class="mb-1 font-size-14">
                    Este expediente fue devuelto con observaciones por Secretaría Académica. Una vez realizadas las correcciones necesarias, presione el botón <strong>"Subsanar y Reenviar a Secretaría Académica"</strong> para continuar con el trámite.
                </p>
                <?php if (!empty($ultimaObservacion)): ?>
                    <div class="mt-2 p-3 bg-white rounded border border-danger text-dark font-size-13">
                        <strong class="text-danger"><i class="fas fa-comment-dots mr-1"></i> Motivo de la observación:</strong><br>
                        <?= nl2br(htmlspecialchars($ultimaObservacion['comentarios'] ?? '')) ?>
                        <div class="text-muted font-size-11 mt-2">
                            <i class="fas fa-user-edit mr-1"></i> Registrado por <?= htmlspecialchars($ultimaObservacion['usuario_nombre'] ?? 'Secretaría Académica') ?>
                            <?= !empty($ultimaObservacion['fecha_hora']) ? ' el ' . date('d/m/Y H:i', strtotime($ultimaObservacion['fecha_hora'])) : '' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($practica['estado'] === 'OBSERVADO_COORDINADOR'): ?>
    <div class="alert alert-warning shadow-sm border-left-warning mb-4">
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading font-weight-bold mb-1">Expediente Observado por Coordinación de Carrera</h5>
                <p class="mb-1 font-size-14">
                    Este expediente fue observado por la Coordinación. Regularice los documentos y presione el botón <strong>"Subsanar y Reenviar a Coordinación"</strong>.
                </p>
                <?php if (!empty($ultimaObservacion)): ?>
                    <div class="mt-2 p-3 bg-white rounded border border-warning text-dark font-size-13">
                        <strong class="text-warning"><i class="fas fa-comment-dots mr-1"></i> Motivo de la observación:</strong><br>
                        <?= nl2br(htmlspecialchars($ultimaObservacion['comentarios'] ?? '')) ?>
                        <div class="text-muted font-size-11 mt-2">
                            <i class="fas fa-user-edit mr-1"></i> Registrado por <?= htmlspecialchars($ultimaObservacion['usuario_nombre'] ?? 'Coordinación') ?>
                            <?= !empty($ultimaObservacion['fecha_hora']) ? ' el ' . date('d/m/Y H:i', strtotime($ultimaObservacion['fecha_hora'])) : '' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <!-- COLUMNA IZQUIERDA: DETALLES DE LA PRÁCTICA -->
    <div class="col-lg-4 mb-4">
        
        <!-- Ficha de la Práctica -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-info-circle mr-2 text-primary"></i> Información de la Práctica
                </h6>
            </div>
            <div class="card-body pt-0">
                <div class="mb-3">
                    <small class="text-muted d-block">Módulo Formativo:</small>
                    <strong class="text-primary font-size-14">Módulo <?= htmlspecialchars($practica['modulo_nro']) ?></strong><br>
                    <span><?= htmlspecialchars($practica['modulo_nombre']) ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Programa y Periodo:</small>
                    <strong><?= htmlspecialchars($practica['programa_nombre']) ?></strong><br>
                    <span class="badge badge-light border"><?= htmlspecialchars($practica['periodo_nombre']) ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Empresa / Institución Receptora:</small>
                    <strong class="text-dark"><?= htmlspecialchars($practica['empresa_razon_social']) ?></strong><br>
                    <small class="text-muted">RUC: <?= htmlspecialchars($practica['empresa_ruc'] ?: 'S/N') ?></small><br>
                    <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($practica['empresa_direccion']) ?></small>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Área de Desempeño:</small>
                    <strong class="text-dark"><?= htmlspecialchars($practica['area_desempeno']) ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Fechas de Práctica:</small>
                    <i class="far fa-calendar-alt text-muted mr-1"></i>
                    <strong><?= date('d/m/Y', strtotime($practica['fecha_inicio'])) ?></strong> al <strong><?= date('d/m/Y', strtotime($practica['fecha_fin'])) ?></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Horas y Calificación:</small>
                    <span class="badge badge-primary px-2 py-1"><?= (int)$practica['horas_requeridas'] ?> Horas Requeridas</span>
                    <span class="badge badge-success px-2 py-1 ml-1"><?= (int)$practica['horas_convalidadas'] ?> Horas Convalidadas</span>
                    <?php if (!empty($practica['calificacion_final'])): ?>
                        <div class="mt-2">
                            <span class="badge badge-dark font-size-13 px-2 py-1">Nota Final: <?= number_format($practica['calificacion_final'], 1) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <hr>

                <!-- Docente Supervisor Asignado -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">DOCENTE SUPERVISOR:</small>
                        <?php if ($esAdmin || $esCoordinador): ?>
                            <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#modalCambiarSupervisor">
                                <i class="fas fa-sync-alt"></i> Asignar
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($practica['supervisor_nombre'])): ?>
                        <strong class="text-dark font-size-14"><?= htmlspecialchars($practica['supervisor_nombre']) ?></strong><br>
                        <small class="text-muted"><i class="fas fa-id-card"></i> DNI: <?= htmlspecialchars($practica['supervisor_dni']) ?></small><br>
                        <?php if (!empty($practica['supervisor_correo'])): ?>
                            <small class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($practica['supervisor_correo']) ?></small><br>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning py-2 px-3 font-size-12 mb-0">
                            <i class="fas fa-exclamation-circle mr-1"></i> Aún no se ha asignado un docente supervisor.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Constancia Oficial si ya existe -->
        <?php if (!empty($constancia)): ?>
            <div class="card border-0 shadow-sm border-left-success bg-white">
                <div class="card-body">
                    <h6 class="font-weight-bold text-success mb-2">
                        <i class="fas fa-award mr-1"></i> Constancia Oficial Emitida
                    </h6>
                    <p class="font-size-13 text-muted mb-2">
                        N° de Constancia: <strong><?= htmlspecialchars($constancia['nro_constancia']) ?></strong><br>
                        Fecha de Emisión: <?= date('d/m/Y', strtotime($constancia['fecha_emision'])) ?><br>
                        Archivo: <code class="text-dark font-weight-bold"><?= htmlspecialchars($constancia['nombre_archivo_descarga']) ?></code>
                    </p>
                    <a href="<?= BASE_URL ?>/efsrt/constancias/descargar/<?= $constancia['id'] ?>" class="btn btn-success btn-block shadow-sm">
                        <i class="fas fa-download mr-1"></i> Descargar PDF Oficial
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- COLUMNA DERECHA: DOCUMENTACIÓN DIGITAL Y VALIDACIÓN -->
    <div class="col-lg-8">
        
        <!-- Tarjeta de Documentos del Expediente -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-copy mr-2 text-primary"></i> Documentación Digital del Expediente
                </h5>
                <span class="badge badge-light border">
                    <?= count($estadoExpediente['aprobados']) ?> de <?= count($estadoExpediente['requeridos']) ?> Obligatorios Aprobados
                </span>
            </div>
            <div class="card-body pt-0">

                <!-- Alert de Ventanas de Subida y Cronograma -->
                <?php if (!empty($cronogramaVigente)): ?>
                    <div class="p-2 mb-3 rounded border bg-light font-size-13">
                        <div class="row align-items-center">
                            <div class="col-md-6 border-right">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="fas fa-user-graduate text-primary mr-1"></i> 
                                        <strong>Plazo Estudiante:</strong> 
                                        Hasta <?= date('d/m/Y H:i', strtotime($cronogramaVigente['fecha_cierre_estudiante'])) ?>
                                    </span>
                                    <?php if ($abiertoEstudiante): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-lock-open mr-1"></i> Abierto</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i> Cerrado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="fas fa-chalkboard-teacher text-info mr-1"></i> 
                                        <strong>Plazo Docente:</strong> 
                                        Hasta <?= date('d/m/Y H:i', strtotime($cronogramaVigente['fecha_cierre_supervisor'])) ?>
                                    </span>
                                    <?php if ($abiertoSupervisor): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-lock-open mr-1"></i> Abierto</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i> Cerrado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($esEstudiante && !$abiertoEstudiante && !$esAdmin): ?>
                    <div class="alert alert-danger font-size-13 py-2 px-3 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i> El plazo institucional para subir documentos de estudiantes ha finalizado. La recepción está cerrada. Comuníquese con secretaría académica si requiere regularización.
                    </div>
                <?php endif; ?>

                <?php if ($esSupervisor && !$abiertoSupervisor && !$esAdmin): ?>
                    <div class="alert alert-warning font-size-13 py-2 px-3 mb-3">
                        <i class="fas fa-clock mr-1"></i> El plazo para subir fichas de supervisión se encuentra cerrado en el cronograma institucional.
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Archivo Escaneado</th>
                                <th>Subido Por</th>
                                <th class="text-center">Validación</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <!-- 1. COPIA DE DNI (ESTUDIANTE) -->
                            <?php $docDni = $documentosMap['DNI'] ?? null; ?>
                            <tr>
                                <td>
                                    <strong>Copia de DNI</strong>
                                    <span class="badge badge-light text-muted border ml-1">Estudiante</span>
                                </td>
                                <td>
                                    <?php if ($docDni): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docDni['id'] ?>" target="_blank" class="text-primary font-weight-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> <?= htmlspecialchars($docDni['nombre_archivo_original']) ?>
                                        </a>
                                        <div class="font-size-11 text-muted"><?= round($docDni['tamano_bytes'] / 1024, 1) ?> KB | <?= date('d/m/Y H:i', strtotime($docDni['created_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-13"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Pendiente de subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $docDni ? htmlspecialchars($docDni['subido_por_nombre']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($docDni): ?>
                                        <?php if ($docDni['estado_validacion'] === 'APROBADO'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Aprobado</span>
                                        <?php elseif ($docDni['estado_validacion'] === 'OBSERVADO'): ?>
                                            <span class="badge badge-danger px-2 py-1" title="<?= htmlspecialchars($docDni['observaciones'] ?? '') ?>"><i class="fas fa-times mr-1"></i> Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-start mr-1"></i> Pendiente</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-light border">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <?php if ($docDni): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docDni['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/descargar/<?= $docDni['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (($esSupervisor || $esCoordinador || $esAdmin) && $practica['estado'] !== 'FINALIZADO_EMITIDO'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='abrirModalValidarDoc(<?= json_encode($docDni) ?>)' title="Evaluar Documento">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($esEstudiante && $abiertoEstudiante) || $esAdmin): ?>
                                        <button type="button" class="btn btn-xs btn-primary ml-1" onclick="abrirModalSubida('DNI', 'Copia de DNI')">
                                            <i class="fas fa-upload mr-1"></i> <?= $docDni ? 'Actualizar' : 'Subir' ?>
                                        </button>
                                    <?php elseif ($esEstudiante && !$abiertoEstudiante): ?>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" disabled title="Plazo institucional cerrado">
                                            <i class="fas fa-lock mr-1"></i> Plazo Vencido
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- 2. FICHA DE ACEPTACIÓN (ESTUDIANTE) -->
                            <?php $docAcep = $documentosMap['FICHA_ACEPTACION'] ?? null; ?>
                            <tr>
                                <td>
                                    <strong>Ficha de Aceptación</strong>
                                    <span class="badge badge-light text-muted border ml-1">Estudiante</span>
                                </td>
                                <td>
                                    <?php if ($docAcep): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docAcep['id'] ?>" target="_blank" class="text-primary font-weight-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> <?= htmlspecialchars($docAcep['nombre_archivo_original']) ?>
                                        </a>
                                        <div class="font-size-11 text-muted"><?= round($docAcep['tamano_bytes'] / 1024, 1) ?> KB | <?= date('d/m/Y H:i', strtotime($docAcep['created_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-13"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Pendiente de subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $docAcep ? htmlspecialchars($docAcep['subido_por_nombre']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($docAcep): ?>
                                        <?php if ($docAcep['estado_validacion'] === 'APROBADO'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Aprobado</span>
                                        <?php elseif ($docAcep['estado_validacion'] === 'OBSERVADO'): ?>
                                            <span class="badge badge-danger px-2 py-1" title="<?= htmlspecialchars($docAcep['observaciones'] ?? '') ?>"><i class="fas fa-times mr-1"></i> Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-start mr-1"></i> Pendiente</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-light border">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <?php if ($docAcep): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docAcep['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/descargar/<?= $docAcep['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (($esSupervisor || $esCoordinador || $esAdmin) && $practica['estado'] !== 'FINALIZADO_EMITIDO'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='abrirModalValidarDoc(<?= json_encode($docAcep) ?>)' title="Evaluar Documento">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($esEstudiante && $abiertoEstudiante) || $esAdmin): ?>
                                        <button type="button" class="btn btn-xs btn-primary ml-1" onclick="abrirModalSubida('FICHA_ACEPTACION', 'Ficha de Aceptación')">
                                            <i class="fas fa-upload mr-1"></i> <?= $docAcep ? 'Actualizar' : 'Subir' ?>
                                        </button>
                                    <?php elseif ($esEstudiante && !$abiertoEstudiante): ?>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" disabled title="Plazo institucional cerrado">
                                            <i class="fas fa-lock mr-1"></i> Plazo Vencido
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- 3. FICHA DE EVALUACIÓN (ESTUDIANTE) -->
                            <?php $docEval = $documentosMap['FICHA_EVALUACION'] ?? null; ?>
                            <tr>
                                <td>
                                    <strong>Ficha de Evaluación</strong>
                                    <span class="badge badge-light text-muted border ml-1">Estudiante</span>
                                    <span class="badge badge-info ml-1">PDF</span>
                                </td>
                                <td>
                                    <?php if ($docEval): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docEval['id'] ?>" target="_blank" class="text-primary font-weight-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> <?= htmlspecialchars($docEval['nombre_archivo_original']) ?>
                                        </a>
                                        <div class="font-size-11 text-muted"><?= round($docEval['tamano_bytes'] / 1024, 1) ?> KB | <?= date('d/m/Y H:i', strtotime($docEval['created_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-13"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Pendiente de subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $docEval ? htmlspecialchars($docEval['subido_por_nombre']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($docEval): ?>
                                        <?php if ($docEval['estado_validacion'] === 'APROBADO'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Aprobado</span>
                                        <?php elseif ($docEval['estado_validacion'] === 'OBSERVADO'): ?>
                                            <span class="badge badge-danger px-2 py-1" title="<?= htmlspecialchars($docEval['observaciones'] ?? '') ?>"><i class="fas fa-times mr-1"></i> Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-start mr-1"></i> Pendiente</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-light border">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <?php if ($docEval): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docEval['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/descargar/<?= $docEval['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (($esSupervisor || $esCoordinador || $esAdmin) && $practica['estado'] !== 'FINALIZADO_EMITIDO'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='abrirModalValidarDoc(<?= json_encode($docEval) ?>)' title="Evaluar Documento">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($esEstudiante && $abiertoEstudiante) || $esAdmin): ?>
                                        <button type="button" class="btn btn-xs btn-primary ml-1" onclick="abrirModalSubida('FICHA_EVALUACION', 'Ficha de Evaluación')">
                                            <i class="fas fa-upload mr-1"></i> <?= $docEval ? 'Actualizar' : 'Subir' ?>
                                        </button>
                                    <?php elseif ($esEstudiante && !$abiertoEstudiante): ?>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" disabled title="Plazo institucional cerrado">
                                            <i class="fas fa-lock mr-1"></i> Plazo Vencido
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- 4. CONSTANCIA DE LA EMPRESA (ESTUDIANTE) -->
                            <?php $docEmp = $documentosMap['CONSTANCIA_EMPRESA'] ?? null; ?>
                            <tr>
                                <td>
                                    <strong>Constancia de Empresa</strong>
                                    <span class="badge badge-light text-muted border ml-1">Estudiante</span>
                                </td>
                                <td>
                                    <?php if ($docEmp): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docEmp['id'] ?>" target="_blank" class="text-primary font-weight-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> <?= htmlspecialchars($docEmp['nombre_archivo_original']) ?>
                                        </a>
                                        <div class="font-size-11 text-muted"><?= round($docEmp['tamano_bytes'] / 1024, 1) ?> KB | <?= date('d/m/Y H:i', strtotime($docEmp['created_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-13"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Pendiente de subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $docEmp ? htmlspecialchars($docEmp['subido_por_nombre']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($docEmp): ?>
                                        <?php if ($docEmp['estado_validacion'] === 'APROBADO'): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Aprobado</span>
                                        <?php elseif ($docEmp['estado_validacion'] === 'OBSERVADO'): ?>
                                            <span class="badge badge-danger px-2 py-1" title="<?= htmlspecialchars($docEmp['observaciones'] ?? '') ?>"><i class="fas fa-times mr-1"></i> Observado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-start mr-1"></i> Pendiente</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-light border">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <?php if ($docEmp): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docEmp['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/descargar/<?= $docEmp['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (($esSupervisor || $esCoordinador || $esAdmin) && $practica['estado'] !== 'FINALIZADO_EMITIDO'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='abrirModalValidarDoc(<?= json_encode($docEmp) ?>)' title="Evaluar Documento">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($esEstudiante && $abiertoEstudiante) || $esAdmin): ?>
                                        <button type="button" class="btn btn-xs btn-primary ml-1" onclick="abrirModalSubida('CONSTANCIA_EMPRESA', 'Constancia de la Empresa')">
                                            <i class="fas fa-upload mr-1"></i> <?= $docEmp ? 'Actualizar' : 'Subir' ?>
                                        </button>
                                    <?php elseif ($esEstudiante && !$abiertoEstudiante): ?>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" disabled title="Plazo institucional cerrado">
                                            <i class="fas fa-lock mr-1"></i> Plazo Vencido
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- 5. FICHA DE SUPERVISIÓN (DOCENTE SUPERVISOR) -->
                            <?php $docSup = $documentosMap['FICHA_SUPERVISION'] ?? null; ?>
                            <tr>
                                <td>
                                    <strong>Ficha de Supervisión</strong>
                                    <span class="badge badge-primary ml-1">Supervisor</span>
                                </td>
                                <td>
                                    <?php if ($docSup): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docSup['id'] ?>" target="_blank" class="text-primary font-weight-bold">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> <?= htmlspecialchars($docSup['nombre_archivo_original']) ?>
                                        </a>
                                        <div class="font-size-11 text-muted"><?= round($docSup['tamano_bytes'] / 1024, 1) ?> KB | <?= date('d/m/Y H:i', strtotime($docSup['created_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted font-italic font-size-13"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Pendiente de subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $docSup ? htmlspecialchars($docSup['subido_por_nombre']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($docSup): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Aprobado</span>
                                    <?php else: ?>
                                        <span class="badge badge-light border">No subido</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-nowrap">
                                    <?php if ($docSup): ?>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/ver/<?= $docSup['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/efsrt/documentos/descargar/<?= $docSup['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if ($esAdmin && $practica['estado'] !== 'FINALIZADO_EMITIDO'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick='abrirModalValidarDoc(<?= json_encode($docSup) ?>)' title="Evaluar Ficha de Supervisión">
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($esSupervisor && $abiertoSupervisor) || $esAdmin): ?>
                                        <button type="button" class="btn btn-xs btn-primary ml-1" onclick="abrirModalSubida('FICHA_SUPERVISION', 'Ficha de Supervisión Escaneada')">
                                            <i class="fas fa-upload mr-1"></i> <?= $docSup ? 'Actualizar' : 'Subir Ficha' ?>
                                        </button>
                                    <?php elseif ($esSupervisor && !$abiertoSupervisor): ?>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" disabled title="Plazo institucional cerrado">
                                            <i class="fas fa-lock mr-1"></i> Plazo Vencido
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Resumen de observaciones activas si las hay -->
                <?php if (!empty($estadoExpediente['observados'])): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Observaciones Pendientes de Subsanar:</h6>
                        <ul class="mb-0 pl-3">
                            <?php foreach ($documentos as $d): ?>
                                <?php if ($d['estado_validacion'] === 'OBSERVADO'): ?>
                                    <li><strong><?= htmlspecialchars($d['tipo_documento']) ?>:</strong> <?= htmlspecialchars($d['observaciones']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Historial y Trazabilidad del Flujo de Derivaciones -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-history mr-2 text-primary"></i> Historial de Derivaciones y Trazabilidad
                </h6>
                <span class="badge badge-light border">
                    <?= count($historial) ?> movimiento(s)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-historial-derivaciones">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 140px;">Fecha y Hora</th>
                                <th>Flujo / Derivación</th>
                                <th>Acción</th>
                                <th>Responsable</th>
                                <th>Comentarios / Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historial)): ?>
                                <?php foreach ($historial as $h): ?>
                                    <tr>
                                        <td class="text-nowrap font-size-13">
                                            <i class="far fa-clock text-muted mr-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($h['fecha_hora'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary font-size-11">
                                                <?= htmlspecialchars($h['etapa_origen']) ?> &rarr; <?= htmlspecialchars($h['etapa_destino']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $accionBadge = 'badge-secondary';
                                            if (strpos($h['accion'], 'APROB') !== false || strpos($h['accion'], 'VISTO') !== false) $accionBadge = 'badge-success';
                                            elseif (strpos($h['accion'], 'OBSERV') !== false) $accionBadge = 'badge-danger';
                                            elseif (strpos($h['accion'], 'SUBSAN') !== false) $accionBadge = 'badge-info';
                                            ?>
                                            <span class="badge <?= $accionBadge ?> font-size-11 px-2 py-1">
                                                <?= htmlspecialchars($h['accion']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark font-size-13"><?= htmlspecialchars($h['usuario_nombre']) ?></strong>
                                            <div class="font-size-11 text-muted">DNI: <?= htmlspecialchars($h['usuario_dni']) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($h['comentarios'])): ?>
                                                <div class="font-size-13 text-dark bg-light p-2 rounded border">
                                                    <?= nl2br(htmlspecialchars($h['comentarios'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted font-italic font-size-12">Sin comentarios adicionales</span>
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

    </div>
</div>

<!-- ========================================== -->
<!-- MODALES DE OPERACIÓN                       -->
<!-- ========================================== -->

<!-- Modal Subir Documento -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/documentos/subir" enctype="multipart/form-data">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <input type="hidden" name="tipo_documento" id="subida_tipo_documento" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalSubidaTitle">Subir Documento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Seleccione el archivo escaneado (PDF o Imagen):</label>
                        <div class="custom-file">
                            <input type="file" name="archivo" id="archivo_input" class="custom-file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                            <label class="custom-file-label" for="archivo_input">Elegir archivo...</label>
                        </div>
                        <small class="text-muted d-block mt-2">Formatos aceptados: PDF, JPG, PNG (Tamaño máximo: 12MB).</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload mr-1"></i> Subir Archivo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Validar Documento Individual (Supervisor / Coordinador) -->
<div class="modal fade" id="modalValidarDoc" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/documentos/validar">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <input type="hidden" name="id_documento" id="val_id_documento" value="">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold" id="modalValidarTitle">Evaluar Documento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Resultado de Evaluación:</label>
                        <select name="estado_validacion" id="val_estado" class="form-control" required onchange="toggleObservaciones(this.value)">
                            <option value="APROBADO">APROBADO</option>
                            <option value="OBSERVADO">OBSERVADO</option>
                        </select>
                    </div>
                    <div class="form-group" id="grupo_observaciones">
                        <label class="font-weight-bold">Observaciones / Indicaciones para el estudiante:</label>
                        <textarea name="observaciones" id="val_observaciones" class="form-control" rows="3" placeholder="Detalle qué corregir o volver a escanear..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Evaluación</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Visto Bueno y Derivación del Supervisor (Nivel 1) -->
<div class="modal fade" id="modalSupervisorAprobar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/derivaciones/supervisorAprobarYDerivar">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> V°B° del Supervisor y Derivación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted font-size-13">
                        Al otorgar su visto bueno, el expediente pasará a la bandeja del <strong>Coordinador de Carrera</strong> para su ratificación.
                    </p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nota / Calificación Final:</label>
                            <input type="number" step="0.1" min="0" max="20" name="calificacion_final" class="form-control" placeholder="Ej. 18.0" value="<?= $practica['calificacion_final'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Horas Convalidadas:</label>
                            <input type="number" name="horas_convalidadas" class="form-control" value="<?= $practica['horas_convalidadas'] ?: $practica['horas_requeridas'] ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Comentarios u observaciones finales:</label>
                        <textarea name="comentarios" class="form-control" rows="3">Expediente revisado satisfactoriamente. Prácticas aprobadas y derivadas a Coordinación.</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Derivar a Coordinador</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Observar y Devolver -->
<div class="modal fade" id="modalObservar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/derivaciones/observarYDevolver">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-undo-alt mr-1"></i> Observar y Devolver Expediente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Motivo detallado de la observación <span class="text-danger">*</span>:</label>
                        <textarea name="comentarios" class="form-control" rows="4" required placeholder="Indique claramente los errores o documentos faltantes que deben corregirse..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-undo mr-1"></i> Devolver Observado</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Subsanar Observación de Secretaría Académica -->
<div class="modal fade" id="modalSubsanarSecretaria" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/derivaciones/subsanarYDerivar">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> Subsanar y Reenviar a Secretaría
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 font-size-13 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Al subsanar, el expediente retornará a la bandeja de <strong>Secretaría Académica</strong> para que continúe la revisión y emisión de la constancia oficial.
                    </div>
                    <?php if (!empty($ultimaObservacion)): ?>
                        <div class="bg-light p-2 rounded mb-3 border font-size-12">
                            <strong>Observación a subsanar:</strong><br>
                            <?= nl2br(htmlspecialchars($ultimaObservacion['comentarios'] ?? '')) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Detalle del sustento / correcciones realizadas <span class="text-danger">*</span>:</label>
                        <textarea name="comentarios" class="form-control" rows="4" required placeholder="Explique las correcciones efectuadas o documentos regularizados..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Subsanar y Reenviar a Secretaría</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Subsanar Observación de Coordinación de Carrera -->
<div class="modal fade" id="modalSubsanarCoordinador" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/derivaciones/subsanarYDerivar">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> Subsanar y Reenviar a Coordinación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 font-size-13 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Al subsanar, el expediente retornará a la bandeja de la <strong>Coordinación de Carrera</strong> con su V°B°.
                    </div>
                    <?php if (!empty($ultimaObservacion)): ?>
                        <div class="bg-light p-2 rounded mb-3 border font-size-12">
                            <strong>Observación a subsanar:</strong><br>
                            <?= nl2br(htmlspecialchars($ultimaObservacion['comentarios'] ?? '')) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Detalle del sustento / correcciones realizadas <span class="text-danger">*</span>:</label>
                        <textarea name="comentarios" class="form-control" rows="4" required placeholder="Explique las correcciones efectuadas o documentos regularizados..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Subsanar y Reenviar a Coordinador</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar / Cambiar Supervisor -->
<?php if ($esAdmin || $esCoordinador): ?>
<div class="modal fade" id="modalCambiarSupervisor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/practicas/asignarSupervisor">
            <input type="hidden" name="id_practica" value="<?= $practica['id'] ?>">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Asignar Docente Supervisor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Seleccionar Supervisor Habilitado:</label>
                        <select name="id_supervisor" class="form-control" required>
                            <option value="">-- Seleccionar Docente --</option>
                            <?php foreach ($supervisoresDisponibles as $sd): ?>
                                <option value="<?= $sd['id'] ?>" <?= ($practica['id_supervisor'] == $sd['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sd['docente_nombre']) ?> (Asignados: <?= $sd['total_asignados'] ?> / <?= $sd['max_estudiantes'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Asignación</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function abrirModalSubida(tipo, titulo) {
    document.getElementById('subida_tipo_documento').value = tipo;
    document.getElementById('modalSubidaTitle').innerText = 'Subir: ' + titulo;
    $('#modalSubirDoc').modal('show');
}

function abrirModalValidarDoc(doc) {
    document.getElementById('val_id_documento').value = doc.id;
    document.getElementById('modalValidarTitle').innerText = 'Evaluar: ' + doc.tipo_documento;
    document.getElementById('val_estado').value = (doc.estado_validacion === 'PENDIENTE') ? 'APROBADO' : doc.estado_validacion;
    document.getElementById('val_observaciones').value = doc.observaciones || '';
    $('#modalValidarDoc').modal('show');
}

function toggleObservaciones(val) {
    var grp = document.getElementById('grupo_observaciones');
    if (val === 'OBSERVADO') {
        grp.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-historial-derivaciones')) {
        $('#tabla-historial-derivaciones').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 20],
            ordering: false,
            responsive: true,
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron movimientos",
                "sEmptyTable": "No se registran movimientos en el historial",
                "sInfo": "Mostrando del _START_ al _END_ de un total de _TOTAL_ movimientos",
                "sInfoEmpty": "Mostrando 0 de 0 movimientos",
                "sInfoFiltered": "(filtrado de un total de _MAX_ movimientos)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
