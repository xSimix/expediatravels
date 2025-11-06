<?php

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    public function __construct(private ?UserRepository $users = null)
    {
        $this->users = $this->users ?? new UserRepository();
    }

    public function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
            return $_SESSION['auth_user'];
        }

        $cookie = $_COOKIE['remember_token'] ?? null;
        if (!$cookie || strpos($cookie, ':') === false) {
            return null;
        }

        [$userId, $token] = explode(':', $cookie, 2);
        $userId = is_numeric($userId) ? (int) $userId : null;
        $token = trim($token);

        if (!$userId || $token === '') {
            return null;
        }

        $user = $this->users->findByRememberToken($userId, hash('sha256', $token));
        if ($user === null || empty($user['verificado_en'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            return null;
        }

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
        ];

        return $_SESSION['auth_user'];
    }
}
