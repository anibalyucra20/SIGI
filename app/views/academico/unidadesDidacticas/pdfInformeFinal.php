<?php
if (!$permitido) {
?>
    <h1>No tienes permiso para imprimir el Informe Final</h1>
<?php

} else {
    # code...

?>
    <style>
        .titulo {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            padding: 5px 0;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
        }

        .info-table,
        .info-table td {
            font-size: 8px;
        }

        .estadistica-table {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 8px;
            margin-left: 20px;
        }

        .estadistica-table th,
        .estadistica-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 2px 4px;
        }
    </style>
    <br>
    <br>
    <br>
    <table width="100%">
        <tr>
            <td width="100%" align="center">
                <div class="titulo">INFORME TÉCNICO - PEDAGÓGICO DEL PERIODO LECTIVO - <?= $periodo_nombre ?></div>
            </td>
        </tr>
    </table>
    <br>
    <br>

    <span class="section-title">I. DATOS INFORMATIVOS:</span><br>
    <table width="100%" class="info-table" cellspacing="5">
        <tr>
            <td width="25%"><b>1. INSTITUCIÓN EDUCATIVA</b></td>
            <td width="5%">:</td>
            <td width="70%"><?= htmlspecialchars($datosSistema['nombre_completo']) ?></td>
        </tr>
        <tr>
            <td><b>2. PROGRAMA DE ESTUDIOS</b></td>
            <td>:</td>
            <td><?= $programa ?></td>
        </tr>
        <tr>
            <td><b>3. MÓDULO FORMATIVO</b></td>
            <td>:</td>
            <td><?= $modulo ?></td>
        </tr>
        <tr>
            <td><b>4. UNIDAD DIDÁCTICA</b></td>
            <td>:</td>
            <td><?= $unidad ?></td>
        </tr>
        <tr>
            <td><b>5. PERIODO ACADÉMICO</b></td>
            <td>:</td>
            <td><?= $periodo_nombre ?></td>
        </tr>
        <tr>
            <td><b>6. DOCENTE</b></td>
            <td>:</td>
            <td><?= $docente ?></td>
        </tr>
    </table>
    <br>
    <br>
    <span class="section-title">II. ASPECTOS TECNICO - PEDAGÓGICOS:</span><br>
    <table width="100%" class="info-table" cellspacing="5">
        <tr>
            <td width="55%"><b>7. PORCENTAJE TOTAL DE AVANCE CURRICULAR:</b></td>
            <td width="45%"> <?= $avance_curricular ?>%</td>
        </tr>
        <tr>
            <td colspan="2"><b>8. U.F. Y TEMA DE LA ULTIMA CLASE DESARROLLADA:</b></td>
        </tr>
        <tr>
            <td colspan="2"><?= $ultima_clase ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>9. TITULO(S) Y Nro DE LA(S) SESIÓN(ES) NO DESARROLLADAS:</b></td>
        </tr>
        <tr>
            <td colspan="2"><?= $sesiones_no_desarrolladas ?></td>
        </tr>
    </table>

    <span class="info-table"><b> 10. RESUMEN ESTADÍSTICO:</b></span><br><br>
    <table class="estadistica-table" width="100%">
        <thead>
            <tr>
                <th width="30%">DESCRIPCIÓN</th>
                <th width="11%">H</th>
                <th width="11%">%</th>
                <th width="11%">M</th>
                <th width="11%">%</th>
                <th width="11%">T</th>
                <th width="11%">%</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="30%"><b>TOTAL MATRICULADOS</b></td>
                <td width="11%"><?= $total_hombres ?></td>
                <td width="11%"><?= $porc_hombres ?>%</td>
                <td width="11%"><?= $total_mujeres ?></td>
                <td width="11%"><?= $porc_mujeres ?>%</td>
                <td width="11%"><?= $total_todos ?></td>
                <td width="11%">100%</td>
            </tr>
            <tr>
                <td><b>RETIRADOS (LICENCIA)</b></td>
                <td><?= $retirados_h ?></td>
                <td><?= $retirados_ph ?>%</td>
                <td><?= $retirados_m ?></td>
                <td><?= $retirados_pm ?>%</td>
                <td><?= $retirados_t ?></td>
                <td><?= $retirados_pt ?>%</td>
            </tr>
            <tr>
                <td><b>APROBADOS</b></td>
                <td><?= $aprobados_h ?></td>
                <td><?= $aprobados_ph ?>%</td>
                <td><?= $aprobados_m ?></td>
                <td><?= $aprobados_pm ?>%</td>
                <td><?= $aprobados_t ?></td>
                <td><?= $aprobados_pt ?>%</td>
            </tr>
            <tr>
                <td><b>DESAPROBADOS</b></td>
                <td><?= $desaprobados_h ?></td>
                <td><?= $desaprobados_ph ?>%</td>
                <td><?= $desaprobados_m ?></td>
                <td><?= $desaprobados_pm ?>%</td>
                <td><?= $desaprobados_t ?></td>
                <td><?= $desaprobados_pt ?>%</td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <table width="100%" class="info-table" cellspacing="5">
        <tr>
            <td width="60%"><b>11. FUE SUPERVISADO</b></td>
            <td width="5%">:</td>
            <td width="5%" style="text-align: center;">SI</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['supervisado'] == 1) ? "X" : ""; ?></td>
            <td width="5%" style="text-align: center;">NO</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['supervisado'] == 0) ? "X" : ""; ?></td>
        </tr>
        <tr>
            <td colspan="3"><b>12. DOCUMENTOS DE EVALUACIÓN UTILIZADAS:</b></td>
        </tr>
        <tr>
            <td> Registro de Evaluación </td>
            <td>:</td>
            <td width="5%" style="text-align: center;">SI</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['reg_evaluacion'] == 1) ? "X" : ""; ?></td>
            <td width="5%" style="text-align: center;">NO</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['reg_evaluacion'] == 0) ? "X" : ""; ?></td>
        </tr>
        <tr>
            <td> Registro Auxiliar</td>
            <td>:</td>
            <td width="5%" style="text-align: center;">SI</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['reg_auxiliar'] == 1) ? "X" : ""; ?></td>
            <td width="5%" style="text-align: center;">NO</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['reg_auxiliar'] == 0) ? "X" : ""; ?></td>
        </tr>
        <tr>
            <td> Programación Curricular</td>
            <td>:</td>
            <td width="5%" style="text-align: center;">SI</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['prog_curricular'] == 1) ? "X" : ""; ?></td>
            <td width="5%" style="text-align: center;">NO</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['prog_curricular'] == 0) ? "X" : ""; ?></td>
        </tr>
        <tr>
            <td> Otros</td>
            <td>:</td>
            <td width="5%" style="text-align: center;">SI</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['otros'] == 1) ? "X" : ""; ?></td>
            <td width="5%" style="text-align: center;">NO</td>
            <td width="5%" style="border: 1px solid #000; text-align: center;"><?= ($datosGenerales['otros'] == 0) ? "X" : ""; ?></td>
        </tr>
    </table>
    <br>
    <br>
    <span class="section-title">III. LOGROS OBTENIDOS:</span><br>
    <span class="info-table" style="text-align: justify;"><?= $datosGenerales['logros_obtenidos'] ?></span><br>
    <span class="section-title">IV. DIFICULTADES:</span><br>
    <span class="info-table" style="text-align: justify;"><?= $datosGenerales['dificultades'] ?></span><br>
    <span class="section-title">V. SUGERENCIAS:</span><br>
    <span class="info-table" style="text-align: justify;"><?= $datosGenerales['sugerencias'] ?></span>
    <?php

    $mes = date('n');
    $anio = date('Y');
    $dia = date('d');
    switch ($mes) {
        case 1:
            $mes = "Enero";
            break;
        case 2:
            $mes = "Febrero";
            break;
        case 3:
            $mes = "Marzo";
            break;
        case 4:
            $mes = "Abril";
            break;
        case 5:
            $mes = "Mayo";
            break;
        case 6:
            $mes = "Junio";
            break;
        case 7:
            $mes = "Julio";
            break;
        case 8:
            $mes = "Agosto";
            break;
        case 9:
            $mes = "Septiembre";
            break;
        case 10:
            $mes = "Octubre";
            break;
        case 11:
            $mes = "Noviembre";
            break;
        case 12:
            $mes = "Diciembre";
            break;
    }
    ?>
    <br>
    <br>
    <table width="100%" style="border: none;" cellspacing="5">
        <tr>
            <td width="100%" style="text-align: right;">Huanta, <?= $dia . ' de ' . $mes . ' del ' . $anio; ?></td>
        </tr>
        <tr>
            <td style="text-align: center;  width:100%;">...........................................</td>
        </tr>
        <tr>
            <td style="text-align: center;  width:100%;">Docente</td>
        </tr>
    </table>
    <span style="text-align: right;"> Huanta, <?= $dia . ' de ' . $mes . ' del ' . $anio; ?></span><br><br><br><br>
    <span style="text-align: center;  width:100%;">...........................................</span>
    <br>
    <span style="text-align: center;  width:100%;">Docente</span>
    <?php
    $html = ob_get_clean();
    $pdf->writeHTML($html, true, false, true, false, '');
    $logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
    if ($datosSistema['logo'] != '') {
        $logoPath = __DIR__ . '/../../../../public/images/' . $datosSistema['logo'];
    } else {
        $logoPath = __DIR__ . '/../../../../public/img/logo_completo.png';
    }
    $pdf->Image($logoMineduPath, 15, 15, 40, 10); // (x, y, width en mm)
    $pdf->Image($logoPath, 163, 15, 40, 12); // (x, y, width en mm)
    ?>
<?php
}
?>
