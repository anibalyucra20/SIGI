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
                <a class="btn btn-info btn-sm btn-block mb-2" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/registroOficial/<?= $id_programacion_ud ?>">Imprimir Registro Oficial</a>
                <a class="btn btn-success btn-sm btn-block mb-2" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/actaFinal/<?= $id_programacion_ud ?>">Imprimir Acta Final</a>
                <a class="btn btn-primary btn-sm btn-block mb-2 text-white">Imprimir Acta Recuperacion</a>
                <a class="btn btn-warning btn-sm btn-block mb-2 text-white">Reporte Registra</a>
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
                                $motivo = ' (Inasistencia)';
                                if ($est['licencia'] != '') {
                                    $motivo = ' (Licencia)';
                                    $inhabilitado = $id_detalle;
                                }
                            ?>
                                <tr class="<?= $inhabilitado ? 'table-danger bg-danger' : '' ?>">
                                    <td class="text-center"><?= ($idx + 1) ?></td>
                                    <td class="text-center <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['dni']; ?></td>
                                    <td class="<?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['apellidos_nombres']; ?><?= $inhabilitado ? $motivo : '' ?></td>
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
                                        if (in_array($promedio_final, [10, 11, 12]) && !$inhabilitado): ?>
                                            <input type="number" class="form-control form-control-sm text-center nota-recuperacion <?= ($recup < 13) ? 'text-danger' : 'text-primary' ?>" data-id-recuperacion="<?= $id_detalle ?>" value="<?= $recup; ?>" style="max-width:50px;display:inline-block;">
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                    if ($recup != '') {
                                        $promedio_finalll = $recup;
                                    } else {
                                        $promedio_finalll = $promedios[$id_detalle];
                                    }
                                    ?>
                                    <td class="text-center font-weight-bold <?= (is_array($promedio_finalll) ? (reset($promedio_finalll) < 13) : ($promedio_finalll < 13)) || $inhabilitado ? "text-danger" : "text-primary"; ?>">
                                        <label id="promedio_finalll_<?= $id_detalle ?>">
                                            <!-- Mostrar nota de inasistencia si el estudiante está inhabilitado -->
                                            <?php
                                            if ($inhabilitado) {
                                                if (is_array($nota_inasistencia) && $est['licencia'] != '') {
                                                    echo $nota_mostrar;
                                                } else {
                                                    $nota_mostrar = reset($nota_inasistencia);
                                                    echo $nota_mostrar;
                                                }
                                            } else {
                                                if (is_array($promedio_finalll)) {
                                                    $nota_mostrar = reset($promedio_finalll);
                                                    echo $nota_mostrar;
                                                } else {
                                                    $nota_mostrar = $promedio_finalll;
                                                    echo $nota_mostrar;
                                                }
                                            }
                                            ?>
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
                <center><a class="btn btn-success mb-3 col-6 col-md-2" href="<?= BASE_URL; ?>/academico/calificaciones/ver/<?= $id_programacion_ud; ?>">Guardar</a></center>
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
        document.querySelectorAll('.nota-recuperacion').forEach(function(input) {
            input.addEventListener('change', function() {
                var val = this.value.trim();

                // Validar que sea número y esté entre 0 y 20
                if (isNaN(val) || val < 0 || val > 20) {
                    this.classList.add('border-danger');
                    this.classList.remove('border-success');
                    alert('El valor debe ser un número entre 0 y 20');
                    this.focus();
                    return;
                } else {
                    this.classList.remove('border-danger');
                    var id_detalle_mat = this.dataset.idRecuperacion;
                    var valor = this.value;

                    fetch('<?= BASE_URL ?>/academico/calificaciones/guardarRecuperacion', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_detalle_mat=' + encodeURIComponent(id_detalle_mat) +
                                '&valor=' + encodeURIComponent(valor)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                this.classList.add('border-success');
                                let label_final = document.getElementById('promedio_finalll_' + id_detalle_mat);
                                if (valor < 13) {
                                    this.classList.remove('text-primary');
                                    this.classList.add('text-danger');
                                    label_final.classList.remove('text-primary');
                                    label_final.classList.add('text-danger');
                                    label_final.innerHTML = valor;
                                } else {
                                    this.classList.remove('text-danger');
                                    this.classList.add('text-primary');
                                    label_final.classList.remove('text-danger');
                                    label_final.classList.add('text-primary');
                                    label_final.innerHTML = valor;
                                }
                                setTimeout(() => this.classList.remove('border-success'), 1500);
                            } else {
                                this.classList.add('border-danger');
                                alert(data.msg || 'Error al guardar');
                            }
                        })
                        .catch(err => {
                            this.classList.add('border-danger');
                            alert('Error en la petición.');
                        });
                }
            });
        });
    </script>
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