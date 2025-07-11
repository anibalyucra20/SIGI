<div class="row">
    <div class="col-md-3 mb-2">
        <label class="form-label">DNI *</label>
        <input type="text" name="dni" class="form-control" maxlength="20" required value="<?= htmlspecialchars($dni ?? '') ?>">
    </div>
    <div class="col-md-5 mb-2">
        <label class="form-label">Apellidos y Nombres *</label>
        <input type="text" name="apellidos_nombres" class="form-control" maxlength="125" required value="<?= htmlspecialchars($apellidos_nombres ?? '') ?>">
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Género *</label>
        <select name="genero" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="M" <?= (isset($genero) && $genero == 'M') ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= (isset($genero) && $genero == 'F') ? 'selected' : '' ?>>Femenino</option>
        </select>
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Fecha Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars($fecha_nacimiento ?? '') ?>">
    </div>
    <div class="col-md-8 mb-2">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control" maxlength="200" value="<?= htmlspecialchars($direccion ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="100" value="<?= htmlspecialchars($correo ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" maxlength="15" value="<?= htmlspecialchars($telefono ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Discapacidad</label>
        <select name="discapacidad" class="form-control">
            <option value="NO" <?= (isset($discapacidad) && $discapacidad == 'NO') ? 'selected' : '' ?>>No</option>
            <option value="SI" <?= (isset($discapacidad) && $discapacidad == 'SI') ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>
    <div class="col-md-4 mb-2">
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
    <div class="col-md-4 mb-2">
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
    <div class="col-md-4 mb-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control">
            <option value="1" <?= (!isset($estado) || $estado == 1) ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= (isset($estado) && $estado == 0) ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#id_programa_estudios').on('change', function () {
        let idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Seleccione...</option>');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function (planes) {
                planes.forEach(function (pl) {
                    $('#id_plan_estudio').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                });
            });
        }
    });
});
</script>
