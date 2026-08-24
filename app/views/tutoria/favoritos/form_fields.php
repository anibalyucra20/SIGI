<div class="row">
    <div class="col-md-6 mb-2">
        <label class="form-label">Estudiante</label>
        <select name="estudiante" id="estudiante" class="form-control">
            <option>Seleccione</option>
            <?php
            foreach ($estudiantes as $estudiante){
                $selected = ($isEdit && $data['id_estudiante'] == $estudiante['id']) ? 'selected' : '';
                ?>
                <option value="<?= $estudiante['id'] ?>" <?= $selected ?>><?= htmlspecialchars($estudiante['apellidos_nombres']) ?></option>
                <?php
            }
            ?>
        </select>
    </div>
</div>