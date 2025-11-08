<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteTagline = $siteSettings['siteTagline'] ?? null;
$siteLogo = $siteSettings['siteLogo'] ?? null;

if (!is_string($siteLogo) || trim($siteLogo) === '') {
    $siteLogo = null;
}

$contact = $siteSettings['contact'] ?? [];

$normaliseList = static function ($items): array {
    if (!is_array($items)) {
        return [];
    }

    $items = array_map(static fn($value): string => trim((string) $value), $items);

    return array_values(array_filter($items, static fn(string $value): bool => $value !== ''));
};

$contactEmails = $normaliseList($contact['emails'] ?? []);
$contactPhones = $normaliseList($contact['phones'] ?? []);
$contactAddresses = $normaliseList($contact['addresses'] ?? []);
$contactLocations = $normaliseList($contact['locations'] ?? []);

$socialLinks = [];

if (!empty($contact['social']) && is_array($contact['social'])) {
    foreach ($contact['social'] as $social) {
        if (!is_array($social)) {
            continue;
        }

        $label = trim((string) ($social['label'] ?? ''));
        $url = trim((string) ($social['url'] ?? ''));

        if ($label === '' || $url === '') {
            continue;
        }

        $socialLinks[] = [
            'label' => $label,
            'url' => $url,
        ];
    }
}

$formatPhoneHref = static function (?string $phone): ?string {
    if ($phone === null) {
        return null;
    }

    $sanitised = preg_replace('/[^\\d+]/', '', $phone);

    return $sanitised !== '' ? $sanitised : null;
};

$contactGroups = [
    [
        'title' => 'Explora',
        'links' => [
            ['label' => 'Paquetes', 'href' => 'index.php#paquetes'],
            ['label' => 'Destinos', 'href' => 'index.php#destinos'],
            ['label' => 'Experiencias', 'href' => 'explorar.php'],
        ],
    ],
    [
        'title' => 'Nosotros',
        'links' => [
            ['label' => 'Quiénes somos', 'href' => 'index.php#inicio'],
            ['label' => 'Trabaja con nosotros', 'href' => '#'],
            ['label' => 'Prensa', 'href' => '#'],
        ],
    ],
    [
        'title' => 'Ayuda',
        'links' => [
            ['label' => 'Centro de soporte', 'href' => '#'],
            ['label' => 'Políticas de viaje', 'href' => '#'],
            ['label' => 'Contacto', 'href' => !empty($contactEmails[0]) ? 'mailto:' . $contactEmails[0] : 'index.php#contacto'],
        ],
    ],
];
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__intro">
            <a class="site-footer__brand" href="index.php#inicio">
                <?php if ($siteLogo): ?>
                    <img class="site-footer__logo" src="<?= htmlspecialchars($siteLogo, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($siteTitle); ?>" width="200" height="56" />
                <?php else: ?>
                    <span class="site-footer__brand-icon" aria-hidden="true">🧭</span>
                    <span class="site-footer__brand-name"><?= htmlspecialchars($siteTitle); ?></span>
                <?php endif; ?>
            </a>
            <?php if (!empty($siteTagline)): ?>
                <p class="site-footer__tagline"><?= htmlspecialchars($siteTagline); ?></p>
            <?php else: ?>
                <p class="site-footer__tagline">Diseñamos experiencias auténticas para que descubras el mundo a tu ritmo.</p>
            <?php endif; ?>

            <?php if (!empty($socialLinks)): ?>
                <ul class="site-footer__social" role="list">
                    <?php foreach ($socialLinks as $social): ?>
                        <li>
                            <a class="site-footer__social-link" href="<?= htmlspecialchars($social['url'], ENT_QUOTES); ?>" target="_blank" rel="noopener">
                                <?= htmlspecialchars($social['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="site-footer__column">
            <h4 class="site-footer__column-title">Contacto</h4>
            <ul class="site-footer__list" role="list">
                <?php foreach ($contactPhones as $phone):
                    $href = $formatPhoneHref($phone);
                ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__contact-icon" aria-hidden="true">📞</span>
                        <?php if ($href): ?>
                            <a href="tel:<?= htmlspecialchars($href, ENT_QUOTES); ?>"><?= htmlspecialchars($phone); ?></a>
                        <?php else: ?>
                            <span><?= htmlspecialchars($phone); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <?php foreach ($contactEmails as $email): ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__contact-icon" aria-hidden="true">✉️</span>
                        <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES); ?>"><?= htmlspecialchars($email); ?></a>
                    </li>
                <?php endforeach; ?>

                <?php foreach ($contactAddresses as $index => $address):
                    $location = $contactLocations[$index] ?? null;
                    $displayAddress = $location ? $address . ' · ' . $location : $address;
                ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__contact-icon" aria-hidden="true">📍</span>
                        <span><?= htmlspecialchars($displayAddress); ?></span>
                    </li>
                <?php endforeach; ?>

                <?php if (empty($contactPhones) && empty($contactEmails) && empty($contactAddresses)): ?>
                    <li class="site-footer__contact-item">
                        <span class="site-footer__contact-icon" aria-hidden="true">💬</span>
                        <a href="index.php#contacto">Escríbenos desde el formulario de contacto</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="site-footer__column">
            <h4 class="site-footer__column-title">Enlaces importantes</h4>
            <div class="site-footer__links">
                <?php foreach ($contactGroups as $group): ?>
                    <div class="site-footer__links-group">
                        <h5><?= htmlspecialchars($group['title']); ?></h5>
                        <ul role="list">
                            <?php foreach ($group['links'] as $link): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES); ?>"><?= htmlspecialchars($link['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <p class="site-footer__legal">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?>. Todos los derechos reservados.</p>
        <div class="site-footer__legal-links">
            <a href="#">Términos y condiciones</a>
            <a href="#">Aviso de privacidad</a>
        </div>
    </div>
</footer>
