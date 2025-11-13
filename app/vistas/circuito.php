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
    $title = 'Circuito';
}

$typeLabel = trim((string) ($detail['type'] ?? ($detail['type_tag'] ?? ($detail['categoria'] ?? 'Circuito'))));
if ($typeLabel === '') {
    $typeLabel = 'Circuito';
}

$tagline = trim((string) ($detail['tagline'] ?? ($detail['resumen'] ?? '')));

$heroImage = trim((string) ($detail['heroImage'] ?? ($detail['imagen'] ?? '')));

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

$reviewsCount = null;
foreach (['reviews', 'totalResenas', 'reviewsCount', 'cantidad_resenas'] as $reviewsKey) {
    if (isset($detail[$reviewsKey]) && is_numeric($detail[$reviewsKey])) {
        $reviewsCount = (int) $detail[$reviewsKey];
        break;
    }
}

$duration = trim((string) ($detail['duration'] ?? ($detail['duracion'] ?? '')));
if ($duration === '') {
    $duration = null;
}

$tourType = trim((string) ($detail['tourType'] ?? ($detail['tipo'] ?? '')));
if ($tourType === '') {
    $tourType = null;
}

$groupSize = trim((string) ($detail['group'] ?? ($detail['tamano_grupo'] ?? ($detail['grupo_maximo'] ?? ''))));
if ($groupSize === '') {
    $groupSize = null;
}

$difficulty = trim((string) ($detail['difficulty'] ?? ($detail['dificultad'] ?? '')));
if ($difficulty === '') {
    $difficulty = null;
}

$frequency = trim((string) ($detail['frequency'] ?? ($detail['frecuencia'] ?? '')));
if ($frequency === '') {
    $frequency = null;
}

$nextDeparture = trim((string) ($detail['nextDeparture'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? ''))));
if ($nextDeparture === '') {
    $nextDeparture = null;
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

$statsRaw = is_array($detail['stats'] ?? null) ? $detail['stats'] : [];
$statsCards = [];
foreach ($statsRaw as $stat) {
    if (!is_array($stat)) {
        continue;
    }
    $label = trim((string) ($stat['label'] ?? ($stat['titulo'] ?? '')));
    $value = trim((string) ($stat['value'] ?? ($stat['valor'] ?? '')));
    if ($label === '' || $value === '') {
        continue;
    }
    $statsCards[] = [
        'label' => $label,
        'value' => $value,
    ];
}

$experiencesRaw = $detail['experiences'] ?? ($detail['experiencias'] ?? []);
if (!is_array($experiencesRaw)) {
    $experiencesRaw = [];
}
$experiencesList = [];
foreach ($experiencesRaw as $experience) {
    if (is_string($experience)) {
        $titleExperience = trim($experience);
        if ($titleExperience === '') {
            continue;
        }
        $experiencesList[] = [
            'title' => $titleExperience,
            'description' => '',
        ];
        continue;
    }
    if (!is_array($experience)) {
        continue;
    }
    $titleExperience = trim((string) ($experience['title'] ?? ($experience['nombre'] ?? '')));
    $descriptionExperience = trim((string) ($experience['description'] ?? ($experience['descripcion'] ?? '')));
    if ($titleExperience === '') {
        continue;
    }
    $experiencesList[] = [
        'title' => $titleExperience,
        'description' => $descriptionExperience,
        'icon' => trim((string) ($experience['icon'] ?? ($experience['emoji'] ?? ''))),
    ];
}

$summaryRaw = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$summaryText = is_string($summaryRaw) ? trim($summaryRaw) : '';
$aboutParagraphs = [];
if ($summaryText !== '') {
    $aboutParagraphs = preg_split('/\n\s*\n/', $summaryText) ?: [];
    $aboutParagraphs = array_values(array_filter(array_map('trim', $aboutParagraphs), static fn ($paragraph) => $paragraph !== ''));
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
        ];
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['description'] ?? ($day['descripcion'] ?? ''))));
    if ($titleDay === '') {
        continue;
    }
    $itineraryDays[] = [
        'title' => $titleDay,
        'description' => $summaryDay,
    ];
}

$mediaRaw = $detail['gallery'] ?? ($detail['galeria'] ?? []);
if (!is_array($mediaRaw)) {
    $mediaRaw = [];
}
$galleryImages = [];
foreach ($mediaRaw as $mediaItem) {
    if (is_string($mediaItem)) {
        $src = trim($mediaItem);
        if ($src === '') {
            continue;
        }
        $galleryImages[] = [
            'src' => $src,
            'alt' => $title !== '' ? $title : 'Imagen del circuito',
        ];
        continue;
    }
    if (!is_array($mediaItem)) {
        continue;
    }
    $src = trim((string) ($mediaItem['src'] ?? ($mediaItem['url'] ?? ($mediaItem['image'] ?? ''))));
    if ($src === '') {
        continue;
    }
    $alt = trim((string) ($mediaItem['alt'] ?? ($mediaItem['label'] ?? ($mediaItem['title'] ?? ''))));
    if ($alt === '') {
        $alt = $title !== '' ? $title : 'Imagen del circuito';
    }
    $galleryImages[] = [
        'src' => $src,
        'alt' => $alt,
    ];
}

$mapImage = trim((string) ($detail['mapImage'] ?? ($detail['mapa'] ?? '')));
if ($mapImage === '') {
    $mapImage = null;
}

$mapLabel = trim((string) ($detail['mapLabel'] ?? ($detail['mapa_label'] ?? '')));
if ($mapLabel === '') {
    $mapLabel = $location !== '' ? 'Mapa del circuito en ' . $location : 'Mapa del circuito';
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
$bookingUrlRaw = $detail['booking_url'] ?? ($detail['bookingUrl'] ?? null);
$bookingUrl = is_string($bookingUrlRaw) ? trim($bookingUrlRaw) : '';
if ($bookingUrl === '') {
    $bookingUrl = null;
}

$departuresRaw = $detail['departures'] ?? ($detail['fechas'] ?? null);
$departureOptions = $normalizeList($departuresRaw);
$departureOptions = array_values(array_filter($departureOptions, static fn ($option) => $option !== ''));
if (!empty($departureOptions)) {
    array_unshift($departureOptions, 'Selecciona una fecha');
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

$languagesBadges = array_map(static fn ($language) => ['label' => $language], $languagesList);

$operationalFacts = [];
if ($duration !== null) {
    $operationalFacts[] = ['label' => 'Duración', 'value' => $duration];
}
if ($difficulty !== null) {
    $operationalFacts[] = ['label' => 'Nivel de dificultad', 'value' => $difficulty];
}
if ($frequency !== null) {
    $operationalFacts[] = ['label' => 'Frecuencia de salida', 'value' => $frequency];
}
if ($tourType !== null) {
    $operationalFacts[] = ['label' => 'Tipo de tour', 'value' => $tourType];
}
if ($groupSize !== null) {
    $operationalFacts[] = ['label' => 'Tamaño del grupo', 'value' => $groupSize];
}
if ($nextDeparture !== null) {
    $operationalFacts[] = ['label' => 'Próxima salida', 'value' => $nextDeparture];
}

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
                <span class="tour-banner__pill"><?= htmlspecialchars($typeLabel); ?></span>
                <h1><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="tour-banner__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <div class="tour-banner__meta">
                    <?php if ($ratingValue !== null): ?>
                        <span class="tour-banner__meta-item" aria-label="Valoración">
                            <span class="tour-banner__icon" aria-hidden="true">⭐</span>
                            <?= htmlspecialchars(number_format($ratingValue, 1, '.', '')); ?>
                            <?php if ($reviewsCount !== null): ?>
                                · <?= htmlspecialchars($reviewsCount); ?> reseñas
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($location !== ''): ?>
                        <span class="tour-banner__meta-item" aria-label="Ubicación">
                            <span class="tour-banner__icon" aria-hidden="true">📍</span>
                            <?= htmlspecialchars($location); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($duration !== null || $tourType !== null || $groupSize !== null || !empty($languagesList)): ?>
                    <div class="tour-banner__info">
                        <?php if ($duration !== null): ?>
                            <article class="tour-banner__info-item">
                                <span class="tour-banner__info-icon" aria-hidden="true">⏱️</span>
                                <div>
                                    <p>Duración</p>
                                    <strong><?= htmlspecialchars($duration); ?></strong>
                                </div>
                            </article>
                        <?php endif; ?>
                        <?php if ($tourType !== null): ?>
                            <article class="tour-banner__info-item">
                                <span class="tour-banner__info-icon" aria-hidden="true">🧭</span>
                                <div>
                                    <p>Tipo de tour</p>
                                    <strong><?= htmlspecialchars($tourType); ?></strong>
                                </div>
                            </article>
                        <?php endif; ?>
                        <?php if ($groupSize !== null): ?>
                            <article class="tour-banner__info-item">
                                <span class="tour-banner__info-icon" aria-hidden="true">👥</span>
                                <div>
                                    <p>Tamaño del grupo</p>
                                    <strong><?= htmlspecialchars($groupSize); ?></strong>
                                </div>
                            </article>
                        <?php endif; ?>
                        <?php if (!empty($languagesList)): ?>
                            <article class="tour-banner__info-item">
                                <span class="tour-banner__info-icon" aria-hidden="true">💬</span>
                                <div>
                                    <p>Idiomas</p>
                                    <strong><?= htmlspecialchars(implode(', ', $languagesList)); ?></strong>
                                </div>
                            </article>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="tour-detail__layout">
            <div class="tour-detail__left">
                <?php if (!empty($aboutParagraphs) || !empty($highlights) || !empty($statsCards)): ?>
                    <section class="detail-section detail-section--about" id="informacion">
                        <header>
                            <h2>Información del circuito</h2>
                        </header>
                        <?php foreach ($aboutParagraphs as $paragraph): ?>
                            <p><?= htmlspecialchars($paragraph); ?></p>
                        <?php endforeach; ?>
                        <?php if (!empty($statsCards)): ?>
                            <dl class="circuit-stats">
                                <?php foreach ($statsCards as $card): ?>
                                    <div class="circuit-stats__item">
                                        <dt><?= htmlspecialchars($card['label']); ?></dt>
                                        <dd><?= htmlspecialchars($card['value']); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                        <?php if (!empty($highlights)): ?>
                            <ul class="highlight-list">
                                <?php foreach ($highlights as $highlight): ?>
                                    <li><?= htmlspecialchars($highlight); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if (!empty($experiencesList)): ?>
                    <section class="detail-section" id="experiencias">
                        <header>
                            <h2>Experiencias recomendadas</h2>
                            <p>Potencia tu viaje con actividades curadas por nuestro equipo local.</p>
                        </header>
                        <div class="experience-grid">
                            <?php foreach ($experiencesList as $experience): ?>
                                <article class="experience-card">
                                    <?php if (!empty($experience['icon'])): ?>
                                        <span class="experience-card__icon" aria-hidden="true"><?= htmlspecialchars($experience['icon']); ?></span>
                                    <?php endif; ?>
                                    <div class="experience-card__content">
                                        <h3><?= htmlspecialchars($experience['title']); ?></h3>
                                        <?php if ($experience['description'] !== ''): ?>
                                            <p><?= htmlspecialchars($experience['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($operationalFacts) || !empty($includes) || !empty($excludes) || !empty($languagesBadges)): ?>
                    <section class="detail-section detail-section--operations" id="operacion">
                        <header>
                            <h2>Detalles operativos</h2>
                        </header>
                        <?php if (!empty($operationalFacts)): ?>
                            <dl class="fact-grid">
                                <?php foreach ($operationalFacts as $fact): ?>
                                    <div class="fact-grid__item">
                                        <dt><?= htmlspecialchars($fact['label']); ?></dt>
                                        <dd><?= htmlspecialchars($fact['value']); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                        <?php if (!empty($languagesBadges)): ?>
                            <div class="strip-list strip-list--languages">
                                <?php foreach ($languagesBadges as $badge): ?>
                                    <div class="strip-list__item">
                                        <span class="strip-list__icon strip-list__icon--check" aria-hidden="true">✔</span>
                                        <span><?= htmlspecialchars($badge['label']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($includes) || !empty($excludes)): ?>
                            <div class="split-columns">
                                <?php if (!empty($includes)): ?>
                                    <div class="split-columns__item">
                                        <h3>Incluye</h3>
                                        <ul>
                                            <?php foreach ($includes as $item): ?>
                                                <li><span class="split-columns__icon" aria-hidden="true">✔</span><?= htmlspecialchars($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($excludes)): ?>
                                    <div class="split-columns__item">
                                        <h3>No incluye</h3>
                                        <ul>
                                            <?php foreach ($excludes as $item): ?>
                                                <li><span class="split-columns__icon split-columns__icon--negative" aria-hidden="true">✘</span><?= htmlspecialchars($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if (!empty($itineraryDays)): ?>
                    <section class="detail-section" id="itinerario">
                        <header>
                            <h2>Contenido del itinerario</h2>
                            <p>Revisa las actividades principales planificadas para cada jornada.</p>
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
                                        <?php if ($day['description'] !== ''): ?>
                                            <p><?= htmlspecialchars($day['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($mapImage !== null || !empty($galleryImages)): ?>
                    <section class="detail-section detail-section--media" id="multimedia">
                        <header>
                            <h2>Recursos multimedia</h2>
                        </header>
                        <div class="media-resources">
                            <?php if ($mapImage !== null): ?>
                                <figure class="media-card media-card--map">
                                    <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel); ?>" loading="lazy" />
                                    <?php if ($mapLabel !== ''): ?>
                                        <figcaption><?= htmlspecialchars($mapLabel); ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endif; ?>
                            <?php if (!empty($galleryImages)): ?>
                                <div class="media-gallery" role="list">
                                    <?php foreach ($galleryImages as $image): ?>
                                        <figure class="media-gallery__item" role="listitem">
                                            <img src="<?= htmlspecialchars($image['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($image['alt']); ?>" loading="lazy" />
                                            <figcaption><?= htmlspecialchars($image['alt']); ?></figcaption>
                                        </figure>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="tour-detail__right">
                <section class="aside-card aside-card--booking">
                    <div class="booking-header">
                        <?php if ($priceFrom !== null): ?>
                            <span class="booking-price"><?= htmlspecialchars($priceFrom); ?></span>
                        <?php endif; ?>
                        <?php if ($ratingValue !== null): ?>
                            <span class="booking-rating">
                                <span aria-hidden="true">⭐</span>
                                <?= htmlspecialchars(number_format($ratingValue, 1, '.', '')); ?>
                                <?php if ($reviewsCount !== null): ?>
                                    · <?= htmlspecialchars($reviewsCount); ?> reseñas
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <form class="booking-form" action="<?= $bookingUrl ? htmlspecialchars($bookingUrl, ENT_QUOTES) : '#'; ?>" method="get">
                        <label class="booking-field">
                            <span>Fecha</span>
                            <select name="date" <?= $bookingUrl ? '' : 'disabled'; ?>>
                                <?php if (!empty($departureOptions)): ?>
                                    <?php foreach ($departureOptions as $option): ?>
                                        <option value="<?= htmlspecialchars($option, ENT_QUOTES); ?>"><?= htmlspecialchars($option); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled selected>Fechas no disponibles</option>
                                <?php endif; ?>
                            </select>
                        </label>
                        <div class="booking-grid">
                            <?php $travellers = [
                                ['label' => 'Adultos', 'name' => 'adults', 'min' => 1],
                                ['label' => 'Niños', 'name' => 'children', 'min' => 0],
                                ['label' => 'Infantes', 'name' => 'infant', 'min' => 0],
                            ]; ?>
                            <?php foreach ($travellers as $traveller): ?>
                                <div class="booking-counter" data-counter>
                                    <span><?= htmlspecialchars($traveller['label']); ?></span>
                                    <div class="booking-counter__controls">
                                        <button type="button" class="booking-counter__btn" data-counter-decrease aria-label="Restar">
                                            −
                                        </button>
                                        <input type="number" name="<?= htmlspecialchars($traveller['name'], ENT_QUOTES); ?>" value="<?= $traveller['min']; ?>" min="<?= $traveller['min']; ?>" readonly />
                                        <button type="button" class="booking-counter__btn" data-counter-increase aria-label="Sumar">
                                            +
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="booking-submit" <?= $bookingUrl ? '' : 'disabled'; ?>>Reservar ahora</button>
                    </form>
                </section>

                <section class="aside-card aside-card--contact">
                    <h3>Información de contacto</h3>
                    <ul>
                        <li><span aria-hidden="true">✉️</span> <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES); ?>"><?= htmlspecialchars($contactEmail); ?></a></li>
                        <li><span aria-hidden="true">🌐</span> <a href="https://<?= htmlspecialchars(ltrim($contactWebsite, 'https://'), ENT_QUOTES); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($contactWebsite); ?></a></li>
                        <li><span aria-hidden="true">📞</span> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone), ENT_QUOTES); ?>"><?= htmlspecialchars($contactPhone); ?></a></li>
                        <li><span aria-hidden="true">📠</span> <?= htmlspecialchars($contactFax); ?></li>
                    </ul>
                </section>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>

    <script src="scripts/circuito.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
