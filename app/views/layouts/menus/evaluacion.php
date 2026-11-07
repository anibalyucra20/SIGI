<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/evaluacion') !== false && !preg_match('#/evaluacion/(encuestas|criterios|seguimiento|reportes|misEvaluaciones|misResultados)#', $_SERVER['REQUEST_URI']) ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/evaluacion">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>

            <?php if (\Core\Auth::tieneRolEnEvaluacion()): ?>

                <!-- ROL ESTUDIANTE -->
                <?php if (\Core\Auth::esEstudianteEvaluacion()): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/misEvaluaciones') !== false || strpos($_SERVER['REQUEST_URI'], '/realizar') !== false ? 'active' : '') ?>" 
                           href="<?= BASE_URL ?>/evaluacion/misEvaluaciones">
                            <i class="fas fa-edit"></i> Mis Evaluaciones
                        </a>
                    </li> -->

                    <!-- ROL DOCENTE -->
                <?php elseif (\Core\Auth::esDocenteEvaluacion() && !\Core\Auth::esCoordinadorPEEvaluacion() && !\Core\Auth::esAdminEvaluacion()): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/misResultados') !== false ? 'active' : '') ?>" 
                           href="<?= BASE_URL ?>/evaluacion/misResultados">
                            <i class="fas fa-chart-line"></i> Mis Resultados
                        </a>
                    </li> -->

                    <!-- ROL COORDINADOR / JEFE DE AREA -->
                <?php elseif (\Core\Auth::esCoordinadorPEEvaluacion() && !\Core\Auth::esAdminEvaluacion()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/encuestas') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/encuestas">
                            <i class="fas fa-clipboard-list"></i> Encuestas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/banco') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/banco">
                            <i class="fas fa-book-open"></i> Banco de Rúbricas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/seguimiento') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/seguimiento">
                            <i class="fas fa-user-clock"></i> Estudiantes Pendientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/reportes">
                            <i class="fas fa-chart-bar"></i> Reportes y Resultados
                        </a>
                    </li>

                    <!-- ROL ADMINISTRADOR / DIRECTOR / JUA -->
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/encuestas') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/encuestas">
                            <i class="fas fa-clipboard-list"></i> Gestión de Encuestas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/banco') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/banco">
                            <i class="fas fa-book-open"></i> Banco de Rúbricas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/seguimiento') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/seguimiento">
                            <i class="fas fa-user-clock"></i> Estudiantes Pendientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '') ?>"
                            href="<?= BASE_URL ?>/evaluacion/reportes">
                            <i class="fas fa-file-invoice"></i> Reportes y Consolidado
                        </a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>
        </ul>
    </div>
</nav>