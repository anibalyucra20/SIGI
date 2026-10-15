<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0 font-size-18"><i class="fas fa-question-circle text-primary mr-2"></i> Preguntas e Indicadores de Evaluación</h4>
                <small class="text-muted"><?= htmlspecialchars($encuesta['titulo']) ?></small>
            </div>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Encuestas
                </a>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalPregunta" onclick="nuevaPregunta()">
                    <i class="fas fa-plus-circle mr-1"></i> Agregar Pregunta
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    Listado de Preguntas e Indicadores (Total: <?= count($preguntas) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($preguntas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-question-circle font-size-48 text-muted mb-3"></i>
                        <h5 class="text-muted">Aún no se han configurado preguntas para esta encuesta.</h5>
                        <button type="button" class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalPregunta" onclick="nuevaPregunta()">
                            Agregar Pregunta
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="6%" class="text-center">#</th>
                                    <th width="45%">Enunciado de la Pregunta / Indicador</th>
                                    <th width="25%">Criterio / Dimensión</th>
                                    <th width="12%">Tipo de Respuesta</th>
                                    <th width="12%" class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $n = 1; foreach ($preguntas as $p): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $p['orden'] ?: $n++ ?></td>
                                        <td>
                                            <span class="font-weight-semibold text-dark font-size-14">
                                                <?= htmlspecialchars($p['enunciado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-primary font-size-12">
                                                <i class="fas fa-layer-group mr-1"></i> <?= htmlspecialchars($p['criterio_nombre'] ?? 'Sin Criterio') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($p['tipo_respuesta'] === 'ESCALA'): ?>
                                                <span class="badge badge-success px-2 py-1">Escala Likert (1-5)</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">Texto Abierto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mr-1" 
                                                    onclick="editarPregunta(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/evaluacion/encuestas/eliminarPregunta/<?= $p['id'] ?>/<?= $encuesta['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('¿Estás seguro de eliminar esta pregunta?');"
                                               title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear / Editar Pregunta -->
<div class="modal fade" id="modalPregunta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="<?= BASE_URL ?>/evaluacion/encuestas/guardarPregunta" method="POST">
            <input type="hidden" name="id_encuesta" value="<?= $encuesta['id'] ?>">
            <input type="hidden" name="id" id="pregunta_id" value="">

            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="modalPreguntaTitulo">Agregar Pregunta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Criterio / Dimensión Asociada: <span class="text-danger">*</span></label>
                        <select name="id_criterio" id="pregunta_id_criterio" class="form-control" required>
                            <option value="">-- Seleccionar Criterio --</option>
                            <?php foreach ($criterios as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Enunciado de la Pregunta / Indicador: <span class="text-danger">*</span></label>
                        <textarea name="enunciado" id="pregunta_enunciado" class="form-control" rows="3" required 
                                  placeholder="Ej: El docente demuestra dominio y conocimiento actualizado de los contenidos desarrollados..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Tipo de Respuesta:</label>
                            <select name="tipo_respuesta" id="pregunta_tipo_respuesta" class="form-control">
                                <option value="ESCALA" selected>Escala Likert (1 - Deficiente a 5 - Excelente)</option>
                                <option value="TEXTO">Texto Libre / Comentario</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Orden de Aparición:</label>
                            <input type="number" name="orden" id="pregunta_orden" class="form-control" value="1" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Guardar Pregunta</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function nuevaPregunta() {
    document.getElementById('modalPreguntaTitulo').innerText = 'Agregar Pregunta';
    document.getElementById('pregunta_id').value = '';
    document.getElementById('pregunta_id_criterio').value = '';
    document.getElementById('pregunta_enunciado').value = '';
    document.getElementById('pregunta_tipo_respuesta').value = 'ESCALA';
    document.getElementById('pregunta_orden').value = '<?= count($preguntas) + 1 ?>';
}

function editarPregunta(p) {
    document.getElementById('modalPreguntaTitulo').innerText = 'Editar Pregunta';
    document.getElementById('pregunta_id').value = p.id;
    document.getElementById('pregunta_id_criterio').value = p.id_criterio || '';
    document.getElementById('pregunta_enunciado').value = p.enunciado;
    document.getElementById('pregunta_tipo_respuesta').value = p.tipo_respuesta;
    document.getElementById('pregunta_orden').value = p.orden;
    $('#modalPregunta').modal('show');
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
