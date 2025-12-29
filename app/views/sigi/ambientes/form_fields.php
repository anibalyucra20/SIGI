<div class="row">
    <div class="col-md-4 mb-2">
        <label class="form-label">Tipo de Ambiente *</label>
        <select name="tipo_ambiente" id="tipo_ambiente" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php if (!empty($tipos_ambientes)): ?>
                <?php foreach ($tipos_ambientes as $t): ?>
                    <option value="<?= $t ?>" <?= (!empty($ambiente['tipo_ambiente']) && $ambiente['tipo_ambiente'] == $t) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="col-md-4 mb-2">
        <label class="form-label">nro *</label>
        <input type="text" name="nro" class="form-control" maxlength="20" required value="<?= htmlspecialchars($ambiente['nro'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">aforo *</label>
        <input type="number" name="aforo" class="form-control" maxlength="20" required value="<?= htmlspecialchars($ambiente['aforo'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Piso *</label>
        <select name="piso" id="piso" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php for ($i = 1; $i <= 10; $i++) {
            ?>
                <option value="PISO <?= $i ?>" <?= (!empty($ambiente['piso']) && $ambiente['piso'] == 'PISO ' . $i) ? 'selected' : '' ?>>
                    PISO <?= $i ?>
                </option>
            <?php
            } ?>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">ubicacion *</label>
        <input type="text" name="ubicacion" class="form-control" maxlength="20" value="<?= htmlspecialchars($ambiente['ubicacion'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">observacion *</label>
        <input type="text" name="observacion" class="form-control" maxlength="20" value="<?= htmlspecialchars($ambiente['observacion'] ?? '') ?>">
    </div>
    <?php
    if ($isEdit) {
    ?>
        <div class="col-md-4 mb-2">
            <label class="form-label">estado *</label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="1" <?= ($ambiente['estado'] == 1) ? 'selected' : '' ?>>
                    ACTIVO
                </option>
                <option value="0" <?= ($ambiente['estado'] == 0) ? 'selected' : '' ?>>
                    INACTIVO
                </option>

            </select>
        </div>
    <?php
    }
    ?>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtros dependientes para selects
        $('#id_programa_estudios').on('change', function() {
            let idPrograma = $(this).val();
            $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
            $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
            $('#id_semestre').html('<option value="">Seleccione...</option>');
            $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
            $('#id_competencia').html('<option value="">Seleccione...</option>');
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
            $('#id_competencia').html('<option value="">Seleccione...</option>');
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
            $('#id_competencia').html('<option value="">Seleccione...</option>');
            if (idModulo) {
                $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idModulo, function(semestres) {
                    semestres.forEach(function(s) {
                        $('#id_semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                    });
                });
                $.getJSON('<?= BASE_URL ?>/sigi/competencias/porModulo/' + idModulo, function(comps) {
                    comps.forEach(function(c) {
                        $('#id_competencia').append('<option value="' + c.id + '">' + c.codigo + ' - ' + c.descripcion.substring(0, 60) + '</option>');
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