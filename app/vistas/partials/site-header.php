<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteTagline = $siteSettings['siteTagline'] ?? null;
$siteLogo = $siteSettings['siteLogo'] ?? null;
if (!is_string($siteLogo) || trim($siteLogo) === '') {
    $siteLogo = null;
}

$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$contactPhones = $contact['phones'] ?? [];

$formatPhoneHref = static function (?string $phone): ?string {
    if ($phone === null) {
        return null;
    }

    $sanitised = preg_replace('/[^\\d+]/', '', $phone);

    return $sanitised !== '' ? $sanitised : null;
};

$primaryPhone = $contactPhones[0] ?? null;
$primaryPhoneHref = $formatPhoneHref($primaryPhone);

$currentUser = $currentUser ?? null;
$isAuthenticated = is_array($currentUser) && !empty($currentUser);
$isAdmin = $isAuthenticated && ($currentUser['rol'] ?? '') === 'administrador';
$displayName = $isAuthenticated ? trim((string) ($currentUser['nombre'] ?? '')) : '';

$activeNav = $activeNav ?? null;
$navItems = [
    ['id' => 'destinos', 'label' => 'Destino', 'href' => 'index.php#destinos'],
    ['id' => 'circuitos', 'label' => 'Circuito', 'href' => 'index.php#circuitos'],
    ['id' => 'paquetes', 'label' => 'Paquetes', 'href' => 'index.php#paquetes'],
    ['id' => 'contacto', 'label' => 'Contacto', 'href' => 'index.php#contacto'],
];
?>
<header class="site-header" data-site-header>
    <div class="site-header__inner">
        <a class="site-header__brand<?= $siteLogo ? ' site-header__brand--image' : ''; ?>" href="index.php#inicio">
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
            <?php foreach ($navItems as $item):
                $isActive = $activeNav === $item['id'];
            ?>
                <a class="site-header__link<?= $isActive ? ' site-header__link--active' : ''; ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES); ?>">
                    <?= htmlspecialchars($item['label']); ?>
                </a>
            <?php endforeach; ?>
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
                        <path d="M3.5 4h1.7a1 1 0 0 1 .98.804l.27 1.352M7 15h10.45a1 1 0 0 0 .98-.804l1.2-6A1 1 0 0 0 18.65 7H6.45" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
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
                            <a class="site-header__user-menu-link" href="perfil.php" data-user-menu-close>
                                <span class="site-header__user-menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-7 2-7 4.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1.5C19 16 16 14 12 14Z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <span class="site-header__user-menu-text">Perfil</span>
                            </a>
                            <a class="site-header__user-menu-link" href="reservaciones.php" data-user-menu-close>
                                <span class="site-header__user-menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 3v3m10-3v3M5 7h14M6 11h5l3 3h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="9" cy="15" r="1" fill="currentColor"/>
                                    </svg>
                                </span>
                                <span class="site-header__user-menu-text">Reservaciones</span>
                            </a>
                            <a class="site-header__user-menu-link" href="favoritos.php" data-user-menu-close>
                                <span class="site-header__user-menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 20s-6-3.5-6-8.5A4.5 4.5 0 0 1 10.5 7 3.5 3.5 0 0 1 12 8.2 3.5 3.5 0 0 1 13.5 7 4.5 4.5 0 0 1 18 11.5c0 5-6 8.5-6 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="site-header__user-menu-text">Favoritos</span>
                            </a>
                            <a class="site-header__user-menu-link" href="carrito.php" data-user-menu-close>
                                <span class="site-header__user-menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 6h1.6a1 1 0 0 1 .98.804L7.5 9.5M7.5 9.5H18a1 1 0 0 1 .98 1.196l-1 5A1 1 0 0 1 17 16H9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="9.5" cy="18.5" r="1" fill="currentColor"/>
                                        <circle cx="16.5" cy="18.5" r="1" fill="currentColor"/>
                                    </svg>
                                </span>
                                <span class="site-header__user-menu-text">Carrito</span>
                            </a>
                            <?php if ($isAdmin): ?>
                                <a class="site-header__user-menu-link" href="../administracion/index.php" data-user-menu-close>
                                    <span class="site-header__user-menu-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 5.5v13M18.5 12h-13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </span>
                                    <span class="site-header__user-menu-text">Panel de Control</span>
                                </a>
                            <?php endif; ?>
                            <button class="site-header__user-menu-link site-header__user-menu-link--logout" type="button" data-auth-logout data-user-menu-close>
                                <span class="site-header__user-menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M14.5 7.5 19 12l-4.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19 12h-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="site-header__user-menu-text">Cerrar sesión</span>
                            </button>
                        </nav>
                    </div>
                <?php else: ?>
                    <button class="button button--primary site-header__cta-button" type="button" data-auth-open>Iniciar sesión</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
