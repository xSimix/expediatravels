<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteLogo = $siteSettings['siteLogo'] ?? null;
$footerDescription = $siteSettings['footerDescription'] ?? null;
$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$contactPhones = $contact['phones'] ?? [];
$contactAddresses = $contact['addresses'] ?? [];
$contactLocations = $contact['locations'] ?? [];
$socialLinks = $contact['social'] ?? [];

$defaultDescription = 'Expediatravels es tu agencia local para explorar la Selva Central del Perú. Descubre Oxapampa, Villa Rica y Pozuzo con experiencias únicas y atención personalizada.';
$footerDescription = is_string($footerDescription) && trim($footerDescription) !== ''
    ? trim($footerDescription)
    : $defaultDescription;

$primaryEmail = $contactEmails[0] ?? 'contacto@expediatravels.com.pe';
$primaryAddress = $contactAddresses[0] ?? ($contactLocations[0] ?? 'Jr. Los Cedros 123, Oxapampa - Perú');
$primaryPhone = $contactPhones[0] ?? '+51 984 635 885';

$formatPhoneHref = static function (?string $phone): ?string {
    if ($phone === null) {
        return null;
    }

    $sanitised = preg_replace('/[^\\d+]/', '', $phone);

    return $sanitised !== '' ? $sanitised : null;
};

$primaryPhoneHref = $formatPhoneHref($primaryPhone);

$resolveMediaPath = static function ($path) {
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

$siteLogoPath = $resolveMediaPath($siteLogo);

$defaultSocial = [
    ['label' => 'Sitio web', 'url' => '#'],
    ['label' => 'Instagram', 'url' => '#'],
    ['label' => 'YouTube', 'url' => '#'],
    ['label' => 'TikTok', 'url' => '#'],
];

$socialItems = !empty($socialLinks) ? $socialLinks : $defaultSocial;

$iconMap = [
    'facebook' => '📘',
    'instagram' => '📸',
    'youtube' => '▶️',
    'tiktok' => '🎵',
    'twitter' => '🐦',
    'x' => '🐦',
    'linkedin' => '💼',
    'whatsapp' => '💬',
    'web' => '🌐',
    'sitio web' => '🌐',
    'site' => '🌐',
];

$resolveSocialIcon = static function (array $item) use ($iconMap): string {
    $label = strtolower((string) ($item['label'] ?? ''));

    foreach ($iconMap as $key => $icon) {
        if (strpos($label, $key) !== false) {
            return $icon;
        }
    }

    return '🔗';
};
?>
<footer class="site-footer">
    <div class="site-footer__container">
        <div class="site-footer__left">
            <?php if ($siteLogoPath !== null): ?>
                <img class="site-footer__logo" src="<?= htmlspecialchars($siteLogoPath); ?>" alt="<?= htmlspecialchars($siteTitle); ?>" loading="lazy">
            <?php else: ?>
                <div class="site-footer__brand"><?= htmlspecialchars($siteTitle); ?></div>
            <?php endif; ?>

            <p class="site-footer__description"><?= htmlspecialchars($footerDescription); ?></p>

            <div class="site-footer__contact">
                <?php if (!empty($primaryEmail)): ?>
                    <div class="site-footer__contact-item">
                        <span aria-hidden="true">📧</span>
                        <a href="mailto:<?= htmlspecialchars($primaryEmail, ENT_QUOTES); ?>"><?= htmlspecialchars($primaryEmail); ?></a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($primaryAddress)): ?>
                    <div class="site-footer__contact-item">
                        <span aria-hidden="true">📍</span>
                        <span><?= htmlspecialchars($primaryAddress); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($primaryPhone)): ?>
                    <div class="site-footer__contact-item">
                        <span aria-hidden="true">📞</span>
                        <?php if ($primaryPhoneHref !== null): ?>
                            <a href="tel:<?= htmlspecialchars($primaryPhoneHref, ENT_QUOTES); ?>"><?= htmlspecialchars($primaryPhone); ?></a>
                        <?php else: ?>
                            <span><?= htmlspecialchars($primaryPhone); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($socialItems)): ?>
                <div class="site-footer__social" aria-label="Redes sociales">
                    <?php foreach ($socialItems as $social): ?>
                        <?php
                        $label = (string) ($social['label'] ?? '');
                        $url = (string) ($social['url'] ?? '#');
                        $icon = $resolveSocialIcon($social);
                        ?>
                        <a href="<?= htmlspecialchars($url, ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer">
                            <span aria-hidden="true"><?= $icon; ?></span>
                            <span class="sr-only"><?= htmlspecialchars($label); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="site-footer__links">
            <div class="site-footer__links-section">
                <h4>Explora</h4>
                <a href="index.php#destinos">Destinos</a>
                <a href="index.php#circuitos">Circuitos Turísticos</a>
                <a href="index.php#paquetes">Paquetes</a>
                <a href="index.php#paquetes">Ofertas</a>
            </div>

            <div class="site-footer__links-section">
                <h4>Nosotros</h4>
                <a href="index.php#inicio">Quiénes somos</a>
                <a href="index.php#contacto">Contáctanos</a>
                <a href="#">Trabaja con nosotros</a>
                <a href="#">Prensa y alianzas</a>
            </div>

            <div class="site-footer__links-section site-footer__newsletter">
                <h4>Suscríbete</h4>
                <p>Recibe noticias y descuentos exclusivos cada mes.</p>
                <form class="site-footer__form" onsubmit="event.preventDefault(); alert('Gracias por suscribirte 🌴');">
                    <label for="newsletter-email" class="sr-only">Correo electrónico</label>
                    <input id="newsletter-email" type="email" name="email" placeholder="Tu correo electrónico" required>
                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        © <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?> · Todos los derechos reservados · <a href="#">Política de privacidad</a>
    </div>

    <button id="creditFab" class="site-footer__credit-button" aria-label="Créditos del programador" aria-expanded="false" aria-controls="creditCard">💻</button>
    <div id="creditCard" class="site-footer__credit-card" role="dialog" aria-hidden="true">
        <div>
            <strong>tide.pe</strong><br>
            Carlos Chamorro<br>
            <a href="tel:+51941125468">📞 941 125 468</a>
        </div>
    </div>
</footer>

<script>
(function () {
    const fab = document.getElementById('creditFab');
    const card = document.getElementById('creditCard');

    if (!fab || !card) {
        return;
    }

    fab.addEventListener('click', function () {
        const isOpen = card.classList.toggle('show');
        fab.setAttribute('aria-expanded', isOpen);
        card.setAttribute('aria-hidden', (!isOpen).toString());
    });
})();
</script>
