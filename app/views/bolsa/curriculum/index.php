<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1 text-dark font-weight-bold"></i>Mis Currículums (CV)</h3>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/bolsa/postulaciones" class="btn btn-primary mr-1">
                <i class="fas fa-clipboard-list mr-1"></i> Mis Postulaciones
            </a>
            <a href="<?= BASE_URL ?>/bolsa/curriculum/nuevo" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i>Nuevo Currículum
            </a>
        </div>
    </div>
</div>

<?php if (empty($curriculums)): ?>
    <!-- Estado cuando el usuario aún no tiene ningún CV registrado -->
    <div class="card shadow-sm rounded-3 border-0 p-5 text-center my-4">
        <div class="my-4">
            <i class="fas fa-file-alt display-3 text-muted mb-3" style="opacity: 0.5;"></i>
            <h4 class="font-weight-bold text-dark">Aún no tienes ningún currículum registrado</h4>
            <p class="text-muted max-w-500 mx-auto mb-4" style="max-width: 500px; margin: 0 auto;">
                Crea tu primer currículum ahora. Puedes armarlo con tus estudios y experiencia dentro del sistema, o vincular un enlace público de Google Drive, OneDrive o LinkedIn.
            </p>
            <a href="<?= BASE_URL ?>/bolsa/curriculum/nuevo" class="btn btn-success px-4 py-2 font-weight-bold">
                <i class="fas fa-plus-circle mr-1"></i> Crear mi Primer Currículum
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Listado con DataTables -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-curriculums" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">Nombre del Currículum</th>
                        <th class="text-center">Tipo de CV</th>
                        <th class="text-center">Enlace en la Nube</th>
                        <th class="text-center">Secciones del Sistema</th>
                        <th class="text-center">Uso en Postulaciones</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($curriculums as $index => $c): 
                        $tieneEnlace = !empty($c['archivo']);
                        $tieneSecciones = ($c['total_educacion'] + $c['total_experiencia'] + $c['total_habilidades'] + $c['total_idiomas'] + $c['total_certificados']) > 0;
                        $nombreCv = mb_strlen($c['descripcion']) > 90 ? mb_substr($c['descripcion'], 0, 90) . '…' : $c['descripcion'];
                    ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($nombreCv) ?></strong></td>
                            <td>
                                <?php if ($tieneEnlace && $tieneSecciones): ?>
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i> Mixto (Link + Sistema)</span>
                                <?php elseif ($tieneEnlace): ?>
                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-link mr-1"></i> Enlace Web Externo</span>
                                <?php else: ?>
                                    <span class="badge badge-primary px-2 py-1"><i class="fas fa-file-invoice mr-1"></i> Creado en el Sistema</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tieneEnlace): ?>
                                    <a href="<?= htmlspecialchars($c['archivo']) ?>" target="_blank" class="btn btn-outline-info btn-sm py-1 px-2" title="Abrir enlace en pestaña nueva">
                                        <i class="fas fa-external-link-alt mr-1"></i> Abrir enlace
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small font-italic">Sin enlace</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap" style="gap: 4px;">
                                    <span class="badge badge-light border text-dark" title="Estudios">
                                        <i class="fas fa-graduation-cap text-primary"></i> <?= $c['total_educacion'] ?>
                                    </span>
                                    <span class="badge badge-light border text-dark" title="Experiencia">
                                        <i class="fas fa-briefcase text-success"></i> <?= $c['total_experiencia'] ?>
                                    </span>
                                    <span class="badge badge-light border text-dark" title="Certificados">
                                        <i class="fas fa-certificate text-warning"></i> <?= $c['total_certificados'] ?>
                                    </span>
                                    <span class="badge badge-light border text-dark" title="Habilidades">
                                        <i class="fas fa-tools text-info"></i> <?= $c['total_habilidades'] ?>
                                    </span>
                                    <span class="badge badge-light border text-dark" title="Idiomas">
                                        <i class="fas fa-language text-secondary"></i> <?= $c['total_idiomas'] ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($c['total_postulaciones'] > 0): ?>
                                    <span class="badge badge-success px-2 py-1" title="Postulaciones activas">
                                        <?= $c['total_postulaciones'] ?> oferta(s)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">0 ofertas</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/bolsa/curriculum/ver/<?= $c['id'] ?>" class="btn btn-info btn-sm" title="Ver ficha de CV">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/bolsa/curriculum/pdf/<?= $c['id'] ?>" target="_blank" class="btn btn-danger btn-sm" title="Descargar / Ver PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/bolsa/curriculum/editar/<?= $c['id'] ?>" class="btn btn-warning btn-sm" title="Editar este CV">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-danger btn-eliminar-cv" 
                                    data-id="<?= $c['id'] ?>" 
                                    data-titulo="<?= htmlspecialchars($nombreCv) ?>"
                                    data-postulaciones="<?= (int)$c['total_postulaciones'] ?>"
                                    title="Eliminar este CV">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialización de DataTable en español
            $('#tabla-curriculums').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[0, 'asc']]
            });

            // Confirmación de eliminación con SweetAlert2
            $(document).on('click', '.btn-eliminar-cv', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const titulo = $(this).data('titulo');
                const postulaciones = parseInt($(this).data('postulaciones') || 0);

                if (postulaciones > 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se puede eliminar',
                            text: `Este currículum ya fue utilizado en ${postulaciones} postulación(es) a ofertas de trabajo. No se puede eliminar para mantener el historial.`,
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(`Este currículum ya fue utilizado en ${postulaciones} postulación(es). No se puede eliminar.`);
                    }
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Eliminar este currículum?',
                        text: `Se eliminará el currículum "${titulo}" junto con sus datos de educación, experiencia y certificados registrados.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `<?= BASE_URL ?>/bolsa/curriculum/eliminar/${id}`;
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de eliminar el currículum "${titulo}"?`)) {
                        window.location.href = `<?= BASE_URL ?>/bolsa/curriculum/eliminar/${id}`;
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
