<?php require __DIR__ . '/../../layouts/header.php'; ?>
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

    // Inyecta X-Api-Key a llamadas al Maestro
    $(document).ajaxSend(function(_e, xhr, opts) {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        if (opts.url && opts.url.indexOf(base) === 0) {
            const k = ($apiKey.val() || '').trim();
            if (k) xhr.setRequestHeader('X-Api-Key', k);
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

    /* =========================
       Diccionarios locales (Sigi)
       ========================= */
    const dict = {
        progName: new Map(), // id_prog -> nombre
        planName: new Map(), // id_plan -> nombre
        modName: new Map(), // id_mod  -> descripcion
        semName: new Map(), // id_sem  -> descripcion
        udName: new Map(), // id_ud   -> nombre
        planesByProg: new Map(), // id_prog -> []
        modsByPlan: new Map(), // id_plan -> []
        semsByMod: new Map(), // id_mod  -> []
        udsBySem: new Map(), // id_sem  -> []
    };

    // Pre-carga programas desde PHP (si vienen en la vista)
    window.PROGS_FROM_PHP = window.PROGS_FROM_PHP ||
        <?php if (!empty($programas)) {
            echo json_encode(array_map(fn($p) => ['id' => $p['id'], 'nombre' => $p['nombre']], $programas), JSON_UNESCAPED_UNICODE);
        } else {
            echo '[]';
        } ?>;

    // Ya puedes usarlo:
    window.PROGS_FROM_PHP.forEach(p => dict.progName.set(String(p.id), p.nombre));

    function ensurePlanesForProg(idProg) {
        idProg = String(idProg || '');
        if (!idProg || dict.planesByProg.has(idProg)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + encodeURIComponent(idProg))
            .then(arr => {
                dict.planesByProg.set(idProg, arr);
                arr.forEach(pl => dict.planName.set(String(pl.id), pl.nombre));
            }).catch(() => {
                /* noop */
            });
    }

    function ensureModulosForPlan(idPlan) {
        idPlan = String(idPlan || '');
        if (!idPlan || dict.modsByPlan.has(idPlan)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + encodeURIComponent(idPlan))
            .then(arr => {
                dict.modsByPlan.set(idPlan, arr);
                arr.forEach(m => dict.modName.set(String(m.id), m.descripcion));
            }).catch(() => {
                /* noop */
            });
    }

    function ensureSemestresForModulo(idMod) {
        idMod = String(idMod || '');
        if (!idMod || dict.semsByMod.has(idMod)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + encodeURIComponent(idMod))
            .then(arr => {
                dict.semsByMod.set(idMod, arr);
                arr.forEach(s => dict.semName.set(String(s.id), s.descripcion));
            }).catch(() => {
                /* noop */
            });
    }

    function ensureUDsForSemestre(idSem) {
        idSem = String(idSem || '');
        if (!idSem || dict.udsBySem.has(idSem)) return Promise.resolve();
        return $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + encodeURIComponent(idSem))
            .then(arr => {
                dict.udsBySem.set(idSem, arr);
                arr.forEach(u => dict.udName.set(String(u.id), u.nombre));
            }).catch(() => {
                /* noop */
            });
    }

    // Carga en lote los diccionarios necesarios para un conjunto de filas
    function ensureDictionaries(rows) {
        // 1) Asegurar PLANES por PROGRAMA (si falta el nombre del plan del row)
        const needProgForPlans = new Set();
        rows.forEach(r => {
            const v = r.vinculo || {};
            if (v.id_plan && !dict.planName.has(String(v.id_plan)) && v.id_programa_estudio) {
                needProgForPlans.add(String(v.id_programa_estudio));
            }
        });
        const p1 = Promise.all(Array.from(needProgForPlans).map(ensurePlanesForProg));

        // 2) Asegurar MODULOS por PLAN (si falta el nombre del módulo del row)
        return p1.then(() => {
                const needPlanForMods = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_modulo_formativo && !dict.modName.has(String(v.id_modulo_formativo)) && v.id_plan) {
                        needPlanForMods.add(String(v.id_plan));
                    }
                });
                return Promise.all(Array.from(needPlanForMods).map(ensureModulosForPlan));
            })
            // 3) Asegurar SEMESTRES por MODULO (si falta el nombre del semestre del row)
            .then(() => {
                const needModForSems = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_semestre && !dict.semName.has(String(v.id_semestre)) && v.id_modulo_formativo) {
                        needModForSems.add(String(v.id_modulo_formativo));
                    }
                });
                return Promise.all(Array.from(needModForSems).map(ensureSemestresForModulo));
            })
            // 4) Asegurar UDs por SEMESTRE (si falta el nombre de la UD del row)
            .then(() => {
                const needSemForUDs = new Set();
                rows.forEach(r => {
                    const v = r.vinculo || {};
                    if (v.id_unidad_didactica && !dict.udName.has(String(v.id_unidad_didactica)) && v.id_semestre) {
                        needSemForUDs.add(String(v.id_semestre));
                    }
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
                ud_nombre: dict.udName.get(String(v.id_unidad_didactica)) || '',
            });
        });
    }

    /* ================
       DataTable render
       ================ */
    function renderTable($table, rows) {
        if (dtAdopt) {
            dtAdopt.clear();
            dtAdopt.rows.add(rows).draw(false);
            return dtAdopt;
        }
        dtAdopt = $table.DataTable({
            data: rows,
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
                    render: r => r.portada_url ? `<a href="${r.portada_url}" target="_blank">Ver</a>` : ''
                },
                {
                    data: null,
                    orderable: false,
                    render: r => `<a href="${r.archivo_url}" target="_blank">Ver</a>`
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
                        return `
            <div class="btn-group btn-group-sm" role="group">
              
              <button class="btn btn-danger btn-unadopt m-1" ${attrs}>Desvincular</button>
            </div>`;
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            }, // usa copia local
            pageLength: 10,
            ordering: true
        });
        return dtAdopt;
    }

    /* ============
       Cargar datos
       ============ */
    function getFilters() {
        return {
            id_programa_estudio: $('#id_programa_estudios').val() || '',
            id_plan: $('#id_plan_estudio').val() || '',
            id_modulo_formativo: $('#id_modulo_formativo').val() || '',
            id_semestre: $('#id_semestre').val() || '',
            id_unidad_didactica: $('#id_unidad_didactica').val() || ''
        };
    }

    async function cargarVinculados() {
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const f = getFilters();
        const qs = Object.entries(f).filter(([, v]) => v).map(([k, v]) => `${k}=${encodeURIComponent(v)}`).join('&');
        const url = `${base}/library/adopted` + (qs ? `?${qs}` : '');
        const data = await $.getJSON(url);
        const rows = data?.data || [];

        // estos usan r.vinculo.*  → si el API no lo trae, nombres quedan vacíos
        await ensureDictionaries(rows);
        const decorated = decorateRows(rows);
        renderTable($('#tbl-adoptados'), decorated);
    }

    /* ==========================
       Desadoptar (con la cadena)
       ========================== */
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

    // util para escapar HTML en títulos/textos
    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        } [m]))
    }

    $(document).on('click', '.btn-unadopt', async function(e) {
        e.preventDefault();
        await ensureSweetAlert();

        const $btn = $(this);
        const base = ($apiBase.val() || '').replace(/\/+$/, '');
        const b = this.dataset;
        const libroId = b.id;

        // tomar el título desde la fila (si DataTable está inicializado)
        const row = (window.dtAdopt ? dtAdopt.row($btn.closest('tr')).data() : null);
        const titulo = row?.titulo || 'este libro';

        // ¿tienes la cadena académica en data-*?
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

        // loading mientras ejecuta
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

            // refresca solo la fila si es posible
            if (window.dtAdopt) {
                dtAdopt.row($btn.closest('tr')).remove().draw(false);
            } else {
                cargarVinculados();
            }

        } catch (err) {
            const msg = err?.responseJSON?.error?.message || err.statusText || 'Error';
            Swal.fire({
                icon: 'error',
                title: 'No se pudo desadoptar',
                text: msg
            });
        }
    });
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
            }).always(cargarVinculados);
        } else {
            cargarVinculados();
        }
    });

    $('#id_plan_estudio').on('change', function() {
        const idPlan = $(this).val();
        $('#id_modulo_formativo').html('<option value="">Todos</option>');
        $('#id_semestre').html('<option value="">Todos</option>');
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + idPlan, ms => {
                ms.forEach(m => $('#id_modulo_formativo').append(`<option value="${m.id}">${m.descripcion}</option>`));
            }).always(cargarVinculados);
        } else {
            cargarVinculados();
        }
    });

    $('#id_modulo_formativo').on('change', function() {
        const idMod = $(this).val();
        $('#id_semestre').html('<option value="">Todos</option>');
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idMod) {
            $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + idMod, ss => {
                ss.forEach(s => $('#id_semestre').append(`<option value="${s.id}">${s.descripcion}</option>`));
            }).always(cargarVinculados);
        } else {
            cargarVinculados();
        }
    });

    $('#id_semestre').on('change', function() {
        const idSem = $(this).val();
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (idSem) {
            $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + idSem, uds => {
                uds.forEach(u => $('#id_unidad_didactica').append(`<option value="${u.id}">${u.nombre}</option>`));
            }).always(cargarVinculados);
        } else {
            cargarVinculados();
        }
    });

    $('#id_unidad_didactica').on('change', cargarVinculados);

    // carga inicial
    window.addEventListener('load', cargarVinculados);
</script>