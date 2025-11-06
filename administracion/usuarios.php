<?php
declare(strict_types=1);

require_once __DIR__ . '/../aplicacion/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioUsuarios;

$userRepository = new RepositorioUsuarios();

$errors = [];
$successMessage = null;
$allowedRoles = ['administrador', 'moderador', 'suscriptor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['contrasena'] ?? '';

    if ($userId <= 0) {
        $errors[] = 'Identificador de usuario no válido.';
    }

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    if ($apellidos === '') {
        $errors[] = 'Los apellidos son obligatorios.';
    }

    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Debes proporcionar un correo electrónico válido.';
    }

    if (!in_array($rol, $allowedRoles, true)) {
        $errors[] = 'El rol seleccionado no es válido.';
    }

    if (empty($errors)) {
        try {
            $payload = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'celular' => $celular !== '' ? $celular : null,
                'correo' => $correo,
                'rol' => $rol,
            ];

            if ($password !== '') {
                $payload['contrasena_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $userRepository->updateAdmin($userId, $payload);
            $successMessage = 'Usuario actualizado correctamente.';
        } catch (Throwable $exception) {
            $errors[] = 'No se pudo actualizar el usuario. Intenta nuevamente.';
        }
    }
}

$users = $userRepository->all();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Usuarios — Expediatravels</title>
    <link rel="stylesheet" href="../sitio_web/estilos/aplicacion.css" />
</head>
<body class="bg-gray-50 text-slate-900">
    <main class="max-w-5xl mx-auto py-12 px-6 space-y-6">
        <header>
            <h1 class="text-3xl font-semibold text-sky-600">Módulo Usuarios</h1>
            <p class="mt-2 text-slate-600">Gestiona las cuentas, roles y permisos del equipo y de los viajeros.</p>
        </header>
        <?php if (!empty($errors)) : ?>
            <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Se encontraron problemas al guardar:</p>
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($successMessage !== null) : ?>
            <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <section class="space-y-4">
            <?php if (empty($users)) : ?>
                <p class="text-sm text-slate-600">No hay usuarios registrados todavía.</p>
            <?php else : ?>
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                <th class="px-4 py-3">Usuario</th>
                                <th class="px-4 py-3">Contacto</th>
                                <th class="px-4 py-3">Rol</th>
                                <th class="px-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach ($users as $user) : ?>
                                <tr class="align-top text-sm">
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-900">
                                            <?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="text-xs text-slate-500">ID #<?= (int) $user['id'] ?></p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <p><?= htmlspecialchars($user['correo'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (!empty($user['celular'])) : ?>
                                            <p class="text-xs text-slate-500 mt-1">Tel: <?= htmlspecialchars($user['celular'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700 capitalize">
                                        <?= htmlspecialchars($user['rol'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <form method="POST" class="grid grid-cols-1 gap-3 text-sm">
                                            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>" />
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Nombre</span>
                                                <input type="text" name="nombre" required class="rounded border border-slate-300 px-3 py-2"
                                                    value="<?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8') ?>" />
                                            </label>
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Apellidos</span>
                                                <input type="text" name="apellidos" required class="rounded border border-slate-300 px-3 py-2"
                                                    value="<?= htmlspecialchars($user['apellidos'], ENT_QUOTES, 'UTF-8') ?>" />
                                            </label>
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Celular</span>
                                                <input type="text" name="celular" class="rounded border border-slate-300 px-3 py-2"
                                                    value="<?= htmlspecialchars($user['celular'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </label>
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Correo</span>
                                                <input type="email" name="correo" required class="rounded border border-slate-300 px-3 py-2"
                                                    value="<?= htmlspecialchars($user['correo'], ENT_QUOTES, 'UTF-8') ?>" />
                                            </label>
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Rol</span>
                                                <select name="rol" class="rounded border border-slate-300 px-3 py-2">
                                                    <?php foreach ($allowedRoles as $roleOption) : ?>
                                                        <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8') ?>"
                                                            <?= $roleOption === $user['rol'] ? 'selected' : '' ?>>
                                                            <?= ucfirst($roleOption) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                            <label class="grid gap-1">
                                                <span class="text-xs font-semibold uppercase text-slate-500">Nueva contraseña</span>
                                                <input type="password" name="contrasena" class="rounded border border-slate-300 px-3 py-2"
                                                    placeholder="Ingresa para actualizar" />
                                                <span class="text-xs text-slate-500">Déjala vacía para mantener la actual.</span>
                                            </label>
                                            <div class="flex justify-end">
                                                <button type="submit" class="rounded bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                                                    Guardar cambios
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
