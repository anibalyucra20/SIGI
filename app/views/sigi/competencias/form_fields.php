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
    <label class="form-label">Módulos Formativos (puede seleccionar varios)</label>
    <div id="modulos-list">
        <?php if (!empty($modulosAll)): ?>
            <?php foreach ($modulosAll as $m): ?>
                <div class="form-check form-check-inline mb-1">
                    <input class="form-check-input" type="checkbox" name="modulos[]"
                        value="<?= $m['id'] ?>"
                        <?= (isset($modulosSeleccionados) && in_array($m['id'], $modulosSeleccionados)) ? 'checked' : '' ?>>
                    <label class="form-check-label"><?= htmlspecialchars($m['descripcion']) ?></label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<div class="mb-3 row">
    <div class="col-md-2">
        <label class="form-label">Tipo *</label>
        <select name="tipo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="ESPECÍFICA" <?= (isset($comp['tipo']) && $comp['tipo'] == 'ESPECÍFICA') ? 'selected' : '' ?>>ESPECÍFICA</option>
            <option value="EMPLEABILIDAD" <?= (isset($comp['tipo']) && $comp['tipo'] == 'EMPLEABILIDAD') ? 'selected' : '' ?>>EMPLEABILIDAD</option>
            <option value="TRANSVERSAL" <?= (isset($comp['tipo']) && $comp['tipo'] == 'TRANSVERSAL') ? 'selected' : '' ?>>TRANSVERSAL</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Código *</label>
        <input type="text" name="codigo" class="form-control" maxlength="10" required
            value="<?= htmlspecialchars($comp['codigo'] ?? '') ?>">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Descripción *</label>
    <textarea name="descripcion" class="form-control" maxlength="3000" required rows="3"><?= htmlspecialchars($comp['descripcion'] ?? '') ?></textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Al cambiar el programa, recarga los planes
    $('#id_programa_estudios').on('change', function () {
        let idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
        $('#modulos-list').html('');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function (planes) {
                planes.forEach(function (pl) {
                    $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                });
            });
        }
    });

    // Al cambiar el plan, recarga los módulos (muestra checkboxes)
    $('#id_plan_estudio').on('change', function () {
        let idPlan = $(this).val();
        $('#modulos-list').html('');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, function (modulos) {
                modulos.forEach(function (m) {
                    $('#modulos-list').append(
                        '<div class="form-check form-check-inline mb-1">' +
                        '<input class="form-check-input" type="checkbox" name="modulos[]" value="' + m.id + '"> ' +
                        '<label class="form-check-label">' + m.descripcion + '</label>' +
                        '</div><br>'
                    );
                });
            });
        }
    });

    // Al editar, carga dependientes y selecciona los módulos correctos
    <?php if (!empty($id_programa_selected)): ?>
        $('#id_programa_estudios').trigger('change');
        <?php if (!empty($id_plan_selected)): ?>
            setTimeout(function(){
                $('#id_plan_estudio').val('<?= $id_plan_selected ?>').trigger('change');
                <?php if (!empty($modulosSeleccionados)): ?>
                    setTimeout(function(){
                        var seleccionados = <?= json_encode($modulosSeleccionados) ?>;
                        $('#modulos-list input[type=checkbox]').each(function(){
                            if (seleccionados.includes(parseInt($(this).val()))) {
                                $(this).prop('checked', true);
                            }
                        });
                    }, 400);
                <?php endif; ?>
            }, 200);
        <?php endif; ?>
    <?php endif; ?>
});
</script>
