<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h4 class="mb-0 font-size-18"><i class="fas fa-book-open text-primary mr-2"></i> Banco Maestro de Rúbricas y Preguntas</h4>
                <small class="text-muted">Criterios e indicadores predeterminados reutilizables en las encuestas de evaluación docente</small>
            </div>
            <div class="page-title-right mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Ir a Encuestas
                </a>
                <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="abrirModalCriterio()">
                    <i class="fas fa-plus-circle mr-1"></i> Nuevo Criterio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Banner Informativo -->
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
            <div>
                <h6 class="alert-heading font-weight-bold mb-1">Reutilización de Instrumentos de Evaluación</h6>
                <p class="mb-0 font-size-13 text-dark">
                    Los criterios y preguntas configurados aquí constituyen el <strong>Banco Maestro Institucional</strong>. 
                    Al programar una nueva encuesta o importar desde la configuración de criterios, se clonarán automáticamente 
                    estos instrumentos para que los docentes y alumnos no requieran configuraciones repetitivas.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if (empty($criterios)): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3.5rem;"></i>
                    <h5 class="font-weight-bold text-dark">No hay criterios registrados en el banco maestro</h5>
                    <p class="text-muted small">Empieza agregando un criterio de evaluación para construir tu banco de rúbricas.</p>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="abrirModalCriterio()">
                        <i class="fas fa-plus mr-1"></i> Crear Primer Criterio
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($criterios as $crit): ?>
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom flex-wrap">
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="badge badge-primary px-2 py-1 mr-2 font-size-12">
                                <i class="fas fa-sort-numeric-down mr-1"></i> Orden #<?= htmlspecialchars($crit['orden']) ?>
                            </span>
                            <span class="badge <?= $crit['estado'] ? 'badge-success' : 'badge-secondary' ?> px-2 py-1 mr-2 font-size-12">
                                <?= $crit['estado'] ? 'ACTIVO' : 'INACTIVO' ?>
                            </span>
                            <span class="badge badge-soft-info px-2 py-1 mr-2 font-size-12">
                                Peso: <?= number_format($crit['peso'], 2) ?>
                            </span>
                            <h5 class="mb-0 font-weight-bold text-dark align-middle">
                                <?= htmlspecialchars($crit['nombre']) ?>
                            </h5>
                        </div>
                        <div class="mt-2 mt-md-0 text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="abrirModalPregunta(<?= $crit['id'] ?>)">
                                <i class="fas fa-plus mr-1"></i> Agregar Pregunta
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editarCriterio(<?= $crit['id'] ?>)" title="Editar Criterio">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <a href="<?= BASE_URL ?>/evaluacion/banco/eliminarCriterio/<?= $crit['id'] ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               title="Eliminar Criterio"
                               onclick="return confirm('¿Seguro de eliminar este criterio y todas sus preguntas asociadas del banco?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($crit['descripcion'])): ?>
                        <div class="card-body bg-light py-2 px-3 border-bottom">
                            <small class="text-muted"><i class="fas fa-align-left mr-1"></i> <?= htmlspecialchars($crit['descripcion']) ?></small>
                        </div>
                    <?php endif; ?>

                    <div class="card-body p-0">
                        <?php if (empty($crit['preguntas'])): ?>
                            <div class="p-4 text-center text-muted">
                                <small>No hay preguntas registradas en este criterio. Haz clic en <strong>Agregar Pregunta</strong> para añadir una.</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">#</th>
                                            <th>Enunciado de la Pregunta / Indicador</th>
                                            <th style="width: 140px;" class="text-center">Tipo Respuesta</th>
                                            <th style="width: 120px;" class="text-center">Obligatoria</th>
                                            <th style="width: 100px;" class="text-center">Estado</th>
                                            <th style="width: 110px;" class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($crit['preguntas'] as $idx => $preg): ?>
                                            <tr>
                                                <td class="text-center font-weight-bold text-muted"><?= $preg['orden'] ?: ($idx + 1) ?></td>
                                                <td>
                                                    <span class="text-dark font-weight-semibold d-block">
                                                        <?= htmlspecialchars($preg['enunciado']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light border">
                                                        <?= $preg['tipo_respuesta'] === 'ESCALA' ? 'Escala 1 al 5' : 'Texto Libre' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?= $preg['es_obligatoria'] ? '<span class="badge badge-soft-success px-2 py-1 font-size-11">Obligatoria</span>' : '<span class="badge badge-soft-secondary px-2 py-1 font-size-11">Opcional</span>' ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= $preg['estado'] ? '<span class="badge badge-success px-2 py-1 font-size-11">Activa</span>' : '<span class="badge badge-secondary px-2 py-1 font-size-11">Inactiva</span>' ?>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editarPregunta(<?= $preg['id'] ?>)" title="Editar Pregunta">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <a href="<?= BASE_URL ?>/evaluacion/banco/eliminarPregunta/<?= $preg['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       title="Eliminar Pregunta"
                                                       onclick="return confirm('¿Seguro de eliminar esta pregunta del banco?')">
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
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL CRITERIO -->
<div class="modal fade" id="modalCriterio" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="<?= BASE_URL ?>/evaluacion/banco/guardarCriterio" method="POST" class="modal-content shadow border-0">
            <input type="hidden" name="id_criterio" id="crit_id">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="crit_modal_title">
                    <i class="fas fa-layer-group mr-2"></i> Nuevo Criterio de Rúbrica
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Nombre del Criterio <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="crit_nombre" class="form-control" placeholder="Ej. Metodología y Estrategias Didácticas" required>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Descripción / Ámbito de Evaluación</label>
                    <textarea name="descripcion" id="crit_descripcion" class="form-control" rows="3" placeholder="Breve explicación de las competencias a evaluar en este criterio..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-dark">Peso Ponderado</label>
                        <input type="number" step="0.01" min="0" max="100" name="peso" id="crit_peso" class="form-control" value="1.00" required>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-dark">Orden de Presentación</label>
                        <input type="number" min="1" name="orden" id="crit_orden" class="form-control" value="1" required>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark">Estado</label>
                    <select name="estado" id="crit_estado" class="form-control">
                        <option value="1">Activo (Disponible para encuestas)</option>
                        <option value="0">Inactivo (No incluir)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar Criterio</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PREGUNTA -->
<div class="modal fade" id="modalPregunta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="<?= BASE_URL ?>/evaluacion/banco/guardarPregunta" method="POST" class="modal-content shadow border-0">
            <input type="hidden" name="id_pregunta" id="preg_id">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold" id="preg_modal_title">
                    <i class="fas fa-question-circle mr-2"></i> Nueva Pregunta / Indicador
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Criterio Perteneciente <span class="text-danger">*</span></label>
                    <select name="id_banco_criterio" id="preg_id_criterio" class="form-control" required>
                        <?php foreach ($criterios as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Enunciado / Pregunta <span class="text-danger">*</span></label>
                    <textarea name="enunciado" id="preg_enunciado" class="form-control" rows="3" placeholder="Ej. Explica las sesiones con claridad y dinamismo..." required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-dark">Tipo de Respuesta</label>
                        <select name="tipo_respuesta" id="preg_tipo_respuesta" class="form-control">
                            <option value="ESCALA">Escala Likert (1 a 5)</option>
                            <option value="TEXTO">Texto Libre</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-dark">Orden</label>
                        <input type="number" min="1" name="orden" id="preg_orden" class="form-control" value="1" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-0">
                        <label class="font-weight-bold text-dark">¿Es Obligatoria?</label>
                        <select name="es_obligatoria" id="preg_es_obligatoria" class="form-control">
                            <option value="1">Sí, obligatoria</option>
                            <option value="0">Opcional</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="font-weight-bold text-dark">Estado</label>
                        <select name="estado" id="preg_estado" class="form-control">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success font-weight-bold"><i class="fas fa-save mr-1"></i> Guardar Pregunta</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCriterio() {
    document.getElementById('crit_id').value = '';
    document.getElementById('crit_nombre').value = '';
    document.getElementById('crit_descripcion').value = '';
    document.getElementById('crit_peso').value = '1.00';
    document.getElementById('crit_orden').value = '1';
    document.getElementById('crit_estado').value = '1';
    document.getElementById('crit_modal_title').innerHTML = '<i class="fas fa-layer-group mr-2"></i> Nuevo Criterio de Rúbrica';
    $('#modalCriterio').modal('show');
}

function editarCriterio(id) {
    fetch('<?= BASE_URL ?>/evaluacion/banco/ajaxObtenerCriterio/' + id)
        .then(res => res.json())
        .then(res => {
            if (res.ok && res.data) {
                document.getElementById('crit_id').value = res.data.id;
                document.getElementById('crit_nombre').value = res.data.nombre;
                document.getElementById('crit_descripcion').value = res.data.descripcion || '';
                document.getElementById('crit_peso').value = res.data.peso;
                document.getElementById('crit_orden').value = res.data.orden;
                document.getElementById('crit_estado').value = res.data.estado;
                document.getElementById('crit_modal_title').innerHTML = '<i class="fas fa-pencil-alt mr-2"></i> Editar Criterio de Rúbrica';
                $('#modalCriterio').modal('show');
            } else {
                alert('No se pudo cargar la información del criterio.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al obtener datos del criterio.');
        });
}

function abrirModalPregunta(idCriterio) {
    document.getElementById('preg_id').value = '';
    if (idCriterio) {
        document.getElementById('preg_id_criterio').value = idCriterio;
    }
    document.getElementById('preg_enunciado').value = '';
    document.getElementById('preg_tipo_respuesta').value = 'ESCALA';
    document.getElementById('preg_orden').value = '1';
    document.getElementById('preg_es_obligatoria').value = '1';
    document.getElementById('preg_estado').value = '1';
    document.getElementById('preg_modal_title').innerHTML = '<i class="fas fa-question-circle mr-2"></i> Nueva Pregunta / Indicador';
    $('#modalPregunta').modal('show');
}

function editarPregunta(id) {
    fetch('<?= BASE_URL ?>/evaluacion/banco/ajaxObtenerPregunta/' + id)
        .then(res => res.json())
        .then(res => {
            if (res.ok && res.data) {
                document.getElementById('preg_id').value = res.data.id;
                document.getElementById('preg_id_criterio').value = res.data.id_banco_criterio;
                document.getElementById('preg_enunciado').value = res.data.enunciado;
                document.getElementById('preg_tipo_respuesta').value = res.data.tipo_respuesta;
                document.getElementById('preg_orden').value = res.data.orden;
                document.getElementById('preg_es_obligatoria').value = res.data.es_obligatoria;
                document.getElementById('preg_estado').value = res.data.estado;
                document.getElementById('preg_modal_title').innerHTML = '<i class="fas fa-pencil-alt mr-2"></i> Editar Pregunta / Indicador';
                $('#modalPregunta').modal('show');
            } else {
                alert('No se pudo cargar la información de la pregunta.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al obtener datos de la pregunta.');
        });
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
