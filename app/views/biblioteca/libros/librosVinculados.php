<?php require __DIR__ . '/../../layouts/header.php'; ?>

<style>
    /* Evita que el tema esconda los números de la paginación */
    .dataTables_wrapper .dataTables_paginate .pagination .page-item,
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-block !important;
        visibility: visible !important;
    }
</style>

<div class="card p-2">
    <div class="mr-3">
        <a href="<?= BASE_URL . '/biblioteca/libros/vincular' ?>" class="btn btn-primary mt-4">Nuevo +</a>
    </div>
    Filtros:
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
    <br>
    <div class="table-responsive">
        <table id="tbl-adoptados" class="table table-bordered table-hover table-sm" width="100%">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Programa de Estudios</th>
                    <th>Plan de Estudios</th>
                    <th>Módulo Formativo</th>
                    <th>Semestre</th>
                    <th>Unidad Didáctica</th>
                    <th>Portada</th>
                    <th>Archivo</th>
                    <th>Opciones</th>
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
    let dtAdopt = null;

    // Inyecta X-Api-Key a llamadas al Maestro (seguirá habiendo preflight por header custom)
    $(document).ajaxSend(function(_e, xhr, opts) {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        if (opts.url && opts.url.indexOf(base) === 0) {
            const k = ($apiKey.val() || '').trim();
            if (!k) {
                console.warn('[SIGI] Falta X-Api-Key, abort:', opts.url);
                xhr.abort();
                return;
            }
            xhr.setRequestHeader('X-Api-Key', k);
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

    // Diccionarios locales para nombres
    const dict = {
        progName: new Map(),
        planName: new Map(),
        modName: new Map(),
        semName: new Map(),
        udName: new Map(),
        planesByProg: new Map(),
        modsByPlan: new Map(),
        semsByMod: new Map(),
        udsBySem: new Map(),
    };

    // Precarga programas desde PHP
    window.PROGS_FROM_PHP = window.PROGS_FROM_PHP ||
        <?php if (!empty($programas)) {
            echo json_encode(array_map(fn($p) => ['id' => $p['id'], 'nombre' => $p['nombre']], $programas), JSON_UNESCAPED_UNICODE);
        } else {
            echo '[]';
        } ?>;
    window.PROGS_FROM_PHP.forEach(p => dict.progName.set(String(p.id), p.nombre));

    function ensurePlanesForProg(idProg) {
        idProg = String(idProg || '');
        if (!idProg || dict.planesByProg.has(idProg)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + encodeURIComponent(idProg))
            .then(arr => {
                dict.planesByProg.set(idProg, arr);
                arr.forEach(pl => dict.planName.set(String(pl.id), pl.nombre));
            })
            .catch(err => console.warn('[SIGI] planes/porPrograma fallo', err));
    }

    function ensureModulosForPlan(idPlan) {
        idPlan = String(idPlan || '');
        if (!idPlan || dict.modsByPlan.has(idPlan)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + encodeURIComponent(idPlan))
            .then(arr => {
                dict.modsByPlan.set(idPlan, arr);
                arr.forEach(m => dict.modName.set(String(m.id), m.descripcion));
            })
            .catch(err => console.warn('[SIGI] moduloFormativo/porPlan fallo', err));
    }

    function ensureSemestresForModulo(idMod) {
        idMod = String(idMod || '');
        if (!idMod || dict.semsByMod.has(idMod)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + encodeURIComponent(idMod))
            .then(arr => {
                dict.semsByMod.set(idMod, arr);
                arr.forEach(s => dict.semName.set(String(s.id), s.descripcion));
            })
            .catch(err => console.warn('[SIGI] semestre/porModulo fallo', err));
    }

    function ensureUDsForSemestre(idSem) {
        idSem = String(idSem || '');
        if (!idSem || dict.udsBySem.has(idSem)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + encodeURIComponent(idSem))
            .then(arr => {
                dict.udsBySem.set(idSem, arr);
                arr.forEach(u => dict.udName.set(String(u.id), u.nombre));
            })
            .catch(err => console.warn('[SIGI] unidadDidactica/porSemestre fallo', err));
    }

    function ensureDictionaries(rows) {
        const needProgForPlans = new Set();
        rows.forEach(r => {
            const v = r.vinculo || {};
            if (v.id_plan && !dict.planName.has(String(v.id_plan)) && v.id_programa_estudio) needProgForPlans.add(String(v.id_programa_estudio));
        });
        return Promise.all(Array.from(needProgForPlans).map(ensurePlanesForProg))
            .then(() => {
                const needPlanForMods = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_modulo_formativo && !dict.modName.has(String(v.id_modulo_formativo)) && v.id_plan) needPlanForMods.add(String(v.id_plan));
                });
                return Promise.all(Array.from(needPlanForMods).map(ensureModulosForPlan));
            }).then(() => {
                const needModForSems = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_semestre && !dict.semName.has(String(v.id_semestre)) && v.id_modulo_formativo) needModForSems.add(String(v.id_modulo_formativo));
                });
                return Promise.all(Array.from(needModForSems).map(ensureSemestresForModulo));
            }).then(() => {
                const needSemForUDs = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_unidad_didactica && !dict.udName.has(String(v.id_unidad_didactica)) && v.id_semestre) needSemForUDs.add(String(v.id_semestre));
                });
                return Promise.all(Array.from(needSemForUDs).map(ensureUDsForSemestre));
            });
    }

    function decorateRows(rows) {
        return rows.map(r => {
            const v = r.vinculo || {};
            return Object.assign({}, r, {
                programa_nombre: dict.progName.get(String(v.id_programa_estudio)) || '',
                plan_nombre: dict.planName.get(String(v.id_plan)) || '',
                modulo_desc: dict.modName.get(String(v.id_modulo_formativo)) || '',
                semestre_desc: dict.semName.get(String(v.id_semestre)) || '',
                ud_nombre: dict.udName.get(String(v.id_unidad_didactica)) || ''
            });
        });
    }

    function buildQuery(obj) {
        return Object.entries(obj)
            .filter(([, v]) => v !== '' && v != null)
            .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
            .join('&');
    }

    function getFilters() {
        return {
            id_programa_estudio: $('#id_programa_estudios').val() || '',
            id_plan: $('#id_plan_estudio').val() || '',
            id_modulo_formativo: $('#id_modulo_formativo').val() || '',
            id_semestre: $('#id_semestre').val() || '',
            id_unidad_didactica: $('#id_unidad_didactica').val() || ''
        };
    }

    // --- DataTables serverSide: solo carga página visible ---
    function initTable() {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');

        // 1) Si ya existe, destruye antes de crear (evita estados viejos con pages=1)
        if ($.fn.DataTable.isDataTable('#tbl-adoptados')) {
            $('#tbl-adoptados').DataTable().destroy();
            $('#tbl-adoptados tbody').empty();
        }

        dtAdopt = $('#tbl-adoptados').DataTable({
            serverSide: true,
            processing: true,
            searching: false,
            ordering: false,
            paging: true,
            info: true,
            deferRender: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'full_numbers',
            // 2) Asegura que el contenedor de paginación aparezca
            dom: 'lfrtip',
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            },

            ajax: async function(dtReq, callback) {
                const page = Math.floor(dtReq.start / dtReq.length) + 1;
                const per_page = dtReq.length;
                const qs = buildQuery({
                    ...getFilters(),
                    page,
                    per_page
                });

                try {
                    const resp = await $.getJSON(`${base}/library/adopted${qs ? `?${qs}` : ''}`);
                    const rows = resp?.data || [];
                    await ensureDictionaries(rows);
                    const decorated = decorateRows(rows);

                    const total = Number(resp?.pagination?.total ?? 0);

                    callback({
                        draw: dtReq.draw, // requerido por DataTables
                        data: decorated,
                        // ojo: DataTables usa recordsFiltered para paginar
                        recordsTotal: total,
                        recordsFiltered: total
                    });

                    // Debug útil: revisa pages > 1
                    setTimeout(() => {
                        const info = $('#tbl-adoptados').DataTable().page.info();
                        //console.log('page.info()', info);
                    }, 0);

                } catch (e) {
                    console.error(e);
                    callback({
                        draw: dtReq.draw,
                        data: [],
                        recordsTotal: 0,
                        recordsFiltered: 0
                    });
                }
            },

            columns: [{
                    data: null,
                    orderable: false,
                    render: (_d, _t, _r, meta) => meta.row + 1 + meta.settings._iDisplayStart
                },
                {
                    data: 'titulo'
                },
                {
                    data: 'programa_nombre'
                },
                {
                    data: 'plan_nombre'
                },
                {
                    data: 'modulo_desc'
                },
                {
                    data: 'semestre_desc'
                },
                {
                    data: 'ud_nombre'
                },
                {
                    data: null,
                    orderable: false,
                    render: r => r.portada_url ? `<a href="${r.portada_url}" target="_blank" rel="noopener">Ver</a>` : ''
                },
                {
                    data: null,
                    orderable: false,
                    render: r => r.archivo_url ? `<a href="${r.archivo_url}" target="_blank" rel="noopener">Ver</a>` : ''
                },
                {
                    data: null,
                    orderable: false,
                    render: r => {
                        const v = r.vinculo || {};
                        const attrs = [
                            `data-id="${r.id}"`,
                            `data-prog="${v.id_programa_estudio||''}"`,
                            `data-plan="${v.id_plan||''}"`,
                            `data-mod="${v.id_modulo_formativo||''}"`,
                            `data-sem="${v.id_semestre||''}"`,
                            `data-ud="${v.id_unidad_didactica||''}"`
                        ].join(' ');
                        return `<div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-danger btn-unadopt m-1" ${attrs}>Desvincular</button>
                  </div>`;
                    }
                }
            ]
        });

        window.dtAdopt = dtAdopt;
    }



    // SweetAlert util
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

    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        } [m]));
    }

    // Desvincular → refresca SOLO la página actual
    $(document).on('click', '.btn-unadopt', async function(e) {
        e.preventDefault();
        await ensureSweetAlert();

        const $btn = $(this);
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const b = this.dataset;
        const libroId = b.id;

        const row = dtAdopt ? dtAdopt.row($btn.closest('tr')).data() : null;
        const titulo = row?.titulo || 'este libro';

        const hasChain = b.prog && b.plan && b.mod && b.sem && b.ud;
        const postData = hasChain ? {
            id_programa_estudio: b.prog,
            id_plan: b.plan,
            id_modulo_formativo: b.mod,
            id_semestre: b.sem,
            id_unidad_didactica: b.ud
        } : {};

        const confirm = await Swal.fire({
            title: '¿Desvincular libro?',
            html: `Vas a desvincular <strong>${esc(titulo)}</strong>. Esta acción es irreversible.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desvincular',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            await $.ajax({
                url: `${base}/library/unadopt/${libroId}`,
                method: 'POST',
                data: postData,
                beforeSend: (xhr) => xhr.setRequestHeader('X-Idempotency-Key', cryptoRandom())
            });

            await Swal.fire({
                icon: 'success',
                title: 'Listo',
                text: 'Se desadoptó el libro.',
                timer: 1400,
                showConfirmButton: false
            });

            // refresca esta página de la tabla (sin ir a la primera)
            dtAdopt?.ajax.reload(null, false);
        } catch (err) {
            const msg = err?.responseJSON?.error?.message || err.statusText || 'Error';
            Swal.fire({
                icon: 'error',
                title: 'No se pudo desadoptar',
                text: msg
            });
        }
    });

    // ---- Filtros dependientes ----
    const reloadDebounced = (() => {
        let t;
        return () => {
            clearTimeout(t);
            t = setTimeout(() => dtAdopt?.ajax.reload(null, true), 160);
        };
    })();

    $('#id_programa_estudios').on('change', function() {
        const idPrograma = $(this).val();
        $('#id_plan_estudio, #id_modulo_formativo, #id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + idPrograma, pl => {
                pl.forEach(x => $('#id_plan_estudio').append(`<option value="${x.id}">${x.nombre}</option>`));
            }).always(reloadDebounced);
        } else reloadDebounced();
    });

    $('#id_plan_estudio').on('change', function() {
        const idPlan = $(this).val();
        $('#id_modulo_formativo, #id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, ms => {
                ms.forEach(m => $('#id_modulo_formativo').append(`<option value="${m.id}">${m.descripcion}</option>`));
            }).always(reloadDebounced);
        } else reloadDebounced();
    });

    $('#id_modulo_formativo').on('change', function() {
        const idMod = $(this).val();
        $('#id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (idMod) {
            $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idMod, ss => {
                ss.forEach(s => $('#id_semestre').append(`<option value="${s.id}">${s.descripcion}</option>`));
            }).always(reloadDebounced);
        } else reloadDebounced();
    });

    $('#id_semestre').on('change', function() {
        const idSem = $(this).val();
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idSem) {
            $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + idSem, uds => {
                uds.forEach(u => $('#id_unidad_didactica').append(`<option value="${u.id}">${u.nombre}</option>`));
            }).always(reloadDebounced);
        } else reloadDebounced();
    });

    $('#id_unidad_didactica').on('change', reloadDebounced);

    // Carga inicial
    window.addEventListener('load', initTable);
</script>