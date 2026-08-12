<div class="row">

    <div class="col-md-5 mb-2">
        <label class="form-label">Empresa</label>
        <select name="id_empresa" id="id_empresa" class="form-control" required>
            <option value="">Seleccione una empresa...</option>
            <?php if (!empty($empresas)): ?>
                <?php foreach ($empresas as $empresa): ?>
                    <option 
                        value="<?= $empresa['id'] ?>"
                        <?= (isset($oferta['id_empresa']) && $oferta['id_empresa'] == $empresa['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($empresa['empresa']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="col-md-4 mb-2">
        <label class="form-label">Programa de Estudio</label>
        <select name="programa_estudio" id="programa_estudio" class="form-control" required>
            <option value="">Seleccione...</option> 
            <?php foreach ($programas as $p): ?>
                <option
                    value="<?= $p['id'] ?>"
                    <?= (($oferta['programa_estudio'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3 mb-2">
        <label class="form-label">Tipo de Contrato</label>
        <select name="tipo_contrato" id="tipo_contrato" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="Tiempo completo"
                <?= (($oferta['tipo_contrato'] ?? '') == 'Tiempo completo') ? 'selected' : '' ?>>
                Tiempo completo
            </option>
            <option value="Medio tiempo"
                <?= (($oferta['tipo_contrato'] ?? '') == 'Medio tiempo') ? 'selected' : '' ?>>
                Medio tiempo
            </option>
            <option value="Prácticas"
                <?= (($oferta['tipo_contrato'] ?? '') == 'Prácticas') ? 'selected' : '' ?>>
                Prácticas
            </option>
            <option value="Temporal"
                <?= (($oferta['tipo_contrato'] ?? '') == 'Temporal') ? 'selected' : '' ?>>
                Temporal
            </option>
            <option value="Por proyecto"
                <?= (($oferta['tipo_contrato'] ?? '') == 'Por proyecto') ? 'selected' : '' ?>>
                Por proyecto
            </option>
        </select>
    </div>

    <div class="col-md-8 mb-2">
        <label class="form-label">Título de la Oferta</label>
        <input type="text" name="titulo" class="form-control" maxlength="200" placeholder="Ejemplo: Soporte Técnico" value="<?= htmlspecialchars($oferta['titulo'] ?? '') ?>" required>
    </div>

    <div class="col-md-4 mb-2">
        <label class="form-label">Salario</label>
        <input type="text" name="salario" class="form-control" maxlength="100" value="<?= htmlspecialchars($oferta['salario'] ?? '') ?>">
    </div>

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

    <div class="col-md-6 mb-2">
        <label class="form-label">Detalle de la Oferta</label>
        <textarea 
            name="detalle"
            class="form-control"
            rows="5"
            placeholder="Describa las funciones y características del puesto..."
            required
        ><?= htmlspecialchars($oferta['detalle'] ?? '') ?></textarea>
    </div>

    <div class="col-md-6 mb-2">
        <label class="form-label">Requisitos</label>
        <textarea name="requisitos" class="form-control" rows="5" placeholder="Ingrese los requisitos necesarios para el puesto..." required
        ><?= htmlspecialchars($oferta['requisitos'] ?? '') ?></textarea>
    </div>

    <div class="col-md-8 mb-2">
        <label class="form-label">Foto / Imagen</label>
        <input type="url" name="foto" class="form-control" value="<?= htmlspecialchars($oferta['foto'] ?? '') ?>">
    </div>

    <div class="col-md-4 mb-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-control" required>
            <option value="1"
                <?= (!isset($oferta['estado']) || $oferta['estado'] == 1) ? 'selected' : '' ?>>
                Activo
            </option>
            <option value="0"
                <?= (isset($oferta['estado']) && $oferta['estado'] == 0) ? 'selected' : '' ?>>
                Inactivo
            </option>
        </select>
    </div>

</div>


