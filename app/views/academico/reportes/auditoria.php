<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="card p-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Auditoría de Cambios</h3>

    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="global_filter" class="form-control" placeholder="Buscar por usuario, tabla o valor...">
        </div>
    </div>

    <div class="table-responsive">
        <table id="tabla-auditoria" class="table table-bordered table-hover table-sm align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th style="width: 15%">Fecha/Hora</th>
                    <th style="width: 20%">Usuario Responsable</th>
                    <th style="width: 15%">Contexto</th>
                    <th style="width: 40%">Detalle del Cambio</th>
                    <th style="width: 10%">IP</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Configuración DataTables Server-Side
        const tabla = $('#tabla-auditoria').DataTable({
            processing: true,
            serverSide: true, // ¡Clave para rendimiento!
            ajax: {
                url: '<?= BASE_URL ?>/academico/reportes/AuditoriaData', // Ajusta esta ruta a tu Router
                type: 'GET'
            },
            order: [
                [0, "desc"]
            ], // Orden inicial por fecha
            searching: true, // Activamos búsqueda nativa (se conecta con el input search del modelo)

            // Definición de Columnas y Renderizado Visual
            columns: [{
                    data: 'fecha',
                    render: function(data, type, row) {
                        // Formateo de fecha JS simple
                        if (!data) return '';
                        let dateObj = new Date(data);
                        // Ajuste manual simple o usar librerías como moment.js si tienes
                        let fecha = data.split(' ')[0].split('-').reverse().join('/');
                        let hora = data.split(' ')[1];
                        return `${fecha} <br> <small class="text-muted">${hora}</small>`;
                    }
                },
                {
                    data: 'apellidos_nombres',
                    render: function(data, type, row) {
                        let dni = row.dni ? `<div class="small text-muted">DNI: ${row.dni}</div>` : '';
                        return `<strong>${data || 'Usuario Sistema'}</strong>${dni}`;
                    }
                },
                {
                    data: 'tabla_afectada',
                    render: function(data, type, row) {
                        return `<span class="badge bg-secondary text-white fw-normal">${data}</span>
                                <div class="small mt-1 text-muted">Campo: <em>${row.campo_afectado}</em></div>`;
                    }
                },
                {
                    data: null, // Columna compuesta (Valor anterior vs Nuevo)
                    orderable: false,
                    render: function(data, type, row) {
                        // RECREAMOS TU LÓGICA VISUAL (ROJO -> VERDE) AQUÍ EN JS
                        let valAnt = row.valor_anterior !== null ? row.valor_anterior : '<em>Vacío</em>';
                        let valNue = row.valor_nuevo !== null ? row.valor_nuevo : '<em>Vacío</em>';

                        return `
                            <div class="d-flex align-items-center p-1 bg-light rounded border-0">
                                <div class="text-danger me-2" style="text-decoration: line-through; opacity: 0.8; margin-right: 10px;">
                                    ${valAnt}
                                </div>
                                <span class="text-muted mx-2"><i class="fas fa-arrow-right"></i></span>
                                <div class="text-success fw-bold">
                                    ${valNue}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ip_origen',
                    className: "text-center small text-muted"
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            },
            dom: 'rtip' // Ocultamos el buscador por defecto 'f' para usar el nuestro si queremos, o usa 'frtip'
        });

        // Conectar tu input de búsqueda personalizado al DataTable
        $('#global_filter').on('keyup', function() {
            tabla.search(this.value).draw();
        });
    });
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>