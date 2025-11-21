<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/efsrt' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/efsrt">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnEfsrt()): ?>
                <?php if (\Core\Auth::esAdminEfsrt()): ?>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/efsrt/efsrt">
                            <i class="fas fa-bookmark"></i> Registro EFSRT
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/efsrt/supervisores">
                            <i class="fas fa-user-tie"></i> Supervisores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/efsrt/programacion">
                            <i class="fas fa-book-open"></i> Programación de EFSRT
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/efsrt/reportes">
                            <i class="fas fa-chart-line"></i> Reportes
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i> Gestión de EFSRT<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-periodos">
                            <a href="<?= BASE_URL ?>/efsrt/convenios" class="dropdown-item">Convenios</a>
                        </div>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <!-- Aquí va solo lo mínimo, o un mensaje, o nada -->
                <!-- O simplemente no muestres nada más -->
            <?php endif; ?>
        </ul>
    </div>
</nav>