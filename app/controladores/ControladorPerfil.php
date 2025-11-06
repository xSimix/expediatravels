<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioUsuarios;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;
use Exception;

class ControladorPerfil
{
    public function __construct(
        private ?ServicioAutenticacion $authService = null,
        private ?RepositorioUsuarios $users = null
    ) {
        $this->authService = $this->authService ?? new ServicioAutenticacion();
        $this->users = $this->users ?? new RepositorioUsuarios();
    }

    public function show(): void
    {
        $user = $this->requireAuthenticatedUser();
        $flash = $_SESSION['alerta_perfil'] ?? null;
        unset($_SESSION['alerta_perfil']);

        $view = new Vista('perfil');
        $view->render([
            'title' => 'Mi perfil — Expediatravels',
            'user' => $this->presentUser($user),
            'flash' => $flash,
        ]);
    }

    public function update(): void
    {
        $user = $this->requireAuthenticatedUser();

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $apellidos = trim((string) ($_POST['apellidos'] ?? ''));
        $correo = mb_strtolower(trim((string) ($_POST['correo'] ?? '')));
        $celular = trim((string) ($_POST['celular'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];

        if ($nombre === '' || $apellidos === '' || $correo === '') {
            $errors[] = 'Completa tu nombre, apellidos y correo electrónico.';
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if ($password !== '') {
            if (strlen($password) < 8) {
                $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            }

            if ($password !== $passwordConfirmation) {
                $errors[] = 'La confirmación de la contraseña no coincide.';
            }
        }

        $existingUser = $this->users->findByEmail($correo);
        if ($existingUser !== null && (int) $existingUser['id'] !== (int) $user['id']) {
            $errors[] = 'Ya existe una cuenta registrada con este correo electrónico.';
        }

        if (!empty($errors)) {
            $this->flashAndRedirect('error', implode(' ', $errors));
        }

        $celularNormalizado = $celular !== '' ? preg_replace('/[^+\d]/', '', $celular) : null;
        if ($celularNormalizado === '') {
            $celularNormalizado = null;
        }

        $updatePayload = [
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'celular' => $celularNormalizado,
            'correo' => $correo,
        ];

        if ($password !== '') {
            $updatePayload['contrasena_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $this->users->updateProfile((int) $user['id'], $updatePayload);
        } catch (Exception) {
            $this->flashAndRedirect('error', 'No pudimos actualizar tu perfil en este momento. Intenta nuevamente.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['auth_user'])) {
            $_SESSION['auth_user']['nombre'] = $nombre;
            $_SESSION['auth_user']['apellidos'] = $apellidos;
            $_SESSION['auth_user']['correo'] = $correo;
        }

        $this->flashAndRedirect('success', 'Tu perfil se actualizó correctamente.');
    }

    public function destroy(): void
    {
        $user = $this->requireAuthenticatedUser();
        $confirmation = strtoupper(trim((string) ($_POST['confirmacion'] ?? '')));

        if ($confirmation !== 'ELIMINAR') {
            $this->flashAndRedirect('error', 'Debes escribir ELIMINAR para confirmar la eliminación de tu cuenta.');
        }

        try {
            $this->users->delete((int) $user['id']);
        } catch (Exception) {
            $this->flashAndRedirect('error', 'No pudimos eliminar tu cuenta en este momento. Intenta nuevamente.');
        }

        $this->logoutUser();
        setcookie('account_deleted_notice', '1', time() + 120, '/', '', false, true);

        header('Location: index.php');
        exit;
    }

    private function requireAuthenticatedUser(): array
    {
        $sessionUser = $this->authService->currentUser();
        if ($sessionUser === null) {
            header('Location: index.php');
            exit;
        }

        $user = $this->users->findById((int) $sessionUser['id']);
        if ($user === null) {
            $this->logoutUser();
            header('Location: index.php');
            exit;
        }

        return $user;
    }

    private function flashAndRedirect(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['alerta_perfil'] = [
            'type' => $type,
            'message' => $message,
        ];

        header('Location: perfil.php');
        exit;
    }

    private function logoutUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['auth_user']['id'])) {
            $this->users->updateRememberToken((int) $_SESSION['auth_user']['id'], null, null);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }

    private function presentUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
            'celular' => $user['celular'] ?? null,
            'rol' => $user['rol'] ?? 'suscriptor',
            'creado_en' => $user['creado_en'] ?? null,
            'verificado_en' => $user['verificado_en'] ?? null,
        ];
    }
}
