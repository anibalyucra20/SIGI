<?php
// Preprocesamiento de variables
$logroRaw = mb_strtolower(trim($sesion['logro_objetivo'] ?? ''), 'UTF-8');
$logroNormalizado = '';
if (strpos($logroRaw, 'no logrado') !== false) {
    $logroNormalizado = 'no logrado';
} elseif (strpos($logroRaw, 'sobresaliente') !== false) {
    $logroNormalizado = 'sobresaliente';
} elseif (strpos($logroRaw, 'en proceso') !== false) {
    $logroNormalizado = 'en proceso';
} elseif (strpos($logroRaw, 'logrado') !== false) {
    $logroNormalizado = 'logrado';
}

// Programas de los tutorandos
$programas = [];
if (!empty($estudiantes) && is_array($estudiantes)) {
    foreach ($estudiantes as $est) {
        if (!empty($est['programa_nombre'])) {
            $programas[] = trim($est['programa_nombre']);
        }
    }
}
$programas = array_unique($programas);
$programaNombreStr = !empty($sesion['programa_nombre']) 
    ? $sesion['programa_nombre'] 
    : (!empty($programas) ? implode(', ', $programas) : '');

// Periodo / Semestre / Ciclo
$semestreStr = !empty($sesion['semestre_nombre']) ? $sesion['semestre_nombre'] : '';
$periodoStr  = !empty($sesion['periodo_nombre']) ? $sesion['periodo_nombre'] : '';
$periodoSemestreArr = [];
if ($semestreStr !== '') $periodoSemestreArr[] = $semestreStr;
if ($periodoStr !== '')  $periodoSemestreArr[] = $periodoStr;
$periodoSemestreStr = implode(' / ', $periodoSemestreArr);

// Horario
$horarioStr = !empty($sesion['horario']) ? $sesion['horario'] : (!empty($sesion['fecha_hora']) ? date('h:i A', strtotime($sesion['fecha_hora'])) : '');

// Asistentes
$asistentesIds = !empty($asistentesIds) ? $asistentesIds : [];

// Separar contenidos tratados en 2 líneas
$contenidosRaw = trim(strip_tags((string)($sesion['contenidos'] ?? '')));
$lineasContenidosRaw = explode("\n", str_replace(["\r\n", "\r"], "\n", $contenidosRaw));
$contenido1 = '';
$contenido2 = '';
$idxCont = 0;
foreach ($lineasContenidosRaw as $lcr) {
    $lcr = trim($lcr);
    if ($lcr === '') continue;
    // Quitar prefijo numérico existente si lo tiene
    $lcr = preg_replace('/^[0-9]+[\.\-\)]\s*/', '', $lcr);
    if ($idxCont === 0) {
        $contenido1 = $lcr;
        $idxCont++;
    } elseif ($idxCont === 1) {
        $contenido2 = $lcr;
        $idxCont++;
    }
}

// Función auxiliar para dividir texto en líneas pautadas
if (!function_exists('tutoria_dividir_lineas_grupal')) {
    function tutoria_dividir_lineas_grupal($texto, $maxLineas = 2, $longitudLinea = 88) {
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

$lineasObservaciones   = tutoria_dividir_lineas_grupal($sesion['observaciones_sesion'] ?? '', 2, 88);
$lineasRecomendaciones = tutoria_dividir_lineas_grupal($sesion['recomendaciones'] ?? '', 2, 88);

// Resolución de ruta absoluta del logo oficial
$candidatosLogo = [
    __DIR__ . '/logo_institucion.jpg',
    __DIR__ . '/../sesionesIndividuales/logo_institucion.jpg',
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
        margin-bottom: 2px;
    }
    .doc-title {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        font-weight: bold;
        color: #000000;
        text-align: center;
    }
    table.ficha-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.5pt;
    }
    table.ficha-table td {
        border: 0.5pt solid #000000;
        padding: 2.2px 4px;
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

<!-- TÍTULO Y CUADRO DE TIPO (IDÉNTICO AL ORIGINAL) -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 5px;">
    <tr>
        <td width="70%" align="center" valign="middle">
            <div class="anexo-title">ANEXO 04</div>
            <div class="doc-title">FICHA DE TUTORÍA GRUPAL N° <?= !empty($sesion['nro']) ? htmlspecialchars(str_pad($sesion['nro'], 3, '0', STR_PAD_LEFT)) : (!empty($sesion['id']) ? htmlspecialchars(str_pad($sesion['id'], 3, '0', STR_PAD_LEFT)) : '_____') ?></div>
        </td>
        <td width="30%" align="right" valign="top">
            <table cellpadding="3" cellspacing="0" style="border: 0.5pt solid #000000; font-family: Arial, Helvetica, sans-serif; font-size: 7pt; font-weight: bold; line-height: 1.15; text-align: left;">
                <tr>
                    <td>
                        TIPO: NIVELACIÓN, REFORZAMIENTO<br/>
                        ACADÉMICO, MENTORÍA.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- TABLA PRINCIPAL DE LA FICHA GRUPAL -->
<table class="ficha-table" cellpadding="2.5" cellspacing="0">
    <!-- Fila 1: Tutor y Fecha -->
    <tr>
        <td width="70%">
            Tutor(a): <?= htmlspecialchars($sesion['docente_formato'] ?? '') ?>
        </td>
        <td width="30%">
            Fecha: <?= !empty($sesion['fecha_hora']) ? date('d/m/Y', strtotime($sesion['fecha_hora'])) : '' ?>
        </td>
    </tr>

    <!-- Fila 2: Programa de Estudios y Semestre/ciclo/periodo -->
    <tr>
        <td width="70%">
            Programa de Estudios: <?= htmlspecialchars($programaNombreStr) ?>
        </td>
        <td width="30%">
            Semestre/ciclo/periodo: <?= htmlspecialchars($periodoSemestreStr) ?>
        </td>
    </tr>

    <!-- Fila 3: Objetivo de la sesión y Horario -->
    <tr>
        <td width="70%">
            Objetivo de la sesión: <?= htmlspecialchars($sesion['objetivo_sesion'] ?? '') ?>
        </td>
        <td width="30%">
            Horario: <?= htmlspecialchars($horarioStr) ?>
        </td>
    </tr>

    <!-- Fila 4: Contenidos tratados (2 filas divididas a la derecha) -->
    <tr>
        <td width="22%" rowspan="2" valign="middle" align="left">
            Contenidos tratados
        </td>
        <td width="78%" style="border-bottom: 0.5pt solid #000000;">
            1. <?= htmlspecialchars($contenido1) ?>
        </td>
    </tr>
    <tr>
        <td width="78%">
            2. <?= htmlspecialchars($contenido2) ?>
        </td>
    </tr>

    <!-- Fila 5: Encabezado Asistencia de estudiantes -->
    <tr>
        <td colspan="2" align="center" style="font-weight: normal; font-size: 8.8pt;">
            Asistencia de estudiantes
        </td>
    </tr>

    <!-- Fila 6: Sub-encabezados de la tabla de asistencia -->
    <tr>
        <td colspan="2" style="padding: 0;">
            <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 8.2pt;">
                <tr>
                    <td width="6%" align="center" style="border: none; border-right: 0.5pt solid #000000; border-bottom: 0.5pt solid #000000;">
                        N°
                    </td>
                    <td width="72%" align="center" style="border: none; border-right: 0.5pt solid #000000; border-bottom: 0.5pt solid #000000;">
                        Apellidos y Nombres
                    </td>
                    <td width="22%" align="center" style="border: none; border-bottom: 0.5pt solid #000000;">
                        Firma
                    </td>
                </tr>
                <?php
                // Se renderizan exactamente 12 filas (como en el PDF modelo original)
                for ($i = 0; $i < 12; $i++):
                    $est = $estudiantes[$i] ?? null;
                    $asistio = $est ? in_array((int)$est['id_estudiante'], $asistentesIds) : false;
                    $esUltimaFila = ($i === 11);
                    $borderBottom = $esUltimaFila ? 'border-bottom: none;' : 'border-bottom: 0.5pt solid #000000;';
                ?>
                <tr>
                    <td width="6%" align="center" style="border: none; border-right: 0.5pt solid #000000; <?= $borderBottom ?> height: 16px;">
                        <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td width="72%" style="border: none; border-right: 0.5pt solid #000000; <?= $borderBottom ?> padding-left: 6px;">
                        <?= $est ? htmlspecialchars($est['nombre_completo']) : '&nbsp;' ?>
                    </td>
                    <td width="22%" align="center" style="border: none; <?= $borderBottom ?>">
                        <?= $asistio ? '<span style="font-size: 7pt; color: #000000;">[ ASISTIÓ ]</span>' : '&nbsp;' ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
        </td>
    </tr>

    <!-- Fila 7: Logro del objetivo: (Marque) -->
    <tr>
        <td colspan="2" style="font-size: 8.5pt;">
            Logro del objetivo: (Marque)
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 0;">
            <table width="100%" cellpadding="2.5" cellspacing="0" style="border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 8.2pt;">
                <tr>
                    <td width="25%" align="left" style="border: none; border-right: 0.5pt solid #000000;">
                        No logrado [ <span class="chk"><?= ($logroNormalizado === 'no logrado') ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="25%" align="left" style="border: none; border-right: 0.5pt solid #000000;">
                        En proceso [ <span class="chk"><?= ($logroNormalizado === 'en proceso') ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="25%" align="left" style="border: none; border-right: 0.5pt solid #000000;">
                        Logrado [ <span class="chk"><?= ($logroNormalizado === 'logrado') ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                    <td width="25%" align="left" style="border: none;">
                        Sobresaliente [ <span class="chk"><?= ($logroNormalizado === 'sobresaliente') ? 'X' : '&nbsp;' ?></span> ]
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Fila 8: Observaciones de la sesión (Cabecera + 2 líneas pautadas) -->
    <tr>
        <td colspan="2">Observaciones de la sesión:</td>
    </tr>
    <?php foreach ($lineasObservaciones as $lo): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $lo ?></td>
    </tr>
    <?php endforeach; ?>

    <!-- Fila 9: Recomendaciones (Cabecera + 2 líneas pautadas) -->
    <tr>
        <td colspan="2">Recomendaciones:</td>
    </tr>
    <?php foreach ($lineasRecomendaciones as $lr): ?>
    <tr>
        <td colspan="2" class="ruled-line"><?= $lr ?></td>
    </tr>
    <?php endforeach; ?>
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
            <div style="font-size: 9.5pt; margin-top: 2px;">V°B° responsable</div>
            <div style="font-size: 8.5pt; margin-top: 1px;">Unidad de Bienestar y Empleabilidad</div>
        </td>
    </tr>
</table>

<!-- LÍNEA SEPARADORA AZUL INFERIOR -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 14px; margin-bottom: 4px;">
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
