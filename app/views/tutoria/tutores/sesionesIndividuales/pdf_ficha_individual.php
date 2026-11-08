<?php
// Preprocesamiento de variables
$genero = strtoupper(trim($sesion['estudiante_genero'] ?? ''));
$edad   = !empty($sesion['edad']) ? $sesion['edad'] : '';

// Solicitante
$solicitanteRaw = strtoupper(trim($sesion['solicitante'] ?? 'TUTOR'));
$solicitanteTipo = 'OTRO';
if (strpos($solicitanteRaw, 'DOCENTE') !== false || $solicitanteRaw === 'TUTOR') {
    $solicitanteTipo = 'DOCENTE';
} elseif (strpos($solicitanteRaw, 'ESTUDIANTE') !== false || strpos($solicitanteRaw, 'ALUMNO') !== false) {
    $solicitanteTipo = 'ESTUDIANTE';
} elseif (strpos($solicitanteRaw, 'FAMILIAR') !== false || strpos($solicitanteRaw, 'PADRE') !== false || strpos($solicitanteRaw, 'APODERADO') !== false) {
    $solicitanteTipo = 'FAMILIAR';
}

// Motivo
$motivoLower = mb_strtolower(trim($sesion['motivo'] ?? ''), 'UTF-8');
$motAcad  = (strpos($motivoLower, 'académic') !== false || strpos($motivoLower, 'academico') !== false || strpos($motivoLower, 'rendimiento') !== false || strpos($motivoLower, 'estudio') !== false || strpos($motivoLower, 'nota') !== false);
$motSalud = (strpos($motivoLower, 'salud') !== false || strpos($motivoLower, 'médic') !== false || strpos($motivoLower, 'enferm') !== false);
$motSocio = (strpos($motivoLower, 'socioemocional') !== false || strpos($motivoLower, 'emocional') !== false || strpos($motivoLower, 'convivencia') !== false || strpos($motivoLower, 'adaptación') !== false || strpos($motivoLower, 'adaptacion') !== false || strpos($motivoLower, 'psicológ') !== false);
$motFam   = (strpos($motivoLower, 'familiar') !== false || strpos($motivoLower, 'padres') !== false || strpos($motivoLower, 'familia') !== false);
$motEcon  = (strpos($motivoLower, 'económic') !== false || strpos($motivoLower, 'economico') !== false || strpos($motivoLower, 'dinero') !== false || strpos($motivoLower, 'recursos') !== false);
$motLab   = (strpos($motivoLower, 'laboral') !== false || strpos($motivoLower, 'trabajo') !== false || strpos($motivoLower, 'empleo') !== false);
$motHor   = (strpos($motivoLower, 'horario') !== false || strpos($motivoLower, 'inasistencia') !== false || strpos($motivoLower, 'tardanza') !== false || strpos($motivoLower, 'tiempo') !== false);
$motOtro  = (!$motAcad && !$motSalud && !$motSocio && !$motFam && !$motEcon && !$motLab && !$motHor);

// Año y Semestre
$semestreNombre = !empty($sesion['semestre_nombre']) ? $sesion['semestre_nombre'] : '-';
$anioEstudio = '-';
if (in_array(strtoupper($semestreNombre), ['I', 'II'])) {
    $anioEstudio = '1°';
} elseif (in_array(strtoupper($semestreNombre), ['III', 'IV'])) {
    $anioEstudio = '2°';
} elseif (in_array(strtoupper($semestreNombre), ['V', 'VI'])) {
    $anioEstudio = '3°';
} elseif (!empty($sesion['periodo_nombre'])) {
    $anioEstudio = substr($sesion['periodo_nombre'], 0, 4);
}
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #000000;
        font-size: 8pt;
    }
    .inst-title {
        font-size: 11pt;
        font-weight: bold;
        color: #111827;
        text-align: center;
        line-height: 1.2;
    }
    .inst-sub {
        font-size: 9.5pt;
        font-weight: bold;
        color: #0284c7;
        text-align: center;
        letter-spacing: 0.5px;
    }
    .anexo-title {
        font-size: 9pt;
        font-weight: bold;
        color: #0f172a;
        text-align: center;
        margin-top: 4px;
        margin-bottom: 2px;
    }
    .doc-title {
        font-size: 10pt;
        font-weight: bold;
        color: #0f172a;
        text-align: center;
        margin-bottom: 6px;
    }
    .table-main {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 7.5pt;
    }
    .table-main td, .table-main th {
        border: 1px solid #000000;
        padding: 3.5px 4px;
        vertical-align: top;
    }
    .bg-header {
        background-color: #f1f5f9;
        font-weight: bold;
    }
    .bg-sub {
        background-color: #f8fafc;
        font-weight: bold;
    }
    .text-box {
        min-height: 48px;
        font-size: 7.5pt;
        line-height: 1.25;
    }
    .footer-text {
        font-size: 6.8pt;
        color: #64748b;
        text-align: center;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="16%" align="left" valign="middle">
            <?php if (file_exists($rutaLogo)): ?>
                <img src="<?= $rutaLogo ?>" width="52" height="52" />
            <?php endif; ?>
        </td>
        <td width="84%" align="center" valign="middle">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO</div>
            <div class="inst-title">“HUANTA”</div>
            <div style="height: 3px;"></div>
            <div class="inst-sub">UNIDAD DE BIENESTAR Y EMPLEABILIDAD</div>
        </td>
    </tr>
</table>

<!-- LÍNEA AZUL DE SEPARACIÓN -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 4px; margin-bottom: 8px;">
    <tr>
        <td style="border-bottom: 2.5px solid #0284c7; height: 1px;"></td>
    </tr>
</table>

<!-- TÍTULO DEL DOCUMENTO -->
<div class="anexo-title">ANEXO 05</div>
<div class="doc-title">FICHA DE TUTORÍA INDIVIDUAL N° <?= htmlspecialchars(str_pad($sesion['nro'] ?? '1', 4, '0', STR_PAD_LEFT)) ?></div>

<!-- TABLA PRINCIPAL DE LA FICHA INDIVIDUAL -->
<table class="table-main" cellpadding="3" cellspacing="0">
    <!-- Tutor y Fecha -->
    <tr>
        <td width="68%"><strong>Tutor(a):</strong> <?= htmlspecialchars($sesion['docente_formato'] ?? '') ?></td>
        <td width="32%"><strong>Fecha:</strong> <?= !empty($sesion['fecha_hora']) ? date('d/m/Y', strtotime($sesion['fecha_hora'])) : date('d/m/Y') ?></td>
    </tr>

    <!-- Información del Estudiante -->
    <tr class="bg-header">
        <td colspan="2" style="text-align: center; font-size: 8pt; letter-spacing: 0.5px;">INFORMACIÓN DEL ESTUDIANTE</td>
    </tr>

    <!-- Apellidos y nombres, Sexo, Edad -->
    <tr>
        <td width="60%"><strong>Apellidos y nombres:</strong> <?= htmlspecialchars($sesion['estudiante_formato'] ?? '') ?></td>
        <td width="40%">
            <strong>Sexo:</strong> &nbsp;
            F [ <?= ($genero === 'F') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;
            M [ <?= ($genero === 'M') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Edad:</strong> <?= htmlspecialchars($edad ?: '-') ?>
        </td>
    </tr>

    <!-- Programa de estudios, Año, Semestre -->
    <tr>
        <td width="60%"><strong>Programa de estudios:</strong> <?= htmlspecialchars($sesion['programa_nombre'] ?? 'General') ?></td>
        <td width="40%">
            <strong>Año:</strong> <?= htmlspecialchars($anioEstudio) ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Semestre:</strong> <?= htmlspecialchars($semestreNombre) ?>
        </td>
    </tr>

    <!-- Solicitante -->
    <tr>
        <td colspan="2">
            <strong>Solicitante:</strong> &nbsp;&nbsp;&nbsp;
            Docente [ <?= ($solicitanteTipo === 'DOCENTE') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            Estudiante: [ <?= ($solicitanteTipo === 'ESTUDIANTE') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            Familiar: [ <?= ($solicitanteTipo === 'FAMILIAR') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            Otro: [ <?= ($solicitanteTipo === 'OTRO') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Especifique:</strong> <?= ($solicitanteTipo === 'OTRO') ? htmlspecialchars($sesion['solicitante']) : '' ?>
        </td>
    </tr>

    <!-- Motivo -->
    <tr>
        <td colspan="2" style="padding: 3px 4px;">
            <table width="100%" cellpadding="1" cellspacing="0" border="0" style="font-size: 7.2pt;">
                <tr>
                    <td width="10%" rowspan="2" valign="middle"><strong>Motivo:</strong></td>
                    <td width="20%">Académico: [ <?= $motAcad ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="16%">Salud [ <?= $motSalud ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="25%">Socioemocional [ <?= $motSocio ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="29%">Otro: [ <?= $motOtro ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] Especifique: <?= $motOtro ? htmlspecialchars($sesion['motivo']) : '' ?></td>
                </tr>
                <tr>
                    <td>Familiar: [ <?= $motFam ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td>Económico [ <?= $motEcon ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td>Laboral: [ <?= $motLab ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td>Horario: [ <?= $motHor ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Contenidos tratados -->
    <tr class="bg-sub">
        <td colspan="2"><strong>Contenidos tratados:</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="min-height: 60px;">
            <div class="text-box">
                <?= !empty($sesion['contenidos_tratados']) ? nl2br(htmlspecialchars($sesion['contenidos_tratados'])) : '<br/><br/><br/>' ?>
            </div>
        </td>
    </tr>

    <!-- Recomendaciones -->
    <tr class="bg-sub">
        <td colspan="2"><strong>Recomendaciones:</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="min-height: 50px;">
            <div class="text-box">
                <?= !empty($sesion['recomendaciones']) ? nl2br(htmlspecialchars($sesion['recomendaciones'])) : '<br/><br/><br/>' ?>
            </div>
        </td>
    </tr>

    <!-- Compromiso del estudiante -->
    <tr class="bg-sub">
        <td colspan="2"><strong>Compromiso del estudiante:</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="min-height: 50px;">
            <div class="text-box">
                <?= !empty($sesion['compromiso_estudiante']) ? nl2br(htmlspecialchars($sesion['compromiso_estudiante'])) : '<br/><br/><br/>' ?>
            </div>
        </td>
    </tr>

    <!-- Seguimiento -->
    <tr>
        <td width="55%">
            <strong>Se recomienda seguimiento:</strong> &nbsp;&nbsp;&nbsp;&nbsp;
            Si [ <?= ($sesion['seguimiento'] == 1) ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            No [ <?= ($sesion['seguimiento'] == 0) ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]
        </td>
        <td width="45%">
            <strong>Fecha próxima sesión:</strong> <?= ($sesion['seguimiento'] == 1 && !empty($sesion['fecha_prox_sesion']) && $sesion['fecha_prox_sesion'] !== '2000-01-01 00:00:00') ? date('d/m/Y', strtotime($sesion['fecha_prox_sesion'])) : '' ?>
        </td>
    </tr>

    <!-- Derivación -->
    <tr>
        <td width="55%">
            <strong>Se recomienda derivación:</strong> &nbsp;&nbsp;&nbsp;&nbsp;
            Si [ <?= ($sesion['derivacion'] == 1) ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ] &nbsp;&nbsp;&nbsp;&nbsp;
            No [ <?= ($sesion['derivacion'] == 0) ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]
        </td>
        <td width="45%">
            <strong>Servicio <sup>(1)</sup>:</strong> <?= htmlspecialchars($sesion['servicio_derivacion'] ?? '') ?><br/>
            <strong>Especifique:</strong> <?= ($sesion['derivacion'] == 1) ? htmlspecialchars($sesion['servicio_derivacion'] ?? '') : '' ?>
        </td>
    </tr>

    <!-- Nota al pie de Servicios -->
    <tr>
        <td colspan="2" style="font-size: 6.8pt; color: #334155; line-height: 1.2;">
            <strong>(1) Servicios de la Unidad de Bienestar y Empleabilidad:</strong><br/>
            &bull; Social &nbsp;&nbsp;&nbsp;&nbsp;&bull; Psicopedagógico &nbsp;&nbsp;&nbsp;&nbsp;&bull; Salud &nbsp;&nbsp;&nbsp;&bull; Inserción laboral &nbsp;&nbsp;&nbsp;&bull; Capacitación
        </td>
    </tr>
</table>

<!-- FIRMAS -->
<div style="height: 38px;"></div>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align: center; font-size: 7.8pt;">
    <tr>
        <td width="44%" align="center">
            __________________________________________<br/>
            <strong>Firma del tutor(a)</strong><br/>
            <span style="font-size: 6.8pt; color: #475569;"><?= htmlspecialchars($sesion['docente_formato'] ?? '') ?></span>
        </td>
        <td width="12%">&nbsp;</td>
        <td width="44%" align="center">
            __________________________________________<br/>
            <strong>V°B° Responsable</strong><br/>
            <strong>Unidad de Bienestar y Empleabilidad</strong>
        </td>
    </tr>
</table>

<!-- PIE DE PÁGINA -->
<div style="height: 20px;"></div>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="border-top: 1px solid #cbd5e1; padding-top: 3px;" class="footer-text">
            TEL: 66 322296 &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Dirección: Jr. Córdova N° 650 - Huanta &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Correo: contactos@iestphuanta.edu.pe &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Web: www.iestphuanta.edu.pe
        </td>
    </tr>
</table>

