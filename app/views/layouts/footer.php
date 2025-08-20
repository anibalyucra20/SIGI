</div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
<?php if ($logueado): ?>
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    SIGI &copy; <?= date('Y') ?>
                </div>
                <div class="col-sm-6">
                    <div class="text-sm-end d-none d-sm-block">
                        Versión 1.0
                    </div>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>
</div>
<!-- end main content-->
</div>
<!-- END layout-wrapper -->

<!-- jQuery (primero) -->
<script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>

<!-- Bootstrap 4 bundle (incluye Popper) -->
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>

<!-- DataTables (después de jQuery) -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<!-- Resto de plugins -->
<script src="<?= BASE_URL ?>/assets/js/metismenu.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/simplebar.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/waves.js"></script>
<script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

<!-- Botones DataTables -->
<link rel="stylesheet" href="//cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<script src="//cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script>
    (() => {
        // Ventana de tiempo para considerar una acción "reciente"
        const WINDOW_MS = 0;

        let porNavegar = false; // clic en enlace interno
        let porRecargar = false; // F5 / Ctrl+R / Cmd+R

        function armarTemporizador(flagSetter) {
            flagSetter(true);
            setTimeout(() => flagSetter(false), WINDOW_MS);
        }

        // Clics en enlaces dentro de la página => navegar (no cerrar)
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a) return;
            if (a.target === '_blank') return; // nueva pestaña, no cuenta como salir
            armarTemporizador(v => porNavegar = v);
        }, true);

        // Teclas típicas de recarga (F5, Ctrl/Cmd+R)
        window.addEventListener('keydown', (e) => {
            const k = (e.key || '').toLowerCase();
            const recargaTeclado = k === 'f5' || ((e.ctrlKey || e.metaKey) && k === 'r');
            if (recargaTeclado) armarTemporizador(v => porRecargar = v);
        }, true);

        // Confirmar SOLO si no parece navegación ni recarga recientes
        window.addEventListener('beforeunload', (e) => {
            if (porNavegar || porRecargar) return; // dejar salir sin diálogo
            e.preventDefault();
            e.returnValue = ''; // obliga a mostrar el diálogo nativo
        });
    })();
</script>
</body>

</html>