<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioUsuarios;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;
use DateTimeImmutable;
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
        $profileUpload = $_FILES['foto_perfil'] ?? null;
        $coverUpload = $_FILES['foto_portada'] ?? null;

        $errors = [];
        $profileExtension = null;
        $coverExtension = null;

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

        try {
            $profileExtension = $this->validateUploadedImage($profileUpload);
        } catch (Exception $exception) {
            if ($profileUpload && ($profileUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $errors[] = $exception->getMessage();
            }
        }

        try {
            $coverExtension = $this->validateUploadedImage($coverUpload);
        } catch (Exception $exception) {
            if ($coverUpload && ($coverUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $errors[] = $exception->getMessage();
            }
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

        $photoErrors = [];
        $photoSuccess = [];

        if ($profileExtension !== null && $profileUpload && $profileUpload['error'] === UPLOAD_ERR_OK) {
            try {
                $relativePath = $this->storeUploadedImage($profileUpload, (int) $user['id'], 'perfil', $profileExtension);
                $this->users->setCurrentProfilePhoto((int) $user['id'], $relativePath);
                $photoSuccess[] = 'Actualizamos tu foto de perfil.';
            } catch (Exception $exception) {
                $photoErrors[] = $exception->getMessage();
            }
        }

        if ($coverExtension !== null && $coverUpload && $coverUpload['error'] === UPLOAD_ERR_OK) {
            try {
                $relativePath = $this->storeUploadedImage($coverUpload, (int) $user['id'], 'portada', $coverExtension);
                $this->users->setCurrentCoverPhoto((int) $user['id'], $relativePath);
                $photoSuccess[] = 'Actualizamos tu foto de portada.';
            } catch (Exception $exception) {
                $photoErrors[] = $exception->getMessage();
            }
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['auth_user'])) {
            $_SESSION['auth_user']['nombre'] = $nombre;
            $_SESSION['auth_user']['apellidos'] = $apellidos;
            $_SESSION['auth_user']['correo'] = $correo;
        }

        if (!empty($photoErrors)) {
            $message = 'Tus datos principales se actualizaron, pero encontramos un problema: ' . implode(' ', $photoErrors);
            if (!empty($photoSuccess)) {
                $message .= ' ' . implode(' ', $photoSuccess);
            }

            $this->flashAndRedirect('error', $message);
        }

        $message = 'Tu perfil se actualizó correctamente.';
        if (!empty($photoSuccess)) {
            $message .= ' ' . implode(' ', $photoSuccess);
        }

        $this->flashAndRedirect('success', $message);
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

    public function logout(): void
    {
        $this->logoutUser();
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
        $userId = (int) $user['id'];
        $reservations = $this->users->reservationsForUser($userId);
        $reviews = $this->users->reviewsForUser($userId, 5);

        $upcomingTrips = [];
        $recentActivity = [];
        $normalizedReservations = [];
        $today = new DateTimeImmutable('today');

        foreach ($reservations as $reservation) {
            $reservationDate = $this->parseDate($reservation['fecha_reserva'] ?? null);
            $createdAt = $this->parseDate($reservation['creado_en'] ?? null);
            $estado = strtolower((string) ($reservation['estado'] ?? 'pendiente'));
            $paquete = $reservation['paquete_nombre'] ?? ('Reserva #' . ($reservation['id'] ?? ''));

            if ($reservationDate && $estado !== 'cancelada' && $reservationDate >= $today) {
                $upcomingTrips[] = [
                    'titulo' => $paquete,
                    'fecha' => $reservationDate->format('d/m/Y'),
                    'estado' => ucfirst($estado),
                    'descripcion' => sprintf(
                        'Para %d viajero%s — Total S/ %s',
                        (int) ($reservation['cantidad_personas'] ?? 1),
                        ((int) ($reservation['cantidad_personas'] ?? 1)) === 1 ? '' : 's',
                        $this->formatCurrency($reservation['total'] ?? 0.0)
                    ),
                ];
            }

            $recentActivity[] = [
                'evento' => 'Reserva de ' . $paquete,
                'fecha' => $createdAt?->format('d/m/Y H:i') ?? $reservationDate?->format('d/m/Y'),
                'detalle' => sprintf('Estado: %s — Total S/ %s', ucfirst($estado), $this->formatCurrency($reservation['total'] ?? 0.0)),
            ];

            $normalizedReservations[] = [
                'paquete' => $paquete,
                'fecha' => $reservationDate?->format('d/m/Y') ?? 'Sin fecha',
                'creado_en' => $createdAt?->format('d/m/Y H:i') ?? null,
                'estado' => ucfirst($estado),
                'estado_slug' => preg_replace('/[^a-z0-9_-]/', '-', $estado),
                'personas' => (int) ($reservation['cantidad_personas'] ?? 1),
                'total' => $this->formatCurrency($reservation['total'] ?? 0.0),
                'duracion' => $reservation['paquete_duracion'] ?? null,
                'resumen' => $reservation['paquete_resumen'] ?? null,
            ];
        }

        $upcomingTrips = array_slice($upcomingTrips, 0, 6);
        $recentActivity = array_slice($recentActivity, 0, 6);

        $recentReviews = [];
        foreach ($reviews as $review) {
            $reviewDate = $this->parseDate($review['fecha'] ?? null);
            $recentReviews[] = [
                'destino' => $review['paquete_nombre'] ?? 'Experiencia',
                'puntuacion' => (int) ($review['rating'] ?? 0),
                'fecha' => $reviewDate?->format('d/m/Y') ?? null,
                'comentario' => $review['comentario'] ?? null,
            ];
        }

        $profilePhoto = $this->normalizeAssetPath($user['foto_perfil'] ?? null);
        $coverPhoto = $this->normalizeAssetPath($user['foto_portada'] ?? null);

        return [
            'id' => $userId,
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
            'celular' => $user['celular'] ?? null,
            'rol' => $user['rol'] ?? 'suscriptor',
            'creado_en' => $user['creado_en'] ?? null,
            'verificado_en' => $user['verificado_en'] ?? null,
            'foto_perfil' => $profilePhoto,
            'foto_portada' => $coverPhoto,
            'proximos_viajes' => $upcomingTrips,
            'actividad_reciente' => $recentActivity,
            'ultimas_resenas' => $recentReviews,
            'reservaciones' => $normalizedReservations,
        ];
    }

    private function validateUploadedImage(?array $upload): ?string
    {
        if ($upload === null || !is_array($upload)) {
            return null;
        }

        $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception('No pudimos subir la imagen seleccionada. Intenta nuevamente.');
        }

        if (!isset($upload['tmp_name']) || !is_file($upload['tmp_name'])) {
            throw new Exception('No pudimos acceder al archivo subido.');
        }

        if (($upload['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new Exception('La imagen debe pesar menos de 5 MB.');
        }

        $imageInfo = @getimagesize($upload['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('El archivo seleccionado no es una imagen válida.');
        }

        $mime = $imageInfo['mime'] ?? '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            throw new Exception('Solo se permiten imágenes en formato JPG, PNG o WebP.');
        }

        return $allowed[$mime];
    }

    private function storeUploadedImage(array $upload, int $userId, string $type, string $extension): string
    {
        $baseDirectory = realpath(__DIR__ . '/../../web') ?: __DIR__ . '/../../web';
        $directory = $baseDirectory . '/uploads/usuarios/' . $type;

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new Exception('No pudimos preparar el directorio para subir imágenes.');
        }

        $filename = sprintf('%s-%d-%s.%s', $type, $userId, bin2hex(random_bytes(6)), $extension);
        $targetPath = $directory . '/' . $filename;

        if (!move_uploaded_file($upload['tmp_name'], $targetPath)) {
            throw new Exception('No pudimos guardar la imagen subida.');
        }

        return 'uploads/usuarios/' . $type . '/' . $filename;
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, '.', ',');
    }

    private function normalizeAssetPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path) === 1 || str_starts_with($path, '/')) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if ($normalized === '') {
            return null;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptName = trim(str_replace('\\', '/', $scriptName), '/');
        $segments = $scriptName !== '' ? explode('/', $scriptName) : [];
        $baseSegment = $segments[0] ?? '';
        $baseSegment = $baseSegment === 'index.php' ? '' : $baseSegment;

        $prefixed = $baseSegment !== '' ? $baseSegment . '/' . $normalized : $normalized;
        $prefixed = '/' . ltrim($prefixed, '/');

        if ($baseSegment !== '' && str_starts_with('/' . $normalized, '/' . $baseSegment . '/')) {
            return '/' . ltrim($normalized, '/');
        }

        return $prefixed;
    }
}
