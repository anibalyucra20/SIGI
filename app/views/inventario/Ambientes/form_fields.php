<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Ambiente</label>
        <select name="id_sigi_ambiente" class="form-control" id="id_sigi_ambiente">
            <option value="">Seleccione</option>
            <?php
            foreach ($ambientes as $ambiente){
                $selected = ($isEdit && isset($data['id_sigi_ambiente']) && $data['id_sigi_ambiente'] == $ambiente['id_ambiente']) ? 'selected' : '';
                ?>
                <option value="<?= $ambiente['id_ambiente'] ?>" <?= $selected ?>><?= htmlspecialchars($ambiente['tipo_ambiente']) ?></option>
                <?php
            }
            ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Docente</label>
        <select name="id_docente" id="id_docente" class="form-control">
            <option value="">Seleccione</option>
            <?php
            foreach ($docentes as $docente){
                $selected = ($isEdit && isset($data['id_docente']) && $data['id_docente'] == $docente['id']) ? 'selected' : '';
                ?>
                <option value="<?= $docente['id'] ?>" <?= $selected ?>><?= htmlspecialchars($docente['apellidos_nombres']) ?></option>
                <?php
            }
            ?>
        </select>
    </div>
</div>