<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];

$typeLabel = $detail['type'] ?? ($detail['categoria'] ?? 'Destino');
$title = $detail['title'] ?? ($detail['nombre'] ?? 'Experiencia destacada');
$tagline = $detail['tagline'] ?? ($detail['resumen'] ?? '');
$summary = $detail['summary'] ?? ($detail['descripcion_larga'] ?? '');
$summaryParagraphs = preg_split('/\n\s*\n/', trim((string) $summary)) ?: [];
$introParagraph = '';
if (!empty($summaryParagraphs)) {
    $introParagraph = trim((string) array_shift($summaryParagraphs));
}

$heroImage = $detail['heroImage'] ?? $detail['imagen'] ?? '';
$mapImage = $detail['mapImage'] ?? '';
$mapLabel = $detail['mapLabel'] ?? ($detail['location'] ?? '');
$location = $detail['location'] ?? (($detail['destino'] ?? '') . ($detail['region'] ?? '' ? ' — ' . $detail['region'] : ''));
$chips = array_values(array_filter(array_map('trim', (array) ($detail['chips'] ?? []))));
$stats = $detail['stats'] ?? [];
$highlights = $detail['highlights'] ?? [];
$itinerary = $detail['itinerary'] ?? [];
if (empty($itinerary) && !empty($detail['itinerary_detallado'])) {
    $itinerary = $detail['itinerary_detallado'];
}
$experiences = $detail['experiences'] ?? [];
$essentials = $detail['essentials'] ?? [];
$gallery = $detail['gallery'] ?? [];
$cta = $detail['cta'] ?? [];
$priceFrom = $detail['priceFrom'] ?? null;
$duration = $detail['duration'] ?? ($detail['duracion'] ?? null);
$related = $detail['related'] ?? [];

$metaBadges = [];
if (!empty($duration)) {
    $metaBadges[] = '⏱️ ' . (string) $duration;
}
if (!empty($typeLabel)) {
    $metaBadges[] = '🏷️ ' . (string) $typeLabel;
}
if (!empty($location)) {
    $metaBadges[] = '📍 ' . (string) $location;
}
if (!empty($priceFrom)) {
    $metaBadges[] = '💸 ' . (string) $priceFrom;
}

$facts = [];
foreach ($stats as $stat) {
    $label = trim((string) ($stat['label'] ?? ''));
    $value = trim((string) ($stat['value'] ?? ''));

    if ($label === '' || $value === '') {
        continue;
    }

    $facts[] = [
        'label' => $label,
        'value' => $value,
    ];

    if (count($facts) === 4) {
        break;
    }
}

if (empty($facts)) {
    foreach ($metaBadges as $badgeText) {
        $parts = explode(' ', $badgeText, 2);
        if (count($facts) === 4 || count($parts) !== 2) {
            continue;
        }

        $facts[] = [
            'label' => trim($parts[1]),
            'value' => trim($parts[0]),
        ];
    }
}

$normalizedHighlights = [];
foreach ($highlights as $highlight) {
    $titleHighlight = trim((string) ($highlight['title'] ?? ''));
    $descriptionHighlight = trim((string) ($highlight['description'] ?? ''));
    if ($titleHighlight === '' || $descriptionHighlight === '') {
        continue;
    }

    $normalizedHighlights[] = [
        'icon' => $highlight['icon'] ?? null,
        'title' => $titleHighlight,
        'description' => $descriptionHighlight,
    ];
}

$normalizedItinerary = [];
foreach ($itinerary as $index => $day) {
    $titleDay = trim((string) ($day['title'] ?? ''));
    $summaryDay = trim((string) ($day['summary'] ?? ''));
    $activities = array_values(array_filter(array_map(static function ($activity): string {
        return trim((string) $activity);
    }, (array) ($day['activities'] ?? []))));

    if ($titleDay === '' && $summaryDay === '' && empty($activities)) {
        continue;
    }

    $normalizedItinerary[] = [
        'day' => $index + 1,
        'title' => $titleDay,
        'summary' => $summaryDay,
        'activities' => $activities,
    ];
}

$normalizedExperiences = [];
foreach ($experiences as $experience) {
    $titleExperience = trim((string) ($experience['title'] ?? ''));
    $descriptionExperience = trim((string) ($experience['description'] ?? ''));
    if ($titleExperience === '' || $descriptionExperience === '') {
        continue;
    }

    $priceRaw = $experience['price'] ?? $experience['precio'] ?? $experience['priceFrom'] ?? null;
    $currency = strtoupper((string) ($experience['currency'] ?? $experience['moneda'] ?? 'PEN'));
    $priceText = '';
    if (is_numeric($priceRaw)) {
        $symbol = match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            default => 'S/',
        };
        $priceText = sprintf('%s %.2f', $symbol, (float) $priceRaw);
    } elseif (is_string($priceRaw)) {
        $priceText = trim($priceRaw);
    }

    $priceNote = '';
    if (!empty($experience['priceNote'] ?? null)) {
        $priceNote = trim((string) $experience['priceNote']);
    } elseif (!empty($experience['notaPrecio'] ?? null)) {
        $priceNote = trim((string) $experience['notaPrecio']);
    }

    $normalizedExperiences[] = [
        'icon' => $experience['icon'] ?? null,
        'title' => $titleExperience,
        'description' => $descriptionExperience,
        'price' => $priceText,
        'note' => $priceNote,
    ];
}

$normalizedEssentials = [];
foreach ($essentials as $essential) {
    $titleEssential = trim((string) ($essential['title'] ?? ''));
    $items = array_values(array_filter(array_map(static function ($item): string {
        return trim((string) $item);
    }, (array) ($essential['items'] ?? []))));

    if ($titleEssential === '' || empty($items)) {
        continue;
    }

    $normalizedEssentials[] = [
        'title' => $titleEssential,
        'items' => $items,
    ];
}

$normalizedGallery = [];
foreach ($gallery as $image) {
    $src = trim((string) ($image['src'] ?? ''));
    if ($src === '') {
        continue;
    }

    $normalizedGallery[] = [
        'src' => $src,
        'alt' => trim((string) ($image['alt'] ?? $title)),
    ];
}

$normalizedRelated = [];
foreach ($related as $card) {
    $cardTitle = trim((string) ($card['title'] ?? ''));
    $cardSummary = trim((string) ($card['summary'] ?? ''));
    if ($cardTitle === '' || $cardSummary === '') {
        continue;
    }

    $normalizedRelated[] = [
        'badge' => $card['badge'] ?? null,
        'title' => $cardTitle,
        'summary' => $cardSummary,
        'href' => $card['href'] ?? '#',
        'image' => $card['image'] ?? null,
    ];
}

$primaryCtaLabel = trim((string) ($cta['primaryLabel'] ?? ''));
$primaryCtaHref = trim((string) ($cta['primaryHref'] ?? ''));
$secondaryCtaLabel = trim((string) ($cta['secondaryLabel'] ?? ''));
$secondaryCtaHref = trim((string) ($cta['secondaryHref'] ?? ''));

$contact = $siteSettings['contact'] ?? [];
$contactPhones = array_values(array_filter(array_map('trim', (array) ($contact['phones'] ?? []))));
$contactEmails = array_values(array_filter(array_map('trim', (array) ($contact['emails'] ?? []))));
$contactAddresses = array_values(array_filter(array_map('trim', (array) ($contact['addresses'] ?? []))));

$sidebarHighlights = array_map(static function (array $highlight): string {
    return $highlight['title'];
}, array_slice($normalizedHighlights, 0, 3));
if (empty($sidebarHighlights)) {
    $sidebarHighlights = [
        'Asesoría personalizada con expertos locales',
        'Hoteles recomendados según tu estilo',
        'Guías certificados para cada actividad',
    ];
}

$heroStyle = $heroImage !== ''
    ? '--destination-hero-image: url(' . htmlspecialchars($heroImage, ENT_QUOTES) . ');'
    : '';

$hasItinerary = !empty($normalizedItinerary);
$hasHighlights = !empty($normalizedHighlights);
$hasExperiences = !empty($normalizedExperiences);
$hasEssentials = !empty($normalizedEssentials);
$hasGallery = !empty($normalizedGallery);
$hasRelated = !empty($normalizedRelated);

$anchorLinks = [
    ['href' => '#detalle', 'label' => 'Descripción', 'show' => true],
    ['href' => '#destacados', 'label' => 'Destacados', 'show' => $hasHighlights],
    ['href' => '#itinerario', 'label' => 'Itinerario', 'show' => $hasItinerary],
    ['href' => '#experiencias', 'label' => 'Experiencias', 'show' => $hasExperiences],
    ['href' => '#preparativos', 'label' => 'Preparativos', 'show' => $hasEssentials],
    ['href' => '#galeria', 'label' => 'Galería', 'show' => $hasGallery],
];
?>
<main class="destination-page">
    <header class="destination-hero" style="<?= $heroStyle; ?>">
        <div class="destination-hero__overlay"></div>
        <div class="destination-shell destination-hero__wrap">
            <div class="destination-hero__left">
                <div class="destination-hero__badge"><?= htmlspecialchars($typeLabel); ?></div>
                <h1 class="destination-hero__title"><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="destination-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <?php if ($introParagraph !== ''): ?>
                    <p class="destination-hero__summary"><?= htmlspecialchars($introParagraph); ?></p>
                <?php endif; ?>
                <?php if (!empty($metaBadges)): ?>
                    <div class="destination-hero__meta">
                        <?php foreach ($metaBadges as $badge): ?>
                            <span class="destination-badge"><?= htmlspecialchars($badge); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($chips)): ?>
                    <ul class="destination-hero__chips">
                        <?php foreach ($chips as $chip): ?>
                            <li><?= htmlspecialchars($chip); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($heroImage !== ''): ?>
                    <figure class="destination-hero__image destination-card">
                        <img src="<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($title); ?>" loading="lazy" />
                    </figure>
                <?php endif; ?>
                <?php if (!empty($facts)): ?>
                    <div class="destination-facts">
                        <?php foreach ($facts as $fact): ?>
                            <div class="destination-fact">
                                <small><?= htmlspecialchars($fact['value']); ?></small>
                                <strong><?= htmlspecialchars($fact['label']); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <aside class="destination-hero__aside">
                <div class="destination-card destination-card--sidebar">
                    <h3>¡Personaliza tu viaje!</h3>
                    <ul class="destination-checklist">
                        <?php foreach ($sidebarHighlights as $item): ?>
                            <li><span aria-hidden="true">✓</span><?= htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="destination-hero__actions">
                        <?php if ($primaryCtaLabel !== '' && $primaryCtaHref !== ''): ?>
                            <a class="destination-btn" href="<?= htmlspecialchars($primaryCtaHref, ENT_QUOTES); ?>"><?= htmlspecialchars($primaryCtaLabel); ?></a>
                        <?php endif; ?>
                        <?php if ($secondaryCtaLabel !== '' && $secondaryCtaHref !== ''): ?>
                            <a class="destination-btn destination-btn--secondary" href="<?= htmlspecialchars($secondaryCtaHref, ENT_QUOTES); ?>"><?= htmlspecialchars($secondaryCtaLabel); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </header>

    <nav class="destination-anchors" aria-label="Secciones del destino">
        <div class="destination-shell destination-anchors__wrap">
            <?php foreach ($anchorLinks as $link):
                if (!$link['show']) {
                    continue;
                }
            ?>
                <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES); ?>"><?= htmlspecialchars($link['label']); ?></a>
            <?php endforeach; ?>
        </div>
    </nav>

    <section class="destination-intro" id="detalle">
        <div class="destination-shell destination-intro__wrap">
            <div class="destination-intro__content">
                <?php foreach ($summaryParagraphs as $paragraph):
                    $trimmed = trim($paragraph);
                    if ($trimmed === '') {
                        continue;
                    }
                ?>
                    <p><?= htmlspecialchars($trimmed); ?></p>
                <?php endforeach; ?>
            </div>
            <?php if ($mapImage !== ''): ?>
                <aside class="destination-intro__map destination-card">
                    <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel !== '' ? $mapLabel : 'Mapa de referencia'); ?>" loading="lazy" />
                    <?php if ($mapLabel !== ''): ?>
                        <span><?= htmlspecialchars($mapLabel); ?></span>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>
        </div>
    </section>

    <div class="destination-layout">
        <div class="destination-shell destination-layout__grid">
            <div class="destination-layout__primary">
                <?php if ($hasHighlights): ?>
                    <section class="destination-section destination-section--highlights" id="destacados">
                        <header class="destination-section__header">
                            <h2>Momentos imperdibles</h2>
                            <p>Los detalles que hacen único este destino.</p>
                        </header>
                        <div class="destination-highlight-list">
                            <?php foreach ($normalizedHighlights as $highlight): ?>
                                <article class="destination-highlight destination-card">
                                    <?php if (!empty($highlight['icon'])): ?>
                                        <div class="destination-highlight__icon" aria-hidden="true"><?= htmlspecialchars($highlight['icon']); ?></div>
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($highlight['title']); ?></h3>
                                    <p><?= htmlspecialchars($highlight['description']); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasItinerary): ?>
                    <section class="destination-section destination-section--itinerary" id="itinerario">
                        <header class="destination-section__header">
                            <h2>Itinerario sugerido</h2>
                            <p>Explora el ritmo ideal para descubrirlo todo.</p>
                        </header>
                        <div class="destination-accordion">
                            <?php foreach ($normalizedItinerary as $index => $day): ?>
                                <details class="destination-day"<?= $index === 0 ? ' open' : ''; ?>>
                                    <summary>
                                        <div>
                                            <span class="destination-day__badge">D<?= (int) $day['day']; ?></span>
                                            <strong><?= htmlspecialchars($day['title'] !== '' ? $day['title'] : 'Día ' . (int) $day['day']); ?></strong>
                                        </div>
                                        <?php if ($day['summary'] !== ''): ?>
                                            <span class="destination-day__summary"><?= htmlspecialchars($day['summary']); ?></span>
                                        <?php endif; ?>
                                    </summary>
                                    <div class="destination-day__body">
                                        <?php if (!empty($day['activities'])): ?>
                                            <ul>
                                                <?php foreach ($day['activities'] as $activity): ?>
                                                    <li><?= htmlspecialchars($activity); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasExperiences): ?>
                    <section class="destination-section destination-section--experiences" id="experiencias">
                        <header class="destination-section__header">
                            <h2>Experiencias recomendadas</h2>
                            <p>Personaliza tu viaje con actividades auténticas.</p>
                        </header>
                        <div class="destination-experiences">
                            <?php foreach ($normalizedExperiences as $experience): ?>
                                <article class="destination-experience destination-card">
                                    <?php if (!empty($experience['icon'])): ?>
                                        <div class="destination-experience__icon" aria-hidden="true"><?= htmlspecialchars($experience['icon']); ?></div>
                                    <?php endif; ?>
                                    <div class="destination-experience__content">
                                        <h3><?= htmlspecialchars($experience['title']); ?></h3>
                                        <p><?= htmlspecialchars($experience['description']); ?></p>
                                        <?php if ($experience['price'] !== ''): ?>
                                            <p class="destination-experience__price">
                                                <strong>Tarifa desde:</strong> <?= htmlspecialchars($experience['price']); ?>
                                                <?php if ($experience['note'] !== ''): ?>
                                                    <span><?= htmlspecialchars($experience['note']); ?></span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="destination-section destination-section--map" id="mapa">
                    <header class="destination-section__header">
                        <h2>Mapa de referencia</h2>
                        <p>Visualiza la ruta y los puntos clave del destino.</p>
                    </header>
                    <div class="destination-map destination-card">
                        <?php if ($mapImage !== ''): ?>
                            <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel !== '' ? $mapLabel : 'Mapa de referencia'); ?>" loading="lazy" />
                        <?php else: ?>
                            <div class="destination-map__placeholder">
                                <span>Pronto encontrarás aquí un mapa interactivo con los puntos destacados.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($hasEssentials): ?>
                    <section class="destination-section destination-section--essentials" id="preparativos">
                        <header class="destination-section__header">
                            <h2>Información esencial</h2>
                            <p>Recomendaciones prácticas para tu próxima visita.</p>
                        </header>
                        <div class="destination-features">
                            <?php foreach ($normalizedEssentials as $essential): ?>
                                <article class="destination-feature destination-card">
                                    <h3><?= htmlspecialchars($essential['title']); ?></h3>
                                    <ul>
                                        <?php foreach ($essential['items'] as $item): ?>
                                            <li><?= htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasGallery): ?>
                    <section class="destination-section destination-section--gallery" id="galeria">
                        <header class="destination-section__header">
                            <h2>Galería inspiradora</h2>
                            <p>Un vistazo a los paisajes y momentos imperdibles.</p>
                        </header>
                        <div class="destination-gallery">
                            <?php foreach ($normalizedGallery as $image): ?>
                                <figure class="destination-gallery__item destination-card">
                                    <img src="<?= htmlspecialchars($image['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($image['alt']); ?>" loading="lazy" />
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasRelated): ?>
                    <section class="destination-section destination-section--related">
                        <header class="destination-section__header">
                            <h2>Planifica tu siguiente aventura</h2>
                            <p>Otras rutas y experiencias que combinan a la perfección.</p>
                        </header>
                        <div class="destination-related">
                            <?php foreach ($normalizedRelated as $card): ?>
                                <article class="destination-related__card destination-card">
                                    <?php if (!empty($card['image'])): ?>
                                        <div class="destination-related__media">
                                            <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($card['title']); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>
                                    <div class="destination-related__body">
                                        <?php if (!empty($card['badge'])): ?>
                                            <span class="destination-related__badge"><?= htmlspecialchars($card['badge']); ?></span>
                                        <?php endif; ?>
                                        <h3><?= htmlspecialchars($card['title']); ?></h3>
                                        <p><?= htmlspecialchars($card['summary']); ?></p>
                                        <a class="destination-related__link" href="<?= htmlspecialchars($card['href'], ENT_QUOTES); ?>">Ver detalles</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="destination-layout__aside" id="reserva">
                <div class="destination-card destination-card--aside">
                    <h2>Reserva rápida</h2>
                    <form class="destination-form">
                        <label>
                            <span>Fecha de salida</span>
                            <input type="date" name="fecha_salida" />
                        </label>
                        <label>
                            <span>Pasajeros</span>
                            <input type="number" name="pasajeros" min="1" value="2" />
                        </label>
                        <button type="button" class="destination-btn">Consultar disponibilidad</button>
                        <?php if ($primaryCtaLabel !== '' && $primaryCtaHref !== ''): ?>
                            <a class="destination-btn destination-btn--ghost" href="<?= htmlspecialchars($primaryCtaHref, ENT_QUOTES); ?>">Diseñar mi viaje</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="destination-card destination-card--aside">
                    <h2>Contacto</h2>
                    <ul class="destination-contact">
                        <?php foreach ($contactPhones as $phone): ?>
                            <li>📞 <?= htmlspecialchars($phone); ?></li>
                        <?php endforeach; ?>
                        <?php foreach ($contactEmails as $email): ?>
                            <li>✉️ <?= htmlspecialchars($email); ?></li>
                        <?php endforeach; ?>
                        <?php foreach ($contactAddresses as $address): ?>
                            <li>📍 <?= htmlspecialchars($address); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
document.querySelectorAll('.destination-accordion details').forEach((detail) => {
    detail.addEventListener('toggle', () => {
        if (detail.open) {
            document.querySelectorAll('.destination-accordion details').forEach((other) => {
                if (other !== detail) {
                    other.removeAttribute('open');
                }
            });
        }
    });
});
</script>
