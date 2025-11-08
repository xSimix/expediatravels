<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteTagline = trim((string) ($siteSettings['siteTagline'] ?? 'Explora la Selva Central'));
$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$contactPhones = $contact['phones'] ?? [];
$contactAddresses = $contact['addresses'] ?? [];
$contactLocations = $contact['locations'] ?? [];
$primaryEmail = $contactEmails[0] ?? null;
$primaryPhone = $contactPhones[0] ?? null;
$primaryAddress = $contactAddresses[0] ?? null;
$primaryLocation = $contactLocations[0] ?? null;

$formatPhoneHref = static function (?string $phone): ?string {
    if ($phone === null) {
        return null;
    }

    $sanitised = preg_replace('/[^\\d+]/', '', $phone);

    return $sanitised !== '' ? 'tel:' . $sanitised : null;
};

$phoneHref = $formatPhoneHref($primaryPhone);
$locationLines = array_values(array_filter([
    trim((string) $primaryAddress),
    trim((string) $primaryLocation),
]));
?>
<footer class="site-footer">
    <div class="site-footer__content">
        <section class="site-footer__about">
            <div class="site-footer__logo">
                <span class="site-footer__logo-mark" aria-hidden="true">🧭</span>
                <div class="site-footer__logo-text">
                    <span class="site-footer__logo-name"><?= htmlspecialchars($siteTitle); ?></span>
                    <?php if ($siteTagline !== ''): ?>
                        <span class="site-footer__logo-tagline"><?= htmlspecialchars($siteTagline); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <p class="site-footer__description">Expediatravels es tu agencia local para explorar la Selva Central del Perú. Descubre Oxapampa, Villa Rica y Pozuzo con experiencias únicas y atención personalizada.</p>
            <ul class="site-footer__contact-list">
                <?php if (!empty($primaryEmail)): ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4.75 5.5h14.5A1.75 1.75 0 0 1 21 7.25v9.5A1.75 1.75 0 0 1 19.25 18.5H4.75A1.75 1.75 0 0 1 3 16.75v-9.5A1.75 1.75 0 0 1 4.75 5.5zm.58 1.5 6.67 5.19a.75.75 0 0 0 .93 0l6.69-5.2H5.33z" fill="currentColor" />
                            </svg>
                        </span>
                        <div>
                            <a class="site-footer__contact-link" href="mailto:<?= htmlspecialchars($primaryEmail, ENT_QUOTES); ?>"><?= htmlspecialchars($primaryEmail); ?></a>
                            <span class="site-footer__contact-label">Escríbenos para planificar tu viaje</span>
                        </div>
                    </li>
                <?php endif; ?>
                <?php if (!empty($locationLines)): ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M12 2.75a7.25 7.25 0 0 1 7.25 7.25c0 4.53-4.67 9.7-6.52 11.47a1.07 1.07 0 0 1-1.46 0C9.42 19.7 4.75 14.53 4.75 10A7.25 7.25 0 0 1 12 2.75zm0 4.5A2.75 2.75 0 1 0 14.75 10 2.75 2.75 0 0 0 12 7.25z" fill="currentColor" />
                            </svg>
                        </span>
                        <div>
                            <?php foreach ($locationLines as $index => $line): ?>
                                <span class="site-footer__contact-text"><?= htmlspecialchars($line); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </li>
                <?php endif; ?>
                <?php if (!empty($primaryPhone)): ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M8.1 3.52c.58-.48 1.4-.54 2.04-.15l.23.15 2.05 1.54a1.75 1.75 0 0 1 .67 1.75l-.07.24-.68 2.04a.75.75 0 0 0 .27.82l.1.07 3.27 2.45a.75.75 0 0 0 .8.05l.11-.07 1.85-1.39a1.75 1.75 0 0 1 1.99-.05l.21.16 2.05 1.54c.7.53.92 1.47.52 2.23-1.63 3.04-4.07 5.54-7.15 7.46l-.3.18c-.9.53-2.05.42-2.83-.26l-.18-.18-1.53-1.83-2.02-2.7-2.18-3.66-1.6-3.6-.98-3.72c-.2-.78.11-1.59.73-2.07z" fill="currentColor" />
                            </svg>
                        </span>
                        <div>
                            <?php if ($phoneHref !== null): ?>
                                <a class="site-footer__contact-link" href="<?= htmlspecialchars($phoneHref, ENT_QUOTES); ?>"><?= htmlspecialchars($primaryPhone); ?></a>
                            <?php else: ?>
                                <span class="site-footer__contact-text"><?= htmlspecialchars($primaryPhone); ?></span>
                            <?php endif; ?>
                            <span class="site-footer__contact-label">Atención personalizada</span>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </section>
        <nav class="site-footer__nav" aria-label="Explora">
            <h4>Explora</h4>
            <ul class="site-footer__nav-list">
                <li><a href="index.php#paquetes">Paquetes</a></li>
                <li><a href="index.php#destinos">Destinos</a></li>
                <li><a href="explorar.php">Experiencias</a></li>
            </ul>
        </nav>
        <nav class="site-footer__nav" aria-label="Nosotros">
            <h4>Nosotros</h4>
            <ul class="site-footer__nav-list">
                <li><a href="index.php#inicio">Quiénes somos</a></li>
                <li><a href="#">Trabaja con nosotros</a></li>
                <li><a href="#">Prensa</a></li>
            </ul>
        </nav>
        <section class="site-footer__subscribe">
            <h4>Suscríbete</h4>
            <p>Recibe noticias y descuentos exclusivos cada mes.</p>
            <form class="site-footer__subscribe-form" action="#" method="post">
                <label class="sr-only" for="footer-subscribe-email">Tu correo electrónico</label>
                <div class="site-footer__subscribe-field">
                    <input type="email" id="footer-subscribe-email" name="email" placeholder="Tu correo electrónico" required />
                    <button type="submit">Enviar</button>
                </div>
            </form>
        </section>
    </div>
    <div class="site-footer__bottom">
        <p class="site-footer__legal">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?> — Todos los derechos reservados.</p>
        <a class="site-footer__privacy" href="#">Política de privacidad</a>
    </div>
</footer>
