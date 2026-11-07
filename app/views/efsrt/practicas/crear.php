<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 text-primary font-weight-bold">
            <i class="fas fa-file-signature mr-2"></i> Apertura de Expediente de EFSRT
        </h3>
        <p class="text-muted mb-0">Registre la empresa receptora, módulo formativo y cronograma de prácticas.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="card-title mb-0 font-weight-bold text-dark">
            <i class="fas fa-edit mr-2 text-primary"></i> Formulario de Registro de Práctica
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/efsrt/practicas/guardar" id="formCrearPractica">
            
            <!-- DATOS DEL ESTUDIANTE -->
            <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">
                <i class="fas fa-user-graduate mr-1"></i> 1. Datos del Estudiante Practicante
            </h6>

            <?php if ($esEstudiante && !empty($estudianteInfo)): ?>
                <input type="hidden" name="id_estudiante" value="<?= $estudianteInfo['id'] ?>">
                <input type="hidden" name="id_programa_estudios" id="reg_id_programa" value="<?= $estudianteInfo['id_programa_estudios'] ?>">

                <div class="row bg-light rounded p-3 mb-4 mx-0 border">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Estudiante:</small>
                        <strong class="font-size-15 text-dark"><?= htmlspecialchars($estudianteInfo['apellidos_nombres']) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">DNI:</small>
                        <strong class="font-size-15 text-dark"><?= htmlspecialchars($estudianteInfo['dni']) ?></strong>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block">Programa de Estudios:</small>
                        <span class="badge badge-info mr-1"><?= htmlspecialchars($estudianteInfo['programa_codigo']) ?></span>
                        <strong class="font-size-14 text-dark"><?= htmlspecialchars($estudianteInfo['programa_nombre']) ?></strong>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Plan de Estudios <span class="text-danger">*</span></label>
                        <select name="id_plan_estudio" id="reg_id_plan" class="form-control" required onchange="cargarModulosPorPlan(this.value)">
                            <option value="">-- Seleccionar Plan de Estudios --</option>
                            <?php foreach ($planesDisponibles as $pln): ?>
                                <option value="<?= $pln['id'] ?>" <?= (!empty($estudianteInfo['id_plan_estudio']) && $estudianteInfo['id_plan_estudio'] == $pln['id']) ? 'selected' : '' ?>>
                                    Plan <?= htmlspecialchars($pln['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">Buscar Estudiante por DNI o Apellidos <span class="text-danger">*</span></label>
                        <input type="text" id="busquedaEstudiante" class="form-control" placeholder="Escriba DNI o apellidos del estudiante..." autocomplete="off">
                        <div id="resultadosEstudiantes" class="list-group position-absolute shadow" style="z-index: 1050; display: none; width: 95%;"></div>
                        <input type="hidden" name="id_estudiante" id="reg_id_estudiante" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Estudiante Seleccionado</label>
                        <input type="text" id="nombreEstudianteSeleccionado" class="form-control bg-light" readonly placeholder="Ninguno seleccionado" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Programa de Estudios <span class="text-danger">*</span></label>
                        <select name="id_programa_estudios" id="reg_id_programa" class="form-control" required onchange="cargarPlanesYModulosPorPrograma(this.value)">
                            <option value="">-- Seleccione Programa --</option>
                            <?php foreach ($programasDisponibles as $prg): ?>
                                <option value="<?= $prg['id'] ?>">
                                    <?= htmlspecialchars($prg['codigo']) ?> - <?= htmlspecialchars($prg['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Plan de Estudios <span class="text-danger">*</span></label>
                        <select name="id_plan_estudio" id="reg_id_plan" class="form-control" required onchange="cargarModulosPorPlan(this.value)">
                            <option value="">-- Primero seleccione programa o estudiante --</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DATOS ACADÉMICOS Y DEL MÓDULO -->
            <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2 mt-4">
                <i class="fas fa-book-open mr-1"></i> 2. Módulo Formativo y Periodo
            </h6>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold">Periodo Académico <span class="text-danger">*</span></label>
                    <select name="id_periodo_academico" id="reg_id_periodo" class="form-control" required>
                        <?php foreach ($periodos as $per): ?>
                            <option value="<?= $per['id'] ?>" <?= ($id_periodo_actual == $per['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($per['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold">Módulo Formativo de EFSRT <span class="text-danger">*</span></label>
                    <select name="id_modulo_formativo" id="reg_id_modulo" class="form-control" required>
                        <option value="">-- Seleccionar Módulo --</option>
                        <?php if (!empty($modulosDisponibles)): ?>
                            <?php foreach ($modulosDisponibles as $mf): ?>
                                <option value="<?= $mf['id'] ?>">
                                    Módulo <?= htmlspecialchars($mf['nro_modulo']) ?>: <?= htmlspecialchars($mf['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- DATOS DE LA EMPRESA Y ÁREA -->
            <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2 mt-4">
                <i class="fas fa-building mr-1"></i> 3. Empresa Receptora y Desempeño
            </h6>

            <div class="row">
                <div class="col-md-7 form-group">
                    <label class="font-weight-bold">Empresa / Institución Receptora <span class="text-danger">*</span></label>
                    <select name="id_empresa" id="reg_id_empresa" class="form-control select2" required onchange="cargarConveniosEmpresa(this.value)">
                        <option value="">-- Seleccionar Empresa --</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['razon_social']) ?> (RUC: <?= htmlspecialchars($emp['ruc'] ?: 'S/N') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5 form-group">
                    <label class="font-weight-bold">Convenio Vinculado (Opcional)</label>
                    <select name="id_convenio" id="reg_id_convenio" class="form-control">
                        <option value="">-- Sin convenio / Práctica individual --</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label class="font-weight-bold">Área o Puesto de Desempeño <span class="text-danger">*</span></label>
                    <input type="text" name="area_desempeno" class="form-control" required placeholder="Ej. Área de Soporte Técnico, Desarrollo de Software, Contabilidad, Asistencia Administrativa">
                </div>
            </div>

            <!-- FECHAS Y HORAS -->
            <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2 mt-4">
                <i class="fas fa-calendar-alt mr-1"></i> 4. Duración y Horas
            </h6>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_inicio" id="reg_fecha_inicio" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">Fecha de Fin / Término <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_fin" id="reg_fecha_fin" class="form-control" required>
                </div>
                <div class="col-md-4 form-group">
                    <label class="font-weight-bold">Horas Requeridas <span class="text-danger">*</span></label>
                    <input type="number" name="horas_requeridas" class="form-control" value="240" min="10" max="1000" required>
                    <small class="text-muted">Por defecto: 240 horas académicas</small>
                </div>
            </div>

            <div class="text-right mt-4 pt-3 border-top">
                <a href="<?= BASE_URL ?>/efsrt/practicas" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Registrar y Abrir Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function cargarConveniosEmpresa(idEmpresa) {
    var selectConv = document.getElementById('reg_id_convenio');
    selectConv.innerHTML = '<option value="">-- Sin convenio / Práctica individual --</option>';
    if (!idEmpresa) return;

    fetch('<?= BASE_URL ?>/efsrt/convenios/getConveniosEmpresaAjax/' + idEmpresa)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.numero_convenio + ' (' + c.tipo_convenio + ') - Vence: ' + c.fecha_fin;
                    selectConv.appendChild(opt);
                });
            }
        });
}

// Búsqueda AJAX de estudiantes si no es estudiante
var inputBusq = document.getElementById('busquedaEstudiante');
if (inputBusq) {
    inputBusq.addEventListener('keyup', function() {
        var q = this.value.trim();
        var lista = document.getElementById('resultadosEstudiantes');
        if (q.length < 2) {
            lista.style.display = 'none';
            return;
        }

        fetch('<?= BASE_URL ?>/efsrt/practicas/buscarEstudiantesAjax?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                lista.innerHTML = '';
                if (data.length === 0) {
                    lista.innerHTML = '<div class="list-group-item text-muted">No se encontraron estudiantes</div>';
                } else {
                    data.forEach(function(est) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action py-2';
                        a.innerHTML = '<strong>' + est.apellidos_nombres + '</strong> (DNI: ' + est.dni + ') - <small class="text-muted">' + est.programa_nombre + '</small>';
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            document.getElementById('reg_id_estudiante').value = est.id;
                            document.getElementById('nombreEstudianteSeleccionado').value = est.apellidos_nombres + ' (' + est.dni + ')';
                            var regProg = document.getElementById('reg_id_programa');
                            if (regProg) regProg.value = est.id_programa_estudios;
                            cargarPlanesYModulosPorPrograma(est.id_programa_estudios, est.id_plan_estudio);
                            lista.style.display = 'none';
                            inputBusq.value = '';
                        });
                        lista.appendChild(a);
                    });
                }
                lista.style.display = 'block';
            });
    });
}

function cargarModulosPorPlan(idPlan) {
    var selectMod = document.getElementById('reg_id_modulo');
    selectMod.innerHTML = '<option value="">-- Seleccionar Módulo --</option>';
    if (!idPlan) return;

    fetch('<?= BASE_URL ?>/efsrt/practicas/getModulosPorPlanAjax/' + idPlan)
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                data.forEach(function(m) {
                    var opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = 'Módulo ' + m.nro_modulo + ': ' + m.nombre;
                    selectMod.appendChild(opt);
                });
            }
        });
}

function cargarPlanesYModulosPorPrograma(idPrograma, idPlanSeleccionado = null) {
    var selectPlan = document.getElementById('reg_id_plan');
    var selectMod = document.getElementById('reg_id_modulo');
    if (!selectPlan) return;

    selectPlan.innerHTML = '<option value="">-- Cargando planes... --</option>';
    selectMod.innerHTML = '<option value="">-- Seleccionar Módulo --</option>';
    if (!idPrograma) {
        selectPlan.innerHTML = '<option value="">-- Primero seleccione programa --</option>';
        return;
    }

    fetch('<?= BASE_URL ?>/efsrt/practicas/getPlanesYModulosPorProgramaAjax/' + idPrograma)
        .then(res => res.json())
        .then(data => {
            selectPlan.innerHTML = '<option value="">-- Seleccionar Plan de Estudios --</option>';
            var planToSelect = idPlanSeleccionado;
            if (data.planes && data.planes.length > 0) {
                data.planes.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = 'Plan ' + p.nombre;
                    if (idPlanSeleccionado && p.id == idPlanSeleccionado) {
                        opt.selected = true;
                    }
                    selectPlan.appendChild(opt);
                });
                if (!planToSelect) {
                    planToSelect = data.planes[0].id;
                    selectPlan.value = planToSelect;
                } else {
                    selectPlan.value = planToSelect;
                }
            }
            if (planToSelect) {
                cargarModulosPorPlan(planToSelect);
            }
        });
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
