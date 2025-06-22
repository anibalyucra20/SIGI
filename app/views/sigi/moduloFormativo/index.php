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
    <h3 class="mb-1">Módulos Formativos</h3>
    <div class="col-md-6 text-end mb-2">
        <a href="<?= BASE_URL ?>/sigi/moduloFormativo/nuevo" class="btn btn-success mt-2">Nuevo Módulo</a>
    </div>
    <div class="row mb-3">
        <div class="col-md-3">
            <label>Programa de Estudio</label>
            <select id="filter-programa" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($programas as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Plan de Estudio</label>
            <select id="filter-plan" class="form-control">
                <option value="">Todos</option>
            </select>
        </div>

    </div>

    <div class="table-responsive">
        <table id="tabla-modulos" class="table table-bordered table-hover table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Programa</th>
                    <th>Plan</th>
                    <th>Descripción</th>
                    <th>Nro. Módulo</th>
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
    let planesPorPrograma = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Al cargar un programa, pide los planes via AJAX
        $('#filter-programa').on('change', function() {
            const idPrograma = $(this).val();
            $('#filter-plan').html('<option value="">Todos</option>');
            if (idPrograma) {
                $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, function(planes) {
                    planes.forEach(function(pl) {
                        $('#filter-plan').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                    });
                });
            }
            $('#tabla-modulos').DataTable().ajax.reload();
        });

        $('#filter-plan').on('change', function() {
            $('#tabla-modulos').DataTable().ajax.reload();
        });


        const tabla = $('#tabla-modulos').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '<?= BASE_URL ?>/sigi/moduloFormativo/data',
                type: 'GET',
                data: function(d) {
                    d.filter_programa = $('#filter-programa').val();
                    d.filter_plan = $('#filter-plan').val();
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    }
                },
                {
                    data: 'programa_nombre'
                },
                {
                    data: 'plan_nombre'
                },
                {
                    data: 'descripcion'
                },
                {
                    data: 'nro_modulo'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <a href="<?= BASE_URL ?>/sigi/moduloFormativo/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
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