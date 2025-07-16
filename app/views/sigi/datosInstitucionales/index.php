<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible">
            <?= $_SESSION['flash_success'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible">
            <?= $_SESSION['flash_error'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    <div class="container mt-4">
        <h3 class="mb-4">Datos Institucionales</h3>
        <form action="<?= BASE_URL ?>/sigi/datosInstitucionales/guardar" method="post" class="card p-4 shadow-sm rounded-3" autocomplete="off" id="form-institucion">
            <input type="hidden" name="id" value="<?= htmlspecialchars($institucion['id'] ?? '') ?>">

            <div class="mb-3">
                <label class="form-label">Código Modular *</label>
                <input type="text" name="cod_modular" class="form-control" maxlength="20" required
                    value="<?= htmlspecialchars($institucion['cod_modular'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">RUC *</label>
                <input type="text" name="ruc" class="form-control" maxlength="11" pattern="\d{11}" required
                    value="<?= htmlspecialchars($institucion['ruc'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre de la institución *</label>
                <input type="text" name="nombre_institucion" class="form-control" maxlength="200" required
                    value="<?= htmlspecialchars($institucion['nombre_institucion'] ?? '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Dre a la que pertenece *</label>
                <input type="text" name="dre" class="form-control" maxlength="50" required
                    value="<?= htmlspecialchars($institucion['dre'] ?? '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Departamento *</label>
                <input type="text" name="departamento" class="form-control" maxlength="50" required
                    value="<?= htmlspecialchars($institucion['departamento'] ?? '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Provincia *</label>
                <input type="text" name="provincia" class="form-control" maxlength="50" required
                    value="<?= htmlspecialchars($institucion['provincia'] ?? '') ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Distrito *</label>
                <input type="text" name="distrito" class="form-control" maxlength="50" required
                    value="<?= htmlspecialchars($institucion['distrito'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Dirección *</label>
                <input type="text" name="direccion" class="form-control" maxlength="200" required
                    value="<?= htmlspecialchars($institucion['direccion'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono *</label>
                <input type="text" name="telefono" class="form-control" maxlength="15" pattern="\d{6,15}" required
                    value="<?= htmlspecialchars($institucion['telefono'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Correo institucional *</label>
                <input type="email" name="correo" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($institucion['correo'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">N° de Resolución *</label>
                <input type="text" name="nro_resolucion" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($institucion['nro_resolucion'] ?? '') ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado *</label>
                <select name="estado" class="form-control" required disabled>
                    <option value="1" <?= (isset($institucion['estado']) && $institucion['estado'] == 1) ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (isset($institucion['estado']) && $institucion['estado'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary px-4 d-none" id="btn-guardar">Guardar</button>
                <button type="button" class="btn btn-secondary d-none" id="btn-cancelar">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-editar">Editar</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-institucion');
            const btnEditar = document.getElementById('btn-editar');
            const btnGuardar = document.getElementById('btn-guardar');
            const btnCancelar = document.getElementById('btn-cancelar');
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select');

            function setEditable(editable) {
                inputs.forEach(input => {
                    if (editable) {
                        input.removeAttribute('readonly');
                        input.removeAttribute('disabled');
                    } else {
                        if (input.tagName === 'SELECT') {
                            input.setAttribute('disabled', true);
                        } else {
                            input.setAttribute('readonly', true);
                        }
                    }
                });
                btnGuardar.classList.toggle('d-none', !editable);
                btnCancelar.classList.toggle('d-none', !editable);
                btnEditar.classList.toggle('d-none', editable);
            }

            btnEditar.addEventListener('click', function() {
                setEditable(true);
            });
            btnCancelar.addEventListener('click', function() {
                setEditable(false);
                form.reset(); // opcional: recarga valores originales
                // Si quieres que los campos vuelvan a los valores iniciales recarga la página:
                // location.reload();
            });

            // Inicialmente: todo bloqueado
            setEditable(false);
        });
    </script>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>