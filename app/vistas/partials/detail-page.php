<?php
$detail = $detail ?? [];
$typeLabel = $detail['type'] ?? ($detail['categoria'] ?? 'Contenido');
$title = $detail['title'] ?? ($detail['nombre'] ?? 'Experiencia destacada');
$tagline = $detail['tagline'] ?? ($detail['resumen'] ?? '');
$summary = $detail['summary'] ?? ($detail['descripcion_larga'] ?? '');
$heroImage = $detail['heroImage'] ?? $detail['imagen'] ?? '';
$mapImage = $detail['mapImage'] ?? '';
$mapLabel = $detail['mapLabel'] ?? ($detail['location'] ?? '');
$location = $detail['location'] ?? (($detail['destino'] ?? '') . ($detail['region'] ?? '' ? ' — ' . $detail['region'] : ''));
$chips = $detail['chips'] ?? [];
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
$labelColor = $detail['labelColor'] ?? null;
$heroStyle = $heroImage ? 'background-image: url(' . htmlspecialchars($heroImage, ENT_QUOTES) . ');' : '';
$summaryParagraphs = preg_split('/\n\s*\n/', trim((string) $summary)) ?: [];
?>
<main class="detail-page">
    <section class="detail-hero" style="<?= htmlspecialchars($heroStyle, ENT_QUOTES); ?>">
        <div class="detail-hero__overlay"></div>
        <div class="detail-hero__content">
            <div class="detail-hero__badge<?= $labelColor ? ' detail-hero__badge--' . htmlspecialchars($labelColor) : ''; ?>">
                <?= htmlspecialchars($typeLabel); ?>
            </div>
            <h1 class="detail-hero__title"><?= htmlspecialchars($title); ?></h1>
            <?php if (!empty($tagline)): ?>
                <p class="detail-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
            <?php endif; ?>
            <div class="detail-hero__meta">
                <?php if (!empty($location)): ?>
                    <span>
                        <strong>Ubicación:</strong> <?= htmlspecialchars($location); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($duration)): ?>
                    <span>
                        <strong>Duración sugerida:</strong> <?= htmlspecialchars($duration); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($priceFrom)): ?>
                    <span>
                        <strong>Tarifa referencial:</strong> <?= htmlspecialchars($priceFrom); ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($chips)): ?>
                <ul class="detail-hero__chips">
                    <?php foreach ($chips as $chip): ?>
                        <li><?= htmlspecialchars($chip); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="detail-hero__actions">
                <?php if (!empty($cta['primaryLabel'] ?? null) && !empty($cta['primaryHref'] ?? null)): ?>
                    <a class="button button--primary" href="<?= htmlspecialchars($cta['primaryHref'], ENT_QUOTES); ?>">
                        <?= htmlspecialchars($cta['primaryLabel']); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($cta['secondaryLabel'] ?? null) && !empty($cta['secondaryHref'] ?? null)): ?>
                    <a class="button button--ghost" href="<?= htmlspecialchars($cta['secondaryHref'], ENT_QUOTES); ?>">
                        <?= htmlspecialchars($cta['secondaryLabel']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($mapImage): ?>
            <aside class="detail-hero__map" aria-label="Mapa de referencia">
                <div class="detail-hero__map-card">
                    <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel ?: 'Mapa de referencia'); ?>" loading="lazy" />
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

    <div class="detail-page__content layout-container layout-stack">
    <nav class="detail-anchors" aria-label="Secciones del contenido">
        <a href="#detalle">Descripción</a>
        <?php if (!empty($itinerary)): ?><a href="#itinerario">Itinerario</a><?php endif; ?>
        <?php if (!empty($experiences)): ?><a href="#circuitos">Circuitos Turisticos</a><?php endif; ?>
        <?php if (!empty($essentials)): ?><a href="#preparativos">Preparativos</a><?php endif; ?>
        <?php if (!empty($gallery)): ?><a href="#galeria">Galería</a><?php endif; ?>
    </nav>

    <section class="detail-section detail-section--intro" id="detalle">
        <div class="detail-section__body">
            <?php if (!empty($summaryParagraphs)): ?>
                <?php foreach ($summaryParagraphs as $paragraph):
                    $trimmed = trim($paragraph);
                    if ($trimmed === '') {
                        continue;
                    }
                ?>
                    <p><?= htmlspecialchars($trimmed); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
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
                <p>Personaliza tu itinerario con actividades vibrantes y memorables.</p>
            </header>
            <div class="detail-experiences">
                <?php foreach ($experiences as $experience):
                    $titleExperience = $experience['title'] ?? '';
                    $descriptionExperience = $experience['description'] ?? '';
                    if ($titleExperience === '' || $descriptionExperience === '') {
                        continue;
                    }
                    $experiencePriceRaw = $experience['price'] ?? $experience['precio'] ?? $experience['priceFrom'] ?? null;
                    $experienceCurrency = strtoupper((string) ($experience['currency'] ?? $experience['moneda'] ?? 'PEN'));
                    $experiencePriceText = '';
                    if (is_numeric($experiencePriceRaw)) {
                        $symbol = match ($experienceCurrency) {
                            'USD' => '$',
                            'EUR' => '€',
                            default => 'S/',
                        };
                        $experiencePriceText = sprintf('%s %.2f', $symbol, (float) $experiencePriceRaw);
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
                <p>Consejos prácticos para aprovechar al máximo tu experiencia.</p>
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
                        <ul class="detail-essential__list">
                            <?php foreach ($items as $item):
                                if (is_array($item)) {
                                    $itemText = trim((string) ($item['label'] ?? $item['nombre'] ?? ''));
                                    $itemIcon = trim((string) ($item['icon'] ?? $item['icono'] ?? ''));
                                    $itemNote = trim((string) ($item['description'] ?? $item['descripcion'] ?? ''));
                                } else {
                                    $itemText = trim((string) $item);
                                    $itemIcon = '';
                                    $itemNote = '';
                                }
                                if ($itemText === '') {
                                    continue;
                                }
                            ?>
                                <li class="detail-essential__item">
                                    <?php if ($itemIcon !== ''): ?>
                                        <span class="detail-essential__icon" aria-hidden="true">
                                            <?php if (str_contains($itemIcon, 'fa-')): ?>
                                                <i class="<?= htmlspecialchars($itemIcon, ENT_QUOTES); ?>" aria-hidden="true"></i>
                                            <?php else: ?>
                                                <?= htmlspecialchars($itemIcon); ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="detail-essential__text">
                                        <?= htmlspecialchars($itemText); ?>
                                        <?php if ($itemNote !== ''): ?>
                                            <small class="detail-essential__note"><?= htmlspecialchars($itemNote); ?></small>
                                        <?php endif; ?>
                                    </span>
                                </li>
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
                <p>Inspírate con algunos momentos de esta experiencia.</p>
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
        <section class="detail-section detail-section--related">
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
</main>
