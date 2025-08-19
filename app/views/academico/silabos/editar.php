<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>
    <form action="<?= BASE_URL ?>/academico/silabos/guardarEdicion" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off">
        <input type="hidden" name="id_silabo" value="<?= htmlspecialchars($silabo['id']) ?>">

        <?php if (\Core\Auth::esDocenteAcademico()): ?>
            <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
        <?php endif; ?>
        <?php if (\Core\Auth::esAdminAcademico()): ?>
            <a class="btn btn-danger btn-sm btn-block col-sm-1 col-4 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/academico/silabos/pdf/<?= htmlspecialchars($silabo['id']) ?>" class="btn btn-sm btn-outline-secondary col-md-1" title="Imprimir" target="_blank"><i class="fa fa-print"></i> Imprimir</a>
        <!-- I. DATOS GENERALES -->
         <?php
         var_dump($datosGenerales);

         
         ?>
        <h5 class="mb-3 mt-2">I. DATOS GENERALES</h5>
        <table class="table table-bordered mb-3">
            <tbody>
                <tr>
                    <td width="30%"><b>Programa de Estudios:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['programa']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Módulo Formativo:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['modulo']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Unidad Didáctica:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['unidad']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Créditos:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['creditos']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Horas Totales:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['horas_totales']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Horas Semanales:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['horas_semanales']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Periodo Lectivo:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['periodo_lectivo']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Periodo Académico:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['periodo_academico']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Fecha Inicio:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['fecha_inicio']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Fecha Fin:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['fecha_fin']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Turno:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['turno']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Horario:</b></td>
                    <td>
                        <?php if ($periodo_vigente): ?>
                            <textarea name="horario" style="width:50%; resize: none; height:auto;" rows="3" maxlength="200"><?= htmlspecialchars($silabo['horario']) ?></textarea>
                        <?php else: ?>
                            <?= ($silabo['horario']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td width="30%"><b>Docente:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['docente']) ?></td>
                </tr>
                <tr>
                    <td width="30%"><b>Correo Institucional:</b></td>
                    <td><?= htmlspecialchars($datosGenerales['correo_docente']) ?></td>
                </tr>

            </tbody>
        </table>

        <!-- II. SUMILLA -->
        <h5 class="mt-4 mb-2">II. SUMILLA</h5>
        <?php if ($periodo_vigente): ?>
            <textarea name="sumilla" class="form-control mb-2" rows="5" maxlength="2000" style="width:100%; resize: none; height:auto;"><?= htmlspecialchars($silabo['sumilla'] ?? '') ?></textarea>
        <?php else: ?>
            <?= ($silabo['sumilla'] ?? '') ?>
        <?php endif; ?>


        <!-- III. UNIDAD DE COMPETENCIA ESPECÍFICA O TÉCNICA DEL MÓDULO -->
        <h5 class="mt-4 mb-2">III. UNIDAD DE COMPETENCIA ESPECÍFICA O TÉCNICA DEL MÓDULO</h5>
        <div class="card card-body mb-3 bg-light">
            <?php foreach ($competenciasUnidadDidactica as $comp): ?>
                <b><?= htmlspecialchars($comp['codigo']) ?>:</b> <?= htmlspecialchars($comp['descripcion']) ?><br>
                <!--<u>Indicadores:</u>
                <ul>
                    <?php foreach ($comp['indicadores'] as $ind): ?>
                        <li><?= htmlspecialchars($ind['descripcion']) ?></li>
                    <?php endforeach; ?>
                </ul>-->
            <?php endforeach; ?>
        </div>

        <!-- IV. CAPACIDADES DE LA UNIDAD DIDÁCTICA -->
        <h5 class="mt-4 mb-2">IV. CAPACIDADES DE LA UNIDAD DIDÁCTICA</h5>
        <table class="table table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th width="50%">Capacidad</th>
                    <th>Indicadores de Logro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($capacidades as $cap): ?>
                    <tr>
                        <td><?= htmlspecialchars($cap['descripcion']) ?></td>
                        <td>
                            <ul class="mb-0">
                                <?php foreach ($cap['indicadores'] as $ind): ?>
                                    <li><?= htmlspecialchars($ind['descripcion']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- V. COMPETENCIA PARA LA EMPLEABILIDAD COMO CONTENIDO TRANSVERSAL -->
        <h5 class="mt-4 mb-2">V. COMPETENCIA PARA LA EMPLEABILIDAD COMO CONTENIDO TRANSVERSAL</h5>
        <table class="table table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th width="50%">Competencia para la empleabilidad como contenido transversal</th>
                    <th>Estrategias</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competenciasTransversales as $ct): ?>
                    <tr>
                        <td><?= htmlspecialchars($ct['codigo']) ?>:</b> <?= htmlspecialchars($ct['descripcion']) ?></td>
                        <td>
                            <ul class="mb-0">
                                <?php foreach ($ct['estrategias'] as $estr): ?>
                                    <li><?= htmlspecialchars($estr) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- VI. PROGRAMACIÓN DE SESIONES DE APRENDIZAJE -->
        <h5 class="mt-4 mb-2">VI. PROGRAMACIÓN DE SESIONES DE APRENDIZAJE</h5>
        <div class="table-responsive">
            <table class="table table-bordered mb-3">
                <thead class="table-light">
                    <tr>
                        <th>Semana / Fecha</th>
                        <th width="15%">Indicador de Logro de Capacidad</th>
                        <th>Denominación de la Sesión</th>
                        <th>Contenido</th>
                        <th>Logro de la Sesión</th>
                        <th>Tareas Previas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sesiones as $sesion): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($sesion['semana']) ?> <br>
                                <?php if ($periodo_vigente): ?>
                                    <input type="date" class="form-control" name="sesiones[<?= $sesion['id_actividad'] ?>][fecha]" value="<?= htmlspecialchars($sesion['fecha']) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($sesion['fecha']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <select name="sesiones[<?= $sesion['id_actividad'] ?>][id_ind_logro_aprendizaje]" class="form-control">
                                        <?php foreach ($indicadoresLogroCapacidad as $ilc): ?>
                                            <option value="<?= $ilc['id'] ?>" <?= ($sesion['id_ind_logro_aprendizaje'] == $ilc['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ilc['codigo_capacidad'] . '.' . $ilc['codigo']) ?> - <?= htmlspecialchars($ilc['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <?= (htmlspecialchars($sesion['desc_ind_logro'])) ?>
                                <?php endif; ?>

                            </td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <textarea name="sesiones[<?= $sesion['id_actividad'] ?>][denominacion]" rows="5" class="form-control" style="width:100%; resize: none; height:auto;" maxlength="255"><?= (htmlspecialchars($sesion['denominacion'])) ?></textarea>
                                <?php else: ?>
                                    <?= (htmlspecialchars($sesion['denominacion'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <textarea name="sesiones[<?= $sesion['id_actividad'] ?>][contenido]" rows="5" class="form-control" style="width:100%; resize: none; height:auto;" maxlength="1000"><?= (htmlspecialchars($sesion['contenidos_basicos'])) ?></textarea>
                                <?php else: ?>
                                    <?= (htmlspecialchars($sesion['contenidos_basicos'])) ?>
                                <?php endif; ?>

                            </td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <textarea name="sesiones[<?= $sesion['id_actividad'] ?>][logro_sesion]" rows="5" class="form-control" style="width:100%; resize: none; height:auto;" maxlength="1000"><?= (htmlspecialchars($sesion['logro_sesion'])) ?></textarea>
                                <?php else: ?>
                                    <?= (htmlspecialchars($sesion['logro_sesion'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($periodo_vigente): ?>
                                    <textarea name="sesiones[<?= $sesion['id_actividad'] ?>][tareas_previas]" rows="5" class="form-control" style="width:100%; resize: none; height:auto;"  maxlength="500"><?= (htmlspecialchars($sesion['tareas_previas'])) ?></textarea>
                                <?php else: ?>
                                    <?= (htmlspecialchars($sesion['tareas_previas'])) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <!-- VII. METODOLOGÍA -->
        <h5 class="mt-4 mb-2">VII. METODOLOGÍA</h5>
        <?php if ($periodo_vigente): ?>
            <textarea name="metodologia" class="form-control mb-2" rows="3" maxlength="1000" style="width:100%; resize: none; height:auto;"><?= (htmlspecialchars($silabo['metodologia'] ?? '')) ?></textarea>
        <?php else: ?>
            <?= (htmlspecialchars($silabo['metodologia'] ?? '')) ?>
        <?php endif; ?>

        <!-- VIII. AMBIENTES Y RECURSOS -->
        <h5 class="mt-4 mb-2">VIII. AMBIENTES Y RECURSOS</h5>
        <?php if ($periodo_vigente): ?>
            <textarea name="recursos_didacticos" class="form-control mb-2" rows="3" maxlength="1000" style="width:100%; resize: none; height:auto;"><?= (htmlspecialchars($silabo['recursos_didacticos'] ?? '')) ?></textarea>
        <?php else: ?>
            <?= (htmlspecialchars($silabo['recursos_didacticos'] ?? '')) ?>
        <?php endif; ?>

        <!-- IX. SISTEMA DE EVALUACIÓN -->
        <h5 class="mt-4 mb-2">IX. SISTEMA DE EVALUACIÓN</h5>
        <?php if ($periodo_vigente): ?>
            <textarea name="sistema_evaluacion" class="form-control mb-2" rows="4" maxlength="1000" style="width:100%; resize: none; height:auto;"><?= (htmlspecialchars($silabo['sistema_evaluacion'] ?? '')) ?></textarea>
        <?php else: ?>
            <?= (htmlspecialchars($silabo['sistema_evaluacion'] ?? '')) ?>
        <?php endif; ?>

        <!-- X. FUENTES DE INFORMACIÓN -->
        <h5 class="mt-4 mb-2">X. FUENTES DE INFORMACIÓN</h5>
        <div class="row mb-2">
            <div class="col-md-6">
                <label><b>10.1 Bibliografía (Impresos)</b></label>
                <?php if ($periodo_vigente): ?>
                    <textarea name="recursos_bibliograficos_impresos" class="form-control" rows="3" maxlength="2000" style="width:100%; resize: none; height:auto;"><?= (htmlspecialchars($silabo['recursos_bibliograficos_impresos'] ?? '')) ?></textarea>
                <?php else: ?>
                    <?= (htmlspecialchars($silabo['recursos_bibliograficos_impresos'] ?? '')) ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label><b>10.2 URL (digitales)</b></label>
                <?php if ($periodo_vigente): ?>
                    <textarea name="recursos_bibliograficos_digitales" class="form-control" rows="3" maxlength="2000" style="width:100%; resize: none; height:auto;"><?= (htmlspecialchars($silabo['recursos_bibliograficos_digitales'] ?? '')) ?></textarea>
                <?php else: ?>
                    <?= (htmlspecialchars($silabo['recursos_bibliograficos_digitales'] ?? '')) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3 text-end">
            <?php if ($periodo_vigente): ?>
                <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
            <?php endif; ?>
            <br>
            <br>
            <?php if (\Core\Auth::esDocenteAcademico()): ?>
                <a class="btn btn-danger btn-sm btn-block col-md-2 mb-1 px-4" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">Regresar</a>
            <?php endif; ?>
            <?php if (\Core\Auth::esAdminAcademico()): ?>
                <a class="btn btn-danger btn-sm btn-block col-md-2 mb-1 px-4" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">Regresar</a>
            <?php endif; ?>

        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // todos los inputs de fecha en VI. Programación de sesiones
            const dateInputs = Array.from(document.querySelectorAll('input[type="date"][name^="sesiones"][name$="[fecha]"]'));
            if (dateInputs.length < 2) return;

            const first = dateInputs[0];

            // sumar días a una fecha YYYY-MM-DD
            function addDays(yyyyMMdd, days) {
                const [y, m, d] = yyyyMMdd.split('-').map(Number);
                const dt = new Date(y, m - 1, d);
                dt.setDate(dt.getDate() + days);
                const yy = dt.getFullYear();
                const mm = String(dt.getMonth() + 1).padStart(2, '0');
                const dd = String(dt.getDate()).padStart(2, '0');
                return `${yy}-${mm}-${dd}`;
            }

            // limitar por min/max (ambos en formato YYYY-MM-DD)
            function clamp(val, min, max) {
                if (min && val < min) return min;
                if (max && val > max) return max;
                return val;
            }

            function autopoblarDesdePrimera() {
                if (!first.value) return; // no hay base
                let prev = first.value;
                for (let i = 1; i < dateInputs.length; i++) {
                    let next = addDays(prev, 7); // +1 semana
                    const min = dateInputs[i].getAttribute('min') || '';
                    const max = dateInputs[i].getAttribute('max') || '';
                    next = clamp(next, min, max); // respeta ventana del período
                    dateInputs[i].value = next;
                    prev = next;
                }
            }
            // cuando el usuario elija la primera fecha, se completan las demás
            first.addEventListener('change', autopoblarDesdePrimera);

            // OPCIONAL: si quieres que cargue ya autocompletado si la primera viene con valor
            // autopoblarDesdePrimera();
        });
    </script>

<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>