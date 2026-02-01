<?php
$html = ob_get_clean();
$pdf->writeHTML($html, true, false, true, false, '');
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<style>
  /* TCPDF: mejor estilos simples */
  .card {
    width: 820px;          /* ajusta según tu página */
    height: 430px;
    border: 0;
    font-family: Arial, Helvetica, sans-serif;
    position: relative;
  }

  /* Encabezado superior (línea fina) */
  .top-line {
    height: 6px;
    background: #111;
    margin-bottom: 10px;
  }

  .row {
    width: 100%;
  }

  .col-left {
    width: 36%;
    vertical-align: top;
    padding-left: 10px;
  }

  .col-right {
    width: 64%;
    vertical-align: top;
    padding-right: 10px;
  }

  /* Bloque logo izquierdo */
  .logo-box {
    width: 280px;
    height: 90px;
    border: 0;
  }

  .licensed {
    font-size: 9px;
    color: #222;
    margin-top: 6px;
    text-align: left;
  }

  /* Título superior derecho */
  .title {
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #111;
    margin-top: 5px;
    line-height: 1.1;
  }

  /* Banda naranja "CARNÉ DE POSTULANTE" */
  .banner-wrap {
    margin-top: 10px;
    width: 100%;
    text-align: right;
  }

  .banner {
    display: inline-block;
    width: 360px;
    height: 48px;
    background: #f28c1b;
    border-radius: 6px;
    text-align: center;
    line-height: 48px;
    font-size: 16px;
    font-weight: bold;
    color: #fff;
  }

  /* Cuerpos (recuadros) */
  .boxes {
    margin-top: 16px;
    width: 100%;
  }

  .photo-box {
    width: 260px;
    height: 210px;
    border: 3px solid #111;
    border-radius: 10px;
    margin-top: 8px;
  }

  .right-box {
    width: 480px;
    height: 210px;
    border: 3px solid #111;
    border-radius: 10px;
    margin-top: 8px;
    margin-left: 10px;
  }

  /* Recuadros inferiores */
  .bottom-row {
    margin-top: 12px;
    width: 100%;
  }

  .bottom-left {
    width: 260px;
    height: 48px;
    border: 3px solid #111;
    border-radius: 10px;
  }

  .bottom-right {
    width: 480px;
    height: 48px;
    border: 3px solid #111;
    border-radius: 10px;
    margin-left: 10px;
  }

  /* Placeholders para imágenes */
  .img-fit {
    width: 100%;
    height: 100%;
  }

</style>
</head>

<body>
  <div class="card">
    <div class="top-line"></div>

    <table class="row" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <!-- COLUMNA IZQUIERDA -->
        <td class="col-left">
          <!-- Aquí va tu logo (izquierda) -->
          <div class="logo-box">
            <!-- Reemplaza src por tu imagen -->
            <img class="img-fit" src="logo_huanta.png" alt="Logo Huanta" />
          </div>

          <div class="licensed">
            <b>LICENCIADO POR</b><br/>
            R. M. N° 371-2025-MINEDU
          </div>
        </td>

        <!-- COLUMNA DERECHA -->
        <td class="col-right">
          <div class="title">
            INSTITUTO DE EDUCACIÓN SUPERIOR<br/>
            PÚBLICO “HUANTA”
          </div>

          <div class="banner-wrap">
            <div class="banner">CARNÉ DE POSTULANTE</div>
          </div>
        </td>
      </tr>
    </table>

    <!-- Recuadros grandes -->
    <table class="boxes" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td style="width: 280px; padding-left:10px;">
          <div class="photo-box">
            <!-- Foto del postulante (opcional) -->
            <!-- <img class="img-fit" src="foto.jpg" alt="Foto" /> -->
          </div>
        </td>
        <td style="width: 520px; padding-right:10px;">
          <div class="right-box">
            <!-- Área grande derecha -->
          </div>
        </td>
      </tr>
    </table>

    <!-- Recuadros inferiores -->
    <table class="bottom-row" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td style="width: 280px; padding-left:10px;">
          <div class="bottom-left"></div>
        </td>
        <td style="width: 520px; padding-right:10px;">
          <div class="bottom-right"></div>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
