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
                                    <a href="<?= BASE_URL ?>/academico/matricula/eliminarDetalle/<?= $row['id'] ?>" target="_blank"
                                        class="btn btn-danger btn-sm m-1"
                                        data-sigi-confirm="1"
                                        data-sigi-title="¿Eliminar unidad didáctica?"
                                        data-sigi-text="Se eliminará la unidad didáctica <?= htmlspecialchars($row['unidad_didactica']) ?>. Esta acción no se puede deshacer."
                                        data-sigi-confirm-text="Sí, eliminar"
                                        data-sigi-cancel-text="No, cancelar"
                                        data-sigi-loader="Eliminando…">
                                        Eliminar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener('click', async function(e) {
            const a = e.target.closest('a[data-sigi-confirm]');
            if (!a) return;

            e.preventDefault(); // no navegar aún

            const href = a.getAttribute('href');
            const title = a.getAttribute('data-sigi-title') || '¿Estás seguro?';
            const text = a.getAttribute('data-sigi-text') || 'Confirma para continuar.';
            const confirmText = a.getAttribute('data-sigi-confirm-text') || 'Si, eliminar';
            const cancelText = a.getAttribute('data-sigi-cancel-text') || 'No, cancelar';
            const loaderText = a.getAttribute('data-sigi-loader') || 'Eliminando…';

            const result = await Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                allowOutsideClick: true
            });

            if (result.isConfirmed) {
                if (window.SIGI_LOADER) window.SIGI_LOADER.show(loaderText);
                window.location.href = href;
            } else {
                if (window.SIGI_LOADER) window.SIGI_LOADER.hide();
            }
        }, true);
    </script>
<?php else: ?>
    <p>El módulo solo es para rol de Administrador Académico.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>