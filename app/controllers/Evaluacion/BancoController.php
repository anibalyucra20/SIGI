<?php

namespace App\Controllers\Evaluacion;

use Core\Controller;
use Core\Auth;
use App\Models\Evaluacion\Banco;
use Exception;

class BancoController extends Controller
{
    protected $bancoModel;

    public function __construct()
    {
        parent::__construct();

        // Solo administradores, directores, JUA o coordinadores pueden gestionar el banco de rúbricas
        if (!Auth::esAdminEvaluacion() && !Auth::esCoordinadorPEEvaluacion()) {
            $_SESSION['flash_error'] = "No tienes permisos para gestionar el banco de preguntas y criterios.";
            header('Location: ' . BASE_URL . '/evaluacion');
            exit;
        }

        $this->bancoModel = new Banco();
    }

    /**
     * Vista principal del banco de rúbricas (criterios y preguntas)
     */
    public function index()
    {
        $criterios = $this->bancoModel->obtenerTodosConPreguntas(false);

        $this->view('evaluacion/banco/index', [
            'pageTitle' => 'Banco de Rúbricas y Preguntas - Evaluación Docente',
            'module'    => 'evaluacion',
            'criterios' => $criterios
        ]);
    }

    /**
     * Guarda o actualiza un criterio en el banco
     */
    public function guardarCriterio()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/banco');
            exit;
        }

        $id = !empty($_POST['id_criterio']) ? (int)$_POST['id_criterio'] : null;
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : 1.00;
        $orden = !empty($_POST['orden']) ? (int)$_POST['orden'] : 1;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($nombre)) {
            $_SESSION['flash_error'] = "El nombre del criterio es obligatorio.";
            header('Location: ' . BASE_URL . '/evaluacion/banco');
            exit;
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'peso'        => $peso,
            'orden'       => $orden,
            'estado'      => $estado
        ];

        try {
            if ($id) {
                $this->bancoModel->actualizarCriterio($id, $data);
                $_SESSION['flash_success'] = "Criterio actualizado correctamente en el banco.";
            } else {
                $this->bancoModel->guardarCriterio($data);
                $_SESSION['flash_success'] = "Nuevo criterio registrado en el banco de rúbricas.";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error al procesar el criterio: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/evaluacion/banco');
        exit;
    }

    /**
     * Elimina un criterio del banco
     */
    public function eliminarCriterio($id)
    {
        $id = (int)$id;
        try {
            $this->bancoModel->eliminarCriterio($id);
            $_SESSION['flash_success'] = "Criterio y sus preguntas eliminados del banco de rúbricas.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error al eliminar el criterio: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/evaluacion/banco');
        exit;
    }

    /**
     * Guarda o actualiza una pregunta en el banco
     */
    public function guardarPregunta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/evaluacion/banco');
            exit;
        }

        $id = !empty($_POST['id_pregunta']) ? (int)$_POST['id_pregunta'] : null;
        $idCriterio = !empty($_POST['id_banco_criterio']) ? (int)$_POST['id_banco_criterio'] : null;
        $enunciado = trim($_POST['enunciado'] ?? '');
        $tipoRespuesta = $_POST['tipo_respuesta'] ?? 'ESCALA';
        $esObligatoria = isset($_POST['es_obligatoria']) ? (int)$_POST['es_obligatoria'] : 1;
        $orden = !empty($_POST['orden']) ? (int)$_POST['orden'] : 1;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($idCriterio) || empty($enunciado)) {
            $_SESSION['flash_error'] = "El criterio y el enunciado de la pregunta son obligatorios.";
            header('Location: ' . BASE_URL . '/evaluacion/banco');
            exit;
        }

        $data = [
            'id_banco_criterio' => $idCriterio,
            'enunciado'         => $enunciado,
            'tipo_respuesta'    => $tipoRespuesta,
            'es_obligatoria'    => $esObligatoria,
            'orden'             => $orden,
            'estado'            => $estado
        ];

        try {
            if ($id) {
                $this->bancoModel->actualizarPregunta($id, $data);
                $_SESSION['flash_success'] = "Pregunta actualizada en el banco.";
            } else {
                $this->bancoModel->guardarPregunta($data);
                $_SESSION['flash_success'] = "Pregunta agregada exitosamente al criterio.";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error al procesar la pregunta: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/evaluacion/banco');
        exit;
    }

    /**
     * Elimina una pregunta del banco
     */
    public function eliminarPregunta($id)
    {
        $id = (int)$id;
        try {
            $this->bancoModel->eliminarPregunta($id);
            $_SESSION['flash_success'] = "Pregunta eliminada del banco.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error al eliminar la pregunta: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/evaluacion/banco');
        exit;
    }

    /**
     * Retorna datos de un criterio en JSON para modal de edición
     */
    public function ajaxObtenerCriterio($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $criterio = $this->bancoModel->obtenerCriterio((int)$id);
        if (!$criterio) {
            echo json_encode(['ok' => false, 'error' => 'Criterio no encontrado']);
            exit;
        }
        echo json_encode(['ok' => true, 'data' => $criterio]);
        exit;
    }

    /**
     * Retorna datos de una pregunta en JSON para modal de edición
     */
    public function ajaxObtenerPregunta($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $pregunta = $this->bancoModel->obtenerPregunta((int)$id);
        if (!$pregunta) {
            echo json_encode(['ok' => false, 'error' => 'Pregunta no encontrada']);
            exit;
        }
        echo json_encode(['ok' => true, 'data' => $pregunta]);
        exit;
    }
}
