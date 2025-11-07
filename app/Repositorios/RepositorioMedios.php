<?php

declare(strict_types=1);

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioMedios
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->query('SELECT * FROM media_items ORDER BY creado_en DESC');
            $registros = $consulta !== false ? $consulta->fetchAll(PDO::FETCH_ASSOC) : [];

            return array_map(fn (array $fila): array => $this->mapear($fila), $registros);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function obtenerPorId(int $id): ?array
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare('SELECT * FROM media_items WHERE id = :id');
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $fila = $consulta->fetch(PDO::FETCH_ASSOC);

            return $fila !== false ? $this->mapear($fila) : null;
        } catch (PDOException $exception) {
            return null;
        }
    }

    public function buscarPorHash(string $hash): ?array
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare('SELECT * FROM media_items WHERE sha1_hash = :hash LIMIT 1');
            $consulta->bindValue(':hash', $hash, PDO::PARAM_STR);
            $consulta->execute();
            $fila = $consulta->fetch(PDO::FETCH_ASSOC);

            return $fila !== false ? $this->mapear($fila) : null;
        } catch (PDOException $exception) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): int
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare(
                'INSERT INTO media_items (titulo, descripcion, texto_alternativo, creditos, ruta, nombre_archivo, nombre_original, tipo_mime, extension, tamano_bytes, ancho, alto, sha1_hash)
                 VALUES (:titulo, :descripcion, :alt, :creditos, :ruta, :nombre_archivo, :nombre_original, :mime, :extension, :tamano, :ancho, :alto, :hash)'
            );
            $consulta->bindValue(':titulo', (string) ($datos['titulo'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':descripcion', $datos['descripcion'] ?? null, $datos['descripcion'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':alt', $datos['texto_alternativo'] ?? null, $datos['texto_alternativo'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':creditos', $datos['creditos'] ?? null, $datos['creditos'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':ruta', (string) ($datos['ruta'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':nombre_archivo', (string) ($datos['nombre_archivo'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':nombre_original', (string) ($datos['nombre_original'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':mime', (string) ($datos['tipo_mime'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':extension', $datos['extension'] ?? null, $datos['extension'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':tamano', (int) ($datos['tamano_bytes'] ?? 0), PDO::PARAM_INT);
            $consulta->bindValue(':ancho', $datos['ancho'] !== null ? (int) $datos['ancho'] : null, $datos['ancho'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $consulta->bindValue(':alto', $datos['alto'] !== null ? (int) $datos['alto'] : null, $datos['alto'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $consulta->bindValue(':hash', (string) ($datos['sha1_hash'] ?? ''), PDO::PARAM_STR);
            $consulta->execute();

            return (int) $pdo->lastInsertId();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): bool
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare(
                'UPDATE media_items
                 SET titulo = :titulo,
                     descripcion = :descripcion,
                     texto_alternativo = :alt,
                     creditos = :creditos
                 WHERE id = :id'
            );
            $consulta->bindValue(':titulo', (string) ($datos['titulo'] ?? ''), PDO::PARAM_STR);
            $consulta->bindValue(':descripcion', $datos['descripcion'] ?? null, $datos['descripcion'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':alt', $datos['texto_alternativo'] ?? null, $datos['texto_alternativo'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':creditos', $datos['creditos'] ?? null, $datos['creditos'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);

            return $consulta->execute();
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function eliminar(int $id): bool
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare('DELETE FROM media_items WHERE id = :id');
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);

            return $consulta->execute();
        } catch (PDOException $exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $fila
     * @return array<string, mixed>
     */
    private function mapear(array $fila): array
    {
        return [
            'id' => (int) ($fila['id'] ?? 0),
            'titulo' => (string) ($fila['titulo'] ?? ''),
            'descripcion' => $fila['descripcion'] ?? null,
            'texto_alternativo' => $fila['texto_alternativo'] ?? null,
            'creditos' => $fila['creditos'] ?? null,
            'ruta' => (string) ($fila['ruta'] ?? ''),
            'nombre_archivo' => (string) ($fila['nombre_archivo'] ?? ''),
            'nombre_original' => (string) ($fila['nombre_original'] ?? ''),
            'tipo_mime' => (string) ($fila['tipo_mime'] ?? ''),
            'extension' => $fila['extension'] ?? null,
            'tamano_bytes' => (int) ($fila['tamano_bytes'] ?? 0),
            'ancho' => isset($fila['ancho']) ? ($fila['ancho'] !== null ? (int) $fila['ancho'] : null) : null,
            'alto' => isset($fila['alto']) ? ($fila['alto'] !== null ? (int) $fila['alto'] : null) : null,
            'sha1_hash' => (string) ($fila['sha1_hash'] ?? ''),
            'creado_en' => $fila['creado_en'] ?? null,
            'actualizado_en' => $fila['actualizado_en'] ?? null,
        ];
    }
}
