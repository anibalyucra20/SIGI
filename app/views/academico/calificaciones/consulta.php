<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if ($ver && \Core\Auth::esEstudianteAcademico()): ?>
<style>
    table.det {
        font-size: 10px;
        width: 100%;
        border-collapse: collapse;
    }

    table.det th,
    table.det td {
        border: 1px solid #777;
        padding: 3px;
        text-align: center;
    }

    table.det th.ud {
        text-align: left;
        background: #2b3d51;
        color: #fff;
    }

    .blue {
        color: #005dff;
    }

    .red {
        color: #d60000;
    }

    @media (max-width: 767.98px) {
        .consulta-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
    }
</style>
<div class="card p-2 p-md-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-size: clamp(1rem, 2.5vw, 1.2rem);">MIS CALIFICACIONES Y ASISTENCIA</h5>
        <div class="d-md-none btn-group btn-group-sm" role="group" aria-label="Modo de vista">
            <button type="button" class="btn btn-primary active" id="btn-cons-modo-cards">Tarjetas</button>
            <button type="button" class="btn btn-outline-primary" id="btn-cons-modo-tabla">Tabla</button>
        </div>
    </div>

    <!-- PANEL MÓVIL (< 768px) -->
    <div id="mobile-consulta-panel" class="d-md-none">
        <ul class="nav nav-pills nav-fill mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#mob-tab-calif" role="tab">Calificaciones</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#mob-tab-asist" role="tab">Asistencia</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="mob-tab-calif" role="tabpanel">
                <?php foreach ($udOrder as $ud => $idx):
                    $prom = $proms[$idx] ?? '';
                    if ($prom != '' && ($tablaCalif[$ud]['recuperacion'] ?? '') != '') {
                        $recuperacion = $tablaCalif[$ud]['recuperacion'];
                        $prom = $recuperacion;
                    } else {
                        $recuperacion = '';
                    }
                    $clsProm = ($prom !== '' && $prom < 13) ? 'badge-danger' : 'badge-primary';
                ?>
                    <div class="consulta-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="font-weight-bold text-dark pr-2" style="font-size: 14px;"><?= htmlspecialchars($ud) ?></div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-muted" style="font-size: 10px;">PROM.</div>
                                <span class="badge <?= $prom !== '' ? $clsProm : 'badge-secondary' ?> px-2 py-1" style="font-size: 13px;">
                                    <?= $prom !== '' ? $prom : '—' ?>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap" style="gap: 5px;">
                            <?php for ($n = 1; $n <= 12; $n++):
                                $nota = $tablaCalif[$ud][$n] ?? '';
                                if ($nota === '') continue;
                                $cls = ($nota < 13) ? 'text-danger font-weight-bold' : 'text-primary font-weight-bold';
                            ?>
                                <div class="border rounded px-2 py-1 bg-light text-center" style="min-width: 52px;">
                                    <div class="text-muted" style="font-size: 10px;">I.L. <?= $n ?></div>
                                    <div class="<?= $cls ?>" style="font-size: 13px;"><?= $nota ?></div>
                                </div>
                            <?php endfor; ?>
                            <?php if ($recuperacion !== ''): ?>
                                <div class="border rounded px-2 py-1 bg-light text-center" style="min-width: 60px;">
                                    <div class="text-muted" style="font-size: 10px;">Recup.</div>
                                    <div class="<?= $recuperacion < 13 ? 'text-danger font-weight-bold' : 'text-primary font-weight-bold' ?>" style="font-size: 13px;"><?= $recuperacion ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tab-pane fade" id="mob-tab-asist" role="tabpanel">
                <?php foreach ($udOrder as $ud => $idx):
                    $faltas = 0;
                    $total = 0;
                    for ($w = 1; $w <= 17; $w++) {
                        $val = $tablaAsist[$ud][$w] ?? '';
                        if ($val !== '') {
                            $total++;
                            if ($val == 'F') $faltas++;
                        }
                    }
                    $porc = $total ? round(100 * $faltas / $total) : 0;
                    $clsPorc = $porc >= 30 ? 'badge-danger' : 'badge-success';
                ?>
                    <div class="consulta-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="font-weight-bold text-dark pr-2" style="font-size: 14px;"><?= htmlspecialchars($ud) ?></div>
                            <span class="badge <?= $total ? $clsPorc : 'badge-secondary' ?> px-2 py-1 flex-shrink-0">
                                Inasist: <?= $total ? $porc . '%' : '—' ?>
                            </span>
                        </div>

                        <div class="d-flex flex-wrap" style="gap: 4px;">
                            <?php for ($w = 1; $w <= 17; $w++):
                                $val = $tablaAsist[$ud][$w] ?? '';
                                if ($val === '') continue;
                                $cls = ($val == 'F') ? 'badge-danger' : 'badge-primary';
                            ?>
                                <span class="badge <?= $cls ?> px-2 py-1">S<?= $w ?>: <?= $val ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- TABLA COMPLETA (escritorio o modo tabla móvil) -->
    <div id="wrapper-tabla-consulta" class="d-none d-md-block">
        <h6>CALIFICACIONES – UNIDADES DIDÁCTICAS</h6>
        <div class="table-responsive mb-3">
            <table class="det">
                <thead>
                    <tr>
                        <th class="ud">Unidad Didáctica</th>
                        <?php for ($i = 1; $i <= 12; $i++): ?><th>I.L. <?= $i ?></th><?php endfor; ?>
                        <th>Recup.</th>
                        <th>Prom.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($udOrder as $ud => $idx): ?>
                        <tr>
                            <td class="ud"><?= $ud ?></td>
                            <?php
                            for ($n = 1; $n <= 12; $n++) {
                                $nota = $tablaCalif[$ud][$n] ?? '';
                                echo '<td>' . ($nota !== '' ? ($nota < 13 ? '<span class="red">' . $nota . '</span>' : '<span class="blue">' . $nota . '</span>') : '') . '</td>';
                            }
                            $prom = $proms[$idx] ?? '';
                            if ($prom != '') {
                                if (($tablaCalif[$ud]['recuperacion'] != '')) {
                                    $recuperacion = $tablaCalif[$ud]['recuperacion'];
                                    $prom = $recuperacion;
                                } else {
                                    $recuperacion = '';
                                }
                            } else {
                                $prom = '';
                                $recuperacion = '';
                            }
                            ?>
                            <td><?= $recuperacion !== '' ? ($recuperacion < 13 ? '<span class="red">' . $recuperacion . '</span>' : '<span class="blue">' . $recuperacion . '</span>') : '' ?></td>
                            <td><?= $prom !== '' ? ($prom < 13 ? '<span class="red">' . $prom . '</span>' : '<span class="blue">' . $prom . '</span>') : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h6 class="mt-3">ASISTENCIA</h6>
        <div class="table-responsive">
            <table class="det">
                <thead>
                    <tr>
                        <th class="ud">Unidad Didáctica</th>
                        <?php for ($w = 1; $w <= 17; $w++): ?><th>Semana <?= $w ?></th><?php endfor; ?>
                        <th>% Inasistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($udOrder as $ud => $idx):
                        $faltas = 0;
                        $total = 0; ?>
                        <tr>
                            <td class="ud"><?= $ud ?></td>
                            <?php for ($w = 1; $w <= 17; $w++):
                                $val = $tablaAsist[$ud][$w] ?? '';
                                if ($val !== '') {
                                    $total++;
                                    if ($val == 'F') $faltas++;
                                }
                                echo '<td>' . ($val == 'F' ? '<span class="red">F</span>' : ($val == 'P' ? '<span class="blue">P</span>' : '')) . '</td>';
                            endfor;
                            $porc = $total ? round(100 * $faltas / $total) . '%' : '';
                            ?>
                            <td><?= $porc ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    (function() {
        var btnCards = document.getElementById('btn-cons-modo-cards');
        var btnTabla = document.getElementById('btn-cons-modo-tabla');
        var panelMovil = document.getElementById('mobile-consulta-panel');
        var wrapperTabla = document.getElementById('wrapper-tabla-consulta');

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
    })();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
