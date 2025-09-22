<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esCoordinadorPEAcademico()): ?>
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="#" data-toggle="modal" data-target="#repNomina">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-plus"></i></div>
                            <div class="count">Reporte</div>
                            <h4>Nómina de Matrícula</h4>
                            <p>Reporte de Nómina de Matrícula</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_matricula.php'); ?>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="" data-toggle="modal" data-target="#rep_calif_consolidado">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-anchor"></i></div>
                            <div class="count"> Reporte</div>
                            <h4>Consolidado</h4>
                            <p>Reporte Consolidado</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_calif_consolidado.php'); ?>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="" data-toggle="modal" data-target="#rep_calif_detallado">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-anchor"></i></div>
                            <div class="count"> Reporte</div>
                            <h4>Consolidado Detallado</h4>
                            <p>Reporte Consolidado Detallado</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_calif_detallado.php'); ?>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="" data-toggle="modal" data-target="#rep_calif_individual">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-comments-o"></i></div>
                            <div class="count">Reporte</div>
                            <h4>Indivual</h4>
                            <p>Reporte Individual Por Estudiante</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_calif_individual.php'); ?>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="" data-toggle="modal" data-target="#rep_primeros_puestos">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-check-square-o"></i></div>
                            <div class="count">Reporte</div>
                            <h4>Primeros Puestos</h4>
                            <p>Reporte de Primeros Puestos</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_primeros_puestos.php'); ?>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="" data-toggle="modal" data-target="#rep_control_diario">
                        <div class="tile-stats">
                            <div class="icon"><i class="fa fa-check-square-o"></i></div>
                            <div class="count">Reporte</div>
                            <h4>Control Diario</h4>
                            <p>Reporte de Control Diario</p>
                        </div>
                    </a>
                </div>
            </div>
            <?php include_once(__DIR__ . '/modals/modal_reporte_control_diario.php'); ?>
        </div>
    <?php endif; ?>
    <?php require __DIR__ . '/../../layouts/footer.php'; ?>