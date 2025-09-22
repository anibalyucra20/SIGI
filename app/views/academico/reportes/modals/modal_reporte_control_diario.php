<!-- Modal para Nómina de Matrícula -->
<div class="modal fade" id="rep_control_diario" tabindex="-1" role="dialog" aria-labelledby="repNominaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repNominaLabel">Generar Reporte de Control Diario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/academico/reportes/pdfControlDiario" target="_blank">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="programa_reporte_control_diario">Programa de Estudios</label>
                        <select name="programa" id="programa_reporte_control_diario" class="form-control" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($programas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plan_reporte_control_diario">Plan de Estudios</label>
                        <select name="plan" id="plan_reporte_control_diario" class="form-control" required>
                            <option value="">Seleccione primero un programa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semestre_reporte_control_diario">Periodo Académico</label>
                        <select name="semestre" id="semestre_reporte_control_diario" class="form-control" required>
                            <option value="">Seleccione primero un plan de estudios</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="turno_reporte_control_diario">Turno</label>
                        <select name="turno" id="turno_reporte_control_diario" class="form-control" required>
                            <option value="M">Mañana</option>
                            <option value="T">Tarde</option>
                            <option value="N">Noche</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="seccion_reporte_control_diario">Sección</label>
                        <select name="seccion" id="seccion_reporte_control_diario" class="form-control" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fi_reporte_control_diario">Fecha de inicio (Semana 1)</label>
                        <input type="date" name="semana_inicio" id="fi_reporte_control_diario" class="form-control" required>
                        <small class="text-muted">Se generarán 16 semanas (5 días por semana) a partir de esta fecha.</small>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="debug_control_diario" name="debug" value="1">
                        <label class="form-check-label" for="debug_control_diario">Modo depuración</label>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Generar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('programa_reporte_control_diario').addEventListener('change', function() {
        const idPrograma = this.value;
        const planSelect = document.getElementById('plan_reporte_control_diario');
        planSelect.innerHTML = '<option value="">Cargando...</option>';
        const semestreSelect = document.getElementById('semestre_reporte_control_diario');
        semestreSelect.innerHTML = '<option value="">Seleccione primero un plan de estudios</option>';
        planSelect.disabled = true;
        fetch(`<?= BASE_URL ?>/sigi/planes/porPrograma/${idPrograma}`)
            .then(res => res.json())
            .then(data => {

                planSelect.innerHTML = '<option value="">Seleccione</option>';
                data.forEach(plan => {
                    planSelect.innerHTML += `<option value="${plan.id}">${plan.nombre}</option>`;
                });

            })
            .catch(() => {
                planSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
        planSelect.disabled = false;
    });
</script>
<script>
    document.getElementById('plan_reporte_control_diario').addEventListener('change', function() {
        const idPlan = this.value;
        const semestreSelect = document.getElementById('semestre_reporte_control_diario');
        semestreSelect.innerHTML = '<option value="">Cargando...</option>';
        semestreSelect.disabled = true;
        fetch(`<?= BASE_URL ?>/sigi/semestre/porPlan/${idPlan}`)
            .then(res => res.json())
            .then(data => {

                semestreSelect.innerHTML = '<option value="">Seleccione</option>';
                data.forEach(sem => {
                    semestreSelect.innerHTML += `<option value="${sem.id}">${sem.descripcion}</option>`;
                });

            })
            .catch(() => {
                semestreSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
        semestreSelect.disabled = false;
    });
</script>