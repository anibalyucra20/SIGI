<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if($ver && \Core\Auth::esEstudianteAcademico()): ?>
<style>
    table.det {
        font-size: 10px;
        width: 100%;
        border-collapse: collapse
    }

    table.det th,
    table.det td {
        border: 1px solid #777;
        padding: 3px;
        text-align: center
    }

    table.det th.ud {
        text-align: left;
        background: #2b3d51;
        color: #fff
    }
    .blue {
        color: #005dff;
    }
    .red {
        color: #d60000;
    }
</style>
<div class="card p-2">
    <h5>CALIFICACIONES – UNIDADES DIDÁCTICAS</h5>
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

    <h5 class="mt-4">ASISTENCIA</h5>
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
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>