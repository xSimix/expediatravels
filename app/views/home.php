<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Expediatravels'); ?></title>
    <link rel="stylesheet" href="/css/app.css" />
</head>
<body class="page">
    <header class="hero">
        <div class="hero__content">
            <div class="hero__badge">Selva Central, Perú</div>
            <h1 class="hero__title">Fly First. Conecta. <span>Respira</span> Oxapampa.</h1>
            <p class="hero__subtitle">Diseñamos viajes inmersivos que combinan aventura, cultura viva y hospitalidad local en Oxapampa, Villa Rica, Pozuzo, Perené y Yanachaga.</p>
            <div class="hero__actions">
                <a class="button button--primary" href="#paquetes">Explorar paquetes</a>
                <a class="button button--ghost" href="#destinos">Ver destinos</a>
            </div>
            <ul class="hero__selling-points">
                <?php foreach ($sellingPoints as $point): ?>
                    <li class="selling-point">
                        <span class="selling-point__icon" aria-hidden="true"><?= htmlspecialchars($point['icon']); ?></span>
                        <div>
                            <h3><?= htmlspecialchars($point['title']); ?></h3>
                            <p><?= htmlspecialchars($point['description']); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="hero__media" aria-hidden="true">
            <div class="hero__card">
                <span>Reserva confirmada</span>
                <strong>Tour <?= htmlspecialchars($featuredPackages[0]['destino'] ?? 'Oxapampa'); ?></strong>
                <p>Salida este fin de semana • 6 viajeros</p>
            </div>
            <div class="hero__floating-tag">+24 experiencias auténticas</div>
        </div>
    </header>

    <main>
        <section id="paquetes" class="section section--packages">
            <div class="section__header">
                <h2>Paquetes destacados</h2>
                <p>Itinerarios listos para desconectar, descubrir la biodiversidad y compartir con comunidades locales.</p>
            </div>
            <div class="cards-grid">
                <?php foreach ($featuredPackages as $package): ?>
                    <article class="card card--package">
                        <div class="card__media" role="presentation"></div>
                        <div class="card__content">
                            <div class="card__header">
                                <span class="card__tag"><?= htmlspecialchars($package['destino']); ?> • <?= htmlspecialchars($package['region']); ?></span>
                                <span class="card__duration"><?= htmlspecialchars($package['duracion']); ?></span>
                            </div>
                            <h3><?= htmlspecialchars($package['nombre']); ?></h3>
                            <p><?= htmlspecialchars($package['resumen']); ?></p>
                            <div class="card__footer">
                                <span class="card__price">S/ <?= number_format((float) $package['precio'], 2); ?></span>
                                <a class="card__link" href="/paquete.php?id=<?= urlencode((string) $package['id']); ?>">Ver detalles</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section--destinations" id="destinos">
            <div class="section__header">
                <h2>Tesoros de la Reserva de Biosfera</h2>
                <p>Desde bosques nubosos hasta cataratas turquesa, seleccionamos los destinos imperdibles de la ruta Oxapampa.</p>
            </div>
            <div class="destinations">
                <?php foreach ($destinations as $destination): ?>
                    <article class="destination">
                        <div class="destination__badge"><?= htmlspecialchars($destination['region']); ?></div>
                        <h3><?= htmlspecialchars($destination['nombre']); ?></h3>
                        <p><?= htmlspecialchars($destination['descripcion']); ?></p>
                        <a class="destination__link" href="/explorar.php?destino=<?= urlencode((string) $destination['id']); ?>">Explorar</a>
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

        <section class="section section--experiences">
            <div class="section__header">
                <h2>Signature Experiences</h2>
                <p>Aventuras que combinan cultura viva, sostenibilidad y confort en cada detalle.</p>
            </div>
            <div class="experience-grid">
                <?php foreach ($signatureExperiences as $experience): ?>
                    <article class="experience">
                        <h3><?= htmlspecialchars($experience['nombre']); ?></h3>
                        <p><?= htmlspecialchars($experience['resumen']); ?></p>
                        <footer>
                            <span><?= htmlspecialchars($experience['destino']); ?></span>
                            <span><?= htmlspecialchars($experience['duracion']); ?></span>
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

        <section class="section section--cta">
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
                    <li><a href="/explorar.php">Experiencias</a></li>
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
</body>
</html>
