<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php'; ?>

<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico() || \Core\Auth::esCoordinadorPEAcademico()) && $permitido): ?>

    <div class="card p-2 p-md-3 m-1 m-md-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <?php if (\Core\Auth::esDocenteAcademico()): ?>
                    <a class="btn btn-danger btn-sm mr-2 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">
                        <i class="fas fa-arrow-left mr-1"></i> Regresar
                    </a>
                <?php endif; ?>
                <?php if (\Core\Auth::esAdminAcademico() || \Core\Auth::esCoordinadorPEAcademico()): ?>
                    <a class="btn btn-danger btn-sm mr-2 mb-1" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">
                        <i class="fas fa-arrow-left mr-1"></i> Regresar
                    </a>
                <?php endif; ?>
            </div>
            <div class="d-md-none btn-group btn-group-sm mb-1" role="group" aria-label="Modo de vista">
                <button type="button" class="btn btn-primary active" id="btn-ver-modo-cards">
                    <i class="fas fa-th-list mr-1"></i> Tarjetas
                </button>
                <button type="button" class="btn btn-outline-primary" id="btn-ver-modo-tabla">
                    <i class="fas fa-table mr-1"></i> Tabla
                </button>
            </div>
        </div>

        <h5 class="text-center font-weight-bold mb-3 px-2" style="color:#607d8b; font-size: clamp(1rem, 2.5vw, 1.25rem);">
            Calificaciones - <span style="color:#4153a1;"><?php echo strtoupper($nombreUnidadDidactica ?? ''); ?></span>
        </h5>

        <?php if (!$esCoordinadorPE): ?>
            <div class="row no-gutters mb-3" style="gap: 6px;">
                <a class="btn btn-info btn-sm col-auto flex-fill flex-md-grow-0" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/registroOficial/<?= $id_programacion_ud ?>">
                    <i class="fas fa-print mr-1"></i> Registro Oficial
                </a>
                <a class="btn btn-success btn-sm col-auto flex-fill flex-md-grow-0" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/actaFinal/<?= $id_programacion_ud ?>">
                    <i class="fas fa-file-pdf mr-1"></i> Acta Final
                </a>
                <a class="btn btn-primary btn-sm text-white col-auto flex-fill flex-md-grow-0" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/actaRecuperacion/<?= $id_programacion_ud ?>">
                    <i class="fas fa-file-alt mr-1"></i> Acta Recuperación
                </a>
                <a class="btn btn-warning btn-sm text-white col-auto flex-fill flex-md-grow-0" target="_blank" href="<?= BASE_URL ?>/academico/calificaciones/reporteRegistra/<?= $id_programacion_ud ?>">
                    <i class="fas fa-chart-bar mr-1"></i> Reporte Registra
                </a>
            </div>
        <?php endif; ?>

        <!-- PANEL MÓVIL (< 768px) -->
        <div id="mobile-ver-panel" class="d-md-none">
            <?php if (isset($nros_calificacion) && count($nros_calificacion)): ?>
                <div class="bg-light border rounded p-2 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small font-weight-bold text-uppercase text-muted">Indicadores de Logro</span>
                        <?php if ($periodo_vigente['vigente'] && !$esCoordinadorPE): ?>
                            <label class="mb-0 small font-weight-bold text-primary d-flex align-items-center" style="cursor:pointer;">
                                <input type="checkbox" class="mr-1 js-mobile-mostrar-todos" <?= (isset($mostrar_promedio_todos) && $mostrar_promedio_todos) ? 'checked' : '' ?>>
                                Mostrar Prom. Final
                            </label>
                        <?php endif; ?>
                    </div>
                    <div class="row no-gutters" style="gap: 6px;">
                        <?php foreach ($nros_calificacion as $nro):
                            $mostrar = $mostrar_calificaciones[$nro] ?? 0;
                            $desc_indicador = $indicadores_capacidad['I' . $nro] ?? $indicadores_capacidad[$nro] ?? '';
                        ?>
                            <div class="col-12 bg-white border rounded p-2 d-flex justify-content-between align-items-center">
                                <div class="pr-2" style="min-width: 0;">
                                    <div class="font-weight-bold text-dark" style="font-size: 13px;">I.L.° <?= $nro ?></div>
                                    <?php if ($desc_indicador !== ''): ?>
                                        <div class="small text-muted text-truncate" title="<?= htmlspecialchars($desc_indicador) ?>"><?= htmlspecialchars($desc_indicador) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center flex-shrink-0">
                                    <?php if (!$esCoordinadorPE): ?>
                                        <?php if ($periodo_vigente['vigente']): ?>
                                            <label class="mb-0 mr-2 small d-flex align-items-center">
                                                <input type="checkbox" class="mr-1 js-mobile-mostrar-ind" data-nro="<?= $nro ?>" <?= $mostrar ? 'checked' : '' ?>>
                                                Ver
                                            </label>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/academico/calificaciones/evaluar/<?= $id_programacion_ud ?>/<?= $nro ?>" class="btn btn-primary btn-sm">
                                            <?= ($periodo_vigente['vigente']) ? 'Evaluar' : 'Ver' ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="input-group input-group-sm mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="search" id="mobile-ver-buscar" class="form-control" placeholder="Buscar estudiante por nombre o DNI...">
            </div>

            <div id="mobile-ver-cards">
                <?php foreach ($estudiantes as $idx => $est):
                    $id_detalle = $est['id_detalle_matricula'];
                    $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                    $motivo = 'Inasistencia';
                    if ($est['licencia'] != '') {
                        $motivo = 'Licencia';
                        $inhabilitado = $id_detalle;
                    }
                    $recup = $recuperaciones[$id_detalle] ?? '';
                    $promedio_final = $promedios[$id_detalle];
                    $promedio_finalll = ($recup != '') ? $recup : $promedio_final;
                    if ($inhabilitado) {
                        $notaMostrarCard = (is_array($nota_inasistencia) && $est['licencia'] != '') ? '' : (is_array($nota_inasistencia) ? reset($nota_inasistencia) : $nota_inasistencia);
                    } else {
                        $notaMostrarCard = is_array($promedio_finalll) ? reset($promedio_finalll) : $promedio_finalll;
                    }
                    $esRojoFinal = ($notaMostrarCard !== '' && is_numeric($notaMostrarCard) && $notaMostrarCard < 13) || $inhabilitado;
                ?>
                    <div class="ver-student-card <?= $inhabilitado ? 'border-danger bg-light' : '' ?>"
                         data-nombre="<?= htmlspecialchars(strtolower($est['apellidos_nombres'])) ?>"
                         data-dni="<?= htmlspecialchars(strtolower($est['dni'])) ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="pr-2" style="min-width: 0;">
                                <div class="font-weight-bold text-dark" style="font-size: 14px; line-height: 1.25;">
                                    <?= ($idx + 1) ?>. <?= htmlspecialchars($est['apellidos_nombres']) ?>
                                </div>
                                <div class="small text-muted mt-1">
                                    DNI: <?= htmlspecialchars($est['dni']) ?>
                                    <?php if ($inhabilitado): ?>
                                        <span class="badge badge-danger ml-1"><?= $motivo ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="small text-muted" style="font-size: 10px;">PROM. FINAL</div>
                                <span id="mobile_promedio_final_<?= $id_detalle ?>"
                                      class="badge <?= $esRojoFinal ? 'badge-danger' : 'badge-primary' ?> px-2 py-1"
                                      style="font-size: 14px; min-width: 36px;">
                                    <?= ($notaMostrarCard !== '' && $notaMostrarCard !== null) ? htmlspecialchars((string)$notaMostrarCard) : '—' ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($nros_calificacion)): ?>
                            <div class="d-flex flex-wrap mb-2" style="gap: 6px;">
                                <?php foreach ($nros_calificacion as $nro):
                                    $nota = $notas[$id_detalle][$nro] ?? '';
                                    $clsNota = 'text-muted';
                                    if ($nota !== '' && is_numeric($nota)) {
                                        $clsNota = ($nota < 13) ? 'text-danger font-weight-bold' : 'text-primary font-weight-bold';
                                    }
                                ?>
                                    <div class="border rounded px-2 py-1 bg-light text-center" style="min-width: 54px;">
                                        <div class="text-muted" style="font-size: 10px;">I.L. <?= $nro ?></div>
                                        <div class="<?= $clsNota ?>" style="font-size: 14px;"><?= ($nota === '') ? '—' : $nota ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($promedio_final, [10, 11, 12]) && !$inhabilitado): ?>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                                <span class="small font-weight-bold text-muted">Recuperación (0 - 20):</span>
                                <?php if ($periodo_vigente['vigente']): ?>
                                    <input type="number" inputmode="numeric" min="0" max="20"
                                           class="form-control form-control-sm text-center font-weight-bold js-mobile-recup <?= ($recup !== '' && $recup < 13) ? 'text-danger' : 'text-primary' ?>"
                                           data-sync-recup="<?= $id_detalle ?>"
                                           value="<?= htmlspecialchars((string)$recup) ?>"
                                           style="width: 78px; font-size: 16px;">
                                <?php else: ?>
                                    <span class="font-weight-bold"><?= htmlspecialchars((string)$recup) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($recup !== ''): ?>
                            <div class="small text-muted border-top pt-1">
                                Recuperación: <b><?= htmlspecialchars((string)$recup) ?></b>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TABLA COMPLETA -->
        <div id="wrapper-tabla-ver" class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-bordered" id="tabla-ver-calif" style="font-size: 14px;">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle text-center">Nro Orden</th>
                            <th rowspan="2" class="align-middle text-center">DNI</th>
                            <th rowspan="2" class="align-middle text-center sticky-nombre-ver">APELLIDOS Y NOMBRES</th>
                            <?php if (isset($nros_calificacion) && count($nros_calificacion)): ?>
                                <th colspan="<?= count($nros_calificacion) ?>" class="align-middle text-center" style="background:#f5f7fa;">
                                    CALIFICACIONES
                                </th>
                            <?php endif; ?>
                            <th rowspan="2" class="align-middle text-center" style="writing-mode:vertical-rl; background:#f5f7fa;">RECUPERACION</th>
                            <th rowspan="2" class="align-middle text-center" style="writing-mode:vertical-rl; background:#f5f7fa;">PROMEDIO FINAL <br>
                                <div style="writing-mode:initial;">
                                    <?php if ($periodo_vigente['vigente'] && !$esCoordinadorPE): ?>
                                        <input type="checkbox"
                                            id="mostrar-promedio-todos"
                                            <?= (isset($mostrar_promedio_todos) && $mostrar_promedio_todos) ? 'checked' : '' ?>>
                                        <span style="font-size:12px;">Mostrar</span>
                                    <?php endif; ?>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <?php foreach ($nros_calificacion as $k => $nro): ?>
                                <?php $mostrar = $mostrar_calificaciones[$nro] ?? 0; ?>
                                <th class="text-center" style="min-width:110px;">
                                    I.L.° <?= $nro ?> <br>
                                    <?php if (!$esCoordinadorPE): ?>
                                        <?php $desc_indicador = $indicadores_capacidad['I' . $nro] ?? $indicadores_capacidad[$nro] ?? ''; ?>
                                        <a href="<?= BASE_URL ?>/academico/calificaciones/evaluar/<?= $id_programacion_ud ?>/<?= $nro ?>" class="btn btn-primary btn-sm ml-2 waves-effect waves-light" data-toggle="tooltip" data-placement="top" title="<?= htmlspecialchars($desc_indicador) ?>" data-original-title="<?= htmlspecialchars($desc_indicador) ?>">
                                            <i></i><?= ($periodo_vigente['vigente']) ? 'Evaluar' : 'Ver' ?>
                                        </a>
                                        <div>
                                            <?php if ($periodo_vigente['vigente']): ?>
                                                <input type="checkbox"
                                                    class="mostrar-checkbox" data-nro="<?= $nro ?>"
                                                    <?= $mostrar ? 'checked' : '' ?>>
                                                <span style="font-size:12px;">Mostrar</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $idx => $est):

                            $id_detalle = $est['id_detalle_matricula'];
                            $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                            $motivo = ' (Inasistencia)';
                            if ($est['licencia'] != '') {
                                $motivo = ' (Licencia)';
                                $inhabilitado = $id_detalle;
                            }
                        ?>
                            <tr class="<?= $inhabilitado ? 'table-danger bg-danger' : '' ?>">
                                <td class="text-center"><?= ($idx + 1) ?></td>
                                <td class="text-center <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['dni']; ?></td>
                                <td class="sticky-nombre-ver <?= $inhabilitado ? 'text-danger font-weight-bold' : '' ?>"><?= $est['apellidos_nombres']; ?><?= $inhabilitado ? $motivo : '' ?></td>
                                <?php foreach ($nros_calificacion as $nro):
                                    $nota = $notas[$id_detalle][$nro] ?? '';
                                    $clase = '';
                                    if ($nota !== '' && is_numeric($nota)) {
                                        $clase = ($nota < 13) ? "text-danger font-weight-bold" : "text-primary font-weight-bold";
                                    }
                                ?>
                                    <td class="text-center <?= $clase; ?>">
                                        <?= ($nota === '') ? '' : $nota; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center">
                                    <?php
                                    $recup = $recuperaciones[$id_detalle] ?? '';
                                    $promedio_final = $promedios[$id_detalle];
                                    if (in_array($promedio_final, [10, 11, 12]) && !$inhabilitado): ?>
                                        <?php if ($periodo_vigente['vigente']): ?>
                                            <input type="number" class="form-control form-control-sm text-center nota-recuperacion <?= ($recup < 13) ? 'text-danger' : 'text-primary' ?>" data-id-recuperacion="<?= $id_detalle ?>" value="<?= $recup; ?>" style="max-width:50px;display:inline-block;">
                                        <?php else: ?>
                                            <?= $recup; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= $recup; ?>
                                    <?php endif; ?>
                                </td>
                                <?php
                                if ($recup != '') {
                                    $promedio_finalll = $recup;
                                } else {
                                    $promedio_finalll = $promedios[$id_detalle];
                                }
                                ?>
                                <td class="text-center font-weight-bold <?= (is_array($promedio_finalll) ? (reset($promedio_finalll) < 13) : ($promedio_finalll < 13)) || $inhabilitado ? "text-danger" : "text-primary"; ?>">
                                    <label id="promedio_finalll_<?= $id_detalle ?>">
                                        <!-- Mostrar nota de inasistencia si el estudiante está inhabilitado -->
                                        <?php
                                        if ($inhabilitado) {
                                            if (is_array($nota_inasistencia) && $est['licencia'] != '') {
                                                echo $nota_mostrar;
                                            } else {
                                                $nota_mostrar = reset($nota_inasistencia);
                                                echo $nota_mostrar;
                                            }
                                        } else {
                                            if (is_array($promedio_finalll)) {
                                                $nota_mostrar = reset($promedio_finalll);
                                                echo $nota_mostrar;
                                            } else {
                                                $nota_mostrar = $promedio_finalll;
                                                echo $nota_mostrar;
                                            }
                                        }
                                        $nota_mostrar = "";
                                        ?>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($periodo_vigente['vigente'] && !$esCoordinadorPE): ?>
            <div class="text-center my-2">
                <a class="btn btn-success col-12 col-sm-6 col-md-2" href="<?= BASE_URL; ?>/academico/calificaciones/ver/<?= $id_programacion_ud; ?>">
                    <i class="fas fa-check mr-1"></i> Guardar
                </a>
            </div>
        <?php endif; ?>
    </div>
    <style>
        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table th {
            background: #f5f7fa !important;
            border-bottom: 2px solid #dbe2ef;
        }

        .table td {
            background: #fff;
        }

        @media (max-width: 767.98px) {
            .ver-student-card {
                border: 1px solid #e3e6f0;
                border-radius: 10px;
                padding: 12px;
                margin-bottom: 10px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }
            #tabla-ver-calif .sticky-nombre-ver {
                position: sticky;
                left: 0;
                z-index: 2;
                min-width: 145px;
                max-width: 165px;
                font-size: 12px;
                box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
            }
        }
    </style>
    <script>
        document.querySelectorAll('.nota-recuperacion').forEach(function(input) {
            input.addEventListener('change', function() {
                var val = this.value.trim();

                // Validar que sea número y esté entre 0 y 20
                if (isNaN(val) || val < 0 || val > 20) {
                    this.classList.add('border-danger');
                    this.classList.remove('border-success');
                    alert('El valor debe ser un número entre 0 y 20');
                    this.focus();
                    return;
                } else {
                    this.classList.remove('border-danger');
                    var id_detalle_mat = this.dataset.idRecuperacion;
                    var valor = this.value;

                    fetch('<?= BASE_URL ?>/academico/calificaciones/guardarRecuperacion', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_detalle_mat=' + encodeURIComponent(id_detalle_mat) +
                                '&valor=' + encodeURIComponent(valor)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                this.classList.add('border-success');
                                let label_final = document.getElementById('promedio_finalll_' + id_detalle_mat);
                                let mobileBadge = document.getElementById('mobile_promedio_final_' + id_detalle_mat);
                                let mobileInput = document.querySelector('.js-mobile-recup[data-sync-recup="' + id_detalle_mat + '"]');
                                if (mobileInput) {
                                    mobileInput.value = valor;
                                    mobileInput.classList.add('border-success');
                                    mobileInput.classList.remove('border-danger', 'text-primary', 'text-danger');
                                    mobileInput.classList.add(valor < 13 ? 'text-danger' : 'text-primary');
                                    setTimeout(() => mobileInput.classList.remove('border-success'), 1500);
                                }
                                if (mobileBadge) {
                                    mobileBadge.textContent = valor === '' ? '—' : valor;
                                    mobileBadge.classList.remove('badge-primary', 'badge-danger');
                                    mobileBadge.classList.add(valor < 13 ? 'badge-danger' : 'badge-primary');
                                }
                                if (valor < 13) {
                                    this.classList.remove('text-primary');
                                    this.classList.add('text-danger');
                                    label_final.classList.remove('text-primary');
                                    label_final.classList.add('text-danger');
                                    label_final.innerHTML = valor;
                                } else {
                                    this.classList.remove('text-danger');
                                    this.classList.add('text-primary');
                                    label_final.classList.remove('text-danger');
                                    label_final.classList.add('text-primary');
                                    label_final.innerHTML = valor;
                                }
                                setTimeout(() => this.classList.remove('border-success'), 1500);
                            } else {
                                this.classList.add('border-danger');
                                alert(data.msg || 'Error al guardar');
                            }
                        })
                        .catch(err => {
                            this.classList.add('border-danger');
                            alert('Error en la petición.');
                        });
                }
            });
        });
    </script>
    <script>
        document.querySelectorAll('.mostrar-checkbox').forEach(function(el) {
            el.addEventListener('change', function() {
                var nro = $(this).data('nro');
                var mostrar = $(this).is(':checked') ? 1 : 0;
                $.post('<?= BASE_URL ?>/academico/calificaciones/actualizarMostrar', {
                    id_programacion_ud: '<?= $id_programacion_ud ?>',
                    nro_calificacion: nro,
                    mostrar: mostrar
                }, function(resp) {
                    // Aquí puedes mostrar un toast o mensaje de éxito/error
                });
            });
        });
        var elMostrarPromedioTodos = document.getElementById('mostrar-promedio-todos');
        if (elMostrarPromedioTodos) {
            elMostrarPromedioTodos.addEventListener('change', function() {
                var mostrar = this.checked ? 1 : 0;
                fetch('<?= BASE_URL ?>/academico/calificaciones/actualizarMostrarPromedioTodos', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id_programacion_ud=<?= $id_programacion_ud ?>&mostrar=' + mostrar
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Opcional: Mostrar mensaje de éxito o error
                    });
            });
        }

        // Sincronización y alternancia móvil (< 768px)
        (function() {
            var btnCards = document.getElementById('btn-ver-modo-cards');
            var btnTabla = document.getElementById('btn-ver-modo-tabla');
            var panelMovil = document.getElementById('mobile-ver-panel');
            var wrapperTabla = document.getElementById('wrapper-tabla-ver');

            function setModo(modo) {
                if (!panelMovil || !wrapperTabla) return;
                if (modo === 'tabla') {
                    panelMovil.classList.add('d-none');
                    wrapperTabla.classList.remove('d-none');
                    btnTabla.classList.add('active', 'btn-primary');
                    btnTabla.classList.remove('btn-outline-primary');
                    btnCards.classList.remove('active', 'btn-primary');
                    btnCards.classList.add('btn-outline-primary');
                } else {
                    panelMovil.classList.remove('d-none');
                    wrapperTabla.classList.add('d-none');
                    btnCards.classList.add('active', 'btn-primary');
                    btnCards.classList.remove('btn-outline-primary');
                    btnTabla.classList.remove('active', 'btn-primary');
                    btnTabla.classList.add('btn-outline-primary');
                }
            }

            if (btnCards && btnTabla) {
                btnCards.addEventListener('click', function() { setModo('cards'); });
                btnTabla.addEventListener('click', function() { setModo('tabla'); });
            }

            var buscador = document.getElementById('mobile-ver-buscar');
            if (buscador) {
                buscador.addEventListener('input', function() {
                    var q = this.value.trim().toLowerCase();
                    document.querySelectorAll('.ver-student-card').forEach(function(card) {
                        var match = !q || card.dataset.nombre.indexOf(q) !== -1 || card.dataset.dni.indexOf(q) !== -1;
                        card.style.display = match ? '' : 'none';
                    });
                });
            }

            document.querySelectorAll('.js-mobile-recup').forEach(function(mInput) {
                mInput.addEventListener('change', function() {
                    var id = this.dataset.syncRecup;
                    var realInput = document.querySelector('.nota-recuperacion[data-id-recuperacion="' + id + '"]');
                    if (!realInput) return;
                    realInput.value = this.value;
                    realInput.dispatchEvent(new Event('change'));
                });
            });

            document.querySelectorAll('.js-mobile-mostrar-ind').forEach(function(chk) {
                chk.addEventListener('change', function() {
                    var nro = this.dataset.nro;
                    var realChk = document.querySelector('.mostrar-checkbox[data-nro="' + nro + '"]');
                    if (!realChk) return;
                    realChk.checked = this.checked;
                    realChk.dispatchEvent(new Event('change'));
                });
            });

            var mobTodos = document.querySelector('.js-mobile-mostrar-todos');
            if (mobTodos && elMostrarPromedioTodos) {
                mobTodos.addEventListener('change', function() {
                    elMostrarPromedioTodos.checked = this.checked;
                    elMostrarPromedioTodos.dispatchEvent(new Event('change'));
                });
            }
        })();
    </script>

<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
