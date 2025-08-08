
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
$pdf->Image($logoPath, 165, 10, 30, 10); // (x, y, width en mm)
ob_start();
?>
<style>
  table{font-size:9px;border-collapse:collapse;width:100%}
  th,td{border:1px solid #000;padding:2px 4px;text-align:center}
  td.name{text-align:left}
</style>
<br>
<h4 align="center">REPORTE DE PRIMEROS PUESTOS – <?= htmlspecialchars($info['programa']) ?></h4>
<p><b>Periodo Académico:</b> <?= $info['semestre'] ?> |
   <b>Turno:</b> <?= $info['turno'] ?> |
   <b>Sección:</b> <?= $info['seccion'] ?> |
   <b>Periodo:</b> <?= $info['periodo'] ?></p>

<table>
  <thead>
    <tr>
      <th width="10%">Orden de Mérito</th>
      <th width="15%">DNI</th>
      <th width="45%" style="text-align:left">APELLIDOS Y NOMBRES</th>
      <th width="15%">PUNTAJE TOTAL CRÉDITOS</th>
      <th width="15%">PROMEDIO PONDERADO</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($estudiantes as $e): ?>
    <tr>
      <td width="10%"><?= $e['ranking'] ?></td>
      <td width="15%"><?= $e['dni'] ?></td>
      <td width="45%" class="name"><?= htmlspecialchars($e['apellidos_nombres']) ?></td>
      <td width="15%"><?= $e['puntaje_credito'] ?></td>
      <td width="15%"><?= $e['promedio_ponderado'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
