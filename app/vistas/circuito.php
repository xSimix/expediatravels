<?php
$detail = is_array($detail ?? null) ? $detail : [];
$siteSettings = is_array($siteSettings ?? null) ? $siteSettings : [];

$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}

$title = trim((string) ($detail['title'] ?? ($detail['nombre'] ?? '')));
if ($title === '') {
    $title = 'Circuito sin nombre';
}

$typeLabel = trim((string) ($detail['type'] ?? ($detail['type_tag'] ?? ($detail['categoria'] ?? 'Circuito'))));
if ($typeLabel === '') {
    $typeLabel = 'Circuito';
}

$tagline = trim((string) ($detail['tagline'] ?? ($detail['resumen'] ?? '')));

$heroImage = trim((string) ($detail['heroImage'] ?? ($detail['imagen'] ?? '')));
if ($heroImage === '') {
    $heroImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80';
}

$videoUrlCandidates = [
    $detail['video_destacado_url'] ?? null,
    $detail['video_destacado'] ?? null,
    $detail['featured_video_url'] ?? null,
    $detail['featuredVideoUrl'] ?? null,
    $detail['video'] ?? null,
];
$featuredVideoUrl = '';
foreach ($videoUrlCandidates as $candidate) {
    if (!is_string($candidate)) {
        continue;
    }
    $candidate = trim($candidate);
    if ($candidate === '') {
        continue;
    }
    $featuredVideoUrl = $candidate;
    break;
}

$videoLightboxSource = '';
$videoLightboxKind = 'embed';
if ($featuredVideoUrl !== '') {
    $videoLightboxSource = $featuredVideoUrl;
    $parsedUrl = parse_url($featuredVideoUrl);
    $host = strtolower($parsedUrl['host'] ?? '');
    $path = $parsedUrl['path'] ?? '';
    $extension = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));

    if (in_array($extension, ['mp4', 'm4v', 'webm', 'ogv', 'ogg'], true)) {
        $videoLightboxKind = 'file';
    } elseif ($host !== '') {
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            $videoId = '';
            if (str_contains($host, 'youtu.be')) {
                $videoId = ltrim((string) $path, '/');
            } else {
                if (str_starts_with((string) $path, '/embed/')) {
                    $videoId = trim(substr((string) $path, 7));
                } elseif (str_starts_with((string) $path, '/shorts/')) {
                    $videoId = trim(substr((string) $path, 8));
                } else {
                    parse_str($parsedUrl['query'] ?? '', $queryParams);
                    $videoId = (string) ($queryParams['v'] ?? '');
                }
            }
            $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId ?? '') ?? '';
            if ($videoId !== '') {
                $videoLightboxSource = 'https://www.youtube.com/embed/' . $videoId;
            }
        } elseif (str_contains($host, 'vimeo.com')) {
            $segments = array_values(array_filter(explode('/', (string) $path)));
            $videoId = end($segments);
            $videoId = preg_replace('/[^0-9]/', '', $videoId ?? '') ?? '';
            if ($videoId !== '') {
                $videoLightboxSource = 'https://player.vimeo.com/video/' . $videoId;
            }
        }
    }
}

$galleryRaw = $detail['gallery'] ?? ($detail['galeria'] ?? []);
if (is_string($galleryRaw) && trim($galleryRaw) !== '') {
    $decodedGallery = json_decode($galleryRaw, true);
    if (is_array($decodedGallery)) {
        $galleryRaw = $decodedGallery;
    } else {
        $galleryRaw = preg_split("/\r\n|\r|\n/", trim($galleryRaw)) ?: [];
    }
}
if (!is_array($galleryRaw)) {
    $galleryRaw = [];
}

$galleryImages = [];
foreach ($galleryRaw as $galleryItem) {
    if (is_string($galleryItem)) {
        $src = trim($galleryItem);
        if ($src === '') {
            continue;
        }
        $galleryImages[] = [
            'src' => $src,
            'alt' => $title,
        ];
        continue;
    }

    if (!is_array($galleryItem)) {
        continue;
    }

    $src = $galleryItem['src'] ?? $galleryItem['url'] ?? $galleryItem['image'] ?? null;
    if (is_string($src)) {
        $src = trim($src);
    } else {
        $src = '';
    }
    if ($src === '') {
        continue;
    }

    $alt = $galleryItem['alt'] ?? $galleryItem['label'] ?? $galleryItem['title'] ?? '';
    if (!is_string($alt)) {
        $alt = '';
    }
    $alt = trim($alt);
    if ($alt === '') {
        $alt = $title;
    }

    $galleryImages[] = [
        'src' => $src,
        'alt' => $alt,
    ];
}

if (empty($galleryImages) && $heroImage !== '') {
    $galleryImages[] = [
        'src' => $heroImage,
        'alt' => $title,
    ];
}

$locationParts = [
    trim((string) ($detail['location'] ?? ($detail['destino'] ?? ''))),
    trim((string) ($detail['region'] ?? ($detail['pais'] ?? ''))),
];
$locationParts = array_values(array_filter($locationParts, static fn ($value) => $value !== ''));
$location = implode(', ', array_unique($locationParts));

$ratingValue = null;
foreach (['rating', 'valoracion', 'score', 'averageRating'] as $ratingKey) {
    if (isset($detail[$ratingKey]) && is_numeric($detail[$ratingKey])) {
        $ratingValue = round((float) $detail[$ratingKey], 1);
        break;
    }
}
if ($ratingValue === null) {
    $ratingValue = 4.9;
}

$reviewsCount = 0;
foreach (['reviews', 'totalResenas', 'reviewsCount', 'cantidad_resenas'] as $reviewsKey) {
    if (isset($detail[$reviewsKey]) && is_numeric($detail[$reviewsKey])) {
        $reviewsCount = (int) $detail[$reviewsKey];
        break;
    }
}
if ($reviewsCount <= 0) {
    $reviewsCount = 128;
}

$duration = trim((string) ($detail['duration'] ?? ($detail['duracion'] ?? '3 días / 2 noches')));
if ($duration === '') {
    $duration = '3 días / 2 noches';
}

$tourType = trim((string) ($detail['tourType'] ?? ($detail['tipo'] ?? $typeLabel)));
if ($tourType === '') {
    $tourType = $typeLabel;
}

$groupSize = trim((string) ($detail['group'] ?? ($detail['tamano_grupo'] ?? ($detail['grupo_maximo'] ?? 'Hasta 12 viajeros'))));
if ($groupSize === '') {
    $groupSize = 'Hasta 12 viajeros';
}

$normalizeList = static function ($value): array {
    if (is_array($value)) {
        $items = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $text = trim($item);
                if ($text !== '') {
                    $items[] = $text;
                }
                continue;
            }
            if (!is_array($item)) {
                continue;
            }
            $text = trim((string) ($item['text'] ?? ($item['label'] ?? ($item['title'] ?? ''))));
            if ($text === '') {
                continue;
            }
            $items[] = $text;
        }
        return $items;
    }

    if (is_string($value) && trim($value) !== '') {
        $parts = preg_split('/\r\n|\r|\n|[,;]+/', trim($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$languagesList = $normalizeList($detail['languages'] ?? ($detail['idiomas'] ?? null));
if (empty($languagesList)) {
    $languagesList = ['Español', 'Inglés'];
}

$summaryRaw = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$summaryText = is_string($summaryRaw) ? trim($summaryRaw) : '';
if ($summaryText === '') {
    $summaryText = 'Explora paisajes emblemáticos acompañado por guías expertos que combinan aventura, cultura y confort en cada momento del recorrido.';
}
$aboutParagraphs = preg_split('/\n\s*\n/', $summaryText) ?: [];
$aboutParagraphs = array_values(array_filter(array_map('trim', $aboutParagraphs), static fn ($paragraph) => $paragraph !== ''));
if (empty($aboutParagraphs)) {
    $aboutParagraphs = [$summaryText];
}

$highlightsRaw = $detail['highlights'] ?? [];
if (!is_array($highlightsRaw)) {
    $highlightsRaw = $normalizeList($highlightsRaw);
}
$highlights = [];
foreach ($highlightsRaw as $highlight) {
    if (is_string($highlight)) {
        $text = trim($highlight);
        if ($text !== '') {
            $highlights[] = $text;
        }
        continue;
    }
    if (!is_array($highlight)) {
        continue;
    }
    $text = trim((string) ($highlight['title'] ?? ($highlight['label'] ?? ($highlight['summary'] ?? ''))));
    if ($text !== '') {
        $highlights[] = $text;
    }
}
if (empty($highlights)) {
    $highlights = [
        'Recorridos exclusivos por los paisajes más icónicos.',
        'Guías certificados con amplia experiencia local.',
        'Transporte premium y cómodas paradas fotográficas.',
        'Actividades seleccionadas para todos los gustos.',
    ];
}

$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$includes = [];
$excludes = [];
foreach ($essentials as $essential) {
    if (!is_array($essential)) {
        continue;
    }
    $titleEssential = strtolower(trim((string) ($essential['title'] ?? '')));
    $itemsEssential = $normalizeList($essential['items'] ?? []);
    if (empty($itemsEssential)) {
        continue;
    }
    if (str_contains($titleEssential, 'no incluye') || str_contains($titleEssential, 'exclu')) {
        $excludes = array_merge($excludes, $itemsEssential);
    } elseif (str_contains($titleEssential, 'inclu')) {
        $includes = array_merge($includes, $itemsEssential);
    }
}
if (empty($includes)) {
    $includes = $normalizeList($detail['included'] ?? ($detail['incluye'] ?? []));
}
if (empty($excludes)) {
    $excludes = $normalizeList($detail['excluded'] ?? ($detail['no_incluye'] ?? []));
}
if (empty($includes)) {
    $includes = ['Transporte turístico de lujo', 'Guía certificado bilingüe', 'Entradas a los parques indicados', 'Snacks y bebidas a bordo'];
}
if (empty($excludes)) {
    $excludes = ['Gastos personales y souvenirs', 'Propinas opcionales', 'Seguro de viaje'];
}

$itineraryRaw = $detail['itinerary'] ?? ($detail['itinerario'] ?? []);
if (!is_array($itineraryRaw)) {
    $itineraryRaw = [];
}
$itineraryDays = [];
foreach ($itineraryRaw as $index => $day) {
    if (is_string($day)) {
        $titleDay = trim($day);
        if ($titleDay === '') {
            continue;
        }
        $itineraryDays[] = [
            'title' => $titleDay,
            'description' => '',
            'mapUrl' => '',
        ];
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['description'] ?? ($day['descripcion'] ?? ''))));
    $mapUrl = trim((string) ($day['map_url'] ?? ($day['map'] ?? ($day['maps'] ?? ($day['ubicacion_maps'] ?? '')))));
    if ($mapUrl !== '' && !preg_match('/^https?:\/\//i', $mapUrl)) {
        $mapUrl = '';
    }
    if ($titleDay === '') {
        $titleDay = 'Experiencia destacada';
    }
    $itineraryDays[] = [
        'title' => $titleDay,
        'description' => $summaryDay,
        'mapUrl' => $mapUrl,
    ];
}
if (empty($itineraryDays)) {
    $itineraryDays = [
        ['title' => 'Bienvenida y exploración urbana', 'description' => 'Recorrido panorámico por los principales atractivos con tiempo libre para fotografía y degustaciones locales.', 'mapUrl' => ''],
        ['title' => 'Aventuras al aire libre', 'description' => 'Caminatas guiadas por senderos icónicos y visitas a miradores exclusivos.', 'mapUrl' => ''],
        ['title' => 'Experiencia cultural y despedida', 'description' => 'Intercambio cultural con comunidades locales y almuerzo de despedida.', 'mapUrl' => ''],
    ];
}

$faqRaw = $detail['faq'] ?? ($detail['preguntasFrecuentes'] ?? ($detail['preguntas_frecuentes'] ?? []));
if (!is_array($faqRaw)) {
    $faqRaw = [];
}
$faqItems = [];
foreach ($faqRaw as $faq) {
    if (is_string($faq)) {
        $question = trim($faq);
        if ($question !== '') {
            $faqItems[] = [
                'question' => $question,
                'answer' => 'Nuestro equipo te brindará la información que necesitas para planificar tu viaje.',
            ];
        }
        continue;
    }
    if (!is_array($faq)) {
        continue;
    }
    $question = trim((string) ($faq['question'] ?? ($faq['pregunta'] ?? '')));
    $answer = trim((string) ($faq['answer'] ?? ($faq['respuesta'] ?? '')));
    if ($question === '') {
        continue;
    }
    if ($answer === '') {
        $answer = 'Responderemos personalmente cada detalle para que disfrutes del circuito sin preocupaciones.';
    }
    $faqItems[] = ['question' => $question, 'answer' => $answer];
}
if (empty($faqItems)) {
    $faqItems = [
        [
            'question' => '¿Cuál es la mejor época para realizar este circuito?',
            'answer' => 'La temporada seca ofrece cielos despejados y temperaturas agradables, ideales para disfrutar cada actividad del itinerario.',
        ],
        [
            'question' => '¿El transporte está incluido durante todo el recorrido?',
            'answer' => 'Sí, contamos con movilidad privada, conductores experimentados y paradas estratégicas para tu comodidad.',
        ],
        [
            'question' => '¿Puedo personalizar algunas actividades?',
            'answer' => 'Podemos adaptar experiencias según tus intereses con aviso previo. Escríbenos para ayudarte.',
        ],
    ];
}

$priceCandidates = [
    $detail['price_from'] ?? null,
    $detail['priceFrom'] ?? null,
    $detail['precio_desde'] ?? null,
    $detail['precio'] ?? null,
];
$priceFrom = null;
foreach ($priceCandidates as $candidate) {
    if ($candidate === null || $candidate === '') {
        continue;
    }
    if (is_numeric($candidate)) {
        $priceFrom = 'S/ ' . number_format((float) $candidate, 2, '.', '');
        break;
    }
    if (is_string($candidate)) {
        $normalized = trim($candidate);
        if ($normalized !== '') {
            $digits = preg_replace('/[^0-9,.]/', '', $normalized) ?? '';
            if ($digits !== '') {
                $digits = str_replace(',', '', $digits);
                if (is_numeric($digits)) {
                    $priceFrom = 'S/ ' . number_format((float) $digits, 2, '.', '');
                    break;
                }
            }
            $priceFrom = $normalized;
            break;
        }
    }
}
if ($priceFrom === null) {
    $priceFrom = 'S/ 1,299.00';
}

$departuresRaw = $detail['departures'] ?? ($detail['fechas'] ?? null);
$departureOptions = $normalizeList($departuresRaw);
if (empty($departureOptions)) {
    $departureOptions = ['Selecciona una fecha', 'Próximo sábado', 'Próximo miércoles'];
} else {
    array_unshift($departureOptions, 'Selecciona una fecha');
}

$guideData = is_array($detail['guide'] ?? null) ? $detail['guide'] : ($detail['guia'] ?? []);
if (!is_array($guideData)) {
    $guideData = [];
}

$guideAvatar = trim((string) ($guideData['avatar'] ?? ($guideData['foto'] ?? '')));
if ($guideAvatar === '') {
    $guideAvatar = 'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=320&q=80';
}

$contactSettings = is_array($siteSettings['contact'] ?? null) ? $siteSettings['contact'] : [];
$contactEmail = trim((string) ($contactSettings['email'] ?? ($contactSettings['correo'] ?? ($siteSettings['contactEmail'] ?? 'hola@expediatravels.pe'))));
if ($contactEmail === '') {
    $contactEmail = 'hola@expediatravels.pe';
}
$contactPhone = trim((string) ($contactSettings['phone'] ?? ($contactSettings['telefono'] ?? '+51 999 888 777')));
if ($contactPhone === '') {
    $contactPhone = '+51 999 888 777';
}
$contactWebsite = trim((string) ($contactSettings['website'] ?? 'www.expediatravels.pe'));
if ($contactWebsite === '') {
    $contactWebsite = 'www.expediatravels.pe';
}
$contactFax = trim((string) ($contactSettings['fax'] ?? '—'));
if ($contactFax === '') {
    $contactFax = '—';
}

$formatWhatsappLink = static function (string $phone) use ($contactPhone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') {
        $digits = preg_replace('/\D+/', '', $contactPhone);
    }
    if ($digits === '') {
        return '#';
    }
    if (strpos($digits, '00') === 0) {
        $digits = substr($digits, 2);
    }
    if (strpos($digits, '51') !== 0) {
        $digits = ltrim($digits, '0');
        if ($digits === '') {
            $digits = preg_replace('/\D+/', '', $contactPhone) ?: '';
        }
        if ($digits === '') {
            return '#';
        }
        $digits = '51' . $digits;
    }

    return 'https://wa.me/' . $digits;
};

$generateInitials = static function (string $name): string {
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';
    $count = 0;
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        if (function_exists('mb_substr')) {
            $char = mb_substr($part, 0, 1, 'UTF-8');
        } else {
            $char = substr($part, 0, 1);
        }
        if (function_exists('mb_strtoupper')) {
            $char = mb_strtoupper($char, 'UTF-8');
        } else {
            $char = strtoupper($char);
        }
        $initials .= $char;
        $count++;
        if ($count >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'EV';
};

$salesAdvisersRaw = $detail['salesAdvisers'] ?? ($contactSettings['advisers'] ?? ($siteSettings['salesAdvisers'] ?? []));
if (!is_array($salesAdvisersRaw)) {
    $salesAdvisersRaw = [];
}
if (empty($salesAdvisersRaw)) {
    $salesAdvisersRaw = [
        ['name' => 'María López', 'phone' => '+51 987 654 321', 'role' => 'Especialista en circuitos'],
        ['name' => 'Jorge Ramírez', 'phone' => '+51 945 123 456', 'role' => 'Atención personalizada'],
    ];
}

$salesAdvisers = [];
foreach ($salesAdvisersRaw as $advisor) {
    $name = '';
    $phone = '';
    $role = '';

    if (is_string($advisor)) {
        $name = trim($advisor);
    } elseif (is_array($advisor)) {
        $name = trim((string) ($advisor['name'] ?? ($advisor['nombre'] ?? '')));
        $phone = trim((string) ($advisor['phone'] ?? ($advisor['telefono'] ?? '')));
        $role = trim((string) ($advisor['role'] ?? ($advisor['cargo'] ?? ($advisor['especialidad'] ?? ''))));
    }

    if ($name === '') {
        continue;
    }

    if ($phone === '') {
        $phone = $contactPhone;
    }

    $salesAdvisers[] = [
        'name' => $name,
        'role' => $role,
        'phone' => $phone,
        'initials' => $generateInitials($name),
        'whatsapp' => $formatWhatsappLink($phone),
    ];
}

$languagesBadges = array_map(static fn ($language) => ['label' => $language], $languagesList);

$durationBadges = [
    ['icon' => '🕒', 'label' => '3 – 5 horas'],
    ['icon' => '🧭', 'label' => '5 – 7 horas'],
    ['icon' => '🌄', 'label' => 'Jornada completa'],
];

$pageTitle = $title . ' — ' . $siteTitle;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <link rel="stylesheet" href="estilos/circuito.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
</head>
<body class="page page--detail page--circuit">
    <?php $activeNav = 'circuitos'; include __DIR__ . '/partials/site-header.php'; ?>

    <main class="tour-detail">
        <section class="tour-banner" style="--banner-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>');">
            <div class="tour-banner__overlay" aria-hidden="true"></div>
            <div class="tour-banner__content">
                <h1><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="tour-banner__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <div class="tour-banner__meta">
                    <span class="tour-banner__meta-item" aria-label="Valoración">
                        <span class="tour-banner__icon" aria-hidden="true">⭐</span>
                        <?= htmlspecialchars(number_format($ratingValue, 1, '.', '')); ?> · <?= htmlspecialchars($reviewsCount); ?> reseñas
                    </span>
                    <?php if ($location !== ''): ?>
                        <span class="tour-banner__meta-item" aria-label="Ubicación">
                            <span class="tour-banner__icon" aria-hidden="true">📍</span>
                            <?= htmlspecialchars($location); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($videoLightboxSource !== ''): ?>
                        <button
                            type="button"
                            class="tour-banner__meta-item tour-banner__meta-button"
                            data-video-lightbox-trigger
                            data-video-src="<?= htmlspecialchars($videoLightboxSource, ENT_QUOTES); ?>"
                            data-video-kind="<?= htmlspecialchars($videoLightboxKind, ENT_QUOTES); ?>"
                            aria-haspopup="dialog"
                        >
                            <span class="tour-banner__icon" aria-hidden="true">▶️</span>
                            Ver video
                        </button>
                    <?php endif; ?>
                </div>
                <div class="tour-banner__info">
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">⏱️</span>
                        <div>
                            <p>Duración</p>
                            <strong><?= htmlspecialchars($duration); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">🧭</span>
                        <div>
                            <p>Tipo de circuito</p>
                            <strong><?= htmlspecialchars($tourType); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">👥</span>
                        <div>
                            <p>Tamaño del grupo</p>
                            <strong><?= htmlspecialchars($groupSize); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">💬</span>
                        <div>
                            <p>Idiomas</p>
                            <strong><?= htmlspecialchars(implode(', ', $languagesList)); ?></strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <div class="tour-detail__layout">
            <div class="tour-detail__left">

                            <section class="detail-section detail-section--about" id="about">
                    <header>
                        <h2>Sobre este circuito</h2>
                    </header>
                    <?php foreach ($aboutParagraphs as $paragraph): ?>
                        <p><?= htmlspecialchars($paragraph); ?></p>
                    <?php endforeach; ?>
                </section>

                

                <?php if (!empty($galleryImages)):
                    $gallerySlides = array_chunk($galleryImages, 2);
                    $hasMultipleSlides = count($gallerySlides) > 1;
                ?>
                    <section
                        class="detail-section detail-section--gallery detail-gallery"
                        id="galeria"
                        data-gallery-slider
                        data-gallery-interval="6000"
                    >
                        <header>
                            <h2>Galería del circuito</h2>
                            <p>Explora una selección de momentos destacados de esta experiencia.</p>
                        </header>
                        <div class="detail-gallery__viewport" data-gallery-viewport>
                            <div class="detail-gallery__track" data-gallery-track>
                                <?php foreach ($gallerySlides as $slideImages): ?>
                                    <div class="detail-gallery__slide" data-gallery-slide>
                                        <?php foreach ($slideImages as $image):
                                            $src = $image['src'] ?? '';
                                            if (!is_string($src) || trim($src) === '') {
                                                continue;
                                            }
                                            $src = trim($src);
                                            $alt = $image['alt'] ?? $title;
                                            if (!is_string($alt) || trim($alt) === '') {
                                                $alt = $title;
                                            }
                                            $alt = trim($alt);
                                            $parsedPath = parse_url($src, PHP_URL_PATH);
                                            $fileName = is_string($parsedPath) ? basename($parsedPath) : '';
                                            $caption = $fileName !== '' ? $fileName : $alt;
                                            $buttonLabel = $caption !== ''
                                                ? 'Ver imagen: ' . $caption
                                                : 'Ver imagen del circuito';
                                        ?>
                                            <figure class="detail-gallery__item">
                                                <button
                                                    type="button"
                                                    class="detail-gallery__media"
                                                    data-gallery-lightbox-trigger
                                                    data-lightbox-src="<?= htmlspecialchars($src, ENT_QUOTES); ?>"
                                                    data-lightbox-alt="<?= htmlspecialchars($alt, ENT_QUOTES); ?>"
                                                    data-lightbox-caption="<?= htmlspecialchars($caption, ENT_QUOTES); ?>"
                                                    aria-label="<?= htmlspecialchars($buttonLabel, ENT_QUOTES); ?>"
                                                >
                                                    <img class="detail-gallery__image" src="<?= htmlspecialchars($src, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($alt, ENT_QUOTES); ?>" loading="lazy" />
                                                </button>
                                                <?php if ($caption !== ''): ?>
                                                    <figcaption class="detail-gallery__caption"><?= htmlspecialchars($caption); ?></figcaption>
                                                <?php endif; ?>
                                            </figure>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php if ($hasMultipleSlides): ?>
                            <div class="detail-gallery__dots" role="tablist" aria-label="Paginación de galería">
                                <?php foreach ($gallerySlides as $index => $_): ?>
                                    <button
                                        type="button"
                                        class="detail-gallery__dot<?= $index === 0 ? ' is-active' : ''; ?>"
                                        data-gallery-dot="<?= $index; ?>"
                                        aria-label="Ir a la página <?= $index + 1; ?>"
                                        <?= $index === 0 ? 'aria-current="true"' : ''; ?>
                                    ></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                    <div class="detail-lightbox" data-gallery-lightbox role="dialog" aria-modal="true" tabindex="-1" hidden>
                        <div class="detail-lightbox__backdrop" data-lightbox-close aria-hidden="true"></div>
                        <figure class="detail-lightbox__figure">
                            <button type="button" class="detail-lightbox__close" data-lightbox-close aria-label="Cerrar galería">×</button>
                            <img class="detail-lightbox__image" src="" alt="" data-lightbox-image />
                            <figcaption class="detail-lightbox__caption" data-lightbox-caption></figcaption>
                        </figure>
                        </div>
                <?php endif; ?>

                <?php if ($videoLightboxSource !== ''): ?>
                    <div class="detail-lightbox" data-video-lightbox role="dialog" aria-modal="true" aria-label="Video destacado del circuito" tabindex="-1" hidden>
                        <div class="detail-lightbox__backdrop" data-lightbox-close aria-hidden="true"></div>
                        <figure class="detail-lightbox__figure">
                            <button type="button" class="detail-lightbox__close" data-lightbox-close aria-label="Cerrar video">×</button>
                            <div class="detail-lightbox__video" data-video-lightbox-container>
                                <iframe
                                    class="detail-lightbox__video-frame"
                                    src=""
                                    title="Video destacado"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    data-video-frame
                                    hidden
                                ></iframe>
                                <video class="detail-lightbox__video-player" controls playsinline data-video-player hidden></video>
                            </div>
                            <figcaption class="detail-lightbox__caption">Disfruta una vista previa del circuito.</figcaption>
                        </figure>
                        </div>
                <?php endif; ?>


                            <section class="detail-section" id="itinerary">
                    <header>
                        <h2>Itinerario</h2>
                    </header>
                    <div class="accordion" data-accordion="itinerary">
                        <?php foreach ($itineraryDays as $index => $day): ?>
                            <?php $isOpen = $index === 0; ?>
                            <article class="accordion__item<?= $isOpen ? ' is-open' : ''; ?>" data-accordion-item>
                                <button type="button" class="accordion__trigger" data-accordion-trigger aria-expanded="<?= $isOpen ? 'true' : 'false'; ?>">
                                    <span class="accordion__day">Día <?= $index + 1; ?></span>
                                    <span class="accordion__title"><?= htmlspecialchars($day['title']); ?></span>
                                    <span class="accordion__icon" aria-hidden="true"></span>
                                </button>
                                <div class="accordion__content" data-accordion-content<?= $isOpen ? '' : ' hidden'; ?>>
                                    <?php $descriptionText = $day['description'] !== '' ? $day['description'] : 'Descubre actividades seleccionadas para este día del circuito.'; ?>
                                    <p><?= htmlspecialchars($descriptionText); ?></p>
                                    <?php if (!empty($day['mapUrl'])): ?>
                                        <a class="accordion__map-link" href="<?= htmlspecialchars($day['mapUrl'], ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer">
                                            <span aria-hidden="true">📍</span>
                                            <span>Ubicación en Google Maps</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                 <section class="detail-section detail-section--split" id="included">
                    <header>
                        <h2>Incluye / No incluye</h2>
                    </header>
                    <div class="split-columns">
                        <div class="split-columns__item">
                            <h3>Incluye</h3>
                            <ul>
                                <?php foreach ($includes as $item): ?>
                                    <li>
                                        <span class="split-columns__icon split-columns__icon--include" aria-hidden="true">
                                            <svg viewBox="0 0 20 20" focusable="false">
                                                <path fill="currentColor" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.54-9.46-4 4a.75.75 0 0 1-1.08 0l-2-2a.75.75 0 0 1 1.06-1.06L8.5 11.44l3.46-3.47a.75.75 0 0 1 1.06 1.06z" />
                                            </svg>
                                        </span>
                                        <span class="split-columns__text"><?= htmlspecialchars($item); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="split-columns__item">
                            <h3>No incluye</h3>
                            <ul>
                                <?php foreach ($excludes as $item): ?>
                                    <li>
                                        <span class="split-columns__icon split-columns__icon--exclude" aria-hidden="true">
                                            <svg viewBox="0 0 20 20" focusable="false">
                                                <path fill="currentColor" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm2.47-10.53-1.94 1.94 1.94 1.94a.75.75 0 1 1-1.06 1.06l-1.94-1.94-1.94 1.94a.75.75 0 0 1-1.06-1.06l1.94-1.94-1.94-1.94a.75.75 0 0 1 1.06-1.06l1.94 1.94 1.94-1.94a.75.75 0 1 1 1.06 1.06z" />
                                            </svg>
                                        </span>
                                        <span class="split-columns__text"><?= htmlspecialchars($item); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>


                <section class="detail-section" id="highlights">
                    <header>
                        <h2>Puntos destacados</h2>
                    </header>
                    <ul class="highlight-list">
                        <?php foreach ($highlights as $highlight): ?>
                            <li><?= htmlspecialchars($highlight); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>

               




                
            </div>

            <aside class="tour-detail__right">
                <section class="aside-card aside-card--pricing">
                    <div class="pricing-header">
                        <span class="pricing-label">Precio referencial</span>
                        <span class="pricing-amount"><?= htmlspecialchars($priceFrom); ?></span>
                    </div>
                    <p class="pricing-note">Tarifa calculada por viajero en base doble. Escríbenos para una propuesta personalizada.</p>
                </section>

                <section class="aside-card aside-card--booking">
                    <form
                        class="reservation-form"
                        action="https://wa.me/51930140668"
                        method="get"
                        data-whatsapp-booking
                        data-package-name="<?= htmlspecialchars($title, ENT_QUOTES); ?>"
                    >
                        <header class="reservation-form__header">
                            <span class="reservation-form__badge">Reserva en minutos</span>
                            <h3 class="reservation-form__title">Formulario de reserva</h3>
                            <p class="reservation-form__subtitle">
                                Comparte tus datos y coordinaremos tu experiencia por WhatsApp.
                            </p>
                        </header>
                        <div class="reservation-form__fields">
                            <label class="reservation-field reservation-field--full">
                                <span class="reservation-field__label">Fecha de viaje</span>
                                <input
                                    class="reservation-field__input"
                                    type="date"
                                    name="date"
                                    min="<?= htmlspecialchars(date('Y-m-d')); ?>"
                                    required
                                />
                            </label>
                            <fieldset class="reservation-form__travellers">
                                <legend>Viajeros</legend>
                                <p>Indica la cantidad de viajeros de cada grupo de edad.</p>
                                <div class="reservation-form__counter-grid">
                                    <div class="reservation-counter" data-counter>
                                        <span class="reservation-counter__label">Adultos</span>
                                        <div class="reservation-counter__controls">
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-decrease
                                                aria-label="Reducir adultos"
                                            >
                                                &minus;
                                            </button>
                                            <input
                                                type="number"
                                                name="adults"
                                                value="2"
                                                min="1"
                                                inputmode="numeric"
                                                aria-label="Número de adultos"
                                            />
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-increase
                                                aria-label="Aumentar adultos"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                    <div class="reservation-counter" data-counter>
                                        <span class="reservation-counter__label">Niños</span>
                                        <div class="reservation-counter__controls">
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-decrease
                                                aria-label="Reducir niños"
                                            >
                                                &minus;
                                            </button>
                                            <input
                                                type="number"
                                                name="children"
                                                value="0"
                                                min="0"
                                                inputmode="numeric"
                                                aria-label="Número de niños"
                                            />
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-increase
                                                aria-label="Aumentar niños"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                    <div class="reservation-counter" data-counter>
                                        <span class="reservation-counter__label">Adultos mayores</span>
                                        <div class="reservation-counter__controls">
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-decrease
                                                aria-label="Reducir adultos mayores"
                                            >
                                                &minus;
                                            </button>
                                            <input
                                                type="number"
                                                name="seniors"
                                                value="0"
                                                min="0"
                                                inputmode="numeric"
                                                aria-label="Número de adultos mayores"
                                            />
                                            <button
                                                type="button"
                                                class="reservation-counter__btn"
                                                data-counter-increase
                                                aria-label="Aumentar adultos mayores"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="reservation-more">
                                <input
                                    type="checkbox"
                                    class="reservation-more__control"
                                    id="reservation-more-control"
                                    data-more-options-toggle
                                    aria-controls="reservation-more-content"
                                    aria-expanded="false"
                                />
                                <label class="reservation-more__label" for="reservation-more-control">
                                    <span class="reservation-more__title">Más opciones</span>
                                    <span class="reservation-more__description">Comparte detalles especiales de tu reserva.</span>
                                </label>
                                <div class="reservation-more-options" id="reservation-more-content" data-more-options>
                                    <div class="reservation-form__field-grid">
                                        <label class="reservation-field">
                                            <span class="reservation-field__label">Personas con discapacidad</span>
                                            <select
                                                class="reservation-field__input reservation-field__input--select"
                                                name="disabilitySupport"
                                            >
                                                <option value="">Sin requerimientos especiales</option>
                                                <option value="Se requiere asistencia para personas con discapacidad">Se requiere asistencia para personas con discapacidad</option>
                                                <option value="Se confirmará más adelante">Se confirmará más adelante</option>
                                            </select>
                                        </label>
                                        <label class="reservation-field">
                                            <span class="reservation-field__label">Mascotas</span>
                                            <select
                                                class="reservation-field__input reservation-field__input--select"
                                                name="pets"
                                            >
                                                <option value="">No viajamos con mascotas</option>
                                                <option value="Viajaremos con mascotas">Viajaremos con mascotas</option>
                                            </select>
                                        </label>
                                    </div>
                                    <label class="reservation-field reservation-field--full">
                                        <span class="reservation-field__label">Condición médica especial</span>
                                        <textarea
                                            class="reservation-field__input reservation-field__input--textarea"
                                            name="medicalNotes"
                                            placeholder="Describe alergias, tratamientos o asistencia médica requerida"
                                        ></textarea>
                                    </label>
                                    <label class="reservation-field reservation-field--full">
                                        <span class="reservation-field__label">Comentarios adicionales</span>
                                        <textarea
                                            class="reservation-field__input reservation-field__input--textarea"
                                            name="message"
                                            placeholder="Información extra que debamos tener en cuenta"
                                        ></textarea>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <footer class="reservation-form__footer">
                            <button type="submit" class="reservation-form__submit">
                                <span class="reservation-form__submit-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" role="img" focusable="false">
                                        <path
                                            fill="currentColor"
                                            d="M16 3a13 13 0 0 0-11 20.08L3.5 28.5A1 1 0 0 0 4.91 30L9 27.81A13 13 0 1 0 16 3Zm0 24a11 11 0 1 1 9.49-5.52 1 1 0 0 0-.12.68l.52 3.09-2.7-1.41a1 1 0 0 0-.86-.05A10.94 10.94 0 0 1 16 27Zm6.12-7.94c-.33-.16-1.94-1-2.24-1.16s-.52-.16-.74.16-.84 1-.93 1.21-.34.25-.62.09a7.71 7.71 0 0 1-2.26-1.38 8.51 8.51 0 0 1-1.55-1.92c-.17-.28 0-.42.12-.56s.28-.36.41-.53a1.82 1.82 0 0 0 .28-.5.54.54 0 0 0 0-.48c-.07-.14-.62-1.46-.86-2s-.44-.48-.62-.48-.35 0-.55 0a1.05 1.05 0 0 0-.73.35 3.22 3.22 0 0 0-1 2.33 5.56 5.56 0 0 0 1.16 2.89 12.53 12.53 0 0 0 4.8 3.95 16.3 16.3 0 0 0 1.6.56 3.82 3.82 0 0 0 1.75.11 2.84 2.84 0 0 0 1.86-1.31 2.36 2.36 0 0 0 .16-1.31c-.06-.11-.24-.21-.57-.37Z"
                                        />
                                    </svg>
                                </span>
                                <span class="reservation-form__submit-label">Enviar por WhatsApp</span>
                            </button>
                            <p class="reservation-form__note">Se abrirá una conversación con toda tu información de reserva.</p>
                        </footer>
                    </form>
                    <div class="sales-advisers" aria-labelledby="sales-advisers-title">
                        <h4 id="sales-advisers-title">Asesores de Venta</h4>
                        <p class="sales-advisers__description">¿Necesitas ayuda? Chatea con un especialista por WhatsApp.</p>
                        <div class="sales-advisers__list">
                            <?php foreach ($salesAdvisers as $advisor): ?>
                                <article class="sales-adviser-card">
                                    <div class="sales-adviser-card__avatar" aria-hidden="true"><?= htmlspecialchars($advisor['initials']); ?></div>
                                    <div class="sales-adviser-card__body">
                                        <h5><?= htmlspecialchars($advisor['name']); ?></h5>
                                        <?php if ($advisor['role'] !== ''): ?>
                                            <p><?= htmlspecialchars($advisor['role']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a class="sales-adviser-card__cta" href="<?= htmlspecialchars($advisor['whatsapp'], ENT_QUOTES); ?>" target="_blank" rel="noopener">
                                        <span aria-hidden="true">💬</span>
                                        Chatear
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>

    <script src="scripts/circuito.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
