<?php

declare(strict_types=1);

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioServicios
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->query('SELECT id, nombre, icono, tipo, descripcion, activo, creado_en, actualizado_en FROM servicios_catalogo ORDER BY nombre');
            $filas = $consulta !== false ? $consulta->fetchAll(PDO::FETCH_ASSOC) : [];

            return array_map(fn (array $fila): array => $this->mapear($fila), $filas);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function crear(string $nombre, string $tipo, ?string $descripcion, string $icono, bool $activo = true): int
    {
        try {
            $pdo = Conexion::obtener();
            $tipoNormalizado = $tipo === 'excluido' ? 'excluido' : 'incluido';
            $consulta = $pdo->prepare(
                'INSERT INTO servicios_catalogo (nombre, icono, tipo, descripcion, activo)
                 VALUES (:nombre, :icono, :tipo, :descripcion, :activo)'
            );
            $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $consulta->bindValue(':icono', $icono !== '' ? $icono : null, $icono !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':tipo', $tipoNormalizado, PDO::PARAM_STR);
            $consulta->bindValue(':descripcion', $descripcion !== null && $descripcion !== '' ? $descripcion : null, ($descripcion !== null && $descripcion !== '') ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $consulta->bindValue(':activo', $activo ? 1 : 0, PDO::PARAM_INT);
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
                'UPDATE servicios_catalogo
                 SET nombre = :nombre,
                     icono = :icono,
                     tipo = :tipo,
                     descripcion = :descripcion,
                     activo = :activo
                 WHERE id = :id'
            );
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':nombre', (string) ($datos['nombre'] ?? ''), PDO::PARAM_STR);
            $icono = trim((string) ($datos['icono'] ?? ''));
            $consulta->bindValue(':icono', $icono !== '' ? $icono : null, $icono !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $tipo = ($datos['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido';
            $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $descripcion = trim((string) ($datos['descripcion'] ?? ''));
            $consulta->bindValue(':descripcion', $descripcion !== '' ? $descripcion : null, $descripcion !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $activo = !empty($datos['activo']);
            $consulta->bindValue(':activo', $activo ? 1 : 0, PDO::PARAM_INT);

            return $consulta->execute();
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function eliminar(int $id): bool
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = $pdo->prepare('DELETE FROM servicios_catalogo WHERE id = :id');
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
            'nombre' => trim((string) ($fila['nombre'] ?? '')),
            'icono' => trim((string) ($fila['icono'] ?? '')),
            'tipo' => ($fila['tipo'] ?? '') === 'excluido' ? 'excluido' : 'incluido',
            'descripcion' => isset($fila['descripcion']) && $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
            'activo' => (bool) ($fila['activo'] ?? false),
            'creado_en' => $fila['creado_en'] ?? null,
            'actualizado_en' => $fila['actualizado_en'] ?? null,
        ];
    }
}
