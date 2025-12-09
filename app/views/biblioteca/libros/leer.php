<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php
$cover = !empty($libro['portada_url']) ? $libro['portada_url'] : (BASE_URL . '/assets/img/book-placeholder.png');
$id_libro = $libro['id'] ?? '0';
$titulo = htmlspecialchars($libro['titulo'] ?? '—', ENT_QUOTES, 'UTF-8');
$autor  = htmlspecialchars($libro['autor']  ?? '—', ENT_QUOTES, 'UTF-8');
$editorial  = htmlspecialchars($libro['editorial']  ?? '—', ENT_QUOTES, 'UTF-8');
$edicion  = htmlspecialchars($libro['edicion']  ?? '—', ENT_QUOTES, 'UTF-8');
$tomo  = htmlspecialchars($libro['tomo']  ?? '—', ENT_QUOTES, 'UTF-8');
$isbn   = htmlspecialchars($libro['isbn']   ?? '—', ENT_QUOTES, 'UTF-8');
$paginas   = htmlspecialchars($libro['paginas']   ?? '—', ENT_QUOTES, 'UTF-8');
$tipo   = htmlspecialchars($libro['tipo_libro'] ?? '—', ENT_QUOTES, 'UTF-8');
$anio   = htmlspecialchars((string)($libro['anio'] ?? '—'), ENT_QUOTES, 'UTF-8');
$temas_relacionados   = htmlspecialchars((string)($libro['temas_relacionados'] ?? '—'), ENT_QUOTES, 'UTF-8');
$file   = $libro['archivo_url'] ?? '#';
$id     = (int)($libro['id'] ?? 0);

// <- Estado inicial: si ya es favorito
$esFavorito = !empty($libro['_es_favorito']); // pon esto desde el controlador al cargar la vista
?>

<style>
    .book-hero {
        background: #fff;
        border-radius: .5rem;
        box-shadow: 0 6px 16px rgba(33, 37, 41, .08);
    }

    .book-cover-wrap {
        background: #f6f7f9;
        border: 1px solid #eef0f2;
        border-radius: .5rem;
        overflow: hidden;
    }

    .book-cover-wrap img {
        width: 100%;
        height: auto;
        display: block;
    }

    .meta dt {
        font-weight: 600;
        color: #6c757d;
        width: 140px;
    }

    .meta dd {
        margin-bottom: .5rem;
    }
</style>

<div class="">
    <div class="mb-3">
        <a href="<?= BASE_URL ?>/biblioteca/libros/ver/<?= $id; ?>" class="btn btn-info btn-sm">
            ← Volver
        </a>
    </div>
    <div class="book-hero p-3 p-md-4">
        <div class="row">
            <div class="col-12 p-3">
                <button
                    id="btnFav"
                    class="btn <?= $esFavorito ? 'btn-primary' : 'btn-outline-primary' ?>"
                    data-id="<?= $id ?>"
                    type="button">
                    <span class="ico"><?= $esFavorito ? '♥' : '♡' ?></span>
                    <span class="txt"><?= $esFavorito ? 'En favoritos' : 'Añadir a Favoritos' ?></span>
                </button>
            </div>
            <div class="col-md-12">
                <h4 class="mb-2"><?= $titulo ?></h4>
                <p class="text-muted mb-4"><?= $autor !== '' ? $autor : '—' ?></p>
            </div>
            <div class="col-md-12">
                <div class="book-cover-wrap">
                    <iframe src="<?= $file ?>" width="100%" height="800"></iframe>
                </div>
            </div>
        </div>
        <div class="mb-3 center p-2">
            <a href="<?= BASE_URL ?>/biblioteca/libros/ver/<?= $id; ?>" class="btn btn-info btn-sm">
                ← Volver
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

<script>
    (function() {
        const btn = document.getElementById('btnFav');
        if (!btn) return;

        const base = '<?= rtrim(BASE_URL, "/") ?>';
        let loading = false;

        function setState(isFav) {
            btn.classList.toggle('btn-primary', isFav);
            btn.classList.toggle('btn-outline-primary', !isFav);
            btn.querySelector('.ico').textContent = isFav ? '♥' : '♡';
            btn.querySelector('.txt').textContent = isFav ? 'En favoritos' : 'Añadir a Favoritos';
        }

        btn.addEventListener('click', async function() {
            if (loading) return;
            const id = this.dataset.id;
            loading = true;
            btn.disabled = true;

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
                        return;
                    }
                    alert(data?.message || 'No se pudo actualizar favorito.');
                    return;
                }
                setState(!!data.is_favorite);
            } catch (e) {
                console.error(e);
                alert('Error de red al actualizar favorito.');
            } finally {
                btn.disabled = false;
                loading = false;
            }
        });
    })();
</script>