<?php
$fechaHoy = date('d/m/Y H:i:s');
$usuarioEmisor = $_SESSION['sigi_user_name'] ?? (\Core\Auth::user()['sigi_user_name'] ?? 'Administrador de Bolsa');
$estadosInfo = \App\Models\Bolsa\Postulaciones::getEstados();
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
    .badge-estado-1 {
        color: #b45309;
        font-weight: bold;
    }
    .badge-estado-2 {
        color: #0284c7;
        font-weight: bold;
    }
    .badge-estado-3 {
        color: #15803d;
        font-weight: bold;
    }
    .badge-estado-4 {
        color: #b91c1c;
        font-weight: bold;
    }
    .kpi-table {
        width: 100%;
        margin-top: 8px;
        border-collapse: collapse;
    }
    .kpi-box {
        text-align: center;
        padding: 5px;
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        font-size: 8pt;
    }
    .kpi-num {
        font-size: 11pt;
        font-weight: bold;
        color: #003366;
    }
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
                <strong>Sistema:</strong> SIGI v2.5 | Bolsa Laboral
            </div>
        </td>
    </tr>
</table>

<div class="report-title">REPORTE CONSOLIDADO DE POSTULACIONES LABORALES</div>
<div class="report-subtitle">Padrón de estudiantes y egresados postulantes a convocatorias de empleo registradas</div>

<!-- CUADRO DE METADATOS Y FILTROS -->
<table class="meta-table" cellpadding="3" cellspacing="0">
    <tr>
        <td class="meta-label">Convocatoria:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($metadatos['oferta']) ?></td>
        <td class="meta-label">Empresa Solicitante:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($metadatos['empresa']) ?></td>
    </tr>
    <tr>
        <td class="meta-label">Programa / Carrera:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($metadatos['programa']) ?></td>
        <td class="meta-label">Estado de Selección:</td>
        <td class="meta-val font-bold" style="color: #003366;"><?= htmlspecialchars($metadatos['estado']) ?></td>
    </tr>
    <tr>
        <td class="meta-label">Periodo / Rango:</td>
        <td class="meta-val"><?= htmlspecialchars($metadatos['periodo']) ?></td>
        <td class="meta-label">Sede Institucional:</td>
        <td class="meta-val">SEDE HUANTA</td>
    </tr>
</table>

<!-- TABLA DE POSTULACIONES -->
<table class="data-table" cellpadding="3" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 3%;">N°</th>
            <th style="width: 9%;">DNI</th>
            <th style="width: 20%;">Apellidos y Nombres</th>
            <th style="width: 17%;">Programa de Estudios</th>
            <th style="width: 18%;">Convocatoria Laboral</th>
            <th style="width: 14%;">Empresa</th>
            <th style="width: 8%;">Fecha</th>
            <th style="width: 11%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($postulaciones)): ?>
            <?php foreach ($postulaciones as $idx => $row): 
                $bgClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
                $estId = (int)$row['estado'];
                $nomEstado = $estadosInfo[$estId]['nombre'] ?? 'En Proceso';
            ?>
                <tr class="<?= $bgClass ?>">
                    <td class="text-center font-bold" style="width: 3%;"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold" style="width: 9%;"><?= htmlspecialchars($row['dni'] ?? '-') ?></td>
                    <td class="text-left font-bold" style="width: 20%;">
                        <?= htmlspecialchars($row['apellidos_nombres']) ?>
                        <?php if (!empty($row['telefono'])): ?>
                            <br><span style="font-size: 6.5pt; color: #64748b;">Cel: <?= htmlspecialchars($row['telefono']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-left" style="width: 17%;"><?= htmlspecialchars($row['programa_nombre'] ?? 'General') ?></td>
                    <td class="text-left font-bold" style="width: 18%; color: #003366;"><?= htmlspecialchars($row['oferta_titulo']) ?></td>
                    <td class="text-left" style="width: 14%;"><?= htmlspecialchars($row['empresa_nombre'] ?? 'Confidencial') ?></td>
                    <td class="text-center" style="width: 8%;"><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                    <td class="text-center badge-estado-<?= $estId ?>" style="width: 11%;"><?= htmlspecialchars($nomEstado) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                    No se encontraron postulaciones registradas para los criterios y filtros seleccionados.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- RESUMEN ESTADÍSTICO -->
<table class="kpi-table" cellpadding="2" cellspacing="0">
    <tr>
        <td class="kpi-box" style="width: 20%;">
            <span class="kpi-num"><?= (int)($metricas['total'] ?? count($postulaciones)) ?></span><br>
            <strong>Total Postulaciones</strong>
        </td>
        <td class="kpi-box" style="width: 20%;">
            <span class="kpi-num" style="color: #b45309;"><?= (int)($metricas['pendientes'] ?? 0) ?></span><br>
            <strong>Pendientes / Revisión</strong>
        </td>
        <td class="kpi-box" style="width: 20%;">
            <span class="kpi-num" style="color: #0284c7;"><?= (int)($metricas['evaluacion'] ?? 0) ?></span><br>
            <strong>En Evaluación</strong>
        </td>
        <td class="kpi-box" style="width: 20%;">
            <span class="kpi-num" style="color: #15803d;"><?= (int)($metricas['seleccionados'] ?? 0) ?></span><br>
            <strong>Seleccionados</strong>
        </td>
        <td class="kpi-box" style="width: 20%;">
            <span class="kpi-num" style="color: #b91c1c;"><?= (int)($metricas['no_seleccionados'] ?? 0) ?></span><br>
            <strong>No Seleccionados</strong>
        </td>
    </tr>
</table>

<!-- BLOQUE DE FIRMAS -->
<table class="signature-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 50%;">
            <div style="height: 40px;"></div>
            <div class="sig-line"></div>
            <strong>COORDINACIÓN DE BOLSA DE TRABAJO</strong><br>
            Área de Inserción y Seguimiento al Egresado<br>
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
