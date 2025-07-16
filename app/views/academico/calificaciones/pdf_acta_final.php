<style>
    body {
        font-size: 11px;
    }

    .acta-header {
        width: 100%;
        margin-bottom: 12px;
    }

    .acta-header td {
        font-size: 11px;
        vertical-align: top;
    }

    .titulo {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        margin: 4px 0;
    }

    .subtitulo {
        text-align: center;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .table-acta {
        border-collapse: collapse;
        width: 100%;
    }

    .table-acta th,
    .table-acta td {
        border: 1px solid #222;
        padding: 3px 2px;
        font-size: 7px;
    }

    .table-acta th {
        background: #f5f5f5;
        text-align: center;
        font-size: 7px;
    }

    .table-acta .nombres {
        text-align: left;
    }

    .table-acta .obs {
        font-size: 7px;
    }

    .rojo {
        color: #B30000;
    }

    .azul {
        color: blue;
    }
    .firma {
        text-align: right;
        font-size: 10px;
        padding-right: 15px;
    }
</style>
<?php
function num_letra($num)
{
    $numu = "";
    switch ($num) {
        case 10: {
                $numu = "DIEZ ";
                break;
            }
        case 11: {
                $numu = "ONCE ";
                break;
            }
        case 12: {
                $numu = "DOCE ";
                break;
            }
        case 13: {
                $numu = "TRECE ";
                break;
            }
        case 14: {
                $numu = "CATORCE ";
                break;
            }
        case 15: {
                $numu = "QUINCE ";
                break;
            }
        case 16: {
                $numu = "DIECISEIS ";
                break;
            }
        case 17: {
                $numu = "DIECISIETE ";
                break;
            }
        case 18: {
                $numu = "DIECIOCHO ";
                break;
            }
        case 19: {
                $numu = "DIECINUEVE ";
                break;
            }
        case 20: {
                $numu = "VEINTE";
                break;
            }
        case 9: {
                $numu = "NUEVE";
                break;
            }
        case 8: {
                $numu = "OCHO";
                break;
            }
        case 7: {
                $numu = "SIETE";
                break;
            }
        case 6: {
                $numu = "SEIS";
                break;
            }
        case 5: {
                $numu = "CINCO";
                break;
            }
        case 4: {
                $numu = "CUATRO";
                break;
            }
        case 3: {
                $numu = "TRES";
                break;
            }
        case 2: {
                $numu = "DOS";
                break;
            }
        case 1: {
                $numu = "UNO";
                break;
            }
        case 0: {
                $numu = "CERO";
                break;
            }
    }
    return $numu;
}
?>
<table class="acta-header">
    <tr>
        <td style="text-align:center;" colspan="2" width="100%">
            <div style="font-weight:bold; font-size:8px;">MINISTERIO DE EDUCACIÓN</div>
            <div style="font-size:8px;">DIRECCIÓN GENERAL DE EDUCACIÓN SUPERIOR TÉCNICO PROFESIONAL</div>
            <div class="titulo" style="font-size:9px;">ACTA DE EVALUACIÓN FINAL DE LA UNIDAD DIDÁCTICA</div>
        </td>
    </tr>
</table>

<table style="width:100%; font-size:8px;" cellpadding="1">
    <tr>
        <td style="width:25%;">INSTITUCIÓN EDUCATIVA</td>
        <td style="width:6%;">:</td>
        <td style="width:69%;"><?= htmlspecialchars($datosSistema['nombre_completo']) ?></td>
    </tr>
    <tr>
        <td>PROGRAMA DE ESTUDIOS</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['programa']) ?></td>
    </tr>
    <tr>
        <td>MÓDULO FORMATIVO</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['modulo']) ?></td>
    </tr>
    <tr>
        <td>UNIDAD DIDÁCTICA</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['unidad']) ?></td>
    </tr>
    <tr>
        <td>CRÉDITOS</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['creditos']) ?></td>
    </tr>
    <tr>
        <td>PERIODO ACADÉMICO</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['periodo_academico']) ?> - <?= htmlspecialchars($datosGenerales['periodo_lectivo']) ?></td>
    </tr>
    <tr>
        <td>DOCENTE</td>
        <td>:</td>
        <td><?= htmlspecialchars($datosGenerales['docente']) ?></td>
    </tr>
</table>
<br>
<br>
<table class="table-acta">
    <thead>
        <tr>
            <th rowspan="2" style=" text-align:center; width:5%;">Nro Orden</th>
            <th rowspan="2" style="width:12%; text-align:center;">DNI</th>
            <th rowspan="2" style="width:33%; text-align:center;">APELLIDOS Y NOMBRES</th>
            <th colspan="5" style="width:50%; text-align:center;">EVALUACIÓN FINAL</th>
        </tr>
        <tr>
            <th style="width:8%; text-align:center;">En Números</th>
            <th style="width:10%; text-align:center;">En Letras</th>
            <th style="width:6%; text-align:center;">Créditos</th>
            <th style="width:6%; text-align:center;">Puntaje</th>
            <th style="width:20%; text-align:center;">Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($estudiantes as $idx => $est):
            $id_detalle = $est['id_detalle_matricula'];
            $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;

            $promedio_final = $promedios[$id_detalle];
            if ($inhabilitado) {
                if (is_array($nota_inasistencia)) {
                    $promedio_final = reset($nota_inasistencia);
                } else {
                    $promedio_final = $nota_inasistencia;
                }
            }
            if ($recuperaciones[$id_detalle] != '') {
                $promedio_final = $recuperaciones[$id_detalle];
                if ($recuperaciones[$id_detalle] > 12) {
                    $obs = "Aprobado en Recuperación";
                }
            }
            if ($promedio_final < 13) {
                $obs = "Repite Unidad Didáctica";
                $clase = "rojo";
            } else {
                $clase = "azul";
            }
            if ($est['licencia'] != '') {
                $obs = "Licencia";
                $promedio_final = '';
            }
        ?>
            <tr>
                <td style="text-align:center; width:5%;"><?= ($idx + 1) ?></td>
                <td style="text-align:center; width:12%;"><?= $est['dni'] ?? '' ?></td>
                <td class="nombres" style="width:33%;"><?= $est['apellidos_nombres'] ?? '' ?></td>
                <td style="text-align:center; width:8%;" class="<?= $clase; ?>"><?= $promedio_final ?? '' ?></td>
                <td style="text-align:center; width:10%;"><?= num_letra($promedio_final); ?></td>
                <td style="text-align:center; width:6%;"><?= htmlspecialchars($datosGenerales['creditos']) ?></td>
                <td style="text-align:center;width:6%;"><?= htmlspecialchars($datosGenerales['creditos']) * $promedio_final; ?></td>
                <td class="obs" style="width:20%;"><?= $obs ?? '' ?></td>
            </tr>
        <?php endforeach; ?>
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
        <tr>
            <br>
            <td colspan="8" class="firma" style="border:none;" width="100%">
                        Huanta, <?= $dia . ' de ' . $mes . ' del ' . $anio; ?>
                    </td>
        </tr>
        <tr>
            <br>
            <br>
            <br>
            <td colspan="8" style="text-align:center; width:100%; border:none;">
                        ...............................................................<br><span style="font-size:10px;">docente</span>
                    </td>
        </tr>
    </tbody>
</table>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
$logoPath = (__DIR__ . '/../../../../public/img/logo_completo.png');
$pdf->Image($logoMineduPath, 15, 15, 30); // (x, y, width en mm)
$pdf->Image($logoPath, 165, 15, 30, 10); // (x, y, width en mm)

?>