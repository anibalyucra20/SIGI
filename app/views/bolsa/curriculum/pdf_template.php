<?php
// Plantilla de CV para TCPDF.
// Conserva la estructura de datos entregada por el controlador.
$cv   = $detalle['cv'] ?? [];
$usr  = $detalle['usuario'] ?? [];
$edu  = $detalle['educacion'] ?? [];
$exp  = $detalle['experiencia'] ?? [];
$hab  = $detalle['habilidades'] ?? [];
$idi  = $detalle['idiomas'] ?? [];
$cert = $detalle['certificados'] ?? [];

function cvTexto($valor, $alternativo = '') {
    return htmlspecialchars((string)($valor ?? $alternativo), ENT_QUOTES, 'UTF-8');
}

$nombre    = cvTexto($usr['nombre_completo'] ?? 'ESTUDIANTE / EGRESADO');
$programa  = cvTexto($usr['programa_estudios'] ?? 'Educación Superior');
$dni       = cvTexto($usr['dni'] ?? '');
$correo    = cvTexto($usr['correo'] ?? '');
$telefono  = cvTexto($usr['telefono'] ?? '');
$direccion = cvTexto($usr['direccion'] ?? '');
$linkCv    = !empty($cv['archivo']) ? cvTexto($cv['archivo']) : '';

// Se utiliza únicamente si el sistema ya guarda alguno de estos campos.
$perfil = $cv['perfil_profesional'] ?? $cv['perfil'] ?? $usr['perfil_profesional'] ?? '';
$perfil = trim((string)$perfil);

function cvFecha($fecha, $alternativo = '') {
    if (empty($fecha)) return $alternativo;
    $marca = strtotime((string)$fecha);
    return $marca ? date('m/Y', $marca) : '';
}
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #263445;
        font-size: 9.5pt;
        line-height: 1.35;
    }
    table { border-collapse: collapse; }
    .top-band {
        background-color: #17365D;
        color: #FFFFFF;
    }
    .header-table { margin-bottom: 8px; }
    .candidate-name {
        font-size: 21pt;
        font-weight: bold;
        color: #17365D;
        line-height: 1.15;
    }
    .candidate-title {
        font-size: 10.5pt;
        color: #356AA0;
        font-weight: bold;
        padding-top: 4px;
    }
    .contact-info {
        font-size: 8.3pt;
        color: #435267;
        line-height: 1.55;
    }
    .contact-label {
        color: #17365D;
        font-weight: bold;
    }
    .rule {
        height: 2px;
        background-color: #356AA0;
        color: #356AA0;
        margin: 7px 0 10px 0;
    }
    .section-title {
        font-size: 10.5pt;
        font-weight: bold;
        color: #17365D;
        border-bottom: 1px solid #B7C9DC;
        padding-bottom: 4px;
        margin-top: 10px;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    .item-title {
        font-size: 9.5pt;
        font-weight: bold;
        color: #263445;
    }
    .item-subtitle {
        font-size: 8.8pt;
        color: #526174;
    }
    .item-date {
        font-size: 8pt;
        color: #526174;
        text-align: right;
    }
    .item-desc {
        font-size: 8.7pt;
        color: #435267;
        line-height: 1.4;
    }
    .level {
        font-size: 8.3pt;
        color: #356AA0;
        font-weight: bold;
        text-align: right;
    }
    .summary {
        font-size: 9pt;
        color: #435267;
        line-height: 1.45;
    }
    .cv-name {
        font-size: 9pt;
        color: #17365D;
        font-weight: bold;
        background-color: #F2F6FA;
        padding: 6px;
    }
    .small-link {
        color: #356AA0;
        font-size: 7.8pt;
    }
    .spacer-row { height: 5px; }
    .footer {
        font-size: 7.5pt;
        color: #7A8797;
        border-top: 0.5px solid #D9E1EA;
        padding-top: 5px;
    }
</style>

<!-- ENCABEZADO -->
<table width="100%" cellpadding="0" cellspacing="0" class="header-table">
    <tr>
        <td width="62%" valign="top">
            <div class="candidate-name"><?= $nombre ?></div>
            <div class="candidate-title"><?= $programa ?></div>
        </td>
        <td width="38%" valign="top" class="contact-info">
            <?php if ($telefono !== ''): ?>
                <span class="contact-label">Teléfono:</span> <?= $telefono ?><br>
            <?php endif; ?>
            <?php if ($correo !== ''): ?>
                <span class="contact-label">Correo:</span> <?= $correo ?><br>
            <?php endif; ?>
            <?php if ($direccion !== ''): ?>
                <span class="contact-label">Dirección:</span> <?= $direccion ?><br>
            <?php endif; ?>
            <?php if ($dni !== ''): ?>
                <span class="contact-label">DNI:</span> <?= $dni ?>
            <?php endif; ?>
        </td>
    </tr>
</table>
<hr class="rule">

<?php if (!empty($cv['descripcion'])): ?>
    <div class="section-title">Currículum</div>
    <div class="cv-name"><?= cvTexto($cv['descripcion']) ?></div>
<?php endif; ?>

<?php if ($perfil !== ''): ?>
    <div class="section-title">Perfil profesional</div>
    <div class="summary"><?= nl2br(cvTexto($perfil)) ?></div>
<?php endif; ?>

<?php if (!empty($exp)): ?>
    <div class="section-title">Experiencia laboral</div>
    <table width="100%" cellpadding="3" cellspacing="0">
        <?php foreach ($exp as $e):
            $fInicio = cvFecha($e['inicio'] ?? '');
            $fFin = !empty($e['termino']) ? cvFecha($e['termino']) : 'Actualidad';
            $tiempo = !empty($e['tiempo_cargo']) ? ' · ' . cvTexto($e['tiempo_cargo']) : '';
        ?>
            <tr>
                <td width="70%" valign="top">
                    <span class="item-title"><?= cvTexto($e['cargo'] ?? 'Cargo') ?></span><br>
                    <span class="item-subtitle"><?= cvTexto($e['institucion'] ?? '') ?></span>
                </td>
                <td width="30%" valign="top" class="item-date">
                    <?= cvTexto($fInicio) ?><?= ($fInicio !== '' || $fFin !== '') ? ' – ' : '' ?><?= cvTexto($fFin) ?><?= $tiempo ?>
                </td>
            </tr>
            <?php if (!empty($e['descripcion']) || !empty($e['archivo'])): ?>
                <tr>
                    <td colspan="2" class="item-desc" style="padding: 1px 3px 6px 7px;">
                        <?php if (!empty($e['descripcion'])): ?>
                            <?= nl2br(cvTexto($e['descripcion'])) ?>
                        <?php endif; ?>
                        <?php if (!empty($e['archivo'])): ?>
                            <br><a href="<?= cvTexto($e['archivo']) ?>" class="small-link">Ver constancia laboral</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr><td colspan="2" class="spacer-row"></td></tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!empty($edu)): ?>
    <div class="section-title">Formación académica</div>
    <table width="100%" cellpadding="3" cellspacing="0">
        <?php foreach ($edu as $ed):
            $fInicio = cvFecha($ed['inicio'] ?? '');
            $fFin = !empty($ed['termino']) ? cvFecha($ed['termino']) : 'En curso';
            $especialidad = $ed['especialidad'] ?? '';
            $nivel = $ed['nivel_academico'] ?? '';
        ?>
            <tr>
                <td width="72%" valign="top">
                    <span class="item-title"><?= cvTexto($especialidad ?: $nivel ?: 'Estudios') ?></span>
                    <?php if ($especialidad && $nivel): ?>
                        <span class="item-subtitle"> (<?= cvTexto($nivel) ?>)</span>
                    <?php endif; ?>
                    <br><span class="item-subtitle"><?= cvTexto($ed['centro_estudios'] ?? '') ?></span>
                    <?php if (!empty($ed['archivo'])): ?>
                        <br><a href="<?= cvTexto($ed['archivo']) ?>" class="small-link">Ver constancia</a>
                    <?php endif; ?>
                </td>
                <td width="28%" valign="top" class="item-date">
                    <?= cvTexto($fInicio) ?><?= ($fInicio !== '' || $fFin !== '') ? ' – ' : '' ?><?= cvTexto($fFin) ?>
                </td>
            </tr>
            <tr><td colspan="2" class="spacer-row"></td></tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!empty($cert)): ?>
    <div class="section-title">Cursos y certificaciones</div>
    <table width="100%" cellpadding="3" cellspacing="0">
        <?php foreach ($cert as $c):
            $fFin = cvFecha($c['termino'] ?? '');
            $horas = !empty($c['horas']) ? ' · ' . (int)$c['horas'] . ' horas' : '';
        ?>
            <tr>
                <td width="72%" valign="top">
                    <span class="item-title"><?= cvTexto($c['nombre'] ?? 'Curso o certificación') ?></span>
                    <?php if (!empty($c['institucion'])): ?>
                        <br><span class="item-subtitle"><?= cvTexto($c['institucion']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($c['tema'])): ?>
                        <br><span class="item-desc">Tema: <?= cvTexto($c['tema']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($c['archivo'])): ?>
                        <br><a href="<?= cvTexto($c['archivo']) ?>" class="small-link">Ver certificado</a>
                    <?php endif; ?>
                </td>
                <td width="28%" valign="top" class="item-date">
                    <?= cvTexto($fFin) ?><?= $horas ?>
                </td>
            </tr>
            <tr><td colspan="2" class="spacer-row"></td></tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!empty($hab)): ?>
    <div class="section-title">Habilidades y competencias</div>
    <table width="100%" cellpadding="3" cellspacing="0">
        <?php foreach ($hab as $h): ?>
            <tr>
                <td width="72%" class="item-desc">• <?= cvTexto($h['titulo'] ?? '') ?></td>
                <td width="28%" class="level"><?= cvTexto($h['nivel'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!empty($idi)): ?>
    <div class="section-title">Idiomas</div>
    <table width="100%" cellpadding="3" cellspacing="0">
        <?php foreach ($idi as $i): ?>
            <tr>
                <td width="72%" class="item-desc">
                    <span class="item-title"><?= cvTexto($i['idioma'] ?? '') ?></span>
                    <?php if (!empty($i['archivo'])): ?>
                        <br><a href="<?= cvTexto($i['archivo']) ?>" class="small-link">Ver certificado</a>
                    <?php endif; ?>
                </td>
                <td width="28%" class="level"><?= cvTexto($i['nivel'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<br><br>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="footer">
            Currículum generado mediante el Sistema Integrado de Gestión Institucional (SIGI) · <?= date('d/m/Y') ?>
        </td>
    </tr>
</table>
