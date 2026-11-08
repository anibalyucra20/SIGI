<div class="row">
    <div class="col-md-4 form-group">
        <label for="tipo" class="font-weight-bold">Tipo de Sesión <span class="text-danger">*</span></label>
        <select name="tipo" id="tipo" class="form-control" required>
            <option value="">-- Seleccione tipo --</option>
            <?php
            $tipos = [
                'Nivelación' => 'Nivelación',
                'Reforzamiento Académico' => 'Reforzamiento Académico',
                'Mentoría' => 'Mentoría',
                'Taller' => 'Taller Práctico / Dinámico',
                'Charla' => 'Charla Informativa / Temática',
                'Inducción' => 'Inducción y Bienvenida al Semestre',
                'Orientación Vocacional' => 'Orientación Vocacional y Profesional',
                'Habilidades Blandas' => 'Desarrollo de Habilidades Blandas',
                'Técnicas de Estudio' => 'Técnicas y Hábitos de Estudio',
                'Clima Institucional' => 'Convivencia y Clima Institucional',
                'Otro' => 'Otra Actividad Grupal'
            ];
            $tipoSel = $isEdit ? $sesion['tipo'] : '';
            foreach ($tipos as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $tipoSel === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4 form-group">
        <label for="fecha_hora" class="font-weight-bold">Fecha y Hora <span class="text-danger">*</span></label>
        <?php
            $fVal = $isEdit ? date('Y-m-d\TH:i', strtotime($sesion['fecha_hora'])) : date('Y-m-d\TH:i');
        ?>
        <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" required value="<?= $fVal ?>">
    </div>

    <div class="col-md-4 form-group">
        <label for="logro_objetivo" class="font-weight-bold">Nivel de Logro del Objetivo</label>
        <select name="logro_objetivo" id="logro_objetivo" class="form-control">
            <?php
            $logros = [
                'No logrado'    => 'No logrado',
                'En proceso'    => 'En proceso',
                'Logrado'       => 'Logrado',
                'Sobresaliente' => 'Sobresaliente'
            ];
            $logroSel = $isEdit ? $sesion['logro_objetivo'] : 'En proceso';
            foreach ($logros as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= strcasecmp($logroSel, $val) === 0 ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-8 form-group">
        <label for="objetivo_sesion" class="font-weight-bold">Objetivo de la Sesión Grupal <span class="text-danger">*</span></label>
        <input type="text" name="objetivo_sesion" id="objetivo_sesion" class="form-control" required
               placeholder="Ej. Promover técnicas de gestión del tiempo y preparación para evaluaciones parciales..."
               value="<?= $isEdit ? htmlspecialchars($sesion['objetivo_sesion']) : '' ?>">
    </div>

    <div class="col-md-4 form-group">
        <label for="link_reunion" class="font-weight-bold">Enlace Virtual (Opcional)</label>
        <input type="url" name="link_reunion" id="link_reunion" class="form-control"
               placeholder="https://meet.google.com/..."
               value="<?= $isEdit ? htmlspecialchars($sesion['link_reunion']) : '' ?>">
    </div>

    <div class="col-md-12 form-group">
        <label for="contenidos" class="font-weight-bold">Contenidos y Actividades Desarrolladas</label>
        <textarea name="contenidos" id="contenidos" class="form-control" rows="3"
                  placeholder="Detalle los temas expuestos, dinámicas realizadas y participación de los estudiantes..."><?= $isEdit ? htmlspecialchars($sesion['contenidos']) : '' ?></textarea>
    </div>

    <!-- Asistencia de Tutorandos -->
    <div class="col-md-12 form-group">
        <div class="card bg-light border p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-user-check mr-1 text-success"></i> Control de Asistencia de Tutorandos
                </label>
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" id="btn_marcar_todos">Marcar Todos</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 ml-1" id="btn_desmarcar_todos">Desmarcar</button>
                </div>
            </div>

            <?php if (empty($estudiantes)): ?>
                <p class="text-muted small mb-0">No hay estudiantes asignados a esta programación de tutoría para marcar asistencia.</p>
            <?php else: ?>
                <div class="row" style="max-height: 220px; overflow-y: auto;">
                    <?php
                    $asistentesArray = $isEdit ? ($asistentesGuardados ?? []) : [];
                    foreach ($estudiantes as $est):
                        $checked = in_array((int)$est['id_estudiante'], $asistentesArray) ? 'checked' : '';
                    ?>
                        <div class="col-md-6 mb-1">
                            <div class="custom-control custom-checkbox small">
                                <input type="checkbox" class="custom-control-input chk-asistente"
                                       id="asist_<?= $est['id_estudiante'] ?>"
                                       name="asistentes[]"
                                       value="<?= $est['id_estudiante'] ?>" <?= $checked ?>>
                                <label class="custom-control-label" for="asist_<?= $est['id_estudiante'] ?>">
                                    [<?= htmlspecialchars($est['dni']) ?>] <?= htmlspecialchars($est['nombre_completo']) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6 form-group">
        <label for="observaciones_sesion" class="font-weight-bold">Observaciones de la Sesión</label>
        <textarea name="observaciones_sesion" id="observaciones_sesion" class="form-control" rows="2"
                  placeholder="Aspectos relevantes observados en el grupo, dudas recurrentes..."><?= $isEdit ? htmlspecialchars($sesion['observaciones_sesion']) : '' ?></textarea>
    </div>

    <div class="col-md-6 form-group">
        <label for="recomendaciones" class="font-weight-bold">Recomendaciones y Tareas Pendientes</label>
        <textarea name="recomendaciones" id="recomendaciones" class="form-control" rows="2"
                  placeholder="Acciones de seguimiento acordadas para la próxima sesión grupal..."><?= $isEdit ? htmlspecialchars($sesion['recomendaciones']) : '' ?></textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#btn_marcar_todos').on('click', function() {
        $('.chk-asistente').prop('checked', true);
    });
    $('#btn_desmarcar_todos').on('click', function() {
        $('.chk-asistente').prop('checked', false);
    });
});
</script>
