<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Registrar Nueva Sesión Individual</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesIndividuales" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Sesiones
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (empty($tutorandos)): ?>
    <div class="alert alert-warning shadow-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i> No se encontraron estudiantes asignados a esta tutoría en el periodo activo. Debe asignar tutorandos previamente en la sección de <a href="<?= BASE_URL ?>/tutoria/programacion" class="alert-link">Programación de Tutores</a>.
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-3 border-left-success">
    <div class="card-body bg-light py-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <label for="select_tutorando_sesion" class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-user-graduate text-success mr-1"></i> Estudiante Tutorando Asignado: <span class="text-danger">*</span>
                </label>
                <select id="select_tutorando_sesion" class="form-control form-control-sm" <?= empty($tutorandos) ? 'disabled' : '' ?>>
                    <?php if (empty($tutorandos)): ?>
                        <option value="">-- Sin estudiantes asignados --</option>
                    <?php else: ?>
                        <?php foreach ($tutorandos as $est): ?>
                            <option value="<?= $est['id_tutoria_estudiante'] ?>"
                                data-dni="<?= htmlspecialchars($est['dni']) ?>"
                                data-programa="<?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?>"
                                data-nombre="<?= htmlspecialchars($est['nombre_completo']) ?>"
                                <?= ($tutorando && (int)$tutorando['id'] === (int)$est['id_tutoria_estudiante']) ? 'selected' : '' ?>>
                                [<?= htmlspecialchars($est['dni']) ?>] <?= htmlspecialchars($est['nombre_completo']) ?> - <?= htmlspecialchars($est['programa_nombre'] ?: 'General') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="text-muted mt-1 d-block" id="info_estudiante_sel">
                    <strong>DNI:</strong> <span id="lbl_dni"><?= htmlspecialchars($tutorando['dni'] ?? '-') ?></span> |
                    <strong>Programa:</strong> <span id="lbl_programa"><?= htmlspecialchars($tutorando['programa_nombre'] ?? 'General') ?></span>
                </small>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <span class="badge badge-primary px-3 py-2 font-size-13 shadow-sm" id="badge_sesion_nro">
                    <i class="fas fa-calendar-check mr-1"></i> Sesión N° <span id="lbl_siguiente_nro"><?= htmlspecialchars($siguienteNro ?? 1) ?></span>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/sesiones/individualGuardar" method="POST" autocomplete="off" id="form_nueva_sesion">
            <!-- ID del registro en tutoria_estudiantes -->
            <input type="hidden" name="id_tutoria_estudiante" id="hidden_id_tutoria_estudiante" value="<?= htmlspecialchars($tutorando['id'] ?? '') ?>">

            <?php include __DIR__ . '/form_fields.php'; ?>

            <div class="mt-4 text-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesIndividuales" class="btn btn-secondary btn-sm mr-2">Cancelar</a>
                <button type="submit" class="btn btn-success btn-sm px-4" <?= empty($tutorandos) ? 'disabled' : '' ?>>
                    <i class="fas fa-save mr-1"></i> Guardar Sesión Individual
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const $selectEst = $('#select_tutorando_sesion');
    const $hiddenId  = $('#hidden_id_tutoria_estudiante');

    $selectEst.on('change', function() {
        const idTe = $(this).val();
        if (!idTe) return;

        $hiddenId.val(idTe);

        const $opt = $(this).find('option:selected');
        $('#lbl_dni').text($opt.data('dni') || '-');
        $('#lbl_programa').text($opt.data('programa') || 'General');

        // Consultar el siguiente número de sesión correlativo del estudiante seleccionado
        $.ajax({
            url: '<?= BASE_URL ?>/tutoria/sesiones/siguienteNumeroAjax/' + idTe,
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (resp && resp.siguienteNro) {
                    $('#lbl_siguiente_nro').text(resp.siguienteNro);
                    $('#nro').val(resp.siguienteNro);
                }
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
