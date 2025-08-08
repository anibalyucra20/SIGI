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
    .titulo {
        font-size: 14px;
        font-weight: bold;
        text-align: center;
    }

    .subtitulo {
        font-size: 11px;
        font-weight: bold;
        margin-top: 6px;
    }

    table.cab {
        font-size: 10px;
        width: 100%;
    }

    table.list {
        border-collapse: collapse;
        font-size: 9px;
        width: 100%;
    }

    table.list th,
    table.list td {
        border: 1px solid #000;
        padding: 2px;
        text-align: center;
    }

    table.list th.nombre,
    table.list td.nombre {
        text-align: left;
        padding-left: 4px;
    }
</style>

<br>
<!-- ────────── ENCABEZADO ────────── -->
<div class="titulo">NÓMINA DE MATRÍCULA</div>

<table class="cab">
    <tr>
        <td width="15%"><b>Programa de Estudios</b></td>
        <td width="80%">: <?= htmlspecialchars($info['programa']) ?></td>

    </tr>
    <tr>
        <td><b>Plan de Estudios</b></td>
        <td>: <?= htmlspecialchars($info['plan']) ?></td>
    </tr>
    <tr>
        <td><b>Módulo Formativo</b></td>
        <td>: <?= htmlspecialchars($info['modulo']) ?></td>
    </tr>
    <tr>
        <td><b>Periodo Académico</b></td>
        <td>: <?= htmlspecialchars($info['semestre']) ?></td>
    </tr>
    <tr>
        <td><b>Turno</b></td>
        <td>: <?= htmlspecialchars($info['turno']) ?></td>
    </tr>
    <tr>
        <td><b>Sección</b></td>
        <td>: <?= htmlspecialchars($info['seccion']) ?></td>
    </tr>
    <tr>
        <td><b>Periodo Académico</b></td>
        <td colspan="3">: <?= htmlspecialchars($info['periodo']) ?></td>
    </tr>
</table>

<!-- ────────── TABLA PRINCIPAL ────────── -->
<br>
<div class="subtitulo">Lista de estudiantes</div>
<table class="list">
    <thead>
        <tr style="text-align: center; font-weight: bold;">
            <th width="4%" rowspan="2">N°</th>
            <th width="10%" rowspan="2">DNI</th>
            <th width="30%" rowspan="2">APELLIDOS Y NOMBRES</th>
            <th colspan="<?=count($unidades) ?>" width="56%">UNIDADES DIDÁCTICAS</th>
        </tr>
        <tr style="text-align: center;">
            <?php foreach ($unidades as $u): ?>
                <th width="<?= 56 / count($unidades) ?>%" style="font-size: 7px;"><?= htmlspecialchars($u['nombre']) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php $n = 1;
        foreach ($estudiantes as $e): ?>
            <tr>
                <td width="4%"><?= $n++ ?></td>
                <td width="10%"><?= htmlspecialchars($e['dni']) ?></td>
                <td width="30%" class="nombre"><?= htmlspecialchars($e['apellidos_nombres']) ?></td>
                <?php foreach ($unidades as $u): ?>
                    <td width="<?= 56 / count($unidades) ?>%"><?= isset($e['uds'][$u['id']]) ? 'SI' : 'NO' ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
