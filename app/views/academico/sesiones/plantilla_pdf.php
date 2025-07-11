
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    th {
        border: 1px solid #333;
        padding: 4px;
        vertical-align: top;
        text-align: center;
    }

    td {
        border: 1px solid #333;
        padding: 4px;
        vertical-align: top;
    }

    .titulo {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .cuerpo {
        text-align: justify;
        font-size: 11px;
        margin-bottom: 10px;
    }

    .section-title {
        font-size: 12px;
        font-weight: bold;
        margin: 10px 0 5px 0;
        text-align: center;
    }

    .gris {
        background-color: grey;
    }
</style>
<div class="titulo">SESIÓN DE APRENDIZAJE</div>
<br>

<table>
    <tr>
        <td colspan="2" style="background-color:#bebdbd;"><span class="section-title">I. INFORMACIÓN GENERAL</span></td>
    </tr>
    <tr>
        <td width="40%" style="background-color:#bebdbd;"><b>Programa de estudios:</b></td>
        <td width="60%"><?= htmlspecialchars($datosUnidad['programa']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Módulo Formativo:</b></td>
        <td><?= htmlspecialchars($datosUnidad['modulo']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Unidad de competencia:</b></td>
        <td style="text-align: justify; !important"><?= htmlspecialchars($datosUnidad['unidad_competencia']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Unidad didáctica:</b></td>
        <td><?= htmlspecialchars($datosUnidad['unidad']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Periodo Lectivo:</b></td>
        <td><?= htmlspecialchars($datosUnidad['periodo_lectivo']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Periodo académico:</b></td>
        <td><?= htmlspecialchars($datosUnidad['periodo_academico']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Capacidad:</b></td>
        <td style="text-align: justify; !important"><?= htmlspecialchars($datosUnidad['capacidad']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Indicador de logro vinculado:</b></td>
        <td style="text-align: justify;">
            <b><?= htmlspecialchars($datosUnidad['ind_logro_codigo']) ?></b>
            <?= htmlspecialchars($datosUnidad['ind_logro_descripcion']) ?>
        </td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Competencia transversal priorizada:</b></td>
        <td style="text-align: justify; !important"><?= htmlspecialchars($datosUnidad['comp_transversal']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Sesión de Aprendizaje:</b></td>
        <td><?= htmlspecialchars($sesion['denominacion']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Fecha de desarrollo:</b></td>
        <td><?= htmlspecialchars($sesion['fecha_desarrollo']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Tipo de actividad:</b></td>
        <td><?= htmlspecialchars($sesion['tipo_actividad']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Logro de la sesión:</b></td>
        <td><?= htmlspecialchars($sesion['logro_sesion']) ?></td>
    </tr>
    <tr>
        <td style="background-color:#bebdbd;"><b>Docente responsable:</b></td>
        <td><?= htmlspecialchars($datosUnidad['docente']) ?></td>
    </tr>
</table>
<br>
<br>
<table>
    <thead>
        <tr style="background-color:#bebdbd;">
            <th colspan="4"><span class="section-title">II. ACTIVIDADES DE APRENDIZAJE</span></th>
        </tr>
        <tr style="background-color:#c7e1ff;">
            <th style="width: 20%;"><b>Momentos</b></th>
            <th style="width: 50%;"><b>Actividades de aprendizaje</b></th>
            <th style="width: 20%;"><b>Recursos didácticos</b></th>
            <th style="width: 10%;"><b>Tiempo</b></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($momentos as $m): ?>
            <tr>
                <td style="width: 20%;"><?= htmlspecialchars($m['momento']) ?></td>
                <td style="width: 50%;  text-align: justify; !important"><?= nl2br(htmlspecialchars($m['actividad'])) ?></td>
                <td style="width: 20%;  text-align: justify; !important"><?= nl2br(htmlspecialchars($m['recursos'])) ?></td>
                <td style="width: 10%;"><?= htmlspecialchars($m['tiempo']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br>
<br>
<table>
    <thead>
        <tr style="background-color:#bebdbd;">
            <th colspan="4"><span class="section-title">III. ACTIVIDADES DE EVALUACIÓN</span></th>
        </tr>
        <tr style="background-color:#c7e1ff;">
            <th style="width: 30%;"><b>Indicador de logro de la sesión</b></th>
            <th style="width: 25%;"><b>Técnicas</b></th>
            <th style="width: 25%;"><b>Instrumentos</b></th>
            <th style="width: 20%;"><b>Momento</b></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($activEval as $a): ?>
            <tr>
                <td style="width: 30%; text-align: justify; !important"><?= nl2br(htmlspecialchars($a['indicador_logro_sesion'])) ?></td>
                <td style="width: 25%; text-align: justify; !important"><?= nl2br(htmlspecialchars($a['tecnica'])) ?></td>
                <td style="width: 25%; text-align: justify; !important"><?= nl2br(htmlspecialchars($a['instrumentos'])) ?></td>
                <td style="width: 20%;"><?= htmlspecialchars($a['momento']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br><br>
<span class="section"><b>IV. BIBLIOGRAFÍA (APA)</b></span>
<div class="cuerpo"><?= nl2br(htmlspecialchars($sesion['bibliografia_obligatoria_docente'] ?? '')) ?></div>
<br><br><br>
<table>
    <tr>
        <td width="33%" align="center" style="border: 1px solid white;">______________________<br>Docente</td>
        <td width="33%" align="center" style="border: 1px solid white;">______________________<br>Coordinador del PE</td>
        <td width="33%" align="center" style="border: 1px solid white;">______________________<br>Jefe Unidad Académica</td>
    </tr>
</table>