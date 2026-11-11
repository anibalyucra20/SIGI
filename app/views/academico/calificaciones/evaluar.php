<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php';
?>

<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>
    <?php
    // Para la cabecera y los filtros móviles tomamos el primer estudiante que tenga evaluaciones:
    $anyEv = [];
    foreach ($estudiantes as $e) {
        if (!empty($evaluacionesEstudiante[$e['id_detalle_matricula']])) {
            $anyEv = $evaluacionesEstudiante[$e['id_detalle_matricula']];
            break;
        }
    }
    ?>
    <div class="card p-2 p-md-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <a class="btn btn-danger btn-sm mr-2 mb-1" href="<?= BASE_URL; ?>/academico/calificaciones/ver/<?php echo $id_programacion_ud; ?>">
                    <i class="fas fa-arrow-left mr-1"></i> Regresar
                </a>
                <a class="btn btn-info btn-sm mr-2 mb-1" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/registroAuxiliar/<?= $id_programacion_ud ?>/<?= $nro_calificacion ?>">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </a>
            </div>
            <div class="d-md-none btn-group btn-group-sm mb-1" role="group" aria-label="Modo de vista">
                <button type="button" class="btn btn-primary active" id="btn-eval-modo-cards">
                    <i class="fas fa-th-list mr-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-primary" id="btn-eval-modo-tabla">
                    <i class="fas fa-table mr-1"></i> Tabla
                </button>
            </div>
        </div>

        <h5 class="text-center font-weight-bold mb-2 px-2" style="color:#607d8b; font-size: clamp(1rem, 2.5vw, 1.25rem);">
            Evaluación - Indicador <?= $nro_calificacion ?>
            - <span style="color:#4153a1;"><?php echo strtoupper($nombreUnidadDidactica ?? ''); ?></span>
        </h5>
        <h6 class="text-center mb-3 px-2" style="font-size: clamp(0.85rem, 2vw, 1rem);">
            <b>Indicador de Logro: </b><?= htmlspecialchars($indicadores_capacidad['I' . $nro_calificacion] ?? $indicadores_capacidad[$nro_calificacion] ?? ($nombreIndicador ?? '')) ?>
        </h6>

        <!-- PANEL MÓVIL DE CALIFICACIÓN (< 768px) -->
        <div id="mobile-evaluar-panel" class="d-md-none mb-3">
            <div class="bg-light border rounded p-2 mb-3 mobile-eval-sticky">
                <div class="form-row">
                    <div class="col-6 mb-2">
                        <label for="mobile-eval-aspecto" class="small font-weight-bold text-muted mb-1">Aspecto</label>
                        <select id="mobile-eval-aspecto" class="form-control form-control-sm">
                            <option value="all">Todos los aspectos</option>
                            <?php foreach ($anyEv as $iEval => $eval): ?>
                                <option value="<?= htmlspecialchars((string)$iEval) ?>">
                                    <?= htmlspecialchars($eval['detalle']) ?> (<?= $eval['ponderado'] ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label for="mobile-eval-criterio" class="small font-weight-bold text-muted mb-1">Criterio</label>
                        <select id="mobile-eval-criterio" class="form-control form-control-sm">
                            <option value="all">Todos los criterios</option>
                            <?php foreach ($anyEv as $iEval => $eval): ?>
                                <?php foreach ($eval['criterios'] as $iCrit => $crit): ?>
                                    <option value="<?= htmlspecialchars($iEval . '_' . $iCrit) ?>" data-aspecto-idx="<?= htmlspecialchars((string)$iEval) ?>">
                                        <?= htmlspecialchars($eval['detalle'] . ' › ' . $crit['detalle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="input-group input-group-sm mb-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="search" id="mobile-eval-buscar" class="form-control" placeholder="Buscar estudiante o DNI...">
                </div>

                <?php if ($periodo_vigente['vigente']): ?>
                    <div id="mobile-eval-acciones" class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                        <button type="button" class="btn btn-success btn-sm d-none" id="mob-btn-agregar-crit">
                            <i class="fas fa-plus mr-1"></i> Nuevo criterio
                        </button>
                        <button type="button" class="btn btn-warning btn-sm d-none" id="mob-btn-vincular-moodle">
                            <i class="fas fa-link mr-1"></i> Vincular Moodle
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm d-none" id="mob-btn-sync-moodle">
                            <i class="fas fa-cloud-download-alt mr-1"></i> Sync Moodle
                        </button>
                        <small class="text-muted" id="mob-eval-hint">Las notas (0–20) se guardan automáticamente al salir del campo.</small>
                    </div>
                <?php endif; ?>
            </div>

            <div id="mobile-evaluar-cards">
                <?php foreach ($estudiantes as $idx => $est):
                    $id_detalle = $est['id_detalle_matricula'];
                    $evals = $evaluacionesEstudiante[$id_detalle] ?? [];
                    $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                    $motivo = 'Inasistencia';
                    if ($est['licencia'] != '') {
                        $motivo = 'Licencia';
                        $inhabilitado = $id_detalle;
                    }
                    $promFinalEst = $promedioFinal[$id_detalle] ?? '';
                    $esRojoFinal = ($promFinalEst !== '' && is_numeric($promFinalEst) && $promFinalEst < 13) || $inhabilitado;
                ?>
                    <div class="eval-student-card <?= $inhabilitado ? 'border-danger bg-light' : '' ?>"
                         data-nombre="<?= htmlspecialchars(strtolower($est['apellidos_nombres'])) ?>"
                         data-dni="<?= htmlspecialchars(strtolower($est['dni'])) ?>">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                            <div class="pr-2" style="min-width: 0;">
                                <div class="font-weight-bold text-dark" style="font-size: 14px; line-height: 1.25;">
                                    <?= ($idx + 1) ?>. <?= htmlspecialchars($est['apellidos_nombres']) ?>
                                </div>
                                <div class="small text-muted">
                                    DNI: <?= htmlspecialchars($est['dni']) ?>
                                    <?php if ($inhabilitado): ?>
                                        <span class="badge badge-danger ml-1"><?= $motivo ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-muted" style="font-size: 10px;">PROMEDIO</div>
                                <span class="badge <?= $esRojoFinal ? 'badge-danger' : 'badge-primary' ?> px-2 py-1" style="font-size: 14px; min-width: 34px;">
                                    <?= ($promFinalEst !== '' && $promFinalEst !== null) ? htmlspecialchars((string)$promFinalEst) : '—' ?>
                                </span>
                            </div>
                        </div>

                        <?php foreach ($evals as $iEval => $eval):
                            $prom_eval = $promediosEvaluacion[$id_detalle][$eval['id']] ?? '';
                            $clsPromEval = ($prom_eval !== '' && is_numeric($prom_eval)) ? ($prom_eval < 13 ? 'text-danger' : 'text-primary') : 'text-muted';
                        ?>
                            <div class="eval-aspecto-group mb-2" data-aspecto-idx="<?= htmlspecialchars((string)$iEval) ?>">
                                <div class="d-flex justify-content-between align-items-center bg-light rounded px-2 py-1 mb-1">
                                    <span class="small font-weight-bold text-dark">
                                        <?= htmlspecialchars($eval['detalle']) ?> (<?= $eval['ponderado'] ?>%)
                                    </span>
                                    <span class="small font-weight-bold <?= $clsPromEval ?>">
                                        Prom: <?= $prom_eval !== '' ? htmlspecialchars((string)$prom_eval) : '—' ?>
                                    </span>
                                </div>

                                <?php foreach ($eval['criterios'] as $iCrit => $criterio):
                                    $calif = $criterio['calificacion'] ?? '';
                                    $clsCalif = ($calif !== '' && is_numeric($calif) && $calif < 13) ? 'text-danger' : 'text-primary';
                                ?>
                                    <div class="eval-criterio-row d-flex justify-content-between align-items-center py-1 px-1 border-bottom"
                                         data-aspecto-idx="<?= htmlspecialchars((string)$iEval) ?>"
                                         data-criterio-key="<?= htmlspecialchars($iEval . '_' . $iCrit) ?>">
                                        <div class="pr-2" style="min-width: 0;">
                                            <div class="text-dark" style="font-size: 13px; line-height: 1.25;">
                                                <?= htmlspecialchars($criterio['detalle']) ?>
                                            </div>
                                            <small class="js-mobile-nota-status text-muted" style="font-size: 11px;"></small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <?php if ($inhabilitado || !$periodo_vigente['vigente']): ?>
                                                <span class="font-weight-bold <?= $inhabilitado ? 'text-danger' : $clsCalif ?>" style="font-size: 15px;">
                                                    <?= $calif !== '' ? htmlspecialchars((string)$calif) : '—' ?>
                                                </span>
                                            <?php else: ?>
                                                <input type="number"
                                                    inputmode="numeric"
                                                    enterkeyhint="next"
                                                    min="0"
                                                    max="20"
                                                    class="form-control form-control-sm text-center font-weight-bold js-mobile-nota <?= $clsCalif ?>"
                                                    data-sync-criterio="<?= $criterio['id'] ?>"
                                                    value="<?= htmlspecialchars((string)$calif) ?>"
                                                    style="width: 74px; height: 38px; font-size: 16px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="wrapper-tabla-evaluar" class="table-responsive d-none d-md-block">
            <table class="table table-bordered" id="tabla-evaluar" style="font-size:14px;">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle text-center">ORDEN</th>
                        <th rowspan="2" class="align-middle text-center">DNI</th>
                        <th rowspan="2" class="align-middle text-center sticky-nombre-eval">APELLIDOS Y NOMBRES</th>
                        <?php foreach ($anyEv as $iEval => $eval): ?>
                            <th colspan="<?= count($eval['criterios']) + 1 ?>" class="text-center" style="background:#f5f7fa;">
                                <?= htmlspecialchars($eval['detalle']) ?><br>
                                <span class="small">
                                    Ponderado:
                                    <span class="ponderado-eval" data-i-eval="<?= $iEval ?>"><?= $eval['ponderado'] ?></span>%
                                    <?php if ($periodo_vigente['vigente']): ?>
                                        <button class="btn btn-primary btn-sm btn-editar-ponderado"
                                            data-i-eval="<?= $iEval ?>"
                                            data-ids-eval="<?php
                                                            // Obtener todos los ids de esa evaluación en todos los estudiantes
                                                            $ids = [];
                                                            foreach ($estudiantes as $est) {
                                                                $id_detalle = $est['id_detalle_matricula'];
                                                                if (!empty($evaluacionesEstudiante[$id_detalle][$iEval])) {
                                                                    $ids[] = $evaluacionesEstudiante[$id_detalle][$iEval]['id'];
                                                                }
                                                            }
                                                            echo implode(',', $ids);
                                                            ?>">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                    <?php endif; ?>
                                </span>
                                <div class="mt-1">
                                    <?php
                                    $ids_eval_columna = [];
                                    foreach ($estudiantes as $est) {
                                        $id_detalle = $est['id_detalle_matricula'];
                                        if (isset($evaluacionesEstudiante[$id_detalle][$iEval]['id'])) {
                                            $ids_eval_columna[] = $evaluacionesEstudiante[$id_detalle][$iEval]['id'];
                                        }
                                    }
                                    ?>
                                    <?php if ($periodo_vigente['vigente']): ?>
                                        <button class="btn btn-success btn-sm btn-agregar-criterio"
                                            data-aspecto-idx="<?= htmlspecialchars((string)$iEval) ?>"
                                            data-eval-ids="<?= implode(',', $ids_eval_columna) ?>"
                                            data-detalle="<?= htmlspecialchars($eval['detalle']) ?>"> <!-- NUEVO -->
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <th rowspan="2" class="align-middle text-center" style="writing-mode:vertical-rl; transform: rotate(180deg); background:#f5f7fa; width:40px;">PROMEDIO DE CALIFICACIÓN</th>
                    </tr>
                    <?php
                    // Suponiendo que $estudiantes y $evaluacionesEstudiante están definidos
                    $criterio_ids_columnas = [];
                    // Recorre los criterios de la "plantilla" (primer estudiante con evaluaciones)
                    foreach ($anyEv as $iEval => $eval) {
                        foreach ($eval['criterios'] as $iCrit => $crit) {
                            $ids = [];
                            foreach ($estudiantes as $est) {
                                $id_detalle = $est['id_detalle_matricula'];
                                // Si el estudiante tiene evaluaciones:
                                if (!empty($evaluacionesEstudiante[$id_detalle][$iEval]['criterios'][$iCrit])) {
                                    $ids[] = $evaluacionesEstudiante[$id_detalle][$iEval]['criterios'][$iCrit]['id'];
                                }
                            }
                            $criterio_ids_columnas[$iEval][$iCrit] = $ids; // guarda para cada columna
                        }
                    }

                    ?>

                    <tr>
                        <?php
                        $cont = 0;
                        foreach ($anyEv as $iEval => $eval):
                            $cont++;
                        ?>
                            <?php foreach ($eval['criterios'] as $iCrit => $criterio): ?>
                                <?php

                                // 1. Identificadores robustos
                                $aspectoNombre = trim($eval['detalle']); // Ej: "Conceptual", "Procedimental"
                                $ordenCriterio = isset($criterio['orden']) ? $criterio['orden'] : ($iCrit + 1);

                                // 2. Buscar si ya existe un CMID para este Aspecto + Orden específico
                                $llaveDiccionario = $aspectoNombre . '_' . $ordenCriterio;
                                $cmidPrevio = $vinculosActivos[$llaveDiccionario] ?? '';

                                // --- NUEVA BANDERA BOOLEANA ---
                                $estaVinculado = !empty($cmidPrevio);
                                $modalId = "modalMoodle_{$iEval}_{$iCrit}";
                                $ids = isset($criterio_ids_columnas[$iEval][$iCrit]) && count($criterio_ids_columnas[$iEval][$iCrit])
                                    ? implode(',', $criterio_ids_columnas[$iEval][$iCrit])
                                    : $criterio['id'];
                                // 3. ID único para evitar cruce de modales
                                $modalId = "modalMoodle_{$iEval}_{$iCrit}";


                                // 1. Obtener el CMID si ya está vinculado (basado en el detalle del criterio)
                                $detalleLimpio = trim($criterio['detalle']);


                                // 2. Definir el orden dinámico. (Asumo que $iCrit es el índice del array, usualmente 0, 1, 2...)
                                // Si tu BD ya trae un campo 'orden', úsalo: $criterio['orden']. Si no, usa el índice + 1.
                                $ordenDinamico = isset($criterio['orden']) ? $criterio['orden'] : ($iCrit + 1);
                                ?>
                                <th class="text-center criterio-header p-0" style="width: 40px;">
                                    <?php if ($periodo_vigente['vigente']): ?>

                                        <?php if ($estaVinculado): ?>
                                            <!-- Habilitado: Color verde/primary, dispara el JS -->
                                            <button class="btn btn-success mb-1 btn-sm btn-sincronizar-criterio"
                                                data-criterio-key="<?= htmlspecialchars($iEval . '_' . $iCrit) ?>"
                                                data-aspecto="<?= htmlspecialchars($aspectoNombre) ?>"
                                                data-orden="<?= $ordenCriterio ?>"
                                                title="Descargar calificaciones desde Moodle">
                                                <i class="fas fa-cloud-download-alt"></i>
                                            </button><br>
                                        <?php else: ?>
                                            <!-- Deshabilitado: Color gris, no hace nada -->
                                            <button class="btn btn-secondary mb-1 btn-sm" disabled
                                                title="Primero debe vincular una actividad de Moodle usando el icono de enlace">
                                                <i class="fas fa-cloud-download-alt" style="opacity: 0.5;"></i>
                                            </button><br>
                                        <?php endif; ?>
                                        <!-- BOTÓN QUE ABRE EL MODAL (Hidratado con datos puros) -->
                                        <button class="btn btn-warning mb-1 btn-sm btn-vinculo-moodle-criterio"
                                            data-criterio-key="<?= htmlspecialchars($iEval . '_' . $iCrit) ?>"
                                            data-toggle="modal"
                                            data-target="#<?= $modalId ?>"
                                            data-aspecto="<?= htmlspecialchars($aspectoNombre) ?>"
                                            data-orden="<?= $ordenCriterio ?>"
                                            data-cmid-previo="<?= $cmidPrevio ?>">
                                            <i class="fas fa-link"></i>
                                        </button><br>

                                        <button class="btn btn-info mb-1 btn-sm btn-editar-criterio" data-ids-criterio="<?= $ids ?>">
                                            <i class="fa fa-pen"></i>
                                        </button>

                                        <!-- MODAL ÚNICO PARA ESTE CRITERIO -->
                                        <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <!-- Cabecera y cuerpo (Mismo HTML que tenías) -->
                                                    <div class="modal-header">
                                                        <h5 class="modal-title h4">Configuración Moodle: <?= $aspectoNombre ?> (Criterio <?= $ordenCriterio ?>)</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body text-left">
                                                        <div class="form-group row">
                                                            <label class="col-4 col-form-label">Vincular con:</label>
                                                            <div class="col-8">
                                                                <select name="criterio-moodle-<?= $iEval ?>-<?= $iCrit ?>" class="form-control select-moodle-dinamico">
                                                                    <option value="">Ninguna</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>

                                                        <!-- BOTÓN GUARDAR (Lleva los datos del Aspecto actual) -->
                                                        <button type="button" class="btn btn-success btn-guardar-vinculo"
                                                            data-aspecto="<?= htmlspecialchars($aspectoNombre) ?>"
                                                            data-orden="<?= $ordenCriterio ?>">
                                                            <i class="fas fa-link"></i> Vincular Actividad
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <br>
                                    <span class="criterio-detalle" style="writing-mode: vertical-lr; transform: rotate(180deg);">
                                        <?= htmlspecialchars($criterio['detalle']) ?>
                                    </span>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="width:40px; writing-mode: vertical-lr; transform: rotate(180deg);">Promedio <?= $eval['detalle']; ?>.</th>
                        <?php endforeach; ?>
                    </tr>

                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $idx => $est):
                        $id_detalle = $est['id_detalle_matricula'];
                        $evals = $evaluacionesEstudiante[$id_detalle] ?? [];
                        $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                        $motivo = ' (Inasistencia)';
                        if ($est['licencia'] != '') {
                            $motivo = ' (Licencia)';
                            $inhabilitado = $id_detalle;
                            $nota_mostrar = '';
                        }
                    ?>
                        <tr class="<?= $inhabilitado ? 'table-danger bg-danger' : '' ?>">
                            <td class="text-center"><?= ($idx + 1) ?></td>
                            <td class="text-center <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['dni']; ?></td>
                            <td class="sticky-nombre-eval <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['apellidos_nombres']; ?> <?= $inhabilitado ? $motivo : '' ?></td>
                            <?php foreach ($evals as $eval): ?>
                                <?php
                                $prom_eval = $promediosEvaluacion[$id_detalle][$eval['id']] ?? '';
                                $clase = '';
                                if ($prom_eval !== '' && is_numeric($prom_eval)) {
                                    $clase = ($prom_eval < 13) ? "text-danger font-weight-bold" : "text-primary font-weight-bold";
                                }
                                ?>
                                <?php foreach ($eval['criterios'] as $criterio): ?>
                                    <td>
                                        <?php if ($inhabilitado): ?>
                                            <span class="text-danger font-weight-bold">
                                                <?= htmlspecialchars($criterio['calificacion'] ?? '') ?>
                                            </span>
                                        <?php else: ?>
                                            <?php if ($periodo_vigente['vigente']): ?>
                                                <input type="number"
                                                    class="nota-criterio <?= ($criterio['calificacion'] < 13) ? 'text-danger' : 'text-primary' ?>"
                                                    data-id-criterio="<?= $criterio['id'] ?>"
                                                    value="<?= htmlspecialchars($criterio['calificacion'] ?? '') ?>"
                                                    min="0" max="20" style="width: 40px;">
                                            <?php else: ?>
                                                <span class="<?= ($criterio['calificacion'] < 13) ? 'text-danger' : 'text-primary' ?>">
                                                    <?= htmlspecialchars($criterio['calificacion'] ?? '') ?>
                                                </span>
                                            <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td class="<?= $clase ?> text-center"><?= $prom_eval !== '' ? $prom_eval : '' ?></td>
                        <?php endforeach; ?>
                        <td class="font-weight-bold text-center <?= ($promedioFinal[$id_detalle] < 13) ? 'text-danger' : 'text-primary'; ?>">
                            <?= $promedioFinal[$id_detalle]; ?>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($periodo_vigente['vigente']): ?>
            <div class="eval-submit-bar text-center my-3">
                <a class="btn btn-success col-12 col-sm-6 col-md-2 btn-guardar-eval" href="<?= BASE_URL; ?>/academico/calificaciones/evaluar/<?= $id_programacion_ud; ?>/<?= $nro_calificacion; ?>">
                    <i class="fas fa-check mr-1"></i> Guardar / Actualizar
                </a>
            </div>
        <?php endif; ?>
        <div class="modal fade" id="modalAgregarCriterioMoodle" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Nuevo Criterio de Evaluación</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Nombre del Criterio o Actividad:</label>
                            <input type="text" id="nuevo-criterio-nombre" class="form-control border-primary" placeholder="Ej: Tarea 1, Examen Parcial...">
                        </div>

                        <hr>

                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="switchMoodle">
                            <label class="custom-control-label font-weight-bold text-primary" for="switchMoodle">
                                <i class="fas fa-graduation-cap"></i> ¿Desea Registrar en el Aula Virtual (Moodle)?
                            </label>
                        </div>

                        <div id="moodle-options-container" style="display:none;" class="bg-light p-3 rounded border mb-3">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="vincular_sigi" name="vincular_sigi" value="0">
                                        <label class="custom-control-label font-weight-bold" for="vincular_sigi">Vincular con Registro SIGI</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <strong>Activado:</strong> Crea columna de notas en SIGI.<br>
                                        <strong>Desactivado:</strong> Solo existirá en Moodle (Material de apoyo).
                                    </small>
                                </div>
                                <div class="col-md-6" id="container-calificable">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="es_calificable" name="es_calificable" value="1">
                                        <label class="custom-control-label font-weight-bold text-danger" for="es_calificable">Actividad Calificable</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Permite importar las notas automáticas desde Moodle a SIGI.</small>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-uppercase">Tipo de Actividad en Moodle:</label>
                                <select id="select-modname-dinamico" class="form-control form-control-sm shadow-sm">
                                    <option value="">-- Seleccione tipo de actividad --</option>
                                    <?php foreach ($final_modules as $fm): ?>
                                        <?php if ($fm['supported']): ?>
                                            <option value="<?= $fm['modname'] ?>"><?= $fm['label'] ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Selecter de rúbrica de Moodle -->
                            <div class="form-group border-left border-info pl-3 mt-3" id="contenedor_rubrica_moodle" style="display: none;">
                                <label class="font-weight-bold text-info"><i class="fa fa-table"></i> Evaluación Avanzada (Rúbrica)</label>
                                <select name="moodle_field[rubric_json]" id="moodle_rubric_json" class="form-control">
                                    <option value="">-- Calificación Simple (Sin Rúbrica) --</option>
                                    <?php if (!empty($rubricas_docente)): foreach ($rubricas_docente as $rub): ?>
                                            <!-- Guardamos el JSON directamente en el value para enviarlo en el payload -->
                                            <option value='<?= htmlspecialchars($rub['contenido_json'], ENT_QUOTES, 'UTF-8') ?>'>
                                                <?= htmlspecialchars($rub['nombre']) ?>
                                            </option>
                                        <?php endforeach;
                                    else: ?>
                                        <option value="" disabled>No tiene rúbricas disponibles para esta Unidad Didáctica.</option>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Si selecciona una rúbrica, la actividad de Moodle se configurará automáticamente para evaluación avanzada.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-primary">Ubicar en:</label>
                                <select id="select-seccion-moodle" class="form-control form-control-sm m-input" name="section_moodle_id">
                                    <option value="<?= $datos_seccion_moodle['id_principal'] ?? 0 ?>" data-type="main" selected>
                                        Raíz de la Sección (Indicador <?= $nro_calificacion ?>)
                                    </option>

                                    <?php foreach ($datos_seccion_moodle['message'] as $modulo): ?>
                                        <?php if ($modulo['modname'] === 'subsection'): ?>
                                            <?php
                                            $customData = json_decode($modulo['customdata'], true);
                                            $subSectionId = $customData['sectionid'] ?? 0;
                                            ?>
                                            <option value="<?= $subSectionId ?>" data-type="subsection">
                                                Subsección: <?= htmlspecialchars($modulo['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Seleccione si desea crearlo dentro de una subsección específica de Moodle.</small>
                            </div>
                        </div>

                        <div id="seccion-moodle-dinamica" style="display:none;" class="border-left border-primary pl-3">

                            <div id="render-campos-moodle" class="row"></div>
                        </div>

                        <input type="hidden" id="hidden-eval-ids">
                        <input type="hidden" id="hidden-detalle-criterio">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btn-confirmar-todo">Guardar Criterio</button>
                    </div>
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

        @media (max-width: 767.98px) {
            .mobile-eval-sticky {
                position: sticky;
                top: 4px;
                z-index: 15;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            .eval-student-card {
                border: 1px solid #e3e6f0;
                border-radius: 10px;
                padding: 12px;
                margin-bottom: 10px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }
            .eval-criterio-row:last-child {
                border-bottom: 0 !important;
            }
            .eval-submit-bar {
                position: sticky;
                bottom: 8px;
                z-index: 20;
            }
            .eval-submit-bar .btn-guardar-eval {
                padding-top: 10px;
                padding-bottom: 10px;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
            }
            #tabla-evaluar .sticky-nombre-eval {
                position: sticky;
                left: 0;
                z-index: 2;
                min-width: 145px;
                max-width: 165px;
                font-size: 12px;
                box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mover los modales de Moodle fuera del contenedor ocultable/overflow de la tabla
            document.querySelectorAll('#tabla-evaluar .modal').forEach(function(modalEl) {
                document.body.appendChild(modalEl);
            });

            var btnCards = document.getElementById('btn-eval-modo-cards');
            var btnTabla = document.getElementById('btn-eval-modo-tabla');
            var panelMovil = document.getElementById('mobile-evaluar-panel');
            var wrapperTabla = document.getElementById('wrapper-tabla-evaluar');
            var selAspecto = document.getElementById('mobile-eval-aspecto');
            var selCriterio = document.getElementById('mobile-eval-criterio');
            var inpBuscar = document.getElementById('mobile-eval-buscar');
            var btnAddCrit = document.getElementById('mob-btn-agregar-crit');
            var btnVincMoodle = document.getElementById('mob-btn-vincular-moodle');
            var btnSyncMoodle = document.getElementById('mob-btn-sync-moodle');

            function setModoEval(modo) {
                if (!panelMovil || !wrapperTabla) return;
                if (modo === 'tabla') {
                    panelMovil.classList.add('d-none');
                    wrapperTabla.classList.remove('d-none');
                    btnTabla.classList.add('active', 'btn-primary');
                    btnTabla.classList.remove('btn-outline-primary');
                    btnCards.classList.remove('active', 'btn-primary');
                    btnCards.classList.add('btn-outline-primary');
                } else {
                    panelMovil.classList.remove('d-none');
                    wrapperTabla.classList.add('d-none');
                    btnCards.classList.add('active', 'btn-primary');
                    btnCards.classList.remove('btn-outline-primary');
                    btnTabla.classList.remove('active', 'btn-primary');
                    btnTabla.classList.add('btn-outline-primary');
                }
            }

            if (btnCards && btnTabla) {
                btnCards.addEventListener('click', function() { setModoEval('cards'); });
                btnTabla.addEventListener('click', function() { setModoEval('tabla'); });
            }

            function actualizarFiltroEval() {
                var asp = selAspecto ? selAspecto.value : 'all';
                var crit = selCriterio ? selCriterio.value : 'all';
                var q = (inpBuscar ? inpBuscar.value : '').trim().toLowerCase();

                // Filtrar opciones del selector de criterio según el aspecto elegido
                if (selCriterio) {
                    Array.from(selCriterio.options).forEach(function(opt) {
                        if (opt.value === 'all') {
                            opt.hidden = false;
                            return;
                        }
                        var showOpt = (asp === 'all' || opt.dataset.aspectoIdx === asp);
                        opt.hidden = !showOpt;
                    });
                    var selectedOpt = selCriterio.options[selCriterio.selectedIndex];
                    if (selectedOpt && selectedOpt.hidden) {
                        selCriterio.value = 'all';
                        crit = 'all';
                    }
                }

                // Mostrar/ocultar grupos y criterios en las tarjetas
                document.querySelectorAll('.eval-student-card').forEach(function(card) {
                    var matchEst = !q || card.dataset.nombre.indexOf(q) !== -1 || card.dataset.dni.indexOf(q) !== -1;
                    card.style.display = matchEst ? '' : 'none';
                    if (!matchEst) return;

                    card.querySelectorAll('.eval-aspecto-group').forEach(function(grp) {
                        var showAsp = (asp === 'all' || grp.dataset.aspectoIdx === asp);
                        if (!showAsp) {
                            grp.style.display = 'none';
                            return;
                        }
                        var visibleRows = 0;
                        grp.querySelectorAll('.eval-criterio-row').forEach(function(row) {
                            var showRow = (crit === 'all' || row.dataset.criterioKey === crit);
                            row.style.display = showRow ? '' : 'none';
                            if (showRow) visibleRows++;
                        });
                        grp.style.display = visibleRows > 0 ? '' : 'none';
                    });
                });

                // Botones de acción rápida en móvil
                if (btnAddCrit) {
                    var realAdd = asp !== 'all' ? document.querySelector('.btn-agregar-criterio[data-aspecto-idx="' + asp + '"]') : null;
                    btnAddCrit.classList.toggle('d-none', !realAdd);
                }
                if (btnVincMoodle) {
                    var realVinc = crit !== 'all' ? document.querySelector('.btn-vinculo-moodle-criterio[data-criterio-key="' + crit + '"]') : null;
                    btnVincMoodle.classList.toggle('d-none', !realVinc);
                }
                if (btnSyncMoodle) {
                    var realSync = crit !== 'all' ? document.querySelector('.btn-sincronizar-criterio[data-criterio-key="' + crit + '"]') : null;
                    btnSyncMoodle.classList.toggle('d-none', !realSync);
                }
            }

            if (selAspecto) selAspecto.addEventListener('change', actualizarFiltroEval);
            if (selCriterio) selCriterio.addEventListener('change', actualizarFiltroEval);
            if (inpBuscar) inpBuscar.addEventListener('input', actualizarFiltroEval);

            if (btnAddCrit) {
                btnAddCrit.addEventListener('click', function() {
                    var realBtn = document.querySelector('.btn-agregar-criterio[data-aspecto-idx="' + selAspecto.value + '"]');
                    if (realBtn) realBtn.click();
                });
            }
            if (btnVincMoodle) {
                btnVincMoodle.addEventListener('click', function() {
                    var realBtn = document.querySelector('.btn-vinculo-moodle-criterio[data-criterio-key="' + selCriterio.value + '"]');
                    if (realBtn) realBtn.click();
                });
            }
            if (btnSyncMoodle) {
                btnSyncMoodle.addEventListener('click', function() {
                    var realBtn = document.querySelector('.btn-sincronizar-criterio[data-criterio-key="' + selCriterio.value + '"]');
                    if (realBtn) realBtn.click();
                });
            }

            // Sincronización 2 vías entre notas móviles y notas reales de la tabla
            document.querySelectorAll('.js-mobile-nota').forEach(function(mInput) {
                var idCrit = mInput.dataset.syncCriterio;
                var realInput = document.querySelector('.nota-criterio[data-id-criterio="' + idCrit + '"]');
                if (!realInput) return;

                var row = mInput.closest('.eval-criterio-row');
                var statusEl = row ? row.querySelector('.js-mobile-nota-status') : null;
                mInput.dataset.syncedVal = mInput.value;

                function syncToReal() {
                    if (mInput.value === mInput.dataset.syncedVal && realInput.value === mInput.value) return;
                    mInput.dataset.syncedVal = mInput.value;
                    realInput.value = mInput.value;
                    if (statusEl) {
                        statusEl.textContent = 'Guardando...';
                        statusEl.className = 'js-mobile-nota-status text-muted';
                    }
                    realInput.dispatchEvent(new Event('change'));
                }

                mInput.addEventListener('change', syncToReal);

                mInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        syncToReal();
                        var visibles = Array.from(document.querySelectorAll('.js-mobile-nota')).filter(function(el) {
                            return el.offsetParent !== null;
                        });
                        var idx = visibles.indexOf(this);
                        if (idx >= 0 && idx + 1 < visibles.length) {
                            visibles[idx + 1].focus();
                            visibles[idx + 1].select();
                        } else {
                            this.blur();
                        }
                    }
                });

                // Si editan en modo tabla, reflejarlo en tarjeta
                realInput.addEventListener('change', function() {
                    if (mInput.value !== realInput.value) {
                        mInput.value = realInput.value;
                    }
                    mInput.dataset.syncedVal = realInput.value;
                });

                // Reflejar estado visual del fetch existente (border-success / border-danger / color)
                var observer = new MutationObserver(function() {
                    mInput.classList.toggle('border-success', realInput.classList.contains('border-success'));
                    mInput.classList.toggle('border-danger', realInput.classList.contains('border-danger'));
                    mInput.classList.toggle('text-danger', realInput.classList.contains('text-danger'));
                    mInput.classList.toggle('text-primary', realInput.classList.contains('text-primary'));

                    if (statusEl) {
                        if (realInput.classList.contains('border-success')) {
                            statusEl.textContent = 'Guardado ✓';
                            statusEl.className = 'js-mobile-nota-status text-success font-weight-bold';
                        } else if (realInput.classList.contains('border-danger')) {
                            statusEl.textContent = 'Error / Valor inválido';
                            statusEl.className = 'js-mobile-nota-status text-danger font-weight-bold';
                        }
                    }
                });
                observer.observe(realInput, { attributes: true, attributeFilter: ['class'] });
            });

            // Evitar que "Guardar / Actualizar" recargue antes de enviar la última nota tipeada
            var btnGuardarEval = document.querySelector('.btn-guardar-eval');
            if (btnGuardarEval) {
                btnGuardarEval.addEventListener('click', function(e) {
                    var active = document.activeElement;
                    if (active && (active.classList.contains('js-mobile-nota') || active.classList.contains('nota-criterio'))) {
                        e.preventDefault();
                        var dest = this.getAttribute('href');
                        active.dispatchEvent(new Event('change'));
                        active.blur();
                        setTimeout(function() {
                            window.location.href = dest;
                        }, 350);
                    }
                });
            }

            actualizarFiltroEval();
        });
    </script>
    <script>
        document.querySelectorAll('.nota-criterio').forEach(function(input) {
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
                    var id_criterio = this.dataset.idCriterio;
                    var valor = this.value;

                    fetch('<?= BASE_URL ?>/academico/calificaciones/guardarCriterio', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_criterio=' + encodeURIComponent(id_criterio) +
                                '&valor=' + encodeURIComponent(valor)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                this.classList.add('border-success');
                                setTimeout(() => this.classList.remove('border-success'), 1500);
                                if (valor < 13) {
                                    this.classList.remove('text-primary');
                                    this.classList.add('text-danger');
                                } else {
                                    this.classList.remove('text-danger');
                                    this.classList.add('text-primary');
                                }
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
        document.querySelectorAll('.btn-editar-criterio').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const th = this.closest('.criterio-header');
                if (!th) return;
                const span = th.querySelector('.criterio-detalle');
                if (!span) return;

                // Validación robusta del atributo
                if (!this.dataset.idsCriterio) {
                    alert('No se encontraron IDs de criterios para editar.');
                    return;
                }
                const idsCriterio = this.dataset.idsCriterio.split(',').map(id => id.trim()).filter(id => id.length > 0);
                const textoActual = span.textContent.trim();

                // Evita duplicar inputs
                if (th.querySelector('input')) return;

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm';
                input.value = textoActual;
                input.style.maxWidth = '180px';
                span.style.display = 'none';
                btn.style = 'style="min-width:60px; writing-mode: vertical-lr; transform: rotate(90deg);"';
                th.style = 'style="min-width:60px; writing-mode: vertical-lr; transform: rotate(180deg);"';
                th.appendChild(input);
                input.focus();

                input.addEventListener('blur', guardar);
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') guardar();
                    if (e.key === 'Escape') cancelar();
                });

                function guardar() {
                    const nuevoDetalle = input.value.trim();

                    fetch('<?= BASE_URL ?>/academico/calificaciones/guardarDetalleCriterioMasivo', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'ids_criterio=' + encodeURIComponent(idsCriterio.join(',')) +
                                '&detalle=' + encodeURIComponent(nuevoDetalle)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                // Cambia en todos los headers con los mismos IDs
                                document.querySelectorAll('.btn-editar-criterio').forEach(function(btn2) {
                                    if (btn2.dataset.idsCriterio === idsCriterio.join(',')) {
                                        const thx = btn2.closest('.criterio-header');
                                        if (!thx) return;
                                        const spanx = thx.querySelector('.criterio-detalle');
                                        if (spanx) spanx.textContent = nuevoDetalle;
                                    }
                                });
                                span.style.display = '';
                                input.remove();
                                // Mostrar loader antes de recargar
                                if (window.SIGI_LOADER) window.SIGI_LOADER.show('Actualizando vista...');
                                setTimeout(() => location.reload(), 50);
                            } else {
                                alert('Error al guardar');
                                span.style.display = '';
                                input.remove();
                            }
                        });
                }

                function cancelar() {
                    span.style.display = '';
                    input.remove();
                }
            });
        });

        document.querySelectorAll('.btn-editar-ponderado').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const idsEval = this.dataset.idsEval ? this.dataset.idsEval.split(',').map(x => x.trim()).filter(x => x) : [];
                if (idsEval.length === 0) {
                    alert('No se encontraron evaluaciones.');
                    return;
                }
                // Busca el span del ponderado
                const th = this.closest('th');
                const spanPonderado = th.querySelector('.ponderado-eval');
                if (!spanPonderado) return;
                const valorActual = spanPonderado.textContent.trim();

                // Evitar doble input
                if (th.querySelector('input.ponderado-input')) return;

                // Input para editar
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control form-control-sm ponderado-input';
                input.style.display = 'inline-block';
                input.style.width = '80px';
                input.value = valorActual;
                spanPonderado.style.display = 'none';
                this.style.display = 'none';
                th.appendChild(input);
                input.focus();

                input.addEventListener('blur', guardar);
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') guardar();
                    if (e.key === 'Escape') cancelar();
                });

                function guardar() {
                    let nuevoValor = parseInt(input.value.trim());
                    if (isNaN(nuevoValor) || nuevoValor < 1 || nuevoValor > 100) {
                        nuevoValor = 1;
                        input.remove();
                    }
                    fetch('<?= BASE_URL ?>/academico/calificaciones/guardarPonderadoEvaluacionMasivo', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'ids_eval=' + encodeURIComponent(idsEval.join(',')) +
                                '&ponderado=' + encodeURIComponent(nuevoValor)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                // Cambia en todos los ponderados iguales en la tabla
                                document.querySelectorAll('.btn-editar-ponderado').forEach(function(btn2) {
                                    if (btn2.dataset.idsEval === idsEval.join(',')) {
                                        const thx = btn2.closest('th');
                                        const spx = thx.querySelector('.ponderado-eval');
                                        if (spx) spx.textContent = nuevoValor;
                                        btn2.style.display = '';
                                    }
                                });
                                spanPonderado.style.display = '';
                                input.remove();
                                // Mostrar loader antes de recargar
                                if (window.SIGI_LOADER) window.SIGI_LOADER.show('Actualizando vista...');
                                setTimeout(() => location.reload(), 50);
                            } else {
                                alert('Error al guardar');
                                spanPonderado.style.display = '';
                                input.remove();
                                btn.style.display = '';
                            }
                        });
                }

                function cancelar() {
                    spanPonderado.style.display = '';
                    input.remove();
                    btn.style.display = '';
                }
            });
        });

        /*document.querySelectorAll('.btn-agregar-criterio').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('¿Desea agregar un nuevo criterio de evaluación para todos los estudiantes en esta evaluación?')) return;

                const ids_eval = this.dataset.evalIds; // IDs separados por coma

                fetch('<?= BASE_URL ?>/academico/calificaciones/agregarCriterioMasivo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'ids_eval=' + encodeURIComponent(ids_eval)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            alert('Criterio agregado');
                            location.reload();
                        } else {
                            alert('Error al agregar');
                        }
                    });
            });
        });*/
    </script>
    <script>
        // Inyección de configuración desde PHP
        const moodleConfig = <?= json_encode(array_values($final_modules)) ?>;

        // Inicializar botones de agregar criterio para abrir el modal
        document.querySelectorAll('.btn-agregar-criterio').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                // Limpiar y preparar el modal para un nuevo registro
                document.getElementById('hidden-eval-ids').value = this.dataset.evalIds;
                document.getElementById('hidden-detalle-criterio').value = this.dataset.detalle;
                document.getElementById('nuevo-criterio-nombre').value = '';
                document.getElementById('switchMoodle').checked = false;

                // AJUSTE PARA ESCENARIOS: Reset de estados y contenedores nuevos
                document.getElementById('vincular_sigi').checked = false;
                document.getElementById('es_calificable').checked = false;
                document.getElementById('moodle-options-container').style.display = 'none';

                document.getElementById('seccion-moodle-dinamica').style.display = 'none';
                document.getElementById('select-modname-dinamico').value = '';
                document.getElementById('render-campos-moodle').innerHTML = '';

                $('#modalAgregarCriterioMoodle').modal('show');
            });
        });

        // Control del Switch Moodle para mostrar/ocultar configuración
        document.getElementById('switchMoodle').addEventListener('change', function() {
            // AJUSTE PARA ESCENARIOS: Controlar el contenedor de opciones (gris) y la sección dinámica
            const optionsContainer = document.getElementById('moodle-options-container');
            const seccionMoodle = document.getElementById('seccion-moodle-dinamica');

            const displayValue = this.checked ? 'block' : 'none';
            optionsContainer.style.display = displayValue;
            seccionMoodle.style.display = displayValue;

            if (!this.checked) {
                document.getElementById('render-campos-moodle').innerHTML = '';
                document.getElementById('select-modname-dinamico').value = '';
            }
        });

        // Renderizado dinámico de campos según el tipo de módulo seleccionado
        document.getElementById('select-modname-dinamico').addEventListener('change', function() {
            const modname = this.value;
            const renderContainer = document.getElementById('render-campos-moodle');
            renderContainer.innerHTML = '';

            if (!modname) return;

            const modInfo = moodleConfig.find(m => m.modname === modname);
            if (!modInfo || !modInfo.fields) return;

            modInfo.fields.forEach(field => {
                // --- 1. FILTRO DE CAMPOS OCULTOS ---
                // Mantenemos estos campos como hidden para que viajen con sus valores por defecto
                const camposOcultos = ['introformat', 'contentformat', 'showdescription', 'grade', 'maxgrade', 'groupmode', 'anonymous', 'numbering', 'forcesubscribe'];

                if (camposOcultos.includes(field.name)) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = field.name;
                    hidden.className = 'm-input'; // Importante para la recolección
                    hidden.value = field.default !== undefined ? (typeof field.default === 'boolean' ? (field.default ? 1 : 0) : field.default) : 1;
                    renderContainer.appendChild(hidden);
                    return;
                }

                const col = document.createElement('div');
                col.className = (field.type === 'editor' || field.name === 'intro') ? 'col-12 mb-3' : 'col-md-6 mb-3';

                const defaultValue = field.default !== undefined ? field.default : '';
                const requiredAttr = field.required ? 'required' : ''; // Atributo HTML5
                const labelText = `<label class="small font-weight-bold mb-1">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>`;

                let inputElement = '';

                // --- 2. MANEJO DE INPUTS (Mejorado para priorizar configuración) ---

                // NUEVO: Si el campo tiene opciones definidas en el PHP, las usamos primero.
                // Esto evita el cruce de 'display' en URL u otros módulos.
                if (field.options) {
                    inputElement = `<select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>`;
                    for (const [val, text] of Object.entries(field.options)) {
                        inputElement += `<option value="${val}" ${val == defaultValue ? 'selected' : ''}>${text}</option>`;
                    }
                    inputElement += `</select>`;
                }
                // Lógica original de Moodle (para campos sin opciones explícitas en config)
                else if (field.name === 'groupmode') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="0" ${defaultValue == 0 ? 'selected' : ''}>No hay grupos</option>
                <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Grupos separados</option>
                <option value="2" ${defaultValue == 2 ? 'selected' : ''}>Grupos visibles</option>
            </select>`;
                } else if (field.name === 'anonymous') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Anónimo</option>
                <option value="2" ${defaultValue == 2 ? 'selected' : ''}>Nombres de usuarios</option>
            </select>`;
                } else if (field.name === 'display' || field.name === 'numbering') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="0" ${defaultValue == 0 ? 'selected' : ''}>Automático / Ninguno</option>
                <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Incrustar / Numérico</option>
                <option value="2" ${defaultValue == 2 ? 'selected' : ''}>Forzar descarga / Viñetas</option>
            </select>`;
                } else if (field.name === 'forcesubscribe') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="0" ${defaultValue == 0 ? 'selected' : ''}>Opcional</option>
                <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Forzada (Siempre)</option>
                <option value="2" ${defaultValue == 2 ? 'selected' : ''}>Automática (Inicial)</option>
                <option value="3" ${defaultValue == 3 ? 'selected' : ''}>Desactivada</option>
            </select>`;
                } else if (field.name === 'mainglossary') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="0" ${defaultValue == 0 ? 'selected' : ''}>Glosario Secundario</option>
                <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Glosario Principal</option>
            </select>`;
                } else if (field.name === 'wikimode') {
                    inputElement = `
            <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                <option value="collaborative" ${defaultValue == 'collaborative' ? 'selected' : ''}>Wiki colaborativa</option>
                <option value="individual" ${defaultValue == 'individual' ? 'selected' : ''}>Wiki individual</option>
            </select>`;
                } else {
                    // --- 3. RENDERIZADO POR TIPO ---
                    switch (field.type) {
                        case 'url':
                            inputElement = `<input type="url" name="${field.name}" class="form-control form-control-sm m-input" value="${defaultValue}" placeholder="https://" ${requiredAttr}>`;
                            break;
                        case 'select':
                            inputElement = `<select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>`;
                            if (field.options) {
                                for (const [val, text] of Object.entries(field.options)) {
                                    inputElement += `<option value="${val}" ${val == defaultValue ? 'selected' : ''}>${text}</option>`;
                                }
                            }
                            inputElement += `</select>`;
                            break;

                        case 'datetime':
                            inputElement = `<input type="datetime-local" name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>`;
                            break;

                        case 'bool':
                            inputElement = `
                    <select name="${field.name}" class="form-control form-control-sm m-input" ${requiredAttr}>
                        <option value="1" ${defaultValue == 1 ? 'selected' : ''}>Sí</option>
                        <option value="0" ${defaultValue == 0 ? 'selected' : ''}>No</option>
                    </select>`;
                            break;

                        case 'int':
                            const isGrade = field.name === 'grade' || field.name === 'maxgrade';
                            inputElement = `<input type="number" name="${field.name}" class="form-control form-control-sm m-input" 
                        value="${defaultValue}" ${isGrade ? 'max="20" min="0"' : ''} ${requiredAttr}>`;
                            break;

                        case 'editor':
                            inputElement = `<textarea name="${field.name}" class="form-control form-control-sm m-input" rows="3" ${requiredAttr} placeholder="Escriba la descripción aquí...">${defaultValue}</textarea>`;
                            break;

                        case 'file':
                            // Definimos las extensiones según el tipo de módulo
                            let acceptAttr = '';
                            if (modname === 'h5pactivity') {
                                acceptAttr = 'accept=".h5p"';
                            } else if (modname === 'scorm' || modname === 'imscp') {
                                acceptAttr = 'accept=".zip"';
                            }
                            inputElement = `
                                <div class="custom-file">
                                    <input type="file" name="${field.name}" class="custom-file-input m-input" id="file_${field.name}" ${requiredAttr} ${acceptAttr}>
                                    <label class="custom-file-label col-form-label-sm" for="file_${field.name}">Elegir archivo...</label>
                                </div>`;
                            break;

                        default:
                            inputElement = `<input type="text" name="${field.name}" class="form-control form-control-sm m-input" 
                        value="${defaultValue}" ${requiredAttr}>`;
                    }
                }

                col.innerHTML = labelText + inputElement;
                renderContainer.appendChild(col);

                // --- SOLUCIÓN VISUAL: Actualizar nombre del archivo en el label ---
                if (field.type === 'file') {
                    const fileInput = col.querySelector('input[type="file"]');
                    fileInput.addEventListener('change', function(e) {
                        const fileName = e.target.files[0] ? e.target.files[0].name : "Elegir archivo...";
                        const label = col.querySelector('.custom-file-label');
                        if (label) label.textContent = fileName;
                    });
                }

                if (field.name === 'name') {
                    const nameInput = col.querySelector('input');
                    const sigiNameInput = document.getElementById('nuevo-criterio-nombre');

                    // Sincronizar valor inicial
                    nameInput.value = sigiNameInput.value;

                    // Sincronizar en cada cambio
                    // Opcional: Ocultar el contenedor del input de Moodle para que no se vea doble
                    col.style.display = 'none';
                }
            });
            document.getElementById('nuevo-criterio-nombre').addEventListener('input', (e) => {
                const moodleNameInput = document.querySelector('#render-campos-moodle input[name="name"]');
                if (moodleNameInput) moodleNameInput.value = e.target.value;
            });


            // --- 4. LÓGICA ESPECIAL PARA CHOICE (CONSULTA) ---
            if (modname === 'choice') {
                const choiceDiv = document.createElement('div');
                choiceDiv.className = 'col-12 border-top pt-3 mt-2';
                choiceDiv.innerHTML = `
            <label class="small font-weight-bold text-primary">Opciones de consulta (Mínimo 2):</label>
            <div id="choice-options-wrapper">
                <input type="text" name="optiontext[]" class="form-control form-control-sm m-input mb-1" placeholder="Opción 1" required>
                <input type="text" name="optiontext[]" class="form-control form-control-sm m-input mb-1" placeholder="Opción 2" required>
            </div>
            <button type="button" class="btn btn-link btn-sm p-0" id="btn-add-option">+ Añadir opción</button>
        `;
                renderContainer.appendChild(choiceDiv);

                document.getElementById('btn-add-option').addEventListener('click', function() {
                    const wrapper = document.getElementById('choice-options-wrapper');
                    const newInput = document.createElement('input');
                    newInput.type = 'text';
                    newInput.name = 'optiontext[]';
                    newInput.className = 'form-control form-control-sm m-input mb-1';
                    newInput.placeholder = 'Nueva opción';
                    wrapper.appendChild(newInput);
                });
            }
        });

        // Acción de Guardar (SIGI + opcionalmente Moodle)
        // Acción de Guardar (Maneja: Solo SIGI, Solo Moodle, o Ambos vinculados)
        document.getElementById('btn-confirmar-todo').onclick = function() {
            const courseIdMoodle = '<?= $programacion['id_moodle'] ?>';
            const crearEnMoodle = document.getElementById('switchMoodle').checked;
            const vincularSigi = document.getElementById('vincular_sigi').checked;
            const nombre = document.getElementById('nuevo-criterio-nombre').value.trim();

            // 1. VALIDACIÓN UNIVERSAL: El nombre siempre es obligatorio (sea para SIGI o Moodle)
            if (!nombre) {
                alert('Por favor, ingrese el nombre.');
                return;
            }

            // 2. VALIDACIONES ESPECÍFICAS DE MOODLE
            if (crearEnMoodle) {
                // Validar que la UD tenga vínculo con Moodle
                if (!courseIdMoodle || courseIdMoodle === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Esta Unidad Didáctica no tiene un ID de Moodle vinculado. Sincroniza la programación primero.'
                    });
                    return;
                }
                // Validar que se haya seleccionado un tipo de actividad
                if (!document.getElementById('select-modname-dinamico').value) {
                    alert('Debe seleccionar un tipo de módulo para Moodle o desactivar la opción.');
                    return;
                }
            }

            const ids_eval = document.getElementById('hidden-eval-ids').value;
            const moodleType = document.getElementById('select-modname-dinamico').value;

            let moodleData = {};
            let physicalFile = null; // Variable para capturar el archivo físico

            if (crearEnMoodle) {
                const inputs = document.querySelectorAll('.m-input');
                let faltanObligatorios = false;

                inputs.forEach(input => {
                    let value = input.value;
                    const name = input.name;

                    // --- PROCESAMIENTO DE ARCHIVOS ---
                    if (input.type === 'file') {
                        if (input.files && input.files[0]) {
                            const file = input.files[0];
                            const fileName = file.name.toLowerCase();

                            // VALIDACIÓN DE EXTENSIONES (H5P y SCORM/IMS)
                            let errorExtension = false;
                            if (moodleType === 'h5pactivity' && !fileName.endsWith('.h5p')) {
                                errorExtension = "El módulo H5P solo acepta archivos con extensión .h5p";
                            } else if ((moodleType === 'scorm' || moodleType === 'imscp') && !fileName.endsWith('.zip')) {
                                errorExtension = "Este módulo solo acepta paquetes comprimidos en formato .zip";
                            }

                            if (errorExtension) {
                                Swal.fire('Archivo no permitido', errorExtension, 'error');
                                faltanObligatorios = true;
                                input.classList.add('is-invalid');
                                return;
                            }

                            physicalFile = file;
                            input.classList.remove('is-invalid');
                        }

                        // Validar si el archivo es obligatorio según configuración
                        if (input.hasAttribute('required') && (!input.files || input.files.length === 0)) {
                            faltanObligatorios = true;
                            input.classList.add('is-invalid');
                        }
                        return;
                    }

                    // --- VALIDACIÓN DE CAMPOS DE TEXTO/SELECT ---
                    if (input.hasAttribute('required') && (!value || value.trim() === "")) {
                        faltanObligatorios = true;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');

                        if (name.includes('[]')) {
                            const cleanName = name.replace('[]', '');
                            if (!moodleData[cleanName]) moodleData[cleanName] = [];
                            moodleData[cleanName].push(value);
                        } else {
                            // Conversión de Fechas
                            if (input.type === 'datetime-local') {
                                value = value ? Math.floor(new Date(value).getTime() / 1000) : 0;
                            }
                            // Conversión de Checkboxes
                            if (input.type === 'checkbox') {
                                value = input.checked ? 1 : 0;
                            }

                            if (name) {
                                // CONVERSIÓN MINUTOS A SEGUNDOS (timelimit para Quiz/Lesson)
                                if (name === 'timelimit') {
                                    const minutos = parseInt(value) || 0;
                                    moodleData[name] = minutos * 60;
                                } else {
                                    moodleData[name] = value;
                                }
                            }
                        }
                    }
                });

                if (faltanObligatorios) {
                    Swal.fire('Atención', 'Por favor complete los campos marcados con (*).', 'warning');
                    return;
                }
            }






            // Bloquear botón para evitar doble envío
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            // --- CONSTRUCCIÓN DE FORMDATA PARA LOS 3 ESCENARIOS ---
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('ids_eval', ids_eval);
            formData.append('detalle', document.getElementById('hidden-detalle-criterio').value);
            formData.append('crear_moodle', crearEnMoodle);

            /**
             * Lógica de escenarios:
             * 1. Solo SIGI: crearEnMoodle es false. El backend creará el criterio.
             * 2. Solo Moodle: crearEnMoodle es true, vincularSigi es false.
             * 3. Ambos: crearEnMoodle es true, vincularSigi es true.
             */
            formData.append('vincular_sigi', crearEnMoodle ? vincularSigi : true);

            formData.append('es_calificable', document.getElementById('es_calificable').checked);
            formData.append('moodle_type', moodleType);
            formData.append('moodle_data', JSON.stringify(moodleData));
            formData.append('section', '<?= $nro_calificacion ?>');
            formData.append('courseid', courseIdMoodle);
            formData.append('section_moodle_id', document.getElementById('select-seccion-moodle').value);

            let selectRubrica = document.getElementById('moodle_rubric_json');
            if (selectRubrica && selectRubrica.value !== '') {
                formData.append('rubric_json', selectRubrica.value);
            }

            if (physicalFile) {
                formData.append('file', physicalFile);
            }

            fetch('<?= BASE_URL ?>/academico/calificaciones/agregarCriterioConMoodle', {
                    method: 'POST',
                    body: formData // Multipart automático
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        if (window.SIGI_LOADER) window.SIGI_LOADER.show('Actualizando calificaciones...');
                        setTimeout(() => {
                            location.reload();
                        }, 50);
                    } else {
                        alert(data.msg || 'Error al procesar la solicitud.');
                        this.disabled = false;
                        this.innerHTML = 'Guardar Criterio';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error crítico en la comunicación con el servidor.');
                    this.disabled = false;
                    this.innerHTML = 'Guardar Criterio';
                });
        };

        document.getElementById('vincular_sigi').addEventListener('change', function() {
            const containerCalificable = document.getElementById('container-calificable');
            const checkCalificable = document.getElementById('es_calificable');

            if (this.checked) {
                containerCalificable.style.opacity = "1";
                checkCalificable.disabled = false;
            } else {
                containerCalificable.style.opacity = "0.5";
                checkCalificable.checked = false;
                checkCalificable.disabled = true;
            }
        });


        // Listener cuando el docente cambia el tipo de actividad de Moodle
        document.getElementById('select-modname-dinamico').addEventListener('change', function() {
            const tipoSeleccionado = this.value;
            const contenedorRubrica = document.getElementById('contenedor_rubrica_moodle');
            const selectRubrica = document.getElementById('moodle_rubric_json');

            // Moodle generalmente soporta rúbricas en el módulo 'assign' (Tareas)
            if (tipoSeleccionado === 'assign') {
                contenedorRubrica.style.display = 'block';
            } else {
                contenedorRubrica.style.display = 'none';
                selectRubrica.value = ''; // Reseteamos si elige un Foro o Cuestionario
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. CARGAR DATOS DE MOODLE EN EL MODAL ---
            // Variables de caché para no consultar Moodle cada vez que abren el modal
            let actividadesMoodleCache = null;

            $('.btn-vinculo-moodle-criterio').on('click', function() {
                const modalId = $(this).data('target');
                const selectElement = $(modalId).find('select[name^="criterio-moodle"]');

                // Mostrar cargando en el select
                selectElement.html('<option value="">Cargando actividades de Moodle...</option>');

                if (actividadesMoodleCache !== null) {
                    poblarSelectMoodle(selectElement, actividadesMoodleCache);
                } else {
                    // Consultar al backend
                    const formData = new FormData();
                    formData.append('id_programacion_ud', '<?= $id_programacion_ud ?>');
                    formData.append('nro_calificacion', '<?= $nro_calificacion ?>');

                    fetch('<?= BASE_URL ?>/academico/calificaciones/listarActividadesMoodleCourse', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                actividadesMoodleCache = res.data;
                                poblarSelectMoodle(selectElement, actividadesMoodleCache);
                            } else {
                                selectElement.html(`<option value="">Error: ${res.message}</option>`);
                            }
                        }).catch(e => selectElement.html('<option value="">Error de conexión</option>'));
                }
            });

            // (Asumo que sigues inyectando $vinculosOcupados desde PHP)
            const vinculosOcupados = <?= json_encode($vinculosOcupados ?? []) ?>;

            function poblarSelectMoodle(selectJQ, datos, cmidPrevio) {
                if (datos.length === 0) {
                    selectJQ.html('<option value="">No hay actividades evaluables en Moodle</option>');
                    return;
                }

                let html = '<option value="">-- Seleccione una actividad de Moodle --</option>';
                datos.forEach(act => {
                    const isSelected = (act.cmid == cmidPrevio) ? 'selected' : '';

                    // UX: Mostrar si está ocupado, pero NO deshabilitarlo
                    let textoAgregado = '';
                    // Atributo data para que el botón de guardar sepa si está "robando" un vínculo
                    let dataOcupado = '';

                    if (vinculosOcupados[act.cmid] && act.cmid != cmidPrevio) {
                        const ocupadoPor = vinculosOcupados[act.cmid];
                        textoAgregado = ` [⚠️ Vinculado a: Ind.${ocupadoPor.indicador} - ${ocupadoPor.detalle}]`;
                        dataOcupado = `data-ocupado="true" data-ocupado-texto="${ocupadoPor.detalle}"`;
                    }

                    html += `<option value="${act.cmid}" data-cmid="${act.cmid}" ${dataOcupado} ${isSelected}>
                        ${act.nombre} (${act.modulo.toUpperCase()})${textoAgregado}
                     </option>`;
                });
                selectJQ.html(html);
            }

            // --- 2. SINCRONIZAR NOTAS DE UN CRITERIO (PULL) ---
            // 3. SINCRONIZAR NOTAS (PULL)
            document.querySelectorAll('.btn-sincronizar-criterio').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Leer las coordenadas exactas de la celda
                    const aspectoCriterio = this.dataset.aspecto;
                    const ordenCriterio = this.dataset.orden; // <-- EL DATO CLAVE

                    Swal.fire({
                        title: `¿Sincronizar calificaciones?`,
                        text: "Se descargarán las calificaciones de Moodle y se convertirán a escala vigesimal. Se sobrescribirán las notas previas.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-sync"></i> Sí, sincronizar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const btnOriginalHTML = this.innerHTML;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            this.disabled = true;

                            // Construcción del Payload
                            const fd = new FormData();
                            fd.append('id_programacion_ud', '<?= $id_programacion_ud ?>');
                            fd.append('nro_calificacion', '<?= $nro_calificacion ?>');
                            fd.append('detalle_criterio', aspectoCriterio);
                            fd.append('criterio_orden', ordenCriterio); // <-- ENVIAR AL BACKEND

                            fetch('<?= BASE_URL ?>/academico/calificaciones/sincronizarCriterioMoodle', {
                                    method: 'POST',
                                    body: fd
                                })
                                .then(r => r.json())
                                .then(res => {
                                    if (res.success) {
                                        Swal.fire('Completado', res.message, 'success').then(() => location.reload());
                                    } else {
                                        Swal.fire('Operación Fallida', res.message, 'error');
                                        this.innerHTML = btnOriginalHTML;
                                        this.disabled = false;
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Error de Red', 'No se pudo comunicar con el servidor SIGI.', 'error');
                                    this.innerHTML = btnOriginalHTML;
                                    this.disabled = false;
                                });
                        }
                    });
                });
            });

        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let actividadesMoodleCache = null;
            let modalActivoAspecto = '';
            let modalActivoOrden = 1;
            let modalActivoCmidPrevio = '';
            const vinculosOcupados = <?= json_encode($vinculosOcupados ?? []) ?>;

            // 1. AL ABRIR EL MODAL
            $('.btn-vinculo-moodle-criterio').on('click', function() {
                const modalId = $(this).data('target');
                const selectElement = $(modalId).find('.select-moodle-dinamico');

                // Captura precisa de coordenadas
                modalActivoAspecto = $(this).data('aspecto');
                modalActivoOrden = $(this).data('orden');
                modalActivoCmidPrevio = $(this).data('cmid-previo');

                selectElement.html('<option value="">Cargando actividades...</option>');

                if (actividadesMoodleCache !== null) {
                    poblarSelectMoodle(selectElement, actividadesMoodleCache, modalActivoCmidPrevio);
                } else {
                    const formData = new FormData();
                    formData.append('id_programacion_ud', '<?= $id_programacion_ud ?>');
                    formData.append('nro_calificacion', '<?= $nro_calificacion ?>');

                    fetch('<?= BASE_URL ?>/academico/calificaciones/listarActividadesMoodleCourse', {
                        method: 'POST',
                        body: formData
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            actividadesMoodleCache = res.data;
                            poblarSelectMoodle(selectElement, actividadesMoodleCache, modalActivoCmidPrevio);
                        } else {
                            selectElement.html(`<option value="">Error: ${res.message}</option>`);
                        }
                    }).catch(e => selectElement.html('<option value="">Error de conexión</option>'));
                }
            });

            function poblarSelectMoodle(selectJQ, datos, cmidPrevio) {
                if (datos.length === 0) {
                    selectJQ.html('<option value="">No hay actividades en Moodle</option>');
                    return;
                }

                let html = '<option value="">-- Seleccione una actividad --</option>';
                datos.forEach(act => {
                    const isSelected = (act.cmid == cmidPrevio) ? 'selected' : '';

                    let textoOcupado = '';
                    let dataOcupado = '';
                    if (vinculosOcupados[act.cmid] && act.cmid != cmidPrevio) {
                        const info = vinculosOcupados[act.cmid];
                        textoOcupado = ` [⚠️ Ocupado en Ind.${info.indicador} - ${info.detalle}]`;
                        dataOcupado = `data-ocupado="true" data-ocupado-texto="${info.detalle}"`;
                    }

                    html += `<option value="${act.cmid}" ${dataOcupado} ${isSelected}>
                        ${act.nombre} (${act.modulo}) ${textoOcupado}
                     </option>`;
                });
                selectJQ.html(html);
            }

            // 2. AL GUARDAR
            document.querySelectorAll('.btn-guardar-vinculo').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const modalContent = this.closest('.modal-content');
                    const selectMoodle = modalContent.querySelector('.select-moodle-dinamico');
                    const moodleCmid = selectMoodle ? selectMoodle.value : '';

                    if (!moodleCmid) {
                        Swal.fire('Atención', 'Seleccione una actividad de Moodle.', 'warning');
                        return;
                    }

                    const optElegida = selectMoodle.options[selectMoodle.selectedIndex];
                    if (optElegida.hasAttribute('data-ocupado')) {
                        const donde = optElegida.getAttribute('data-ocupado-texto');
                        Swal.fire({
                            title: 'Reasignar Actividad',
                            text: `Esta tarea ya califica al ${donde}. ¿Desea moverla aquí?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, reasignar'
                        }).then((r) => {
                            if (r.isConfirmed) ejecutarVinculacion(this, moodleCmid);
                        });
                    } else {
                        ejecutarVinculacion(this, moodleCmid);
                    }
                });
            });

            function ejecutarVinculacion(boton, moodleCmid) {
                const btnHTML = boton.innerHTML;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                boton.disabled = true;

                const formData = new FormData();
                formData.append('id_programacion_ud', '<?= $id_programacion_ud ?>');
                formData.append('nro_calificacion', '<?= $nro_calificacion ?>');

                // Usamos los datos inyectados por PHP en el botón Guardar
                formData.append('evaluacion_detalle', boton.dataset.aspecto);
                formData.append('criterio_orden', boton.dataset.orden);
                formData.append('moodle_cmid', moodleCmid);

                fetch('<?= BASE_URL ?>/academico/calificaciones/vincularCriterioMoodle', {
                    method: 'POST',
                    body: formData
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Vinculado',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        boton.disabled = false;
                        boton.innerHTML = btnHTML;
                    }
                }).catch(() => {
                    Swal.fire('Error', 'Fallo de red.', 'error');
                    boton.disabled = false;
                    boton.innerHTML = btnHTML;
                });
            }


        });
    </script>

<?php else: ?>
    <p>No tiene permisos para editar Calificaciones o el periodo ya culminó.</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>