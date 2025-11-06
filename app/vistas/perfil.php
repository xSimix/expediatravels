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
    :root{
      /* Paleta viva, minimalista y legible para 25–70 años */
      --bg:#f6f7fb;
      --surface:#ffffff;
      --text:#1b1f24;
      --muted:#6b7280;
      --brand:#0ea5e9;       /* celeste vibrante */
      --brand-2:#22c55e;     /* verde de acento */
      --accent:#f59e0b;      /* amarillo suave */
      --danger:#ef4444;
      --radius:18px;
      --shadow:0 8px 20px rgba(2,8,23,0.06);
      --ring:0 0 0 3px rgba(14,165,233,.2);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
      background:var(--bg); color:var(--text);
    }
    a{color:var(--brand); text-decoration:none}
    .wrap{max-width:1100px; margin:auto; padding:20px;}

    /* Header / Cover */
    .cover{
      position:relative; height:220px; border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow);
      background:linear-gradient(135deg,#a5f3fc,#93c5fd 45%, #60a5fa);
    }
    .cover .actions{
      position:absolute; right:16px; bottom:16px; display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end;
    }
    .btn{
      display:inline-flex; align-items:center; gap:8px; border:none; cursor:pointer;
      padding:10px 14px; border-radius:14px; font-weight:600; background:var(--surface);
      color:var(--text); box-shadow:var(--shadow);
    }
    .btn.primary{ background:var(--brand); color:white; }
    .btn.ghost{ background:rgba(255,255,255,.85); backdrop-filter:blur(6px); }
    .btn:focus{ outline:none; box-shadow:var(--ring); }

    /* Card base */
    .card{ background:var(--surface); border-radius:var(--radius); box-shadow:var(--shadow); }
    .card.pad{ padding:18px; }

    /* Profile header block */
    .profile-head{ display:grid; grid-template-columns:108px 1fr; gap:16px; align-items:end; transform:translateY(-64px); padding:0 16px; }
    .avatar{
      width:108px; height:108px; border-radius:50%; border:4px solid var(--surface);
      background:#dbeafe url('https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9?q=80&w=300&auto=format&fit=crop') center/cover no-repeat;
      box-shadow:var(--shadow);
    }
    .ph-meta{ display:flex; flex-wrap:wrap; align-items:center; gap:14px; }
    .name{ font-size:clamp(20px, 3.2vw, 28px); font-weight:800; }
    .badge{ padding:6px 10px; border-radius:999px; background:#e0f2fe; color:#0369a1; font-weight:700; font-size:12px; }
    .row{ display:flex; gap:18px; flex-wrap:wrap; color:var(--muted); font-weight:600; }
    .stat{ background:#f1f5f9; color:#0f172a; padding:8px 12px; border-radius:12px; font-weight:700; }

    /* Tabs */
    .tabs{ margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; }
    .tab{ background:transparent; border:none; padding:12px 14px; border-radius:12px; font-weight:700; color:var(--muted); cursor:pointer; }
    .tab.active{ color:#0b1220; background:#eaf6ff; box-shadow:var(--shadow); }

    /* Grid */
    .grid{ display:grid; grid-template-columns: 1.05fr 1.5fr; gap:18px; }

    /* Left column cards */
    .chips{ display:flex; flex-wrap:wrap; gap:8px; }
    .chip{ background:#eef2ff; color:#3730a3; padding:8px 12px; border-radius:999px; font-weight:700; }
    .list{ display:grid; gap:10px; }
    .list .item{ display:flex; align-items:center; gap:12px; }
    .icon{ width:34px; height:34px; border-radius:10px; background:#f1f5f9; display:grid; place-items:center; font-weight:900; }

    /* Right column cards */
    .trip{ display:grid; grid-template-columns:72px 1fr auto; gap:12px; align-items:center; padding:12px; border-radius:14px; background:#f8fafc; }
    .thumb{ width:72px; height:72px; border-radius:12px; background:#ddd center/cover no-repeat; }
    .pill{ padding:6px 10px; border-radius:999px; background:#dcfce7; color:#166534; font-weight:800; font-size:12px; }
    .timeline{ border-left:3px solid #e5e7eb; padding-left:14px; display:grid; gap:16px; }
    .dot{ width:12px; height:12px; border-radius:50%; background:var(--brand); box-shadow:0 0 0 4px #e0f2fe; position:relative; left:-23px; top:6px; }

    /* Forms */
    .form{ display:grid; gap:12px; }
    .field{ display:grid; gap:6px; }
    label{ font-weight:700; color:#0f172a; }
    input,select,textarea{
      width:100%; padding:12px 14px; border-radius:12px; border:1px solid #e5e7eb; background:#fff; font-size:16px;
    }
    input:focus,select:focus,textarea:focus{ outline:none; box-shadow:var(--ring); border-color:#bae6fd; }
    .row2{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }

    /* Photo grid */
    .photos{ display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; }
    .photos div{ aspect-ratio:1/1; border-radius:12px; background:#ddd center/cover no-repeat; display:flex; align-items:center; justify-content:center; color:var(--muted); font-weight:700; }

    /* Footer */
    .footer{ text-align:center; color:var(--muted); padding:28px 8px; }

    /* Alerts */
    .alert{ margin:18px 0; padding:16px 18px; border-radius:16px; font-weight:600; box-shadow:var(--shadow); }
    .alert--success{ background:#dcfce7; color:#14532d; }
    .alert--error{ background:#fee2e2; color:#7f1d1d; }
    .alert--info{ background:#e0f2fe; color:#0c4a6e; }

    .danger-card{ border:2px dashed rgba(239,68,68,0.25); }
    .danger-card .btn.primary{ background:var(--danger); }

    /* Responsive */
    @media (max-width: 960px){
      .grid{ grid-template-columns:1fr; }
      .profile-head{ grid-template-columns:84px 1fr; transform:translateY(-52px); }
      .avatar{ width:84px; height:84px; }
      .photos{ grid-template-columns:repeat(2, 1fr); }
      .row2{ grid-template-columns:1fr; }
      .cover .actions{ right:10px; left:10px; justify-content:flex-start; }
    }
  </style>
  <script src="scripts/modal-autenticacion.js" defer></script>
</head>
<body>
  <header class="wrap">
    <div class="cover" role="img" aria-label="Portada con degradado">
      <div class="actions">
        <button class="btn ghost" type="button" id="btn-cover">Cambiar portada</button>
        <button class="btn ghost" type="button" data-tab-target="ajustes" id="btn-edit">Editar perfil</button>
        <a class="btn ghost" href="index.php">Inicio</a>
        <?php if ($role === 'administrador'): ?>
          <a class="btn ghost" href="../administracion/index.php">Panel administrativo</a>
        <?php endif; ?>
        <button class="btn primary" type="button" data-auth-logout>Cerrar sesión</button>
      </div>
    </div>

    <section class="profile-head">
      <div>
        <div class="avatar" id="avatar" aria-label="Foto de perfil"></div>
      </div>
      <div>
        <div class="ph-meta">
          <div class="name"><?= htmlspecialchars($fullName !== '' ? $fullName : $user['nombre']); ?></div>
          <span class="badge" title="Rol del usuario"><?= htmlspecialchars($roleLabel); ?></span>
          <?php if ($createdAt !== null): ?>
            <span class="stat" title="Miembro desde">Miembro desde <?= htmlspecialchars($createdAt); ?></span>
          <?php endif; ?>
          <span class="stat" title="Estado de verificación">
            <?= $verifiedAt ? 'Cuenta verificada' : 'Verificación pendiente'; ?>
          </span>
        </div>
        <div class="row" style="margin-top:6px">
          <span>📧 <?= htmlspecialchars($user['correo']); ?></span>
          <?php if (!empty($phone)): ?>
            <span>☎ <?= htmlspecialchars($phone); ?></span>
          <?php else: ?>
            <span>☎ Sin número registrado</span>
          <?php endif; ?>
          <span>🆔 Usuario #<?= htmlspecialchars((string) $user['id']); ?></span>
        </div>
        <nav class="tabs" role="tablist" aria-label="Secciones de perfil">
          <button class="tab active" data-tab="resumen" role="tab" aria-selected="true">Resumen</button>
          <button class="tab" data-tab="viajes" role="tab" aria-selected="false">Viajes</button>
          <button class="tab" data-tab="resenas" role="tab" aria-selected="false">Reseñas</button>
          <button class="tab" data-tab="ajustes" role="tab" aria-selected="false">Ajustes</button>
        </nav>
      </div>
    </section>
  </header>

  <main class="wrap" id="content">
    <?php if (!empty($flash) && !empty($flash['message'])): ?>
      <?php $flashType = $flash['type'] ?? 'info'; ?>
      <div class="alert <?= $flashType === 'success' ? 'alert--success' : ($flashType === 'error' ? 'alert--error' : 'alert--info'); ?>">
        <?= htmlspecialchars($flash['message']); ?>
      </div>
    <?php endif; ?>

    <!-- TAB: RESUMEN -->
    <section class="tab-panel" id="tab-resumen" aria-labelledby="Resumen">
      <div class="grid">
        <aside class="card pad" aria-label="Información del usuario">
          <h2 style="margin:0 0 8px">Acerca de</h2>
          <p style="margin:0 0 14px; color:var(--muted)">
            <?= htmlspecialchars($user['nombre']); ?> administra su experiencia de viaje desde esta cuenta de Expediatravels. Aquí puedes consultar tus datos principales y el estado general de tu cuenta.
          </p>

          <div class="list" style="margin:16px 0">
            <div class="item"><span class="icon">✉</span> <a href="mailto:<?= htmlspecialchars($user['correo']); ?>"><?= htmlspecialchars($user['correo']); ?></a></div>
            <div class="item"><span class="icon">☎</span> <span><?= $phone ? htmlspecialchars($phone) : 'Sin número registrado'; ?></span></div>
            <div class="item"><span class="icon">🎟</span> <span><?= htmlspecialchars($roleLabel); ?></span></div>
          </div>

          <h3 style="margin:10px 0 8px">Estado de la cuenta</h3>
          <div class="chips">
            <span class="chip">ID <?= htmlspecialchars((string) $user['id']); ?></span>
            <?php if ($createdAtFull !== null): ?>
              <span class="chip">Creada <?= htmlspecialchars($createdAtFull); ?></span>
            <?php endif; ?>
            <span class="chip" style="background:#e0f2fe;color:#0369a1;">
              <?= $verifiedAt ? 'Verificada ' . htmlspecialchars($verifiedAt) : 'Verificación pendiente'; ?>
            </span>
          </div>

          <h3 style="margin:16px 0 8px">Recomendaciones</h3>
          <div class="chips">
            <span class="chip" style="background:#dcfce7;color:#14532d">Mantén tus datos actualizados</span>
            <span class="chip" style="background:#fff7ed;color:#9a3412">Activa la verificación</span>
            <span class="chip" style="background:#fef9c3;color:#854d0e">Protege tu contraseña</span>
          </div>
        </aside>

        <section class="card pad" aria-label="Actividad del perfil">
          <h2 style="margin:0 0 12px">Próximos viajes</h2>
          <div class="list" style="margin-bottom:18px">
            <div class="trip">
              <div class="thumb" style="background-image:url('https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=300&auto=format&fit=crop')"></div>
              <div>
                <div style="font-weight:800">Personaliza tus planes</div>
                <div style="color:var(--muted)">Agrega tus próximos viajes desde el panel principal.</div>
              </div>
              <span class="pill">Disponible</span>
            </div>
          </div>

          <h2 style="margin:6px 0 12px">Actividad reciente</h2>
          <div class="timeline">
            <?php if ($createdAtFull !== null): ?>
              <div>
                <div class="dot"></div>
                <div style="font-weight:700">Cuenta creada</div>
                <div style="color:var(--muted)">El <?= htmlspecialchars($createdAtFull); ?></div>
              </div>
            <?php endif; ?>
            <?php if ($verifiedAt !== null): ?>
              <div>
                <div class="dot"></div>
                <div style="font-weight:700">Verificación completada</div>
                <div style="color:var(--muted)">El <?= htmlspecialchars($verifiedAt); ?></div>
              </div>
            <?php else: ?>
              <div>
                <div class="dot"></div>
                <div style="font-weight:700">Verificación pendiente</div>
                <div style="color:var(--muted)">Confirma tu correo para proteger tu cuenta.</div>
              </div>
            <?php endif; ?>
            <div>
              <div class="dot"></div>
              <div style="font-weight:700">Última actualización</div>
              <div style="color:var(--muted)">Gestiona tus datos en la pestaña Ajustes.</div>
            </div>
          </div>

          <h2 style="margin:16px 0 12px">Fotos</h2>
          <div class="photos">
            <div>Sube tus recuerdos</div>
            <div>Mantén tu perfil vivo</div>
            <div>Comparte tu historia</div>
            <div>Explora destinos</div>
            <div>Inspira a otros</div>
            <div>Expediatravels</div>
          </div>
        </section>
      </div>
    </section>

    <!-- TAB: VIAJES -->
    <section class="tab-panel" id="tab-viajes" hidden>
      <div class="card pad">
        <h2 style="margin:0 0 12px">Historial de viajes</h2>
        <p style="margin:0; color:var(--muted)">Aún no registras viajes en tu perfil. Cuando participes en actividades, aparecerán aquí para que puedas revisarlas.</p>
      </div>
    </section>

    <!-- TAB: RESEÑAS -->
    <section class="tab-panel" id="tab-resenas" hidden>
      <div class="card pad">
        <h2 style="margin:0 0 12px">Reseñas</h2>
        <p style="margin:0; color:var(--muted)">Comparte tus experiencias sobre destinos y servicios turísticos desde la plataforma principal para verlas en tu perfil.</p>
      </div>
    </section>

    <!-- TAB: AJUSTES -->
    <section class="tab-panel" id="tab-ajustes" hidden>
      <form class="card pad form" action="perfil.php" method="post" novalidate>
        <h2 style="margin:0 0 12px">Actualizar datos personales</h2>
        <input type="hidden" name="action" value="update" />
        <div class="row2">
          <div class="field">
            <label for="nombres">Nombres</label>
            <input id="nombres" name="nombre" value="<?= htmlspecialchars($user['nombre']); ?>" required />
          </div>
          <div class="field">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" value="<?= htmlspecialchars($user['apellidos']); ?>" required />
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="correo">Correo</label>
            <input id="correo" name="correo" type="email" value="<?= htmlspecialchars($user['correo']); ?>" required />
          </div>
          <div class="field">
            <label for="telefono">Teléfono</label>
            <input id="telefono" name="celular" value="<?= $phone ? htmlspecialchars($phone) : ''; ?>" placeholder="Opcional" />
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="password">Nueva contraseña</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Dejar en blanco para mantener" />
          </div>
          <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Repite tu nueva contraseña" />
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="avatarInput">Foto de perfil</label>
            <input id="avatarInput" type="file" accept="image/*" />
          </div>
          <div class="field">
            <label for="coverInput">Portada</label>
            <input id="coverInput" type="file" accept="image/*" />
          </div>
        </div>
        <p style="margin:0; color:var(--muted); font-size:14px;">Las imágenes seleccionadas se muestran como vista previa y no se almacenan de forma permanente.</p>
        <div class="row2">
          <div class="field">
            <label for="rol">Rol asignado</label>
            <input id="rol" value="<?= htmlspecialchars($roleLabel); ?>" disabled />
          </div>
          <div class="field">
            <label for="creado">Miembro desde</label>
            <input id="creado" value="<?= $createdAtFull ? htmlspecialchars($createdAtFull) : 'Sin registro'; ?>" disabled />
          </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:4px">
          <button class="btn" type="reset">Restablecer</button>
          <button class="btn primary" type="submit">Guardar cambios</button>
        </div>
      </form>

      <form class="card pad form danger-card" action="perfil.php" method="post" style="margin-top:18px">
        <h2 style="margin:0 0 12px; color:var(--danger)">Eliminar cuenta</h2>
        <p style="margin:0; color:var(--muted)">Esta acción no se puede deshacer. Para confirmar, escribe <strong>ELIMINAR</strong> en el campo y envía el formulario.</p>
        <input type="hidden" name="action" value="delete" />
        <div class="field">
          <label for="confirmacion">Confirmación</label>
          <input id="confirmacion" name="confirmacion" placeholder="Escribe ELIMINAR" required />
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn" type="reset">Cancelar</button>
          <button class="btn primary" type="submit" style="background:var(--danger)">Cerrar mi cuenta</button>
        </div>
      </form>
    </section>

  </main>

  <footer class="footer wrap">© <?= date('Y'); ?> Expediatravels — Perfil de usuario</footer>

  <input type="file" id="coverHidden" accept="image/*" hidden />

  <script>
    // Tabs handler
    const tabs = document.querySelectorAll('.tab');
    const panels = {
      resumen: document.getElementById('tab-resumen'),
      viajes: document.getElementById('tab-viajes'),
      resenas: document.getElementById('tab-resenas'),
      ajustes: document.getElementById('tab-ajustes')
    };
    tabs.forEach(t => t.addEventListener('click', () => {
      tabs.forEach(x => {
        x.classList.remove('active');
        x.setAttribute('aria-selected', 'false');
      });
      t.classList.add('active');
      t.setAttribute('aria-selected', 'true');
      Object.values(panels).forEach(p => p.hidden = true);
      panels[t.dataset.tab].hidden = false;
      window.scrollTo({ top: document.querySelector('.wrap').offsetTop, behavior: 'smooth' });
    }));

    // Switch to ajustes when pressing Editar perfil
    const editButton = document.getElementById('btn-edit');
    editButton?.addEventListener('click', () => {
      const ajustesTab = document.querySelector('.tab[data-tab="ajustes"]');
      ajustesTab?.dispatchEvent(new Event('click'));
    });

    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    const avatar = document.getElementById('avatar');
    avatarInput?.addEventListener('change', e => {
      const f = e.target.files?.[0];
      if(!f) return;
      const url = URL.createObjectURL(f);
      avatar.style.backgroundImage = `url('${url}')`;
    });

    // Cover upload
    const btnCover = document.getElementById('btn-cover');
    const coverHidden = document.getElementById('coverHidden');
    const cover = document.querySelector('.cover');
    const coverInput = document.getElementById('coverInput');

    btnCover?.addEventListener('click', () => coverHidden.click());
    coverHidden?.addEventListener('change', e => {
      const f = e.target.files?.[0];
      if(!f) return;
      const url = URL.createObjectURL(f);
      cover.style.background = `url('${url}') center/cover no-repeat`;
    });
    coverInput?.addEventListener('change', e => {
      const f = e.target.files?.[0];
      if(!f) return;
      const url = URL.createObjectURL(f);
      cover.style.background = `url('${url}') center/cover no-repeat`;
    });
  </script>
</body>
</html>
