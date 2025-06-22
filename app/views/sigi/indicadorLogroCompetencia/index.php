<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <div class="card p-2">
        <h4>
            Indicadores de Logro - Competencia:
            <span class="text-primary"><?= htmlspecialchars($competencia['codigo'] . ' - ' . $competencia['descripcion']) ?></span>
        </h4>
        <div class="mb-3 text-end">
            <a href="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/nuevo/<?= $id_competencia ?>" class="btn btn-success">Nuevo Indicador</a>
            <a href="<?= BASE_URL ?>/sigi/competencias" class="btn btn-secondary">Volver a Competencias</a>
        </div>
        <div class="table-responsive">
            <table id="tabla-indicadores" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Correlativo</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables AJAX llenará esto -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = $('#tabla-indicadores').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/data/<?= $id_competencia ?>',
                    type: 'GET'
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {
                        data: 'correlativo'
                    },
                    {
                        data: 'descripcion'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/editar/${row.id}" class="btn btn-warning btn-sm m-1">Editar</a>
                        <a href="<?= BASE_URL ?>/sigi/indicadorLogroCompetencia/eliminar/${row.id}/<?= $id_competencia ?>" class="btn btn-danger btn-sm m-1" onclick="return confirm('¿Seguro de eliminar este indicador?');">Eliminar</a>
                    `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>