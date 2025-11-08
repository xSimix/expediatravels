<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$primaryEmail = $contactEmails[0] ?? null;
?>
<footer class="site-footer">
    <div class="site-footer__brand"><?= htmlspecialchars($siteTitle); ?></div>
    <div class="site-footer__links">
        <div>
            <h4>Explora</h4>
            <ul>
                <li><a href="index.php#paquetes">Paquetes</a></li>
                <li><a href="index.php#destinos">Destinos</a></li>
                <li><a href="explorar.php">Experiencias</a></li>
            </ul>
        </div>
        <div>
            <h4>Nosotros</h4>
            <ul>
                <li><a href="index.php#inicio">Quiénes somos</a></li>
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
                        <a href="index.php#contacto">Contacto</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
    <p class="site-footer__legal">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?>. Todos los derechos reservados.</p>
</footer>
