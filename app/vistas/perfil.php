<?php
/** @var array $user */
/** @var array|null $flash */
/** @var string|null $title */

$roleLabels = [
    'administrador' => 'Administrador',
    'moderador' => 'Moderador',
    'suscriptor' => 'Suscriptor',
];

$role = $user['rol'] ?? 'suscriptor';
$roleLabel = $roleLabels[$role] ?? ucfirst($role);

$formatDate = static function (?string $value): ?string {
    if ($value === null || $value === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Exception) {
        return $value;
    }
};

$createdAt = $formatDate($user['creado_en'] ?? null);
$verifiedAt = $formatDate($user['verificado_en'] ?? null);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Mi perfil'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <script src="scripts/modal-autenticacion.js" defer></script>
</head>
<body class="profile-page">
    <header class="profile-header">
        <div class="profile-header__content">
            <div class="profile-header__brand">
                <span class="profile-header__logo">🧭</span>
                <div>
                    <p class="profile-header__title">Expediatravels</p>
                    <p class="profile-header__subtitle">Tu cuenta personal</p>
                </div>
            </div>
            <div class="profile-header__actions">
                <a class="profile-header__link" href="index.php">← Volver al inicio</a>
                <button class="profile-header__logout" type="button" data-auth-logout>Cerrar sesión</button>
            </div>
        </div>
    </header>
    <main class="profile-main">
        <section class="profile-intro">
            <h1 class="profile-intro__title">Hola, <?= htmlspecialchars($user['nombre']); ?> 👋</h1>
            <p class="profile-intro__subtitle">
                Administra los datos de tu cuenta, revisa tu rol de acceso y controla la seguridad de tu perfil.
            </p>
            <?php if ($role === 'administrador'): ?>
                <a class="profile-admin-link" href="../administracion/index.php">Ir al panel administrativo</a>
            <?php endif; ?>
        </section>
        <?php if (!empty($flash) && !empty($flash['message'])): ?>
            <?php $flashType = $flash['type'] ?? 'info'; ?>
            <div class="alert <?= $flashType === 'success' ? 'alert--success' : ($flashType === 'error' ? 'alert--error' : 'alert--info'); ?>">
                <?= htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        <div class="profile-grid">
            <section class="profile-card">
                <h2 class="profile-card__title">Detalles del suscriptor</h2>
                <dl class="profile-details">
                    <div class="profile-details__item">
                        <dt>Nombre completo</dt>
                        <dd><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']); ?></dd>
                    </div>
                    <div class="profile-details__item">
                        <dt>Correo electrónico</dt>
                        <dd><?= htmlspecialchars($user['correo']); ?></dd>
                    </div>
                    <div class="profile-details__item">
                        <dt>Celular</dt>
                        <dd><?= $user['celular'] ? htmlspecialchars($user['celular']) : 'No registrado'; ?></dd>
                    </div>
                    <div class="profile-details__item">
                        <dt>Rol</dt>
                        <dd><span class="profile-role profile-role--<?= htmlspecialchars($role); ?>"><?= htmlspecialchars($roleLabel); ?></span></dd>
                    </div>
                    <div class="profile-details__item">
                        <dt>Cuenta creada</dt>
                        <dd><?= $createdAt ? htmlspecialchars($createdAt) : 'Sin registro'; ?></dd>
                    </div>
                    <div class="profile-details__item">
                        <dt>Verificada</dt>
                        <dd><?= $verifiedAt ? htmlspecialchars($verifiedAt) : 'Pendiente de verificación'; ?></dd>
                    </div>
                </dl>
            </section>
            <section class="profile-card">
                <h2 class="profile-card__title">Editar datos personales</h2>
                <form class="profile-form" action="perfil.php" method="post" novalidate>
                    <input type="hidden" name="action" value="update" />
                    <div class="profile-form__grid">
                        <label class="profile-form__field">
                            <span>Nombres</span>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($user['nombre']); ?>" required />
                        </label>
                        <label class="profile-form__field">
                            <span>Apellidos</span>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($user['apellidos']); ?>" required />
                        </label>
                        <label class="profile-form__field">
                            <span>Correo electrónico</span>
                            <input type="email" name="correo" value="<?= htmlspecialchars($user['correo']); ?>" required />
                        </label>
                        <label class="profile-form__field">
                            <span>Celular</span>
                            <input type="text" name="celular" value="<?= htmlspecialchars($user['celular'] ?? ''); ?>" placeholder="Opcional" />
                        </label>
                    </div>
                    <fieldset class="profile-form__fieldset">
                        <legend>Actualizar contraseña</legend>
                        <p>Completa los siguientes campos solo si deseas definir una nueva contraseña.</p>
                        <div class="profile-form__grid">
                            <label class="profile-form__field">
                                <span>Nueva contraseña</span>
                                <input type="password" name="password" minlength="8" autocomplete="new-password" />
                            </label>
                            <label class="profile-form__field">
                                <span>Confirmar contraseña</span>
                                <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" />
                            </label>
                        </div>
                    </fieldset>
                    <div class="profile-form__actions">
                        <button class="profile-form__submit" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </section>
            <section class="profile-card profile-card--danger">
                <h2 class="profile-card__title">Eliminar cuenta</h2>
                <p class="profile-card__copy">
                    Esta acción eliminará tu cuenta de forma permanente. Para confirmar, escribe <strong>ELIMINAR</strong> y selecciona "Cerrar mi cuenta".
                </p>
                <form class="profile-delete" action="perfil.php" method="post">
                    <input type="hidden" name="action" value="delete" />
                    <label class="profile-form__field">
                        <span>Confirmación</span>
                        <input type="text" name="confirmacion" placeholder="Escribe ELIMINAR" required />
                    </label>
                    <button class="profile-delete__button" type="submit">Cerrar mi cuenta</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
