
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
    transform: rotate(180deg);
  }
  .rojo {
    color: red;
  }

  .azul {
    color: blue;
  }
</style>
<br>
<h4 align="center">
  REPORTE CONSOLIDADO – <?= htmlspecialchars($info['programa']) ?>
</h4>

<p>
  <b>Plan:</b> <?= $info['plan'] ?> |
  <b>Módulo:</b> <?= $info['modulo'] ?> |
  <b>Semestre:</b> <?= $info['semestre'] ?> |
  <b>Turno:</b> <?= $info['turno'] ?> |
  <b>Sección:</b> <?= $info['seccion'] ?> |
  <b>Periodo:</b> <?= $info['periodo'] ?>
</p>

<table>
  <thead>
    <tr>
      <th rowspan="2" width="3%">N°</th>
      <th rowspan="2" width="7%">DNI</th>
      <th rowspan="2" style="text-align:left" width="24%">Apellidos y Nombres</th>
      <th colspan="<?= count($uds) ?>" width="40%">UNIDADES DIDACTICAS</th>
      <td width="4%" rowspan="2">Ptj. Total</td>
      <td width="4%" rowspan="2">Ptj. Crédito</td>
      <td width="18%" rowspan="2">Condición</td>
    </tr>
    <tr>
      <?php /* columnas fijas repetidas ya tienen rowspan */ ?>
      <?php foreach ($uds as $ud): ?>
        <th class="rotate" width="<?= 40 / count($uds) ?>%"><?= htmlspecialchars($ud['nombre']) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php $n = 1;
    foreach ($estudiantes as $est): ?>
      <tr>
        <td width="3%"><?= $n++ ?></td>
        <td width="7%"><?= $est['dni'] ?></td>
        <td style="text-align:left" width="24%"><?= htmlspecialchars($est['apellidos_nombres']) ?></td>

        <?php foreach ($uds as $ud): ?>
          <?php $nota = $est['promedios'][$ud['id']] ?? null; ?>
          <td width="<?= 40 / count($uds) ?>%" class="<?= ($est['promedios'][$ud['id']] < 13) ? "rojo" : "azul";  ?>">
            <?= $est['promedios'][$ud['id']] ?? '' ?>
          </td>
        <?php endforeach; ?>
        <td width="4%"><?= $est['puntaje_total'] ?></td>
        <td width="4%"><?= $est['puntaje_credito'] ?></td>
        <td width="18%"><?= $est['condicion'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>