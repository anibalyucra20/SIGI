<?php

namespace App\Models\Academico;

use Core\Model;
use PDO;

class Rubrica extends Model
{
    protected $table = 'acad_rubricas';

    public function find($id_rubrica){
        $st = self::$db->prepare("SELECT * FROM {$this->table} WHERE id=? AND estado=1");
        $st->execute([$id_rubrica]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    /**
     * Obtiene las rúbricas locales del docente para DataTables
     */
    public function getPaginatedPorDocente(int $usuario_id, string $search, int $length, int $start, int $orderCol, string $orderDir): array
    {
        $orderDir = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';
        $cols = [
            0 => 'r.id',
            1 => 'r.nombre',
            2 => 'ud.nombre',
            3 => 'r.master_rubrica_id'
        ];
        $orderBy = $cols[$orderCol] ?? 'r.id';

        $where = 'WHERE r.usuario_id = :uid AND r.estado = 1';
        $params = [':uid' => $usuario_id];

        if ($search !== '') {
            $where .= " AND (r.nombre LIKE :q OR ud.nombre LIKE :q)";
            $params[':q'] = "%{$search}%";
        }

        $sql = "SELECT r.id, r.nombre, r.master_rubrica_id, r.unidad_didactica_id, ud.nombre as unidad_didactica_nombre, r.created_at
                  FROM {$this->table} r
                  LEFT JOIN sigi_unidad_didactica ud ON r.unidad_didactica_id = ud.id
                  $where
                 ORDER BY $orderBy $orderDir
                 LIMIT :limit OFFSET :offset";

        $st = self::$db->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limit', $length, PDO::PARAM_INT);
        $st->bindValue(':offset', $start, PDO::PARAM_INT);
        $st->execute();
        $data = $st->fetchAll(PDO::FETCH_ASSOC);

        $stTotal = self::$db->prepare("SELECT COUNT(*) FROM {$this->table} r WHERE r.usuario_id = :uid AND r.estado = 1");
        $stTotal->execute([':uid' => $usuario_id]);
        $total = (int) $stTotal->fetchColumn();

        $stFiltered = self::$db->prepare("SELECT COUNT(*) FROM {$this->table} r LEFT JOIN sigi_unidad_didactica ud ON r.unidad_didactica_id = ud.id $where");
        foreach ($params as $k => $v) {
            $stFiltered->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stFiltered->execute();
        $filtered = (int) $stFiltered->fetchColumn();

        return ['data' => $data, 'total' => $total, 'filtered' => $filtered];
    }

    public function findPropia(int $id, int $usuario_id): ?array
    {
        $st = self::$db->prepare("SELECT * FROM {$this->table} WHERE id=? AND usuario_id=? AND estado=1");
        $st->execute([$id, $usuario_id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function guardar(array $d): bool
    {
        $params = [
            ':usuario_id'          => (int)$d['usuario_id'],
            ':unidad_didactica_id' => !empty($d['unidad_didactica_id']) ? (int)$d['unidad_didactica_id'] : null,
            ':nombre'              => trim($d['nombre']),
            ':contenido_json'      => $d['contenido_json']
        ];

        if (!empty($d['id'])) {
            $params[':id'] = (int)$d['id'];
            $sql = "UPDATE {$this->table}
                       SET unidad_didactica_id = :unidad_didactica_id, 
                           nombre = :nombre, 
                           contenido_json = :contenido_json, 
                           updated_at = NOW()
                     WHERE id = :id AND usuario_id = :usuario_id";
        } else {
            $params[':master_id'] = !empty($d['master_rubrica_id']) ? (int)$d['master_rubrica_id'] : null;
            $sql = "INSERT INTO {$this->table}
                       (master_rubrica_id, usuario_id, unidad_didactica_id, nombre, contenido_json, estado, created_at, updated_at)
                    VALUES (:master_id, :usuario_id, :unidad_didactica_id, :nombre, :contenido_json, 1, NOW(), NOW())";
        }

        $st = self::$db->prepare($sql);
        return $st->execute($params);
    }

    public function eliminarPropia(int $id, int $usuario_id): bool
    {
        $st = self::$db->prepare("UPDATE {$this->table} SET estado = 0, updated_at = NOW() WHERE id = ? AND usuario_id = ?");
        return $st->execute([$id, $usuario_id]);
    }

    /**
     * Obtiene las rúbricas elegibles para una evaluación específica.
     * Filtra por el docente en sesión y rúbricas globales (sin UD) o vinculadas a la UD actual.
     */
    public function getRubricasDisponiblesPorUD(int $usuario_id, int $id_unidad_didactica): array
    {
        $sql = "SELECT id, nombre, contenido_json 
                  FROM {$this->table} 
                 WHERE usuario_id = :uid 
                   AND estado = 1 
                   AND (unidad_didactica_id IS NULL OR unidad_didactica_id = :id_ud)
                 ORDER BY nombre ASC";

        $st = self::$db->prepare($sql);
        $st->execute([
            ':uid' => $usuario_id,
            ':id_ud' => $id_unidad_didactica
        ]);

        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
