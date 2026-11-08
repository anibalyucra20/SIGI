<?php require __DIR__ . '/../../layouts/header.php'; ?>

<!-- ENCABEZADO DEL MÓDULO DE REPORTES -->
<div class="row align-items-center mb-3">
    <div class="col-md-7">
        <h3 class="mb-1 text-dark font-weight-bold">Reportes de Bolsa de Trabajo</h3>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary shadow-sm mr-1">
            <i class="fas fa-users mr-1"></i> Postulaciones
        </a>
        <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-secondary shadow-sm">
            <i class="far fa-address-book mr-1"></i> Convocatorias
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

<!-- NAVEGACIÓN POR PESTAÑAS DE REPORTES -->
<ul class="nav nav-pills mb-3 bg-white p-2 rounded shadow-sm border">
    <li class="nav-item">
        <a class="nav-link font-weight <?= ($tipoReporte === 'postulaciones') ? 'active bg-primary text-white' : 'text-dark' ?>" 
           href="<?= BASE_URL ?>/bolsa/reportes?tipo=postulaciones">
            <i class="fas fa-users mr-1"></i> 1. Reporte de Postulaciones
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link font-weight <?= ($tipoReporte === 'ofertas') ? 'active bg-primary text-white' : 'text-dark' ?>" 
           href="<?= BASE_URL ?>/bolsa/reportes?tipo=ofertas">
            <i class="far fa-address-book mr-1"></i> 2. Reporte de Convocatorias
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link font-weight <?= ($tipoReporte === 'empresas') ? 'active bg-primary text-white' : 'text-dark' ?>" 
           href="<?= BASE_URL ?>/bolsa/reportes?tipo=empresas">
            <i class="fas fa-building mr-1"></i> 3. Directorio de Empresas
        </a>
    </li>
</ul>

<!-- ============================================================== -->
<!-- PESTAÑA 1: REPORTE DE POSTULACIONES LABORALES                   -->
<!-- ============================================================== -->
<?php if ($tipoReporte === 'postulaciones'): ?>

    <!-- TARJETA DE FILTROS -->
    <div class="card p-3 shadow-sm rounded-3 mb-3 border-0">
        <form method="GET" action="<?= BASE_URL ?>/bolsa/reportes">
            <input type="hidden" name="tipo" value="postulaciones">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Convocatoria:</label>
                    <select name="id_oferta" class="form-control form-control-sm">
                        <option value="">Todas las convocatorias</option>
                        <?php foreach ($ofertas as $of): ?>
                            <option value="<?= $of['id'] ?>" <?= (($filtros['id_oferta'] ?? '') == $of['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($of['titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Empresa:</label>
                    <select name="id_empresa" class="form-control form-control-sm">
                        <option value="">Todas las empresas</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= (($filtros['id_empresa'] ?? '') == $emp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['empresa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Programa / Carrera:</label>
                    <select name="id_programa" class="form-control form-control-sm">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($programas as $prg): ?>
                            <option value="<?= $prg['id'] ?>" <?= (($filtros['id_programa'] ?? '') == $prg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prg['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Estado:</label>
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?= $est['id'] ?>" <?= (($filtros['estado'] ?? '') == $est['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($est['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row align-items-end mt-1">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Fecha Desde:</label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Fecha Hasta:</label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-2 text-md-right">
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mr-1">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/bolsa/reportes?tipo=postulaciones" class="btn btn-outline-secondary btn-sm px-3 shadow-sm mr-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </a>
                    <?php
                    $queryParams = http_build_query([
                        'id_oferta'    => $filtros['id_oferta'],
                        'id_empresa'   => $filtros['id_empresa'],
                        'id_programa'  => $filtros['id_programa'],
                        'estado'       => $filtros['estado'],
                        'fecha_inicio' => $filtros['fecha_inicio'],
                        'fecha_fin'    => $filtros['fecha_fin']
                    ]);
                    ?>
                    <a href="<?= BASE_URL ?>/bolsa/reportes/pdfPostulaciones?<?= $queryParams ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm font-weight-bold <?= empty($postulaciones) ? 'disabled' : '' ?>">
                        <i class="fas fa-file-pdf mr-1"></i> Generar Reporte PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TARJETAS DE MÉTRICAS EJECUTIVAS -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #4e73df !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Total Postulaciones</div>
                        <h4 class="font-weight-bold mb-0 text-dark"><?= (int)($metricas['total'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-users fa-2x text-primary" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #f6c23e !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Pendientes</div>
                        <h4 class="font-weight-bold mb-0 text-warning"><?= (int)($metricas['pendientes'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #36b9cc !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">En Evaluación</div>
                        <h4 class="font-weight-bold mb-0 text-info"><?= (int)($metricas['evaluacion'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-user-clock fa-2x text-info" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-0 shadow-sm p-3 rounded-3" style="border-left: 4px solid #1cc88a !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Seleccionados</div>
                        <h4 class="font-weight-bold mb-0 text-success"><?= (int)($metricas['seleccionados'] ?? 0) ?></h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

<!-- TABLA DE RESULTADOS -->
<div class="card p-3 shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table id="tabla-reporte-postulaciones" class="table table-bordered table-hover table-sm align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Nro</th>
                    <th class="text-center">DNI</th>
                    <th class="text-center">Postulante</th>
                    <th class="text-center">Carrera / Programa</th>
                    <th class="text-center">Convocatoria</th>
                    <th class="text-center">Empresa</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">CV / Ficha</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($postulaciones)): ?>
                    <?php foreach ($postulaciones as $idx => $row):
                        $est = (int)$row['estado'];

                        $estadoInfo = $estados[$est] ?? [
                            'nombre' => 'En Proceso',
                            'badge'  => 'badge-secondary',
                            'icono'  => 'fas fa-info'
                        ];
                    ?>
                        <tr>
                            <td class="text-center">
                                <?= $idx + 1 ?>
                            </td>

                            <td class="text-center">
                                <?= htmlspecialchars($row['dni'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(str_replace('_', ' ', $row['apellidos_nombres'])) ?>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-phone mr-1"></i>
                                    <?= htmlspecialchars($row['telefono'] ?? '-') ?>
                                </small>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['programa_nombre'] ?? 'General') ?>
                            </td>

                            <td>
                                <a href="<?= BASE_URL ?>/bolsa/postulaciones/ver/<?= $row['id_oferta_laboral'] ?>" class="text-primary">
                                    <?= htmlspecialchars($row['oferta_titulo']) ?>
                                </a>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['empresa_nombre'] ?? 'Confidencial') ?>
                            </td>

                            <td class="text-center small">
                                <?= !empty($row['fecha'])
                                    ? date('d/m/Y', strtotime($row['fecha']))
                                    : '-' ?>
                            </td>

                            <td class="text-center">
                                <span class="badge <?= htmlspecialchars($estadoInfo['badge']) ?> px-2 py-1 font-size-12">
                                    <?= htmlspecialchars($estadoInfo['nombre']) ?>
                                </span>
                            </td>

                            <td class="text-center text-nowrap">
                                <a href="<?= BASE_URL ?>/bolsa/curriculum/ver/<?= $row['id_curriculum'] ?>" target="_blank" class="btn btn-info btn-sm py-0 px-2" title="Ver ficha">
                                    <i class="fas fa-id-card"></i>
                                </a>

                                <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $row['id_curriculum'] ?>" target="_blank" class="btn btn-danger btn-sm py-0 px-2" title="Ver PDF del CV">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                <?php if (!empty($row['cv_archivo'])): ?>
                                    <a href="<?= htmlspecialchars($row['cv_archivo']) ?>" target="_blank" class="btn btn-primary btn-sm py-0 px-2" title="Link web">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- ============================================================== -->
<!-- PESTAÑA 2: REPORTE DE CONVOCATORIAS / OFERTAS                   -->
<!-- ============================================================== -->
<?php elseif ($tipoReporte === 'ofertas'): ?>

    <!-- TARJETA DE FILTROS -->
    <div class="card p-3 shadow-sm rounded-3 mb-3 border-0">
        <form method="GET" action="<?= BASE_URL ?>/bolsa/reportes">
            <input type="hidden" name="tipo" value="ofertas">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Empresa:</label>
                    <select name="id_empresa" class="form-control form-control-sm">
                        <option value="">Todas las empresas</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= (($filtros['id_empresa'] ?? '') == $emp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['empresa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Programa / Carrera:</label>
                    <select name="id_programa" class="form-control form-control-sm">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($programas as $prg): ?>
                            <option value="<?= $prg['id'] ?>" <?= (($filtros['id_programa'] ?? '') == $prg['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prg['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Estado:</label>
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="1" <?= (($filtros['estado'] ?? '') === 1 || ($filtros['estado'] ?? '') === '1') ? 'selected' : '' ?>>Vigente</option>
                        <option value="0" <?= (($filtros['estado'] ?? '') === 0 || ($filtros['estado'] ?? '') === '0') ? 'selected' : '' ?>>Finalizada</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2 text-md-right">
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mr-1">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/bolsa/reportes?tipo=ofertas" class="btn btn-outline-secondary btn-sm px-3 shadow-sm mr-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </a>
                    <?php
                    $queryOfertas = http_build_query([
                        'id_empresa'   => $filtros['id_empresa'],
                        'id_programa'  => $filtros['id_programa'],
                        'estado'       => $filtros['estado'],
                        'fecha_inicio' => $filtros['fecha_inicio'],
                        'fecha_fin'    => $filtros['fecha_fin']
                    ]);
                    ?>
                    <a href="<?= BASE_URL ?>/bolsa/reportes/pdfOfertas?<?= $queryOfertas ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm font-weight-bold <?= empty($ofertasData) ? 'disabled' : '' ?>">
                        <i class="fas fa-file-pdf mr-1"></i> Generar Reporte PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA DE CONVOCATORIAS -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-reporte-ofertas" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">Convocatoria</th>
                        <th class="text-center">Empresa</th>
                        <th class="text-center">Carrera Requerida</th>
                        <th class="text-center">Remuneración</th>
                        <th class="text-center">F. Publicación</th>
                        <th class="text-center">F. Cierre</th>
                        <th class="text-center">Postulantes</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Expediente PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ofertasData)): ?>
                        <?php foreach ($ofertasData as $idx => $of): 
                            $esVig = ((int)$of['estado'] === 1);
                        ?>
                            <tr>
                                <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($of['titulo']) ?></strong>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= htmlspecialchars($of['ubicacion'] ?? 'Huanta') ?></small>
                                </td>
                                <td><?= htmlspecialchars($of['empresa_nombre'] ?? 'Confidencial') ?></td>
                                <td><?= htmlspecialchars($of['programa_nombre'] ?? 'Todas las carreras') ?></td>
                                <td class="text-center font-weight-bold">
                                    <?= (!empty($of['salario']) && is_numeric($of['salario'])) ? 'S/ ' . number_format($of['salario'], 2) : ($of['salario'] ?: 'A Tratar') ?>
                                </td>
                                <td class="text-center small"><?= date('d/m/Y', strtotime($of['fecha_publicacion'])) ?></td>
                                <td class="text-center small"><?= date('d/m/Y', strtotime($of['fecha_cierre'])) ?></td>
                                <td class="text-center font-weight-bold">
                                    <?= (int)($of['total_postulantes'] ?? 0) ?>
                                    <small class="text-success d-block">(<?= (int)($of['total_seleccionados'] ?? 0) ?> sel.)</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $esVig ? 'badge-success' : 'badge-secondary' ?> px-2 py-1">
                                        <?= $esVig ? 'Vigente' : 'Finalizada' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/bolsa/reportes/pdfOferta/<?= $of['id'] ?>" target="_blank" class="btn btn-outline-danger btn-sm py-0 px-2" title="Descargar Expediente PDF de la Convocatoria">
                                        <i class="fas fa-file-pdf mr-1"></i> Expediente
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- ============================================================== -->
<!-- PESTAÑA 3: DIRECTORIO DE EMPRESAS ALIADAS                      -->
<!-- ============================================================== -->
<?php elseif ($tipoReporte === 'empresas'): ?>

    <!-- TARJETA DE FILTROS -->
    <div class="card p-3 shadow-sm rounded-3 mb-3 border-0">
        <form method="GET" action="<?= BASE_URL ?>/bolsa/reportes">
            <input type="hidden" name="tipo" value="empresas">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Estado de Empresa:</label>
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todas las empresas</option>
                        <option value="1" <?= (($filtros['estado'] ?? '') === 1 || ($filtros['estado'] ?? '') === '1') ? 'selected' : '' ?>>Activas</option>
                        <option value="0" <?= (($filtros['estado'] ?? '') === 0 || ($filtros['estado'] ?? '') === '0') ? 'selected' : '' ?>>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-8 mb-2 text-md-right">
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mr-1">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/bolsa/reportes?tipo=empresas" class="btn btn-outline-secondary btn-sm px-3 shadow-sm mr-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </a>
                    <?php
                    $queryEmp = http_build_query([
                        'estado' => $filtros['estado']
                    ]);
                    ?>
                    <a href="<?= BASE_URL ?>/bolsa/reportes/pdfEmpresas?<?= $queryEmp ?>" target="_blank" class="btn btn-danger btn-sm shadow-sm font-weight-bold <?= empty($empresasData) ? 'disabled' : '' ?>">
                        <i class="fas fa-file-pdf mr-1"></i> Generar Directorio PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA DE EMPRESAS -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-reporte-empresas" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">RUC</th>
                        <th class="text-center">Empresa / Razón Social</th>
                        <th class="text-center">Dirección</th>
                        <th class="text-center">Teléfono</th>
                        <th class="text-center">Correo</th>
                        <th class="text-center">Convocatorias</th>
                        <th class="text-center">Candidatos</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empresasData)): ?>
                        <?php foreach ($empresasData as $idx => $emp): 
                            $esAct = ((int)$emp['estado'] === 1);
                        ?>
                            <tr>
                                <td class="text-center font-weight-bold text-muted"><?= $idx + 1 ?></td>
                                <td class="text-center font-weight-bold"><?= htmlspecialchars($emp['ruc'] ?? '-') ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($emp['empresa']) ?></strong>
                                    <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> Reg: <?= date('d/m/Y', strtotime($emp['fecha_registro'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($emp['direccion'] ?? 'No registrada') ?></td>
                                <td class="text-center"><?= htmlspecialchars($emp['telefono'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($emp['email'] ?? '-') ?></td>
                                <td class="text-center font-weight-bold">
                                    <?= (int)($emp['total_ofertas'] ?? 0) ?>
                                    <small class="text-success d-block">(<?= (int)($emp['ofertas_vigentes'] ?? 0) ?> vig.)</small>
                                </td>
                                <td class="text-center font-weight-bold text-primary">
                                    <?= (int)($emp['total_candidatos'] ?? 0) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $esAct ? 'badge-success' : 'badge-secondary' ?> px-2 py-1">
                                        <?= $esAct ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables en la tabla activa
        if ($('#tabla-reporte-postulaciones').length) {
            $('#tabla-reporte-postulaciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                order: [[6, 'desc']]
            });
        }
        if ($('#tabla-reporte-ofertas').length) {
            $('#tabla-reporte-ofertas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                order: [[5, 'desc']]
            });
        }
        if ($('#tabla-reporte-empresas').length) {
            $('#tabla-reporte-empresas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                order: [[2, 'asc']]
            });
        }
    });
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
