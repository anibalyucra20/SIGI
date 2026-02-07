<div class="row">
    <div class="col-md-3 mb-2">
        <label class="form-label">Tipo Documento</label>
        <select name="tipo_doc" id="tipo_doc" class="form-control" required>
            <option value="DNI" <?= (isset($estudiante['tipo_doc']) && $estudiante['tipo_doc'] == 'DNI') ? 'selected' : '' ?>>DNI</option>
            <option value="CE" <?= (isset($estudiante['tipo_doc']) && $estudiante['tipo_doc'] == 'CE') ? 'selected' : '' ?>>CARNET DE EXTRANJERIA</option>
            <option value="PASAPORTE" <?= (isset($estudiante['tipo_doc']) && $estudiante['tipo_doc'] == 'PASAPORTE') ? 'selected' : '' ?>>PASAPORTE</option>
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <label class="form-label">DNI *</label>
        <input type="text" name="dni" class="form-control" maxlength="20" required value="<?= htmlspecialchars($dni ?? $estudiante['dni'] ?? '') ?>">
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
            <option value="M" <?= (($estudiante['genero'] ?? '') == 'M') ? 'selected' : '' ?>>Masculino</option>
            <option value="F" <?= (($estudiante['genero'] ?? '') == 'F') ? 'selected' : '' ?>>Femenino</option>
        </select>
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Fecha Nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" value="<?= $estudiante['fecha_nacimiento'] ?? '' ?>">
    </div>
    <div class="col-md-8 mb-2">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control" maxlength="200" value="<?= $estudiante['direccion'] ?? '' ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" maxlength="100" value="<?= $estudiante['correo'] ?? '' ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" maxlength="15" value="<?= $estudiante['telefono'] ?? '' ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Discapacidad</label>
        <select name="discapacidad" class="form-control">
            <option value="NO" <?= (($estudiante['discapacidad'] ?? '') == 'NO') ? 'selected' : '' ?>>No</option>
            <option value="SI" <?= (($estudiante['discapacidad'] ?? '') == 'SI') ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Sede *</label>
        <select name="id_sede" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($sedes as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (($id_sede_selected ?? $estudiante['id_sede'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Programa de Estudio *</label>
        <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($programas as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (($id_programa_selected ?? $estudiante['id_programa_estudios'] ?? '') == $p['id']) ? 'selected' : '' ?>>
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
                    <option value="<?= $pl['id'] ?>" <?= (($id_plan_selected ?? $estudiante['id_plan_estudio'] ?? '') == $pl['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Periodo *</label>
        <select name="id_periodo" class="form-control" required>
            <option value="">Seleccione...</option>
            <?php foreach ($periodos as $per): ?>
                <option value="<?= $per['id'] ?>" <?= (($id_periodo_selected ?? $estudiante['id_periodo'] ?? '') == $per['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($per['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control">
            <option value="1" <?= (!isset($estado) || $estado == 1 || ($estudiante['estado'] ?? 1) == 1) ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= (isset($estado) && $estado == 0 || ($estudiante['estado'] ?? 1) == 0) ? 'selected' : '' ?>>Inactivo</option>
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