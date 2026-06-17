<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminBiblioteca()): ?>
    <div class="card p-3">
        <h3 class="mb-3"><i class="fa fa-history text-primary"></i> Reporte de Auditoría y Lecturas</h3>

        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold small">Usuario:</label>
                <select id="filter-usuario" class="form-control form-control-sm">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['apellidos_nombres']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold small">Fecha Inicio:</label>
                <input type="date" id="filter-fecha-ini" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold small">Fecha Fin:</label>
                <input type="date" id="filter-fecha-fin" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <input type="hidden" id="filter-tabla" value="biblioteca_libros">
        </div>

        <div class="table-responsive">
            <table id="tabla-logs" class="table table-bordered table-hover table-sm align-middle mb-0" width="100%">
                <thead class="table-light">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Rol</th>
                        <th>Usuario</th>
                        <th>Título del Libro</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = $('#tabla-logs').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: true,
                order: [[0, "desc"]], 
                ajax: {
                    url: '<?= BASE_URL ?>/biblioteca/lecturas/data',
                    type: 'GET',
                    data: function(d) {
                        // Forzamos el envío de valores limpios o nulos exactos
                        d.filter_usuario   = $('#filter-usuario').val() || '';
                        d.filter_fecha_ini = $('#filter-fecha-ini').val() || '';
                        d.filter_fecha_fin = $('#filter-fecha-fin').val() || '';
                        d.filter_tabla     = $('#filter-tabla').val() || '';
                    }
                },
                columns: [
                    {
                        data: 'fecha',
                        render: function(data) {
                            return data ? `<span class="text-monospace">${data}</span>` : '—';
                        }
                    },
                    {
                        data: 'rol',
                        render: function(data) {
                            let badge = 'badge-secondary';
                            if (data === 'Administrador' || data === 'Admin') badge = 'badge-danger';
                            if (data === 'Estudiante') badge = 'badge-info';
                            if (data === 'Docente') badge = 'badge-success';
                            return `<span class="badge ${badge}">${html(data)}</span>`;
                        }
                    },
                    {
                        data: 'usuario',
                        render: d => html(d)
                    },
                    {
                        data: 'libro', // Sincronizado perfectamente con el payload del backend
                        render: d => html(d)
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                pageLength: 25
            });

            // Cambiamos el listener a 'input change' para asegurar captura en todos los navegadores
            $('#filter-usuario, #filter-fecha-ini, #filter-fecha-fin').on('change input', function() {
                tabla.ajax.reload();
            });

            function html(s) {
                if (!s) return '';
                return String(s).replace(/[&<>"']/g, function(m) {
                    switch (m) {
                        case '&': return '&amp;';
                        case '<': return '&lt;';
                        case '>': return '&gt;';
                        case '"': return '&quot;';
                        case "'": return '&#39;';
                        default: return m;
                    }
                });
            }
        });
    </script>
<?php else: ?>
    <div class="alert alert-warning m-3" role="alert">
        <h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Acceso Restringido</h4>
        <p>El módulo de reportes y auditoría del SIGI está reservado exclusivamente para usuarios con el rol de <strong>Administrador de Biblioteca</strong>.</p>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>