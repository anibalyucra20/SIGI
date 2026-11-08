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
        font-size: 11pt;
        font-weight: bold;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .inst-sub {
        font-size: 7.5pt;
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
    .badge-estado-1 { color: #b45309; font-weight: bold; }
    .badge-estado-2 { color: #0284c7; font-weight: bold; }
    .badge-estado-3 { color: #15803d; font-weight: bold; }
    .badge-estado-4 { color: #b91c1c; font-weight: bold; }
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
            <div class="inst-sub">ÁREA DE VINCULACIÓN LABORAL, SEGUIMIENTO DE EGRESADOS Y BOLSA DE TRABAJO</div>
        </td>
        <td style="width: 25%; text-align: right; vertical-align: top;">
            <div style="font-size: 7.5pt; color: #64748b;">
                <strong>Fecha:</strong> <?= $fechaHoy ?><br>
                <strong>Emitido por:</strong> <?= htmlspecialchars($usuarioEmisor) ?><br>
                <strong>Sistema:</strong> SIGI v2.5
            </div>
        </td>
    </tr>
</table>

<div class="report-title">EXPEDIENTE DE CANDIDATOS POSTULANTES</div>
<div class="report-subtitle">Convocatoria: <?= htmlspecialchars($oferta['titulo']) ?></div>

<!-- FICHA DE LA OFERTA -->
<table class="meta-table" cellpadding="3" cellspacing="0">
    <tr>
        <td class="meta-label">Puesto Solicitado:</td>
        <td class="meta-val font-bold" style="color: #003366;"><?= htmlspecialchars($oferta['titulo']) ?></td>
        <td class="meta-label">Empresa Solicitante:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($oferta['empresa_nombre'] ?? 'Confidencial') ?></td>
    </tr>
    <tr>
        <td class="meta-label">Carrera / Programa:</td>
        <td class="meta-val font-bold"><?= htmlspecialchars($oferta['programa_nombre'] ?? 'Todas las carreras') ?></td>
        <td class="meta-label">RUC Empresa:</td>
        <td class="meta-val"><?= htmlspecialchars($oferta['empresa_ruc'] ?? 'No registrado') ?></td>
    </tr>
    <tr>
        <td class="meta-label">Remuneración:</td>
        <td class="meta-val font-bold"><?= (!empty($oferta['salario']) && is_numeric($oferta['salario'])) ? 'S/ ' . number_format($oferta['salario'], 2) : ($oferta['salario'] ?: 'A Tratar') ?></td>
        <td class="meta-label">Tipo Contrato:</td>
        <td class="meta-val"><?= htmlspecialchars($oferta['tipo_contrato'] ?? 'No especificado') ?></td>
    </tr>
    <tr>
        <td class="meta-label">Fecha Publicación:</td>
        <td class="meta-val"><?= date('d/m/Y', strtotime($oferta['fecha_publicacion'])) ?></td>
        <td class="meta-label">Fecha Cierre:</td>
        <td class="meta-val font-bold" style="color: #b91c1c;"><?= date('d/m/Y', strtotime($oferta['fecha_cierre'])) ?></td>
    </tr>
</table>

<!-- TABLA DE POSTULANTES -->
<table class="data-table" cellpadding="3" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 4%;">N°</th>
            <th style="width: 11%;">DNI</th>
            <th style="width: 25%;">Apellidos y Nombres</th>
            <th style="width: 18%;">Programa de Estudios</th>
            <th style="width: 14%;">Contacto / Teléfono</th>
            <th style="width: 12%;">Fecha Postulación</th>
            <th style="width: 16%;">Estado Postulación</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($postulantes)): ?>
            <?php foreach ($postulantes as $idx => $p): 
                $bgClass = ($idx % 2 === 0) ? 'row-even' : 'row-odd';
                $estId = (int)$p['estado'];
                $nomEstado = $estadosInfo[$estId]['nombre'] ?? 'En Proceso';
            ?>
                <tr class="<?= $bgClass ?>">
                    <td class="text-center font-bold" style="width: 4%;"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold" style="width: 11%;"><?= htmlspecialchars($p['dni'] ?? '-') ?></td>
                    <td class="text-left font-bold" style="width: 25%; color: #003366;">
                        <?= htmlspecialchars($p['apellidos_nombres']) ?>
                    </td>
                    <td class="text-left" style="width: 18%;"><?= htmlspecialchars($p['programa_nombre'] ?? 'General') ?></td>
                    <td class="text-center" style="width: 14%;">
                        <?= htmlspecialchars($p['telefono'] ?? '-') ?>
                        <?php if (!empty($p['correo'])): ?>
                            <br><span style="font-size: 6pt; color: #64748b;"><?= htmlspecialchars($p['correo']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="width: 12%;"><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                    <td class="text-center badge-estado-<?= $estId ?>" style="width: 16%;"><?= htmlspecialchars($nomEstado) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                    No se han registrado postulantes para esta convocatoria laboral hasta la fecha.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 8px; font-size: 8.5pt; font-weight: bold; color: #1e293b;">
    Total de Postulantes Recibidos: <?= count($postulantes) ?> candidato(s)
</div>

<!-- BLOQUE DE FIRMAS -->
<table class="signature-table" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 50%;">
            <div style="height: 35px;"></div>
            <div class="sig-line"></div>
            <strong>COORDINACIÓN DE BOLSA DE TRABAJO</strong><br>
            Área de Empleabilidad e Intermediación Laboral<br>
            IESTP HUANTA
        </td>
        <td style="width: 50%;">
            <div style="height: 35px;"></div>
            <div class="sig-line"></div>
            <strong>REPRESENTANTE DE LA EMPRESA</strong><br>
            <?= htmlspecialchars($oferta['empresa_nombre'] ?? 'Empresa Solicitante') ?>
        </td>
    </tr>
</table>
