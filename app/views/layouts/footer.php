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

<!-- Scripts del template -->
<script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<!-- DataTables núcleo + integración Bootstrap 4 -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/metisMenu.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/simplebar.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/waves.js"></script>
<!-- App js -->
<script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

<!-- Header.php (plugins DataTables export) -->
<link rel="stylesheet" href="//cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<script src="//cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="//cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
</body>

</html>