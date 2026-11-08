<div class="row">
    <div class="col-md-8 mb-3">
        <label for="docente" class="form-label font-weight-bold">Docente Tutor <span class="text-danger">*</span></label>
        <select name="docente" id="docente" class="form-control" required>
            <option value="">-- Seleccione un docente --</option>
            <?php
            foreach ($docentes as $docente) {
                $selected = ($isEdit && $data['id_docente'] == $docente['id']) ? 'selected' : '';
                $nombres = explode('_', trim($docente['apellidos_nombres']));
                $nombreFormato = trim(($nombres[0] ?? '') . ' ' . ($nombres[1] ?? '') . ', ' . ($nombres[2] ?? ''));
            ?>
                <option value="<?= $docente['id'] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($nombreFormato) ?> (DNI: <?= htmlspecialchars($docente['dni'] ?? '-') ?>)
                </option>
            <?php
            }
            ?>
        </select>
        <small class="form-text text-muted">Seleccione el docente que asumirá el rol de tutor en la sede y periodo actual.</small>
    </div>

    <?php if ($isEdit): ?>
    <div class="col-md-12 mb-3">
        <label for="conclusiones" class="form-label font-weight-bold">Conclusiones Finales del Periodo</label>
        <textarea name="conclusiones" id="conclusiones" class="form-control" rows="3" placeholder="Informe o balance final de tutoría..."><?= htmlspecialchars($data['conclusiones'] ?? '') ?></textarea>
    </div>
    <?php endif; ?>
</div>