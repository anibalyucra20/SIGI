<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card p-2">
        <h4>Editar Programación de Unidad Didáctica</h4>
        <form action="<?= BASE_URL ?>/academico/programacionUnidadDidactica/guardarEdicion" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
            <input type="hidden" name="id" value="<?= htmlspecialchars($programacion['id']) ?>">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label>Programa de Estudios</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['programa_nombre']) ?>" readonly>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Plan de Estudios</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['plan_nombre']) ?>" readonly>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Módulo Profesional</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['modulo_nombre']) ?>" readonly>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Semestre</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['semestre_nombre']) ?>" readonly>
                </div>
                <div class="col-md-5 mb-2">
                    <label>Unidad Didáctica</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['unidad_nombre']) ?>" readonly>
                </div>
                <div class="col-md-5 mb-2">
                    <label>Docente *</label>
                    <select name="id_docente" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($docentes as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($programacion['id_docente'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['apellidos_nombres']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Turno</label>
                    <input type="text" class="form-control" value="<?php
                        switch ($programacion['turno']) {
                            case 'M': echo 'Mañana'; break;
                            case 'T': echo 'Tarde'; break;
                            case 'N': echo 'Noche'; break;
                            default: echo $programacion['turno'];
                        }
                    ?>" readonly>
                </div>
                <div class="col-md-2 mb-2">
                    <label>Sección</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($programacion['seccion']) ?>" readonly>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                <a href="<?= BASE_URL ?>/academico/programacionUnidadDidactica" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
