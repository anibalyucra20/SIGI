<div class="row">
    <div class="col-md-3 mb-2">
        <label class="form-label">DNI *</label>
        <input type="text" name="dni" class="form-control" maxlength="20" required value="<?= htmlspecialchars($estudiante['dni'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Apellido Paterno</label>
        <input type="text" name="ApellidoPaterno" class="form-control" maxlength="120" value="<?= $estudiante['ApellidoPaterno'] ?? '' ?>" required>
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Apellido Materno</label>
        <input type="text" name="ApellidoMaterno" class="form-control" maxlength="120" value="<?= $estudiante['ApellidoMaterno'] ?? '' ?>" required>
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">Nombres</label>
        <input type="text" name="Nombres" class="form-control" maxlength="120" value="<?= $estudiante['Nombres'] ?? '' ?>" required>
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Género *</label>
        <select name="genero" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="M" <?= (isset($estudiante['genero']) && $estudiante['genero'] == 'M') ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= (isset($estudiante['genero']) && $estudiante['genero'] == 'F') ? 'selected' : '' ?>>Femenino</option>
        </select>
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Fecha Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars($estudiante['fecha_nacimiento'] ?? '') ?>">
    </div>
    <div class="col-md-8 mb-2">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control" maxlength="200" value="<?= htmlspecialchars($estudiante['direccion'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="100" value="<?= htmlspecialchars($estudiante['correo'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" maxlength="15" value="<?= htmlspecialchars($estudiante['telefono'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Discapacidad</label>
        <select name="discapacidad" class="form-control">
            <option value="NO" <?= (isset($estudiante['discapacidad']) && $estudiante['discapacidad'] == 'NO') ? 'selected' : '' ?>>No</option>
            <option value="SI" <?= (isset($estudiante['discapacidad']) && $estudiante['discapacidad'] == 'SI') ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Programa de Estudio *</label>
        <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($programas as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (!empty($estudiante['id_programa_estudios']) && $estudiante['id_programa_estudios'] == $p['id']) ? 'selected' : '' ?>>
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
                    <option value="<?= $pl['id'] ?>" <?= (!empty($estudiante['id_plan_estudio']) && $estudiante['id_plan_estudio'] == $pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control">
            <option value="1" <?= (!isset($estudiante['estado']) || $estudiante['estado'] == 1) ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= (isset($estudiante['estado']) && $estudiante['estado'] == 0) ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#id_programa_estudios').on('change', function() {
            let idPrograma = $(this).val();
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