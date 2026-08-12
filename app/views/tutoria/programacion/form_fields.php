<div class="row">
    <div class="col-md-4 mb-2">
        <label class="form-label">Sede</label>
        <input type="text" name="sede" class="form-control" maxlength="100" value="<?= htmlspecialchars($tutoria_programacion['id_sede'] ?? '') ?>">
    </div>
    <div class="col-md-2 mb-2">
        <label class="form-label">Periodo Académico</label>
        <input type="text" name="periodo_academico" class="form-control" value="<?= htmlspecialchars($tutoria_programacion['id_periodo_academico'] ?? '') ?>">
    </div>
    <div class="col-md-8 mb-2">
        <label class="form-label">Docente</label>
        <input type="text" name="docente" class="form-control" maxlength="200" value="<?= htmlspecialchars($tutoria_programacion['id_docente'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Conclusiones</label>
        <input type="text" name="conclusiones" class="form-control" maxlength="3000" value="<?= htmlspecialchars($tutoria_programacion['conclusiones'] ?? '') ?>">
    </div>
</div>