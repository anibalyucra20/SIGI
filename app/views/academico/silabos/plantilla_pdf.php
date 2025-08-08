<?php
if (!$permitido) {
?>
    <h1>No tienes permiso para imprimir el silabo</h1>
<?php

} else {
    # code...

?>

    <style>
        body {
            font-size: 9px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            border: 1px solid #444;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        td {
            border: 1px solid #444;
            padding: 5px;
        }

        .no-border {
            border: none !important;
        }

        .titulo {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
    <!-- Puedes incluir tu header institucional aquí en HTML, o dejar que TCPDF lo pinte -->
    <div class="titulo">SÍLABO</div>
    <span class="section-title">I. DATOS GENERALES</span><br>
    <table>
        <tr>
            <td width="30%" style="border: 1px solid white;"><b>Programa de Estudios:</b></td>
            <td width="70%" style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['programa']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Módulo Formativo:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['modulo']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Unidad Didáctica:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['unidad']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Créditos:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['creditos']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Horas Totales:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['horas_totales']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Horas Semanales:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['horas_semanales']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Periodo Lectivo:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['periodo_lectivo']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Periodo Académico:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['periodo_academico']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Fecha Inicio:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['fecha_inicio']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Fecha Fin:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['fecha_fin']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Turno:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['turno']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Docente:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['docente']) ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid white;"><b>Correo Institucional:</b></td>
            <td style="border: 1px solid white;"><?= htmlspecialchars($datosGenerales['correo_docente']) ?></td>
        </tr>
    </table>
    <br><br>
    <span class="section-title">II. SUMILLA</span>
    <div><?= nl2br(htmlspecialchars($silabo['sumilla'])) ?></div>
    <br>
    <span class="section-title">III. UNIDAD DE COMPETENCIA ESPECÍFICA O TÉCNICA DEL MÓDULO</span>
    <div>
        <?php foreach ($competenciasUnidadDidactica as $comp): ?>
            <b><?= htmlspecialchars($comp['codigo']) ?>:</b> <?= htmlspecialchars($comp['descripcion']) ?>
        <?php endforeach; ?>
    </div><br>
    <span class="section-title">IV. CAPACIDADES DE LA UNIDAD DIDÁCTICA</span>
    <br>
    <table>
        <tr>
            <th width="30%"><b>Capacidad</b></th>
            <th width="70%"><b>Indicadores de Logro</b></th>
        </tr>
        <?php foreach ($capacidades as $cap): ?>
            <tr>
                <td><?= htmlspecialchars($cap['descripcion']) ?></td>
                <td>
                    <ul>
                        <?php foreach ($cap['indicadores'] as $ind): ?>
                            <li><?= htmlspecialchars($ind['descripcion']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <br>
    <span class="section-title">V. COMPETENCIA PARA LA EMPLEABILIDAD COMO CONTENIDO TRANSVERSAL</span>
    <br>
    <table>
        <tr>
            <th width="50%">Competencia para la empleabilidad como contenido transversal</th>
            <th>Estrategias</th>
        </tr>
        <?php foreach ($competenciasTransversales as $ct): ?>
            <tr>
                <td><?= htmlspecialchars($ct['codigo']) ?>: <?= htmlspecialchars($ct['descripcion']) ?></td>
                <td>
                    <ul>
                        <?php foreach ($ct['estrategias'] as $estr): ?>
                            <li><?= htmlspecialchars($estr) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <br>
    <span class="section-title">VI. PROGRAMACIÓN DE SESIONES DE APRENDIZAJE</span>
    <br>
    <table style="font-size: 11px;">
        <tr>
            <th width="12%">Semana / Fecha</th>
            <th width="20%">Indicador de Logro de Capacidad</th>
            <th width="16%">Denominación de la Sesión</th>
            <th width="25%">Contenido</th>
            <th width="14%">Logro de la Sesión</th>
            <th width="13%">Tareas Previas</th>
        </tr>
        <?php
        $posicion = 0;
        $contar = 0;
        $anterior = "";
        foreach ($sesiones as $sesion):
            // calcular cantidad de semanas que tiene el mismo indicador de logro
            $actual = $sesion['codigo_ind_logro'];
            if ($anterior != $actual) {
                for ($i = $posicion; $i < count($sesiones); $i++) {
                    $anterior = $actual;
                    $actual = $sesiones[$i]['codigo_ind_logro'];
                    if ($anterior == $actual) {
                        //echo $sesiones[$i]['codigo_ind_logro'];
                        $contar++;
                    } else {
                        break;
                    }
                }
            }
            $posicion++;

        ?>
            <tr>
                <td><?= htmlspecialchars($sesion['semana']) ?><br><?= htmlspecialchars($sesion['fecha']) ?></td>
                <?php if ($contar > 0) { ?>
                    <td rowspan="<?= $contar; ?>"><?= htmlspecialchars($sesion['codigo_ind_logro']) ?> - <?= htmlspecialchars(mb_substr($sesion['desc_ind_logro'], 0, 200)) ?></td>
                <?php } ?>
                <td><?= htmlspecialchars($sesion['denominacion']) ?></td>
                <td><?= htmlspecialchars($sesion['contenidos_basicos']) ?></td>
                <td><?= htmlspecialchars($sesion['logro_sesion']) ?></td>
                <td><?= htmlspecialchars($sesion['tareas_previas']) ?></td>
            </tr>
        <?php
            $contar = 0;
        endforeach; ?>
    </table>
    <br>
    <br>
    <span class="section-title">VII. METODOLOGÍA</span>
    <div><?= nl2br(htmlspecialchars($silabo['metodologia'])) ?></div>
    <br>
    <span class="section-title">VIII. AMBIENTES Y RECURSOS</span>
    <div><?= nl2br(htmlspecialchars($silabo['recursos_didacticos'])) ?></div>
    <br>
    <span class="section-title">IX. SISTEMA DE EVALUACIÓN</span>
    <div><?= nl2br(htmlspecialchars($silabo['sistema_evaluacion'])) ?></div>
    <br>
    <span class="section-title">X. FUENTES DE INFORMACIÓN</span>
    <br>
    <b>10.1 Bibliografía (Impresos):</b>
    <div><?= nl2br(htmlspecialchars($silabo['recursos_bibliograficos_impresos'])) ?></div>
    <b>  10.2 URL (digitales):</b>
    <div><?= nl2br(htmlspecialchars($silabo['recursos_bibliograficos_digitales'])) ?></div>
    <br>
    <span class="section-title">FECHA:</span>
    <?php
    $hoy = new DateTime();
    $fmt = new IntlDateFormatter(
        'es-ES',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'America/Lima',
        IntlDateFormatter::GREGORIAN,
        'dd \'de\' MMMM \'del\' yyyy'
    );
    echo $fmt->format($hoy);
    ?>
    <br>
    <br>
    <span class="section-title">APROBADO POR:</span>
    <br>
    <table>
        <thead>
            <tr>
                <td colspan="2" style="border: 1px solid white; text-align: center;"><br><br><br><br>Docente de la Unidad Académica</td>
            </tr>
            <tr>
                <td style="border: 1px solid white; text-align: center;"><br><br><br><br><br><br>Jefe de la Unidad Académica</td>
                <td style="border: 1px solid white; text-align: center;"><br><br><br><br><br><br>Coordinador del programa de estudios</td>
            </tr>
        </thead>
    </table>

<?php

}
?>