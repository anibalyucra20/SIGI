<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row mb-3">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Asignar Estudiante a Tutor</h4>
            <div class="page-title-right">
                <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/<?= $programacion['id'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a Tutorandos
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Datos del Docente Tutor -->
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1"><strong>Docente Tutor:</strong> <?= htmlspecialchars($programacion['docente_formato']) ?></p>
                <p class="mb-0 text-muted"><strong>DNI:</strong> <?= htmlspecialchars($programacion['docente_dni']) ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Sede:</strong> <?= htmlspecialchars($programacion['sede_nombre']) ?></p>
                <p class="mb-0 text-muted"><strong>Periodo:</strong> <?= htmlspecialchars($programacion['periodo_nombre']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Formulario de Asignación -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Seleccionar y Asignar Estudiante</h5>

        <form action="<?= BASE_URL ?>/tutoria/programacion/guardarTutorando" method="post" autocomplete="off">
            <input type="hidden" name="id_programacion_tutoria" value="<?= $programacion['id'] ?>">

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="filtro_programa">Filtrar por Programa de Estudios:</label>
                    <select id="filtro_programa" class="form-control form-control-sm">
                        <option value="">-- Todos los Programas --</option>
                        <?php foreach ($programas as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="buscar_estudiante">Buscar por DNI o Apellidos:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="buscar_estudiante" class="form-control" placeholder="Ej. 70123456 o Quispe...">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="button" id="btn_buscar_est">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-2">
                <label for="select_estudiante">Estudiante Disponible: <span class="text-danger">*</span></label>
                <select name="id_estudiante" id="select_estudiante" class="form-control" required size="8">
                    <option value="" disabled selected>-- Cargando estudiantes disponibles... --</option>
                </select>
                <small id="estudiante_contador" class="text-muted d-block mt-1">Cargando...</small>
            </div>

            <div class="form-group">
                <label for="observacion">Observación Inicial / Diagnóstico Previo (Opcional):</label>
                <textarea name="observacion" id="observacion" class="form-control" rows="3" placeholder="Motivo de asignación, antecedentes o condición especial..."></textarea>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-success px-4" id="btn_submit_asignar" disabled>
                    <i class="fas fa-check mr-1"></i> Asignar a este Tutor
                </button>
                <a href="<?= BASE_URL ?>/tutoria/programacion/tutorandos/<?= $programacion['id'] ?>" class="btn btn-secondary ml-2">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function cargarEstudiantesDisponibles() {
        const progId = $('#filtro_programa').val();
        const query  = $('#buscar_estudiante').val();
        const idProg = '<?= $programacion['id'] ?>';

        const $select = $('#select_estudiante');
        const $btnSubmit = $('#btn_submit_asignar');
        $btnSubmit.prop('disabled', true);
        $select.html('<option value="" disabled selected>Cargando estudiantes disponibles...</option>');

        $.ajax({
            url: '<?= BASE_URL ?>/tutoria/programacion/buscarEstudiantesAjax',
            type: 'GET',
            data: {
                id_programacion: idProg,
                id_programa: progId,
                q: query
            },
            dataType: 'json',
            success: function(data) {
                $select.empty();
                if (!data || data.length === 0) {
                    $select.append('<option value="" disabled selected>No se encontraron estudiantes disponibles</option>');
                    $('#estudiante_contador').text('0 estudiantes disponibles.');
                } else {
                    data.forEach(function(item) {
                        $select.append(
                            `<option value="${item.id}">${item.nombre_completo}</option>`
                        );
                    });
                    $('#estudiante_contador').text(`${data.length} estudiantes disponibles para asignar.`);
                }
            },
            error: function() {
                $select.html('<option value="" disabled selected>Error al cargar estudiantes</option>');
                $('#estudiante_contador').text('Error de conexión al cargar estudiantes.');
            }
        });
    }

    $('#select_estudiante').on('change', function() {
        $('#btn_submit_asignar').prop('disabled', !$(this).val());
    });

    $('#btn_buscar_est').on('click', cargarEstudiantesDisponibles);
    $('#buscar_estudiante').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            cargarEstudiantesDisponibles();
        }
    });
    $('#buscar_estudiante').on('input', function() {
        clearTimeout(window.searchEstTimer);
        window.searchEstTimer = setTimeout(cargarEstudiantesDisponibles, 350);
    });
    $('#filtro_programa').on('change', cargarEstudiantesDisponibles);

    cargarEstudiantesDisponibles();
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

