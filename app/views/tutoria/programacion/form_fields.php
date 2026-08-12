<div class="row">
    <div class="col-md-6 mb-2">
        <label class="form-label">Docente</label>
        <select name="docente" id="docente" class="form-control">
            <option>Seleccione</option>
            <?php
            foreach ($docentes as $docente){
                $selected = ($isEdit && $data['id_docente'] == $docente['id']) ? 'selected' : '';
                ?>
                <option value="<?= $docente['id'] ?>" <?= $selected ?>><?= htmlspecialchars($docente['apellidos_nombres']) ?></option>
                <?php
            }
            ?>
        </select>
    </div>
</div>