<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-2">
    <label class="form-label">Vincular Libro a Unidad Didáctica:</label>
    <br>
    <div class="row">
        <div class="col-md-2 mb-2">
            <label class="form-label">Programa de Estudio *</label>
            <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
                <option value="">Todos</option>
                <?php foreach ($programas as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (!empty($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Plan de Estudio *</label>
            <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
                <option value="">Todos</option>
                <?php if (!empty($planes)): ?>
                    <?php foreach ($planes as $pl): ?>
                        <option value="<?= $pl['id'] ?>" <?= (!empty($id_plan_selected) && $id_plan_selected == $pl['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pl['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Módulo Formativo *</label>
            <select name="id_modulo_formativo" id="id_modulo_formativo" class="form-control" required>
                <option value="">Todos</option>
                <?php if (!empty($modulos)): ?>
                    <?php foreach ($modulos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= (!empty($id_modulo_selected) && $id_modulo_selected == $m['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Periodo Académico *</label>
            <select name="id_semestre" id="id_semestre" class="form-control" required>
                <option value="">Todos</option>
                <?php if (!empty($semestres)): ?>
                    <?php foreach ($semestres as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (!empty($id_semestre_selected) && $id_semestre_selected == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Unidad Didáctica *</label>
            <select name="id_unidad_didactica" id="id_unidad_didactica" class="form-control" required>
                <option value="">Todos</option>
                <?php if (!empty($unidades)): ?>
                    <?php foreach ($unidades as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (!empty($cap['id_unidad_didactica']) && $cap['id_unidad_didactica'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
    <div class="form-row mb-2">
        <div class="col-md-3">
            <label>Búsqueda</label>
            <input type="text" id="busq-q" class="form-control" placeholder="título o autor...">
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
    let dtBuscar = null;

    // Inyecta headers para la API
    $(document).ajaxSend(function(_e, xhr, opts) {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        if (opts.url && opts.url.indexOf(base) === 0) {
            const k = ($apiKey.val() || '').trim();
            if (k) xhr.setRequestHeader('X-Api-Key', k);
            if ((opts.type || 'GET').toUpperCase() === 'POST') {
                xhr.setRequestHeader('X-Idempotency-Key', cryptoRandom());
            }
        }
    });

    function cryptoRandom() {
        try {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
        } catch {
            return 'idem-' + Date.now() + '-' + Math.random().toString(16).slice(2);
        }
    }

    function initOrReplace($table, rows, existingDtRef, columnDefs = []) {
        if (existingDtRef) {
            existingDtRef.clear();
            existingDtRef.rows.add(rows).draw(false);
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
                    render: r => `<a href="${r.archivo_url}" target="_blank">Ver</a>`
                },
                ...(columnDefs.length ? columnDefs : [])
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                // Si te da CORS, usa copia local: <?= BASE_URL ?>/assets/datatables/es-ES.json
            },
            pageLength: 10,
            ordering: true
        });
    }

    function getChain() {
        return {
            id_programa_estudio: $('#id_programa_estudios').val() || '',
            id_plan: $('#id_plan_estudio').val() || '',
            id_modulo_formativo: $('#id_modulo_formativo').val() || '',
            id_semestre: $('#id_semestre').val() || '',
            id_unidad_didactica: $('#id_unidad_didactica').val() || ''
        };
    }

    async function fetchAllPaged(endpoint, params = {}, perPage = 50, maxPages = 10) {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const out = [];
        for (let page = 1; page <= maxPages; page++) {
            const qp = $.param(Object.assign({}, params, {
                page,
                per_page: perPage
            }));
            const url = `${base}${endpoint}?${qp}`;
            try {
                const data = await $.getJSON(url);
                const rows = data?.data || [];
                out.push(...rows);
                if (rows.length < perPage) break;
            } catch (err) {
                console.error('Error GET', url, err);
                const msg = err?.responseJSON?.error?.message || err.statusText || 'Error consultando API';
                alert(msg);
                break;
            }
        }
        return out;
    }

    function validateChainOrWarn() {
        const chain = getChain();
        if (!chain.id_programa_estudio || !chain.id_plan || !chain.id_modulo_formativo || !chain.id_semestre || !chain.id_unidad_didactica) {
            const msg = 'Debe seleccionar Programa, Plan, Módulo, Periodo Académico y Unidad Didáctica antes de buscar.';
            if (window.Swal) Swal.fire('Faltan filtros', msg, 'warning');
            else alert(msg);
            return null;
        }
        return chain;
    }

    // Buscar: título/autor/temas (API ya contempla temas_relacionados)
    $('#btnBuscar').on('click', async function() {
        const q = $('#busq-q').val().trim();
        const chain = validateChainOrWarn();
        if (!chain) return;

        // 1) Trae los ya adoptados para ESA UD (para excluirlos del resultado)
        const adopted = await (async () => {
            const base = ($apiBase.val() || '').replace(/\/+$/, '');
            const qs = $.param(chain);
            try {
                const data = await $.getJSON(`${base}/library/adopted?${qs}`);
                return data?.data || [];
            } catch (e) {
                console.warn('No se pudo leer adoptados, continuaré igual.', e);
                return [];
            }
        })();
        const adoptedIds = new Set(adopted.map(x => x.id));

        // 2) Busca en el maestro (global) por q (título/autor/temas)
        const rows = await fetchAllPaged('/library/search', {
            search: q
        }, 50, 10);

        // 3) Excluir los que ya están adoptados en esa UD
        const filtrados = rows.filter(r => !adoptedIds.has(r.id));

        // 4) Render con botón "Adoptar"
        const colAcciones = [{
            data: null,
            orderable: false,
            render: r => `<button class="btn btn-sm btn-primary btn-adoptar" data-id="${r.id}">Vincular</button>`
        }];
        dtBuscar = initOrReplace($('#tbl-buscar'), filtrados, dtBuscar, colAcciones);
    });

    function ensureSweetAlert() {
        if (window.Swal) return Promise.resolve();
        return new Promise((res, rej) => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            s.onload = res;
            s.onerror = rej;
            document.head.appendChild(s);
        });
    }
    // Adoptar → vincular a la UD seleccionada, luego quitar fila
    $(document).on('click', '.btn-adoptar', async function() {
        await ensureSweetAlert();
        const chain = validateChainOrWarn();
        if (!chain) return;

        const idLibro = this.dataset.id;
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const ask = async (title, text) => {
            if (window.Swal) {
                const res = await Swal.fire({
                    title,
                    text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, adoptar',
                    cancelButtonText: 'Cancelar'
                });
                return res.isConfirmed;
            }
            return confirm(`${title}\n\n${text}`);
        };

        const ok = await ask('Confirmar Vinculo', '¿Desea vincular este libro a la unidad didáctica seleccionada?');
        if (!ok) return;

        const $btn = $(this).prop('disabled', true);
        try {
            await $.ajax({
                url: `${base}/library/adopt/${idLibro}`,
                method: 'POST',
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(chain),
                beforeSend: (xhr) => xhr.setRequestHeader('X-Idempotency-Key', cryptoRandom())
            });

            // quitar de la tabla para evitar duplicidad
            dtBuscar.row($btn.closest('tr')).remove().draw(false);

            if (window.Swal) Swal.fire('Adoptado', 'Libro vinculado correctamente.', 'success');
        } catch (err) {
            const m = err?.responseJSON?.error?.message || err.statusText || 'Error al adoptar';
            if (window.Swal) Swal.fire('Error', m, 'error');
            else alert(m);
            $btn.prop('disabled', false);
        }
    });

    // (Opcional) deshabilita el botón Buscar si no hay UD aún
    function toggleBuscar() {
        const chain = getChain();
        const ready = chain.id_programa_estudio && chain.id_plan && chain.id_modulo_formativo && chain.id_semestre && chain.id_unidad_didactica;
        $('#btnBuscar').prop('disabled', !ready);
    }
    ['#id_programa_estudios', '#id_plan_estudio', '#id_modulo_formativo', '#id_semestre', '#id_unidad_didactica']
    .forEach(sel => $(sel).on('change', toggleBuscar));
    toggleBuscar();
</script>

<script>
    // Filtros dependientes (tus endpoints locales SIGI)
    $('#id_programa_estudios').on('change', function() {
        const idPrograma = $(this).val();
        $('#id_plan_estudio').html('<option value="">Todos</option>');
        $('#id_modulo_formativo').html('<option value="">Todos</option>');
        $('#id_semestre').html('<option value="">Todos</option>');
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, pl => {
                pl.forEach(x => $('#id_plan_estudio').append(`<option value="${x.id}">${x.nombre}</option>`));
            }).always();
        } else {}
    });

    $('#id_plan_estudio').on('change', function() {
        const idPlan = $(this).val();
        $('#id_modulo_formativo').html('<option value="">Todos</option>');
        $('#id_semestre').html('<option value="">Todos</option>');
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, ms => {
                ms.forEach(m => $('#id_modulo_formativo').append(`<option value="${m.id}">${m.descripcion}</option>`));
            }).always();
        } else {}
    });

    $('#id_modulo_formativo').on('change', function() {
        const idMod = $(this).val();
        $('#id_semestre').html('<option value="">Todos</option>');
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idMod) {
            $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idMod, ss => {
                ss.forEach(s => $('#id_semestre').append(`<option value="${s.id}">${s.descripcion}</option>`));
            }).always();
        } else {}
    });

    $('#id_semestre').on('change', function() {
        const idSem = $(this).val();
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idSem) {
            $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + idSem, uds => {
                uds.forEach(u => $('#id_unidad_didactica').append(`<option value="${u.id}">${u.nombre}</option>`));
            }).always();
        } else {}
    });
    // carga inicial
</script>