<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}
$title = $detail['title'] ?? ($detail['nombre'] ?? 'Circuito turístico');
$pageTitle = $title;
$typeLabel = $detail['type'] ?? ($detail['categoria'] ?? 'Circuito');
$tagline = $detail['tagline'] ?? ($detail['resumen'] ?? '');
$summary = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$heroImage = $detail['heroImage'] ?? ($detail['imagen'] ?? '');
$mapImage = $detail['mapImage'] ?? '';
$mapLabel = $detail['mapLabel'] ?? ($detail['location'] ?? 'Mapa de referencia');
$location = $detail['location'] ?? ($detail['destino'] ?? '');
$region = $detail['region'] ?? '';
if ($region !== '' && $location !== '') {
    $location = $location . ' — ' . $region;
} elseif ($location === '' && $region !== '') {
    $location = $region;
}
$chips = is_array($detail['chips'] ?? null) ? array_values(array_filter($detail['chips'])) : [];
$stats = is_array($detail['stats'] ?? null) ? $detail['stats'] : [];
$highlights = is_array($detail['highlights'] ?? null) ? $detail['highlights'] : [];
$itinerary = $detail['itinerary'] ?? [];
if (empty($itinerary) && !empty($detail['itinerary_detallado'])) {
    $itinerary = $detail['itinerary_detallado'];
}
$experiences = is_array($detail['experiences'] ?? null) ? $detail['experiences'] : [];
$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$related = is_array($detail['related'] ?? null) ? $detail['related'] : [];
$services = is_array($detail['servicios'] ?? null) ? $detail['servicios'] : [];
$gallery = is_array($detail['gallery'] ?? null) ? $detail['gallery'] : [];

$reviewsSummary = is_array($reviewsSummary ?? null) ? $reviewsSummary : [];
$reviewsListRaw = is_array($detail['reviewsList'] ?? null) ? $detail['reviewsList'] : [];

$duration = $detail['duration'] ?? ($detail['duracion'] ?? '');
$frequency = $detail['frecuencia'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? ''));
$group = $detail['grupo'] ?? ($detail['grupo_maximo'] ?? '');
$experienceLevel = $detail['experiencia'] ?? ($detail['dificultad'] ?? '');
$rating = $detail['ratingPromedio'] ?? ($detail['rating'] ?? null);
if (is_numeric($rating)) {
    $rating = round((float) $rating, 1);
} else {
    $rating = null;
}
$reviews = $detail['totalResenas'] ?? ($detail['reviews'] ?? null);
if (is_numeric($reviews)) {
    $reviews = (int) $reviews;
} else {
    $reviews = null;
}

$priceFrom = $detail['priceFrom'] ?? ($detail['price_from'] ?? '');
if (is_string($priceFrom)) {
    $priceFrom = trim($priceFrom);
} else {
    $priceFrom = '';
}
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
if (trim((string) $summary) !== '') {
    $summaryParagraphs = preg_split('/\n\s*\n/', trim((string) $summary)) ?: [];
}
$primarySummaryParagraph = $summaryParagraphs[0] ?? '';
$extraSummaryParagraphs = array_slice($summaryParagraphs, 1);

$normalizeList = static function ($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map(static fn ($item) => is_string($item) ? trim($item) : null, $value), static fn ($item) => $item !== null && $item !== ''));
    }

    if (is_string($value) && trim($value) !== '') {
        $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$contact = $siteSettings['contact'] ?? [];
$contactPhones = $normalizeList($contact['phones'] ?? ($siteSettings['contactPhones'] ?? null));
$contactEmails = $normalizeList($contact['emails'] ?? ($siteSettings['contactEmails'] ?? null));
$contactAddresses = $normalizeList($contact['addresses'] ?? ($siteSettings['contactAddresses'] ?? null));
$contactLocations = $normalizeList($contact['locations'] ?? ($siteSettings['contactLocations'] ?? null));

$metaBadges = [];
if ($duration !== '') {
    $metaBadges[] = ['text' => '⏱️ ' . $duration];
}
if (!empty($chips)) {
    $metaBadges[] = ['text' => '🏷️ ' . $chips[0]];
} elseif ($typeLabel !== '') {
    $metaBadges[] = ['text' => '🏷️ ' . $typeLabel];
}
if ($location !== '') {
    $metaBadges[] = ['text' => '📍 ' . $location];
}
if ($priceFrom !== '') {
    $metaBadges[] = ['text' => '💸 ' . $priceFrom, 'variant' => 'price'];
}
if ($frequency !== '') {
    $metaBadges[] = ['text' => '🗓️ ' . $frequency];
}

$facts = [];
foreach ($stats as $stat) {
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
    $facts['Nivel'] = $experienceLevel;
}
if ($frequency !== '') {
    $facts['Frecuencia'] = $frequency;
}
if ($group !== '') {
    $facts['Grupo'] = $group;
}
if ($location !== '') {
    $facts['Destino'] = $location;
}
$facts = array_slice(array_map(static fn ($label, $value) => ['label' => $label, 'value' => $value], array_keys($facts), $facts), 0, 6);

$locationsRaw = $detail['locations'] ?? ($detail['stops'] ?? ($detail['destinos'] ?? []));
if (is_string($locationsRaw)) {
    $locationsRaw = $normalizeList($locationsRaw);
}
$locationsList = [];
if (is_array($locationsRaw)) {
    foreach ($locationsRaw as $item) {
        if (is_string($item)) {
            $titleLocation = trim($item);
            if ($titleLocation === '') {
                continue;
            }
            $locationsList[] = [
                'title' => $titleLocation,
                'duration' => '',
                'image' => $heroImage,
            ];
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $titleLocation = trim((string) ($item['title'] ?? ($item['name'] ?? ($item['label'] ?? ''))));
        $durationLocation = trim((string) ($item['duration'] ?? ($item['dias'] ?? ($item['stay'] ?? ''))));
        $imageLocation = $item['image'] ?? ($item['photo'] ?? ($item['cover'] ?? $heroImage));
        if ($titleLocation === '') {
            continue;
        }
        $locationsList[] = [
            'title' => $titleLocation,
            'duration' => $durationLocation,
            'image' => $imageLocation,
        ];
    }
}

$notesRaw = $detail['notes'] ?? ($detail['notas'] ?? ($detail['observaciones'] ?? []));
if (is_string($notesRaw)) {
    $notesRaw = $normalizeList($notesRaw);
}
$notesList = [];
if (is_array($notesRaw)) {
    foreach ($notesRaw as $note) {
        $textNote = is_string($note) ? trim($note) : '';
        if ($textNote === '') {
            continue;
        }
        $notesList[] = $textNote;
    }
}
if (empty($notesList) && !empty($extraSummaryParagraphs)) {
    foreach ($extraSummaryParagraphs as $paragraph) {
        $trimmed = trim((string) $paragraph);
        if ($trimmed !== '') {
            $notesList[] = $trimmed;
        }
    }
}

$includesList = [];
$excludesList = [];
$otherEssentialSections = [];
foreach ($essentials as $essential) {
    if (!is_array($essential)) {
        continue;
    }
    $sectionTitle = strtolower(trim((string) ($essential['title'] ?? '')));
    $items = $normalizeList($essential['items'] ?? []);
    if (empty($items)) {
        continue;
    }
    if (str_contains($sectionTitle, 'no incluye') || str_contains($sectionTitle, 'exclu')) {
        $excludesList = array_merge($excludesList, $items);
    } elseif (str_contains($sectionTitle, 'inclu')) {
        $includesList = array_merge($includesList, $items);
    } else {
        $otherEssentialSections[] = [
            'title' => $essential['title'] ?? '',
            'items' => $items,
        ];
    }
}
if (empty($includesList) && !empty($services)) {
    $includesList = $normalizeList($services);
}
$includesList = array_values(array_unique($includesList));
$excludesList = array_values(array_unique($excludesList));

$brochuresRaw = $detail['brochures'] ?? ($detail['brochure'] ?? []);
$brochures = [];
if (is_array($brochuresRaw)) {
    foreach ($brochuresRaw as $brochure) {
        if (is_string($brochure)) {
            $label = trim($brochure);
            if ($label === '') {
                continue;
            }
            $brochures[] = [
                'title' => $label,
                'description' => '',
                'href' => '#',
                'primary' => true,
            ];
            continue;
        }
        if (!is_array($brochure)) {
            continue;
        }
        $brochureTitle = trim((string) ($brochure['title'] ?? ($brochure['label'] ?? '')));
        $brochureDescription = trim((string) ($brochure['description'] ?? ($brochure['summary'] ?? '')));
        $brochureHref = trim((string) ($brochure['href'] ?? ($brochure['url'] ?? '#')));
        if ($brochureTitle === '') {
            continue;
        }
        $brochures[] = [
            'title' => $brochureTitle,
            'description' => $brochureDescription,
            'href' => $brochureHref !== '' ? $brochureHref : '#',
            'primary' => (bool) ($brochure['primary'] ?? false),
        ];
    }
}

$galleryItems = [];
foreach ($gallery as $image) {
    if (is_string($image)) {
        $src = trim($image);
        if ($src === '') {
            continue;
        }
        $galleryItems[] = [
            'src' => $src,
            'alt' => $title,
        ];
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
    $galleryItems[] = [
        'src' => $src,
        'alt' => $alt !== '' ? $alt : $title,
    ];
}

$relatedCards = [];
foreach ($related as $card) {
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

$itineraryDays = [];
foreach ($itinerary as $index => $day) {
    if (is_string($day)) {
        $titleDay = trim($day);
        if ($titleDay === '') {
            continue;
        }
        $itineraryDays[] = [
            'title' => $titleDay,
            'summary' => '',
            'activities' => [],
            'meta' => [],
            'schedule' => '',
        ];
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['descripcion'] ?? '')));
    $schedule = trim((string) ($day['schedule'] ?? ($day['time'] ?? ($day['horario'] ?? ''))));
    $activitiesList = $normalizeList($day['activities'] ?? ($day['actividades'] ?? []));

    $metaEntries = [];
    $potentialMetaKeys = ['keyValues', 'key_values', 'kv', 'facts', 'details', 'info'];
    foreach ($potentialMetaKeys as $metaKey) {
        if (!isset($day[$metaKey])) {
            continue;
        }
        $rawMeta = $day[$metaKey];
        if (is_array($rawMeta)) {
            foreach ($rawMeta as $metaItemKey => $metaItemValue) {
                if (is_array($metaItemValue)) {
                    $labelMeta = trim((string) ($metaItemValue['label'] ?? ($metaItemValue['title'] ?? '')));
                    $valueMeta = trim((string) ($metaItemValue['value'] ?? ($metaItemValue['description'] ?? '')));
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                    continue;
                }
                if (is_string($metaItemValue)) {
                    $parts = explode(':', $metaItemValue, 2);
                    if (count($parts) === 2) {
                        $labelMeta = trim($parts[0]);
                        $valueMeta = trim($parts[1]);
                        if ($labelMeta !== '' && $valueMeta !== '') {
                            $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                        }
                        continue;
                    }
                    $valueMeta = trim($metaItemValue);
                    if ($valueMeta !== '') {
                        $metaEntries[] = ['label' => 'Detalle', 'value' => $valueMeta];
                    }
                    continue;
                }
                if (is_string($metaItemKey) && (is_scalar($metaItemValue) || $metaItemValue === null)) {
                    $labelMeta = trim($metaItemKey);
                    $valueMeta = trim((string) $metaItemValue);
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                }
            }
        } elseif (is_string($rawMeta)) {
            $parts = preg_split('/\r\n|\r|\n/', trim($rawMeta)) ?: [];
            foreach ($parts as $part) {
                $metaValue = trim($part);
                if ($metaValue === '') {
                    continue;
                }
                $split = explode(':', $metaValue, 2);
                if (count($split) === 2) {
                    $labelMeta = trim($split[0]);
                    $valueMeta = trim($split[1]);
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                } else {
                    $metaEntries[] = ['label' => 'Detalle', 'value' => $metaValue];
                }
            }
        }
    }

    $itineraryDays[] = [
        'title' => $titleDay !== '' ? $titleDay : ('Día ' . ($index + 1)),
        'summary' => $summaryDay,
        'activities' => $activitiesList,
        'meta' => $metaEntries,
        'schedule' => $schedule,
    ];
}

$reviewsAverage = isset($reviewsSummary['average']) && is_numeric($reviewsSummary['average']) ? round((float) $reviewsSummary['average'], 1) : null;
$reviewsCountSummary = isset($reviewsSummary['count']) && is_numeric($reviewsSummary['count']) ? (int) $reviewsSummary['count'] : 0;
if ($reviewsAverage === null) {
    $reviewsAverage = $rating;
}
if ($reviewsCountSummary === 0 && $reviews !== null) {
    $reviewsCountSummary = $reviews;
}

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

$heroSlides = [];
if ($heroImage !== '') {
    $heroSlides[] = ['src' => $heroImage, 'alt' => $title];
}
foreach ($galleryItems as $image) {
    $heroSlides[] = $image;
}
$uniqueSlides = [];
foreach ($heroSlides as $slide) {
    $srcSlide = $slide['src'] ?? '';
    if ($srcSlide === '') {
        continue;
    }
    $uniqueSlides[$srcSlide] = [
        'src' => $srcSlide,
        'alt' => $slide['alt'] ?? $title,
    ];
}
$heroSlides = array_values($uniqueSlides);
if (empty($heroSlides)) {
    $heroSlides[] = [
        'src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
        'alt' => $title,
    ];
}
while (count($heroSlides) < 3) {
    $heroSlides[] = $heroSlides[count($heroSlides) % max(1, count($heroSlides))];
}

$mapMarkers = [];
foreach ($locationsList as $loc) {
    $markerTitle = trim((string) ($loc['title'] ?? ''));
    if ($markerTitle === '') {
        continue;
    }
    $markerImage = trim((string) ($loc['image'] ?? ''));
    if ($markerImage === '') {
        $markerImage = $heroImage;
    }
    $mapMarkers[] = [
        'title' => $markerTitle,
        'duration' => trim((string) ($loc['duration'] ?? '')),
        'image' => $markerImage,
        'mapUrl' => 'https://maps.google.com/maps?q=' . rawurlencode($markerTitle) . '&output=embed',
    ];
}
if (empty($mapMarkers) && ($location !== '' || $title !== '')) {
    $label = $location !== '' ? $location : $title;
    $mapMarkers[] = [
        'title' => $label,
        'duration' => '',
        'image' => $heroImage,
        'mapUrl' => 'https://maps.google.com/maps?q=' . rawurlencode($label) . '&output=embed',
    ];
}
$mapDefaultUrl = $mapMarkers[0]['mapUrl'] ?? 'https://maps.google.com/maps?q=' . rawurlencode($location !== '' ? $location : $title) . '&output=embed';

$sidebarBenefits = !empty($services) ? array_slice($normalizeList($services), 0, 3) : [];
if (empty($sidebarBenefits)) {
    $sidebarBenefits = [
        'Reservas flexibles y confirmación en menos de 24h.',
        'Asistencia de especialistas locales durante todo el viaje.',
        'Pagos seguros y protección de datos garantizada.',
    ];
}

$reviewsAverageText = $reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '—';
$reviewsCountText = number_format($reviewsCountSummary);

$selectionGroups = [
    'include' => $includesList,
    'exclude' => $excludesList,
];

$currentUser = $currentUser ?? null;
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
        <section class="circuit-hero">
            <div class="circuit-hero__gallery">
                <div class="circuit-hero__track" data-hero-track>
                    <?php foreach ($heroSlides as $slide): ?>
                        <figure class="circuit-hero__slide">
                            <img src="<?= htmlspecialchars($slide['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($slide['alt']); ?>" loading="lazy" />
                        </figure>
                    <?php endforeach; ?>
                </div>
                <?php if (count($heroSlides) > 1): ?>
                    <button class="circuit-hero__nav circuit-hero__nav--prev" type="button" data-hero-prev aria-label="Imagen anterior">‹</button>
                    <button class="circuit-hero__nav circuit-hero__nav--next" type="button" data-hero-next aria-label="Imagen siguiente">›</button>
                <?php endif; ?>
            </div>
            <div class="circuit-hero__info">
                <?php if ($typeLabel !== ''): ?>
                    <span class="circuit-hero__label"><?= htmlspecialchars($typeLabel); ?></span>
                <?php endif; ?>
                <h1 class="circuit-hero__title"><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="circuit-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <?php if (!empty($metaBadges)): ?>
                    <div class="circuit-hero__meta">
                        <?php foreach ($metaBadges as $badge): ?>
                            <?php $badgeClasses = 'circuit-hero__badge'; ?>
                            <?php if (!empty($badge['variant'] ?? null)): ?>
                                <?php $badgeClasses .= ' circuit-hero__badge--' . htmlspecialchars((string) $badge['variant']); ?>
                            <?php endif; ?>
                            <span class="<?= $badgeClasses; ?>"><?= htmlspecialchars($badge['text']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="circuit-hero__rating">
                    <div class="rating-stars" data-review-stars style="--rating: <?= htmlspecialchars($reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '0'); ?>;">
                        <span class="sr-only">Calificación promedio <?= htmlspecialchars($reviewsAverageText); ?> de 5</span>
                    </div>
                    <div class="rating-summary">
                        <span class="rating-summary__value"><strong data-review-average><?= htmlspecialchars($reviewsAverageText); ?></strong> / 5</span>
                        <span class="rating-summary__count"><span data-review-count><?= htmlspecialchars($reviewsCountText); ?></span> opiniones</span>
                    </div>
                </div>
                <?php if ($primarySummaryParagraph !== ''): ?>
                    <p class="circuit-hero__summary"><?= htmlspecialchars($primarySummaryParagraph); ?></p>
                <?php endif; ?>
                <div class="circuit-hero__actions">
                    <?php if ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
                        <a class="button button--primary" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaPrimaryLabel); ?></a>
                    <?php else: ?>
                        <a class="button button--primary" href="#reserva">Reservar ahora</a>
                    <?php endif; ?>
                    <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
                        <a class="button button--ghost" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaSecondaryLabel); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="circuit-layout">
            <div class="circuit-main">
                <?php if (!empty($extraSummaryParagraphs) || !empty($facts)): ?>
                    <section class="circuit-section" id="resumen">
                        <?php if (!empty($extraSummaryParagraphs)): ?>
                            <div class="circuit-intro">
                                <?php foreach ($extraSummaryParagraphs as $paragraph): ?>
                                    <?php $trimmedParagraph = trim((string) $paragraph); ?>
                                    <?php if ($trimmedParagraph === '') { continue; } ?>
                                    <p><?= htmlspecialchars($trimmedParagraph); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($facts)): ?>
                            <dl class="circuit-facts">
                                <?php foreach ($facts as $fact): ?>
                                    <div class="circuit-fact">
                                        <dt><?= htmlspecialchars($fact['label']); ?></dt>
                                        <dd><?= htmlspecialchars($fact['value']); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if (!empty($highlights)): ?>
                    <section class="circuit-section" id="destacados">
                        <header class="circuit-section__header">
                            <h2>Momentos imperdibles</h2>
                            <p>Una selección curada de experiencias para este circuito.</p>
                        </header>
                        <div class="circuit-highlights">
                            <?php foreach ($highlights as $highlight): ?>
                                <?php
                                    $highlightTitle = trim((string) ($highlight['title'] ?? ''));
                                    $highlightDescription = trim((string) ($highlight['description'] ?? ''));
                                    if ($highlightTitle === '' || $highlightDescription === '') {
                                        continue;
                                    }
                                    $highlightIcon = trim((string) ($highlight['icon'] ?? '✨'));
                                ?>
                                <article class="circuit-highlight">
                                    <div class="circuit-highlight__icon" aria-hidden="true"><?= htmlspecialchars($highlightIcon); ?></div>
                                    <div class="circuit-highlight__body">
                                        <h3><?= htmlspecialchars($highlightTitle); ?></h3>
                                        <p><?= htmlspecialchars($highlightDescription); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($itineraryDays)): ?>
                    <section class="circuit-section" id="itinerario">
                        <header class="circuit-section__header">
                            <h2>Itinerario sugerido</h2>
                            <p>Despliega cada jornada para conocer el detalle de actividades y horarios.</p>
                        </header>
                        <div class="circuit-accordion">
                            <?php foreach ($itineraryDays as $index => $day): ?>
                                <details class="circuit-day"<?= $index === 0 ? ' open' : ''; ?>>
                                    <summary>
                                        <span class="circuit-day__badge">D<?= $index + 1; ?></span>
                                        <div class="circuit-day__header">
                                            <h3><?= htmlspecialchars($day['title']); ?></h3>
                                            <?php if ($day['schedule'] !== ''): ?>
                                                <span class="circuit-day__schedule"><?= htmlspecialchars($day['schedule']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </summary>
                                    <div class="circuit-day__body">
                                        <table class="circuit-table" aria-label="Detalle del día <?= $index + 1; ?>">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Día / Hora</th>
                                                    <th scope="col">Título</th>
                                                    <th scope="col">Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?= htmlspecialchars($day['schedule'] !== '' ? $day['schedule'] : 'Todo el día'); ?></td>
                                                    <td><?= htmlspecialchars($day['title']); ?></td>
                                                    <td>
                                                        <?php if ($day['summary'] !== ''): ?>
                                                            <p><?= htmlspecialchars($day['summary']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($day['activities'])): ?>
                                                            <ul class="circuit-day__activities">
                                                                <?php foreach ($day['activities'] as $activity): ?>
                                                                    <li><?= htmlspecialchars($activity); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php if (!empty($day['meta'])): ?>
                                                    <?php foreach ($day['meta'] as $meta): ?>
                                                        <tr class="circuit-table__meta">
                                                            <td><?= htmlspecialchars($meta['label']); ?></td>
                                                            <td colspan="2"><?= htmlspecialchars($meta['value']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="mapa">
                    <header class="circuit-section__header">
                        <h2>Mapa del circuito</h2>
                        <p>Explora los puntos clave del recorrido y selecciona cada marcador para más detalles.</p>
                    </header>
                    <div class="circuit-map" data-map-container>
                        <div class="circuit-map__frame">
                            <iframe src="<?= htmlspecialchars($mapDefaultUrl, ENT_QUOTES); ?>" loading="lazy" allowfullscreen data-map-frame title="Mapa del circuito"></iframe>
                        </div>
                        <?php if (!empty($mapMarkers)): ?>
                            <div class="circuit-map__markers" role="list">
                                <?php foreach ($mapMarkers as $markerIndex => $marker): ?>
                                    <button class="circuit-map__marker<?= $markerIndex === 0 ? ' is-active' : ''; ?>" type="button" data-map-marker="<?= htmlspecialchars($marker['mapUrl'], ENT_QUOTES); ?>">
                                        <span class="circuit-map__marker-thumb" style="background-image: url('<?= htmlspecialchars($marker['image'] !== '' ? $marker['image'] : $heroSlides[0]['src'], ENT_QUOTES); ?>');"></span>
                                        <span class="circuit-map__marker-title"><?= htmlspecialchars($marker['title']); ?></span>
                                        <?php if ($marker['duration'] !== ''): ?>
                                            <span class="circuit-map__marker-duration"><?= htmlspecialchars($marker['duration']); ?></span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if (!empty($selectionGroups['include']) || !empty($selectionGroups['exclude'])): ?>
                    <section class="circuit-section" id="incluye">
                        <header class="circuit-section__header">
                            <h2>Incluido y no incluido</h2>
                            <p>Marca los elementos que quieres destacar y míralos aparecer en tu nube personalizada.</p>
                        </header>
                        <div class="selection-cloud" data-selection-cloud>
                            <span class="selection-chip selection-chip--empty">Selecciona elementos para construir tu nube.</span>
                        </div>
                        <div class="selection-groups">
                            <?php if (!empty($selectionGroups['include'])): ?>
                                <div class="selection-group">
                                    <h3>Incluye</h3>
                                    <div class="selection-options">
                                        <?php foreach ($selectionGroups['include'] as $index => $item): ?>
                                            <label class="selection-option">
                                                <input type="checkbox" name="incluye[]" value="<?= htmlspecialchars($item); ?>" data-selection-source data-selection-group="include" data-label="<?= htmlspecialchars($item); ?>"<?= $index < 6 ? ' checked' : ''; ?> />
                                                <span><?= htmlspecialchars($item); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($selectionGroups['exclude'])): ?>
                                <div class="selection-group">
                                    <h3>No incluye</h3>
                                    <div class="selection-options">
                                        <?php foreach ($selectionGroups['exclude'] as $index => $item): ?>
                                            <label class="selection-option">
                                                <input type="checkbox" name="no_incluye[]" value="<?= htmlspecialchars($item); ?>" data-selection-source data-selection-group="exclude" data-label="<?= htmlspecialchars($item); ?>"<?= $index < 3 ? ' checked' : ''; ?> />
                                                <span><?= htmlspecialchars($item); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($experiences)): ?>
                    <section class="circuit-section" id="experiencias">
                        <header class="circuit-section__header">
                            <h2>Experiencias que elevan tu viaje</h2>
                            <p>Opcionales recomendadas para personalizar tu circuito.</p>
                        </header>
                        <div class="circuit-experiences">
                            <?php foreach ($experiences as $experience): ?>
                                <?php
                                    $experienceTitle = trim((string) ($experience['title'] ?? ''));
                                    $experienceDescription = trim((string) ($experience['description'] ?? ''));
                                    if ($experienceTitle === '' || $experienceDescription === '') {
                                        continue;
                                    }
                                    $experienceIcon = trim((string) ($experience['icon'] ?? '🌟'));
                                    $experiencePriceRaw = $experience['price'] ?? ($experience['precio'] ?? ($experience['priceFrom'] ?? null));
                                    $experienceCurrency = strtoupper((string) ($experience['currency'] ?? ($experience['moneda'] ?? 'PEN')));
                                    $experiencePriceText = '';
                                    if (is_numeric($experiencePriceRaw)) {
                                        $symbol = match ($experienceCurrency) {
                                            'USD' => '$',
                                            'EUR' => '€',
                                            'GBP' => '£',
                                            default => 'S/',
                                        };
                                        $experiencePriceText = sprintf('%s %s', $symbol, number_format((float) $experiencePriceRaw, 2, '.', ','));
                                    } elseif (is_string($experiencePriceRaw)) {
                                        $experiencePriceText = trim($experiencePriceRaw);
                                    }
                                ?>
                                <article class="circuit-experience">
                                    <div class="circuit-experience__icon" aria-hidden="true"><?= htmlspecialchars($experienceIcon); ?></div>
                                    <div class="circuit-experience__body">
                                        <h3><?= htmlspecialchars($experienceTitle); ?></h3>
                                        <p><?= htmlspecialchars($experienceDescription); ?></p>
                                        <?php if ($experiencePriceText !== ''): ?>
                                            <p class="circuit-experience__price">Tarifa referencial: <?= htmlspecialchars($experiencePriceText); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($otherEssentialSections) || !empty($notesList)): ?>
                    <section class="circuit-section" id="informacion">
                        <header class="circuit-section__header">
                            <h2>Información útil</h2>
                            <p>Todo lo que necesitas saber antes de viajar.</p>
                        </header>
                        <?php if (!empty($notesList)): ?>
                            <ul class="circuit-notes">
                                <?php foreach ($notesList as $note): ?>
                                    <li><?= htmlspecialchars($note); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($otherEssentialSections)): ?>
                            <div class="circuit-essentials">
                                <?php foreach ($otherEssentialSections as $extraSection): ?>
                                    <article class="circuit-essential">
                                        <h3><?= htmlspecialchars($extraSection['title']); ?></h3>
                                        <ul>
                                            <?php foreach ($extraSection['items'] as $item): ?>
                                                <li><?= htmlspecialchars($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if (!empty($brochures)): ?>
                    <section class="circuit-section" id="descargas">
                        <header class="circuit-section__header">
                            <h2>Material descargable</h2>
                            <p>Folletos y fichas técnicas para compartir con tu equipo o familia.</p>
                        </header>
                        <div class="circuit-brochures">
                            <?php foreach ($brochures as $brochure): ?>
                                <article class="circuit-brochure">
                                    <div>
                                        <h3><?= htmlspecialchars($brochure['title']); ?></h3>
                                        <?php if ($brochure['description'] !== ''): ?>
                                            <p><?= htmlspecialchars($brochure['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a class="button button--primary" href="<?= htmlspecialchars($brochure['href'], ENT_QUOTES); ?>"<?= preg_match('~^https?://~i', $brochure['href']) ? ' target="_blank" rel="noopener"' : ''; ?>>Descargar</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="opiniones">
                    <header class="circuit-section__header">
                        <h2>Opiniones de viajeros</h2>
                        <p>Reseñas verificadas de nuestra comunidad de suscriptores.</p>
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
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES); ?>" />
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
                                <label class="form-grid__full">
                                    <span>Tu reseña *</span>
                                    <textarea name="comentario" rows="4" required placeholder="Cuéntanos cómo fue tu experiencia"></textarea>
                                </label>
                            </div>
                            <div class="form-actions">
                                <button class="button button--primary" type="submit" data-loading>Enviar reseña</button>
                                <p class="form-note">Marcamos con * los campos obligatorios. Nos reservamos el derecho de moderar los comentarios.</p>
                            </div>
                            <div class="form-status" data-review-status></div>
                        </form>
                    </div>
                </section>

                <?php if (!empty($relatedCards)): ?>
                    <section class="circuit-section" id="relacionados">
                        <header class="circuit-section__header">
                            <h2>También te puede interesar</h2>
                            <p>Explora otras propuestas que combinan perfecto con este circuito.</p>
                        </header>
                        <div class="circuit-related">
                            <?php foreach ($relatedCards as $card): ?>
                                <article class="circuit-related__card">
                                    <?php if ($card['image'] !== ''): ?>
                                        <div class="circuit-related__media">
                                            <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($card['title']); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>
                                    <div class="circuit-related__body">
                                        <?php if ($card['badge'] !== ''): ?>
                                            <span class="circuit-related__badge"><?= htmlspecialchars($card['badge']); ?></span>
                                        <?php endif; ?>
                                        <h3><?= htmlspecialchars($card['title']); ?></h3>
                                        <p><?= htmlspecialchars($card['summary']); ?></p>
                                        <a class="button button--ghost" href="<?= htmlspecialchars($card['href'], ENT_QUOTES); ?>">Ver detalles</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="circuit-sidebar" id="reserva">
                <section class="sidebar-card">
                    <h2>Reserva tu circuito</h2>
                    <p>Comparte tus datos y un asesor se comunicará contigo para confirmar disponibilidad.</p>
                    <form class="sidebar-form" method="post" action="api/reservas-circuitos.php" data-reservation-form>
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES); ?>" />
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
                            <span>Teléfono</span>
                            <input type="tel" name="telefono" autocomplete="tel" placeholder="Opcional" />
                        </label>
                        <label>
                            <span>Fecha estimada de viaje</span>
                            <input type="date" name="fecha_salida" />
                        </label>
                        <label>
                            <span>Personas</span>
                            <input type="number" name="cantidad_personas" min="1" value="2" />
                        </label>
                        <label>
                            <span>Mensaje</span>
                            <textarea name="mensaje" rows="3" placeholder="¿Quieres una experiencia personalizada?"></textarea>
                        </label>
                        <button class="button button--primary" type="submit" data-loading>Enviar solicitud</button>
                        <div class="form-status" data-reservation-status></div>
                    </form>
                </section>

                <section class="sidebar-card sidebar-card--accent">
                    <h3>Garantía Expediatravels</h3>
                    <ul class="sidebar-benefits">
                        <?php foreach ($sidebarBenefits as $benefit): ?>
                            <li><?= htmlspecialchars($benefit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="sidebar-note">Somos especialistas locales. Cada reserva pasa por nuestro equipo para garantizar calidad y seguridad.</p>
                </section>

                <section class="sidebar-card">
                    <h3>Contacto directo</h3>
                    <ul class="sidebar-contact">
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
