<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminTutoria()): ?>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0">Programación de Tutores</h4>
                <div class="page-title-right">
                    <a href="<?= BASE_URL ?>/tutoria/programacion/nuevo" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Asignar Nuevo Tutor
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i><?= $_SESSION['flash_success']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i><?= $_SESSION['flash_error']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-programacion" class="table table-bordered table-hover table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">Nro</th>
                            <th>Sede</th>
                            <th>Periodo Académico</th>
                            <th>Docente Tutor</th>
                            <th width="120" class="text-center">Tutorandos</th>
                            <th width="200" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTable AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = $('#tabla-programacion').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/tutoria/programacion/data',
                    type: 'GET'
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    { data: 'sede_nombre' },
                    { data: 'periodo_academico_nombre' },
                    {
                        data: 'docente_nombre',
                        render: function(data, type, row) {
                            return `<strong>${data}</strong><br><small class="text-muted">DNI: ${row.docente_dni || '-'}</small>`;
                        }
                    },
                    {
                        data: 'total_estudiantes',
                        className: 'text-center',
                        render: function(data, type, row) {
                            const badge = data > 0 ? 'badge-primary' : 'badge-secondary';
                            return `<span class="badge ${badge} p-1 font-size-12"><i class="fas fa-user-graduate mr-1"></i>${data} alumnos</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/${row.id}" class="btn btn-info btn-sm" title="Ver y Asignar Tutorandos">
                                    <i class="fas fa-users mr-1"></i> Tutorandos
                                </a>
                                <a href="<?= BASE_URL ?>/tutoria/programacion/editar/${row.id}" class="btn btn-warning btn-sm" title="Editar Tutor">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/tutoria/programacion/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar esta Programación de Tutoría?');" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
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
    <div class="alert alert-warning">
        <i class="fas fa-lock mr-2"></i> Esta sección es exclusiva para el rol de Coordinador / Administrador de Tutoría.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>