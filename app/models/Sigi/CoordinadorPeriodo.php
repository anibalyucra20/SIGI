<?php

namespace App\Models\Sigi;

use Core\Model;
use PDO;

class CoordinadorPeriodo extends Model
{
    protected $table = 'sigi_competencias';

    public function listarPorPeriodoYSede($id_periodo, $id_sede)
    {
        $sql = "SELECT 
                c.id, 
                u.apellidos_nombres AS coordinador, 
                pe.nombre AS programa, 
                p.nombre AS periodo
            FROM sigi_coordinador_pe_periodo c
            INNER JOIN sigi_usuarios u ON c.id_usuario = u.id
            INNER JOIN sigi_programa_estudios pe ON c.id_programa_estudio = pe.id
            INNER JOIN sigi_periodo_academico p ON c.id_periodo = p.id
            WHERE c.id_periodo = ? AND c.id_sede = ?
            ORDER BY u.apellidos_nombres ASC";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_periodo, $id_sede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeRegistro($data)
    {
        $sql = "SELECT COUNT(*) FROM sigi_coordinador_pe_periodo 
            WHERE id_usuario = ? AND id_programa_estudio = ? 
              AND id_periodo = ? AND id_sede = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_usuario'],
            $data['id_programa_estudio'],
            $data['id_periodo'],
            $data['id_sede']
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function registrar($data)
    {
        $sql = "INSERT INTO sigi_coordinador_pe_periodo (id_usuario, id_programa_estudio, id_periodo, id_sede)
            VALUES (?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            $data['id_usuario'],
            $data['id_programa_estudio'],
            $data['id_periodo'],
            $data['id_sede']
        ]);
    }
    public function eliminar($id)
    {
        $sql = "DELETE FROM sigi_coordinador_pe_periodo WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getProgramasAsignados($id_usuario, $id_periodo, $id_sede)
    {
        $sql = "SELECT pe.id, pe.nombre 
            FROM sigi_coordinador_pe_periodo cp
            INNER JOIN sigi_programa_estudios pe ON cp.id_programa_estudio = pe.id
            WHERE cp.id_usuario = ? AND cp.id_periodo = ? AND cp.id_sede = ?
            ORDER BY pe.nombre";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id_usuario, $id_periodo, $id_sede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
