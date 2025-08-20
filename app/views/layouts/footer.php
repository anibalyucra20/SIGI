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
        // --- Flags instantáneos (se limpian en el próximo frame) ---
        let porClicEnlace = false; // navegación por enlace
        let porRecarga = false; // F5 / Ctrl|Cmd + R
        let porSubmit = false; // envío de formulario (permitir)

        // --- Detectar si hay formularios en la página (también dinámicos) ---
        let hayForm = !!document.querySelector('form');
        const mo = new MutationObserver(() => {
            hayForm = !!document.querySelector('form');
        });
        mo.observe(document.documentElement, {
            childList: true,
            subtree: true
        });

        // --- Marcar clic en enlaces internos (misma pestaña) ---
        window.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || a.target === '_blank') return; // nueva pestaña no descarga la actual
            porClicEnlace = true;
            requestAnimationFrame(() => {
                porClicEnlace = false;
            });
        }, true);

        // --- Marcar recarga por teclado ---
        window.addEventListener('keydown', (e) => {
            const k = (e.key || '').toLowerCase();
            if (k === 'f5' || ((e.ctrlKey || e.metaKey) && k === 'r')) {
                porRecarga = true;
                requestAnimationFrame(() => {
                    porRecarga = false;
                });
            }
        }, true);
        // --- No interrumpir si el usuario envía un formulario ---
        window.addEventListener('submit', () => {
            porSubmit = true;
            requestAnimationFrame(() => {
                porSubmit = false;
            });
        }, true);
        // --- Regla de salida ---
        window.addEventListener('beforeunload', (e) => {
            // 1) Si hay formulario: validar cualquier descarga (recarga o navegación),
            //    excepto cuando se está enviando el formulario.
            if (hayForm && !porSubmit) {
                e.preventDefault();
                e.returnValue = '';
                return;
            }
            // 2) Si NO hay formulario: solo avisar cuando parezca "cerrar pestaña"
            //    (no hubo clic en enlace ni recarga por teclado en este frame).
            if (!porClicEnlace && !porRecarga) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    })();
</script>


</body>

</html>