<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt') !== false && !preg_match('#/efsrt/(practicas|constancias|empresas|convenios|supervisores|periodos|backups|reportes)#', $_SERVER['REQUEST_URI']) ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/efsrt">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>

            <?php if (\Core\Auth::tieneRolEnEfsrt()): ?>

                <!-- ROL ESTUDIANTE -->
                <?php if (\Core\Auth::esEstudianteEfsrt()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/practicas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/practicas">
                            <i class="fas fa-folder-open"></i> Mis Expedientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/constancias') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/constancias">
                            <i class="fas fa-certificate"></i> Mis Constancias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/empresas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/empresas">
                            <i class="fas fa-building"></i> Directorio de Empresas
                        </a>
                    </li>

                    <!-- ROL DOCENTE SUPERVISOR -->
                <?php elseif (\Core\Auth::esDocenteEfsrt() && !\Core\Auth::esCoordinadorPEEfsrt() && !\Core\Auth::esAdminEfsrt() && !\Core\Auth::esSecretarioPEEfsrt()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/practicas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/practicas">
                            <i class="fas fa-user-check"></i> Practicantes Asignados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/empresas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/empresas">
                            <i class="fas fa-building"></i> Empresas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/convenios') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/convenios">
                            <i class="fas fa-file-contract"></i> Convenios
                        </a>
                    </li>

                    <!-- ROL COORDINADOR / JEFE DE AREA -->
                <?php elseif (\Core\Auth::esCoordinadorPEEfsrt() && !\Core\Auth::esAdminEfsrt() && !\Core\Auth::esSecretarioPEEfsrt() && !\Core\Auth::esDirectorEfsrt()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/practicas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/practicas">
                            <i class="fas fa-clipboard-list"></i> Expedientes de Carrera
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/supervisores') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/supervisores">
                            <i class="fas fa-chalkboard-teacher"></i> Supervisores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/empresas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/empresas">
                            <i class="fas fa-building"></i> Empresas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/convenios') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/convenios">
                            <i class="fas fa-file-contract"></i> Convenios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/constancias') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/constancias">
                            <i class="fas fa-award"></i> Constancias
                        </a>
                    </li>

                    <!-- ROLES ADMINISTRATIVOS: ADMIN, SECRETARIO ACADEMICO, DIRECTOR, JUA -->
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/practicas') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/practicas">
                            <i class="fas fa-folder-open"></i> Expedientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/constancias') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/constancias">
                            <i class="fas fa-certificate"></i> Constancias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/supervisores') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/supervisores">
                            <i class="fas fa-chalkboard-teacher"></i> Supervisores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/reportes') !== false ? 'active' : '') ?>" href="<?= BASE_URL ?>/efsrt/reportes">
                            <i class="fas fa-file-invoice"></i> Reportes
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/empresas') !== false || strpos($_SERVER['REQUEST_URI'], '/efsrt/convenios') !== false ? 'active' : '') ?>" href="#" id="nav-entidades" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-handshake"></i> Empresas y Convenios <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-entidades">
                            <a href="<?= BASE_URL ?>/efsrt/empresas" class="dropdown-item"><i class="fas fa-building mr-1"></i> Empresas / Instituciones</a>
                            <a href="<?= BASE_URL ?>/efsrt/convenios" class="dropdown-item"><i class="fas fa-file-contract mr-1"></i> Convenios Institucionales</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none <?= (strpos($_SERVER['REQUEST_URI'], '/efsrt/periodos') !== false || strpos($_SERVER['REQUEST_URI'], '/efsrt/backups') !== false ? 'active' : '') ?>" href="#" id="nav-config" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i> Configuración <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-config">
                            <a href="<?= BASE_URL ?>/efsrt/periodos" class="dropdown-item"><i class="fas fa-calendar-alt mr-1"></i> Cronograma de Subidas</a>
                            <a href="<?= BASE_URL ?>/efsrt/backups" class="dropdown-item"><i class="fas fa-archive mr-1"></i> Mantenimiento y Purga (>5 años)</a>
                        </div>
                    </li>
                <?php endif; ?>

            <?php endif; ?>
        </ul>
    </div>
</nav>