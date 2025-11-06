<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';

use App\Repositories\SiteSettingsRepository;

$repository = new SiteSettingsRepository();

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
            $imageUrl = trim((string) ($_POST['slide_image'] ?? ''));
            $label = trim((string) ($_POST['slide_label'] ?? ''));

            if ($imageUrl === '') {
                $feedback = ['type' => 'error', 'message' => 'Debes ingresar una URL válida para la imagen del hero.'];
            } else {
                $repository->addHeroSlide($imageUrl, $label !== '' ? $label : null);
                $feedback = ['type' => 'success', 'message' => 'Nuevo fondo del hero agregado.'];
            }
        } elseif ($formType === 'delete_slide') {
            $slideId = isset($_POST['slide_id']) ? (int) $_POST['slide_id'] : 0;
            if ($slideId > 0) {
                $repository->deleteHeroSlide($slideId);
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
$heroSlides = $siteSettings['heroSlides'] ?? [];
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
    <link rel="stylesheet" href="../web/css/app.css" />
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
        .hero-slides {
            display: grid;
            gap: 1.25rem;
        }
        .hero-slide {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            min-height: 180px;
            background: #0f172a;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.35);
        }
        .hero-slide::before {
            content: '';
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-image: var(--hero-slide-image);
            opacity: 0.88;
        }
        .hero-slide__meta {
            position: relative;
            z-index: 1;
            padding: 1rem 1.2rem;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.75) 100%);
        }
        .hero-slide__label {
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .hero-slide__actions {
            display: flex;
            justify-content: flex-end;
            padding: 0 1rem 1rem;
        }
        .hero-slide__delete {
            border: none;
            background: rgba(239, 68, 68, 0.95);
            color: #fff;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .hero-slide__delete:hover {
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
            <p class="admin-help">Las imágenes se muestran como slider de fondo en la página principal. Usa enlaces a imágenes optimizadas en alta resolución.</p>
            <div class="hero-slides">
                <?php if (!empty($heroSlides)): ?>
                    <?php foreach ($heroSlides as $slide):
                        $slideId = $slide['id'] ?? null;
                        $slideLabel = $slide['label'] ?? '';
                        $slideImage = $slide['image'] ?? '';
                        if ($slideImage === '') {
                            continue;
                        }
                    ?>
                        <article class="hero-slide" style="--hero-slide-image: url('<?= htmlspecialchars($slideImage, ENT_QUOTES); ?>');">
                            <div class="hero-slide__meta">
                                <div class="hero-slide__label"><?= htmlspecialchars($slideLabel !== '' ? $slideLabel : 'Sin título'); ?></div>
                                <small><?= htmlspecialchars($slideImage); ?></small>
                            </div>
                            <?php if (!empty($slideId)): ?>
                                <div class="hero-slide__actions">
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta imagen del slider?');">
                                        <input type="hidden" name="form_type" value="delete_slide" />
                                        <input type="hidden" name="slide_id" value="<?= (int) $slideId; ?>" />
                                        <button type="submit" class="hero-slide__delete">Eliminar</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="admin-help">Aún no hay imágenes registradas. Agrega la primera para activar el slider.</p>
                <?php endif; ?>
            </div>

            <form method="post" class="admin-grid" style="margin-top: 1.5rem;">
                <input type="hidden" name="form_type" value="add_slide" />
                <div class="admin-grid two-columns">
                    <div class="admin-field">
                        <label for="slide_image">URL de la imagen</label>
                        <input type="url" id="slide_image" name="slide_image" placeholder="https://images.unsplash.com/..." required />
                        <p class="admin-help">Usa imágenes horizontales (1600x900 o superior) y enlaces seguros (HTTPS).</p>
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
</body>
</html>
