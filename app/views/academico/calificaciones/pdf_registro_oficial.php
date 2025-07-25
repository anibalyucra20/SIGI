<style>
    .tabla-outer {
        width: 100%;
        table-layout: fixed;
    }

    .tabla-cell {
        vertical-align: top;
        padding: 0 3px;
    }

    .asist-table,
    .indi-table,
    .datos-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 9px;
    }

    .asist-table th,
    .asist-table td,
    .indi-table th,
    .indi-table td,
    .datos-table th,
    .datos-table td {
        border: 1px solid #333;
        padding: 2px 2px;
        text-align: center;
    }

    .titulo {
        font-weight: bold;
        text-align: center;
        font-size: 10px;
    }

    .asist-table th,
    .indi-table th,
    .datos-table th {
        background: #f5f5f5;
    }

    .rojo {
        color: #B30000;
    }

    .azul {
        color: blue;
    }
</style>
<table class="tabla-outer">
    <tr>
        <!-- ASISTENCIAS -->
        <td width="34%" class="tabla-cell">
            <table class="asist-table">
                <?php
                $total_semanas = count($datos_asistencia['sesiones']); // Total de semanas
                $cantida_general = $total_semanas + 4; // Cantidad general de columnas
                $ancho = 100 / $total_semanas; // Ancho de cada columna
                $limiteInasistencia = 30;
                ?>
                <tr>
                    <td colspan="<?= $cantida_general; ?>" width="<?= $ancho * $total_semanas; ?>%">CONTROL DE ASISTENCIA</td>
                </tr>
                <tr>
                    <td rowspan="2" width="<?= $ancho; ?>%" style="font-size:6px;">No</td>
                    <td colspan="<?= $total_semanas; ?>" width="<?= ($ancho * $total_semanas) - (3 * $ancho); ?>%">FECHAS <br>
                        Registra el dia y mes de la asistencia</td>
                    <td rowspan="2" width="<?= $ancho; ?>%" style="font-size:7px;">Total F</td>
                    <td rowspan="2" width="<?= $ancho; ?>%" style="font-size:7px;">% F</td>
                </tr>
                <tr>
                    <?php foreach ($datos_asistencia['sesiones'] as $ses): ?>
                        <td style="font-size:6px;" title="Sesión <?= $ses['semana'] ?>">
                            <?= date('d/m', strtotime($ses['fecha_desarrollo'])) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php
                $contador = 1;
                foreach ($datos_asistencia['estudiantes'] as $est):
                ?>
                    <tr>
                        <?php
                        if ($est['licencia'] != '') {
                        ?>
                            <td class="sticky-col left-col bg-white" style="font-size:7px;"><?= htmlspecialchars($contador) ?></td>
                            <td colspan="<?= $cantida_general - 2; ?>" style="text-center">Licencia</td>
                        <?php
                        } else {
                        ?>
                            <td class="sticky-col left-col bg-white" style="font-size:7px;"><?= htmlspecialchars($contador) ?></td>
                            <?php
                            $faltas = 0;
                            foreach ($datos_asistencia['sesiones'] as $i => $ses):
                                $id_detalle_matricula = $est['id_detalle_matricula'];
                                $id_sesion = $ses['id'];
                                $valor = $datos_asistencia['asistencias'][$id_detalle_matricula][$id_sesion] ?? '';
                                if ($valor === 'F') $faltas++;
                                $class_nota = ($valor === 'F') ? 'rojo' : 'azul';
                            ?>
                                <td class="text-center <?= $class_nota; ?>" style="font-size:8px;"><?= $valor ?: '-' ?></td>
                            <?php endforeach; ?>
                            <?php
                            $porcFaltas = $total_semanas ? round($faltas * 100 / $total_semanas) : 0;
                            $class = $porcFaltas >= $limiteInasistencia ? ' rojo' : ' azul';
                            ?>
                            <td class="text-center <?= $class ?>" style="font-size:7px;"><?= $faltas; ?></td>
                            <td class="text-center <?= $class ?>" style="font-size:7px;"><?= $porcFaltas; ?></td>
                        <?php } ?>
                    </tr>
                <?php

                    $contador++;
                endforeach; ?>
                <?php
                for ($fila = $contador; $fila <= 40; $fila++): ?>
                    <tr>
                        <td style="font-size:7px;"><?= $fila ?></td>
                        <?php for ($col = 1; $col <= $total_semanas; $col++): ?>
                            <td></td>
                        <?php endfor; ?>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endfor; ?>
            </table>
        </td>
        <!-- INDICADORES DE LOGRO -->
        <td width="33%" class="tabla-cell">
            <table class="indi-table">
                <tr>
                    <td class="titulo" colspan="2" style="font-size:10px;">INDICADORES DE LOGRO</td>
                </tr>
                <?php
                $contador_ind_logro_capacidad = 1;
                foreach ($ind_logro_capacidad as $indicador) {
                ?>
                    <tr>
                        <td width="10%"><?= $contador_ind_logro_capacidad; ?></td>
                        <td width="90%" style="text-align:justify;"><?= $indicador['descripcion'] ?>
                        </td>
                    </tr>
                <?php
                    $contador_ind_logro_capacidad++;
                }
                ?>
                <?php for ($i = $contador_ind_logro_capacidad; $i <= 12; $i++): ?>
                    <tr>
                        <td><?= $i; ?></td>
                        <td style="height:30px; text-align:left;">
                        </td>
                    </tr>
                <?php endfor; ?>

            </table>
        </td>
        <?php
        function a_romano($integer, $upcase = true)
        {
            $table = array(
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            );
            $return = '';
            while ($integer > 0) {
                foreach ($table as $rom => $arb) {
                    if ($integer >= $arb) {
                        $integer -= $arb;
                        $return .= $rom;
                        break;
                    }
                }
            }
            return $return;
        }

        ?>
        <!-- DATOS UNIDAD DIDÁCTICA -->
        <td width="33%" class="tabla-cell">
            <table class="datos-table">
                <tr>
                    <th style="text-align:center;" colspan="2">
                        <br>
                        <br>
                        <br>
                    </th>
                </tr>
                <tr>
                    <td colspan="2" class="titulo" style="font-size:20px; padding:10px 2px;">REGISTRO DE EVALUACION Y NOTAS <?= htmlspecialchars($datosGenerales['periodo_lectivo']) ?></td>
                </tr>
                <tr>
                    <th colspan="2" style="height:2px; border:none;"></th>
                </tr>
                <tr>
                    <th class="titulo" colspan="2" style="text-align:center;">PROGRAMA DE ESTUDIOS: <br></th>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center;"><?= htmlspecialchars($datosGenerales['programa']) ?></th>
                </tr>
                <tr>
                    <th colspan="2" style="height:4px; border:none;"><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>MODULO FORMATIVO NRO : </b><?= htmlspecialchars(a_romano($datosGenerales['nro_modulo'])); ?><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>MODULO FORMATIVO:</b></th>
                </tr>
                <tr>
                    <th class="" colspan="2" style="text-align:center;"><?= htmlspecialchars($datosGenerales['modulo']) ?><br></th>
                </tr>
                <tr>
                    <th colspan="2" style="height:4px; border:none;"></th>
                </tr>
                <tr>
                    <th class="titulo" colspan="2" style="text-align:center; font-size:13px;">UNIDAD DIDÁCTICA: <br></th>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center;"><?= htmlspecialchars($datosGenerales['unidad']) ?><br></th>
                </tr>
                <tr>
                    <th colspan="2" style="height:4px; border:none;"></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>PERIODO ACADEMICO :</b> <?= htmlspecialchars($datosGenerales['periodo_academico']) ?> <br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>CREDITOS :</b> <?= htmlspecialchars($datosGenerales['creditos']) ?><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>HORAS POR SEMANA :</b> <?= htmlspecialchars($datosGenerales['horas_semanales']) ?><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>DOCENTE :</b> <?= htmlspecialchars($datosGenerales['docente']) ?><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>SECCION :</b> <?= htmlspecialchars($datosGenerales['seccion']) ?><br></th>
                </tr>
                <tr>
                    <th style="font-size:9px;" colspan="2"><b>TURNO :</b> <?= htmlspecialchars($datosGenerales['turno']) ?><br></th>
                </tr>
                <tr>
                    <th colspan="2" style="height:30px; border:none;"></th>
                </tr>
                <tr>
                    <th colspan="2" style="text-align:center; "><br><br>.................................<br><span style="font-size:10px;">Firma del docente</span></th>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
if ($datosSistema['logo'] != '') {
    $logoPath = __DIR__ . '/../../../../public/images/' . $datosSistema['logo'];
} else {
    $logoPath = __DIR__ . '/../../../../public/img/logo_completo.png';
}
$pdf->Image($logoMineduPath, 200, 15, 30, 8); // (x, y, width en mm)
$pdf->Image($logoPath, 255, 15, 30, 10); // (x, y, width en mm)
$pdf->AddPage(); // Segunda página

ob_start();
?>
<style>
    .notas-outer {
        width: 100%;
        table-layout: fixed;
    }

    .notas-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 9px;
    }

    .notas-table th,
    .notas-table td {
        border: 1px solid #333;
        padding: 2px 2px;
        text-align: center;
    }

    .notas-table th {
        background: #f5f5f5;
    }

    .notas-table .header {
        font-weight: bold;
        font-size: 10px;
    }

    .notas-table .nombre {
        text-align: left;
    }

    .notas-table .azul {
        color: blue;
        font-weight: bold;
    }

    .notas-table .rojo {
        color: red;
        font-weight: bold;
    }

    .notas-table .center {
        text-align: center;
    }

    .notas-table .firma {
        text-align: right;
        font-size: 10px;
        padding-right: 15px;
    }
</style>
<table class="notas-outer">
    <tr>
        <td>
            <table class="notas-table">
                <tr>
                    <th colspan="17">CALIFICACIONES DE COMUNICACIÓN ORAL</th>
                </tr>
                <tr>
                    <th rowspan="2" width="4%" style="font-size:6px;">No<br>de Orden</th>
                    <th rowspan="2" width="36%">APELLIDOS Y NOMBRES</th>
                    <th colspan="12" width="36%">INDICADORES DE LOGRO</th>
                    <th rowspan="2" class="header" width="7%" style="font-size:6px;">PROMEDIO</th>
                    <th rowspan="2" class="header" width="10%" style="font-size:6px;">EVALUACION DE<br>RECUPERACION</th>
                    <th rowspan="2" class="header" width="7%" style="font-size:6px;">NOTA FINAL</th>
                </tr>
                <tr>
                    <?php
                    $contador_ind_logro_capacidad = 1;
                    foreach ($ind_logro_capacidad as $indicador) {
                    ?>
                        <th class="" width="3%"><?= $contador_ind_logro_capacidad ?></th>
                    <?php
                        $contador_ind_logro_capacidad++;
                    }
                    ?>
                    <?php for ($i = $contador_ind_logro_capacidad; $i <= 12; $i++): ?>
                        <th class="" width="3%"><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
                <?php
                $cont_est = 1;
                foreach ($estudiantes as $idx => $est):
                    $id_detalle = $est['id_detalle_matricula'];
                    $inhabilitado = $estudiantes_inhabilitados[$id_detalle] ?? false;
                    $nota_mostrar = (is_array($nota_inasistencia)) ? reset($nota_inasistencia) : '';
                    if ($est['licencia'] != '') {
                        $nota_mostrar = 'Licencia';
                        $inhabilitado = $id_detalle;
                    }
                ?>
                    <tr>
                        <td class="text-center"><?= ($idx + 1) ?></td>
                        <!--<td class="text-center"><?= $est['dni']; ?></td>-->
                        <td style="text-align: left;"><?= $est['apellidos_nombres']; ?></td>
                        <?php
                        $cont_calif = 1;
                        foreach ($nros_calificacion as $nro):
                            if ($inhabilitado):
                                $nota = '';
                            else:
                                $nota = $notas[$id_detalle][$nro] ?? '';
                                $clase = '';
                                if ($nota !== '' && is_numeric($nota)) {
                                    $clase = ($nota < 13) ? "rojo" : "azul";
                                }
                            endif;
                        ?>
                            <td class="text-center <?= $clase; ?>">
                                <?= ($nota === '') ? '' : $nota; ?>
                            </td>
                        <?php

                            $cont_calif++;
                        endforeach; ?>
                        <?php
                        for ($i = $cont_calif; $i <= 12; $i++) { ?>
                            <td>
                            </td>
                        <?php }
                        ?>
                        <?php
                        if ($inhabilitado) {
                            if (is_numeric($nota_mostrar)) { ?>
                                <td class="rojo"><?php echo $nota_mostrar; ?></td>
                                <td></td>
                                <td class="rojo"><?php echo $nota_mostrar; ?></td>
                            <?php } else { ?>
                                <td><?php echo $nota_mostrar; ?></td>
                                <td></td>
                                <td><?php echo $nota_mostrar; ?></td>
                            <?php }
                        } else {
                            $clase_pf = ($promedios[$id_detalle] < 13) ? "rojo" : "azul";
                            $recup = $recuperaciones[$id_detalle] ?? '';
                            $clase_recup = ($recup < 13) ? "rojo" : "azul";
                            $nota_final = ($recup != '') ? $recup : $promedios[$id_detalle];
                            $clase_nota_final = ($nota_final < 13) ? "rojo" : "azul";
                            if (is_array($promedios[$id_detalle])) { ?>
                                <td class="<?= $clase_pf; ?>"><?php echo reset($promedios[$id_detalle]); ?></td>
                                <td class="<?= ($recup != '' && $recup < 13) ? "rojo" : "azul"; ?>">
                                    <?php if ($recup != '') {
                                        echo $recup;
                                    } ?></td>
                                <td class="<?= $clase_nota_final; ?>"><?php echo reset($nota_final); ?></td>
                            <?php
                            } else { ?>
                                <td class="<?= $clase_pf; ?>"><?php echo $promedios[$id_detalle]; ?></td>
                                <td class="<?= ($recup != '' && $recup < 13) ? "rojo" : "azul"; ?>"><?php if ($recup != '') {
                                                                                                        echo $recup;
                                                                                                    } ?></td>
                                <td class="<?= $clase_nota_final; ?>"><?php echo $nota_final; ?></td>
                        <?php }
                        }
                        ?>
                    </tr>
                <?php
                    $cont_est++;
                endforeach;
                for ($i = $cont_est; $i <= 40; $i++) { ?>
                    <tr>
                        <td><?= $i; ?></td>
                        <td></td>
                        <?php
                        for ($j = 0; $j < 12; $j++) { ?>
                            <td></td>
                        <?php } ?>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php }
                ?>
                <!--
                <?php
                // Simulación de 40 estudiantes. Sustituye por tus datos reales.
                for ($fila = 1; $fila <= 40; $fila++):
                    // Cambia aquí por tus datos reales
                    $nombres = $fila <= 15 ? 'APELLIDO NOMBRES ' . $fila : '';
                    $notas = [];
                    for ($c = 1; $c <= 12; $c++) $notas[] = ($fila <= 15 && $c <= 3) ? rand(11, 17) : '';
                    $promedio = $fila <= 15 ? rand(12, 17) : '';
                    $recup = $fila <= 15 && $promedio < 13 ? rand(13, 16) : '';
                    $notafinal = $fila <= 15 ? ($promedio < 13 ? $recup : $promedio) : '';
                ?>
                    <tr>
                        <td><?= $fila ?></td>
                        <td class="nombre"><?= $nombres ?></td>
                        <?php foreach ($notas as $n): ?>
                            <td class="<?= ($n && $n < 13) ? 'rojo' : ($n ? 'azul' : '') ?>"><?= $n ?></td>
                        <?php endforeach; ?>
                        <td class="<?= ($promedio && $promedio < 13) ? 'rojo' : ($promedio ? 'azul' : '') ?>"><?= $promedio ?></td>
                        <td class="center"><?= $recup ?></td>
                        <td class="<?= ($notafinal && $notafinal < 13) ? 'rojo' : ($notafinal ? 'azul' : '') ?>"><?= $notafinal ?></td>
                    </tr>
                <?php endfor; ?>
                        -->
                <tr>
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
                    <td colspan="17" class="firma" style="border:none;">
                        Huanta, <?= $dia . ' de ' . $mes . ' del ' . $anio; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>