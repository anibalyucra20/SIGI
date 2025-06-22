<!-- app/views/sigi/docentes/permisos.php -->

<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <h4>Permisos de <?= htmlspecialchars($docente['apellidos_nombres']) ?></h4>
    <form action="<?= BASE_URL ?>/sigi/docentes/guardarPermisos" method="post" class="card p-1">
        <input type="hidden" name="id_usuario" value="<?= $docente['id'] ?>">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Sistema / Rol</th>
                    <?php foreach ($roles as $rol): ?>
                        <th><?= htmlspecialchars($rol['nombre']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sistemas as $sistema): ?>
                    <tr>
                        <td><?= htmlspecialchars($sistema['nombre']) ?></td>
                        <?php foreach ($roles as $rol):
                            $checked = false;
                            foreach ($permisos as $perm) {
                                if ($perm['id_sistema'] == $sistema['id'] && $perm['id_rol'] == $rol['id']) {
                                    $checked = true;
                                    break;
                                }
                            }
                        ?>
                            <td class="text-center">
                                <input type="checkbox" name="permisos[]" value="<?= $sistema['id'] ?>-<?= $rol['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-3 d-flex justify-content-center gap-2">
            <button type="submit" class="btn btn-success btn-sm px-4 m-1">
                <i class="bi bi-floppy"></i> Guardar Permisos
            </button>
            <a href="<?= BASE_URL ?>/sigi/docentes" class="btn btn-secondary btn-sm px-4 m-1">
                <i class="bi bi-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>