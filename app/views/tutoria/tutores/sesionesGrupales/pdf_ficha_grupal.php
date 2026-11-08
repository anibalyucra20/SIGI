<?php
// Preprocesamiento de variables
$logroRaw = mb_strtolower(trim($sesion['logro_objetivo'] ?? ''), 'UTF-8');
$logroNormalizado = 'en proceso';
if (strpos($logroRaw, 'no logrado') !== false) {
    $logroNormalizado = 'no logrado';
} elseif (strpos($logroRaw, 'sobresaliente') !== false) {
    $logroNormalizado = 'sobresaliente';
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
$programaNombreStr = !empty($programas) ? implode(', ', $programas) : 'General';

// Periodo / Semestre
$periodoSemestreStr = !empty($sesion['periodo_nombre']) ? $sesion['periodo_nombre'] : date('Y');

// Horario
$horarioStr = !empty($sesion['fecha_hora']) ? date('h:i A', strtotime($sesion['fecha_hora'])) : '';

// Asistentes
$asistentesIds = !empty($asistentesIds) ? $asistentesIds : [];
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #000000;
        font-size: 7.8pt;
    }
    .inst-title {
        font-size: 10.5pt;
        font-weight: bold;
        color: #111827;
        text-align: center;
        line-height: 1.15;
    }
    .inst-sub {
        font-size: 9pt;
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
        margin-top: 2px;
        margin-bottom: 2px;
    }
    .doc-title {
        font-size: 9.5pt;
        font-weight: bold;
        color: #0f172a;
        text-align: center;
        margin-bottom: 4px;
    }
    .tipo-box {
        border: 1px solid #000000;
        font-size: 6.5pt;
        font-weight: bold;
        padding: 4px;
        text-align: center;
        line-height: 1.15;
    }
    .table-main {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 7pt;
    }
    .table-main td, .table-main th {
        border: 1px solid #000000;
        padding: 2px 3px;
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
    .table-asistencia {
        width: 100%;
        border-collapse: collapse;
        font-size: 6.5pt;
    }
    .table-asistencia td, .table-asistencia th {
        border: 1px solid #000000;
        padding: 1.2px 2.5px;
    }
    .footer-text {
        font-size: 6.5pt;
        color: #64748b;
        text-align: center;
    }
</style>

<!-- ENCABEZADO INSTITUCIONAL CON CUADRO DE TIPO A LA DERECHA -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="14%" align="left" valign="middle">
            <?php if (file_exists($rutaLogo)): ?>
                <img src="<?= $rutaLogo ?>" width="46" height="46" />
            <?php endif; ?>
        </td>
        <td width="56%" align="center" valign="middle">
            <div class="inst-title">INSTITUTO DE EDUCACIÓN SUPERIOR PÚBLICO</div>
            <div class="inst-title">“HUANTA”</div>
            <div style="height: 2px;"></div>
            <div class="inst-sub">UNIDAD DE BIENESTAR Y EMPLEABILIDAD</div>
        </td>
        <td width="30%" align="right" valign="top">
            <div class="tipo-box">
                TIPO : NIVELACIÓN, REFORZAMIENTO ACADÉMICO, MENTORÍA .
            </div>
        </td>
    </tr>
</table>

<!-- LÍNEA AZUL DE SEPARACIÓN -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 3px; margin-bottom: 4px;">
    <tr>
        <td style="border-bottom: 2px solid #0284c7; height: 1px;"></td>
    </tr>
</table>

<!-- TÍTULO DEL DOCUMENTO -->
<div class="anexo-title">ANEXO 04</div>
<div class="doc-title">FICHA DE TUTORÍA GRUPAL N° <?= htmlspecialchars(str_pad($sesion['id'] ?? '1', 4, '0', STR_PAD_LEFT)) ?></div>

<!-- TABLA PRINCIPAL DE LA FICHA GRUPAL -->
<table class="table-main" cellpadding="2" cellspacing="0">
    <!-- Tutor y Fecha -->
    <tr>
        <td width="65%"><strong>Tutor(a):</strong> <?= htmlspecialchars($sesion['docente_formato'] ?? '') ?></td>
        <td width="35%"><strong>Fecha:</strong> <?= !empty($sesion['fecha_hora']) ? date('d/m/Y', strtotime($sesion['fecha_hora'])) : date('d/m/Y') ?></td>
    </tr>

    <!-- Programa de Estudios y Semestre/ciclo/periodo -->
    <tr>
        <td width="65%"><strong>Programa de Estudios:</strong> <?= htmlspecialchars($programaNombreStr) ?></td>
        <td width="35%"><strong>Semestre/ciclo/periodo:</strong> <?= htmlspecialchars($periodoSemestreStr) ?></td>
    </tr>

    <!-- Objetivo de la sesión y Horario -->
    <tr>
        <td width="65%"><strong>Objetivo de la sesión:</strong> <?= htmlspecialchars($sesion['objetivo_sesion'] ?? '') ?></td>
        <td width="35%"><strong>Horario:</strong> <?= htmlspecialchars($horarioStr) ?></td>
    </tr>

    <!-- Contenidos tratados -->
    <tr>
        <td colspan="2">
            <strong>Contenidos tratados</strong><br/>
            <?php if (!empty($sesion['contenidos'])): ?>
                <?= nl2br(htmlspecialchars($sesion['contenidos'])) ?>
            <?php else: ?>
                1.<br/>2.
            <?php endif; ?>
        </td>
    </tr>

    <!-- Asistencia de estudiantes (Encabezado) -->
    <tr class="bg-header">
        <td colspan="2" align="center" style="font-size: 7.2pt; letter-spacing: 0.5px;">Asistencia de estudiantes</td>
    </tr>

    <!-- Subtabla de Asistencia (N° | Apellidos y Nombres | Firma) -->
    <tr>
        <td colspan="2" style="padding: 0;">
            <table class="table-asistencia" cellpadding="1.5" cellspacing="0">
                <tr class="bg-sub">
                    <th width="7%" align="center"><strong>N°</strong></th>
                    <th width="65%" align="left"><strong>Apellidos y Nombres</strong></th>
                    <th width="28%" align="center"><strong>Firma</strong></th>
                </tr>
                <?php
                $totalFilas = max(12, count($estudiantes));
                for ($i = 0; $i < $totalFilas; $i++):
                    $est = $estudiantes[$i] ?? null;
                    $asistio = $est ? in_array((int)$est['id_estudiante'], $asistentesIds) : false;
                ?>
                <tr>
                    <td align="center" width="7%"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                    <td width="65%"><?= $est ? htmlspecialchars($est['nombre_completo']) : '&nbsp;' ?></td>
                    <td width="28%" align="center">
                        <?= $asistio ? '<span style="color:#0284c7; font-size:6.2pt; font-weight:bold;">[ ASISTIÓ ]</span>' : '&nbsp;' ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
        </td>
    </tr>

    <!-- Logro del objetivo -->
    <tr>
        <td colspan="2" style="padding: 2px 3px;">
            <table width="100%" cellpadding="1" cellspacing="0" border="0" style="font-size: 7pt;">
                <tr>
                    <td width="28%"><strong>Logro del objetivo: (Marque)</strong></td>
                    <td width="18%">No logrado [ <?= ($logroNormalizado === 'no logrado') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="18%">En proceso [ <?= ($logroNormalizado === 'en proceso') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="18%">Logrado [ <?= ($logroNormalizado === 'logrado') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                    <td width="18%">Sobresaliente [ <?= ($logroNormalizado === 'sobresaliente') ? '<strong>X</strong>' : '&nbsp;&nbsp;' ?> ]</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Observaciones de la sesión -->
    <tr class="bg-sub">
        <td colspan="2"><strong>Observaciones de la sesión :</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="min-height: 20px;">
            <?= !empty($sesion['observaciones_sesion']) ? nl2br(htmlspecialchars($sesion['observaciones_sesion'])) : '<br/>' ?>
        </td>
    </tr>

    <!-- Recomendaciones -->
    <tr class="bg-sub">
        <td colspan="2"><strong>Recomendaciones:</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="min-height: 20px;">
            <?= !empty($sesion['recomendaciones']) ? nl2br(htmlspecialchars($sesion['recomendaciones'])) : '<br/>' ?>
        </td>
    </tr>
</table>

<!-- FIRMAS -->
<div style="height: 16px;"></div>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align: center; font-size: 7.2pt;">
    <tr>
        <td width="44%" align="center">
            __________________________________________<br/>
            <strong>Firma del tutor(a)</strong><br/>
            <span style="font-size: 6.2pt; color: #475569;"><?= htmlspecialchars($sesion['docente_formato'] ?? '') ?></span>
        </td>
        <td width="12%">&nbsp;</td>
        <td width="44%" align="center">
            __________________________________________<br/>
            <strong>V°B° responsable</strong><br/>
            <strong>Unidad de Bienestar y Empleabilidad</strong>
        </td>
    </tr>
</table>

<!-- PIE DE PÁGINA -->
<div style="height: 6px;"></div>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="border-top: 1px solid #cbd5e1; padding-top: 2px;" class="footer-text">
            TEL: 66 322296 &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Dirección: Jr. Córdova N° 650 - Huanta &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Correo: contactos@iestphuanta.edu.pe &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; Web: www.iestphuanta.edu.pe
        </td>
    </tr>
</table>
