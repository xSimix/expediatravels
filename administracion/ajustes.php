<?php

declare(strict_types=1);

$ajustes = require __DIR__ . '/ajustes_controlador.php';

extract($ajustes, EXTR_OVERWRITE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ajustes del sitio — Panel Expediatravels</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --panel-bg: #f4f6f9;
            --panel-sidebar: #ffffff;
            --panel-border: rgba(15, 23, 42, 0.08);
            --panel-text: #0f172a;
            --panel-muted: #64748b;
            --panel-brand: #3b82f6;
            --panel-radius: 18px;
            --panel-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --surface: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f7f8fb 0%, #eef1f5 60%, #f7f8fb 100%);
            color: var(--panel-text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .panel-app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .panel-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            background: linear-gradient(180deg, #ffffff 0%, #f4f6f9 100%);
            border-right: 1px solid var(--panel-border);
            padding: 24px 18px;
        }

        .panel-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            margin-bottom: 18px;
        }

        .panel-brand__logo {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #ffffff;
            font-weight: 700;
            box-shadow: var(--panel-shadow);
        }

        .panel-brand__meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .panel-brand__meta small {
            color: var(--panel-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.72rem;
        }

        .panel-section {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            color: var(--panel-muted);
            padding: 0 10px;
            margin: 20px 0 8px;
        }

        .panel-nav {
            display: grid;
            gap: 6px;
            padding: 0 6px;
        }

        .panel-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid transparent;
            color: var(--panel-text);
            transition: background 0.18s ease, border-color 0.18s ease;
        }

        .panel-nav a:hover {
            background: #eef1f5;
            border-color: var(--panel-border);
        }

        .panel-nav a.active {
            background: linear-gradient(90deg, #eef1f5, #f8fafc);
            border-color: var(--panel-border);
            font-weight: 600;
        }

        .panel-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .panel-topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid var(--panel-border);
        }

        .panel-topbar__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
        }

        .panel-topbar__actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-chip {
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid var(--panel-border);
            color: var(--panel-muted);
            background: #ffffff;
            font-size: 0.82rem;
        }

        .panel-content {
            padding: 26px clamp(18px, 3vw, 36px) 48px;
            display: grid;
            gap: 28px;
        }

        .panel-page-header {
            display: grid;
            gap: 10px;
        }

        .panel-page-header h1 {
            margin: 0;
            font-size: clamp(1.6rem, 3vw, 2rem);
        }

        .panel-page-header p {
            margin: 0;
            color: var(--panel-muted);
            max-width: 720px;
        }

        .admin-card {
            background: var(--surface);
            border-radius: var(--panel-radius);
            border: 1px solid rgba(148, 163, 184, 0.28);
            box-shadow: var(--panel-shadow);
            padding: clamp(1.8rem, 3vw, 2.4rem);
            display: grid;
            gap: 1.75rem;
        }

        .admin-card h2 {
            margin: 0;
            font-size: 1.35rem;
        }

        .admin-grid {
            display: grid;
            gap: 1.4rem;
        }

        .admin-grid.two-columns {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .admin-field {
            display: grid;
            gap: 0.55rem;
        }

        .admin-field label {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            color: var(--panel-muted);
        }

        .admin-field input,
        .admin-field textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            background: rgba(248, 250, 252, 0.7);
            font: inherit;
            min-height: 52px;
            color: inherit;
        }

        .admin-field textarea {
            resize: vertical;
            min-height: 120px;
        }

        .admin-help {
            margin: 0;
            font-size: 0.85rem;
            color: var(--panel-muted);
        }

        .admin-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .admin-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border: none;
            border-radius: 999px;
            padding: 0.75rem 1.6rem;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            color: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(2, 132, 199, 0.25);
        }

        .admin-alert {
            border-radius: 18px;
            padding: 1rem 1.4rem;
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.35);
        }

        .admin-alert.error {
            background: rgba(248, 113, 113, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #b91c1c;
        }

        .admin-logo-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #f1f5f9;
            border-radius: 18px;
        }

        .admin-logo-preview__image {
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            width: 140px;
            height: 70px;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .admin-logo-preview__image--square {
            width: 70px;
            height: 70px;
        }

        .admin-logo-preview__image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .admin-logo-preview__meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.95rem;
            color: var(--panel-muted);
        }

        .admin-logo-preview__label {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.75rem;
            color: var(--panel-text);
        }

        .admin-logo-preview__path {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.85rem;
            word-break: break-all;
        }

        .hero-gallery {
            display: grid;
            gap: 1.5rem;
        }

        .hero-gallery__grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .hero-gallery__item {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: #0f172a;
            color: #fff;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(148, 163, 184, 0.28);
            display: grid;
        }

        .hero-gallery__item:not(.hero-gallery__item--inactive) {
            box-shadow: 0 24px 65px rgba(2, 132, 199, 0.24);
            border-color: rgba(56, 189, 248, 0.45);
        }

        .hero-gallery__item--inactive {
            opacity: 0.7;
        }

        .hero-gallery__item--inactive::after {
            content: 'Oculta';
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(15, 23, 42, 0.75);
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .hero-gallery__edit {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            border: none;
            background: rgba(15, 23, 42, 0.6);
            color: inherit;
            border-radius: 12px;
            padding: 6px 10px;
            cursor: pointer;
        }

        .hero-gallery__select {
            display: grid;
            gap: 0.9rem;
            color: inherit;
            text-decoration: none;
            padding: 0;
        }

        .hero-gallery__checkbox {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .hero-gallery__thumbnail {
            display: block;
            aspect-ratio: 4 / 3;
            overflow: hidden;
        }

        .hero-gallery__thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-gallery__meta {
            display: grid;
            gap: 0.4rem;
            padding: 1rem 1.2rem 1.4rem;
        }

        .hero-gallery__meta strong {
            font-size: 1rem;
        }

        .hero-gallery__meta small {
            color: rgba(226, 232, 240, 0.9);
            font-size: 0.82rem;
        }

        .hero-gallery__delete-form {
            margin: 0 1.2rem 1.2rem;
        }

        .hero-gallery__delete {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 0.55rem 1rem;
            background: rgba(220, 38, 38, 0.9);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.04em;
            transition: background 0.2s ease;
        }

        .hero-gallery__delete:hover {
            background: rgba(220, 38, 38, 1);
        }

        .hero-gallery__dialog::backdrop {
            background: rgba(15, 23, 42, 0.55);
        }

        .hero-gallery__dialog {
            border: none;
            border-radius: 20px;
            padding: 0;
            max-width: min(520px, 92vw);
            width: 100%;
            overflow: hidden;
            background: #0f172a;
            color: #e2e8f0;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.35);
        }

        .hero-gallery__dialog header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.4rem 0.6rem;
            background: rgba(30, 41, 59, 0.65);
        }

        .hero-gallery__dialog header h3 {
            margin: 0;
            font-size: 1.05rem;
        }

        .hero-gallery__dialog form {
            display: grid;
            gap: 1rem;
            padding: 1.4rem;
        }

        .hero-gallery__dialog .admin-field input,
        .hero-gallery__dialog .admin-field textarea {
            background: rgba(15, 23, 42, 0.65);
            color: inherit;
            border-color: rgba(148, 163, 184, 0.45);
        }

        .hero-gallery__dialog footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 0 1.4rem 1.4rem;
        }

        .hero-gallery__dialog button {
            font: inherit;
        }

        .hero-gallery__dialog-close {
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.4rem;
            cursor: pointer;
        }

        .hero-gallery__dialog-cancel {
            border: 1px solid rgba(148, 163, 184, 0.45);
            background: transparent;
            color: inherit;
            border-radius: 999px;
            padding: 0.55rem 1.3rem;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .hero-gallery__dialog-cancel:hover {
            background: rgba(148, 163, 184, 0.15);
        }

        .hero-gallery__dialog-submit {
            border: none;
            border-radius: 999px;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            color: #0f172a;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hero-gallery__dialog-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.35);
        }

        .panel-mobile-toggle {
            display: none;
            background: #ffffff;
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 8px 12px;
            cursor: pointer;
        }

        @media (max-width: 1100px) {
            .panel-app {
                grid-template-columns: 1fr;
            }

            .panel-sidebar {
                position: fixed;
                left: -100%;
                max-width: 320px;
                width: 80%;
                height: 100%;
                transition: transform 0.25s ease;
                transform: translateX(0);
            }

            .panel-sidebar.is-open {
                left: 0;
            }

            .panel-mobile-toggle {
                display: inline-flex;
            }

            body.panel-locked {
                overflow: hidden;
            }
        }

        @media (max-width: 640px) {
            .panel-topbar__inner {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .admin-grid.two-columns {
                grid-template-columns: 1fr;
            }

            .admin-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="panel-app">
        <aside class="panel-sidebar" id="panelSidebar">
            <div class="panel-brand">
                <div class="panel-brand__logo" aria-hidden="true">Ex</div>
                <div class="panel-brand__meta">
                    <strong>Expediatravels</strong>
                    <small>Panel de control</small>
                </div>
            </div>

            <div class="panel-section">Gestión</div>
            <nav class="panel-nav" aria-label="Secciones de gestión">
                <a href="index.php"><span aria-hidden="true">🏠</span> Inicio</a>
                <a href="destinos.php"><span aria-hidden="true">📍</span> Destinos</a>
                <a href="paquetes.php"><span aria-hidden="true">🎒</span> Paquetes</a>
                <a href="usuarios.php"><span aria-hidden="true">🧑‍💼</span> Administradores</a>
            </nav>

            <div class="panel-section">Operación</div>
            <nav class="panel-nav" aria-label="Secciones de operación">
                <a href="reportes.php"><span aria-hidden="true">📊</span> Reportes</a>
                <a class="active" href="ajustes.php"><span aria-hidden="true">⚙️</span> Ajustes</a>
            </nav>
        </aside>

        <main class="panel-main">
            <header class="panel-topbar">
                <div class="panel-topbar__inner">
                    <button class="panel-mobile-toggle" id="panelToggle" aria-controls="panelSidebar" aria-expanded="false">☰</button>
                    <div class="panel-topbar__actions">
                        <span class="panel-chip" id="panelToday">—</span>
                        <span class="panel-chip" id="panelClock">—</span>
                    </div>
                </div>
            </header>

            <div class="panel-content">
                <header class="panel-page-header">
                    <h1>Ajustes del sitio</h1>
                    <p>Administra la identidad visual, los datos de contacto y los fondos del hero que alimentan la experiencia pública del sitio.</p>
                </header>

                <?php if (!empty($feedback)): ?>
                    <div class="admin-alert<?= $feedback['type'] === 'error' ? ' error' : ''; ?>">
                        <?= htmlspecialchars($feedback['message']); ?>
                    </div>
                <?php endif; ?>

                <section class="admin-card" aria-labelledby="ajustes-identidad">
                    <h2 id="ajustes-identidad">Identidad y datos de contacto</h2>
                    <form method="post" class="admin-grid" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="site_settings" />
                        <input type="hidden" name="current_site_logo" value="<?= htmlspecialchars((string) ($siteLogo ?? ''), ENT_QUOTES); ?>" />
                        <input type="hidden" name="current_site_favicon" value="<?= htmlspecialchars((string) ($siteFavicon ?? ''), ENT_QUOTES); ?>" />

                        <div class="admin-grid two-columns">
                            <div class="admin-field">
                                <label for="site_title">Título del sitio</label>
                                <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($siteTitle, ENT_QUOTES); ?>" required />
                            </div>
                            <div class="admin-field">
                                <label for="site_tagline">Lema o tagline</label>
                                <input type="text" id="site_tagline" name="site_tagline" value="<?= htmlspecialchars($siteTagline, ENT_QUOTES); ?>" placeholder="Explora la Selva Central" />
                            </div>
                        </div>

                        <div class="admin-field">
                            <label for="site_logo_file">Logo del sitio</label>
                            <input type="file" id="site_logo_file" name="site_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml" />
                            <p class="admin-help">Sube una imagen horizontal (recomendado 250x65 px). Formatos permitidos: JPG, PNG, WEBP o SVG.</p>
                            <?php if ($siteLogo): ?>
                                <div class="admin-logo-preview">
                                    <div class="admin-logo-preview__image">
                                        <img src="<?= htmlspecialchars($siteLogo, ENT_QUOTES); ?>" alt="Logo actual del sitio" />
                                    </div>
                                    <div class="admin-logo-preview__meta">
                                        <span class="admin-logo-preview__label">Logo actual</span>
                                        <span class="admin-logo-preview__path"><?= htmlspecialchars($siteLogo, ENT_QUOTES); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="admin-field">
                            <label for="site_favicon_file">Icono de la página (favicon)</label>
                            <input type="file" id="site_favicon_file" name="site_favicon_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon,image/vnd.microsoft.icon" />
                            <p class="admin-help">Sube un ícono cuadrado (recomendado 512x512 px). Formatos permitidos: PNG, JPG, WEBP, SVG o ICO.</p>
                            <?php if ($siteFavicon): ?>
                                <div class="admin-logo-preview">
                                    <div class="admin-logo-preview__image admin-logo-preview__image--square">
                                        <img src="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" alt="Favicon actual del sitio" />
                                    </div>
                                    <div class="admin-logo-preview__meta">
                                        <span class="admin-logo-preview__label">Favicon actual</span>
                                        <span class="admin-logo-preview__path"><?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="admin-grid two-columns">
                            <div class="admin-field">
                                <label for="contact_phones">Teléfonos</label>
                                <textarea id="contact_phones" name="contact_phones" rows="4" placeholder="+51 999 999 999&#10;+51 988 888 888"><?= $renderTextarea($contactPhones); ?></textarea>
                                <p class="admin-help">Ingresa un número por línea. Se usará el primero como principal.</p>
                            </div>
                            <div class="admin-field">
                                <label for="contact_emails">Correos electrónicos</label>
                                <textarea id="contact_emails" name="contact_emails" rows="4" placeholder="hola@expediatravels.pe&#10;reservas@expediatravels.pe"><?= $renderTextarea($contactEmails); ?></textarea>
                                <p class="admin-help">Ingresa un correo por línea. Se usará el primero en encabezado y pie.</p>
                            </div>
                        </div>

                        <div class="admin-grid two-columns">
                            <div class="admin-field">
                                <label for="contact_addresses">Direcciones</label>
                                <textarea id="contact_addresses" name="contact_addresses" rows="4" placeholder="Jr. San Martín 245, Oxapampa&#10;Centro empresarial Aurora, Lima"><?= $renderTextarea($contactAddresses); ?></textarea>
                                <p class="admin-help">Coloca la dirección física o base de operaciones por línea.</p>
                            </div>
                            <div class="admin-field">
                                <label for="contact_locations">Ubicaciones / Referencias</label>
                                <textarea id="contact_locations" name="contact_locations" rows="4" placeholder="Oxapampa, Pasco — Perú&#10;Miraflores, Lima — Perú"><?= $renderTextarea($contactLocations); ?></textarea>
                                <p class="admin-help">Opcional. Se mostrará debajo de cada dirección (mismo orden).</p>
                            </div>
                        </div>

                        <div class="admin-field">
                            <label for="social_links">Redes sociales</label>
                            <textarea id="social_links" name="social_links" rows="4" placeholder="Instagram|https://instagram.com/expediatravels&#10;Facebook|https://facebook.com/expediatravels"><?= $renderTextarea(array_map(static fn (array $social): string => ($social['label'] ?? '') . '|' . ($social['url'] ?? ''), $socialLinks)); ?></textarea>
                            <p class="admin-help">Formato: <strong>Nombre|URL</strong> por línea (por ejemplo <em>Instagram|https://instagram.com/expediatravels</em>).</p>
                        </div>

                        <div class="admin-actions">
                            <button class="admin-button" type="submit">Guardar cambios</button>
                        </div>
                    </form>
                </section>

                <section class="admin-card" aria-labelledby="ajustes-hero">
                    <h2 id="ajustes-hero">Fondos del hero</h2>
                    <p class="admin-help">Gestiona las imágenes que aparecen en el slider principal. Las subidas se guardan en <code>web/cargas/hero</code>.</p>

                    <div class="hero-gallery">
                        <form method="post" id="hero-visibility-form" style="display: none;">
                            <input type="hidden" name="form_type" value="update_visibility" />
                        </form>

                        <?php if (!empty($heroSlides)): ?>
                            <div class="hero-gallery__grid">
                                <?php foreach ($heroSlides as $slide):
                                    $slideId = $slide['id'] ?? null;
                                    $slideLabel = $slide['label'] ?? '';
                                    $slideImage = $slide['image'] ?? '';
                                    $slideAlt = $slide['altText'] ?? '';
                                    $slideDescription = $slide['description'] ?? '';
                                    if (!$slideId || $slideImage === '') {
                                        continue;
                                    }
                                    $isVisible = isset($visibleHeroSlideIds[(int) $slideId]);
                                    $checkboxId = 'hero-slide-' . (int) $slideId;
                                ?>
                                    <div class="hero-gallery__item<?= $isVisible ? '' : ' hero-gallery__item--inactive'; ?>">
                                        <button type="button" class="hero-gallery__edit" data-hero-edit-trigger aria-label="Editar metadatos de la imagen">
                                            <span aria-hidden="true">✏️</span>
                                        </button>
                                        <label class="hero-gallery__select" for="<?= htmlspecialchars($checkboxId, ENT_QUOTES); ?>">
                                            <input
                                                type="checkbox"
                                                class="hero-gallery__checkbox"
                                                id="<?= htmlspecialchars($checkboxId, ENT_QUOTES); ?>"
                                                name="visible_slides[]"
                                                value="<?= (int) $slideId; ?>"
                                                form="hero-visibility-form"
                                                <?= $isVisible ? 'checked' : ''; ?>
                                            />
                                            <span class="hero-gallery__thumbnail">
                                                <img src="<?= htmlspecialchars($slideImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($slideAlt !== '' ? $slideAlt : ($slideLabel !== '' ? $slideLabel : 'Imagen del hero'), ENT_QUOTES); ?>" loading="lazy" />
                                            </span>
                                            <span class="hero-gallery__meta">
                                                <strong><?= htmlspecialchars($slideLabel !== '' ? $slideLabel : 'Sin título'); ?></strong>
                                                <small><?= htmlspecialchars($slideImage); ?></small>
                                                <?php if ($slideAlt !== ''): ?>
                                                    <small>ALT: <?= htmlspecialchars($slideAlt); ?></small>
                                                <?php endif; ?>
                                                <?php if ($slideDescription !== ''): ?>
                                                    <small><?= htmlspecialchars($slideDescription); ?></small>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                        <form method="post" class="hero-gallery__delete-form" onsubmit="return confirm('¿Eliminar esta imagen del slider?');">
                                            <input type="hidden" name="form_type" value="delete_slide" />
                                            <input type="hidden" name="slide_id" value="<?= (int) $slideId; ?>" />
                                            <button type="submit" class="hero-gallery__delete">Eliminar</button>
                                        </form>
                                        <dialog class="hero-gallery__dialog" data-hero-edit-dialog>
                                            <header>
                                                <h3>Editar imagen del hero</h3>
                                                <button type="button" class="hero-gallery__dialog-close" data-hero-edit-close aria-label="Cerrar editor">&times;</button>
                                            </header>
                                            <form method="post">
                                                <input type="hidden" name="form_type" value="update_slide" />
                                                <input type="hidden" name="slide_id" value="<?= (int) $slideId; ?>" />
                                                <div class="admin-field">
                                                    <label for="slide-label-<?= (int) $slideId; ?>">Título de la imagen</label>
                                                    <input type="text" id="slide-label-<?= (int) $slideId; ?>" name="slide_label" value="<?= htmlspecialchars($slideLabel, ENT_QUOTES); ?>" placeholder="Bosques de Oxapampa" />
                                                    <p class="admin-help">Se muestra como título del slider y texto principal.</p>
                                                </div>
                                                <div class="admin-field">
                                                    <label for="slide-alt-<?= (int) $slideId; ?>">Texto alternativo (ALT)</label>
                                                    <input type="text" id="slide-alt-<?= (int) $slideId; ?>" name="slide_alt_text" value="<?= htmlspecialchars($slideAlt, ENT_QUOTES); ?>" placeholder="Paisaje de los bosques de Oxapampa al amanecer" />
                                                    <p class="admin-help">Úsalo para accesibilidad y SEO. Describe la imagen en una frase.</p>
                                                </div>
                                                <div class="admin-field">
                                                    <label for="slide-description-<?= (int) $slideId; ?>">Descripción SEO</label>
                                                    <textarea id="slide-description-<?= (int) $slideId; ?>" name="slide_description" rows="3" placeholder="Describe el contexto de la imagen, ubicación o experiencia que representa."><?= htmlspecialchars($slideDescription, ENT_QUOTES); ?></textarea>
                                                    <p class="admin-help">Opcional. Profundiza en los detalles para motores de búsqueda.</p>
                                                </div>
                                                <footer>
                                                    <button type="button" class="hero-gallery__dialog-cancel" data-hero-edit-close>Cancelar</button>
                                                    <button type="submit" class="hero-gallery__dialog-submit">Guardar</button>
                                                </footer>
                                            </form>
                                        </dialog>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="admin-actions">
                                <button class="admin-button" type="submit" form="hero-visibility-form">Guardar selección</button>
                            </div>
                        <?php else: ?>
                            <p class="admin-help">Aún no hay imágenes registradas. Agrega la primera para activar la galería.</p>
                        <?php endif; ?>
                    </div>

                    <form method="post" class="admin-grid" style="margin-top: 1.5rem;" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="add_slide" />
                        <div class="admin-grid two-columns">
                            <div class="admin-field">
                                <label for="slide_upload">Imagen del hero</label>
                                <input type="file" id="slide_upload" name="slide_upload" accept="image/jpeg,image/png,image/webp" required />
                                <p class="admin-help">Se admiten imágenes horizontales (1600x900 o superior). Formatos: JPG, PNG o WEBP.</p>
                            </div>
                            <div class="admin-field">
                                <label for="slide_label">Título o descripción</label>
                                <input type="text" id="slide_label" name="slide_label" placeholder="Bosques de Oxapampa" />
                                <p class="admin-help">Opcional. Se muestra como etiqueta de contexto del slider.</p>
                            </div>
                        </div>
                        <div class="admin-actions">
                            <button class="admin-button" type="submit">Agregar imagen</button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script>
        const todayChip = document.getElementById('panelToday');
        const clockChip = document.getElementById('panelClock');
        const sidebar = document.getElementById('panelSidebar');
        const toggle = document.getElementById('panelToggle');

        const updateClock = () => {
            const now = new Date();
            const dateFormatter = new Intl.DateTimeFormat('es-PE', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const timeFormatter = new Intl.DateTimeFormat('es-PE', { hour: '2-digit', minute: '2-digit' });
            if (todayChip) {
                todayChip.textContent = dateFormatter.format(now);
            }
            if (clockChip) {
                clockChip.textContent = timeFormatter.format(now);
            }
        };

        updateClock();
        setInterval(updateClock, 60000);

        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                const isOpen = sidebar.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                document.body.classList.toggle('panel-locked', isOpen);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const supportsDialog = typeof HTMLDialogElement !== 'undefined';

            document.querySelectorAll('[data-hero-edit-trigger]').forEach((button) => {
                const item = button.closest('.hero-gallery__item');
                if (!item) {
                    return;
                }

                const dialog = item.querySelector('[data-hero-edit-dialog]');
                if (!dialog) {
                    return;
                }

                const closeDialog = () => {
                    if (supportsDialog && typeof dialog.close === 'function') {
                        dialog.close();
                    } else {
                        dialog.removeAttribute('open');
                    }
                };

                button.addEventListener('click', () => {
                    if (supportsDialog && typeof dialog.showModal === 'function') {
                        dialog.showModal();
                    } else {
                        dialog.setAttribute('open', '');
                    }
                });

                dialog.querySelectorAll('[data-hero-edit-close]').forEach((closeButton) => {
                    closeButton.addEventListener('click', () => {
                        closeDialog();
                    });
                });

                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        closeDialog();
                    }
                });
            });
        });
    </script>
</body>
</html>
