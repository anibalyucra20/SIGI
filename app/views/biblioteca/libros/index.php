<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-3">
    <div class="mr-3">
        <a href="<?= BASE_URL . '/biblioteca/libros/nuevo' ?>" class="btn btn-primary mt-4">Nuevo</a>
    </div>
    <!-- Mis Libros -->
    <div class="form-row mb-2">
        <div class="col-md-3">
            <label>Búsqueda</label>
            <input type="text" id="mios-search" class="form-control" placeholder="título o autor...">
        </div>
        <div class="col-md-3">
            <label>Tipo</label>
            <input type="text" id="mios-tipo" class="form-control" placeholder="p.e. PDF">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button id="btnMiosCargar" class="btn btn-outline-primary btn-block">Cargar</button>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tbl-mios" class="table table-bordered table-hover table-sm" width="100%">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Tipo</th>
                    <th>Año</th>
                    <th>Portada</th>
                    <th>Archivo</th>
                    <th>Acciones</th>
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
            <div class="modal-header">
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
                    <div class="form-group col-md-4">
                        <label>Tags</label>
                        <input type="text" class="form-control" id="edit-tags" maxlength="1000">
                    </div>
                    <div class="form-group col-md-12">
                        <label>Temas relacionados</label><br>
                        <textarea class="col-12" name="edit-temas_relacionados" id="edit-temas_relacionados" rows="7" maxlength="5000"></textarea>
                    </div>

                </div>
                <hr>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Portada (opcional, reemplaza la actual)</label>
                        <input type="file" class="form-control-file" id="edit-portada-file" accept="image/*">
                        <small>Actual: <a href="#" id="edit-portada-link" target="_blank">ver portada</a></small>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Archivo del libro (PDF/EPUB) (opcional)</label>
                        <input type="file" class="form-control-file" id="edit-libro-file" accept=".pdf,.epub,application/pdf,application/epub+zip">
                        <small>Actual: <a href="#" id="edit-libro-link" target="_blank">descargar</a></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
<script>
    (function(global) {
        if (typeof global.cryptoRandom !== 'function') {
            global.cryptoRandom = function() {
                try {
                    // UUID-like, usando Web Crypto si está disponible
                    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                        (c ^ global.crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
                    );
                } catch (e) {
                    // Fallback
                    return 'idem-' + Date.now() + '-' + Math.random().toString(16).slice(2);
                }
            };
        }
    })(window);
</script>

<script>
    (function() {
        // ====== Helpers de config ======
        const $apiKey = $('#apiKey');
        const $apiBase = $('#apiBase');

        // jQuery: inyectar X-Api-Key en cada AJAX hacia la API base
        $(document).ajaxSend(function(_e, xhr, opts) {
            const base = ($apiBase.val() || '').replace(/\/+$/, '');
            if (opts.url && opts.url.indexOf(base) === 0) {
                const k = ($apiKey.val() || '').trim();
                if (k) xhr.setRequestHeader('X-Api-Key', k);
                if ((opts.type || 'GET').toUpperCase() === 'POST') {
                    const idem = (typeof window.cryptoRandom === 'function' ?
                        window.cryptoRandom() :
                        String(Date.now()));
                    xhr.setRequestHeader('X-Idempotency-Key', idem);
                }
            }
        });


        // ====== DataTables ======
        let dtMios = null,
            dtBuscar = null,
            dtAdopt = null;

        function initOrReplace($table, rows, existingDtRef, columnDefs = []) {
            if (existingDtRef) {
                existingDtRef.clear();
                existingDtRef.rows.add(rows).draw();
                return existingDtRef;
            }
            return $table.DataTable({
                data: rows,
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'titulo'
                    },
                    {
                        data: 'autor'
                    },
                    {
                        data: 'tipo_libro'
                    },
                    {
                        data: 'anio'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: r => r.portada_url ? `<a href="${r.portada_url}" target="_blank">Ver</a>` : ''
                    },
                    {
                        data: null,
                        orderable: false,
                        render: r => `<a href="${r.archivo_url}" target="_blank">Descargar</a>`
                    },
                    {
                        data: null,
                        orderable: false,
                        render: r => `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-edit-book" data-id="${r.id}">Editar</button>
                        </div>
                        `

                    },
                    ...(columnDefs.length ? columnDefs : [])
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                pageLength: 10,
                ordering: true
            });
        }

        // ====== Carga paginada (mergea páginas hasta un tope) ======
        async function fetchAllPaged(endpoint, params = {}, perPage = 50, maxPages = 10) {
            const base = ($apiBase.val() || '').replace(/\/+$/, '');
            const res = [];
            for (let page = 1; page <= maxPages; page++) {
                const qp = $.param(Object.assign({}, params, {
                    page: page,
                    per_page: perPage
                }));
                const url = `${base}${endpoint}?${qp}`;
                try {
                    const data = await $.getJSON(url);
                    const rows = (data && data.data) ? data.data : [];
                    res.push(...rows);
                    if (rows.length < perPage) break; // última página
                } catch (err) {
                    console.error('Error GET', url, err);
                    alert('Error al consultar la API: ' + (err?.responseJSON?.error?.message || err.statusText || ''));
                    break;
                }
            }
            return res;
        }

        // ====== Mis Libros ======
        $('#btnMiosCargar').on('click', async function() {
            const q = $('#mios-search').val().trim();
            const tipo = $('#mios-tipo').val().trim();
            const rows = await fetchAllPaged('/library/items', {
                search: q,
                tipo: tipo
            }, 50, 20);
            dtMios = initOrReplace($('#tbl-mios'), rows, dtMios);
        });
        // utils
        function html(s) {
            return String(s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]));
        }

        // Cargar por defecto "Mis Libros"
        $('#btnMiosCargar').click();
    })();
</script>


<script>
    // Abre modal y carga datos actuales
    $(document).on('click', '.btn-edit-book', async function() {
        const id = this.dataset.id;
        const base = ($('#apiBase').val() || '').replace(/\/+$/, '');

        try {
            const book = await $.getJSON(`${base}/library/show/${id}`);
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
            // al cargar show/{id}
            $('#edit-portada-link').attr('href', book.portada_url || '#')
                .text(book.portada_url ? 'ver portada' : '—');
            $('#edit-libro-link').attr('href', book.archivo_url || '#')
                .text(book.archivo_url ? 'descargar' : '—');
            $('#modalEditBook').modal('show');

        } catch (err) {
            const m = err?.responseJSON?.error?.message || err.statusText || 'No se pudo cargar el libro';
            if (window.Swal) Swal.fire('Error', m, 'error');
            else alert(m);
        }
    });

    // Envía cambios al Maestro
    $('#formEditBook').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const base = ($('#apiBase').val() || '').replace(/\/+$/, '');

        // Campos de texto
        const payload = {
            titulo: $('#edit-titulo').val().trim(),
            tipo_libro: $('#edit-tipo_libro').val().trim(),
            autor: $('#edit-autor').val().trim(),
            editorial: $('#edit-editorial').val().trim(),
            edicion: $('#edit-edicion').val().trim(),
            tomo: $('#edit-tomo').val().trim(),
            paginas: Number($('#edit-paginas').val() || 0),
            anio: Number($('#edit-anio').val() || 0),
            isbn: $('#edit-isbn').val().trim(),
            temas_relacionados: $('#edit-temas_relacionados').val().trim(),
            tags: $('#edit-tags').val().trim()
        };
        Object.keys(payload).forEach(k => { // envía solo cambios/no vacíos si quieres
            if (payload[k] === '' || Number.isNaN(payload[k])) delete payload[k];
        });

        // Archivos seleccionados
        const fPortada = $('#edit-portada-file')[0].files[0] || null;
        const fLibro = $('#edit-libro-file')[0].files[0] || null;

        const hasFiles = !!(fPortada || fLibro);

        try {
            if (hasFiles) {
                // multipart/form-data
                const fd = new FormData();
                Object.entries(payload).forEach(([k, v]) => fd.append(k, v));
                if (fPortada) fd.append('portada', fPortada);
                if (fLibro) fd.append('libro', fLibro);

                await $.ajax({
                    url: `${base}/library/update/${id}`,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    beforeSend: (xhr) => {
                        const idem = (typeof window.cryptoRandom === 'function' ?
                            window.cryptoRandom() :
                            String(Date.now()));
                        xhr.setRequestHeader('X-Idempotency-Key', idem);
                    }

                });
            } else {
                // JSON puro
                await $.ajax({
                    url: `${base}/library/update/${id}`,
                    method: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    data: JSON.stringify(payload),
                    beforeSend: (xhr) => {
                        const idem = (typeof window.cryptoRandom === 'function' ?
                            window.cryptoRandom() :
                            String(Date.now()));
                        xhr.setRequestHeader('X-Idempotency-Key', idem);
                    }

                });
            }

            $('#modalEditBook').modal('hide');
            if (window.dtAdopt) cargarVinculados(); // refresca tu tabla
            if (window.Swal) Swal.fire('Guardado', 'Datos actualizados correctamente.', 'success');

        } catch (err) {
            console.error('Update failed:', err);
            const m = err?.responseJSON?.error?.message || err.statusText || 'No se pudo guardar';
            if (window.Swal) Swal.fire('Error', m, 'error');
            else alert(m);
        }
    });
</script>