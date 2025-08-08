<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php';

// Determinar si el periodo está finalizado para bloquear edición
$periodoFinalizado = strtotime($periodo['fecha_fin']) < strtotime(date('Y-m-d'));
$limiteInasistencia = 30; // Porcentaje para resaltar en rojo
?>
<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>
    <div class="card p-2">
        <h4 class="text-center my-3">Asistencia - <?= htmlspecialchars($nombreUnidadDidactica) ?></h4>
        <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-danger mb-3 col-sm-2">Regresar</a>
        <div class="table-responsive" style="overflow-x:auto;">
            <form action="<?= BASE_URL ?>/academico/asistencia/guardar" method="post" autocomplete="off" id="form-asistencia">
                <input type="hidden" name="id_programacion_ud" value="<?= (int)$id_programacion_ud ?>">
                <table class="table table-bordered table-hover table-sm align-middle" id="tabla-asistencia">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="bg-light sticky-col left-col" rowspan="2">DNI</th>
                            <th class="bg-light sticky-col left-col2" rowspan="2">APELLIDOS Y NOMBRES</th>
                            <?php foreach ($sesiones as $ses): ?>
                                <th style="min-width:60px; writing-mode: vertical-lr; transform: rotate(180deg);" title="Sesión <?= $ses['semana'] ?>">
                                    <?= date('Y-m-d', strtotime($ses['fecha_desarrollo'])) ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="bg-light" style="min-width:60px; writing-mode: vertical-lr; transform: rotate(180deg);" rowspan="2">INASISTENCIA</th>
                        </tr>
                        <tr>
                            <?php foreach ($sesiones as $ses): ?>
                                <th>
                                    <?php if (!$periodoFinalizado): ?>
                                        <select class="form-control form-control-sm select-masivo" data-sesion="<?= $ses['id'] ?>">
                                            <option value=""></option>
                                            <option value="P">P</option>
                                            <option value="F">F</option>
                                        </select>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                            <tr>
                                <td class="sticky-col left-col bg-white"><?= htmlspecialchars($est['dni']) ?></td>
                                <td class="sticky-col left-col2 bg-white"><?= htmlspecialchars($est['apellidos_nombres']) ?></td>
                                <?php
                                $faltas = 0;
                                foreach ($sesiones as $i => $ses):
                                    $id_detalle_matricula = $est['id_detalle_matricula'];
                                    $id_sesion = $ses['id'];
                                    $valor = $asistencias[$id_detalle_matricula][$id_sesion] ?? '';
                                    if ($valor === 'F') $faltas++;
                                ?>
                                    <td class="text-center vertical">
                                        <?php if ($periodoFinalizado): ?>
                                            <b><?= $valor ?: '-' ?></b>
                                        <?php else: ?>
                                            <select name="asistencia[<?= $id_detalle_matricula ?>][<?= $id_sesion ?>]" class="form-control form-control-sm text-center select-asistencia" data-sesion="<?= $id_sesion ?>">
                                                <option value="" <?= $valor == '' ? 'selected' : '' ?>></option>
                                                <option value="P" <?= $valor == 'P' ? 'selected' : '' ?>>P</option>
                                                <option value="F" <?= $valor == 'F' ? 'selected' : '' ?>>F</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php
                                $totalSesiones = count($sesiones);
                                $porcFaltas = $totalSesiones ? round($faltas * 100 / $totalSesiones) : 0;
                                $class = $porcFaltas >= $limiteInasistencia ? 'text-danger font-weight-bold' : '';
                                ?>
                                <td class="text-center <?= $class ?>"><?= $porcFaltas ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!$periodoFinalizado): ?>
                    <div class="text-right my-3">
                        <button type="submit" class="btn btn-primary px-4">Guardar Asistencia</button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning my-3">El periodo académico ha finalizado. La asistencia no es editable.</div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <style>
        /* Fijar las dos primeras columnas al hacer scroll horizontal */
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
        }

        .left-col {
            left: 0;
            min-width: 100px;
        }

        .left-col2 {
            left: 100px;
            min-width: 200px;
            z-index: 2;
        }

        #tabla-asistencia th,
        #tabla-asistencia td {
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.select-masivo').on('change', function() {
                var sesion = $(this).data('sesion');
                var valor = $(this).val();
                // Solo afecta los selects de la columna correspondiente
                $('select.select-asistencia[data-sesion="' + sesion + '"]').val(valor);
            });
        });
    </script>
<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>