<?php 
require __DIR__ . '/../../layouts/header.php'; 

$cv   = $detalle['cv'];
$usr  = $detalle['usuario'];
$edu  = $detalle['educacion'] ?? [];
$exp  = $detalle['experiencia'] ?? [];
$hab  = $detalle['habilidades'] ?? [];
$idi  = $detalle['idiomas'] ?? [];
$cert = $detalle['certificados'] ?? [];
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark font-weight-bold">
                <i class="fas fa-id-badge text-primary mr-2"></i>Vista de Currículum Vitae
            </h4>
            <p class="text-muted small mb-0">Visualización previa del currículum registrado en el sistema.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $cv['id'] ?>" target="_blank" class="btn btn-danger btn-sm mr-1">
                <i class="fas fa-file-pdf mr-1"></i> Descargar en PDF
            </a>
            <a href="<?= BASE_URL ?>/bolsa/curriculum/editar/<?= $cv['id'] ?>" class="btn btn-warning btn-sm mr-1">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="<?= BASE_URL ?>/bolsa/curriculum" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
</div>

<!-- FICHA / PERFIL DIGITAL ESTILO EJECUTIVO -->
<div class="card border-0 shadow-sm mb-4">
    <!-- Encabezado de la Ficha -->
    <div class="card-header bg-primary text-white p-4 rounded-top">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="font-weight-bold mb-1 text-white"><?= htmlspecialchars($usr['nombre_completo'] ?? 'Estudiante') ?></h3>
                <h5 class="text-white-50 font-weight-normal mb-3"><?= htmlspecialchars($usr['programa_estudios'] ?? 'Educación Superior') ?></h5>
                
                <div class="d-flex flex-wrap text-white-50 small">
                    <span class="mr-3 mb-1"><i class="fas fa-id-card mr-1 text-white"></i> DNI: <?= htmlspecialchars($usr['dni'] ?? '-') ?></span>
                    <span class="mr-3 mb-1"><i class="fas fa-envelope mr-1 text-white"></i> <?= htmlspecialchars($usr['correo'] ?? '-') ?></span>
                    <span class="mr-3 mb-1"><i class="fas fa-phone mr-1 text-white"></i> <?= htmlspecialchars($usr['telefono'] ?? '-') ?></span>
                    <span class="mr-3 mb-1"><i class="fas fa-map-marker-alt mr-1 text-white"></i> <?= htmlspecialchars($usr['direccion'] ?? '-') ?></span>
                </div>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <?php if (!empty($cv['archivo'])): ?>
                    <a href="<?= htmlspecialchars($cv['archivo']) ?>" target="_blank" class="btn btn-light btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-external-link-alt text-primary mr-1"></i> Ver CV en la Nube
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- 1. Nombre del Currículum -->
        <?php if (!empty($cv['descripcion'])): ?>
            <div class="mb-4">
                <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                    <i class="fas fa-id-card mr-2"></i>Nombre del Currículum
                </h5>
                <h6 class="text-dark font-weight-bold mb-0" style="font-size: 15px;">
                    <?= htmlspecialchars($cv['descripcion']) ?>
                </h6>
            </div>
        <?php endif; ?>

        <!-- 2. Experiencia Laboral -->
        <div class="mb-4">
            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                <i class="fas fa-briefcase mr-2"></i>Experiencia Laboral
            </h5>
            <?php if (!empty($exp)): ?>
                <?php foreach ($exp as $e): 
                    $fIni = !empty($e['inicio']) ? date('m/Y', strtotime($e['inicio'])) : '';
                    $fFin = !empty($e['termino']) ? date('m/Y', strtotime($e['termino'])) : 'Actualidad';
                ?>
                    <div class="mb-3 pl-3 border-left border-primary" style="border-width: 3px !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($e['cargo']) ?></h6>
                            <span class="badge badge-light text-muted font-weight-bold">
                                <?= $fIni ?> - <?= $fFin ?> <?= !empty($e['tiempo_cargo']) ? '(' . htmlspecialchars($e['tiempo_cargo']) . ')' : '' ?>
                            </span>
                        </div>
                        <div class="text-muted small font-weight-bold mb-1"><?= htmlspecialchars($e['institucion']) ?></div>
                        <?php if (!empty($e['descripcion'])): ?>
                            <p class="text-secondary small mb-1"><?= nl2br(htmlspecialchars($e['descripcion'])) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($e['archivo'])): ?>
                            <a href="<?= htmlspecialchars($e['archivo']) ?>" target="_blank" class="small text-primary">
                                <i class="fas fa-link mr-1"></i> Ver constancia de trabajo
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small font-italic">No se ha registrado experiencia laboral en este currículum.</p>
            <?php endif; ?>
        </div>

        <!-- 3. Formación Académica -->
        <div class="mb-4">
            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                <i class="fas fa-graduation-cap mr-2"></i>Educación y Formación
            </h5>
            <?php if (!empty($edu)): ?>
                <div class="row">
                    <?php foreach ($edu as $ed): 
                        $fIni = !empty($ed['inicio']) ? date('m/Y', strtotime($ed['inicio'])) : '';
                        $fFin = !empty($ed['termino']) ? date('m/Y', strtotime($ed['termino'])) : 'En curso';
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <span class="badge badge-info float-right"><?= htmlspecialchars($ed['nivel_academico']) ?></span>
                                <h6 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($ed['especialidad'] ?: $ed['nivel_academico']) ?></h6>
                                <div class="text-muted small mb-2"><?= htmlspecialchars($ed['centro_estudios']) ?></div>
                                <div class="text-muted small">
                                    <i class="far fa-calendar-alt mr-1"></i> Período: <?= $fIni ?> - <?= $fFin ?>
                                </div>
                                <?php if (!empty($ed['archivo'])): ?>
                                    <div class="mt-2">
                                        <a href="<?= htmlspecialchars($ed['archivo']) ?>" target="_blank" class="small text-primary font-weight-bold">
                                            <i class="fas fa-external-link-alt mr-1"></i> Ver constancia / diploma
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted small font-italic">No se ha registrado formación académica en este currículum.</p>
            <?php endif; ?>
        </div>

        <!-- 4. Cursos y Certificados -->
        <div class="mb-4">
            <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                <i class="fas fa-certificate mr-2"></i>Cursos y Certificaciones
            </h5>
            <?php if (!empty($cert)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Curso / Certificado</th>
                                <th>Institución</th>
                                <th>Tema</th>
                                <th class="text-center">Horas</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Enlace</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cert as $c): ?>
                                <tr>
                                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td><?= htmlspecialchars($c['institucion']) ?></td>
                                    <td><?= htmlspecialchars($c['tema']) ?></td>
                                    <td class="text-center"><?= !empty($c['horas']) ? (int)$c['horas'] . ' hrs' : '-' ?></td>
                                    <td class="text-center small"><?= !empty($c['termino']) ? date('d/m/Y', strtotime($c['termino'])) : '-' ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($c['archivo'])): ?>
                                            <a href="<?= htmlspecialchars($c['archivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2">
                                                <i class="fas fa-link"></i>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted small font-italic">No se han registrado certificaciones en este currículum.</p>
            <?php endif; ?>
        </div>

        <!-- 5. Habilidades e Idiomas -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                    <i class="fas fa-tools mr-2"></i>Habilidades
                </h5>
                <?php if (!empty($hab)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($hab as $h): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($h['titulo']) ?></span>
                                <span class="badge badge-primary badge-pill px-3 py-1"><?= htmlspecialchars($h['nivel']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small font-italic">No registradas.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mb-3">
                <h5 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
                    <i class="fas fa-language mr-2"></i>Idiomas
                </h5>
                <?php if (!empty($idi)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($idi as $i): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div>
                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($i['idioma']) ?></span>
                                    <?php if (!empty($i['archivo'])): ?>
                                        <a href="<?= htmlspecialchars($i['archivo']) ?>" target="_blank" class="ml-2 text-primary small">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <span class="badge badge-info badge-pill px-3 py-1"><?= htmlspecialchars($i['nivel']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small font-italic">No registrados.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
