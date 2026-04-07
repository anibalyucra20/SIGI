<?php require __DIR__ . '/../../layouts/header.php'; ?>

<style>
    /* Estilos para emular la paginación circular */
    .dataTables_wrapper .pagination .page-item .page-link {
        border: none;
        color: #555;
        padding: 6px 12px;
        margin: 0 2px;
        cursor: pointer;
        background: none;
        font-weight: 500;
    }
    .dataTables_wrapper .pagination .page-item.active .page-link {
        background-color: #007bff !important;
        color: #fff !important;
        border-radius: 50% !important;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dataTables_wrapper .pagination .page-item.disabled .page-link {
        color: #ccc;
        cursor: not-allowed;
    }
</style>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">Biblioteca Virtual</h4>
        <a href="<?= BASE_URL . '/biblioteca/libros/nuevo' ?>" class="btn btn-primary">Nuevo</a>
    </div>

    <div class="form-row mb-3">
        <div class="col-md-5">
            <label class="small font-weight-bold">Búsqueda rápida</label>
            <input type="text" id="mios-search" class="form-control" placeholder="Título, autor o ISBN...">
        </div>
        <div class="col-md-4">
            <label class="small font-weight-bold">Filtrar por Tipo</label>
            <input type="text" id="mios-tipo" class="form-control" placeholder="p.e. PDF, EPUB">
        </div>
        <div class="col-md-3">
            <label class="small font-weight-bold">Registros</label>
            <select id="mios-per-page" class="form-control">
                <option value="10">10 por página</option>
                <option value="25">25 por página</option>
                <option value="50">50 por página</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tbl-mios" class="table table-bordered table-hover table-sm mb-0" width="100%">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Tipo</th>
                    <th>Año</th>
                    <th>Portada</th>
                    <th>Archivo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <input type="hidden" id="apiKey" value="<?= htmlspecialchars($sistema['token_sistema'] ?? '') ?>">
    <input type="hidden" id="apiBase" value="<?= rtrim(API_BASE_URL, '/') ?>/api">
</div>

<div class="modal fade" id="modalEditBook" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEditBook" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Editar libro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Título *</label>
                        <input type="text" class="form-control" id="edit-titulo" required maxlength="500">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tipo</label>
                        <input type="text" class="form-control" id="edit-tipo_libro" maxlength="100">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Autor</label>
                        <input type="text" class="form-control" id="edit-autor" maxlength="300">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Editorial</label>
                        <input type="text" class="form-control" id="edit-editorial" maxlength="300">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Edición</label>
                        <input type="text" class="form-control" id="edit-edicion" maxlength="20">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tomo</label>
                        <input type="text" class="form-control" id="edit-tomo" maxlength="10">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Páginas</label>
                        <input type="number" class="form-control" id="edit-paginas" min="0">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Año</label>
                        <input type="number" class="form-control" id="edit-anio" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>ISBN</label>
                        <input type="text" class="form-control" id="edit-isbn" maxlength="50">
                    </div>
                    <div class="form-group col-md-8">
                        <label>Tags</label>
                        <input type="text" class="form-control" id="edit-tags" maxlength="1000">
                    </div>
                    <div class="form-group col-md-12">
                        <label>Temas relacionados</label>
                        <textarea class="form-control" id="edit-temas_relacionados" rows="3" maxlength="5000"></textarea>
                    </div>
                </div>
                <hr>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Portada</label>
                        <input type="file" class="form-control-file" id="edit-portada-file" accept="image/*">
                        <small>Actual: <a href="#" id="edit-portada-link" target="_blank">ver</a></small>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Archivo del libro</label>
                        <input type="file" class="form-control-file" id="edit-libro-file" accept=".pdf,.epub">
                        <small>Actual: <a href="#" id="edit-libro-link" target="_blank">descargar</a></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

<script>
$(document).ready(function() {
    const $apiBase = $('#apiBase').val().replace(/\/+$/, '');
    const $apiKey = $('#apiKey').val();
    let dtMios = null;

    // Configuración de Seguridad para Ajax
    $.ajaxSetup({
        beforeSend: function(xhr, opts) {
            if (opts.url.indexOf($apiBase) === 0 && $apiKey) {
                xhr.setRequestHeader('X-Api-Key', $apiKey);
            }
        }
    });

    // Inicialización de DataTable con Server-Side real
    dtMios = $('#tbl-mios').DataTable({
        serverSide: true,
        processing: true,
        searching: false, // Desactivamos el search de DT para usar tus inputs personalizados
        ordering: false,
        lengthChange: false, // Usamos tu select personalizado
        pageLength: 10,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
        ajax: async function(data, callback) {
            const page = Math.floor(data.start / data.length) + 1;
            const params = {
                page: page,
                per_page: data.length,
                search: $('#mios-search').val().trim(),
                tipo: $('#mios-tipo').val().trim()
            };

            try {
                const res = await $.getJSON(`${$apiBase}/library/items?${$.param(params)}`);
                const rows = res.data || [];
                // Tu API devuelve total_records en pagination
                const total = res.pagination?.total_records || rows.length;

                callback({
                    draw: data.draw,
                    data: rows,
                    recordsTotal: total,
                    recordsFiltered: total
                });
            } catch (err) {
                console.error("Error API", err);
                callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: [
            { 
                data: null, 
                render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 
            },
            { 
                data: 'titulo', 
                render: d => `<span class="font-weight-bold">${html(d)}</span>` 
            },
            { data: 'autor', render: d => html(d) },
            { data: 'tipo_libro' },
            { data: 'anio' },
            { 
                data: null, 
                render: r => r.portada_url ? `<a href="${r.portada_url}" target="_blank" class="btn btn-xs btn-outline-info">Ver</a>` : 'N/A' 
            },
            { 
                data: null, 
                render: r => `<a href="${r.archivo_url}" target="_blank" class="btn btn-xs btn-outline-success">Descargar</a>` 
            },
            { 
                data: null, 
                class: 'text-center', 
                render: r => `<button class="btn btn-xs btn-primary btn-edit-book" data-id="${r.id}"><i class="fa fa-edit"></i> Editar</button>` 
            }
        ]
    });

    // Controladores de Filtros (Recargan la tabla)
    let searchTimer;
    $('#mios-search, #mios-tipo').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => dtMios.ajax.reload(), 500);
    });

    $('#mios-per-page').on('change', function() {
        dtMios.page.len($(this).val()).ajax.reload();
    });

    function html(s) { return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

    // Lógica del Modal (Show)
    $(document).on('click', '.btn-edit-book', async function() {
        const id = this.dataset.id;
        try {
            const book = await $.getJSON(`${$apiBase}/library/show/${id}`);
            $('#edit-id').val(book.id);
            $('#edit-titulo').val(book.titulo || '');
            $('#edit-tipo_libro').val(book.tipo_libro || '');
            $('#edit-autor').val(book.autor || '');
            $('#edit-editorial').val(book.editorial || '');
            $('#edit-edicion').val(book.edicion || '');
            $('#edit-tomo').val(book.tomo || '');
            $('#edit-paginas').val(book.paginas || '');
            $('#edit-anio').val(book.anio || '');
            $('#edit-isbn').val(book.isbn || '');
            $('#edit-temas_relacionados').val(book.temas_relacionados || '');
            $('#edit-tags').val(book.tags || '');
            $('#edit-portada-link').attr('href', book.portada_url || '#');
            $('#edit-libro-link').attr('href', book.archivo_url || '#');
            $('#modalEditBook').modal('show');
        } catch (err) { alert('Error al cargar datos'); }
    });

    // Lógica del Modal (Update)
    $('#formEditBook').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const fd = new FormData();
        
        fd.append('titulo', $('#edit-titulo').val());
        fd.append('tipo_libro', $('#edit-tipo_libro').val());
        fd.append('autor', $('#edit-autor').val());
        fd.append('editorial', $('#edit-editorial').val());
        fd.append('edicion', $('#edit-edicion').val());
        fd.append('tomo', $('#edit-tomo').val());
        fd.append('paginas', $('#edit-paginas').val());
        fd.append('anio', $('#edit-anio').val());
        fd.append('isbn', $('#edit-isbn').val());
        fd.append('temas_relacionados', $('#edit-temas_relacionados').val());
        fd.append('tags', $('#edit-tags').val());

        const fP = $('#edit-portada-file')[0].files[0];
        const fL = $('#edit-libro-file')[0].files[0];
        if (fP) fd.append('portada', fP);
        if (fL) fd.append('libro', fL);

        try {
            await $.ajax({
                url: `${$apiBase}/library/update/${id}`,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false
            });
            $('#modalEditBook').modal('hide');
            dtMios.ajax.reload(null, false); // Refresca sin perder la página
        } catch (err) { alert('Error al actualizar'); }
    });
});
</script>