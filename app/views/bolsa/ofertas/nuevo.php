<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminBolsa()): ?>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-dark font-weight-bold">Nueva Oferta</h3>
        </div>
    </div>

    <div class="card p-2">
        <form action="<?= BASE_URL ?>/bolsa/ofertas/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off" enctype="multipart/form-data">
            <?php include __DIR__ . '/form_fields.php'; ?>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Guardar</button>
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-secondary px-4"><i class="fas fa-times mr-1"></i>Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Script para vista previa de la imagen -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                    
            const inputFoto = document.getElementById('foto');
            const previewFoto = document.getElementById('previewFoto');
                    
            inputFoto.addEventListener('change', function () {
                    
                const archivo = this.files[0];
                    
                if (archivo) {
                    const url = URL.createObjectURL(archivo);
                    
                    previewFoto.src = url;
                    previewFoto.style.display = 'block';
                }
                    
            });
                    
        });
    </script>

<?php else: ?>
    <p>El módulo solo es para rol de Administrador.</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>