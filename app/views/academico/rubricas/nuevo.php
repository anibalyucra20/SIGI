<?php 
$module = 'academico';
require __DIR__ . '/../../layouts/header.php'; 
?>

<div class="card p-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-success mb-0"><i class="fa fa-plus-circle"></i> Nueva Rúbrica de Evaluación</h3>
        <a href="<?= BASE_URL ?>/academico/rubricas" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Volver a Mis Rúbricas
        </a>
    </div>

    <!-- NAVEGACIÓN POR PESTAÑAS (UX: Elegir el método de creación) -->
    <ul class="nav nav-tabs" id="creacionTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="crear-cero-tab" data-toggle="tab" href="#crear-cero" role="tab">
                <i class="fa fa-edit"></i> Crear desde Cero
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-info font-weight-bold" id="clonar-master-tab" data-toggle="tab" href="#clonar-master" role="tab">
                <i class="fa fa-cloud-download-alt"></i> Explorar Catálogo Institucional (Clonar)
            </a>
        </li>
    </ul>

    <div class="tab-content pt-3 border-left border-right border-bottom p-3 mb-3">
        
        <!-- ========================================== -->
        <!-- PESTAÑA 1: CREAR DESDE CERO (Matriz Dinámica)-->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="crear-cero" role="tabpanel">
            <div class="alert alert-success small">
                <i class="fa fa-info-circle"></i> Construya su propia matriz de evaluación. Añada los criterios en las filas y los niveles de logro (con sus respectivos puntajes) en las columnas.
            </div>
            
            <form action="<?= BASE_URL ?>/academico/rubricas/guardar" method="POST" id="frmRubricaLocal">
                <input type="hidden" name="contenido_json" id="input_contenido_json" value="">

                <div class="row mb-3 bg-light p-3 rounded border">
                    <div class="col-md-6">
                        <label class="font-weight-bold">Nombre de la Rúbrica <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control border-success" required placeholder="Ej: Rúbrica para Examen Parcial">
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold text-primary">Vincular a Unidad Didáctica (Opcional):</label>
                        <select name="unidad_didactica_id" class="form-control">
                            <option value="">-- Guardar en mi banco sin asignar --</option>
                            <?php if(isset($unidades)): foreach($unidades as $ud): ?>
                                <option value="<?= $ud['id_unidad_didactica'] ?>"><?= htmlspecialchars($ud['nombre_ud']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3 text-secondary"><i class="fa fa-table"></i> Constructor de Matriz</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle" id="tablaRubricaConstructor">
                        <thead class="table-light">
                            <tr id="trCabeceraNiveles">
                                <th style="width: 25%;" class="align-middle">Criterios de Evaluación</th>
                                <th style="width: 10%;" id="thAddNivel" class="align-middle">
                                    <button type="button" class="btn btn-sm btn-info w-100 font-weight-bold" id="btn-add-nivel">+ Agregar Nivel</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCriteriosConstructor"></tbody>
                    </table>
                </div>

                <div class="mb-4 d-flex">
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btn-add-criterio">
                        <i class="fa fa-plus"></i> Agregar Criterio (Fila)
                    </button>
                    <button type="button" class="btn btn-info btn-sm font-weight-bold ml-2" data-toggle="modal" data-target="#modalImportarJSON">
                        <i class="fa fa-code"></i> Importar JSON
                    </button>
                    <button type="button" class="btn btn-dark btn-sm font-weight-bold ml-2" onclick="previsualizarJSON()">
                        <i class="fa fa-eye"></i> Ver / Exportar JSON
                    </button>
                </div>

                <div class="text-right border-top pt-3">
                    <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Guardar Rúbrica</button>
                </div>
            </form>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 2: EXPLORAR Y CLONAR (Catálogo)    -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="clonar-master" role="tabpanel">
            <div class="alert alert-info small">
                <i class="fa fa-info-circle"></i> Seleccione una rúbrica oficial de la institución para previsualizarla y clonarla directamente a su banco personal.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Técnica</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-catalogo-master">
                        <tr><td colspan="5" class="text-center text-info"><i class="fa fa-spinner fa-spin"></i> Haga clic aquí para cargar el catálogo...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
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
                    <strong>Atención:</strong> Al importar, se reemplazará la matriz actual. Asegúrate de que el JSON contenga la estructura correcta.
                </div>
                <div class="form-group">
                    <textarea id="textarea_import_json" class="form-control text-monospace" rows="12" placeholder='{
  "criterios": [
    {
      "description": "Criterio 1",
      "niveles": [
        {"score": 0, "definition": "Malo"},
        {"score": 10, "definition": "Bueno"}
      ]
    }
  ]
}'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="procesarImportacionJSON()">Cargar a la Matriz</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: PREVISUALIZAR Y CLONAR DEL MASTER   -->
<!-- ========================================== -->
<div class="modal fade" id="modalPrevisualizar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tituloModalRubrica"><i class="fa fa-copy"></i> Clonar Rúbrica Institucional</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="<?= BASE_URL ?>/academico/rubricas/guardar" method="POST" id="frmClonar">
                <div class="modal-body">
                    <input type="hidden" name="master_rubrica_id" id="hidden_master_id">
                    <input type="hidden" name="contenido_json" id="hidden_contenido_json">
                    
                    <div class="row mb-3 bg-light p-3 rounded">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Nombre de la Rúbrica (Copia Local):</label>
                            <input type="text" name="nombre" id="hidden_nombre" class="form-control border-success" required>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold text-primary">Vincular a Unidad Didáctica (Opcional):</label>
                            <select name="unidad_didactica_id" class="form-control border-primary">
                                <option value="">-- Guardar en mi banco sin asignar --</option>
                                <?php if(isset($unidades)): foreach($unidades as $ud): ?>
                                    <option value="<?= $ud['id_unidad_didactica'] ?>"><?= htmlspecialchars($ud['nombre_ud']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-secondary font-weight-bold border-bottom pb-2">Previsualización de la Matriz:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center" id="tablaPreviewRubrica">
                            <thead class="table-dark">
                                <tr id="trCabeceraPreview">
                                    <th style="width: 25%;">Criterios</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPreview"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnProcesarClon"><i class="fa fa-save"></i> Confirmar Clonación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) return;

    // =========================================================================
    // 1. LÓGICA: CREAR DESDE CERO (Constructor de Matriz y JSON)
    // =========================================================================
    let nivelCount = 0;
    const trCabecera = document.getElementById('trCabeceraNiveles');
    const thAddNivel = document.getElementById('thAddNivel');
    const tbody = document.getElementById('tbodyCriteriosConstructor');

    // Inicializar 3 niveles y 1 criterio por defecto
    agregarNivel(0); agregarNivel(10); agregarNivel(20);
    agregarCriterio();

    document.getElementById('btn-add-nivel').addEventListener('click', () => agregarNivel(0));
    document.getElementById('btn-add-criterio').addEventListener('click', () => agregarCriterio());

    function agregarNivel(puntaje = 0) {
        nivelCount++;
        const th = document.createElement('th');
        th.className = 'nivel-col bg-secondary text-white';
        th.innerHTML = `
            <div class="input-group input-group-sm mb-1">
                <div class="input-group-prepend"><span class="input-group-text bg-dark text-white border-dark">Pts</span></div>
                <input type="number" class="form-control input-score text-center font-weight-bold" value="${puntaje}" min="0" required>
            </div>
            <button type="button" class="btn btn-danger btn-sm w-100" onclick="eliminarNivel(this)" title="Eliminar Columna"><i class="fa fa-trash"></i></button>`;
        trCabecera.insertBefore(th, thAddNivel);

        document.querySelectorAll('#tbodyCriteriosConstructor .criterio-row').forEach(tr => {
            const td = document.createElement('td');
            td.innerHTML = `<textarea class="form-control input-def" rows="3" placeholder="Describa el nivel..." required></textarea>`;
            tr.appendChild(td);
        });
    }

    function agregarCriterio(descripcion = '', definicionesNiveles = []) {
        if (typeof descripcion !== 'string') descripcion = '';

        const tr = document.createElement('tr');
        tr.className = 'criterio-row';
        let tds = `<td>
            <textarea class="form-control input-criterio-desc mb-2 border-primary" rows="2" placeholder="Nombre del criterio..." required>${descripcion}</textarea>
            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('tr').remove()"><i class="fa fa-times"></i> Quitar</button>
        </td>`;
        for(let i = 0; i < nivelCount; i++) {
            let defText = definicionesNiveles[i] !== undefined ? definicionesNiveles[i] : '';
            tds += `<td><textarea class="form-control input-def" rows="3" placeholder="Describa el nivel..." required>${defText}</textarea></td>`;
        }
        tr.innerHTML = tds;
        tbody.appendChild(tr);
    }

    window.eliminarNivel = function(btn) {
        if(nivelCount <= 1) {
            alert('La rúbrica debe tener al menos un nivel de puntuación.');
            return;
        }
        const th = btn.closest('th');
        const idx = Array.from(th.parentNode.children).indexOf(th);
        th.remove();
        document.querySelectorAll('#tbodyCriteriosConstructor .criterio-row').forEach(tr => tr.children[idx].remove());
        nivelCount--;
    };

    // Función Central de Extracción JSON
    function compilarJSONMatriz() {
        const payload = { criterios: [] };
        const puntajes = [];
        
        document.querySelectorAll('#trCabeceraNiveles .input-score').forEach(input => puntajes.push(parseFloat(input.value) || 0));

        let sortOrder = 1;
        document.querySelectorAll('#tbodyCriteriosConstructor .criterio-row').forEach(tr => {
            const descCriterio = tr.querySelector('.input-criterio-desc').value.trim();
            if (!descCriterio) return; 

            const criterio = { sortorder: sortOrder++, description: descCriterio, niveles: [] };
            tr.querySelectorAll('.input-def').forEach((textarea, idx) => {
                criterio.niveles.push({ score: puntajes[idx], definition: textarea.value.trim() });
            });
            payload.criterios.push(criterio);
        });

        return payload;
    }

    // Guardado natural
    document.getElementById('frmRubricaLocal').addEventListener('submit', function (e) {
        const payload = compilarJSONMatriz();
        document.getElementById('input_contenido_json').value = JSON.stringify(payload);
    });

    // =========================================================
    // IMPORTAR / EXPORTAR JSON (Matriz Dinámica)
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
            alert("JSON copiado al portapapeles exitosamente.");
        }).catch(err => {
            document.execCommand('copy');
            alert("JSON copiado al portapapeles.");
        });
    };

    function limpiarMatrizDOM() {
        document.querySelectorAll('#trCabeceraNiveles .nivel-col').forEach(el => el.remove());
        tbody.innerHTML = '';
        nivelCount = 0;
    }

    function construirMatriz(data) {
        if (data.criterios && data.criterios.length > 0) {
            const nivelesBase = data.criterios[0].niveles;
            nivelesBase.forEach(nivel => agregarNivel(nivel.score));
            data.criterios.forEach(criterio => {
                const defs = criterio.niveles.map(n => n.definition);
                agregarCriterio(criterio.description, defs);
            });
        } else {
            agregarNivel(0); agregarNivel(10); agregarNivel(20);
            agregarCriterio();
        }
    }

    window.procesarImportacionJSON = function() {
        const jsonText = document.getElementById('textarea_import_json').value.trim();
        if (!jsonText) {
            alert("Por favor, pegue un código JSON válido.");
            return;
        }

        try {
            const dataImportada = JSON.parse(jsonText);
            if (!dataImportada.criterios || !Array.isArray(dataImportada.criterios)) {
                throw new Error("El JSON no tiene un nodo 'criterios' válido.");
            }
            limpiarMatrizDOM();
            construirMatriz(dataImportada);
            $('#modalImportarJSON').modal('hide');
            document.getElementById('textarea_import_json').value = '';
            alert("Matriz reconstruida correctamente a partir del JSON.");
        } catch (error) {
            alert("Error de validación JSON:\n" + error.message);
        }
    };


    // =========================================================================
    // 2. LÓGICA: EXPLORAR Y CLONAR CATÁLOGO MASTER
    // =========================================================================
    let catalogoCargado = false;

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'clonar-master-tab' && !catalogoCargado) {
            cargarCatalogoMaster();
        }
    });

    function cargarCatalogoMaster() {
        const tbodyMaster = document.getElementById('tbody-catalogo-master');
        tbodyMaster.innerHTML = '<tr><td colspan="5" class="text-center text-info"><i class="fa fa-spinner fa-spin"></i> Cargando catálogo institucional...</td></tr>';
        
        fetch('<?= BASE_URL ?>/academico/rubricas/catalogoMaster')
        .then(r => r.json())
        .then(res => {
            if (res.ok && res.data && res.data.length > 0) {
                tbodyMaster.innerHTML = '';
                res.data.forEach((r, i) => {
                    tbodyMaster.innerHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td class="font-weight-bold">${r.nombre}</td>
                            <td><span class="badge badge-primary">${r.tipo_tecnica}</span></td>
                            <td class="small">${r.descripcion || '-'}</td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="previsualizarMaster(${r.id}, '${r.nombre.replace(/'/g, "\\'")}')">
                                    <i class="fa fa-eye"></i> Previsualizar y Clonar
                                </button>
                            </td>
                        </tr>
                    `;
                });
                catalogoCargado = true;
            } else {
                tbodyMaster.innerHTML = `<tr><td colspan="5" class="text-center text-warning">${res.details || 'No hay rúbricas disponibles.'}</td></tr>`;
            }
        }).catch(err => {
            tbodyMaster.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Error de red al conectar.</td></tr>`;
        });
    }

    window.previsualizarMaster = function(idMaster, nombre) {
        document.getElementById('tituloModalRubrica').innerText = 'Cargando matriz...';
        document.getElementById('btnProcesarClon').disabled = true;
        document.getElementById('tbodyPreview').innerHTML = '';
        document.querySelectorAll('#trCabeceraPreview .preview-col').forEach(e => e.remove());
        $('#modalPrevisualizar').modal('show');

        fetch(`<?= BASE_URL ?>/academico/rubricas/detalleMaster/${idMaster}`)
        .then(r => r.json())
        .then(res => {
            if(res.ok && res.data && res.data.contenido_json) {
                const rub = res.data;
                document.getElementById('tituloModalRubrica').innerHTML = `<i class="fa fa-copy"></i> Clonar: ${rub.nombre}`;
                document.getElementById('hidden_master_id').value = rub.id;
                document.getElementById('hidden_nombre').value = rub.nombre + ' (Copia)';
                
                const jsonParseado = typeof rub.contenido_json === 'string' ? JSON.parse(rub.contenido_json) : rub.contenido_json;
                document.getElementById('hidden_contenido_json').value = JSON.stringify(jsonParseado);
                
                dibujarMatrizPreview(jsonParseado);
                document.getElementById('btnProcesarClon').disabled = false;
            } else {
                alert("Error al cargar la rúbrica.");
                $('#modalPrevisualizar').modal('hide');
            }
        });
    };

    function dibujarMatrizPreview(data) {
        if (!data.criterios || data.criterios.length === 0) return;
        const head = document.getElementById('trCabeceraPreview');
        const body = document.getElementById('tbodyPreview');
        
        data.criterios[0].niveles.forEach(n => {
            const th = document.createElement('th');
            th.className = 'preview-col bg-secondary text-white';
            th.innerText = `${n.score} Pts`;
            head.appendChild(th);
        });

        data.criterios.forEach(c => {
            let tr = `<tr><td class="font-weight-bold text-primary text-left">${c.description}</td>`;
            c.niveles.forEach(n => tr += `<td class="small">${n.definition}</td>`);
            tr += `</tr>`;
            body.innerHTML += tr;
        });
    }
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>