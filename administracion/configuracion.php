<?php

declare(strict_types=1);

require_once __DIR__ . '/../aplicacion/configuracion/arranque.php';

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;

$repository = new RepositorioConfiguracionSitio();

$feedback = null;

$parseTextarea = static function (?string $value): array {
    if ($value === null) {
        return [];
    }

    $items = preg_split('/\r\n|\r|\n/', $value) ?: [];

    return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
};

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formType = $_POST['form_type'] ?? '';

        if ($formType === 'site_settings') {
            $repository->update([
                'siteTitle' => $_POST['site_title'] ?? '',
                'siteTagline' => $_POST['site_tagline'] ?? '',
                'contactEmails' => $parseTextarea($_POST['contact_emails'] ?? null),
                'contactPhones' => $parseTextarea($_POST['contact_phones'] ?? null),
                'contactAddresses' => $parseTextarea($_POST['contact_addresses'] ?? null),
                'contactLocations' => $parseTextarea($_POST['contact_locations'] ?? null),
                'socialLinks' => $parseTextarea($_POST['social_links'] ?? null),
            ]);

            $feedback = ['type' => 'success', 'message' => 'Configuración general guardada correctamente.'];
        } elseif ($formType === 'add_slide') {
            $label = trim((string) ($_POST['slide_label'] ?? ''));
            $upload = $_FILES['slide_upload'] ?? null;

            if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $feedback = ['type' => 'error', 'message' => 'Debes seleccionar una imagen para el hero.'];
            } elseif (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
                $feedback = ['type' => 'error', 'message' => 'No se pudo subir la imagen. Inténtalo nuevamente.'];
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($upload['tmp_name']);
                $allowedMimeTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];

                if (!isset($allowedMimeTypes[$mimeType])) {
                    $feedback = ['type' => 'error', 'message' => 'Formato no permitido. Usa imágenes JPG, PNG o WEBP.'];
                } else {
                    $uploadDirectory = __DIR__ . '/../sitio_web/cargas/hero';

                    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                        $feedback = ['type' => 'error', 'message' => 'No se pudo preparar la carpeta de subida de imágenes.'];
                    } else {
                        $extension = $allowedMimeTypes[$mimeType];
                        $filename = 'hero-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
                        $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

                        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                            $feedback = ['type' => 'error', 'message' => 'No se pudo guardar la imagen en el servidor.'];
                        } else {
                            $publicPath = '/sitio_web/cargas/hero/' . $filename;
                            $repository->addHeroSlide($publicPath, $label !== '' ? $label : null);
                            $feedback = ['type' => 'success', 'message' => 'Nuevo fondo del hero agregado y almacenado en el sitio.'];
                        }
                    }
                }
            }
        } elseif ($formType === 'update_slide') {
            $slideId = isset($_POST['slide_id']) ? (int) $_POST['slide_id'] : 0;

            if ($slideId > 0) {
                $repository->updateHeroSlide($slideId, [
                    'label' => $_POST['slide_label'] ?? null,
                    'alt_text' => $_POST['slide_alt_text'] ?? null,
                    'description' => $_POST['slide_description'] ?? null,
                ]);

                $feedback = ['type' => 'success', 'message' => 'Metadatos de la imagen actualizados correctamente.'];
            }
        } elseif ($formType === 'update_visibility') {
            $visibleSlides = $_POST['visible_slides'] ?? [];
            $repository->updateHeroVisibility(is_array($visibleSlides) ? $visibleSlides : []);

            $feedback = ['type' => 'success', 'message' => 'Visibilidad del slider actualizada.'];
        } elseif ($formType === 'delete_slide') {
            $slideId = isset($_POST['slide_id']) ? (int) $_POST['slide_id'] : 0;
            if ($slideId > 0) {
                $imagePath = $repository->deleteHeroSlide($slideId);

                if ($imagePath) {
                    $normalizedPath = ltrim($imagePath, '/');
                    if (str_starts_with($normalizedPath, 'sitio_web/cargas/hero/')) {
                        $absolutePath = dirname(__DIR__) . '/' . $normalizedPath;
                        if (is_file($absolutePath)) {
                            @unlink($absolutePath);
                        }
                    }
                }

                $feedback = ['type' => 'success', 'message' => 'Imagen eliminada del slider del hero.'];
            }
        }
    }
} catch (Throwable $exception) {
    $feedback = ['type' => 'error', 'message' => 'Ocurrió un error al guardar los cambios.'];
}

$siteSettings = $repository->get();
$siteTitle = $siteSettings['siteTitle'] ?? 'Expediatravels';
$siteTagline = $siteSettings['siteTagline'] ?? '';
$heroSlides = $repository->getHeroSlides(false);
$visibleHeroSlideIds = [];
foreach ($heroSlides as $slide) {
    if (!empty($slide['isVisible']) && isset($slide['id'])) {
        $visibleHeroSlideIds[(int) $slide['id']] = true;
    }
}
$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$contactPhones = $contact['phones'] ?? [];
$contactAddresses = $contact['addresses'] ?? [];
$contactLocations = $contact['locations'] ?? [];
$socialLinks = $contact['social'] ?? [];

$renderTextarea = static fn (array $items): string => htmlspecialchars(implode("\n", $items), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Configuración del sitio — Expediatravels</title>
    <link rel="stylesheet" href="../sitio_web/estilos/aplicacion.css" />
    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
        }
        .admin-wrapper {
            max-width: 1080px;
            margin: 0 auto;
            padding: clamp(2rem, 4vw, 4rem) clamp(1.5rem, 4vw, 3rem);
            display: grid;
            gap: 2.5rem;
        }
        .admin-card {
            background: #ffffff;
            border-radius: 28px;
            padding: clamp(1.8rem, 3vw, 2.6rem);
            box-shadow: 0 25px 65px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.25);
            display: grid;
            gap: 1.75rem;
        }
        .admin-card h2 {
            margin: 0;
            font-size: 1.45rem;
        }
        .admin-grid {
            display: grid;
            gap: 1.5rem;
        }
        .admin-grid.two-columns {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
        .admin-field {
            display: grid;
            gap: 0.5rem;
        }
        .admin-field label {
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: #475569;
        }
        .admin-field input,
        .admin-field textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            font: inherit;
            resize: vertical;
            min-height: 52px;
            background: rgba(248, 250, 252, 0.7);
        }
        .admin-field textarea {
            min-height: 120px;
        }
        .admin-help {
            font-size: 0.85rem;
            color: #64748b;
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
        .hero-gallery {
            display: grid;
            gap: 1.5rem;
        }
        .hero-gallery__grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            opacity: 0.65;
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
        .hero-gallery__select {
            display: grid;
            gap: 0.9rem;
            color: inherit;
            text-decoration: none;
            padding: 1.1rem 1.15rem 1rem;
            cursor: pointer;
            position: relative;
        }
        .hero-gallery__checkbox {
            position: absolute;
            top: 16px;
            left: 16px;
            width: 22px;
            height: 22px;
            accent-color: #38bdf8;
            cursor: pointer;
        }
        .hero-gallery__thumbnail {
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 16 / 9;
            background: rgba(15, 23, 42, 0.45);
        }
        .hero-gallery__thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .hero-gallery__meta {
            display: grid;
            gap: 0.4rem;
        }
        .hero-gallery__meta strong {
            font-size: 0.95rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .hero-gallery__meta small {
            font-size: 0.78rem;
            color: rgba(226, 232, 240, 0.75);
            word-break: break-word;
        }
        .hero-gallery__delete-form {
            position: absolute;
            right: 12px;
            bottom: 12px;
        }
        .hero-gallery__edit {
            position: absolute;
            top: 12px;
            right: 12px;
            border: none;
            background: rgba(15, 23, 42, 0.85);
            color: #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .hero-gallery__edit:hover {
            background: rgba(56, 189, 248, 0.95);
            color: #0f172a;
            transform: translateY(-1px);
        }
        .hero-gallery__delete {
            border: none;
            background: rgba(239, 68, 68, 0.95);
            color: #fff;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            transition: background 0.2s ease;
        }
        .hero-gallery__delete:hover {
            background: rgba(220, 38, 38, 1);
        }
        .admin-alert {
            border-radius: 20px;
            padding: 1rem 1.5rem;
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.35);
        }
        .admin-alert.error {
            background: rgba(248, 113, 113, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #b91c1c;
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
        .admin-header {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .admin-header p {
            margin: 0;
            color: #64748b;
        }
    </style>
</head>
<body>
    <main class="admin-wrapper">
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
            <form method="post" class="admin-grid">
                <input type="hidden" name="form_type" value="site_settings" />
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

        <section class="admin-card">
            <h2>Fondos del hero</h2>
            <p class="admin-help">Las imágenes se muestran como slider de fondo en la página principal. Al subirlas se guardan en el hosting del sitio dentro de <code>sitio_web/cargas/hero</code>.</p>
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
    </main>
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
</body>
</html>
