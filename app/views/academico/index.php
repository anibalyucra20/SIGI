<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Estilos personalizados para el Dashboard Académico -->
<style>
  .kpi-card {
    border: none;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }

  .kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  }

  .kpi-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
  }

  .chart-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  .progress-thin {
    height: 6px;
    border-radius: 3px;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(63, 81, 181, 0.03);
  }

  /* Estilos para Paginación de Tablas */
  .pagination .page-link {
    color: #495057;
    border-radius: 6px;
    margin: 0 2px;
    font-size: 12px;
    padding: 5px 10px;
    border: 1px solid #dee2e6;
    transition: all 0.15s ease-in-out;
  }
  .pagination .page-item.active .page-link {
    background-color: #3f51b5;
    border-color: #3f51b5;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(63, 81, 181, 0.3);
  }
  .pagination .page-link:hover:not(.active) {
    background-color: #f1f3f9;
    color: #3f51b5;
    border-color: #c7d2fe;
  }
  .pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
    border-color: #e9ecef;
  }
  .panel-paginacion-tabla {
    background-color: #fafbfc;
    border-radius: 0 0 8px 8px;
    padding: 10px 15px;
  }

  .badge-pulse-danger {
    animation: pulse-danger 2s infinite;
  }

  @keyframes pulse-danger {
    0% {
      box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }

    70% {
      box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
    }

    100% {
      box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
  }

  .nav-pills .nav-link {
    border-radius: 8px;
    font-weight: 500;
    color: #495057;
  }

  .nav-pills .nav-link.active {
    background-color: #3f51b5;
    color: #fff;
    box-shadow: 0 4px 10px rgba(63, 81, 181, 0.3);
  }

  /* Estilos específicos para la vista de Coordinación y Reportes */
  .badge-registrado {
    background-color: #00b074;
    color: #ffffff;
    font-weight: 600;
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.3px;
  }

  .badge-pendiente {
    background-color: #f59e0b;
    color: #ffffff;
    font-weight: 600;
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
  }

  .report-tile-card {
    border: none;
    border-radius: 12px;
    background: #ffffff;
    transition: all 0.25s ease;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    text-decoration: none !important;
    display: block;
    height: 100%;
  }

  .report-tile-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(63, 81, 181, 0.15);
  }

  .report-tile-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 12px;
  }

  .table-uds thead th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 13px;
    border-bottom: 2px solid #e2e8f0;
    padding: 14px 16px;
  }

  .table-uds tbody td {
    padding: 16px;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
  }

  .prog-blue {
    background-color: #0284c7;
  }

  .prog-green {
    background-color: #10b981;
  }

  .prog-purple {
    background-color: #8b5cf6;
  }

  .badge-purple {
    background-color: #8b5cf6;
    color: #ffffff;
  }
</style>

<!-- ENCABEZADO DEL DASHBOARD -->
<div class="row align-items-center mb-4">
  <div class="col-md-7">
    <h3 class="mb-1 text-dark font-weight-bold">
      <?php if (!empty($esAutoridad)): ?>
        <?php if (!empty($esDirector)): ?>
          <i class="feather-award text-primary mr-2"></i>Panel de Dirección Académica
        <?php elseif (!empty($esJUA)): ?>
          <i class="feather-shield text-primary mr-2"></i>Panel de Jefatura de Unidad Académica
        <?php else: ?>
          <i class="feather-settings text-primary mr-2"></i><?= htmlspecialchars($tituloRol ?? 'Panel Académico') ?>
        <?php endif; ?>
      <?php elseif (!empty($esCoordinador)): ?>
        <i class="feather-grid text-primary mr-2"></i>Panel de Coordinación / Jefatura de Área
      <?php else: ?>
        <i class="feather-grid text-primary mr-2"></i>Panel Académico
      <?php endif; ?>
    </h3>
    <p class="text-muted mb-0">
      Bienvenido(a), <strong><?= htmlspecialchars($_SESSION['sigi_user_name'] ?? 'Usuario') ?></strong>
      &bull; Periodo Contexto: <span class="badge badge-light-primary px-2 py-1"><?= htmlspecialchars($periodo) ?></span>
      <?php if (!empty($esAutoridad)): ?>
        &bull; Cobertura: <span class="badge badge-primary px-2 py-1"><i class="fa fa-university mr-1"></i>Institucional (<?= count($programasAutoridad ?? []) ?> Programas)</span>
        <?php if (!empty($programaFiltroSeleccionado)): ?>
          <?php 
            $nomFiltro = '';
            foreach (($programasAutoridad ?? []) as $pa) {
              if ($pa['id'] == $programaFiltroSeleccionado) {
                $nomFiltro = $pa['nombre'];
                break;
              }
            }
          ?>
          &bull; Filtrado por: <span class="badge badge-info px-2 py-1"><i class="fa fa-filter mr-1"></i><?= htmlspecialchars($nomFiltro) ?></span>
        <?php endif; ?>
      <?php elseif (!empty($esCoordinador) && !empty($programasCoordinador)): ?>
        &bull; Programas Asignados:
        <?php foreach ($programasCoordinador as $prog): ?>
          <span class="badge badge-primary px-2 py-1 mr-1"><?= htmlspecialchars($prog['nombre']) ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </p>
  </div>
  <div class="col-md-5 text-md-right mt-3 mt-md-0">
    <?php if (!empty($esAutoridad) || !empty($esCoordinador)): ?>
      <div class="btn-group shadow-sm">
        <a href="<?= BASE_URL ?>/academico/reportes" class="btn btn-outline-primary">
          <i class="mdi mdi-chart-line mr-1"></i> Módulo de Reportes
        </a>
        <?php if (!empty($uds_count) && $uds_count > 0): ?>
          <button type="button" class="btn btn-primary" id="btnToggleVistaDocente">
            <i class="fa fa-chalkboard-teacher mr-1"></i> <span id="txtToggleDocente">Ver Mis Clases como Docente (<?= $uds_count ?>)</span>
          </button>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-primary shadow-sm">
        <i class="fa fa-book mr-1"></i> Mis Unidades Didácticas
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- ========================================================================= -->
<!-- SECCIÓN 0: VISTA DE JEFE DE UNIDAD ACADÉMICA / DIRECTOR (AUTORIDAD)       -->
<!-- ========================================================================= -->
<?php if (!empty($esAutoridad)): ?>

  <div id="seccion-autoridad">

    <!-- 0.1 BARRA SUPERIOR DE FILTRO POR PROGRAMA Y CONTEXTO INSTITUCIONAL -->
    <div class="card chart-card mb-4 bg-white border-0">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
          <div>
            <h6 class="font-weight-bold text-dark mb-0">
              <i class="fa fa-sliders-h text-primary mr-2"></i>Filtro de Visualización Institucional
            </h6>
            <small class="text-muted">Seleccione un Programa de Estudios específico para enfocar el análisis o mantenga la visión institucional consolidada.</small>
          </div>
          <?php if (!empty($programaFiltroSeleccionado)): ?>
            <div class="mt-2 mt-md-0">
              <a href="<?= BASE_URL ?>/academico?periodo_filtro=<?= $periodoFiltroSeleccionado ?>" class="btn btn-sm btn-outline-danger">
                <i class="fa fa-times mr-1"></i> Ver Toda la Institución
              </a>
            </div>
          <?php endif; ?>
        </div>

        <div class="row mt-3 pt-2 border-top align-items-end">
          <div class="col-lg-6 col-md-7 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Programa de Estudios:</label>
            <div class="input-group input-group-sm">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light border-right-0"><i class="fa fa-university text-primary"></i></span>
              </div>
              <select id="filtro-autoridad-programa" class="form-control form-control-sm font-weight-bold text-primary">
                <option value="todos" <?= empty($programaFiltroSeleccionado) ? 'selected' : '' ?>>
                  🏢 Todos los Programas de Estudio (Visión Institucional Global)
                </option>
                <?php foreach (($programasAutoridad ?? []) as $prog): ?>
                  <option value="<?= $prog['id'] ?>" <?= ($prog['id'] == $programaFiltroSeleccionado) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prog['codigo'] ? $prog['codigo'] . ' - ' : '') ?><?= htmlspecialchars($prog['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-lg-4 col-md-5 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Periodo Académico (Histórico):</label>
            <div class="input-group input-group-sm">
              <div class="input-group-prepend">
                <span class="input-group-text bg-light border-right-0"><i class="fa fa-calendar-alt text-secondary"></i></span>
              </div>
              <select id="filtro-autoridad-periodo" class="form-control form-control-sm">
                <?php foreach (($periodosAcademicos ?? []) as $per): ?>
                  <option value="<?= $per['id'] ?>" <?= ($per['id'] == $periodoFiltroSeleccionado) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($per['nombre']) ?> <?= ($per['id'] == $id_periodo) ? '(Periodo Actual)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-lg-2 col-md-12 mb-2 text-md-right">
            <a href="<?= BASE_URL ?>/academico/reportes" class="btn btn-sm btn-outline-primary btn-block">
              <i class="mdi mdi-chart-line mr-1"></i> Reportes
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 0.2 ACCESOS DIRECTOS A REPORTES OFICIALES INSTITUCIONALES -->
    <div class="card chart-card mb-4 bg-light border-0">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="font-weight-bold text-dark mb-0">
              <i class="mdi mdi-file-document-multiple text-primary mr-1" style="font-size: 18px;"></i> Accesos Directos a Reportes Oficiales
            </h6>
            <small class="text-muted">Generación inmediata de reportes académicos para la Jefatura de Unidad Académica y Dirección</small>
          </div>
          <a href="<?= BASE_URL ?>/academico/reportes" class="btn btn-sm btn-outline-primary">
            Ver Todos los Reportes <i class="fa fa-arrow-right ml-1"></i>
          </a>
        </div>

        <div class="row">
          <!-- Nómina de Matrícula -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#repNomina" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-primary text-primary mx-auto">
                <i class="fa fa-clipboard-list"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Nómina de Matrícula</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">PDF Oficial</span>
            </a>
          </div>

          <!-- Consolidado de Calificaciones -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_consolidado" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-success text-success mx-auto">
                <i class="fa fa-file-invoice"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Consolidado</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Calificaciones</span>
            </a>
          </div>

          <!-- Consolidado Detallado -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_detallado" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-info text-info mx-auto">
                <i class="fa fa-table"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Consolidado Detallado</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Por Criterio</span>
            </a>
          </div>

          <!-- Reporte Individual -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_individual" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-warning text-warning mx-auto">
                <i class="fa fa-user-graduate"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reporte Individual</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Boleta por Alumno</span>
            </a>
          </div>

          <!-- Primeros Puestos -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_primeros_puestos" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-danger text-danger mx-auto">
                <i class="fa fa-award"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Primeros Puestos</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Cuadro de Honor</span>
            </a>
          </div>

          <!-- Control Diario / Asistencia -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_control_diario" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-secondary text-secondary mx-auto">
                <i class="fa fa-calendar-check"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Control Asistencia</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Registro Diario</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 0.3 TARJETAS KPI EJECUTIVAS MACRO (6 MÉTRICAS PRINCIPALES) -->
    <div class="row mb-4">
      <!-- 1. Programas Activos -->
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Programas Activos</p>
                <h3 class="font-weight-bold mb-0 text-primary"><?= $resumenAutoridad['total_programas'] ?? 0 ?></h3>
                <small class="text-muted" style="font-size: 11px;"><?= $resumenAutoridad['total_uds'] ?? 0 ?> UDs programadas</small>
              </div>
              <div class="kpi-icon-circle bg-light-primary text-primary" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-university"></i>
              </div>
            </div>
            <div class="mt-2 small text-muted">
              <i class="fa fa-chalkboard-teacher text-info mr-1"></i><?= $resumenAutoridad['total_docentes'] ?? 0 ?> docentes
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Matrícula Institucional -->
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Matrícula Total</p>
                <h3 class="font-weight-bold mb-0 text-dark"><?= $resumenAutoridad['total_estudiantes'] ?? 0 ?></h3>
                <small class="text-muted" style="font-size: 11px;">Alumnos únicos</small>
              </div>
              <div class="kpi-icon-circle bg-light text-dark" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-users"></i>
              </div>
            </div>
            <div class="mt-2 small text-muted">
              <i class="fa fa-check-circle text-success mr-1"></i>Matrícula vigente
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Avance de Sílabos -->
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Avance Sílabos</p>
                <h3 class="font-weight-bold mb-0" style="color: #8b5cf6;"><?= $resumenAutoridad['porcentaje_silabos'] ?? 0 ?><small style="font-size: 13px;">%</small></h3>
                <small class="text-muted" style="font-size: 11px;"><?= $resumenAutoridad['silabos_registrados'] ?? 0 ?> de <?= $resumenAutoridad['total_uds'] ?? 0 ?> UDs</small>
              </div>
              <div class="kpi-icon-circle bg-light" style="color: #8b5cf6; width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-book-open"></i>
              </div>
            </div>
            <div class="mt-2">
              <div class="progress progress-thin">
                <div class="progress-bar prog-purple" style="width: <?= $resumenAutoridad['porcentaje_silabos'] ?? 0 ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Avance de Sesiones -->
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Avance Sesiones</p>
                <h3 class="font-weight-bold mb-0 text-info"><?= $resumenAutoridad['porcentaje_sesiones'] ?? 0 ?><small style="font-size: 13px;">%</small></h3>
                <small class="text-muted" style="font-size: 11px;"><?= $resumenAutoridad['sesiones_desarrolladas'] ?? 0 ?> de <?= $resumenAutoridad['sesiones_programadas'] ?? 0 ?></small>
              </div>
              <div class="kpi-icon-circle bg-light text-info" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-calendar-check"></i>
              </div>
            </div>
            <div class="mt-2">
              <div class="progress progress-thin">
                <div class="progress-bar prog-blue" style="width: <?= $resumenAutoridad['porcentaje_sesiones'] ?? 0 ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Avance de Calificaciones -->
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Calificaciones</p>
                <h3 class="font-weight-bold mb-0 text-success"><?= $resumenAutoridad['porcentaje_calificaciones'] ?? 0 ?><small style="font-size: 13px;">%</small></h3>
                <small class="text-muted" style="font-size: 11px;"><?= $resumenAutoridad['notas_registradas'] ?? 0 ?> de <?= $resumenAutoridad['notas_esperadas'] ?? 0 ?></small>
              </div>
              <div class="kpi-icon-circle bg-light text-success" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-chart-line"></i>
              </div>
            </div>
            <div class="mt-2">
              <div class="progress progress-thin">
                <div class="progress-bar prog-green" style="width: <?= $resumenAutoridad['porcentaje_calificaciones'] ?? 0 ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Alertas Tempranas -->
      <?php $totalAlertasAut = ($resumenAutoridad['total_alertas_academicas'] ?? 0) + ($resumenAutoridad['total_alertas_asistencia'] ?? 0); ?>
      <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Alertas Tempranas</p>
                <h3 class="font-weight-bold mb-0 <?= $totalAlertasAut > 0 ? 'text-danger' : 'text-success' ?>"><?= $totalAlertasAut ?></h3>
                <small class="text-muted" style="font-size: 11px;"><?= $resumenAutoridad['total_alertas_academicas'] ?? 0 ?> acad &bull; <?= $resumenAutoridad['total_alertas_asistencia'] ?? 0 ?> inasist.</small>
              </div>
              <div class="kpi-icon-circle bg-light <?= $totalAlertasAut > 0 ? 'text-danger badge-pulse-danger' : 'text-success' ?>" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fa fa-bell"></i>
              </div>
            </div>
            <div class="mt-2 small">
              <a href="#seccion-alertas-autoridad" class="text-danger font-weight-bold text-decoration-none">
                Revisar alertas <i class="fa fa-arrow-down ml-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 0.4 GRÁFICOS ESTADÍSTICOS COMPARATIVOS INSTITUCIONALES -->
    <div class="row mb-4">
      <!-- Gráfico 1: Comparativo por Programa de Estudios -->
      <div class="col-xl-8 mb-4 mb-xl-0">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
              <h5 class="card-title font-weight-bold text-dark mb-1">
                <?php if (($chartTipoAutoridad ?? 'programas') === 'programas'): ?>
                  <i class="fa fa-chart-bar text-primary mr-2"></i>Avance Comparativo por Programa de Estudios (Periodo Actual)
                <?php else: ?>
                  <i class="fa fa-chart-bar text-primary mr-2"></i>Avance Comparativo por Unidad Didáctica (Periodo Actual)
                <?php endif; ?>
              </h5>
              <p class="text-muted small mb-0">
                <?php if (($chartTipoAutoridad ?? 'programas') === 'programas'): ?>
                  Comparativa institucional de Sílabos, Sesiones desarrolladas y Calificaciones registradas por Programa
                <?php else: ?>
                  Comparativa entre Sesiones de Aprendizaje desarrolladas vs Calificaciones registradas
                <?php endif; ?>
              </p>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0">
              <?php if (($chartTipoAutoridad ?? 'programas') === 'programas'): ?>
                <span class="badge mr-2 px-2 py-1" style="background-color: #8b5cf6; color: white;">Sílabos</span>
              <?php endif; ?>
              <span class="badge mr-2 px-2 py-1" style="background-color: #0284c7; color: white;">Sesiones</span>
              <span class="badge px-2 py-1" style="background-color: #10b981; color: white;">Calificaciones</span>
            </div>
          </div>
          <div class="card-body px-4 pb-4">
            <div style="position: relative; height: 320px;">
              <canvas id="chartAvanceAutoridad"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico 2: Rendimiento Académico Institucional (Doughnut) -->
      <div class="col-xl-4">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-chart-pie text-success mr-2"></i>Rendimiento Académico Institucional
            </h5>
            <p class="text-muted small mb-0">Distribución de estudiantes según Promedio Final de Indicadores (PFI)</p>
          </div>
          <div class="card-body px-4 pb-4 d-flex flex-column justify-content-center">
            <div style="position: relative; height: 210px;">
              <canvas id="chartRendimientoAutoridad"></canvas>
            </div>
            <div class="mt-3">
              <div class="row text-center">
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Aprobados</div>
                  <strong class="text-success h6 mb-0"><?= $chartRendimientoAutoridad['aprobados'] ?? 0 ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Riesgo (10-12)</div>
                  <strong class="text-warning h6 mb-0"><?= $chartRendimientoAutoridad['en_riesgo'] ?? 0 ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Desaprobados</div>
                  <strong class="text-danger h6 mb-0"><?= $chartRendimientoAutoridad['desaprobados'] ?? 0 ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Sin Calif.</div>
                  <strong class="text-secondary h6 mb-0"><?= $chartRendimientoAutoridad['sin_calificar'] ?? 0 ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 0.5 MATRIZ COMPARATIVA EJECUTIVA POR PROGRAMA DE ESTUDIOS -->
    <div class="card chart-card mb-4">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title font-weight-bold text-dark mb-1">
            <i class="fa fa-table text-primary mr-2"></i>Matriz de Monitoreo por Programa de Estudios
          </h5>
          <p class="text-muted small mb-0">Resumen integral de cumplimiento académico por cada programa formativo</p>
        </div>
        <span class="badge badge-light-primary px-3 py-2 font-weight-bold">
          <?= count($programasComparativa ?? []) ?> Programas
        </span>
      </div>
      <div class="card-body px-4 pb-4 pt-2">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tabla-aut-programas">
            <thead class="thead-light">
              <tr>
                <th style="min-width: 240px;">Programa de Estudios</th>
                <th class="text-center" style="min-width: 100px;">Matrícula</th>
                <th class="text-center" style="min-width: 80px;">UDs</th>
                <th style="min-width: 150px;">Avance Sílabos</th>
                <th style="min-width: 170px;">Avance Sesiones</th>
                <th style="min-width: 170px;">Avance Calificaciones</th>
                <th class="text-center" style="min-width: 130px;">Alertas</th>
                <th class="text-center" style="min-width: 120px;">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($programasComparativa ?? []) as $progComp): ?>
                <tr class="fila-aut-prog">
                  <td>
                    <div class="font-weight-bold text-dark mb-0">
                      <?= htmlspecialchars($progComp['nombre']) ?>
                    </div>
                    <?php if (!empty($progComp['codigo'])): ?>
                      <span class="badge badge-light-secondary font-weight-normal"><?= htmlspecialchars($progComp['codigo']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-light-primary font-weight-bold px-2 py-1" style="font-size: 13px;">
                      <?= $progComp['total_matriculados'] ?>
                    </span>
                  </td>
                  <td class="text-center font-weight-bold text-muted">
                    <?= $progComp['total_uds'] ?>
                  </td>
                  <td>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="small font-weight-bold" style="color: #8b5cf6;"><?= $progComp['porcentaje_silabos'] ?>%</span>
                      <small class="text-muted"><?= $progComp['total_silabos'] ?>/<?= $progComp['total_uds'] ?></small>
                    </div>
                    <div class="progress progress-thin">
                      <div class="progress-bar prog-purple" style="width: <?= $progComp['porcentaje_silabos'] ?>%;"></div>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="small font-weight-bold text-info"><?= $progComp['porcentaje_sesiones'] ?>%</span>
                      <small class="text-muted"><?= $progComp['total_sesiones'] ?>/<?= $progComp['total_actividades'] ?></small>
                    </div>
                    <div class="progress progress-thin">
                      <div class="progress-bar prog-blue" style="width: <?= $progComp['porcentaje_sesiones'] ?>%;"></div>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="small font-weight-bold text-success"><?= $progComp['porcentaje_calificaciones'] ?>%</span>
                      <small class="text-muted"><?= $progComp['total_notas_registradas'] ?>/<?= $progComp['total_notas_esperadas'] ?></small>
                    </div>
                    <div class="progress progress-thin">
                      <div class="progress-bar prog-green" style="width: <?= $progComp['porcentaje_calificaciones'] ?>%;"></div>
                    </div>
                  </td>
                  <td class="text-center">
                    <?php $totAlertProg = $progComp['alertas_academicas'] + $progComp['alertas_asistencia']; ?>
                    <?php if ($totAlertProg > 0): ?>
                      <span class="badge badge-light-danger font-weight-bold px-2 py-1" title="<?= $progComp['alertas_academicas'] ?> académicas, <?= $progComp['alertas_asistencia'] ?> inasistencias">
                        <i class="fa fa-exclamation-triangle mr-1"></i><?= $totAlertProg ?>
                      </span>
                    <?php else: ?>
                      <span class="badge badge-light-success font-weight-bold px-2 py-1">
                        <i class="fa fa-check mr-1"></i>0
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="<?= BASE_URL ?>/academico?programa_filtro=<?= $progComp['id'] ?>&periodo_filtro=<?= $periodoFiltroSeleccionado ?>" class="btn btn-xs btn-outline-primary" title="Filtrar vista para este programa">
                      <i class="fa fa-search mr-1"></i> Filtrar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Paginación de Tabla Programas -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-aut-programas">
          <div class="d-flex align-items-center mb-2 mb-sm-0">
            <span class="small text-muted mr-2">Mostrar:</span>
            <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-aut-programas">
              <option value="5">5</option>
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="todos">Todos</option>
            </select>
            <span class="small text-muted ml-3" id="info-aut-programas"></span>
          </div>
          <nav aria-label="Paginación Programas">
            <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-aut-programas"></ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- 0.6 MONITOR INSTITUCIONAL DE ALERTAS TEMPRANAS -->
    <div class="card chart-card mb-4" id="seccion-alertas-autoridad">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-exclamation-circle text-danger mr-2"></i>Monitor Institucional de Alertas Tempranas (Periodo Actual)
            </h5>
            <p class="text-muted small mb-0">Estudiantes identificados en riesgo de bajo rendimiento (PFI &lt; 13) o riesgo de inhabilitación por inasistencias (DPI &ge; 20%)</p>
          </div>
          <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center">
            <!-- Filtro Programa Alertas -->
            <select id="filtro-autoridad-alertas-prog" class="form-control form-control-sm mr-2 mb-1 mb-md-0" style="max-width: 220px;">
              <option value="todos">Todos los Programas</option>
              <?php foreach (($programasAutoridad ?? []) as $prog): ?>
                <option value="<?= $prog['id'] ?>"><?= htmlspecialchars($prog['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <!-- Filtro UD Alertas -->
            <select id="filtro-autoridad-alertas-ud" class="form-control form-control-sm mr-2 mb-1 mb-md-0" style="max-width: 220px;">
              <option value="todos">Todas las Unidades Didácticas</option>
              <?php foreach (($unidadesTablaAutoridad ?? []) as $u): ?>
                <?php if ($u['id_periodo_academico'] == $periodoFiltroSeleccionado): ?>
                  <option value="<?= $u['id_programacion_ud'] ?>" data-prog="<?= $u['id_programa_estudio'] ?>">
                    <?= htmlspecialchars($u['unidad_nombre']) ?> (<?= htmlspecialchars($u['semestre_nombre']) ?>)
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
            <!-- Pills -->
            <ul class="nav nav-pills" id="pills-alertas-autoridad" role="tablist">
              <li class="nav-item">
                <a class="nav-link active py-1 px-3" id="tab-aut-academicas" data-toggle="pill" href="#panel-aut-academicas" role="tab">
                  <i class="fa fa-graduation-cap mr-1"></i> Rendimiento (<span id="badge-total-aut-academicas"><?= count($alertasAcademicasAutoridad ?? []) ?></span>)
                </a>
              </li>
              <li class="nav-item ml-1">
                <a class="nav-link py-1 px-3" id="tab-aut-asistencia" data-toggle="pill" href="#panel-aut-asistencia" role="tab">
                  <i class="fa fa-calendar-times mr-1"></i> Asistencia (<span id="badge-total-aut-asistencia"><?= count($alertasAsistenciaAutoridad ?? []) ?></span>)
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body px-4 pb-4 pt-3">
        <div class="tab-content" id="pills-tabContentAlertasAut">
          <!-- Pestaña 1: Alertas Académicas -->
          <div class="tab-pane fade show active" id="panel-aut-academicas" role="tabpanel">
            <?php if (empty($alertasAcademicasAutoridad)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success" style="font-size: 32px;"></i>
                <p class="mt-2 mb-0 font-weight-bold">Excelente: No se registran estudiantes con alertas de bajo rendimiento en el periodo actual.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-aut-alertas-acad">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Programa de Estudios</th>
                      <th>Semestre</th>
                      <th>Unidad Didáctica</th>
                      <th>Docente</th>
                      <th class="text-center">Promedio Final</th>
                      <th>Condición</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAcademicasAutoridad as $alerta): ?>
                      <tr class="fila-aut-alerta-academica" data-pud="<?= $alerta['id_pud'] ?>" data-prog="<?= $alerta['id_programa_estudio'] ?>">
                        <td><strong><?= htmlspecialchars($alerta['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($alerta['dni']) ?></td>
                        <td><span class="badge badge-light-primary"><?= htmlspecialchars($alerta['programa']) ?></span></td>
                        <td><span class="badge badge-light-info"><?= htmlspecialchars($alerta['semestre']) ?></span></td>
                        <td><?= htmlspecialchars($alerta['unidad']) ?></td>
                        <td><small class="text-muted"><i class="fa fa-user-tie mr-1"></i><?= htmlspecialchars($alerta['docente']) ?></small></td>
                        <td class="text-center font-weight-bold <?= $alerta['tipo'] === 'desaprobado' ? 'text-danger' : 'text-warning' ?>" style="font-size: 15px;">
                          <?= number_format($alerta['promedio'], 0) ?>
                        </td>
                        <td>
                          <?php if ($alerta['tipo'] === 'desaprobado'): ?>
                            <span class="badge badge-danger"><?= htmlspecialchars($alerta['mensaje']) ?></span>
                          <?php else: ?>
                            <span class="badge badge-warning text-white"><?= htmlspecialchars($alerta['mensaje']) ?></span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-info" title="Ver Registro de Calificaciones">
                            <i class="fa fa-eye"></i> Calificaciones
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-aut-academica-vacio-filtro" style="display: none;">
                      <td colspan="9" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas académicas con los filtros seleccionados.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Académicas Autoridad -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-aut-alertas-acad">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-aut-alertas-acad">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-aut-alertas-acad"></span>
                </div>
                <nav aria-label="Paginación Alertas Académicas">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-aut-alertas-acad"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>

          <!-- Pestaña 2: Alertas de Asistencia -->
          <div class="tab-pane fade" id="panel-aut-asistencia" role="tabpanel">
            <?php if (empty($alertasAsistenciaAutoridad)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success" style="font-size: 32px;"></i>
                <p class="mt-2 mb-0 font-weight-bold">Excelente: No se registran estudiantes con alertas de inasistencia en el periodo actual.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-aut-alertas-asis">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Programa de Estudios</th>
                      <th>Semestre</th>
                      <th>Unidad Didáctica</th>
                      <th>Docente</th>
                      <th class="text-center">Faltas / Total</th>
                      <th class="text-center">% Inasistencias</th>
                      <th>Condición</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAsistenciaAutoridad as $alerta): ?>
                      <tr class="fila-aut-alerta-asistencia" data-pud="<?= $alerta['id_pud'] ?>" data-prog="<?= $alerta['id_programa_estudio'] ?>">
                        <td><strong><?= htmlspecialchars($alerta['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($alerta['dni']) ?></td>
                        <td><span class="badge badge-light-primary"><?= htmlspecialchars($alerta['programa']) ?></span></td>
                        <td><span class="badge badge-light-info"><?= htmlspecialchars($alerta['semestre']) ?></span></td>
                        <td><?= htmlspecialchars($alerta['unidad']) ?></td>
                        <td><small class="text-muted"><i class="fa fa-user-tie mr-1"></i><?= htmlspecialchars($alerta['docente']) ?></small></td>
                        <td class="text-center"><?= $alerta['faltas'] ?> / <?= $alerta['total_sesiones'] ?></td>
                        <td class="text-center font-weight-bold <?= $alerta['estado'] === 'inhabilitado' ? 'text-danger' : 'text-warning' ?>">
                          <?= $alerta['porcentaje'] ?>%
                        </td>
                        <td>
                          <?php if ($alerta['estado'] === 'inhabilitado'): ?>
                            <span class="badge badge-danger"><i class="fa fa-ban mr-1"></i>Inhabilitado DPI (&gt;30%)</span>
                          <?php else: ?>
                            <span class="badge badge-warning text-white"><i class="fa fa-exclamation-triangle mr-1"></i>En Riesgo DPI (20% - 30%)</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-success" title="Ver Control de Asistencia">
                            <i class="fa fa-calendar-check"></i> Asistencia
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-aut-asistencia-vacio-filtro" style="display: none;">
                      <td colspan="10" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas de inasistencia con los filtros seleccionados.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Asistencia Autoridad -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-aut-alertas-asis">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-aut-alertas-asis">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-aut-alertas-asis"></span>
                </div>
                <nav aria-label="Paginación Alertas Asistencia">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-aut-alertas-asis"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 0.7 GESTIÓN INTEGRAL DE UNIDADES DIDÁCTICAS (TABLA INSTITUCIONAL COMPLETA) -->
    <div class="card chart-card mb-4">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-th-large text-primary mr-2"></i>Gestión de Unidades Didácticas Institucionales
            </h5>
            <p class="text-muted small mb-0">Monitoreo y accesos directos a Sílabos, Sesiones de Aprendizaje, Asistencia, Calificaciones e Informes</p>
          </div>
          <div class="mt-2 mt-lg-0">
            <span class="text-muted small">
              Mostrando <strong id="contador-uds-visibles-aut"><?= count($unidadesTablaAutoridad ?? []) ?></strong> de <?= count($unidadesTablaAutoridad ?? []) ?> unidades didácticas
            </span>
          </div>
        </div>

        <!-- BARRA DE FILTROS AVANZADOS -->
        <div class="row mt-3 pt-3 border-top align-items-end">
          <!-- Filtro Programa -->
          <div class="col-lg-3 col-md-6 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Programa de Estudios:</label>
            <select id="filtro-aut-tabla-prog" class="form-control form-control-sm">
              <option value="todos">Todos los Programas</option>
              <?php foreach (($programasAutoridad ?? []) as $prog): ?>
                <option value="<?= $prog['id'] ?>" <?= ($prog['id'] == $programaFiltroSeleccionado) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($prog['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Plan de Estudios -->
          <div class="col-lg-2 col-md-6 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Plan de Estudios:</label>
            <select id="filtro-aut-tabla-plan" class="form-control form-control-sm">
              <option value="todos">Todos los Planes</option>
              <?php foreach (($planesAutoridad ?? []) as $plan): ?>
                <option value="<?= $plan['id'] ?>" data-prog="<?= $plan['id_programa_estudios'] ?>">
                  <?= htmlspecialchars($plan['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Periodo Académico (Histórico) -->
          <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Periodo Académico:</label>
            <select id="filtro-aut-tabla-periodo" class="form-control form-control-sm bg-light-primary text-primary font-weight-bold">
              <?php foreach (($periodosAcademicos ?? []) as $per): ?>
                <option value="<?= $per['id'] ?>" <?= ($per['id'] == $periodoFiltroSeleccionado) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($per['nombre']) ?> <?= (!empty($id_periodo) && $per['id'] == $id_periodo) ? '(Actual)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Semestre -->
          <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Semestre:</label>
            <select id="filtro-aut-tabla-semestre" class="form-control form-control-sm">
              <option value="todos">Todos los Semestres</option>
              <?php foreach (($semestresCatalogo ?? []) as $sem): ?>
                <option value="<?= htmlspecialchars($sem) ?>"><?= htmlspecialchars($sem) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Buscador en tiempo real -->
          <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Buscar:</label>
            <div class="input-group input-group-sm">
              <input type="text" id="busqueda-aut-ud" class="form-control form-control-sm" placeholder="Buscar UD, docente...">
            </div>
          </div>

          <!-- Botón Limpiar -->
          <div class="col-lg-1 col-md-2 mb-2 text-md-right">
            <button type="button" id="btn-limpiar-filtros-aut" class="btn btn-sm btn-outline-secondary btn-block" title="Limpiar filtros">
              <i class="fa fa-sync-alt"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="card-body px-4 pb-4 pt-2">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-uds" id="tabla-gestion-autoridad">
            <thead>
              <tr>
                <th style="min-width: 270px;">Unidad Didáctica</th>
                <th style="min-width: 210px;">Programa / Semestre</th>
                <th style="min-width: 120px;">Turno / Sec</th>
                <th style="min-width: 130px;">Sílabo</th>
                <th style="min-width: 170px;">Avance Sesiones</th>
                <th style="min-width: 170px;">Avance Calificaciones</th>
                <th class="text-center" style="min-width: 180px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($unidadesTablaAutoridad)): ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa fa-folder-open text-secondary mb-2" style="font-size: 36px;"></i>
                    <p class="mb-0 font-weight-bold">No se encontraron unidades didácticas programadas para el periodo seleccionado.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($unidadesTablaAutoridad as $ud): ?>
                  <tr class="fila-ud-aut"
                    data-prog="<?= $ud['id_programa_estudio'] ?>"
                    data-plan="<?= $ud['id_plan_estudio'] ?>"
                    data-periodo="<?= $ud['id_periodo_academico'] ?>"
                    data-semestre="<?= htmlspecialchars($ud['semestre_nombre']) ?>"
                    data-busqueda="<?= htmlspecialchars(mb_strtolower($ud['unidad_nombre'] . ' ' . $ud['docente_nombre'] . ' ' . $ud['programa_nombre'])) ?>">

                    <!-- Unidad Didáctica -->
                    <td>
                      <div class="font-weight-bold text-dark text-uppercase mb-1" style="font-size: 14px; letter-spacing: 0.3px;">
                        <?= htmlspecialchars($ud['unidad_nombre']) ?>
                      </div>
                      <div class="small text-muted mb-1">
                        <i class="fa fa-users text-secondary mr-1"></i><?= $ud['matriculados'] ?> estudiantes matriculados
                      </div>
                      <div class="small text-muted">
                        <i class="fa fa-user-tie text-info mr-1"></i>Docente: <strong><?= htmlspecialchars($ud['docente_nombre']) ?></strong>
                      </div>
                    </td>

                    <!-- Programa / Semestre -->
                    <td>
                      <div class="font-weight-bold text-dark text-uppercase" style="font-size: 13px;">
                        <?= htmlspecialchars($ud['programa_nombre']) ?>
                      </div>
                      <div class="font-weight-bold text-primary" style="font-size: 14px;">
                        <?= htmlspecialchars($ud['semestre_nombre']) ?>
                      </div>
                      <?php if (!empty($ud['plan_nombre'])): ?>
                        <div class="small text-muted">Plan <?= htmlspecialchars($ud['plan_nombre']) ?></div>
                      <?php endif; ?>
                    </td>

                    <!-- Turno / Sec -->
                    <td>
                      <span class="text-muted font-weight-500" style="font-size: 13px;">
                        <?= htmlspecialchars($ud['turno_seccion']) ?>
                      </span>
                    </td>

                    <!-- Sílabo -->
                    <td>
                      <?php if ($ud['silabo_registrado']): ?>
                        <span class="badge-registrado">
                          <i class="fa fa-check mr-1"></i> Registrado
                        </span>
                      <?php else: ?>
                        <span class="badge-pendiente">
                          <i class="fa fa-clock mr-1"></i> Pendiente
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Avance Sesiones -->
                    <td>
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small font-weight-bold text-dark"><?= $ud['porcentaje_sesiones'] ?>%</span>
                        <span class="small text-muted"><?= $ud['sesiones_registradas'] ?> de <?= $ud['sesiones_totales'] ?></span>
                      </div>
                      <div class="progress progress-thin">
                        <div class="progress-bar prog-blue" style="width: <?= $ud['porcentaje_sesiones'] ?>%;"></div>
                      </div>
                    </td>

                    <!-- Avance Calificaciones -->
                    <td>
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small font-weight-bold text-dark"><?= $ud['porcentaje_calificaciones'] ?>%</span>
                        <span class="small text-muted"><?= $ud['calificaciones_registradas'] ?> de <?= $ud['calificaciones_totales'] ?></span>
                      </div>
                      <div class="progress progress-thin">
                        <div class="progress-bar prog-green" style="width: <?= $ud['porcentaje_calificaciones'] ?>%;"></div>
                      </div>
                    </td>

                    <!-- Acciones -->
                    <td class="text-center">
                      <div class="btn-group">
                        <a href="<?= BASE_URL ?>/academico/silabos/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Sílabo">
                          <i class="fa fa-file-alt"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/sesiones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-info" title="Sesiones de Aprendizaje">
                          <i class="fa fa-calendar"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-success" title="Control de Asistencia">
                          <i class="fa fa-users"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-info" title="Registro de Calificaciones">
                          <i class="fa fa-edit"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-dark" title="Informe Final">
                          <i class="fa fa-chart-bar"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr id="fila-aut-vacia-filtro" style="display: none;">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa fa-filter text-secondary mb-2" style="font-size: 32px;"></i>
                    <p class="mb-0 font-weight-bold">No se encontraron unidades didácticas con los filtros seleccionados.</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Paginación Unidades Didácticas Autoridad -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-gestion-autoridad">
          <div class="d-flex align-items-center mb-2 mb-sm-0">
            <span class="small text-muted mr-2">Mostrar:</span>
            <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-gestion-autoridad">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="todos">Todos</option>
            </select>
            <span class="small text-muted ml-3" id="info-gestion-autoridad"></span>
          </div>
          <nav aria-label="Paginación Unidades Didácticas">
            <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-gestion-autoridad"></ul>
          </nav>
        </div>
      </div>
    </div>

  </div> <!-- /#seccion-autoridad -->

<?php endif; ?>

<!-- ========================================================================= -->
<!-- SECCIÓN 1: VISTA DE COORDINADOR / JEFE DE ÁREA                            -->
<!-- ========================================================================= -->
<?php if (!empty($esCoordinador) && empty($esAutoridad)): ?>

  <div id="seccion-coordinador">

    <!-- 1.1 ACCESOS DIRECTOS A REPORTES OFICIALES ASIGNADOS AL COORDINADOR -->
    <div class="card chart-card mb-4 bg-light border-0">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="font-weight-bold text-dark mb-0">
              <i class="mdi mdi-file-document-multiple text-primary mr-1" style="font-size: 18px;"></i> Accesos Directos a Reportes Oficiales
            </h6>
            <small class="text-muted">Generación inmediata de reportes académicos asignados a su programa de estudios</small>
          </div>
          <a href="<?= BASE_URL ?>/academico/reportes" class="btn btn-sm btn-outline-primary">
            Ver Todos los Reportes <i class="fa fa-arrow-right ml-1"></i>
          </a>
        </div>

        <div class="row">
          <!-- Nómina de Matrícula -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#repNomina" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-primary text-primary mx-auto">
                <i class="fa fa-clipboard-list"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Nómina de Matrícula</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">PDF Oficial</span>
            </a>
          </div>

          <!-- Consolidado de Calificaciones -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_consolidado" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-success text-success mx-auto">
                <i class="fa fa-file-invoice"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Consolidado</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Calificaciones</span>
            </a>
          </div>

          <!-- Consolidado Detallado -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_detallado" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-info text-info mx-auto">
                <i class="fa fa-table"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Consolidado Detallado</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Por Criterio</span>
            </a>
          </div>

          <!-- Reporte Individual -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_calif_individual" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-warning text-warning mx-auto">
                <i class="fa fa-user-graduate"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Reporte Individual</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Boleta por Alumno</span>
            </a>
          </div>

          <!-- Primeros Puestos -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_primeros_puestos" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light-danger text-danger mx-auto">
                <i class="fa fa-award"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Primeros Puestos</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Cuadro de Mérito</span>
            </a>
          </div>

          <!-- Control Diario -->
          <div class="col-xl-2 col-md-4 col-sm-6 mb-2 mb-xl-0">
            <a href="#" data-toggle="modal" data-target="#rep_control_diario" class="report-tile-card p-3 text-center">
              <div class="report-tile-icon bg-light text-secondary mx-auto">
                <i class="fa fa-calendar-check"></i>
              </div>
              <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Control Diario</h6>
              <span class="badge badge-light-secondary" style="font-size: 10px;">Asistencia</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 1.2 TARJETAS KPI ESTADÍSTICAS DEL PROGRAMA (PERIODO Y SEDE DE CONTEXTO) -->
    <div class="row mb-4">
      <!-- Total UDs y Docentes -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">UDs Programadas</p>
                <h2 class="font-weight-bold mb-0 text-dark"><?= $resumenCoordinador['total_uds'] ?></h2>
                <small class="text-muted"><i class="fa fa-chalkboard-teacher text-primary mr-1"></i><?= $resumenCoordinador['total_docentes'] ?> docentes asignados</small>
              </div>
              <div class="kpi-icon-circle bg-light text-primary">
                <i class="fa fa-layer-group"></i>
              </div>
            </div>
            <div class="mt-3">
              <small class="text-muted"><i class="fa fa-graduation-cap text-info mr-1"></i><?= $resumenCoordinador['total_programas'] ?> programa(s) coordinado(s)</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Estudiantes -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Matrícula del Programa</p>
              <h2 class="font-weight-bold mb-0 text-dark"><?= $resumenCoordinador['total_estudiantes'] ?></h2>
              <small class="text-muted"><i class="fa fa-users text-success mr-1"></i>Estudiantes en sus programas asignados</small>
            </div>
            <div class="kpi-icon-circle bg-light text-success">
              <i class="fa fa-user-friends"></i>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge badge-light-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>Contexto Activo</span>
          </div>
        </div>
      </div>



      <!-- Avance General de Sesiones -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Avance de Sesiones</p>
                <h2 class="font-weight-bold mb-0 text-info"><?= $resumenCoordinador['porcentaje_sesiones'] ?><small class="text-muted" style="font-size: 16px;">%</small></h2>
                <small class="text-muted"><?= $resumenCoordinador['sesiones_desarrolladas'] ?> de <?= $resumenCoordinador['sesiones_programadas'] ?> sesiones</small>
              </div>
              <div class="kpi-icon-circle bg-light text-info">
                <i class="fa fa-book-reader"></i>
              </div>
            </div>
            <div class="mt-2">
              <div class="progress progress-thin">
                <div class="progress-bar prog-blue" style="width: <?= $resumenCoordinador['porcentaje_sesiones'] ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Avance General de Calificaciones -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Avance de Calificaciones</p>
                <h2 class="font-weight-bold mb-0 text-success"><?= $resumenCoordinador['porcentaje_calificaciones'] ?><small class="text-muted" style="font-size: 16px;">%</small></h2>
                <small class="text-muted"><?= $resumenCoordinador['notas_registradas'] ?> de <?= $resumenCoordinador['notas_esperadas'] ?> notas</small>
              </div>
              <div class="kpi-icon-circle bg-light text-success">
                <i class="fa fa-chart-line"></i>
              </div>
            </div>
            <div class="mt-2">
              <div class="progress progress-thin">
                <div class="progress-bar prog-green" style="width: <?= $resumenCoordinador['porcentaje_calificaciones'] ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 1.3 GRÁFICOS ESTADÍSTICOS DEL PROGRAMA EN EL PERIODO DE CONTEXTO -->
    <div class="row mb-4">
      <!-- Avance Comparativo por UD -->
      <div class="col-xl-8 mb-4 mb-xl-0">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title font-weight-bold text-dark mb-1">
                <i class="fa fa-chart-bar text-primary mr-2"></i>Avance Comparativo por Unidad Didáctica (Periodo Actual)
              </h5>
              <p class="text-muted small mb-0">Comparativa entre Sesiones de Aprendizaje desarrolladas vs Calificaciones registradas</p>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge mr-2 px-2 py-1" style="background-color: #0284c7; color: white;">Sesiones</span>
              <span class="badge px-2 py-1" style="background-color: #10b981; color: white;">Calificaciones</span>
            </div>
          </div>
          <div class="card-body px-4 pb-4">
            <div style="position: relative; height: 320px;">
              <canvas id="chartAvanceCoord"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Distribución de Rendimiento Académico -->
      <div class="col-xl-4">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-chart-pie text-success mr-2"></i>Rendimiento de Estudiantes
            </h5>
            <p class="text-muted small mb-0">Distribución según Promedio Final de Indicadores (PFI)</p>
          </div>
          <div class="card-body px-4 pb-4 d-flex flex-column justify-content-center">
            <div style="position: relative; height: 210px;">
              <canvas id="chartRendimientoCoord"></canvas>
            </div>
            <div class="mt-3">
              <div class="row text-center">
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Aprobados</div>
                  <strong class="text-success h6 mb-0"><?= $chartRendimientoCoord['aprobados'] ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Riesgo (10-12)</div>
                  <strong class="text-warning h6 mb-0"><?= $chartRendimientoCoord['en_riesgo'] ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Desaprobados</div>
                  <strong class="text-danger h6 mb-0"><?= $chartRendimientoCoord['desaprobados'] ?></strong>
                </div>
                <div class="col-3 px-1">
                  <div class="small text-muted mb-1" style="font-size: 11px;">Sin Calif.</div>
                  <strong class="text-secondary h6 mb-0"><?= $chartRendimientoCoord['sin_calificar'] ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 1.4 ALERTAS TEMPRANAS DEL PROGRAMA (ACADÉMICAS Y DE ASISTENCIA) -->
    <div class="card chart-card mb-4">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-exclamation-circle text-danger mr-2"></i>Alertas Tempranas de Estudiantes (Periodo Actual)
            </h5>
            <p class="text-muted small mb-0">Monitoreo de bajo rendimiento académico (PFI &lt; 13) y riesgo de inhabilitación por inasistencias (DPI &ge; 20%)</p>
          </div>
          <div class="mt-3 mt-md-0 d-flex align-items-center">
            <select id="filtro-ud-alertas-coord" class="form-control form-control-sm mr-2" style="max-width: 250px;">
              <option value="todos">Todas las Unidades Didácticas</option>
              <?php foreach ($unidadesTablaCoord as $u): ?>
                <?php if ($u['id_periodo_academico'] == $periodoFiltroSeleccionado): ?>
                  <option value="<?= $u['id_programacion_ud'] ?>"><?= htmlspecialchars($u['unidad_nombre']) ?> (<?= htmlspecialchars($u['semestre_nombre']) ?>)</option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
            <ul class="nav nav-pills" id="pills-alertas-coord" role="tablist">
              <li class="nav-item">
                <a class="nav-link active py-1 px-3" id="tab-coord-academicas" data-toggle="pill" href="#panel-coord-academicas" role="tab">
                  <i class="fa fa-graduation-cap mr-1"></i> Rendimiento (<span id="badge-total-coord-academicas"><?= count($alertasAcademicasCoord) ?></span>)
                </a>
              </li>
              <li class="nav-item ml-1">
                <a class="nav-link py-1 px-3" id="tab-coord-asistencia" data-toggle="pill" href="#panel-coord-asistencia" role="tab">
                  <i class="fa fa-calendar-times mr-1"></i> Asistencia (<span id="badge-total-coord-asistencia"><?= count($alertasAsistenciaCoord) ?></span>)
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body px-4 pb-4 pt-3">
        <div class="tab-content" id="pills-tabContentAlertasCoord">
          <!-- Pestaña 1: Alertas Académicas -->
          <div class="tab-pane fade show active" id="panel-coord-academicas" role="tabpanel">
            <?php if (empty($alertasAcademicasCoord)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success" style="font-size: 32px;"></i>
                <p class="mt-2 mb-0 font-weight-bold">Excelente: No se registran estudiantes con alertas de bajo rendimiento en el periodo actual.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-coord-alertas-acad">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Programa / Semestre</th>
                      <th>Unidad Didáctica</th>
                      <th>Docente</th>
                      <th class="text-center">Promedio Final</th>
                      <th>Condición</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAcademicasCoord as $alerta): ?>
                      <tr class="fila-coord-alerta-academica" data-pud="<?= $alerta['id_pud'] ?>">
                        <td><strong><?= htmlspecialchars($alerta['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($alerta['dni']) ?></td>
                        <td>
                          <span class="badge badge-light-primary"><?= htmlspecialchars($alerta['programa']) ?></span>
                          <span class="badge badge-secondary"><?= htmlspecialchars($alerta['semestre']) ?></span>
                        </td>
                        <td><strong class="text-dark"><?= htmlspecialchars($alerta['unidad']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($alerta['docente']) ?></small></td>
                        <td class="text-center">
                          <span class="badge badge-pill <?= $alerta['promedio'] >= 10 ? 'badge-warning text-white' : 'badge-danger' ?> font-weight-bold px-3 py-1" style="font-size: 13px;">
                            <?= number_format($alerta['promedio'], 1) ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($alerta['tipo'] === 'recuperacion'): ?>
                            <span class="badge badge-warning text-white px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i>En Riesgo (10 - 12)</span>
                          <?php else: ?>
                            <span class="badge badge-danger px-2 py-1 badge-pulse-danger"><i class="fa fa-times-circle mr-1"></i>Desaprobado (&lt; 10)</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-info" title="Ver Registro de Calificaciones">
                            <i class="fa fa-edit mr-1"></i> Ver Notas
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-coord-academica-vacio-filtro" style="display: none;">
                      <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas académicas para la unidad didáctica seleccionada.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Académicas Coordinador -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-coord-alertas-acad">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-coord-alertas-acad">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-coord-alertas-acad"></span>
                </div>
                <nav aria-label="Paginación Alertas Académicas Coordinador">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-coord-alertas-acad"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>

          <!-- Pestaña 2: Alertas de Asistencia -->
          <div class="tab-pane fade" id="panel-coord-asistencia" role="tabpanel">
            <?php if (empty($alertasAsistenciaCoord)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success" style="font-size: 32px;"></i>
                <p class="mt-2 mb-0 font-weight-bold">Excelente: No se registran estudiantes con alertas de inasistencia en el periodo actual.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-coord-alertas-asis">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Programa / Semestre</th>
                      <th>Unidad Didáctica</th>
                      <th>Docente</th>
                      <th>Faltas / Sesiones</th>
                      <th>% Inasistencias</th>
                      <th>Estado</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAsistenciaCoord as $alerta): ?>
                      <tr class="fila-coord-alerta-asistencia" data-pud="<?= $alerta['id_pud'] ?>">
                        <td><strong><?= htmlspecialchars($alerta['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($alerta['dni']) ?></td>
                        <td>
                          <span class="badge badge-light-primary"><?= htmlspecialchars($alerta['programa']) ?></span>
                          <span class="badge badge-secondary"><?= htmlspecialchars($alerta['semestre']) ?></span>
                        </td>
                        <td><strong class="text-dark"><?= htmlspecialchars($alerta['unidad']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($alerta['docente']) ?></small></td>
                        <td>
                          <span class="font-weight-bold <?= $alerta['estado'] === 'inhabilitado' ? 'text-danger' : 'text-warning' ?>">
                            <?= $alerta['faltas'] ?> faltas
                          </span> de <?= $alerta['total_sesiones'] ?> sesiones
                        </td>
                        <td style="min-width: 140px;">
                          <div class="d-flex align-items-center">
                            <span class="mr-2 font-weight-bold <?= $alerta['estado'] === 'inhabilitado' ? 'text-danger' : 'text-warning' ?>">
                              <?= $alerta['porcentaje'] ?>%
                            </span>
                            <div class="progress flex-grow-1 progress-thin">
                              <div class="progress-bar <?= $alerta['estado'] === 'inhabilitado' ? 'bg-danger' : 'bg-warning' ?>" style="width: <?= min(100, $alerta['porcentaje']) ?>%;"></div>
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php if ($alerta['estado'] === 'inhabilitado'): ?>
                            <span class="badge badge-danger px-2 py-1 badge-pulse-danger"><i class="fa fa-ban mr-1"></i>Inhabilitado (DPI)</span>
                          <?php else: ?>
                            <span class="badge badge-warning text-white px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i>Riesgo DPI (&ge;20%)</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-success" title="Ver Control de Asistencia">
                            <i class="fa fa-external-link-alt mr-1"></i> Asistencia
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-coord-asistencia-vacio-filtro" style="display: none;">
                      <td colspan="9" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas de inasistencia para la unidad didáctica seleccionada.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Asistencia Coordinador -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-coord-alertas-asis">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-coord-alertas-asis">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-coord-alertas-asis"></span>
                </div>
                <nav aria-label="Paginación Alertas Asistencia Coordinador">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-coord-alertas-asis"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 1.5 SECCIÓN DE GESTIÓN DE UNIDADES DIDÁCTICAS (DISEÑO FIEL AL ADJUNTO CON FILTROS AVANZADOS) -->
    <div class="card chart-card mb-4">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-th-large text-primary mr-2"></i>Gestión de Mis Unidades Didácticas
            </h5>
            <p class="text-muted small mb-0">Accesos directos a Sílabos, Sesiones de Aprendizaje, Asistencia, Calificaciones e Informes</p>
          </div>
          <div class="mt-2 mt-lg-0">
            <span class="text-muted small">
              Mostrando <strong id="contador-filtro-uds"><?= count($unidadesTablaCoord) ?></strong> de <?= count($unidadesTablaCoord) ?> unidades didácticas
            </span>
          </div>
        </div>

        <!-- BARRA DE FILTROS: Programa, Plan, Periodo Académico y Semestre -->
        <div class="row mt-3 pt-3 border-top align-items-end">
          <!-- Filtro Programa -->
          <div class="col-lg-3 col-md-6 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Programa de Estudios:</label>
            <select id="filtro-coord-programa" class="form-control form-control-sm">
              <?php if (count($programasCoordinador) > 1): ?>
                <option value="todos">Todos los Programas</option>
              <?php endif; ?>
              <?php foreach ($programasCoordinador as $prog): ?>
                <option value="<?= $prog['id'] ?>"><?= htmlspecialchars($prog['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Plan de Estudios -->
          <div class="col-lg-2 col-md-6 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Plan de Estudios:</label>
            <select id="filtro-coord-plan" class="form-control form-control-sm">
              <option value="todos">Todos los Planes</option>
              <?php foreach ($planesCoordinador as $plan): ?>
                <option value="<?= $plan['id'] ?>" data-prog="<?= $plan['id_programa_estudios'] ?>">
                  <?= htmlspecialchars($plan['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Periodo Académico (Histórico) -->
          <div class="col-lg-3 col-md-6 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Periodo Académico:</label>
            <select id="filtro-coord-periodo" class="form-control form-control-sm bg-light-primary text-primary font-weight-bold">
              <?php foreach ($periodosAcademicos as $per): ?>
                <option value="<?= $per['id'] ?>" <?= ($per['id'] == $periodoFiltroSeleccionado) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($per['nombre']) ?> <?= (!empty($id_periodo) && $per['id'] == $id_periodo) ? '(Periodo Actual)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Filtro Semestre -->
          <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted font-weight-bold mb-1">Semestre:</label>
            <select id="filtro-coord-semestre" class="form-control form-control-sm">
              <option value="todos">Todos los Semestres</option>
              <?php foreach ($semestresCatalogo as $sem): ?>
                <option value="<?= htmlspecialchars($sem) ?>"><?= htmlspecialchars($sem) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Botón Limpiar -->
          <div class="col-lg-2 col-md-2 mb-2 text-md-right">
            <button type="button" id="btn-limpiar-filtros-coord" class="btn btn-sm btn-outline-secondary btn-block">
              <i class="fa fa-sync-alt mr-1"></i> Limpiar
            </button>
          </div>
        </div>
      </div>

      <div class="card-body px-4 pb-4 pt-2">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-uds" id="tabla-gestion-coordinador">
            <thead>
              <tr>
                <th style="min-width: 280px;">Unidad Didáctica</th>
                <th style="min-width: 220px;">Programa / Semestre</th>
                <th style="min-width: 130px;">Turno / Sec</th>
                <th style="min-width: 140px;">Sílabo</th>
                <th style="min-width: 180px;">Avance Sesiones</th>
                <th style="min-width: 180px;">Avance Calificaciones</th>
                <th class="text-center" style="min-width: 180px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($unidadesTablaCoord)): ?>
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa fa-folder-open text-secondary mb-2" style="font-size: 36px;"></i>
                    <p class="mb-0 font-weight-bold">No se encontraron unidades didácticas programadas para el periodo seleccionado.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($unidadesTablaCoord as $ud): ?>
                  <tr class="fila-ud-coord"
                    data-prog="<?= $ud['id_programa_estudio'] ?>"
                    data-plan="<?= $ud['id_plan_estudio'] ?>"
                    data-periodo="<?= $ud['id_periodo_academico'] ?>"
                    data-semestre="<?= htmlspecialchars($ud['semestre_nombre']) ?>"
                    data-busqueda="<?= htmlspecialchars(mb_strtolower($ud['unidad_nombre'] . ' ' . $ud['docente_nombre'] . ' ' . $ud['programa_nombre'])) ?>">

                    <!-- Unidad Didáctica -->
                    <td>
                      <div class="font-weight-bold text-dark text-uppercase mb-1" style="font-size: 14px; letter-spacing: 0.3px;">
                        <?= htmlspecialchars($ud['unidad_nombre']) ?>
                      </div>
                      <div class="small text-muted mb-1">
                        <i class="fa fa-users text-secondary mr-1"></i><?= $ud['matriculados'] ?> estudiantes matriculados
                      </div>
                      <div class="small text-muted">
                        <i class="fa fa-user-tie text-info mr-1"></i>Docente: <strong><?= htmlspecialchars($ud['docente_nombre']) ?></strong>
                      </div>
                    </td>

                    <!-- Programa / Semestre -->
                    <td>
                      <div class="font-weight-bold text-dark text-uppercase" style="font-size: 13px;">
                        <?= htmlspecialchars($ud['programa_nombre']) ?>
                      </div>
                      <div class="font-weight-bold text-primary" style="font-size: 14px;">
                        <?= htmlspecialchars($ud['semestre_nombre']) ?>
                      </div>
                      <?php if (!empty($ud['plan_nombre'])): ?>
                        <div class="small text-muted">Plan <?= htmlspecialchars($ud['plan_nombre']) ?></div>
                      <?php endif; ?>
                    </td>

                    <!-- Turno / Sec -->
                    <td>
                      <span class="text-muted font-weight-500" style="font-size: 13px;">
                        <?= htmlspecialchars($ud['turno_seccion']) ?>
                      </span>
                    </td>

                    <!-- Sílabo -->
                    <td>
                      <?php if ($ud['silabo_registrado']): ?>
                        <span class="badge-registrado">
                          <i class="fa fa-check mr-1"></i> Registrado
                        </span>
                      <?php else: ?>
                        <span class="badge-pendiente">
                          <i class="fa fa-clock mr-1"></i> Pendiente
                        </span>
                      <?php endif; ?>
                    </td>

                    <!-- Avance Sesiones -->
                    <td>
                      <div class="d-flex align-items-center mb-1">
                        <span class="font-weight-bold text-dark mr-2" style="font-size: 13px;">
                          <?= $ud['porcentaje_sesiones'] ?>%
                        </span>
                        <small class="text-muted">(<?= $ud['sesiones_registradas'] ?>/<?= $ud['sesiones_totales'] ?>)</small>
                      </div>
                      <div class="progress progress-thin">
                        <div class="progress-bar prog-blue" style="width: <?= $ud['porcentaje_sesiones'] ?>%;"></div>
                      </div>
                    </td>

                    <!-- Avance Calificaciones -->
                    <td>
                      <div class="d-flex align-items-center mb-1">
                        <span class="font-weight-bold text-dark mr-2" style="font-size: 13px;">
                          <?= $ud['porcentaje_calificaciones'] ?>%
                        </span>
                        <small class="text-muted">(<?= $ud['calificaciones_registradas'] ?>/<?= $ud['calificaciones_totales'] ?>)</small>
                      </div>
                      <div class="progress progress-thin">
                        <div class="progress-bar prog-green" style="width: <?= $ud['porcentaje_calificaciones'] ?>%;"></div>
                      </div>
                    </td>

                    <!-- Acciones Rápidas -->
                    <td class="text-center">
                      <div class="btn-group" role="group">
                        <a href="<?= BASE_URL ?>/academico/silabos/editar/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-warning" title="Gestionar Sílabo">
                          <i class="fa fa-book"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/sesiones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-primary" title="Sesiones de Aprendizaje">
                          <i class="fa fa-briefcase"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-success" title="Control de Asistencia">
                          <i class="fa fa-users"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-info" title="Registro de Calificaciones">
                          <i class="fa fa-edit"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-dark" title="Informe Final">
                          <i class="fa fa-chart-bar"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr id="fila-coord-vacia-filtro" style="display: none;">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa fa-filter text-secondary mb-2" style="font-size: 32px;"></i>
                    <p class="mb-0 font-weight-bold">No se encontraron unidades didácticas con los filtros seleccionados.</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Paginación Unidades Didácticas Coordinador -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-gestion-coordinador">
          <div class="d-flex align-items-center mb-2 mb-sm-0">
            <span class="small text-muted mr-2">Mostrar:</span>
            <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-gestion-coordinador">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="todos">Todos</option>
            </select>
            <span class="small text-muted ml-3" id="info-gestion-coordinador"></span>
          </div>
          <nav aria-label="Paginación Unidades Didácticas Coordinador">
            <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-gestion-coordinador"></ul>
          </nav>
        </div>
      </div>
    </div>

  </div> <!-- /#seccion-coordinador -->

<?php endif; ?>



<div id="seccion-docente" style="<?= (!empty($esCoordinador) || !empty($esAutoridad)) ? 'display: none;' : '' ?>">

  <?php if (!empty($esDocente) && $uds_count > 0): ?>

    <!-- 1. TARJETAS KPI DOCENTE -->
    <div class="row mb-4">
      <!-- Total UDs -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Mis Unidades</p>
                <h2 class="font-weight-bold mb-0 text-dark"><?= $uds_count ?></h2>
                <small class="text-muted"><i class="fa fa-users text-primary mr-1"></i><?= $resumenDocente['total_estudiantes'] ?> estudiantes matriculados</small>
              </div>
              <div class="kpi-icon-circle bg-light text-primary">
                <i class="fa fa-layer-group"></i>
              </div>
            </div>
            <div class="mt-3">
              <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="text-primary font-weight-bold small text-decoration-none">
                Ver unidades didácticas <i class="fa fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Avance Sílabos y Sesiones -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Avance de Sesiones</p>
                <h2 class="font-weight-bold mb-0 text-dark"><?= $resumenDocente['promedio_sesiones'] ?><small class="text-muted" style="font-size: 16px;">%</small></h2>
                <small class="text-muted">
                  <?php if ($resumenDocente['silabos_pendientes'] > 0): ?>
                    <span class="text-danger font-weight-bold"><i class="fa fa-exclamation-triangle"></i> <?= $resumenDocente['silabos_pendientes'] ?> sílabo(s) pendiente(s)</span>
                  <?php else: ?>
                    <span class="text-success"><i class="fa fa-check-circle"></i> Todos los sílabos al día</span>
                  <?php endif; ?>
                </small>
              </div>
              <div class="kpi-icon-circle bg-light text-info">
                <i class="fa fa-calendar-check"></i>
              </div>
            </div>
            <div class="mt-3">
              <div class="progress progress-thin">
                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $resumenDocente['promedio_sesiones'] ?>%;" aria-valuenow="<?= $resumenDocente['promedio_sesiones'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Avance Calificaciones -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Avance Calificaciones</p>
                <h2 class="font-weight-bold mb-0 text-dark"><?= $resumenDocente['promedio_calificaciones'] ?><small class="text-muted" style="font-size: 16px;">%</small></h2>
                <small class="text-muted d-block mt-1">
                  <span class="text-success font-weight-bold"><?= $resumenDocente['aprobados'] ?></span> aprobados &bull;
                  <span class="text-warning font-weight-bold"><?= $resumenDocente['en_riesgo'] ?></span> recup. &bull;
                  <span class="text-danger font-weight-bold"><?= $resumenDocente['desaprobados'] ?></span> desaprob. &bull;
                  <span class="text-secondary"><?= $resumenDocente['sin_calificar'] ?></span> s/calif.
                </small>
              </div>
              <div class="kpi-icon-circle bg-light text-success">
                <i class="fa fa-edit"></i>
              </div>
            </div>
            <div class="mt-3">
              <div class="progress progress-thin">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $resumenDocente['promedio_calificaciones'] ?>%;" aria-valuenow="<?= $resumenDocente['promedio_calificaciones'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas de Estudiantes -->
      <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <?php $totalAlertas = $resumenDocente['total_alertas_academicas'] + $resumenDocente['total_alertas_asistencia']; ?>
        <div class="card kpi-card h-100 bg-white">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Alertas Tempranas</p>
                <h2 class="font-weight-bold mb-0 <?= $totalAlertas > 0 ? 'text-danger' : 'text-success' ?>">
                  <?= $totalAlertas ?>
                </h2>
                <small class="text-muted">
                  <span class="text-warning font-weight-bold"><?= $resumenDocente['total_alertas_academicas'] ?> académicas</span> &bull;
                  <span class="text-danger font-weight-bold"><?= $resumenDocente['total_alertas_asistencia'] ?> inasistencias</span>
                </small>
              </div>
              <div class="kpi-icon-circle bg-light <?= $totalAlertas > 0 ? 'text-danger badge-pulse-danger' : 'text-success' ?>">
                <i class="fa fa-bell"></i>
              </div>
            </div>
            <div class="mt-3">
              <a href="#seccion-alertas" class="text-danger font-weight-bold small text-decoration-none">
                Revisar estudiantes en riesgo <i class="fa fa-arrow-down ml-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. GRÁFICOS ESTADÍSTICOS -->
    <div class="row mb-4">
      <!-- Gráfico de Avance Curricular y Calificaciones por UD -->
      <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title font-weight-bold text-dark mb-1">
                <i class="fa fa-chart-bar text-primary mr-2"></i>Avance Curricular y de Calificaciones por Unidad Didáctica
              </h5>
              <p class="text-muted small mb-0">Comparativa del % de sesiones desarrolladas vs % de evaluaciones registradas</p>
            </div>
            <span class="badge badge-light-secondary px-2 py-1 small">
              <i class="fa fa-clock mr-1"></i>Actualizado
            </span>
          </div>
          <div class="card-body px-4 pb-4">
            <div style="height: 320px; position: relative;">
              <canvas id="chartAvanceDocente"></canvas>
            </div>
            <div class="d-flex justify-content-center align-items-center mt-3 small text-muted">
              <span class="mr-4"><i class="fa fa-square mr-1" style="color: #00c2b2;"></i> % Sesiones Desarrolladas (Sílabo)</span>
              <span><i class="fa fa-square mr-1" style="color: #3f51b5;"></i> % Avance de Calificaciones</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico de Rendimiento Estudiantil (Semáforo) -->
      <div class="col-lg-4">
        <div class="card chart-card h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-chart-pie text-success mr-2"></i>Semáforo de Rendimiento
            </h5>
            <p class="text-muted small mb-0">Total: <strong><?= $resumenDocente['total_estudiantes'] ?></strong> estudiantes matriculados</p>
          </div>
          <div class="card-body px-4 pb-4 d-flex flex-column justify-content-center">
            <div style="height: 240px; position: relative;">
              <canvas id="chartRendimiento"></canvas>
            </div>
            <div class="mt-3">
              <div class="row text-center">
                <div class="col-6 mb-2">
                  <span class="badge badge-success px-2 py-1 d-block mb-1">Aprobados (&ge; 13)</span>
                  <strong><?= $resumenDocente['aprobados'] ?></strong>
                </div>
                <div class="col-6 mb-2">
                  <span class="badge badge-warning px-2 py-1 d-block mb-1 text-white">Recuperación (10-12)</span>
                  <strong><?= $resumenDocente['en_riesgo'] ?></strong>
                </div>
                <div class="col-6">
                  <span class="badge badge-danger px-2 py-1 d-block mb-1">Desaprobados (&lt; 10)</span>
                  <strong><?= $resumenDocente['desaprobados'] ?></strong>
                </div>
                <div class="col-6">
                  <span class="badge badge-secondary px-2 py-1 d-block mb-1">Sin Calificar</span>
                  <strong><?= $resumenDocente['sin_calificar'] ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. SECCIÓN DE ALERTAS TEMPRANAS -->
    <div class="card chart-card mb-4" id="seccion-alertas">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
          <div>
            <h5 class="card-title font-weight-bold text-dark mb-1">
              <i class="fa fa-shield-alt text-danger mr-2"></i>Alertas Tempranas de Estudiantes
            </h5>
            <p class="text-muted small mb-0">Monitoreo preventivo basado en el Promedio Final de Indicadores y límite de inasistencias (DPI)</p>
          </div>
          <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center">
            <!-- Filtro rápido por Unidad Didáctica -->
            <div class="mr-md-3 mb-2 mb-md-0">
              <select id="filtro-ud-alertas" class="form-control form-control-sm custom-select" style="min-width: 230px;">
                <option value="todos">Todas las Unidades (<?= $resumenDocente['total_estudiantes'] ?> alumnos)</option>
                <?php foreach ($unidadesDetalle as $udItem): ?>
                  <option value="<?= $udItem['id_programacion_ud'] ?>">
                    <?= htmlspecialchars($udItem['unidad_nombre']) ?> - Sec <?= $udItem['seccion'] ?> (<?= $udItem['total_estudiantes'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <ul class="nav nav-pills" id="pills-alertas" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="pills-academicas-tab" data-toggle="pill" href="#pills-academicas" role="tab">
                  <i class="fa fa-user-times mr-1"></i>Riesgo Académico
                  <span class="badge badge-danger ml-1" id="badge-total-academicas"><?= count($alertasAcademicas) ?></span>
                </a>
              </li>
              <li class="nav-item ml-2">
                <a class="nav-link" id="pills-asistencia-tab" data-toggle="pill" href="#pills-asistencia" role="tab">
                  <i class="fa fa-user-clock mr-1"></i>Riesgo Inasistencia (DPI)
                  <span class="badge badge-warning ml-1 text-white" id="badge-total-asistencia"><?= count($alertasAsistencia) ?></span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body px-4 pb-4">
        <div class="tab-content" id="pills-alertasContent">

          <!-- Pestaña 1: Alertas Académicas -->
          <div class="tab-pane fade show active" id="pills-academicas" role="tabpanel">
            <?php if (empty($alertasAcademicas)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success fa-3x mb-2"></i>
                <p class="mb-0 font-weight-bold">¡Excelente! No hay alumnos en riesgo académico o desaprobados en sus unidades didácticas.</p>
                <small>Todos los estudiantes calificados cuentan con promedio final de indicadores aprobatorio (&ge; 13).</small>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-doc-alertas-acad">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Unidad Didáctica</th>
                      <th>Turno / Sec</th>
                      <th>Promedio Final Indicadores</th>
                      <th>Diagnóstico</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAcademicas as $alerta): ?>
                      <tr class="fila-alerta-academica" data-pud="<?= $alerta['id_pud'] ?>">
                        <td class="font-weight-bold text-dark">
                          <i class="fa fa-user-circle text-secondary mr-2"></i><?= htmlspecialchars($alerta['nombre']) ?>
                        </td>
                        <td><code><?= htmlspecialchars($alerta['dni']) ?></code></td>
                        <td>
                          <span class="text-primary font-weight-bold"><?= htmlspecialchars($alerta['unidad']) ?></span>
                          <div class="small text-muted"><?= htmlspecialchars($alerta['programa']) ?></div>
                        </td>
                        <td>
                          <span class="badge badge-light-dark font-weight-normal"><?= $alerta['turno'] === 'M' ? 'Mañana' : ($alerta['turno'] === 'T' ? 'Tarde' : 'Noche') ?> - Sec <?= $alerta['seccion'] ?></span>
                        </td>
                        <td>
                          <span class="h6 font-weight-bold <?= $alerta['tipo'] === 'desaprobado' ? 'text-danger' : 'text-warning' ?>">
                            <?= number_format($alerta['promedio'], 1) ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($alerta['tipo'] === 'desaprobado'): ?>
                            <span class="badge badge-danger px-2 py-1"><i class="fa fa-times-circle mr-1"></i>Desaprobado Crítico (&lt; 10)</span>
                          <?php else: ?>
                            <span class="badge badge-warning text-white px-2 py-1"><i class="fa fa-exclamation-circle mr-1"></i>En Recuperación (10-12)</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-info" title="Ver Calificaciones en Detalle">
                            <i class="fa fa-external-link-alt mr-1"></i> Calificaciones
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-academica-vacio-filtro" style="display: none;">
                      <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas académicas para la unidad didáctica seleccionada.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Académicas Docente -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-doc-alertas-acad">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-doc-alertas-acad">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-doc-alertas-acad"></span>
                </div>
                <nav aria-label="Paginación Alertas Académicas Docente">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-doc-alertas-acad"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>

          <!-- Pestaña 2: Alertas de Inasistencia -->
          <div class="tab-pane fade" id="pills-asistencia" role="tabpanel">
            <?php if (empty($alertasAsistencia)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success fa-3x mb-2"></i>
                <p class="mb-0 font-weight-bold">¡Muy bien! Todos los estudiantes se encuentran dentro del límite de asistencia regular.</p>
                <small>Ningún estudiante supera el 20% de inasistencias en sus unidades didácticas.</small>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-doc-alertas-asis">
                  <thead class="thead-light">
                    <tr>
                      <th>Estudiante</th>
                      <th>DNI</th>
                      <th>Unidad Didáctica</th>
                      <th>Turno / Sec</th>
                      <th>Faltas / Sesiones</th>
                      <th>% Inasistencia</th>
                      <th>Estado</th>
                      <th class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($alertasAsistencia as $alerta): ?>
                      <tr class="fila-alerta-asistencia" data-pud="<?= $alerta['id_pud'] ?>">
                        <td class="font-weight-bold text-dark">
                          <i class="fa fa-user-circle text-secondary mr-2"></i><?= htmlspecialchars($alerta['nombre']) ?>
                        </td>
                        <td><code><?= htmlspecialchars($alerta['dni']) ?></code></td>
                        <td>
                          <span class="text-primary font-weight-bold"><?= htmlspecialchars($alerta['unidad']) ?></span>
                          <div class="small text-muted"><?= htmlspecialchars($alerta['programa']) ?></div>
                        </td>
                        <td>
                          <span class="badge badge-light-dark font-weight-normal"><?= $alerta['turno'] === 'M' ? 'Mañana' : ($alerta['turno'] === 'T' ? 'Tarde' : 'Noche') ?> - Sec <?= $alerta['seccion'] ?></span>
                        </td>
                        <td>
                          <strong><?= $alerta['faltas'] ?></strong> faltas de <?= $alerta['total_sesiones'] ?> sesiones
                        </td>
                        <td style="min-width: 140px;">
                          <div class="d-flex align-items-center">
                            <span class="mr-2 font-weight-bold <?= $alerta['estado'] === 'inhabilitado' ? 'text-danger' : 'text-warning' ?>">
                              <?= $alerta['porcentaje'] ?>%
                            </span>
                            <div class="progress flex-grow-1 progress-thin">
                              <div class="progress-bar <?= $alerta['estado'] === 'inhabilitado' ? 'bg-danger' : 'bg-warning' ?>" style="width: <?= min(100, $alerta['porcentaje']) ?>%;"></div>
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php if ($alerta['estado'] === 'inhabilitado'): ?>
                            <span class="badge badge-danger px-2 py-1"><i class="fa fa-ban mr-1"></i>Inhabilitado (>30% DPI)</span>
                          <?php else: ?>
                            <span class="badge badge-warning text-white px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i>Riesgo DPI (&ge;20%)</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $alerta['id_pud'] ?>" class="btn btn-sm btn-outline-success" title="Ver Control de Asistencia">
                            <i class="fa fa-external-link-alt mr-1"></i> Asistencia
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="alerta-asistencia-vacio-filtro" style="display: none;">
                      <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle text-info mr-1"></i> No se encontraron alertas de inasistencia para la unidad didáctica seleccionada.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Paginación Alertas Asistencia Docente -->
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-doc-alertas-asis">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                  <span class="small text-muted mr-2">Mostrar:</span>
                  <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-doc-alertas-asis">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="todos">Todos</option>
                  </select>
                  <span class="small text-muted ml-3" id="info-doc-alertas-asis"></span>
                </div>
                <nav aria-label="Paginación Alertas Asistencia Docente">
                  <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-doc-alertas-asis"></ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <!-- 4. TABLA RESUMEN DE MIS UNIDADES DIDÁCTICAS CON REDIRECCIONES -->
    <div class="card chart-card mb-4">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title font-weight-bold text-dark mb-1">
            <i class="fa fa-th-list text-primary mr-2"></i>Gestión de Mis Unidades Didácticas
          </h5>
          <p class="text-muted small mb-0">Accesos directos a Sílabos, Sesiones de Aprendizaje, Asistencia, Calificaciones e Informes</p>
        </div>
        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-sm btn-outline-primary">
          Ver Tabla Completa <i class="fa fa-chevron-right ml-1"></i>
        </a>
      </div>
      <div class="card-body px-4 pb-4">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tabla-gestion-docente">
            <thead class="thead-light">
              <tr>
                <th>Unidad Didáctica</th>
                <th>Programa / Semestre</th>
                <th>Turno / Sec</th>
                <th>Sílabo</th>
                <th>Avance Sesiones</th>
                <th>Avance Calificaciones</th>
                <th class="text-center" style="min-width: 220px;">Acciones Rápidas</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($unidadesDetalle as $ud): ?>
                <tr class="fila-ud-doc">
                  <td>
                    <strong class="text-dark"><?= htmlspecialchars($ud['unidad_nombre']) ?></strong>
                    <div class="small text-muted"><i class="fa fa-users mr-1"></i><?= $ud['total_estudiantes'] ?> estudiantes matriculados</div>
                  </td>
                  <td>
                    <span class="text-dark font-weight-500"><?= htmlspecialchars($ud['programa_nombre']) ?></span>
                    <div class="small text-muted"><?= htmlspecialchars($ud['semestre']) ?></div>
                  </td>
                  <td>
                    <span class="badge badge-light-primary"><?= $ud['turno'] === 'M' ? 'Mañana' : ($ud['turno'] === 'T' ? 'Tarde' : 'Noche') ?> - <?= $ud['seccion'] ?></span>
                  </td>
                  <td>
                    <?php if ($ud['tiene_silabo']): ?>
                      <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>Registrado</span>
                    <?php else: ?>
                      <span class="badge badge-warning text-white px-2 py-1"><i class="fa fa-clock mr-1"></i>Pendiente</span>
                    <?php endif; ?>
                  </td>
                  <td style="min-width: 140px;">
                    <div class="d-flex align-items-center mb-1">
                      <span class="small font-weight-bold mr-2"><?= $ud['porc_sesiones'] ?>%</span>
                      <small class="text-muted">(<?= $ud['total_sesiones'] ?>/<?= $ud['total_actividades'] ?>)</small>
                    </div>
                    <div class="progress progress-thin">
                      <div class="progress-bar bg-info" style="width: <?= $ud['porc_sesiones'] ?>%;"></div>
                    </div>
                  </td>
                  <td style="min-width: 140px;">
                    <div class="d-flex align-items-center mb-1">
                      <span class="small font-weight-bold mr-2"><?= $ud['porc_calificaciones'] ?>%</span>
                      <small class="text-muted">(<?= $ud['criterios_calificados'] ?>/<?= $ud['total_criterios'] ?>)</small>
                    </div>
                    <div class="progress progress-thin">
                      <div class="progress-bar bg-success" style="width: <?= $ud['porc_calificaciones'] ?>%;"></div>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="<?= BASE_URL ?>/academico/silabos/editar/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-warning" title="Sílabo">
                        <i class="fa fa-book"></i>
                      </a>
                      <a href="<?= BASE_URL ?>/academico/sesiones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-primary" title="Sesiones de Aprendizaje">
                        <i class="fa fa-briefcase"></i>
                      </a>
                      <a href="<?= BASE_URL ?>/academico/asistencia/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-success" title="Control de Asistencia">
                        <i class="fa fa-users"></i>
                      </a>
                      <a href="<?= BASE_URL ?>/academico/calificaciones/ver/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-info" title="Registro de Calificaciones">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a href="<?= BASE_URL ?>/academico/unidadesDidacticas/informeFinal/<?= $ud['id_programacion_ud'] ?>" class="btn btn-sm btn-outline-dark" title="Informe Final">
                        <i class="fa fa-chart-bar"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Paginación Mis Unidades Didácticas Docente -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-2 border-top px-1" id="paginacion-gestion-docente">
          <div class="d-flex align-items-center mb-2 mb-sm-0">
            <span class="small text-muted mr-2">Mostrar:</span>
            <select class="custom-select custom-select-sm" style="width: auto; height: 31px; font-size: 12px;" id="tamano-gestion-docente">
              <option value="10" selected>10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="todos">Todos</option>
            </select>
            <span class="small text-muted ml-3" id="info-gestion-docente"></span>
          </div>
          <nav aria-label="Paginación Mis Unidades Didácticas">
            <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginas-gestion-docente"></ul>
          </nav>
        </div>
      </div>
    </div>

  <?php endif; ?>


</div> <!-- /#seccion-docente -->


<!-- ========================================================================= -->
<?php if (empty($esCoordinador) && (\Core\Auth::esAdminAcademico() || \Core\Auth::esDirectorAcademico() || \Core\Auth::esSecretarioAcadAcademico())): ?>
  <div class="card chart-card mb-4 mt-2">
    <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
      <h5 class="card-title font-weight-bold text-dark mb-1">
        <i class="fa fa-landmark text-secondary mr-2"></i>Resumen Institucional Académico
      </h5>
    </div>
    <div class="card-body px-4 pb-4">
      <div class="row">
        <div class="col-md-3 col-6 mb-3 mb-md-0">
          <div class="p-3 border rounded text-center bg-light">
            <h6 class="text-muted text-uppercase small mb-1">Periodo Académico</h6>
            <h4 class="font-weight-bold text-primary mb-0"><?= htmlspecialchars($periodo) ?></h4>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3 mb-md-0">
          <div class="p-3 border rounded text-center bg-light">
            <h6 class="text-muted text-uppercase small mb-1">Sedes Activas</h6>
            <h4 class="font-weight-bold text-dark mb-0"><?= $sedes_count ?></h4>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3 mb-md-0">
          <div class="p-3 border rounded text-center bg-light">
            <h6 class="text-muted text-uppercase small mb-1">Programas de Estudio</h6>
            <h4 class="font-weight-bold text-dark mb-0"><?= $programas ?></h4>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3 mb-md-0">
          <div class="p-3 border rounded text-center bg-light">
            <h6 class="text-muted text-uppercase small mb-1">Plana Docente</h6>
            <h4 class="font-weight-bold text-dark mb-0"><?= $docentes ?></h4>
          </div>
        </div>
      </div>
      <?php if (\Core\Auth::esCoordinadorPEAcademico() || \Core\Auth::esDirectorAcademico() || \Core\Auth::esJUAAcademico() || \Core\Auth::esSecretarioAcadAcademico() || \Core\Auth::esAdminAcademico()): ?>
        <div class="mt-3 text-right">
          <a href="<?= BASE_URL ?>/academico/reportes" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-file-pdf mr-1"></i> Ir al Módulo de Reportes Oficiales
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODALES DE REPORTES OFICIALES (Asignados al Coordinador / Administrador)   -->
<!-- ========================================================================= -->
<?php if (!empty($esCoordinador) || \Core\Auth::esAdminAcademico()): ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_matricula.php'; ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_calif_consolidado.php'; ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_calif_detallado.php'; ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_calif_individual.php'; ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_primeros_puestos.php'; ?>
  <?php include_once __DIR__ . '/reportes/modals/modal_reporte_control_diario.php'; ?>
<?php endif; ?>



<!-- Carga de Chart.js desde los assets locales del sistema -->
<script src="<?= BASE_URL ?>/assets/libs/chart-js/chart.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    // =========================================================================
    // HELPER UNIVERSAL DE PAGINACIÓN DE TABLAS
    // =========================================================================
    function inicializarPaginacionTabla(config) {
      const table = document.getElementById(config.tableId);
      const container = document.getElementById(config.containerId);
      const selectTamano = document.getElementById(config.selectId);
      const infoEl = document.getElementById(config.infoId);
      const paginasEl = document.getElementById(config.paginasId);
      const filaVacia = config.emptyRowId ? document.getElementById(config.emptyRowId) : null;

      if (!table || !container || !selectTamano || !infoEl || !paginasEl) {
        return null;
      }

      let currentPage = 1;
      const defaultPageSize = config.defaultPageSize || 10;
      selectTamano.value = defaultPageSize.toString();

      function obtenerFilas() {
        const allRows = table.querySelectorAll('tbody tr');
        return Array.from(allRows).filter(function(tr) {
          if (tr.id && (tr.id.indexOf('vacio') !== -1 || tr.id.indexOf('vacia') !== -1)) return false;
          if (tr.classList.contains('fila-vacia-mensaje')) return false;
          return true;
        });
      }

      function actualizar(resetPage) {
        if (resetPage) currentPage = 1;

        const filas = obtenerFilas();
        const filasFiltradas = filas.filter(function(tr) {
          return tr.getAttribute('data-oculto-filtro') !== '1';
        });
        const total = filasFiltradas.length;

        if (filaVacia) {
          filaVacia.style.display = (filas.length > 0 && total === 0) ? '' : 'none';
        }

        if (total === 0) {
          filas.forEach(function(tr) { tr.style.display = 'none'; });
          infoEl.innerHTML = '<span class="text-muted">Sin registros para mostrar</span>';
          paginasEl.innerHTML = '';
          container.style.display = filas.length > 0 ? '' : 'none';
          return;
        }

        container.style.display = '';

        const tamanoVal = selectTamano.value;
        const pageSize = (tamanoVal === 'todos') ? total : parseInt(tamanoVal, 10);
        const totalPages = Math.ceil(total / pageSize);

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, total);

        filas.forEach(function(tr) {
          tr.style.display = 'none';
        });

        for (let i = startIndex; i < endIndex; i++) {
          filasFiltradas[i].style.display = '';
        }

        infoEl.innerHTML = 'Mostrando <strong class="text-dark">' + (startIndex + 1) + '</strong> a <strong class="text-dark">' + endIndex + '</strong> de <strong class="text-dark">' + total + '</strong> registros';

        renderPaginador(totalPages);
      }

      function renderPaginador(totalPages) {
        paginasEl.innerHTML = '';

        if (totalPages <= 1) {
          return;
        }

        // Botón Anterior
        const prevLi = document.createElement('li');
        prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
        prevLi.innerHTML = '<a class="page-link" href="javascript:void(0)" aria-label="Anterior">&laquo; Ant</a>';
        if (currentPage > 1) {
          prevLi.querySelector('a').addEventListener('click', function(e) {
            e.preventDefault();
            currentPage--;
            actualizar(false);
          });
        }
        paginasEl.appendChild(prevLi);

        // Ventana deslizante de hasta 5 páginas
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
          startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
          const firstLi = document.createElement('li');
          firstLi.className = 'page-item';
          firstLi.innerHTML = '<a class="page-link" href="javascript:void(0)">1</a>';
          firstLi.querySelector('a').addEventListener('click', function(e) {
            e.preventDefault();
            currentPage = 1;
            actualizar(false);
          });
          paginasEl.appendChild(firstLi);

          if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = '<span class="page-link border-0">...</span>';
            paginasEl.appendChild(dotsLi);
          }
        }

        for (let p = startPage; p <= endPage; p++) {
          const pLi = document.createElement('li');
          pLi.className = 'page-item' + (p === currentPage ? ' active' : '');
          pLi.innerHTML = '<a class="page-link" href="javascript:void(0)">' + p + '</a>';
          (function(pageNumber) {
            if (pageNumber !== currentPage) {
              pLi.querySelector('a').addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = pageNumber;
                actualizar(false);
              });
            }
          })(p);
          paginasEl.appendChild(pLi);
        }

        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = '<span class="page-link border-0">...</span>';
            paginasEl.appendChild(dotsLi);
          }
          const lastLi = document.createElement('li');
          lastLi.className = 'page-item';
          lastLi.innerHTML = '<a class="page-link" href="javascript:void(0)">' + totalPages + '</a>';
          lastLi.querySelector('a').addEventListener('click', function(e) {
            e.preventDefault();
            currentPage = totalPages;
            actualizar(false);
          });
          paginasEl.appendChild(lastLi);
        }

        // Botón Siguiente
        const nextLi = document.createElement('li');
        nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
        nextLi.innerHTML = '<a class="page-link" href="javascript:void(0)" aria-label="Siguiente">Sig &raquo;</a>';
        if (currentPage < totalPages) {
          nextLi.querySelector('a').addEventListener('click', function(e) {
            e.preventDefault();
            currentPage++;
            actualizar(false);
          });
        }
        paginasEl.appendChild(nextLi);
      }

      selectTamano.addEventListener('change', function() {
        actualizar(true);
      });

      actualizar(true);

      return {
        actualizar: actualizar,
        irAPagina: function(p) { currentPage = p; actualizar(false); }
      };
    }

    // Variables para instancias de gráficos del docente
    let chartAvanceDocInstance = null;
    let chartRendDocInstance = null;

    // Función para inicializar/redimensionar gráficos de la vista Docente
    function initChartsDocente() {
      <?php if (!empty($esDocente) && $uds_count > 0): ?>
        // 1. Gráfico de Avance por Unidad Didáctica (Barras Comparativas)
        const ctxAvance = document.getElementById('chartAvanceDocente');
        if (ctxAvance && !chartAvanceDocInstance) {
          const labels = <?= json_encode($chartLabels ?? []) ?>;
          const dataSesiones = <?= json_encode($chartSesiones ?? []) ?>;
          const dataCalificaciones = <?= json_encode($chartCalificaciones ?? []) ?>;

          chartAvanceDocInstance = new Chart(ctxAvance, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                  label: '% Sesiones Desarrolladas',
                  data: dataSesiones,
                  backgroundColor: 'rgba(0, 194, 178, 0.85)',
                  borderColor: '#00c2b2',
                  borderWidth: 1,
                  borderRadius: 4
                },
                {
                  label: '% Calificaciones Registradas',
                  data: dataCalificaciones,
                  backgroundColor: 'rgba(63, 81, 181, 0.85)',
                  borderColor: '#3f51b5',
                  borderWidth: 1,
                  borderRadius: 4
                }
              ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                  label: function(tooltipItem, data) {
                    const datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                    return datasetLabel + ': ' + tooltipItem.yLabel + '%';
                  }
                }
              },
              scales: {
                yAxes: [{
                  ticks: {
                    beginAtZero: true,
                    max: 100,
                    callback: function(value) {
                      return value + '%';
                    }
                  },
                  gridLines: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    zeroLineColor: 'rgba(0, 0, 0, 0.1)'
                  }
                }],
                xAxes: [{
                  gridLines: {
                    display: false
                  }
                }]
              },
              legend: {
                display: false
              }
            }
          });
        } else if (chartAvanceDocInstance) {
          chartAvanceDocInstance.resize();
        }

        // 2. Gráfico de Rendimiento Estudiantil (Doughnut Chart)
        const ctxRendimiento = document.getElementById('chartRendimiento');
        if (ctxRendimiento && !chartRendDocInstance) {
          const datosRendimiento = [
            <?= (int)($chartRendimiento['aprobados'] ?? 0) ?>,
            <?= (int)($chartRendimiento['en_riesgo'] ?? 0) ?>,
            <?= (int)($chartRendimiento['desaprobados'] ?? 0) ?>,
            <?= (int)($chartRendimiento['sin_calificar'] ?? 0) ?>
          ];
          const sumaTotal = datosRendimiento.reduce((a, b) => a + b, 0);

          chartRendDocInstance = new Chart(ctxRendimiento, {
            type: 'doughnut',
            data: {
              labels: ['Aprobados (≥13)', 'Recuperación (10-12)', 'Desaprobados (<10)', 'Sin Calificar'],
              datasets: [{
                data: sumaTotal > 0 ? datosRendimiento : [1],
                backgroundColor: sumaTotal > 0 ? ['#28a745', '#ffc107', '#dc3545', '#e9ecef'] : ['#e9ecef'],
                borderColor: '#ffffff',
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutoutPercentage: 65,
              legend: {
                display: false
              },
              tooltips: {
                callbacks: {
                  label: function(tooltipItem, data) {
                    if (sumaTotal === 0) return 'Sin datos de estudiantes aún';
                    const label = data.labels[tooltipItem.index] || '';
                    const value = data.datasets[0].data[tooltipItem.index];
                    const pct = ((value / sumaTotal) * 100).toFixed(1);
                    return label + ': ' + value + ' (' + pct + '%)';
                  }
                }
              }
            }
          });
        } else if (chartRendDocInstance) {
          chartRendDocInstance.resize();
        }
      <?php endif; ?>
    }

    // Toggle entre Vista Principal (Autoridad o Coordinador) y Vista Docente (si el usuario tiene clases)
    const btnToggle = document.getElementById('btnToggleVistaDocente');
    if (btnToggle) {
      btnToggle.addEventListener('click', function() {
        const seccAut = document.getElementById('seccion-autoridad');
        const seccCoord = document.getElementById('seccion-coordinador');
        const seccDoc = document.getElementById('seccion-docente');
        const txtToggle = document.getElementById('txtToggleDocente');

        const seccPrincipal = seccAut || seccCoord;
        const nombrePanel = seccAut ? 'Volver al Panel Principal' : 'Volver a Coordinación';

        if (seccDoc && seccDoc.style.display === 'none') {
          seccDoc.style.display = 'block';
          if (seccPrincipal) seccPrincipal.style.display = 'none';
          if (txtToggle) txtToggle.textContent = nombrePanel;
          btnToggle.className = 'btn btn-outline-primary';
          setTimeout(initChartsDocente, 50);
        } else if (seccDoc) {
          seccDoc.style.display = 'none';
          if (seccPrincipal) seccPrincipal.style.display = 'block';
          if (txtToggle) txtToggle.textContent = 'Ver Mis Clases como Docente (<?= $uds_count ?? 0 ?>)';
          btnToggle.className = 'btn btn-primary';
        }
      });
    }

    // Si la vista docente se carga visible (ej. rol docente), inicializar gráficos inmediatamente
    const seccDocInit = document.getElementById('seccion-docente');
    if (seccDocInit && seccDocInit.style.display !== 'none') {
      initChartsDocente();
    }

    // 3. PAGINACIÓN Y FILTROS INTERACTIVOS VISTA DOCENTE
    const paginadorDocAlertasAcad = inicializarPaginacionTabla({
      tableId: 'tabla-doc-alertas-acad',
      containerId: 'paginacion-doc-alertas-acad',
      selectId: 'tamano-doc-alertas-acad',
      infoId: 'info-doc-alertas-acad',
      paginasId: 'paginas-doc-alertas-acad',
      emptyRowId: 'alerta-academica-vacio-filtro',
      defaultPageSize: 10
    });

    const paginadorDocAlertasAsis = inicializarPaginacionTabla({
      tableId: 'tabla-doc-alertas-asis',
      containerId: 'paginacion-doc-alertas-asis',
      selectId: 'tamano-doc-alertas-asis',
      infoId: 'info-doc-alertas-asis',
      paginasId: 'paginas-doc-alertas-asis',
      emptyRowId: 'alerta-asistencia-vacio-filtro',
      defaultPageSize: 10
    });

    const paginadorDocGestion = inicializarPaginacionTabla({
      tableId: 'tabla-gestion-docente',
      containerId: 'paginacion-gestion-docente',
      selectId: 'tamano-gestion-docente',
      infoId: 'info-gestion-docente',
      paginasId: 'paginas-gestion-docente',
      defaultPageSize: 10
    });

    const filtroUD = document.getElementById('filtro-ud-alertas');
    if (filtroUD) {
      filtroUD.addEventListener('change', function() {
        const val = this.value;

        // Filtrar filas de alertas académicas
        let visibleAcad = 0;
        const filasAcad = document.querySelectorAll('.fila-alerta-academica');
        filasAcad.forEach(function(row) {
          const match = (val === 'todos' || row.getAttribute('data-pud') === val);
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibleAcad++;
        });
        if (paginadorDocAlertasAcad) {
          paginadorDocAlertasAcad.actualizar(true);
        }
        const badgeAcad = document.getElementById('badge-total-academicas');
        if (badgeAcad) badgeAcad.textContent = visibleAcad;

        // Filtrar filas de alertas de asistencia
        let visibleAsis = 0;
        const filasAsis = document.querySelectorAll('.fila-alerta-asistencia');
        filasAsis.forEach(function(row) {
          const match = (val === 'todos' || row.getAttribute('data-pud') === val);
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibleAsis++;
        });
        if (paginadorDocAlertasAsis) {
          paginadorDocAlertasAsis.actualizar(true);
        }
        const badgeAsis = document.getElementById('badge-total-asistencia');
        if (badgeAsis) badgeAsis.textContent = visibleAsis;
      });
    }

    <?php if (!empty($esCoordinador)): ?>

      // 4. GRÁFICO BARRAS COORDINADOR (Avance de Sesiones vs Calificaciones por UD)
      const ctxAvanceCoord = document.getElementById('chartAvanceCoord');
      if (ctxAvanceCoord) {
        new Chart(ctxAvanceCoord, {
          type: 'bar',
          data: {
            labels: <?= json_encode($chartLabelsCoord ?? []) ?>,
            datasets: [{
                label: '% Sesiones Desarrolladas',
                data: <?= json_encode($chartSesionesCoord ?? []) ?>,
                backgroundColor: 'rgba(2, 132, 199, 0.85)',
                borderColor: '#0284c7',
                borderWidth: 1,
                borderRadius: 4
              },
              {
                label: '% Calificaciones Registradas',
                data: <?= json_encode($chartCalificacionesCoord ?? []) ?>,
                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 4
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
              mode: 'index',
              intersect: false,
              callbacks: {
                label: function(tooltipItem, data) {
                  const datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                  return datasetLabel + ': ' + tooltipItem.yLabel + '%';
                }
              }
            },
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true,
                  max: 100,
                  callback: function(value) {
                    return value + '%';
                  }
                },
                gridLines: {
                  color: 'rgba(0, 0, 0, 0.05)',
                  zeroLineColor: 'rgba(0, 0, 0, 0.1)'
                }
              }],
              xAxes: [{
                gridLines: {
                  display: false
                }
              }]
            },
            legend: {
              display: false
            }
          }
        });
      }

      // 5. GRÁFICO DOUGHNUT COORDINADOR (Distribución Rendimiento de Estudiantes)
      const ctxRendCoord = document.getElementById('chartRendimientoCoord');
      if (ctxRendCoord) {
        const dataRend = [
          <?= (int)($chartRendimientoCoord['aprobados'] ?? 0) ?>,
          <?= (int)($chartRendimientoCoord['en_riesgo'] ?? 0) ?>,
          <?= (int)($chartRendimientoCoord['desaprobados'] ?? 0) ?>,
          <?= (int)($chartRendimientoCoord['sin_calificar'] ?? 0) ?>
        ];
        const sumRend = dataRend.reduce((a, b) => a + b, 0);

        new Chart(ctxRendCoord, {
          type: 'doughnut',
          data: {
            labels: ['Aprobados (≥13)', 'Recuperación (10-12)', 'Desaprobados (<10)', 'Sin Calificar'],
            datasets: [{
              data: sumRend > 0 ? dataRend : [1],
              backgroundColor: sumRend > 0 ? ['#10b981', '#f59e0b', '#ef4444', '#cbd5e1'] : ['#e2e8f0'],
              borderColor: '#ffffff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 68,
            legend: {
              display: false
            },
            tooltips: {
              callbacks: {
                label: function(tooltipItem, data) {
                  if (sumRend === 0) return 'Sin datos de estudiantes aún';
                  const label = data.labels[tooltipItem.index] || '';
                  const value = data.datasets[0].data[tooltipItem.index];
                  const pct = ((value / sumRend) * 100).toFixed(1);
                  return label + ': ' + value + ' (' + pct + '%)';
                }
              }
            }
          }
        });
      }

      // 6. PAGINADORES Y FILTRO DE ALERTAS TEMPRANAS COORDINADOR
      const paginadorCoordAlertasAcad = inicializarPaginacionTabla({
        tableId: 'tabla-coord-alertas-acad',
        containerId: 'paginacion-coord-alertas-acad',
        selectId: 'tamano-coord-alertas-acad',
        infoId: 'info-coord-alertas-acad',
        paginasId: 'paginas-coord-alertas-acad',
        emptyRowId: 'alerta-coord-academica-vacio-filtro',
        defaultPageSize: 10
      });

      const paginadorCoordAlertasAsis = inicializarPaginacionTabla({
        tableId: 'tabla-coord-alertas-asis',
        containerId: 'paginacion-coord-alertas-asis',
        selectId: 'tamano-coord-alertas-asis',
        infoId: 'info-coord-alertas-asis',
        paginasId: 'paginas-coord-alertas-asis',
        emptyRowId: 'alerta-coord-asistencia-vacio-filtro',
        defaultPageSize: 10
      });

      const filtroAlertasCoord = document.getElementById('filtro-ud-alertas-coord');
      if (filtroAlertasCoord) {
        filtroAlertasCoord.addEventListener('change', function() {
          const val = this.value;

          // Filtrar académicas
          let visibleAcad = 0;
          const filasAcad = document.querySelectorAll('.fila-coord-alerta-academica');
          filasAcad.forEach(function(row) {
            const match = (val === 'todos' || row.getAttribute('data-pud') === val);
            row.setAttribute('data-oculto-filtro', match ? '0' : '1');
            if (match) visibleAcad++;
          });
          if (paginadorCoordAlertasAcad) {
            paginadorCoordAlertasAcad.actualizar(true);
          }
          const badgeAcad = document.getElementById('badge-total-coord-academicas');
          if (badgeAcad) badgeAcad.textContent = visibleAcad;

          // Filtrar asistencia
          let visibleAsis = 0;
          const filasAsis = document.querySelectorAll('.fila-coord-alerta-asistencia');
          filasAsis.forEach(function(row) {
            const match = (val === 'todos' || row.getAttribute('data-pud') === val);
            row.setAttribute('data-oculto-filtro', match ? '0' : '1');
            if (match) visibleAsis++;
          });
          if (paginadorCoordAlertasAsis) {
            paginadorCoordAlertasAsis.actualizar(true);
          }
          const badgeAsis = document.getElementById('badge-total-coord-asistencia');
          if (badgeAsis) badgeAsis.textContent = visibleAsis;
        });
      }

      // 7. FILTROS Y PAGINACIÓN EN TABLA DE GESTIÓN DE UNIDADES DIDÁCTICAS DEL COORDINADOR
      const paginadorCoordGestion = inicializarPaginacionTabla({
        tableId: 'tabla-gestion-coordinador',
        containerId: 'paginacion-gestion-coordinador',
        selectId: 'tamano-gestion-coordinador',
        infoId: 'info-gestion-coordinador',
        paginasId: 'paginas-gestion-coordinador',
        emptyRowId: 'fila-coord-vacia-filtro',
        defaultPageSize: 10
      });

      const selectPrograma = document.getElementById('filtro-coord-programa');
      const selectPlan = document.getElementById('filtro-coord-plan');
      const selectPeriodo = document.getElementById('filtro-coord-periodo');
      const selectSemestre = document.getElementById('filtro-coord-semestre');
      const btnLimpiar = document.getElementById('btn-limpiar-filtros-coord');
      const inputBusqueda = document.getElementById('busqueda-coord-ud');
      const contadorFiltro = document.getElementById('contador-uds-visibles');

      // Al cambiar Periodo Histórico: Recargar página con ?periodo_filtro=ID
      if (selectPeriodo) {
        selectPeriodo.addEventListener('change', function() {
          const idPeriodo = this.value;
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('periodo_filtro', idPeriodo);
          window.location.href = currentUrl.toString();
        });
      }

      // Buscador instantáneo en tiempo real integrado con filtros
      if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function() {
          aplicarFiltrosTablaCoord();
        });
      }

      // Al cambiar de Programa: Actualizar opciones de Plan de Estudios
      if (selectPrograma) {
        selectPrograma.addEventListener('change', function() {
          const progId = this.value;
          if (selectPlan) {
            Array.from(selectPlan.options).forEach(function(opt) {
              if (opt.value === 'todos') {
                opt.style.display = '';
              } else {
                const optProg = opt.getAttribute('data-prog');
                opt.style.display = (progId === 'todos' || optProg === progId) ? '' : 'none';
              }
            });
            selectPlan.value = 'todos';
          }
          aplicarFiltrosTablaCoord();
        });
      }

      if (selectPlan) {
        selectPlan.addEventListener('change', aplicarFiltrosTablaCoord);
      }
      if (selectSemestre) {
        selectSemestre.addEventListener('change', aplicarFiltrosTablaCoord);
      }

      if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
          if (selectPrograma) selectPrograma.value = selectPrograma.options[0].value;
          if (selectPlan) selectPlan.value = 'todos';
          if (selectSemestre) selectSemestre.value = 'todos';
          if (inputBusqueda) inputBusqueda.value = '';
          aplicarFiltrosTablaCoord();
        });
      }

      function aplicarFiltrosTablaCoord() {
        const progVal = selectPrograma ? selectPrograma.value : 'todos';
        const planVal = selectPlan ? selectPlan.value : 'todos';
        const semVal = selectSemestre ? selectSemestre.value : 'todos';
        const query = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : '';

        const filas = document.querySelectorAll('.fila-ud-coord');
        let visibles = 0;

        filas.forEach(function(row) {
          const rProg = row.getAttribute('data-prog');
          const rPlan = row.getAttribute('data-plan');
          const rSem = row.getAttribute('data-semestre');
          const rBusqueda = row.getAttribute('data-busqueda') || '';

          const matchProg = (progVal === 'todos' || rProg === progVal);
          const matchPlan = (planVal === 'todos' || rPlan === planVal);
          const matchSem = (semVal === 'todos' || rSem === semVal);
          const matchQuery = (query === '' || rBusqueda.indexOf(query) !== -1);

          const match = matchProg && matchPlan && matchSem && matchQuery;
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibles++;
        });

        if (paginadorCoordGestion) {
          paginadorCoordGestion.actualizar(true);
        }

        if (contadorFiltro) {
          contadorFiltro.textContent = visibles;
        }
      }

    <?php endif; ?>

    <?php if (!empty($esAutoridad)): ?>

      // =========================================================================
      // SCRIPTS ESPECÍFICOS PARA JEFE DE UNIDAD ACADÉMICA / DIRECTOR (AUTORIDAD)
      // =========================================================================

      // 1. SELECTOR SUPERIOR DE ÁMBITO / PROGRAMA DE ESTUDIOS
      const selectAutProgTop = document.getElementById('filtro-autoridad-programa');
      if (selectAutProgTop) {
        selectAutProgTop.addEventListener('change', function() {
          const val = this.value;
          const currentUrl = new URL(window.location.href);
          if (val === 'todos') {
            currentUrl.searchParams.delete('programa_filtro');
          } else {
            currentUrl.searchParams.set('programa_filtro', val);
          }
          window.location.href = currentUrl.toString();
        });
      }

      // 2. SELECTOR SUPERIOR DE PERIODO ACADÉMICO HISTÓRICO
      const selectAutPeriodoTop = document.getElementById('filtro-autoridad-periodo');
      if (selectAutPeriodoTop) {
        selectAutPeriodoTop.addEventListener('change', function() {
          const val = this.value;
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('periodo_filtro', val);
          window.location.href = currentUrl.toString();
        });
      }

      // 3. GRÁFICO COMPARATIVO INSTITUCIONAL / PROGRAMA
      const ctxAvanceAut = document.getElementById('chartAvanceAutoridad');
      if (ctxAvanceAut) {
        const tipoGraficoAut = '<?= $chartTipoAutoridad ?? "programas" ?>';
        const labelsAut = <?= json_encode($chartLabelsAutoridad ?? []) ?>;
        const silabosAut = <?= json_encode($chartSilabosAutoridad ?? []) ?>;
        const sesionesAut = <?= json_encode($chartSesionesAutoridad ?? []) ?>;
        const calificacionesAut = <?= json_encode($chartCalificacionesAutoridad ?? []) ?>;

        const datasetsAut = [];
        if (tipoGraficoAut === 'programas') {
          datasetsAut.push({
            label: 'Sílabos (%)',
            data: silabosAut,
            backgroundColor: '#8b5cf6',
            borderRadius: 4
          });
        }
        datasetsAut.push({
          label: 'Sesiones Desarrolladas (%)',
          data: sesionesAut,
          backgroundColor: '#0284c7',
          borderRadius: 4
        });
        datasetsAut.push({
          label: 'Calificaciones (%)',
          data: calificacionesAut,
          backgroundColor: '#10b981',
          borderRadius: 4
        });

        new Chart(ctxAvanceAut, {
          type: 'bar',
          data: {
            labels: labelsAut,
            datasets: datasetsAut
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              display: true,
              position: 'top',
              labels: {
                boxWidth: 12,
                fontSize: 11
              }
            },
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true,
                  max: 100,
                  callback: function(value) { return value + '%'; }
                },
                gridLines: {
                  color: 'rgba(0,0,0,0.05)'
                }
              }],
              xAxes: [{
                gridLines: { display: false },
                ticks: {
                  fontSize: 11
                }
              }]
            },
            tooltips: {
              callbacks: {
                label: function(tooltipItem, data) {
                  const dsLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                  return dsLabel + ': ' + tooltipItem.yLabel + '%';
                }
              }
            }
          }
        });
      }

      // 4. GRÁFICO RENDIMIENTO GLOBAL INSTITUCIONAL
      const ctxRendAut = document.getElementById('chartRendimientoAutoridad');
      if (ctxRendAut) {
        const apAut = <?= (int)($chartRendimientoAutoridad['aprobados'] ?? 0) ?>;
        const riAut = <?= (int)($chartRendimientoAutoridad['en_riesgo'] ?? 0) ?>;
        const deAut = <?= (int)($chartRendimientoAutoridad['desaprobados'] ?? 0) ?>;
        const scAut = <?= (int)($chartRendimientoAutoridad['sin_calificar'] ?? 0) ?>;
        const sumRendAut = apAut + riAut + deAut + scAut;

        new Chart(ctxRendAut, {
          type: 'doughnut',
          data: {
            labels: ['Aprobados (>=13)', 'En Riesgo (10-12)', 'Desaprobados (<10)', 'Sin Calificar'],
            datasets: [{
              data: sumRendAut > 0 ? [apAut, riAut, deAut, scAut] : [0, 0, 0, 1],
              backgroundColor: sumRendAut > 0 ? ['#10b981', '#f59e0b', '#ef4444', '#cbd5e1'] : ['#e2e8f0'],
              borderColor: '#ffffff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 68,
            legend: { display: false },
            tooltips: {
              callbacks: {
                label: function(tooltipItem, data) {
                  if (sumRendAut === 0) return 'Sin datos de estudiantes aún';
                  const label = data.labels[tooltipItem.index] || '';
                  const value = data.datasets[0].data[tooltipItem.index];
                  const pct = ((value / sumRendAut) * 100).toFixed(1);
                  return label + ': ' + value + ' (' + pct + '%)';
                }
              }
            }
          }
        });
      }

      // 4.1 PAGINADOR TABLA PROGRAMAS COMPARATIVA AUTORIDAD
      const paginadorAutProgramas = inicializarPaginacionTabla({
        tableId: 'tabla-aut-programas',
        containerId: 'paginacion-aut-programas',
        selectId: 'tamano-aut-programas',
        infoId: 'info-aut-programas',
        paginasId: 'paginas-aut-programas',
        defaultPageSize: 10
      });

      // 5. FILTROS Y PAGINADORES DE ALERTAS TEMPRANAS AUTORIDAD (POR PROGRAMA Y POR UD)
      const paginadorAutAlertasAcad = inicializarPaginacionTabla({
        tableId: 'tabla-aut-alertas-acad',
        containerId: 'paginacion-aut-alertas-acad',
        selectId: 'tamano-aut-alertas-acad',
        infoId: 'info-aut-alertas-acad',
        paginasId: 'paginas-aut-alertas-acad',
        emptyRowId: 'alerta-aut-academica-vacio-filtro',
        defaultPageSize: 10
      });

      const paginadorAutAlertasAsis = inicializarPaginacionTabla({
        tableId: 'tabla-aut-alertas-asis',
        containerId: 'paginacion-aut-alertas-asis',
        selectId: 'tamano-aut-alertas-asis',
        infoId: 'info-aut-alertas-asis',
        paginasId: 'paginas-aut-alertas-asis',
        emptyRowId: 'alerta-aut-asistencia-vacio-filtro',
        defaultPageSize: 10
      });

      const filtroAlertasProgAut = document.getElementById('filtro-autoridad-alertas-prog');
      const filtroAlertasUdAut = document.getElementById('filtro-autoridad-alertas-ud');

      function filtrarAlertasAutoridad() {
        const progVal = filtroAlertasProgAut ? filtroAlertasProgAut.value : 'todos';
        const udVal = filtroAlertasUdAut ? filtroAlertasUdAut.value : 'todos';

        // Filtrar académicas
        let visibleAcad = 0;
        const filasAcad = document.querySelectorAll('.fila-aut-alerta-academica');
        filasAcad.forEach(function(row) {
          const rProg = row.getAttribute('data-prog');
          const rPud = row.getAttribute('data-pud');
          const matchProg = (progVal === 'todos' || rProg === progVal);
          const matchUd = (udVal === 'todos' || rPud === udVal);
          const match = matchProg && matchUd;
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibleAcad++;
        });
        if (paginadorAutAlertasAcad) {
          paginadorAutAlertasAcad.actualizar(true);
        }
        const badgeAcad = document.getElementById('badge-total-aut-academicas');
        if (badgeAcad) badgeAcad.textContent = visibleAcad;

        // Filtrar asistencia
        let visibleAsis = 0;
        const filasAsis = document.querySelectorAll('.fila-aut-alerta-asistencia');
        filasAsis.forEach(function(row) {
          const rProg = row.getAttribute('data-prog');
          const rPud = row.getAttribute('data-pud');
          const matchProg = (progVal === 'todos' || rProg === progVal);
          const matchUd = (udVal === 'todos' || rPud === udVal);
          const match = matchProg && matchUd;
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibleAsis++;
        });
        if (paginadorAutAlertasAsis) {
          paginadorAutAlertasAsis.actualizar(true);
        }
        const badgeAsis = document.getElementById('badge-total-aut-asistencia');
        if (badgeAsis) badgeAsis.textContent = visibleAsis;
      }

      if (filtroAlertasProgAut) {
        filtroAlertasProgAut.addEventListener('change', function() {
          const progId = this.value;
          // Actualizar opciones de UD
          if (filtroAlertasUdAut) {
            Array.from(filtroAlertasUdAut.options).forEach(function(opt) {
              if (opt.value === 'todos') {
                opt.style.display = '';
              } else {
                const optProg = opt.getAttribute('data-prog');
                opt.style.display = (progId === 'todos' || optProg === progId) ? '' : 'none';
              }
            });
            filtroAlertasUdAut.value = 'todos';
          }
          filtrarAlertasAutoridad();
        });
      }

      if (filtroAlertasUdAut) {
        filtroAlertasUdAut.addEventListener('change', filtrarAlertasAutoridad);
      }

      // 6. FILTROS Y PAGINACIÓN EN TABLA DE GESTIÓN DE UNIDADES DIDÁCTICAS AUTORIDAD
      const paginadorAutGestion = inicializarPaginacionTabla({
        tableId: 'tabla-gestion-autoridad',
        containerId: 'paginacion-gestion-autoridad',
        selectId: 'tamano-gestion-autoridad',
        infoId: 'info-gestion-autoridad',
        paginasId: 'paginas-gestion-autoridad',
        emptyRowId: 'fila-aut-vacia-filtro',
        defaultPageSize: 10
      });

      const selectProgAutTabla = document.getElementById('filtro-aut-tabla-prog');
      const selectPlanAutTabla = document.getElementById('filtro-aut-tabla-plan');
      const selectPeriodoAutTabla = document.getElementById('filtro-aut-tabla-periodo');
      const selectSemestreAutTabla = document.getElementById('filtro-aut-tabla-semestre');
      const btnLimpiarAutTabla = document.getElementById('btn-limpiar-filtros-aut');
      const inputBusquedaAut = document.getElementById('busqueda-aut-ud');
      const contadorFiltroAut = document.getElementById('contador-uds-visibles-aut');

      if (selectPeriodoAutTabla) {
        selectPeriodoAutTabla.addEventListener('change', function() {
          const idPeriodo = this.value;
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('periodo_filtro', idPeriodo);
          window.location.href = currentUrl.toString();
        });
      }

      if (inputBusquedaAut) {
        inputBusquedaAut.addEventListener('input', function() {
          aplicarFiltrosTablaAutoridad();
        });
      }

      if (selectProgAutTabla) {
        selectProgAutTabla.addEventListener('change', function() {
          const progId = this.value;
          if (selectPlanAutTabla) {
            Array.from(selectPlanAutTabla.options).forEach(function(opt) {
              if (opt.value === 'todos') {
                opt.style.display = '';
              } else {
                const optProg = opt.getAttribute('data-prog');
                opt.style.display = (progId === 'todos' || optProg === progId) ? '' : 'none';
              }
            });
            selectPlanAutTabla.value = 'todos';
          }
          aplicarFiltrosTablaAutoridad();
        });
      }

      if (selectPlanAutTabla) {
        selectPlanAutTabla.addEventListener('change', aplicarFiltrosTablaAutoridad);
      }
      if (selectSemestreAutTabla) {
        selectSemestreAutTabla.addEventListener('change', aplicarFiltrosTablaAutoridad);
      }

      if (btnLimpiarAutTabla) {
        btnLimpiarAutTabla.addEventListener('click', function() {
          if (selectProgAutTabla) selectProgAutTabla.value = 'todos';
          if (selectPlanAutTabla) selectPlanAutTabla.value = 'todos';
          if (selectSemestreAutTabla) selectSemestreAutTabla.value = 'todos';
          if (inputBusquedaAut) inputBusquedaAut.value = '';
          aplicarFiltrosTablaAutoridad();
        });
      }

      function aplicarFiltrosTablaAutoridad() {
        const progVal = selectProgAutTabla ? selectProgAutTabla.value : 'todos';
        const planVal = selectPlanAutTabla ? selectPlanAutTabla.value : 'todos';
        const semVal = selectSemestreAutTabla ? selectSemestreAutTabla.value : 'todos';
        const query = inputBusquedaAut ? inputBusquedaAut.value.toLowerCase().trim() : '';

        const filas = document.querySelectorAll('.fila-ud-aut');
        let visibles = 0;

        filas.forEach(function(row) {
          const rProg = row.getAttribute('data-prog');
          const rPlan = row.getAttribute('data-plan');
          const rSem = row.getAttribute('data-semestre');
          const rBusqueda = row.getAttribute('data-busqueda') || '';

          const matchProg = (progVal === 'todos' || rProg === progVal);
          const matchPlan = (planVal === 'todos' || rPlan === planVal);
          const matchSem = (semVal === 'todos' || rSem === semVal);
          const matchQuery = (query === '' || rBusqueda.indexOf(query) !== -1);

          const match = matchProg && matchPlan && matchSem && matchQuery;
          row.setAttribute('data-oculto-filtro', match ? '0' : '1');
          if (match) visibles++;
        });

        if (paginadorAutGestion) {
          paginadorAutGestion.actualizar(true);
        }

        if (contadorFiltroAut) {
          contadorFiltroAut.textContent = visibles;
        }
      }

    <?php endif; ?>

  });
</script>

<!-- MODALES OFICIALES DE REPORTES ACADÉMICOS -->
<?php if (!empty($esCoordinador) || !empty($esAutoridad)): ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_matricula.php'); ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_calif_consolidado.php'); ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_calif_detallado.php'); ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_calif_individual.php'); ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_primeros_puestos.php'); ?>
  <?php include_once(__DIR__ . '/reportes/modals/modal_reporte_control_diario.php'); ?>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>