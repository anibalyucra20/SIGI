<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminAcademico()): ?>
    <div class="card p-4 shadow-sm rounded-3">
        <h4 class="mb-3 mt-2 text-center">Agregar Unidad Didáctica <br> <small>Matricula de <?= htmlspecialchars($estudiante['apellidos_nombres']) ?></small></h4>
        <form action="<?= BASE_URL ?>/academico/matricula/guardarUnidadDidactica/<?= $matricula['id'] ?>" method="post" autocomplete="off">
            <!-- Mostrar datos fijos de la matrícula -->
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label>Programa de Estudios</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($matricula['programa']) ?>" readonly>
                </div>
                <div class="col-md-4 mb-2">
                    <label>Plan de Estudios</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($matricula['plan']) ?>" readonly>
                </div>
                <div class="col-md-4 mb-2">
                    <label>Turno</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($matricula['turno']) ?>" readonly>
                </div>
                <div class="col-md-4 mb-2">
                    <label>Sección</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($matricula['seccion']) ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label>Semestre *</label>
                    <select name="id_semestre" id="id_semestre" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($semestresDisponibles as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($s['id'] == $semestreActual ? 'selected' : '') ?>>
                                <?= htmlspecialchars($s['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-3 mb-2">
                <label><b>Unidades Didácticas Programadas</b> <small>(marque para agregar)</small></label>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Unidad Didáctica</th>
                                <th>Módulo</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-unidades">
                            <?php foreach ($unidadesDisponibles as $ud): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="unidades[]" value="<?= $ud['id_programacion_ud'] ?>">
                                    </td>
                                    <td><?= htmlspecialchars($ud['unidad_didactica']) ?></td>
                                    <td><?= htmlspecialchars($ud['modulo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($unidadesDisponibles)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-danger">No hay unidades didácticas disponibles para este semestre.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-success px-4">Agregar Seleccionadas</button>
                <a href="<?= BASE_URL ?>/academico/matricula/ver/<?= $matricula['id'] ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // AJAX para cargar unidades al cambiar semestre (si lo deseas dinámico)
        document.getElementById('id_semestre').addEventListener('change', function() {
            let idSemestre = this.value;
            let url = '<?= BASE_URL ?>/academico/matricula/unidadesDisponiblesAjax/<?= $matricula['id'] ?>/' + idSemestre;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let tbody = document.getElementById('tbody-unidades');
                    tbody.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(function(ud) {
                            tbody.innerHTML += `<tr>
                            <td><input type="checkbox" name="unidades[]" value="${ud.id_programacion_ud}"></td>
                            <td>${ud.unidad_didactica}</td>
                            <td>${ud.modulo}</td>
                        </tr>`;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">No hay unidades didácticas disponibles para este semestre.</td></tr>';
                    }
                });
        });
    </script>
<?php else: ?>
    <div class="alert alert-danger mt-4">
        <b>Acceso denegado:</b> Solo el administrador académico puede registrar matrículas.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>