<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/biblioteca' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/biblioteca">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnBiblioteca()): ?>
                <?php if (!\Core\Auth::esAdminBiblioteca()): ?>
                <li class="nav-item">
                    <a class="nav-link"
                        href="<?= BASE_URL ?>/biblioteca/busqueda">
                        <i class="fas fa-book"></i> Biblioteca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        href="<?= BASE_URL ?>/biblioteca/libros/favoritos">
                        <i class="far fa-bookmark"></i> Mis Favoritos
                    </a>
                </li>
                <?php endif; ?>
                <?php if (\Core\Auth::esAdminBiblioteca()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-book"></i> Libros<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-periodos">
                            <a href="<?= BASE_URL ?>/biblioteca/libros" class="dropdown-item">Libros Propios</a>
                            <a href="<?= BASE_URL ?>/biblioteca/libros/vinculados" class="dropdown-item">Libros Vinculados</a>
                            <a href="<?= BASE_URL ?>/biblioteca/libros/vincular" class="dropdown-item">Búsqueda de Libros</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/lecturas') !== false ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>/biblioteca/lecturas">
                            <i class="mdi mdi-book"></i> Lecturas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>/biblioteca/reportes">
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