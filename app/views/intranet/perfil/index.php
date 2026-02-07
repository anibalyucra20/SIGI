<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::user()): ?>
    <?php
    // En "ver" normalmente es false
    $isEdit = $isEdit ?? false;

    // Asegura que existan (por si la vista se usa en otro lado)
    $usuario   = $usuario   ?? [];
    $roles     = $roles     ?? [];
    $sedes     = $sedes     ?? [];
    $programas = $programas ?? [];
    $permisos  = $permisos  ?? [];
    ?>

    <div class="container mt-4">
        <h3 class="mb-4">Detalles del Docente</h3>

        <form id="form-ver-docente" class="card p-4 shadow-sm rounded-3" autocomplete="off">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id'] ?? '') ?>">

            <!-- ======= FORMULARIO MISMO ESTILO QUE EDITAR/NUEVO ======= -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo Documento</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['tipo_doc'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Número</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['dni'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Apellido Paterno</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['ApellidoPaterno'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Apellido Materno</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['ApellidoMaterno'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Nombres</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['Nombres'] ?? '') ?></label>
                    </div>
                </div>
                <?php if ($usuario['tipo_usuario'] == 'docente') {
                ?>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Grado Académico</label>
                        <!--<input type="text" name="grado_academico" class="form-control"
                            maxlength="120"
                            value="<?= htmlspecialchars($usuario['grado_academico'] ?? '') ?>">-->
                        <div class="input-group">
                            <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['grado_academico'] ?? '') ?></label>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Género</label>

                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= (isset($usuario) && ($usuario['genero'] ?? '') == 'M') ? 'Masculino' : 'Femenino' ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['fecha_nacimiento'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Teléfono</label>
                    <!--<input type="text" name="telefono" class="form-control"
                        maxlength="15"
                        value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">-->
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['telefono'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <!--<input type="email" name="correo" class="form-control"
                        maxlength="120"
                        value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>-->
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['correo'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-5 mb-3">
                    <label class="form-label">Dirección</label>
                    <!--<input type="text" name="direccion" class="form-control"
                        maxlength="150"
                        value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">-->
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['direccion'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Discapacidad</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['discapacidad'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Sede</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['nombre_sede'] ?? '') ?></label>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Programa de Estudio</label>
                    <div class="input-group">
                        <label class="form-label" class="form-control"><?= htmlspecialchars($usuario['nombre_programa'] ?? '') ?></label>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control" required>
                            <option value="1" <?= ((int)($usuario['estado'] ?? 1) === 1) ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ((int)($usuario['estado'] ?? 1) === 0) ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            <!-- ======= /FORMULARIO ======= -->

            <div class="mt-3 d-flex justify-content-end gap-2">
                <!--<button type="button" class="btn btn-primary d-none m-1" id="btn-guardar-ver">Guardar</button>
                <button type="button" class="btn btn-secondary d-none m-1" id="btn-cancelar-ver">Cancelar</button>
                <button type="button" class="btn btn-warning m-1" id="btn-editar-ver">Editar</button>-->
                <a href="<?= BASE_URL ?>/intranet" class="btn btn-outline-secondary m-1">Ir a Panel</a>
            </div>
        </form>

        <div class="mt-4">
            <h4 class="mb-3">Permisos del Usuario</h4>

            <?php if (!empty($permisos)): ?>
                <div class="card p-3 shadow-sm rounded-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sistema</th>
                                    <th>Rol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permisos as $perm): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($perm['sistema'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($perm['rol'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary mb-0">Este usuario no tiene permisos asignados.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-ver-docente');
            const btnEditar = document.getElementById('btn-editar-ver');
            const btnGuardar = document.getElementById('btn-guardar-ver');
            const btnCancelar = document.getElementById('btn-cancelar-ver');

            // Todos los inputs/select/textarea visibles (excepto hidden)
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');

            function setEditable(editable) {
                inputs.forEach(el => {
                    if (editable) {
                        el.removeAttribute('readonly');
                        el.removeAttribute('disabled');
                    } else {
                        // Los selects deben ir disabled; inputs/textarea readonly
                        if (el.tagName === 'SELECT') el.setAttribute('disabled', true);
                        else el.setAttribute('readonly', true);
                    }
                });

                btnGuardar.classList.toggle('d-none', !editable);
                btnCancelar.classList.toggle('d-none', !editable);
                btnEditar.classList.toggle('d-none', editable);
            }

            btnEditar.addEventListener('click', () => setEditable(true));
            btnCancelar.addEventListener('click', () => {
                setEditable(false);
                form.reset();
            });

            // En la vista "ver" NO guardamos (evita submits accidentales)
            btnGuardar.addEventListener('click', () => {
                // Si luego quieres guardar de verdad, aquí cambiamos a submit con action=...
                setEditable(false);
            });

            // Inicial: bloqueado
            setEditable(false);
        });
    </script>

<?php else: ?>
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>