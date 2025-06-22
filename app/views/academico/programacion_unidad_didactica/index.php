<?php require __DIR__ . '/../../layouts/header.php'; ?>

<h4 class="card-title mb-3">Programación de Unidades Didácticas</h4>

<?php if ($periodo_activo): ?>
    <a class="btn btn-primary mb-2" href="<?= BASE_URL ?>/academico/programacion_unidad_didactica/nuevo">+ Nueva Programación</a>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-md-2">
        <select id="filtro-programa" class="form-control">
            <option value="">Programa</option>
            <?php foreach($programas as $p): ?>
                <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select id="filtro-plan" class="form-control"><option value="">Plan</option></select>
    </div>
    <div class="col-md-2">
        <select id="filtro-modulo" class="form-control"><option value="">Módulo</option></select>
    </div>
    <div class="col-md-2">
        <select id="filtro-semestre" class="form-control"><option value="">Semestre</option></select>
    </div>
    <div class="col-md-2">
        <select id="filtro-docente" class="form-control">
            <option value="">Docente</option>
            <?php foreach($docentes as $d): ?>
                <option value="<?= $d['id'] ?>"><?= $d['apellidos_nombres'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <input id="filtro-unidad" class="form-control" placeholder="Unidad Didáctica">
    </div>
</div>

<table id="tabla-programacion-unidad" class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Unidad Didáctica</th>
            <th>Docente</th>
            <th>Programa</th>
            <th>Plan de Estudio</th>
            <th>Módulo Formativo</th>
            <th>Semestre</th>
            <th>Turno</th>
            <th>Sección</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
$(document).ready(function(){
    var periodoActivo = <?= $periodo_activo ? 'true' : 'false' ?>;
    var tabla = $('#tabla-programacion-unidad').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= BASE_URL ?>/academico/programacionUnidadDidactica/listar",
            "type": "POST",
            "data": function(d){
                d.programa = $('#filtro-programa').val();
                d.plan     = $('#filtro-plan').val();
                d.modulo   = $('#filtro-modulo').val();
                d.semestre = $('#filtro-semestre').val();
                d.docente  = $('#filtro-docente').val();
                d.unidad   = $('#filtro-unidad').val();
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre_unidad_didactica" },
            { "data": "nombre_docente" },
            { "data": "nombre_programa" },
            { "data": "nombre_plan" },
            { "data": "nombre_modulo" },
            { "data": "nombre_semestre" },
            { "data": "turno" },
            { "data": "seccion" },
            { 
                "data": null,
                "render": function(data, type, row){
                    let btns = `<a href="<?= BASE_URL ?>/academico/programacion_unidad_didactica/ver/${row.id}" class="btn btn-info btn-sm">Ver</a> `;
                    if(periodoActivo){
                        btns += `<a href="<?= BASE_URL ?>/academico/programacion_unidad_didactica/editar/${row.id}" class="btn btn-warning btn-sm">Editar</a>`;
                    }
                    return btns;
                }
            }
        ]
    });

    // Filtros en cascada
    $('#filtro-programa').change(function(){
        $.post('<?= BASE_URL ?>/academico/programacion_unidad_didactica/planesPorPrograma', {id_programa: $(this).val()}, function(data){
            let options = '<option value="">Plan</option>';
            data = JSON.parse(data);
            data.forEach(function(plan){
                options += `<option value="${plan.id}">${plan.nombre}</option>`;
            });
            $('#filtro-plan').html(options);
            $('#filtro-modulo').html('<option value="">Módulo</option>');
            $('#filtro-semestre').html('<option value="">Semestre</option>');
            tabla.ajax.reload();
        });
    });
    $('#filtro-plan').change(function(){
        $.post('<?= BASE_URL ?>/academico/programacion_unidad_didactica/modulosPorPlan', {id_plan: $(this).val()}, function(data){
            let options = '<option value="">Módulo</option>';
            data = JSON.parse(data);
            data.forEach(function(m){
                options += `<option value="${m.id}">${m.descripcion}</option>`;
            });
            $('#filtro-modulo').html(options);
            $('#filtro-semestre').html('<option value="">Semestre</option>');
            tabla.ajax.reload();
        });
    });
    $('#filtro-modulo').change(function(){
        $.post('<?= BASE_URL ?>/academico/programacion_unidad_didactica/semestresPorModulo', {id_modulo: $(this).val()}, function(data){
            let options = '<option value="">Semestre</option>';
            data = JSON.parse(data);
            data.forEach(function(s){
                options += `<option value="${s.id}">${s.descripcion}</option>`;
            });
            $('#filtro-semestre').html(options);
            tabla.ajax.reload();
        });
    });
    $('#filtro-semestre, #filtro-docente').change(function(){
        tabla.ajax.reload();
    });
    $('#filtro-unidad').on('input', function(){
        tabla.ajax.reload();
    });
});
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
