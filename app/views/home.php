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
        <section class="section section--destinations" id="destinos">
            <?php
                $destinationRegions = array_values(array_filter(array_unique(array_map(
                    static fn (array $destination): string => (string) ($destination['region'] ?? ''),
                    $destinations
                ))));

                $destinationStatsPresets = [
                    ['tours' => 1, 'departures' => 32, 'guests' => 12774],
                    ['tours' => 2, 'departures' => 26, 'guests' => 11892],
                    ['tours' => 3, 'departures' => 18, 'guests' => 10115],
                    ['tours' => 4, 'departures' => 22, 'guests' => 9420],
                ];

                $packagesByDestination = [];
                foreach ($featuredPackages as $package) {
                    $destinationKey = $package['destino'] ?? null;
                    if ($destinationKey) {
                        $packagesByDestination[$destinationKey] = ($packagesByDestination[$destinationKey] ?? 0) + 1;
                    }
                }
            ?>
            <div class="section__header section__header--centered">
                <span class="section__eyebrow">Featured Destinations</span>
                <h2>Vive la magia de la Selva Central</h2>
                <p>Inspiración curada por nuestro equipo local con los paisajes, culturas y aventuras que definen Oxapampa y sus alrededores.</p>
            </div>
            <div class="destinations-shell">
                <?php if ($destinationRegions): ?>
                    <div class="destinations-toolbar" role="tablist" aria-label="Regiones destacadas">
                        <?php foreach ($destinationRegions as $index => $region): ?>
                            <button type="button" class="destinations-tab<?= $index === 0 ? ' is-active' : ''; ?>" data-region="<?= htmlspecialchars($region); ?>" role="tab" aria-selected="<?= $index === 0 ? 'true' : 'false'; ?>">
                                <?= htmlspecialchars($region); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="destinations-carousel" role="list">
                    <?php foreach ($destinations as $index => $destination): ?>
                        <?php
                            $imagePath = null;
                            if (!empty($destination['imagen']) && is_file(__DIR__ . '/../../web/assets/' . $destination['imagen'])) {
                                $imagePath = 'assets/' . $destination['imagen'];
                            }
                            $destinationName = $destination['nombre'] ?? '';
                            $stats = $destinationStatsPresets[$index] ?? [
                                'tours' => $index + 1,
                                'departures' => 20 + ($index * 2),
                                'guests' => 9200 + ($index * 480),
                            ];
                            $formattedTours = str_pad((string) ($packagesByDestination[$destinationName] ?? $stats['tours']), 2, '0', STR_PAD_LEFT);
                            $formattedDepartures = str_pad((string) $stats['departures'], 2, '0', STR_PAD_LEFT);
                            $formattedGuests = number_format((int) $stats['guests']);
                        ?>
                        <article class="destination-card<?= $index === 0 ? ' is-active' : ''; ?>" data-region="<?= htmlspecialchars($destination['region']); ?>" role="listitem" tabindex="0">
                            <figure class="destination-card__media" aria-hidden="true">
                                <?php if ($imagePath): ?>
                                    <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($destinationName); ?>" loading="lazy" />
                                <?php elseif ($destinationName): ?>
                                    <span class="destination-card__initial" aria-hidden="true"><?= htmlspecialchars(mb_substr($destinationName, 0, 1)); ?></span>
                                <?php endif; ?>
                            </figure>
                            <div class="destination-card__content">
                                <span class="destination-card__badge"><?= htmlspecialchars($destination['region']); ?></span>
                                <h3 class="destination-card__title"><?= htmlspecialchars($destinationName); ?></h3>
                                <span class="destination-card__location"><?= htmlspecialchars($destinationName); ?></span>
                                <p class="destination-card__details"><?= htmlspecialchars($destination['descripcion']); ?></p>
                                <div class="destination-card__stats" aria-hidden="true">
                                    <span class="destination-card__stat"><strong><?= htmlspecialchars($formattedTours); ?></strong><small>Tours</small></span>
                                    <span class="destination-card__stat"><strong><?= htmlspecialchars($formattedDepartures); ?></strong><small>Departures</small></span>
                                    <span class="destination-card__stat"><strong><?= htmlspecialchars($formattedGuests); ?></strong><small>Guests travelled</small></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="destinations-indicators" aria-hidden="true">
                    <?php foreach ($destinations as $index => $_): ?>
                        <span class="destinations-dot<?= $index === 0 ? ' is-active' : ''; ?>"></span>
                    <?php endforeach; ?>
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
            const destinationCards = Array.from(document.querySelectorAll('.destination-card'));
            const destinationDots = Array.from(document.querySelectorAll('.destinations-dot'));
            const destinationTabs = Array.from(document.querySelectorAll('.destinations-tab'));

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

            const setActiveDestination = (targetIndex) => {
                if (!destinationCards.length) {
                    return;
                }

                destinationCards.forEach((card, index) => {
                    const isActive = index === targetIndex;
                    card.classList.toggle('is-active', isActive);
                });

                destinationDots.forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === targetIndex);
                });

                const activeCard = destinationCards[targetIndex];
                const activeRegion = activeCard ? activeCard.getAttribute('data-region') : null;

                if (activeRegion) {
                    destinationTabs.forEach((tab) => {
                        const tabRegion = tab.getAttribute('data-region') || '';
                        const isMatch = tabRegion === activeRegion;
                        tab.classList.toggle('is-active', isMatch);
                        tab.setAttribute('aria-selected', String(isMatch));
                    });
                }
            };

            destinationCards.forEach((card, index) => {
                const activate = () => setActiveDestination(index);
                card.addEventListener('mouseenter', activate);
                card.addEventListener('focus', activate);
                card.addEventListener('click', activate);
            });

            destinationTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const tabRegion = tab.getAttribute('data-region') || '';
                    const matchIndex = destinationCards.findIndex((card) => card.getAttribute('data-region') === tabRegion);
                    if (matchIndex >= 0) {
                        setActiveDestination(matchIndex);
                    }
                });
            });

            if (destinationCards.length) {
                const defaultActiveIndex = destinationCards.findIndex((card) => card.classList.contains('is-active'));
                setActiveDestination(defaultActiveIndex >= 0 ? defaultActiveIndex : 0);
            }
        });
    </script>
</body>
</html>
