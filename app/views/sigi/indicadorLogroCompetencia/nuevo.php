<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
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
    <h4>Nuevo Indicador de Logro</h4>
    <form action="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
        <?php include __DIR__ . '/form_fields.php'; ?>
        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-success px-4">Guardar</button>
            <a href="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/index/<?= $id_competencia ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
