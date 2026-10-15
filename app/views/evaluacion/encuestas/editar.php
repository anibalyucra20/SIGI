<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-edit text-primary mr-2"></i> Editar Encuesta de Evaluación Docente
                </h5>
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/evaluacion/encuestas/actualizar/<?= $encuesta['id'] ?>" method="POST">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Título de la Encuesta: <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" required 
                               value="<?= htmlspecialchars($encuesta['titulo']) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Descripción / Instrucciones:</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($encuesta['descripcion']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Periodo Académico:</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($encuesta['periodo_nombre']) ?>">
                            <small class="text-muted">El periodo de una encuesta activa no se puede modificar.</small>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Programa de Estudios:</label>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($encuesta['programa_nombre'] ?? 'Todos los Programas') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Fecha de Inicio: <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="<?= date('Y-m-d', strtotime($encuesta['fecha_inicio'])) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Fecha de Cierre: <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="<?= date('Y-m-d', strtotime($encuesta['fecha_fin'])) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Estado:</label>
                            <select name="estado" class="form-control">
                                <option value="ACTIVA" <?= $encuesta['estado'] === 'ACTIVA' ? 'selected' : '' ?>>ACTIVA</option>
                                <option value="BORRADOR" <?= $encuesta['estado'] === 'BORRADOR' ? 'selected' : '' ?>>BORRADOR</option>
                                <option value="CERRADA" <?= $encuesta['estado'] === 'CERRADA' ? 'selected' : '' ?>>CERRADA</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
