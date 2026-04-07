<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="card p-2">
    <label class="form-label font-weight-bold">Vincular Libro a Unidad Didáctica:</label>
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
            <input type="text" id="busq-q" class="form-control" placeholder="título, autor o temas...">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button id="btnBuscar" class="btn btn-primary btn-block">Buscar</button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tbl-buscar" class="table table-bordered table-hover table-sm" width="100%">
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
$(document).ready(function() {
    const $apiKey = $('#apiKey').val();
    const $apiBase = $('#apiBase').val().replace(/\/+$/, '');
    let dtBuscar = null;

    // Configuración global de Ajax
    $.ajaxSetup({
        beforeSend: function(xhr, opts) {
            if (opts.url.indexOf($apiBase) === 0 && $apiKey) {
                xhr.setRequestHeader('X-Api-Key', $apiKey);
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

    function getChain() {
        return {
            id_programa_estudio: $('#id_programa_estudios').val() || '',
            id_plan: $('#id_plan_estudio').val() || '',
            id_modulo_formativo: $('#id_modulo_formativo').val() || '',
            id_semestre: $('#id_semestre').val() || '',
            id_unidad_didactica: $('#id_unidad_didactica').val() || ''
        };
    }

    function validateChainOrWarn() {
        const chain = getChain();
        if (!chain.id_programa_estudio || !chain.id_plan || !chain.id_modulo_formativo || !chain.id_semestre || !chain.id_unidad_didactica) {
            const msg = 'Seleccione todos los filtros (Programa, Plan, Módulo, Periodo y UD) antes de buscar.';
            if (window.Swal) Swal.fire('Atención', msg, 'warning');
            else alert(msg);
            return null;
        }
        return chain;
    }

    // Inicialización del DataTable modo Server-Side
    function initDataTable() {
        dtBuscar = $('#tbl-buscar').DataTable({
            serverSide: true,
            processing: true,
            searching: false,
            ordering: false,
            pageLength: 10,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            ajax: async function(dtReq, callback) {
                const chain = validateChainOrWarn();
                if (!chain) {
                    callback({ draw: dtReq.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                    return;
                }

                const page = Math.floor(dtReq.start / dtReq.length) + 1;
                const searchQ = $('#busq-q').val().trim();

                try {
                    // 1. Obtener libros ya adoptados para esta UD (para filtrar)
                    const adoptedRes = await $.getJSON(`${$apiBase}/library/adopted?${$.param(chain)}`);
                    const adoptedIds = new Set((adoptedRes.data || []).map(x => x.id));

                    // 2. Buscar libros en el maestro
                    const res = await $.getJSON(`${$apiBase}/library/search`, {
                        search: searchQ,
                        page: page,
                        per_page: dtReq.length
                    });

                    const allRows = res.data || [];
                    // Filtrar los que ya están vinculados
                    const filteredRows = allRows.filter(r => !adoptedIds.has(r.id));
                    const total = res.pagination?.total_records || allRows.length;

                    callback({
                        draw: dtReq.draw,
                        data: filteredRows,
                        recordsTotal: total,
                        recordsFiltered: total
                    });
                } catch (err) {
                    console.error("Error en búsqueda:", err);
                    callback({ draw: dtReq.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                }
            },
            columns: [
                { data: 'id' },
                { data: 'titulo', render: d => `<span class="font-weight-bold">${d}</span>` },
                { data: 'autor' },
                { data: 'tipo_libro' },
                { data: 'anio' },
                { 
                    data: null, 
                    render: r => r.portada_url ? `<a href="${r.portada_url}" target="_blank" class="btn btn-xs btn-outline-info">Ver</a>` : '' 
                },
                { 
                    data: null, 
                    render: r => `<a href="${r.archivo_url}" target="_blank" class="btn btn-xs btn-outline-success">Archivo</a>` 
                },
                { 
                    data: null, 
                    render: r => `<button class="btn btn-sm btn-primary btn-adoptar" data-id="${r.id}">Vincular</button>` 
                }
            ]
        });
    }

    $('#btnBuscar').on('click', function() {
        if (!dtBuscar) initDataTable();
        else dtBuscar.ajax.reload();
    });

    // Acción de Vincular
    $(document).on('click', '.btn-adoptar', async function() {
        const idLibro = this.dataset.id;
        const chain = validateChainOrWarn();
        if (!chain) return;

        const confirmAdopt = async () => {
            if (window.Swal) {
                const res = await Swal.fire({
                    title: '¿Vincular libro?',
                    text: "Se asociará este libro a la Unidad Didáctica seleccionada.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, vincular'
                });
                return res.isConfirmed;
            }
            return confirm("¿Desea vincular este libro?");
        };

        if (!(await confirmAdopt())) return;

        const $btn = $(this).prop('disabled', true).text('Procesando...');

        try {
            await $.ajax({
                url: `${$apiBase}/library/adopt/${idLibro}`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(chain),
                beforeSend: (xhr) => xhr.setRequestHeader('X-Idempotency-Key', cryptoRandom())
            });

            if (window.Swal) Swal.fire('Éxito', 'Libro vinculado correctamente.', 'success');
            dtBuscar.ajax.reload(null, false); // Refresca la tabla y desaparece el libro vinculado
        } catch (err) {
            const msg = err?.responseJSON?.error?.message || 'Error al vincular';
            if (window.Swal) Swal.fire('Error', msg, 'error');
            else alert(msg);
            $btn.prop('disabled', false).text('Vincular');
        }
    });

    // Filtros Dependientes
    $('#id_programa_estudios').on('change', function() {
        const id = $(this).val();
        $('#id_plan_estudio, #id_modulo_formativo, #id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (id) $.getJSON('<?= BASE_URL ?>/sigi/planes/porPrograma/' + id, data => {
            data.forEach(x => $('#id_plan_estudio').append(`<option value="${x.id}">${x.nombre}</option>`));
        });
    });

    $('#id_plan_estudio').on('change', function() {
        const id = $(this).val();
        $('#id_modulo_formativo, #id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (id) $.getJSON('<?= BASE_URL ?>/sigi/moduloFormativo/porPlan/' + id, data => {
            data.forEach(x => $('#id_modulo_formativo').append(`<option value="${x.id}">${x.descripcion}</option>`));
        });
    });

    $('#id_modulo_formativo').on('change', function() {
        const id = $(this).val();
        $('#id_semestre, #id_unidad_didactica').html('<option value="">Todos</option>');
        if (id) $.getJSON('<?= BASE_URL ?>/sigi/semestre/porModulo/' + id, data => {
            data.forEach(x => $('#id_semestre').append(`<option value="${x.id}">${x.descripcion}</option>`));
        });
    });

    $('#id_semestre').on('change', function() {
        const id = $(this).val();
        $('#id_unidad_didactica').html('<option value="">Todos</option>');
        if (id) $.getJSON('<?= BASE_URL ?>/sigi/unidadDidactica/porSemestre/' + id, data => {
            data.forEach(x => $('#id_unidad_didactica').append(`<option value="${x.id}">${x.nombre}</option>`));
        });
    });
});
</script>