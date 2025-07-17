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
        margin-bottom: 9px;
    }

    .table-acta {
        border-collapse: collapse;
        width: 100%;
    }

    .table-acta th,
    .table-acta td {
        border: 1px solid #222;
        padding: 3px 2px;
        font-size: 9px;
    }

    .table-acta th {
        background: #f5f5f5;
        text-align: center;
        font-size: 9px;
    }

    .table-acta .nombres {
        text-align: left;
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
<table class="acta-header">
    <tr>
        <td style="text-align:center;" colspan="2" width="100%">
            <div class="titulo" style="font-size:10px;">ACTA DE EVALUACIÓN DE RECUPERACIÓN</div>
            <br>
        </td>
    </tr>
</table>

<table style="width:100%; font-size:9px;" cellpadding="1">
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
            <th rowspan="2" style=" text-align:center; width:10%;">Nro Orden</th>
            <th rowspan="2" style="width:15%; text-align:center;">DNI</th>
            <th rowspan="2" style="width:40%; text-align:center;">APELLIDOS Y NOMBRES</th>
            <th colspan="3" style="width:35%; text-align:center;">LOGRO FINAL</th>
        </tr>
        <tr>
            <th style="width:13%; text-align:center;">En Números</th>
            <th style="width:11%; text-align:center;">Créditos</th>
            <th style="width:11%; text-align:center;">Puntaje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($estudiantes as $idx => $est):
            $id_detalle = $est['id_detalle_matricula'];

            if ($recuperaciones[$id_detalle] != '') {
                $promedio_final = $recuperaciones[$id_detalle];
                if ($promedio_final < 13) {
                    $clase = "rojo";
                } else {
                    $clase = "azul";
                }
        ?>
                <tr>
                    <td style="text-align:center; width:10%;"><?= ($idx + 1) ?></td>
                    <td style="text-align:center; width:15%;"><?= $est['dni'] ?? '' ?></td>
                    <td class="nombres" style="width:40%;"><?= $est['apellidos_nombres'] ?? '' ?></td>
                    <td style="text-align:center; width:13%;" class="<?= $clase; ?>"><?= $promedio_final ?? '' ?></td>
                    <td style="text-align:center; width:11%;"><?= htmlspecialchars($datosGenerales['creditos']) ?></td>
                    <td style="text-align:center;width:11%;"><?= htmlspecialchars($datosGenerales['creditos']) * $promedio_final; ?></td>
                </tr>

        <?php   }
        endforeach; ?>
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