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
$phoneLink = $phone ? preg_replace('/\s+/', '', (string) $phone) : null;
$displayName = $fullName !== '' ? $fullName : ($user['nombre'] ?? '');
$profilePhoto = $user['foto_perfil'] ?? null;
$coverPhoto = $user['foto_portada'] ?? null;
$coverStyle = $coverPhoto ? '--cover-image: url(' . json_encode($coverPhoto) . ');' : '';
$avatarStyle = $profilePhoto ? '--avatar-image: url(' . json_encode($profilePhoto) . ');' : '';
$coverClass = 'cover' . ($coverPhoto ? ' has-cover' : '');

$normalizeList = static function ($value): array {
    if (is_array($value)) {
        return $value;
    }

    if ($value instanceof \Traversable) {
        return iterator_to_array($value);
    }

    return [];
};

$upcomingTrips = $normalizeList($user['proximos_viajes'] ?? []);
$recentActivity = $normalizeList($user['actividad_reciente'] ?? []);
$recentReviews = $normalizeList($user['ultimas_resenas'] ?? []);
$reservations = $normalizeList($user['reservaciones'] ?? []);

$emojis = [
    'document-text' => '📄',
    'sparkles' => '✨',
    'mail' => '📧',
    'user' => '🧑',
    'shield-check' => '🛡️',
    'calendar' => '📅',
    'check-circle' => '✅',
    'clock' => '⏰',
    'shield-exclamation' => '⚠️',
    'pencil-square' => '✏️',
    'trash' => '🗑️',
    'home' => '🏠',
    'compass' => '🧭',
    'arrow-uturn-left' => '↩️',
    'save' => '💾',
    'logout' => '🚪',
    'photo' => '📸',
    'phone' => '📱',
];

$renderEmoji = static function (string $name, string $sizeClass = '') use ($emojis): string {
    $symbol = $emojis[$name] ?? '❔';
    $class = 'emoji';

    if ($sizeClass !== '') {
        $class .= ' ' . $sizeClass;
    }

    return '<span class="' . htmlspecialchars($class, ENT_QUOTES) . '" aria-hidden="true">' . htmlspecialchars($symbol, ENT_QUOTES) . '</span>';
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
      --bg: #f3f4ff;
      --bg-secondary: #e9edff;
      --surface: rgba(255, 255, 255, 0.95);
      --surface-strong: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --brand: #6366f1;
      --brand-alt: #38bdf8;
      --highlight: #f43f5e;
      --success: #22c55e;
      --danger: #dc2626;
      --radius: 24px;
      --shadow: 0 25px 60px rgba(99, 102, 241, 0.12);
      --ring: 0 0 0 3px rgba(99, 102, 241, .2);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
      color: var(--text);
      background: var(--bg);
      min-height: 100vh;
    }

    a { color: var(--brand); text-decoration: none; }
    a:hover { text-decoration: underline; }

    .wrap { max-width: 1100px; margin: 0 auto; padding: 24px; position: relative; }

    .top-actions {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 18px;
    }

    .logout-form {
      margin: 0;
    }

    .action-button {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: none;
      border-radius: 999px;
      padding: 10px 18px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, rgba(255, 255, 255, .85), rgba(255, 255, 255, .65));
      color: var(--brand);
      box-shadow: 0 15px 35px rgba(249, 115, 22, .2);
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .action-button--secondary {
      background: linear-gradient(135deg, rgba(15, 23, 42, .85), rgba(15, 23, 42, .65));
      color: #f8fafc;
      box-shadow: 0 18px 40px rgba(15, 23, 42, .25);
    }

    .action-button__emoji { font-size: 1.2rem; display: inline-flex; align-items: center; }

    .action-button:hover,
    .action-button:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 22px 45px rgba(249, 115, 22, .28);
      outline: none;
    }

    .action-button--secondary:hover,
    .action-button--secondary:focus-visible {
      box-shadow: 0 22px 50px rgba(15, 23, 42, .32);
    }

    .cover {
      position: relative;
      border-radius: var(--radius);
      padding: 48px 32px 32px;
      background-image: linear-gradient(135deg, rgba(56, 189, 248, .85) 0%, rgba(251, 191, 36, .85) 45%, rgba(244, 114, 182, .85) 100%);
      background-size: cover;
      background-position: center;
      box-shadow: var(--shadow);
      overflow: hidden;
      color: #fdf2f8;
    }

    .cover::after {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, .35) 0, transparent 60%),
        radial-gradient(circle at 80% 35%, rgba(255, 255, 255, .25) 0, transparent 60%),
        radial-gradient(circle at 50% 100%, rgba(56, 189, 248, .2) 0, transparent 70%);
      pointer-events: none;
      z-index: 0;
      opacity: .9;
    }

    .cover.has-cover {
      background-image: linear-gradient(135deg, rgba(15, 23, 42, .5) 0%, rgba(15, 23, 42, .6) 45%, rgba(15, 23, 42, .65) 100%), var(--cover-image);
      background-size: cover, cover;
      background-position: center, center;
    }

    .cover.has-cover::after { opacity: .45; }

    .cover-content { position: relative; z-index: 1; display: grid; gap: 28px; }

    .profile-head {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 24px;
    }

    .avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid rgba(255, 255, 255, .85);
      background-color: rgba(255, 255, 255, .25);
      background-image: var(--avatar-image, url('https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9?q=80&w=300&auto=format&fit=crop'));
      background-position: center;
      background-size: cover;
      box-shadow: var(--shadow);
      position: relative;
      cursor: pointer;
      display: flex;
      align-items: flex-end;
      justify-content: flex-end;
      transition: transform .2s ease, box-shadow .2s ease;
      text-decoration: none;
      color: inherit;
    }

    .avatar:hover,
    .avatar:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 22px 45px rgba(15, 23, 42, .28);
      outline: none;
    }

    .profile-data { display: grid; gap: 14px; min-width: 0; }

    .name-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .name {
      margin: 0;
      font-size: clamp(1.8rem, 3.2vw, 2.4rem);
      font-weight: 800;
      color: #fff7ed;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-shadow: 0 12px 30px rgba(15, 23, 42, .35);
    }

    .name__check {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #1877f2;
      box-shadow: 0 8px 22px rgba(24, 119, 242, .35);
    }

    .name__check-icon {
      width: 16px;
      height: 16px;
      fill: #fff;
    }

    .badge {
      padding: 6px 14px;
      border-radius: 999px;
      background: rgba(15, 23, 42, .25);
      color: #fff7ed;
      font-weight: 600;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .contact-chip__icon { display: inline-flex; align-items: center; justify-content: center; }

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
      background: rgba(255, 255, 255, .9);
      padding: 8px 16px;
      border-radius: 14px;
      box-shadow: 0 15px 30px rgba(15, 23, 42, .12);
      color: inherit;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .contact-chip:hover,
    .contact-chip:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 18px 36px rgba(15, 23, 42, .2);
      outline: none;
    }

    .summary {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .summary span {
      padding: 6px 14px;
      border-radius: 999px;
      background: rgba(15, 23, 42, .25);
      color: #fff7ed;
      font-weight: 600;
    }

    .profile-intro {
      margin: 0;
      color: #fff7ed;
      font-weight: 500;
      max-width: 520px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: none;
      border-radius: 14px;
      padding: 11px 18px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(255, 255, 255, .8));
      color: var(--text);
      box-shadow: var(--shadow);
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .btn.primary {
      background: linear-gradient(135deg, rgba(249, 115, 22, .95), rgba(236, 72, 153, .95));
      color: #fff7ed;
    }

    .btn.danger {
      background: linear-gradient(135deg, rgba(248, 113, 113, .95), rgba(220, 38, 38, .95));
      color: #fef2f2;
    }

    .btn:hover,
    .btn:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 20px 45px rgba(244, 114, 182, .25);
      outline: none;
    }

    .btn__icon { display: inline-flex; align-items: center; justify-content: center; }

    .emoji { display: inline-flex; align-items: center; justify-content: center; font-size: 1.25em; line-height: 1; }
    .emoji--lg { font-size: 1.6em; }
    .emoji--md { font-size: 1.35em; }
    .emoji--sm { font-size: 1em; }

    .edit-badge {
      position: absolute;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      background: rgba(15, 23, 42, .55);
      color: #f8fafc;
      border-radius: 999px;
      padding: 6px 12px;
      font-weight: 600;
      font-size: .95rem;
      opacity: 0;
      transform: translateY(6px);
      transition: opacity .2s ease, transform .2s ease;
      cursor: pointer;
    }

    .edit-badge:focus-visible { outline: none; box-shadow: var(--ring); opacity: 1; transform: translateY(0); }

    .edit-badge--cover {
      top: 24px;
      right: 24px;
      background: rgba(255, 255, 255, .28);
      color: #0f172a;
      border: 1px solid rgba(255, 255, 255, .4);
    }

    .cover:hover .edit-badge--cover,
    .cover:focus-within .edit-badge--cover {
      opacity: 1;
      transform: translateY(0);
    }

    .avatar .edit-badge {
      bottom: 10px;
      right: 10px;
      width: 40px;
      height: 40px;
      padding: 0;
      border-radius: 50%;
      transform: translateY(8px);
      background: rgba(15, 23, 42, .6);
    }

    .avatar:hover .edit-badge,
    .avatar:focus-visible .edit-badge {
      opacity: 1;
      transform: translateY(0);
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      border: 0;
    }

    main { display: grid; gap: 28px; margin-top: 36px; }

    .tabs {
      display: flex;
      align-items: center;
      gap: 12px;
      background: var(--surface);
      padding: 10px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      backdrop-filter: blur(12px);
    }

    .tab-button {
      flex: 1;
      border: none;
      border-radius: 14px;
      padding: 12px 16px;
      font-weight: 600;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .tab-button:hover,
    .tab-button:focus-visible {
      background: rgba(249, 115, 22, .15);
      color: var(--text);
      outline: none;
    }

    .tab-button.active {
      background: linear-gradient(135deg, rgba(249, 115, 22, .95), rgba(236, 72, 153, .95));
      color: #fff7ed;
      transform: translateY(-1px);
      box-shadow: var(--shadow);
    }

    .tab-panels { display: grid; gap: 24px; }
    .tab-panel[hidden] { display: none !important; }

    .tab-layout {
      display: grid;
      gap: 24px;
      grid-template-columns: minmax(0, 1.75fr) minmax(0, 1fr);
      align-items: start;
    }

    .tab-layout--single { grid-template-columns: minmax(0, 1fr); }
    .tab-column { display: grid; gap: 24px; align-content: start; }

    .card {
      background: var(--surface);
      border-radius: var(--radius);
      padding: 26px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
    }

    .card h2 {
      margin: 0 0 18px;
      font-size: 1.15rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--text);
    }

    .card-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 14px;
      background: linear-gradient(135deg, rgba(56, 189, 248, .18), rgba(244, 114, 182, .2));
      color: var(--brand);
      font-size: 1.4rem;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
    }

    .info-item { display: grid; gap: 6px; }

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
      width: 30px;
      height: 30px;
      border-radius: 10px;
      background: rgba(56, 189, 248, .18);
      color: var(--brand);
    }

    .info-item dd { margin: 0; font-weight: 600; font-size: 1.05rem; }

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

    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible {
      outline: none;
      box-shadow: var(--ring);
      border-color: rgba(59, 130, 246, .5);
    }

    .form-section {
      display: grid;
      gap: 16px;
      padding: 20px;
      border-radius: 18px;
      background: rgba(255, 255, 255, .7);
      border: 1px solid rgba(99, 102, 241, .14);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .4);
    }

    .form-section__header {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-section__title {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--brand);
    }

    .form-section__description {
      margin: 0;
      color: var(--muted);
      font-size: .9rem;
    }

    .form-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .field--full { grid-column: 1 / -1; }
    .form-actions { display: flex; flex-wrap: wrap; gap: 12px; }

    .list { display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; }
    .list-item { display: grid; gap: 6px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0; }
    .list-item:last-child { border-bottom: none; padding-bottom: 0; }
    .list-item__meta { color: var(--muted); font-size: .9rem; display: flex; gap: 8px; flex-wrap: wrap; }

    .empty-state {
      display: grid;
      gap: 8px;
      text-align: center;
      color: var(--muted);
      padding: 16px;
      border-radius: 16px;
      background: rgba(56, 189, 248, .12);
    }

    .upload-helper { color: var(--muted); font-size: .85rem; }

    .field--file { position: relative; }

    .file-preview {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-top: 10px;
      padding: 12px;
      border-radius: 16px;
      background: rgba(99, 102, 241, .08);
    }

    .file-preview__thumb {
      width: 80px;
      height: 80px;
      border-radius: 18px;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, .9);
      box-shadow: 0 14px 32px rgba(99, 102, 241, .18);
      background: #f8fafc;
    }

    .file-preview__meta { display: grid; gap: 6px; }
    .file-preview__name { font-weight: 600; font-size: .95rem; color: var(--text); }
    .file-preview__note { margin: 0; font-size: .85rem; color: var(--muted); }

    .file-preview__remove {
      border: none;
      padding: 0;
      background: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 600;
      color: var(--danger);
      cursor: pointer;
    }

    .file-preview__remove:hover,
    .file-preview__remove:focus-visible {
      text-decoration: underline;
      outline: none;
    }

    .card--danger { border: 1px solid rgba(220, 38, 38, .25); }

    .resumen-grid { display: grid; gap: 24px; }

    .insights-grid {
      display: grid;
      gap: 24px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      align-items: stretch;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      border-radius: 999px;
      font-size: .85rem;
      font-weight: 600;
      padding: 6px 14px;
      text-transform: capitalize;
      background: rgba(99, 102, 241, .12);
      color: #3730a3;
    }

    .status-badge--pendiente { background: rgba(245, 158, 11, .15); color: #b45309; }
    .status-badge--confirmada { background: rgba(34, 197, 94, .18); color: #15803d; }
    .status-badge--cancelada { background: rgba(239, 68, 68, .15); color: #b91c1c; }

    .table-scroll { overflow-x: auto; }

    .reservations-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 640px;
    }

    .reservations-table th,
    .reservations-table td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid rgba(15, 23, 42, .08);
      vertical-align: top;
    }

    .reservations-table th {
      font-size: .95rem;
      font-weight: 600;
      color: var(--muted);
    }

    .table-note {
      margin: 6px 0 0;
      color: var(--muted);
      font-size: .85rem;
      line-height: 1.4;
    }

    .footer { text-align: center; color: var(--muted); padding: 32px 0 24px; font-size: .9rem; }

    [hidden] { display: none !important; }

    @media (max-width: 768px) {
      .wrap { padding: 18px; }
      .top-actions { justify-content: center; }
      .cover { padding: 40px 24px 28px; }
      .avatar { width: 100px; height: 100px; }
      .form-section { padding: 18px; }
      .tab-layout { grid-template-columns: minmax(0, 1fr); }
      .tabs { flex-wrap: wrap; }
      .tab-button { flex: 1 1 calc(50% - 12px); }
      .insights-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
      .reservations-table { min-width: 520px; }
    }
  </style>
  <script src="scripts/modal-autenticacion.js" defer></script>
</head>
<body>
  <header class="wrap">
    <div class="top-actions">
      <a class="action-button" href="index.php">
        <span class="action-button__emoji"><?= $renderEmoji('home', 'emoji--sm'); ?></span>
        <span>Volver al inicio</span>
      </a>
      <?php if ($role === 'administrador'): ?>
        <a class="action-button" href="../administracion/index.php">
          <span class="action-button__emoji"><?= $renderEmoji('compass', 'emoji--sm'); ?></span>
          <span>Panel administrativo</span>
        </a>
      <?php endif; ?>
      <form class="logout-form" action="perfil.php" method="post">
        <input type="hidden" name="action" value="logout" />
        <button class="action-button action-button--secondary" type="submit">
          <span class="action-button__emoji"><?= $renderEmoji('logout', 'emoji--sm'); ?></span>
          <span>Cerrar sesión</span>
        </button>
      </form>
    </div>
    <div class="<?= htmlspecialchars($coverClass); ?>" role="img" aria-label="Portada del perfil de <?= htmlspecialchars($displayName); ?>"<?php if ($coverStyle !== ''): ?> style="<?= htmlspecialchars($coverStyle, ENT_QUOTES); ?>"<?php endif; ?>>
      <label class="edit-badge edit-badge--cover" for="foto_portada" role="button" tabindex="0" data-open-tab="ajustes" data-scroll-target="#foto_portada">
        <?= $renderEmoji('pencil-square', 'emoji--sm'); ?>
        <span class="sr-only">Editar foto de portada</span>
      </label>
      <div class="cover-content">
        <div class="profile-head">
          <label class="avatar" id="avatar" for="foto_perfil" role="button" tabindex="0" aria-label="Foto de perfil de <?= htmlspecialchars($displayName); ?>" data-open-tab="ajustes" data-scroll-target="#foto_perfil"<?php if ($avatarStyle !== ''): ?> style="<?= htmlspecialchars($avatarStyle, ENT_QUOTES); ?>"<?php endif; ?>>
            <span class="sr-only">Editar foto de perfil</span>
            <span class="edit-badge"><?= $renderEmoji('pencil-square', 'emoji--sm'); ?></span>
          </label>
          <div class="profile-data">
            <div class="name-row">
              <h1 class="name">
                <span><?= htmlspecialchars($displayName); ?></span>
                <?php if ($verifiedAt): ?>
                  <span class="name__check" title="Cuenta verificada" aria-label="Cuenta verificada">
                    <svg class="name__check-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <path d="M8.11 14.59a1 1 0 01-.7-.29l-2.4-2.38a1 1 0 111.4-1.43l1.66 1.63 4.24-4.22a1 1 0 111.41 1.42l-4.94 4.92a1 1 0 01-.67.35h-.01z" />
                    </svg>
                  </span>
                <?php endif; ?>
              </h1>
              <span class="badge" title="Rol del usuario"><?= htmlspecialchars($roleLabel); ?></span>
            </div>
            <p class="profile-intro">¡Hola <?= htmlspecialchars($displayName); ?>! Tu próxima aventura te espera. Mantén tu perfil al día para descubrir experiencias inolvidables.</p>
            <div class="contact-row">
              <a class="contact-chip" href="mailto:<?= htmlspecialchars($user['correo']); ?>">
                <span class="contact-chip__icon"><?= $renderEmoji('mail', 'emoji--sm'); ?></span>
                <span><?= htmlspecialchars($user['correo']); ?></span>
              </a>
            </div>
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

    <nav class="tabs" aria-label="Secciones del perfil">
      <button class="tab-button active" type="button" data-tab-button="resumen">Resumen</button>
      <button class="tab-button" type="button" data-tab-button="viajes">Viajes</button>
      <button class="tab-button" type="button" data-tab-button="reservaciones">Reservaciones</button>
      <button class="tab-button" type="button" data-tab-button="resenas">Reseñas</button>
      <button class="tab-button" type="button" data-tab-button="ajustes">Ajustes</button>
    </nav>

    <div class="tab-panels">
      <section class="tab-panel" data-tab-panel="resumen">
        <div class="resumen-grid">
          <section class="card" aria-label="Resumen del perfil">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('document-text', 'emoji--md'); ?></span> Información principal</h2>
            <dl class="info-grid">
              <div class="info-item">
                <dt><span class="info-item__icon" aria-hidden="true"><?= $renderEmoji('user', 'emoji--sm'); ?></span> Nombre completo</dt>
                <dd><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></dd>
              </div>
              <div class="info-item">
                <dt><span class="info-item__icon" aria-hidden="true"><?= $renderEmoji('mail', 'emoji--sm'); ?></span> Correo</dt>
                <dd><a href="mailto:<?= htmlspecialchars($user['correo']); ?>"><?= htmlspecialchars($user['correo']); ?></a></dd>
              </div>
              <div class="info-item">
                <dt><span class="info-item__icon" aria-hidden="true"><?= $renderEmoji('shield-check', 'emoji--sm'); ?></span> Rol</dt>
                <dd><?= htmlspecialchars($roleLabel); ?></dd>
              </div>
              <div class="info-item">
                <dt><span class="info-item__icon" aria-hidden="true"><?= $renderEmoji('calendar', 'emoji--sm'); ?></span> Miembro desde</dt>
                <dd><?= $createdAtFull ? htmlspecialchars($createdAtFull) : 'Sin registro'; ?></dd>
              </div>
              <div class="info-item">
                <dt><span class="info-item__icon" aria-hidden="true"><?= $renderEmoji('check-circle', 'emoji--sm'); ?></span> Estado</dt>
                <dd><?= $verifiedAt ? 'Verificada ' . htmlspecialchars($verifiedAt) : 'Verificación pendiente'; ?></dd>
              </div>
            </dl>
          </section>

          <div class="insights-grid">
            <section class="card" aria-label="Próximos viajes">
              <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('calendar', 'emoji--md'); ?></span> Próximos viajes</h2>
              <?php if ($upcomingTrips): ?>
                <ul class="list">
                  <?php foreach ($upcomingTrips as $trip): ?>
                    <?php
                      $tripTitle = is_array($trip) ? ($trip['titulo'] ?? $trip['destino'] ?? 'Viaje programado') : (string) $trip;
                      $tripDate = is_array($trip) ? ($trip['fecha'] ?? null) : null;
                      $tripStatus = is_array($trip) ? ($trip['estado'] ?? null) : null;
                      $tripNotes = is_array($trip) ? ($trip['descripcion'] ?? $trip['notas'] ?? null) : null;
                    ?>
                    <li class="list-item">
                      <strong><?= htmlspecialchars($tripTitle); ?></strong>
                      <?php if ($tripDate || $tripStatus): ?>
                        <span class="list-item__meta">
                          <?php if ($tripDate): ?><span><?= htmlspecialchars($tripDate); ?></span><?php endif; ?>
                          <?php if ($tripStatus): ?><span><?= htmlspecialchars($tripStatus); ?></span><?php endif; ?>
                        </span>
                      <?php endif; ?>
                      <?php if ($tripNotes): ?><span><?= htmlspecialchars($tripNotes); ?></span><?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="empty-state">
                  <span aria-hidden="true"><?= $renderEmoji('calendar', 'emoji--md'); ?></span>
                  <span>No hay viajes próximos programados todavía.</span>
                </div>
              <?php endif; ?>
            </section>

            <section class="card" aria-label="Actividad reciente">
              <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('clock', 'emoji--md'); ?></span> Actividad reciente</h2>
              <?php if ($recentActivity): ?>
                <ul class="list">
                  <?php foreach ($recentActivity as $activity): ?>
                    <?php
                      $activityTitle = is_array($activity) ? ($activity['evento'] ?? $activity['titulo'] ?? 'Actualización') : (string) $activity;
                      $activityDate = is_array($activity) ? ($activity['fecha'] ?? null) : null;
                      $activityDetails = is_array($activity) ? ($activity['detalle'] ?? $activity['descripcion'] ?? null) : null;
                    ?>
                    <li class="list-item">
                      <strong><?= htmlspecialchars($activityTitle); ?></strong>
                      <?php if ($activityDate): ?><span class="list-item__meta"><span><?= htmlspecialchars($activityDate); ?></span></span><?php endif; ?>
                      <?php if ($activityDetails): ?><span><?= htmlspecialchars($activityDetails); ?></span><?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="empty-state">
                  <span aria-hidden="true"><?= $renderEmoji('clock', 'emoji--md'); ?></span>
                  <span>Sin actividad registrada recientemente.</span>
                </div>
              <?php endif; ?>
            </section>

            <section class="card" aria-label="Últimas reseñas">
              <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('sparkles', 'emoji--md'); ?></span> Últimas reseñas</h2>
              <?php if ($recentReviews): ?>
                <ul class="list">
                  <?php foreach ($recentReviews as $review): ?>
                    <?php
                      $reviewTitle = is_array($review) ? ($review['destino'] ?? $review['titulo'] ?? 'Reseña') : (string) $review;
                      $reviewRating = is_array($review) ? ($review['puntuacion'] ?? null) : null;
                      $reviewDate = is_array($review) ? ($review['fecha'] ?? null) : null;
                      $reviewBody = is_array($review) ? ($review['comentario'] ?? $review['descripcion'] ?? null) : null;
                    ?>
                    <li class="list-item">
                      <strong><?= htmlspecialchars($reviewTitle); ?></strong>
                      <span class="list-item__meta">
                        <?php if ($reviewRating !== null && $reviewRating !== ''): ?><span><?= htmlspecialchars('⭐ ' . $reviewRating . '/5'); ?></span><?php endif; ?>
                        <?php if ($reviewDate): ?><span><?= htmlspecialchars($reviewDate); ?></span><?php endif; ?>
                      </span>
                      <?php if ($reviewBody): ?><span><?= htmlspecialchars($reviewBody); ?></span><?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="empty-state">
                  <span aria-hidden="true"><?= $renderEmoji('sparkles', 'emoji--md'); ?></span>
                  <span>No se han publicado reseñas recientes.</span>
                </div>
              <?php endif; ?>
            </section>
          </div>
        </div>
      </section>

      <section class="tab-panel" data-tab-panel="viajes" hidden>
        <div class="tab-layout tab-layout--single">
          <section class="card" aria-label="Historial de viajes">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('compass', 'emoji--md'); ?></span> Tus viajes</h2>
            <?php if ($upcomingTrips): ?>
              <ul class="list">
                <?php foreach ($upcomingTrips as $trip): ?>
                  <?php
                    $tripTitle = is_array($trip) ? ($trip['titulo'] ?? $trip['destino'] ?? 'Viaje programado') : (string) $trip;
                    $tripDate = is_array($trip) ? ($trip['fecha'] ?? null) : null;
                    $tripStatus = is_array($trip) ? ($trip['estado'] ?? null) : null;
                    $tripNotes = is_array($trip) ? ($trip['descripcion'] ?? $trip['notas'] ?? null) : null;
                  ?>
                  <li class="list-item">
                    <strong><?= htmlspecialchars($tripTitle); ?></strong>
                    <span class="list-item__meta">
                      <?php if ($tripDate): ?><span><?= htmlspecialchars('Salida: ' . $tripDate); ?></span><?php endif; ?>
                      <?php if ($tripStatus): ?><span><?= htmlspecialchars('Estado: ' . $tripStatus); ?></span><?php endif; ?>
                    </span>
                    <?php if ($tripNotes): ?><span><?= htmlspecialchars($tripNotes); ?></span><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="empty-state">
                <span aria-hidden="true"><?= $renderEmoji('compass', 'emoji--md'); ?></span>
                <span>Aún no tienes viajes programados. ¡Explora nuevos destinos!</span>
              </div>
            <?php endif; ?>
          </section>
        </div>
      </section>

      <section class="tab-panel" data-tab-panel="reservaciones" hidden>
        <div class="tab-layout tab-layout--single">
          <section class="card" aria-label="Reservaciones realizadas">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('document-text', 'emoji--md'); ?></span> Tus reservaciones</h2>
            <?php if ($reservations): ?>
              <div class="table-scroll" role="region" aria-live="polite">
                <table class="reservations-table">
                  <thead>
                    <tr>
                      <th scope="col">Experiencia</th>
                      <th scope="col">Fecha de viaje</th>
                      <th scope="col">Estado</th>
                      <th scope="col">Personas</th>
                      <th scope="col">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                      <?php
                        $packageTitle = is_array($reservation) ? ($reservation['paquete'] ?? 'Reserva') : (string) $reservation;
                        $reservationDate = is_array($reservation) ? ($reservation['fecha'] ?? null) : null;
                        $reservationStatus = is_array($reservation) ? ($reservation['estado'] ?? '') : '';
                        $reservationSlug = preg_replace('/[^a-z0-9_-]/i', '-', (string) (is_array($reservation) ? ($reservation['estado_slug'] ?? $reservationStatus) : $reservationStatus));
                        $reservationPeople = is_array($reservation) ? ($reservation['personas'] ?? null) : null;
                        $reservationTotal = is_array($reservation) ? ($reservation['total'] ?? null) : null;
                        $reservationSummary = is_array($reservation) ? ($reservation['resumen'] ?? null) : null;
                        $reservationDuration = is_array($reservation) ? ($reservation['duracion'] ?? null) : null;
                        $reservationCreated = is_array($reservation) ? ($reservation['creado_en'] ?? null) : null;
                      ?>
                      <tr>
                        <td>
                          <strong><?= htmlspecialchars($packageTitle); ?></strong>
                          <?php if ($reservationSummary): ?><p class="table-note"><?= htmlspecialchars($reservationSummary); ?></p><?php endif; ?>
                          <?php if ($reservationDuration): ?><p class="table-note">Duración: <?= htmlspecialchars($reservationDuration); ?></p><?php endif; ?>
                          <?php if ($reservationCreated): ?><p class="table-note">Reservado el <?= htmlspecialchars($reservationCreated); ?></p><?php endif; ?>
                        </td>
                        <td><?= $reservationDate ? htmlspecialchars($reservationDate) : '—'; ?></td>
                        <td>
                          <?php if ($reservationStatus !== ''): ?>
                            <span class="status-badge status-badge--<?= htmlspecialchars(strtolower($reservationSlug)); ?>"><?= htmlspecialchars($reservationStatus); ?></span>
                          <?php else: ?>
                            —
                          <?php endif; ?>
                        </td>
                        <td><?= $reservationPeople !== null ? htmlspecialchars((string) $reservationPeople) : '—'; ?></td>
                        <td><?= $reservationTotal !== null ? 'S/ ' . htmlspecialchars($reservationTotal) : '—'; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <span aria-hidden="true"><?= $renderEmoji('calendar', 'emoji--md'); ?></span>
                <span>Todavía no registras reservaciones con nosotros.</span>
              </div>
            <?php endif; ?>
          </section>
        </div>
      </section>

      <section class="tab-panel" data-tab-panel="resenas" hidden>
        <div class="tab-layout tab-layout--single">
          <section class="card" aria-label="Reseñas realizadas">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('sparkles', 'emoji--md'); ?></span> Historial de reseñas</h2>
            <?php if ($recentReviews): ?>
              <ul class="list">
                <?php foreach ($recentReviews as $review): ?>
                  <?php
                    $reviewTitle = is_array($review) ? ($review['destino'] ?? $review['titulo'] ?? 'Reseña') : (string) $review;
                    $reviewRating = is_array($review) ? ($review['puntuacion'] ?? null) : null;
                    $reviewDate = is_array($review) ? ($review['fecha'] ?? null) : null;
                    $reviewBody = is_array($review) ? ($review['comentario'] ?? $review['descripcion'] ?? null) : null;
                  ?>
                  <li class="list-item">
                    <strong><?= htmlspecialchars($reviewTitle); ?></strong>
                    <span class="list-item__meta">
                      <?php if ($reviewRating !== null && $reviewRating !== ''): ?><span><?= htmlspecialchars('⭐ ' . $reviewRating . '/5'); ?></span><?php endif; ?>
                      <?php if ($reviewDate): ?><span><?= htmlspecialchars($reviewDate); ?></span><?php endif; ?>
                    </span>
                    <?php if ($reviewBody): ?><span><?= htmlspecialchars($reviewBody); ?></span><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="empty-state">
                <span aria-hidden="true"><?= $renderEmoji('sparkles', 'emoji--md'); ?></span>
                <span>Todavía no has dejado reseñas en tus viajes.</span>
              </div>
            <?php endif; ?>
          </section>
        </div>
      </section>

      <section class="tab-panel" data-tab-panel="ajustes" hidden>
        <div class="tab-layout tab-layout--single">
          <section class="card" aria-label="Editar perfil" id="ajustes-form">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('pencil-square', 'emoji--md'); ?></span> Editar información personal</h2>
            <form action="perfil.php" method="post" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="action" value="update" />
              <div class="form-section">
                <div class="form-section__header">
                  <h3 class="form-section__title"><?= $renderEmoji('user', 'emoji--sm'); ?> Información básica</h3>
                  <p class="form-section__description">Actualiza tu nombre y apellidos tal como deseas que aparezcan en tu perfil.</p>
                </div>
                <div class="form-grid">
                  <div class="field">
                    <label for="nombres">Nombres</label>
                    <input id="nombres" name="nombre" value="<?= htmlspecialchars($user['nombre']); ?>" required />
                  </div>
                  <div class="field">
                    <label for="apellidos">Apellidos</label>
                    <input id="apellidos" name="apellidos" value="<?= htmlspecialchars($user['apellidos']); ?>" required />
                  </div>
                </div>
              </div>

              <div class="form-section">
                <div class="form-section__header">
                  <h3 class="form-section__title"><?= $renderEmoji('mail', 'emoji--sm'); ?> Contacto</h3>
                  <p class="form-section__description">Mantén tu correo y número de teléfono actualizados para recibir notificaciones importantes.</p>
                </div>
                <div class="form-grid">
                  <div class="field">
                    <label for="correo">Correo</label>
                    <input id="correo" name="correo" type="email" value="<?= htmlspecialchars($user['correo']); ?>" required />
                  </div>
                  <div class="field">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="celular" value="<?= $phone ? htmlspecialchars($phone) : ''; ?>" placeholder="Opcional" />
                  </div>
                </div>
              </div>

              <div class="form-section">
                <div class="form-section__header">
                  <h3 class="form-section__title"><?= $renderEmoji('photo', 'emoji--sm'); ?> Fotografías</h3>
                  <p class="form-section__description">Elige imágenes que representen tu estilo. Obtendrás una vista previa antes de guardar los cambios.</p>
                </div>
                <div class="form-grid">
                  <div class="field field--full field--file"
                    data-file-field
                    data-preview-alt="Vista previa de la foto de portada"
                    data-selected-note="Se actualizará tu portada al guardar los cambios."
                    data-current-note="Esta es la imagen que se muestra actualmente en tu portada."
                    data-current-label="Imagen de portada actual"
                    data-selected-label="Imagen seleccionada"
                    <?php if ($coverPhoto): ?>data-current-image="<?= htmlspecialchars($coverPhoto, ENT_QUOTES); ?>"<?php endif; ?>>
                    <label for="foto_portada">Foto de portada</label>
                    <input id="foto_portada" name="foto_portada" type="file" accept="image/*" />
                    <span class="upload-helper">Sube una imagen en formato JPG o PNG para personalizar la portada.</span>
                    <div class="file-preview" data-file-preview hidden>
                      <img class="file-preview__thumb" src="" alt="" data-file-preview-image />
                      <div class="file-preview__meta">
                        <span class="file-preview__name" data-file-preview-name></span>
                        <p class="file-preview__note" data-file-preview-note></p>
                        <button type="button" class="file-preview__remove" data-file-preview-remove hidden>
                          <?= $renderEmoji('trash', 'emoji--sm'); ?>
                          <span>Quitar imagen seleccionada</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="field field--full field--file"
                    data-file-field
                    data-preview-alt="Vista previa de la foto de perfil"
                    data-selected-note="Tu foto de perfil cambiará al guardar."
                    data-current-note="Esta es tu foto de perfil actual."
                    data-current-label="Foto de perfil actual"
                    data-selected-label="Imagen seleccionada"
                    <?php if ($profilePhoto): ?>data-current-image="<?= htmlspecialchars($profilePhoto, ENT_QUOTES); ?>"<?php endif; ?>>
                    <label for="foto_perfil">Foto de perfil</label>
                    <input id="foto_perfil" name="foto_perfil" type="file" accept="image/*" />
                    <span class="upload-helper">Elige una imagen cuadrada para una mejor visualización.</span>
                    <div class="file-preview" data-file-preview hidden>
                      <img class="file-preview__thumb" src="" alt="" data-file-preview-image />
                      <div class="file-preview__meta">
                        <span class="file-preview__name" data-file-preview-name></span>
                        <p class="file-preview__note" data-file-preview-note></p>
                        <button type="button" class="file-preview__remove" data-file-preview-remove hidden>
                          <?= $renderEmoji('trash', 'emoji--sm'); ?>
                          <span>Quitar imagen seleccionada</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-section">
                <div class="form-section__header">
                  <h3 class="form-section__title"><?= $renderEmoji('shield-check', 'emoji--sm'); ?> Seguridad</h3>
                  <p class="form-section__description">Cambia tu contraseña cuando lo necesites. Déjalo en blanco si deseas conservar la actual.</p>
                </div>
                <div class="form-grid">
                  <div class="field">
                    <label for="password">Nueva contraseña</label>
                    <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Dejar en blanco para mantener" />
                  </div>
                  <div class="field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Repite tu nueva contraseña" />
                  </div>
                </div>
              </div>
              <div class="form-actions">
                <button class="btn primary" type="submit">
                  <span class="btn__icon" aria-hidden="true"><?= $renderEmoji('save', 'emoji--sm'); ?></span>
                  <span>Guardar cambios</span>
                </button>
              </div>
            </form>
          </section>

          <section class="card card--danger" aria-label="Eliminar cuenta" id="eliminar-cuenta">
            <h2><span class="card-icon" aria-hidden="true"><?= $renderEmoji('trash', 'emoji--md'); ?></span> Eliminar cuenta</h2>
            <form action="perfil.php" method="post">
              <p>Esta acción no se puede deshacer. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
              <input type="hidden" name="action" value="delete" />
              <div class="field">
                <label for="confirmacion">Confirmación</label>
                <input id="confirmacion" name="confirmacion" placeholder="Escribe ELIMINAR" required />
              </div>
              <div class="form-actions">
                <button class="btn danger" type="submit">
                  <span class="btn__icon" aria-hidden="true"><?= $renderEmoji('logout', 'emoji--sm'); ?></span>
                  <span>Cerrar mi cuenta</span>
                </button>
              </div>
            </form>
          </section>
        </div>
      </section>
    </div>
  </main>

  <footer class="footer wrap">© <?= date('Y'); ?> Expediatravels — Perfil de usuario</footer>

  <script>
    const tabButtons = Array.from(document.querySelectorAll('[data-tab-button]'));
    const tabPanels = Array.from(document.querySelectorAll('[data-tab-panel]'));

    const activateTab = (tabId) => {
      tabButtons.forEach((button) => {
        const isActive = button.dataset.tabButton === tabId;
        button.classList.toggle('active', isActive);
        if (isActive) {
          button.setAttribute('aria-current', 'page');
        } else {
          button.removeAttribute('aria-current');
        }
      });

      tabPanels.forEach((panel) => {
        const shouldShow = panel.dataset.tabPanel === tabId;
        if (shouldShow) {
          panel.removeAttribute('hidden');
        } else {
          panel.setAttribute('hidden', '');
        }
      });
    };

    tabButtons.forEach((button) => {
      button.addEventListener('click', () => activateTab(button.dataset.tabButton));
    });

    document.querySelectorAll('[data-open-tab]').forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const tabId = trigger.dataset.openTab;
        if (!tabId) return;
        activateTab(tabId);

        const scrollTarget = trigger.dataset.scrollTarget;
        if (scrollTarget) {
          const targetElement = document.querySelector(scrollTarget);
          if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      });
    });

    const defaultTabButton = document.querySelector('[data-tab-button].active') || tabButtons[0];
    if (defaultTabButton) {
      activateTab(defaultTabButton.dataset.tabButton);
    }

    const fileFields = Array.from(document.querySelectorAll('[data-file-field]'));

    fileFields.forEach((field) => {
      const input = field.querySelector('input[type="file"]');
      const preview = field.querySelector('[data-file-preview]');

      if (!input || !preview) {
        return;
      }

      const image = preview.querySelector('[data-file-preview-image]');
      const name = preview.querySelector('[data-file-preview-name]');
      const note = preview.querySelector('[data-file-preview-note]');
      const removeButton = preview.querySelector('[data-file-preview-remove]');
      const previewAlt = field.dataset.previewAlt || 'Vista previa de la imagen seleccionada';
      const selectedNote = field.dataset.selectedNote || 'La imagen se actualizará al guardar los cambios.';
      const currentNote = field.dataset.currentNote || 'Esta es la imagen que se muestra actualmente.';
      const currentLabel = field.dataset.currentLabel || 'Imagen actual';
      const selectedLabel = field.dataset.selectedLabel || 'Imagen seleccionada';
      const currentImage = field.dataset.currentImage || '';
      let objectUrl = null;

      const setRemoveVisibility = (visible) => {
        if (!removeButton) {
          return;
        }

        removeButton.hidden = !visible;
      };

      const hidePreview = () => {
        if (!preview.hidden) {
          preview.hidden = true;
        }

        field.classList.remove('has-preview');
        setRemoveVisibility(false);

        if (name) {
          name.textContent = '';
        }

        if (note) {
          note.textContent = '';
        }

        if (image) {
          image.removeAttribute('src');
        }

        if (objectUrl) {
          URL.revokeObjectURL(objectUrl);
          objectUrl = null;
        }
      };

      const showPreview = (src, label, noteText, { canRemove = false, isObjectUrl = false } = {}) => {
        if (!image) {
          return;
        }

        if (objectUrl && objectUrl !== src) {
          URL.revokeObjectURL(objectUrl);
          objectUrl = null;
        }

        if (isObjectUrl) {
          objectUrl = src;
        }

        image.src = src;
        image.alt = previewAlt;

        if (name) {
          name.textContent = label;
        }

        if (note) {
          note.textContent = noteText;
        }

        preview.hidden = false;
        field.classList.add('has-preview');
        setRemoveVisibility(canRemove);
      };

      const showExistingImage = () => {
        if (!currentImage) {
          hidePreview();
          return;
        }

        showPreview(currentImage, currentLabel, currentNote, { canRemove: false, isObjectUrl: false });
      };

      const resetSelection = () => {
        if (objectUrl) {
          URL.revokeObjectURL(objectUrl);
          objectUrl = null;
        }

        input.value = '';

        if (currentImage) {
          showExistingImage();
        } else {
          hidePreview();
        }
      };

      input.addEventListener('change', () => {
        const file = input.files && input.files[0];

        if (file) {
          const url = URL.createObjectURL(file);
          const label = `${selectedLabel}: ${file.name}`;
          showPreview(url, label, selectedNote, { canRemove: true, isObjectUrl: true });
        } else if (currentImage) {
          showExistingImage();
        } else {
          hidePreview();
        }
      });

      if (removeButton) {
        removeButton.addEventListener('click', () => {
          resetSelection();
          input.dispatchEvent(new Event('change', { bubbles: true }));
          input.focus();
        });
      }

      if (currentImage) {
        showExistingImage();
      }
    });
  </script>
</body>
</html>
