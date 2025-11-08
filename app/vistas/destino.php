<?php
$destination = $destination ?? [];
$relatedCircuits = $relatedCircuits ?? [];
$relatedPackages = $relatedPackages ?? [];
$otherDestinations = $otherDestinations ?? [];

$gradients = [
    'linear-gradient(135deg, #6366f1, #22d3ee)',
    'linear-gradient(135deg, #ec4899, #fbbf24)',
    'linear-gradient(135deg, #22c55e, #14b8a6)',
    'linear-gradient(135deg, #8b5cf6, #ec4899)',
];

$ctaPackageId = $relatedPackages[0]['id'] ?? null;
$ctaHref = $ctaPackageId ? 'paquete.php?id=' . urlencode((string) $ctaPackageId) : 'explorar.php';
$updatedAt = $destination['actualizado_en'] ?? null;
$timestamp = $updatedAt ? strtotime($updatedAt) : false;
$updatedLabel = $timestamp ? date('d/m/Y', $timestamp) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Destino'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <link rel="stylesheet" href="estilos/detalles.css" />
</head>
<body class="detail-page">
    <div class="detail-page__background"></div>
    <main class="detail-page__container">
        <header class="detail-hero">
            <div class="detail-hero__content">
                <span class="detail-hero__badge">Destino multicolor</span>
                <h1 class="detail-hero__title"><?= htmlspecialchars($destination['nombre'] ?? 'Destino en preparación'); ?></h1>
                <?php if (!empty($destination['tagline'])): ?>
                    <p class="detail-hero__tagline"><?= htmlspecialchars($destination['tagline']); ?></p>
                <?php endif; ?>
                <p class="detail-hero__description"><?= htmlspecialchars($destination['descripcion'] ?? 'Estamos diseñando experiencias vibrantes para este destino.'); ?></p>
                <ul class="detail-hero__meta">
                    <?php if (!empty($destination['region'])): ?>
                        <li>
                            <span>Región</span>
                            <strong><?= htmlspecialchars($destination['region']); ?></strong>
                        </li>
                    <?php endif; ?>
                    <li>
                        <span>Circuitos activos</span>
                        <strong><?= count($relatedCircuits); ?></strong>
                    </li>
                    <li>
                        <span>Paquetes sugeridos</span>
                        <strong><?= count($relatedPackages); ?></strong>
                    </li>
                    <?php if ($updatedLabel): ?>
                        <li>
                            <span>Actualizado</span>
                            <strong><?= htmlspecialchars($updatedLabel); ?></strong>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="detail-hero__actions">
                    <a class="detail-button detail-button--primary" href="<?= htmlspecialchars($ctaHref); ?>">Planifica tu viaje</a>
                    <a class="detail-button detail-button--ghost" href="explorar.php">Ver más experiencias</a>
                </div>
            </div>
            <?php if (!empty($destination['tags'])): ?>
                <div class="detail-hero__aside">
                    <h2 class="detail-hero__aside-title">Sensaciones clave</h2>
                    <ul class="detail-pill-group">
                        <?php foreach ($destination['tags'] as $tag): ?>
                            <li class="detail-pill"><?= htmlspecialchars($tag); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </header>

        <?php if ($relatedCircuits): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Circuitos vibrantes en <?= htmlspecialchars($destination['nombre'] ?? 'la zona'); ?></h2>
                    <p>Explora itinerarios cuidadosamente curados con guías locales y colores que inspiran aventura.</p>
                </header>
                <div class="detail-grid">
                    <?php foreach ($relatedCircuits as $index => $circuit): ?>
                        <?php $gradient = $gradients[$index % count($gradients)]; ?>
                        <article class="detail-card detail-card--vibrant" style="--card-gradient: <?= htmlspecialchars($gradient); ?>">
                            <div class="detail-card__content">
                                <span class="detail-card__chip">Circuito exclusivo</span>
                                <h3><?= htmlspecialchars($circuit['nombre'] ?? 'Circuito'); ?></h3>
                                <p><?= htmlspecialchars($circuit['descripcion'] ?? 'Muy pronto tendremos una descripción completa.'); ?></p>
                                <ul class="detail-list">
                                    <?php if (!empty($circuit['duracion'])): ?>
                                        <li><strong>Duración:</strong> <?= htmlspecialchars($circuit['duracion']); ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($circuit['frecuencia'])): ?>
                                        <li><strong>Frecuencia:</strong> <?= htmlspecialchars($circuit['frecuencia']); ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($circuit['puntos_interes'])): ?>
                                        <li><strong>Puntos clave:</strong> <?= htmlspecialchars(implode(' · ', $circuit['puntos_interes'])); ?></li>
                                    <?php endif; ?>
                                </ul>
                                <a class="detail-link" href="circuito.php?id=<?= urlencode((string) ($circuit['id'] ?? '')); ?>">Descubrir circuito</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($relatedPackages): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Paquetes que conectan con este destino</h2>
                    <p>Combina alojamiento boutique, gastronomía local y actividades guiadas con nuestros paquetes llenos de color.</p>
                </header>
                <div class="detail-grid detail-grid--three">
                    <?php foreach ($relatedPackages as $index => $package): ?>
                        <?php $gradient = $gradients[($index + 1) % count($gradients)]; ?>
                        <article class="detail-card detail-card--frosted">
                            <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradient); ?>"></div>
                            <div class="detail-card__content">
                                <h3><?= htmlspecialchars($package['nombre'] ?? 'Paquete'); ?></h3>
                                <p><?= htmlspecialchars($package['descripcion_breve'] ?? ($package['descripcion_detallada'] ?? 'Muy pronto revelaremos este paquete.')); ?></p>
                                <div class="detail-price">
                                    <?php if (!empty($package['precio_desde'])): ?>
                                        <span class="detail-price__label">Desde</span>
                                        <span class="detail-price__value">S/ <?= number_format((float) $package['precio_desde'], 2, '.', ' '); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($package['duracion'])): ?>
                                        <span class="detail-price__meta"><?= htmlspecialchars($package['duracion']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a class="detail-button detail-button--light" href="paquete.php?id=<?= urlencode((string) ($package['id'] ?? '')); ?>">Ver detalles</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($otherDestinations): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Más destinos coloridos</h2>
                    <p>Dale un vistazo a otros lugares de la Selva Central listos para sorprenderte.</p>
                </header>
                <div class="detail-marquee">
                    <?php foreach ($otherDestinations as $index => $other): ?>
                        <?php $gradient = $gradients[($index + 2) % count($gradients)]; ?>
                        <a class="detail-marquee__item" style="--card-gradient: <?= htmlspecialchars($gradient); ?>" href="destino.php?id=<?= urlencode((string) ($other['id'] ?? '')); ?>">
                            <span><?= htmlspecialchars($other['nombre'] ?? 'Destino'); ?></span>
                            <?php if (!empty($other['region'])): ?>
                                <small><?= htmlspecialchars($other['region']); ?></small>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
