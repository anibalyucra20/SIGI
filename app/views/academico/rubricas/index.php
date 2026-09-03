<?php 
$module = 'academico';
require __DIR__ . '/../../layouts/header.php'; 
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card p-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"> Mi Banco de Rúbricas</h3><br>
        <a href="<?= BASE_URL ?>/academico/rubricas/nuevo" class="btn btn-success btn-sm">
            <i class="fa fa-cloud-download-alt"></i> + nuevo
        </a>
    </div>

    <div class="table-responsive">
        <table id="tabla-mis-rubricas" class="table table-bordered table-hover table-sm w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Unidad Didáctica Asignada</th>
                    <th>Origen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) return;

    $('#tabla-mis-rubricas').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '<?= BASE_URL ?>/academico/rubricas/data', type: 'GET' },
        columns: [
            { data: null, render: (d,t,r,m) => m.row + 1 },
            { data: 'nombre' },
            { data: 'unidad_didactica_nombre', render: d => d ? d : '<i class="text-muted">No asignada</i>' },
            { data: 'master_rubrica_id', render: d => d ? '<span class="badge badge-info">Clonada</span>' : '<span class="badge badge-secondary">Propia</span>' },
            {
                data: null, orderable:false, searchable:false,
                render: function(row){
                    const btnEdit = `<a href="<?= BASE_URL ?>/academico/rubricas/editar/${row.id}" class="btn btn-warning btn-sm m-1" title="Editar"><i class="fa fa-pen"></i></a>`;
                    const btnPrint = `<a href="<?= BASE_URL ?>/academico/rubricas/imprimirPdf/${row.id}" target="_blank" class="btn btn-info btn-sm m-1" title="Imprimir PDF"><i class="fa fa-file-pdf"></i></a>`;
                    const btnDel = `<a href="<?= BASE_URL ?>/academico/rubricas/eliminar/${row.id}" class="btn btn-danger btn-sm m-1" onclick="return confirm('¿Seguro que desea eliminar esta rúbrica?')" title="Eliminar"><i class="fa fa-trash"></i></a>`;
                    return btnEdit + btnPrint + btnDel;
                }
            }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
    });
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>