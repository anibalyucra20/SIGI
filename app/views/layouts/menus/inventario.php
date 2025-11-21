<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/inventario' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/inventario">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnInventario()): ?>
                <?php if (\Core\Auth::esAdminInventario()): ?>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/inventario/bienes">
                            <i class="fas fa-tags"></i> Bienes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/inventario/ambientes">
                            <i class="fas fa-door-open"></i> Ambientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/inventario/movimientos">
                            <i class="fas fa-sync"></i> Movimientos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/inventario/reportes">
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