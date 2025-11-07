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
    }

    .badge {
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .15);
      color: #e0f2fe;
      font-weight: 600;
      letter-spacing: .02em;
    }

    .status-check {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 999px;
      background: #22c55e;
      color: #f0fdf4;
      box-shadow: 0 10px 25px rgba(34, 197, 94, .45);
    }

    .status-check svg {
      width: 16px;
      height: 16px;
    }

    .status-line {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      color: rgba(241, 245, 249, .92);
      font-weight: 600;
    }

    .status-pill {
      background: rgba(15, 23, 42, .35);
      padding: 4px 12px;
      border-radius: 999px;
      color: #e0f2fe;
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
      font-size: 1rem;
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
      width: 34px;
      height: 34px;
      border-radius: 12px;
      background: rgba(14, 165, 233, .15);
      color: var(--brand);
      font-size: 1.1rem;
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
      width: 24px;
      height: 24px;
      border-radius: 8px;
      background: rgba(14, 165, 233, .15);
      color: var(--brand);
      font-size: .85rem;
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
          <span class="btn__icon" aria-hidden="true">⎋</span>
          <span>Cerrar sesión</span>
        </button>
      </div>
      <div class="profile-head">
        <div class="avatar" id="avatar" aria-label="Foto de perfil de <?= htmlspecialchars($displayName); ?>"<?php if ($avatarStyle !== ''): ?> style="<?= htmlspecialchars($avatarStyle, ENT_QUOTES); ?>"<?php endif; ?>></div>
        <div class="profile-data">
          <div class="name-row">
            <h1 class="name"><?= htmlspecialchars($displayName); ?></h1>
            <span class="badge" title="Rol del usuario"><?= htmlspecialchars($roleLabel); ?></span>
            <?php if ($verifiedAt): ?>
              <span class="status-check" title="Cuenta verificada" aria-label="Cuenta verificada">
                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M13.78 4.72a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 1 1 1.06-1.06L6.75 10.19l5.97-5.97a.75.75 0 0 1 1.06 0Z" fill="currentColor" />
                </svg>
              </span>
            <?php endif; ?>
          </div>
          <div class="status-line">
            <?php if ($createdAt !== null): ?>
              <span class="status-pill">Miembro desde <?= htmlspecialchars($createdAt); ?></span>
            <?php endif; ?>
            <?php if (!$verifiedAt): ?>
              <span class="status-pill">Verificación pendiente</span>
            <?php endif; ?>
          </div>
          <div class="contact-row">
            <a class="contact-chip" href="mailto:<?= htmlspecialchars($user['correo']); ?>">
              <span class="contact-chip__icon" aria-hidden="true">✉️</span>
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
      <h2><span class="card-icon" aria-hidden="true">📋</span> Información principal</h2>
      <dl class="info-grid">
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">👤</span> Nombre completo</dt>
          <dd><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">✉️</span> Correo</dt>
          <dd><a href="mailto:<?= htmlspecialchars($user['correo']); ?>"><?= htmlspecialchars($user['correo']); ?></a></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">📱</span> Teléfono</dt>
          <dd><?= $phone ? htmlspecialchars($phone) : 'Sin número registrado'; ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">🛡️</span> Rol</dt>
          <dd><?= htmlspecialchars($roleLabel); ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">🆔</span> ID de usuario</dt>
          <dd>#<?= htmlspecialchars((string) $user['id']); ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">📅</span> Miembro desde</dt>
          <dd><?= $createdAtFull ? htmlspecialchars($createdAtFull) : 'Sin registro'; ?></dd>
        </div>
        <div class="info-item">
          <dt><span class="info-item__icon" aria-hidden="true">✅</span> Estado</dt>
          <dd><?= $verifiedAt ? 'Verificada ' . htmlspecialchars($verifiedAt) : 'Verificación pendiente'; ?></dd>
        </div>
      </dl>
    </section>

    <section class="card" aria-label="Acciones rápidas">
      <h2><span class="card-icon" aria-hidden="true">⚡</span> Acciones rápidas</h2>
      <div class="quick-actions">
        <button class="btn" type="button" data-action="toggle-settings">
          <span class="btn__icon" aria-hidden="true">✏️</span>
          <span>Editar información</span>
        </button>
        <button class="btn" type="button" data-action="toggle-delete">
          <span class="btn__icon" aria-hidden="true">🗑️</span>
          <span>Eliminar cuenta</span>
        </button>
        <a class="btn" href="index.php">
          <span class="btn__icon" aria-hidden="true">🏠</span>
          <span>Volver al inicio</span>
        </a>
        <?php if ($role === 'administrador'): ?>
          <a class="btn" href="../administracion/index.php">
            <span class="btn__icon" aria-hidden="true">🧭</span>
            <span>Ir al panel administrativo</span>
          </a>
        <?php endif; ?>
      </div>
    </section>

    <section class="card" id="settings-panel" aria-label="Editar perfil" hidden>
      <h2><span class="card-icon" aria-hidden="true">🛠️</span> Editar información personal</h2>
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
            <span class="btn__icon" aria-hidden="true">↩️</span>
            <span>Cancelar</span>
          </button>
          <button class="btn primary" type="submit">
            <span class="btn__icon" aria-hidden="true">💾</span>
            <span>Guardar cambios</span>
          </button>
        </div>
      </form>
    </section>

    <section class="card card--danger" id="delete-panel" aria-label="Eliminar cuenta" hidden>
      <h2><span class="card-icon" aria-hidden="true">🗑️</span> Eliminar cuenta</h2>
      <form action="perfil.php" method="post">
        <p>Esta acción no se puede deshacer. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
        <input type="hidden" name="action" value="delete" />
        <div class="field">
          <label for="confirmacion">Confirmación</label>
          <input id="confirmacion" name="confirmacion" placeholder="Escribe ELIMINAR" required />
        </div>
        <div class="form-actions">
          <button class="btn" type="button" data-action="close-delete">
            <span class="btn__icon" aria-hidden="true">↩️</span>
            <span>Cancelar</span>
          </button>
          <button class="btn danger" type="submit">
            <span class="btn__icon" aria-hidden="true">🚪</span>
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
