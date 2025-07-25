<?php require __DIR__ . '/../../layouts/header.php'; ?>
<!-- page content -->
<div class="card p-2">
     <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible">
            <?= $_SESSION['flash_success'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="">
                <h2 align="center"><b>INFORME FINAL - <?= htmlspecialchars($datosGenerales['unidad']) ?></b></h2>
                <a href="<?= BASE_URL ?>/academico/unidadesDidacticas/pdfInformeFinal/<?= $id_programacion; ?>" class="btn btn-info m-1">Imprimir</a> <br>
                <a href="<?= BASE_URL ?>/academico/unidadesDidacticas" class="btn btn-danger m-1">Regresar</a>
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
                                        <select name="supervisado" id="supervisado">
                                            <option value="1" <?= ($datosGenerales['supervisado'] == 1) ? "selected" : ""; ?>>SI</option>
                                            <option value="0" <?= ($datosGenerales['supervisado'] == 0) ? "selected" : ""; ?>>NO</option>
                                        </select>
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
                                        <select name="reg_evaluacion" id="reg_evaluacion">
                                            <option value="1" <?= ($datosGenerales['reg_evaluacion'] == 1) ? "selected" : ""; ?>>SI</option>
                                            <option value="0" <?= ($datosGenerales['reg_evaluacion'] == 0) ? "selected" : ""; ?>>NO</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Registro Auxiliar</td>
                                    <td>:
                                        <select name="reg_auxiliar" id="reg_auxiliar">
                                            <option value="1" <?= ($datosGenerales['reg_auxiliar'] == 1) ? "selected" : ""; ?>>SI</option>
                                            <option value="0" <?= ($datosGenerales['reg_auxiliar'] == 0) ? "selected" : ""; ?>>NO</option>
                                        </select>
                                </tr>
                                <tr>
                                    <td>Programación Curricular</td>
                                    <td>:
                                        <select name="prog_curricular" id="prog_curricular">
                                            <option value="1" <?= ($datosGenerales['prog_curricular'] == 1) ? "selected" : ""; ?>>SI</option>
                                            <option value="0" <?= ($datosGenerales['prog_curricular'] == 0) ? "selected" : ""; ?>>NO</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Otros</td>
                                    <td>:
                                        <select name="otros" id="otros">
                                            <option value="1" <?= ($datosGenerales['otros'] == 1) ? "selected" : ""; ?>>SI</option>
                                            <option value="0" <?= ($datosGenerales['otros'] == 0) ? "selected" : ""; ?>>NO</option>
                                        </select>
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
                                        <textarea name="logros_obtenidos" style="width:100%; resize: none; height:auto;" rows="3"><?= $datosGenerales['logros_obtenidos']?></textarea>
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
                                        <textarea name="dificultades" style="width:100%; resize: none; height:auto;" rows="3"><?= $datosGenerales['dificultades']?></textarea>
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
                                        <textarea name="sugerencias" style="width:100%; resize: none; height:auto;" rows="3"><?= $datosGenerales['sugerencias']?></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div align="center">
                        <br>
                        <br>
                        <a href="calificaciones_unidades_didacticas.php" class="btn btn-danger">Regresar</a>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>