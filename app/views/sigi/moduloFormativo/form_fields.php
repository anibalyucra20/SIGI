<div class="mb-3">
    <label class="form-label">Programa de Estudio *</label>
    <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
        <option value="">Seleccione...</option>
        <?php foreach ($programas as $p): ?>
            <option value="<?= $p['id'] ?>"
                <?= (isset($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
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
                    <?= (isset($modulo['id_plan_estudio']) && $modulo['id_plan_estudio'] == $pl['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pl['nombre']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Descripción *</label>
    <input type="text" name="descripcion" class="form-control" maxlength="1000" required
        value="<?= htmlspecialchars($modulo['descripcion'] ?? '') ?>">
</div>
<div class="mb-3">
    <label class="form-label">Nro. Módulo *</label>
    <input type="number" name="nro_modulo" class="form-control" min="1" max="99" required
        value="<?= htmlspecialchars($modulo['nro_modulo'] ?? '') ?>">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#id_programa_estudios').on('change', function() {
            var idPrograma = $(this).val();
            $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
            if (idPrograma) {
                $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                    planes.forEach(function(pl) {
                        $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                    });
                });
            }
        });
    });
</script>