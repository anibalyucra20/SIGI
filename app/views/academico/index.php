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
  .badge-pulse-danger {
    animation: pulse-danger 2s infinite;
  }
  @keyframes pulse-danger {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
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
</style>

<div class="row align-items-center mb-4">
  <div class="col-md-7">
    <h3 class="mb-1 text-dark font-weight-bold">
      <i class="feather-grid text-primary mr-2"></i>Panel Académico
    </h3>
    <p class="text-muted mb-0">
      Bienvenido(a), <strong><?= htmlspecialchars($_SESSION['sigi_user_name'] ?? 'Docente') ?></strong> &bull; Periodo: <span class="badge badge-light-primary px-2 py-1"><?= htmlspecialchars($periodo) ?></span>
    </p>
  </div>
  <div class="col-md-5 text-md-right mt-3 mt-md-0">
    <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-primary shadow-sm">
      <i class="fa fa-book mr-1"></i> Mis Unidades Didácticas
    </a>
  </div>
</div>

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
              <table class="table table-hover align-middle mb-0">
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
              <table class="table table-hover align-middle mb-0">
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
        <table class="table table-hover align-middle mb-0">
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
              <tr>
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
    </div>
  </div>

<?php endif; ?>

<!-- 5. PANEL INFORMATIVO PARA ROLES ADMINISTRATIVOS / COORDINADORES -->
<?php if (\Core\Auth::esAdminAcademico() || \Core\Auth::esCoordinadorPEAcademico() || \Core\Auth::esDirectorAcademico() || \Core\Auth::esJUAAcademico() || \Core\Auth::esSecretarioAcadAcademico()): ?>
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

<!-- Carga de Chart.js desde los assets locales del sistema -->
<script src="<?= BASE_URL ?>/assets/libs/chart-js/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($esDocente) && $uds_count > 0): ?>

    // 1. Gráfico de Avance por Unidad Didáctica (Barras Comparativas)
    const ctxAvance = document.getElementById('chartAvanceDocente');
    if (ctxAvance) {
      const labels = <?= json_encode($chartLabels) ?>;
      const dataSesiones = <?= json_encode($chartSesiones) ?>;
      const dataCalificaciones = <?= json_encode($chartCalificaciones) ?>;

      new Chart(ctxAvance, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
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
                callback: function(value) { return value + '%'; }
              },
              gridLines: {
                color: 'rgba(0, 0, 0, 0.05)',
                zeroLineColor: 'rgba(0, 0, 0, 0.1)'
              }
            }],
            xAxes: [{
              gridLines: { display: false }
            }]
          },
          legend: { display: false }
        }
      });
    }

    // 2. Gráfico de Rendimiento Estudiantil (Doughnut Chart)
    const ctxRendimiento = document.getElementById('chartRendimiento');
    if (ctxRendimiento) {
      const datosRendimiento = [
        <?= (int)$chartRendimiento['aprobados'] ?>,
        <?= (int)$chartRendimiento['en_riesgo'] ?>,
        <?= (int)$chartRendimiento['desaprobados'] ?>,
        <?= (int)$chartRendimiento['sin_calificar'] ?>
      ];
      const sumaTotal = datosRendimiento.reduce((a, b) => a + b, 0);

      new Chart(ctxRendimiento, {
        type: 'doughnut',
        data: {
          labels: ['Aprobados (≥13)', 'Recuperación (10-12)', 'Desaprobados (<10)', 'Sin Calificar'],
          datasets: [{
            data: sumaTotal > 0 ? datosRendimiento : [1],
            backgroundColor: sumaTotal > 0 
              ? ['#28a745', '#ffc107', '#dc3545', '#e9ecef']
              : ['#e9ecef'],
            borderColor: '#ffffff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutoutPercentage: 65,
          legend: { display: false },
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
    }

    // 3. Filtro interactivo de Alertas Tempranas por Unidad Didáctica
    const filtroUD = document.getElementById('filtro-ud-alertas');
    if (filtroUD) {
      filtroUD.addEventListener('change', function() {
        const val = this.value;

        // Filtrar filas de alertas académicas
        let visibleAcad = 0;
        const filasAcad = document.querySelectorAll('.fila-alerta-academica');
        filasAcad.forEach(function(row) {
          const match = (val === 'todos' || row.getAttribute('data-pud') === val);
          row.style.display = match ? '' : 'none';
          if (match) visibleAcad++;
        });
        const vacioAcad = document.getElementById('alerta-academica-vacio-filtro');
        if (vacioAcad) {
          vacioAcad.style.display = (filasAcad.length > 0 && visibleAcad === 0) ? '' : 'none';
        }
        const badgeAcad = document.getElementById('badge-total-academicas');
        if (badgeAcad) badgeAcad.textContent = visibleAcad;

        // Filtrar filas de alertas de asistencia
        let visibleAsis = 0;
        const filasAsis = document.querySelectorAll('.fila-alerta-asistencia');
        filasAsis.forEach(function(row) {
          const match = (val === 'todos' || row.getAttribute('data-pud') === val);
          row.style.display = match ? '' : 'none';
          if (match) visibleAsis++;
        });
        const vacioAsis = document.getElementById('alerta-asistencia-vacio-filtro');
        if (vacioAsis) {
          vacioAsis.style.display = (filasAsis.length > 0 && visibleAsis === 0) ? '' : 'none';
        }
        const badgeAsis = document.getElementById('badge-total-asistencia');
        if (badgeAsis) badgeAsis.textContent = visibleAsis;
      });
    }

  <?php endif; ?>
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>