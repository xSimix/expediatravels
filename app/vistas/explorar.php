<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}

$heroSlides = $siteSettings['heroSlides'] ?? [];
$visibleHeroSlides = array_values(array_filter($heroSlides, static function ($slide) {
    if (isset($slide['isVisible']) && !$slide['isVisible']) {
        return false;
    }

    $imageUrl = (string) ($slide['image'] ?? '');

    return $imageUrl !== '';
}));

$heroSlide = $visibleHeroSlides[0] ?? null;
$heroImage = is_array($heroSlide) ? (string) ($heroSlide['image'] ?? '') : '';
$heroAlt = '';
if ($heroSlide) {
    $altText = trim((string) ($heroSlide['altText'] ?? ''));
    $labelText = trim((string) ($heroSlide['label'] ?? ''));
    if ($altText !== '') {
        $heroAlt = $altText;
    } elseif ($labelText !== '') {
        $heroAlt = $labelText;
    }
}
if ($heroAlt === '') {
    $heroAlt = 'Paisajes y experiencias de la Selva Central del Perú';
}

$currentUser = $currentUser ?? null;
$filters = $filters ?? [];
$activeFilters = $activeFilters ?? [];
$sortOptions = $sortOptions ?? [];
$selectedSort = $selectedSort ?? '';
$results = $results ?? [];
$stats = $stats ?? ['total' => count($results)];

$categoryLabels = [
    'experiencias' => 'Experiencias',
    'circuitos' => 'Circuitos',
    'destinos' => 'Destinos',
];

$formatCurrency = static function (?float $amount, string $currency): ?string {
    if ($amount === null) {
        return null;
    }

    $symbols = [
        'PEN' => 'S/',
        'USD' => '$',
        'EUR' => '€',
    ];

    $code = strtoupper($currency);
    $symbol = $symbols[$code] ?? $symbols['PEN'];

    return sprintf('%s %s', $symbol, number_format($amount, 2));
};

$activeChips = [];
foreach ($filters as $key => $definition) {
    if (!isset($activeFilters[$key])) {
        continue;
    }

    $value = $activeFilters[$key];
    foreach ($definition['options'] ?? [] as $option) {
        if (($option['value'] ?? null) === $value) {
            $activeChips[] = [
                'key' => $key,
                'value' => $value,
                'label' => $option['label'] ?? $value,
            ];
            break;
        }
    }
}

$hasFilters = !empty($activeChips);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Explorar'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
</head>
<body class="page page--explorar">
    <?php $activeNav = 'destinos'; include __DIR__ . '/partials/site-header.php'; ?>
    <section class="hero hero--explore">
        <?php if ($heroImage !== ''): ?>
            <div class="hero__backgrounds">
                <div
                    class="hero__background hero__background--active"
                    style="background-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>');"
                    role="img"
                    aria-label="<?= htmlspecialchars($heroAlt, ENT_QUOTES); ?>"
                ></div>
            </div>
        <?php endif; ?>
        <div class="hero__content explore__hero">
            <div class="hero__copy explore__hero-copy">
                <span class="hero__badge">Explorador Expediatravels</span>
                <h1 class="hero__title">Explora la Selva Central a tu manera</h1>
                <p class="hero__subtitle explore__lead">Descubre circuitos, experiencias y destinos curados para inspirar tu próximo viaje a Oxapampa y sus alrededores.</p>
                <ul class="explore__stats" aria-label="Resumen de resultados">
                    <?php $totalResults = (int) ($stats['total'] ?? count($results)); ?>
                    <li><strong><?= $totalResults; ?></strong> opciones totales</li>
                    <?php foreach ($categoryLabels as $key => $label): ?>
                        <?php if (!empty($stats[$key])): ?>
                            <li><strong><?= (int) $stats[$key]; ?></strong> <?= htmlspecialchars($label); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <aside class="explore__hero-card" aria-label="Ventajas del explorador">
                <div class="explore__hero-card-inner">
                    <h2>Diseñamos experiencias flexibles</h2>
                    <p>Filtra por duración, presupuesto y estilo de viaje. Cada resultado incluye anfitriones locales certificados y logística verificada.</p>
                    <ul>
                        <li>✔️ Operaciones sostenibles</li>
                        <li>✔️ Atención 24/7 en destino</li>
                        <li>✔️ Reservas flexibles y personalizadas</li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
    <main class="explore">
        <section class="explore__layout">
            <aside class="explore__filters" id="filters-panel" data-filters-panel>
                <div class="explore__filters-header">
                    <h2>Filtrar resultados</h2>
                    <button class="explore__filters-close" type="button" data-filters-close aria-label="Cerrar filtros">Cerrar</button>
                </div>
                <form class="explore-filters" method="get">
                    <?php if ($selectedSort !== ''): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES); ?>" />
                    <?php endif; ?>
                    <?php foreach ($filters as $key => $definition):
                        $options = $definition['options'] ?? [];
                        if ($options === []):
                            continue;
                        endif;
                        $fieldId = 'filter-' . htmlspecialchars($key);
                        $selected = $activeFilters[$key] ?? '';
                    ?>
                        <div class="explore-filters__group">
                            <label class="explore-filters__label" for="<?= $fieldId; ?>"><?= htmlspecialchars($definition['label'] ?? ucfirst($key)); ?></label>
                            <div class="explore-filters__control">
                                <select id="<?= $fieldId; ?>" name="<?= htmlspecialchars($key); ?>">
                                    <option value="">Todas</option>
                                    <?php foreach ($options as $option):
                                        $value = (string) ($option['value'] ?? '');
                                        $label = (string) ($option['label'] ?? $value);
                                        $isSelected = $selected !== '' && $selected === $value;
                                    ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES); ?>"<?= $isSelected ? ' selected' : ''; ?>><?= htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="explore-filters__actions">
                        <button class="button button--primary" type="submit">Aplicar filtros</button>
                        <a class="button button--ghost" href="explorar.php">Limpiar</a>
                    </div>
                </form>
            </aside>

            <section class="explore__results">
                <header class="explore-results__header">
                    <div class="explore-results__summary">
                        <button class="explore__filters-toggle" type="button" data-filters-toggle aria-controls="filters-panel" aria-expanded="false">
                            <span>Filtros</span>
                        </button>
                        <p><?= count($results); ?> resultados disponibles</p>
                    </div>
                    <form class="explore-results__sort" method="get">
                        <?php foreach ($activeFilters as $key => $value): ?>
                            <input type="hidden" name="<?= htmlspecialchars($key); ?>" value="<?= htmlspecialchars($value, ENT_QUOTES); ?>" />
                        <?php endforeach; ?>
                        <label class="explore-results__sort-label" for="sort">Ordenar por</label>
                        <select id="sort" name="sort" data-sort-select>
                            <?php foreach ($sortOptions as $option):
                                $value = (string) ($option['value'] ?? '');
                                $label = (string) ($option['label'] ?? $value);
                                $isSelected = $selectedSort === $value;
                            ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES); ?>"<?= $isSelected ? ' selected' : ''; ?>><?= htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </header>

                <?php if ($hasFilters): ?>
                    <div class="explore-results__chips" role="status" aria-live="polite">
                        <?php foreach ($activeChips as $chip): ?>
                            <span class="explore-results__chip"><?= htmlspecialchars($chip['label']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($results === []): ?>
                    <div class="explore-empty">
                        <h2>Sin coincidencias</h2>
                        <p>No encontramos experiencias con los filtros seleccionados. Ajusta la duración o el presupuesto para ver más opciones.</p>
                        <a class="button button--primary" href="explorar.php">Borrar filtros</a>
                    </div>
                <?php else: ?>
                    <div class="cards-grid cards-grid--experiences">
                        <?php foreach ($results as $item):
                            $image = $item['image'] ?? null;
                            $theme = $item['theme'] ?? 'experience';
                            $priceText = $formatCurrency($item['price'] ?? null, $item['currency'] ?? 'PEN');
                            $duration = $item['duration'] ?? '';
                            $destination = $item['destination'] ?? '';
                            $region = $item['region'] ?? '';
                            $highlights = $item['highlights'] ?? [];
                            $rating = $item['rating'] ?? null;
                            $reviews = $item['reviews'] ?? null;
                            $typeLabel = $item['typeLabel'] ?? ($categoryLabels[$item['category'] ?? 'experiencias'] ?? 'Experiencia');
                            $title = $item['title'] ?? 'Experiencia';
                            $summary = $item['summary'] ?? '';
                        ?>
                            <article class="travel-card" data-theme="<?= htmlspecialchars($theme); ?>">
                                <div class="travel-card__media"<?= $image ? " style=\"background-image: url('" . htmlspecialchars($image, ENT_QUOTES) . "');\"" : ''; ?> aria-hidden="true"></div>
                                <div class="travel-card__content">
                                    <header class="travel-card__header">
                                        <span class="travel-card__category"><?= htmlspecialchars($typeLabel); ?></span>
                                        <?php if ($destination !== ''): ?>
                                            <span class="travel-card__badge"><?= htmlspecialchars($destination); ?><?= $region !== '' ? ' · ' . htmlspecialchars($region) : ''; ?></span>
                                        <?php endif; ?>
                                    </header>
                                    <h3 class="travel-card__title">
                                        <a href="<?= htmlspecialchars($item['href'] ?? '#', ENT_QUOTES); ?>"><?= htmlspecialchars($title); ?></a>
                                    </h3>
                                    <p class="travel-card__excerpt"><?= htmlspecialchars($summary); ?></p>
                                    <dl class="travel-card__meta">
                                        <?php if ($duration !== ''): ?>
                                            <div>
                                                <dt>Duración</dt>
                                                <dd><?= htmlspecialchars($duration); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($destination !== '' && $region !== ''): ?>
                                            <div>
                                                <dt>Ubicación</dt>
                                                <dd><?= htmlspecialchars($destination); ?> · <?= htmlspecialchars($region); ?></dd>
                                            </div>
                                        <?php elseif ($region !== ''): ?>
                                            <div>
                                                <dt>Región</dt>
                                                <dd><?= htmlspecialchars($region); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($rating !== null): ?>
                                            <div>
                                                <dt>Calificación</dt>
                                                <dd><?= number_format($rating, 1); ?><?= $reviews ? ' · ' . (int) $reviews . ' reseñas' : ''; ?></dd>
                                            </div>
                                        <?php endif; ?>
                                    </dl>
                                    <?php if (!empty($highlights)): ?>
                                        <ul class="travel-card__details">
                                            <?php foreach ($highlights as $highlight): ?>
                                                <li><?= htmlspecialchars($highlight); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <footer class="travel-card__footer">
                                    <div class="travel-card__pricing">
                                        <?php if ($priceText !== null): ?>
                                            <span class="travel-card__price"><?= htmlspecialchars($priceText); ?></span>
                                            <span class="travel-card__price-note">por viajero</span>
                                        <?php else: ?>
                                            <span class="travel-card__price">Consulta personalizada</span>
                                        <?php endif; ?>
                                    </div>
                                    <a class="travel-card__cta" href="<?= htmlspecialchars($item['href'] ?? '#', ENT_QUOTES); ?>">Ver detalles</a>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </section>

        <section class="explore__cta">
            <div class="explore__cta-content">
                <h2>¿Necesitas una propuesta a medida?</h2>
                <p>Cuéntanos el estilo de viaje que buscas y diseñaremos un itinerario exclusivo con alojamientos, actividades y traslados coordinados.</p>
            </div>
            <div class="explore__cta-actions">
                <a class="button button--primary" href="mailto:reservas@expediatravels.pe">Escribir a un asesor</a>
                <a class="button button--ghost" href="index.php#contacto">Ver canales de contacto</a>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/partials/site-footer.php'; ?>
    <?php include __DIR__ . '/partials/auth-modal.php'; ?>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <script src="scripts/explorar.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
