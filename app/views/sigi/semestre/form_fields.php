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
                    <?= (!empty($semestre['id_modulo_formativo']) && $semestre['id_modulo_formativo'] == $m['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['descripcion']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Descripción *</label>
    <input type="text" name="descripcion" class="form-control" maxlength="1000" required
        value="<?= htmlspecialchars($semestre['descripcion'] ?? '') ?>">
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#id_programa_estudios').on('change', function () {
        let idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
        $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
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
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function (modulos) {
                modulos.forEach(function (m) {
                    $('#id_modulo_formativo').append('<option value="' + m.id + '">' + m.descripcion + '</option>');
                });
            });
        }
    });

    // Al editar: cargar dependientes
    <?php if (!empty($id_programa_selected)): ?>
        $('#id_programa_estudios').trigger('change');
        <?php if (!empty($id_plan_selected)): ?>
            setTimeout(function(){
                $('#id_plan_estudio').val('<?= $id_plan_selected ?>').trigger('change');
            }, 200);
        <?php endif; ?>
        <?php if (!empty($semestre['id_modulo_formativo'])): ?>
            setTimeout(function(){
                $('#id_modulo_formativo').val('<?= $semestre['id_modulo_formativo'] ?>');
            }, 400);
        <?php endif; ?>
    <?php endif; ?>
});
</script>
