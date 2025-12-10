<?php

namespace App\Models\Biblioteca;

use Core\Model;
use PDO;

class Libros extends Model
{
    public function buscarFavorito($id_usuario, $id_libro)
    {
        $stmt = self::$db->prepare("SELECT * FROM biblioteca_favoritos WHERE id_usuario=? AND id_libro=?");
        $stmt->execute([$id_usuario, $id_libro]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function registrarFavorito($id_usuario, $id_libro)
    {
        $stmt = self::$db->prepare("INSERT INTO biblioteca_favoritos (id_usuario, id_libro) VALUES (?, ?)");
        $stmt->execute([$id_usuario, $id_libro]);
        return self::$db->lastInsertId();
    }
    public function eliminarFavorito($id_usuario, $id_libro)
    {
        $stmt = self::$db->prepare("DELETE FROM biblioteca_favoritos WHERE id_usuario=? AND id_libro=?");
        $stmt->execute([$id_usuario, $id_libro]);
        return self::$db->lastInsertId();
    }



    public function buscarLectura($id_usuario, $id_libro)
    {
        $stmt = self::$db->prepare("SELECT * FROM biblioteca_lecturas WHERE id_usuario=? AND id_libro=? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$id_usuario, $id_libro]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function registrarLectura($id_usuario, $id_libro)
    {
        $stmt = self::$db->prepare("INSERT INTO biblioteca_lecturas (id_usuario, id_libro) VALUES (?, ?)");
        $stmt->execute([$id_usuario, $id_libro]);
        return self::$db->lastInsertId();
    }


    public function contarFavoritos(int $idUsuario): int
    {
        $sql = "SELECT COUNT(*) FROM biblioteca_favoritos WHERE id_usuario = :u";
        $st  = self::$db->prepare($sql);
        $st->execute([':u' => $idUsuario]);
        return (int)$st->fetchColumn();
    }

    public function listarFavoritos(int $idUsuario, int $offset, int $limit): array
    {
        $sql = "SELECT id_libro
            FROM biblioteca_favoritos
            WHERE id_usuario = :u
            ORDER BY fecha_registro DESC
            LIMIT :o, :l";
        $st = self::$db->prepare($sql);
        $st->bindValue(':u', $idUsuario, \PDO::PARAM_INT);
        $st->bindValue(':o', $offset,    \PDO::PARAM_INT);
        $st->bindValue(':l', $limit,     \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
