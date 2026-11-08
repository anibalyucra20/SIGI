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
$semestreNombre = !empty($sesion['semestre_nombre']) ? $sesion['semestre_nombre'] : '';
$anioEstudio = '';
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

// 3 líneas para contenidos, 4 para recomendaciones, 4 para compromiso (idéntico al PDF original)
$lineasContenidos      = tutoria_dividir_lineas($sesion['contenidos_tratados'] ?? '', 3, 88);
$lineasRecomendaciones = tutoria_dividir_lineas($sesion['recomendaciones'] ?? '', 4, 88);
$lineasCompromiso      = tutoria_dividir_lineas($sesion['compromiso_estudiante'] ?? '', 4, 88);

// Resolución de ruta absoluta del logo oficial
$candidatosLogo = [
    __DIR__ . '/logo_institucion.jpg',
    __DIR__ . '/../../logo_institucion.jpg',
    __DIR__ . '/../../../../public/img/logo.png'
];
$logoPath = '';
foreach ($candidatosLogo as $cand) {
    if (file_exists($cand)) {
        $logoPath = str_replace('\\', '/', realpath($cand));
        break;
    }
}

// Inserción directa de alta precisión del logo en TCPDF
if (isset($pdf) && is_object($pdf) && $logoPath && file_exists($logoPath)) {
    // Coordenadas exactas extraídas del PDF original: X=25.0mm, Y=10.0mm, W=29.35mm, H=10.18mm
    $pdf->Image($logoPath, 25, 10, 29.35, 10.18, '', '', '', true, 300, '', false, false, 0, false, false, false);
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
        font-size: 11pt;
        font-weight: bold;
        color: #001F5F;
        text-align: center;
        line-height: 1.15;
    }
    .inst-sub {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        font-weight: bold;
        color: #006FC0;
        text-align: center;
        margin-top: 2px;
    }
    .anexo-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        font-weight: bold;
        color: #000000;
        text-align: center;
        margin-top: 5px;
        margin-bottom: 2px;
    }
    .doc-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        font-weight: bold;
        color: #000000;
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
        border: 0.5pt solid #000000;
        padding: 2px 4px;
        vertical-align: middle;
    }
    .ruled-line {
        height: 16px;
        color: #000000;
        font-size: 8pt;
        padding-left: 6px;
        vertical-align: middle;
        border: 0.5pt solid #000000;
    }
    .chk {
        font-weight: bold;
        color: #000000;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="22%" align="left" valign="top">
            <?php if (!isset($pdf) && $logoPath): ?>
                <img src="<?= $logoPath ?>" width="104" height="36" />
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </td>
        <td width="78%" align="center" valign="top">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO</div>
            <div class="inst-title">“HUANTA”</div>
            <div class="inst-sub">UNIDAD DE BIENESTAR Y EMPLEABILIDAD</div>
        </td>
    </tr>
</table>

<!-- LÍNEA SEPARADORA AZUL SUPERIOR -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 3px; margin-bottom: 5px;">
    <tr>
        <td style="border-bottom: 1.5pt solid #006FC0; height: 1px;"></td>
    </tr>
</table>

<!-- TÍTULO DEL DOCUMENTO -->
<div class="anexo-title">ANEXO 05</div>
<div class="doc-title">FICHA DE TUTORÍA INDIVIDUAL N° <?= !empty($sesion['nro']) ? htmlspecialchars(str_pad($sesion['nro'], 3, '0', STR_PAD_LEFT)) : '______' ?></div>

<!-- TABLA PRINCIPAL DE LA FICHA INDIVIDUAL -->
<table class="ficha-table" cellpadding="2.5" cellspacing="0">
    <!-- Fila 1: Tutor y Fecha -->
    <tr>
        <td width="71%">
            Tutor(a): <?= htmlspecialchars($sesion['docente_formato'] ?? '') ?>
        </td>
        <td width="29%">
            Fecha: <?= !empty($sesion['fecha_hora']) ? date('d/m/Y', strtotime($sesion['fecha_hora'])) : '' ?>
        </td>
    </tr>

    <!-- Fila 2: Cabecera Estudiante -->
    <tr>
        <td colspan="2" align="center" style="font-weight: normal; font-size: 8.8pt;">
            INFORMACIÓN DEL ESTUDIANTE
        </td>
    </tr>

    <!-- Fila 3: Apellidos y nombres, Sexo, Edad -->
    <tr>
        <td width="71%">
            Apellidos y nombres: <?= htmlspecialchars($sesion['estudiante_formato'] ?? '') ?>
        </td>
        <td width="29%">
            Sexo: &nbsp; F [ <span class="chk"><?= ($genero === 'F') ? 'X' : '&nbsp;' ?></span> ] &nbsp; M [ <span class="chk"><?= ($genero === 'M') ? 'X' : '&nbsp;' ?></span> ] &nbsp; Edad: <?= htmlspecialchars($edad ?: '') ?>
        </td>
    </tr>

    <!-- Fila 4: Programa de estudios, Año, Semestre -->
    <tr>
        <td width="71%">
            Programa de estudios: <?= htmlspecialchars($sesion['programa_nombre'] ?? '') ?>
        </td>
        <td width="29%">
            Año: <?= htmlspecialchars($anioEstudio) ?> &nbsp;&nbsp;&nbsp;&nbsp; Semestre: <?= htmlspecialchars($semestreNombre) ?>
        </td>
    </tr>

    <!-- Fila 5: Solicitante -->
    <tr>
        <td colspan="2">
            Solicitante: &nbsp;&nbsp;
            Docente [ <span class="chk"><?= ($solicitanteTipo === 'DOCENTE') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            Estudiante: [ <span class="chk"><?= ($solicitanteTipo === 'ESTUDIANTE') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            Familiar: [ <span class="chk"><?= ($solicitanteTipo === 'FAMILIAR') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            Otro: [ <span class="chk"><?= ($solicitanteTipo === 'OTRO') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            Especifique: <?= htmlspecialchars($solicitanteEspecifique) ?>
        </td>
    </tr>

    <!-- Fila 6: Motivo (Estructura de 2 filas y 5 columnas) -->
    <tr>
        <td colspan="2" style="padding: 0;">
            <table width="100%" cellpadding="2.5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt;">
                <tr>
                    <td width="9%" rowspan="2" align="center" valign="middle" style="border: none; border-right: 0.5pt solid #000000;">
                        Motivo
                    </td>
                    <td width="20%" style="border: none; border-right: 0.5pt solid #000000; border-bottom: 0.5pt solid #000000;">
                        Académico: [ <span class="chk"><?= $motAcad ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="16%" style="border: none; border-right: 0.5pt solid #000000; border-bottom: 0.5pt solid #000000;">
                        Salud [ <span class="chk"><?= $motSalud ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="24%" style="border: none; border-right: 0.5pt solid #000000; border-bottom: 0.5pt solid #000000;">
                        Socioemocional [ <span class="chk"><?= $motSocio ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="31%" style="border: none; border-bottom: 0.5pt solid #000000;">
                        Otro: [ <span class="chk"><?= $motOtro ? 'X' : '&nbsp;' ?></span> ] Especifique: <?= htmlspecialchars($motOtroEsp) ?>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; border-right: 0.5pt solid #000000;">
                        Familiar: [ <span class="chk"><?= $motFam ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td style="border: none; border-right: 0.5pt solid #000000;">
                        Económico [ <span class="chk"><?= $motEcon ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td style="border: none; border-right: 0.5pt solid #000000;">
                        Laboral: [ <span class="chk"><?= $motLab ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td style="border: none;">
                        Horario: [ <span class="chk"><?= $motHor ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Fila 7: Contenidos tratados (Cabecera + 3 líneas pautadas) -->
    <tr>
        <td colspan="2">Contenidos tratados:</td>
    </tr>
    <?php foreach ($lineasContenidos as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 8: Recomendaciones (Cabecera + 4 líneas pautadas) -->
    <tr>
        <td colspan="2">Recomendaciones:</td>
    </tr>
    <?php foreach ($lineasRecomendaciones as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 9: Compromiso del estudiante (Cabecera + 4 líneas pautadas) -->
    <tr>
        <td colspan="2">Compromiso del estudiante:</td>
    </tr>
    <?php foreach ($lineasCompromiso as $l): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $l ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 10: Seguimiento -->
    <tr>
        <td width="55%">
            Se recomienda seguimiento: &nbsp;&nbsp;
            Si [ <span class="chk"><?= ($sesion['seguimiento'] === 1 || $sesion['seguimiento'] === '1') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            No [ <span class="chk"><?= ($sesion['seguimiento'] === 0 || $sesion['seguimiento'] === '0') ? 'X' : '&nbsp;' ?></span> ]
        </td>
        <td width="45%">
            Fecha próxima sesión: <?= ($sesion['seguimiento'] == 1 && !empty($sesion['fecha_prox_sesion']) && $sesion['fecha_prox_sesion'] !== '2000-01-01 00:00:00') ? date('d/m/Y', strtotime($sesion['fecha_prox_sesion'])) : '' ?>
        </td>
    </tr>

    <!-- Fila 11: Derivación -->
    <tr>
        <td width="55%">
            Se recomienda derivación: &nbsp;&nbsp;
            Si [ <span class="chk"><?= ($sesion['derivacion'] === 1 || $sesion['derivacion'] === '1') ? 'X' : '&nbsp;' ?></span> ] &nbsp;&nbsp;
            No [ <span class="chk"><?= ($sesion['derivacion'] === 0 || $sesion['derivacion'] === '0') ? 'X' : '&nbsp;' ?></span> ]
        </td>
        <td width="45%">
            Servicio <sup>(1)</sup> Especifique: <?= ($sesion['derivacion'] == 1) ? htmlspecialchars($sesion['servicio_derivacion'] ?? '') : '' ?>
        </td>
    </tr>

    <!-- Fila 12: Nota al pie de Servicios -->
    <tr>
        <td colspan="2" style="font-size: 8.5pt; color: #000000; line-height: 1.25; padding: 4px 6px;">
            <div style="margin-bottom: 2px;">(1) Servicios de la Unidad de Bienestar y Empleabilidad:</div>
            <div style="padding-left: 20px;">
                Social<br/>
                Psicopedagógico<br/>
                Salud<br/>
                Inserción laboral<br/>
                Capacitación
            </div>
        </td>
    </tr>
</table>

<!-- FIRMAS -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 25px; text-align: center; color: #000000; font-family: Arial, Helvetica, sans-serif;">
    <tr>
        <td width="45%" align="center" valign="top">
            <div style="font-size: 10pt;">______________________</div>
            <div style="font-size: 9.5pt; margin-top: 2px;">Firma del tutor(a)</div>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" align="center" valign="top">
            <div style="font-size: 10pt;">__________________________</div>
            <div style="font-size: 9.5pt; margin-top: 2px;">V°B° Responsable</div>
            <div style="font-size: 8.5pt; margin-top: 1px;">Unidad de Bienestar y Empleabilidad</div>
        </td>
    </tr>
</table>

<!-- LÍNEA SEPARADORA AZUL INFERIOR -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 16px; margin-bottom: 4px;">
    <tr>
        <td style="border-bottom: 1.5pt solid #006FC0; height: 1px;"></td>
    </tr>
</table>

<!-- PIE DE PÁGINA -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align: center; font-family: Calibri, Arial, sans-serif; font-size: 7.5pt;">
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
