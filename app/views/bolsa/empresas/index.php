<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminBolsa()): ?>

    <!-- Encabezado del módulo -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="mb-0 text-dark font-weight-bold">Empresas</h3>

            <a href="<?= BASE_URL ?>/bolsa/empresas/nuevo"
               class="btn btn-success shadow-sm">
                <i class="fas fa-plus mr-1"></i> Nueva Empresa
            </a>
        </div>
    </div>

    <!-- Filtros con el estilo del módulo Ofertas -->
    <div class="card p-3 shadow-sm rounded-3 mb-3">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label for="filter-ruc" class="font-weight-bold">
                    RUC
                </label>
                <input type="text"
                       id="filter-ruc"
                       class="form-control"
                       placeholder="Buscar por RUC"
                       maxlength="11">
            </div>

            <div class="col-md-4 mb-2">
                <label for="filter-empresa" class="font-weight-bold">
                    Empresa
                </label>
                <input type="text"
                       id="filter-empresa"
                       class="form-control"
                       placeholder="Buscar por razón social">
            </div>

            <div class="col-md-4 mb-2 d-flex align-items-end">
                <button type="button"
                        id="btn-limpiar-filtros"
                        class="btn btn-secondary shadow-sm">
                    <i class="fas fa-eraser mr-1"></i> Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de empresas -->
    <div class="card p-3 shadow-sm border-0 mb-4">
        <div class="table-responsive">
            <table id="tabla-empresas" class="table table-bordered table-hover table-sm align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">RUC</th>
                        <th class="text-center">Razón Social / Empresa</th>
                        <th class="text-center">Dirección</th>
                        <th class="text-center">Teléfono</th>
                        <th class="text-center">Correo Electrónico</th>
                        <th class="text-center">F. Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos cargados mediante DataTables AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const baseUrl = '<?= BASE_URL ?>';

            const tabla = $('#tabla-empresas').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searching: false,
                order: [[2, 'asc']],

                ajax: {
                    url: baseUrl + '/bolsa/empresas/data',
                    type: 'GET',
                    data: function (d) {
                        d.filter_ruc = $('#filter-ruc').val();
                        d.filter_empresa = $('#filter-empresa').val();
                    }
                },

                columns: [
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart;
                        }
                    },
                    {data: 'ruc', className: 'text-center'},
                    {data: 'empresa'},
                    {data: 'direccion'},
                    {data: 'telefono', className: 'text-center'},
                    {data: 'email'},
                    {
                        data: 'fecha_registro',
                        className: 'text-center',
                        render: function (data, type) {
                            if (!data) return '-';
                            if (type !== 'display') return data;

                            const fecha = new Date(
                                data.replace(' ', 'T')
                            );

                            if (isNaN(fecha.getTime())) return data;

                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const anio = fecha.getFullYear();

                            return `${dia}/${mes}/${anio}`;
                        }
                    },
                    {
                        data: null,
                        className: 'text-center text-nowrap',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const empresaEscapada = encodeURIComponent(
                                row.empresa || ''
                            );

                            return `
                                <a href="${baseUrl}/bolsa/empresas/editar/${row.id}"class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar-empresa" data-id="${row.id}" data-empresa="${empresaEscapada}" title="Eliminar"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    }
                ],

                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });

            // Búsqueda por RUC y razón social
            let timerBusqueda;

            $('#filter-ruc, #filter-empresa').on('input', function () {
                clearTimeout(timerBusqueda);

                timerBusqueda = setTimeout(function () {
                    tabla.ajax.reload();
                }, 350);
            });

            // Limpiar filtros
            $('#btn-limpiar-filtros').on('click', function () {
                $('#filter-ruc').val('');
                $('#filter-empresa').val('');
                tabla.ajax.reload();
            });

            // Eliminar empresa mediante la ruta existente
            $(document).on('click', '.btn-eliminar-empresa', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const empresa = decodeURIComponent(
                    String($(this).attr('data-empresa') || '')
                );

                function eliminarEmpresa() {
                    window.location.href =
                        `${baseUrl}/bolsa/empresas/eliminar/${id}`;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Eliminar empresa?',
                        text: `Se dará de baja la empresa "${empresa}". Esta acción cambiará su estado a inactivo.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            eliminarEmpresa();
                        }
                    });
                } else {
                    if (confirm(
                        `¿Está seguro de eliminar la empresa "${empresa}"?`
                    )) {
                        eliminarEmpresa();
                    }
                }
            });

        });
    </script>

<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>No cuentas con permisos para visualizar este módulo.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>