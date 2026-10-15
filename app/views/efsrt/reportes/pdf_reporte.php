<?php
$sedeNombre = !empty($metadatos['sede']['nombre']) ? htmlspecialchars($metadatos['sede']['nombre']) : 'HUANTA';
$progNombre = !empty($metadatos['programa']['nombre']) ? htmlspecialchars($metadatos['programa']['nombre']) : 'TODOS LOS PROGRAMAS';
$progTipo   = !empty($metadatos['programa']['tipo']) ? htmlspecialchars($metadatos['programa']['tipo']) : 'MODULAR';
$planNombre = !empty($metadatos['plan']['nombre']) ? htmlspecialchars($metadatos['plan']['nombre']) : 'TODOS LOS PLANES';
$moduloDesc = !empty($metadatos['modulo']) ? 'MÓDULO ' . str_pad($metadatos['modulo']['nro_modulo'], 2, '0', STR_PAD_LEFT) . ' - ' . htmlspecialchars($metadatos['modulo']['descripcion']) : 'TODOS LOS MÓDULOS FORMATIVOS';
$periodoNom = !empty($metadatos['periodo']['nombre']) ? htmlspecialchars($metadatos['periodo']['nombre']) : 'TODOS';
$fechaHoy   = date('d/m/Y H:i:s');
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #1e293b;
        font-size: 9pt;
    }
    .header-table {
        width: 100%;
        margin-bottom: 8px;
        border-bottom: 2px solid #0284c7;
        padding-bottom: 6px;
    }
    .inst-title {
        font-size: 13pt;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .inst-sub {
        font-size: 8pt;
        color: #64748b;
    }
    .report-title {
        font-size: 11pt;
        font-weight: bold;
        color: #0369a1;
        text-align: center;
        margin-top: 6px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .report-subtitle {
        font-size: 8pt;
        color: #475569;
        text-align: center;
        margin-bottom: 10px;
    }
    .meta-table {
        width: 100%;
        margin-bottom: 10px;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
    }
    .meta-table td {
        padding: 4px 6px;
        font-size: 8pt;
        vertical-align: middle;
    }
    .meta-label {
        font-weight: bold;
        color: #334155;
        width: 18%;
    }
    .meta-val {
        color: #0f172a;
        width: 32%;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    .data-table th {
        background-color: #0284c7;
        color: #ffffff;
        font-size: 8pt;
        font-weight: bold;
        text-align: center;
        padding: 5px 3px;
        border: 1px solid #0284c7;
    }
    .data-table td {
        font-size: 7.5pt;
        padding: 4px 3px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }
    .row-even {
        background-color: #ffffff;
    }
    .row-odd {
        background-color: #f8fafc;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .badge-approved {
        background-color: #dcfce7;
        color: #166534;
        font-weight: bold;
        padding: 1px 4px;
        border-radius: 2px;
    }
    .footer-summary {
        margin-top: 10px;
        font-size: 8.5pt;
        font-weight: bold;
        color: #1e293b;
    }
    .signature-table {
        width: 100%;
        margin-top: 35px;
    }
    .signature-table td {
        text-align: center;
        font-size: 8pt;
        color: #334155;
    }
    .sig-line {
        border-top: 1px solid #475569;
        width: 200px;
        margin: 0 auto 4px auto;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL -->
<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 75%;">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "HUANTA"</div>
            <div class="inst-sub">Ley de Creación N° 23924 | Revalidado con R.D. N° 045-2006-ED | Huanta - Ayacucho</div>
            <div class="inst-sub">SECRETARÍA ACADÉMICA - ÁREA DE EXPERIENCIAS FORMATIVAS EN SITUACIONES REALES DE TRABAJO (EFSRT)</div>
        </td>
        <td style="width: 25%; text-align: right; vertical-align: top;">
            <div style="font-size: 7.5pt; color: #64748b;">
                <strong>Fecha emisión:</strong> <?= $fechaHoy ?><br>
                <strong>Sistema:</strong> SIGI v2.5<br>
                <strong>Estado:</strong> EXPEDIENTES VALIDADOS
            </div>
        </td>
    </tr>
</table>

<div class="report-title">NÓMINA DE EXPEDIENTES CULMINADOS Y VALIDADOS DE EFSRT</div>
<div class="report-subtitle">Constatación de cumplimiento de Experiencias Formativas en Situaciones Reales de Trabajo</div>

<!-- CUADRO DE METADATOS Y FILTROS -->
<table class="meta-table" cellpadding="3" cellspacing="0">
    <tr>
        <td class="meta-label">Sede:</td>
        <td class="meta-val font-bold"><?= $sedeNombre ?></td>
        <td class="meta-label">Periodo Académico:</td>
        <td class="meta-val font-bold"><?= $periodoNom ?></td>
    </tr>
    <tr>
        <td class="meta-label">Programa de Estudios:</td>
        <td class="meta-val font-bold"><?= $progNombre ?></td>
        <td class="meta-label">Tipo Plan de Estudios:</td>
        <td class="meta-val"><?= $progTipo ?></td>
    </tr>
    <tr>
        <td class="meta-label">Plan de Estudios:</td>
        <td class="meta-val font-bold"><?= $planNombre ?></td>
        <td class="meta-label">Módulo Formativo:</td>
        <td class="meta-val font-bold" style="color: #0369a1;"><?= $moduloDesc ?></td>
    </tr>
</table>

<!-- TABLA PRINCIPAL DE EXPEDIENTES -->
<table class="data-table" cellpadding="3" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 4%;">N°</th>
            <th style="width: 10%;">Tipo / N° Doc</th>
            <th style="width: 22%;">Apellidos y Nombres</th>
            <th style="width: 22%;">Empresa / Razón Social</th>
            <th style="width: 14%;">Docente Supervisor</th>
            <th style="width: 6%;">Horas</th>
            <th style="width: 6%;">Nota</th>
            <th style="width: 16%;">Constancia de Empresa</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($expedientes)): ?>
            <?php foreach ($expedientes as $i => $exp): ?>
                <?php $bgClass = ($i % 2 === 0) ? 'row-even' : 'row-odd'; ?>
                <tr class="<?= $bgClass ?>">
                    <td class="text-center font-bold" style="width: 4%;"><?= $i + 1 ?></td>
                    <td class="text-center" style="width: 10%;">
                        <?= htmlspecialchars($exp['estudiante_tipo_doc']) ?>: <strong><?= htmlspecialchars($exp['estudiante_dni']) ?></strong>
                    </td>
                    <td class="text-left font-bold" style="width: 22%;">
                        <?= htmlspecialchars($exp['estudiante_nombre']) ?>
                    </td>
                    <td class="text-left" style="width: 22%;">
                        <?= htmlspecialchars($exp['empresa_razon_social']) ?>
                        <?php if (!empty($exp['empresa_ruc'])): ?>
                            <br><span style="font-size: 6.5pt; color: #64748b;">RUC: <?= htmlspecialchars($exp['empresa_ruc']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-left" style="width: 14%;">
                        <?= !empty($exp['supervisor_nombre']) ? htmlspecialchars($exp['supervisor_nombre']) : '<span style="color:#94a3b8;">No asignado</span>' ?>
                    </td>
                    <td class="text-center font-bold" style="width: 6%;">
                        <?= !empty($exp['horas_convalidadas']) ? $exp['horas_convalidadas'] : $exp['horas_requeridas'] ?>
                    </td>
                    <td class="text-center font-bold" style="width: 6%; color: #0f766e;">
                        <?= !empty($exp['calificacion_final']) ? number_format($exp['calificacion_final'], 1) : '-' ?>
                    </td>
                    <td class="text-left font-bold" style="width: 16%; color: #0369a1;">
                        <?= htmlspecialchars($exp['nombre_archivo_descarga']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                    No se encontraron expedientes con estado finalizado y validado para los filtros seleccionados.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- RESUMEN ESTADÍSTICO -->
<div class="footer-summary">
    Total de Expedientes Validados y Culminados: <strong><?= count($expedientes) ?> estudiante(s)</strong> | 
    Total de Horas EFSRT Registradas: <strong><?= array_sum(array_map(function($e) { return (int)($e['horas_convalidadas'] ?: $e['horas_requeridas']); }, $expedientes)) ?> horas</strong>
</div>

<!-- BLOQUE DE FIRMAS -->
<table class="signature-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 50%;">
            <div style="height: 45px;"></div>
            <div class="sig-line"></div>
            <strong>COORDINACIÓN DE PROGRAMA DE ESTUDIOS</strong><br>
            <?= $progNombre ?><br>
            IESTP HUANTA
        </td>
        <td style="width: 50%;">
            <div style="height: 45px;"></div>
            <div class="sig-line"></div>
            <strong>SECRETARÍA ACADÉMICA</strong><br>
            Área de Experiencias Formativas (EFSRT)<br>
            IESTP HUANTA
        </td>
    </tr>
</table>
