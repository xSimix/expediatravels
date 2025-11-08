<?php
$detail = isset($detail) && is_array($detail) ? $detail : [];

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$heroImage = $detail['heroImage'] ?? '';
$themeGradient = $detail['themeGradient'] ?? '';
$heroStyleParts = [];

if (is_string($heroImage) && trim($heroImage) !== '') {
    $encodedHeroImage = json_encode($heroImage, JSON_UNESCAPED_SLASHES);
    if ($encodedHeroImage !== false) {
        $heroStyleParts[] = '--detail-hero-image: url(' . $encodedHeroImage . ');';
    }
}

if (is_string($themeGradient) && trim($themeGradient) !== '') {
    $heroStyleParts[] = '--detail-hero-gradient: ' . $themeGradient . ';';
}

$heroStyle = $heroStyleParts !== [] ? $escape(implode(' ', $heroStyleParts)) : '';

$quickFacts = isset($detail['quickFacts']) && is_array($detail['quickFacts']) ? $detail['quickFacts'] : [];
$overview = isset($detail['overview']) && is_array($detail['overview']) ? $detail['overview'] : [];
$themes = isset($overview['themes']) && is_array($overview['themes']) ? $overview['themes'] : [];
$itinerary = isset($detail['itinerary']) && is_array($detail['itinerary']) ? $detail['itinerary'] : [];
$highlights = isset($detail['highlights']) && is_array($detail['highlights']) ? $detail['highlights'] : [];
$faqs = isset($detail['faqs']) && is_array($detail['faqs']) ? $detail['faqs'] : [];
$reviews = isset($detail['reviews']) && is_array($detail['reviews']) ? $detail['reviews'] : [];
$gallery = isset($detail['gallery']) && is_array($detail['gallery']) ? $detail['gallery'] : [];
$contact = isset($detail['contact']) && is_array($detail['contact']) ? $detail['contact'] : [];
$price = isset($detail['price']) && is_array($detail['price']) ? $detail['price'] : null;
$mapEmbed = isset($detail['mapEmbed']) ? (string) $detail['mapEmbed'] : '';
$typeLabel = isset($detail['type']) ? (string) $detail['type'] : '';
$titleText = isset($detail['title']) ? (string) $detail['title'] : '';
$tagline = isset($detail['tagline']) ? (string) $detail['tagline'] : '';
$description = isset($overview['description']) ? (string) $overview['description'] : '';

$formatPrice = static function (?array $price) use ($escape): string {
    if ($price === null) {
        return '';
    }

    $amount = $price['amount'] ?? null;
    $currency = $price['currency'] ?? '';
    if (!is_numeric($amount)) {
        return '';
    }

    $formattedAmount = number_format((float) $amount, 2, '.', ',');
    $currencyText = is_string($currency) && $currency !== '' ? $currency . ' ' : '';

    return $escape($currencyText . $formattedAmount);
};

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($title) ? $escape($title) : 'Expediatravels'; ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
</head>
<body class="page page--detail">
    <header class="detail-hero"<?= $heroStyle !== '' ? ' style="' . $heroStyle . '"' : ''; ?>>
        <div class="detail-hero__overlay"></div>
        <div class="detail-hero__inner">
            <nav class="detail-hero__nav">
                <a class="detail-hero__brand" href="index.php">Expediatravels</a>
                <div class="detail-hero__links">
                    <a href="explorar.php">Explorar</a>
                    <a href="perfil.php">Mi cuenta</a>
                </div>
            </nav>
            <div class="detail-hero__content">
                <?php if ($typeLabel !== ''): ?>
                    <span class="detail-badge detail-badge--frost"><?= $escape($typeLabel); ?></span>
                <?php endif; ?>
                <h1 class="detail-hero__title"><?= $escape($titleText); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="detail-hero__tagline"><?= $escape($tagline); ?></p>
                <?php endif; ?>
                <?php if ($quickFacts !== []): ?>
                    <ul class="detail-hero__facts">
                        <?php foreach ($quickFacts as $fact):
                            $label = isset($fact['label']) ? (string) $fact['label'] : '';
                            $value = isset($fact['value']) ? (string) $fact['value'] : '';
                            $icon = isset($fact['icon']) ? (string) $fact['icon'] : '';
                            if ($value === '' && $label === '') {
                                continue;
                            }
                            ?>
                            <li class="detail-hero__fact">
                                <?php if ($icon !== ''): ?>
                                    <span class="detail-hero__fact-icon" aria-hidden="true"><?= $escape($icon); ?></span>
                                <?php endif; ?>
                                <span class="detail-hero__fact-label"><?= $escape($label); ?></span>
                                <span class="detail-hero__fact-value"><?= $escape($value); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($price !== null): ?>
                    <div class="detail-hero__cta">
                        <span class="detail-hero__price"><?= $formatPrice($price); ?></span>
                        <?php if (isset($price['per']) && is_string($price['per'])): ?>
                            <span class="detail-hero__price-note"><?= $escape($price['per']); ?></span>
                        <?php endif; ?>
                        <a class="detail-button detail-button--primary" href="#reserva">Reservar ahora</a>
                    </div>
                <?php else: ?>
                    <a class="detail-button detail-button--primary" href="#reserva">Planificar experiencia</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="detail-layout">
        <div class="detail-main">
            <?php if ($description !== ''): ?>
                <section class="detail-section detail-section--overview">
                    <header class="detail-section__header">
                        <h2>Panorama general</h2>
                        <p><?= $escape($description); ?></p>
                    </header>
                    <?php if ($themes !== []): ?>
                        <div class="detail-theme-grid">
                            <?php foreach ($themes as $theme):
                                $themeTitle = isset($theme['title']) ? (string) $theme['title'] : '';
                                $themeDescription = isset($theme['description']) ? (string) $theme['description'] : '';
                                if ($themeTitle === '' && $themeDescription === '') {
                                    continue;
                                }
                                ?>
                                <article class="detail-theme-card">
                                    <?php if ($themeTitle !== ''): ?>
                                        <h3><?= $escape($themeTitle); ?></h3>
                                    <?php endif; ?>
                                    <?php if ($themeDescription !== ''): ?>
                                        <p><?= $escape($themeDescription); ?></p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($gallery !== []): ?>
                <section class="detail-section detail-section--gallery">
                    <header class="detail-section__header detail-section__header--compact">
                        <h2>Galería inspiradora</h2>
                        <p>Descubre momentos que vivirás en esta experiencia.</p>
                    </header>
                    <div class="detail-gallery">
                        <?php foreach ($gallery as $image):
                            if (!is_string($image) || trim($image) === '') {
                                continue;
                            }
                            ?>
                            <figure class="detail-gallery__item">
                                <img src="<?= $escape($image); ?>" alt="Vista destacada" loading="lazy" />
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($itinerary !== []): ?>
                <section class="detail-section detail-section--itinerary" id="itinerario">
                    <header class="detail-section__header">
                        <h2>Itinerario sugerido</h2>
                        <p>Actividades cuidadosamente curadas para aprovechar cada momento.</p>
                    </header>
                    <ol class="detail-timeline">
                        <?php foreach ($itinerary as $index => $item):
                            $stepTitle = isset($item['title']) ? (string) $item['title'] : '';
                            $stepDescription = isset($item['description']) ? (string) $item['description'] : '';
                            if ($stepTitle === '' && $stepDescription === '') {
                                continue;
                            }
                            ?>
                            <li class="detail-timeline__item">
                                <div class="detail-timeline__badge"><?= $escape((string) ($index + 1)); ?></div>
                                <div class="detail-timeline__body">
                                    <?php if ($stepTitle !== ''): ?>
                                        <h3><?= $escape($stepTitle); ?></h3>
                                    <?php endif; ?>
                                    <?php if ($stepDescription !== ''): ?>
                                        <p><?= $escape($stepDescription); ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>

            <?php if ($highlights !== []): ?>
                <section class="detail-section detail-section--highlights">
                    <header class="detail-section__header detail-section__header--compact">
                        <h2>Lo que hace único a este <?= $typeLabel !== '' ? $escape(strtolower($typeLabel)) : 'programa'; ?></h2>
                    </header>
                    <div class="detail-highlight-grid">
                        <?php foreach ($highlights as $highlight):
                            $highlightTitle = isset($highlight['title']) ? (string) $highlight['title'] : '';
                            $highlightDescription = isset($highlight['description']) ? (string) $highlight['description'] : '';
                            $highlightIcon = isset($highlight['icon']) ? (string) $highlight['icon'] : '';
                            if ($highlightTitle === '' && $highlightDescription === '') {
                                continue;
                            }
                            ?>
                            <article class="detail-highlight-card">
                                <?php if ($highlightIcon !== ''): ?>
                                    <span class="detail-highlight-card__icon" aria-hidden="true"><?= $escape($highlightIcon); ?></span>
                                <?php endif; ?>
                                <?php if ($highlightTitle !== ''): ?>
                                    <h3><?= $escape($highlightTitle); ?></h3>
                                <?php endif; ?>
                                <?php if ($highlightDescription !== ''): ?>
                                    <p><?= $escape($highlightDescription); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($faqs !== []): ?>
                <section class="detail-section detail-section--faqs" id="preguntas">
                    <header class="detail-section__header detail-section__header--compact">
                        <h2>Preguntas frecuentes</h2>
                    </header>
                    <div class="detail-faqs">
                        <?php foreach ($faqs as $faq):
                            $question = isset($faq['question']) ? (string) $faq['question'] : '';
                            $answer = isset($faq['answer']) ? (string) $faq['answer'] : '';
                            if ($question === '' && $answer === '') {
                                continue;
                            }
                            ?>
                            <article class="detail-faqs__item">
                                <?php if ($question !== ''): ?>
                                    <h3><?= $escape($question); ?></h3>
                                <?php endif; ?>
                                <?php if ($answer !== ''): ?>
                                    <p><?= $escape($answer); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($reviews !== []): ?>
                <section class="detail-section detail-section--reviews">
                    <header class="detail-section__header detail-section__header--compact">
                        <h2>Experiencias reales</h2>
                        <p>Comentarios de viajeros que ya vivieron esta propuesta.</p>
                    </header>
                    <div class="detail-reviews">
                        <?php foreach ($reviews as $review):
                            $name = isset($review['name']) ? (string) $review['name'] : '';
                            $rating = isset($review['rating']) ? (int) $review['rating'] : 0;
                            $date = isset($review['date']) ? (string) $review['date'] : '';
                            $comment = isset($review['comment']) ? (string) $review['comment'] : '';
                            if ($name === '' && $comment === '') {
                                continue;
                            }
                            $rating = max(0, min(5, $rating));
                            ?>
                            <article class="detail-review-card">
                                <header class="detail-review-card__header">
                                    <?php if ($name !== ''): ?>
                                        <h3><?= $escape($name); ?></h3>
                                    <?php endif; ?>
                                    <?php if ($date !== ''): ?>
                                        <span class="detail-review-card__date"><?= $escape($date); ?></span>
                                    <?php endif; ?>
                                </header>
                                <?php if ($rating > 0): ?>
                                    <div class="detail-review-card__rating" aria-label="Valoración: <?= $escape((string) $rating); ?> de 5">
                                        <?php for ($i = 0; $i < $rating; $i++): ?>
                                            <span aria-hidden="true">★</span>
                                        <?php endfor; ?>
                                        <?php for ($i = $rating; $i < 5; $i++): ?>
                                            <span aria-hidden="true" class="detail-review-card__rating--muted">★</span>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($comment !== ''): ?>
                                    <p><?= $escape($comment); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($mapEmbed !== ''): ?>
                <section class="detail-section detail-section--map">
                    <header class="detail-section__header detail-section__header--compact">
                        <h2>Ubicación</h2>
                    </header>
                    <div class="detail-map">
                        <iframe src="<?= $escape($mapEmbed); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <aside class="detail-aside" id="reserva">
            <?php if ($price !== null): ?>
                <section class="detail-card detail-card--pricing">
                    <h2>Reserva tu lugar</h2>
                    <p class="detail-card__price"><?= $formatPrice($price); ?></p>
                    <?php if (isset($price['per']) && is_string($price['per'])): ?>
                        <p class="detail-card__meta"><?= $escape($price['per']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($price['notes']) && is_string($price['notes'])): ?>
                        <p class="detail-card__notes"><?= $escape($price['notes']); ?></p>
                    <?php endif; ?>
                    <a class="detail-button detail-button--gradient" href="autenticacion.php">Iniciar reserva</a>
                </section>
            <?php endif; ?>

            <?php if ($contact !== []): ?>
                <section class="detail-card detail-card--contact">
                    <h2>Contacta a un especialista</h2>
                    <?php if (isset($contact['agent']) && is_string($contact['agent'])): ?>
                        <p class="detail-card__lead"><?= $escape($contact['agent']); ?></p>
                    <?php endif; ?>
                    <ul class="detail-contact-list">
                        <?php if (isset($contact['email']) && is_string($contact['email'])): ?>
                            <li><span>Correo:</span> <a href="mailto:<?= $escape($contact['email']); ?>"><?= $escape($contact['email']); ?></a></li>
                        <?php endif; ?>
                        <?php if (isset($contact['phone']) && is_string($contact['phone'])):
                            $phone = preg_replace('/[^\d+]/', '', $contact['phone']);
                            ?>
                            <li><span>Teléfono:</span> <a href="tel:<?= $escape($phone); ?>"><?= $escape($contact['phone']); ?></a></li>
                        <?php endif; ?>
                        <?php if (isset($contact['hours']) && is_string($contact['hours'])): ?>
                            <li><span>Horario:</span> <?= $escape($contact['hours']); ?></li>
                        <?php endif; ?>
                    </ul>
                    <a class="detail-button detail-button--ghost" href="perfil.php">Hablar con nosotros</a>
                </section>
            <?php endif; ?>

            <section class="detail-card detail-card--tips">
                <h2>Tips rápidos</h2>
                <ul>
                    <li>Empaca ropa ligera, impermeable y abrigo para las noches.</li>
                    <li>Hidrátate constantemente y respeta las indicaciones del guía.</li>
                    <li>Apoya a emprendedores locales comprando productos artesanales.</li>
                </ul>
            </section>
        </aside>
    </main>

    <footer class="detail-footer">
        <p>© <?= date('Y'); ?> Expediatravels · Diseñamos viajes memorables en la selva central.</p>
    </footer>
</body>
</html>
