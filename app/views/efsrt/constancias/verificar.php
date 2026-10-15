<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Verificación de Constancia Digital') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-verification { border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .header-logo { max-height: 70px; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="card card-verification bg-white p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>/images/logo.png" alt="Logo Institucional" class="header-logo mb-3" onerror="this.style.display='none'">
                    <h5 class="text-uppercase font-weight-bold text-dark mb-1">Instituto de Educación Superior Tecnológico Público "Huanta"</h5>
                    <p class="text-muted font-size-13 mb-0">Sistema Integrado de Gestión Institucional (SIGI) - Validación Oficial de EFSRT</p>
                </div>

                <hr class="my-4">

                <?php if (!empty($constancia)): ?>
                    <div class="alert alert-success d-flex align-items-center p-3 mb-4 rounded-lg" role="alert">
                        <i class="fas fa-check-circle fa-2x mr-3 text-success"></i>
                        <div>
                            <h5 class="alert-heading font-weight-bold mb-1">DOCUMENTO VÁLIDO Y AUTÉNTICO</h5>
                            <p class="mb-0 font-size-13">La presente constancia fue emitida oficialmente por la Secretaría Académica del IESTP Huanta y se encuentra registrada en la base de datos institucional.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-4">
                            <tbody>
                                <tr>
                                    <th class="bg-light" style="width: 35%;">N° de Constancia:</th>
                                    <td><strong class="text-primary font-size-16"><?= htmlspecialchars($constancia['nro_constancia']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Estudiante Titular:</th>
                                    <td><strong><?= htmlspecialchars($constancia['estudiante_nombre']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Documento de Identidad:</th>
                                    <td>DNI N° <?= htmlspecialchars($constancia['estudiante_dni']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Programa de Estudios:</th>
                                    <td><?= htmlspecialchars($constancia['programa_nombre']) ?> (<?= htmlspecialchars($constancia['programa_codigo']) ?>)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Módulo Formativo:</th>
                                    <td><strong>Módulo <?= htmlspecialchars($constancia['nro_modulo']) ?>:</strong> <?= htmlspecialchars($constancia['modulo_nombre']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Empresa / Institución Receptora:</th>
                                    <td><?= htmlspecialchars($constancia['empresa_nombre']) ?> (Área: <?= htmlspecialchars($constancia['area_desempeno']) ?>)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Periodo de Realización:</th>
                                    <td><?= date('d/m/Y', strtotime($constancia['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($constancia['fecha_fin'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Total Horas Convalidadas:</th>
                                    <td><span class="badge badge-primary px-2 py-1"><?= (int)$constancia['horas_convalidadas'] ?> Horas</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Calificación Final:</th>
                                    <td><span class="badge badge-success px-2 py-1 font-size-13"><?= $constancia['calificacion_final'] ? number_format($constancia['calificacion_final'], 1) : '-' ?></span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Fecha de Emisión:</th>
                                    <td><?= date('d/m/Y', strtotime($constancia['fecha_emision'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Emitido Por:</th>
                                    <td><?= htmlspecialchars($constancia['emitido_por_nombre']) ?> (Secretaría Académica)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center text-muted font-size-12">
                        Token de Seguridad: <code><?= htmlspecialchars($constancia['qr_token']) ?></code>
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger text-center p-4 rounded-lg" role="alert">
                        <i class="fas fa-times-circle fa-3x mb-3 text-danger"></i>
                        <h4 class="alert-heading font-weight-bold">DOCUMENTO NO ENCONTRADO O INVÁLIDO</h4>
                        <p class="mb-0 font-size-14">
                            El código o token de verificación escaneado no corresponde a ninguna constancia válida emitida por el sistema SIGI.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-home mr-1"></i> Ir al Portal Institucional
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
