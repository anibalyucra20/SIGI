<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <div class="card-header">
            <h4 class="">Coordinadores por Programa de Estudios - Periodo</h4>
        </div>
        <div class="card-body table-responsive">
            <a href="<?= BASE_URL . "/sigi/coordinadores/nuevo" ?>" class="btn btn-success"><i class="fa fa-plus"></i> Nuevo</a> <br><br>
            <table class="table table-bordered table-hover table-striped" id="tabla-coordinadores">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Coordinador</th>
                        <th>Programa de Estudios</th>
                        <th>Periodo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($coordinadores)) : ?>
                        <?php foreach ($coordinadores as $i => $item) : ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $item['apellidos_nombres'] ?></td>
                                <td><?= $item['programa'] ?></td>
                                <td><?= $item['periodo'] ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/sigi/coordinadores/eliminar/<?= $item['id'] ?>"
                                        onclick="return confirm('¿Estás seguro de eliminar este registro?');"
                                        class="btn btn-danger btn-sm">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron registros para este periodo y sede.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>