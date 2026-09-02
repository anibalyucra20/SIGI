<?php
if (!$permitido) {
?>
    <h1>No tienes permiso para imprimir este documento</h1>
<?php
} else {
?>
<style>
    body {
        font-size: 7px;
        font-family: helvetica;
    }
    .acta-header {
        width: 100%;
        margin-bottom: 12px;
    }
    .titulo {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        margin: 4px 0;
    }
    .table-acta {
        border-collapse: collapse;
        width: 100%;
    }
    .table-acta th,
    .table-acta td {
        border: 1px solid #222;
        padding: 5px;
        font-size: 7px;
        vertical-align: top;
    }
    .table-acta th {
        background-color: #f5f5f5;
        text-align: center;
        font-weight: bold;
        font-size: 7px;
    }
    .criterio-title {
        background-color: #fafafa;
        font-weight: bold;
        text-align: justify;
    }
    .pts {
        color: #B30000;
        font-weight: bold;
        font-size: 7px;
    }
    .firma {
        text-align: center;
        font-size: 10px;
    }
</style>

<!-- ENCABEZADO MINEDU -->
<!--<table class="acta-header">
    <tr>
        <td style="text-align:center;" width="100%">
            <div class="titulo">MATRIZ DE RÚBRICA DE EVALUACIÓN</div>
        </td>
    </tr>
</table>-->

<!-- DATOS DE LA RÚBRICA -->
<table style="width:100%; font-size:8px;" cellpadding="1">
    <tr>
        <td style="width:15%;"><b>INSTRUMENTO</b></td>
        <td style="width:2%;">:</td>
        <td style="width:30%;"><?= htmlspecialchars($rubrica['nombre'] ?? '') ?></td>
        <td style="width:70%;" rowspan="2"> Otros datos : </td>
    </tr>
    <!--<?php if(!empty($rubrica['tipo_tecnica'])): ?>
    <tr>
        <td><b>TÉCNICA</b></td>
        <td>:</td>
        <td><?= htmlspecialchars($rubrica['tipo_tecnica']) ?></td>
    </tr>
    <?php endif; ?>
    -->
    <tr>
        <td><b>FECHA IMPRESIÓN</b></td>
        <td>:</td>
        <td><?= date('d/m/Y') ?></td>
    </tr>
</table>
<br><br>

<!-- MATEMÁTICA DE COLUMNAS -->
<?php 
    $numNiveles = count($nivelesPuntajes);
    if ($numNiveles === 0) { $numNiveles = 1; }
    
    $anchoCriterio = 12; // 20% para la descripción del criterio
    $anchoNiveles = 88 / $numNiveles; // El 80% restante se divide exactamente entre los niveles
?>

<!-- TABLA DE LA RÚBRICA -->
<table class="table-acta">
    <thead>
        <tr>
            <th style="width: <?= $anchoCriterio ?>%; text-align: center;">CRITERIOS DE EVALUACIÓN</th>
            <?php 
            $countniveles = 0;
            foreach ($nivelesPuntajes as $puntaje): 
                $countniveles++;
                ?>
                <th style="width: <?= $anchoNiveles ?>%; text-align: center;">Nivel <?= $countniveles ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($criterios as $criterio): ?>
            <tr>
                <td class="criterio-title" style="width: <?= $anchoCriterio ?>%; text-align: center;">
                    <?= nl2br(htmlspecialchars($criterio['description'] ?? '-')) ?>
                </td>
                
                <?php 
                $niveles = $criterio['niveles'] ?? [];
                for ($i = 0; $i < $numNiveles; $i++): 
                    $nivel = $niveles[$i] ?? ['definition' => '-', 'score' => 0];
                ?>
                    <td style="width: <?= $anchoNiveles ?>%; text-align: justify;">
                        <?= nl2br(htmlspecialchars($nivel['definition'] ?? '-')) ?>
                        <br>
                        <span class="pts">[<?= floatval($nivel['score']) ?> pts]</span>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- BLOQUE DE FIRMAS -->
<br><br><br><br>
<!--<table width="100%">
    <tr>
        <td width="30%" class="firma" style="border-top: 1px solid #000;"><br>Firma del Docente</td>
        <td width="40%"></td>
        <td width="30%" class="firma" style="border-top: 1px solid #000;"><br>Firma Coordinación Académica</td>
    </tr>
</table>-->

<?php
// =========================================================================
// RENDERIZADO TCPDF Y ESTAMPADO DE LOGOS (PATRÓN SIGI)
// =========================================================================
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');

$logoMineduPath = (__DIR__ . '/../../../../public/img/logo_minedu.jpeg');
if (!empty($datosSistema['logo'])) {
    $logoPath = __DIR__ . '/../../../../public/images/' . $datosSistema['logo'];
} else {
    $logoPath = __DIR__ . '/../../../../public/img/logo_completo.png';
}

// Logo MINEDU Izquierda
/*if(file_exists($logoMineduPath)) {
    $pdf->Image($logoMineduPath, 10, 10, 35, 10); 
}*/

// Logo Institucional Derecha (En Landscape A4 el ancho total es 297mm. Coordenada X = 250)
if(file_exists($logoPath)) {
    $pdf->Image($logoPath, 250, 10, 35, 12); 
}

// Cierre del PDF
$pdf->Output('Rubrica_Institucional_' . date('Ymd_His') . '.pdf', 'I');
?>
<?php
}
?>