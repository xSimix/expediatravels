<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use DateTimeImmutable;
use Exception;

class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function register(): void
    {
        $payload = $this->getPayload();

        $nombre = trim((string) ($payload['nombres'] ?? ''));
        $apellidos = trim((string) ($payload['apellidos'] ?? ''));
        $celular = trim((string) ($payload['celular'] ?? ''));
        $correo = trim((string) ($payload['correo'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($nombre === '' || $apellidos === '' || $correo === '' || $password === '') {
            $this->respondError('Por favor completa todos los campos obligatorios.');
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->respondError('El correo electrónico no es válido.');
            return;
        }

        if (strlen($password) < 8) {
            $this->respondError('La contraseña debe tener al menos 8 caracteres.');
            return;
        }

        $celularNormalizado = $celular !== '' ? preg_replace('/[^+\d]/', '', $celular) : null;

        $existingUser = $this->users->findByEmail($correo);
        $pin = $this->generatePin();
        $expiresAt = $this->pinExpiry();

        try {
            if ($existingUser !== null) {
                if (!empty($existingUser['verificado_en'])) {
                    $this->respondError('Ya existe una cuenta registrada con este correo.');
                    return;
                }

                $this->users->updatePendingUser((int) $existingUser['id'], [
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'celular' => $celularNormalizado,
                    'contrasena_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'verificacion_pin' => $pin,
                    'pin_expira_en' => $expiresAt->format('Y-m-d H:i:s'),
                ]);

                $userId = (int) $existingUser['id'];
            } else {
                $userId = $this->users->create([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'celular' => $celularNormalizado,
                    'correo' => $correo,
                    'contrasena_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'verificacion_pin' => $pin,
                    'pin_expira_en' => $expiresAt->format('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception) {
            $this->respondError('No pudimos registrar la cuenta en este momento. Intenta nuevamente.');
            return;
        }

        $this->sendVerificationPin($correo, $pin, $nombre);

        $this->respondSuccess('Cuenta creada. Revisa tu correo para validar el PIN.', [
            'needsVerification' => true,
            'correo' => $correo,
            'usuarioId' => $userId,
        ]);
    }

    public function login(): void
    {
        $payload = $this->getPayload();
        $correo = trim((string) ($payload['correo'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $remember = (bool) ($payload['recordar'] ?? false);

        if ($correo === '' || $password === '') {
            $this->respondError('Ingresa tu correo y contraseña.');
            return;
        }

        $user = $this->users->findByEmail($correo);
        if ($user === null || !password_verify($password, (string) $user['contrasena_hash'])) {
            $this->respondError('Las credenciales no son válidas.');
            return;
        }

        if (empty($user['verificado_en'])) {
            $this->respondError('Tu cuenta aún no ha sido verificada. Revisa tu correo para validar el PIN.', [
                'needsVerification' => true,
                'correo' => $correo,
            ]);
            return;
        }

        $this->startSession();
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
        ];

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = new DateTimeImmutable('+30 days');
            $hash = hash('sha256', $token);
            $this->users->updateRememberToken((int) $user['id'], $hash, $expiresAt);
            setcookie('remember_token', $user['id'] . ':' . $token, $expiresAt->getTimestamp(), '/', '', false, true);
        } else {
            $this->users->updateRememberToken((int) $user['id'], null, null);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }

        $this->respondSuccess('Sesión iniciada correctamente.', [
            'usuario' => $_SESSION['auth_user'],
        ]);
    }

    public function verify(): void
    {
        $payload = $this->getPayload();
        $correo = trim((string) ($payload['correo'] ?? ''));
        $pin = trim((string) ($payload['pin'] ?? ''));

        if ($correo === '' || $pin === '') {
            $this->respondError('Debes ingresar el correo y el PIN recibido.');
            return;
        }

        $user = $this->users->findByEmail($correo);
        if ($user === null) {
            $this->respondError('No encontramos una cuenta asociada a este correo.');
            return;
        }

        if (!empty($user['verificado_en'])) {
            $this->respondSuccess('Tu cuenta ya está verificada.');
            return;
        }

        $pinAlmacenado = (string) ($user['verificacion_pin'] ?? '');
        $expira = $user['pin_expira_en'] ?? null;

        if ($pinAlmacenado === '' || $pinAlmacenado !== $pin) {
            $this->respondError('El PIN ingresado no es correcto.');
            return;
        }

        if (!empty($expira) && new DateTimeImmutable($expira) < new DateTimeImmutable('now')) {
            $this->respondError('El PIN ha expirado. Solicita uno nuevo.');
            return;
        }

        $this->users->markVerified((int) $user['id']);
        $this->startSession();
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
        ];

        $this->respondSuccess('Cuenta verificada correctamente.', [
            'usuario' => $_SESSION['auth_user'],
        ]);
    }

    public function resendPin(): void
    {
        $payload = $this->getPayload();
        $correo = trim((string) ($payload['correo'] ?? ''));

        if ($correo === '') {
            $this->respondError('Ingresa el correo con el que te registraste.');
            return;
        }

        $user = $this->users->findByEmail($correo);
        if ($user === null) {
            $this->respondError('No encontramos una cuenta asociada a este correo.');
            return;
        }

        if (!empty($user['verificado_en'])) {
            $this->respondSuccess('Tu cuenta ya está verificada. Puedes iniciar sesión.');
            return;
        }

        $pin = $this->generatePin();
        $expiresAt = $this->pinExpiry();
        $this->users->updatePin((int) $user['id'], $pin, $expiresAt);
        $this->sendVerificationPin($correo, $pin, $user['nombre']);

        $this->respondSuccess('Hemos enviado un nuevo PIN de verificación a tu correo.');
    }

    public function forgotPassword(): void
    {
        $payload = $this->getPayload();
        $correo = trim((string) ($payload['correo'] ?? ''));

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->respondError('Ingresa un correo válido.');
            return;
        }

        $user = $this->users->findByEmail($correo);
        if ($user === null) {
            $this->respondError('No encontramos una cuenta con este correo.');
            return;
        }

        // In a production application we would send an email with a secure link.
        $this->respondSuccess('Hemos enviado las instrucciones para restablecer tu contraseña a tu correo.');
    }

    public function logout(): void
    {
        $this->startSession();
        if (isset($_SESSION['auth_user'])) {
            $userId = (int) $_SESSION['auth_user']['id'];
            $this->users->updateRememberToken($userId, null, null);
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);

        $this->respondSuccess('Sesión cerrada correctamente.');
    }

    private function getPayload(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return $_POST;
    }

    private function respondSuccess(string $message, array $data = []): void
    {
        $this->respond(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function respondError(string $message, array $data = [], int $status = 400): void
    {
        http_response_code($status);
        $this->respond(['success' => false, 'message' => $message, 'data' => $data]);
    }

    private function respond(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function pinExpiry(): DateTimeImmutable
    {
        return new DateTimeImmutable('+15 minutes');
    }

    private function generatePin(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendVerificationPin(string $correo, string $pin, string $nombre): void
    {
        $directory = __DIR__ . '/../../storage';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $logEntry = sprintf(
            "[%s] PIN %s enviado a %s (%s)%s",
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $pin,
            $correo,
            $nombre,
            PHP_EOL
        );

        file_put_contents($directory . '/mail.log', $logEntry, FILE_APPEND);
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
