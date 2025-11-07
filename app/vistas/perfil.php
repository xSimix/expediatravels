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

$formatDate = static function (?string $value, string $format = 'd/m/Y'): ?string {
    if ($value === null || $value === '') {
        return null;
    }

    try {
        return (new DateTimeImmutable($value))->format($format);
    } catch (Exception) {
        return $value;
    }
};

$createdAt = $formatDate($user['creado_en'] ?? null, 'd \d\e F \d\e Y');
$createdAtFull = $formatDate($user['creado_en'] ?? null, 'd/m/Y H:i');
$verifiedAt = $formatDate($user['verificado_en'] ?? null, 'd/m/Y H:i');
$fullName = trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''));
$phone = $user['celular'] ?? null;
$displayName = $fullName !== '' ? $fullName : ($user['nombre'] ?? '');
$profilePhoto = $user['foto_perfil'] ?? null;
$coverPhoto = $user['foto_portada'] ?? null;
$coverStyle = $coverPhoto ? '--cover-image: url(' . json_encode($coverPhoto) . ');' : '';
$avatarStyle = $profilePhoto ? '--avatar-image: url(' . json_encode($profilePhoto) . ');' : '';
$coverClass = 'cover' . ($coverPhoto ? ' has-cover' : '');

$icons = [
    'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13.5h6m-6-3h6m2.25 9h-9.75A2.25 2.25 0 0 1 5.25 17.25V6.75A2.25 2.25 0 0 1 7.5 4.5h6l3.75 3.75v9a2.25 2.25 0 0 1-2.25 2.25Z" />',
    'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v4.5m0 9V21m9-9h-4.5M7.5 12H3m13.364-6.364-3.182 3.182m0 6.364 3.182 3.182M9.818 8.818 6.636 5.636m0 12.728 3.182-3.182" />',
    'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 7.5 12 13.5 2.25 7.5m19.5 9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25h15A2.25 2.25 0 0 1 21.75 7.5v9Z" />',
    'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 12.75a6 6 0 1 1 12 0v.75H6.75v-.75Z" />',
    'shield-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 12 2.25 2.25L15 10.5m6-3.75-7.5-3-7.5 3v5.25a9.75 9.75 0 0 0 7.5 9.45 9.75 9.75 0 0 0 7.5-9.45V6.75Z" />',
    'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M4.5 9.75h15m-12 4.5h3m3 0h3m-12 4.5h15A2.25 2.25 0 0 0 21.75 16.5V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75V16.5a2.25 2.25 0 0 0 2.25 2.25Z" />',
    'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 12 2.25 2.25L15 10.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" />',
    'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    'shield-exclamation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m0 3.75h.007v-.007H12V16.5Zm7.5-9.75-7.5-3-7.5 3v5.25a9.75 9.75 0 0 0 7.5 9.45 9.75 9.75 0 0 0 7.5-9.45V6.75Z" />',
    'pencil-square' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-3.247.931.931-3.247a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125M18 14.25v4.125A1.125 1.125 0 0 1 16.875 19.5h-9.75A1.125 1.125 0 0 1 6 18.375v-9.75A1.125 1.125 0 0 1 7.125 7.5H11.25" />',
    'trash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.683.107 1.022.166m-1.022-.166L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.398m7.5 0v-.916C14.25 3.834 13.42 3 12.375 3h-1.5C9.832 3 9 3.834 9 4.875v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />',
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 12 9.75-9.75L21.75 12M4.5 9.75V21h15V9.75" />',
    'compass' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9.813 9.813 6.28-2.04-2.04 6.28-6.28 2.04 2.04-6.28Zm10.89 2.187a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z" />',
    'arrow-uturn-left' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />',
    'save' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7.5H6a1.5 1.5 0 0 0-1.5 1.5v9A1.5 1.5 0 0 0 6 19.5h12a1.5 1.5 0 0 0 1.5-1.5V9l-3-3h-7.5ZM15 7.5V12a3 3 0 1 1-6 0V7.5m0 0V3h6v4.5" />',
    'logout' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3-3m0 0 3 3m-3-3v12" />',
];

$renderIcon = static function (string $name, string $sizeClass = '') use ($icons): string {
    if (!isset($icons[$name])) {
        return '';
    }

    $class = 'icon';
    if ($sizeClass !== '') {
        $class .= ' ' . $sizeClass;
    }

    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $icons[$name] . '</svg>';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? 'Perfil de Usuario – Expediatravels'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f5f7fb;
      --surface: #ffffff;
      --text: #111827;
      --muted: #6b7280;
      --brand: #0ea5e9;
      --danger: #ef4444;
      --radius: 18px;
      --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
      --ring: 0 0 0 3px rgba(14, 165, 233, .25);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    a { color: var(--brand); text-decoration: none; }

    .wrap { max-width: 1080px; margin: 0 auto; padding: 20px; }

    .cover {
      position: relative;
      border-radius: var(--radius);
      padding: 28px;
      background-color: #60a5fa;
      background-image: linear-gradient(135deg, rgba(125, 211, 252, 0.92) 0%, rgba(96, 165, 250, 0.95) 45%, rgba(79, 70, 229, 0.9) 100%);
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .cover.has-cover {
      background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.45) 45%, rgba(15, 23, 42, 0.6) 100%), var(--cover-image);
      background-size: cover, cover;
      background-position: center, center;
    }

    .cover-actions {
      display: flex;
      justify-content: flex-end;
      flex-wrap: wrap;
      gap: 10px;
    }

    .profile-head {
      margin-top: 36px;
      display: flex;
      align-items: flex-end;
      gap: 22px;
      flex-wrap: wrap;
    }

    .avatar {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      border: 4px solid rgba(255, 255, 255, .85);
      background-color: #dbeafe;
      background-image: var(--avatar-image, url('https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9?q=80&w=300&auto=format&fit=crop'));
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      box-shadow: var(--shadow);
      flex-shrink: 0;
    }

    .profile-data { display: grid; gap: 12px; min-width: 0; }

    .name-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .name {
      margin: 0;
      font-size: clamp(1.6rem, 3vw, 2rem);
      font-weight: 800;
      color: #f8fafc;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .name__check {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      border-radius: 999px;
      background: #22c55e;
      color: #f0fdf4;
      box-shadow: 0 10px 25px rgba(34, 197, 94, .45);
    }

    .name__check .icon {
      width: 18px;
      height: 18px;
      stroke: currentColor;
    }

    .badge {
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .15);
      color: #e0f2fe;
      font-weight: 600;
      letter-spacing: .02em;
    }

    .status-line {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      color: rgba(241, 245, 249, .92);
      font-weight: 600;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(15, 23, 42, .35);
      padding: 6px 14px;
      border-radius: 999px;
      color: #e0f2fe;
    }

    .status-pill__icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .contact-row {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      font-weight: 600;
      color: rgba(15, 23, 42, .92);
    }

    .contact-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, .85);
      padding: 6px 14px;
      border-radius: 12px;
      color: inherit;
      text-decoration: none;
      box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .contact-chip:hover {
      background: rgba(255, 255, 255, .95);
    }

    .contact-chip__icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .summary {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .summary span {
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .15);
      color: #e0f2fe;
      font-weight: 600;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: none;
      border-radius: 14px;
      padding: 10px 16px;
      font-weight: 600;
      cursor: pointer;
      background: rgba(255, 255, 255, .9);
      color: var(--text);
      box-shadow: var(--shadow);
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .btn.primary { background: var(--brand); color: #fff; }
    .btn.danger { background: var(--danger); color: #fff; }
    .btn__icon { display: inline-flex; align-items: center; justify-content: center; }

    .icon {
      width: 1.25em;
      height: 1.25em;
      stroke: currentColor;
    }

    .icon--lg { width: 1.6em; height: 1.6em; }
    .icon--md { width: 1.35em; height: 1.35em; }
    .icon--sm { width: 1em; height: 1em; }
    .btn:hover { transform: translateY(-1px); }
    .btn:focus-visible { outline: none; box-shadow: var(--ring); }

    main { display: grid; gap: 24px; margin-top: 32px; }

    .card {
      background: var(--surface);
      border-radius: var(--radius);
      padding: 24px;
      box-shadow: var(--shadow);
    }

    .card h2 {
      margin: 0 0 16px;
      font-size: 1.1rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 12px;
      background: rgba(14, 165, 233, .15);
      color: var(--brand);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
    }

    .info-item { display: grid; gap: 4px; }
    .info-item dt {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: .85rem;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--muted);
    }

    .info-item__icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: rgba(14, 165, 233, .15);
      color: var(--brand);
    }
    .info-item dd { margin: 0; font-weight: 600; font-size: 1.05rem; }

    .quick-actions { display: flex; flex-wrap: wrap; gap: 12px; }

    .alert {
      margin: 0;
      padding: 16px 18px;
      border-radius: 16px;
      font-weight: 600;
      box-shadow: var(--shadow);
    }

    .alert--success { background: #dcfce7; color: #14532d; }
    .alert--error { background: #fee2e2; color: #7f1d1d; }
    .alert--info { background: #e0f2fe; color: #0c4a6e; }

    form { display: grid; gap: 16px; }
    .field { display: grid; gap: 6px; }
    label { font-weight: 600; color: var(--text); }
    input, select, textarea {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      background: #fff;
      font-size: 16px;
    }
    input:focus-visible, select:focus-visible, textarea:focus-visible { outline: none; box-shadow: var(--ring); border-color: #bae6fd; }

    .form-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .form-actions { display: flex; flex-wrap: wrap; gap: 12px; }

    .card--danger { border: 1px solid rgba(239, 68, 68, .25); }

    .footer { text-align: center; color: var(--muted); padding: 32px 0 24px; font-size: .9rem; }

    [hidden] { display: none !important; }

    @media (max-width: 768px) {
      .wrap { padding: 16px; }
      .cover { padding: 22px; }
      .cover-actions { justify-content: flex-start; }
      .avatar { width: 92px; height: 92px; }
      .contact-chip { font-size: .95rem; }
    }
  </style>
  <script src="scripts/modal-autenticacion.js" defer></script>
</head>
<body>
  <header class="wrap">
    <div class="<?= htmlspecialchars($coverClass); ?>" role="img" aria-label="Portada del perfil de <?= htmlspecialchars($displayName); ?>"<?php if ($coverStyle !== ''): ?> style="<?= htmlspecialchars($coverStyle, ENT_QUOTES); ?>"<?php endif; ?>>
      <div class="cover-actions">
        <button class="btn primary" type="button" data-auth-logout>
          <span class="btn__icon" aria-hidden="true"><?= $renderIcon('logout'); ?></span>
          <span>Cerrar sesión</span>
        </button>
      </div>
      <div class="profile-head">
        <div class="avatar" id="avatar" aria-label="Foto de perfil de <?= htmlspecialchars($displayName); ?>"<?php if ($avatarStyle !== ''): ?> style="<?= htmlspecialchars($avatarStyle, ENT_QUOTES); ?>"<?php endif; ?>></div>
        <div class="profile-data">
          <div class="name-row">
            <h1 class="name">
              <span><?= htmlspecialchars($displayName); ?></span>
              <?php if ($verifiedAt): ?>
                <span class="name__check" title="Cuenta verificada" aria-label="Cuenta verificada">
                  <?= $renderIcon('check-circle', 'icon--sm'); ?>
                </span>
              <?php endif; ?>
            </h1>
            <span class="badge" title="Rol del usuario"><?= htmlspecialchars($roleLabel); ?></span>
          </div>
          <div class="status-line">
            <?php if ($createdAt !== null): ?>
              <span class="status-pill">
                <span class="status-pill__icon" aria-hidden="true"><?= $renderIcon('calendar', 'icon--sm'); ?></span>
                <span>Miembro desde <?= htmlspecialchars($createdAt); ?></span>
              </span>
            <?php endif; ?>
            <?php if (!$verifiedAt): ?>
              <span class="status-pill">
                <span class="status-pill__icon" aria-hidden="true"><?= $renderIcon('shield-exclamation', 'icon--sm'); ?></span>
                <span>Verificación pendiente</span>
              </span>
            <?php endif; ?>
          </div>
          <div class="contact-row">
            <a class="contact-chip" href="mailto:<?= htmlspecialchars($user['correo']); ?>">
              <span class="contact-chip__icon" aria-hidden="true"><?= $renderIcon('mail', 'icon--sm'); ?></span>
              <span><?= htmlspecialchars($user['correo']); ?></span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="wrap" id="content">
    <?php if (!empty($flash) && !empty($flash['message'])): ?>
      <?php $flashType = $flash['type'] ?? 'info'; ?>
      <div class="alert <?= $flashType === 'success' ? 'alert--success' : ($flashType === 'error' ? 'alert--error' : 'alert--info'); ?>">
        <?= htmlspecialchars($flash['message']); ?>
      </div>
    <?php endif; ?>

    <section class="card" aria-label="Resumen del perfil">
      <h2><span class="card-icon" aria-hidden="true"><?= $renderIcon('document-text', 'icon--md'); ?></span> Información principal</h2>
      <dl class="info-grid">
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true"><?= $renderIcon('user', 'icon--sm'); ?></span> Nombre completo</dt>
          <dd><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true"><?= $renderIcon('mail', 'icon--sm'); ?></span> Correo</dt>
          <dd><a href="mailto:<?= htmlspecialchars($user['correo']); ?>"><?= htmlspecialchars($user['correo']); ?></a></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true"><?= $renderIcon('shield-check', 'icon--sm'); ?></span> Rol</dt>
          <dd><?= htmlspecialchars($roleLabel); ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true"><?= $renderIcon('calendar', 'icon--sm'); ?></span> Miembro desde</dt>
          <dd><?= $createdAtFull ? htmlspecialchars($createdAtFull) : 'Sin registro'; ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true"><?= $renderIcon('check-circle', 'icon--sm'); ?></span> Estado</dt>
          <dd><?= $verifiedAt ? 'Verificada ' . htmlspecialchars($verifiedAt) : 'Verificación pendiente'; ?></dd>
        </div>
      </dl>
    </section>

    <section class="card" aria-label="Acciones rápidas">
      <h2><span class="card-icon" aria-hidden="true"><?= $renderIcon('sparkles', 'icon--md'); ?></span> Acciones rápidas</h2>
      <div class="quick-actions">
        <button class="btn" type="button" data-action="toggle-settings">
          <span class="btn__icon" aria-hidden="true"><?= $renderIcon('pencil-square', 'icon--sm'); ?></span>
          <span>Editar información</span>
        </button>
        <button class="btn" type="button" data-action="toggle-delete">
          <span class="btn__icon" aria-hidden="true"><?= $renderIcon('trash', 'icon--sm'); ?></span>
          <span>Eliminar cuenta</span>
        </button>
        <a class="btn" href="index.php">
          <span class="btn__icon" aria-hidden="true"><?= $renderIcon('home', 'icon--sm'); ?></span>
          <span>Volver al inicio</span>
        </a>
        <?php if ($role === 'administrador'): ?>
          <a class="btn" href="../administracion/index.php">
            <span class="btn__icon" aria-hidden="true"><?= $renderIcon('compass', 'icon--sm'); ?></span>
            <span>Ir al panel administrativo</span>
          </a>
        <?php endif; ?>
      </div>
    </section>

    <section class="card" id="settings-panel" aria-label="Editar perfil" hidden>
      <h2><span class="card-icon" aria-hidden="true"><?= $renderIcon('pencil-square', 'icon--md'); ?></span> Editar información personal</h2>
      <form action="perfil.php" method="post" novalidate>
        <input type="hidden" name="action" value="update" />
        <div class="form-grid">
          <div class="field">
            <label for="nombres">Nombres</label>
            <input id="nombres" name="nombre" value="<?= htmlspecialchars($user['nombre']); ?>" required />
          </div>
          <div class="field">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" value="<?= htmlspecialchars($user['apellidos']); ?>" required />
          </div>
          <div class="field">
            <label for="correo">Correo</label>
            <input id="correo" name="correo" type="email" value="<?= htmlspecialchars($user['correo']); ?>" required />
          </div>
          <div class="field">
            <label for="telefono">Teléfono</label>
            <input id="telefono" name="celular" value="<?= $phone ? htmlspecialchars($phone) : ''; ?>" placeholder="Opcional" />
          </div>
          <div class="field">
            <label for="password">Nueva contraseña</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Dejar en blanco para mantener" />
          </div>
          <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Repite tu nueva contraseña" />
          </div>
        </div>
        <div class="form-actions">
          <button class="btn" type="button" data-action="close-settings">
            <span class="btn__icon" aria-hidden="true"><?= $renderIcon('arrow-uturn-left', 'icon--sm'); ?></span>
            <span>Cancelar</span>
          </button>
          <button class="btn primary" type="submit">
            <span class="btn__icon" aria-hidden="true"><?= $renderIcon('save', 'icon--sm'); ?></span>
            <span>Guardar cambios</span>
          </button>
        </div>
      </form>
    </section>

    <section class="card card--danger" id="delete-panel" aria-label="Eliminar cuenta" hidden>
      <h2><span class="card-icon" aria-hidden="true"><?= $renderIcon('trash', 'icon--md'); ?></span> Eliminar cuenta</h2>
      <form action="perfil.php" method="post">
        <p>Esta acción no se puede deshacer. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
        <input type="hidden" name="action" value="delete" />
        <div class="field">
          <label for="confirmacion">Confirmación</label>
          <input id="confirmacion" name="confirmacion" placeholder="Escribe ELIMINAR" required />
        </div>
        <div class="form-actions">
          <button class="btn" type="button" data-action="close-delete">
            <span class="btn__icon" aria-hidden="true"><?= $renderIcon('arrow-uturn-left', 'icon--sm'); ?></span>
            <span>Cancelar</span>
          </button>
          <button class="btn danger" type="submit">
            <span class="btn__icon" aria-hidden="true"><?= $renderIcon('logout', 'icon--sm'); ?></span>
            <span>Cerrar mi cuenta</span>
          </button>
        </div>
      </form>
    </section>

  </main>

  <footer class="footer wrap">© <?= date('Y'); ?> Expediatravels — Perfil de usuario</footer>

  <script>
    const settingsPanel = document.getElementById('settings-panel');
    const deletePanel = document.getElementById('delete-panel');

    const toggleVisibility = (panel, force) => {
      if (!panel) return;
      const show = typeof force === 'boolean' ? force : panel.hasAttribute('hidden');
      if (show) {
        panel.removeAttribute('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        panel.setAttribute('hidden', '');
      }
    };

    document.querySelectorAll('[data-action="toggle-settings"]').forEach(btn => {
      btn.addEventListener('click', () => {
        toggleVisibility(deletePanel, false);
        toggleVisibility(settingsPanel, true);
      });
    });

    document.querySelectorAll('[data-action="close-settings"]').forEach(btn => {
      btn.addEventListener('click', () => toggleVisibility(settingsPanel, false));
    });

    document.querySelectorAll('[data-action="toggle-delete"]').forEach(btn => {
      btn.addEventListener('click', () => {
        toggleVisibility(settingsPanel, false);
        toggleVisibility(deletePanel, true);
      });
    });

    document.querySelectorAll('[data-action="close-delete"]').forEach(btn => {
      btn.addEventListener('click', () => toggleVisibility(deletePanel, false));
    });
  </script>
</body>
</html>
