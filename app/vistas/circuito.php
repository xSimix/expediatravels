<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}

$title = trim((string) ($detail['title'] ?? ($detail['nombre'] ?? 'Circuito turístico')));
if ($title === '') {
    $title = 'Circuito turístico';
}
$typeLabel = trim((string) ($detail['type'] ?? ($detail['categoria'] ?? 'Circuito')));
$tagline = trim((string) ($detail['tagline'] ?? ($detail['resumen'] ?? '')));
$summaryRaw = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$summary = is_string($summaryRaw) ? trim($summaryRaw) : '';

$heroImage = trim((string) ($detail['heroImage'] ?? ($detail['imagen'] ?? '')));
if ($heroImage === '') {
    $heroImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80';
}

$mapLabel = trim((string) ($detail['mapLabel'] ?? ($detail['location'] ?? 'Mapa del circuito')));
$mapImage = trim((string) ($detail['mapImage'] ?? ''));
$mapUrlRaw = $detail['mapUrl'] ?? ($detail['map_url'] ?? '');
$mapUrl = is_string($mapUrlRaw) ? trim($mapUrlRaw) : '';

$location = trim((string) ($detail['location'] ?? ($detail['destino'] ?? '')));
$region = trim((string) ($detail['region'] ?? ''));
if ($region !== '' && $location !== '') {
    $location .= ' — ' . $region;
} elseif ($location === '' && $region !== '') {
    $location = $region;
}

$chips = is_array($detail['chips'] ?? null) ? array_values(array_filter(array_map('trim', $detail['chips']))) : [];

$duration = trim((string) ($detail['duration'] ?? ($detail['duracion'] ?? '')));
$frequency = trim((string) ($detail['frecuencia'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? ''))));
$group = trim((string) ($detail['grupo'] ?? ($detail['grupo_maximo'] ?? '')));
$experienceLevel = trim((string) ($detail['experiencia'] ?? ($detail['dificultad'] ?? '')));

$stats = is_array($detail['stats'] ?? null) ? $detail['stats'] : [];
$facts = [];
foreach ($stats as $stat) {
    if (!is_array($stat)) {
        continue;
    }
    $label = isset($stat['label']) ? trim((string) $stat['label']) : '';
    $value = isset($stat['value']) ? trim((string) $stat['value']) : '';
    if ($label === '' || $value === '') {
        continue;
    }
    $facts[$label] = $value;
}
if ($duration !== '') {
    $facts['Duración'] = $duration;
}
if ($experienceLevel !== '') {
    $facts['Nivel de experiencia'] = $experienceLevel;
}
if ($group !== '') {
    $facts['Grupo'] = $group;
}
if ($frequency !== '') {
    $facts['Próxima salida'] = $frequency;
}
if ($location !== '') {
    $facts['Destino'] = $location;
}
$factsList = array_map(
    static fn ($label, $value) => ['label' => $label, 'value' => $value],
    array_keys($facts),
    $facts
);

$heroHighlights = [];
if ($duration !== '') {
    $heroHighlights[] = ['icon' => '🗓️', 'label' => 'Duración', 'value' => $duration];
}
if ($group !== '') {
    $heroHighlights[] = ['icon' => '👥', 'label' => 'Grupo', 'value' => $group];
}
if ($experienceLevel !== '') {
    $heroHighlights[] = ['icon' => '⭐', 'label' => 'Experiencia', 'value' => $experienceLevel];
}
if ($frequency !== '') {
    $heroHighlights[] = ['icon' => '🗺️', 'label' => 'Próxima salida', 'value' => $frequency];
}
if ($location !== '') {
    $heroHighlights[] = ['icon' => '📍', 'label' => 'Ubicación', 'value' => $location];
}

$priceFromRaw = $detail['priceFrom'] ?? ($detail['price_from'] ?? '');
$priceFrom = is_string($priceFromRaw) ? trim($priceFromRaw) : '';
$price = $detail['precio'] ?? ($detail['price'] ?? null);
$currency = strtoupper((string) ($detail['moneda'] ?? ($detail['currency'] ?? 'PEN')));
if ($priceFrom === '' && is_numeric($price)) {
    $symbol = match ($currency) {
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => 'S/',
    };
    $priceFrom = sprintf('Desde %s %s', $symbol, number_format((float) $price, 2, '.', ','));
}

$cta = is_array($detail['cta'] ?? null) ? $detail['cta'] : [];
$ctaPrimaryLabel = trim((string) ($cta['primaryLabel'] ?? ''));
$ctaPrimaryHref = trim((string) ($cta['primaryHref'] ?? ''));
$ctaSecondaryLabel = trim((string) ($cta['secondaryLabel'] ?? ''));
$ctaSecondaryHref = trim((string) ($cta['secondaryHref'] ?? ''));

$summaryParagraphs = [];
if ($summary !== '') {
    $summaryParagraphs = preg_split('/\n\s*\n/', $summary) ?: [];
}
$summaryParagraphs = array_values(array_filter(array_map('trim', $summaryParagraphs), static fn ($paragraph) => $paragraph !== ''));
$primarySummaryParagraph = $summaryParagraphs[0] ?? '';
$extraSummaryParagraphs = array_slice($summaryParagraphs, 1);

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
        $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$services = is_array($detail['servicios'] ?? null) ? $detail['servicios'] : [];
$includesList = [];
$excludesList = [];
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
        $excludesList = array_merge($excludesList, $itemsEssential);
    } elseif (str_contains($titleEssential, 'inclu')) {
        $includesList = array_merge($includesList, $itemsEssential);
    }
}
if (empty($includesList) && !empty($services)) {
    $includesList = $normalizeList($services);
}
$includesList = array_values(array_unique($includesList));
$excludesList = array_values(array_unique($excludesList));

$highlightsRaw = is_array($detail['highlights'] ?? null) ? $detail['highlights'] : [];
$highlightItems = [];
foreach ($highlightsRaw as $highlight) {
    if (is_string($highlight)) {
        $label = trim($highlight);
        if ($label !== '') {
            $highlightItems[] = ['title' => $label, 'description' => ''];
        }
        continue;
    }
    if (!is_array($highlight)) {
        continue;
    }
    $titleHighlight = trim((string) ($highlight['title'] ?? ($highlight['label'] ?? '')));
    $descriptionHighlight = trim((string) ($highlight['summary'] ?? ($highlight['description'] ?? '')));
    if ($titleHighlight === '') {
        continue;
    }
    $highlightItems[] = [
        'title' => $titleHighlight,
        'description' => $descriptionHighlight,
    ];
}
if (empty($highlightItems) && !empty($includesList)) {
    foreach (array_slice($includesList, 0, 4) as $includeItem) {
        $highlightItems[] = ['title' => $includeItem, 'description' => ''];
    }
}

$locationsRaw = $detail['locations'] ?? ($detail['stops'] ?? ($detail['destinos'] ?? []));
if (is_string($locationsRaw)) {
    $locationsRaw = $normalizeList($locationsRaw);
}
$mapPoints = [];
if (is_array($locationsRaw)) {
    foreach ($locationsRaw as $item) {
        if (is_string($item)) {
            $label = trim($item);
            if ($label !== '') {
                $mapPoints[] = [
                    'title' => $label,
                    'duration' => '',
                ];
            }
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $titlePoint = trim((string) ($item['title'] ?? ($item['name'] ?? ($item['label'] ?? ''))));
        if ($titlePoint === '') {
            continue;
        }
        $durationPoint = trim((string) ($item['duration'] ?? ($item['dias'] ?? ($item['stay'] ?? ''))));
        $mapPoints[] = [
            'title' => $titlePoint,
            'duration' => $durationPoint,
        ];
    }
}

$mapDefaultUrl = $mapUrl !== '' ? $mapUrl : 'https://maps.google.com/maps?q=' . rawurlencode($location !== '' ? $location : $title) . '&output=embed';

$itineraryRaw = $detail['itinerary'] ?? [];
if (empty($itineraryRaw) && !empty($detail['itinerary_detallado'])) {
    $itineraryRaw = $detail['itinerary_detallado'];
}
$itineraryDays = [];
foreach ($itineraryRaw as $index => $day) {
    if (is_string($day)) {
        $labelDay = trim($day);
        if ($labelDay !== '') {
            $itineraryDays[] = [
                'title' => $labelDay,
                'summary' => '',
                'schedule' => '',
                'activities' => [],
            ];
        }
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['descripcion'] ?? '')));
    $scheduleDay = trim((string) ($day['schedule'] ?? ($day['time'] ?? ($day['horario'] ?? ''))));
    $activitiesDay = $normalizeList($day['activities'] ?? ($day['actividades'] ?? []));
    if ($titleDay === '') {
        $titleDay = 'Día ' . ($index + 1);
    }
    $itineraryDays[] = [
        'title' => $titleDay,
        'summary' => $summaryDay,
        'schedule' => $scheduleDay,
        'activities' => $activitiesDay,
    ];
}

$galleryRaw = is_array($detail['gallery'] ?? null) ? $detail['gallery'] : [];
$galleryItems = [];
foreach ($galleryRaw as $image) {
    if (is_string($image)) {
        $src = trim($image);
        if ($src !== '') {
            $galleryItems[] = ['src' => $src, 'alt' => $title];
        }
        continue;
    }
    if (!is_array($image)) {
        continue;
    }
    $src = trim((string) ($image['src'] ?? ($image['url'] ?? '')));
    if ($src === '') {
        continue;
    }
    $alt = trim((string) ($image['alt'] ?? ($image['label'] ?? $title)));
    $galleryItems[] = ['src' => $src, 'alt' => $alt !== '' ? $alt : $title];
}
if (empty($galleryItems) && $heroImage !== '') {
    $galleryItems[] = ['src' => $heroImage, 'alt' => $title];
}

$relatedRaw = is_array($detail['related'] ?? null) ? $detail['related'] : [];
$relatedCards = [];
foreach ($relatedRaw as $card) {
    if (!is_array($card)) {
        continue;
    }
    $cardTitle = trim((string) ($card['title'] ?? ''));
    $cardSummary = trim((string) ($card['summary'] ?? ($card['description'] ?? '')));
    if ($cardTitle === '' || $cardSummary === '') {
        continue;
    }
    $cardHref = trim((string) ($card['href'] ?? ($card['url'] ?? '#')));
    $cardBadge = trim((string) ($card['badge'] ?? ''));
    $cardImage = trim((string) ($card['image'] ?? ($card['cover'] ?? '')));
    $relatedCards[] = [
        'title' => $cardTitle,
        'summary' => $cardSummary,
        'href' => $cardHref !== '' ? $cardHref : '#',
        'badge' => $cardBadge,
        'image' => $cardImage,
    ];
}

$reviewsSummaryData = is_array($reviewsSummary ?? null) ? $reviewsSummary : [];
$reviewsAverage = isset($reviewsSummaryData['average']) && is_numeric($reviewsSummaryData['average']) ? round((float) $reviewsSummaryData['average'], 1) : null;
$reviewsCountSummary = isset($reviewsSummaryData['count']) && is_numeric($reviewsSummaryData['count']) ? (int) $reviewsSummaryData['count'] : 0;
$rating = $detail['ratingPromedio'] ?? ($detail['rating'] ?? null);
if ($reviewsAverage === null && is_numeric($rating)) {
    $reviewsAverage = round((float) $rating, 1);
}
$reviewsRaw = $detail['totalResenas'] ?? ($detail['reviews'] ?? null);
if ($reviewsCountSummary === 0 && is_numeric($reviewsRaw)) {
    $reviewsCountSummary = (int) $reviewsRaw;
}
$reviewsAverageText = $reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '—';
$reviewsCountText = number_format($reviewsCountSummary);

$reviewsListRaw = is_array($detail['reviewsList'] ?? null) ? $detail['reviewsList'] : [];
$reviewsList = [];
foreach ($reviewsListRaw as $review) {
    if (!is_array($review)) {
        continue;
    }
    $nameReview = trim((string) ($review['nombre'] ?? ($review['usuario'] ?? ($review['name'] ?? ''))));
    if ($nameReview === '') {
        $nameReview = 'Viajero';
    }
    $ratingReview = $review['rating'] ?? ($review['calificacion'] ?? ($review['calificación'] ?? null));
    if (!is_numeric($ratingReview)) {
        $ratingReview = 5;
    }
    $ratingReview = max(1, min(5, (int) $ratingReview));
    $commentReview = trim((string) ($review['comentario'] ?? ($review['comment'] ?? '')));
    if ($commentReview === '') {
        $commentReview = 'Sin comentarios.';
    }
    $createdReview = $review['creado_en'] ?? ($review['fecha'] ?? null);
    if ($createdReview !== null && !is_string($createdReview)) {
        $createdReview = null;
    }
    $reviewsList[] = [
        'nombre' => $nameReview,
        'rating' => $ratingReview,
        'comentario' => $commentReview,
        'creado_en' => $createdReview,
    ];
}

$contact = $siteSettings['contact'] ?? [];
$contactPhones = $normalizeList($contact['phones'] ?? ($siteSettings['contactPhones'] ?? null));
$contactEmails = $normalizeList($contact['emails'] ?? ($siteSettings['contactEmails'] ?? null));
$contactAddresses = $normalizeList($contact['addresses'] ?? ($siteSettings['contactAddresses'] ?? null));
$contactLocations = $normalizeList($contact['locations'] ?? ($siteSettings['contactLocations'] ?? null));

$bookingBenefits = [];
if (!empty($services)) {
    $bookingBenefits = array_slice($normalizeList($services), 0, 3);
}
if (empty($bookingBenefits)) {
    $bookingBenefits = [
        'Reservas flexibles y confirmación en menos de 24h.',
        'Asistencia de especialistas locales durante todo el viaje.',
        'Pagos seguros y protección de datos garantizada.',
    ];
}

$breadcrumbs = [
    ['label' => 'Inicio', 'href' => 'index.php'],
    ['label' => 'Circuitos', 'href' => 'explorar.php'],
    ['label' => $title, 'href' => '#'],
];

$slug = trim((string) ($detail['slug'] ?? ''));
$currentUser = $currentUser ?? null;

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

    <main class="circuit-page">
        <section class="circuit-hero" style="--hero-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>');">
            <div class="circuit-hero__overlay"></div>
            <div class="circuit-hero__inner">
                <div class="circuit-hero__content">
                    <nav class="circuit-breadcrumbs" aria-label="Ruta de navegación">
                        <ol>
                            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                <li>
                                    <?php if ($crumb['href'] !== '#' && $index !== count($breadcrumbs) - 1): ?>
                                        <a href="<?= htmlspecialchars($crumb['href'], ENT_QUOTES); ?>"><?= htmlspecialchars($crumb['label']); ?></a>
                                    <?php else: ?>
                                        <span aria-current="page"><?= htmlspecialchars($crumb['label']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php if ($typeLabel !== ''): ?>
                        <span class="circuit-hero__badge"><?= htmlspecialchars($typeLabel); ?></span>
                    <?php endif; ?>
                    <h1><?= htmlspecialchars($title); ?></h1>
                    <?php if ($tagline !== ''): ?>
                        <p class="circuit-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
                    <?php endif; ?>
                    <div class="circuit-hero__meta">
                        <div class="circuit-hero__rating">
                            <div class="rating-stars rating-stars--lg" data-review-stars style="--rating: <?= htmlspecialchars($reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '0'); ?>;"></div>
                            <div>
                                <p class="circuit-hero__score"><strong data-review-average><?= htmlspecialchars($reviewsAverageText); ?></strong> / 5</p>
                                <p class="circuit-hero__reviews"><span data-review-count><?= htmlspecialchars($reviewsCountText); ?></span> opiniones</p>
                            </div>
                        </div>
                        <?php if (!empty($heroHighlights)): ?>
                            <ul class="circuit-hero__stats">
                                <?php foreach (array_slice($heroHighlights, 0, 4) as $stat): ?>
                                    <li>
                                        <span class="circuit-hero__stat-icon" aria-hidden="true"><?= $stat['icon']; ?></span>
                                        <div>
                                            <span class="circuit-hero__stat-label"><?= htmlspecialchars($stat['label']); ?></span>
                                            <strong><?= htmlspecialchars($stat['value']); ?></strong>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <?php if ($primarySummaryParagraph !== ''): ?>
                        <p class="circuit-hero__summary"><?= htmlspecialchars($primarySummaryParagraph); ?></p>
                    <?php endif; ?>
                    <div class="circuit-hero__actions">
                        <?php if ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
                            <a class="button button--primary" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaPrimaryLabel); ?></a>
                        <?php else: ?>
                            <a class="button button--primary" href="#reserva">Reserva tu lugar</a>
                        <?php endif; ?>
                        <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
                            <a class="button button--ghost" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaSecondaryLabel); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <aside class="circuit-hero__aside" id="reserva">
                    <div class="booking-card">
                        <h2>Reserva tu aventura</h2>
                        <?php if ($priceFrom !== ''): ?>
                            <p class="booking-card__price"><?= htmlspecialchars($priceFrom); ?></p>
                        <?php endif; ?>
                        <?php if ($frequency !== ''): ?>
                            <p class="booking-card__note">Próxima salida: <strong><?= htmlspecialchars($frequency); ?></strong></p>
                        <?php endif; ?>
                        <form class="booking-form" method="post" action="api/reservas-circuitos.php" data-reservation-form>
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($slug, ENT_QUOTES); ?>" />
                            <input type="hidden" name="titulo" value="<?= htmlspecialchars($title, ENT_QUOTES); ?>" />
                            <label>
                                <span>Nombre completo *</span>
                                <input type="text" name="nombre" required autocomplete="name" />
                            </label>
                            <label>
                                <span>Correo electrónico *</span>
                                <input type="email" name="correo" required autocomplete="email" />
                            </label>
                            <label>
                                <span>Teléfono de contacto</span>
                                <input type="tel" name="telefono" autocomplete="tel" placeholder="Opcional" />
                            </label>
                            <div class="booking-form__row">
                                <label>
                                    <span>Fecha preferida</span>
                                    <input type="date" name="fecha" />
                                </label>
                                <label>
                                    <span>Personas</span>
                                    <input type="number" name="cantidad_personas" min="1" value="1" />
                                </label>
                            </div>
                            <label>
                                <span>Mensaje adicional</span>
                                <textarea name="mensaje" rows="3" placeholder="Cuéntanos qué esperas de este viaje"></textarea>
                            </label>
                            <button class="button button--primary" type="submit" data-loading>Solicitar información</button>
                            <div class="form-status" data-reservation-status></div>
                        </form>
                        <ul class="booking-card__benefits">
                            <?php foreach ($bookingBenefits as $benefit): ?>
                                <li><?= htmlspecialchars($benefit); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>

        <div class="circuit-body">
            <div class="circuit-main">
                <section class="circuit-section" id="descripcion">
                    <header class="section-header">
                        <h2>Disfruta la aventura</h2>
                        <p>Descubre qué hace único a este circuito y cómo se adapta a tu estilo de viaje.</p>
                    </header>
                    <div class="section-content">
                        <?php foreach ($summaryParagraphs as $paragraph): ?>
                            <p><?= htmlspecialchars($paragraph); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($factsList)): ?>
                        <div class="feature-grid">
                            <?php foreach (array_slice($factsList, 0, 6) as $fact): ?>
                                <article class="feature-card">
                                    <span class="feature-card__label"><?= htmlspecialchars($fact['label']); ?></span>
                                    <strong><?= htmlspecialchars($fact['value']); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if (!empty($includesList) || !empty($excludesList)): ?>
                    <section class="circuit-section" id="incluye">
                        <header class="section-header">
                            <h2>Incluye &amp; No incluye</h2>
                            <p>Transparencia total para planificar con confianza.</p>
                        </header>
                        <div class="includes-grid">
                            <?php if (!empty($includesList)): ?>
                                <div class="includes-column">
                                    <h3>Incluye</h3>
                                    <ul>
                                        <?php foreach ($includesList as $item): ?>
                                            <li><?= htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($excludesList)): ?>
                                <div class="includes-column">
                                    <h3>No incluye</h3>
                                    <ul>
                                        <?php foreach ($excludesList as $item): ?>
                                            <li><?= htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="mapa">
                    <header class="section-header">
                        <h2><?= htmlspecialchars($mapLabel !== '' ? $mapLabel : 'Mapa del circuito'); ?></h2>
                        <p>Visualiza el recorrido y ubica los puntos clave del circuito.</p>
                    </header>
                    <div class="map-wrapper">
                        <iframe src="<?= htmlspecialchars($mapDefaultUrl, ENT_QUOTES); ?>" title="Mapa del circuito" loading="lazy" allowfullscreen></iframe>
                    </div>
                    <?php if (!empty($mapPoints)): ?>
                        <ul class="map-points">
                            <?php foreach (array_slice($mapPoints, 0, 6) as $point): ?>
                                <li>
                                    <strong><?= htmlspecialchars($point['title']); ?></strong>
                                    <?php if ($point['duration'] !== ''): ?>
                                        <span><?= htmlspecialchars($point['duration']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <?php if (!empty($itineraryDays)): ?>
                    <section class="circuit-section" id="itinerario">
                        <header class="section-header">
                            <h2>Itinerario detallado</h2>
                            <p>Una mirada día a día para que sepas exactamente qué esperar.</p>
                        </header>
                        <ol class="itinerary">
                            <?php foreach ($itineraryDays as $index => $day): ?>
                                <li>
                                    <div class="itinerary__day">Día <?= $index + 1; ?></div>
                                    <div class="itinerary__content">
                                        <h3><?= htmlspecialchars($day['title']); ?></h3>
                                        <?php if ($day['schedule'] !== ''): ?>
                                            <p class="itinerary__schedule"><strong>Horario:</strong> <?= htmlspecialchars($day['schedule']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($day['summary'] !== ''): ?>
                                            <p><?= htmlspecialchars($day['summary']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($day['activities'])): ?>
                                            <ul class="itinerary__activities">
                                                <?php foreach ($day['activities'] as $activity): ?>
                                                    <li><?= htmlspecialchars($activity); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if (!empty($galleryItems)): ?>
                    <section class="circuit-section" id="galeria">
                        <header class="section-header">
                            <h2>Galería de experiencias</h2>
                            <p>Inspírate con momentos capturados en el circuito.</p>
                        </header>
                        <div class="gallery-grid">
                            <?php foreach ($galleryItems as $image): ?>
                                <figure>
                                    <img src="<?= htmlspecialchars($image['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES); ?>" loading="lazy" />
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="opiniones">
                    <header class="section-header">
                        <h2>Opiniones de viajeros</h2>
                        <p>Historias reales de quienes ya vivieron esta experiencia.</p>
                    </header>
                    <div class="reviews">
                        <div class="reviews__summary">
                            <div class="rating-stars rating-stars--lg" data-review-stars-secondary style="--rating: <?= htmlspecialchars($reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '0'); ?>;"></div>
                            <div>
                                <p class="reviews__average"><strong data-review-average-secondary><?= htmlspecialchars($reviewsAverageText); ?></strong> / 5</p>
                                <p class="reviews__count"><span data-review-count-secondary><?= htmlspecialchars($reviewsCountText); ?></span> opiniones totales</p>
                            </div>
                        </div>
                        <ul class="reviews__list" data-review-list>
                            <?php if (empty($reviewsList)): ?>
                                <li class="reviews__empty">Sé la primera persona en dejar una reseña sobre este circuito.</li>
                            <?php else: ?>
                                <?php foreach ($reviewsList as $review): ?>
                                    <li class="review-card">
                                        <div class="review-card__header">
                                            <strong><?= htmlspecialchars($review['nombre']); ?></strong>
                                            <div class="review-card__stars" style="--rating: <?= htmlspecialchars(number_format((float) $review['rating'], 1, '.', '')); ?>;" aria-label="<?= htmlspecialchars($review['rating']); ?> de 5"></div>
                                        </div>
                                        <p class="review-card__comment"><?= htmlspecialchars($review['comentario']); ?></p>
                                        <?php if (!empty($review['creado_en'])): ?>
                                            <small class="review-card__date">Publicado el <?= htmlspecialchars($review['creado_en']); ?></small>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <form class="reviews__form" method="post" action="api/resenas-circuitos.php" data-review-form>
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($slug, ENT_QUOTES); ?>" />
                            <input type="hidden" name="titulo" value="<?= htmlspecialchars($title, ENT_QUOTES); ?>" />
                            <div class="form-grid">
                                <label>
                                    <span>Nombre completo *</span>
                                    <input type="text" name="nombre" required autocomplete="name" />
                                </label>
                                <label>
                                    <span>Correo electrónico</span>
                                    <input type="email" name="correo" autocomplete="email" placeholder="Opcional" />
                                </label>
                                <label>
                                    <span>Calificación *</span>
                                    <select name="rating" required>
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?= $i; ?>"><?= $i; ?> estrella<?= $i === 1 ? '' : 's'; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                            </div>
                            <label>
                                <span>Tu reseña *</span>
                                <textarea name="comentario" rows="4" required placeholder="Comparte detalles de tu experiencia"></textarea>
                            </label>
                            <button class="button button--primary" type="submit" data-loading>Enviar reseña</button>
                            <div class="form-status" data-review-status></div>
                        </form>
                    </div>
                </section>

                <?php if (!empty($relatedCards)): ?>
                    <section class="circuit-section" id="relacionados">
                        <header class="section-header">
                            <h2>Otros circuitos recomendados</h2>
                            <p>Explora más aventuras seleccionadas especialmente para ti.</p>
                        </header>
                        <div class="card-grid">
                            <?php foreach ($relatedCards as $card): ?>
                                <article class="related-card">
                                    <?php if ($card['image'] !== ''): ?>
                                        <figure class="related-card__media">
                                            <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES); ?>" loading="lazy" />
                                        </figure>
                                    <?php endif; ?>
                                    <div class="related-card__body">
                                        <?php if ($card['badge'] !== ''): ?>
                                            <span class="related-card__badge"><?= htmlspecialchars($card['badge']); ?></span>
                                        <?php endif; ?>
                                        <h3><?= htmlspecialchars($card['title']); ?></h3>
                                        <p><?= htmlspecialchars($card['summary']); ?></p>
                                        <a class="button button--ghost" href="<?= htmlspecialchars($card['href'], ENT_QUOTES); ?>">Ver circuito</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="circuit-aside">
                <?php if (!empty($highlightItems)): ?>
                    <section class="aside-card">
                        <h3>Lo que amarás de este viaje</h3>
                        <ul>
                            <?php foreach ($highlightItems as $highlight): ?>
                                <li>
                                    <strong><?= htmlspecialchars($highlight['title']); ?></strong>
                                    <?php if ($highlight['description'] !== ''): ?>
                                        <span><?= htmlspecialchars($highlight['description']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <section class="aside-card aside-card--contact">
                    <h3>Contacto directo</h3>
                    <ul>
                        <?php if (!empty($contactPhones)): ?>
                            <?php foreach ($contactPhones as $phone): ?>
                                <li><span aria-hidden="true">📞</span> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $phone)); ?>"><?= htmlspecialchars($phone); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($contactEmails)): ?>
                            <?php foreach ($contactEmails as $email): ?>
                                <li><span aria-hidden="true">✉️</span> <a href="mailto:<?= htmlspecialchars($email); ?>"><?= htmlspecialchars($email); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($contactAddresses)): ?>
                            <?php foreach ($contactAddresses as $address): ?>
                                <li><span aria-hidden="true">📍</span> <?= htmlspecialchars($address); ?></li>
                            <?php endforeach; ?>
                        <?php elseif (!empty($contactLocations)): ?>
                            <?php foreach ($contactLocations as $contactLocation): ?>
                                <li><span aria-hidden="true">📍</span> <?= htmlspecialchars($contactLocation); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>

                <section class="aside-card aside-card--accent">
                    <h3>Garantía Expediatravels</h3>
                    <p>Estamos contigo en cada paso, desde la planificación hasta tu regreso a casa.</p>
                    <ul>
                        <li>Atención 24/7 durante el viaje.</li>
                        <li>Seguros y asistencia internacional disponible.</li>
                        <li>Expertos locales certificados.</li>
                    </ul>
                </section>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>
    <?php include __DIR__ . '/partials/auth-modal.php'; ?>
    <script>
        window.circuitoPageConfig = {
            reviewEndpoint: 'api/resenas-circuitos.php',
            reservationEndpoint: 'api/reservas-circuitos.php'
        };
    </script>
    <script src="scripts/circuito.js" defer></script>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
