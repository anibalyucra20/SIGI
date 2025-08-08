<?php
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
if ($datosSistema['logo'] != '') {
    $logoPath = __DIR__ . '/../../../../public/images/' . $datosSistema['logo'];
} else {
    $logoPath = __DIR__ . '/../../../../public/img/logo_completo.png';
}
$pdf->Image($logoMineduPath, 15, 10, 30, 8); // (x, y, width en mm)
$pdf->Image($logoPath, 250, 10, 30, 10); // (x, y, width en mm)
ob_start();
?>
<style>
    table {
        font-size: 8px;
        border-collapse: collapse;
        width: 100%
    }

    th,
    td {
        border: 1px solid #000;
        padding: 2px 4px;
        text-align: center
    }

    th.rotate {
        writing-mode: vertical-rl;
        transform: rotate(180deg)
    }

    .rojo {
        color: red;
    }

    .azul {
        color: blue;
    }
</style>
<br>
<h4 align="center">REPORTE DETALLADO – <?= htmlspecialchars($info['programa']) ?></h4>
<p><b>Plan:</b> <?= $info['plan'] ?> |
    <b>Módulo:</b> <?= $info['modulo'] ?> |
    <b>Semestre:</b> <?= $info['semestre'] ?> |
    <b>Turno:</b> <?= $info['turno'] ?> |
    <b>Sección:</b> <?= $info['seccion'] ?> |
    <b>Periodo:</b> <?= $info['periodo'] ?>
</p>

<table>
    <thead>
        <tr>
            <th rowspan="2" width="2%">N°</th>
            <th rowspan="2" width="7%">DNI</th>
            <th rowspan="2" style="text-align:left" width="24%">Apellidos y Nombres</th>
            <?php
            $contar_celdas = 0;
            foreach ($uds as $ud):
                $contar_celdas += count($ud['nros_calif']);
            endforeach;
            foreach ($uds as $ud):
            ?>
                <th colspan="<?= count($ud['nros_calif']) ?>" width="<?= (65 / $contar_celdas) * count($ud['nros_calif']) ?>%">
                    <?= htmlspecialchars($ud['nombre']) ?>
                </th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <?php foreach ($uds as $ud): ?>
                <?php foreach ($ud['nros_calif'] as $c): ?>
                    <th width="<?= 65 / $contar_celdas ?>%"><?= $c ?></th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php $n = 1;
        foreach ($estudiantes as $e): ?>
            <tr>
                <td width="2%"><?= $n++ ?></td>
                <td width="7%"><?= $e['dni'] ?></td>
                <td style="text-align:left" width="24%"><?= htmlspecialchars($e['apellidos_nombres']) ?></td>
                <?php foreach ($uds as $ud): ?>
                    <?php foreach ($ud['nros_calif'] as $c): ?>
                        <td width="<?= 65 / $contar_celdas ?>%"  class="<?= ($e['notas'][$ud['id']][$c] < 13) ? "rojo" : "azul";  ?>"><?= $e['notas'][$ud['id']][$c] ?? '' ?></td>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>