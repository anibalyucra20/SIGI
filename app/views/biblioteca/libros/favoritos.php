<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Mis favoritos</h5>
  <a href="<?= BASE_URL ?>/biblioteca/busqueda" class="btn btn-sm btn-outline-secondary">← Buscar libros</a>
</div>

<style>
  .book-card{height:100%;display:flex;flex-direction:column}
  .book-cover-wrap{aspect-ratio:3/4;background:#f6f7f9;display:flex;align-items:center;justify-content:center;overflow:hidden;border-bottom:1px solid #eee}
  .book-cover{width:100%;height:100%;object-fit:cover}
  .book-body{padding:.6rem .6rem 0;display:flex;flex-direction:column;gap:.25rem}
  .book-title{font-weight:600;font-size:.95rem;line-height:1.2;max-height:2.4em;overflow:hidden}
  .book-meta{font-size:.85rem;color:#6c757d}
  .btn-area{margin-top:auto;padding:.6rem}
</style>

<div id="libros-grid" class="row"></div>

<nav class="d-flex justify-content-center my-3">
  <ul id="pager" class="pagination pagination-sm"></ul>
</nav>

<input type="hidden" id="baseUrl" value="<?= rtrim(BASE_URL, '/') ?>">

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

<script>
(function(){
  const BASE = document.getElementById('baseUrl').value;
  const state = { page: 1, per_page: 8 };

  const esc = s => String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
  const grid = document.getElementById('libros-grid');
  const pager = document.getElementById('pager');

  function cardTpl(b){
    const cover = b.portada_url || '<?= BASE_URL ?>/assets/img/book-placeholder.png';
    const title = esc(b.titulo||'');
    const autor = esc(b.autor||'—');
    const prog  = esc(b.programa_nombre||'—');
    const ud    = esc(b.ud_nombre||'—');
    const ver   = `${BASE}/biblioteca/libros/ver/${encodeURIComponent(b.id)}`;

    return `
      <div class="col-12 col-sm-6 col-lg-3 mb-3 d-flex">
        <div class="card book-card shadow-sm w-100">
          <div class="book-cover-wrap">
            <img class="book-cover" loading="lazy" src="${cover}" alt="${title}">
          </div>
          <div class="book-body">
            <div class="book-title" title="${title}">${title}</div>
            <div class="book-meta"><strong>Programa:</strong> ${prog}</div>
            <div class="book-meta"><strong>UD:</strong> ${ud}</div>
            <div class="book-meta"><strong>Autor:</strong> ${autor}</div>
          </div>
          <div class="btn-area d-grid gap-2">
            <a class="btn btn-primary btn-sm" href="${ver}">Ver</a>
            <button class="btn btn-outline-danger btn-sm btn-unfav" data-id="${b.id}">Quitar</button>
          </div>
        </div>
      </div>`;
  }

  function buildPager(total, page, per){
    pager.innerHTML = '';
    const pages = Math.max(1, Math.ceil(total/per));
    if(pages<=1) return;

    function li(lbl, target, disabled=false, active=false){
      const li = document.createElement('li');
      li.className = 'page-item' + (disabled?' disabled':'') + (active?' active':'');
      li.innerHTML = `<a class="page-link" href="#" data-page="${target}">${lbl}</a>`;
      return li;
    }
    pager.appendChild(li('Inicio', 1, page===1));
    pager.appendChild(li('Anterior', page-1, page===1));

    const span=2; let from=Math.max(1,page-span); let to=Math.min(pages,page+span);
    if(to-from<span*2){ if(from===1) to=Math.min(pages,from+span*2); else if(to===pages) from=Math.max(1,to-span*2); }
    for(let i=from;i<=to;i++) pager.appendChild(li(String(i), i, false, i===page));

    pager.appendChild(li('Siguiente', page+1, page===pages));
    pager.appendChild(li('Final', pages, page===pages));

    pager.querySelectorAll('a[data-page]').forEach(a=>{
      a.addEventListener('click', e=>{
        e.preventDefault();
        const p = parseInt(a.dataset.page,10);
        if(!isNaN(p) && p>=1 && p<=pages && p!==page){ state.page=p; loadPage(); }
      });
    });
  }

  async function loadPage(){
    grid.innerHTML = `<div class="col-12 text-center py-5"><div class="spinner-border" role="status"></div></div>`;
    try{
      const url = `${BASE}/biblioteca/libros/FavoritosList?page=${state.page}&per_page=${state.per_page}`;
      const res = await fetch(url, { credentials: 'same-origin' });
      const j   = await res.json();
      if(!res.ok || !j.ok){ throw new Error(j?.message||'Error'); }

      const data  = j.data||[];
      const total = j.pagination?.total||0;

      if(!data.length){
        grid.innerHTML = `<div class="col-12 text-center text-muted py-5">No tienes libros favoritos aún.</div>`;
        pager.innerHTML = '';
        return;
      }
      grid.innerHTML = data.map(cardTpl).join('');
      buildPager(total, state.page, state.per_page);

      // Quitar de favoritos
      grid.querySelectorAll('.btn-unfav').forEach(btn=>{
        btn.addEventListener('click', async ()=>{
          const id = btn.dataset.id;
          btn.disabled = true;
          try{
            const res = await fetch(`${BASE}/biblioteca/libros/ActualizarFavorito/${encodeURIComponent(id)}`, {
              method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'
            });
            const jr = await res.json().catch(()=> ({}));
            if(!res.ok || !jr.ok){ alert(jr?.message||'No se pudo actualizar'); }
            // recarga esta página (si quedó vacía y no es la primera, retrocede 1)
            await loadPage();
            const items = grid.querySelectorAll('.book-card').length;
            if(items===0 && state.page>1){ state.page--; loadPage(); }
          }catch(e){ alert('Error de red'); }
          finally{ btn.disabled=false; }
        });
      });

    }catch(e){
      console.error(e);
      grid.innerHTML = `<div class="col-12 text-center text-danger py-5">Error cargando favoritos.</div>`;
      pager.innerHTML = '';
    }
  }

  loadPage();
})();
</script>
