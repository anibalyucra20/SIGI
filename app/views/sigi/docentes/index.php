<?php require __DIR__ . '/../../layouts/header.php'; ?>
<?php if (\Core\Auth::esAdminSigi()): ?>
  <h4 class="card-title mb-3">Lista de Docentes</h4>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/sigi/docentes/nuevo">+Nuevo</a><br><br>
  <!-- Filtros -->
  <div class="card p-2">
    <div class="row g-2 mb-2">
      <div class="col-md-3"><input id="f_dni" class="form-control" placeholder="Buscar DNI" maxlength="20" autofocus></div>
      <div class="col-md-3"><input id="f_nom" class="form-control" placeholder="Apellidos y nombres" maxlength="120"></div>
      <div class="col-md-2">
        <select id="f_estado" class="form-control">
          <option value="">Todos los estados</option>
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>
      <div class="col-md-2">
        <button id="btn-filtrar" class="btn btn-primary w-100">Filtrar</button>
      </div>
    </div>
  </div>

  <div class="card p-2">
    <table id="tabla-docentes" class="table table-striped table-bordered dt-responsive nowrap col-12">
      <thead>
        <tr>
          <th>Nro</th>
          <th>DNI</th>
          <th>Apellidos y Nombres</th>
          <th>Email</th>
          <th>Estado</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
  <?php require __DIR__ . '/../../layouts/footer.php'; ?>
  <script>
    $(function() {
      // DataTable server-side
      var tabla = $('#tabla-docentes').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        ajax: {
          url: '<?= BASE_URL ?>/docentes/data',
          data: function(d) {
            d.filter_dni = $('#f_dni').val();
            d.filter_nombres = $('#f_nom').val();
            d.filter_estado = $('#f_estado').val();

          }
        },
        columns: [{
            data: null,
            orderable: false,
            render: function(data, type, row, meta) {
              // meta.row: 0,1,2 dentro de la página
              // _iDisplayStart = primer índice de la página actual
              return meta.row + 1 + meta.settings._iDisplayStart;
            }
          }, {
            data: 'dni'
          }, {
            data: 'apellidos_nombres'
          },
          {
            data: 'correo'
          },
          {
            data: 'estado_text'
          }, // ← muestra “Activo / Inactivo”
          {
            data: 'nombre_rol'
          },
          {
            data: 'id',
            orderable: false,
            render: function(data, type, row) {
              return `
            <a href="<?= BASE_URL ?>/sigi/docentes/ver/${row.id}"      title="ver" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
            <a href="<?= BASE_URL ?>/sigi/docentes/editar/${row.id}"   title="editar" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
            <a href="<?= BASE_URL ?>/sigi/docentes/permisos/${row.id}" title="Permisos" class="btn btn-primary btn-sm"><i class="fa fa-project-diagram"></i></a>
            <a href="<?= BASE_URL ?>/resetPassword?data=${btoa(row.id)}&back=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Enviar Correo" class="btn btn-dark btn-sm"><i class="fa fa-envelope"></i></a>
            <a href="<?= BASE_URL ?>/sigi/docentes/resetPassword?data=${btoa(row.id)}&back=<?= urlencode($_SERVER['REQUEST_URI']) ?>" title="Cambiar Contraseña" class="btn btn-success btn-sm"><i class="fa fa-key"></i></a>`;
            }
          }
        ],
        order: [
          [2, 'asc']
        ],
        responsive: true, // importante
      });
      $('#f_dni, #f_nom, #f_estado').on('input change', function() {
        tabla.ajax.reload();
      });

      // Botón filtrar
      $('#btn-filtrar').on('click', function() {
        tabla.ajax.reload();
      });
    });
  </script>
<?php else: ?>
  <!-- Para director o coordinador en SIGI -->
  <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>