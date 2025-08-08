<?php

namespace App\Controllers\Academico;

use Core\Controller;

require_once __DIR__ . '/../../../app/models/Academico/Reportes.php';
require_once __DIR__ . '/../../../app/models/Academico/Calificaciones.php';
require_once __DIR__ . '/../../../app/models/Sigi/CoordinadorPeriodo.php';
require_once __DIR__ . '/../../../app/models/Sigi/Semestre.php';

use App\Models\Academico\Reportes;
use App\Models\Academico\Calificaciones;
use App\Models\Sigi\CoordinadorPeriodo;
use App\Models\Sigi\Semestre;
use TCPDF;

class ReportesController extends Controller
{
    protected $model;
    protected $objCalificacion;
    protected $objCoordinador;
    protected $objSemestre;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Reportes();
        $this->objCalificacion = new Calificaciones();
        $this->objCoordinador = new CoordinadorPeriodo();
        $this->objSemestre = new Semestre();
    }

    public function index()
    {
        $usuario_id = $_SESSION['sigi_user_id'];
        $periodo_id = $_SESSION['sigi_periodo_actual_id'];
        $sede_id    = $_SESSION['sigi_sede_actual'];
        $programas = $this->objCoordinador->getProgramasAsignados($usuario_id, $periodo_id, $sede_id);
        // Puedes pasar $periodo si lo necesitas para la lógica JS de acciones.
        $this->view('academico/reportes/index', [
            'programas' => $programas,
            'module' => 'academico',
            'pageTitle' => 'Reportes'
        ]);
    }
    public function pdfNominaMatricula()
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        $id_programa = $_POST['programa'] ?? null;
        $id_semestre = $_POST['semestre'] ?? null;
        $turno       = $_POST['turno'] ?? null;
        $seccion     = $_POST['seccion'] ?? null;

        $usuario_id = $_SESSION['sigi_user_id'];
        $periodo_id = $_SESSION['sigi_periodo_actual_id'];
        $sede_id    = $_SESSION['sigi_sede_actual'];

        if (!$id_programa || !$id_semestre || !$turno || !$seccion) {
            $_SESSION['flash_error'] = 'Parámetros incompletos para generar el reporte.';
            header('Location: ' . BASE_URL . "/academico/reportes");
        }

        // Obtener los datos del modelo
        $info   = $this->model->getCabeceraNomina($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id);
        $unidades   = $this->model->getUnidadesDidacticas($id_programa, $id_semestre);
        $estudiantes = $this->model->getEstudiantesMatriculados($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id);
        //var_dump($estudiantes);
        $map = [];                // clave = id_usuario   (o DNI)
        foreach ($estudiantes as $fila) {
            $uid = $fila['id_usuario'];          // ← añade a SELECT
            if (!isset($map[$uid])) {
                $map[$uid] = [
                    'dni'  => $fila['dni'],
                    'apellidos_nombres' => $fila['apellidos_nombres'],
                    'uds'  => []
                ];
            }
            // añadimos las UDs de ESTE detalle
            $uds = $this->model->getUnidadesPorEstudiante($fila['id_detalle_matricula']);
            foreach ($uds as $ud) {
                $map[$uid]['uds'][$ud] = true;
            }
        }
        $estudiantes = array_values($map);   // lista final SIN duplicados

        // Crear PDF
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Nómina de Matrícula');
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // Renderizar vista TCPDF
        ob_start();
        include __DIR__ . '/../../views/academico/reportes/pdf_nomina_matricula.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Nomina_Matricula.pdf', 'I');
    }


    public function pdfCalifConsolidado()
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        $id_programa = $_POST['programa'] ?? null;
        $id_semestre = $_POST['semestre'] ?? null;
        $turno       = $_POST['turno'] ?? null;
        $seccion     = $_POST['seccion'] ?? null;

        $periodo_id  = $_SESSION['sigi_periodo_actual_id'];
        $sede_id     = $_SESSION['sigi_sede_actual'];

        if (!$id_programa || !$id_semestre || !$turno || !$seccion) {
            $_SESSION['flash_error'] = "Complete todos los filtros para el consolidado.";
            header('Location: ' . BASE_URL . '/academico/reportes');
            exit;
        }

        //-- datos de cabecera y listas
        $info       = $this->model->getCabeceraNomina($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id);
        $uds        = $this->model->getUnidadesDidacticas($id_programa, $id_semestre);
        $estudiantes = $this->model->getEstudiantesMatriculados($id_programa, $id_semestre, $turno, $seccion, $periodo_id, $sede_id);
        /* ---------- 1.  Construir un caché de nros_calif por UD ---------- */
        $nrosPorUd = [];
        foreach ($uds as $u) {
            $nrosPorUd[$u['id']] = $this->model->getNrosCalificacionPorUd($u['id']);
            if (!$nrosPorUd[$u['id']]) {               // si no hay califs aún
                $nrosPorUd[$u['id']] = [];             // evita null
            }
        }

        /* ---------- 2.  Armar la matriz de estudiantes sin duplicados ---- */
        $map = [];
        foreach ($estudiantes as $fila) {
            $uid = $fila['id_usuario'];
            $ud  = $fila['id_ud'];
            if (!isset($map[$uid])) {
                $map[$uid] = [
                    'dni'               => $fila['dni'],
                    'apellidos_nombres' => $fila['apellidos_nombres'],
                    'promedios'         => []
                ];
            }
            /* ---------- 3.  Calcular promedio final con los nros de ESA UD */
            $nota = $this->objCalificacion->promedioFinalEstudiante(
                (int)$fila['id_detalle_matricula'],
                $nrosPorUd[$ud]                 // <-- aquí la lista dinámica
            );

            $map[$uid]['promedios'][$ud] = $nota !== '' ? $nota : '';
        }
        foreach ($map as &$e) {
            $ptotal   = 0;    // puntaje sin ponderar
            $pcredito = 0;    // puntaje × crédito
            $desap    = 0;    // UDs desaprobadas (<13)

            foreach ($uds as $u) {
                $nota = $e['promedios'][$u['id']] ?? null;

                if ($nota !== null && $nota !== '') {
                    $ptotal   += $nota;
                    $pcredito += $nota * $u['creditos'];
                    if ($nota < 13) $desap++;
                }
            }
            $totalUd = count($uds);

            // Condición
            if ($desap === 0) {
                $cond = 'Promovido';
            } elseif ($desap / $totalUd < 0.5) {
                $cond = 'Repite U.D. del Módulo Profesional';
            } else {
                $cond = 'Repite el Módulo Profesional';
            }

            $e['puntaje_total']   = $ptotal;
            $e['puntaje_credito'] = $pcredito;
            $e['condicion']       = $cond;
        }
        unset($e);           // salimos por seguridad

        $estudiantes = array_values($map);

        /* ---------- PDF ---------- */
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Consolidado ' . $info['programa']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/academico/reportes/pdf_calif_consolidado.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Consolidado.pdf', 'I');
    }

    public function pdfCalifDetallado()
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        /* 1️⃣  Filtros recibidos del modal */
        [$id_programa, $id_semestre, $turno, $seccion] = [
            $_POST['programa'] ?? null,
            $_POST['semestre'] ?? null,
            $_POST['turno']    ?? null,
            $_POST['seccion']  ?? null
        ];

        $periodo_id = $_SESSION['sigi_periodo_actual_id'];
        $sede_id    = $_SESSION['sigi_sede_actual'];

        if (!$id_programa || !$id_semestre || !$turno || !$seccion) {
            $_SESSION['flash_error'] = 'Complete todos los filtros.';
            header('Location: ' . BASE_URL . '/academico/reportes');
            exit;
        }

        /* 2️⃣  Cabecera y Unidades Didácticas */
        $info = $this->model->getCabeceraNomina(
            $id_programa,
            $id_semestre,
            $turno,
            $seccion,
            $periodo_id,
            $sede_id
        );

        $uds = $this->model->getUnidadesDidacticas($id_programa, $id_semestre);

        /* 3️⃣  Para cada UD averiguamos cuántas C-n calificaciones tiene */
        foreach ($uds as &$u) {
            $progUdId        = $this->model->idProgramacionUd(
                $u['id'],
                $periodo_id,
                $sede_id,
                $turno,
                $seccion
            );
            $u['nros_calif'] = $progUdId
                ? $this->model->getNrosEvaluacionPorUd($progUdId)
                : [];
        }
        unset($u);  // rompe referencia

        /* 4️⃣  Traemos todas las calificaciones ya procesadas (nota final) */
        $rows = $this->model->getCalifDetalladas(
            $id_programa,
            $id_semestre,
            $turno,
            $seccion,
            $periodo_id,
            $sede_id
        );

        /* 5️⃣  Re-mapeamos a estructura → $estudiantes[id]['notas'][id_ud][nro] */
        $est = [];
        foreach ($rows as $r) {
            $uid = $r['id_usuario'];
            $ud  = $r['id_ud'];
            $nc  = $r['nro_calificacion'];

            if (!isset($est[$uid])) {
                $est[$uid] = [
                    'dni'               => $r['dni'],
                    'apellidos_nombres' => $r['apellidos_nombres'],
                    'notas'             => []
                ];
            }

            $nota = $this->objCalificacion->notaCalificacion($r['id_calif']);
            $est[$uid]['notas'][$ud][$nc] = ($nota === '') ? '' : $nota;
        }
        $estudiantes = array_values($est);
        // limpia índices

        /* 6️⃣  PDF */
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle('Detallado ' . $info['programa']);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/academico/reportes/pdf_calif_detallado.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Detallado.pdf', 'I');
    }



    public function pdfPrimerosPuestos()
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        /* ① Filtros desde el modal */
        [$id_programa, $id_semestre, $turno, $seccion] = [
            $_POST['programa'] ?? null,
            $_POST['semestre'] ?? null,
            $_POST['turno']    ?? null,
            $_POST['seccion']  ?? null
        ];
        $periodo_id = $_SESSION['sigi_periodo_actual_id'];
        $sede_id    = $_SESSION['sigi_sede_actual'];

        if (!$id_programa || !$id_semestre || !$turno || !$seccion) {
            $_SESSION['flash_error'] = 'Complete todos los filtros para el reporte.';
            header('Location: ' . BASE_URL . '/academico/reportes');
            exit;
        }

        /* ② Cabecera y listas */
        $info = $this->model->getCabeceraNomina(
            $id_programa,
            $id_semestre,
            $turno,
            $seccion,
            $periodo_id,
            $sede_id
        );
        $uds  = $this->model->getUnidadesDidacticas($id_programa, $id_semestre);

        /* ③ Estudiantes matriculados (1 fila por UD) */
        $rows = $this->model->getEstudiantesMatriculados(
            $id_programa,
            $id_semestre,
            $turno,
            $seccion,
            $periodo_id,
            $sede_id
        );

        /* ④ Caché de nros_calif por UD (para promedio final) */
        $nrosPorUd = [];
        foreach ($uds as $u) {
            $nrosPorUd[$u['id']] = $this->model->getNrosCalificacionPorUd($u['id']) ?: [];
        }

        /* ⑤ Agrupar → $map[idUsuario] */
        $map = [];
        foreach ($rows as $r) {
            $uid = $r['id_usuario'];
            $ud  = $r['id_ud'];

            if (!isset($map[$uid])) {
                $map[$uid] = [
                    'dni'               => $r['dni'],
                    'apellidos_nombres' => $r['apellidos_nombres'],
                    'puntaje_total'     => 0,
                    'puntaje_credito'   => 0,
                    'desap'             => 0,
                    'uds_matriculadas'  => []
                ];
            }
            // ── calcular nota final de ESTA UD para ESTE estudiante
            $nota = $this->objCalificacion->promedioFinalEstudiante(
                (int)$r['id_detalle_matricula'],
                $nrosPorUd[$ud]
            );
            // registrar
            $map[$uid]['uds_matriculadas'][$ud] = [
                'nota'    => ($nota === '' ? null : (int)$nota),
                'credito' => $this->buscarCreditoUd($uds, $ud)
            ];
        }

        /* ⑥ Calcular puntajes + categoría */
        $totalUdSemestre = count($uds);

        foreach ($map as $uid => &$e) {
            $matriculadoTodos = (count($e['uds_matriculadas']) === $totalUdSemestre);
            $tieneRecup       = false;
            $suma_creditos = 0;
            foreach ($e['uds_matriculadas'] as $udid => $data) {
                if ($data['nota'] !== null) {
                    $e['puntaje_total']   += $data['nota'];
                    $e['puntaje_credito'] += $data['nota'] * $data['credito'];
                    $suma_creditos += $data['credito'];
                    if ($data['nota'] < 13) {
                        $e['desap']++;
                        /* ¿tiene recuperación en esa DM? */
                        $tieneRecup = $tieneRecup ||
                            $this->model->tieneRecuperacion(
                                /* id_detalle_matricula: viene en $rows pero
                                         no lo guardamos aquí; el criterio para ranking
                                         sólo pide “alguna con recuperación” – lo consideramos
                                         true si existió nota <13 Y recuperacion==SI */
                                $this->buscarDetalleMatricula($rows, $uid, $udid)
                            );
                    }
                }
            }
            $e['promedio_ponderado'] = round(($e['puntaje_credito'] / $suma_creditos), 2);
            /* ── PRIORIDAD (1 → 4) */
            if ($matriculadoTodos && $e['desap'] === 0) {
                $e['prio'] = 1;
            } elseif ($matriculadoTodos && $tieneRecup) {
                $e['prio'] = 2;
            } elseif ($matriculadoTodos) {
                $e['prio'] = 3;
            } else {
                $e['prio'] = 4;
            }
        }
        unset($e);

        /* ⑦ Ordenar por prioridad y puntaje_credito DESC */
        usort($map, function ($a, $b) {
            return [$a['prio'], -$a['puntaje_credito']] <=> [$b['prio'], -$b['puntaje_credito']];
        });

        /* ⑧ Asignar ranking */
        $rank = 1;
        foreach ($map as &$e) {
            $e['ranking'] = $rank++;
        }
        unset($e);
        $estudiantes = $map;   // lista final ordenada

        /* ⑨ PDF */
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetTitle("Primeros Puestos – {$info['programa']}");
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        ob_start();
        include __DIR__ . '/../../views/academico/reportes/pdf_primeros_puestos.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Primeros_Puestos.pdf', 'I');
    }

    /* ─── helpers internos del controlador ─── */
    private function buscarCreditoUd(array $uds, int $udId): int
    {
        foreach ($uds as $u) if ($u['id'] == $udId) return (int)$u['creditos'];
        return 0;
    }
    private function buscarDetalleMatricula(array $rows, int $uid, int $udId): ?int
    {
        foreach ($rows as $r) {
            if ($r['id_usuario'] == $uid && $r['id_ud'] == $udId) {
                return (int)$r['id_detalle_matricula'];
            }
        }
        return null;
    }




   
    /* ---------------- VISTA LISTADO (botones “Ver reporte”) ------------------ */
    public function buscarMatriculas()
    {
        $uid       = $_SESSION['sigi_user_id'];
        $periodoId = $_SESSION['sigi_periodo_actual_id'];
        $sedeId    = $_SESSION['sigi_sede_actual'];

       
        [$prog, $plan, $sem, $turn, $sec] = [
            $_POST['programa'] ?? null,
            $_POST['plan'] ?? null,
            $_POST['semestre'] ?? null,
            $_POST['turno'] ?? null,
            $_POST['seccion'] ?? null
        ];

        $matriculas = $this->model->getMatriculasCoordinador(
            $uid,
            $periodoId,
            $sedeId,
            $prog,
            $plan,
            $sem,
            $turn,
            $sec
        );

        echo json_encode($matriculas);
        exit;
    }

    /* ---------------- REPORTE INDIVIDUAL EN PANTALLA ------------------------ */
    public function estudianteDetalle(int $id_matricula)
    {
        $califs  = $this->model->calificacionesVisibles($id_matricula);
        $proms   = $this->model->promediosVisibles($id_matricula);
        $asist   = $this->model->asistenciasEstudiante($id_matricula);

        /* organización de datos para la vista */
        $udOrder = [];  // [nombreUd] => idx
        $tablaCalif = []; // [ud][nro] => nota
        foreach ($califs as $c) {
            $ud = $c['ud'];
            if (!isset($udOrder[$ud])) $udOrder[$ud] = $c['id_ud'];
            if ($c['mostrar_calificacion']) {// si esta habilitado mostrar
                $tablaCalif[$ud][$c['nro']] = $c['nota'];
                $tablaCalif[$ud]['recuperacion'] = $c['recuperacion'];
            }
        }
        //var_dump($proms);

        /* asistencia re-mapeada */
        $tablaAsist = []; // [ud][semana] => 'P'|'F'
        foreach ($asist as $a) {
            $tablaAsist[$a['ud']][$a['semana']] = $a['asistencia'];
        }

        $this->view('academico/reportes/estudianteDetalle', [
            'udOrder' => $udOrder,
            'tablaCalif' => $tablaCalif,
            'proms' => $proms,
            'tablaAsist' => $tablaAsist,
            'module' => 'academico',
            'pageTitle' => 'Reporte Individual'
        ]);
    }
}
