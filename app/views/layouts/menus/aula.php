<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/aula' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/aula">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnAula()): ?>
                <?php if (\Core\Auth::esAdminAula()): ?>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/aula/unidadesDidacticas">
                            <i class="far fa-address-book"></i> Mis Unidades Didácticas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/aula/reportes">
                            <i class="fas fa-chart-line"></i> Reportes
                        </a>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <!-- Aquí va solo lo mínimo, o un mensaje, o nada -->
                <!-- O simplemente no muestres nada más -->
            <?php endif; ?>
        </ul>
    </div>
</nav>