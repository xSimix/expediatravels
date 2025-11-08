<?php
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$contact = $siteSettings['contact'] ?? [];
$contactEmails = $contact['emails'] ?? [];
$primaryEmail = $contactEmails[0] ?? null;
?>
<footer class="site-footer">
    <div class="site-footer__container">
        <div class="site-footer__cta">
            <h2 class="site-footer__heading">Planifica tu próxima aventura con nosotros</h2>
            <p class="site-footer__description">
                Diseñamos experiencias de viaje flexibles y seguras para que explores el mundo con tranquilidad.
                Nuestro equipo está listo para ayudarte en cada paso del camino.
            </p>
            <a class="site-footer__cta-button" href="<?= !empty($primaryEmail)
                ? 'mailto:' . htmlspecialchars($primaryEmail, ENT_QUOTES)
                : 'index.php#contacto'; ?>">Quiero conversar</a>
        </div>
        <div class="site-footer__info">
            <div class="site-footer__brand-block">
                <div class="site-footer__brand"><?= htmlspecialchars($siteTitle); ?></div>
                <p class="site-footer__tagline">Inspiramos a los viajeros con ideas auténticas y apoyo cercano.</p>
                <ul class="site-footer__social">
                    <li><a href="#" aria-label="Visítanos en Facebook">Facebook</a></li>
                    <li><a href="#" aria-label="Síguenos en Instagram">Instagram</a></li>
                    <li><a href="#" aria-label="Síguenos en YouTube">YouTube</a></li>
                </ul>
            </div>
            <div class="site-footer__columns">
                <div>
                    <h4>Encuéntranos</h4>
                    <ul>
                        <li>356, Road - 3</li>
                        <li>New York, Estados Unidos</li>
                        <li><a href="tel:+10235788687">+1 (023) 578 8687</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Explora</h4>
                    <ul>
                        <li><a href="index.php#paquetes">Paquetes</a></li>
                        <li><a href="index.php#destinos">Destinos</a></li>
                        <li><a href="explorar.php">Experiencias</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Recursos</h4>
                    <ul>
                        <li><a href="#">Centro de soporte</a></li>
                        <li><a href="#">Políticas de viaje</a></li>
                        <li>
                            <?php if (!empty($primaryEmail)): ?>
                                <a href="mailto:<?= htmlspecialchars($primaryEmail, ENT_QUOTES); ?>">Escríbenos</a>
                            <?php else: ?>
                                <a href="index.php#contacto">Escríbenos</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <p class="site-footer__legal">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?>. Todos los derechos reservados.</p>
</footer>
