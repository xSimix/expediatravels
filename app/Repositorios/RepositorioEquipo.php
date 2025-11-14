<?php

declare(strict_types=1);

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioEquipo
{
    public const CATEGORIA_ASESOR_VENTAS = 'asesor_ventas';
    public const CATEGORIA_GUIA = 'guia';
    public const CATEGORIA_OPERACIONES = 'operaciones';
    public const CATEGORIA_OTRO = 'otro';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerTodos(?string $categoria = null, bool $soloActivos = false): array
    {
        try {
            $pdo = Conexion::obtener();
            $consulta = 'SELECT id, nombre, cargo, telefono, correo, categoria, descripcion, prioridad, activo'
                . ' FROM equipo';
            $condiciones = [];
            $parametros = [];

            if ($categoria !== null && $categoria !== '') {
                $condiciones[] = 'categoria = :categoria';
                $parametros[':categoria'] = $categoria;
            }

            if ($soloActivos) {
                $condiciones[] = 'activo = 1';
            }

            if (!empty($condiciones)) {
                $consulta .= ' WHERE ' . implode(' AND ', $condiciones);
            }

            $consulta .= ' ORDER BY prioridad DESC, nombre ASC';

            $sentencia = $pdo->prepare($consulta);
            foreach ($parametros as $parametro => $valor) {
                if ($parametro === ':categoria') {
                    $sentencia->bindValue($parametro, $valor, PDO::PARAM_STR);
                    continue;
                }

                $sentencia->bindValue($parametro, $valor);
            }

            $sentencia->execute();
            $registros = $sentencia->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return array_map(fn (array $fila): array => $this->mapear($fila), $registros);
        } catch (PDOException $exception) {
            return $this->filtrarFallback($categoria, $soloActivos);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerActivosPorCategoria(string $categoria): array
    {
        return $this->obtenerTodos($categoria, true);
    }

    public function obtenerPorId(int $id): ?array
    {
        try {
            $pdo = Conexion::obtener();
            $sentencia = $pdo->prepare(
                'SELECT id, nombre, cargo, telefono, correo, categoria, descripcion, prioridad, activo'
                . ' FROM equipo WHERE id = :id LIMIT 1'
            );
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();

            $registro = $sentencia->fetch(PDO::FETCH_ASSOC);

            return $registro !== false ? $this->mapear($registro) : null;
        } catch (PDOException $exception) {
            foreach ($this->fallback() as $miembro) {
                if ((int) ($miembro['id'] ?? 0) === $id) {
                    return $this->mapear($miembro);
                }
            }

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
            $cargo = $datos['cargo'] ?? null;
            $telefono = $datos['telefono'] ?? null;
            $correo = $datos['correo'] ?? null;
            $categoria = $datos['categoria'] ?? self::CATEGORIA_OTRO;
            $descripcion = $datos['descripcion'] ?? null;
            $prioridad = (int) ($datos['prioridad'] ?? 0);
            $activo = (int) ($datos['activo'] ?? 1);
            $sentencia = $pdo->prepare(
                'INSERT INTO equipo (nombre, cargo, telefono, correo, categoria, descripcion, prioridad, activo)'
                . ' VALUES (:nombre, :cargo, :telefono, :correo, :categoria, :descripcion, :prioridad, :activo)'
            );
            $sentencia->bindValue(':nombre', (string) ($datos['nombre'] ?? ''), PDO::PARAM_STR);
            $sentencia->bindValue(':cargo', $cargo !== null ? (string) $cargo : null, $cargo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':telefono', $telefono !== null ? (string) $telefono : null, $telefono !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':correo', $correo !== null ? (string) $correo : null, $correo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':categoria', (string) $categoria, PDO::PARAM_STR);
            $sentencia->bindValue(':descripcion', $descripcion !== null ? (string) $descripcion : null, $descripcion !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':prioridad', $prioridad, PDO::PARAM_INT);
            $sentencia->bindValue(':activo', $activo, PDO::PARAM_INT);
            $sentencia->execute();

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
            $cargo = $datos['cargo'] ?? null;
            $telefono = $datos['telefono'] ?? null;
            $correo = $datos['correo'] ?? null;
            $categoria = $datos['categoria'] ?? self::CATEGORIA_OTRO;
            $descripcion = $datos['descripcion'] ?? null;
            $prioridad = (int) ($datos['prioridad'] ?? 0);
            $activo = (int) ($datos['activo'] ?? 1);
            $sentencia = $pdo->prepare(
                'UPDATE equipo'
                . ' SET nombre = :nombre, cargo = :cargo, telefono = :telefono, correo = :correo,'
                . ' categoria = :categoria, descripcion = :descripcion, prioridad = :prioridad, activo = :activo'
                . ' WHERE id = :id'
            );
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->bindValue(':nombre', (string) ($datos['nombre'] ?? ''), PDO::PARAM_STR);
            $sentencia->bindValue(':cargo', $cargo !== null ? (string) $cargo : null, $cargo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':telefono', $telefono !== null ? (string) $telefono : null, $telefono !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':correo', $correo !== null ? (string) $correo : null, $correo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':categoria', (string) $categoria, PDO::PARAM_STR);
            $sentencia->bindValue(':descripcion', $descripcion !== null ? (string) $descripcion : null, $descripcion !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $sentencia->bindValue(':prioridad', $prioridad, PDO::PARAM_INT);
            $sentencia->bindValue(':activo', $activo, PDO::PARAM_INT);

            return $sentencia->execute();
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function eliminar(int $id): bool
    {
        try {
            $pdo = Conexion::obtener();
            $sentencia = $pdo->prepare('DELETE FROM equipo WHERE id = :id');
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);

            return $sentencia->execute();
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
            'nombre' => (string) ($fila['nombre'] ?? ''),
            'cargo' => $fila['cargo'] !== null ? (string) $fila['cargo'] : null,
            'telefono' => $fila['telefono'] !== null ? (string) $fila['telefono'] : null,
            'correo' => $fila['correo'] !== null ? (string) $fila['correo'] : null,
            'categoria' => (string) ($fila['categoria'] ?? self::CATEGORIA_OTRO),
            'descripcion' => $fila['descripcion'] !== null ? (string) $fila['descripcion'] : null,
            'prioridad' => (int) ($fila['prioridad'] ?? 0),
            'activo' => (int) ($fila['activo'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallback(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'María López',
                'cargo' => 'Especialista en circuitos',
                'telefono' => '+51 987 654 321',
                'correo' => 'maria.lopez@expediatravels.pe',
                'categoria' => self::CATEGORIA_ASESOR_VENTAS,
                'descripcion' => 'Atiende consultas personalizadas para circuitos amazónicos.',
                'prioridad' => 10,
                'activo' => 1,
            ],
            [
                'id' => 2,
                'nombre' => 'Jorge Ramírez',
                'cargo' => 'Atención personalizada',
                'telefono' => '+51 945 123 456',
                'correo' => 'jorge.ramirez@expediatravels.pe',
                'categoria' => self::CATEGORIA_ASESOR_VENTAS,
                'descripcion' => 'Especialista en experiencias a medida para viajes familiares.',
                'prioridad' => 8,
                'activo' => 1,
            ],
            [
                'id' => 3,
                'nombre' => 'Lucía Quispe',
                'cargo' => 'Guía senior',
                'telefono' => '+51 912 345 678',
                'correo' => 'lucia.quispe@expediatravels.pe',
                'categoria' => self::CATEGORIA_GUIA,
                'descripcion' => 'Guía certificada para circuitos en la selva central.',
                'prioridad' => 5,
                'activo' => 1,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filtrarFallback(?string $categoria, bool $soloActivos): array
    {
        $miembros = $this->fallback();

        if ($categoria !== null && $categoria !== '') {
            $miembros = array_values(array_filter(
                $miembros,
                static fn (array $miembro): bool => ($miembro['categoria'] ?? null) === $categoria
            ));
        }

        if ($soloActivos) {
            $miembros = array_values(array_filter(
                $miembros,
                static fn (array $miembro): bool => (int) ($miembro['activo'] ?? 0) === 1
            ));
        }

        usort(
            $miembros,
            static function (array $a, array $b): int {
                $prioridadA = (int) ($a['prioridad'] ?? 0);
                $prioridadB = (int) ($b['prioridad'] ?? 0);

                if ($prioridadA === $prioridadB) {
                    return strcmp((string) ($a['nombre'] ?? ''), (string) ($b['nombre'] ?? ''));
                }

                return $prioridadB <=> $prioridadA;
            }
        );

        return array_map(fn (array $miembro): array => $this->mapear($miembro), $miembros);
    }
}
