<?php
$module = 'academico';
require __DIR__ . '/../../layouts/header.php';

// Determinar si el periodo está finalizado para bloquear edición
$periodoFinalizado = strtotime($periodo['fecha_fin']) < strtotime(date('Y-m-d'));
$limiteInasistencia = 30; // Porcentaje para resaltar en rojo
if ($esCoordinadorPE) {
    $periodoFinalizado = true;
} else {
    $periodoFinalizado = false;
}

// Elegir sesión inicial sugerida para la vista móvil (hoy > última con registro > primera)
$sesionInicialId = !empty($sesiones) ? (int)$sesiones[0]['id'] : 0;
$hoyYmd = date('Y-m-d');
$encontroHoy = false;
foreach ($sesiones as $ses) {
    if (date('Y-m-d', strtotime($ses['fecha_desarrollo'])) === $hoyYmd) {
        $sesionInicialId = (int)$ses['id'];
        $encontroHoy = true;
        break;
    }
}
if (!$encontroHoy && !empty($sesiones)) {
    foreach (array_reverse($sesiones) as $ses) {
        $sid = (int)$ses['id'];
        $tieneDato = false;
        foreach ($estudiantes as $est) {
            $idm = $est['id_detalle_matricula'];
            if (!empty($asistencias[$idm][$sid])) {
                $tieneDato = true;
                break;
            }
        }
        if ($tieneDato) {
            $sesionInicialId = $sid;
            break;
        }
    }
}
?>
<?php if ((\Core\Auth::esDocenteAcademico() || \Core\Auth::esAdminAcademico() || \Core\Auth::esCoordinadorPEAcademico()) && $permitido): ?>
    <div class="card p-2 p-md-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <?php if (\Core\Auth::esDocenteAcademico()): ?>
                    <a class="btn btn-danger btn-sm mr-2" href="<?= BASE_URL; ?>/academico/unidadesDidacticas">
                        <i class="fas fa-arrow-left mr-1"></i> Regresar
                    </a>
                <?php endif; ?>
                <?php if (\Core\Auth::esAdminAcademico() || \Core\Auth::esCoordinadorPEAcademico()): ?>
                    <a class="btn btn-danger btn-sm mr-2" href="<?= BASE_URL; ?>/academico/unidadesDidacticas/evaluar">
                        <i class="fas fa-arrow-left mr-1"></i> Regresar
                    </a>
                <?php endif; ?>
            </div>
            <div class="d-md-none btn-group btn-group-sm" role="group" aria-label="Modo de vista">
                <button type="button" class="btn btn-primary active" id="btn-asist-modo-sesion">
                    <i class="fas fa-mobile-alt mr-1"></i> Por Sesión
                </button>
                <button type="button" class="btn btn-outline-primary" id="btn-asist-modo-tabla">
                    <i class="fas fa-table mr-1"></i> Tabla
                </button>
            </div>
        </div>

        <h4 class="text-center mb-3 px-2" style="font-size: clamp(1rem, 2.5vw, 1.35rem);">
            Asistencia - <?= htmlspecialchars($nombreUnidadDidactica) ?>
        </h4>

        <form action="<?= BASE_URL ?>/academico/asistencia/guardar" method="post" autocomplete="off" id="form-asistencia">
            <input type="hidden" name="id_programacion_ud" value="<?= (int)$id_programacion_ud ?>">

            <!-- PANEL MÓVIL POR SESIÓN (< 768px) -->
            <div id="mobile-asistencia-panel" class="d-md-none mb-3">
                <?php if (empty($sesiones)): ?>
                    <div class="alert alert-info mb-2">No hay sesiones programadas aún.</div>
                <?php else: ?>
                    <div class="bg-light border rounded p-2 mb-2 mobile-sticky-controls">
                        <div class="form-group mb-2">
                            <label for="mobile-sesion-picker" class="small font-weight-bold text-muted mb-1">Sesión / Fecha</label>
                            <select id="mobile-sesion-picker" class="form-control form-control-sm font-weight-bold">
                                <?php foreach ($sesiones as $idxSes => $ses): ?>
                                    <option value="<?= (int)$ses['id'] ?>" <?= ((int)$ses['id'] === $sesionInicialId) ? 'selected' : '' ?>>
                                        Sesión <?= htmlspecialchars($ses['semana'] ?? ($idxSes + 1)) ?> — <?= date('d/m/Y', strtotime($ses['fecha_desarrollo'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="search" id="mobile-buscar-asist" class="form-control" placeholder="Buscar estudiante o DNI...">
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <?php if (!$periodoFinalizado): ?>
                                <div class="btn-group btn-group-sm mb-1" role="group" aria-label="Marcar todos">
                                    <button type="button" class="btn btn-outline-success js-mobile-masivo" data-val="P">Todos P</button>
                                    <button type="button" class="btn btn-outline-danger js-mobile-masivo" data-val="F">Todos F</button>
                                    <button type="button" class="btn btn-outline-secondary js-mobile-masivo" data-val="">Limpiar</button>
                                </div>
                            <?php endif; ?>
                            <div class="small mb-1" id="mobile-asist-resumen">
                                <span class="badge badge-success mr-1">P: <span id="cnt-p">0</span></span>
                                <span class="badge badge-danger mr-1">F: <span id="cnt-f">0</span></span>
                                <span class="badge badge-secondary">Sin: <span id="cnt-v">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div id="mobile-asistencia-cards"></div>
                <?php endif; ?>
            </div>

            <!-- TABLA COMPLETA (escritorio o modo tabla móvil) -->
            <div id="wrapper-tabla-asistencia" class="table-responsive d-none d-md-block" style="overflow-x:auto;">
                <table class="table table-bordered table-hover table-sm align-middle" id="tabla-asistencia">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="bg-light sticky-col left-col" rowspan="2">DNI</th>
                            <th class="bg-light sticky-col left-col2" rowspan="2">APELLIDOS Y NOMBRES</th>
                            <?php foreach ($sesiones as $ses): ?>
                                <th style="min-width:60px; writing-mode: vertical-lr; transform: rotate(180deg);" title="Sesión <?= $ses['semana'] ?>">
                                    <?= date('Y-m-d', strtotime($ses['fecha_desarrollo'])) ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="bg-light" style="min-width:60px; writing-mode: vertical-lr; transform: rotate(180deg);" rowspan="2">INASISTENCIA</th>
                        </tr>
                        <tr>
                            <?php foreach ($sesiones as $ses): ?>
                                <th>
                                    <?php if (!$periodoFinalizado): ?>
                                        <select class="form-control form-control-sm select-masivo" data-sesion="<?= $ses['id'] ?>">
                                            <option value=""></option>
                                            <option value="P">P</option>
                                            <option value="F">F</option>
                                        </select>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $idxEst => $est): ?>
                            <tr data-orden="<?= $idxEst + 1 ?>"
                                data-id-detalle="<?= (int)$est['id_detalle_matricula'] ?>"
                                data-dni="<?= htmlspecialchars($est['dni']) ?>"
                                data-nombre="<?= htmlspecialchars($est['apellidos_nombres']) ?>">
                                <td class="sticky-col left-col bg-white"><?= htmlspecialchars($est['dni']) ?></td>
                                <td class="sticky-col left-col2 bg-white"><?= htmlspecialchars($est['apellidos_nombres']) ?></td>
                                <?php
                                $faltas = 0;
                                foreach ($sesiones as $i => $ses):
                                    $id_detalle_matricula = $est['id_detalle_matricula'];
                                    $id_sesion = $ses['id'];
                                    $valor = $asistencias[$id_detalle_matricula][$id_sesion] ?? '';
                                    if ($valor === 'F') $faltas++;
                                ?>
                                    <td class="text-center vertical">
                                        <?php if ($periodoFinalizado): ?>
                                            <b class="valor-asistencia-readonly" data-sesion="<?= $id_sesion ?>"><?= $valor ?: '-' ?></b>
                                        <?php else: ?>
                                            <select name="asistencia[<?= $id_detalle_matricula ?>][<?= $id_sesion ?>]" class="form-control form-control-sm text-center select-asistencia" data-sesion="<?= $id_sesion ?>">
                                                <option value="" <?= $valor == '' ? 'selected' : '' ?>></option>
                                                <option value="P" <?= $valor == 'P' ? 'selected' : '' ?>>P</option>
                                                <option value="F" <?= $valor == 'F' ? 'selected' : '' ?>>F</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php
                                $totalSesiones = count($sesiones);
                                $porcFaltas = $totalSesiones ? round($faltas * 100 / $totalSesiones) : 0;
                                $class = $porcFaltas >= $limiteInasistencia ? 'text-danger font-weight-bold' : '';
                                ?>
                                <td class="text-center td-porc-faltas <?= $class ?>"><?= $porcFaltas ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$periodoFinalizado): ?>
                <div class="asistencia-submit-bar text-right my-3">
                    <button type="submit" class="btn btn-primary px-4 btn-guardar-asist">
                        <i class="fas fa-save mr-1"></i> Guardar Asistencia
                    </button>
                </div>
            <?php else: ?>
                <div class="alert alert-warning my-3">No tiene permiso para editar la asistencia y/o el periodo académico ha finalizado.</div>
            <?php endif; ?>
        </form>
    </div>

    <style>
        /* Fijar las dos primeras columnas en escritorio */
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
        }

        .left-col {
            left: 0;
            min-width: 100px;
        }

        .left-col2 {
            left: 100px;
            min-width: 200px;
            z-index: 2;
        }

        #tabla-asistencia th,
        #tabla-asistencia td {
            vertical-align: middle;
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            /* En modo tabla móvil, dejar fija solo la columna de nombre compacta */
            #tabla-asistencia .left-col {
                position: static;
                min-width: 82px;
            }
            #tabla-asistencia .left-col2 {
                left: 0;
                min-width: 145px;
                max-width: 165px;
                white-space: normal;
                font-size: 12px;
                box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
            }
            #tabla-asistencia .select-asistencia,
            #tabla-asistencia .select-masivo {
                min-width: 52px;
                font-size: 14px;
            }
            .mobile-sticky-controls {
                position: sticky;
                top: 4px;
                z-index: 15;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            .asist-card {
                border: 1px solid #e3e6f0;
                border-radius: 10px;
                padding: 10px 12px;
                margin-bottom: 8px;
                background: #fff;
            }
            .asist-card-name {
                font-size: 14px;
                font-weight: 600;
                color: #1f2937;
                line-height: 1.25;
            }
            .asist-pill-btn {
                min-width: 44px;
                min-height: 38px;
                font-weight: 700;
                font-size: 14px;
            }
            .asistencia-submit-bar {
                position: sticky;
                bottom: 8px;
                z-index: 20;
            }
            .asistencia-submit-bar .btn-guardar-asist {
                width: 100%;
                padding-top: 10px;
                padding-bottom: 10px;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(0, 86, 179, 0.25);
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var limiteInasistencia = <?= (int)$limiteInasistencia ?>;
            var totalSesiones = <?= count($sesiones) ?>;
            var periodoFinalizado = <?= $periodoFinalizado ? 'true' : 'false' ?>;

            $('.select-masivo').on('change', function() {
                var sesion = $(this).data('sesion');
                var valor = $(this).val();
                $('select.select-asistencia[data-sesion="' + sesion + '"]').val(valor);
                actualizarPorcentajesYCards();
            });

            $('#tabla-asistencia').on('change', 'select.select-asistencia', function() {
                actualizarPorcentajesYCards();
            });

            var btnModoSesion = document.getElementById('btn-asist-modo-sesion');
            var btnModoTabla = document.getElementById('btn-asist-modo-tabla');
            var panelMovil = document.getElementById('mobile-asistencia-panel');
            var wrapperTabla = document.getElementById('wrapper-tabla-asistencia');
            var sesionPicker = document.getElementById('mobile-sesion-picker');
            var buscador = document.getElementById('mobile-buscar-asist');
            var cardsContainer = document.getElementById('mobile-asistencia-cards');

            function setModoMovil(modo) {
                if (!panelMovil || !wrapperTabla) return;
                if (modo === 'tabla') {
                    panelMovil.classList.add('d-none');
                    wrapperTabla.classList.remove('d-none');
                    btnModoTabla.classList.add('active', 'btn-primary');
                    btnModoTabla.classList.remove('btn-outline-primary');
                    btnModoSesion.classList.remove('active', 'btn-primary');
                    btnModoSesion.classList.add('btn-outline-primary');
                } else {
                    panelMovil.classList.remove('d-none');
                    wrapperTabla.classList.add('d-none');
                    btnModoSesion.classList.add('active', 'btn-primary');
                    btnModoSesion.classList.remove('btn-outline-primary');
                    btnModoTabla.classList.remove('active', 'btn-primary');
                    btnModoTabla.classList.add('btn-outline-primary');
                    renderCardsMovil();
                }
            }

            if (btnModoSesion && btnModoTabla) {
                btnModoSesion.addEventListener('click', function() { setModoMovil('sesion'); });
                btnModoTabla.addEventListener('click', function() { setModoMovil('tabla'); });
            }

            function recalcularFila($tr) {
                var faltas = 0;
                if (periodoFinalizado) {
                    $tr.find('.valor-asistencia-readonly').each(function() {
                        if ($(this).text().trim() === 'F') faltas++;
                    });
                } else {
                    $tr.find('select.select-asistencia').each(function() {
                        if ($(this).val() === 'F') faltas++;
                    });
                }
                var porc = totalSesiones ? Math.round((faltas * 100) / totalSesiones) : 0;
                var $tdPorc = $tr.find('.td-porc-faltas');
                $tdPorc.text(porc + '%');
                $tdPorc.toggleClass('text-danger font-weight-bold', porc >= limiteInasistencia);
                return porc;
            }

            function renderCardsMovil() {
                if (!cardsContainer || !sesionPicker) return;
                var sesionId = sesionPicker.value;
                var filtro = (buscador ? buscador.value : '').trim().toLowerCase();
                var html = '';
                var cntP = 0, cntF = 0, cntV = 0;

                $('#tabla-asistencia tbody tr').each(function() {
                    var $tr = $(this);
                    var orden = $tr.data('orden');
                    var dni = String($tr.data('dni') || '');
                    var nombre = String($tr.data('nombre') || '');
                    var idDetalle = $tr.data('id-detalle');
                    var porc = recalcularFila($tr);

                    var val = '';
                    if (periodoFinalizado) {
                        var raw = $tr.find('.valor-asistencia-readonly[data-sesion="' + sesionId + '"]').text().trim();
                        val = (raw === 'P' || raw === 'F') ? raw : '';
                    } else {
                        val = $tr.find('select.select-asistencia[data-sesion="' + sesionId + '"]').val() || '';
                    }

                    if (val === 'P') cntP++;
                    else if (val === 'F') cntF++;
                    else cntV++;

                    if (filtro && nombre.toLowerCase().indexOf(filtro) === -1 && dni.toLowerCase().indexOf(filtro) === -1) {
                        return;
                    }

                    var badgePorcClass = porc >= limiteInasistencia ? 'badge-danger' : 'badge-light border text-dark';
                    var cardBorder = val === 'P' ? 'border-left: 4px solid #28a745;' : (val === 'F' ? 'border-left: 4px solid #dc3545;' : '');

                    var controlesHtml = '';
                    if (periodoFinalizado) {
                        var lbl = val === 'P' ? '<span class="badge badge-success px-3 py-2">Presente (P)</span>' :
                                  (val === 'F' ? '<span class="badge badge-danger px-3 py-2">Falta (F)</span>' :
                                  '<span class="badge badge-secondary px-3 py-2">Sin registro</span>');
                        controlesHtml = lbl;
                    } else {
                        controlesHtml =
                            '<div class="btn-group btn-group-sm" role="group">' +
                                '<button type="button" class="btn asist-pill-btn js-card-asist ' + (val === 'P' ? 'btn-success' : 'btn-outline-success') + '" data-id-detalle="' + idDetalle + '" data-val="P">P</button>' +
                                '<button type="button" class="btn asist-pill-btn js-card-asist ' + (val === 'F' ? 'btn-danger' : 'btn-outline-danger') + '" data-id-detalle="' + idDetalle + '" data-val="F">F</button>' +
                                '<button type="button" class="btn asist-pill-btn js-card-asist ' + (val === '' ? 'btn-secondary' : 'btn-outline-secondary') + '" data-id-detalle="' + idDetalle + '" data-val="">—</button>' +
                            '</div>';
                    }

                    html +=
                        '<div class="asist-card d-flex justify-content-between align-items-center" style="' + cardBorder + '">' +
                            '<div class="pr-2" style="min-width: 0;">' +
                                '<div class="asist-card-name">' + orden + '. ' + escapeHtml(nombre) + '</div>' +
                                '<div class="small text-muted mt-1">' +
                                    'DNI: ' + escapeHtml(dni) + ' ' +
                                    '<span class="badge ' + badgePorcClass + ' ml-1">Inasist: ' + porc + '%</span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="flex-shrink-0">' + controlesHtml + '</div>' +
                        '</div>';
                });

                if (!html) {
                    html = '<div class="text-center text-muted py-3">No se encontraron estudiantes con ese filtro.</div>';
                }

                cardsContainer.innerHTML = html;
                var elP = document.getElementById('cnt-p');
                var elF = document.getElementById('cnt-f');
                var elV = document.getElementById('cnt-v');
                if (elP) elP.textContent = cntP;
                if (elF) elF.textContent = cntF;
                if (elV) elV.textContent = cntV;
            }

            function actualizarPorcentajesYCards() {
                $('#tabla-asistencia tbody tr').each(function() {
                    recalcularFila($(this));
                });
                if (panelMovil && !panelMovil.classList.contains('d-none')) {
                    renderCardsMovil();
                }
            }

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            if (sesionPicker) {
                sesionPicker.addEventListener('change', renderCardsMovil);
            }
            if (buscador) {
                buscador.addEventListener('input', renderCardsMovil);
            }

            $(document).on('click', '.js-card-asist', function() {
                var idDetalle = $(this).data('id-detalle');
                var val = $(this).data('val');
                var sesionId = sesionPicker ? sesionPicker.value : '';
                if (!sesionId) return;

                var $sel = $('select.select-asistencia[name="asistencia[' + idDetalle + '][' + sesionId + ']"]');
                $sel.val(val);
                actualizarPorcentajesYCards();
            });

            $(document).on('click', '.js-mobile-masivo', function() {
                var val = $(this).data('val');
                var sesionId = sesionPicker ? sesionPicker.value : '';
                if (!sesionId) return;

                var $masivo = $('.select-masivo[data-sesion="' + sesionId + '"]');
                if ($masivo.length) {
                    $masivo.val(val).trigger('change');
                } else {
                    $('select.select-asistencia[data-sesion="' + sesionId + '"]').val(val);
                    actualizarPorcentajesYCards();
                }
            });

            renderCardsMovil();
        });
    </script>
<?php else: ?>
    <p>No tiene permisos para editar este sílabo o el periodo ya culminó.</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
