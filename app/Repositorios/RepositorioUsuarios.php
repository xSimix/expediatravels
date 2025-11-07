<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use DateTimeInterface;
use PDO;

class RepositorioUsuarios
{
    private function baseSelect(): string
    {
        return <<<SQL
SELECT u.*, fp.ruta AS foto_perfil, fc.ruta AS foto_portada
FROM usuarios u
LEFT JOIN usuario_fotos_perfil fp ON fp.usuario_id = u.id AND fp.es_actual = 1
LEFT JOIN usuario_fotos_portada fc ON fc.usuario_id = u.id AND fc.es_actual = 1
SQL;
    }

    public function findByEmail(string $email): ?array
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare($this->baseSelect() . ' WHERE u.correo = :correo LIMIT 1');
        $statement->execute([':correo' => mb_strtolower($email)]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public function all(): array
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->query($this->baseSelect() . ' ORDER BY u.creado_en DESC, u.id DESC');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $payload): int
    {
        $pdo = Conexion::obtener();

        $statement = $pdo->prepare(
            'INSERT INTO usuarios (nombre, apellidos, celular, correo, contrasena_hash, verificacion_pin, pin_expira_en, rol) '
            . 'VALUES (:nombre, :apellidos, :celular, :correo, :contrasena_hash, :verificacion_pin, :pin_expira_en, :rol)'
        );

        $statement->execute([
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'],
            ':correo' => mb_strtolower($payload['correo']),
            ':contrasena_hash' => $payload['contrasena_hash'],
            ':verificacion_pin' => $payload['verificacion_pin'],
            ':pin_expira_en' => $payload['pin_expira_en'],
            ':rol' => $payload['rol'] ?? 'suscriptor',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function updatePendingUser(int $userId, array $payload): void
    {
        $pdo = Conexion::obtener();
        $query =
            'UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, celular = :celular, contrasena_hash = :contrasena_hash, '
            . 'verificacion_pin = :verificacion_pin, pin_expira_en = :pin_expira_en';

        if (array_key_exists('rol', $payload)) {
            $query .= ', rol = :rol';
        }

        $query .= ' WHERE id = :id';

        $statement = $pdo->prepare($query);

        $parameters = [
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'],
            ':contrasena_hash' => $payload['contrasena_hash'],
            ':verificacion_pin' => $payload['verificacion_pin'],
            ':pin_expira_en' => $payload['pin_expira_en'],
            ':id' => $userId,
        ];

        if (array_key_exists('rol', $payload)) {
            $parameters[':rol'] = $payload['rol'];
        }

        $statement->execute($parameters);
    }

    public function updatePin(int $userId, string $pin, DateTimeInterface $expiresAt): void
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'UPDATE usuarios SET verificacion_pin = :pin, pin_expira_en = :expira_en WHERE id = :id'
        );

        $statement->execute([
            ':pin' => $pin,
            ':expira_en' => $expiresAt->format('Y-m-d H:i:s'),
            ':id' => $userId,
        ]);
    }

    public function markVerified(int $userId): void
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'UPDATE usuarios SET verificado_en = NOW(), verificacion_pin = NULL, pin_expira_en = NULL WHERE id = :id'
        );
        $statement->execute([':id' => $userId]);
    }

    public function updateRememberToken(int $userId, ?string $token, ?DateTimeInterface $expiresAt): void
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            'UPDATE usuarios SET remember_token = :token, remember_token_expira_en = :expira_en WHERE id = :id'
        );

        $statement->execute([
            ':token' => $token,
            ':expira_en' => $expiresAt?->format('Y-m-d H:i:s'),
            ':id' => $userId,
        ]);
    }

    public function findByRememberToken(int $userId, string $tokenHash): ?array
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare(
            $this->baseSelect() .
            ' WHERE u.id = :id AND u.remember_token = :token AND u.remember_token_expira_en > NOW() LIMIT 1'
        );

        $statement->execute([
            ':id' => $userId,
            ':token' => $tokenHash,
        ]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public function findById(int $userId): ?array
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare($this->baseSelect() . ' WHERE u.id = :id LIMIT 1');
        $statement->execute([':id' => $userId]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public function updateProfile(int $userId, array $payload): void
    {
        $pdo = Conexion::obtener();

        $query = 'UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, celular = :celular, correo = :correo';
        $params = [
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'],
            ':correo' => mb_strtolower($payload['correo']),
            ':id' => $userId,
        ];

        if (array_key_exists('contrasena_hash', $payload)) {
            $query .= ', contrasena_hash = :contrasena_hash';
            $params[':contrasena_hash'] = $payload['contrasena_hash'];
        }

        $query .= ' WHERE id = :id';

        $statement = $pdo->prepare($query);
        $statement->execute($params);
    }

    public function updateAdmin(int $userId, array $payload): void
    {
        $pdo = Conexion::obtener();

        $query = 'UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, celular = :celular, correo = :correo, rol = :rol';
        $params = [
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'] ?? null,
            ':correo' => mb_strtolower($payload['correo']),
            ':rol' => $payload['rol'],
            ':id' => $userId,
        ];

        if (array_key_exists('contrasena_hash', $payload)) {
            $query .= ', contrasena_hash = :contrasena_hash';
            $params[':contrasena_hash'] = $payload['contrasena_hash'];
        }

        $query .= ' WHERE id = :id';

        $statement = $pdo->prepare($query);
        $statement->execute($params);
    }

    public function delete(int $userId): void
    {
        $pdo = Conexion::obtener();
        $statement = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $statement->execute([':id' => $userId]);
    }
}
