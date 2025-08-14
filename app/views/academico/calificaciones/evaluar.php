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
                Evaluación - Calificación <?= $nro_calificacion ?>
                <?php if (!empty($nombreIndicador)) : ?>
                    - <?= $nombreIndicador ?>
                <?php endif; ?>
                - <span style="color:#4153a1;"><?php echo strtoupper($nombreUnidadDidactica ?? ''); ?></span>
            </h5>
        
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
                        <?php foreach ($anyEv as $iEval => $eval): ?>
                            <?php foreach ($eval['criterios'] as $iCrit => $criterio): ?>
                                <?php
                                // Obtiene todos los IDs de la columna
                                $ids = isset($criterio_ids_columnas[$iEval][$iCrit]) && count($criterio_ids_columnas[$iEval][$iCrit])
                                    ? implode(',', $criterio_ids_columnas[$iEval][$iCrit])
                                    : $criterio['id'];
                                ?>
                                <th class="text-center criterio-header p-0" style="width: 40px;">
                                    <?php if ($periodo_vigente['vigente']): ?>
                                        <button class="btn btn-info mb-1 btn-sm btn-editar-criterio"
                                            data-ids-criterio="<?= $ids ?>"><i class="fa fa-pen"></i></button>
                                    <?php endif; ?>
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
                                location.reload();
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
                                location.reload();
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

        document.querySelectorAll('.btn-agregar-criterio').forEach(function(btn) {
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
        });
    </script>
<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>