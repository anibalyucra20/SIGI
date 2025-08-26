<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-2">
    <div class="form-row mb-2">
        <div class="col-md-3">
            <label>Búsqueda</label>
            <input type="text" id="busq-q" class="form-control" placeholder="título o autor...">
        </div>
        <div class="col-md-2">
            <label>Tipo</label>
            <input type="text" id="busq-tipo" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Owner IES</label>
            <input type="number" id="busq-owner" class="form-control" min="1">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button id="btnBuscar" class="btn btn-outline-primary btn-block">Buscar</button>
        </div>
    </div>
    <div class="table-responsive">
        <table id="tbl-buscar" class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Tipo</th>
                    <th>Año</th>
                    <th>Owner IES</th>
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
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
<script>
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
    $('#btnBuscar').on('click', async function() {
        const q = $('#busq-q').val().trim();
        const tipo = $('#busq-tipo').val().trim();
        const owner = $('#busq-owner').val().trim();
        const rows = await fetchAllPaged('/library/search', {
            search: q,
            tipo: tipo,
            owner: owner || undefined
        }, 50, 20);
        // agrega columna acciones (adoptar)
        const colAcciones = [{
            data: null,
            orderable: false,
            render: r =>
                `<button class="btn btn-sm btn-primary btn-adoptar" data-id="${r.id}" data-title="${html(r.titulo)}">Adoptar</button>`
        }];
        dtBuscar = initOrReplace($('#tbl-buscar'), rows, dtBuscar, colAcciones);
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
    };
    $('#btnBuscar').click();
</script>