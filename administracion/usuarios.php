<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/configuracion/arranque.php';

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

$paginaActiva = 'usuarios';
$tituloPagina = 'Usuarios — Panel de Control';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Módulo de usuarios</h1>
        <p>Gestiona las cuentas, roles y permisos del equipo y de los viajeros.</p>
    </header>

    <?php if (!empty($errors)) : ?>
        <div class="admin-alert error">
            <p><strong>Se encontraron problemas al guardar:</strong></p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage !== null) : ?>
        <div class="admin-alert">
            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="admin-card admin-card--flush">
        <h2>Usuarios registrados</h2>
        <?php if (empty($users)) : ?>
            <p class="admin-empty">No hay usuarios registrados todavía.</p>
        <?php else : ?>
            <div class="admin-table-wrapper">
                <table class="admin-table admin-table--users">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Contacto</th>
                            <th>Rol</th>
                            <th>Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <?php $userId = (int) $user['id']; ?>
                            <tr>
                                <td>
                                    <p class="admin-table__user">
                                        <?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                    <p class="admin-table__meta">ID #<?= $userId; ?></p>
                                </td>
                                <td>
                                    <p><?= htmlspecialchars($user['correo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php if (!empty($user['celular'])) : ?>
                                        <p class="admin-table__meta">Tel: <?= htmlspecialchars($user['celular'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($user['rol'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <form method="post" class="admin-table__form">
                                        <input type="hidden" name="user_id" value="<?= $userId; ?>" />
                                        <div class="admin-grid two-columns">
                                            <div class="admin-field">
                                                <label for="nombre-<?= $userId; ?>">Nombre</label>
                                                <input type="text" id="nombre-<?= $userId; ?>" name="nombre" value="<?= htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required />
                                            </div>
                                            <div class="admin-field">
                                                <label for="apellidos-<?= $userId; ?>">Apellidos</label>
                                                <input type="text" id="apellidos-<?= $userId; ?>" name="apellidos" value="<?= htmlspecialchars($user['apellidos'], ENT_QUOTES, 'UTF-8'); ?>" required />
                                            </div>
                                        </div>
                                        <div class="admin-grid two-columns">
                                            <div class="admin-field">
                                                <label for="celular-<?= $userId; ?>">Celular</label>
                                                <input type="text" id="celular-<?= $userId; ?>" name="celular" value="<?= htmlspecialchars($user['celular'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                            </div>
                                            <div class="admin-field">
                                                <label for="correo-<?= $userId; ?>">Correo</label>
                                                <input type="email" id="correo-<?= $userId; ?>" name="correo" value="<?= htmlspecialchars($user['correo'], ENT_QUOTES, 'UTF-8'); ?>" required />
                                            </div>
                                        </div>
                                        <div class="admin-grid two-columns">
                                            <div class="admin-field">
                                                <label for="rol-<?= $userId; ?>">Rol</label>
                                                <select id="rol-<?= $userId; ?>" name="rol">
                                                    <?php foreach ($allowedRoles as $roleOption) : ?>
                                                        <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8'); ?>" <?= $roleOption === $user['rol'] ? 'selected' : ''; ?>>
                                                            <?= ucfirst($roleOption); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="admin-field">
                                                <label for="contrasena-<?= $userId; ?>">Nueva contraseña</label>
                                                <input type="password" id="contrasena-<?= $userId; ?>" name="contrasena" placeholder="Ingresa para actualizar" />
                                                <p class="admin-help">Déjala vacía para mantener la actual.</p>
                                            </div>
                                        </div>
                                        <div class="admin-table__actions">
                                            <button type="submit" class="admin-button">Guardar cambios</button>
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
</div>

<?php require __DIR__ . '/plantilla/pie.php'; ?>
