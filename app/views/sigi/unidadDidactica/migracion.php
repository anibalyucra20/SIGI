<?php
$conexion_sispa = mysqli_connect('localhost', 'root', '', 'sispa');
if ($conexion_sispa) {
    date_default_timezone_set("America/Lima");
} else {
    echo "error de conexion a la base de datos";
}
mysqli_set_charset($conexion_sispa, "utf8");

$conexion_sigi = mysqli_connect('localhost', 'root', '', 'sigi_huanta');
if ($conexion_sigi) {
    date_default_timezone_set("America/Lima");
} else {
    echo "error de conexion a la base de datos";
}
mysqli_set_charset($conexion_sigi, "utf8");


function buscarSesionById($conexion, $id)
{
    $sql = "SELECT * FROM sesion_aprendizaje WHERE id='$id'";
    return mysqli_query($conexion, $sql);
}

?>
<style>
    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<table>
    <tr>
        <th>Nro</th>
        <th>ID</th>
        <th>ESTUDIANTE</th>
        <th>PERIODO</th>
        <th>PROGRAMA ESTUDIO</th>
        <th>SEMESTRE</th>
        <th></th>
        <th>ID_ESTUDIANTE</th>
        <th>PERIODO</th>
        <th>PROGRAMA ESTUDIO</th>
        <th>SEMESTRE</th>
        <th>Observacion</th>
    </tr>

    <?php

    // Asistencia
    /* $buscar_asistencia_sispa = "SELECT * FROM asistencia";
    $r_buscar_asistencia_sispa = mysqli_query($conexion_sispa, $buscar_asistencia_sispa);
    while ($res_buscar_asistencia_sispa = mysqli_fetch_array($r_buscar_asistencia_sispa)) {
        $id_asistencia = $res_buscar_asistencia_sispa['id'];
        $id_sesion_aprendizaje = $res_buscar_asistencia_sispa['id_sesion_aprendizaje'];
        $id_estudiante_sispa = $res_buscar_asistencia_sispa['id_estudiante'];
        $asistencia = $res_buscar_asistencia_sispa['asistencia'];

        $b_est_sispa = "SELECT * FROM estudiante WHERE id='$id_estudiante_sispa'";
        $r_b_est_sispa = mysqli_query($conexion_sispa, $b_est_sispa);
        $res_b_est_sispa = mysqli_fetch_array($r_b_est_sispa);
        $dni_est_sigi = $res_b_est_sispa['dni'];

        $b_est_sigi = "SELECT * FROM sigi_usuarios WHERE dni='$dni_est_sigi'";
        $ejec_b_est_sigi = mysqli_query($conexion_sigi, $b_est_sigi);
        $res_b_est_sigi = mysqli_fetch_array($ejec_b_est_sigi);
        $id_est_sigi = $res_b_est_sigi['id'];

        $b_sesion_sigi = "SELECT * FROM acad_sesion_aprendizaje WHERE id='$id_sesion_aprendizaje'";
        $ejec_b_sesion_sigi = mysqli_query($conexion_sigi, $b_sesion_sigi);
        $res_b_sesion_sigi = mysqli_fetch_array($ejec_b_sesion_sigi);
        $id_prog_act_silabo = $res_b_sesion_sigi['id_prog_actividad_silabo'];

        $b_prog_act_silabo = "SELECT * FROM acad_programacion_actividades_silabo WHERE id='$id_prog_act_silabo'";
        $ejec_b_prog_act_silabo = mysqli_query($conexion_sigi, $b_prog_act_silabo);
        $res_b_prog_act_silabo = mysqli_fetch_array($ejec_b_prog_act_silabo);
        $id_silabo = $res_b_prog_act_silabo['id_silabo'];

        $b_silabo = "SELECT * FROM acad_silabos WHERE id='$id_silabo'";
        $ejec_b_silabo = mysqli_query($conexion_sigi, $b_silabo);
        $res_b_silabo = mysqli_fetch_array($ejec_b_silabo);
        $id_prog_ud = $res_b_silabo['id_prog_unidad_didactica'];

        $b_detalle_mat = "SELECT * FROM acad_detalle_matricula WHERE id_programacion_ud='$id_prog_ud'";
        $ejec_b_detalle_mat = mysqli_query($conexion_sigi, $b_detalle_mat);
        $id_detalle_matricula = '';

        while ($r_b_det_mat = mysqli_fetch_array($ejec_b_detalle_mat)) {
            $id_det_mat = $r_b_det_mat['id'];
            $id_matricula = $r_b_det_mat['id_matricula'];

            $b_matricula = "SELECT * FROM acad_matricula WHERE id='$id_matricula'";
            $ejec_b_matricula = mysqli_query($conexion_sigi, $b_matricula);
            $res_b_matricula = mysqli_fetch_array($ejec_b_matricula);
            $id_est_pe = $res_b_matricula['id_estudiante'];

            $b_est_pe = "SELECT * FROM acad_estudiante_programa WHERE id='$id_est_pe'";
            $ejec_b_est_pe = mysqli_query($conexion_sigi, $b_est_pe);
            $res_b_est_pe = mysqli_fetch_array($ejec_b_est_pe);
            $id_estudiante_pe = $res_b_est_pe['id_usuario'];

            if ($id_estudiante_pe == $id_est_sigi) {
                $id_detalle_matricula = $id_det_mat;
            }
        }

        //registrar
        $sql_migracion = "INSERT INTO acad_asistencia (id, id_sesion_aprendizaje, id_detalle_matricula, asistencia) VALUES ('$id_asistencia','$id_sesion_aprendizaje','$id_detalle_matricula','$asistencia')";
        $ejecutar = mysqli_query($conexion_sigi, $sql_migracion);
    }

*/


    // Asume que $conexion_sispa y $conexion_sigi ya están abiertos
    ini_set('max_execution_time', 3600);
    ini_set('memory_limit', '4048M');
    // 1) Preparar todas las sentencias que vamos a usar
    $stmtAsis     = $conexion_sispa->prepare(
        "SELECT * FROM criterio_evaluacion"
    );


    $stmtInsert   = $conexion_sigi->prepare(
        "INSERT INTO acad_criterio_evaluacion
       (id, id_evaluacion, orden, detalle, calificacion)
     VALUES (?, ?, ?, ?, ?)"
    );

    // 2) Ejecutar y recorrer asistencias
    $stmtAsis->execute();
    $resultAsis = $stmtAsis->get_result();

    while ($asis = $resultAsis->fetch_assoc()) {
        // Datos básicos
        $id_criterio      = $asis['id'];
        $id_evaluacion    = $asis['id_evaluacion'];
        $orden            = $asis['orden'];
        $detalle          = $asis['detalle'];
        $calificacion     = $asis['calificacion'];

        // 11) Insertar la asistencia en la nueva tabla
        //     (siempre que hallamos encontrado detalle de matrícula)
        /*$stmtInsert->bind_param(
            'iiisi',
            $id_criterio,
            $id_evaluacion,
            $orden,
            $detalle,
            $calificacion
        );
        $stmtInsert->execute();*/
    }

    // 12) Cerrar statements
    $stmtAsis->close();
    $stmtInsert->close();

    ?>
</table>