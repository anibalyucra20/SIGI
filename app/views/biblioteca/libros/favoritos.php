<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php
$libros   = $libros ?? [];
$page     = (int)($page ?? 1);
$per_page = (int)($per_page ?? 8);
$total    = (int)($total ?? 0);
$pages    = max(1, (int)ceil($total / max(1, $per_page)));

$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Mis favoritos</h5>
  <!--<a href="<?= BASE_URL ?>/biblioteca/busqueda" class="btn btn-sm btn-outline-secondary">← Buscar libros</a>-->
</div>

<style>
  .book-card {
    height: 100%;
    display: flex;
    flex-direction: column
  }

  .book-cover-wrap {
    aspect-ratio: 3/4;
    background: #f6f7f9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-bottom: 1px solid #eee
  }

  .book-cover {
    width: 100%;
    height: 100%;
    object-fit: cover
  }

  .book-body {
    padding: .6rem .6rem 0;
    display: flex;
    flex-direction: column;
    gap: .25rem
  }

  .book-title {
    font-weight: 600;
    font-size: .95rem;
    line-height: 1.2;
    max-height: 2.4em;
    overflow: hidden
  }

  .book-meta {
    font-size: .85rem;
    color: #6c757d
  }

  .btn-area {
    margin-top: auto;
    padding: .6rem
  }
</style>

<div id="libros-grid" class="row">
  <?php if (empty($libros)): ?>
    <div class="col-12 text-center text-muted py-5">No tienes libros favoritos aún.</div>
  <?php else: ?>
    <?php foreach ($libros as $b):
      $id     = (int)($b['id'] ?? 0);
      $title  = $esc($b['titulo'] ?? '');
      $autor  = $esc($b['autor'] ?? '—');
      $cover  = $b['portada_url'] ?: (BASE_URL . '/assets/img/book-placeholder.png');
      $prog   = $esc($b['programa_nombre'] ?? '—');
      $ud     = $esc($b['ud_nombre'] ?? '—');
      $verUrl = BASE_URL . '/biblioteca/libros/ver/' . $id;
    ?>
      <div class="col-12 col-sm-6 col-lg-3 mb-3 d-flex">
        <div class="card book-card shadow-sm w-100">
          <a href='<?= $verUrl ?>'>
          <div class="book-cover-wrap">
            <img class="book-cover" loading="lazy" src="<?= $cover ?>" alt="<?= $title ?>">
          </div>
          </a>
          <div class="book-body">
            <div class="book-title" title="<?= $title ?>"><?= $title ?></div>
            <div class="book-meta"><strong>Autor:</strong> <?= $autor ?></div>
            <div class="book-meta"><strong>Programa:</strong> <?= $prog ?></div>
            <div class="book-meta"><strong>UD:</strong> <?= $ud ?></div>
          </div>
          <div class="btn-area d-grid gap-2">
            <a class="btn btn-primary btn-sm btn-block m-1" href="<?= $verUrl ?>">Ver</a>
            <button class="btn btn-outline-danger btn-sm btn-block m-1" type="button" onclick="actualizar_Fav(<?= $id ?>);" id="btn_delete_<?= $id ?>">Quitar</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
  <nav class="d-flex justify-content-center my-3">
    <ul class="pagination pagination-sm">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/biblioteca/libros/favoritos?page=1&per_page=<?= $per_page ?>">Inicio</a>
      </li>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/biblioteca/libros/favoritos?page=<?= max(1, $page - 1) ?>&per_page=<?= $per_page ?>">Anterior</a>
      </li>

      <?php
      $span = 2;
      $from = max(1, $page - $span);
      $to = min($pages, $page + $span);
      if ($to - $from < $span * 2) {
        if ($from === 1) $to = min($pages, $from + $span * 2);
        elseif ($to === $pages) $from = max(1, $to - $span * 2);
      }
      for ($i = $from; $i <= $to; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= BASE_URL ?>/biblioteca/libros/favoritos?page=<?= $i ?>&per_page=<?= $per_page ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/biblioteca/libros/favoritos?page=<?= min($pages, $page + 1) ?>&per_page=<?= $per_page ?>">Siguiente</a>
      </li>
      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/biblioteca/libros/favoritos?page=<?= $pages ?>&per_page=<?= $per_page ?>">Final</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>
<script>
  const base = '<?= rtrim(BASE_URL, "/") ?>';

  function actualizar_Fav(id) {
    const base = '<?= rtrim(BASE_URL, "/") ?>';
    //Warning Message
    Swal.fire({
      title: "¿Esta Seguro?",
      text: "¿De eliminar de Favoritos?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, Eliminar",
      cancelButtonText: "Cancelar"
    }).then(function(result) {
      if (result.value) {
        let res = actualizar_estado(id);
      }
    });


  };
  async function actualizar_estado(id) {
    try {
      const res = await fetch(`${base}/biblioteca/libros/ActualizarFavorito/${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }, // para detectar AJAX del lado PHP
        credentials: 'same-origin'
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) {
        // si no está logueado o error de permiso
        if (data?.error === 'AUTH') {
          window.location.href = `${base}/auth/login`;
          return 0;
        }
        alert(data?.message || 'No se pudo actualizar favorito.');
        return 0;
      }
      let btn = document.getElementById('btn_delete_' + id);
      btn.style.display = 'none';
      Swal.fire("Actualizado!", "Se quitó de favoritos", "success");
    } catch (e) {
      console.error(e);
      //alert('Error de red al actualizar favorito.');
      Swal.fire("Error!", "Error de red al actualizar favorito.", "danger");
      return 0;
    } finally {
      //btn.disabled = false;
      return 1;
    }
  }
</script>


<?php require __DIR__ . '/../../layouts/footer.php'; ?>