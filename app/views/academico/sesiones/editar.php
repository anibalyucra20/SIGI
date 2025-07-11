<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if ($permitido): ?>
    <div class="card p-4 shadow-sm rounded-3 mt-3">
        <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/sesiones/ver/<?= $id_programacion; ?>">Regresar</a>
        <h4>Editar Sesión de Aprendizaje</h4>
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
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
                        <td><input type="text" name="denominacion" class="form-control" maxlength="200" required value="<?= htmlspecialchars($sesion['denominacion']) ?>"></td>
                    </tr>
                    <tr>
                        <td><b>Fecha de desarrollo:</b></td>
                        <td><input type="date" name="fecha_desarrollo" class="form-control col-4" required value="<?= htmlspecialchars($sesion['fecha_desarrollo']) ?>"></td>
                    </tr>
                    <tr>
                        <td><b>Tipo de actividad:</b></td>
                        <td><select name="tipo_actividad" class="form-control col-4" required>
                                <option value="">Seleccione...</option>
                                <option value="Teórico-Práctico" <?= $sesion['tipo_actividad'] == 'Teórico-Práctico' ? 'selected' : '' ?>>Teórico-Práctico</option>
                                <option value="Práctico" <?= $sesion['tipo_actividad'] == 'Práctico' ? 'selected' : '' ?>>Práctico</option>
                                <option value="Teórico" <?= $sesion['tipo_actividad'] == 'Teórico' ? 'selected' : '' ?>>Teórico</option>
                            </select></td>
                    </tr>
                    <tr>
                        <td><b>Logro de la sesión:</b></td>
                        <td><input type="text" name="logro_sesion" class="form-control" maxlength="1000" value="<?= htmlspecialchars($sesion['logro_sesion']) ?>"></td>
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
                                    <textarea name="actividad_<?= $m['id'] ?>" class="form-control" rows="5" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($m['actividad']) ?></textarea>
                                </td>
                                <td>
                                    <textarea name="recursos_<?= $m['id'] ?>" class="form-control" rows="5" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($m['recursos']) ?></textarea>
                                </td>
                                <td>
                                    <input type="number" min="1" max="999" name="tiempo_<?= $m['id'] ?>" class="form-control" value="<?= htmlspecialchars($m['tiempo']) ?>">
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
                                    <textarea name="indicador_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($a['indicador_logro_sesion']) ?></textarea>
                                </td>
                                <td>
                                    <textarea name="tecnica_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($a['tecnica']) ?></textarea>
                                </td>
                                <td>
                                    <textarea name="instrumentos_<?= $a['id'] ?>" class="form-control" rows="3" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($a['instrumentos']) ?></textarea>
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
                <textarea name="bibliografia" class="form-control" rows="4" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($sesion['bibliografia_obligatoria_docente']) ?></textarea>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-success px-4">Guardar Cambios</button>
                <a href="<?= BASE_URL ?>/academico/sesiones/ver/<?= $id_programacion ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-danger mt-4">
        <b>Acceso denegado:</b> Solo puede editar la sesión el administrador académico o el docente encargado de esta programación.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>