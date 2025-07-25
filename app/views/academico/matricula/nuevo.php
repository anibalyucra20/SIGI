<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-4 shadow-sm rounded-3 mt-3">
        <h4>Nueva Matrícula</h4>
        <form action="<?= BASE_URL ?>/academico/matricula/guardar" method="post" id="form-matricula" autocomplete="off">
            <!-- BUSCADOR DE ESTUDIANTE POR DNI -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">DNI del Estudiante *</label>
                    <div class="input-group">
                        <input type="text" name="dni" id="dni-estudiante" class="form-control" maxlength="15" required>
                        <button type="button" id="buscar-estudiante" class="btn btn-secondary">Buscar</button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Apellidos y Nombres</label>
                    <input type="text" id="apellidos-nombres" class="form-control" disabled>
                </div>
            </div>
            <!-- SELECCIÓN DE PROGRAMA Y PLAN DE ESTUDIOS -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Programa de Estudios *</label>
                    <select name="id_programa_estudios" id="id_programa_estudios" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <!-- Opciones se llenarán por JS según estudiante -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plan de Estudios *</label>
                    <select name="id_plan_estudio" id="id_plan_estudio" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <!-- Opciones por JS según programa -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semestre *</label>
                    <select name="id_semestre" id="id_semestre" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <!-- Opciones por JS o backend -->
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Turno *</label>
                    <select name="turno" id="turno" class="form-control" required>
                        <option value="">Seleccione</option>
                        <option value="M">Mañana</option>
                        <option value="T">Tarde</option>
                        <option value="N">Noche</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Sección *</label>
                    <select name="seccion" id="seccion" class="form-control" required>
                        <option value="">Seleccione</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>
            <!-- UNIDADES DIDÁCTICAS PROGRAMADAS PARA MATRICULAR -->
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label class="form-label">Unidades Didácticas Programadas (seleccione para matricular)</label>
                    <div id="ud-programadas-list" class="row">
                        <!-- JS llenará la lista de UDs disponibles, agrupadas por semestre -->
                    </div>
                    <div class="small text-muted">Puede seleccionar UDs de diferentes semestres. Lo ya seleccionado se conserva al cambiar de semestre.</div>
                </div>
                <div class="mt-3 col-md-6">
                    <h5>Unidades Didácticas Seleccionadas</h5>
                    <div id="uds-seleccionadas-panel" class="row g-1"></div>
                    <div id="uds-seleccionadas-hidden"></div> <!-- aquí van los inputs hidden para el submit final -->
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-success px-4">Registrar Matrícula</button>
                <a href="<?= BASE_URL ?>/academico/matricula" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // ====================
        // JS de búsqueda de estudiante y carga dinámica de combos
        // ====================
        let estudianteEncontrado = false;
        let datosEstudiante = {};

        document.getElementById('buscar-estudiante').onclick = function() {
            const dni = document.getElementById('dni-estudiante').value.trim();
            if (!dni) {
                alert('Ingrese DNI.');
                return;
            }
            // AJAX para buscar estudiante por DNI
            fetch('<?= BASE_URL ?>/academico/matricula/buscarEstudiante?dni=' + dni)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        estudianteEncontrado = false;
                        document.getElementById('apellidos-nombres').value = '';
                        // Vaciar selects
                        document.getElementById('id_programa_estudios').innerHTML = '<option value="">Seleccione...</option>';
                        document.getElementById('id_plan_estudio').innerHTML = '<option value="">Seleccione...</option>';
                        return;
                    }
                    estudianteEncontrado = true;
                    datosEstudiante = data;
                    document.getElementById('apellidos-nombres').value = data.apellidos_nombres;
                    // Llenar programas de estudios
                    let progSel = document.getElementById('id_programa_estudios');
                    progSel.innerHTML = '<option value="">Seleccione...</option>';
                    data.programas.forEach(p => {
                        progSel.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                    });
                });
        };

        // Al seleccionar programa, cargar planes
        document.getElementById('id_programa_estudios').onchange = function() {
            let idPrograma = this.value;
            let planes = datosEstudiante.planes.filter(pl => pl.id_programa_estudios == idPrograma);
            let selPlan = document.getElementById('id_plan_estudio');
            selPlan.innerHTML = '<option value="">Seleccione...</option>';
            planes.forEach(pl => {
                selPlan.innerHTML += `<option value="${pl.id}">${pl.nombre}</option>`;
            });
        };

        document.getElementById('id_plan_estudio').onchange = function() {
            udsSeleccionadas.clear();
            actualizarHiddenUDs();
            actualizarPanelUDs();
            cargarUDProgramadas();

            let idPlan = this.value;
            // ...cargar UDs programadas (ya tienes)
            // Cargar semestres:
            fetch('<?= BASE_URL ?>/sigi/semestre/porPlan/' + idPlan)
                .then(res => res.json())
                .then(data => {
                    let selSemestre = document.getElementById('id_semestre');
                    selSemestre.innerHTML = '<option value="">Seleccione...</option>';
                    data.forEach(s => {
                        selSemestre.innerHTML += `<option value="${s.id}">${s.descripcion}</option>`;
                    });
                });
        };

        // Al seleccionar plan o semestre, cargar UDs programadas
        document.getElementById('id_semestre').onchange = cargarUDProgramadas;

        let udsSeleccionadas = new Map(); // key: id_ud, value: {nombre, docente, semestre, turno, seccion}

        function cargarUDProgramadas() {
            let idPlan = document.getElementById('id_plan_estudio').value;
            let idSemestre = document.getElementById('id_semestre').value;
            let turno = document.getElementById('turno').value;
            let seccion = document.getElementById('seccion').value;
            if (!idPlan || !idSemestre || !turno || !seccion) return;

            fetch('<?= BASE_URL ?>/academico/matricula/udsProgramadas?plan=' + idPlan + '&semestre=' + idSemestre + '&turno=' + turno + '&seccion=' + seccion)
                .then(res => res.json())
                .then(data => {
                    let cont = document.getElementById('ud-programadas-list');
                    cont.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(ud => {
                            let checked = udsSeleccionadas.has(String(ud.id)) ? 'checked' : '';
                            cont.innerHTML += `
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ud_temp" value="${ud.id}" id="ud_${ud.id}" ${checked}>
                                <label class="form-check-label" for="ud_${ud.id}">
                                    <b>${ud.nombre}</b> | Docente: ${ud.docente}
                                </label>
                            </div>
                        </div>
                    `;
                        });

                        // Vincula el checkbox al control global y panel visual
                        cont.querySelectorAll('input[type="checkbox"]').forEach(chk => {
                            chk.addEventListener('change', function() {
                                // Encuentra el objeto UD según el ID
                                let objUD = data.find(ud => String(ud.id) === String(this.value));
                                if (this.checked) {
                                    udsSeleccionadas.set(String(this.value), {
                                        nombre: objUD.nombre,
                                        docente: objUD.docente,
                                        semestre: objUD.semestre ?? '',
                                        turno: objUD.turno,
                                        seccion: objUD.seccion
                                    });
                                } else {
                                    udsSeleccionadas.delete(String(this.value));
                                }
                                actualizarHiddenUDs();
                                actualizarPanelUDs();
                            });
                        });
                    } else {
                        cont.innerHTML = '<div class="col-md-12 text-danger">No hay unidades didácticas programadas para esta configuración.</div>';
                    }
                    actualizarHiddenUDs();
                    actualizarPanelUDs();
                });
        }

        function actualizarHiddenUDs() {
            let hiddenCont = document.getElementById('uds-seleccionadas-hidden');
            hiddenCont.innerHTML = '';
            udsSeleccionadas.forEach(function(_, key) {
                hiddenCont.innerHTML += `<input type="hidden" name="ud_programadas[]" value="${key}">`;
            });
        }

        function actualizarPanelUDs() {
            let panel = document.getElementById('uds-seleccionadas-panel');
            panel.innerHTML = '';
            if (udsSeleccionadas.size === 0) {
                panel.innerHTML = '<div class="text-muted">No hay UDs seleccionadas.</div>';
                return;
            }
            udsSeleccionadas.forEach((ud, key) => {
                panel.innerHTML += `
            <div class="col-md-12 mb-1" id="panel-ud-${key}">
                <div class="alert alert-info py-1 d-flex align-items-center justify-content-between mb-1" style="font-size:0.97em;">
                    <span>
                        <b>${ud.nombre}</b>
                        <span class="text-secondary"> | Docente: ${ud.docente} | Semestre: ${ud.semestre} </span>
                    </span>
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="deseleccionarUD('${key}')">
                        <i class="fa fa-times"></i> Quitar
                    </button>
                </div>
            </div>
        `;
            });
        }

        window.deseleccionarUD = function(idUD) {
            udsSeleccionadas.delete(String(idUD));
            actualizarHiddenUDs();
            actualizarPanelUDs();
            // Si el check está visible en la lista actual, desmarcarlo
            let chk = document.getElementById('ud_' + idUD);
            if (chk) chk.checked = false;
        }

        // Limpiar selección global si cambian plan/turno/sección
        document.getElementById('turno').onchange = function() {
            udsSeleccionadas.clear();
            actualizarHiddenUDs();
            actualizarPanelUDs();
            cargarUDProgramadas();
        };
        document.getElementById('seccion').onchange = function() {
            udsSeleccionadas.clear();
            actualizarHiddenUDs();
            actualizarPanelUDs();
            cargarUDProgramadas();
        };
        // Cambiar semestre no borra, solo recarga la lista visible

        // Inicializa el panel vacío al cargar
        actualizarPanelUDs();
    </script>
<?php else: ?>
    <div class="alert alert-danger mt-4">
        <b>Acceso denegado:</b> Solo el administrador académico puede registrar matrículas.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>