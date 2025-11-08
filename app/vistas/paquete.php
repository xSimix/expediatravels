<?php
$package = $package ?? [];
$includedCircuits = $includedCircuits ?? [];
$includedDestinations = $includedDestinations ?? [];
$otherPackages = $otherPackages ?? [];

$gradients = [
    'linear-gradient(135deg, #f97316, #facc15)',
    'linear-gradient(135deg, #22d3ee, #6366f1)',
    'linear-gradient(135deg, #34d399, #10b981)',
    'linear-gradient(135deg, #ec4899, #a855f7)',
];

$status = strtolower((string) ($package['estado'] ?? 'borrador'));
$statusLabels = [
    'publicado' => 'Disponible',
    'borrador' => 'Próximamente',
    'inactivo' => 'En pausa',
];
$statusLabel = $statusLabels[$status] ?? ucfirst($status ?: 'Borrador');
$ctaHref = !empty($package['id']) ? 'perfil.php' : 'explorar.php';
$price = $package['precio_desde'] ?? null;
$currency = strtoupper((string) ($package['moneda'] ?? 'PEN'));
$duration = $package['duracion'] ?? '';
$departures = $package['salidas'] ?? [];
$formattedDepartures = array_values(array_filter(array_map(function ($date) {
    $timestamp = strtotime((string) $date);
    if ($timestamp === false) {
        return null;
    }
    return date('d/m/Y', $timestamp);
}, $departures)));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Paquete'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <link rel="stylesheet" href="estilos/detalles.css" />
</head>
<body class="detail-page">
    <div class="detail-page__background"></div>
    <main class="detail-page__container">
        <header class="detail-hero">
            <div class="detail-hero__content">
                <span class="detail-hero__badge">Paquete multicolor</span>
                <h1 class="detail-hero__title"><?= htmlspecialchars($package['nombre'] ?? 'Paquete en preparación'); ?></h1>
                <?php if (!empty($package['descripcion_breve'])): ?>
                    <p class="detail-hero__tagline"><?= htmlspecialchars($package['descripcion_breve']); ?></p>
                <?php endif; ?>
                <p class="detail-hero__description"><?= htmlspecialchars($package['descripcion_detallada'] ?? 'Estamos curando un paquete lleno de experiencias vibrantes y servicios premium.'); ?></p>
                <ul class="detail-hero__meta">
                    <?php if ($price): ?>
                        <li>
                            <span>Desde</span>
                            <strong><?= htmlspecialchars($currency); ?> <?= number_format((float) $price, 2, '.', ' '); ?></strong>
                        </li>
                    <?php endif; ?>
                    <?php if ($duration): ?>
                        <li>
                            <span>Duración</span>
                            <strong><?= htmlspecialchars($duration); ?></strong>
                        </li>
                    <?php endif; ?>
                    <li>
                        <span>Estado</span>
                        <strong><?= htmlspecialchars($statusLabel); ?></strong>
                    </li>
                    <?php if ($formattedDepartures): ?>
                        <li>
                            <span>Próximas salidas</span>
                            <strong><?= htmlspecialchars(implode(' · ', $formattedDepartures)); ?></strong>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="detail-hero__actions">
                    <a class="detail-button detail-button--primary" href="<?= htmlspecialchars($ctaHref); ?>">Reservar ahora</a>
                    <a class="detail-button detail-button--ghost" href="explorar.php">Ver más opciones</a>
                </div>
            </div>
            <?php if ($includedDestinations): ?>
                <div class="detail-hero__aside">
                    <h2 class="detail-hero__aside-title">Destinos del paquete</h2>
                    <ul class="detail-pill-group">
                        <?php foreach ($includedDestinations as $destination): ?>
                            <li class="detail-pill"><?= htmlspecialchars($destination['nombre'] ?? 'Destino'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </header>

        <section class="detail-section">
            <header class="detail-section__header">
                <h2>Lo que incluye tu experiencia</h2>
                <p>Servicios cuidadosamente seleccionados para vivir una travesía llena de color, sabor y aventura.</p>
            </header>
            <div class="detail-grid">
                <?php if (!empty($package['incluye'])): ?>
                    <article class="detail-card detail-card--frosted">
                        <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradients[0]); ?>"></div>
                        <div class="detail-card__content">
                            <h3>Incluye</h3>
                            <ul class="detail-bullet-list">
                                <?php foreach ($package['incluye'] as $item): ?>
                                    <li><?= htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endif; ?>
                <?php if (!empty($package['no_incluye'])): ?>
                    <article class="detail-card detail-card--frosted">
                        <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradients[1]); ?>"></div>
                        <div class="detail-card__content">
                            <h3>No incluye</h3>
                            <ul class="detail-bullet-list">
                                <?php foreach ($package['no_incluye'] as $item): ?>
                                    <li><?= htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endif; ?>
                <?php if (!empty($package['beneficios'])): ?>
                    <article class="detail-card detail-card--vibrant" style="--card-gradient: <?= htmlspecialchars($gradients[2]); ?>">
                        <div class="detail-card__content">
                            <span class="detail-card__chip">Beneficios</span>
                            <ul class="detail-bullet-list detail-bullet-list--inline">
                                <?php foreach ($package['beneficios'] as $benefit): ?>
                                    <li><?= htmlspecialchars($benefit); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($includedCircuits): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Circuitos dentro del paquete</h2>
                    <p>Conecta los mejores recorridos guiados en un solo itinerario lleno de vivacidad.</p>
                </header>
                <div class="detail-grid">
                    <?php foreach ($includedCircuits as $index => $circuit): ?>
                        <?php $gradient = $gradients[($index + 1) % count($gradients)]; ?>
                        <article class="detail-card detail-card--vibrant" style="--card-gradient: <?= htmlspecialchars($gradient); ?>">
                            <div class="detail-card__content">
                                <span class="detail-card__chip">Circuito</span>
                                <h3><?= htmlspecialchars($circuit['nombre'] ?? 'Circuito'); ?></h3>
                                <p><?= htmlspecialchars($circuit['descripcion'] ?? 'Circuito exclusivo seleccionado para este paquete.'); ?></p>
                                <a class="detail-link" href="circuito.php?id=<?= urlencode((string) ($circuit['id'] ?? '')); ?>">Ver circuito</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($otherPackages): ?>
            <section class="detail-section">
                <header class="detail-section__header">
                    <h2>Otros paquetes llenos de color</h2>
                    <p>Explora alternativas que también combinan destinos, circuitos y experiencias vibrantes.</p>
                </header>
                <div class="detail-grid detail-grid--three">
                    <?php foreach ($otherPackages as $index => $other): ?>
                        <?php $gradient = $gradients[($index + 2) % count($gradients)]; ?>
                        <article class="detail-card detail-card--frosted">
                            <div class="detail-card__accent" style="--card-gradient: <?= htmlspecialchars($gradient); ?>"></div>
                            <div class="detail-card__content">
                                <h3><?= htmlspecialchars($other['nombre'] ?? 'Paquete'); ?></h3>
                                <p><?= htmlspecialchars($other['descripcion_breve'] ?? ($other['descripcion_detallada'] ?? 'Pronto revelaremos los detalles.')); ?></p>
                                <div class="detail-price">
                                    <?php if (!empty($other['precio_desde'])): ?>
                                        <span class="detail-price__label">Desde</span>
                                        <span class="detail-price__value">S/ <?= number_format((float) $other['precio_desde'], 2, '.', ' '); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($other['duracion'])): ?>
                                        <span class="detail-price__meta"><?= htmlspecialchars($other['duracion']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a class="detail-button detail-button--light" href="paquete.php?id=<?= urlencode((string) ($other['id'] ?? '')); ?>">Ver experiencia</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
