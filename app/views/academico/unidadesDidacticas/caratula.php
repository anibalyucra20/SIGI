<?php
if (!$permitido) {

?>
    <h1>No tienes permiso para imprimir la caratula</h1>
<?php

} else {
    $horario_raw = $silabo['horario'] ?? '';
    if (class_exists(\App\Helpers\HorarioHelper::class)) {
        try {
            // Mostrar bonito si viene JSON u objeto; si ya es texto, lo deja igual
            $horarioPretty = \App\Helpers\HorarioHelper::pretty($horario_raw);
        } catch (\Throwable $e) {
            $horarioPretty = $horario_raw; // fallback
        }
    } else {
        $horarioPretty = $horario_raw; // fallback sin helper
    }
?>
    <style>
        .titulo {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            padding: 8px 0;
            border: 2px solid #000;
            margin: 16px 0;

        }

        .subtitulo {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            border: 1px solid #000;
        }

        .info-table td {
            font-size: 12px;
            padding: 2px 4px;
        }

        .hr-dotted {
            border-top: 1px dotted #000;
            margin: 30px 0 8px 0;
        }
    </style>
    <br>
    <br>
    <br>
    <br>
    <table width="100%">
        <tr>
            <td width="100%" align="center" style="font-size:13px;">
                <b><?= htmlspecialchars($datosSistema['nombre_completo']) ?></b>
            </td>

        </tr>
    </table>
    <br><br><br>
    <table width="100%">
        <tr>
            <td width="20%"></td>
            <td width="60%" style="text-align: center; font-size: 20px; border: 2px solid #000;"><br><br><b>REGISTRO ACADÉMICO</b><br></td>
            <td width="20%"></td>
        </tr>
    </table>
    <br>
    <br>
    <br>
    <table width="100%" style="margin-bottom: 10px;">
        <tr>
            <td align="center" style="font-size:14px;">
                <b>PROGRAMA DE ESTUDIOS</b><br>
                <span style="font-size: 16px;"><?= htmlspecialchars($datosGenerales['programa']) ?></span>
            </td>
        </tr>
    </table>
    <br>
    <br>
    <br>
    <table width="100%" style="margin-bottom: 10px;">
        <tr>
            <td align="center" style="font-size:13px;">
                <b>MÓDULO FORMATIVO</b><br>
                <?= htmlspecialchars($datosGenerales['modulo']) ?>
            </td>
        </tr>
    </table>
    <br>
    <br>
    <table width="100%">
        <tr>
            <td width="20%"></td>
            <td width="60%" style="text-align: center; font-size: 13px; border: 1px solid #000;"><br><b>UNIDAD DIDÁCTICA<br></b><span style="font-size:13px;"><?= htmlspecialchars($datosGenerales['unidad']) ?></span></td>
            <td width="20%"></td>
        </tr>
    </table>
    <br>
    <br>
    <table width="100%">
        <tr>
            <td width="20%"></td>
            <td width="60%" style="text-align: center; font-size: 13px; border: 1px solid #000;"><br><b>HORARIO<br></b><span style="font-size:13px;"><?php echo htmlspecialchars($horarioPretty); ?></span></td>
            <td width="20%"></td>
        </tr>
    </table>
    <br>
    <br>
    <table width="100%" class="info-table" style="margin-bottom: 10px;" cellspacing="5">
        <tr>
            <td width="15%"></td>
            <td width="30%"><b>HORAS SEMANAL</b></td>
            <td width="10%">: <?= htmlspecialchars($datosGenerales['horas_semanales']) ?></td>
            <td><b>SEMESTRAL</b></td>
            <td>: <?= htmlspecialchars($datosGenerales['horas_totales']) ?></td>
        </tr>
        <tr>
            <td></td>
            <td><b>CRÉDITOS</b></td>
            <td colspan="3">: <?= htmlspecialchars($datosGenerales['creditos']) ?></td>
        </tr>
        <tr>
            <td></td>
            <td><b>SECCIÓN</b></td>
            <td>: <?= htmlspecialchars($datosGenerales['seccion']) ?></td>
        </tr>
        <tr>
            <td></td>
            <td><b>PERIODO ACADÉMICO</b></td>
            <td>: <?= htmlspecialchars($datosGenerales['periodo_academico']) ?></td>
        </tr>
        <tr>
            <td></td>
            <td><b>DOCENTE</b></td>
            <td colspan="3">: <?= htmlspecialchars($datosGenerales['docente']) ?></td>
        </tr>
        <tr>
            <td></td>
            <td><b>DIRECTOR</b></td>
            <td colspan="3">: <?= htmlspecialchars($datoDirector['director']) ?></td>
        </tr>
    </table>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <div align="center" style="font-size:11px;">.............................................................. <br>PERIODO <?= htmlspecialchars($datosGenerales['periodo_lectivo']) ?></div>
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