<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <!-- Tarjeta de Información del Docente y Unidad Didáctica -->
        <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #007bff !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <span class="badge badge-primary px-3 py-1 font-size-12 mb-2">Evaluación del Desempeño Docente</span>
                        <h3 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-chalkboard-teacher text-primary mr-2"></i> <?= htmlspecialchars($evaluacion['docente_nombre']) ?>
                        </h3>
                        <p class="text-muted mb-0 font-size-15">
                            <strong>Unidad Didáctica:</strong> <?= htmlspecialchars($evaluacion['unidad_didactica_nombre']) ?>
                            <span class="text-muted font-size-13">(Semestre <?= htmlspecialchars($evaluacion['semestre_romano']) ?> - Sec. <?= htmlspecialchars($evaluacion['seccion']) ?>)</span>
                        </p>
                        <p class="text-muted small mb-0 font-size-13">
                            <strong>Programa de Estudios:</strong> <?= htmlspecialchars($evaluacion['programa_nombre']) ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0 text-md-right">
                        <span class="badge badge-soft-warning p-2 text-dark font-weight-bold font-size-12 d-block mb-1">
                            <i class="fas fa-clock mr-1"></i> Estado: Pendiente
                        </span>
                        <small class="text-muted">Cierre: <?= date('d/m/Y', strtotime($evaluacion['encuesta_fecha_fin'])) ?></small>
                    </div>
                </div>

                <hr class="my-3">

                <!-- Escala de Valoración -->
                <div class="bg-light p-3 rounded border">
                    <h6 class="font-weight-bold text-dark mb-2 small text-uppercase">
                        <i class="fas fa-info-circle text-info mr-1"></i> Escala de Valoración:
                    </h6>
                    <div class="row text-center font-size-12">
                        <div class="col"><span class="badge badge-danger p-2 d-block">1. Deficiente</span></div>
                        <div class="col"><span class="badge badge-warning p-2 d-block text-dark">2. Regular</span></div>
                        <div class="col"><span class="badge badge-info p-2 d-block">3. Bueno</span></div>
                        <div class="col"><span class="badge badge-primary p-2 d-block">4. Muy Bueno</span></div>
                        <div class="col"><span class="badge badge-success p-2 d-block">5. Excelente</span></div>
                    </div>
                    <small class="text-muted d-block mt-2 font-italic text-center">
                        Seleccione de 1 a 5 según el nivel de desempeño que mejor describa la labor del docente en cada aspecto.
                    </small>
                </div>
            </div>
        </div>

        <!-- Formulario de Evaluación -->
        <form action="<?= BASE_URL ?>/evaluacion/realizar/guardar" method="POST" id="formEvaluacionDocente">
            <input type="hidden" name="id_evaluacion" value="<?= $evaluacion['id'] ?>">

            <?php $numeroPregunta = 1; ?>
            <?php foreach ($criterios as $crit): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 font-weight-bold text-primary">
                            <i class="fas fa-layer-group mr-2"></i> <?= htmlspecialchars($crit['nombre']) ?>
                        </h5>
                        <?php if (!empty($crit['descripcion'])): ?>
                            <small class="text-muted"><?= htmlspecialchars($crit['descripcion']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($crit['preguntas'] as $preg): ?>
                                <li class="list-group-item p-4">
                                    <div class="mb-3">
                                        <span class="badge badge-secondary mr-2">Pregunta <?= $numeroPregunta++ ?></span>
                                        <span class="font-weight-bold text-dark font-size-15">
                                            <?= htmlspecialchars($preg['enunciado']) ?>
                                        </span>
                                    </div>

                                    <?php if ($preg['tipo_respuesta'] === 'ESCALA'): ?>
                                        <!-- Opciones en Escala Likert de 1 a 5 -->
                                        <div class="d-flex flex-wrap justify-content-between align-items-center rating-container pt-2">
                                            <?php
                                            $etiquetas = [
                                                1 => 'Deficiente',
                                                2 => 'Regular',
                                                3 => 'Bueno',
                                                4 => 'Muy Bueno',
                                                5 => 'Excelente'
                                            ];
                                            for ($i = 1; $i <= 5; $i++):
                                            ?>
                                                <label class="rating-box text-center mb-2 px-3 py-2 rounded border flex-fill mx-1 cursor-pointer transition-all">
                                                    <input type="radio"
                                                        name="respuestas[<?= $preg['id'] ?>]"
                                                        value="<?= $i ?>"
                                                        required
                                                        class="d-none radio-eval">
                                                    <span class="d-block font-weight-bold font-size-18 rating-num"><?= $i ?></span>
                                                    <span class="d-block font-size-11 rating-lbl text-muted"><?= $etiquetas[$i] ?></span>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    <?php else: ?>
                                        <textarea name="respuestas[<?= $preg['id'] ?>]"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Escriba su respuesta..."></textarea>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Comentario Opcional / Sugerencia -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-comment-dots mr-2 text-primary"></i> Comentarios y Sugerencias de Mejora (Opcional)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Puedes dejar una sugerencia constructiva para el docente. Tus comentarios son confidenciales y están orientados a la mejora continua de la enseñanza.
                    </p>
                    <textarea name="comentario_general" class="form-control" rows="3" placeholder="Escribe aquí algún comentario o recomendación constructiva..."></textarea>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/evaluacion" class="btn btn-outline-secondary px-4 mb-2 mb-md-0 font-weight-semibold">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Evaluaciones
                    </a>
                    <button type="button" id="btnEnviarEvaluacion" class="btn btn-success btn-lg px-5 font-weight-bold shadow">
                        <i class="fas fa-check-circle mr-2"></i> Finalizar y Enviar Evaluación
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .rating-box {
        background-color: #fcfcfc;
        transition: all 0.2s ease-in-out;
    }

    .rating-box:hover {
        background-color: #e9ecef;
        border-color: #007bff !important;
    }

    .rating-box.selected {
        background-color: #007bff !important;
        border-color: #0056b3 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    }

    .rating-box.selected .rating-num,
    .rating-box.selected .rating-lbl {
        color: #ffffff !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejo visual de selección en la escala Likert
        const radioBoxes = document.querySelectorAll('.rating-box');
        radioBoxes.forEach(box => {
            box.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;

                // Deseleccionar hermanos
                const container = this.closest('.rating-container');
                container.querySelectorAll('.rating-box').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Validación antes de enviar con SweetAlert2
        const btnEnviar = document.getElementById('btnEnviarEvaluacion');
        const form = document.getElementById('formEvaluacionDocente');

        btnEnviar.addEventListener('click', function() {
            // Verificar que todos los radios requeridos estén marcados
            const radioGroups = {};
            const allRadios = form.querySelectorAll('.radio-eval');
            allRadios.forEach(r => {
                radioGroups[r.name] = radioGroups[r.name] || false;
                if (r.checked) radioGroups[r.name] = true;
            });

            let faltantes = 0;
            for (const name in radioGroups) {
                if (!radioGroups[name]) {
                    faltantes++;
                }
            }

            if (faltantes > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Preguntas Incompletas',
                    text: 'Aún tienes ' + faltantes + ' pregunta(s) sin responder en la escala de valoración. Por favor responde todas para enviar.',
                    confirmButtonColor: '#007bff'
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar Envío Definitivo?',
                html: 'Una vez enviada tu evaluación, <strong>no podrás modificar tus respuestas</strong>. Se enviará una constancia a tu correo personal registrado.<br><br>¿Deseas enviar tu evaluación ahora?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, Enviar Evaluación',
                cancelButtonText: 'Revisar respuestas'
            }).then((result) => {
                if (result.isConfirmed) {
                    btnEnviar.disabled = true;
                    btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registrando evaluación...';
                    form.submit();
                }
            });
        });
    });
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>