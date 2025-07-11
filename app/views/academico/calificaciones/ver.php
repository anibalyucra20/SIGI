<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php'; ?>

<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>

    <div class="card pt-2 m-2">
        <h5 class="text-center font-weight-bold mb-4" style="color:#607d8b;">
            Calificaciones - <span style="color:#4153a1;"><?php echo strtoupper($nombreUnidadDidactica ?? ''); ?></span>
        </h5>
        <div>
            <div class="col-4 col-md-2 mb-3">
                <a class="btn btn-info btn-sm btn-block mb-2" href="<?= BASE_URL ?>/academico/calificaciones/registroOficial/<?= $id_programacion_ud ?>">Imprimir Registro Oficial</a>
                <button class="btn btn-success btn-sm btn-block mb-2">Imprimir Acta Final</button>
                <button class="btn btn-primary btn-sm btn-block mb-2">Imprimir Acta Recuperacion</button>
                <button class="btn btn-warning btn-sm btn-block mb-2 text-white">Reporte Registra</button>
                <a class="btn btn-danger btn-sm btn-block" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
            </div>
            <div class="col-12 col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered" style="font-size: 14px;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-center">Nro Orden</th>
                                <th rowspan="2" class="align-middle text-center">DNI</th>
                                <th rowspan="2" class="align-middle text-center">APELLIDOS Y NOMBRES</th>
                                <?php if (isset($nros_calificacion) && count($nros_calificacion)): ?>
                                    <th colspan="<?= count($nros_calificacion) ?>" class="align-middle text-center" style="background:#f5f7fa;">
                                        CALIFICACIONES
                                    </th>
                                <?php endif; ?>
                                <th rowspan="2" class="align-middle text-center" style="writing-mode:vertical-rl; background:#f5f7fa;">RECUPERACION</th>
                                <th rowspan="2" class="align-middle text-center" style="writing-mode:vertical-rl; background:#f5f7fa;">PROMEDIO FINAL <br>
                                    <div style="writing-mode:initial;">
                                        <input type="checkbox"
                                            id="mostrar-promedio-todos"
                                            <?= (isset($mostrar_promedio_todos) && $mostrar_promedio_todos) ? 'checked' : '' ?>>
                                        <span style="font-size:12px;">Mostrar</span>
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <?php foreach ($nros_calificacion as $k => $nro): ?>
                                    <?php $mostrar = $mostrar_calificaciones[$nro] ?? 0; ?>
                                    <th class="text-center" style="min-width:110px;">
                                        N° <?= $nro ?> <br>
                                        <a href="<?= BASE_URL ?>/academico/calificaciones/evaluar/<?= $id_programacion_ud ?>/<?= $nro ?>" class="btn btn-primary btn-sm ml-2">
                                            <i class="fa fa-pen"></i> Evaluar
                                        </a>
                                        <div>
                                            <input type="checkbox"
                                                class="mostrar-checkbox" data-nro="<?= $nro ?>"
                                                <?= $mostrar ? 'checked' : '' ?>>
                                            <span style="font-size:12px;">Mostrar</span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $idx => $est):
                                $id_detalle = $est['id_detalle_matricula'];
                                $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                            ?>
                                <tr class="<?= $inhabilitado ? 'table-danger bg-danger' : '' ?>">
                                    <td class="text-center"><?= ($idx + 1) ?></td>
                                    <td class="text-center <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['dni']; ?></td>
                                    <td class="<?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['apellidos_nombres']; ?><?= $inhabilitado ? ' (Inasistencia)' : '' ?></td>
                                    <?php foreach ($nros_calificacion as $nro):
                                        $nota = $notas[$id_detalle][$nro] ?? '';
                                        $clase = '';
                                        if ($nota !== '' && is_numeric($nota)) {
                                            $clase = ($nota < 13) ? "text-danger font-weight-bold" : "text-primary font-weight-bold";
                                        }
                                    ?>
                                        <td class="text-center <?= $clase; ?>">
                                            <?= ($nota === '') ? '' : $nota; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center">
                                        <?php
                                        $recup = $recuperaciones[$id_detalle] ?? '';
                                        $promedio_final = $promedios[$id_detalle];
                                        if (in_array($promedio_final, [10, 11, 12])) { ?>
                                            <input type="text" class="form-control form-control-sm text-center" value="<?= $recup; ?>" style="max-width:50px;display:inline-block;">
                                        <?php } ?>
                                    </td>
                                    <td class="text-center font-weight-bold <?= (is_array($promedios[$id_detalle]) ? (reset($promedios[$id_detalle]) < 13) : ($promedios[$id_detalle] < 13)) || $inhabilitado ? "text-danger" : "text-primary"; ?>">
                                        <!-- Mostrar nota de inasistencia si el estudiante está inhabilitado -->
                                        <?php
                                        if ($inhabilitado) {
                                            if (is_array($nota_inasistencia)) {
                                                echo reset($nota_inasistencia);
                                            } else {
                                                echo $nota_inasistencia;
                                            }
                                        } else {
                                            if (is_array($promedios[$id_detalle])) {
                                                echo reset($promedios[$id_detalle]);
                                            } else {
                                                echo $promedios[$id_detalle];
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
    <style>
        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table th {
            background: #f5f7fa !important;
            border-bottom: 2px solid #dbe2ef;
        }

        .table td {
            background: #fff;
        }
    </style>
    <script>
        document.querySelectorAll('.mostrar-checkbox').forEach(function(el) {
            el.addEventListener('change', function() {
                var nro = $(this).data('nro');
                var mostrar = $(this).is(':checked') ? 1 : 0;
                $.post('<?= BASE_URL ?>/academico/calificaciones/actualizarMostrar', {
                    id_programacion_ud: '<?= $id_programacion_ud ?>',
                    nro_calificacion: nro,
                    mostrar: mostrar
                }, function(resp) {
                    // Aquí puedes mostrar un toast o mensaje de éxito/error
                });
            });
        });
        document.getElementById('mostrar-promedio-todos').addEventListener('change', function() {
            var mostrar = this.checked ? 1 : 0;
            fetch('<?= BASE_URL ?>/academico/calificaciones/actualizarMostrarPromedioTodos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id_programacion_ud=<?= $id_programacion_ud ?>&mostrar=' + mostrar
                })
                .then(response => response.json())
                .then(data => {
                    // Opcional: Mostrar mensaje de éxito o error
                });
        });
    </script>

<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>