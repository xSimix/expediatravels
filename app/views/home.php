<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Expediatravels'); ?></title>
    <link rel="stylesheet" href="css/app.css" />
</head>
<body class="page">
<?php
    $siteSettings = $siteSettings ?? [];
    $siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
    $siteTagline = $siteSettings['siteTagline'] ?? null;
    $heroSlides = $siteSettings['heroSlides'] ?? [];
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
?>
    <header class="site-header" data-site-header>
        <div class="site-header__inner">
            <a class="site-header__brand" href="#inicio">
                <span class="site-header__logo" aria-hidden="true">🧭</span>
                <span class="site-header__brand-text">
                    <strong><?= htmlspecialchars($siteTitle); ?></strong>
                    <?php if (!empty($siteTagline)): ?>
                        <small><?= htmlspecialchars($siteTagline); ?></small>
                    <?php endif; ?>
                </span>
            </a>
            <button class="site-header__menu" type="button" aria-label="Abrir menú" aria-expanded="false" data-menu-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="site-header__nav" aria-label="Menú principal" data-site-nav>
                <a class="site-header__link site-header__link--active" href="#inicio">Inicio</a>
                <a class="site-header__link" href="#paquetes">Paquetes</a>
                <a class="site-header__link" href="#destinos">Destinos</a>
                <a class="site-header__link" href="#experiencias">Experiencias</a>
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
                <a class="button button--primary site-header__cta-button" href="admin/index.php">Login</a>
            </div>
        </div>
    </header>

    <section class="hero" id="inicio" data-hero-slider>
        <?php if (!empty($heroSlides)): ?>
            <div class="hero__backgrounds" data-hero-backgrounds>
                <?php foreach ($heroSlides as $index => $slide):
                    $imageUrl = (string) ($slide['image'] ?? '');
                    if ($imageUrl === '') {
                        continue;
                    }
                    $isActive = $index === 0;
                    $label = $slide['label'] ?? null;
                ?>
                    <div
                        class="hero__background<?= $isActive ? ' hero__background--active' : ''; ?>"
                        style="background-image: url('<?= htmlspecialchars($imageUrl, ENT_QUOTES); ?>');"
                        data-hero-slide
                        <?= $label ? 'data-hero-label-text="' . htmlspecialchars($label, ENT_QUOTES) . '"' : ''; ?>
                    ></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="hero__content">
            <div class="hero__copy">
                <h1 class="hero__title">Reserva tours y experiencias en Oxapampa</h1>
                <p class="hero__subtitle">Planifica tu viaje por la Selva Central del Perú con especialistas locales: Oxapampa, Villa Rica, Pozuzo y reservas de biosfera a tu ritmo.</p>
            </div>
            <form class="booking-form" action="explorar.php" method="get" role="search">
                <fieldset class="booking-form__tabs">
                    <legend class="visually-hidden">Tipo de servicio</legend>
                    <label class="booking-tab">
                        <input type="radio" name="categoria" value="destinos" checked />
                        <span>Destinos</span>
                    </label>
                    <label class="booking-tab">
                        <input type="radio" name="categoria" value="circuitos" />
                        <span>Circuitos</span>
                    </label>
                    <label class="booking-tab">
                        <input type="radio" name="categoria" value="paquetes" />
                        <span>Paquetes</span>
                    </label>
                </fieldset>
                <div class="booking-form__fields">
                    <label class="booking-field">
                        <span class="booking-field__label">Destino</span>
                        <select name="destino" required>
                            <option value="" disabled selected>Selecciona un destino</option>
                            <option value="oxapampa">Oxapampa</option>
                            <option value="villa-rica">Villa Rica</option>
                            <option value="pozuzo">Pozuzo</option>
                            <option value="selva-central">Selva Central</option>
                        </select>
                    </label>
                    <label class="booking-field">
                        <span class="booking-field__label">Fecha de viaje</span>
                        <input type="date" name="fecha" min="<?= date('Y-m-d'); ?>" />
                    </label>
                    <label class="booking-field">
                        <span class="booking-field__label">Tipo de tour</span>
                        <select name="tipo">
                            <option value="" selected>Selecciona una experiencia</option>
                            <option value="aventura">Aventura</option>
                            <option value="cultural">Cultural</option>
                            <option value="gastronomia">Gastronomía</option>
                            <option value="naturaleza">Naturaleza</option>
                        </select>
                    </label>
                    <button class="booking-form__submit" type="submit">Buscar</button>
                </div>
            </form>
            <?php if (!empty($heroSlides)): ?>
                <div class="hero__slider-meta">
                    <?php $initialLabel = $heroSlides[0]['label'] ?? null; ?>
                    <?php if (!empty($initialLabel)): ?>
                        <div class="hero__slider-label" data-hero-label><?= htmlspecialchars($initialLabel); ?></div>
                    <?php else: ?>
                        <div class="hero__slider-label" data-hero-label hidden></div>
                    <?php endif; ?>
                    <?php if (count($heroSlides) > 1): ?>
                        <div class="hero__slider-dots" role="tablist">
                            <?php foreach ($heroSlides as $index => $slide):
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

                $destinationsByRegion = [];
                foreach ($destinations as $index => $destination) {
                    $region = trim((string) ($destination['region'] ?? 'Otros destinos'));
                    if ($region === '') {
                        $region = 'Otros destinos';
                    }

                    $destinationName = trim((string) ($destination['nombre'] ?? ''));
                    if ($destinationName === '') {
                        continue;
                    }

                    $imagePath = null;
                    $imageFile = trim((string) ($destination['imagen'] ?? ''));
                    if ($imageFile !== '' && is_file(__DIR__ . '/../../web/assets/' . $imageFile)) {
                        $imagePath = 'assets/' . $imageFile;
                    } elseif ($imageFile !== '' && isset($fallbackImageMap[$imageFile])) {
                        $imagePath = $fallbackImageMap[$imageFile];
                    } elseif (isset($fallbackImageMap[$destinationName])) {
                        $imagePath = $fallbackImageMap[$destinationName];
                    }

                    $stats = $destinationStatsPresets[$index % count($destinationStatsPresets)] ?? [
                        'tours' => $index + 1,
                        'departures' => 20 + ($index * 2),
                        'guests' => 9_200 + ($index * 480),
                    ];

                    $toursCount = max(1, (int) ($packagesByDestination[$destinationName] ?? $stats['tours']));
                    $formattedTours = str_pad((string) $toursCount, 2, '0', STR_PAD_LEFT);
                    $formattedDepartures = str_pad((string) ($stats['departures'] ?? 0), 2, '0', STR_PAD_LEFT);
                    $formattedGuests = number_format((int) ($stats['guests'] ?? 0));

                    $destinationsByRegion[$region][] = [
                        'title' => $destinationName,
                        'meta' => sprintf('%s tours | %s salidas · %s viajeros.', $formattedTours, $formattedDepartures, $formattedGuests),
                        'img' => $imagePath,
                    ];
                }

                $destinationsPayload = [];
                foreach ($destinationsByRegion as $region => $items) {
                    $destinationsPayload[$region] = array_values(array_map(
                        static function (array $item): array {
                            return [
                                'title' => (string) $item['title'],
                                'meta' => (string) $item['meta'],
                                'img' => $item['img'] ? (string) $item['img'] : null,
                            ];
                        },
                        $items
                    ));
                }

                $regionNames = array_keys($destinationsPayload);
                $activeRegion = $regionNames[0] ?? null;
            ?>
            <div class="destinations-showcase__container">
                <h1>Featured Destinations</h1>
                <div class="tabs" id="destination-tabs">
                    <?php foreach ($regionNames as $index => $region): ?>
                        <button type="button" class="tab<?= $index === 0 ? ' active' : ''; ?>" data-region="<?= htmlspecialchars($region); ?>">
                            <?= htmlspecialchars($region); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <section class="cards" id="destination-cards" aria-live="polite">
                    <?php if ($activeRegion): ?>
                        <?php foreach ($destinationsPayload[$activeRegion] as $item): ?>
                            <article class="card" role="article">
                                <?php if (!empty($item['img'])): ?>
                                    <img class="media" src="<?= htmlspecialchars($item['img']); ?>" alt="<?= htmlspecialchars($item['title']); ?>" />
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
                                    <?php if (!empty($item['meta'])): ?>
                                        <div class="meta"><?= htmlspecialchars($item['meta']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="destinations-showcase__empty">Pronto compartiremos nuevos destinos destacados.</p>
                    <?php endif; ?>
                </section>
                <div class="dots" id="destination-dots" aria-hidden="true">
                    <?php if ($activeRegion): ?>
                        <?php foreach ($destinationsPayload[$activeRegion] as $index => $_): ?>
                            <span class="dot<?= $index === 0 ? ' active' : ''; ?>"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="paquetes" class="section section--packages">
            <div class="section__header">
                <h2>Paquetes destacados</h2>
                <p>Itinerarios listos para desconectar, descubrir la biodiversidad y compartir con comunidades locales.</p>
            </div>
            <div class="cards-grid">
                <?php foreach ($featuredPackages as $package): ?>
                    <?php
                        $originalPrice = $package['precio'] * 1.18;
                        $statusLabel = $package['precio'] <= 110 ? 'Oferta especial' : 'Salida garantizada';
                        $tagLabel = $package['destino'] . ' • ' . $package['region'];
                        $imagePath = null;
                        if (!empty($package['imagen']) && is_file(__DIR__ . '/../../web/assets/' . $package['imagen'])) {
                            $imagePath = 'assets/' . $package['imagen'];
                        }
                    ?>
                    <article class="travel-card" data-theme="package">
                        <div class="travel-card__media"<?= $imagePath ? " style=\"background-image: url('" . htmlspecialchars($imagePath) . "');\"" : ''; ?> aria-hidden="true"></div>
                        <div class="travel-card__pill-group">
                            <span class="travel-card__status"><?= htmlspecialchars($statusLabel); ?></span>
                            <span class="travel-card__tag"><?= htmlspecialchars($tagLabel); ?></span>
                        </div>
                        <div class="travel-card__content">
                            <header class="travel-card__header">
                                <span class="travel-card__category">Tour <?= htmlspecialchars($package['destino']); ?></span>
                                <span class="travel-card__badge">Circuito destacado</span>
                            </header>
                            <h3 class="travel-card__title"><?= htmlspecialchars($package['nombre']); ?></h3>
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
                                <span class="travel-card__price">S/ <?= number_format((float) $package['precio'], 2); ?></span>
                                <span class="travel-card__price-original">S/ <?= number_format((float) $originalPrice, 2); ?></span>
                                <span class="travel-card__price-note">por persona</span>
                            </div>
                            <a class="travel-card__cta" href="paquete.php?id=<?= urlencode((string) $package['id']); ?>">RESERVAR</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section--metrics">
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
        </section>

        <section class="section section--experiences" id="experiencias">
            <div class="section__header">
                <h2>Circuitos exclusivos</h2>
                <p>Aventuras que combinan cultura viva, sostenibilidad y confort en cada detalle.</p>
            </div>
            <div class="cards-grid cards-grid--experiences">
                <?php foreach ($signatureExperiences as $experience): ?>
                    <?php
                        $experiencePrice = $experience['precio'] ?? 0;
                        $experienceOriginal = $experiencePrice ? $experiencePrice * 1.12 : null;
                    ?>
                    <article class="travel-card" data-theme="experience">
                        <div class="travel-card__media" aria-hidden="true"></div>
                        <div class="travel-card__pill-group">
                            <span class="travel-card__status">Nuevo recorrido</span>
                            <span class="travel-card__tag"><?= htmlspecialchars($experience['destino']); ?></span>
                        </div>
                        <div class="travel-card__content">
                            <header class="travel-card__header">
                                <span class="travel-card__category">Experiencia guiada</span>
                                <span class="travel-card__badge">Circuito exclusivo</span>
                            </header>
                            <h3 class="travel-card__title"><?= htmlspecialchars($experience['nombre']); ?></h3>
                            <p class="travel-card__excerpt"><?= htmlspecialchars($experience['resumen']); ?></p>
                            <dl class="travel-card__meta">
                                <div>
                                    <dt>Duración</dt>
                                    <dd><?= htmlspecialchars($experience['duracion']); ?></dd>
                                </div>
                                <div>
                                    <dt>Destino</dt>
                                    <dd><?= htmlspecialchars($experience['destino']); ?></dd>
                                </div>
                                <div>
                                    <dt>Experiencia</dt>
                                    <dd>Grupos reducidos</dd>
                                </div>
                            </dl>
                        </div>
                        <footer class="travel-card__footer">
                            <div class="travel-card__pricing">
                                <?php if ($experiencePrice): ?>
                                    <span class="travel-card__price">S/ <?= number_format((float) $experiencePrice, 2); ?></span>
                                    <?php if ($experienceOriginal): ?>
                                        <span class="travel-card__price-original">S/ <?= number_format((float) $experienceOriginal, 2); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="travel-card__price">Pronto</span>
                                <?php endif; ?>
                                <span class="travel-card__price-note">por persona</span>
                            </div>
                            <a class="travel-card__cta" href="#contacto">RESERVAR</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section--pillars">
            <div class="pillars">
                <?php foreach ($travelPillars as $pillar): ?>
                    <article class="pillar">
                        <h3><?= htmlspecialchars($pillar['title']); ?></h3>
                        <p><?= htmlspecialchars($pillar['copy']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section--testimonials">
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
        </section>

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
            <div class="contact-cards">
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
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="site-footer__brand"><?= htmlspecialchars($siteTitle); ?></div>
        <div class="site-footer__links">
            <div>
                <h4>Explora</h4>
                <ul>
                    <li><a href="#paquetes">Paquetes</a></li>
                    <li><a href="#destinos">Destinos</a></li>
                    <li><a href="explorar.php">Experiencias</a></li>
                </ul>
            </div>
            <div>
                <h4>Nosotros</h4>
                <ul>
                    <li><a href="#">Quiénes somos</a></li>
                    <li><a href="#">Trabaja con nosotros</a></li>
                    <li><a href="#">Prensa</a></li>
                </ul>
            </div>
            <div>
                <h4>Ayuda</h4>
                <ul>
                    <li><a href="#">Centro de soporte</a></li>
                    <li><a href="#">Políticas de viaje</a></li>
                    <li>
                        <?php if (!empty($primaryEmail)): ?>
                            <a href="mailto:<?= htmlspecialchars($primaryEmail, ENT_QUOTES); ?>">Contacto</a>
                        <?php else: ?>
                            <a href="#contacto">Contacto</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
        <p class="site-footer__legal">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?>. Todos los derechos reservados.</p>
    </footer>

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

            if (toggle && nav) {
                toggle.addEventListener('click', () => {
                    const isOpen = header.classList.toggle('site-header--open');
                    toggle.setAttribute('aria-expanded', String(isOpen));
                });

                nav.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLElement && event.target.classList.contains('site-header__link')) {
                        header.classList.remove('site-header--open');
                        toggle.setAttribute('aria-expanded', 'false');
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

            const destinationsData = <?= json_encode((object) $destinationsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            const destinationRegions = Object.keys(destinationsData);
            const tabsEl = document.getElementById('destination-tabs');
            const cardsEl = document.getElementById('destination-cards');
            const dotsEl = document.getElementById('destination-dots');

            if (destinationRegions.length && tabsEl && cardsEl && dotsEl) {
                const iconPin = () => '<svg class="pin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22s7-6.1 7-12a7 7 0 10-14 0c0 5.9 7 12 7 12z" stroke="currentColor" stroke-width="1.5" /><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5" /></svg>';
                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                })[character] ?? character);

                const renderTabs = (activeRegion) => {
                    tabsEl.innerHTML = '';
                    destinationRegions.forEach((region) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'tab' + (region === activeRegion ? ' active' : '');
                        button.textContent = region;
                        button.dataset.region = region;
                        button.addEventListener('click', () => selectRegion(region));
                        tabsEl.appendChild(button);
                    });
                };

                const renderDots = (count) => {
                    dotsEl.innerHTML = Array.from({ length: count }, (_, index) => `<span class="dot ${index === 0 ? 'active' : ''}"></span>`).join('');
                };

                const renderCards = (region) => {
                    const items = destinationsData[region] || [];
                    cardsEl.innerHTML = items.map((item) => {
                        const title = escapeHtml(item.title);
                        const imageMarkup = item.img ? `<img class="media" src="${escapeHtml(item.img)}" alt="${title}" />` : '';
                        const metaMarkup = item.meta ? `<div class="meta">${escapeHtml(item.meta)}</div>` : '';
                        return `
                            <article class="card" role="article">
                                ${imageMarkup}
                                <div class="body">
                                    <div class="title"><span class="row">${iconPin()}</span> ${title}</div>
                                    ${metaMarkup}
                                </div>
                            </article>
                        `;
                    }).join('');

                    renderDots(items.length);
                };

                const selectRegion = (region) => {
                    renderTabs(region);
                    renderCards(region);

                    const firstDot = dotsEl.querySelector('.dot');
                    if (firstDot) {
                        firstDot.setAttribute('aria-current', 'true');
                    }
                };

                selectRegion(destinationRegions[0]);
            }
        });
    </script>
</body>
</html>
