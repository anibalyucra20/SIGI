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
            <?php if (\Core\Auth::esAdminTutoria() || \Core\Auth::esDocenteTutoria()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-tutoria" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-account-circle"></i> <?= \Core\Auth::esAdminTutoria() ? 'Gestión de Tutoría' : 'Mi Tutoría' ?><div class="arrow-down"></div>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="nav-tutoria">
                        <?php if (\Core\Auth::esAdminTutoria()): ?>
                            <a href="<?= BASE_URL ?>/tutoria/programacion" class="dropdown-item">Programación de Tutores</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesIndividuales" class="dropdown-item">Sesiones Individuales</a>
                        <a href="<?= BASE_URL ?>/tutoria/tutores/sesionesGrupales" class="dropdown-item">Sesiones Grupales</a>
                        <a href="<?= BASE_URL ?>/tutoria/tutores/fichas" class="dropdown-item">Fichas de Tutoría</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '' ?>"
                        href="<?= BASE_URL ?>/tutoria/reportes">
                        <i class="mdi mdi-book"></i> Reportes
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
