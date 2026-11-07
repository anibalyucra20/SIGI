<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminInventario()): ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800"> Módulo de Reportes Oficiales</h1>
            <p class="text-muted mt-1 mb-0">Generación y exportación de formatos de inventario patrimonial.</p>
        </div>
    </div>
    <hr>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-bottom pt-3 pb-0">
            <!-- Pestañas de Navegación-->
            <ul class="nav nav-tabs border-bottom-0" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active font-weight-bold" id="bienes-tab" data-toggle="tab" href="#bienes" role="tab" aria-controls="bienes" aria-selected="true"><i class="fas fa-boxes mr-2"></i> Reportes de Bienes</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="ambientes-tab" data-toggle="tab" href="#ambientes" role="tab" aria-controls="ambientes" aria-selected="false"><i class="fas fa-building mr-2"></i> Reportes de Ambientes</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link font-weight-bold" id="movimientos-tab" data-toggle="tab" href="#movimientos" role="tab" aria-controls="movimientos" aria-selected="false"><i class="fas fa-exchange-alt mr-2"></i> Reportes de Movimientos</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="reportTabsContent">
                
                <!-- PESTAÑA 1: BIENES -->
                <div class="tab-pane fade show active" id="bienes" role="tabpanel" aria-labelledby="bienes-tab">
                    <form id="formBienes" method="POST" action="">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-dark">Tipo de Reporte:</label>
                                <select name="tipo_reporte_bienes" class="form-control" id="tipo_reporte_bienes" onchange="toggleFechasBajas()">
                                    <option value="activos">Bienes Activos (En uso normal)</option>
                                    <option value="bajas">Bienes Dados de Baja</option>
                                    <option value="general">Inventario General (Todos: Activos e Inactivos)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-dark">Filtrar por Ambiente (Opcional):</label>
                                <select name="id_ambiente" class="form-control">
                                    <option value="">-- TODOS LOS AMBIENTES --</option>
                                    <?php if (!empty($ambientes)): ?>
                                        <?php foreach ($ambientes as $amb): ?>
                                            <?php 
                                                // Obtenemos el ID y el nombre real del ambiente
                                                $id_val = $amb['id_inventario'] ?? $amb['id'] ?? '';
                                                $nombre_ambiente = trim($amb['tipo_ambiente'] ?? '');
                                                
                                            ?>
                                            <option value="<?= $id_val ?>">
                                                <?= htmlspecialchars($nombre_ambiente ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro de fechas (Oculto por defecto, se muestra si seleccionan "Dados de Baja") -->
                        <div class="row mb-4" id="contenedor-fechas" style="display: none; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-danger">Fecha Baja Desde:</label>
                                <input type="date" name="fecha_desde" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-danger">Fecha Baja Hasta:</label>
                                <input type="date" name="fecha_hasta" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex" style="gap: 15px;">
                            <button type="button" class="btn btn-primary shadow-sm" onclick="exportar('formBienes', 'pdf', 'Bienes')"><i class="fas fa-file-pdf mr-2"></i> Generar PDF</button>
                            <button type="button" class="btn btn-success shadow-sm" onclick="exportar('formBienes', 'excel', 'Bienes')"><i class="fas fa-file-excel mr-2"></i> Exportar Excel</button>
                        </div>
                    </form>
                </div>

                <!-- PESTAÑA 2: AMBIENTES -->
                <div class="tab-pane fade" id="ambientes" role="tabpanel" aria-labelledby="ambientes-tab">
                    <form id="formAmbientes" method="POST" action="">
                        <p class="text-muted mb-4">Genere un listado completo de todos los ambientes físicos registrados en la sede institucional y sus responsables asignados.</p>
                        <div class="d-flex" style="gap: 15px;">
                            <button type="button" class="btn btn-primary shadow-sm" onclick="exportar('formAmbientes', 'pdf', 'Ambientes')"><i class="fas fa-file-pdf mr-2"></i> Listado PDF</button>
                            <button type="button" class="btn btn-success shadow-sm" onclick="exportar('formAmbientes', 'excel', 'Ambientes')"><i class="fas fa-file-excel mr-2"></i> Reporte Excel</button>
                        </div>
                    </form>
                </div>

                <!-- PESTAÑA 3: MOVIMIENTOS -->
                <div class="tab-pane fade" id="movimientos" role="tabpanel" aria-labelledby="movimientos-tab">
                    <form id="formMovimientos" method="POST" action="">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-dark">Filtrar Rango de Movimientos Desde:</label>
                                <input type="date" name="mov_desde" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold text-dark">Hasta:</label>
                                <input type="date" name="mov_hasta" class="form-control">
                            </div>
                        </div>
                        <div class="d-flex" style="gap: 15px;">
                            <button type="button" class="btn btn-primary shadow-sm" onclick="exportar('formMovimientos', 'pdf', 'Movimientos')"><i class="fas fa-file-pdf mr-2"></i> Historial PDF</button>
                            <button type="button" class="btn btn-success shadow-sm" onclick="exportar('formMovimientos', 'excel', 'Movimientos')"><i class="fas fa-file-excel mr-2"></i> Historial Excel</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Mostrar u ocultar el filtro de fechas para bienes dados de baja
    function toggleFechasBajas() {
        var tipo = document.getElementById('tipo_reporte_bienes').value;
        var contenedorFechas = document.getElementById('contenedor-fechas');
        if (tipo === 'bajas') {
            contenedorFechas.style.display = 'flex';
        } else {
            contenedorFechas.style.display = 'none';
        }
    }

    // Función dinámica para enviar el formulario y evitar el "Cargando..." infinito
    function exportar(formId, formato, modulo) {
        let form = document.getElementById(formId);
        
        if (formato === 'pdf') {
            form.action = '<?= BASE_URL ?>/inventario/reportes/generarPdf' + modulo;
        } else if (formato === 'excel') {
            form.action = '<?= BASE_URL ?>/inventario/reportes/generarExcel' + modulo;
        }
        
        // Esta línea obliga a descargar en segundo plano sin congelar la pantalla actual
        form.target = '_blank'; 
        
        form.submit();
    }
</script>

<?php else: ?>
    <div class="container-fluid px-4 mt-4">
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle"></i> No tiene permisos para generar reportes.</div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>