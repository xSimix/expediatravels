<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;
use Throwable;

class RepositorioResenasCircuitos
{
    private bool $tableEnsured = false;

    private function ensureTable(): void
    {
        if ($this->tableEnsured) {
            return;
        }

        try {
            $pdo = Conexion::obtener();
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS resenas_circuitos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    circuito_slug VARCHAR(150) NOT NULL,
                    circuito_titulo VARCHAR(180) NOT NULL,
                    nombre VARCHAR(150) NOT NULL,
                    correo VARCHAR(150) DEFAULT NULL,
                    rating TINYINT NOT NULL,
                    comentario TEXT NOT NULL,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_resenas_circuitos_slug (circuito_slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            $this->tableEnsured = true;
        } catch (Throwable $exception) {
            $this->tableEnsured = false;
        }
    }

    public function crear(array $datos): int
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'INSERT INTO resenas_circuitos (
                    circuito_slug,
                    circuito_titulo,
                    nombre,
                    correo,
                    rating,
                    comentario
                ) VALUES (:slug, :titulo, :nombre, :correo, :rating, :comentario)'
            );

            $statement->execute([
                ':slug' => substr(trim((string) ($datos['slug'] ?? '')), 0, 150),
                ':titulo' => substr(trim((string) ($datos['titulo'] ?? 'Circuito')), 0, 180),
                ':nombre' => substr(trim((string) ($datos['nombre'] ?? '')), 0, 150),
                ':correo' => $this->normalizarCorreo($datos['correo'] ?? null),
                ':rating' => $this->normalizarEntero($datos['rating'] ?? 5),
                ':comentario' => $this->normalizarComentario($datos['comentario'] ?? ''),
            ]);

            return (int) $pdo->lastInsertId();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function obtenerPorCircuito(string $slug): array
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT id, circuito_slug, circuito_titulo, nombre, correo, rating, comentario, creado_en
                 FROM resenas_circuitos
                 WHERE circuito_slug = :slug
                 ORDER BY creado_en DESC'
            );
            $statement->execute([':slug' => substr(trim($slug), 0, 150)]);

            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return [
                    'reviews' => [],
                    'average' => null,
                    'count' => 0,
                ];
            }

            $reviews = array_map([$this, 'hidratarResena'], $rows);
            $promedio = array_sum(array_column($reviews, 'rating')) / count($reviews);

            return [
                'reviews' => $reviews,
                'average' => round($promedio, 1),
                'count' => count($reviews),
            ];
        } catch (PDOException $exception) {
            return [
                'reviews' => [],
                'average' => null,
                'count' => 0,
            ];
        }
    }

    public function obtenerResumenGlobal(int $limit = 6): array
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT circuito_slug, circuito_titulo, AVG(rating) AS promedio, COUNT(*) AS total
                 FROM resenas_circuitos
                 GROUP BY circuito_slug, circuito_titulo
                 ORDER BY total DESC, promedio DESC
                 LIMIT :limit'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();

            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return [];
            }

            return array_map(static function (array $row): array {
                return [
                    'slug' => (string) ($row['circuito_slug'] ?? ''),
                    'titulo' => (string) ($row['circuito_titulo'] ?? ''),
                    'promedio' => isset($row['promedio']) ? round((float) $row['promedio'], 1) : null,
                    'total' => (int) ($row['total'] ?? 0),
                ];
            }, $rows);
        } catch (PDOException $exception) {
            return [];
        }
    }

    private function hidratarResena(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['circuito_slug'] ?? ''),
            'titulo' => (string) ($row['circuito_titulo'] ?? ''),
            'nombre' => (string) ($row['nombre'] ?? ''),
            'correo' => $row['correo'] ?? null,
            'rating' => (int) ($row['rating'] ?? 5),
            'comentario' => (string) ($row['comentario'] ?? ''),
            'creado_en' => $row['creado_en'] ?? null,
        ];
    }

    private function normalizarCorreo($correo): ?string
    {
        if ($correo === null) {
            return null;
        }

        $correo = strtolower(trim((string) $correo));
        if ($correo === '') {
            return null;
        }

        return substr($correo, 0, 150);
    }

    private function normalizarEntero($valor): int
    {
        $numero = is_numeric($valor) ? (int) $valor : 5;
        if ($numero < 1) {
            return 1;
        }
        if ($numero > 5) {
            return 5;
        }

        return $numero;
    }

    private function normalizarComentario($comentario): string
    {
        $comentario = trim((string) $comentario);
        if ($comentario === '') {
            return 'Sin comentarios';
        }

        return substr($comentario, 0, 2000);
    }
}
