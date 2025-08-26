<?php require __DIR__ . '/../../layouts/header.php'; ?>
<form id="formUpload" class="card p-3" enctype="multipart/form-data" autocomplete="off">
    <div id="uploadResult" class="mt-3"></div>
    <div class="row">
        <div class="col-md-2 mb-2">
            <label class="form-label">Programa de Estudio *</label>
            <select name="id_programa_estudio" id="id_programa_estudio" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($programas as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (!empty($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Plan de Estudio *</label>
            <select name="id_plan" id="id_plan" class="form-control" required>
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
        <div class="col-md-2 mb-2">
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
        <div class="col-md-2 mb-2">
            <label class="form-label">Periodo Académico *</label>
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
        <div class="col-md-2 mb-2">
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
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Título *</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="form-group col-md-3">
            <label>Tipo Libro *</label>
            <input type="text" name="tipo_libro" class="form-control" placeholder="p.e. PDF" required>
        </div>
        <div class="form-group col-md-3">
            <label>Autor</label>
            <input type="text" name="autor" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Archivo (PDF) *</label>
            <input type="file" name="libro" class="form-control-file" accept="application/pdf" required>
        </div>
        <div class="form-group col-md-6">
            <label>Portada (JPG/PNG/WEBP)</label>
            <input type="file" name="portada" class="form-control-file" accept="image/*">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-3">
            <label>Editorial</label>
            <input type="text" name="editorial" class="form-control">
        </div>
        <div class="form-group col-md-2">
            <label>Edición</label>
            <input type="text" name="edicion" class="form-control">
        </div>
        <div class="form-group col-md-2">
            <label>Tomo</label>
            <input type="text" name="tomo" class="form-control">
        </div>
        <div class="form-group col-md-3">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control">
        </div>
        <div class="form-group col-md-2">
            <label>Año</label>
            <input type="number" name="anio" class="form-control" min="0" max="2100">
        </div>
    </div>

    <div class="form-group">
        <label>Temas relacionados</label>
        <input type="text" name="temas_relacionados" class="form-control">
    </div>
    <div class="form-group">
        <label>Tags</label>
        <input type="text" name="tags" class="form-control" placeholder="separados por coma">
    </div>

    <div class="text-right">
        <a href="<?= BASE_URL . '/biblioteca/libros' ?>" class="btn btn-danger">Cancelar</a>
        <button class="btn btn-success">Guardar</button>
    </div>
    <input type="hidden" id="apiKey" value="<?= htmlspecialchars($sistema['token_sistema'] ?? '') ?>">
    <input type="hidden" id="apiBase" value="<?= rtrim(API_BASE_URL, '/') ?>/api">
</form>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
<script>
    const $apiKey = $('#apiKey');
    const $apiBase = $('#apiBase');
    let dtAdopt = null;

    // Inyecta X-Api-Key a llamadas al Maestro
    $(document).ajaxSend(function(_e, xhr, opts) {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        if (opts.url && opts.url.indexOf(base) === 0) {
            const k = ($apiKey.val() || '').trim();
            if (k) xhr.setRequestHeader('X-Api-Key', k);
        }
    });

    function cryptoRandom() {
        try {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
        } catch (e) {
            return 'idem-' + Date.now() + '-' + Math.random().toString(16).slice(2);
        }
    }
    // ====== Subir Libro ======
    $('#formUpload').on('submit', function(e) {
        e.preventDefault();
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const fd = new FormData(this);
        $('#uploadResult').html('<div class="text-muted">Subiendo...</div>');
        $.ajax({
            url: `${base}/library/upload`,
            method: 'POST',
            processData: false,
            contentType: false,
            data: fd,
            success: function(r) {
                $('#uploadResult').html(
                    `<div class="alert alert-success">OK (#${r.id}). ` +
                    (r.portada_url ? `<a href="${r.portada_url}" target="_blank">Portada</a> · ` : '') +
                    `<a href="${r.archivo_url}" target="_blank">Archivo</a></div>`
                );
                $('#formUpload')[0].reset();
            },
            error: function(xhr) {
                const msg = xhr?.responseJSON?.error?.message || xhr.statusText || 'Error';
                $('#uploadResult').html(`<div class="alert alert-danger">${msg}</div>`);
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtros dependientes para selects
        $('#id_programa_estudio').on('change', function() {
            let idPrograma = $(this).val();
            $('#id_plan').html('<option value="">Seleccione...</option>');
            $('#id_modulo_formativo').html('<option value="">Seleccione...</option>');
            $('#id_semestre').html('<option value="">Seleccione...</option>');
            $('#id_unidad_didactica').html('<option value="">Seleccione...</option>');
            if (idPrograma) {
                $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                    planes.forEach(function(pl) {
                        $('#id_plan').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                    });
                });
            }
        });
        $('#id_plan').on('change', function() {
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