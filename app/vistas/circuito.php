<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}
$pageTitle = $title ?? ($detail['title'] ?? ($detail['nombre'] ?? $siteTitle));
$currentUser = $currentUser ?? null;

$typeLabel = $detail['type'] ?? ($detail['categoria'] ?? 'Circuito');
$title = $detail['title'] ?? ($detail['nombre'] ?? 'Circuito turístico');
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
$gallery = is_array($detail['gallery'] ?? null) ? $detail['gallery'] : [];
$related = is_array($detail['related'] ?? null) ? $detail['related'] : [];
$services = is_array($detail['servicios'] ?? null) ? $detail['servicios'] : [];

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
$ctaPrimaryLabel = $cta['primaryLabel'] ?? '';
$ctaPrimaryHref = $cta['primaryHref'] ?? '';
$ctaSecondaryLabel = $cta['secondaryLabel'] ?? '';
$ctaSecondaryHref = $cta['secondaryHref'] ?? '';

$heroStyle = $heroImage !== '' ? 'background-image: url(' . htmlspecialchars($heroImage, ENT_QUOTES) . ');' : '';
$summaryParagraphs = [];
if (trim((string) $summary) !== '') {
    $summaryParagraphs = preg_split('/\n\s*\n/', trim((string) $summary)) ?: [];
}
$labelColor = $detail['labelColor'] ?? null;
$badgeModifier = '';
if (is_string($labelColor)) {
    $normalizedLabel = strtolower(trim($labelColor));
    $normalizedLabel = preg_replace('/[^a-z0-9_-]/', '', str_replace(' ', '-', $normalizedLabel));
    if ($normalizedLabel !== '') {
        $badgeModifier = ' detail-hero__badge--' . $normalizedLabel;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
</head>
<body class="page page--detail">
    <?php $activeNav = 'experiencias'; include __DIR__ . '/partials/site-header.php'; ?>
    <main class="detail-page circuit-page">
        <section class="detail-hero circuit-hero" style="<?= $heroStyle; ?>">
            <div class="detail-hero__overlay"></div>
            <div class="detail-hero__content">
                <div class="detail-hero__badge<?= $badgeModifier; ?>">
                    <?= htmlspecialchars($typeLabel); ?>
                </div>
                <h1 class="detail-hero__title"><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="detail-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <?php if ($rating !== null || $reviews !== null): ?>
                    <div class="circuit-hero__rating">
                        <?php if ($rating !== null): ?>
                            <span class="circuit-hero__rating-value">★ <?= htmlspecialchars(number_format($rating, 1)); ?></span>
                        <?php endif; ?>
                        <?php if ($reviews !== null): ?>
                            <span class="circuit-hero__rating-reviews"><?= htmlspecialchars((string) $reviews); ?> opiniones</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="circuit-hero__meta">
                    <?php if ($location !== ''): ?>
                        <span><strong>Ubicación:</strong> <?= htmlspecialchars($location); ?></span>
                    <?php endif; ?>
                    <?php if ($duration !== ''): ?>
                        <span><strong>Duración:</strong> <?= htmlspecialchars($duration); ?></span>
                    <?php endif; ?>
                    <?php if ($frequency !== ''): ?>
                        <span><strong>Próxima salida:</strong> <?= htmlspecialchars($frequency); ?></span>
                    <?php endif; ?>
                    <?php if ($group !== ''): ?>
                        <span><strong>Grupo ideal:</strong> <?= htmlspecialchars($group); ?></span>
                    <?php endif; ?>
                    <?php if ($experienceLevel !== ''): ?>
                        <span><strong>Intensidad:</strong> <?= htmlspecialchars($experienceLevel); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($chips)): ?>
                    <ul class="detail-hero__chips">
                        <?php foreach ($chips as $chip):
                            $chipText = trim((string) $chip);
                            if ($chipText === '') {
                                continue;
                            }
                        ?>
                            <li><?= htmlspecialchars($chipText); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="circuit-hero__cta">
                    <?php if ($priceFrom !== ''): ?>
                        <div class="circuit-hero__price">
                            <span class="circuit-hero__price-label">Tarifa referencial</span>
                            <span class="circuit-hero__price-value"><?= htmlspecialchars($priceFrom); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="circuit-hero__buttons">
                        <?php if ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
                            <a class="button button--primary" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>">
                                <?= htmlspecialchars($ctaPrimaryLabel); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
                            <a class="button button--ghost" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>">
                                <?= htmlspecialchars($ctaSecondaryLabel); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($mapImage !== ''): ?>
                <aside class="detail-hero__map" aria-label="Mapa de referencia">
                    <div class="detail-hero__map-card">
                        <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel); ?>" loading="lazy" />
                        <?php if (!empty($stats)): ?>
                            <dl class="detail-hero__stats">
                                <?php foreach ($stats as $stat):
                                    $label = $stat['label'] ?? '';
                                    $value = $stat['value'] ?? '';
                                    if ($label === '' || $value === '') {
                                        continue;
                                    }
                                ?>
                                    <div class="detail-hero__stat">
                                        <dt><?= htmlspecialchars($label); ?></dt>
                                        <dd><?= htmlspecialchars($value); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                    </div>
                </aside>
            <?php endif; ?>
        </section>

        <section class="circuit-overview">
            <div class="circuit-overview__columns">
                <div class="circuit-overview__primary">
                    <?php if (!empty($summaryParagraphs) || !empty($highlights)): ?>
                        <section class="detail-section detail-section--intro" id="detalle">
                            <div class="detail-section__body">
                                <?php foreach ($summaryParagraphs as $paragraph):
                                    $trimmed = trim((string) $paragraph);
                                    if ($trimmed === '') {
                                        continue;
                                    }
                                ?>
                                    <p><?= htmlspecialchars($trimmed); ?></p>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($highlights)): ?>
                                <div class="detail-highlights">
                                    <?php foreach ($highlights as $highlight):
                                        $accent = $highlight['accent'] ?? 'sunrise';
                                        $titleHighlight = $highlight['title'] ?? '';
                                        $descriptionHighlight = $highlight['description'] ?? '';
                                        if ($titleHighlight === '' || $descriptionHighlight === '') {
                                            continue;
                                        }
                                    ?>
                                        <article class="detail-highlight detail-highlight--<?= htmlspecialchars($accent); ?>">
                                            <?php if (!empty($highlight['icon'])): ?>
                                                <div class="detail-highlight__icon" aria-hidden="true"><?= htmlspecialchars($highlight['icon']); ?></div>
                                            <?php endif; ?>
                                            <h3><?= htmlspecialchars($titleHighlight); ?></h3>
                                            <p><?= htmlspecialchars($descriptionHighlight); ?></p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($itinerary)): ?>
                        <section class="detail-section detail-section--itinerary" id="itinerario">
                            <header class="detail-section__header">
                                <h2>Itinerario sugerido</h2>
                                <p>Explora día a día cómo se vive esta experiencia.</p>
                            </header>
                            <ol class="detail-timeline">
                                <?php foreach ($itinerary as $index => $day):
                                    $titleDay = $day['title'] ?? '';
                                    $summaryDay = $day['summary'] ?? '';
                                    $activities = $day['activities'] ?? [];
                                ?>
                                    <li class="detail-timeline__item">
                                        <div class="detail-timeline__badge">D<?= $index + 1; ?></div>
                                        <div class="detail-timeline__content">
                                            <?php if ($titleDay !== ''): ?>
                                                <h3><?= htmlspecialchars($titleDay); ?></h3>
                                            <?php endif; ?>
                                            <?php if ($summaryDay !== ''): ?>
                                                <p><?= htmlspecialchars($summaryDay); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($activities)): ?>
                                                <ul>
                                                    <?php foreach ($activities as $activity):
                                                        $activityText = trim((string) $activity);
                                                        if ($activityText === '') {
                                                            continue;
                                                        }
                                                    ?>
                                                        <li><?= htmlspecialchars($activityText); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($experiences)): ?>
                        <section class="detail-section detail-section--experiences" id="circuitos">
                            <header class="detail-section__header">
                                <h2>Experiencias que elevan tu viaje</h2>
                                <p>Personaliza tu circuito con actividades memorables.</p>
                            </header>
                            <div class="detail-experiences">
                                <?php foreach ($experiences as $experience):
                                    $titleExperience = $experience['title'] ?? '';
                                    $descriptionExperience = $experience['description'] ?? '';
                                    if ($titleExperience === '' || $descriptionExperience === '') {
                                        continue;
                                    }
                                    $experiencePriceRaw = $experience['price'] ?? ($experience['precio'] ?? ($experience['priceFrom'] ?? null));
                                    $experienceCurrency = strtoupper((string) ($experience['currency'] ?? ($experience['moneda'] ?? 'PEN')));
                                    $experiencePriceText = '';
                                    if (is_numeric($experiencePriceRaw)) {
                                        $symbol = match ($experienceCurrency) {
                                            'USD' => '$',
                                            'EUR' => '€',
                                            default => 'S/',
                                        };
                                        $experiencePriceText = sprintf('%s %s', $symbol, number_format((float) $experiencePriceRaw, 2, '.', ','));
                                    } elseif (is_string($experiencePriceRaw)) {
                                        $experiencePriceText = trim($experiencePriceRaw);
                                    }
                                    $experiencePriceNote = '';
                                    if (!empty($experience['priceNote'] ?? null)) {
                                        $experiencePriceNote = trim((string) $experience['priceNote']);
                                    } elseif (!empty($experience['notaPrecio'] ?? null)) {
                                        $experiencePriceNote = trim((string) $experience['notaPrecio']);
                                    }
                                ?>
                                    <article class="detail-experience">
                                        <?php if (!empty($experience['icon'])): ?>
                                            <div class="detail-experience__icon" aria-hidden="true"><?= htmlspecialchars($experience['icon']); ?></div>
                                        <?php endif; ?>
                                        <h3><?= htmlspecialchars($titleExperience); ?></h3>
                                        <p><?= htmlspecialchars($descriptionExperience); ?></p>
                                        <?php if ($experiencePriceText !== ''): ?>
                                            <p class="detail-experience__price">
                                                <strong>Tarifa desde:</strong> <?= htmlspecialchars($experiencePriceText); ?>
                                                <?php if ($experiencePriceNote !== ''): ?>
                                                    <span><?= htmlspecialchars($experiencePriceNote); ?></span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($essentials)): ?>
                        <section class="detail-section detail-section--essentials" id="preparativos">
                            <header class="detail-section__header">
                                <h2>Prepárate para la aventura</h2>
                                <p>Consejos prácticos para disfrutar cada etapa del circuito.</p>
                            </header>
                            <div class="detail-essentials">
                                <?php foreach ($essentials as $essential):
                                    $titleEssential = $essential['title'] ?? '';
                                    $items = $essential['items'] ?? [];
                                    if ($titleEssential === '' || empty($items)) {
                                        continue;
                                    }
                                ?>
                                    <article class="detail-essential">
                                        <h3><?= htmlspecialchars($titleEssential); ?></h3>
                                        <ul>
                                            <?php foreach ($items as $item):
                                                $itemText = trim((string) $item);
                                                if ($itemText === '') {
                                                    continue;
                                                }
                                            ?>
                                                <li><?= htmlspecialchars($itemText); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($gallery)): ?>
                        <section class="detail-section detail-section--gallery" id="galeria">
                            <header class="detail-section__header">
                                <h2>Galería vibrante</h2>
                                <p>Inspírate con momentos esenciales del circuito.</p>
                            </header>
                            <div class="detail-gallery">
                                <?php foreach ($gallery as $image):
                                    $src = $image['src'] ?? '';
                                    if ($src === '') {
                                        continue;
                                    }
                                    $alt = $image['alt'] ?? $title;
                                ?>
                                    <figure class="detail-gallery__item">
                                        <img src="<?= htmlspecialchars($src, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($alt); ?>" loading="lazy" />
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($related)): ?>
                        <section class="detail-section detail-section--related" id="relacionados">
                            <header class="detail-section__header">
                                <h2>También te puede interesar</h2>
                                <p>Explora otras rutas y experiencias que combinan perfecto.</p>
                            </header>
                            <div class="detail-related">
                                <?php foreach ($related as $card):
                                    $cardTitle = $card['title'] ?? '';
                                    $cardSummary = $card['summary'] ?? '';
                                    $cardHref = $card['href'] ?? '#';
                                    if ($cardTitle === '' || $cardSummary === '') {
                                        continue;
                                    }
                                ?>
                                    <article class="detail-related__card">
                                        <?php if (!empty($card['image'])): ?>
                                            <div class="detail-related__media">
                                                <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($cardTitle); ?>" loading="lazy" />
                                            </div>
                                        <?php endif; ?>
                                        <div class="detail-related__body">
                                            <?php if (!empty($card['badge'])): ?>
                                                <span class="detail-related__badge"><?= htmlspecialchars($card['badge']); ?></span>
                                            <?php endif; ?>
                                            <h3><?= htmlspecialchars($cardTitle); ?></h3>
                                            <p><?= htmlspecialchars($cardSummary); ?></p>
                                            <a class="detail-related__link" href="<?= htmlspecialchars($cardHref, ENT_QUOTES); ?>">Ver detalles</a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>

                <aside class="circuit-overview__sidebar" aria-label="Resumen del circuito">
                    <div class="circuit-sidebar-card">
                        <h2>Datos clave del circuito</h2>
                        <dl class="circuit-facts">
                            <?php if ($duration !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Duración</dt>
                                    <dd><?= htmlspecialchars($duration); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($frequency !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Próxima salida</dt>
                                    <dd><?= htmlspecialchars($frequency); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($group !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Grupo sugerido</dt>
                                    <dd><?= htmlspecialchars($group); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($experienceLevel !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Intensidad</dt>
                                    <dd><?= htmlspecialchars($experienceLevel); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($location !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Territorio</dt>
                                    <dd><?= htmlspecialchars($location); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($priceFrom !== ''): ?>
                                <div class="circuit-fact">
                                    <dt>Inversión estimada</dt>
                                    <dd><?= htmlspecialchars($priceFrom); ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                        <?php if ($rating !== null || $reviews !== null): ?>
                            <div class="circuit-sidebar-rating">
                                <?php if ($rating !== null): ?>
                                    <span class="circuit-sidebar-rating__value">★ <?= htmlspecialchars(number_format($rating, 1)); ?></span>
                                <?php endif; ?>
                                <?php if ($reviews !== null): ?>
                                    <span class="circuit-sidebar-rating__reviews"><?= htmlspecialchars((string) $reviews); ?> reseñas</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($services)): ?>
                            <div class="circuit-sidebar-services">
                                <h3>Servicios destacados</h3>
                                <ul>
                                    <?php foreach ($services as $service):
                                        $serviceText = trim((string) $service);
                                        if ($serviceText === '') {
                                            continue;
                                        }
                                    ?>
                                        <li><?= htmlspecialchars($serviceText); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($ctaPrimaryLabel !== '' || $ctaSecondaryLabel !== ''): ?>
                        <div class="circuit-sidebar-card circuit-sidebar-card--cta">
                            <h2>¿Listo para vivirlo?</h2>
                            <p>Nuestro equipo puede ayudarte a personalizar fechas, actividades y logística.</p>
                            <div class="circuit-sidebar-card__buttons">
                                <?php if ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
                                    <a class="button button--primary" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>">
                                        <?= htmlspecialchars($ctaPrimaryLabel); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
                                    <a class="button button--ghost" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>">
                                        <?= htmlspecialchars($ctaSecondaryLabel); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/partials/site-footer.php'; ?>
    <?php include __DIR__ . '/partials/auth-modal.php'; ?>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
