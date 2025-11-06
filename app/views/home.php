<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Expediatravels'); ?></title>
    <link rel="stylesheet" href="css/app.css" />
</head>
<body class="page">
    <header class="site-header" data-site-header>
        <div class="site-header__inner">
            <a class="site-header__brand" href="#inicio">
                <span class="site-header__logo" aria-hidden="true">🧭</span>
                <span class="site-header__brand-text">
                    <strong>Expediatravels</strong>
                    <small>Travel Dev</small>
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
                <div class="site-header__contact">
                    <span class="site-header__contact-label">Hablemos</span>
                    <a class="site-header__contact-phone" href="tel:+51984635885">+51 984 635 885</a>
                </div>
                <a class="button button--primary site-header__cta-button" href="admin/index.php">Login</a>
            </div>
        </div>
    </header>

    <section class="hero" id="inicio">
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
        </div>
    </section>

    <main>
        <section class="featured-destinations" id="destinos">
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

                $destinationsByRegion = [];
                foreach ($destinations as $index => $destination) {
                    $region = trim((string) ($destination['region'] ?? 'Otros destinos'));
                    $destinationName = trim((string) ($destination['nombre'] ?? ''));
                    $description = trim((string) ($destination['descripcion'] ?? ''));

                    $imagePath = null;
                    if (!empty($destination['imagen']) && is_file(__DIR__ . '/../../web/assets/' . $destination['imagen'])) {
                        $imagePath = 'assets/' . $destination['imagen'];
                    }

                    $stats = $destinationStatsPresets[$index] ?? [
                        'tours' => $index + 1,
                        'departures' => 20 + ($index * 2),
                        'guests' => 9_200 + ($index * 480),
                    ];

                    $toursCount = (int) ($packagesByDestination[$destinationName] ?? $stats['tours']);
                    $formattedTours = str_pad((string) $toursCount, 2, '0', STR_PAD_LEFT);
                    $formattedDepartures = str_pad((string) $stats['departures'], 2, '0', STR_PAD_LEFT);
                    $formattedGuests = number_format((int) $stats['guests']);

                    $destinationsByRegion[$region][] = [
                        'title' => $destinationName,
                        'description' => $description,
                        'meta' => sprintf('%s tours | %s salidas · %s viajeros.', $formattedTours, $formattedDepartures, $formattedGuests),
                        'image' => $imagePath,
                        'initial' => mb_strtoupper(mb_substr($destinationName, 0, 1)),
                    ];
                }

                $regionNames = array_keys($destinationsByRegion);
                $activeRegion = $regionNames[0] ?? null;
                $destinationsPayload = [];

                foreach ($destinationsByRegion as $region => $items) {
                    $destinationsPayload[$region] = array_values(array_map(
                        static function (array $item): array {
                            return [
                                'title' => (string) $item['title'],
                                'description' => (string) $item['description'],
                                'meta' => (string) $item['meta'],
                                'image' => $item['image'] ? (string) $item['image'] : null,
                                'initial' => (string) $item['initial'],
                            ];
                        },
                        $items
                    ));
                }
            ?>
            <div class="featured-destinations__container">
                <header class="featured-destinations__intro">
                    <p class="featured-destinations__eyebrow">Featured Destinations</p>
                    <h2 class="featured-destinations__title">Vive la magia de la Selva Central</h2>
                    <p class="featured-destinations__copy">Inspiración curada por nuestro equipo local con los paisajes, culturas y aventuras que definen Oxapampa y sus alrededores.</p>
                </header>
                <?php if ($regionNames): ?>
                    <div class="featured-destinations__tabs" data-destination-tabs role="tablist" aria-label="Regiones destacadas">
                        <?php foreach ($regionNames as $index => $region): ?>
                            <button type="button" class="featured-destinations__tab<?= $index === 0 ? ' is-active' : ''; ?>" data-region="<?= htmlspecialchars($region); ?>" role="tab" aria-selected="<?= $index === 0 ? 'true' : 'false'; ?>">
                                <?= htmlspecialchars($region); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <section class="featured-destinations__cards" data-destination-cards aria-live="polite">
                    <?php if ($activeRegion): ?>
                        <?php foreach ($destinationsByRegion[$activeRegion] as $index => $item): ?>
                            <article class="destination-card<?= $index === 0 ? ' is-active' : ''; ?>" data-index="<?= $index; ?>" tabindex="0" role="article">
                                <?php if (!empty($item['image'])): ?>
                                    <img class="destination-card__media" src="<?= htmlspecialchars((string) $item['image']); ?>" alt="<?= htmlspecialchars((string) $item['title']); ?>" loading="lazy" />
                                <?php else: ?>
                                    <div class="destination-card__placeholder" aria-hidden="true">
                                        <?= htmlspecialchars((string) $item['initial']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="destination-card__body">
                                    <div class="destination-card__title">
                                        <span class="destination-card__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z" stroke="currentColor" stroke-width="1.5"></path>
                                                <circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"></circle>
                                            </svg>
                                        </span>
                                        <span><?= htmlspecialchars((string) $item['title']); ?></span>
                                    </div>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="destination-card__description"><?= htmlspecialchars((string) $item['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['meta'])): ?>
                                        <p class="destination-card__meta"><?= htmlspecialchars((string) $item['meta']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="featured-destinations__empty">Pronto compartiremos nuevos destinos destacados.</p>
                    <?php endif; ?>
                </section>
                <?php if ($activeRegion): ?>
                    <div class="featured-destinations__dots" data-destination-dots aria-hidden="true">
                        <?php foreach ($destinationsByRegion[$activeRegion] as $index => $_): ?>
                            <span class="featured-destinations__dot<?= $index === 0 ? ' is-active' : ''; ?>"></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                <a class="button button--primary" href="mailto:hola@expediatravels.pe">Agendar asesoría</a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="site-footer__brand">Expediatravels</div>
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
                    <li><a href="mailto:hola@expediatravels.pe">Contacto</a></li>
                </ul>
            </div>
        </div>
        <p class="site-footer__legal">© <?= date('Y'); ?> Expediatravels. Todos los derechos reservados.</p>
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

            const destinationsData = <?= json_encode((object) $destinationsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            const destinationRegions = Object.keys(destinationsData);
            const tabsEl = document.querySelector('[data-destination-tabs]');
            const cardsEl = document.querySelector('[data-destination-cards]');
            const dotsEl = document.querySelector('[data-destination-dots]');

            if (destinationRegions.length && tabsEl && cardsEl && dotsEl) {
                const pinIcon = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z" stroke="currentColor" stroke-width="1.5"></path><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"></circle></svg>';
                let activeRegion = destinationRegions[0];

                const setActiveCard = (index) => {
                    const cards = cardsEl.querySelectorAll('.destination-card');
                    const dots = dotsEl.querySelectorAll('.featured-destinations__dot');

                    if (!cards.length) {
                        return;
                    }

                    cards.forEach((card, cardIndex) => {
                        card.classList.toggle('is-active', cardIndex === index);
                    });

                    dots.forEach((dot, dotIndex) => {
                        const isActive = dotIndex === index;
                        dot.classList.toggle('is-active', isActive);
                        if (isActive) {
                            dot.setAttribute('aria-current', 'true');
                        } else {
                            dot.removeAttribute('aria-current');
                        }
                    });
                };

                const renderCards = (region) => {
                    const items = destinationsData[region] || [];
                    cardsEl.innerHTML = '';
                    dotsEl.innerHTML = '';
                    cardsEl.setAttribute('data-region', region);

                    items.forEach((item, index) => {
                        const article = document.createElement('article');
                        article.className = 'destination-card';
                        article.setAttribute('role', 'article');
                        article.tabIndex = 0;

                        if (item.image) {
                            const img = document.createElement('img');
                            img.className = 'destination-card__media';
                            img.src = item.image;
                            img.alt = item.title;
                            img.loading = 'lazy';
                            article.appendChild(img);
                        } else {
                            const placeholder = document.createElement('div');
                            placeholder.className = 'destination-card__placeholder';
                            placeholder.setAttribute('aria-hidden', 'true');
                            const initial = item.initial && item.initial.length ? item.initial : (item.title ? item.title.charAt(0) : '•');
                            placeholder.textContent = initial;
                            article.appendChild(placeholder);
                        }

                        const body = document.createElement('div');
                        body.className = 'destination-card__body';

                        const titleRow = document.createElement('div');
                        titleRow.className = 'destination-card__title';

                        const iconWrapper = document.createElement('span');
                        iconWrapper.className = 'destination-card__icon';
                        iconWrapper.setAttribute('aria-hidden', 'true');
                        iconWrapper.innerHTML = pinIcon;

                        const titleText = document.createElement('span');
                        titleText.textContent = item.title;

                        titleRow.appendChild(iconWrapper);
                        titleRow.appendChild(titleText);
                        body.appendChild(titleRow);

                        if (item.description) {
                            const description = document.createElement('p');
                            description.className = 'destination-card__description';
                            description.textContent = item.description;
                            body.appendChild(description);
                        }

                        if (item.meta) {
                            const meta = document.createElement('p');
                            meta.className = 'destination-card__meta';
                            meta.textContent = item.meta;
                            body.appendChild(meta);
                        }

                        article.appendChild(body);

                        article.addEventListener('mouseenter', () => setActiveCard(index));
                        article.addEventListener('focus', () => setActiveCard(index));
                        article.addEventListener('click', () => setActiveCard(index));
                        article.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                setActiveCard(index);
                            }
                        });

                        cardsEl.appendChild(article);

                        const dot = document.createElement('span');
                        dot.className = 'featured-destinations__dot';
                        dotsEl.appendChild(dot);
                    });

                    if (items.length) {
                        setActiveCard(0);
                    }
                };

                const renderTabs = (selectedRegion) => {
                    const tabs = tabsEl.querySelectorAll('.featured-destinations__tab');
                    tabs.forEach((tab) => {
                        const tabRegion = tab.getAttribute('data-region');
                        const isActive = tabRegion === selectedRegion;
                        tab.classList.toggle('is-active', isActive);
                        tab.setAttribute('aria-selected', String(isActive));
                    });
                };

                const selectRegion = (region) => {
                    activeRegion = region;
                    renderTabs(region);
                    renderCards(region);
                };

                tabsEl.querySelectorAll('.featured-destinations__tab').forEach((tab) => {
                    const activate = () => {
                        const region = tab.getAttribute('data-region');
                        if (region) {
                            selectRegion(region);
                        }
                    };

                    tab.addEventListener('click', activate);
                    tab.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            activate();
                        }
                    });
                });

                selectRegion(activeRegion);
            }
        });
    </script>
</body>
</html>
