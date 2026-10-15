<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-file-invoice mr-2"></i> Reporte de Expedientes Culminados EFSRT
        </h3>
        <p class="text-muted mb-0">Secretaría Académica: Consolidado de expedientes aprobados y descarga de constancias de empresa en formato estandarizado.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-outline-secondary shadow-sm mr-1">
            <i class="fas fa-folder-open mr-1"></i> Expedientes
        </a>
        <a href="<?= BASE_URL ?>/efsrt/periodos" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-calendar-alt mr-1"></i> Cronograma
        </a>
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

<!-- Tarjeta de Filtros Académicos -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-filter mr-2 text-primary"></i> Filtros de Selección Académica
        </h5>
    </div>
    <div class="card-body pt-0">
        <form method="GET" action="<?= BASE_URL ?>/efsrt/reportes" id="formFiltrosReporte">
            <div class="form-row">
                <!-- Programa de Estudios -->
                <div class="col-md-4 form-group mb-2">
                    <label class="font-weight-bold font-size-13 text-muted">Programa de Estudios (Carrera) <span class="text-danger">*</span></label>
                    <select name="id_programa" id="filtro_programa" class="form-control form-control-sm select2" onchange="cargarPlanesPorPrograma(this.value)">
                        <option value="">-- Todos los Programas --</option>
                        <?php foreach ($programas as $prg): ?>
                            <option value="<?= $prg['id'] ?>" <?= ($filtroPrograma == $prg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Plan de Estudios -->
                <div class="col-md-3 form-group mb-2">
                    <label class="font-weight-bold font-size-13 text-muted">Plan de Estudios <span class="text-danger">*</span></label>
                    <select name="id_plan" id="filtro_plan" class="form-control form-control-sm" onchange="cargarModulosPorPlan(this.value)">
                        <option value="">-- Todos los Planes --</option>
                        <?php foreach ($planes as $pl): ?>
                            <option value="<?= $pl['id'] ?>" <?= ($filtroPlan == $pl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pl['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Módulo Formativo -->
                <div class="col-md-5 form-group mb-2">
                    <label class="font-weight-bold font-size-13 text-muted">Módulo Formativo <span class="text-danger">*</span></label>
                    <select name="id_modulo" id="filtro_modulo" class="form-control form-control-sm">
                        <option value="">-- Todos los Módulos --</option>
                        <?php foreach ($modulos as $mod): ?>
                            <option value="<?= $mod['id'] ?>" <?= ($filtroModulo == $mod['id']) ? 'selected' : '' ?>>
                                Módulo <?= $mod['nro_modulo'] ?>: <?= htmlspecialchars($mod['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row align-items-end mt-2">
                <!-- Periodo Académico -->
                <div class="col-md-3 form-group mb-2 mb-md-0">
                    <label class="font-weight-bold font-size-13 text-muted">Periodo Académico</label>
                    <select name="id_periodo" id="filtro_periodo" class="form-control form-control-sm">
                        <option value="">-- Todos los Periodos --</option>
                        <?php foreach ($periodos as $per): ?>
                            <option value="<?= $per['id'] ?>" <?= ($filtroPeriodo == $per['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($per['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sede -->
                <div class="col-md-3 form-group mb-2 mb-md-0">
                    <label class="font-weight-bold font-size-13 text-muted">Sede</label>
                    <select name="id_sede" id="filtro_sede" class="form-control form-control-sm">
                        <option value="">-- Todas las Sedes --</option>
                        <?php foreach ($sedes as $se): ?>
                            <option value="<?= $se['id'] ?>" <?= ($filtroSede == $se['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($se['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botones de Acción de Filtro -->
                <div class="col-md-6 form-group mb-0 text-md-right">
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mr-1">
                        <i class="fas fa-search mr-1"></i> Filtrar Resultados
                    </button>
                    <a href="<?= BASE_URL ?>/efsrt/reportes" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen y Barra de Exportación -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase font-size-12 mb-1">Expedientes Culminados</h6>
                    <h3 class="mb-0 font-weight-bold"><?= count($expedientes) ?></h3>
                </div>
                <div class="bg-white rounded-circle p-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-user-graduate fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-info text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase font-size-12 mb-1">Horas Totales EFSRT</h6>
                    <h3 class="mb-0 font-weight-bold">
                        <?= array_sum(array_map(function($e) { return (int)($e['horas_convalidadas'] ?: $e['horas_requeridas']); }, $expedientes)) ?> hrs
                    </h3>
                </div>
                <div class="bg-white rounded-circle p-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-clock fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase font-size-12 mb-1">Constancias de Empresa</h6>
                    <h3 class="mb-0 font-weight-bold"><?= count($expedientes) ?> Listas</h3>
                </div>
                <div class="bg-white rounded-circle p-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-file-download fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Descargas Masivas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h6 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-download mr-1 text-primary"></i> Descargas Oficiales del Módulo
            </h6>
            <span class="text-muted font-size-13">Descargue el consolidado en PDF o el paquete completo ZIP con todas las constancias renombradas según normativa.</span>
        </div>
        <div class="mt-2 mt-md-0">
            <?php
            $queryFiltros = http_build_query([
                'id_programa' => $filtroPrograma,
                'id_plan'     => $filtroPlan,
                'id_modulo'   => $filtroModulo,
                'id_periodo'  => $filtroPeriodo,
                'id_sede'     => $filtroSede
            ]);
            ?>
            <a href="<?= BASE_URL ?>/efsrt/reportes/pdf?<?= $queryFiltros ?>" target="_blank" class="btn btn-danger shadow-sm mr-2 <?= empty($expedientes) ? 'disabled' : '' ?>">
                <i class="fas fa-file-pdf mr-1"></i> Generar Reporte PDF
            </a>
            <a href="<?= BASE_URL ?>/efsrt/reportes/descargarPaqueteZip?<?= $queryFiltros ?>" class="btn btn-success shadow-sm <?= empty($expedientes) ? 'disabled' : '' ?>">
                <i class="fas fa-file-archive mr-1"></i> Descargar Constancias y Reporte (.ZIP)
            </a>
        </div>
    </div>
</div>

<!-- Tabla de Expedientes Culminados -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabla-reporte-efsrt">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Tipo / N° Doc</th>
                        <th>Estudiante</th>
                        <th>Carrera / Plan / Módulo</th>
                        <th>Empresa / RUC</th>
                        <th>Supervisor</th>
                        <th class="text-center">Horas / Nota</th>
                        <th>Constancia de Empresa</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expedientes)): ?>
                        <?php foreach ($expedientes as $idx => $exp): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($exp['estudiante_tipo_doc']) ?></span>
                                    <div class="font-weight-bold text-dark font-size-14 mt-1"><?= htmlspecialchars($exp['estudiante_dni']) ?></div>
                                </td>
                                <td>
                                    <strong class="text-primary font-size-14"><?= htmlspecialchars($exp['estudiante_nombre']) ?></strong>
                                    <div class="font-size-11 text-muted">
                                        <?= !empty($exp['estudiante_telefono']) ? '<i class="fas fa-phone mr-1"></i>' . htmlspecialchars($exp['estudiante_telefono']) : '' ?>
                                        <?= !empty($exp['estudiante_correo']) ? ' | ' . htmlspecialchars($exp['estudiante_correo']) : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($exp['programa_codigo']) ?></span>
                                    <span class="badge badge-light border">M<?= htmlspecialchars($exp['modulo_nro']) ?></span>
                                    <div class="font-size-12 text-dark mt-1 font-weight-bold">
                                        <?= htmlspecialchars($exp['programa_nombre']) ?>
                                    </div>
                                    <div class="font-size-11 text-muted">
                                        Plan: <?= htmlspecialchars($exp['plan_nombre'] ?? 'Modular') ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark font-size-13"><?= htmlspecialchars($exp['empresa_razon_social']) ?></strong>
                                    <?php if (!empty($exp['empresa_ruc'])): ?>
                                        <div class="font-size-11 text-muted">RUC: <?= htmlspecialchars($exp['empresa_ruc']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($exp['supervisor_nombre']) ? htmlspecialchars($exp['supervisor_nombre']) : '<span class="text-muted font-italic font-size-12">No asignado</span>' ?>
                                </td>
                                <td class="text-center">
                                    <div class="font-weight-bold text-dark">
                                        <?= !empty($exp['horas_convalidadas']) ? $exp['horas_convalidadas'] : $exp['horas_requeridas'] ?> hrs
                                    </div>
                                    <?php if (!empty($exp['calificacion_final'])): ?>
                                        <span class="badge badge-success px-2 py-1 mt-1 font-size-12">
                                            Nota: <?= number_format($exp['calificacion_final'], 1) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger fa-lg mr-2"></i>
                                        <div>
                                            <strong class="text-dark font-size-12"><?= htmlspecialchars($exp['nombre_archivo_descarga']) ?></strong>
                                            <div class="mt-1">
                                                <a href="<?= BASE_URL ?>/efsrt/reportes/descargarConstancia/<?= $exp['id'] ?>" class="btn btn-xs btn-outline-success font-size-11 py-1 px-2" title="Descargar Constancia de Empresa">
                                                    <i class="fas fa-download mr-1"></i> Descargar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right text-nowrap">
                                    <a href="<?= BASE_URL ?>/efsrt/practicas/detalle/<?= $exp['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Expediente Completo">
                                        <i class="fas fa-eye mr-1"></i> Ver
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

<script>
function cargarPlanesPorPrograma(idPrograma) {
    var selectPlan = document.getElementById('filtro_plan');
    var selectMod = document.getElementById('filtro_modulo');
    selectPlan.innerHTML = '<option value="">-- Todos los Planes --</option>';
    selectMod.innerHTML = '<option value="">-- Todos los Módulos --</option>';

    if (!idPrograma) return;

    fetch('<?= BASE_URL ?>/efsrt/reportes/getPlanesAjax/' + idPrograma)
        .then(function(res) { return res.json(); })
        .then(function(planes) {
            planes.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nombre;
                selectPlan.appendChild(opt);
            });
        })
        .catch(function(err) {
            console.error('Error al cargar planes:', err);
        });
}

function cargarModulosPorPlan(idPlan) {
    var selectMod = document.getElementById('filtro_modulo');
    selectMod.innerHTML = '<option value="">-- Todos los Módulos --</option>';

    if (!idPlan) return;

    fetch('<?= BASE_URL ?>/efsrt/reportes/getModulosAjax/' + idPlan)
        .then(function(res) { return res.json(); })
        .then(function(modulos) {
            modulos.forEach(function(m) {
                var opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = 'Módulo ' + m.nro_modulo + ': ' + m.descripcion;
                selectMod.appendChild(opt);
            });
        })
        .catch(function(err) {
            console.error('Error al cargar módulos:', err);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#tabla-reporte-efsrt')) {
        $('#tabla-reporte-efsrt').DataTable({
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
                "sSearch": "Buscar en tabla:",
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
