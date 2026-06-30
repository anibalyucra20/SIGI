<?php

use Core\Auth;

Auth::start();
$logueado = Auth::user() !== null;

if ($logueado):
  $db = (new \Core\Model())->getDB();
  $userLogin = $_SESSION['sigi_user_id'] ?? null;
  //"SELECT s.id, s.nombre FROM sigi_sedes s INNER JOIN sigi_usuarios u ON u.id_sede = s.id WHERE u.id='$userLogin' ORDER BY s.nombre"
  //SELECT id, nombre FROM sigi_sedes ORDER BY nombre"
  $sedess = $db->query("SELECT s.id, s.nombre FROM sigi_sedes s INNER JOIN sigi_usuarios u ON u.id_sede = s.id WHERE u.id='$userLogin' ORDER BY s.nombre")->fetchAll(PDO::FETCH_ASSOC);
  $periodos = $db->query("SELECT id, nombre FROM sigi_periodo_academico ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);

  $_SESSION['sigi_sede_actual'] = $sedess[0]['id'] ?? 0;
  $sedeActual    = $_SESSION['sigi_sede_actual'] ?? 0;
  $periodoActual = $_SESSION['sigi_periodo_actual_id'] ?? ($periodos[0]['id'] ?? 0);

  // Definir el id de admin en una variable por claridad
  $rolAdmin = 1;
  $rolActual = $_SESSION['sigi_rol_actual'] ?? null;
endif;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title><?= $pageTitle ?? 'SIGI' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="<?= BASE_URL ?>/assets/css/sweetalert2.min.css" rel="stylesheet" />
  <link href="<?= BASE_URL ?>/assets/css/icons.min.css" rel="stylesheet" />
  <link href="<?= BASE_URL ?>/assets/css/theme.min.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

  <!-- Si usas Responsive de DataTables, descomenta estas dos líneas -->
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">


  <?php
  if ($_SESSION['favicon'] != '') {
    $ruta_favicon = BASE_URL . '/images/' . $_SESSION['favicon'];
  } else {
    $ruta_favicon = BASE_URL . '/img/favicon.ico';
  }
  ?>
  <link rel="icon" type="image/x-icon" href="<?= $ruta_favicon; ?>">
</head>

<body data-sidebar="light">
  <div id="layout-wrapper">
    <!-- SIEMPRE muestra el header y logo -->
    <?php if ($logueado): ?>
      <header id="page-topbar">
        <div class="navbar-header">
          <div class="navbar-brand-box d-flex align-items-left">
            <a href="<?= BASE_URL . '/' . $_SESSION['modulo_vista']; ?>" class="logo">
              <?php
              if ($_SESSION['logo'] != '') {
                $ruta_logo = BASE_URL . '/images/' . $_SESSION['logo'];
              } else {
                $ruta_logo = BASE_URL . '/img/logo_completo.png';
              }
              ?>
              <i class="mdi"><img src="<?= $ruta_logo ?>" alt="" width="100px" height="30px"></i>
              <span> SIGI</span>
            </a>
            <button type="button" class="btn btn-sm mr-2 font-size-16 d-lg-none header-item waves-effect waves-light"
              data-toggle="collapse" data-target="#topnav-menu-content">
              <i class="fa fa-fw fa-bars"></i>
            </button>
          </div>
          <div class="d-flex align-items-center">
            <div class="dropdown d-inline-block">
              <form action="<?= BASE_URL ?>/sedes/cambiarSesion" method="get" class="d-flex align-items-center">
                <label for="sedeee" class="me-2 small">Sede:</label>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <select id="sedeee" name="sede" class="form-control me-2" onchange="this.form.submit()">
                  <?php foreach ($sedess as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $sedeActual ? 'selected' : '' ?>>
                      <?= $s['nombre'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            </div>
            <?php if ($_SESSION['sigi_modulo_actual'] != 0): ?>
              <div class="dropdown d-inline-block">
                <form action="<?= BASE_URL ?>/sigi/periodoAcademico/cambiarSesion" method="get" class="d-flex align-items-center">
                  <label for="periodo" class="me-2 small">Periodo:</label>
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                  <select name="periodo" class="form-control me-2" onchange="this.form.submit()">
                    <?php foreach ($periodos as $p): ?>
                      <option value="<?= $p['id'] ?>" <?= $p['id'] == $periodoActual ? 'selected' : '' ?>>
                        <?= $p['nombre'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['sigi_permisos_usuario']) && $_SESSION['sigi_modulo_actual'] != 0): ?>
              <div class="dropdown d-inline-block">
                <form method="get" action="<?= BASE_URL ?>/sigi/rol/cambiarSesion" class="d-flex align-items-center">
                  <label for="permiso" class="me-2 small">Rol:</label>
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                  <select name="permiso" id="permiso" class="form-control me-2" style="width:auto;" onchange="this.form.submit()">
                    <?php
                    $moduloActual = $_SESSION['sigi_modulo_actual'] ?? null;
                    foreach ($_SESSION['sigi_permisos_usuario'] as $permiso):
                      if ($permiso['id_sistema'] != $moduloActual) continue;
                    ?>
                      <option value="<?= $permiso['id_sistema'] ?>-<?= $permiso['id_rol'] ?>"
                        <?= ($_SESSION['sigi_rol_actual'] == $permiso['id_rol']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($permiso['rol']) ?>
                      </option>
                    <?php endforeach; ?>

                  </select>
                </form>
              </div>
            <?php endif; ?>
          </div>
          <div class="d-flex align-items-center">
            <div class="dropdown d-inline-block ml-2">
              <button type="button" class="btn header-item waves-effect waves-light"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="rounded-circle header-profile-user"
                  src="<?= BASE_URL ?>/img/user.png"
                  alt="Header Avatar">
                <span class="d-none d-sm-inline-block ml-1"><?= $_SESSION['sigi_user_name'] ?? 'Usuario' ?></span>
                <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item d-flex align-items-center justify-content-between"
                  href="<?= BASE_URL ?>/intranet/perfil">
                  <span>Mi perfil</span>
                </a>
                <a class="dropdown-item d-flex align-items-center justify-content-between"
                  href="<?= BASE_URL ?>/resetPassword?data=<?= base64_encode($_SESSION['sigi_user_id']) ?>&back=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                  <span>Cambiar contraseña</span>
                </a>
                <a class="dropdown-item d-flex align-items-center justify-content-between text-danger"
                  href="<?= BASE_URL ?>/logout">
                  <span>Cerrar sesión</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>
    <?php endif; ?>
    <?php if ($logueado): ?>
      <div class="topnav">
        <div class="container-fluid">
          <?php
          $module = strtolower($module ?? 'sigi');
          include __DIR__ . "/menus/{$module}.php";
          ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <div class="main-content">
      <div class="page-content">
        <div class="container-fluid">
          <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
              <ul>
                <?php foreach ($errores as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible">
              <?= $_SESSION['flash_success'] ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <?= $_SESSION['flash_error'] ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
          <?php endif; ?>


          <!-- POP UP NOTIFICACION DE VENCIMIENTO -->
          <style>
            /* Caja del Popup (Fija al centro de la pantalla) */
            .popup-content {
              background: #ffffff !important;
              padding: 35px 30px !important;
              border-radius: 8px !important;
              box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
              width: 90% !important;
              max-width: 520px !important;
              text-align: center !important;
              margin: auto !important;
            }

            .warning-border {
              border-top: 5px solid #f59e0b !important;
            }

            /* Ícono de Alerta */
            .popup-icon {
              font-size: 45px;
              margin-bottom: 15px;
              color: #f59e0b;
            }

            /* Título */
            .popup-content h2 {
              color: #1f2937 !important;
              font-size: 22px !important;
              margin-top: 0 !important;
              margin-bottom: 15px !important;
              font-weight: 700 !important;
            }

            /* Cuerpo del texto */
            .popup-body p {
              color: #4b5563 !important;
              font-size: 14.5px !important;
              line-height: 1.6 !important;
            }

            /* Contenedor del Contador Faltante */
            .countdown-box {
              background-color: #f9fafb !important;
              border: 2px dashed #d1d5db !important;
              border-radius: 6px !important;
              padding: 15px !important;
              margin: 15px 0 !important;
            }

            .countdown-label {
              font-size: 11px !important;
              font-weight: 800 !important;
              color: #4b5563 !important;
              letter-spacing: 1px !important;
              display: block;
            }

            .countdown-number {
              font-size: 54px !important;
              font-weight: 900 !important;
              color: #d97706 !important;
              margin: 5px 0 !important;
            }

            .countdown-sub {
              font-size: 12px !important;
              font-weight: 700 !important;
              color: #6b7280 !important;
              letter-spacing: 2px !important;
            }

            /* Recuadro de Administración */
            .alert-box-notice {
              background-color: #fef3c7 !important;
              border-left: 4px solid #d97706 !important;
              color: #92400e !important;
              padding: 12px 15px !important;
              border-radius: 4px !important;
              font-size: 14px !important;
              text-align: left !important;
              margin: 20px 0 !important;
            }

            /* Botón estilizado (Para cambiar el botón feo por defecto) */
            .btn-popup-warning {
              background-color: #d97706 !important;
              color: white !important;
              border: none !important;
              padding: 12px 30px !important;
              font-size: 15px !important;
              font-weight: 600 !important;
              border-radius: 5px !important;
              cursor: pointer !important;
              width: 100% !important;
            }

            .btn-popup-warning:hover {
              background-color: #b45309 !important;
            }
          </style>
          <div id="global-popup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.65); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(2px);">
            <div class="popup-content warning-border">
              <div class="popup-icon">⚠️</div>

              <h2>Aviso Importante: Renovación de Servicio</h2>

              <div class="popup-body">
                <p>Estimado(a) docente, le informamos que el sistema <strong>SIGI</strong> se encuentra en riesgo de suspensión temporal debido al próximo vencimiento del Hosting/VPS.</p>
                <?php
                // Calcular los días restantes para el vencimiento del VPS
                $fechaVencimiento = new DateTime('2026-06-26'); // Fecha de vencimiento del VPS
                $fechaActual = new DateTime();
                $intervalo = $fechaActual->diff($fechaVencimiento);
                $diasRestantes = $intervalo->format('%r%a'); // Días restantes (con signo) para el vencimiento
                ?>
                <div id="countdown-container" class="countdown-box">
                  <span class="countdown-label">EL SERVICIO SE SUSPENDERÁ EN:</span>
                  <div id="countdown-days" class="countdown-number"><?php echo $diasRestantes; ?></div>
                  <span class="countdown-sub">DÍAS</span>
                </div>

                <!--<div class="alert-box-notice">
                  <strong>Nota para la gestión:</strong> El trámite y pago de renovación ya ha sido derivado y debe ser regularizado a la brevedad por el <strong>Área de Administración</strong> de la institución.
                </div>

                <p class="action-text">Agradecemos su comprensión. Estamos trabajando para evitar interrupciones en sus labores académicas.</p>
              </div>-->

                <button id="accept-popup-btn" class="btn-popup-warning">Entendido</button>
              </div>
            </div>
          </div>
          <!--<script>
            document.addEventListener("DOMContentLoaded", function() {
              const popup = document.getElementById("global-popup");
              const acceptBtn = document.getElementById("accept-popup-btn");

              // Lógica para mostrarlo si no ha sido cerrado en la sesión
              if (!sessionStorage.getItem("avisoVpsMostrado")) {
                popup.style.setProperty("display", "flex", "important"); // Esto fuerza a que se vuelva un modal flotante centrado
              }

              acceptBtn.addEventListener("click", function() {
                popup.style.setProperty("display", "none", "important");
                sessionStorage.setItem("avisoVpsMostrado", "true");
              });
            });
          </script>-->