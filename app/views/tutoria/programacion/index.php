<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminTutoria()): ?>

    <div class="card p-2">
        <h3 class="mb-2">Programación Tutoria</h3>
        <div class="mb-2">
            <a href="<?= BASE_URL ?>/tutoria/programacion/nuevo" class="btn btn-success">+ Nuevo</a>
        </div>
        <div class="table-responsive">
            <table id="tabla-programacion" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nro</th>
                        <th>Sede</th>
                        <th>Periodo Academico</th>
                        <th>Docente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable AJAX -->
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>