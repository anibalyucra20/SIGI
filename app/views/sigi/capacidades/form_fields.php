<div class="row">
    <div class="col-md-6 mb-2">
        <label class="form-label">Programa de Estudio *</label>
        <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($programas as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (!empty($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Plan de Estudio *</label>
        <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($planes)): ?>
                <?php foreach ($planes as $pl): ?>
                    <option value="<?= $pl['id'] ?>" <?= (!empty($id_plan_selected) && $id_plan_selected == $pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Módulo Formativo *</label>
        <select name="id_modulo_formativo" id="id_modulo_formativo" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($modulos)): ?>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= (!empty($id_modulo_selected) && $id_modulo_selected == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Semestre *</label>
        <select name="id_semestre" id="id_semestre" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($semestres)): ?>
                <?php foreach ($semestres as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (!empty($id_semestre_selected) && $id_semestre_selected == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Unidad Didáctica *</label>
        <select name="id_unidad_didactica" id="id_unidad_didactica" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($unidades)): ?>
                <?php foreach ($unidades as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= (!empty($cap['id_unidad_didactica']) && $cap['id_unidad_didactica'] == $u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Competencia *</label>
        <select name="id_competencia" id="id_competencia" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($competencias)): ?>
                <?php foreach ($competencias as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (!empty($cap['id_competencia']) && $cap['id_competencia'] == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['codigo'] . ' - ' . mb_substr($c['descripcion'], 0, 60)) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
</div>
<div class="mb-2">
    <label class="form-label">Código *</label>
    <input type="text" name="codigo" class="form-control" maxlength="20" required value="<?= htmlspecialchars($cap['codigo'] ?? '') ?>">
</div>
<div class="mb-2">
    <label class="form-label">Descripción *</label>
    <textarea name="descripcion" class="form-control" maxlength="3000" required rows="3"><?= htmlspecialchars($cap['descripcion'] ?? '') ?></textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filtros dependientes para selects
    $('#id_programa_estudios').on('change', function () {
        let idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
        $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
        $('#id_semestre').html('<option value="">Seleccione...</option>');
        $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
        $('#id_competencia').html('<option value="">Seleccione...</option>');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function (planes) {
                planes.forEach(function (pl) {
                    $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                });
            });
        }
    });
    $('#id_plan_estudio').on('change', function () {
        let idPlan = $(this).val();
        $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
        $('#id_semestre').html('<option value="">Seleccione...</option>');
        $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
        $('#id_competencia').html('<option value="">Seleccione...</option>');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function (modulos) {
                modulos.forEach(function (m) {
                    $('#id_modulo_formativo').append('<option value="' + m.id + '">' + m.descripcion + '</option>');
                });
            });
        }
    });
    $('#id_modulo_formativo').on('change', function () {
        let idModulo = $(this).val();
        $('#id_semestre').html('<option value="">Seleccione...</option>');
        $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
        $('#id_competencia').html('<option value="">Seleccione...</option>');
        if (idModulo) {
            $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function (semestres) {
                semestres.forEach(function (s) {
                    $('#id_semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                });
            });
            $.getJSON('<?= BASE_URL ?>/sigi/competencias/porModulo/' + idModulo, function (comps) {
                comps.forEach(function (c) {
                    $('#id_competencia').append('<option value="' + c.id + '">' + c.codigo + ' - ' + c.descripcion.substring(0, 60) + '</option>');
                });
            });
        }
    });
    $('#id_semestre').on('change', function () {
        let idSemestre = $(this).val();
        $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
        if (idSemestre) {
            $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + idSemestre, function (uds) {
                uds.forEach(function (u) {
                    $('#id_unidad_didactica').append('<option value="' + u.id + '">' + u.nombre + '</option>');
                });
            });
        }
    });
});
</script>
