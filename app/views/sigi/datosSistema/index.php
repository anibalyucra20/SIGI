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
        <h3 class="mb-4">Datos del Sistema</h3>
        <form id="form-datos-sistema" action="<?= BASE_URL ?>/sigi/datosSistema/guardar" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm rounded-3" autocomplete="off" >
            <input type="hidden" name="id" value="<?= htmlspecialchars($sistema['id'] ?? '') ?>">

            <div class="mb-3">
                <label class="form-label">Dominio Página *</label>
                <input type="url" name="dominio_pagina" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($sistema['dominio_pagina'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Favicon *</label>
                <?php if ($sistema['favicon'] != ''): ?>
                    <div>
                        <img src="<?= BASE_URL ?>/images/<?= htmlspecialchars($sistema['favicon']) ?>" alt="Favicon" style="height:32px;">
                    </div>
                <?php else: ?>
                    <div>
                        <img src="<?= BASE_URL ?>/img/favicon.ico" alt="Favicon" style="height:32px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="favicon_file" class="form-control d-none" maxlength="100" 
                    value="<?= htmlspecialchars($sistema['favicon'] ?? '') ?>" readonly accept="image/x-icon">
                <input type="hidden" name="favicon" value="<?= htmlspecialchars($sistema['favicon'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Logo *</label>
                <?php if ($sistema['logo'] != ''): ?>
                    <div>
                        <img src="<?= BASE_URL ?>/images/<?= htmlspecialchars($sistema['logo']) ?>" alt="logo" style="height:62px;">
                    </div>
                <?php else: ?>
                    <div>
                        <img src="<?= BASE_URL ?>/img/logo_completo.png" alt="logo" style="height:62px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="logo_file" class="form-control d-none" maxlength="100" 
                    value="<?= htmlspecialchars($sistema['logo'] ?? '') ?>" readonly accept="image/png,image/jpeg,image/svg+xml">
                <input type="hidden" name="logo" value="<?= htmlspecialchars($sistema['logo'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre Completo *</label>
                <input type="text" name="nombre_completo" class="form-control" maxlength="200" required
                    value="<?= htmlspecialchars($sistema['nombre_completo'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre Corto *</label>
                <input type="text" name="nombre_corto" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($sistema['nombre_corto'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Pie de Página *</label>
                <input type="text" name="pie_pagina" class="form-control" maxlength="300" required
                    value="<?= htmlspecialchars($sistema['pie_pagina'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Host Mail *</label>
                <input type="text" name="host_mail" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($sistema['host_mail'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Email de Sistema *</label>
                <input type="email" name="email_email" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($sistema['email_email'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña Email *</label>
                <input type="text" name="password_email" class="form-control" maxlength="100" required
                    value="<?= htmlspecialchars($sistema['password_email'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Puerto Email *</label>
                <input type="text" name="puerto_email" class="form-control" maxlength="10" required
                    value="<?= htmlspecialchars($sistema['puerto_email'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Color Institucional *</label>
                <input type="color" name="color_correo" class="form-control" maxlength="20" required
                    value="<?= htmlspecialchars($sistema['color_correo'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Cantidad de Semanas *</label>
                <input type="number" name="cant_semanas" class="form-control" min="1" max="26" required
                    value="<?= htmlspecialchars($sistema['cant_semanas'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Calificacion si estudiante sobrepasa 30% de inasistencia *</label>
                <input type="number" name="nota_inasistencia" class="form-control" min="0" max="20" required
                    value="<?= htmlspecialchars($sistema['nota_inasistencia'] ?? '') ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Token Sistema *</label>
                <textarea name="token_sistema" class="form-control" maxlength="1000" required readonly><?= htmlspecialchars($sistema['token_sistema'] ?? '') ?></textarea>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary px-4 d-none" id="btn-guardar-ds">Guardar</button>
                <button type="button" class="btn btn-secondary d-none" id="btn-cancelar-ds">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-editar-ds">Editar</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-datos-sistema');
            const btnEditar = document.getElementById('btn-editar-ds');
            const btnGuardar = document.getElementById('btn-guardar-ds');
            const btnCancelar = document.getElementById('btn-cancelar-ds');
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');

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
                // Mostrar file inputs si editable, ocultar si no
                document.querySelectorAll('input[type="file"]').forEach(input => {
                    if (editable) input.classList.remove('d-none');
                    else input.classList.add('d-none');
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
                form.reset();
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