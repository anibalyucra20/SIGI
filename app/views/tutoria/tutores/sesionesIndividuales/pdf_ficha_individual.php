<?php
// Preprocesamiento de variables
$genero = strtoupper(trim($sesion['estudiante_genero'] ?? ''));
$edad   = !empty($sesion['edad']) ? $sesion['edad'] : '';

// Solicitante
$solicitanteRaw = strtoupper(trim($sesion['solicitante'] ?? 'TUTOR'));
$solicitanteTipo = 'OTRO';
$solicitanteEspecifique = '';
if (strpos($solicitanteRaw, 'DOCENTE') !== false || $solicitanteRaw === 'TUTOR') {
    $solicitanteTipo = 'DOCENTE';
} elseif (strpos($solicitanteRaw, 'ESTUDIANTE') !== false || strpos($solicitanteRaw, 'ALUMNO') !== false) {
    $solicitanteTipo = 'ESTUDIANTE';
} elseif (strpos($solicitanteRaw, 'FAMILIAR') !== false || strpos($solicitanteRaw, 'PADRE') !== false || strpos($solicitanteRaw, 'APODERADO') !== false) {
    $solicitanteTipo = 'FAMILIAR';
} else {
    $solicitanteTipo = 'OTRO';
    $solicitanteEspecifique = $sesion['solicitante'] ?? '';
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
$motOtro  = (!$motAcad && !$motSalud && !$motSocio && !$motFam && !$motEcon && !$motLab && !$motHor && !empty(trim($sesion['motivo'] ?? '')));
$motOtroEsp = $motOtro ? trim($sesion['motivo']) : '';

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

// Función auxiliar para dividir texto en líneas pautadas
if (!function_exists('tutoria_dividir_lineas')) {
    function tutoria_dividir_lineas($texto, $maxLineas = 4, $longitudLinea = 88) {
        $textoLimpio = trim(strip_tags((string)$texto));
        if ($textoLimpio === '') {
            return array_fill(0, $maxLineas, '&nbsp;');
        }
        $lineasRaw = explode("\n", str_replace(["\r\n", "\r"], "\n", $textoLimpio));
        $resultado = [];
        foreach ($lineasRaw as $lr) {
            $lr = trim($lr);
            if ($lr === '') continue;
            $wrapped = explode("\n", wordwrap($lr, $longitudLinea, "\n", true));
            foreach ($wrapped as $w) {
                if (trim($w) !== '') {
                    $resultado[] = trim($w);
                }
            }
        }
        $lineasFinales = [];
        for ($i = 0; $i < $maxLineas; $i++) {
            $lineasFinales[] = isset($resultado[$i]) && $resultado[$i] !== ''
                ? htmlspecialchars($resultado[$i])
                : '&nbsp;';
        }
        return $lineasFinales;
    }
}

$lineasContenidos      = tutoria_dividir_lineas($sesion['contenidos_tratados'] ?? '', 4, 88);
$lineasRecomendaciones = tutoria_dividir_lineas($sesion['recomendaciones'] ?? '', 4, 88);
$lineasCompromiso      = tutoria_dividir_lineas($sesion['compromiso_estudiante'] ?? '', 4, 88);

// Logo institucional
$logoPath = __DIR__ . '/../../logo_institucion.jpg';
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../../../../public/img/logo.png';
}
?>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #000000;
        font-size: 8.5pt;
    }
    .inst-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11.5pt;
        font-weight: bold;
        color: #001F5F;
        text-align: center;
        line-height: 1.15;
    }
    .inst-sub {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5pt;
        font-weight: bold;
        color: #006FC0;
        text-align: center;
        margin-top: 2px;
    }
    .anexo-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5pt;
        font-weight: bold;
        color: #4F81BC;
        text-align: center;
        margin-top: 4px;
        margin-bottom: 2px;
    }
    .doc-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5pt;
        font-weight: bold;
        color: #4F81BC;
        text-align: center;
        margin-bottom: 5px;
    }
    table.ficha-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.5pt;
    }
    table.ficha-table td {
        border: 0.5pt solid #4F81BC;
        padding: 2.5px 4px;
        vertical-align: middle;
    }
    .lbl {
        color: #4F81BC;
        font-weight: bold;
    }
    .val {
        color: #000000;
    }
    .chk {
        font-family: Arial, Helvetica, sans-serif;
        font-weight: bold;
        color: #000000;
    }
    .ruled-line {
        height: 16px;
        color: #000000;
        font-size: 8pt;
        padding-left: 6px;
        vertical-align: middle;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="22%" align="left" valign="middle">
            <?php if (file_exists($logoPath)): ?>
                <img src="<?= $logoPath ?>" width="105" height="36" />
            <?php endif; ?>
        </td>
        <td width="78%" align="center" valign="middle">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO</div>
            <div class="inst-title">“HUANTA”</div>
            <div class="inst-sub">UNIDAD DE BIENESTAR Y EMPLEABILIDAD</div>
        </td>
    </tr>
</table>

<!-- TÍTULO DEL DOCUMENTO -->
<div class="anexo-title">ANEXO 05</div>
<div class="doc-title">FICHA DE TUTORÍA INDIVIDUAL N° <?= !empty($sesion['nro']) ? htmlspecialchars(str_pad($sesion['nro'], 4, '0', STR_PAD_LEFT)) : '______' ?></div>

<!-- TABLA PRINCIPAL DE LA FICHA INDIVIDUAL -->
<table class="ficha-table" cellpadding="2.5" cellspacing="0">
    <!-- Fila 1: Tutor y Fecha -->
    <tr>
        <td width="71%">
            <span class="lbl">Tutor(a):</span> <span class="val"><?= htmlspecialchars($sesion['docente_formato'] ?? '') ?></span>
        </td>
        <td width="29%">
            <span class="lbl">Fecha:</span> <span class="val"><?= !empty($sesion['fecha_hora']) ? date('d/m/Y', strtotime($sesion['fecha_hora'])) : date('d/m/Y') ?></span>
        </td>
    </tr>

    <!-- Fila 2: Cabecera Estudiante -->
    <tr>
        <td colspan="2" align="center" style="font-weight: bold; color: #4F81BC; font-size: 9pt;">
            INFORMACIÓN DEL ESTUDIANTE
        </td>
    </tr>

    <!-- Fila 3: Apellidos y nombres, Sexo, Edad -->
    <tr>
        <td width="71%">
            <span class="lbl">Apellidos y nombres:</span> <span class="val"><?= htmlspecialchars($sesion['estudiante_formato'] ?? '') ?></span>
        </td>
        <td width="29%">
            <span class="lbl">Sexo:</span>
            <span class="lbl">F [</span> <span class="chk"><?= ($genero === 'F') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            <span class="lbl">M [</span> <span class="chk"><?= ($genero === 'M') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;
            <span class="lbl">Edad:</span> <span class="val"><?= htmlspecialchars($edad ?: '-') ?></span>
        </td>
    </tr>

    <!-- Fila 4: Programa de estudios, Año, Semestre -->
    <tr>
        <td width="71%">
            <span class="lbl">Programa de estudios:</span> <span class="val"><?= htmlspecialchars($sesion['programa_nombre'] ?? 'General') ?></span>
        </td>
        <td width="29%">
            <span class="lbl">Año:</span> <span class="val"><?= htmlspecialchars($anioEstudio) ?></span>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="lbl">Semestre:</span> <span class="val"><?= htmlspecialchars($semestreNombre) ?></span>
        </td>
    </tr>

    <!-- Fila 5: Solicitante -->
    <tr>
        <td colspan="2">
            <span class="lbl">Solicitante:</span>
            &nbsp;&nbsp;&nbsp;
            <span class="lbl">Docente [</span> <span class="chk"><?= ($solicitanteTipo === 'DOCENTE') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">Estudiante: [</span> <span class="chk"><?= ($solicitanteTipo === 'ESTUDIANTE') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">Familiar: [</span> <span class="chk"><?= ($solicitanteTipo === 'FAMILIAR') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">Otro: [</span> <span class="chk"><?= ($solicitanteTipo === 'OTRO') ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">Especifique:</span> <span class="val"><?= htmlspecialchars($solicitanteEspecifique) ?></span>
        </td>
    </tr>

    <!-- Fila 6: Motivo (Estructura de 2 filas con 5 columnas internas) -->
    <tr>
        <td colspan="2" style="padding: 0;">
            <table width="100%" cellpadding="2.5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt;">
                <tr>
                    <td width="10%" rowspan="2" align="center" valign="middle" style="border: none; border-right: 0.5pt solid #4F81BC; color: #4F81BC; font-weight: bold;">
                        Motivo
                    </td>
                    <td width="20%" style="border: none; border-right: 0.5pt solid #4F81BC; border-bottom: 0.5pt solid #4F81BC;">
                        <span class="lbl">Académico: [</span> <span class="chk"><?= $motAcad ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td width="16%" style="border: none; border-right: 0.5pt solid #4F81BC; border-bottom: 0.5pt solid #4F81BC;">
                        <span class="lbl">Salud [</span> <span class="chk"><?= $motSalud ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td width="23%" style="border: none; border-right: 0.5pt solid #4F81BC; border-bottom: 0.5pt solid #4F81BC;">
                        <span class="lbl">Socioemocional [</span> <span class="chk"><?= $motSocio ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td width="31%" style="border: none; border-bottom: 0.5pt solid #4F81BC;">
                        <span class="lbl">Otro: [</span> <span class="chk"><?= $motOtro ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                        <span class="lbl">Especifique:</span> <span class="val"><?= htmlspecialchars($motOtroEsp) ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; border-right: 0.5pt solid #4F81BC;">
                        <span class="lbl">Familiar: [</span> <span class="chk"><?= $motFam ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td style="border: none; border-right: 0.5pt solid #4F81BC;">
                        <span class="lbl">Económico [</span> <span class="chk"><?= $motEcon ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td style="border: none; border-right: 0.5pt solid #4F81BC;">
                        <span class="lbl">Laboral: [</span> <span class="chk"><?= $motLab ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                    <td style="border: none;">
                        <span class="lbl">Horario: [</span> <span class="chk"><?= $motHor ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Fila 7: Contenidos tratados (Cabecera + 4 líneas pautadas) -->
    <tr>
        <td colspan="2" class="lbl" style="background-color: #ffffff;">Contenidos tratados:</td>
    </tr>
    <?php foreach ($lineasContenidos as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 8: Recomendaciones (Cabecera + 4 líneas pautadas) -->
    <tr>
        <td colspan="2" class="lbl" style="background-color: #ffffff;">Recomendaciones:</td>
    </tr>
    <?php foreach ($lineasRecomendaciones as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 9: Compromiso del estudiante (Cabecera + 4 líneas pautadas) -->
    <tr>
        <td colspan="2" class="lbl" style="background-color: #ffffff;">Compromiso del estudiante:</td>
    </tr>
    <?php foreach ($lineasCompromiso as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 10: Seguimiento -->
    <tr>
        <td width="55%">
            <span class="lbl">Se recomienda seguimiento:</span>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="lbl">Si [</span> <span class="chk"><?= ($sesion['seguimiento'] == 1) ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">No [</span> <span class="chk"><?= ($sesion['seguimiento'] == 0) ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
        </td>
        <td width="45%">
            <span class="lbl">Fecha próxima sesión:</span>
            <span class="val"><?= ($sesion['seguimiento'] == 1 && !empty($sesion['fecha_prox_sesion']) && $sesion['fecha_prox_sesion'] !== '2000-01-01 00:00:00') ? date('d/m/Y', strtotime($sesion['fecha_prox_sesion'])) : '' ?></span>
        </td>
    </tr>

    <!-- Fila 11: Derivación -->
    <tr>
        <td width="55%">
            <span class="lbl">Se recomienda derivación:</span>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="lbl">Si [</span> <span class="chk"><?= ($sesion['derivacion'] == 1) ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
            &nbsp;&nbsp;
            <span class="lbl">No [</span> <span class="chk"><?= ($sesion['derivacion'] == 0) ? 'X' : '&nbsp;' ?></span> <span class="lbl">]</span>
        </td>
        <td width="45%">
            <span class="lbl">Servicio</span> <sup>(1)</sup> <span class="lbl">Especifique:</span>
            <span class="val"><?= ($sesion['derivacion'] == 1) ? htmlspecialchars($sesion['servicio_derivacion'] ?? '') : '' ?></span>
        </td>
    </tr>

    <!-- Fila 12: Nota al pie de Servicios -->
    <tr>
        <td colspan="2" style="font-size: 8.5pt; color: #4F81BC; line-height: 1.25; padding: 4px 6px;">
            <div style="font-weight: bold; margin-bottom: 2px;">(1) Servicios de la Unidad de Bienestar y Empleabilidad:</div>
            <div style="padding-left: 8px;">
                &bull; Social<br/>
                &bull; Psicopedagógico<br/>
                &bull; Salud<br/>
                &bull; Inserción laboral<br/>
                &bull; Capacitación
            </div>
        </td>
    </tr>
</table>

<!-- FIRMAS -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 18px; text-align: center; color: #4F81BC; font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <td width="45%" align="center" valign="top">
            <div style="color: #4F81BC; font-size: 10pt;">______________________________</div>
            <div style="color: #4F81BC; font-size: 9.5pt; font-weight: normal; margin-top: 2px;">Firma del tutor(a)</div>
            <?php if (!empty($sesion['docente_formato'])): ?>
                <div style="font-size: 7.5pt; color: #334155; margin-top: 2px;"><?= htmlspecialchars($sesion['docente_formato']) ?></div>
            <?php endif; ?>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" align="center" valign="top">
            <div style="color: #4F81BC; font-size: 10pt;">______________________________</div>
            <div style="color: #4F81BC; font-size: 9.5pt; font-weight: normal; margin-top: 2px;">V°B° Responsable</div>
            <div style="color: #4F81BC; font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Unidad de Bienestar y Empleabilidad</div>
        </td>
    </tr>
</table>

<!-- PIE DE PÁGINA -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 12px; text-align: center; font-family: Calibri, Arial, sans-serif; font-size: 7.5pt;">
    <tr>
        <td style="color: #006FC0;">
            TEL: 66 322296 &nbsp;&nbsp;&bull;&nbsp;&nbsp; Dirección: Jr. Córdova N° 650 - Huanta &nbsp;&nbsp;&bull;&nbsp;&nbsp; Correo: <a href="mailto:contactos@iestphuanta.edu.pe" style="color: #0462C1; text-decoration: underline;">contactos@iestphuanta.edu.pe</a>
        </td>
    </tr>
    <tr>
        <td style="color: #0462C1; padding-top: 1px;">
            Web: <a href="http://www.iestphuanta.edu.pe" style="color: #0462C1; text-decoration: underline;">www.iestphuanta.edu.pe</a>
        </td>
    </tr>
</table>
