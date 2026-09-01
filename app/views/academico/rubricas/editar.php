<?php 
$module = 'academico';
require __DIR__ . '/../../layouts/header.php'; 
?>

<div class="card p-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-warning mb-0"><i class="fa fa-pen"></i> Editar Rúbrica Local</h3>
        <a href="<?= BASE_URL ?>/academico/rubricas" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Volver al Banco
        </a>
    </div>
    
    <form action="<?= BASE_URL ?>/academico/rubricas/guardar" method="POST" id="frmRubricaLocal">
        <input type="hidden" name="id" value="<?= $rubrica['id'] ?>">
        <input type="hidden" name="contenido_json" id="input_contenido_json" value="">

        <div class="row mb-3 bg-light p-3 rounded border-warning">
            <div class="col-md-6">
                <label class="font-weight-bold">Nombre de la Rúbrica <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control border-warning" required value="<?= htmlspecialchars($rubrica['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <label class="font-weight-bold text-primary">Vincular a Unidad Didáctica</label>
                <select name="unidad_didactica_id" class="form-control">
                    <option value="">-- Mantener en mi banco (Sin asignar) --</option>
                    <?php if(isset($unidades)): foreach($unidades as $ud): ?>
                        <option value="<?= $ud['id_unidad_didactica'] ?>" <?= ($rubrica['unidad_didactica_id'] == $ud['id_unidad_didactica']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ud['nombre_ud']) ?> (Sec: <?= $ud['seccion'] ?>)
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <hr>
        <h5 class="mb-3 text-secondary"><i class="fa fa-table"></i> Constructor de la Matriz de Evaluación</h5>
        
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle" id="tablaRubrica">
                <thead class="table-light">
                    <tr id="trCabeceraNiveles">
                        <th style="width: 25%;" class="align-middle">Criterios de Evaluación</th>
                        <th style="width: 10%;" id="thAddNivel" class="align-middle">
                            <button type="button" class="btn btn-sm btn-info w-100 font-weight-bold" id="btn-add-nivel">+ Agregar Nivel</button>
                        </th>
                    </tr>
                </thead>
                <tbody id="tbodyCriterios"></tbody>
            </table>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btn-add-criterio">
                <i class="fa fa-plus"></i> Agregar Nuevo Criterio (Fila)
            </button>
            <button type="button" class="btn btn-info btn-sm font-weight-bold ml-2" data-toggle="modal" data-target="#modalImportarJSON">
                <i class="fa fa-code"></i> Importar JSON
            </button>
            <button type="button" class="btn btn-dark btn-sm font-weight-bold ml-2" onclick="previsualizarJSON()">
                <i class="fa fa-eye"></i> Ver / Exportar JSON
            </button>
        </div>

        <div class="text-right border-top pt-3">
            <button type="submit" class="btn btn-warning btn-lg"><i class="fa fa-save"></i> Actualizar Rúbrica</button>
        </div>
    </form>
</div>

<!-- ========================================== -->
<!-- MODALES PARA IMPORTAR Y EXPORTAR JSON      -->
<!-- ========================================== -->

<!-- Modal: Ver y Copiar JSON Generado -->
<div class="modal fade" id="modalVerJSON" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa fa-code"></i> Previsualización del JSON</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <textarea id="textarea_export_json" class="form-control text-monospace" rows="15" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="copiarJSON()">
                    <i class="fa fa-copy"></i> Copiar al Portapapeles
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Importar JSON -->
<div class="modal fade" id="modalImportarJSON" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-file-import"></i> Importar JSON de Rúbrica</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    <strong>Atención:</strong> Al importar, se reemplazará la matriz actual.
                </div>
                <div class="form-group">
                    <textarea id="textarea_import_json" class="form-control text-monospace" rows="12" placeholder='{"criterios":[]}'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="procesarImportacionJSON()">Cargar a la Matriz</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let nivelCount = 0;
    const trCabecera = document.getElementById('trCabeceraNiveles');
    const thAddNivel = document.getElementById('thAddNivel');
    const tbody = document.getElementById('tbodyCriterios');

    // Extraer JSON inyectado desde PHP
    const rubricaData = <?= $rubrica['contenido_json'] ?: '{"criterios":[]}' ?>;

    // Hidratación de la Matriz
    function construirMatriz(data) {
        if (data.criterios && data.criterios.length > 0) {
            data.criterios[0].niveles.forEach(n => agregarNivelDOM(n.score));
            data.criterios.forEach(c => {
                const defs = c.niveles.map(n => n.definition);
                agregarCriterioDOM(c.description, defs);
            });
        } else {
            // Failsafe
            agregarNivelDOM(0); agregarNivelDOM(10); agregarNivelDOM(20);
            agregarCriterioDOM('', ['', '', '']);
        }
    }

    function agregarNivelDOM(puntaje = 0) {
        nivelCount++;
        const th = document.createElement('th');
        th.className = 'nivel-col bg-secondary text-white';
        th.innerHTML = `
            <div class="input-group input-group-sm mb-1">
                <div class="input-group-prepend"><span class="input-group-text bg-dark text-white border-dark">Pts</span></div>
                <input type="number" class="form-control input-score text-center font-weight-bold" value="${puntaje}" min="0" required>
            </div>
            <button type="button" class="btn btn-danger btn-sm w-100" onclick="eliminarNivel(this)"><i class="fa fa-trash"></i></button>`;
        trCabecera.insertBefore(th, thAddNivel);
    }

    function agregarCriterioDOM(desc = '', defs = []) {
        const tr = document.createElement('tr');
        tr.className = 'criterio-row';
        let tds = `<td>
            <textarea class="form-control input-criterio-desc mb-2 border-primary" rows="2" required>${desc}</textarea>
            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('tr').remove()"><i class="fa fa-times"></i> Quitar</button>
        </td>`;
        for(let i = 0; i < nivelCount; i++) {
            let defText = defs[i] !== undefined ? defs[i] : '';
            tds += `<td><textarea class="form-control input-def" rows="3" required>${defText}</textarea></td>`;
        }
        tr.innerHTML = tds;
        tbody.appendChild(tr);
    }

    // Eventos 
    document.getElementById('btn-add-nivel').addEventListener('click', () => {
        agregarNivelDOM(0);
        document.querySelectorAll('.criterio-row').forEach(tr => {
            const td = document.createElement('td');
            td.innerHTML = `<textarea class="form-control input-def" rows="3" required></textarea>`;
            tr.appendChild(td);
        });
    });

    document.getElementById('btn-add-criterio').addEventListener('click', () => agregarCriterioDOM());

    window.eliminarNivel = function(btn) {
        if(nivelCount <= 1) {
            alert('La rúbrica debe tener al menos un nivel de puntuación.');
            return;
        }
        const th = btn.closest('th');
        const idx = Array.from(th.parentNode.children).indexOf(th);
        th.remove();
        document.querySelectorAll('.criterio-row').forEach(tr => tr.children[idx].remove());
        nivelCount--;
    };

    // Función de extracción centralizada
    function compilarJSONMatriz() {
        const payload = { criterios: [] };
        const puntajes = [];
        document.querySelectorAll('.input-score').forEach(inp => puntajes.push(parseFloat(inp.value) || 0));

        let sortOrder = 1;
        document.querySelectorAll('.criterio-row').forEach(tr => {
            const desc = tr.querySelector('.input-criterio-desc').value.trim();
            if (!desc) return;
            const crit = { sortorder: sortOrder++, description: desc, niveles: [] };
            tr.querySelectorAll('.input-def').forEach((ta, i) => {
                crit.niveles.push({ score: puntajes[i], definition: ta.value.trim() });
            });
            payload.criterios.push(crit);
        });
        return payload;
    }

    document.getElementById('frmRubricaLocal').addEventListener('submit', function (e) {
        document.getElementById('input_contenido_json').value = JSON.stringify(compilarJSONMatriz());
    });

    // =========================================================
    // IMPORTAR / EXPORTAR JSON
    // =========================================================
    window.previsualizarJSON = function() {
        const payload = compilarJSONMatriz();
        document.getElementById('textarea_export_json').value = JSON.stringify(payload, null, 2);
        $('#modalVerJSON').modal('show');
    };

    window.copiarJSON = function() {
        const textarea = document.getElementById('textarea_export_json');
        textarea.select();
        textarea.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(textarea.value).then(() => {
            alert("JSON copiado exitosamente.");
        }).catch(err => {
            document.execCommand('copy');
            alert("JSON copiado al portapapeles.");
        });
    };

    function limpiarMatrizDOM() {
        document.querySelectorAll('.nivel-col').forEach(el => el.remove());
        tbody.innerHTML = '';
        nivelCount = 0;
    }

    window.procesarImportacionJSON = function() {
        const jsonText = document.getElementById('textarea_import_json').value.trim();
        if (!jsonText) {
            alert("Pegue un JSON válido.");
            return;
        }
        try {
            const dataImportada = JSON.parse(jsonText);
            if (!dataImportada.criterios || !Array.isArray(dataImportada.criterios)) {
                throw new Error("Falta el nodo 'criterios'.");
            }
            limpiarMatrizDOM();
            construirMatriz(dataImportada);
            $('#modalImportarJSON').modal('hide');
            document.getElementById('textarea_import_json').value = '';
        } catch (error) {
            alert("Error JSON: " + error.message);
        }
    };

    // Iniciar
    construirMatriz(rubricaData);
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>