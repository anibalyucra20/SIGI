<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/tutoria' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/tutoria">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnTutoria()): ?>
                <?php if (\Core\Auth::esAdminTutoria()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-account-circle"></i> Tutores<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-periodos">
                            <a href="<?= BASE_URL ?>/tutoria/tutores" class="dropdown-item">Tutores</a>
                            <a href="<?= BASE_URL ?>/tutoria/tutores/sesiones" class="dropdown-item">Sesiones de Tutoria</a>
                            <a href="<?= BASE_URL ?>/tutoria/tutores/fichas" class="dropdown-item">Reportes de Tutores</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?>/tutoria/favoritos">
                            <i class="far fa-user"></i> Estudiantes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>/tutoria/reportes">
                            <i class="mdi mdi-book"></i> Reportes
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