<?php
$circuit = $circuit ?? [];
$destination = $destination ?? null;
$relatedPackages = $relatedPackages ?? [];
$otherCircuits = $otherCircuits ?? [];

$gradients = [
    'linear-gradient(135deg, #0ea5e9, #6366f1)',
    'linear-gradient(135deg, #f97316, #ec4899)',
    'linear-gradient(135deg, #10b981, #14b8a6)',
    'linear-gradient(135deg, #a855f7, #6366f1)',
];

$ctaPackageId = $relatedPackages[0]['id'] ?? null;
$ctaHref = $ctaPackageId ? 'paquete.php?id=' . urlencode((string) $ctaPackageId) : 'explorar.php';
$updatedAt = $circuit['actualizado_en'] ?? null;
$timestamp = $updatedAt ? strtotime($updatedAt) : false;
$updatedLabel = $timestamp ? date('d/m/Y', $timestamp) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Circuito'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <link rel="stylesheet" href="estilos/detalles.css" />
</head>
<body class="detail-page">
    <div class="detail-page__background"></div>
    <main class="detail-page__container">
        <header class="detail-hero">
            <div class="detail-hero__content">
                <span class="detail-hero__badge">Circuito multicolor</span>
                <h1 class="detail-hero__title"><?= htmlspecialchars($circuit['nombre'] ?? 'Circuito en preparación'); ?></h1>
                <p class="detail-hero__description"><?= htmlspecialchars($circuit['descripcion'] ?? 'Estamos elaborando un circuito vibrante con experiencias únicas.'); ?></p>
                <ul class="detail-hero__meta">
                    <?php if (!empty($circuit['duracion'])): ?>
                        <li>
                            <span>Duración</span>
                            <strong><?= htmlspecialchars($circuit['duracion']); ?></strong>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($circuit['dificultad'])): ?>
                        <li>
                            <span>Dificultad</span>
                            <strong><?= htmlspecialchars(ucfirst($circuit['dificultad'])); ?></strong>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($circuit['frecuencia'])): ?>
                        <li>
                            <span>Frecuencia</span>
                            <strong><?= htmlspecialchars($circuit['frecuencia']); ?></strong>
                        </li>
                    <?php endif; ?>
                    <?php if ($updatedLabel): ?>
                        <li>
                            <span>Actualizado</span>
                            <strong><?= htmlspecialchars($updatedLabel); ?></strong>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="detail-hero__actions">
                    <a class="detail-button detail-button--primary" href="<?= htmlspecialchars($ctaHref); ?>">Reservar paquete</a>
                    <a class="detail-button detail-button--ghost" href="destino.php<?= $destination ? '?id=' . urlencode((string) ($destination['id'] ?? '')) : ''; ?>">Ver destino asociado</a>
                </div>
            </div>
            <div class="detail-hero__aside">
                <h2 class="detail-hero__aside-title">Destello del destino</h2>
                <?php if ($destination): ?>
                    <p class="detail-hero__aside-highlight"><?= htmlspecialchars($destination['nombre']); ?> · <?= htmlspecialchars($destination['region'] ?? ''); ?></p>
                    <?php if (!empty($destination['tagline'])): ?>
                        <p class="detail-hero__aside-description"><?= htmlspecialchars($destination['tagline']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="detail-hero__aside-description">Pronto compartiremos el destino que aloja este circuito.</p>
                <?php endif; ?>
            </div>
        </header>

        <section class="detail-section">
            <header class="detail-section__header">
                <h2>Experiencias destacadas del circuito</h2>
                <p>Actividades inmersivas, servicios premium y paisajes llenos de color para viajeros curiosos.</p>
            </header>
            <div class="detail-grid">
                <?php if (!empty($circuit['puntos_interes'])): ?>
                    <article class="detail-card detail-card--frosted">
                        <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradients[0]); ?>"></div>
                        <div class="detail-card__content">
                            <h3>Momentos imperdibles</h3>
                            <ul class="detail-bullet-list">
                                <?php foreach ($circuit['puntos_interes'] as $point): ?>
                                    <li><?= htmlspecialchars($point); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endif; ?>
                <?php if (!empty($circuit['servicios'])): ?>
                    <article class="detail-card detail-card--frosted">
                        <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradients[1]); ?>"></div>
                        <div class="detail-card__content">
                            <h3>Servicios incluidos</h3>
                            <ul class="detail-bullet-list">
                                <?php foreach ($circuit['servicios'] as $service): ?>
                                    <li><?= htmlspecialchars($service); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endif; ?>
                <?php if (!empty($circuit['categoria'])): ?>
                    <article class="detail-card detail-card--vibrant" style="--card-gradient: <?= htmlspecialchars($gradients[2]); ?>">
                        <div class="detail-card__content">
                            <span class="detail-card__chip">Categoría</span>
                            <h3><?= htmlspecialchars(ucfirst($circuit['categoria'])); ?></h3>
                            <p>Diseñado para viajeros que buscan una dosis de <?= htmlspecialchars($circuit['categoria']); ?> con una curaduría local auténtica.</p>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($relatedPackages): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Paquetes que incluyen este circuito</h2>
                    <p>Selecciona la experiencia ideal y vive todo el circuito con hospedaje, transporte y atención personalizada.</p>
                </header>
                <div class="detail-grid detail-grid--three">
                    <?php foreach ($relatedPackages as $index => $package): ?>
                        <?php $gradient = $gradients[($index + 1) % count($gradients)]; ?>
                        <article class="detail-card detail-card--frosted">
                            <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradient); ?>"></div>
                            <div class="detail-card__content">
                                <h3><?= htmlspecialchars($package['nombre'] ?? 'Paquete'); ?></h3>
                                <p><?= htmlspecialchars($package['descripcion_breve'] ?? ($package['descripcion_detallada'] ?? 'Muy pronto compartiremos más detalles.')); ?></p>
                                <div class="detail-price">
                                    <?php if (!empty($package['precio_desde'])): ?>
                                        <span class="detail-price__label">Desde</span>
                                        <span class="detail-price__value">S/ <?= number_format((float) $package['precio_desde'], 2, '.', ' '); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($package['duracion'])): ?>
                                        <span class="detail-price__meta"><?= htmlspecialchars($package['duracion']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a class="detail-button detail-button--light" href="paquete.php?id=<?= urlencode((string) ($package['id'] ?? '')); ?>">Ver paquete</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($otherCircuits): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Otros circuitos llenos de color</h2>
                    <p>Experimenta nuevas rutas y paisajes con el sello creativo de Expediatravels.</p>
                </header>
                <div class="detail-marquee">
                    <?php foreach ($otherCircuits as $index => $other): ?>
                        <?php $gradient = $gradients[($index + 2) % count($gradients)]; ?>
                        <a class="detail-marquee__item" style="--card-gradient: <?= htmlspecialchars($gradient); ?>" href="circuito.php?id=<?= urlencode((string) ($other['id'] ?? '')); ?>">
                            <span><?= htmlspecialchars($other['nombre'] ?? 'Circuito'); ?></span>
                            <?php if (!empty($other['duracion'])): ?>
                                <small><?= htmlspecialchars($other['duracion']); ?></small>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
