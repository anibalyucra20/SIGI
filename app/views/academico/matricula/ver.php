<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-2">
        <h4 class="mb-3 mt-2 text-center">Detalle de Matrícula - <?= htmlspecialchars($estudiante['apellidos_nombres']) ?></h4>

        <div class="mb-3">
            <a href="<?= BASE_URL ?>/academico/matricula" class="btn btn-danger">Regresar</a>
            <?php if ($periodo_vigente): ?>
                <a href="<?= BASE_URL ?>/academico/matricula/agregarUd/<?= $id_matricula ?>" class="btn btn-success">Agregar Unidad Didáctica</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table id="tabla-detalle-matricula" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Orden</th>
                        <th>Programa de Estudios</th>
                        <th>Semestre</th>
                        <th>Unidad Didáctica</th>
                        <th>Docente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['programa']) ?></td>
                            <td><?= htmlspecialchars($row['semestre']) ?></td>
                            <td><?= htmlspecialchars($row['unidad_didactica']) ?></td>
                            <td><?= htmlspecialchars($row['docente']) ?></td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <a href="<?= BASE_URL ?>/academico/matricula/eliminarDetalle/<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro de eliminar esta unidad didáctica?')">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>