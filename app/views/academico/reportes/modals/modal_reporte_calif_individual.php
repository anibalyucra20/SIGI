<!-- Modal para Nómina de Matrícula -->
<div class="modal fade" id="rep_calif_individual" tabindex="-1" role="dialog" aria-labelledby="repNominaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repNominaLabel">Busqueda de Estudiantes para Reporte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formFiltroIndiv">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="programa_reporte_individual">Programa de Estudios</label>
                        <select name="programa" id="programa_reporte_individual" class="form-control" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($programas as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plan_reporte_individual">Plan de Estudios</label>
                        <select name="plan" id="plan_reporte_individual" class="form-control" required>
                            <option value="">Seleccione primero un programa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semestre_reporte_individual">Periodo Académico</label>
                        <select name="semestre" id="semestre_reporte_individual" class="form-control" required>
                            <option value="">Seleccione primero un plan de estudios</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="turno_reporte_individual">Turno</label>
                        <select name="turno" id="turno_reporte_individual" class="form-control" required>
                            <option value="M">Mañana</option>
                            <option value="T">Tarde</option>
                            <option value="N">Noche</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="seccion_reporte_individual">Sección</label>
                        <select name="seccion" id="seccion_reporte_individual" class="form-control" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="listar_est_rep_individual();">Filtrar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
                <div class="container">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>DNI</th>
                                <th>Apellidos y Nombres</th>
                                <th>Programa</th>
                                <th>Plan</th>
                                <th>Sem.</th>
                                <th>Turno</th>
                                <th>Sec.</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tbody_rep_individual">
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('programa_reporte_individual').addEventListener('change', function() {
        const idPrograma = this.value;
        const planSelect = document.getElementById('plan_reporte_individual');
        planSelect.innerHTML = '<option value="">Cargando...</option>';
        const semestreSelect = document.getElementById('semestre_reporte_individual');
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
        listar_est_rep_individual();
    });
</script>
<script>
    document.getElementById('plan_reporte_individual').addEventListener('change', function() {
        const idPlan = this.value;
        const semestreSelect = document.getElementById('semestre_reporte_individual');
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
        listar_est_rep_individual();
    });
</script>
<script>
    document.getElementById('semestre_reporte_individual').addEventListener('change', function() {
        listar_est_rep_individual();
    });
</script>
<script>
    document.getElementById('turno_reporte_individual').addEventListener('change', function() {
        listar_est_rep_individual();
    });
</script>
<script>
    document.getElementById('seccion_reporte_individual').addEventListener('change', function() {
        listar_est_rep_individual();
    });
</script>
<script>
    function listar_est_rep_individual() {
        const form = document.getElementById('formFiltroIndiv');
        const data = new FormData(form);
        const body = new URLSearchParams(data); // envia x-www-form-urlencoded

        const tbody = document.getElementById('tbody_rep_individual');
        tbody.innerHTML =
            `<tr><td colspan="9" class="text-center">Cargando…</td></tr>`;

        fetch('<?= BASE_URL ?>/academico/reportes/buscarMatriculas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            })
            .then(r => r.json())
            .then(lista => {
                if (lista.error) {
                    tbody.innerHTML =
                        `<tr><td colspan="9" class="text-danger text-center">${lista.error}</td></tr>`;
                    return;
                }
                if (!lista.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="9" class="text-center">Sin resultados</td></tr>';
                    return;
                }
                let i = 1,
                    rows = '';
                lista.forEach(m => {
                    rows += `<tr>
            <td>${i++}</td>
            <td>${m.dni}</td>
            <td>${m.apellidos_nombres}</td>
            <td>${m.programa}</td>
            <td>${m.plan}</td>
            <td>${m.semestre}</td>
            <td>${m.turno}</td>
            <td>${m.seccion}</td>
            <td>
              <a class="btn btn-info btn-sm"
                 href="<?= BASE_URL ?>/academico/reportes/estudianteDetalle/${m.id_matricula}"
                 target="_blank">
                 Ver reporte
              </a>
            </td>
          </tr>`;
                });
                tbody.innerHTML = rows;
            })
            .catch(() => {
                tbody.innerHTML =
                    '<tr><td colspan="9" class="text-danger text-center">Error de servidor</td></tr>';
            });
    }
</script>
<script>
    listar_est_rep_individual();
</script>