<?php
/**
 * Variables disponibles (inyectadas por el controlador):
 * - $cab    : ['programa','plan','semestre','turno','seccion','periodo'] (si getCabeceraNomina devolvió)
 * - $semana : [
 *      'n' => int,
 *      'desde' => 'Y-m-d',
 *      'hasta' => 'Y-m-d',
 *      'rows' => [
 *          ['fecha'=>'Y-m-d','ud'=>'','tema'=>'','docente'=>'','firma'=>'','hora_ini'=>'HH:MM','hora_fin'=>'HH:MM'],
 *          ...
 *      ]
 *   ]
 */
$programa = $cab['programa']  ?? '-';
$plan     = $cab['plan']      ?? '-';
$semNom   = $cab['semestre']  ?? '-';
$turno    = $cab['turno']     ?? '-';
$seccion  = $cab['seccion']   ?? '-';
$periodo  = $cab['periodo']   ?? '-';
?>
<style>
    .rep-title { margin: 0 0 4px 0; font-size: 14px; font-weight: bold; }
    .rep-meta  { margin: 0 0 6px 0; font-size: 10px; }
    .rep-week  { margin: 0 0 8px 0; font-size: 10px; }
    table.rep { width: 100%; border-collapse: collapse; }
    table.rep th, table.rep td { border: 1px solid #000; padding: 4px; font-size: 10px; }
    table.rep thead th { text-align: center; font-weight: bold; }
    .txt-center { text-align: center; }
    .nowrap { white-space: nowrap; }
</style>

<h3 class="rep-title">CONTROL DIARIO DE CLASES</h3>
<div class="rep-meta">
    Programa: <b><?= htmlspecialchars($programa) ?></b> &nbsp;|&nbsp;
    Plan: <b><?= htmlspecialchars($plan) ?></b> &nbsp;|&nbsp;
    Periodo Académico: <b><?= htmlspecialchars($semNom) ?></b> &nbsp;|&nbsp;
    Turno: <b><?= htmlspecialchars($turno) ?></b> &nbsp;|&nbsp;
    Sección: <b><?= htmlspecialchars($seccion) ?></b> &nbsp;|&nbsp;
    Periodo Lectivo: <b><?= htmlspecialchars($periodo) ?></b>
</div>
<div class="rep-week">
    Semana <b><?= htmlspecialchars($semana['n']) ?></b> —
    <?= htmlspecialchars($semana['desde']) ?> al <?= htmlspecialchars($semana['hasta']) ?>
</div>

<table class="rep">
    <thead>
        <tr>
            <th width="15%">Fecha</th>
            <th width="20%">Unidad Didáctica</th>
            <th width="25%">Tema</th>
            <th width="15%">Docente</th>
            <th width="15%">Firma</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($semana['rows'])): ?>
        <tr>
            <td class="txt-center" colspan="5">Sin clases programadas L–V.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($semana['rows'] as $r): ?>
            <tr>
                <td class="nowrap"  width="15%">
                    <?= htmlspecialchars($r['fecha']) ?>
                    <?php /* Si deseas mostrar horas:
                    <div style="font-size:9px;">
                        <?= htmlspecialchars(($r['hora_ini'] ?? '').($r['hora_fin'] ? ' - '.$r['hora_fin'] : '')) ?>
                    </div> */ ?>
                </td>
                <td width="20%"><?= htmlspecialchars($r['ud']) ?></td>
                <td width="25%"><?= htmlspecialchars($r['tema']) ?></td>
                <td width="15%"><?= htmlspecialchars($r['docente']) ?></td>
                <td width="15%">&nbsp;</td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
