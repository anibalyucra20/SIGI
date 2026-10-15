<?php

namespace App\Models\Efsrt;

use Core\Model;
use PDO;

class Documento extends Model
{
    // Tipos de documentos permitidos
    const TIPO_DNI = 'DNI';
    const TIPO_FICHA_ACEPTACION = 'FICHA_ACEPTACION';
    const TIPO_FICHA_EVALUACION = 'FICHA_EVALUACION';
    const TIPO_CONSTANCIA_EMPRESA = 'CONSTANCIA_EMPRESA';
    const TIPO_FICHA_SUPERVISION = 'FICHA_SUPERVISION';
    const TIPO_INFORME_FINAL_SUPERVISOR = 'INFORME_FINAL_SUPERVISOR';

    /**
     * Obtiene todos los documentos asociados a una práctica
     */
    public function getByPractica($id_practica, $solo_activos = true)
    {
        $sql = "SELECT d.*, 
                    u_sub.apellidos_nombres AS subido_por_nombre,
                    u_val.apellidos_nombres AS validado_por_nombre
                FROM efsrt_documentos d
                INNER JOIN sigi_usuarios u_sub ON d.subido_por = u_sub.id
                LEFT JOIN sigi_usuarios u_val ON d.validado_por = u_val.id
                WHERE d.id_practica = ? ";

        if ($solo_activos) {
            $sql .= " AND d.estado_archivo != 'ELIMINADO_PURGADO' ";
        }

        $sql .= " ORDER BY d.id ASC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([(int)$id_practica]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['subido_por_nombre'] = \Core\Auth::formatearNombreUsuario($r['subido_por_nombre'] ?? '');
            if (!empty($r['validado_por_nombre'])) {
                $r['validado_por_nombre'] = \Core\Auth::formatearNombreUsuario($r['validado_por_nombre']);
            }
        }
        return $rows;
    }

    /**
     * Obtiene un documento específico por tipo en una práctica
     */
    public function getByTipo($id_practica, $tipo_documento)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_documentos 
                                        WHERE id_practica = ? AND tipo_documento = ? 
                                        AND estado_archivo != 'ELIMINADO_PURGADO' 
                                        ORDER BY id DESC LIMIT 1");
        $stmt->execute([(int)$id_practica, $tipo_documento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un documento por su ID
     */
    public function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM efsrt_documentos WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra o reemplaza un documento subido
     */
    public function registrarOActualizar($data)
    {
        $db = self::getDB();
        $existente = $this->getByTipo($data['id_practica'], $data['tipo_documento']);

        $estadoVal = $data['estado_validacion'] ?? 'PENDIENTE';
        $validadoPor = ($estadoVal === 'APROBADO' && !empty($data['validado_por'])) ? (int)$data['validado_por'] : null;
        $fechaVal = ($estadoVal === 'APROBADO') ? date('Y-m-d H:i:s') : null;

        if ($existente) {
            // Reemplazar archivo existente
            $sql = "UPDATE efsrt_documentos SET 
                        subido_por = ?,
                        nombre_archivo_original = ?,
                        nombre_archivo_guardado = ?,
                        ruta_archivo = ?,
                        tamano_bytes = ?,
                        extension = ?,
                        hash_sha256 = ?,
                        estado_validacion = ?,
                        observaciones = NULL,
                        validado_por = ?,
                        fecha_validacion = ?,
                        estado_archivo = 'ACTIVO',
                        created_at = NOW()
                    WHERE id = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                (int)$data['subido_por'],
                $data['nombre_archivo_original'],
                $data['nombre_archivo_guardado'],
                $data['ruta_archivo'],
                (int)$data['tamano_bytes'],
                $data['extension'],
                !empty($data['hash_sha256']) ? $data['hash_sha256'] : null,
                $estadoVal,
                $validadoPor,
                $fechaVal,
                (int)$existente['id']
            ]);

            return $existente['id'];
        } else {
            // Insertar nuevo documento
            $sql = "INSERT INTO efsrt_documentos (
                        id_practica, tipo_documento, subido_por,
                        nombre_archivo_original, nombre_archivo_guardado, ruta_archivo,
                        tamano_bytes, extension, hash_sha256,
                        estado_validacion, validado_por, fecha_validacion, estado_archivo, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO', NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                (int)$data['id_practica'],
                $data['tipo_documento'],
                (int)$data['subido_por'],
                $data['nombre_archivo_original'],
                $data['nombre_archivo_guardado'],
                $data['ruta_archivo'],
                (int)$data['tamano_bytes'],
                $data['extension'],
                !empty($data['hash_sha256']) ? $data['hash_sha256'] : null,
                $estadoVal,
                $validadoPor,
                $fechaVal
            ]);

            return $db->lastInsertId();
        }
    }

    /**
     * Actualizar validación de un documento (APROBADO u OBSERVADO)
     */
    public function validar($id_documento, $estado_validacion, $observaciones, $id_usuario_validador)
    {
        $db = self::getDB();
        $sql = "UPDATE efsrt_documentos SET 
                    estado_validacion = ?,
                    observaciones = ?,
                    validado_por = ?,
                    fecha_validacion = NOW()
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $estado_validacion,
            trim($observaciones),
            (int)$id_usuario_validador,
            (int)$id_documento
        ]);
    }

    /**
     * Verifica si todos los documentos obligatorios han sido subidos y aprobados
     */
    public function estadoExpedienteCompleto($id_practica)
    {
        $docs = $this->getByPractica($id_practica);
        $documentosMap = [];
        foreach ($docs as $d) {
            $documentosMap[$d['tipo_documento']] = $d;
        }

        $requeridos = [
            self::TIPO_DNI => 'Copia de DNI',
            self::TIPO_FICHA_ACEPTACION => 'Ficha de Aceptación',
            self::TIPO_FICHA_EVALUACION => 'Ficha de Evaluación',
            self::TIPO_CONSTANCIA_EMPRESA => 'Constancia de Empresa',
            self::TIPO_FICHA_SUPERVISION => 'Ficha de Supervisión'
        ];

        $faltantes = [];
        $observados = [];
        $pendientes = [];
        $aprobados = [];

        foreach ($requeridos as $tipo => $nombre) {
            if (!isset($documentosMap[$tipo])) {
                $faltantes[$tipo] = $nombre;
            } else {
                $st = $documentosMap[$tipo]['estado_validacion'];
                if ($st === 'OBSERVADO') {
                    $observados[$tipo] = $nombre;
                } elseif ($st === 'PENDIENTE') {
                    $pendientes[$tipo] = $nombre;
                } elseif ($st === 'APROBADO') {
                    $aprobados[$tipo] = $nombre;
                }
            }
        }

        $todosAprobados = (count($aprobados) === count($requeridos));

        return [
            'todos_aprobados' => $todosAprobados,
            'requeridos' => $requeridos,
            'faltantes' => $faltantes,
            'observados' => $observados,
            'pendientes' => $pendientes,
            'aprobados' => $aprobados,
            'documentos' => $documentosMap
        ];
    }

    /**
     * Obtiene archivos de más de N años para respaldo y purga
     */
    public function obtenerArchivosAntiguosParaBackup($anios = 5)
    {
        $sql = "SELECT d.*, 
                    p.id_estudiante,
                    u.dni AS estudiante_dni,
                    u.apellidos_nombres AS estudiante_nombre,
                    prog.codigo AS programa_codigo
                FROM efsrt_documentos d
                INNER JOIN efsrt_practicas p ON d.id_practica = p.id
                INNER JOIN sigi_usuarios u ON p.id_estudiante = u.id
                INNER JOIN sigi_programa_estudios prog ON p.id_programa_estudios = prog.id
                WHERE d.created_at < DATE_SUB(NOW(), INTERVAL ? YEAR)
                  AND d.estado_archivo = 'ACTIVO'
                ORDER BY d.created_at ASC";

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([(int)$anios]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['estudiante_nombre'] = \Core\Auth::formatearNombreUsuario($r['estudiante_nombre'] ?? '');
        }
        return $rows;
    }

    /**
     * Marca documentos como purgados tras empaquetarlos en zip
     */
    public function marcarArchivosPurgados($array_ids)
    {
        if (empty($array_ids)) return 0;
        $db = self::getDB();
        $placeholders = implode(',', array_fill(0, count($array_ids), '?'));
        $sql = "UPDATE efsrt_documentos SET estado_archivo = 'ELIMINADO_PURGADO' WHERE id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($array_ids);
        return $stmt->rowCount();
    }
}
