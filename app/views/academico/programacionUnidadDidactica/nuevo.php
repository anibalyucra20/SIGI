<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <?php if ($periodo_vigente): ?>
        <div class="card p-2">
            <h4>Nueva Programación de Unidad Didáctica</h4>
            <form action="<?= BASE_URL ?>/academico/programacionUnidadDidactica/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label>Programa de Estudios *</label>
                        <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($programas as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Plan de Estudios *</label>
                        <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Módulo Profesional *</label>
                        <select name="id_modulo_formativo" id="id_modulo_formativo" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Semestre *</label>
                        <select name="id_semestre" id="id_semestre" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label>Unidad Didáctica *</label>
                        <select name="id_unidad_didactica" id="id_unidad_didactica" class="form-control" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label>Docente *</label>
                        <select name="id_docente" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($docentes as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= (isset($id_docente_selected) && $id_docente_selected == $d['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['apellidos_nombres']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Turno *</label>
                        <select name="turno" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="M">Mañana</option>
                            <option value="T">Tarde</option>
                            <option value="N">Noche</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Sección *</label>
                        <select name="seccion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success px-4">Registrar</button>
                    <a href="<?= BASE_URL ?>/academico/programacionUnidadDidactica" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Cascada de selects dependientes
                $('#id_programa_estudios').on('change', function() {
                    let idPrograma = $(this).val();
                    $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
                    $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
                    $('#id_semestre').html('<option value="">Seleccione...</option>');
                    $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
                    if (idPrograma) {
                        $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                            planes.forEach(function(pl) {
                                $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                            });
                        });
                    }
                });
                $('#id_plan_estudio').on('change', function() {
                    let idPlan = $(this).val();
                    $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
                    $('#id_semestre').html('<option value="">Seleccione...</option>');
                    $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
                    if (idPlan) {
                        $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function(modulos) {
                            modulos.forEach(function(m) {
                                $('#id_modulo_formativo').append('<option value="' + m.id + '">' + m.descripcion + '</option>');
                            });
                        });
                    }
                });
                $('#id_modulo_formativo').on('change', function() {
                    let idModulo = $(this).val();
                    $('#id_semestre').html('<option value="">Seleccione...</option>');
                    $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
                    if (idModulo) {
                        $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function(semestres) {
                            semestres.forEach(function(s) {
                                $('#id_semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                            });
                        });
                    }
                });
                $('#id_semestre').on('change', function() {
                    let idSemestre = $(this).val();
                    $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
                    if (idSemestre) {
                        $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + idSemestre, function(uds) {
                            uds.forEach(function(u) {
                                $('#id_unidad_didactica').append('<option value="' + u.id + '">' + u.nombre + '</option>');
                            });
                        });
                    }
                });
            });
        </script>
    <?php else: ?>
        <p>El periodo Académico ah Finalizado</p>
    <?php endif; ?>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>