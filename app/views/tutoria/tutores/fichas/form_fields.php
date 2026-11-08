<?php
// Campos reutilizables para recojo de información de tutoría
?>
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
