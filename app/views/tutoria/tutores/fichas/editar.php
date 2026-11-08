<?php require __DIR__ . '/../../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Llenar / Modificar Ficha Diagnóstica</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/fichas?id_programacion=<?= $tutorando['id_programacion_tutoria'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Lista
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body bg-light py-2">
        <strong class="text-primary font-size-15"><i class="fas fa-user-graduate mr-2"></i><?= htmlspecialchars($tutorando['nombre_completo']) ?></strong>
        <span class="text-muted ml-2">| DNI: <?= htmlspecialchars($tutorando['dni']) ?> | <?= htmlspecialchars($tutorando['programa_nombre'] ?: 'General') ?></span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/tutoria/fichas/guardar" method="POST">
            <input type="hidden" name="id_tutoria_estudiante" value="<?= $tutorando['id'] ?>">

            <p class="text-muted mb-3 font-size-13">
                <i class="fas fa-info-circle text-info mr-1"></i> Marque la respuesta correspondiente para cada uno de los criterios diagnósticos del tutorando:
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th>Aspecto / Pregunta a Evaluar</th>
                            <th width="200" class="text-center">Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td class="text-center font-weight-bold"><?= $idx + 1 ?></td>
                                <td>
                                    <input type="hidden" name="descripcion[]" value="<?= htmlspecialchars($item['descripcion']) ?>">
                                    <strong><?= htmlspecialchars($item['descripcion']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-success btn-sm <?= ($item['valor'] == 1) ? 'active' : '' ?>">
                                            <input type="radio" name="valor[<?= $idx ?>]" value="1" autocomplete="off" <?= ($item['valor'] == 1) ? 'checked' : '' ?>>
                                            <i class="fas fa-check mr-1"></i> SÍ
                                        </label>
                                        <label class="btn btn-outline-secondary btn-sm <?= ($item['valor'] == 0) ? 'active' : '' ?>">
                                            <input type="radio" name="valor[<?= $idx ?>]" value="0" autocomplete="off" <?= ($item['valor'] == 0) ? 'checked' : '' ?>>
                                            <i class="fas fa-times mr-1"></i> NO
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-right">
                <a href="<?= BASE_URL ?>/tutoria/tutores/fichas?id_programacion=<?= $tutorando['id_programacion_tutoria'] ?>" class="btn btn-secondary btn-sm mr-2">Cancelar</a>
                <button type="submit" class="btn btn-success btn-sm px-4">
                    <i class="fas fa-save mr-1"></i> Guardar Ficha Diagnóstica
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../../layouts/footer.php'; ?>
