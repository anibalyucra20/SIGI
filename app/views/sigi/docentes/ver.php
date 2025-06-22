<?php require __DIR__ . '/../../layouts/header.php'; ?>

<?php if (\Core\Auth::esAdminSigi()): ?>
  <h3>Detalles del Docente</h3>
  <div class="card p-2">
    <table class="table">
      <tr>
        <th>DNI</th>
        <td><?= htmlspecialchars($docente['dni']) ?></td>
      </tr>
      <tr>
        <th>Nombre Completo</th>
        <td><?= htmlspecialchars($docente['apellidos_nombres']) ?></td>
      </tr>
      <tr>
        <th>Correo</th>
        <td><?= htmlspecialchars($docente['correo']) ?></td>
      </tr>
      <tr>
        <th>Teléfono</th>
        <td><?= htmlspecialchars($docente['telefono']) ?></td>
      </tr>
      <tr>
        <th>Dirección</th>
        <td><?= htmlspecialchars($docente['direccion']) ?></td>
      </tr>
      <tr>
        <th>Género</th>
        <td><?= htmlspecialchars($docente['genero']) ?></td>
      </tr>
      <tr>
        <th>Fecha Nacimiento</th>
        <td><?= htmlspecialchars($docente['fecha_nacimiento']) ?></td>
      </tr>
      <tr>
        <th>Estado</th>
        <td><?= $docente['estado'] ? 'Activo' : 'Inactivo' ?></td>
      </tr>
      <tr>
        <th>Rol</th>
        <td><?= htmlspecialchars($docente['nombre_rol']) ?></td>
      </tr>
      <tr>
        <th>Sede</th>
        <td><?= htmlspecialchars($docente['nombre_sede']) ?></td>
      </tr>
      <tr>
        <th>Programa</th>
        <td><?= htmlspecialchars($docente['nombre_programa']) ?></td>
      </tr>
    </table>
  </div>
  <h4>Permisos del Usuario</h4>
  <?php if (!empty($data['permisos'])): ?>
    <div class="card p-2">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Sistema</th>
            <th>Rol</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['permisos'] as $permiso): ?>
            <tr>
              <td><?= htmlspecialchars($permiso['sistema']) ?></td>
              <td><?= htmlspecialchars($permiso['rol']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p>Este usuario no tiene permisos asignados.</p>
  <?php endif; ?>


  <a href="<?= BASE_URL ?>/sigi/docentes" class="btn btn-secondary">Volver</a>

  <br>
  <br>
<?php else: ?>
  <!-- Para director o coordinador en SIGI -->
  <p>El Modulo SIGI solo es para rol de Administrador</p>
<?php endif; ?>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>