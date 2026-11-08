<?php
$fechaHoy = date('d/m/Y H:i:s');
$usuarioEmisor = $_SESSION['sigi_user_name'] ?? (\Core\Auth::user()['sigi_user_name'] ?? 'Administrador de Bolsa');
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #1e293b;
        font-size: 8.5pt;
    }
    .header-table {
        width: 100%;
        margin-bottom: 8px;
        border-bottom: 2px solid #003366;
        padding-bottom: 6px;
    }
    .inst-title {
        font-size: 12pt;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .inst-sub {
        font-size: 8pt;
        color: #64748b;
    }
    .report-title {
        font-size: 11pt;
        font-weight: bold;
        color: #003366;
        text-align: center;
        margin-top: 5px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .report-subtitle {
        font-size: 8pt;
        color: #475569;
        text-align: center;
        margin-bottom: 8px;
    }
    .meta-table {
        width: 100%;
        margin-bottom: 8px;
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
        width: 15%;
    }
    .meta-val {
        color: #0f172a;
        width: 35%;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    .data-table th {
        background-color: #003366;
        color: #ffffff;
        font-size: 7.5pt;
        font-weight: bold;
        text-align: center;
        padding: 5px 3px;
        border: 1px solid #003366;
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
    .signature-table {
        width: 100%;
        margin-top: 30px;
    }
    .signature-table td {
        text-align: center;
        font-size: 8pt;
        color: #334155;
    }
    .sig-line {
        border-top: 1px solid #475569;
        width: 220px;
        margin: 0 auto 4px auto;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL -->
<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 75%;">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "HUANTA"</div>
            <div class="inst-sub">Ley de Creación N° 23924 | Revalidado con R.D. N° 045-2006-ED | Huanta - Ayacucho</div>
            <div class="inst-sub">ÁREA DE VINCULACIÓN LABORAL, SEGUIMIENTO DE EGRESADOS Y BOLSA DE TRABAJO</div>
        </td>
        <td style="width: 25%; text-align: right; vertical-align: top;">
            <div style="font-size: 7.5pt; color: #64748b;">
                <strong>Fecha emisión:</strong> <?= $fechaHoy ?><br>
                <strong>Emitido por:</strong> <?= htmlspecialchars($usuarioEmisor) ?><br>
                <strong>Sistema:</strong> SIGI v2.5 | Directorio de Empresas
            </div>
        </td>
    </tr>
</table>

<div class="report-title">DIRECTORIO OFICIAL DE EMPRESAS ALIADAS DE BOLSA LABORAL</div>
<div class="report-subtitle">Padrón de empresas e instituciones registradas para vinculación y captación de talento</div>

<!-- CUADRO DE METADATOS Y FILTROS -->
<table class="meta-table" cellpadding="3" cellspacing="0">
    <tr>
        <td class="meta-label">Estado Registro:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($metadatos['estado'] ?? 'TODOS') ?></td>
        <td class="meta-label">Sede Institucional:</td>
        <td class="meta-val">SEDE HUANTA</td>
    </tr>
</table>

<!-- TABLA DE EMPRESAS -->
<table class="data-table" cellpadding="3" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 4%;">N°</th>
            <th style="width: 12%;">RUC</th>
            <th style="width: 26%;">Empresa / Razón Social</th>
            <th style="width: 22%;">Dirección Fiscal</th>
            <th style="width: 12%;">Teléfono</th>
            <th style="width: 16%;">Correo Electrónico</th>
            <th style="width: 8%;">Ofertas</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($empresas)): ?>
            <?php foreach ($empresas as $idx => $emp): 
                $bgClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
            ?>
                <tr class="<?= $bgClass ?>">
                    <td class="text-center font-bold" style="width: 4%;"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold" style="width: 12%;"><?= htmlspecialchars($emp['ruc'] ?? '-') ?></td>
                    <td class="text-left font-bold" style="width: 26%; color: #003366;">
                        <?= htmlspecialchars($emp['empresa']) ?>
                    </td>
                    <td class="text-left" style="width: 22%;"><?= htmlspecialchars($emp['direccion'] ?? 'No registrada') ?></td>
                    <td class="text-center" style="width: 12%;"><?= htmlspecialchars($emp['telefono'] ?? '-') ?></td>
                    <td class="text-left" style="width: 16%;"><?= htmlspecialchars($emp['email'] ?? '-') ?></td>
                    <td class="text-center font-bold" style="width: 8%;">
                        <?= (int)($emp['total_ofertas'] ?? 0) ?>
                        <span style="font-size: 6.5pt; color: #15803d;">(<?= (int)($emp['ofertas_vigentes'] ?? 0) ?> vig.)</span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                    No se encontraron empresas aliadas registradas para los filtros aplicados.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 8px; font-size: 8.5pt; font-weight: bold; color: #1e293b;">
    Total de Empresas Aliadas: <?= count($empresas) ?> empresa(s) registradas
</div>

<!-- BLOQUE DE FIRMAS -->
<table class="signature-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 50%;">
            <div style="height: 40px;"></div>
            <div class="sig-line"></div>
            <strong>COORDINACIÓN DE BOLSA DE TRABAJO</strong><br>
            Área de Alianzas y Relaciones Corporativas<br>
            IESTP HUANTA
        </td>
        <td style="width: 50%;">
            <div style="height: 40px;"></div>
            <div class="sig-line"></div>
            <strong>SECRETARÍA ACADÉMICA / DIRECCIÓN</strong><br>
            Sistema Integrado de Gestión Institucional (SIGI)<br>
            IESTP HUANTA
        </td>
    </tr>
</table>
