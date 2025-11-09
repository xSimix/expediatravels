<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;
use Throwable;

class RepositorioReservasCircuitos
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
                'CREATE TABLE IF NOT EXISTS reservas_circuitos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    circuito_slug VARCHAR(150) NOT NULL,
                    circuito_titulo VARCHAR(180) NOT NULL,
                    nombre VARCHAR(150) NOT NULL,
                    correo VARCHAR(150) NOT NULL,
                    telefono VARCHAR(80) DEFAULT NULL,
                    fecha_salida DATE DEFAULT NULL,
                    cantidad_personas INT DEFAULT 1,
                    mensaje TEXT DEFAULT NULL,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_reservas_circuitos_slug (circuito_slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            $this->tableEnsured = true;
        } catch (Throwable $exception) {
            // Si no es posible crear la tabla, se ignora para permitir datos de respaldo.
            $this->tableEnsured = false;
        }
    }

    public function crear(array $datos): int
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'INSERT INTO reservas_circuitos (
                    circuito_slug,
                    circuito_titulo,
                    nombre,
                    correo,
                    telefono,
                    fecha_salida,
                    cantidad_personas,
                    mensaje
                ) VALUES (:slug, :titulo, :nombre, :correo, :telefono, :fecha, :personas, :mensaje)'
            );

            $statement->execute([
                ':slug' => substr(trim((string) ($datos['slug'] ?? '')), 0, 150),
                ':titulo' => substr(trim((string) ($datos['titulo'] ?? 'Circuito')), 0, 180),
                ':nombre' => substr(trim((string) ($datos['nombre'] ?? '')), 0, 150),
                ':correo' => substr(trim((string) ($datos['correo'] ?? '')), 0, 150),
                ':telefono' => $this->normalizarTelefono($datos['telefono'] ?? null),
                ':fecha' => $this->normalizarFecha($datos['fecha_salida'] ?? null),
                ':personas' => $this->normalizarEntero($datos['cantidad_personas'] ?? null, 1),
                ':mensaje' => $this->normalizarMensaje($datos['mensaje'] ?? null),
            ]);

            return (int) $pdo->lastInsertId();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function obtenerPorSlug(string $slug): array
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT id, circuito_slug, circuito_titulo, nombre, correo, telefono, fecha_salida, cantidad_personas, mensaje, creado_en
                 FROM reservas_circuitos
                 WHERE circuito_slug = :slug
                 ORDER BY creado_en DESC'
            );
            $statement->execute([':slug' => substr(trim($slug), 0, 150)]);

            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return [];
            }

            return array_map([$this, 'hidratarReserva'], $rows);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function obtenerTodas(): array
    {
        $this->ensureTable();

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->query(
                'SELECT id, circuito_slug, circuito_titulo, nombre, correo, telefono, fecha_salida, cantidad_personas, mensaje, creado_en
                 FROM reservas_circuitos
                 ORDER BY creado_en DESC'
            );

            $rows = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
            if (!$rows) {
                return [];
            }

            return array_map([$this, 'hidratarReserva'], $rows);
        } catch (PDOException $exception) {
            return [];
        }
    }

    private function hidratarReserva(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['circuito_slug'] ?? ''),
            'titulo' => (string) ($row['circuito_titulo'] ?? ''),
            'nombre' => (string) ($row['nombre'] ?? ''),
            'correo' => (string) ($row['correo'] ?? ''),
            'telefono' => $row['telefono'] ?? null,
            'fecha_salida' => $row['fecha_salida'] ?? null,
            'cantidad_personas' => (int) ($row['cantidad_personas'] ?? 1),
            'mensaje' => $row['mensaje'] ?? null,
            'creado_en' => $row['creado_en'] ?? null,
        ];
    }

    private function normalizarTelefono($telefono): ?string
    {
        if ($telefono === null) {
            return null;
        }

        $telefono = trim((string) $telefono);
        if ($telefono === '') {
            return null;
        }

        return substr($telefono, 0, 80);
    }

    private function normalizarFecha($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }

        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return null;
        }

        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizarEntero($valor, int $default): int
    {
        if (is_numeric($valor)) {
            $numero = (int) $valor;
            return $numero > 0 ? $numero : $default;
        }

        return $default;
    }

    private function normalizarMensaje($mensaje): ?string
    {
        if ($mensaje === null) {
            return null;
        }

        $mensaje = trim((string) $mensaje);
        if ($mensaje === '') {
            return null;
        }

        return substr($mensaje, 0, 2000);
    }
}
