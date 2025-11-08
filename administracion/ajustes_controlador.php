<?php

declare(strict_types=1);

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;

require_once __DIR__ . '/../app/configuracion/arranque.php';

return (static function (): array {
    $repository = new RepositorioConfiguracionSitio();
    $feedback = null;

    $parseTextarea = static function (?string $value): array {
        if ($value === null) {
            return [];
        }

        $items = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(
            array_filter(
                array_map('trim', $items),
                static fn (string $item): bool => $item !== ''
            )
        );
    };

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = $_POST['form_type'] ?? '';

            if ($formType === 'site_settings') {
                $logoPath = trim((string) ($_POST['site_logo'] ?? ($_POST['current_site_logo'] ?? '')));
                $faviconPath = trim((string) ($_POST['site_favicon'] ?? ($_POST['current_site_favicon'] ?? '')));
                $logoUpload = $_FILES['site_logo_file'] ?? null;
                $faviconUpload = $_FILES['site_favicon_file'] ?? null;
                $uploadFailed = false;

                if (is_array($logoUpload) && ($logoUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if (($logoUpload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !isset($logoUpload['tmp_name']) || !is_uploaded_file($logoUpload['tmp_name'])) {
                        $feedback = ['type' => 'error', 'message' => 'No se pudo subir el logo. Inténtalo nuevamente.'];
                        $uploadFailed = true;
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($logoUpload['tmp_name']);
                        $allowedMimeTypes = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                            'image/svg+xml' => 'svg',
                        ];

                        if (!isset($allowedMimeTypes[$mimeType])) {
                            $feedback = ['type' => 'error', 'message' => 'Formato de logo no permitido. Usa imágenes JPG, PNG, WEBP o SVG.'];
                            $uploadFailed = true;
                        } else {
                            $uploadDirectory = __DIR__ . '/../web/cargas/logos';

                            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                                $feedback = ['type' => 'error', 'message' => 'No se pudo preparar la carpeta de subida del logo.'];
                                $uploadFailed = true;
                            } else {
                                $extension = $allowedMimeTypes[$mimeType];
                                $filename = 'logo-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
                                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

                                if (!move_uploaded_file($logoUpload['tmp_name'], $destination)) {
                                    $feedback = ['type' => 'error', 'message' => 'No se pudo guardar el logo en el servidor.'];
                                    $uploadFailed = true;
                                } else {
                                    $publicPath = '/web/cargas/logos/' . $filename;

                                    $normalizedCurrent = ltrim($logoPath, '/');
                                    if ($normalizedCurrent !== '' && str_starts_with($normalizedCurrent, 'web/cargas/logos/')) {
                                        $absoluteCurrent = dirname(__DIR__) . '/' . $normalizedCurrent;
                                        if (is_file($absoluteCurrent)) {
                                            @unlink($absoluteCurrent);
                                        }
                                    }

                                    $logoPath = $publicPath;
                                }
                            }
                        }
                    }
                }

                if (!$uploadFailed && is_array($faviconUpload) && ($faviconUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if (($faviconUpload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !isset($faviconUpload['tmp_name']) || !is_uploaded_file($faviconUpload['tmp_name'])) {
                        $feedback = ['type' => 'error', 'message' => 'No se pudo subir el favicon. Inténtalo nuevamente.'];
                        $uploadFailed = true;
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($faviconUpload['tmp_name']);
                        $allowedMimeTypes = [
                            'image/png' => 'png',
                            'image/jpeg' => 'jpg',
                            'image/webp' => 'webp',
                            'image/svg+xml' => 'svg',
                            'image/x-icon' => 'ico',
                            'image/vnd.microsoft.icon' => 'ico',
                        ];

                        if (!isset($allowedMimeTypes[$mimeType])) {
                            $feedback = ['type' => 'error', 'message' => 'Formato de favicon no permitido. Usa imágenes PNG, JPG, WEBP, SVG o ICO.'];
                            $uploadFailed = true;
                        } else {
                            $uploadDirectory = __DIR__ . '/../web/cargas/favicons';

                            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                                $feedback = ['type' => 'error', 'message' => 'No se pudo preparar la carpeta de subida del favicon.'];
                                $uploadFailed = true;
                            } else {
                                $extension = $allowedMimeTypes[$mimeType];
                                $filename = 'favicon-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
                                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

                                if (!move_uploaded_file($faviconUpload['tmp_name'], $destination)) {
                                    $feedback = ['type' => 'error', 'message' => 'No se pudo guardar el favicon en el servidor.'];
                                    $uploadFailed = true;
                                } else {
                                    $publicPath = '/web/cargas/favicons/' . $filename;

                                    $normalizedCurrent = ltrim($faviconPath, '/');
                                    if ($normalizedCurrent !== '' && str_starts_with($normalizedCurrent, 'web/cargas/favicons/')) {
                                        $absoluteCurrent = dirname(__DIR__) . '/' . $normalizedCurrent;
                                        if (is_file($absoluteCurrent)) {
                                            @unlink($absoluteCurrent);
                                        }
                                    }

                                    $faviconPath = $publicPath;
                                }
                            }
                        }
                    }
                }

                if (!$uploadFailed) {
                    $repository->update([
                        'siteLogo' => $logoPath,
                        'siteFavicon' => $faviconPath,
                        'siteTitle' => $_POST['site_title'] ?? '',
                        'siteTagline' => $_POST['site_tagline'] ?? '',
                        'contactEmails' => $parseTextarea($_POST['contact_emails'] ?? null),
                        'contactPhones' => $parseTextarea($_POST['contact_phones'] ?? null),
                        'contactAddresses' => $parseTextarea($_POST['contact_addresses'] ?? null),
                        'contactLocations' => $parseTextarea($_POST['contact_locations'] ?? null),
                        'socialLinks' => $parseTextarea($_POST['social_links'] ?? null),
                    ]);

                    $feedback = ['type' => 'success', 'message' => 'Configuración general guardada correctamente.'];
                }
            } elseif ($formType === 'add_slide') {
                $label = trim((string) ($_POST['slide_label'] ?? ''));
                $imagePath = trim((string) ($_POST['slide_image'] ?? ''));
                $upload = $_FILES['slide_upload'] ?? null;

                if ($imagePath !== '') {
                    $repository->addHeroSlide($imagePath, $label !== '' ? $label : null);
                    $feedback = ['type' => 'success', 'message' => 'Nuevo fondo del hero agregado desde la biblioteca de medios.'];
                } elseif (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
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
                        $uploadDirectory = __DIR__ . '/../web/cargas/hero';

                        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                            $feedback = ['type' => 'error', 'message' => 'No se pudo preparar la carpeta de subida de imágenes.'];
                        } else {
                            $extension = $allowedMimeTypes[$mimeType];
                            $filename = 'hero-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
                            $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

                            if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                                $feedback = ['type' => 'error', 'message' => 'No se pudo guardar la imagen en el servidor.'];
                            } else {
                                $publicPath = '/web/cargas/hero/' . $filename;
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
                        if (str_starts_with($normalizedPath, 'web/cargas/hero/')) {
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
    $siteLogo = $siteSettings['siteLogo'] ?? null;
    $siteFavicon = $siteSettings['siteFavicon'] ?? null;
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
    $socialLinks = [];

    if (!empty($contact['social']) && is_array($contact['social'])) {
        foreach ($contact['social'] as $entry) {
            if (is_array($entry)) {
                $label = isset($entry['label']) ? trim((string) $entry['label']) : '';
                $url = isset($entry['url']) ? trim((string) $entry['url']) : '';

                if ($label === '' && $url === '') {
                    continue;
                }

                if ($label === '' || $label === $url) {
                    $socialLinks[] = $url;
                } else {
                    $socialLinks[] = sprintf('%s | %s', $label, $url);
                }
            } elseif (is_string($entry)) {
                $trimmed = trim($entry);

                if ($trimmed !== '') {
                    $socialLinks[] = $trimmed;
                }
            }
        }
    }

    $renderTextarea = static fn (array $items): string => htmlspecialchars(implode("\n", $items), ENT_QUOTES);

    if (!is_string($siteLogo) || trim($siteLogo) === '') {
        $siteLogo = null;
    }

    if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
        $siteFavicon = null;
    }

    return [
        'feedback' => $feedback,
        'siteLogo' => $siteLogo,
        'siteFavicon' => $siteFavicon,
        'siteTitle' => $siteTitle,
        'siteTagline' => $siteTagline,
        'heroSlides' => $heroSlides,
        'visibleHeroSlideIds' => $visibleHeroSlideIds,
        'contactEmails' => $contactEmails,
        'contactPhones' => $contactPhones,
        'contactAddresses' => $contactAddresses,
        'contactLocations' => $contactLocations,
        'socialLinks' => $socialLinks,
        'renderTextarea' => $renderTextarea,
    ];
})();
