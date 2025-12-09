<nav class="navbar navbar-light navbar-expand-lg topnav-menu">
    <div class="collapse navbar-collapse" id="topnav-menu-content">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/intranet">
                    <i class="mdi mdi-view-dashboard"></i> Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/caja' ? 'active' : '') ?>"
                    href="<?= BASE_URL ?>/caja">
                    <i class="mdi mdi-home-analytics"></i> Inicio
                </a>
            </li>
            <!-- Menú normal solo si es administrador -->
            <?php if (\Core\Auth::tieneRolEnCaja()): ?>
                <?php if (\Core\Auth::esAdminCaja()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-money-check-alt"></i> Ingresos y Egresos<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-gestion">
                            <a href="<?= BASE_URL ?>/caja/ingresosContables" class="dropdown-item">Ingresos Contables</a>
                            <a href="<?= BASE_URL ?>/caja/egresosGastos" class="dropdown-item">Egresos y gastos</a>
                            <a href="<?= BASE_URL ?>/caja/boletas" class="dropdown-item">Boletas de Venta</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-money-check-alt"></i> Mant. Ingresos<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-gestion">
                            <a href="<?= BASE_URL ?>/caja/bajaBoletas" class="dropdown-item">Dar de baja Boletas(Ingresos)</a>
                            <a href="<?= BASE_URL ?>/caja/series" class="dropdown-item">Modificación de Nro de Serie</a>
                            <a href="<?= BASE_URL ?>/caja/rubrosIngresos" class="dropdown-item">Rubro de Ingresos</a>
                            <a href="<?= BASE_URL ?>/caja/boletas" class="dropdown-item">Modificar Boletas</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i> Mant. Egresos<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-gestion">
                            <a href="<?= BASE_URL ?>/caja/saldosIniciales" class="dropdown-item">Modificación de Saldos Iniciales</a>
                            <a href="<?= BASE_URL ?>/caja/planCuentas" class="dropdown-item">Plan de cuentas</a>
                            <a href="<?= BASE_URL ?>/caja/tiposDocumentos" class="dropdown-item">Tipos de Documentos</a>
                            <a href="<?= BASE_URL ?>/caja/mediosPago" class="dropdown-item">Medios de Pago</a>
                            <a href="<?= BASE_URL ?>/caja/rubrosEgresosContables" class="dropdown-item">Rubro de Egresos Contab.</a>
                            <a href="<?= BASE_URL ?>/caja/rubrosIngresosContables" class="dropdown-item">Rubro de Ingresos Contab.</a>
                            <a href="<?= BASE_URL ?>/caja/centroCostos" class="dropdown-item">Centro de Costos</a>
                            <a href="<?= BASE_URL ?>/caja/proveedores" class="dropdown-item">Proveedor ó Entidad</a>
                            <a href="<?= BASE_URL ?>/caja/leVentas" class="dropdown-item">LE Ventas</a>
                            <a href="<?= BASE_URL ?>/caja/leCompras" class="dropdown-item">LE Compras</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="nav-periodos" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i> Reportes<div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="nav-gestion">
                            <a href="<?= BASE_URL ?>/caja/reporteIngresos" class="dropdown-item">Reporte de Ingresos</a>
                            <a href="<?= BASE_URL ?>/caja/reporteEgresos" class="dropdown-item">Reporte de Egresos</a>
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