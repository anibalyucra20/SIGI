<?php
$cv   = $detalle['cv'] ?? [];
$usr  = $detalle['usuario'] ?? [];
$edu  = $detalle['educacion'] ?? [];
$exp  = $detalle['experiencia'] ?? [];
$hab  = $detalle['habilidades'] ?? [];
$idi  = $detalle['idiomas'] ?? [];
$cert = $detalle['certificados'] ?? [];
?>

<!-- ========================================== -->
<!-- 1. DATOS GENERALES Y PERFIL PROFESIONAL   -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light py-3 border-bottom">
        <h5 class="mb-0 text-dark font-weight-bold">
            <i class="fas fa-user-tie text-primary mr-2"></i>1. Datos Principales del Currículum
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- Resumen de Titular -->
        <div class="alert alert-light border mb-4 py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="text-muted small d-block">Titular del CV:</span>
                    <strong class="text-dark font-size-15"><?= htmlspecialchars($usr['nombre_completo'] ?? $_SESSION['sigi_user_name'] ?? 'Usuario') ?></strong>
                    <span class="text-muted small ml-2">(DNI: <?= htmlspecialchars($usr['dni'] ?? '-') ?>)</span>
                </div>
                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="text-muted small d-block">Programa / Carrera:</span>
                    <span class="badge badge-primary px-2 py-1 font-size-13"><?= htmlspecialchars($usr['programa_estudios'] ?? 'Educación Superior') ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Nombre del Currículum -->
            <div class="col-md-12 mb-3">
                <label class="form-label font-weight-bold" for="descripcion">
                    Nombre del Currículum <span class="text-danger">*</span>
                </label>
                <input type="text"name="descripcion" id="descripcion" class="form-control" maxlength="255" placeholder="" value="<?= htmlspecialchars($cv['descripcion'] ?? '') ?>"required>
            </div>

            <!-- Enlace Web a CV / Portafolio externo -->
            <div class="col-md-12 mb-2">
                <label class="form-label font-weight-bold">
                    Enlace a Currículum en la Nube o Portafolio Digital (Opcional)
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-link text-info"></i></span>
                    <input type="url" name="archivo" class="form-control" maxlength="200" placeholder="" value="<?= htmlspecialchars($cv['archivo'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. SECCIONES DEL CURRÍCULUM EN EL SISTEMA -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-dark font-weight-bold">
            <i class="fas fa-layer-group text-primary mr-2"></i>2. Secciones Detalladas (Para generar tu CV en el sistema)
        </h5>
        <small class="text-muted">Complete las secciones para generar su PDF profesional</small>
    </div>
    <div class="card-body p-4">

        <!-- Pestañas de navegación -->
        <ul class="nav nav-pills nav-justified mb-4" id="cvTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="tab-edu-link" data-toggle="pill" href="#tab-edu" role="tab">
                    <i class="fas fa-graduation-cap mr-1"></i> Educación
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-exp-link" data-toggle="pill" href="#tab-exp" role="tab">
                    <i class="fas fa-briefcase mr-1"></i> Experiencia
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-cert-link" data-toggle="pill" href="#tab-cert" role="tab">
                    <i class="fas fa-certificate mr-1"></i> Certificados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-hab-link" data-toggle="pill" href="#tab-hab" role="tab">
                    <i class="fas fa-tools mr-1"></i> Habilidades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-idi-link" data-toggle="pill" href="#tab-idi" role="tab">
                    <i class="fas fa-language mr-1"></i> Idiomas
                </a>
            </li>
        </ul>

        <div class="tab-content" id="cvTabsContent">

            <!-- ========================================== -->
            <!-- TAB 1: EDUCACIÓN                           -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="tab-edu" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">Formación Académica y Estudios</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-edu">
                        <i class="fas fa-plus mr-1"></i> Agregar Estudio
                    </button>
                </div>

                <div id="contenedor-educacion">
                    <?php if (!empty($edu)): ?>
                        <?php foreach ($edu as $idx => $e): ?>
                            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-educacion">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-light text-primary font-weight-bold">Estudio #<span class="num-item"><?= $idx + 1 ?></span></span>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="small font-weight-bold">Nivel Académico</label>
                                        <select name="educacion[<?= $idx ?>][nivel_academico]" class="form-control form-control-sm">
                                            <?php 
                                            $niveles = ['Secundaria Completa', 'Superior Técnico', 'Universitario', 'Bachiller', 'Titulado', 'Egresado', 'Diplomado', 'Maestría', 'Doctorado'];
                                            foreach ($niveles as $nv): ?>
                                                <option value="<?= $nv ?>" <?= (($e['nivel_academico'] ?? '') == $nv) ? 'selected' : '' ?>><?= $nv ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="small font-weight-bold">Centro de Estudios / Institución</label>
                                        <input type="text" name="educacion[<?= $idx ?>][centro_estudios]" class="form-control form-control-sm" placeholder="Ej: IESP Huanta" value="<?= htmlspecialchars($e['centro_estudios'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="small font-weight-bold">Carrera / Especialidad</label>
                                        <input type="text" name="educacion[<?= $idx ?>][especialidad]" class="form-control form-control-sm" placeholder="Ej: Computación e Informática" value="<?= htmlspecialchars($e['especialidad'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Inicio</label>
                                        <input type="date" name="educacion[<?= $idx ?>][inicio]" class="form-control form-control-sm" value="<?= htmlspecialchars($e['inicio'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Término</label>
                                        <input type="date" name="educacion[<?= $idx ?>][termino]" class="form-control form-control-sm" value="<?= htmlspecialchars($e['termino'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Enlace a Constancia/Título (Opcional)</label>
                                        <input type="url" name="educacion[<?= $idx ?>][archivo]" class="form-control form-control-sm" placeholder="https://..." value="<?= htmlspecialchars($e['archivo'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Plantilla inicial vacía -->
                        <div class="border rounded p-3 mb-3 bg-white shadow-sm item-educacion">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-light text-primary font-weight-bold">Estudio #<span class="num-item">1</span></span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="small font-weight-bold">Nivel Académico</label>
                                    <select name="educacion[0][nivel_academico]" class="form-control form-control-sm">
                                        <option value="Superior Técnico">Superior Técnico</option>
                                        <option value="Universitario">Universitario</option>
                                        <option value="Bachiller">Bachiller</option>
                                        <option value="Titulado">Titulado</option>
                                        <option value="Egresado" selected>Egresado</option>
                                        <option value="Secundaria Completa">Secundaria Completa</option>
                                        <option value="Diplomado">Diplomado</option>
                                        <option value="Maestría">Maestría</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small font-weight-bold">Centro de Estudios / Institución</label>
                                    <input type="text" name="educacion[0][centro_estudios]" class="form-control form-control-sm" placeholder="Ej: IESP Huanta">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small font-weight-bold">Carrera / Especialidad</label>
                                    <input type="text" name="educacion[0][especialidad]" class="form-control form-control-sm" placeholder="Ej: Educación Inicial" value="<?= htmlspecialchars($usr['programa_estudios'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Fecha Inicio</label>
                                    <input type="date" name="educacion[0][inicio]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Fecha Término</label>
                                    <input type="date" name="educacion[0][termino]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Enlace a Constancia/Título (Opcional)</label>
                                    <input type="url" name="educacion[0][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: EXPERIENCIA LABORAL                 -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-exp" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">Trayectoria y Experiencia Laboral</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-exp">
                        <i class="fas fa-plus mr-1"></i> Agregar Experiencia
                    </button>
                </div>

                <div id="contenedor-experiencia">
                    <?php if (!empty($exp)): ?>
                        <?php foreach ($exp as $idx => $ex): ?>
                            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-experiencia">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-light text-primary font-weight-bold">Experiencia #<span class="num-item"><?= $idx + 1 ?></span></span>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Empresa o Institución</label>
                                        <input type="text" name="experiencia[<?= $idx ?>][institucion]" class="form-control form-control-sm" placeholder="Ej: UGEL Huanta" value="<?= htmlspecialchars($ex['institucion'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Cargo / Puesto Desempeñado</label>
                                        <input type="text" name="experiencia[<?= $idx ?>][cargo]" class="form-control form-control-sm" placeholder="Ej: Asistente Administrativo" value="<?= htmlspecialchars($ex['cargo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Inicio</label>
                                        <input type="date" name="experiencia[<?= $idx ?>][inicio]" class="form-control form-control-sm" value="<?= htmlspecialchars($ex['inicio'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Término</label>
                                        <input type="date" name="experiencia[<?= $idx ?>][termino]" class="form-control form-control-sm" value="<?= htmlspecialchars($ex['termino'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Tiempo en el Cargo</label>
                                        <input type="text" name="experiencia[<?= $idx ?>][tiempo_cargo]" class="form-control form-control-sm" placeholder="Ej: 1 año, 6 meses" value="<?= htmlspecialchars($ex['tiempo_cargo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Enlace a Constancia (Opcional)</label>
                                        <input type="url" name="experiencia[<?= $idx ?>][archivo]" class="form-control form-control-sm" placeholder="https://..." value="<?= htmlspecialchars($ex['archivo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-12 mb-1">
                                        <label class="small font-weight-bold">Funciones y Logros Principales</label>
                                        <textarea name="experiencia[<?= $idx ?>][descripcion]" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Describa las principales tareas que desempeñó..."><?= htmlspecialchars($ex['descripcion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="border rounded p-3 mb-3 bg-white shadow-sm item-experiencia">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-light text-primary font-weight-bold">Experiencia #<span class="num-item">1</span></span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Empresa o Institución</label>
                                    <input type="text" name="experiencia[0][institucion]" class="form-control form-control-sm" placeholder="Ej: Municipalidad Provincial de Huanta">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Cargo / Puesto Desempeñado</label>
                                    <input type="text" name="experiencia[0][cargo]" class="form-control form-control-sm" placeholder="Ej: Practicante Profesional">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Fecha Inicio</label>
                                    <input type="date" name="experiencia[0][inicio]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Fecha Término</label>
                                    <input type="date" name="experiencia[0][termino]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Tiempo en el Cargo</label>
                                    <input type="text" name="experiencia[0][tiempo_cargo]" class="form-control form-control-sm" placeholder="Ej: 6 meses">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Enlace a Constancia (Opcional)</label>
                                    <input type="url" name="experiencia[0][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                                </div>
                                <div class="col-md-12 mb-1">
                                    <label class="small font-weight-bold">Funciones y Logros Principales</label>
                                    <textarea name="experiencia[0][descripcion]" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Describa las principales tareas que desempeñó..."></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: CERTIFICADOS Y CAPACITACIONES      -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-cert" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">Cursos, Talleres y Certificaciones</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-cert">
                        <i class="fas fa-plus mr-1"></i> Agregar Certificado
                    </button>
                </div>

                <div id="contenedor-certificados">
                    <?php if (!empty($cert)): ?>
                        <?php foreach ($cert as $idx => $c): ?>
                            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-certificado">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-light text-primary font-weight-bold">Certificado #<span class="num-item"><?= $idx + 1 ?></span></span>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Nombre del Curso / Certificación</label>
                                        <input type="text" name="certificados[<?= $idx ?>][nombre]" class="form-control form-control-sm" placeholder="Ej: Especialización en Ofimática Profesional" value="<?= htmlspecialchars($c['nombre'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Institución Emisora</label>
                                        <input type="text" name="certificados[<?= $idx ?>][institucion]" class="form-control form-control-sm" placeholder="Ej: SENATI / MINEDU" value="<?= htmlspecialchars($c['institucion'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-5 mb-2">
                                        <label class="small font-weight-bold">Tema o Especialidad</label>
                                        <input type="text" name="certificados[<?= $idx ?>][tema]" class="form-control form-control-sm" placeholder="Ej: Gestión Documental" value="<?= htmlspecialchars($c['tema'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Horas</label>
                                        <input type="number" name="certificados[<?= $idx ?>][horas]" class="form-control form-control-sm" placeholder="120" min="0" value="<?= htmlspecialchars($c['horas'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Fecha Inicio</label>
                                        <input type="date" name="certificados[<?= $idx ?>][inicio]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['inicio'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Fecha Término</label>
                                        <input type="date" name="certificados[<?= $idx ?>][termino]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['termino'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-12 mb-1">
                                        <label class="small font-weight-bold">Enlace al Certificado Digital (Opcional)</label>
                                        <input type="url" name="certificados[<?= $idx ?>][archivo]" class="form-control form-control-sm" placeholder="https://..." value="<?= htmlspecialchars($c['archivo'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="border rounded p-3 mb-3 bg-white shadow-sm item-certificado">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-light text-primary font-weight-bold">Certificado #<span class="num-item">1</span></span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Nombre del Curso / Certificación</label>
                                    <input type="text" name="certificados[0][nombre]" class="form-control form-control-sm" placeholder="Ej: Curso de Metodologías de Aprendizaje Activo">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Institución Emisora</label>
                                    <input type="text" name="certificados[0][institucion]" class="form-control form-control-sm" placeholder="Ej: IESP Huanta / MINEDU">
                                </div>
                                <div class="col-md-5 mb-2">
                                    <label class="small font-weight-bold">Tema o Especialidad</label>
                                    <input type="text" name="certificados[0][tema]" class="form-control form-control-sm" placeholder="Ej: Educación y TIC">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="small font-weight-bold">Horas</label>
                                    <input type="number" name="certificados[0][horas]" class="form-control form-control-sm" placeholder="60" min="0">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="small font-weight-bold">Fecha Inicio</label>
                                    <input type="date" name="certificados[0][inicio]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Fecha Término</label>
                                    <input type="date" name="certificados[0][termino]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-12 mb-1">
                                    <label class="small font-weight-bold">Enlace al Certificado Digital (Opcional)</label>
                                    <input type="url" name="certificados[0][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: HABILIDADES                         -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-hab" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">Habilidades Técnicas y Competencias</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-hab">
                        <i class="fas fa-plus mr-1"></i> Agregar Habilidad
                    </button>
                </div>

                <div id="contenedor-habilidades">
                    <?php if (!empty($hab)): ?>
                        <?php foreach ($hab as $idx => $h): ?>
                            <div class="row align-items-center mb-2 item-habilidad">
                                <div class="col-md-7">
                                    <input type="text" name="habilidades[<?= $idx ?>][titulo]" class="form-control form-control-sm" placeholder="Ej: Microsoft Excel Avanzado, Liderazgo, etc." value="<?= htmlspecialchars($h['titulo'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <select name="habilidades[<?= $idx ?>][nivel]" class="form-control form-control-sm">
                                        <?php foreach (['Básico', 'Intermedio', 'Avanzado', 'Experto'] as $niv): ?>
                                            <option value="<?= $niv ?>" <?= (($h['nivel'] ?? '') == $niv) ? 'selected' : '' ?>><?= $niv ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="row align-items-center mb-2 item-habilidad">
                            <div class="col-md-7">
                                <input type="text" name="habilidades[0][titulo]" class="form-control form-control-sm" placeholder="Ej: Trabajo en equipo y comunicación asertiva">
                            </div>
                            <div class="col-md-4">
                                <select name="habilidades[0][nivel]" class="form-control form-control-sm">
                                    <option value="Básico">Básico</option>
                                    <option value="Intermedio" selected>Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                    <option value="Experto">Experto</option>
                                </select>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 5: IDIOMAS                             -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-idi" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">Idiomas y Nivel de Dominio</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-idi">
                        <i class="fas fa-plus mr-1"></i> Agregar Idioma
                    </button>
                </div>

                <div id="contenedor-idiomas">
                    <?php if (!empty($idi)): ?>
                        <?php foreach ($idi as $idx => $i): ?>
                            <div class="row align-items-center mb-2 item-idioma">
                                <div class="col-md-4">
                                    <input type="text" name="idiomas[<?= $idx ?>][idioma]" class="form-control form-control-sm" placeholder="Ej: Quechua, Inglés" value="<?= htmlspecialchars($i['idioma'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="idiomas[<?= $idx ?>][nivel]" class="form-control form-control-sm">
                                        <?php foreach (['Básico', 'Intermedio', 'Avanzado', 'Nativo / Fluido'] as $niv): ?>
                                            <option value="<?= $niv ?>" <?= (($i['nivel'] ?? '') == $niv) ? 'selected' : '' ?>><?= $niv ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="url" name="idiomas[<?= $idx ?>][archivo]" class="form-control form-control-sm" placeholder="Enlace a certificado (opcional)" value="<?= htmlspecialchars($i['archivo'] ?? '') ?>">
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="row align-items-center mb-2 item-idioma">
                            <div class="col-md-4">
                                <input type="text" name="idiomas[0][idioma]" class="form-control form-control-sm" placeholder="Ej: Español" value="Español">
                            </div>
                            <div class="col-md-3">
                                <select name="idiomas[0][nivel]" class="form-control form-control-sm">
                                    <option value="Básico">Básico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                    <option value="Nativo / Fluido" selected>Nativo / Fluido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="url" name="idiomas[0][archivo]" class="form-control form-control-sm" placeholder="Enlace a certificado (opcional)">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div> <!-- /tab-content -->
    </div>
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT DINÁMICO PARA AGREGAR / ELIMINAR FILAS DE SECCIONES -->
<!-- ============================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let indexEdu = document.querySelectorAll('.item-educacion').length;
    let indexExp = document.querySelectorAll('.item-experiencia').length;
    let indexCert = document.querySelectorAll('.item-certificado').length;
    let indexHab = document.querySelectorAll('.item-habilidad').length;
    let indexIdi = document.querySelectorAll('.item-idioma').length;

    // Eliminar fila en cualquier sección
    document.addEventListener('click', function(e) {
        const btnRemove = e.target.closest('.btn-remove-item');
        if (btnRemove) {
            const item = btnRemove.closest('.item-educacion, .item-experiencia, .item-certificado, .item-habilidad, .item-idioma');
            if (item) {
                item.remove();
                renumerarItems();
            }
        }
    });

    function renumerarItems() {
        document.querySelectorAll('#contenedor-educacion .item-educacion').forEach((el, idx) => {
            const num = el.querySelector('.num-item');
            if (num) num.textContent = idx + 1;
        });
        document.querySelectorAll('#contenedor-experiencia .item-experiencia').forEach((el, idx) => {
            const num = el.querySelector('.num-item');
            if (num) num.textContent = idx + 1;
        });
        document.querySelectorAll('#contenedor-certificados .item-certificado').forEach((el, idx) => {
            const num = el.querySelector('.num-item');
            if (num) num.textContent = idx + 1;
        });
    }

    // Agregar Educación
    document.getElementById('btn-add-edu').addEventListener('click', function() {
        const container = document.getElementById('contenedor-educacion');
        const num = container.querySelectorAll('.item-educacion').length + 1;
        const html = `
            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-educacion">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge badge-light text-primary font-weight-bold">Estudio #<span class="num-item">${num}</span></span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold">Nivel Académico</label>
                        <select name="educacion[${indexEdu}][nivel_academico]" class="form-control form-control-sm">
                            <option value="Superior Técnico">Superior Técnico</option>
                            <option value="Universitario">Universitario</option>
                            <option value="Bachiller">Bachiller</option>
                            <option value="Titulado">Titulado</option>
                            <option value="Egresado" selected>Egresado</option>
                            <option value="Secundaria Completa">Secundaria Completa</option>
                            <option value="Diplomado">Diplomado</option>
                            <option value="Maestría">Maestría</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold">Centro de Estudios / Institución</label>
                        <input type="text" name="educacion[${indexEdu}][centro_estudios]" class="form-control form-control-sm" placeholder="Ej: IESP Huanta">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold">Carrera / Especialidad</label>
                        <input type="text" name="educacion[${indexEdu}][especialidad]" class="form-control form-control-sm" placeholder="Ej: Educación Inicial">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Fecha Inicio</label>
                        <input type="date" name="educacion[${indexEdu}][inicio]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Fecha Término</label>
                        <input type="date" name="educacion[${indexEdu}][termino]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Enlace a Constancia/Título (Opcional)</label>
                        <input type="url" name="educacion[${indexEdu}][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        indexEdu++;
    });

    // Agregar Experiencia
    document.getElementById('btn-add-exp').addEventListener('click', function() {
        const container = document.getElementById('contenedor-experiencia');
        const num = container.querySelectorAll('.item-experiencia').length + 1;
        const html = `
            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-experiencia">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge badge-light text-primary font-weight-bold">Experiencia #<span class="num-item">${num}</span></span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Empresa o Institución</label>
                        <input type="text" name="experiencia[${indexExp}][institucion]" class="form-control form-control-sm" placeholder="Ej: UGEL Huanta">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Cargo / Puesto Desempeñado</label>
                        <input type="text" name="experiencia[${indexExp}][cargo]" class="form-control form-control-sm" placeholder="Ej: Asistente">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Fecha Inicio</label>
                        <input type="date" name="experiencia[${indexExp}][inicio]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Fecha Término</label>
                        <input type="date" name="experiencia[${indexExp}][termino]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Tiempo en el Cargo</label>
                        <input type="text" name="experiencia[${indexExp}][tiempo_cargo]" class="form-control form-control-sm" placeholder="Ej: 1 año">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Enlace a Constancia (Opcional)</label>
                        <input type="url" name="experiencia[${indexExp}][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                    </div>
                    <div class="col-md-12 mb-1">
                        <label class="small font-weight-bold">Funciones y Logros Principales</label>
                        <textarea name="experiencia[${indexExp}][descripcion]" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Describa las principales tareas que desempeñó..."></textarea>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        indexExp++;
    });

    // Agregar Certificado
    document.getElementById('btn-add-cert').addEventListener('click', function() {
        const container = document.getElementById('contenedor-certificados');
        const num = container.querySelectorAll('.item-certificado').length + 1;
        const html = `
            <div class="border rounded p-3 mb-3 bg-white shadow-sm item-certificado">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge badge-light text-primary font-weight-bold">Certificado #<span class="num-item">${num}</span></span>
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-item" title="Eliminar fila">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Nombre del Curso / Certificación</label>
                        <input type="text" name="certificados[${indexCert}][nombre]" class="form-control form-control-sm" placeholder="Ej: Taller de Robótica Educativa">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small font-weight-bold">Institución Emisora</label>
                        <input type="text" name="certificados[${indexCert}][institucion]" class="form-control form-control-sm" placeholder="Ej: MINEDU">
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="small font-weight-bold">Tema o Especialidad</label>
                        <input type="text" name="certificados[${indexCert}][tema]" class="form-control form-control-sm" placeholder="Ej: Innovación">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">Horas</label>
                        <input type="number" name="certificados[${indexCert}][horas]" class="form-control form-control-sm" placeholder="40" min="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">Fecha Inicio</label>
                        <input type="date" name="certificados[${indexCert}][inicio]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Fecha Término</label>
                        <input type="date" name="certificados[${indexCert}][termino]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-12 mb-1">
                        <label class="small font-weight-bold">Enlace al Certificado Digital (Opcional)</label>
                        <input type="url" name="certificados[${indexCert}][archivo]" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        indexCert++;
    });

    // Agregar Habilidad
    document.getElementById('btn-add-hab').addEventListener('click', function() {
        const container = document.getElementById('contenedor-habilidades');
        const html = `
            <div class="row align-items-center mb-2 item-habilidad">
                <div class="col-md-7">
                    <input type="text" name="habilidades[${indexHab}][titulo]" class="form-control form-control-sm" placeholder="Nombre de la habilidad">
                </div>
                <div class="col-md-4">
                    <select name="habilidades[${indexHab}][nivel]" class="form-control form-control-sm">
                        <option value="Básico">Básico</option>
                        <option value="Intermedio" selected>Intermedio</option>
                        <option value="Avanzado">Avanzado</option>
                        <option value="Experto">Experto</option>
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        indexHab++;
    });

    // Agregar Idioma
    document.getElementById('btn-add-idi').addEventListener('click', function() {
        const container = document.getElementById('contenedor-idiomas');
        const html = `
            <div class="row align-items-center mb-2 item-idioma">
                <div class="col-md-4">
                    <input type="text" name="idiomas[${indexIdi}][idioma]" class="form-control form-control-sm" placeholder="Idioma">
                </div>
                <div class="col-md-3">
                    <select name="idiomas[${indexIdi}][nivel]" class="form-control form-control-sm">
                        <option value="Básico">Básico</option>
                        <option value="Intermedio" selected>Intermedio</option>
                        <option value="Avanzado">Avanzado</option>
                        <option value="Nativo / Fluido">Nativo / Fluido</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="url" name="idiomas[${indexIdi}][archivo]" class="form-control form-control-sm" placeholder="Enlace a certificado (opcional)">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-remove-item" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        indexIdi++;
    });
});
</script>
