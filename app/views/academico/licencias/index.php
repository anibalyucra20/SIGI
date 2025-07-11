<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="card p-3">
    <h4 class="mb-2">Licencias de estudios</h4>

    <form class="row g-2 mb-3" id="filtros-licencias" autocomplete="off">
        <div class="col-md-2">
            <input type="text" id="filtro-dni" class="form-control" placeholder="DNI">
        </div>
        <div class="col-md-2">
            <input type="text" id="filtro-nombres" class="form-control" placeholder="Apellidos y Nombres">
        </div>
        <div class="col-md-2">
            <select id="filtro-programa" class="form-control">
                <option value="">Todos los programas</option>
                <?php foreach ($programas as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select id="filtro-plan" class="form-control">
                <option value="">Todos los planes</option>
            </select>
        </div>
        <div class="col-md-2">
            <select id="filtro-semestre" class="form-control">
                <option value="">Semestre</option>
            </select>
        </div>
        <div class="col-md-1">
            <select id="filtro-turno" class="form-control">
                <option value="">Turno</option>
                <option value="M">Mañana</option>
                <option value="T">Tarde</option>
                <option value="N">Noche</option>
            </select>
        </div>
        <div class="col-md-1">
            <select id="filtro-seccion" class="form-control">
                <option value="">Sección</option>
                <?php foreach (['A','B','C','D','E'] as $sec): ?>
                    <option value="<?= $sec ?>"><?= $sec ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="mb-2 text-end">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalLicencia">
            <i class="fa fa-plus"></i> Agregar Licencia
        </button>
    </div>

    <table class="table table-bordered table-sm align-middle" id="tabla-licencias">
        <thead class="table-light">
            <tr>
                <th>Nro</th>
                <th>DNI</th>
                <th>Apellidos y Nombres</th>
                <th>Programa</th>
                <th>Plan</th>
                <th>Semestre</th>
                <th>Turno</th>
                <th>Sección</th>
                <th>Licencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody><!-- DataTables AJAX llena esto --></tbody>
    </table>
</div>

<!-- Modal registro licencia -->
<div class="modal fade" id="modalLicencia" tabindex="-1" role="dialog" aria-labelledby="modalLicenciaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formLicencia" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLicenciaLabel">Registrar Licencia</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <!-- Buscar estudiante -->
        <div class="form-group mb-2">
            <label>DNI del estudiante</label>
            <div class="input-group">
                <input type="text" class="form-control" id="dniLicencia" maxlength="15" placeholder="Ingrese DNI" required>
                <div class="input-group-append">
                    <button type="button" id="btnBuscarMatricula" class="btn btn-primary">Buscar</button>
                </div>
            </div>
            <small id="msgBuscarMatricula" class="text-danger"></small>
        </div>
        <div id="infoMatricula" style="display:none;">
            <input type="hidden" name="id_matricula" id="id_matricula">
            <div class="form-group">
                <label>Estudiante</label>
                <input type="text" id="nombreEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Programa de estudios</label>
                <input type="text" id="programaEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Plan de estudios</label>
                <input type="text" id="planEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Semestre</label>
                <input type="text" id="semestreEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Turno</label>
                <input type="text" id="turnoEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Sección</label>
                <input type="text" id="seccionEstudiante" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Licencia *</label>
                <input type="text" name="licencia" class="form-control" maxlength="50" required>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" id="btnGuardarLicencia" class="btn btn-success" style="display:none;">Guardar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabla = $('#tabla-licencias').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: '<?= BASE_URL ?>/academico/licencias/data',
            type: 'GET',
            data: function(d) {
                d.dni = $('#filtro-dni').val();
                d.apellidos_nombres = $('#filtro-nombres').val();
                d.programa = $('#filtro-programa').val();
                d.plan = $('#filtro-plan').val();
                d.semestre = $('#filtro-semestre').val();
                d.turno = $('#filtro-turno').val();
                d.seccion = $('#filtro-seccion').val();
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + 1 + meta.settings._iDisplayStart; } },
            { data: 'dni' },
            { data: 'apellidos_nombres' },
            { data: 'programa' },
            { data: 'plan' },
            { data: 'semestre' },
            { data: 'turno' },
            { data: 'seccion' },
            { data: 'licencia' },
            { data: null, orderable: false, render: function(data, type, row) {
                return `<a href="<?= BASE_URL ?>/academico/licencias/eliminar/${row.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar licencia?');"><i class="fa fa-trash"></i></a>`;
            }}
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' }
    });

    // Recarga por filtros
    $('#filtro-dni, #filtro-nombres').on('keyup', function() { tabla.ajax.reload(); });
    $('#filtro-programa, #filtro-plan, #filtro-semestre, #filtro-turno, #filtro-seccion').on('change', function() { tabla.ajax.reload(); });

    // Programa->Planes
    $('#filtro-programa').on('change', function() {
        let idPrograma = $(this).val();
        $('#filtro-plan').html('<option value="">Todos los planes</option>');
        $('#filtro-semestre').html('<option value="">Semestre</option>');
        if (idPrograma) {
            $.getJSON('<?= BASE_URL ?>/academico/licencias/planesPorPrograma/' + idPrograma, function(planes) {
                planes.forEach(function(pl) {
                    $('#filtro-plan').append('<option value="' + pl.id + '">' + pl.nombre + '</option>');
                });
            });
        }
        tabla.ajax.reload();
    });

    // Plan->Semestre
    $('#filtro-plan').on('change', function() {
        let idPlan = $(this).val();
        $('#filtro-semestre').html('<option value="">Semestre</option>');
        if (idPlan) {
            $.getJSON('<?= BASE_URL ?>/academico/licencias/semestresPorPlan/' + idPlan, function(semestres) {
                semestres.forEach(function(s) {
                    $('#filtro-semestre').append('<option value="' + s.id + '">' + s.descripcion + '</option>');
                });
            });
        }
        tabla.ajax.reload();
    });

    // MODAL: Buscar matrícula x DNI
    $('#btnBuscarMatricula').on('click', function() {
        let dni = $('#dniLicencia').val();
        $('#msgBuscarMatricula').text('');
        $('#infoMatricula').hide();
        $('#btnGuardarLicencia').hide();
        if (!dni) { $('#msgBuscarMatricula').text('Ingrese DNI'); return; }
        $.getJSON('<?= BASE_URL ?>/academico/licencias/buscarMatriculaAjax?dni=' + dni, function(resp) {
            if (resp.success) {
                $('#id_matricula').val(resp.data.id_matricula);
                $('#nombreEstudiante').val(resp.data.apellidos_nombres);
                $('#programaEstudiante').val(resp.data.programa);
                $('#planEstudiante').val(resp.data.plan);
                $('#semestreEstudiante').val(resp.data.semestre);
                $('#turnoEstudiante').val(resp.data.turno);
                $('#seccionEstudiante').val(resp.data.seccion);
                $('#infoMatricula').show();
                $('#btnGuardarLicencia').show();
            } else {
                $('#msgBuscarMatricula').text(resp.msg || 'No se encontró matrícula para ese DNI.');
            }
        });
    });

    // Guardar licencia
    $('#formLicencia').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post('<?= BASE_URL ?>/academico/licencias/guardar', formData, function(resp) {
            if (resp.success) {
                $('#modalLicencia').modal('hide');
                tabla.ajax.reload();
            } else {
                alert(resp.msg || 'No se pudo registrar la licencia');
            }
        }, 'json');
    });

    // Limpia modal al cerrar
    $('#modalLicencia').on('hidden.bs.modal', function () {
        $('#formLicencia')[0].reset();
        $('#infoMatricula').hide();
        $('#btnGuardarLicencia').hide();
        $('#msgBuscarMatricula').text('');
    });
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
