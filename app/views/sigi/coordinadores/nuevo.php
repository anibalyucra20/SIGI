<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h4><?= $pageTitle ?></h4>
        <form action="<?= BASE_URL ?>/sigi/coordinadores/guardar" method="POST">
            <div class="form-group">
                <label>Docente (usuario)</label>
                <select name="id_usuario" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= $u['apellidos_nombres'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Programa de Estudios</label>
                <select name="id_programa_estudio" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($programas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Coordinador</button>
            <a href="<?= BASE_URL ?>/sigi/coordinadores" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>