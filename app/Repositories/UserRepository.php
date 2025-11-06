<?php

namespace App\Repositories;

use App\Database\Connection;
use DateTimeInterface;
use PDO;

class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $pdo = Connection::get();
        $statement = $pdo->prepare('SELECT * FROM usuarios WHERE correo = :correo LIMIT 1');
        $statement->execute([':correo' => mb_strtolower($email)]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public function create(array $payload): int
    {
        $pdo = Connection::get();

        $statement = $pdo->prepare(
            'INSERT INTO usuarios (nombre, apellidos, celular, correo, contrasena_hash, verificacion_pin, pin_expira_en) '
            . 'VALUES (:nombre, :apellidos, :celular, :correo, :contrasena_hash, :verificacion_pin, :pin_expira_en)'
        );

        $statement->execute([
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'],
            ':correo' => mb_strtolower($payload['correo']),
            ':contrasena_hash' => $payload['contrasena_hash'],
            ':verificacion_pin' => $payload['verificacion_pin'],
            ':pin_expira_en' => $payload['pin_expira_en'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function updatePendingUser(int $userId, array $payload): void
    {
        $pdo = Connection::get();
        $statement = $pdo->prepare(
            'UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, celular = :celular, contrasena_hash = :contrasena_hash, '
            . 'verificacion_pin = :verificacion_pin, pin_expira_en = :pin_expira_en WHERE id = :id'
        );

        $statement->execute([
            ':nombre' => $payload['nombre'],
            ':apellidos' => $payload['apellidos'],
            ':celular' => $payload['celular'],
            ':contrasena_hash' => $payload['contrasena_hash'],
            ':verificacion_pin' => $payload['verificacion_pin'],
            ':pin_expira_en' => $payload['pin_expira_en'],
            ':id' => $userId,
        ]);
    }

    public function updatePin(int $userId, string $pin, DateTimeInterface $expiresAt): void
    {
        $pdo = Connection::get();
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
        $pdo = Connection::get();
        $statement = $pdo->prepare(
            'UPDATE usuarios SET verificado_en = NOW(), verificacion_pin = NULL, pin_expira_en = NULL WHERE id = :id'
        );
        $statement->execute([':id' => $userId]);
    }

    public function updateRememberToken(int $userId, ?string $token, ?DateTimeInterface $expiresAt): void
    {
        $pdo = Connection::get();
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
        $pdo = Connection::get();
        $statement = $pdo->prepare(
            'SELECT * FROM usuarios WHERE id = :id AND remember_token = :token AND remember_token_expira_en > NOW() LIMIT 1'
        );

        $statement->execute([
            ':id' => $userId,
            ':token' => $tokenHash,
        ]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }
}
