<?php
$detail = is_array($detail ?? null) ? $detail : [];
$siteSettings = is_array($siteSettings ?? null) ? $siteSettings : [];

$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}

$title = trim((string) ($detail['title'] ?? ($detail['nombre'] ?? '')));
if ($title === '') {
    $title = 'Circuito sin nombre';
}

$typeLabel = trim((string) ($detail['type'] ?? ($detail['type_tag'] ?? ($detail['categoria'] ?? 'Circuito'))));
if ($typeLabel === '') {
    $typeLabel = 'Circuito';
}

$tagline = trim((string) ($detail['tagline'] ?? ($detail['resumen'] ?? '')));

$heroImage = trim((string) ($detail['heroImage'] ?? ($detail['imagen'] ?? '')));
if ($heroImage === '') {
    $heroImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80';
}

$galleryRaw = $detail['gallery'] ?? ($detail['galeria'] ?? []);
if (is_string($galleryRaw) && trim($galleryRaw) !== '') {
    $decodedGallery = json_decode($galleryRaw, true);
    if (is_array($decodedGallery)) {
        $galleryRaw = $decodedGallery;
    } else {
        $galleryRaw = preg_split("/\r\n|\r|\n/", trim($galleryRaw)) ?: [];
    }
}
if (!is_array($galleryRaw)) {
    $galleryRaw = [];
}

$galleryImages = [];
foreach ($galleryRaw as $galleryItem) {
    if (is_string($galleryItem)) {
        $src = trim($galleryItem);
        if ($src === '') {
            continue;
        }
        $galleryImages[] = [
            'src' => $src,
            'alt' => $title,
        ];
        continue;
    }

    if (!is_array($galleryItem)) {
        continue;
    }

    $src = $galleryItem['src'] ?? $galleryItem['url'] ?? $galleryItem['image'] ?? null;
    if (is_string($src)) {
        $src = trim($src);
    } else {
        $src = '';
    }
    if ($src === '') {
        continue;
    }

    $alt = $galleryItem['alt'] ?? $galleryItem['label'] ?? $galleryItem['title'] ?? '';
    if (!is_string($alt)) {
        $alt = '';
    }
    $alt = trim($alt);
    if ($alt === '') {
        $alt = $title;
    }

    $galleryImages[] = [
        'src' => $src,
        'alt' => $alt,
    ];
}

if (empty($galleryImages) && $heroImage !== '') {
    $galleryImages[] = [
        'src' => $heroImage,
        'alt' => $title,
    ];
}

$locationParts = [
    trim((string) ($detail['location'] ?? ($detail['destino'] ?? ''))),
    trim((string) ($detail['region'] ?? ($detail['pais'] ?? ''))),
];
$locationParts = array_values(array_filter($locationParts, static fn ($value) => $value !== ''));
$location = implode(', ', array_unique($locationParts));

$ratingValue = null;
foreach (['rating', 'valoracion', 'score', 'averageRating'] as $ratingKey) {
    if (isset($detail[$ratingKey]) && is_numeric($detail[$ratingKey])) {
        $ratingValue = round((float) $detail[$ratingKey], 1);
        break;
    }
}
if ($ratingValue === null) {
    $ratingValue = 4.9;
}

$reviewsCount = 0;
foreach (['reviews', 'totalResenas', 'reviewsCount', 'cantidad_resenas'] as $reviewsKey) {
    if (isset($detail[$reviewsKey]) && is_numeric($detail[$reviewsKey])) {
        $reviewsCount = (int) $detail[$reviewsKey];
        break;
    }
}
if ($reviewsCount <= 0) {
    $reviewsCount = 128;
}

$duration = trim((string) ($detail['duration'] ?? ($detail['duracion'] ?? '3 días / 2 noches')));
if ($duration === '') {
    $duration = '3 días / 2 noches';
}

$tourType = trim((string) ($detail['tourType'] ?? ($detail['tipo'] ?? $typeLabel)));
if ($tourType === '') {
    $tourType = $typeLabel;
}

$groupSize = trim((string) ($detail['group'] ?? ($detail['tamano_grupo'] ?? ($detail['grupo_maximo'] ?? 'Hasta 12 viajeros'))));
if ($groupSize === '') {
    $groupSize = 'Hasta 12 viajeros';
}

$normalizeList = static function ($value): array {
    if (is_array($value)) {
        $items = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $text = trim($item);
                if ($text !== '') {
                    $items[] = $text;
                }
                continue;
            }
            if (!is_array($item)) {
                continue;
            }
            $text = trim((string) ($item['text'] ?? ($item['label'] ?? ($item['title'] ?? ''))));
            if ($text === '') {
                continue;
            }
            $items[] = $text;
        }
        return $items;
    }

    if (is_string($value) && trim($value) !== '') {
        $parts = preg_split('/\r\n|\r|\n|[,;]+/', trim($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$languagesList = $normalizeList($detail['languages'] ?? ($detail['idiomas'] ?? null));
if (empty($languagesList)) {
    $languagesList = ['Español', 'Inglés'];
}

$summaryRaw = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$summaryText = is_string($summaryRaw) ? trim($summaryRaw) : '';
if ($summaryText === '') {
    $summaryText = 'Explora paisajes emblemáticos acompañado por guías expertos que combinan aventura, cultura y confort en cada momento del recorrido.';
}
$aboutParagraphs = preg_split('/\n\s*\n/', $summaryText) ?: [];
$aboutParagraphs = array_values(array_filter(array_map('trim', $aboutParagraphs), static fn ($paragraph) => $paragraph !== ''));
if (empty($aboutParagraphs)) {
    $aboutParagraphs = [$summaryText];
}

$highlightsRaw = $detail['highlights'] ?? [];
if (!is_array($highlightsRaw)) {
    $highlightsRaw = $normalizeList($highlightsRaw);
}
$highlights = [];
foreach ($highlightsRaw as $highlight) {
    if (is_string($highlight)) {
        $text = trim($highlight);
        if ($text !== '') {
            $highlights[] = $text;
        }
        continue;
    }
    if (!is_array($highlight)) {
        continue;
    }
    $text = trim((string) ($highlight['title'] ?? ($highlight['label'] ?? ($highlight['summary'] ?? ''))));
    if ($text !== '') {
        $highlights[] = $text;
    }
}
if (empty($highlights)) {
    $highlights = [
        'Recorridos exclusivos por los paisajes más icónicos.',
        'Guías certificados con amplia experiencia local.',
        'Transporte premium y cómodas paradas fotográficas.',
        'Actividades seleccionadas para todos los gustos.',
    ];
}

$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$includes = [];
$excludes = [];
foreach ($essentials as $essential) {
    if (!is_array($essential)) {
        continue;
    }
    $titleEssential = strtolower(trim((string) ($essential['title'] ?? '')));
    $itemsEssential = $normalizeList($essential['items'] ?? []);
    if (empty($itemsEssential)) {
        continue;
    }
    if (str_contains($titleEssential, 'no incluye') || str_contains($titleEssential, 'exclu')) {
        $excludes = array_merge($excludes, $itemsEssential);
    } elseif (str_contains($titleEssential, 'inclu')) {
        $includes = array_merge($includes, $itemsEssential);
    }
}
if (empty($includes)) {
    $includes = $normalizeList($detail['included'] ?? ($detail['incluye'] ?? []));
}
if (empty($excludes)) {
    $excludes = $normalizeList($detail['excluded'] ?? ($detail['no_incluye'] ?? []));
}
if (empty($includes)) {
    $includes = ['Transporte turístico de lujo', 'Guía certificado bilingüe', 'Entradas a los parques indicados', 'Snacks y bebidas a bordo'];
}
if (empty($excludes)) {
    $excludes = ['Gastos personales y souvenirs', 'Propinas opcionales', 'Seguro de viaje'];
}

$itineraryRaw = $detail['itinerary'] ?? ($detail['itinerario'] ?? []);
if (!is_array($itineraryRaw)) {
    $itineraryRaw = [];
}
$itineraryDays = [];
foreach ($itineraryRaw as $index => $day) {
    if (is_string($day)) {
        $titleDay = trim($day);
        if ($titleDay === '') {
            continue;
        }
        $itineraryDays[] = [
            'title' => $titleDay,
            'description' => '',
            'mapUrl' => '',
        ];
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['description'] ?? ($day['descripcion'] ?? ''))));
    $mapUrl = trim((string) ($day['map_url'] ?? ($day['map'] ?? ($day['maps'] ?? ($day['ubicacion_maps'] ?? '')))));
    if ($mapUrl !== '' && !preg_match('/^https?:\/\//i', $mapUrl)) {
        $mapUrl = '';
    }
    if ($titleDay === '') {
        $titleDay = 'Experiencia destacada';
    }
    $itineraryDays[] = [
        'title' => $titleDay,
        'description' => $summaryDay,
        'mapUrl' => $mapUrl,
    ];
}
if (empty($itineraryDays)) {
    $itineraryDays = [
        ['title' => 'Bienvenida y exploración urbana', 'description' => 'Recorrido panorámico por los principales atractivos con tiempo libre para fotografía y degustaciones locales.', 'mapUrl' => ''],
        ['title' => 'Aventuras al aire libre', 'description' => 'Caminatas guiadas por senderos icónicos y visitas a miradores exclusivos.', 'mapUrl' => ''],
        ['title' => 'Experiencia cultural y despedida', 'description' => 'Intercambio cultural con comunidades locales y almuerzo de despedida.', 'mapUrl' => ''],
    ];
}

$faqRaw = $detail['faq'] ?? ($detail['preguntasFrecuentes'] ?? ($detail['preguntas_frecuentes'] ?? []));
if (!is_array($faqRaw)) {
    $faqRaw = [];
}
$faqItems = [];
foreach ($faqRaw as $faq) {
    if (is_string($faq)) {
        $question = trim($faq);
        if ($question !== '') {
            $faqItems[] = [
                'question' => $question,
                'answer' => 'Nuestro equipo te brindará la información que necesitas para planificar tu viaje.',
            ];
        }
        continue;
    }
    if (!is_array($faq)) {
        continue;
    }
    $question = trim((string) ($faq['question'] ?? ($faq['pregunta'] ?? '')));
    $answer = trim((string) ($faq['answer'] ?? ($faq['respuesta'] ?? '')));
    if ($question === '') {
        continue;
    }
    if ($answer === '') {
        $answer = 'Responderemos personalmente cada detalle para que disfrutes del circuito sin preocupaciones.';
    }
    $faqItems[] = ['question' => $question, 'answer' => $answer];
}
if (empty($faqItems)) {
    $faqItems = [
        [
            'question' => '¿Cuál es la mejor época para realizar este circuito?',
            'answer' => 'La temporada seca ofrece cielos despejados y temperaturas agradables, ideales para disfrutar cada actividad del itinerario.',
        ],
        [
            'question' => '¿El transporte está incluido durante todo el recorrido?',
            'answer' => 'Sí, contamos con movilidad privada, conductores experimentados y paradas estratégicas para tu comodidad.',
        ],
        [
            'question' => '¿Puedo personalizar algunas actividades?',
            'answer' => 'Podemos adaptar experiencias según tus intereses con aviso previo. Escríbenos para ayudarte.',
        ],
    ];
}

$priceCandidates = [
    $detail['price_from'] ?? null,
    $detail['priceFrom'] ?? null,
    $detail['precio_desde'] ?? null,
    $detail['precio'] ?? null,
];
$priceFrom = null;
foreach ($priceCandidates as $candidate) {
    if ($candidate === null || $candidate === '') {
        continue;
    }
    if (is_numeric($candidate)) {
        $priceFrom = 'S/ ' . number_format((float) $candidate, 2, '.', '');
        break;
    }
    if (is_string($candidate)) {
        $normalized = trim($candidate);
        if ($normalized !== '') {
            $digits = preg_replace('/[^0-9,.]/', '', $normalized) ?? '';
            if ($digits !== '') {
                $digits = str_replace(',', '', $digits);
                if (is_numeric($digits)) {
                    $priceFrom = 'S/ ' . number_format((float) $digits, 2, '.', '');
                    break;
                }
            }
            $priceFrom = $normalized;
            break;
        }
    }
}
if ($priceFrom === null) {
    $priceFrom = 'S/ 1,299.00';
}

$bookingUrlRaw = $detail['booking_url'] ?? ($detail['bookingUrl'] ?? null);
$bookingUrl = is_string($bookingUrlRaw) ? trim($bookingUrlRaw) : '';
if ($bookingUrl === '') {
    $bookingUrl = null;
}

$departuresRaw = $detail['departures'] ?? ($detail['fechas'] ?? null);
$departureOptions = $normalizeList($departuresRaw);
if (empty($departureOptions)) {
    $departureOptions = ['Selecciona una fecha', 'Próximo sábado', 'Próximo miércoles'];
} else {
    array_unshift($departureOptions, 'Selecciona una fecha');
}

$guideData = is_array($detail['guide'] ?? null) ? $detail['guide'] : ($detail['guia'] ?? []);
if (!is_array($guideData)) {
    $guideData = [];
}

$guideAvatar = trim((string) ($guideData['avatar'] ?? ($guideData['foto'] ?? '')));
if ($guideAvatar === '') {
    $guideAvatar = 'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=320&q=80';
}

$contactSettings = is_array($siteSettings['contact'] ?? null) ? $siteSettings['contact'] : [];
$contactEmail = trim((string) ($contactSettings['email'] ?? ($contactSettings['correo'] ?? ($siteSettings['contactEmail'] ?? 'hola@expediatravels.pe'))));
if ($contactEmail === '') {
    $contactEmail = 'hola@expediatravels.pe';
}
$contactPhone = trim((string) ($contactSettings['phone'] ?? ($contactSettings['telefono'] ?? '+51 999 888 777')));
if ($contactPhone === '') {
    $contactPhone = '+51 999 888 777';
}
$contactWebsite = trim((string) ($contactSettings['website'] ?? 'www.expediatravels.pe'));
if ($contactWebsite === '') {
    $contactWebsite = 'www.expediatravels.pe';
}
$contactFax = trim((string) ($contactSettings['fax'] ?? '—'));
if ($contactFax === '') {
    $contactFax = '—';
}

$languagesBadges = array_map(static fn ($language) => ['label' => $language], $languagesList);

$durationBadges = [
    ['icon' => '🕒', 'label' => '3 – 5 horas'],
    ['icon' => '🧭', 'label' => '5 – 7 horas'],
    ['icon' => '🌄', 'label' => 'Jornada completa'],
];

$pageTitle = $title . ' — ' . $siteTitle;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
    <link rel="stylesheet" href="estilos/circuito.css" />
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
</head>
<body class="page page--detail page--circuit">
    <?php $activeNav = 'circuitos'; include __DIR__ . '/partials/site-header.php'; ?>

    <main class="tour-detail">
        <section class="tour-banner" style="--banner-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>');">
            <div class="tour-banner__overlay" aria-hidden="true"></div>
            <div class="tour-banner__content">
                <span class="tour-banner__pill"><?= htmlspecialchars($typeLabel); ?></span>
                <h1><?= htmlspecialchars($title); ?></h1>
                <?php if ($tagline !== ''): ?>
                    <p class="tour-banner__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <div class="tour-banner__meta">
                    <span class="tour-banner__meta-item" aria-label="Valoración">
                        <span class="tour-banner__icon" aria-hidden="true">⭐</span>
                        <?= htmlspecialchars(number_format($ratingValue, 1, '.', '')); ?> · <?= htmlspecialchars($reviewsCount); ?> reseñas
                    </span>
                    <?php if ($location !== ''): ?>
                        <span class="tour-banner__meta-item" aria-label="Ubicación">
                            <span class="tour-banner__icon" aria-hidden="true">📍</span>
                            <?= htmlspecialchars($location); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="tour-banner__info">
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">⏱️</span>
                        <div>
                            <p>Duración</p>
                            <strong><?= htmlspecialchars($duration); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">🧭</span>
                        <div>
                            <p>Tipo de circuito</p>
                            <strong><?= htmlspecialchars($tourType); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">👥</span>
                        <div>
                            <p>Tamaño del grupo</p>
                            <strong><?= htmlspecialchars($groupSize); ?></strong>
                        </div>
                    </article>
                    <article class="tour-banner__info-item">
                        <span class="tour-banner__info-icon" aria-hidden="true">💬</span>
                        <div>
                            <p>Idiomas</p>
                            <strong><?= htmlspecialchars(implode(', ', $languagesList)); ?></strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <div class="tour-detail__layout">
            <div class="tour-detail__left">

                            <section class="detail-section detail-section--about" id="about">
                    <header>
                        <h2>Sobre este circuito</h2>
                    </header>
                    <?php foreach ($aboutParagraphs as $paragraph): ?>
                        <p><?= htmlspecialchars($paragraph); ?></p>
                    <?php endforeach; ?>
                </section>

                

                <?php if (!empty($galleryImages)): ?>
                    <section class="detail-section detail-section--gallery" id="galeria">
                        <header>
                            <h2>Galería del circuito</h2>
                            <p>Explora una selección de momentos destacados de esta experiencia.</p>
                        </header>
                        <div class="detail-gallery">
                            <?php foreach ($galleryImages as $image):
                                $src = $image['src'] ?? '';
                                if ($src === '') {
                                    continue;
                                }
                                $alt = $image['alt'] ?? $title;
                                if (!is_string($alt) || trim($alt) === '') {
                                    $alt = $title;
                                }
                                $caption = trim($alt);
                            ?>
                                <figure class="detail-gallery__item">
                                    <img class="detail-gallery__image" src="<?= htmlspecialchars($src, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($alt); ?>" loading="lazy" />
                                    <?php if ($caption !== ''): ?>
                                        <figcaption class="detail-gallery__caption"><?= htmlspecialchars($caption); ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>


                            <section class="detail-section" id="itinerary">
                    <header>
                        <h2>Itinerario</h2>
                    </header>
                    <div class="accordion" data-accordion="itinerary">
                        <?php foreach ($itineraryDays as $index => $day): ?>
                            <?php $isOpen = $index === 0; ?>
                            <article class="accordion__item<?= $isOpen ? ' is-open' : ''; ?>" data-accordion-item>
                                <button type="button" class="accordion__trigger" data-accordion-trigger aria-expanded="<?= $isOpen ? 'true' : 'false'; ?>">
                                    <span class="accordion__day">Día <?= $index + 1; ?></span>
                                    <span class="accordion__title"><?= htmlspecialchars($day['title']); ?></span>
                                    <span class="accordion__icon" aria-hidden="true"></span>
                                </button>
                                <div class="accordion__content" data-accordion-content<?= $isOpen ? '' : ' hidden'; ?>>
                                    <?php $descriptionText = $day['description'] !== '' ? $day['description'] : 'Descubre actividades seleccionadas para este día del circuito.'; ?>
                                    <p><?= htmlspecialchars($descriptionText); ?></p>
                                    <?php if (!empty($day['mapUrl'])): ?>
                                        <a class="accordion__map-link" href="<?= htmlspecialchars($day['mapUrl'], ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer">
                                            <span aria-hidden="true">📍</span>
                                            <span>Ubicación en Google Maps</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>


                <section class="detail-section" id="highlights">
                    <header>
                        <h2>Puntos destacados</h2>
                    </header>
                    <ul class="highlight-list">
                        <?php foreach ($highlights as $highlight): ?>
                            <li><?= htmlspecialchars($highlight); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <section class="detail-section detail-section--split" id="included">
                    <header>
                        <h2>Incluye / No incluye</h2>
                    </header>
                    <div class="split-columns">
                        <div class="split-columns__item">
                            <h3>Incluye</h3>
                            <ul>
                                <?php foreach ($includes as $item): ?>
                                    <li><span class="split-columns__icon" aria-hidden="true">✔</span><?= htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="split-columns__item">
                            <h3>No incluye</h3>
                            <ul>
                                <?php foreach ($excludes as $item): ?>
                                    <li><span class="split-columns__icon split-columns__icon--negative" aria-hidden="true">✘</span><?= htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>




                
            </div>

            <aside class="tour-detail__right">
                <section class="aside-card aside-card--booking">
                    <div class="booking-header">
                        <span class="booking-price"><?= htmlspecialchars($priceFrom); ?></span>
                        <span class="booking-rating">
                            <span aria-hidden="true">⭐</span>
                            <?= htmlspecialchars(number_format($ratingValue, 1, '.', '')); ?> · <?= htmlspecialchars($reviewsCount); ?> reseñas
                        </span>
                    </div>
                    <form class="booking-form" action="<?= $bookingUrl ? htmlspecialchars($bookingUrl, ENT_QUOTES) : '#'; ?>" method="get">
                        <label class="booking-field">
                            <span>Fecha</span>
                            <select name="date" <?= $bookingUrl ? '' : 'disabled'; ?>>
                                <?php foreach ($departureOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option, ENT_QUOTES); ?>"><?= htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="booking-grid">
                            <?php $travellers = [
                                ['label' => 'Adultos', 'name' => 'adults', 'min' => 1],
                                ['label' => 'Niños', 'name' => 'children', 'min' => 0],
                                ['label' => 'Infantes', 'name' => 'infant', 'min' => 0],
                            ]; ?>
                            <?php foreach ($travellers as $traveller): ?>
                                <div class="booking-counter" data-counter>
                                    <span><?= htmlspecialchars($traveller['label']); ?></span>
                                    <div class="booking-counter__controls">
                                        <button type="button" class="booking-counter__btn" data-counter-decrease aria-label="Restar">
                                            −
                                        </button>
                                        <input type="number" name="<?= htmlspecialchars($traveller['name'], ENT_QUOTES); ?>" value="<?= $traveller['min']; ?>" min="<?= $traveller['min']; ?>" readonly />
                                        <button type="button" class="booking-counter__btn" data-counter-increase aria-label="Sumar">
                                            +
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="booking-submit" <?= $bookingUrl ? '' : 'disabled'; ?>>Reservar ahora</button>
                    </form>
                </section>


                <section class="aside-card aside-card--contact">
                    <h3>Información de contacto</h3>
                    <ul>
                        <li><span aria-hidden="true">✉️</span> <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES); ?>"><?= htmlspecialchars($contactEmail); ?></a></li>
                        <li><span aria-hidden="true">🌐</span> <a href="https://<?= htmlspecialchars(ltrim($contactWebsite, 'https://'), ENT_QUOTES); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($contactWebsite); ?></a></li>
                        <li><span aria-hidden="true">📞</span> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone), ENT_QUOTES); ?>"><?= htmlspecialchars($contactPhone); ?></a></li>
                        <li><span aria-hidden="true">📠</span> <?= htmlspecialchars($contactFax); ?></li>
                    </ul>
                </section>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>

    <script src="scripts/circuito.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
