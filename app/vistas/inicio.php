<?php
    $siteSettings = $siteSettings ?? [];
    $siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
    $siteTagline = $siteSettings['siteTagline'] ?? null;
    $siteLogo = $siteSettings['siteLogo'] ?? null;
    $siteFavicon = $siteSettings['siteFavicon'] ?? null;
    if (!is_string($siteLogo) || trim($siteLogo) === '') {
        $siteLogo = null;
    }
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
    $hasHeroSlides = !empty($visibleHeroSlides);
    $contact = $siteSettings['contact'] ?? [];
    $contactEmails = $contact['emails'] ?? [];
    $contactPhones = $contact['phones'] ?? [];
    $contactAddresses = $contact['addresses'] ?? [];
    $contactLocations = $contact['locations'] ?? [];
    $socialLinks = $contact['social'] ?? [];

    $primaryPhone = $contactPhones[0] ?? null;
    $primaryEmail = $contactEmails[0] ?? null;

    $formatPhoneHref = static function (?string $phone): ?string {
        if ($phone === null) {
            return null;
        }

        $sanitised = preg_replace('/[^\\d+]/', '', $phone);

        return $sanitised !== '' ? $sanitised : null;
    };

    $primaryPhoneHref = $formatPhoneHref($primaryPhone);
    $currentUser = $currentUser ?? null;
    $isAuthenticated = is_array($currentUser) && !empty($currentUser);
    $isAdmin = $isAuthenticated && ($currentUser['rol'] ?? '') === 'administrador';
    $displayName = $isAuthenticated ? trim((string) ($currentUser['nombre'] ?? '')) : '';
    $accountDeleted = !empty($accountDeleted);

    $featuredPackages = $featuredPackages ?? [];
    $signatureExperiences = $signatureExperiences ?? [];
    $featuredCircuits = $featuredCircuits ?? $signatureExperiences;
    if (empty($featuredCircuits) && !empty($signatureExperiences)) {
        $featuredCircuits = $signatureExperiences;
    }

    $searchMetadata = $searchMetadata ?? ['regions' => [], 'styles' => [], 'durationOptions' => [], 'budgetOptions' => []];
    $searchRegions = $searchMetadata['regions'] ?? [];
    $searchStyles = $searchMetadata['styles'] ?? [];
    $searchDurations = $searchMetadata['durationOptions'] ?? [];
    $searchBudgets = $searchMetadata['budgetOptions'] ?? [];

    $slugify = static function (string $value): string {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $normalized = strtolower(trim((string) $normalized));
        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $normalized);
        if (!is_string($normalized)) {
            $normalized = '';
        }

        return $normalized !== '' ? trim($normalized, '-') : 'experiencia';
    };

    $resolveMediaPath = static function (?string $path) {
        if (!is_string($path)) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        $normalized = ltrim($trimmed, '/');
        if (is_file(__DIR__ . '/../../web/' . $normalized)) {
            return $normalized;
        }

        if (is_file(__DIR__ . '/../../' . $normalized)) {
            return '../' . $normalized;
        }

        if (is_file(__DIR__ . '/../../web/recursos/' . $normalized)) {
            return 'recursos/' . $normalized;
        }

        return null;
    };

    $parsePriceFromString = static function ($value): ?float {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $filtered = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
        if ($filtered === null || $filtered === false || $filtered === '') {
            return null;
        }

        return (float) str_replace(',', '', (string) $filtered);
    };

    $currencySymbols = [
        'PEN' => 'S/',
        'USD' => '$',
        'EUR' => '€',
    ];

    $formatCurrency = static function (?float $amount, string $currency) use ($currencySymbols): ?string {
        if ($amount === null) {
            return null;
        }

        $code = strtoupper($currency);
        $symbol = $currencySymbols[$code] ?? ($currencySymbols['PEN'] ?? 'S/');

        return sprintf('%s %s', $symbol, number_format($amount, 2));
    };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Expediatravels'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <script src="scripts/scroll-enhancements.js" defer></script>
    <script src="scripts/quick-view.js" defer></script>
</head>
<body class="page">

    <header class="site-header" data-site-header>
        <div class="site-header__inner">
            <a class="site-header__brand<?= $siteLogo ? ' site-header__brand--image' : ''; ?>" href="#inicio">
                <?php if ($siteLogo): ?>
                    <img class="site-header__logo-image" src="<?= htmlspecialchars($siteLogo, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($siteTitle); ?>" width="250" height="65" />
                    <?php if (!empty($siteTagline)): ?>
                        <span class="site-header__brand-tagline visually-hidden"><?= htmlspecialchars($siteTagline); ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="site-header__logo" aria-hidden="true">🧭</span>
                    <span class="site-header__brand-text">
                        <strong><?= htmlspecialchars($siteTitle); ?></strong>
                        <?php if (!empty($siteTagline)): ?>
                            <small><?= htmlspecialchars($siteTagline); ?></small>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </a>
            <button class="site-header__menu" type="button" aria-label="Abrir menú" aria-expanded="false" data-menu-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="site-header__nav" aria-label="Menú principal" data-site-nav>
                <a class="site-header__link" href="#destinos">Destino</a>
                <a class="site-header__link" href="#circuitos">Circuito</a>
                <a class="site-header__link" href="#paquetes">Paquetes</a>
                <a class="site-header__link" href="#contacto">Contacto</a>
            </nav>
            <div class="site-header__cta">
                <?php if (!empty($primaryPhone)): ?>
                    <div class="site-header__contact">
                        <span class="site-header__contact-label">Hablemos</span>
                        <a class="site-header__contact-phone" href="<?= htmlspecialchars($primaryPhoneHref ? 'tel:' . $primaryPhoneHref : '#contacto', ENT_QUOTES); ?>">
                            <?= htmlspecialchars($primaryPhone); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="site-header__actions">
                    <a class="site-header__icon-button" href="carrito.php" aria-label="Ver carrito">
                        <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                            <path d="M3.5 4h1.7a1 1 0 0 1 .98.804l.27 1.352M7 15h10.45a1 1 0 0 0 .98-.804l1.2-6A1 1 0 0 0 18.66 7H6.45" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                            <circle cx="9" cy="19" r="1.5" fill="currentColor" />
                            <circle cx="17" cy="19" r="1.5" fill="currentColor" />
                        </svg>
                    </a>
                    <?php if ($isAuthenticated): ?>
                        <div class="site-header__user" data-user-menu-container>
                            <button class="site-header__icon-button site-header__icon-button--user" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="site-header-user-menu" data-user-menu-toggle>
                                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-7 2-7 4.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1.5C19 16 16 14 12 14Z" fill="currentColor" />
                                </svg>
                                <span class="visually-hidden">Abrir menú de usuario</span>
                            </button>
                            <nav class="site-header__user-menu" id="site-header-user-menu" data-user-menu aria-label="Opciones de usuario" hidden>
                                <?php if ($displayName !== ''): ?>
                                    <p class="site-header__user-menu-greeting">Hola, <?= htmlspecialchars($displayName); ?></p>
                                <?php endif; ?>
                                <a class="site-header__user-menu-link" href="perfil.php" data-user-menu-close>Perfil</a>
                                <a class="site-header__user-menu-link" href="reservaciones.php" data-user-menu-close>Reservaciones</a>
                                <a class="site-header__user-menu-link" href="favoritos.php" data-user-menu-close>Favoritos</a>
                                <a class="site-header__user-menu-link" href="carrito.php" data-user-menu-close>Carrito</a>
                                <?php if ($isAdmin): ?>
                                    <a class="site-header__user-menu-link" href="../administracion/index.php" data-user-menu-close>Panel de Control</a>
                                <?php endif; ?>
                                <button class="site-header__user-menu-link site-header__user-menu-link--logout" type="button" data-auth-logout data-user-menu-close>Cerrar sesión</button>
                            </nav>
                        </div>
                    <?php else: ?>
                        <button class="button button--primary site-header__cta-button" type="button" data-auth-open>Iniciar sesión</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php if ($accountDeleted): ?>
        <div class="global-alert alert alert--success" role="status">
            Tu cuenta se eliminó correctamente. ¡Gracias por confiar en Expediatravels!
        </div>
    <?php endif; ?>

    <section class="hero" id="inicio" data-hero-slider>
        <?php if ($hasHeroSlides): ?>
            <div class="hero__backgrounds" data-hero-backgrounds>
                <?php foreach ($visibleHeroSlides as $index => $slide):
                    $isActive = $index === 0;
                    $label = $slide['label'] ?? null;
                    $altText = trim((string) ($slide['altText'] ?? ''));
                    $description = $slide['description'] ?? null;
                    $ariaLabel = $altText !== '' ? $altText : ($label !== null && $label !== '' ? $label : 'Imagen del hero');
                ?>
                    <div
                        class="hero__background<?= $isActive ? ' hero__background--active' : ''; ?>"
                        style="background-image: url('<?= htmlspecialchars((string) $slide['image'], ENT_QUOTES); ?>');"
                        role="img"
                        aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES); ?>"
                        data-hero-slide
                        <?= $label ? 'data-hero-label-text="' . htmlspecialchars($label, ENT_QUOTES) . '"' : ''; ?>
                        <?= $altText !== '' ? 'data-hero-alt="' . htmlspecialchars($altText, ENT_QUOTES) . '"' : ''; ?>
                        <?= $description ? 'data-hero-description="' . htmlspecialchars($description, ENT_QUOTES) . '"' : ''; ?>
                    ></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="hero__content">
            <div class="hero__copy">
                <h1 class="hero__title">Reserva tours y experiencias en Oxapampa</h1>
                <p class="hero__subtitle">Planifica tu viaje por la Selva Central del Perú con especialistas locales: Oxapampa, Villa Rica, Pozuzo y reservas de biosfera a tu ritmo.</p>
            </div>
            <form class="booking-form" action="explorar.php" method="get" role="search" data-hero-search>
                <fieldset class="booking-form__tabs">
                    <legend class="visually-hidden">Tipo de servicio</legend>
                    <label class="booking-tab">
                        <input type="radio" name="category" value="destinos" checked data-search-category />
                        <span>Destinos</span>
                    </label>
                    <label class="booking-tab">
                        <input type="radio" name="category" value="circuitos" data-search-category />
                        <span>Circuitos</span>
                    </label>
                    <label class="booking-tab">
                        <input type="radio" name="category" value="experiencias" data-search-category />
                        <span>Paquetes</span>
                    </label>
                </fieldset>
                <div class="booking-form__fields" data-search-fields>
                    <div class="booking-form__group" data-category-fields="destinos">
                        <label class="booking-field">
                            <span class="booking-field__label">Destino</span>
                            <select name="region" data-search-input data-field-name="region"<?php if (!empty($searchRegions)): ?> data-required="true" required<?php endif; ?>>
                                <option value="">Selecciona un destino</option>
                                <?php foreach ($searchRegions as $region): ?>
                                    <option value="<?= htmlspecialchars($region, ENT_QUOTES); ?>"><?= htmlspecialchars($region); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <?php if (!empty($searchStyles)): ?>
                            <label class="booking-field">
                                <span class="booking-field__label">Estilo de viaje</span>
                                <select data-search-input data-field-name="style">
                                    <option value="">Todos los estilos</option>
                                    <?php foreach ($searchStyles as $style): ?>
                                        <option value="<?= htmlspecialchars($style, ENT_QUOTES); ?>"><?= htmlspecialchars($style); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endif; ?>
                    </div>
                    <div class="booking-form__group" data-category-fields="circuitos" hidden>
                        <label class="booking-field">
                            <span class="booking-field__label">Zona</span>
                            <select data-search-input data-field-name="region">
                                <option value="">Cualquier destino</option>
                                <?php foreach ($searchRegions as $region): ?>
                                    <option value="<?= htmlspecialchars($region, ENT_QUOTES); ?>"><?= htmlspecialchars($region); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <?php if (!empty($searchDurations)): ?>
                            <label class="booking-field">
                                <span class="booking-field__label">Duración</span>
                                <select data-search-input data-field-name="duration">
                                    <option value="">Cualquier duración</option>
                                    <?php foreach ($searchDurations as $durationOption):
                                        $value = (string) ($durationOption['value'] ?? '');
                                        $label = (string) ($durationOption['label'] ?? $value);
                                        if ($value === '') {
                                            continue;
                                        }
                                    ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES); ?>"><?= htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($searchStyles)): ?>
                            <label class="booking-field">
                                <span class="booking-field__label">Estilo</span>
                                <select data-search-input data-field-name="style">
                                    <option value="">Todos los estilos</option>
                                    <?php foreach ($searchStyles as $style): ?>
                                        <option value="<?= htmlspecialchars($style, ENT_QUOTES); ?>"><?= htmlspecialchars($style); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endif; ?>
                    </div>
                    <div class="booking-form__group" data-category-fields="experiencias" hidden>
                        <label class="booking-field">
                            <span class="booking-field__label">Destino</span>
                            <select data-search-input data-field-name="region">
                                <option value="">Cualquier destino</option>
                                <?php foreach ($searchRegions as $region): ?>
                                    <option value="<?= htmlspecialchars($region, ENT_QUOTES); ?>"><?= htmlspecialchars($region); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <?php if (!empty($searchBudgets)): ?>
                            <label class="booking-field">
                                <span class="booking-field__label">Presupuesto</span>
                                <select data-search-input data-field-name="budget">
                                    <option value="">Todos los presupuestos</option>
                                    <?php foreach ($searchBudgets as $budgetOption):
                                        $value = (string) ($budgetOption['value'] ?? '');
                                        $label = (string) ($budgetOption['label'] ?? $value);
                                        if ($value === '') {
                                            continue;
                                        }
                                    ?>
                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES); ?>"><?= htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($searchStyles)): ?>
                            <label class="booking-field">
                                <span class="booking-field__label">Estilo de viaje</span>
                                <select data-search-input data-field-name="style">
                                    <option value="">Todos los estilos</option>
                                    <?php foreach ($searchStyles as $style): ?>
                                        <option value="<?= htmlspecialchars($style, ENT_QUOTES); ?>"><?= htmlspecialchars($style); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        <?php endif; ?>
                    </div>
                    <button class="booking-form__submit" type="submit">Buscar</button>
                </div>
            </form>
            <?php if ($hasHeroSlides): ?>
                <div class="hero__slider-meta">
                    <?php $initialLabel = $visibleHeroSlides[0]['label'] ?? null; ?>
                    <?php if (!empty($initialLabel)): ?>
                        <div class="hero__slider-label" data-hero-label><?= htmlspecialchars($initialLabel); ?></div>
                    <?php else: ?>
                        <div class="hero__slider-label" data-hero-label hidden></div>
                    <?php endif; ?>
                    <?php if (count($visibleHeroSlides) > 1): ?>
                        <div class="hero__slider-dots" role="tablist">
                            <?php foreach ($visibleHeroSlides as $index => $slide):
                                $label = $slide['label'] ?? ('Fondo ' . ($index + 1));
                            ?>
                                <button
                                    type="button"
                                    class="hero__dot<?= $index === 0 ? ' hero__dot--active' : ''; ?>"
                                    data-hero-dot="<?= $index; ?>"
                                    aria-label="Mostrar <?= htmlspecialchars($label, ENT_QUOTES); ?>"
                                    aria-pressed="<?= $index === 0 ? 'true' : 'false'; ?>"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main>
        <section class="destinations-showcase" id="destinos">
            <?php
                $destinationStatsPresets = [
                    ['tours' => 1, 'departures' => 32, 'guests' => 12_774],
                    ['tours' => 2, 'departures' => 26, 'guests' => 11_892],
                    ['tours' => 3, 'departures' => 18, 'guests' => 10_115],
                    ['tours' => 4, 'departures' => 22, 'guests' => 9_420],
                ];

                $packagesByDestination = [];
                foreach ($featuredPackages as $package) {
                    $destinationKey = $package['destino'] ?? null;
                    if ($destinationKey) {
                        $packagesByDestination[$destinationKey] = ($packagesByDestination[$destinationKey] ?? 0) + 1;
                    }
                }

                $fallbackImageMap = [
                    'oxapampa.jpg' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1400&auto=format&fit=crop',
                    'villa-rica.jpg' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1400&auto=format&fit=crop',
                    'pozuzo.jpg' => 'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?q=80&w=1400&auto=format&fit=crop',
                    'perene.jpg' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?q=80&w=1400&auto=format&fit=crop',
                    'yanachaga.jpg' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?q=80&w=1400&auto=format&fit=crop',
                    'Oxapampa' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1400&auto=format&fit=crop',
                    'Villa Rica' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1400&auto=format&fit=crop',
                    'Pozuzo' => 'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?q=80&w=1400&auto=format&fit=crop',
                    'Perené' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?q=80&w=1400&auto=format&fit=crop',
                    'Yanachaga' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?q=80&w=1400&auto=format&fit=crop',
                ];

                $defaultRegionName = 'Otros distinos';
                $destinationsByRegion = [];
                foreach ($destinations as $index => $destination) {
                    $region = trim((string) ($destination['region'] ?? $defaultRegionName));
                    if ($region === '') {
                        $region = $defaultRegionName;
                    }

                    $destinationName = trim((string) ($destination['nombre'] ?? ''));
                    if ($destinationName === '') {
                        continue;
                    }

                    $imageSource = $destination['imagen_destacada'] ?? $destination['imagen'] ?? null;
                    $imagePath = ($resolveMediaPath)($imageSource);
                    if ($imagePath === null && is_string($imageSource) && isset($fallbackImageMap[$imageSource])) {
                        $imagePath = $fallbackImageMap[$imageSource];
                    }
                    if ($imagePath === null && isset($fallbackImageMap[$destinationName])) {
                        $imagePath = $fallbackImageMap[$destinationName];
                    }

                    $stats = $destinationStatsPresets[$index % count($destinationStatsPresets)] ?? [
                        'departures' => 20 + ($index * 2),
                        'guests' => 9_200 + ($index * 480),
                    ];

                    $circuitCount = (int) ($destination['circuit_count'] ?? 0);
                    $packageCount = (int) ($destination['package_count'] ?? 0);
                    $metaSegments = [];
                    if ($circuitCount > 0) {
                        $metaSegments[] = $circuitCount === 1
                            ? '1 circuito publicado'
                            : sprintf('%d circuitos publicados', $circuitCount);
                    }
                    if ($packageCount > 0) {
                        $metaSegments[] = $packageCount === 1
                            ? '1 paquete activo'
                            : sprintf('%d paquetes activos', $packageCount);
                    }
                    if (empty($metaSegments)) {
                        $metaSegments[] = sprintf('%s salidas programadas', str_pad((string) ($stats['departures'] ?? 0), 2, '0', STR_PAD_LEFT));
                    }
                    $metaSegments[] = sprintf('%s viajeros felices', number_format((int) ($stats['guests'] ?? 0)));

                    $destinationSlug = $destination['slug'] ?? $slugify($destinationName);

                    $destinationsByRegion[$region][] = [
                        'title' => $destinationName,
                        'tags' => $metaSegments,
                        'img' => $imagePath,
                        'slug' => $destinationSlug,
                        'href' => 'destino.php?slug=' . urlencode($destinationSlug),
                    ];
                }

                $destinationsList = [];
                foreach ($destinationsByRegion as $items) {
                    foreach ($items as $item) {
                        $tags = array_values(array_filter(
                            array_map(
                                static fn ($value): string => is_string($value) ? trim($value) : '',
                                $item['tags'] ?? []
                            ),
                            static fn (string $value): bool => $value !== ''
                        ));

                        $destinationsList[] = [
                            'title' => (string) $item['title'],
                            'img' => $item['img'] ? (string) $item['img'] : null,
                            'href' => isset($item['href']) ? (string) $item['href'] : null,
                            'tags' => $tags,
                        ];
                    }
                }
            ?>
            <div class="destinations-showcase__container">
                <h1>Nuestros Destinos</h1>
                <section class="cards" id="destination-cards" aria-live="polite">
                    <?php if (!empty($destinationsList)): ?>
                        <?php foreach ($destinationsList as $item): ?>
                            <?php
                                $cardImage = isset($item['img']) ? ($resolveMediaPath)($item['img']) : null;
                                if ($cardImage === null) {
                                    $cardImage = ($resolveMediaPath)('recursos/placeholder-destino.svg');
                                }
                                $cardHref = $item['href'] ?? null;
                                $cardTags = array_values(array_filter(
                                    is_array($item['tags'] ?? null) ? $item['tags'] : [],
                                    static fn ($tag): bool => is_string($tag) && $tag !== ''
                                ));
                            ?>
                            <article class="card" role="article">
                                <a class="card__link" href="<?= htmlspecialchars($cardHref ?? '#', ENT_QUOTES); ?>">
                                    <?php if (!empty($cardImage)): ?>
                                        <img class="media" src="<?= htmlspecialchars($cardImage); ?>" alt="<?= htmlspecialchars($item['title']); ?>" />
                                    <?php endif; ?>
                                    <div class="body">
                                        <div class="title">
                                            <span class="row">
                                                <svg class="pin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 22s7-6.1 7-12a7 7 0 10-14 0c0 5.9 7 12 7 12z" stroke="currentColor" stroke-width="1.5" />
                                                    <circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5" />
                                                </svg>
                                            </span>
                                            <?= htmlspecialchars($item['title']); ?>
                                        </div>
                                        <?php if (!empty($cardTags)): ?>
                                            <div class="data-tags">
                                                <?php foreach ($cardTags as $tag): ?>
                                                    <span class="data-tag"><?= htmlspecialchars($tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="destinations-showcase__empty">Pronto compartiremos nuevos destinos destacados.</p>
                    <?php endif; ?>
                </section>
                <div class="dots" id="destination-dots" aria-hidden="true">
                    <?php if (!empty($destinationsList)): ?>
                        <?php foreach ($destinationsList as $index => $_): ?>
                            <span class="dot<?= $index === 0 ? ' active' : ''; ?>"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--experiences" id="circuitos">
            <div class="section__header">
                <h2>Circuitos Turisticos</h2>
                <p>Aventuras que combinan cultura viva, sostenibilidad y confort en cada detalle.</p>
            </div>
            <div class="cards-grid cards-grid--experiences">
                <?php foreach ($featuredCircuits as $experience): ?>
                    <?php
                        $circuitName = (string) ($experience['nombre'] ?? ($experience['title'] ?? 'Circuito destacado'));
                        $circuitSlug = $experience['slug'] ?? $slugify($circuitName);
                        $circuitHref = 'circuito.php?slug=' . urlencode($circuitSlug);
                        $circuitCurrency = $experience['moneda'] ?? 'PEN';
                        $circuitPrice = $parsePriceFromString($experience['precio'] ?? $experience['priceFrom'] ?? null);
                        if ($circuitPrice === null && isset($experience['priceFrom'])) {
                            $circuitPrice = $parsePriceFromString($experience['priceFrom']);
                        }
                        $circuitImage = isset($experience['imagen']) ? ($resolveMediaPath)($experience['imagen']) : null;
                        $circuitDestination = $experience['destino'] ?? ($experience['location'] ?? 'Selva Central');
                        $circuitRegion = $experience['region'] ?? '';
                        $circuitDuration = trim((string) ($experience['duracion'] ?? ($experience['duration'] ?? '')));
                        $circuitDurationDisplay = $circuitDuration !== '' ? $circuitDuration : '1 día';
                        $circuitGroupRaw = $experience['grupo'] ?? $experience['personas'] ?? $experience['capacidad'] ?? $experience['capacidad_maxima'] ?? $experience['group'] ?? null;
                        if (is_numeric($circuitGroupRaw)) {
                            $circuitGroupRaw = (int) $circuitGroupRaw . ' pax';
                        }
                        $circuitGroupDisplay = is_string($circuitGroupRaw) && trim($circuitGroupRaw) !== '' ? trim($circuitGroupRaw) : '6 pax';
                        $ratingValueRaw = $experience['ratingPromedio'] ?? $experience['rating'] ?? $experience['calificacion'] ?? null;
                        if (is_string($ratingValueRaw) && is_numeric(str_replace(',', '.', $ratingValueRaw))) {
                            $ratingValueRaw = str_replace(',', '.', $ratingValueRaw);
                        }
                        $ratingValue = is_numeric($ratingValueRaw) ? round((float) $ratingValueRaw, 1) : null;
                        $ratingCountRaw = $experience['totalResenas'] ?? $experience['reseñas'] ?? $experience['resenas'] ?? $experience['reviews'] ?? $experience['reviewsCount'] ?? null;
                        if (is_string($ratingCountRaw)) {
                            $ratingCountRaw = preg_replace('/[^0-9]/', '', $ratingCountRaw);
                        }
                        $ratingCount = is_numeric($ratingCountRaw) ? (int) $ratingCountRaw : null;
                        $servicesRaw = $experience['servicios'] ?? [];
                        $servicesList = array_values(array_filter(
                            is_array($servicesRaw) ? $servicesRaw : [],
                            static fn ($service): bool => is_string($service) && trim($service) !== ''
                        ));
                        $transportLabel = null;
                        foreach ($servicesList as $service) {
                            if (stripos($service, 'transporte') !== false || stripos($service, 'traslado') !== false) {
                                $transportLabel = $service;
                                break;
                            }
                        }
                        $transportDisplay = $transportLabel ?? ($servicesList[0] ?? 'Traslados incluidos');
                        $highlightService = null;
                        foreach ($servicesList as $service) {
                            if ($service !== $transportDisplay) {
                                $highlightService = $service;
                                break;
                            }
                        }
                        if ($highlightService === null) {
                            $highlightService = 'Experiencia guiada con expertos locales';
                        }
                        $locationSegments = [];
                        $circuitDestinationLabel = trim((string) $circuitDestination);
                        $circuitRegionLabel = is_string($circuitRegion) ? trim($circuitRegion) : '';
                        if ($circuitDestinationLabel !== '') {
                            $locationSegments[] = $circuitDestinationLabel;
                        }
                        if ($circuitRegionLabel !== '' && !in_array($circuitRegionLabel, $locationSegments, true)) {
                            $locationSegments[] = $circuitRegionLabel;
                        }
                        $circuitLocationDisplay = !empty($locationSegments) ? implode(' · ', $locationSegments) : 'Selva Central';
                        $difficultyDisplay = '';
                        $difficultyRaw = trim((string) ($experience['dificultad'] ?? $experience['experiencia'] ?? ''));
                        $difficultyLabels = [
                            'relajado' => 'Relajado',
                            'moderado' => 'Moderado',
                            'intenso' => 'Intenso',
                            'activo' => 'Activo',
                        ];
                        $difficultyKey = strtolower($difficultyRaw);
                        if (isset($difficultyLabels[$difficultyKey])) {
                            $difficultyDisplay = $difficultyLabels[$difficultyKey];
                        } else {
                            $difficultyDisplay = $difficultyRaw !== '' ? ucfirst($difficultyRaw) : '';
                        }
                        $nextDepartureDisplay = '';
                        $nextDepartureRaw = $experience['proximaSalida'] ?? $experience['proxima_salida'] ?? $experience['nextDeparture'] ?? $experience['frecuencia'] ?? '';
                        if ($nextDepartureRaw instanceof \DateTimeInterface) {
                            $nextDepartureDisplay = $nextDepartureRaw->format('d M Y');
                        } else {
                            $nextDepartureDisplay = is_string($nextDepartureRaw) ? trim($nextDepartureRaw) : '';
                        }
                        $circuitPriceText = $formatCurrency($circuitPrice, $circuitCurrency);
                        $priceNoteText = $circuitPriceText !== null ? $circuitDurationDisplay : '';
                        $ratingDisplay = $ratingValue !== null ? number_format($ratingValue, 1, '.', '') : null;
                        $ratingCountDisplay = $ratingCount !== null && $ratingCount > 0 ? number_format($ratingCount, 0, '.', ',') : null;
                    ?>
                    <article class="circuit-card">
                        <?php if ($circuitImage): ?>
                            <div class="circuit-card__media">
                                <img class="circuit-card__image" src="<?= htmlspecialchars($circuitImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($circuitName); ?>" loading="lazy" />
                                <?php if ($circuitDestinationLabel !== '' || $difficultyDisplay !== ''): ?>
                                    <div class="circuit-card__tags circuit-card__tags--overlay">
                                        <?php if ($circuitDestinationLabel !== ''): ?>
                                            <span class="circuit-card__tag circuit-card__tag--destination">
                                                <span class="circuit-card__tag-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" focusable="false" role="presentation">
                                                        <path d="M12 2.75a7.25 7.25 0 0 0-7.25 7.25c0 4.78 4.43 9.6 6.4 11.5a1.2 1.2 0 0 0 1.7 0c1.97-1.9 6.4-6.72 6.4-11.5A7.25 7.25 0 0 0 12 2.75Zm0 9.88a2.63 2.63 0 1 1 0-5.25 2.63 2.63 0 0 1 0 5.25Z" fill="currentColor" />
                                                    </svg>
                                                </span>
                                                <span class="circuit-card__tag-text">
                                                    <span class="visually-hidden">Destino asociado:</span>
                                                    <?= htmlspecialchars($circuitDestinationLabel); ?>
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($difficultyDisplay !== ''): ?>
                                            <span class="circuit-card__tag circuit-card__tag--difficulty">
                                                <span class="circuit-card__tag-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" focusable="false" role="presentation">
                                                        <path d="M3.5 19.25h17a1 1 0 0 0 .86-1.5l-6.5-11a1 1 0 0 0-1.72 0l-2.1 3.54-1.28-2.12a1 1 0 0 0-1.72 0l-5.25 8.87a1 1 0 0 0 .86 1.5Zm9.5-10.01L18.02 17.25H5.98l3.4-5.75 1.28 2.12a1 1 0 0 0 1.72 0Z" fill="currentColor" />
                                                    </svg>
                                                </span>
                                                <span class="circuit-card__tag-text">
                                                    <span class="visually-hidden">Dificultad:</span>
                                                    <?= htmlspecialchars($difficultyDisplay); ?>
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="circuit-card__body">
                            <div class="circuit-card__location">
                                <span class="circuit-card__location-icon" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none">
                                        <path d="M10 18s6-5.2 6-10A6 6 0 1 0 4 8c0 4.8 6 10 6 10z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="10" cy="8" r="2.5" stroke="currentColor" stroke-width="1.4" />
                                    </svg>
                                </span>
                                <span class="circuit-card__location-text"><?= htmlspecialchars($circuitLocationDisplay); ?></span>
                            </div>
                            <h3 class="circuit-card__title">
                                <a href="<?= htmlspecialchars($circuitHref, ENT_QUOTES); ?>"><?= htmlspecialchars($circuitName); ?></a>
                            </h3>
                            <ul class="circuit-card__features" aria-label="Características del circuito">
                                <li>
                                    <span class="circuit-card__feature-icon" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none">
                                            <path d="M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M4.5 16a5.5 5.5 0 0 1 11 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span><?= htmlspecialchars($circuitGroupDisplay); ?></span>
                                </li>
                                <li>
                                    <span class="circuit-card__feature-icon" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none">
                                            <rect x="3" y="4" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.4" />
                                            <path d="M3 8h14" stroke="currentColor" stroke-width="1.4" />
                                            <path d="M7 2v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                                            <path d="M13 2v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <span><?= htmlspecialchars($circuitDurationDisplay); ?></span>
                                </li>
                                <li>
                                    <span class="circuit-card__feature-icon" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none">
                                            <path d="M3 11h14l1 4H2l1-4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M5.5 11V6.5A2.5 2.5 0 0 1 8 4h4a2.5 2.5 0 0 1 2.5 2.5V11" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="6" cy="15" r="1.2" fill="currentColor" />
                                            <circle cx="14" cy="15" r="1.2" fill="currentColor" />
                                        </svg>
                                    </span>
                                    <span><?= htmlspecialchars($transportDisplay); ?></span>
                                </li>
                            </ul>
                            <?php if (($circuitDestinationLabel !== '' || $difficultyDisplay !== '') && !$circuitImage): ?>
                                <div class="circuit-card__tags">
                                    <?php if ($circuitDestinationLabel !== ''): ?>
                                        <span class="circuit-card__tag circuit-card__tag--destination">
                                            <span class="circuit-card__tag-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" focusable="false" role="presentation">
                                                    <path d="M12 2.75a7.25 7.25 0 0 0-7.25 7.25c0 4.78 4.43 9.6 6.4 11.5a1.2 1.2 0 0 0 1.7 0c1.97-1.9 6.4-6.72 6.4-11.5A7.25 7.25 0 0 0 12 2.75Zm0 9.88a2.63 2.63 0 1 1 0-5.25 2.63 2.63 0 0 1 0 5.25Z" fill="currentColor" />
                                                </svg>
                                            </span>
                                            <span class="circuit-card__tag-text">
                                                <span class="visually-hidden">Destino asociado:</span>
                                                <?= htmlspecialchars($circuitDestinationLabel); ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($difficultyDisplay !== ''): ?>
                                        <span class="circuit-card__tag circuit-card__tag--difficulty">
                                            <span class="circuit-card__tag-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" focusable="false" role="presentation">
                                                    <path d="M3.5 19.25h17a1 1 0 0 0 .86-1.5l-6.5-11a1 1 0 0 0-1.72 0l-2.1 3.54-1.28-2.12a1 1 0 0 0-1.72 0l-5.25 8.87a1 1 0 0 0 .86 1.5Zm9.5-10.01L18.02 17.25H5.98l3.4-5.75 1.28 2.12a1 1 0 0 0 1.72 0Z" fill="currentColor" />
                                                </svg>
                                            </span>
                                            <span class="circuit-card__tag-text">
                                                <span class="visually-hidden">Dificultad:</span>
                                                <?= htmlspecialchars($difficultyDisplay); ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($nextDepartureDisplay !== ''): ?>
                                <p class="circuit-card__meta">
                                    <span class="circuit-card__meta-label">Próxima salida:</span>
                                    <span class="circuit-card__meta-value"><?= htmlspecialchars($nextDepartureDisplay); ?></span>
                                </p>
                            <?php endif; ?>
                            <p class="circuit-card__highlight"><?= htmlspecialchars($highlightService); ?></p>
                            <div class="circuit-card__rating">
                                <span class="circuit-card__stars" aria-hidden="true">
                                    <svg viewBox="0 0 88 16" xmlns="http://www.w3.org/2000/svg" fill="none">
                                        <path d="M8 0l1.9 5.9H16l-4.8 3.5 1.8 5.9L8 12l-5.1 3.3 1.8-5.9L0 5.9h6.1L8 0z" fill="currentColor" />
                                        <path d="M25.6 0l1.9 5.9h6.1l-4.8 3.5 1.8 5.9-5-3.3-5.1 3.3 1.8-5.9-4.9-3.5h6.1L25.6 0z" fill="currentColor" />
                                        <path d="M43.2 0l1.9 5.9h6.1l-4.8 3.5 1.8 5.9-5-3.3-5.1 3.3 1.8-5.9-4.9-3.5h6.1L43.2 0z" fill="currentColor" />
                                        <path d="M60.8 0l1.9 5.9h6.1l-4.8 3.5 1.8 5.9-5-3.3-5.1 3.3 1.8-5.9-4.9-3.5h6.1L60.8 0z" fill="currentColor" />
                                        <path d="M78.4 0l1.9 5.9h6.1l-4.8 3.5 1.8 5.9-5-3.3-5.1 3.3 1.8-5.9-4.9-3.5h6.1L78.4 0z" fill="currentColor" />
                                    </svg>
                                </span>
                                <span class="circuit-card__rating-text">
                                    <?php if ($ratingDisplay !== null): ?>
                                        <?= htmlspecialchars($ratingDisplay); ?> ★
                                    <?php else: ?>
                                        Sin reseñas
                                    <?php endif; ?>
                                    <?php if ($ratingCountDisplay !== null): ?>
                                        <span class="circuit-card__rating-count">(<?= htmlspecialchars($ratingCountDisplay); ?> reseñas)</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <footer class="circuit-card__footer">
                            <div class="circuit-card__price-group">
                                <span class="circuit-card__price-label">Desde</span>
                                <div class="circuit-card__price-amount">
                                    <?php if ($circuitPriceText !== null): ?>
                                        <strong><?= htmlspecialchars($circuitPriceText); ?></strong>
                                        <?php if ($priceNoteText !== ''): ?>
                                            <span>/ <?= htmlspecialchars($priceNoteText); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <strong>Consultar</strong>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a class="circuit-card__cta" href="<?= htmlspecialchars($circuitHref, ENT_QUOTES); ?>">Ver</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="quick-view-modal" id="quick-view-modal" hidden>
            <div class="quick-view-modal__backdrop" data-quick-view-close></div>
            <div class="quick-view-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="quick-view-title">
                <button type="button" class="quick-view-modal__close" data-quick-view-close aria-label="Cerrar vista rápida">×</button>
                <div class="quick-view-modal__image" data-quick-view-image hidden></div>
                <div class="quick-view-modal__body">
                    <p class="quick-view-modal__destination" data-quick-view-destination></p>
                    <h2 class="quick-view-modal__title" id="quick-view-title" data-quick-view-title></h2>
                    <p class="quick-view-modal__summary" data-quick-view-summary></p>
                    <dl class="quick-view-modal__details">
                        <div>
                            <dt>Duración</dt>
                            <dd data-quick-view-duration>Pronto</dd>
                        </div>
                        <div>
                            <dt>Experiencia</dt>
                            <dd data-quick-view-experience>Pronto</dd>
                        </div>
                        <div>
                            <dt>Grupo</dt>
                            <dd data-quick-view-group>Pronto</dd>
                        </div>
                        <div>
                            <dt>Próxima salida</dt>
                            <dd data-quick-view-departure>Pronto</dd>
                        </div>
                        <div>
                            <dt>Reseñas</dt>
                            <dd data-quick-view-reviews>Pronto</dd>
                        </div>
                    </dl>
                    <div class="quick-view-modal__footer">
                        <div class="quick-view-modal__price">
                            <span class="quick-view-modal__price-value" data-quick-view-price></span>
                            <span class="quick-view-modal__price-note" data-quick-view-price-note></span>
                        </div>
                        <a class="quick-view-modal__link button button--primary" data-quick-view-link href="#">Ver circuito completo</a>
                    </div>
                </div>
            </div>
        </div>

        <section id="paquetes" class="section section--packages">
            <div class="section__header">
                <h2>Paquetes Turisticos</h2>
                <p>Itinerarios listos para desconectar, descubrir la biodiversidad y compartir con comunidades locales.</p>
            </div>
            <div class="cards-grid">
                <?php foreach ($featuredPackages as $package): ?>
                    <?php
                        $packageName = (string) ($package['nombre'] ?? '');
                        $packageSlug = $package['slug'] ?? $slugify($packageName);
                        $packageHref = 'paquete.php?slug=' . urlencode($packageSlug);
                        $packageCurrency = $package['moneda'] ?? 'PEN';
                        $packagePrice = $parsePriceFromString($package['precio'] ?? null);
                        $originalPrice = $packagePrice !== null ? $packagePrice * 1.18 : null;
                        $statusLabel = ($packagePrice !== null && $packagePrice <= 110) ? 'Oferta especial' : 'Salida garantizada';
                        $tagParts = array_filter([
                            trim((string) ($package['destino'] ?? '')),
                            trim((string) ($package['region'] ?? '')),
                        ]);
                        $tagLabel = implode(' • ', $tagParts);
                        $imagePath = null;
                        if (!empty($package['imagen'])) {
                            $imagePath = ($resolveMediaPath)($package['imagen']);
                        }
                        $priceText = $formatCurrency($packagePrice, $packageCurrency);
                        $originalPriceText = $formatCurrency($originalPrice, $packageCurrency);
                    ?>
                    <article class="travel-card" data-theme="package">
                        <div class="travel-card__media"<?= $imagePath ? " style=\"background-image: url('" . htmlspecialchars($imagePath) . "');\"" : ''; ?> aria-hidden="true"></div>
                        <div class="travel-card__pill-group">
                            <span class="travel-card__status"><?= htmlspecialchars($statusLabel); ?></span>
                            <?php if ($tagLabel !== ''): ?>
                                <span class="travel-card__tag"><?= htmlspecialchars($tagLabel); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="travel-card__content">
                            <header class="travel-card__header">
                                <span class="travel-card__category">Tour <?= htmlspecialchars($package['destino']); ?></span>
                                <span class="travel-card__badge">Circuito destacado</span>
                            </header>
                            <h3 class="travel-card__title">
                                <a href="<?= htmlspecialchars($packageHref, ENT_QUOTES); ?>"><?= htmlspecialchars($packageName); ?></a>
                            </h3>
                            <p class="travel-card__excerpt"><?= htmlspecialchars($package['resumen']); ?></p>
                            <dl class="travel-card__meta">
                                <div>
                                    <dt>Duración</dt>
                                    <dd><?= htmlspecialchars($package['duracion']); ?></dd>
                                </div>
                                <div>
                                    <dt>Destino</dt>
                                    <dd><?= htmlspecialchars($package['destino']); ?></dd>
                                </div>
                                <div>
                                    <dt>Incluye</dt>
                                    <dd>Guía local • Traslados</dd>
                                </div>
                            </dl>
                        </div>
                        <footer class="travel-card__footer">
                            <div class="travel-card__pricing">
                                <?php if ($priceText !== null): ?>
                                    <span class="travel-card__price"><?= htmlspecialchars($priceText); ?></span>
                                    <?php if ($originalPriceText !== null): ?>
                                        <span class="travel-card__price-original"><?= htmlspecialchars($originalPriceText); ?></span>
                                    <?php endif; ?>
                                    <span class="travel-card__price-note">p/p</span>
                                <?php else: ?>
                                    <span class="travel-card__price">Pronto</span>
                                <?php endif; ?>
                            </div>
                            <a class="travel-card__cta" href="<?= htmlspecialchars($packageHref, ENT_QUOTES); ?>">Ver</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!--<section class="section section--metrics">
            <div class="metrics">
                <div class="metric">
                    <strong><?= htmlspecialchars((string) ($metrics['destinos'] ?? 0)); ?>+</strong>
                    <span>destinos curados</span>
                </div>
                <div class="metric">
                    <strong><?= htmlspecialchars((string) ($metrics['paquetes'] ?? 0)); ?></strong>
                    <span>paquetes publicados</span>
                </div>
                <div class="metric">
                    <strong><?= htmlspecialchars((string) ($metrics['experiencias'] ?? 0)); ?>+</strong>
                    <span>experiencias guiadas</span>
                </div>
                <div class="metric">
                    <strong><?= htmlspecialchars(number_format((float) ($metrics['satisfaccion'] ?? 4.9), 1)); ?>/5</strong>
                    <span>satisfacción promedio</span>
                </div>
            </div>
        </section>-->

        <!--<section class="section section--pillars">
            <div class="pillars">
                <?php foreach ($travelPillars as $pillar): ?>
                    <article class="pillar">
                        <h3><?= htmlspecialchars($pillar['title']); ?></h3>
                        <p><?= htmlspecialchars($pillar['copy']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>-->

        <!--<section class="section section--testimonials">
            <div class="section__header">
                <h2>Lo que dicen los viajeros</h2>
                <p>Historias reales de quienes ya descubrieron la magia de la Selva Central con nosotros.</p>
            </div>
            <div class="testimonials">
                <?php foreach ($testimonials as $testimonial): ?>
                    <article class="testimonial">
                        <div class="testimonial__rating" aria-label="Valoración: <?= htmlspecialchars((string) $testimonial['rating']); ?> de 5">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <span class="star<?= $i < (int) $testimonial['rating'] ? ' star--filled' : ''; ?>" aria-hidden="true">★</span>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial__comment">“<?= htmlspecialchars($testimonial['comentario']); ?>”</p>
                        <footer>
                            <strong><?= htmlspecialchars($testimonial['usuario']); ?></strong>
                            <span><?= htmlspecialchars($testimonial['paquete']); ?></span>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>-->

     <section class="section section--cta" id="contacto">
            <div class="cta">
                <div class="cta__copy">
                    <h2>¿Listo para tu próximo viaje?</h2>
                    <p>Agenda una videollamada con nuestro equipo y personaliza tu experiencia en Oxapampa según tus intereses.</p>
                </div>
                <?php if (!empty($primaryEmail)): ?>
                    <a class="button button--primary" href="mailto:<?= htmlspecialchars($primaryEmail, ENT_QUOTES); ?>">Agendar asesoría</a>
                <?php else: ?>
                    <a class="button button--primary" href="#contacto">Solicitar información</a>
                <?php endif; ?>
            </div>
            <!--<div class="contact-cards">
                <?php if (!empty($contactPhones)): ?>
                    <article class="contact-card">
                        <h3 class="contact-card__title">Teléfonos</h3>
                        <ul class="contact-card__list">
                            <?php foreach ($contactPhones as $phone):
                                $phoneHref = $formatPhoneHref($phone);
                            ?>
                                <li class="contact-card__item">
                                    <?php if ($phoneHref): ?>
                                        <a href="tel:<?= htmlspecialchars($phoneHref, ENT_QUOTES); ?>"><?= htmlspecialchars($phone); ?></a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($phone); ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>
                <?php if (!empty($contactEmails)): ?>
                    <article class="contact-card">
                        <h3 class="contact-card__title">Correos</h3>
                        <ul class="contact-card__list">
                            <?php foreach ($contactEmails as $email): ?>
                                <li class="contact-card__item">
                                    <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES); ?>"><?= htmlspecialchars($email); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>
                <?php if (!empty($contactAddresses) || !empty($contactLocations)): ?>
                    <article class="contact-card">
                        <h3 class="contact-card__title">Ubicaciones</h3>
                        <ul class="contact-card__list">
                            <?php foreach ($contactAddresses as $index => $address):
                                $location = $contactLocations[$index] ?? null;
                            ?>
                                <li class="contact-card__item">
                                    <strong><?= htmlspecialchars($address); ?></strong>
                                    <?php if (!empty($location)): ?>
                                        <small><?= htmlspecialchars($location); ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                            <?php if (count($contactLocations) > count($contactAddresses)): ?>
                                <?php for ($i = count($contactAddresses); $i < count($contactLocations); $i++): ?>
                                    <li class="contact-card__item">
                                        <?= htmlspecialchars($contactLocations[$i]); ?>
                                    </li>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </ul>
                    </article>
                <?php endif; ?>
                <?php if (!empty($socialLinks)): ?>
                    <article class="contact-card">
                        <h3 class="contact-card__title">Redes sociales</h3>
                        <ul class="contact-card__list">
                            <?php foreach ($socialLinks as $social):
                                $label = $social['label'] ?? '';
                                $url = $social['url'] ?? '';
                                if ($label === '' || $url === '') {
                                    continue;
                                }
                            ?>
                                <li class="contact-card__item">
                                    <a href="<?= htmlspecialchars($url, ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer">
                                        <?= htmlspecialchars($label); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>
            </div>-->
        </section>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>

    <div class="auth-modal" data-auth-modal hidden>
        <div class="auth-modal__backdrop" data-auth-close></div>
        <div class="auth-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
            <button class="auth-modal__close" type="button" aria-label="Cerrar ventana" data-auth-close>×</button>
            <div class="auth-modal__logo" aria-hidden="true">🧭</div>
            <div class="auth-modal__message" data-auth-message role="status" aria-live="polite"></div>

            <section class="auth-view auth-view--active" data-auth-view="login">
                <header class="auth-view__header">
                    <h2 id="auth-modal-title">Inicia sesión</h2>
                    <p>Accede a tu cuenta para completar tus reservas y seguir tus itinerarios.</p>
                </header>
                <form class="auth-form" id="auth-login-form" novalidate>
                    <label class="auth-field">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" autocomplete="email" required />
                    </label>
                    <label class="auth-field">
                        <span>Contraseña</span>
                        <input type="password" name="password" autocomplete="current-password" required />
                    </label>
                    <label class="auth-checkbox">
                        <input type="checkbox" name="recordar" value="1" />
                        <span>Recordar mi cuenta</span>
                    </label>
                    <button class="auth-submit" type="submit">Ingresar</button>
                    <button class="auth-google" type="button" data-auth-google>
                        <span aria-hidden="true">🔐</span>
                        Continuar con Google
                    </button>
                    <p class="auth-links">
                        <button class="auth-link" type="button" data-auth-switch="forgot">¿Olvidaste tu contraseña?</button>
                    </p>
                    <p class="auth-links">
                        ¿No tienes una cuenta?
                        <button class="auth-link" type="button" data-auth-switch="register">Crear una cuenta</button>
                    </p>
                </form>
            </section>

            <section class="auth-view" data-auth-view="register" hidden>
                <header class="auth-view__header">
                    <h2>Regístrate en minutos</h2>
                    <p>Crea una cuenta para guardar tus datos y recibir confirmaciones rápidas.</p>
                </header>
                <form class="auth-form" id="auth-register-form" novalidate>
                    <label class="auth-field">
                        <span>Nombres</span>
                        <input type="text" name="nombres" autocomplete="given-name" required />
                    </label>
                    <label class="auth-field">
                        <span>Apellidos</span>
                        <input type="text" name="apellidos" autocomplete="family-name" required />
                    </label>
                    <label class="auth-field">
                        <span>Celular</span>
                        <input type="tel" name="celular" autocomplete="tel" inputmode="tel" placeholder="Ej. +51 987 654 321" />
                    </label>
                    <label class="auth-field">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" autocomplete="email" required />
                    </label>
                    <label class="auth-field">
                        <span>Contraseña</span>
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required />
                    </label>
                    <button class="auth-submit" type="submit">Crear cuenta</button>
                    <p class="auth-links">
                        ¿Ya tienes una cuenta?
                        <button class="auth-link" type="button" data-auth-switch="login">Iniciar sesión</button>
                    </p>
                </form>
            </section>

            <section class="auth-view" data-auth-view="verify" hidden>
                <header class="auth-view__header">
                    <h2>Verifica tu cuenta</h2>
                    <p>Te enviamos un PIN de 6 dígitos a tu correo. Ingresa el código para activar tu cuenta.</p>
                </header>
                <form class="auth-form" id="auth-verify-form" novalidate>
                    <label class="auth-field">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" autocomplete="email" required />
                    </label>
                    <label class="auth-field">
                        <span>PIN de verificación</span>
                        <input type="text" name="pin" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required />
                    </label>
                    <button class="auth-submit" type="submit">Validar cuenta</button>
                    <p class="auth-links">
                        <button class="auth-link" type="button" data-auth-resend>Reenviar PIN</button>
                    </p>
                    <p class="auth-links">
                        ¿Necesitas iniciar sesión?
                        <button class="auth-link" type="button" data-auth-switch="login">Volver al inicio de sesión</button>
                    </p>
                </form>
            </section>

            <section class="auth-view" data-auth-view="forgot" hidden>
                <header class="auth-view__header">
                    <h2>Recupera tu contraseña</h2>
                    <p>Te enviaremos un enlace para restablecerla en pocos pasos.</p>
                </header>
                <form class="auth-form" id="auth-forgot-form" novalidate>
                    <label class="auth-field">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" autocomplete="email" required />
                    </label>
                    <button class="auth-submit" type="submit">Enviar instrucciones</button>
                    <p class="auth-links">
                        ¿Recordaste tu contraseña?
                        <button class="auth-link" type="button" data-auth-switch="login">Volver al inicio de sesión</button>
                    </p>
                </form>
            </section>
        </div>
    </div>

    <button class="scroll-to-top" type="button" aria-label="Volver al inicio" data-scroll-top hidden>
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 5.5a1 1 0 0 1 .78.37l6 7a1 1 0 0 1-1.56 1.26L12 7.89l-5.22 6.24a1 1 0 0 1-1.56-1.26l6-7A1 1 0 0 1 12 5.5Z" fill="currentColor" />
            <path d="M12 11a1 1 0 0 1 .78.37l3 3.5a1 1 0 0 1-1.56 1.26L12 13.89l-2.22 2.24a1 1 0 0 1-1.56-1.26l3-3.5A1 1 0 0 1 12 11Z" fill="currentColor" opacity="0.65" />
        </svg>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const header = document.querySelector('[data-site-header]');
            const toggle = document.querySelector('[data-menu-toggle]');
            const nav = document.querySelector('[data-site-nav]');

            if (!header) {
                return;
            }

            const updateHeaderState = () => {
                if (window.scrollY > 24) {
                    header.classList.add('site-header--scrolled');
                } else {
                    header.classList.remove('site-header--scrolled');
                }
            };

            updateHeaderState();
            window.addEventListener('scroll', updateHeaderState, { passive: true });

            const userMenuContainer = document.querySelector('[data-user-menu-container]');
            const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
            const userMenu = document.querySelector('[data-user-menu]');

            const closeUserMenu = () => {
                if (!userMenu || userMenu.hidden) {
                    return;
                }

                userMenu.hidden = true;
                userMenuToggle?.setAttribute('aria-expanded', 'false');
                userMenuContainer?.classList.remove('site-header__user--open');
            };

            if (toggle && nav) {
                toggle.addEventListener('click', () => {
                    const isOpen = header.classList.toggle('site-header--open');
                    toggle.setAttribute('aria-expanded', String(isOpen));
                    if (isOpen === true) {
                        closeUserMenu();
                    }
                });

                nav.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLElement && event.target.classList.contains('site-header__link')) {
                        header.classList.remove('site-header--open');
                        toggle.setAttribute('aria-expanded', 'false');
                        closeUserMenu();
                    }
                });
            }

            if (userMenuContainer && userMenuToggle && userMenu) {
                const openUserMenu = () => {
                    if (!userMenu.hidden) {
                        return;
                    }

                    userMenu.hidden = false;
                    userMenuToggle.setAttribute('aria-expanded', 'true');
                    userMenuContainer.classList.add('site-header__user--open');
                };

                userMenuToggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (userMenu.hidden) {
                        openUserMenu();
                    } else {
                        closeUserMenu();
                    }
                });

                userMenu.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLElement && event.target.closest('[data-user-menu-close]')) {
                        closeUserMenu();
                    }
                });

                document.addEventListener('click', (event) => {
                    const target = event.target;
                    if (!userMenu.hidden && userMenuContainer && target instanceof Node && !userMenuContainer.contains(target)) {
                        closeUserMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        if (!userMenu.hidden) {
                            closeUserMenu();
                            userMenuToggle.focus();
                        }
                    }
                });
            }

            const heroSection = document.querySelector('[data-hero-slider]');
            const heroSlides = heroSection ? Array.from(heroSection.querySelectorAll('[data-hero-slide]')) : [];
            const heroDots = heroSection ? Array.from(heroSection.querySelectorAll('[data-hero-dot]')) : [];
            const heroLabel = heroSection ? heroSection.querySelector('[data-hero-label]') : null;

            if (heroSection && heroSlides.length) {
                let activeIndex = heroSlides.findIndex((slide) => slide.classList.contains('hero__background--active'));
                if (activeIndex < 0) {
                    activeIndex = 0;
                }

                const applyLabel = (slide) => {
                    if (!heroLabel) {
                        return;
                    }

                    const labelText = (slide.dataset.heroLabelText || '').trim();
                    if (labelText) {
                        heroLabel.textContent = labelText;
                        heroLabel.hidden = false;
                    } else {
                        heroLabel.hidden = true;
                        heroLabel.textContent = '';
                    }
                };

                const setActive = (index) => {
                    heroSlides.forEach((slide, slideIndex) => {
                        slide.classList.toggle('hero__background--active', slideIndex === index);
                    });

                    heroDots.forEach((dot, dotIndex) => {
                        dot.classList.toggle('hero__dot--active', dotIndex === index);
                        dot.setAttribute('aria-pressed', dotIndex === index ? 'true' : 'false');
                    });

                    applyLabel(heroSlides[index]);
                    activeIndex = index;
                };

                applyLabel(heroSlides[activeIndex]);

                const advance = () => {
                    const nextIndex = (activeIndex + 1) % heroSlides.length;
                    setActive(nextIndex);
                };

                let intervalId = null;

                const stop = () => {
                    if (intervalId !== null) {
                        window.clearInterval(intervalId);
                        intervalId = null;
                    }
                };

                const start = () => {
                    if (heroSlides.length < 2) {
                        return;
                    }

                    stop();
                    intervalId = window.setInterval(advance, 6500);
                };

                heroDots.forEach((dot, dotIndex) => {
                    dot.addEventListener('click', () => {
                        setActive(dotIndex);
                        start();
                    });
                });

                heroSection.addEventListener('mouseenter', stop);
                heroSection.addEventListener('mouseleave', start);

                start();
            }

        });
    </script>
</body>
</html>
