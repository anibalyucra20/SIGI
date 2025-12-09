<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminCaja()): ?>
    <div class="card p-2">
        <h3 class="mb-2">Egresos / Gastos</h3>
        <div class="col-md-2 mb-2">
            <a href="<?= BASE_URL ?>/caja/egresosGastos/nuevo" class="btn btn-success mt-2">Nuevo +</a>
        </div>
        <h5 class="mb-2">Filtros:</h5>
        <div class="row mb-3">
            <div class="col-md-3 mb-2 text-center">
                <label class="form-label">Desde:</label>
                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" maxlength="200" max="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3 mb-2 text-center">
                <label class="form-label">Hasta:</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" maxlength="200" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2 mb-2 text-center">
                <label>Correlativo</label>
                <input type="text" id="correlativo" class="form-control">
            </div>
            <!-- TIPO DE EGRESO -->
            <div class="col-md-4 mb-2 text-center position-relative sd-container" data-rows-per-page="10">
                <label class="form-label">Tipo de Egreso</label>

                <!-- ID real que se enviará al backend (OJO: sin espacio en el name) -->
                <input type="hidden" name="id_rubro_egreso_contable" id="id_rubro_egreso_contable" class="sd-id">
                <!-- Input visible para buscar/mostrar texto -->
                <input type="text" class="form-control sd-input" placeholder="Buscar tipo de egreso..." autocomplete="off" required id="txt1">
                <!-- Dropdown con tabla -->
                <div class="card shadow-sm position-absolute w-100 sd-dropdown" style="max-height: 300px; overflow: hidden; z-index: 1050; display: none;">
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Código</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="sd-tbody">
                                <?php foreach ($RubrosEgreso as $rubro):
                                    $texto = $rubro['codigo'] . ' - ' . $rubro['descripcion'];
                                ?>
                                    <tr class="sd-row" data-id="<?= $rubro['id'] ?>" data-texto="<?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>">
                                        <td><?= htmlspecialchars($rubro['codigo']) ?></td>
                                        <td><?= htmlspecialchars($rubro['descripcion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="sd-pagination border-top bg-light px-2 py-1"></div>
                </div>
            </div>

            <!-- MEDIO DE PAGO (ejemplo con el mismo componente) -->
            <div class="col-md-3 mb-2 text-center position-relative sd-container" data-rows-per-page="10">
                <label class="form-label">Medio de Pago</label>
                <input type="hidden" name="id_medio_pago" id="id_medio_pago" class="sd-id">
                <input type="text" class="form-control sd-input" placeholder="Buscar medio de pago..." autocomplete="off" required>
                <div class="card shadow-sm position-absolute w-100 sd-dropdown" style="max-height: 300px; overflow: hidden; z-index: 1050; display: none;">
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Código</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="sd-tbody">
                                <?php foreach ($MediosPago as $medio):
                                    // AJUSTA ESTOS CAMPOS A TU TABLA REAL (codigo / descripcion / nombre / etc.)
                                    $texto = $medio['codigo'] . ' - ' . $medio['descripcion'];
                                ?>
                                    <tr class="sd-row" data-id="<?= $medio['id'] ?>" data-texto="<?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>">
                                        <td><?= htmlspecialchars($medio['codigo']) ?></td>
                                        <td><?= htmlspecialchars($medio['descripcion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="sd-pagination border-top bg-light px-2 py-1"></div>
                </div>
            </div>
            <!-- Afecta a (Centro de Costos) -->
            <div class="col-md-3 mb-2 text-center position-relative sd-container" data-rows-per-page="10">
                <label class="form-label">Afecta a:</label>
                <input type="hidden" name="id_centro_costos_afectado" id="id_centro_costos_afectado" class="sd-id" value="<?= htmlspecialchars($data['id_centro_costos_afectado'] ?? '') ?>">
                <input type="text" class="form-control sd-input" placeholder="Buscar centro de costos..." autocomplete="off" required>
                <div class="card shadow-sm position-absolute w-100 sd-dropdown" style="max-height: 300px; overflow: hidden; z-index: 1050; display: none;">
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Código</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="sd-tbody">
                                <?php foreach ($CentrosCostos as $cc):
                                    // AJUSTA CAMPOS
                                    $texto = $cc['codigo'] . ' - ' . $cc['descripcion'];
                                ?>
                                    <tr class="sd-row" data-id="<?= $cc['id'] ?>" data-texto="<?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>">
                                        <td><?= htmlspecialchars($cc['codigo']) ?></td>
                                        <td><?= htmlspecialchars($cc['descripcion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="sd-pagination border-top bg-light px-2 py-1"></div>
                </div>
            </div>
            <!-- Cuenta Afectada -->
            <div class="col-md-3 mb-2 text-center position-relative sd-container" data-rows-per-page="10">
                <label class="form-label">Cta Afectada</label>
                <input type="hidden" name="id_cuenta_afectada" id="id_cuenta_afectada" class="sd-id" value="<?= htmlspecialchars($data['id_cuenta_afectada'] ?? '') ?>">
                <input type="text" class="form-control sd-input" placeholder="Buscar cuenta..." autocomplete="off" required>
                <div class="card shadow-sm position-absolute w-100 sd-dropdown" style="max-height: 300px; overflow: hidden; z-index: 1050; display: none;">
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Código</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="sd-tbody">
                                <?php foreach ($Cuentas as $cta):
                                    // AJUSTA CAMPOS
                                    $texto = $cta['codigo'] . ' - ' . $cta['nombre'];
                                ?>
                                    <tr class="sd-row" data-id="<?= $cta['id'] ?>" data-texto="<?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>">
                                        <td><?= htmlspecialchars($cta['codigo']) ?></td>
                                        <td><?= htmlspecialchars($cta['nombre']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="sd-pagination border-top bg-light px-2 py-1"></div>
                </div>
            </div>
            <!-- Proveedor -->
            <div class="col-md-3 mb-2 text-center position-relative sd-container" data-rows-per-page="10">
                <label class="form-label">Proveedor</label>
                <input type="hidden" name="id_proveedor" id="id_proveedor" class="sd-id" value="<?= htmlspecialchars($data['id_proveedor'] ?? '') ?>">
                <input type="text" class="form-control sd-input" placeholder="Buscar proveedor..." autocomplete="off" required>
                <div class="card shadow-sm position-absolute w-100 sd-dropdown" style="max-height: 300px; overflow: hidden; z-index: 1050; display: none;">
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">RUC</th>
                                    <th>Razón Social</th>
                                </tr>
                            </thead>
                            <tbody class="sd-tbody">
                                <?php foreach ($Proveedores as $prov):
                                    // AJUSTA CAMPOS según tu tabla (ruc / razon_social / nombre)
                                    $texto = $prov['ruc'] . ' - ' . $prov['razon_social'];
                                ?>
                                    <tr class="sd-row" data-id="<?= $prov['id'] ?>" data-texto="<?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>">
                                        <td><?= htmlspecialchars($prov['ruc']) ?></td>
                                        <td><?= htmlspecialchars($prov['razon_social']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="sd-pagination border-top bg-light px-2 py-1"></div>
                </div>
            </div>


        </div>
        <div class="table-responsive col-12">
            <table id="tabla-egresosGastos" class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Rubro de Egreso</th>
                        <th>Total Egreso</th>
                        <th>Centro de Costo</th>
                        <th>Proveedor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables AJAX llenará esto -->
                </tbody>
            </table>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtros dependientes
            $('#fecha_desde').on('change', function() {
                tabla.ajax.reload();
            });
            $('#fecha_hasta').on('change', function() {
                tabla.ajax.reload();
            });
            $('#correlativo').on('keyup', function() {
                tabla.ajax.reload();
            });
            

            const tabla = $('#tabla-egresosGastos').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '<?= BASE_URL ?>/caja/egresosGastos/data',
                    type: 'GET',
                    data: function(d) {
                        d.fecha_desde = $('#fecha_desde').val();
                        d.fecha_hasta = $('#fecha_hasta').val();
                        d.correlativo = $('#correlativo').val();
                        d.id_rubro_egreso_contable = $('#id_rubro_egreso_contable').val();
                        d.id_medio_pago = $('#id_medio_pago').val();
                        d.id_centro_costos_afectado = $('#id_centro_costos_afectado').val();
                        d.id_cuenta_afectada = $('#id_cuenta_afectada').val();
                        d.id_proveedor = $('#id_proveedor').val();
                    }
                },
                columns: [{
                        data: 'correlativo'
                    },
                    {
                        data: 'fecha'
                    },
                    {
                        data: 'descripcion_rubro_egreso_contable'
                    },
                    {
                        data: 'total_egreso'
                    },
                    {
                        data: 'descripcion_centro_costos'
                    },
                    {
                        data: 'rs_proveedor'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <a href="<?= BASE_URL ?>/caja/egresosGastos/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>
                    `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });


            const containers = document.querySelectorAll('.sd-container');

            function initSearchDropdown(container) {
                const inputTexto = container.querySelector('.sd-input');
                const inputId = container.querySelector('.sd-id');
                const dropdown = container.querySelector('.sd-dropdown');
                const tbody = container.querySelector('.sd-tbody');
                const pagDiv = container.querySelector('.sd-pagination');

                const filasTodas = Array.from(tbody.querySelectorAll('.sd-row'));
                const filasPorPagina = parseInt(container.dataset.rowsPerPage) || 10;
                let filasFiltradas = [...filasTodas];
                let paginaActual = 1;

                // Fila "sin resultados"
                const columnas = filasTodas[0] ? filasTodas[0].children.length : 1;
                const filaSinResultados = document.createElement('tr');
                filaSinResultados.innerHTML =
                    '<td colspan="' + columnas + '" class="text-center text-muted">No se encontraron resultados</td>';
                filaSinResultados.style.display = 'none';
                tbody.appendChild(filaSinResultados);

                // Mostrar dropdown al hacer focus
                inputTexto.addEventListener('focus', function() {
                    dropdown.style.display = 'block';
                    aplicarFiltroYMostrar();
                });

                // Filtrar mientras escribe
                inputTexto.addEventListener('keyup', function() {
                    // Si el usuario escribe/cambia el texto, el ID seleccionado deja de ser válido
                    inputId.value = '';
                    paginaActual = 1;
                    aplicarFiltroYMostrar();
                    tabla.ajax.reload();
                });

                // Click en fila (delegado dentro del tbody)
                tbody.addEventListener('click', function(e) {
                    const fila = e.target.closest('.sd-row');
                    if (!fila) return;

                    inputId.value = fila.dataset.id;
                    inputTexto.value = fila.dataset.texto;

                    dropdown.style.display = 'none';
                    tabla.ajax.reload();
                });

                // Que los clicks dentro del dropdown no cierren el componente
                dropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });

                // ----- LÓGICA DE FILTRO + PAGINACIÓN -----

                function aplicarFiltroYMostrar() {
                    const filtro = inputTexto.value.toLowerCase().trim();

                    filasFiltradas = filasTodas.filter(function(fila) {
                        const textoFila = fila.dataset.texto.toLowerCase();
                        return textoFila.includes(filtro);
                    });

                    if (filasFiltradas.length === 0) {
                        filasTodas.forEach(f => f.style.display = 'none');
                        filaSinResultados.style.display = '';
                        pagDiv.innerHTML = '';
                        return;
                    } else {
                        filaSinResultados.style.display = 'none';
                    }

                    const totalPaginas = Math.ceil(filasFiltradas.length / filasPorPagina);
                    if (paginaActual > totalPaginas) {
                        paginaActual = totalPaginas || 1;
                    }

                    mostrarPagina(paginaActual);
                    generarControlesPaginacion(totalPaginas);
                }

                function mostrarPagina(pagina) {
                    filasTodas.forEach(f => f.style.display = 'none');

                    const inicio = (pagina - 1) * filasPorPagina;
                    const fin = inicio + filasPorPagina;

                    filasFiltradas.slice(inicio, fin).forEach(function(fila) {
                        fila.style.display = '';
                    });

                    paginaActual = pagina;
                    dropdown.style.display = 'block';
                }

                function generarControlesPaginacion(totalPaginas) {
                    pagDiv.innerHTML = '';
                    if (totalPaginas <= 1) return;

                    const nav = document.createElement('nav');
                    const ul = document.createElement('ul');
                    ul.className = 'pagination pagination-sm mb-0 justify-content-end';

                    const makeItem = (label, page, disabled = false, active = false) => {
                        const li = document.createElement('li');
                        li.className = 'page-item';
                        if (disabled) li.classList.add('disabled');
                        if (active) li.classList.add('active');

                        const a = document.createElement('a');
                        a.className = 'page-link';
                        a.href = '#';
                        a.textContent = label;

                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (disabled || page === paginaActual) return;
                            mostrarPagina(page);
                            generarControlesPaginacion(totalPaginas);
                        });

                        li.appendChild(a);
                        return li;
                    };

                    // Anterior
                    ul.appendChild(
                        makeItem('«', paginaActual - 1, paginaActual === 1)
                    );

                    // Números de página (si son muchos se puede optimizar, pero por ahora está bien)
                    for (let i = 1; i <= totalPaginas; i++) {
                        ul.appendChild(
                            makeItem(String(i), i, false, i === paginaActual)
                        );
                    }

                    // Siguiente
                    ul.appendChild(
                        makeItem('»', paginaActual + 1, paginaActual === totalPaginas)
                    );

                    nav.appendChild(ul);
                    pagDiv.appendChild(nav);
                }

                // Precargar texto si venimos en modo edición (ya hay un ID seleccionado)
                const selectedId = inputId.value;
                if (selectedId) {
                    const filaSel = filasTodas.find(f => f.dataset.id === selectedId);
                    if (filaSel) {
                        inputTexto.value = filaSel.dataset.texto;
                    }
                }

                // Inicial (por si quieres que siempre esté listo)
                //aplicarFiltroYMostrar();
            }

            // Inicializar todos los componentes
            containers.forEach(initSearchDropdown);

            // Cerrar dropdowns al hacer click fuera de cualquiera
            document.addEventListener('click', function(e) {
                containers.forEach(container => {
                    const dropdown = container.querySelector('.sd-dropdown');
                    if (!container.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            });
        });
    </script>
<?php else: ?>
    <!-- Para director o coordinador en SIGI -->
    <p>Usted no cuenta con el permiso necesario para acceder al modulo de Caja</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>