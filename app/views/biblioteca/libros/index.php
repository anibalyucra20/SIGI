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
                    <th>Dueño IES</th>
                    <th>Portada</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <input type="hidden" id="apiKey" value="<?= htmlspecialchars($sistema['token_sistema'] ?? '') ?>">
    <input type="hidden" id="apiBase" value="<?= rtrim(API_BASE_URL, '/') ?>/api">
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
<script>
    (function() {
        // ====== Helpers de config ======
        const $apiKey = $('#apiKey');
        const $apiBase = $('#apiBase');

        // jQuery: inyectar X-Api-Key en cada AJAX hacia la API base
        $(document).ajaxSend(function(e, xhr, opts) {
            const base = ($apiBase.val() || '').replace(/\/+$/, '');
            if (opts.url && opts.url.indexOf(base) === 0) {
                const k = ($apiKey.val() || '').trim();
                if (k) xhr.setRequestHeader('X-Api-Key', k);
                // Opcional: idem key por petición POST
                if ((opts.type || 'GET').toUpperCase() === 'POST') {
                    xhr.setRequestHeader('X-Idempotency-Key', cryptoRandom());
                }
            }
        });

        function cryptoRandom() {
            try {
                return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
            } catch (e) {
                return 'idem-' + Date.now() + '-' + Math.random().toString(16).slice(2);
            }
        }

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
                        data: 'owner_ies',
                        defaultContent: ''
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