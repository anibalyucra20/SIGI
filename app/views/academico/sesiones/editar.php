<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if ($permitido): ?>
    <div class="card p-4 shadow-sm rounded-3 mt-3">
        <a class="btn btn-danger btn-sm btn-block col-sm-1 col-md-2 mb-1" href="<?= BASE_URL; ?>/academico/sesiones/ver/<?= $id_programacion; ?>">Regresar</a>
        <a href="<?= BASE_URL ?>/academico/sesiones/pdf/<?= $sesion['id']; ?>" class="btn btn-sm btn-outline-secondary col-md-2 mb-1" title="Imprimir" target="_blank"><i class="fa fa-print"></i> Imprimir</a>
        <?php if ($periodo_vigente): ?>
            <!-- Botón para abrir modal (Bootstrap 4) -->
            <button type="button"
                class="btn btn-primary btn-sm mb-2 col-md-2"
                data-toggle="modal"
                data-target="#modalCopiarSesion"
                id="btnAbrirModalCopiar">
                Copiar desde otra sesión
            </button>

            <!-- Modal Bootstrap 4 -->
            <div class="modal fade" id="modalCopiarSesion" tabindex="-1" role="dialog" aria-labelledby="modalCopiarSesionLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="modalCopiarSesionLabel">Copiar contenido desde otra sesión</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="selectSesionOrigen">Seleccione la sesión de origen</label>
                                <select id="selectSesionOrigen" class="form-control">
                                    <option value="">Cargando...</option>
                                </select>
                                <small class="form-text text-muted">
                                    Se listan las sesiones de esta misma programación de UD.
                                </small>
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="chkCopiarFechas">
                                <label class="form-check-label" for="chkCopiarFechas">
                                    Copiar también la fecha de desarrollo
                                </label>
                            </div>

                            <div class="alert alert-info mt-2" id="infoCopiarSesion" style="display:none;"></div>
                            <div class="alert alert-danger mt-2" id="errCopiarSesion" style="display:none;"></div>

                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btnConfirmarCopiar">Copiar ahora</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>


        <h4 class="text-center">Editar Sesión de Aprendizaje - <?= $sesion['semana']; ?></h4>
        <form action="<?= BASE_URL ?>/academico/sesiones/guardarEdicionSesion/<?= $sesion['id'] ?>" method="post" autocomplete="off">
            <h6 class="mb-2">I. INFORMACIÓN GENERAL</h6>
            <table class="table table table-striped table-bordered align-middle">
                <tbody>
                    <tr>
                        <td width="20%"><b>Programa de estudios:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['programa']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Módulo Formativo:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['modulo']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Unidad de competencia:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['unidad_competencia']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Unidad didáctica:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['unidad']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Periodo Lectivo:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['periodo_lectivo']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Periodo académico:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['periodo_academico']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Capacidad:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['capacidad']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Indicador de logro vinculado:</b></td>
                        <td><b><?= htmlspecialchars($datosUnidad['ind_logro_codigo']) ?></b>
                            <?= htmlspecialchars($datosUnidad['ind_logro_descripcion']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Competencia transversal priorizada:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['comp_transversal']) ?></td>
                    </tr>
                    <tr>
                        <td><b>Sesión de Aprendizaje:</b></td>
                        <td>
                            <?php if ($periodo_vigente): ?>
                                <input type="text" name="denominacion" class="form-control" maxlength="255" required value="<?= htmlspecialchars($sesion['denominacion']) ?>">
                            <?php else: ?>
                                <?= htmlspecialchars($sesion['denominacion']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Fecha de desarrollo:</b></td>
                        <td>
                            <?php if ($periodo_vigente): ?>
                                <input type="date" name="fecha_desarrollo" class="form-control col-4" required value="<?= htmlspecialchars($sesion['fecha_desarrollo']) ?>">
                            <?php else: ?>
                                <?= htmlspecialchars($sesion['fecha_desarrollo']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Tipo de actividad:</b></td>
                        <td>
                            <?php if ($periodo_vigente): ?>
                                <select name="tipo_actividad" class="form-control col-4" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Teórico-Práctico" <?= $sesion['tipo_actividad'] == 'Teórico-Práctico' ? 'selected' : '' ?>>Teórico-Práctico</option>
                                    <option value="Práctico" <?= $sesion['tipo_actividad'] == 'Práctico' ? 'selected' : '' ?>>Práctico</option>
                                    <option value="Teórico" <?= $sesion['tipo_actividad'] == 'Teórico' ? 'selected' : '' ?>>Teórico</option>
                                </select>
                            <?php else: ?>
                                <?= htmlspecialchars($sesion['tipo_actividad']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Logro de la sesión:</b></td>
                        <td>
                            <?php if ($periodo_vigente): ?>
                                <input type="text" name="logro_sesion" class="form-control" maxlength="1000" value="<?= htmlspecialchars($sesion['logro_sesion']) ?>">
                            <?php else: ?>
                                <?= htmlspecialchars($sesion['logro_sesion']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Docente responsable:</b></td>
                        <td><?= htmlspecialchars($datosUnidad['docente']) ?></td>
                    </tr>

                </tbody>
            </table>
            <hr>
            <h6 class="mb-2">II. ACTIVIDADES DE APRENDIZAJE</h6>
            <div class="table-responsive mb-3">
                <table class="table table-striped table-bordered ">
                    <thead class="table-light">
                        <tr class="bg-dark text-white">
                            <th>Momentos</th>
                            <th>Actividades de aprendizaje</th>
                            <th>Recursos didácticos</th>
                            <th>Tiempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($momentos as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['momento']) ?></td>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <textarea name="actividad_<?= $m['id'] ?>" class="form-control" rows="5" style="width:100%; resize: none; height:auto;" maxlength="2000"><?= htmlspecialchars($m['actividad']) ?></textarea>
                                    <?php else: ?>
                                        <?= htmlspecialchars($m['actividad']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <textarea name="recursos_<?= $m['id'] ?>" class="form-control" rows="5" style="width:100%; resize: none; height:auto;" maxlength="500"><?= htmlspecialchars($m['recursos']) ?></textarea>
                                    <?php else: ?>
                                        <?= htmlspecialchars($m['recursos']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <input type="number" min="1" max="999" name="tiempo_<?= $m['id'] ?>" class="form-control" value="<?= htmlspecialchars($m['tiempo']) ?>">
                                    <?php else: ?>
                                        <?= htmlspecialchars($m['tiempo']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <h6 class="mb-2">III. ACTIVIDADES DE EVALUACIÓN</h6>
            <div class="table-responsive mb-3">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="bg-dark text-white">
                            <th>Indicador de logro de la sesión</th>
                            <th>Técnicas</th>
                            <th>Instrumentos</th>
                            <th>Momento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activEval as $a): ?>
                            <tr>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <textarea name="indicador_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;" maxlength="300"><?= htmlspecialchars($a['indicador_logro_sesion']) ?></textarea>
                                    <?php else: ?>
                                        <?= htmlspecialchars($a['indicador_logro_sesion']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <textarea name="tecnica_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;" maxlength="300"><?= htmlspecialchars($a['tecnica']) ?></textarea>
                                    <?php else: ?>
                                        <?= htmlspecialchars($a['tecnica']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($periodo_vigente): ?>
                                        <textarea name="instrumentos_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;" maxlength="300"><?= htmlspecialchars($a['instrumentos']) ?></textarea>
                                    <?php else: ?>
                                        <?= htmlspecialchars($a['instrumentos']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($a['momento']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <h6 class="mb-2">IV. BIBLIOGRAFÍA (APA)</h6>
            <div class="mb-3">
                <?php if ($periodo_vigente): ?>
                    <textarea name="bibliografia" class="form-control" rows="4" style="width:100%; resize: none; height:auto;" maxlength="2000"><?= htmlspecialchars($sesion['bibliografia_obligatoria_docente']) ?></textarea>
                <?php else: ?>
                    <?= htmlspecialchars($sesion['bibliografia_obligatoria_docente']) ?>
                <?php endif; ?>

            </div>
            <div class="mt-3 text-end">
                <?php if ($periodo_vigente): ?>
                    <button type="submit" class="btn btn-success px-4">Guardar Cambios</button>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/academico/sesiones/ver/<?= $id_programacion ?>" class="btn btn-secondary">Cancelar</a>
            </div>


        </form>
    </div>
    <script>
        (function() {
            if (!(window.bootstrap && bootstrap.Modal) && window.jQuery) {
                // Bootstrap 4: mostrar el botón .close y ocultar el .btn-close
                var closeV4 = document.querySelector('#modalCopiarSesion .close');
                var closeV5 = document.querySelector('#modalCopiarSesion .btn-close');
                if (closeV4) closeV4.classList.remove('d-none');
                if (closeV5) closeV5.style.display = 'none';
            }
        })();
    </script>


<?php else: ?>
    <div class="alert alert-danger mt-4">
        <b>Acceso denegado:</b> Solo puede editar la sesión el administrador académico o el docente encargado de esta programación.
    </div>
<?php endif; ?>


<?php require __DIR__ . '/../../layouts/footer.php'; ?>
<script>
    (function($) {
        if (!$) {
            console.error('jQuery no está cargado todavía.');
            return;
        }

        $(function() {
            var BASE = '<?= BASE_URL ?>';
            var idProgramacion = <?= (int)$id_programacion ?>;
            var idSesionActual = <?= (int)$sesion['id'] ?>;

            // Carga sesiones en el select al abrir el modal
            $('#modalCopiarSesion').on('show.bs.modal', function() {
                var $sel = $('#selectSesionOrigen');
                $sel.html('<option value="">Cargando...</option>');
                $('#infoCopiarSesion').hide();
                $('#errCopiarSesion').hide();

                $.getJSON(BASE + '/academico/sesiones/listarPorProgramacion/' + idProgramacion, function(rows) {
                    var opts = ['<option value="">-- Seleccione --</option>'];
                    (rows || []).forEach(function(r) {
                        if (parseInt(r.id, 10) === idSesionActual) return; // excluir actual
                        var etiqueta = 'Semana ' + (r.semana || '') + ' - ' + (r.denominacion || '');
                        opts.push('<option value="' + r.id + '">' + etiqueta + '</option>');
                    });
                    if (opts.length === 1) {
                        opts = ['<option value="">No hay otras sesiones disponibles</option>'];
                    }
                    $sel.html(opts.join(''));
                }).fail(function() {
                    $sel.html('<option value="">Error al cargar</option>');
                });
            });

            // Confirmar copia
            $('#btnConfirmarCopiar').on('click', function() {
                var id_origen = $('#selectSesionOrigen').val();
                var copiar_fechas = $('#chkCopiarFechas').is(':checked') ? 1 : 0;

                $('#infoCopiarSesion').hide();
                $('#errCopiarSesion').hide();

                if (!id_origen) {
                    $('#errCopiarSesion').text('Seleccione una sesión de origen.').show();
                    return;
                }
                if (parseInt(id_origen, 10) === idSesionActual) {
                    $('#errCopiarSesion').text('La sesión de origen no puede ser la misma que la actual.').show();
                    return;
                }

                $.ajax({
                    url: BASE + '/academico/sesiones/copiarDesde',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_origen: id_origen,
                        id_destino: idSesionActual,
                        copiar_fechas: copiar_fechas
                    },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            $('#infoCopiarSesion').html('¡Listo! Contenido copiado. ' +
                                (resp.res ? ('Mom. act.: ' + (resp.res.momentos_actualizados || 0) + ' · Eval. act.: ' + (resp.res.activ_eval_actualizadas || 0)) : '')
                            ).show();
                            setTimeout(function() {
                                location.reload();
                            }, 700);
                        } else {
                            $('#errCopiarSesion').text(resp && resp.msg ? resp.msg : 'No fue posible copiar.').show();
                        }
                    },
                    error: function() {
                        $('#errCopiarSesion').text('Error de comunicación con el servidor.').show();
                    }
                });
            });

        });
    })(window.jQuery);
</script>