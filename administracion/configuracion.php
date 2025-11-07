<?php

declare(strict_types=1);

$ajustes = require __DIR__ . '/ajustes_controlador.php';

extract($ajustes, EXTR_OVERWRITE);

$paginaActiva = 'configuracion';
$tituloPagina = 'Configuración del sitio — Expediatravels';
$estilosExtra = ['recursos/panel-admin.css'];

require __DIR__ . '/plantilla/cabecera.php';
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Configuración del sitio</h1>
        <p>Gestiona la identidad de marca, los fondos del hero y los datos de contacto visibles en la web pública.</p>
    </header>

    <?php if (!empty($feedback)): ?>
        <div class="admin-alert<?= $feedback['type'] === 'error' ? ' error' : ''; ?>">
            <?= htmlspecialchars($feedback['message']); ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">
        <h2>Identidad y datos de contacto</h2>
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
                <p class="admin-help">Sube una imagen horizontal (recomendado 250x65 px). Se permiten formatos JPG, PNG, WEBP o SVG.</p>
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
                    <label for="contact_emails">Correos de contacto</label>
                    <textarea id="contact_emails" name="contact_emails" rows="4" placeholder="contacto@expediatravels.pe&#10;reservas@expediatravels.pe"><?= htmlspecialchars(implode(PHP_EOL, $contactEmails), ENT_QUOTES); ?></textarea>
                    <p class="admin-help">Separa cada correo en una línea diferente.</p>
                </div>
                <div class="admin-field">
                    <label for="contact_phones">Teléfonos</label>
                    <textarea id="contact_phones" name="contact_phones" rows="4" placeholder="+51 999 888 777&#10;+51 01 555 1234"><?= htmlspecialchars(implode(PHP_EOL, $contactPhones), ENT_QUOTES); ?></textarea>
                    <p class="admin-help">Incluye el código de país. Separa cada teléfono en una línea.</p>
                </div>
            </div>
            <div class="admin-grid two-columns">
                <div class="admin-field">
                    <label for="contact_addresses">Direcciones</label>
                    <textarea id="contact_addresses" name="contact_addresses" rows="4" placeholder="Jr. Amazonas 123 - La Merced"><?= htmlspecialchars(implode(PHP_EOL, $contactAddresses), ENT_QUOTES); ?></textarea>
                    <p class="admin-help">Direcciones físicas que se mostrarán en la web.</p>
                </div>
                <div class="admin-field">
                    <label for="contact_locations">Ubicaciones</label>
                    <textarea id="contact_locations" name="contact_locations" rows="4" placeholder="La Merced&#10;Oxapampa"><?= htmlspecialchars(implode(PHP_EOL, $contactLocations), ENT_QUOTES); ?></textarea>
                    <p class="admin-help">Nombres de ciudades o zonas donde tienen cobertura.</p>
                </div>
            </div>
            <div class="admin-field">
                <label for="social_links">Enlaces sociales</label>
                <textarea id="social_links" name="social_links" rows="4" placeholder="https://facebook.com/expediatravels&#10;https://instagram.com/expediatravels"><?= htmlspecialchars(implode(PHP_EOL, $socialLinks), ENT_QUOTES); ?></textarea>
                <p class="admin-help">Un enlace por línea. Puedes incluir Facebook, Instagram, TikTok, YouTube, etc.</p>
            </div>
            <div class="admin-actions">
                <button class="admin-button" type="submit">Guardar configuración</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <h2>Galería del hero</h2>
        <div class="hero-gallery">
            <?php if (!empty($heroSlides)): ?>
                <form method="post" id="hero-visibility-form">
                    <input type="hidden" name="form_type" value="update_visibility" />
                    <div class="hero-gallery__grid">
                        <?php foreach ($heroSlides as $slideId => $slideData): ?>
                            <?php
                                $slideLabel = $slideData['label'] ?? '';
                                $slidePath = $slideData['path'] ?? '';
                                $slideActive = (bool) ($slideData['activo'] ?? false);
                                $slideAlt = $slideData['alt_text'] ?? '';
                                $slideDescription = $slideData['description'] ?? '';
                            ?>
                            <div class="hero-gallery__item<?= $slideActive ? '' : ' hero-gallery__item--inactive'; ?>">
                                <label class="hero-gallery__select">
                                    <input type="checkbox" class="hero-gallery__checkbox" name="slides_visible[]" value="<?= (int) $slideId; ?>" <?= $slideActive ? 'checked' : ''; ?> />
                                    <div class="hero-gallery__thumbnail">
                                        <?php if ($slidePath !== ''): ?>
                                            <img src="<?= htmlspecialchars($slidePath, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($slideLabel !== '' ? $slideLabel : 'Imagen del slider', ENT_QUOTES); ?>" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="hero-gallery__meta">
                                        <strong><?= htmlspecialchars($slideLabel !== '' ? $slideLabel : 'Imagen sin título'); ?></strong>
                                        <small><?= htmlspecialchars($slidePath, ENT_QUOTES); ?></small>
                                    </div>
                                </label>
                                <button type="button" class="hero-gallery__edit" data-hero-edit-trigger aria-label="Editar imagen">✎</button>
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
                </form>
            <?php else: ?>
                <p class="admin-help">Aún no hay imágenes registradas. Agrega la primera para activar la galería del slider.</p>
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

<script>
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
<?php require __DIR__ . '/plantilla/pie.php'; ?>
