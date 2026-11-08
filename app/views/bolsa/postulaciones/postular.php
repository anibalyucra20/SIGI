<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark font-weight-bold">
                <i class="fas fa-paper-plane text-success mr-2"></i>Postulación a Oferta Laboral
            </h4>
            <p class="text-muted small mb-0">Seleccione el currículum con el que desea postular a esta convocatoria.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-outline-primary btn-sm mr-1">
                <i class="fas fa-list-alt mr-1"></i> Mis Postulaciones
            </a>
            <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Ofertas
            </a>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- CASO 1: EL ESTUDIANTE YA POSTULÓ ANTERIORMENTE A LA OFERTA -->
<!-- ======================================================== -->
<?php if (!empty($postulacionPrevia)): 
    $estadoInfo = $estados[$postulacionPrevia['estado']] ?? [
        'nombre' => 'En Proceso', 
        'badge'  => 'badge-info', 
        'icono'  => 'fas fa-info-circle'
    ];
?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-check-circle text-success display-4"></i>
            </div>
            <h4 class="font-weight-bold text-dark mb-2">¡Ya te encuentras postulado a esta convocatoria!</h4>
            <p class="text-muted max-w-500 mx-auto mb-3" style="max-width: 550px; margin: 0 auto;">
                Registraste tu postulación para <strong><?= htmlspecialchars($oferta['titulo']) ?></strong> en <strong><?= htmlspecialchars($oferta['empresa_nombre'] ?? 'la empresa') ?></strong> el <strong><?= date('d/m/Y \a \l\a\s H:i', strtotime($postulacionPrevia['fecha'])) ?></strong>.
            </p>
            <div class="mb-4">
                <span class="badge <?= $estadoInfo['badge'] ?> px-3 py-2 font-size-14">
                    <i class="<?= $estadoInfo['icono'] ?> mr-1"></i> Estado actual: <?= htmlspecialchars($estadoInfo['nombre']) ?>
                </span>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary px-4 font-weight-bold shadow-sm mr-2">
                    <i class="fas fa-clipboard-list mr-1"></i> Ver Mis Postulaciones
                </a>
                <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-secondary px-4">
                    Ver Otras Ofertas
                </a>
            </div>
        </div>
    </div>

<!-- ======================================================== -->
<!-- CASO 2: LA OFERTA NO ESTÁ VIGENTE O YA CADUCÓ           -->
<!-- ======================================================== -->
<?php elseif (!$esVigente): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-calendar-times text-danger display-4"></i>
            </div>
            <h4 class="font-weight-bold text-dark mb-2">Convocatoria Fuera de Plazo</h4>
            <p class="text-muted max-w-500 mx-auto mb-4" style="max-width: 500px; margin: 0 auto;">
                La fecha límite de postulación para <strong><?= htmlspecialchars($oferta['titulo']) ?></strong> finalizó el <strong><?= date('d/m/Y', strtotime($oferta['fecha_cierre'])) ?></strong>. No se reciben nuevas postulaciones.
            </p>
            <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-primary px-4">
                <i class="fas fa-search mr-1"></i> Explorar Ofertas Vigentes
            </a>
        </div>
    </div>


<!-- ======================================================== -->
<!-- CASO 3: EL ESTUDIANTE NO TIENE NINGÚN CV REGISTRADO      -->
<!-- ======================================================== -->
<?php elseif (empty($curriculums)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-id-card text-warning display-4"></i>
            </div>
            <h4 class="font-weight-bold text-dark mb-2">Necesitas registrar tu Currículum primero</h4>
            <p class="text-muted max-w-500 mx-auto mb-4" style="max-width: 550px; margin: 0 auto;">
                Para poder postular a <strong><?= htmlspecialchars($oferta['titulo']) ?></strong>, primero debes registrar o vincular al menos un currículum con tus datos en el sistema.
            </p>
            <div>
                <a href="<?= BASE_URL ?>/bolsa/curriculum/nuevo" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Crear / Vincular mi Currículum Ahora
                </a>
            </div>
        </div>
    </div>


<!-- ======================================================== -->
<!-- CASO 4: FORMULARIO DE POSTULACIÓN HABILITADO            -->
<!-- ======================================================== -->
<?php else: ?>
    <div class="row">
        <!-- Columna Izquierda: Información de la Oferta -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge badge-success px-3 py-1 font-weight-normal">
                            <i class="fas fa-check-circle mr-1"></i> Convocatoria Vigente
                        </span>
                        <span class="text-danger small font-weight-bold">
                            <i class="fas fa-clock mr-1"></i> Cierre: <?= date('d/m/Y', strtotime($oferta['fecha_cierre'])) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <h4 class="font-weight-bold text-primary mb-1"><?= htmlspecialchars($oferta['titulo']) ?></h4>
                    <h6 class="text-muted mb-3 font-weight-bold">
                        <i class="fas fa-building text-secondary mr-1"></i> <?= htmlspecialchars($oferta['empresa_nombre'] ?? 'Empresa Confidencial') ?>
                    </h6>

                    <!-- Grid de características rápidas -->
                    <div class="row mb-3 bg-light p-3 rounded mx-0 border">
                        <div class="col-sm-6 mb-2">
                            <small class="text-muted d-block">Programa / Carrera:</small>
                            <strong class="text-dark"><?= htmlspecialchars($oferta['programa_nombre'] ?? 'Todas las carreras') ?></strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <small class="text-muted d-block">Salario Ofrecido:</small>
                            <strong class="text-success font-size-15">
                                <?= !empty($oferta['salario']) && is_numeric($oferta['salario']) ? 'S/ ' . number_format($oferta['salario'], 2) : ($oferta['salario'] ?: 'A Tratar') ?>
                            </strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <small class="text-muted d-block">Ubicación:</small>
                            <strong class="text-dark"><?= htmlspecialchars($oferta['ubicacion'] ?? 'Huanta') ?></strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <small class="text-muted d-block">Tipo de Contrato:</small>
                            <strong class="text-dark"><?= htmlspecialchars($oferta['tipo_contrato'] ?? 'Tiempo completo') ?></strong>
                        </div>
                    </div>
                    <!-- Funciones y Requisitos -->
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2">
                            <i class="fas fa-tasks text-primary mr-1"></i> Funciones del Puesto:
                        </h6>
                        <div class="text-secondary small leading-relaxed" style="text-align: justify; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($oferta['detalle'])) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2">
                            <i class="fas fa-clipboard-check text-primary mr-1"></i> Requisitos Solicitados:
                        </h6>
                        <div class="text-secondary small leading-relaxed" style="text-align: justify; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($oferta['requisitos'])) ?>
                        </div>
                    </div>
                    <?php if (!empty($oferta['foto'])): ?>
                        <div class="mt-3 p-2 bg-light border rounded text-center">
                            <small class="text-muted d-block mb-1"><i class="fas fa-image mr-1"></i> Afiche oficial de la convocatoria:</small>
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($oferta['foto']) ?>" target="_blank">
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($oferta['foto']) ?>" alt="Afiche" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Tarjeta de Postulación -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 font-weight-bold text-white">
                        <i class="fas fa-user-edit mr-2"></i>Ficha de Postulación
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Resumen del Postulante -->
                    <div class="p-3 bg-light rounded border mb-4">
                        <small class="text-muted d-block">Postulante:</small>
                        <strong class="text-dark font-size-15 d-block mb-1">
                            <?= htmlspecialchars($usuario['nombre_completo'] ?? $_SESSION['sigi_user_name'] ?? 'Usuario') ?>
                        </strong>
                        <div class="small text-muted">
                            <div><i class="fas fa-id-card mr-1"></i> DNI: <?= htmlspecialchars($usuario['dni'] ?? '-') ?></div>
                            <div><i class="fas fa-envelope mr-1"></i> <?= htmlspecialchars($usuario['correo'] ?? '-') ?></div>
                            <div><i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($usuario['programa_estudios'] ?? 'Educación Superior') ?></div>
                        </div>
                    </div>

                    <!-- Formulario de Envío de Postulación -->
                    <form action="<?= BASE_URL ?>/bolsa/postulaciones/guardar" method="post" id="form-postular">
                        <input type="hidden" name="id_oferta_laboral" value="<?= (int)$idOferta ?>">
                        <!-- Selección del Currículum -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">
                                Seleccione su Currículum <span class="text-danger">*</span>
                            </label>
                            <select name="id_curriculum" id="select-cv" class="form-control" required>
                                <option value="">Elija un currículum</option>
                                <?php foreach ($curriculums as $cv): 
                                    $tipoTxt = !empty($cv['archivo']) ? ' (Con enlace web)' : ' (En sistema)';
                                    $resumen = mb_strlen($cv['descripcion']) > 50 ? mb_substr($cv['descripcion'], 0, 50) . '…' : $cv['descripcion'];
                                ?>
                                    <option value="<?= $cv['id'] ?>" data-archivo="<?= htmlspecialchars($cv['archivo'] ?? '') ?>">
                                        <?= htmlspecialchars($resumen) ?><?= $tipoTxt ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Puede elegir cuál de sus currículums registrados desea enviar para esta postulación.
                            </small>
                        </div>

                        <!-- Botón dinámico de vista previa del CV seleccionado -->
                        <div id="contenedor-preview-cv" class="mb-3 p-3 bg-white border rounded" style="display: none;">
                            <span class="small text-muted d-block mb-2 font-weight-bold">Vista previa del CV seleccionado:</span>
                            <div class="d-flex" style="gap: 8px;">
                                <a id="btn-ver-cv" href="#" target="_blank" class="btn btn-outline-info btn-sm flex-fill">
                                    <i class="fas fa-eye mr-1"></i> Ver Ficha
                                </a>
                                <a id="btn-pdf-cv" href="#" target="_blank" class="btn btn-outline-danger btn-sm flex-fill">
                                    <i class="fas fa-file-pdf mr-1"></i> Ver PDF
                                </a>
                                <a id="btn-link-cv" href="#" target="_blank" class="btn btn-outline-primary btn-sm flex-fill" style="display: none;">
                                    <i class="fas fa-external-link-alt mr-1"></i> Link Web
                                </a>
                            </div>
                        </div>

                        <!-- Declaración de conformidad -->
                        <div class="custom-control custom-checkbox mb-4">
                            <input type="checkbox" class="custom-control-input" id="checkConformidad" required>
                            <label class="custom-control-label small text-muted" for="checkConformidad">
                                Declaro bajo juramento que los datos contenidos en mi currículum son verídicos y autorizo a la empresa a evaluar mi perfil para la convocatoria.
                            </label>
                        </div>
                        <!-- Botón de Postulación -->
                        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm mb-2" id="btn-submit-postulacion">
                            <i class="fas fa-paper-plane mr-2"></i> Confirmar y Postular
                        </button>
                        <a href="<?= BASE_URL ?>/bolsa/ofertas" class="btn btn-light btn-block text-muted">
                            Cancelar y Volver
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script interactivo de previsualización de CV -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = '<?= BASE_URL ?>';
            const selectCv = document.getElementById('select-cv');
            const previewCont = document.getElementById('contenedor-preview-cv');
            const btnVer = document.getElementById('btn-ver-cv');
            const btnPdf = document.getElementById('btn-pdf-cv');
            const btnLink = document.getElementById('btn-link-cv');
            selectCv.addEventListener('change', function() {
                const cvId = this.value;
                if (cvId) {
                    const opt = this.options[this.selectedIndex];
                    const archivo = opt.getAttribute('data-archivo');
                    btnVer.href = baseUrl + '/bolsa/curriculum/ver/' + cvId;
                    btnPdf.href = baseUrl + '/bolsa/curriculum/pdf/' + cvId;
                    if (archivo && archivo.trim() !== '') {
                        btnLink.href = archivo;
                        btnLink.style.display = 'inline-block';
                    } else {
                        btnLink.style.display = 'none';
                    }
                    previewCont.style.display = 'block';
                } else {
                    previewCont.style.display = 'none';
                }
            });
        });
    </script>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
