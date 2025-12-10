<?php require __DIR__ . '/../../layouts/header.php'; ?>

<!-- ===== FILTROS ===== -->
<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="mb-0">Búsqueda</h6>
      <div>
        <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary">Limpiar</button>
        <button id="btnApplyFilters" class="btn btn-sm btn-primary">Aplicar</button>
      </div>
    </div>

    <!-- Fila 1: Título / Autor / Temas -->
    <div class="form-row">
      <div class="form-group col-md-12">
        <label class="mb-1">Buscar</label>
        <input id="f_titulo" type="text" class="form-control" placeholder="Puede ser titulo, autor o palabra clave">
      </div>
      <!--<div class="form-group col-md-4">
        <label class="mb-1">Por Autor</label>
        <input id="f_autor" type="text" class="form-control" placeholder="Ej. Pressman, Tanenbaum…">
      </div>
      <div class="form-group col-md-4">
        <label class="mb-1">Por Temas</label>
        <input id="f_temas" type="text" class="form-control" placeholder="Ej. algoritmos, BD, IA…">
      </div>-->
    </div>

    <!-- Fila 2: 5 selects en una sola línea (20% cada uno en ≥lg) -->
    <div class="form-row five-cols">
      <div class="form-group col-12 col-sm-6 col-md-4 col-lg form-col">
        <label class="mb-1">Programa de Estudio *</label>
        <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
          <option value="">Todos</option>
          <?php foreach ($programas as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (!empty($id_programa_selected) && $id_programa_selected == $p['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-12 col-sm-6 col-md-4 col-lg form-col">
        <label class="mb-1">Plan de Estudio *</label>
        <select id="id_plan_estudio" class="form-control">
          <option value="">Todos</option>
        </select>
      </div>
      <div class="form-group col-12 col-sm-6 col-md-4 col-lg form-col">
        <label class="mb-1">Módulo Formativo *</label>
        <select id="id_modulo_formativo" class="form-control">
          <option value="">Todos</option>
        </select>
      </div>
      <div class="form-group col-12 col-sm-6 col-md-4 col-lg form-col">
        <label class="mb-1">Periodo Académico *</label>
        <select id="id_semestre" class="form-control">
          <option value="">Todos</option>
        </select>
      </div>
      <div class="form-group col-12 col-sm-6 col-md-4 col-lg form-col">
        <label class="mb-1">Unidad Didáctica *</label>
        <select id="id_unidad_didactica" class="form-control">
          <option value="">Todos</option>
        </select>
      </div>
    </div>
  </div>
</div>

<style>
  @media (min-width: 992px) {
    .five-cols .form-col {
      flex: 0 0 20%;
      max-width: 20%;
    }
  }

  /* Tarjetas uniformes y botón “Ver” alineado abajo */
  .book-card {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .book-cover-wrap {
    aspect-ratio: 3/4;
    background: #f6f7f9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-bottom: 1px solid #eee;
  }

  .book-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .book-body {
    padding: .6rem .6rem 0;
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }

  .book-title {
    font-weight: 600;
    font-size: .95rem;
    line-height: 1.2;
    max-height: 2.4em;
    overflow: hidden;
  }

  .book-meta {
    font-size: .85rem;
    color: #6c757d;
  }

  .btn-area {
    margin-top: auto;
    padding: .6rem;
  }
</style>

<!-- ===== GRID ===== -->
<div id="libros-grid" class="row"></div>

<!-- ===== PAGINACIÓN ===== -->
<nav class="d-flex justify-content-center my-3">
  <ul id="pager" class="pagination pagination-sm"></ul>
</nav>

<input type="hidden" id="apiKey" value="<?= htmlspecialchars($sistema['token_sistema'] ?? '') ?>">
<input type="hidden" id="apiBase" value="<?= rtrim(API_BASE_URL, '/') ?>/api">
<input type="hidden" id="baseUrl" value="<?= rtrim(BASE_URL, '/') ?>">

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

<script>
  (() => {
    const $apiKey = $('#apiKey');
    const $apiBase = $('#apiBase');
    const BASE = $('#baseUrl').val();

    // ====== Header X-Api-Key solo a /api ======
    $(document).ajaxSend(function(_e, xhr, opts) {
      const base = ($apiBase.val() || '').replace(/\/+$/, '');
      if (opts.url && opts.url.indexOf(base) === 0) {
        const k = ($apiKey.val() || '').trim();
        if (k) xhr.setRequestHeader('X-Api-Key', k);
      }
    });

    // ====== Estado ======
    const state = {
      page: 1,
      per_page: 8,
      search: '',
      id_programa_estudio: '',
      id_plan: '',
      id_modulo_formativo: '',
      id_semestre: '',
      id_unidad_didactica: ''
    };

    // ====== Diccionarios para nombres (como en tu código anterior) ======
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
    // Precarga de programas ya viene en el <select>, llenamos dict
    $('#id_programa_estudios option').each(function() {
      const v = this.value;
      const t = this.textContent.trim();
      if (v) dict.progName.set(String(v), t);
    });

    const esc = s => String(s || '').replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;'
    } [m]));

    // Cargadores en cascada (reutilizando tus endpoints locales)
    function fillSelect($sel, items, textKey, valueKey = 'id', first = 'Todos') {
      $sel.empty().append(`<option value="">${first}</option>`);
      (items || []).forEach(it => $sel.append(`<option value="${it[valueKey]}">${esc(it[textKey]||'')}</option>`));
    }

    function ensurePlanesForProg(idProg) {
      idProg = String(idProg || '');
      if (!idProg || dict.planesByProg.has(idProg)) return Promise.resolve();
      return $.getJSON(`${BASE}/sigi/planes/porPrograma/${encodeURIComponent(idProg)}`)
        .then(arr => {
          dict.planesByProg.set(idProg, arr);
          arr.forEach(pl => dict.planName.set(String(pl.id), pl.nombre));
        });
    }

    function ensureModulosForPlan(idPlan) {
      idPlan = String(idPlan || '');
      if (!idPlan || dict.modsByPlan.has(idPlan)) return Promise.resolve();
      return $.getJSON(`${BASE}/sigi/moduloFormativo/porPlan/${encodeURIComponent(idPlan)}`)
        .then(arr => {
          dict.modsByPlan.set(idPlan, arr);
          arr.forEach(m => dict.modName.set(String(m.id), m.descripcion));
        });
    }

    function ensureSemestresForModulo(idMod) {
      idMod = String(idMod || '');
      if (!idMod || dict.semsByMod.has(idMod)) return Promise.resolve();
      return $.getJSON(`${BASE}/sigi/semestre/porModulo/${encodeURIComponent(idMod)}`)
        .then(arr => {
          dict.semsByMod.set(idMod, arr);
          arr.forEach(s => dict.semName.set(String(s.id), s.descripcion));
        });
    }

    function ensureUDsForSemestre(idSem) {
      idSem = String(idSem || '');
      if (!idSem || dict.udsBySem.has(idSem)) return Promise.resolve();
      return $.getJSON(`${BASE}/sigi/unidadDidactica/porSemestre/${encodeURIComponent(idSem)}`)
        .then(arr => {
          dict.udsBySem.set(idSem, arr);
          arr.forEach(u => dict.udName.set(String(u.id), u.nombre));
        });
    }

    function ensureDictionaries(rows) {
      const needProgForPlans = new Set();
      rows.forEach(r => {
        const v = r.vinculo || {};
        if (v.id_plan && !dict.planName.has(String(v.id_plan)) && v.id_programa_estudio) needProgForPlans.add(String(v.id_programa_estudio));
      });
      return Promise.all([...needProgForPlans].map(ensurePlanesForProg)).then(() => {
        const needPlanForMods = new Set();
        rows.forEach(r => {
          const v = r.vinculo || {};
          if (v.id_modulo_formativo && !dict.modName.has(String(v.id_modulo_formativo)) && v.id_plan) needPlanForMods.add(String(v.id_plan));
        });
        return Promise.all([...needPlanForMods].map(ensureModulosForPlan));
      }).then(() => {
        const needModForSems = new Set();
        rows.forEach(r => {
          const v = r.vinculo || {};
          if (v.id_semestre && !dict.semName.has(String(v.id_semestre)) && v.id_modulo_formativo) needModForSems.add(String(v.id_modulo_formativo));
        });
        return Promise.all([...needModForSems].map(ensureSemestresForModulo));
      }).then(() => {
        const needSemForUDs = new Set();
        rows.forEach(r => {
          const v = r.vinculo || {};
          if (v.id_unidad_didactica && !dict.udName.has(String(v.id_unidad_didactica)) && v.id_semestre) needSemForUDs.add(String(v.id_semestre));
        });
        return Promise.all([...needSemForUDs].map(ensureUDsForSemestre));
      });
    }

    function decorateRows(rows) {
      return rows.map(r => {
        const v = r.vinculo || {};
        return Object.assign({}, r, {
          programa_nombre: dict.progName.get(String(v.id_programa_estudio)) || '',
          ud_nombre: dict.udName.get(String(v.id_unidad_didactica)) || ''
        });
      });
    }

    // ====== Render de tarjetas y paginación ======
    function cardTpl(b) {
      const cover = b.portada_url || '<?= BASE_URL ?>/assets/img/book-placeholder.png';
      const title = esc(b.titulo || '');
      const autor = esc(b.autor || '—');
      const prog = esc(b.programa_nombre || '—');
      const ud = esc(b.ud_nombre || '—');
      const href = `${BASE}/biblioteca/libros/ver/${encodeURIComponent(b.id)}`; // <-- ajusta si tu ruta es distinta
      return `
      <div class="col-12 col-sm-6 col-lg-3 mb-3 d-flex">
        <div class="card book-card shadow-sm w-100">
        <a href='${href}'>
          <div class="book-cover-wrap">
            <img class="book-cover" loading="lazy" src="${cover}" alt="${title}">
          </div>
          </a>
          <div class="book-body">
            <div class="book-title" title="${title}">${title}</div>
            <div class="book-meta"><strong>Autor:</strong> ${autor}</div>
            <div class="book-meta"><strong>Programa:</strong> ${prog}</div>
            <div class="book-meta"><strong>UD:</strong> ${ud}</div>
          </div>
          <div class="btn-area">
            <a class="btn btn-primary btn-sm btn-block" href="${href}">Ver</a>
          </div>
        </div>
      </div>`;
    }

    function buildPager(total, page, per) {
      const $p = $('#pager').empty();
      const pages = Math.max(1, Math.ceil(total / per));
      if (pages <= 1) return;

      function li(lbl, target, disabled = false, active = false) {
        const cls = ['page-item'];
        if (disabled) cls.push('disabled');
        if (active) cls.push('active');
        return $(`<li class="${cls.join(' ')}"><a class="page-link" href="#" data-page="${target}">${lbl}</a></li>`);
      }
      $p.append(li('Inicio', 1, page === 1));
      $p.append(li('Anterior', page - 1, page === 1));

      const span = 2;
      let from = Math.max(1, page - span);
      let to = Math.min(pages, page + span);
      if (to - from < span * 2) {
        if (from === 1) to = Math.min(pages, from + span * 2);
        else if (to === pages) from = Math.max(1, to - span * 2);
      }
      for (let i = from; i <= to; i++) $p.append(li(String(i), i, false, i === page));

      $p.append(li('Siguiente', page + 1, page === pages));
      $p.append(li('Final', pages, page === pages));

      $p.find('a[data-page]').on('click', function(e) {
        e.preventDefault();
        const p = parseInt($(this).data('page'), 10);
        if (!isNaN(p) && p >= 1 && p <= pages && p !== page) {
          state.page = p;
          loadPage();
        }
      });
    }

    // ====== Carga de página desde la API ======
    function buildQuery(obj) {
      return Object.entries(obj)
        .filter(([, v]) => v !== '' && v != null)
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('&');
    }
    async function loadPage() {
      const baseApi = ($apiBase.val() || '').replace(/\/+$/, '');
      const params = {
        page: state.page,
        per_page: state.per_page,
        search: state.search || undefined,
        id_programa_estudio: state.id_programa_estudio || undefined,
        id_plan: state.id_plan || undefined,
        id_modulo_formativo: state.id_modulo_formativo || undefined,
        id_semestre: state.id_semestre || undefined,
        id_unidad_didactica: state.id_unidad_didactica || undefined
      };
      const qs = buildQuery(params);

      const $grid = $('#libros-grid').html('<div class="col-12 text-center py-5"><div class="spinner-border" role="status"></div></div>');
      try {
        const resp = await $.getJSON(`${baseApi}/library/adopted${qs?`?${qs}`:''}`);
        const rows = resp?.data || [];
        await ensureDictionaries(rows);
        const decorated = decorateRows(rows);

        const total = Number(resp?.pagination?.total ?? rows.length);
        $grid.empty();
        if (!decorated.length) {
          $grid.html('<div class="col-12 text-center text-muted py-4">Sin resultados.</div>');
        } else {
          $grid.append(decorated.map(cardTpl).join(''));
        }
        buildPager(total, state.page, state.per_page);
      } catch (e) {
        console.error('[SIGI] adopted grid error', e);
        $grid.html('<div class="col-12 text-center text-danger py-4">Error cargando libros.</div>');
        $('#pager').empty();
      }
    }

    // ====== Eventos: filtros dependientes ======
    $('#id_programa_estudios').on('change', function() {
      state.id_programa_estudio = $(this).val() || '';
      state.id_plan = state.id_modulo_formativo = state.id_semestre = state.id_unidad_didactica = '';
      fillSelect($('#id_plan_estudio'), [], 'nombre');
      fillSelect($('#id_modulo_formativo'), [], 'descripcion');
      fillSelect($('#id_semestre'), [], 'descripcion');
      fillSelect($('#id_unidad_didactica'), [], 'nombre');

      const idProg = state.id_programa_estudio;
      if (idProg) {
        $.getJSON(`${BASE}/sigi/planes/porPrograma/${encodeURIComponent(idProg)}`, pl => {
          fillSelect($('#id_plan_estudio'), pl, 'nombre', 'id', 'Todos');
        }).always(() => {
          state.page = 1;
          loadPage();
        });
      } else {
        state.page = 1;
        loadPage();
      }
    });

    $('#id_plan_estudio').on('change', function() {
      state.id_plan = $(this).val() || '';
      state.id_modulo_formativo = state.id_semestre = state.id_unidad_didactica = '';
      fillSelect($('#id_modulo_formativo'), [], 'descripcion');
      fillSelect($('#id_semestre'), [], 'descripcion');
      fillSelect($('#id_unidad_didactica'), [], 'nombre');

      const idPlan = state.id_plan;
      if (idPlan) {
        $.getJSON(`${BASE}/sigi/moduloFormativo/porPlan/${encodeURIComponent(idPlan)}`, ms => {
          fillSelect($('#id_modulo_formativo'), ms, 'descripcion', 'id', 'Todos');
        }).always(() => {
          state.page = 1;
          loadPage();
        });
      } else {
        state.page = 1;
        loadPage();
      }
    });

    $('#id_modulo_formativo').on('change', function() {
      state.id_modulo_formativo = $(this).val() || '';
      state.id_semestre = state.id_unidad_didactica = '';
      fillSelect($('#id_semestre'), [], 'descripcion');
      fillSelect($('#id_unidad_didactica'), [], 'nombre');

      const idMod = state.id_modulo_formativo;
      if (idMod) {
        $.getJSON(`${BASE}/sigi/semestre/porModulo/${encodeURIComponent(idMod)}`, ss => {
          fillSelect($('#id_semestre'), ss, 'descripcion', 'id', 'Todos');
        }).always(() => {
          state.page = 1;
          loadPage();
        });
      } else {
        state.page = 1;
        loadPage();
      }
    });

    $('#id_semestre').on('change', function() {
      state.id_semestre = $(this).val() || '';
      state.id_unidad_didactica = '';
      fillSelect($('#id_unidad_didactica'), [], 'nombre');

      const idSem = state.id_semestre;
      if (idSem) {
        $.getJSON(`${BASE}/sigi/unidadDidactica/porSemestre/${encodeURIComponent(idSem)}`, uds => {
          fillSelect($('#id_unidad_didactica'), uds, 'nombre', 'id', 'Todos');
        }).always(() => {
          state.page = 1;
          loadPage();
        });
      } else {
        state.page = 1;
        loadPage();
      }
    });

    $('#id_unidad_didactica').on('change', function() {
      state.id_unidad_didactica = $(this).val() || '';
      state.page = 1;
      loadPage();
    });

    // ====== Búsqueda texto (Título/Autor/Temas) ======
    function rebuildSearch() {
      const t = $('#f_titulo').val().trim();
      //const a = $('#f_autor').val().trim();
      //const m = $('#f_temas').val().trim();
      state.search = [t].filter(Boolean).join(' ');
    }
    let tmr;
    const deb = fn => {
      clearTimeout(tmr);
      tmr = setTimeout(fn, 300);
    };
    $('#f_titulo').on('input', () => deb(() => {
      rebuildSearch();
      state.page = 1;
      loadPage();
    }));

    $('#btnApplyFilters').on('click', () => {
      rebuildSearch();
      state.page = 1;
      loadPage();
    });
    $('#btnClearFilters').on('click', () => {
      $('#f_titulo').val('');
      $('#id_programa_estudios').val('');
      fillSelect($('#id_plan_estudio'), [], 'nombre');
      fillSelect($('#id_modulo_formativo'), [], 'descripcion');
      fillSelect($('#id_semestre'), [], 'descripcion');
      fillSelect($('#id_unidad_didactica'), [], 'nombre');
      state.page = 1;
      state.search = '';
      state.id_programa_estudio = state.id_plan = state.id_modulo_formativo = state.id_semestre = state.id_unidad_didactica = '';
      loadPage();
    });

    // ====== Carga inicial ======
    rebuildSearch();
    loadPage();
  })();
</script>