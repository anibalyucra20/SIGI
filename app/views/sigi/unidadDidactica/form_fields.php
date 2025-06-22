<div class="mb-3">
    <label class="form-label">Programa de Estudio *</label>
    <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php foreach ($programas as $p): ?>
            <option value="<?= $p['id'] ?>"
                <?= (!empty($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Plan de Estudio *</label>
    <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php if (!empty($planes)): ?>
            <?php foreach ($planes as $pl): ?>
                <option value="<?= $pl['id'] ?>"
                    <?= (!empty($id_plan_selected) && $id_plan_selected == $pl['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pl['nombre']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Módulo Formativo *</label>
    <select name="id_modulo_formativo" id="id_modulo_formativo" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php if (!empty($modulos)): ?>
            <?php foreach ($modulos as $m): ?>
                <option value="<?= $m['id'] ?>"
                    <?= (!empty($id_modulo_selected) && $id_modulo_selected == $m['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Semestre *</label>
    <select name="id_semestre" id="id_semestre" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php if (!empty($semestres)): ?>
            <?php foreach ($semestres as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= (!empty($ud['id_semestre']) && $ud['id_semestre'] == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Nombre *</label>
    <input type="text" name="nombre" class="form-control" maxlength="200" required
        value="<?= htmlspecialchars($ud['nombre'] ?? '') ?>">
</div>
<div class="mb-3 row">
    <div class="col-md-2">
        <label class="form-label">Créditos Teóricos*</label>
        <input type="number" name="creditos_teorico" class="form-control" min="1" max="20" required
            value="<?= htmlspecialchars($ud['creditos_teorico'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Créditos Prácticos*</label>
        <input type="number" name="creditos_practico" class="form-control" min="1" max="300" required
            value="<?= htmlspecialchars($ud['creditos_practico'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Tipo *</label>
        <select name="tipo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="ESPECIALIDAD" <?= (isset($ud['tipo']) && $ud['tipo'] == 'ESPECIALIDAD') ? 'selected' : '' ?>>ESPECIALIDAD</option>
            <option value="EMPLEABILIDAD" <?= (isset($ud['tipo']) && $ud['tipo'] == 'EMPLEABILIDAD') ? 'selected' : '' ?>>EMPLEABILIDAD</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Orden *</label>
        <input type="number" name="orden" class="form-control" min="1" max="20" required
            value="<?= htmlspecialchars($ud['orden'] ?? '') ?>">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#id_programa_estudios').on('change', function () {
        let idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
        $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
        $('#id_semestre').html('<option value="">Seleccione...</option>');
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
        if (idModulo) {
            $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function (semestres) {
                semestres.forEach(function (s) {
                    $('#id_semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                });
            });
        }
    });

    // Al editar: cargar dependientes y setear valores seleccionados
    <?php if (!empty($id_programa_selected)): ?>
        $('#id_programa_estudios').trigger('change');
        <?php if (!empty($id_plan_selected)): ?>
            setTimeout(function(){
                $('#id_plan_estudio').val('<?= $id_plan_selected ?>').trigger('change');
            }, 200);
        <?php endif; ?>
        <?php if (!empty($id_modulo_selected)): ?>
            setTimeout(function(){
                $('#id_modulo_formativo').val('<?= $id_modulo_selected ?>').trigger('change');
            }, 400);
        <?php endif; ?>
        <?php if (!empty($ud['id_semestre'])): ?>
            setTimeout(function(){
                $('#id_semestre').val('<?= $ud['id_semestre'] ?>');
            }, 600);
        <?php endif; ?>
    <?php endif; ?>
});
</script>
