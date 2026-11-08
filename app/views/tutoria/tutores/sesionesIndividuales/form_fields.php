<div class="row">
    <div class="col-md-2 form-group">
        <label for="nro" class="font-weight-bold">N° Sesión <span class="text-danger">*</span></label>
        <input type="number" name="nro" id="nro" class="form-control" required
               value="<?= $isEdit ? htmlspecialchars($sesion['nro']) : htmlspecialchars($siguienteNro) ?>" min="1">
    </div>

    <div class="col-md-4 form-group">
        <label for="fecha_hora" class="font-weight-bold">Fecha y Hora <span class="text-danger">*</span></label>
        <?php
            $fechaVal = $isEdit ? date('Y-m-d\TH:i', strtotime($sesion['fecha_hora'])) : date('Y-m-d\TH:i');
        ?>
        <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" required value="<?= $fechaVal ?>">
    </div>

    <div class="col-md-3 form-group">
        <label for="solicitante" class="font-weight-bold">Solicitante de la Sesión</label>
        <select name="solicitante" id="solicitante" class="form-control">
            <?php
            $solicitantes = [
                'DOCENTE'    => 'Docente / Iniciativa del Tutor',
                'ESTUDIANTE' => 'Estudiante',
                'FAMILIAR'   => 'Familiar / Padre de Familia',
                'OTRO'       => 'Otro / Coordinación / Dirección'
            ];
            $solSel = $isEdit ? $sesion['solicitante'] : 'DOCENTE';
            foreach ($solicitantes as $val => $label): ?>
                <option value="<?= $val ?>" <?= $solSel === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3 form-group">
        <label for="asistencia" class="font-weight-bold">Asistencia</label>
        <select name="asistencia" id="asistencia" class="form-control">
            <option value="1" <?= ($isEdit && $sesion['asistencia'] == 1) ? 'selected' : '' ?>>Asistió Puntualmente</option>
            <option value="0" <?= ($isEdit && $sesion['asistencia'] == 0) ? 'selected' : '' ?>>No Asistió / Inasistencia</option>
        </select>
    </div>

    <div class="col-md-8 form-group">
        <label for="motivo" class="font-weight-bold">Motivo Principal de la Atención <span class="text-danger">*</span></label>
        <input type="text" name="motivo" id="motivo" class="form-control" list="motivos_sugeridos" required
               placeholder="Ej. Rendimiento académico, inasistencias, dificultades personales..."
               value="<?= $isEdit ? htmlspecialchars($sesion['motivo']) : '' ?>">
        <datalist id="motivos_sugeridos">
            <option value="Bajo rendimiento académico">
            <option value="Inasistencias continuas a clases">
            <option value="Dificultades socioeconómicas o laborales">
            <option value="Problemas familiares / personales">
            <option value="Orientación vocacional y profesional">
            <option value="Problemas de adaptación o convivencia">
            <option value="Consulta sobre trámites y normativas">
            <option value="Entrevista de seguimiento o diagnóstico">
        </datalist>
    </div>

    <div class="col-md-4 form-group">
        <label for="link_reunion" class="font-weight-bold">Enlace Virtual (Opcional)</label>
        <input type="url" name="link_reunion" id="link_reunion" class="form-control"
               placeholder="https://meet.google.com/..."
               value="<?= $isEdit ? htmlspecialchars($sesion['link_reunion']) : '' ?>">
    </div>

    <div class="col-md-12 form-group">
        <label for="contenidos_tratados" class="font-weight-bold">Aspectos y Temas Tratados en la Sesión</label>
        <textarea name="contenidos_tratados" id="contenidos_tratados" class="form-control" rows="3"
                  placeholder="Detalle los puntos dialogados, situación planteada por el tutorando y hechos relevantes..."><?= $isEdit ? htmlspecialchars($sesion['contenidos_tratados']) : '' ?></textarea>
    </div>

    <div class="col-md-6 form-group">
        <label for="recomendaciones" class="font-weight-bold">Recomendaciones y Orientaciones del Tutor</label>
        <textarea name="recomendaciones" id="recomendaciones" class="form-control" rows="3"
                  placeholder="Orientaciones pedagógicas, métodos sugeridos, pautas de organización..."><?= $isEdit ? htmlspecialchars($sesion['recomendaciones']) : '' ?></textarea>
    </div>

    <div class="col-md-6 form-group">
        <label for="compromiso_estudiante" class="font-weight-bold">Compromisos Asumidos por el Estudiante</label>
        <textarea name="compromiso_estudiante" id="compromiso_estudiante" class="form-control" rows="3"
                  placeholder="Acuerdos específicos asumidos por el tutorando para superar la dificultad..."><?= $isEdit ? htmlspecialchars($sesion['compromiso_estudiante']) : '' ?></textarea>
    </div>
</div>

<hr class="my-3">

<div class="row">
    <!-- Seguimiento -->
    <div class="col-md-6">
        <div class="card bg-light border-0 p-3 h-100">
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="check_seguimiento" name="seguimiento" value="1"
                       <?= ($isEdit && $sesion['seguimiento'] == 1) ? 'checked' : '' ?>>
                <label class="custom-control-label font-weight-bold text-dark" for="check_seguimiento">
                    <i class="fas fa-redo-alt mr-1 text-info"></i> ¿Requiere Sesión Posterior de Seguimiento?
                </label>
            </div>
            <div id="caja_fecha_prox" style="<?= ($isEdit && $sesion['seguimiento'] == 1) ? '' : 'display: none;' ?>">
                <label for="fecha_prox_sesion" class="small font-weight-bold">Fecha / Hora de Próxima Sesión:</label>
                <?php
                    $proxVal = ($isEdit && !empty($sesion['fecha_prox_sesion']) && $sesion['fecha_prox_sesion'] != '2000-01-01 00:00:00')
                        ? date('Y-m-d\TH:i', strtotime($sesion['fecha_prox_sesion']))
                        : '';
                ?>
                <input type="datetime-local" name="fecha_prox_sesion" id="fecha_prox_sesion" class="form-control form-control-sm" value="<?= $proxVal ?>">
            </div>
        </div>
    </div>

    <!-- Derivación -->
    <div class="col-md-6">
        <div class="card bg-light border-0 p-3 h-100">
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="check_derivacion" name="derivacion" value="1"
                       <?= ($isEdit && $sesion['derivacion'] == 1) ? 'checked' : '' ?>>
                <label class="custom-control-label font-weight-bold text-dark" for="check_derivacion">
                    <i class="fas fa-share-square mr-1 text-warning"></i> ¿Derivar a Servicio Especializado?
                </label>
            </div>
            <div id="caja_servicio_derivacion" style="<?= ($isEdit && $sesion['derivacion'] == 1) ? '' : 'display: none;' ?>">
                <label for="servicio_derivacion" class="small font-weight-bold">Servicio Institucional de Destino:</label>
                <select name="servicio_derivacion" id="servicio_derivacion" class="form-control form-control-sm">
                    <option value="">-- Seleccione Servicio --</option>
                    <?php
                    $servicios = [
                        'Social'            => 'Social (Bienestar Estudiantil)',
                        'Psicopedagógico'   => 'Psicopedagógico',
                        'Salud'             => 'Salud / Tópico Médico',
                        'Inserción laboral' => 'Inserción laboral / Bolsa de Trabajo',
                        'Capacitación'      => 'Capacitación',
                        'Otro'              => 'Otro Servicio Especializado'
                    ];
                    $servSel = $isEdit ? $sesion['servicio_derivacion'] : '';
                    foreach ($servicios as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($servSel === $val || (empty($servSel) && $val === 'Psicopedagógico')) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#check_seguimiento').on('change', function() {
        if ($(this).is(':checked')) {
            $('#caja_fecha_prox').slideDown(150);
        } else {
            $('#caja_fecha_prox').slideUp(150);
            $('#fecha_prox_sesion').val('');
        }
    });

    $('#check_derivacion').on('change', function() {
        if ($(this).is(':checked')) {
            $('#caja_servicio_derivacion').slideDown(150);
        } else {
            $('#caja_servicio_derivacion').slideUp(150);
            $('#servicio_derivacion').val('');
        }
    });
});
</script>
