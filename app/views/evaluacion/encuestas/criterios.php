<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0 font-size-18"><i class="fas fa-layer-group text-primary mr-2"></i> Criterios / Dimensiones de Evaluación</h4>
                <small class="text-muted"><?= htmlspecialchars($encuesta['titulo']) ?></small>
            </div>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Encuestas
                </a>
                <a href="<?= BASE_URL ?>/evaluacion/encuestas/importarDesdeBanco/<?= $encuesta['id'] ?>" 
                   class="btn btn-outline-info mr-2"
                   onclick="return confirm('¿Deseas importar todos los criterios y preguntas activos del Banco Maestro a esta encuesta?');">
                    <i class="fas fa-book-open mr-1"></i> Importar desde Banco Maestro
                </a>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCriterio" onclick="nuevoCriterio()">
                    <i class="fas fa-plus-circle mr-1"></i> Agregar Criterio
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
                    Listado de Criterios / Dimensiones
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($criterios)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-layer-group font-size-48 text-muted mb-3"></i>
                        <h5 class="text-muted">Aún no se han configurado criterios para esta encuesta.</h5>
                        <button type="button" class="btn btn-primary mt-2" data-toggle="modal" data-target="#modalCriterio" onclick="nuevoCriterio()">
                            Agregar Criterio
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="8%" class="text-center">Orden</th>
                                    <th width="35%">Nombre del Criterio / Dimensión</th>
                                    <th width="35%">Descripción</th>
                                    <th width="10%" class="text-center">Preguntas</th>
                                    <th width="12%" class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criterios as $c): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $c['orden'] ?></td>
                                        <td>
                                            <span class="font-weight-bold text-dark d-block font-size-14">
                                                <?= htmlspecialchars($c['nombre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($c['descripcion'] ?? 'Sin descripción') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-2 py-1"><?= $c['total_preguntas'] ?> preguntas</span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mr-1" 
                                                    onclick="editarCriterio(<?= htmlspecialchars(json_encode($c)) ?>)" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/evaluacion/encuestas/eliminarCriterio/<?= $c['id'] ?>/<?= $encuesta['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('¿Estás seguro de eliminar este criterio? Las preguntas asociadas quedarán sin dimensión.');"
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

<!-- Modal para Crear / Editar Criterio -->
<div class="modal fade" id="modalCriterio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="<?= BASE_URL ?>/evaluacion/encuestas/guardarCriterio" method="POST">
            <input type="hidden" name="id_encuesta" value="<?= $encuesta['id'] ?>">
            <input type="hidden" name="id" id="criterio_id" value="">

            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="modalCriterioTitulo">Agregar Criterio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Nombre del Criterio / Dimensión: <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="criterio_nombre" class="form-control" required placeholder="Ej: Metodología y Estrategias Didácticas">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Descripción:</label>
                        <textarea name="descripcion" id="criterio_descripcion" class="form-control" rows="2" placeholder="Breve explicación de la dimensión a evaluar..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold">Orden:</label>
                            <input type="number" name="orden" id="criterio_orden" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label class="font-weight-bold">Peso:</label>
                            <input type="number" step="0.01" name="peso" id="criterio_peso" class="form-control" value="1.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Guardar Criterio</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function nuevoCriterio() {
    document.getElementById('modalCriterioTitulo').innerText = 'Agregar Criterio';
    document.getElementById('criterio_id').value = '';
    document.getElementById('criterio_nombre').value = '';
    document.getElementById('criterio_descripcion').value = '';
    document.getElementById('criterio_orden').value = '<?= count($criterios) + 1 ?>';
    document.getElementById('criterio_peso').value = '1.00';
}

function editarCriterio(c) {
    document.getElementById('modalCriterioTitulo').innerText = 'Editar Criterio';
    document.getElementById('criterio_id').value = c.id;
    document.getElementById('criterio_nombre').value = c.nombre;
    document.getElementById('criterio_descripcion').value = c.descripcion || '';
    document.getElementById('criterio_orden').value = c.orden;
    document.getElementById('criterio_peso').value = c.peso;
    $('#modalCriterio').modal('show');
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
