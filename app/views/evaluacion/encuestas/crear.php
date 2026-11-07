<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-dark">
                    <i class="fas fa-plus-circle text-primary mr-2"></i> Crear Nueva Encuesta de Evaluación Docente
                </h5>
                <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/evaluacion/encuestas/guardar" method="POST">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Título de la Encuesta: <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" required 
                               placeholder="Ej: Evaluación de Desempeño Docente 2026-I">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Descripción / Instrucciones para los Estudiantes:</label>
                        <textarea name="descripcion" class="form-control" rows="3" 
                                  placeholder="Estimado estudiante, califique con objetividad el desempeño pedagógico de su docente..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Periodo Académico (Contexto Activo):</label>
                            <input type="hidden" name="id_periodo_academico" value="<?= $idPeriodo ?>">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light text-primary"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($periodoActualNombre) ?>" style="background-color: #f8f9fa; font-weight: 600;">
                            </div>
                            <small class="text-muted">El periodo se asigna automáticamente del selector de contexto del menú superior.</small>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Programa de Estudios (Carrera): <?= !$puedeVerTodos ? '<span class="text-danger">*</span>' : '' ?></label>
                            <select name="id_programa_estudios" class="form-control" <?= !$puedeVerTodos ? 'required' : '' ?>>
                                <?php if ($puedeVerTodos): ?>
                                    <option value="">-- Aplica a Todos los Programas --</option>
                                <?php else: ?>
                                    <option value="">-- Selecciona tu carrera asignada --</option>
                                <?php endif; ?>
                                <?php foreach ($programas as $prg): ?>
                                    <option value="<?= $prg['id'] ?>">
                                        <?= htmlspecialchars($prg['codigo'] . ' - ' . $prg['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                <?= $puedeVerTodos ? 'Deje en blanco si la encuesta aplica a nivel institucional.' : 'Como coordinador, debe seleccionar la carrera asignada a evaluar.' ?>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Fecha de Inicio: <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Fecha de Cierre: <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Estado Inicial:</label>
                            <select name="estado" class="form-control">
                                <option value="ACTIVA" selected>ACTIVA (Disponible para estudiantes)</option>
                                <option value="BORRADOR">BORRADOR (En configuración)</option>
                                <option value="CERRADA">CERRADA</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Opciones Inteligentes de Automatización -->
                    <div class="bg-light p-3 rounded border mb-4">
                        <h6 class="font-weight-bold text-dark mb-3">
                            <i class="fas fa-magic text-warning mr-1"></i> Opciones de Automatización y Rúbrica
                        </h6>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="rubrica_estandar" name="rubrica_estandar" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-dark" for="rubrica_estandar">
                                Cargar Rúbrica desde el Banco Maestro de Criterios y Preguntas (Recomendado)
                            </label>
                            <small class="text-muted d-block pl-4">
                                Inicializa automáticamente los criterios y preguntas activos configurados en el <strong>Banco de Rúbricas</strong> para que no tenga que redactarlos nuevamente.
                            </small>
                        </div>
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" class="custom-control-input" id="generar_asignaciones" name="generar_asignaciones" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-dark" for="generar_asignaciones">
                                Asignar inmediatamente a los Estudiantes Matriculados
                            </label>
                            <small class="text-muted d-block pl-4">
                                Escanea la base de datos de matrículas del periodo y crea las evaluaciones vinculando a cada estudiante con los docentes de sus unidades didácticas.
                            </small>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="<?= BASE_URL ?>/evaluacion/encuestas" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar y Crear Encuesta
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
