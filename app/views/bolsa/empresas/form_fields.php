    <div class="col-md-6 mb-2">
        <label class="form-label">Ubicación</label>
        <input type="text" name="ubicacion" class="form-control" maxlength="200" placeholder="Ejemplo: Huanta, Ayacucho" value="<?= htmlspecialchars($oferta['ubicacion'] ?? '') ?>" required>
    </div>

    <div class="col-md-3 mb-2">
        <label class="form-label">Fecha de Publicación</label>
        <input type="date" name="fecha_publicacion" class="form-control" value="<?= htmlspecialchars($oferta['fecha_publicacion'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="col-md-3 mb-2">
        <label class="form-label">Fecha de Cierre</label>
        <input type="date" name="fecha_cierre" class="form-control" value="<?= htmlspecialchars($oferta['fecha_cierre'] ?? '') ?>" required>
    </div>