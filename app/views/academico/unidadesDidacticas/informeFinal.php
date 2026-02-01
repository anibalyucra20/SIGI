<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>
    <!-- page content -->
    <div class="card p-2">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="">
                    <h2 align="center"><b>INFORME FINAL - <?= htmlspecialchars($datosGenerales['unidad']) ?></b></h2>
                    <a href="<?= BASE_URL ?>/academico/unidadesDidacticas/pdfInformeFinal/<?= $id_programacion; ?>" class="btn btn-info m-1" target="_blank">Imprimir</a> <br>
                    <?php if (\Core\Auth::esDocenteAcademico()): ?>
                        <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
                    <?php endif; ?>
                    <?php if (\Core\Auth::esAdminAcademico()): ?>
                        <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br>

                    <form role="form" action="<?= BASE_URL ?>/academico/unidadesDidacticas/guardarEdicionInformeFinal/<?= $id_programacion ?>" class="form-horizontal form-label-left input_mask" method="POST">
                        <input type="hidden" name="id_prog" value="<?php echo $id_programacion; ?>">
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                    <tr>
                                        <th colspan="2">
                                            <center>SOBRE LA SUPERVISIÓN Y EVALUACIÓN</center>
                                        </th>
                                    </tr>
                                    <tr>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="30%">Fue Supervisado</td>
                                        <td>:
                                            <?php if ($periodo_vigente): ?>
                                                <select name="supervisado" id="supervisado">
                                                    <option value="1" <?= ($datosGenerales['supervisado'] == 1) ? "selected" : ""; ?>>SI</option>
                                                    <option value="0" <?= ($datosGenerales['supervisado'] == 0) ? "selected" : ""; ?>>NO</option>
                                                </select>
                                            <?php else: ?>
                                                <?= ($datosGenerales['supervisado'] == 1) ? "SI" : "NO"; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">
                                            <center>DOCUMENTOS DE EVALUACIÓN UTILIZADAS</center>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>Registro de Evaluación</td>
                                        <td>:
                                            <?php if ($periodo_vigente): ?>
                                                <select name="reg_evaluacion" id="reg_evaluacion">
                                                    <option value="1" <?= ($datosGenerales['reg_evaluacion'] == 1) ? "selected" : ""; ?>>SI</option>
                                                    <option value="0" <?= ($datosGenerales['reg_evaluacion'] == 0) ? "selected" : ""; ?>>NO</option>
                                                </select>
                                            <?php else: ?>
                                                <?= ($datosGenerales['reg_evaluacion'] == 1) ? "SI" : "NO"; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Registro Auxiliar</td>
                                        <td>:
                                            <?php if ($periodo_vigente): ?>
                                                <select name="reg_auxiliar" id="reg_auxiliar">
                                                    <option value="1" <?= ($datosGenerales['reg_auxiliar'] == 1) ? "selected" : ""; ?>>SI</option>
                                                    <option value="0" <?= ($datosGenerales['reg_auxiliar'] == 0) ? "selected" : ""; ?>>NO</option>
                                                </select>
                                            <?php else: ?>
                                                <?= ($datosGenerales['reg_auxiliar'] == 1) ? "SI" : "NO"; ?>
                                            <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td>Programación Curricular</td>
                                        <td>:
                                            <?php if ($periodo_vigente): ?>
                                                <select name="prog_curricular" id="prog_curricular">
                                                    <option value="1" <?= ($datosGenerales['prog_curricular'] == 1) ? "selected" : ""; ?>>SI</option>
                                                    <option value="0" <?= ($datosGenerales['prog_curricular'] == 0) ? "selected" : ""; ?>>NO</option>
                                                </select>
                                            <?php else: ?>
                                                <?= ($datosGenerales['prog_curricular'] == 1) ? "SI" : "NO"; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Otros</td>
                                        <td>:
                                            <?php if ($periodo_vigente): ?>
                                                <select name="otros" id="otros">
                                                    <option value="1" <?= ($datosGenerales['otros'] == 1) ? "selected" : ""; ?>>SI</option>
                                                    <option value="0" <?= ($datosGenerales['otros'] == 0) ? "selected" : ""; ?>>NO</option>
                                                </select>
                                            <?php else: ?>
                                                <?= ($datosGenerales['otros'] == 1) ? "SI" : "NO"; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                    <tr>
                                        <th>
                                            <center>LOGROS OBTENIDOS</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php if ($periodo_vigente): ?>
                                                <textarea name="logros_obtenidos" style="width:100%; resize: none; height:auto;" rows="3" maxlength="500"><?= $datosGenerales['logros_obtenidos'] ?></textarea>
                                            <?php else: ?>
                                                <?= htmlspecialchars($datosGenerales['logros_obtenidos']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                    <tr>
                                        <th>
                                            <center>DIFICULTADES</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php if ($periodo_vigente): ?>
                                                <textarea name="dificultades" style="width:100%; resize: none; height:auto;" rows="3" maxlength="500"><?= $datosGenerales['dificultades'] ?></textarea>
                                            <?php else: ?>
                                                <?= htmlspecialchars($datosGenerales['dificultades']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped jambo_table bulk_action">
                                <thead>
                                    <tr>
                                        <th>
                                            <center>SUGERENCIAS</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php if ($periodo_vigente): ?>
                                                <textarea name="sugerencias" style="width:100%; resize: none; height:auto;" rows="3" maxlength="500"><?= $datosGenerales['sugerencias'] ?></textarea>
                                            <?php else: ?>
                                                <?= htmlspecialchars($datosGenerales['sugerencias']) ?>
                                            <?php endif; ?>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div align="center">
                            <br>
                            <br>
                            <?php if (\Core\Auth::esDocenteAcademico()): ?>
                                <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::esAdminAcademico()): ?>
                                <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
                            <?php endif; ?>
                            <?php if ($periodo_vigente): ?>
                                <button type="submit" class="btn btn-success">Guardar</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>No tiene permisos para editar el Informe Final o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>