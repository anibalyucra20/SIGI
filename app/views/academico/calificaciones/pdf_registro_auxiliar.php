<?php
if (!$permitido) {
?>
    <h1>No tienes permiso para imprimir el documento</h1>
<?php

} else {
    # code...

?>

    <?php
    // ===== Vista TCPDF: Registro Auxiliar (HTML estático) =====
    // Variables esperadas:
    // $id_programacion_ud, $nro_calificacion, $nombreIndicador, $nombreUnidadDidactica
    // $estudiantes, $evaluacionesEstudiante, $promediosEvaluacion, $promedioFinal
    // $estudiantes_inhabilitados (opcional)

    // 1) Construir la plantilla de evaluaciones por ÍNDICE (no por id)
    $plantillaEvaluaciones = [];
    if (!empty($estudiantes) && !empty($evaluacionesEstudiante)) {
        foreach ($estudiantes as $e) {
            $id_det = $e['id_detalle_matricula'] ?? null;
            if ($id_det && !empty($evaluacionesEstudiante[$id_det])) {
                $plantillaEvaluaciones = array_values($evaluacionesEstudiante[$id_det]);
                break;
            }
        }
    }
    // Fallback: si nadie tiene, intenta consolidar cualquiera
    if (empty($plantillaEvaluaciones) && !empty($evaluacionesEstudiante)) {
        foreach ($evaluacionesEstudiante as $evalsAlumno) {
            if (is_array($evalsAlumno) && !empty($evalsAlumno)) {
                $plantillaEvaluaciones = array_values($evalsAlumno);
                break;
            }
        }
    }

    // 2) Mapa de criterios por ÍNDICE de evaluación
    $plantillaCriteriosPorIdx = [];
    foreach ($plantillaEvaluaciones as $iEval => $evTpl) {
        $plantillaCriteriosPorIdx[$iEval] =
            (isset($evTpl['criterios']) && is_array($evTpl['criterios']))
            ? array_values($evTpl['criterios'])
            : [];
    }
    ?>
    <style>
        .t1 {
            font-size: 7px;
        }

        .title {
            font-size: 11px;
            font-weight: bold;
            color: #4153a1;
            text-align: center;
        }

        .subtitle {
            font-size: 10px;
            color: #607d8b;
            text-align: center;
        }

        .th-bg {
            background-color: #f5f7fa;
        }

        .txt-danger {
            color: #c0392b;
            font-weight: bold;
        }

        .txt-primary {
            color: #1f4e79;
            font-weight: bold;
        }

        .txt-center {
            text-align: center;
        }

        .txt-left {
            text-align: left;
        }

        .small {
            font-size: 9px;
            color: #555;
        }
    </style>

    <h4 class="subtitle">
        Registro Auxiliar - Indicador <?= htmlspecialchars($nro_calificacion ?? '') ?>
        <?php if (!empty($nombreIndicador)): ?> - <?= htmlspecialchars($nombreIndicador) ?><?php endif; ?>
            - <span class="title"><?= htmlspecialchars(strtoupper($nombreUnidadDidactica ?? '')) ?></span>
    </h4>

    <table border="1" cellpadding="4" cellspacing="0" width="100%" class="t1">
        <thead>
            <tr class="th-bg">
                <th width="5%" class="txt-center" rowspan="2"><b>ORD.</b></th>
                <th width="10%" class="txt-center" rowspan="2"><b>DNI</b></th>
                <th width="27%" class="txt-center" rowspan="2"><b>APELLIDOS Y NOMBRES</b></th>

                <?php foreach ($plantillaEvaluaciones as $iEval => $evTpl): ?>
                    <?php $criteriosCount = count($plantillaCriteriosPorIdx[$iEval]); ?>
                    <th colspan="<?= (int)($criteriosCount + 1) ?>" class="txt-center th-bg" width="17%">
                        <b><?= htmlspecialchars($evTpl['detalle'] ?? '') ?></b><br>
                        <span class="small">Ponderado: <?= htmlspecialchars($evTpl['ponderado'] ?? '') ?>%</span>
                    </th>
                <?php endforeach; ?>

                <th width="8%" class="txt-center th-bg"><b>PROMEDIO<br>DE CALIFICACIÓN</b></th>
            </tr>

            <?php if (!empty($plantillaEvaluaciones)): ?>
                <tr class="th-bg">
                    <?php foreach ($plantillaEvaluaciones as $iEval => $evTpl):
                        $cant_crit  = count($plantillaCriteriosPorIdx[$iEval]) + 1; // +1 = promedio de esa eval
                        $ancho_crit = 17 / $cant_crit;
                    ?>
                        <?php foreach ($plantillaCriteriosPorIdx[$iEval] as $critTpl): ?>
                            <th class="txt-center" width="<?= $ancho_crit; ?>%">
                                <b><?= htmlspecialchars($critTpl['detalle'] ?? '') ?></b>
                            </th>
                        <?php endforeach; ?>
                        <th class="txt-center" width="<?= $ancho_crit; ?>%">
                            <b>Promedio <?= htmlspecialchars($evTpl['detalle'] ?? '') ?></b>
                        </th>
                    <?php endforeach; ?>
                    <th></th>
                </tr>
            <?php endif; ?>
        </thead>

        <tbody>
            <?php if (!empty($estudiantes)): ?>
                <?php foreach ($estudiantes as $idx => $est): ?>
                    <?php
                    $id_detalle = $est['id_detalle_matricula'] ?? null;

                    // Evaluaciones del alumno COMO LISTA (por índice)
                    $evalsAlumnoList = ($id_detalle && !empty($evaluacionesEstudiante[$id_detalle]))
                        ? array_values($evaluacionesEstudiante[$id_detalle])
                        : [];

                    $inhabilitado = !empty($estudiantes_inhabilitados[$id_detalle]);
                    $motivo = (!empty($est['licencia'])) ? ' (Licencia)' : ' (Inasistencia)';
                    if (!empty($est['licencia'])) $inhabilitado = true;

                    $dni = $est['dni'] ?? '';
                    $nom = $est['apellidos_nombres'] ?? '';
                    $promFin = $promedioFinal[$id_detalle] ?? '';
                    $promFinClass = (is_numeric($promFin) && $promFin < 13) ? 'txt-danger' : 'txt-primary';
                    ?>
                    <tr>
                        <td class="txt-center" width="5%"><?= (int)($idx + 1) ?></td>
                        <td class="txt-center <?= $inhabilitado ? 'txt-danger' : '' ?>" width="10%"><?= htmlspecialchars($dni) ?></td>
                        <td class="<?= $inhabilitado ? 'txt-danger' : '' ?>" width="27%">
                            <?= htmlspecialchars($nom) ?><?= $inhabilitado ? htmlspecialchars($motivo) : '' ?>
                        </td>

                        <?php foreach ($plantillaEvaluaciones as $iEval => $evTpl):
                            $cant_crit  = count($plantillaCriteriosPorIdx[$iEval]) + 1;
                            $ancho_crit = 17 / $cant_crit;

                            // Toma la evaluación del alumno en este ÍNDICE
                            $evAlumno = $evalsAlumnoList[$iEval] ?? null;

                            // Notas por criterio (alineación por ÍNDICE de criterio)
                            $critAlumno = ($evAlumno && !empty($evAlumno['criterios']))
                                ? array_values($evAlumno['criterios']) : [];
                            $colsCrit = count($plantillaCriteriosPorIdx[$iEval]);

                            for ($i = 0; $i < $colsCrit; $i++) {
                                $nota = isset($critAlumno[$i]['calificacion']) ? $critAlumno[$i]['calificacion'] : '';
                                $notaClass = (is_numeric($nota) && $nota < 13) ? 'txt-danger' : 'txt-primary';
                                echo '<td class="txt-center ' . ($inhabilitado ? 'txt-danger' : $notaClass) .
                                    '" width="' . $ancho_crit . '%">' . htmlspecialchars((string)$nota) . '</td>';
                            }

                            // Promedio por evaluación: usa el id de ESTA evaluación del alumno
                            $prom_eval = '';
                            if ($evAlumno && isset($evAlumno['id']) && isset($promediosEvaluacion[$id_detalle][$evAlumno['id']])) {
                                $prom_eval = $promediosEvaluacion[$id_detalle][$evAlumno['id']];
                            }
                            $promClass = (is_numeric($prom_eval) && $prom_eval < 13) ? 'txt-danger' : 'txt-primary';
                        ?>
                            <td class="txt-center <?= $promClass ?>" width="<?= $ancho_crit; ?>%">
                                <?= $prom_eval !== '' ? htmlspecialchars((string)$prom_eval) : '' ?>
                            </td>
                        <?php endforeach; ?>

                        <td width="8%" class="txt-center <?= $promFinClass ?>"><?= htmlspecialchars((string)$promFin) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="999" class="txt-center">No hay estudiantes para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
    if ($datosSistema['logo'] != '') {
        $logoPath = __DIR__ . '/../../../../public/images/' . $datosSistema['logo'];
    } else {
        $logoPath = __DIR__ . '/../../../../public/img/logo_completo.png';
    }
    $pdf->Image($logoMineduPath, 20, 10, 30, 8); // (x, y, width en mm)
    $pdf->Image($logoPath, 240, 10, 30, 10); // (x, y, width en mm)

    ?>

<?php

}
?>