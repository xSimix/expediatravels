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
      background: linear-gradient(135deg, #7dd3fc 0%, #60a5fa 45%, #4f46e5 100%);
      box-shadow: var(--shadow);
      overflow: hidden;
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
      background: #dbeafe url('https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9?q=80&w=300&auto=format&fit=crop') center / cover no-repeat;
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

    .status-line {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      color: rgba(241, 245, 249, .92);
      font-weight: 600;
    }

    .status-line span { background: rgba(15, 23, 42, .35); padding: 4px 12px; border-radius: 999px; color: #e0f2fe; }

    .contact-row {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      font-weight: 600;
      color: rgba(15, 23, 42, .92);
    }

    .contact-row span { background: rgba(255, 255, 255, .85); padding: 6px 12px; border-radius: 12px; }

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
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
    }

    .info-item { display: grid; gap: 4px; }
    .info-item dt { font-size: .85rem; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
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
      .contact-row span { font-size: .95rem; }
    }
  </style>
  <script src="scripts/modal-autenticacion.js" defer></script>
</head>
<body>
  <header class="wrap">
    <div class="cover" role="img" aria-label="Portada con degradado">
      <div class="cover-actions">
        <button class="btn" type="button" id="btn-cover">Cambiar portada</button>
        <button class="btn" type="button" data-action="toggle-settings">Editar perfil</button>
        <a class="btn" href="index.php">Inicio</a>
        <?php if ($role === 'administrador'): ?>
          <a class="btn" href="../administracion/index.php">Panel administrativo</a>
        <?php endif; ?>
        <button class="btn primary" type="button" data-auth-logout>Cerrar sesión</button>
      </div>
      <div class="profile-head">
        <div class="avatar" id="avatar" aria-label="Foto de perfil"></div>
        <div class="profile-data">
          <div class="name-row">
            <h1 class="name"><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></h1>
            <span class="badge" title="Rol del usuario"><?= htmlspecialchars($roleLabel); ?></span>
          </div>
          <div class="status-line">
            <?php if ($createdAt !== null): ?>
              <span>Miembro desde <?= htmlspecialchars($createdAt); ?></span>
            <?php endif; ?>
            <span><?= $verifiedAt ? 'Cuenta verificada' : 'Verificación pendiente'; ?></span>
            <span>ID #<?= htmlspecialchars((string) $user['id']); ?></span>
          </div>
          <div class="contact-row">
            <span>📧 <?= htmlspecialchars($user['correo']); ?></span>
            <span>☎ <?= $phone ? htmlspecialchars($phone) : 'Sin número registrado'; ?></span>
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
      <h2>Información principal</h2>
      <dl class="info-grid">
        <div class="info-item">
          <dt>Nombre completo</dt>
          <dd><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></dd>
        </div>
        <div class="info-item">
          <dt>Correo</dt>
          <dd><a href="mailto:<?= htmlspecialchars($user['correo']); ?>"><?= htmlspecialchars($user['correo']); ?></a></dd>
        </div>
        <div class="info-item">
          <dt>Teléfono</dt>
          <dd><?= $phone ? htmlspecialchars($phone) : 'Sin número registrado'; ?></dd>
        </div>
        <div class="info-item">
          <dt>Rol</dt>
          <dd><?= htmlspecialchars($roleLabel); ?></dd>
        </div>
        <div class="info-item">
          <dt>ID de usuario</dt>
          <dd>#<?= htmlspecialchars((string) $user['id']); ?></dd>
        </div>
        <div class="info-item">
          <dt>Miembro desde</dt>
          <dd><?= $createdAtFull ? htmlspecialchars($createdAtFull) : 'Sin registro'; ?></dd>
        </div>
        <div class="info-item">
          <dt>Estado</dt>
          <dd><?= $verifiedAt ? 'Verificada ' . htmlspecialchars($verifiedAt) : 'Verificación pendiente'; ?></dd>
        </div>
      </dl>
    </section>

    <section class="card" aria-label="Acciones rápidas">
      <h2>Acciones rápidas</h2>
      <div class="quick-actions">
        <button class="btn" type="button" data-action="toggle-settings">Editar información</button>
        <button class="btn" type="button" data-action="toggle-delete">Eliminar cuenta</button>
        <a class="btn" href="index.php">Volver al inicio</a>
        <?php if ($role === 'administrador'): ?>
          <a class="btn" href="../administracion/index.php">Ir al panel administrativo</a>
        <?php endif; ?>
      </div>
    </section>

    <section class="card" id="settings-panel" aria-label="Editar perfil" hidden>
      <h2>Editar información personal</h2>
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
          <button class="btn" type="button" data-action="close-settings">Cancelar</button>
          <button class="btn primary" type="submit">Guardar cambios</button>
        </div>
      </form>
    </section>

    <section class="card card--danger" id="delete-panel" aria-label="Eliminar cuenta" hidden>
      <h2>Eliminar cuenta</h2>
      <form action="perfil.php" method="post">
        <p>Esta acción no se puede deshacer. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
        <input type="hidden" name="action" value="delete" />
        <div class="field">
          <label for="confirmacion">Confirmación</label>
          <input id="confirmacion" name="confirmacion" placeholder="Escribe ELIMINAR" required />
        </div>
        <div class="form-actions">
          <button class="btn" type="button" data-action="close-delete">Cancelar</button>
          <button class="btn danger" type="submit">Cerrar mi cuenta</button>
        </div>
      </form>
    </section>

  </main>

  <footer class="footer wrap">© <?= date('Y'); ?> Expediatravels — Perfil de usuario</footer>

  <input type="file" id="coverHidden" accept="image/*" hidden />

  <script>
    const coverButton = document.getElementById('btn-cover');
    const coverHidden = document.getElementById('coverHidden');
    const cover = document.querySelector('.cover');

    coverButton?.addEventListener('click', () => coverHidden?.click());
    coverHidden?.addEventListener('change', event => {
      const file = event.target.files?.[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      cover.style.background = `url('${url}') center/cover no-repeat`;
    });

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
