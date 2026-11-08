<?php
// Preprocesamiento de datos del estudiante
$genero = strtoupper(trim($tutorando['genero'] ?? ''));
$edad   = !empty($tutorando['edad']) ? $tutorando['edad'] : '';
$fechaNac = !empty($tutorando['fecha_nacimiento']) && $tutorando['fecha_nacimiento'] !== '0000-00-00'
    ? date('d / m / Y', strtotime($tutorando['fecha_nacimiento']))
    : '   /   /   ';

$apPaterno = $tutorando['ApellidoPaterno'] ?? '';
$apMaterno = $tutorando['ApellidoMaterno'] ?? '';
$nombres   = $tutorando['Nombres'] ?? '';
$dni       = $tutorando['dni'] ?? '';
$distritoNac = $tutorando['distrito_nacimiento'] ?? '';
$direccion = $tutorando['direccion'] ?? '';
$correo    = $tutorando['correo'] ?? '';
$telefono  = $tutorando['telefono'] ?? '';
$programa  = $tutorando['programa_nombre'] ?? 'General';
$semestre  = strtoupper(trim($tutorando['semestre_nombre'] ?? ''));
$discap    = strtoupper(trim($tutorando['discapacidad'] ?? 'NO'));

// Recojo de Información (Preguntas de diagnóstico si las hay)
$itemsMap = [];
if (!empty($items) && is_array($items)) {
    foreach ($items as $it) {
        $itemsMap[mb_strtolower(trim($it['descripcion']))] = (int)$it['valor'];
    }
}
?>
<style>
    body {
        font-family: helvetica, sans-serif;
        color: #000000;
        font-size: 6.8pt;
    }
    .inst-header {
        font-size: 8.5pt;
        font-weight: bold;
        text-align: center;
        line-height: 1.15;
    }
    .anexo-header {
        font-size: 8.5pt;
        font-weight: bold;
        text-align: center;
        margin-top: 2px;
    }
    .titulo-doc {
        font-size: 8.5pt;
        font-weight: bold;
        text-align: center;
    }
    .subtitulo-doc {
        font-size: 7.8pt;
        font-weight: bold;
        text-align: center;
        margin-bottom: 4px;
    }
    .table-socio {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 6.2pt;
    }
    .table-socio td, .table-socio th {
        border: 1px solid #000000;
        padding: 1.5px 2.5px;
        vertical-align: middle;
    }
    .sec-header {
        background-color: #cbd5e1;
        font-weight: bold;
        font-size: 6.5pt;
        padding: 2px 2.5px;
    }
    .bg-label {
        background-color: #e2e8f0;
        font-weight: bold;
    }
    .pie-pagina {
        font-size: 6.2pt;
        text-align: right;
        color: #475569;
    }
</style>

<!-- ============================================================== -->
<!-- PÁGINA 1: SECCIONES I A IV -->
<!-- ============================================================== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="15%" align="left" valign="middle">
            <?php if (file_exists($rutaLogo)): ?>
                <img src="<?= $rutaLogo ?>" width="42" height="42" />
            <?php endif; ?>
        </td>
        <td width="85%" align="center" valign="middle">
            <div class="inst-header">Instituto de Educación Superior Público "Huanta"</div>
            <div class="anexo-header">ANEXO N° 03</div>
            <div class="titulo-doc">FICHA DE EVALUACIÓN SOCIOECONÓMICA</div>
            <div class="subtitulo-doc">DECLARACIÓN JURADA</div>
        </td>
    </tr>
</table>

<!-- I. INFORMACIÓN GENERAL DEL ESTUDIANTE -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="7" class="sec-header">I. INFORMACIÓN GENERAL DEL ESTUDIANTE</td>
    </tr>
    <tr class="bg-label">
        <td width="33%" align="center">Apellido Paterno</td>
        <td width="33%" align="center">Apellidos Materno</td>
        <td width="34%" colspan="5" align="center">Nombres</td>
    </tr>
    <tr>
        <td align="center"><strong><?= htmlspecialchars($apPaterno) ?></strong></td>
        <td align="center"><strong><?= htmlspecialchars($apMaterno) ?></strong></td>
        <td colspan="5" align="center"><strong><?= htmlspecialchars($nombres) ?></strong></td>
    </tr>
    <tr>
        <td class="bg-label" width="10%">Edad</td>
        <td class="bg-label" width="30%">Fecha de Nacimiento</td>
        <td width="15%" align="center"><?= $fechaNac ?></td>
        <td class="bg-label" width="9%">Sexo</td>
        <td width="12%" align="center">F [ <?= ($genero === 'F') ? 'X' : '&nbsp;' ?> ] &nbsp; M [ <?= ($genero === 'M') ? 'X' : '&nbsp;' ?> ]</td>
        <td class="bg-label" width="10%">DNI/CE</td>
        <td width="14%" align="center"><strong><?= htmlspecialchars($dni) ?></strong></td>
    </tr>
    <tr>
        <td class="bg-label" colspan="2">Lugar de nacimiento &nbsp;&nbsp; Región</td>
        <td align="center">Ayacucho</td>
        <td class="bg-label" colspan="2">Provincia</td>
        <td colspan="2" align="center"><?= htmlspecialchars($distritoNac ?: 'Huanta') ?></td>
    </tr>
    <tr>
        <td class="bg-label">E-mail</td>
        <td colspan="2"><?= htmlspecialchars($correo) ?></td>
        <td class="bg-label" colspan="2">Celular</td>
        <td colspan="2" align="center"><?= htmlspecialchars($telefono) ?></td>
    </tr>
    <tr>
        <td class="bg-label" colspan="7">
            Estado Civil: &nbsp;
            Soltero(a) [ X ] &nbsp;&nbsp;&nbsp;
            Casado(a) [ &nbsp; ] &nbsp;&nbsp;&nbsp;
            Conviviente [ &nbsp; ] &nbsp;&nbsp;&nbsp;
            Separado(a) [ &nbsp; ] &nbsp;&nbsp;&nbsp;
            Divorciado(a) [ &nbsp; ] &nbsp;&nbsp;&nbsp;
            Viudo(a) [ &nbsp; ] &nbsp;&nbsp;&nbsp;
            N° hijos [ 0 ]
        </td>
    </tr>
    <tr>
        <td class="bg-label" colspan="3">Domicilio actual /Calle /Jr /Av.</td>
        <td colspan="4"><?= htmlspecialchars($direccion ?: 'Jr. Córdova S/N') ?></td>
    </tr>
    <tr>
        <td class="bg-label">Departamento</td>
        <td align="center">Ayacucho</td>
        <td class="bg-label">Provincia</td>
        <td align="center">Huanta</td>
        <td class="bg-label" colspan="2">Distrito</td>
        <td align="center">Huanta</td>
    </tr>
    <tr>
        <td class="bg-label" colspan="2">Institución educativa de la Educación Básica de Procedencia</td>
        <td colspan="3">Particular Privado [ &nbsp; ] &nbsp; Parroquial [ &nbsp; ] &nbsp; Nacional [ X ]</td>
        <td class="bg-label">Repitió algún año</td>
        <td align="center">SI [ &nbsp; ] &nbsp; NO [ X ]</td>
    </tr>
    <tr>
        <td class="bg-label" colspan="2">¿Obtuvo una beca completa?</td>
        <td align="center">SI [ &nbsp; ] &nbsp; NO [ X ]</td>
        <td class="bg-label" colspan="2">¿Obtuvo una beca parcial?</td>
        <td align="center">SI [ &nbsp; ] &nbsp; NO [ X ]</td>
        <td align="center">No obtuvo beca [ X ]</td>
    </tr>
    <tr>
        <td class="bg-label" colspan="2">Programa de Estudios</td>
        <td colspan="5"><strong><?= htmlspecialchars($programa) ?></strong></td>
    </tr>
    <tr>
        <td class="bg-label">Ciclo/Periodo</td>
        <td colspan="3">
            Sem. 1 [ <?= ($semestre === 'I' || $semestre === '1') ? 'X' : '&nbsp;' ?> ] &nbsp;
            Sem. 2 [ <?= ($semestre === 'II' || $semestre === '2') ? 'X' : '&nbsp;' ?> ] &nbsp;
            Sem. 3 [ <?= ($semestre === 'III' || $semestre === '3') ? 'X' : '&nbsp;' ?> ] &nbsp;
            Sem. 4 [ <?= ($semestre === 'IV' || $semestre === '4') ? 'X' : '&nbsp;' ?> ] &nbsp;
            Sem. 5 [ <?= ($semestre === 'V' || $semestre === '5') ? 'X' : '&nbsp;' ?> ] &nbsp;
            Sem. 6 [ <?= ($semestre === 'VI' || $semestre === '6') ? 'X' : '&nbsp;' ?> ]
        </td>
        <td class="bg-label" colspan="2">Créditos matriculados:</td>
        <td align="center">22</td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- II. COMPOSICIÓN FAMILIAR -->
<table class="table-socio" cellpadding="1.5" cellspacing="0">
    <tr>
        <td colspan="8" class="sec-header">II. COMPOSICIÓN FAMILIAR (Integrantes de su familia)</td>
    </tr>
    <tr class="bg-label" align="center">
        <td width="28%">Nombres y Apellidos</td>
        <td width="12%">Parentesco</td>
        <td width="8%">Sexo</td>
        <td width="8%">Edad</td>
        <td width="12%">Estado Civil</td>
        <td width="14%">Grado Instrucción</td>
        <td width="10%">Ocupación</td>
        <td width="8%">Ingreso S/.</td>
    </tr>
    <?php for ($k = 0; $k < 4; $k++): ?>
    <tr>
        <td height="10">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <?php endfor; ?>
    <tr>
        <td colspan="7" align="right" class="bg-label"><strong>Ingreso Total:</strong></td>
        <td align="center">S/.</td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- III. SITUACIÓN FAMILIAR -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="7" class="sec-header">III. SITUACIÓN FAMILIAR (Integrantes de la familia que viven en su hogar)</td>
    </tr>
    <tr>
        <td class="bg-label" width="15%">Ud. Vive con</td>
        <td width="12%">Padre [ &nbsp; ]</td>
        <td width="12%">Madre [ &nbsp; ]</td>
        <td width="12%">Ambos [ X ]</td>
        <td width="12%">Hnos [ &nbsp; ]</td>
        <td width="15%">Cónyuge [ &nbsp; ]</td>
        <td width="22%">Otros familiares [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Relaciones familiares:</td>
        <td colspan="2" align="center">Buenas [ X ]</td>
        <td colspan="2" align="center">Regulares [ &nbsp; ]</td>
        <td align="center">Deficientes [ &nbsp; ]</td>
        <td align="center">Malas [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label" colspan="2">Apoderado o Familiar de contacto:</td>
        <td colspan="3"><?= htmlspecialchars($tutorando['nombre_completo']) ?> (Titular)</td>
        <td class="bg-label">Celular:</td>
        <td align="center"><?= htmlspecialchars($telefono) ?></td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- IV. VIDA SOCIAL -->
<table class="table-socio" cellpadding="2" cellspacing="0">
    <tr>
        <td class="sec-header">IV. VIDA SOCIAL</td>
    </tr>
    <tr>
        <td>
            ¿Haces amigos con facilidad entre tus compañeros de clase? &nbsp;&nbsp; SI ( X ) &nbsp;&nbsp; NO ( &nbsp; )<br/>
            ¿Prefieres estar solo(a)? &nbsp;&nbsp; SI ( &nbsp; ) &nbsp;&nbsp; NO ( X )<br/>
            ¿Qué deporte(s) practicas? &nbsp;&nbsp; <u>Fútbol / Vóley / Atletismo</u><br/>
            ¿Cómo ocupas tu tiempo libre? &nbsp;&nbsp; <u>Lectura, estudio y apoyo en el hogar</u>
        </td>
    </tr>
</table>

<div style="height: 5px;"></div>
<div class="pie-pagina">IES Público "Huanta" &nbsp;|&nbsp; Página 1 de 2</div>

<!-- ============================================================== -->
<!-- PÁGINA 2: SECCIONES V A VIII Y DECLARACIÓN JURADA -->
<!-- ============================================================== -->
<div style="page-break-before: always;"></div>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="15%" align="left" valign="middle">
            <?php if (file_exists($rutaLogo)): ?>
                <img src="<?= $rutaLogo ?>" width="38" height="38" />
            <?php endif; ?>
        </td>
        <td width="85%" align="center" valign="middle">
            <div class="inst-header">Instituto de Educación Superior Público "Huanta"</div>
            <div style="font-size: 7.8pt; font-weight: bold; color: #475569;">UNIDAD DE BIENESTAR Y EMPLEABILIDAD</div>
        </td>
    </tr>
</table>
<div style="height: 3px;"></div>

<!-- V. ASPECTO ECONÓMICO -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="6" class="sec-header">V. ASPECTO ECONÓMICO (Integrantes de la familia que viven en su hogar)</td>
    </tr>
    <tr>
        <td class="bg-label" width="20%">Dependencia económica</td>
        <td width="16%">Solo Padre [ &nbsp; ]</td>
        <td width="16%">Solo Madre [ &nbsp; ]</td>
        <td width="16%">Ambos [ X ]</td>
        <td width="16%">De sí mismo [ &nbsp; ]</td>
        <td width="16%">Otros [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">¿Trabaja o ha trabajado?</td>
        <td colspan="2">SI [ &nbsp; ] &nbsp;&nbsp; NO [ X ]</td>
        <td class="bg-label">¿Continuará Trabajando?</td>
        <td colspan="2">SI [ &nbsp; ] &nbsp;&nbsp; NO [ X ]</td>
    </tr>
    <tr>
        <td class="bg-label">Empresa / Empleador:</td>
        <td colspan="2">-</td>
        <td class="bg-label">Cargo / Ocupación:</td>
        <td colspan="2">Estudiante</td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- VI. VIVIENDA Y SERVICIOS -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="6" class="sec-header">VI. VIVIENDA Y SERVICIOS (donde reside actualmente)</td>
    </tr>
    <tr>
        <td class="bg-label" width="20%">Tenencia de la vivienda</td>
        <td colspan="5">Propia [ X ] &nbsp;&nbsp;&nbsp; Alquilada [ &nbsp; ] &nbsp;&nbsp;&nbsp; Alojado [ &nbsp; ] &nbsp;&nbsp;&nbsp; Guardianía [ &nbsp; ] &nbsp;&nbsp;&nbsp; Otro [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Tipo de vivienda</td>
        <td colspan="2">Casa [ X ] &nbsp;&nbsp; Departamento [ &nbsp; ] &nbsp;&nbsp; Habitación [ &nbsp; ]</td>
        <td class="bg-label">Material de construcción</td>
        <td colspan="2">Noble [ X ] &nbsp;&nbsp; Adobe [ &nbsp; ] &nbsp;&nbsp; Drywall [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Condición</td>
        <td colspan="2">Terminado [ X ] &nbsp;&nbsp; En construcción [ &nbsp; ]</td>
        <td class="bg-label">N° Dormitorios / Baños</td>
        <td colspan="2">Dormitorios: [ 3 ] &nbsp;&nbsp; Baños: [ 1 ]</td>
    </tr>
    <tr>
        <td class="bg-label">Servicios básicos</td>
        <td colspan="5">Luz [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Agua [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Desagüe [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Teléfono [ &nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Internet [ X ] &nbsp;&nbsp;&nbsp;&nbsp; TV Cable [ &nbsp; ]</td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- VII. ALIMENTACIÓN -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="6" class="sec-header">VII. ALIMENTACIÓN</td>
    </tr>
    <tr>
        <td class="bg-label" width="20%">Se alimenta en:</td>
        <td colspan="5">Su casa [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Restaurante [ &nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Pensión [ &nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Quiosco [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Comidas al día</td>
        <td colspan="5">Desayuno [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Almuerzo [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Cena [ X ] &nbsp;&nbsp;&nbsp;&nbsp; Media mañana [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Subsidio alimentario previo</td>
        <td colspan="5">SI [ &nbsp; ] &nbsp;&nbsp; NO [ X ]</td>
    </tr>
</table>

<div style="height: 3px;"></div>

<!-- VIII. SALUD -->
<table class="table-socio" cellpadding="1.8" cellspacing="0">
    <tr>
        <td colspan="6" class="sec-header">VIII. SALUD (del estudiante)</td>
    </tr>
    <tr>
        <td class="bg-label" width="20%">Seguro de Salud</td>
        <td colspan="5">SIS [ X ] &nbsp;&nbsp;&nbsp;&nbsp; EsSalud [ &nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; EPS [ &nbsp; ] &nbsp;&nbsp;&nbsp;&nbsp; Ninguno [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="bg-label">Tratamiento continuo</td>
        <td colspan="2">SI [ &nbsp; ] &nbsp;&nbsp; NO [ X ]</td>
        <td class="bg-label">Alergias:</td>
        <td colspan="2">Ninguna conocida</td>
    </tr>
    <tr>
        <td class="bg-label">Presenta discapacidad</td>
        <td colspan="2">SI [ <?= ($discap !== 'NO' && !empty($discap)) ? 'X' : '&nbsp;' ?> ] &nbsp;&nbsp; NO [ <?= ($discap === 'NO' || empty($discap)) ? 'X' : '&nbsp;' ?> ]</td>
        <td class="bg-label">Tipo discapacidad:</td>
        <td colspan="2"><?= ($discap !== 'NO') ? htmlspecialchars($discap) : 'Ninguna' ?></td>
    </tr>
    <tr>
        <td class="bg-label">Sufre de alguna enfermedad</td>
        <td colspan="2">SI ( &nbsp; ) &nbsp;&nbsp; NO ( X )</td>
        <td class="bg-label">¿Toma medicamento?</td>
        <td colspan="2">SI ( &nbsp; ) &nbsp;&nbsp; NO ( X )</td>
    </tr>
</table>

<div style="height: 5px;"></div>

<!-- DECLARACIÓN JURADA -->
<table width="100%" cellpadding="2" cellspacing="0" border="0" style="font-size: 6.3pt; text-align: justify; line-height: 1.2;">
    <tr>
        <td>
            La información contenida en el presente documento constituye declaración jurada del informante, quien declara la veracidad de la misma.<br/>
            La información declarada será verificada y en caso que, la información proporcionada sea falsa o distorsionada, se someterá a las sanciones disciplinarias correspondientes, además de las acciones administrativas, civiles y penales a que hubiera lugar conforme a lo previsto por el artículo 411 del código penal.<br/>
            <div style="text-align: right; margin-top: 3px;">
                Huanta, <?= date('d') ?> de <?= date('F') ?> de <?= date('Y') ?>
            </div>
        </td>
    </tr>
</table>

<!-- FIRMAS -->
<div style="height: 25px;"></div>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="text-align: center; font-size: 7.2pt;">
    <tr>
        <td width="45%" align="center">
            __________________________________________<br/>
            <strong>Firma del estudiante</strong><br/>
            <span style="font-size: 6.3pt; color: #475569;">DNI: <?= htmlspecialchars($dni) ?></span>
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" align="center">
            __________________________________________<br/>
            <strong>Firma del padre/madre o apoderado</strong><br/>
            <span style="font-size: 6.3pt; color: #475569;">(en caso de ser menor de edad)</span>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="height: 18px;"></td>
    </tr>
    <tr>
        <td colspan="3" align="center">
            __________________________________________<br/>
            <strong>V° B° Responsable de Bienestar y Empleabilidad</strong>
        </td>
    </tr>
</table>

<div style="height: 5px;"></div>
<div class="pie-pagina">IES Público "Huanta" &nbsp;|&nbsp; Página 2 de 2</div>
