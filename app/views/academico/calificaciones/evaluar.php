<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php';
?>

<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico()) && $permitido): ?>
    <div class="card p-2">
        <div class="col-4 col-md-2 mb-3">
            <a class="btn btn-danger mb-3 col-12" href="<?= BASE_URL; ?>/academico/calificaciones/ver/<?php echo $id_programacion_ud; ?>">Regresar</a>
            <a class="btn btn-info btn-sm btn-block mb-2 col-12" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/registroAuxiliar/<?= $id_programacion_ud ?>/<?= $nro_calificacion ?>">Imprimir</a>
        </div>
        <h5 class="text-center font-weight-bold mb-4" style="color:#607d8b;">
            Evaluación - Indicador <?= $nro_calificacion ?>

            - <span style="color:#4153a1;"><?php echo strtoupper($nombreUnidadDidactica ?? ''); ?></span>
        </h5>
        <h6 class="text-center mb-4"><b>Indicador de Logro: </b><?= $indicadores_capacidad['I' . $nro_calificacion] ?></h6>

        <div class="table-responsive">
            <table class="table table-bordered" style="font-size:14px;">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle text-center">ORDEN</th>
                        <th rowspan="2" class="align-middle text-center">DNI</th>
                        <th rowspan="2" class="align-middle text-center">APELLIDOS Y NOMBRES</th>
                        <?php
                        // Para la cabecera tomamos el primer estudiante que tenga evaluaciones:
                        $anyEv = [];
                        foreach ($estudiantes as $e) {
                            if (!empty($evaluacionesEstudiante[$e['id_detalle_matricula']])) {
                                $anyEv = $evaluacionesEstudiante[$e['id_detalle_matricula']];
                                break;
                            }
                        }
                        ?>
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
                                            data-eval-ids="<?= implode(',', $ids_eval_columna) ?>">
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
                                // Obtiene todos los IDs de la columna
                                $ids = isset($criterio_ids_columnas[$iEval][$iCrit]) && count($criterio_ids_columnas[$iEval][$iCrit])
                                    ? implode(',', $criterio_ids_columnas[$iEval][$iCrit])
                                    : $criterio['id'];
                                ?>
                                <th class="text-center criterio-header p-0" style="width: 40px;">
                                    <?php if ($periodo_vigente['vigente']): ?>
                                        <button class="btn btn-warning mb-1 btn-sm btn-vinculo-moodle-criterio" data-toggle="modal"
                                            data-target=".bd-example-modal-lg-<?= $cont  ?>" data-ids-criterio="<?= $ids ?>"><i class="fas fa-link"></i></button><br>
                                        <button class="btn btn-info mb-1 btn-sm btn-editar-criterio"
                                            data-ids-criterio="<?= $ids ?>"><i class="fa fa-pen"></i></button>
                                        <!-- Modal para moodle -->
                                        <div class="modal fade bd-example-modal-lg-<?= $cont ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title h4" id="myLargeModalLabel">Configuración con Moodle</h5>
                                                        <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row col-12">
                                                            <div class="form-group row col-md-12">
                                                                <label class="col-4 col-form-label">Vincular Criterio con :</label>
                                                                <div class="col-8">
                                                                    <select name="criterio-moodle-<?= $cont ?>" id="criterio-moodle-<?= $cont ?>" class="form-control" data-ids-criterio="<?= $ids ?>">
                                                                        <option value="">Ninguna</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                    <br>
                                    <span class="criterio-detalle" style="width:40px; writing-mode: vertical-lr; transform: rotate(180deg);"><?= htmlspecialchars($criterio['detalle']) ?></span>
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
                            <td class="<?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['apellidos_nombres']; ?> <?= $inhabilitado ? $motivo : '' ?></td>
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
            <br>
            <?php if ($periodo_vigente['vigente']): ?>
                <center><a class="btn btn-success mb-3 col-6 col-md-2" href="<?= BASE_URL; ?>/academico/calificaciones/evaluar/<?= $id_programacion_ud; ?>/<?= $nro_calificacion; ?>">Guardar</a></center>
            <?php endif; ?>

        </div>
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
    </style>
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
    </script>
<?php else: ?>
    <p>No tiene permisos para editar Calificaciones o el periodo ya culminó.</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>