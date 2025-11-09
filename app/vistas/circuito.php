<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}
$title = $detail['title'] ?? ($detail['nombre'] ?? 'Circuito turístico');
$pageTitle = $title;
$typeLabel = $detail['type'] ?? ($detail['categoria'] ?? 'Circuito');
$tagline = $detail['tagline'] ?? ($detail['resumen'] ?? '');
$summary = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$heroImage = $detail['heroImage'] ?? ($detail['imagen'] ?? '');
$mapImage = $detail['mapImage'] ?? '';
$mapLabel = $detail['mapLabel'] ?? ($detail['location'] ?? 'Mapa de referencia');
$location = $detail['location'] ?? ($detail['destino'] ?? '');
$region = $detail['region'] ?? '';
if ($region !== '' && $location !== '') {
    $location = $location . ' — ' . $region;
} elseif ($location === '' && $region !== '') {
    $location = $region;
}
$chips = is_array($detail['chips'] ?? null) ? array_values(array_filter($detail['chips'])) : [];
$stats = is_array($detail['stats'] ?? null) ? $detail['stats'] : [];
$highlights = is_array($detail['highlights'] ?? null) ? $detail['highlights'] : [];
$itinerary = $detail['itinerary'] ?? [];
if (empty($itinerary) && !empty($detail['itinerary_detallado'])) {
    $itinerary = $detail['itinerary_detallado'];
}
$experiences = is_array($detail['experiences'] ?? null) ? $detail['experiences'] : [];
$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$related = is_array($detail['related'] ?? null) ? $detail['related'] : [];
$services = is_array($detail['servicios'] ?? null) ? $detail['servicios'] : [];
$gallery = is_array($detail['gallery'] ?? null) ? $detail['gallery'] : [];

$duration = $detail['duration'] ?? ($detail['duracion'] ?? '');
$frequency = $detail['frecuencia'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? ''));
$group = $detail['grupo'] ?? ($detail['grupo_maximo'] ?? '');
$experienceLevel = $detail['experiencia'] ?? ($detail['dificultad'] ?? '');
$rating = $detail['ratingPromedio'] ?? ($detail['rating'] ?? null);
if (is_numeric($rating)) {
    $rating = round((float) $rating, 1);
} else {
    $rating = null;
}
$reviews = $detail['totalResenas'] ?? ($detail['reviews'] ?? null);
if (is_numeric($reviews)) {
    $reviews = (int) $reviews;
} else {
    $reviews = null;
}

$priceFrom = $detail['priceFrom'] ?? ($detail['price_from'] ?? '');
if (is_string($priceFrom)) {
    $priceFrom = trim($priceFrom);
} else {
    $priceFrom = '';
}
$price = $detail['precio'] ?? ($detail['price'] ?? null);
$currency = strtoupper((string) ($detail['moneda'] ?? ($detail['currency'] ?? 'PEN')));
if ($priceFrom === '' && is_numeric($price)) {
    $symbol = match ($currency) {
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => 'S/',
    };
    $priceFrom = sprintf('Desde %s %s', $symbol, number_format((float) $price, 2, '.', ','));
}

$cta = is_array($detail['cta'] ?? null) ? $detail['cta'] : [];
$ctaPrimaryLabel = trim((string) ($cta['primaryLabel'] ?? ''));
$ctaPrimaryHref = trim((string) ($cta['primaryHref'] ?? ''));
$ctaSecondaryLabel = trim((string) ($cta['secondaryLabel'] ?? ''));
$ctaSecondaryHref = trim((string) ($cta['secondaryHref'] ?? ''));

$summaryParagraphs = [];
if (trim((string) $summary) !== '') {
    $summaryParagraphs = preg_split('/\n\s*\n/', trim((string) $summary)) ?: [];
}
$primarySummaryParagraph = $summaryParagraphs[0] ?? '';
$extraSummaryParagraphs = array_slice($summaryParagraphs, 1);

$normalizeList = static function ($value): array {
    if (is_array($value)) {
        return array_values(array_filter(array_map(static fn ($item) => is_string($item) ? trim($item) : null, $value), static fn ($item) => $item !== null && $item !== ''));
    }

    if (is_string($value) && trim($value) !== '') {
        $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$contact = $siteSettings['contact'] ?? [];
$contactPhones = $normalizeList($contact['phones'] ?? ($siteSettings['contactPhones'] ?? null));
$contactEmails = $normalizeList($contact['emails'] ?? ($siteSettings['contactEmails'] ?? null));
$contactAddresses = $normalizeList($contact['addresses'] ?? ($siteSettings['contactAddresses'] ?? null));
$contactLocations = $normalizeList($contact['locations'] ?? ($siteSettings['contactLocations'] ?? null));

$metaBadges = [];
if ($duration !== '') {
    $metaBadges[] = ['text' => '⏱️ ' . $duration];
}
if (!empty($chips)) {
    $metaBadges[] = ['text' => '🏷️ ' . $chips[0]];
} elseif ($typeLabel !== '') {
    $metaBadges[] = ['text' => '🏷️ ' . $typeLabel];
}
if ($location !== '') {
    $metaBadges[] = ['text' => '📍 ' . $location];
}
if ($priceFrom !== '') {
    $metaBadges[] = ['text' => '💸 ' . $priceFrom, 'variant' => 'price'];
}
if ($frequency !== '') {
    $metaBadges[] = ['text' => '🗓️ ' . $frequency];
}

$facts = [];
foreach ($stats as $stat) {
    $label = isset($stat['label']) ? trim((string) $stat['label']) : '';
    $value = isset($stat['value']) ? trim((string) $stat['value']) : '';
    if ($label === '' || $value === '') {
        continue;
    }
    $facts[$label] = $value;
}
if ($duration !== '') {
    $facts['Duración'] = $duration;
}
if ($experienceLevel !== '') {
    $facts['Nivel'] = $experienceLevel;
}
if ($frequency !== '') {
    $facts['Frecuencia'] = $frequency;
}
if ($group !== '') {
    $facts['Grupo'] = $group;
}
if ($location !== '') {
    $facts['Destino'] = $location;
}
$facts = array_slice(array_map(static fn ($label, $value) => ['label' => $label, 'value' => $value], array_keys($facts), $facts), 0, 6);

$locationsRaw = $detail['locations'] ?? ($detail['stops'] ?? ($detail['destinos'] ?? []));
if (is_string($locationsRaw)) {
    $locationsRaw = $normalizeList($locationsRaw);
}
$locationsList = [];
if (is_array($locationsRaw)) {
    foreach ($locationsRaw as $item) {
        if (is_string($item)) {
            $locationsList[] = [
                'title' => $item,
                'duration' => '',
                'image' => $heroImage,
            ];
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $titleLocation = trim((string) ($item['title'] ?? ($item['name'] ?? ($item['label'] ?? ''))));
        $durationLocation = trim((string) ($item['duration'] ?? ($item['dias'] ?? ($item['stay'] ?? ''))));
        $imageLocation = $item['image'] ?? ($item['photo'] ?? ($item['cover'] ?? $heroImage));
        if ($titleLocation === '') {
            continue;
        }
        $locationsList[] = [
            'title' => $titleLocation,
            'duration' => $durationLocation,
            'image' => $imageLocation,
        ];
    }
}

$notesRaw = $detail['notes'] ?? ($detail['notas'] ?? ($detail['observaciones'] ?? []));
if (is_string($notesRaw)) {
    $notesRaw = $normalizeList($notesRaw);
}
$notesList = [];
if (is_array($notesRaw)) {
    foreach ($notesRaw as $note) {
        $textNote = is_string($note) ? trim($note) : '';
        if ($textNote === '') {
            continue;
        }
        $notesList[] = $textNote;
    }
}
if (empty($notesList) && !empty($extraSummaryParagraphs)) {
    foreach ($extraSummaryParagraphs as $paragraph) {
        $trimmed = trim((string) $paragraph);
        if ($trimmed !== '') {
            $notesList[] = $trimmed;
        }
    }
}

$includesList = [];
$excludesList = [];
$otherEssentialSections = [];
foreach ($essentials as $essential) {
    if (!is_array($essential)) {
        continue;
    }
    $sectionTitle = strtolower(trim((string) ($essential['title'] ?? '')));
    $items = $normalizeList($essential['items'] ?? []);
    if (empty($items)) {
        continue;
    }
    if (str_contains($sectionTitle, 'no incluye') || str_contains($sectionTitle, 'exclu')) {
        $excludesList = array_merge($excludesList, $items);
    } elseif (str_contains($sectionTitle, 'inclu')) {
        $includesList = array_merge($includesList, $items);
    } else {
        $otherEssentialSections[] = [
            'title' => $essential['title'] ?? '',
            'items' => $items,
        ];
    }
}
if (empty($includesList) && !empty($services)) {
    $includesList = $normalizeList($services);
}

$brochuresRaw = $detail['brochures'] ?? ($detail['brochure'] ?? []);
$brochures = [];
if (is_array($brochuresRaw)) {
    foreach ($brochuresRaw as $brochure) {
        if (is_string($brochure)) {
            $label = trim($brochure);
            if ($label === '') {
                continue;
            }
            $brochures[] = [
                'title' => $label,
                'description' => '',
                'href' => '#',
                'primary' => true,
            ];
            continue;
        }
        if (!is_array($brochure)) {
            continue;
        }
        $brochureTitle = trim((string) ($brochure['title'] ?? ($brochure['label'] ?? '')));
        $brochureDescription = trim((string) ($brochure['description'] ?? ($brochure['summary'] ?? '')));
        $brochureHref = trim((string) ($brochure['href'] ?? ($brochure['url'] ?? '#')));
        if ($brochureTitle === '') {
            continue;
        }
        $brochures[] = [
            'title' => $brochureTitle,
            'description' => $brochureDescription,
            'href' => $brochureHref !== '' ? $brochureHref : '#',
            'primary' => (bool) ($brochure['primary'] ?? false),
        ];
    }
}

$galleryItems = [];
foreach ($gallery as $image) {
    if (is_string($image)) {
        $src = trim($image);
        if ($src === '') {
            continue;
        }
        $galleryItems[] = [
            'src' => $src,
            'alt' => $title,
        ];
        continue;
    }
    if (!is_array($image)) {
        continue;
    }
    $src = trim((string) ($image['src'] ?? ($image['url'] ?? '')));
    if ($src === '') {
        continue;
    }
    $alt = trim((string) ($image['alt'] ?? ($image['label'] ?? $title)));
    $galleryItems[] = [
        'src' => $src,
        'alt' => $alt !== '' ? $alt : $title,
    ];
}

$relatedCards = [];
foreach ($related as $card) {
    if (!is_array($card)) {
        continue;
    }
    $cardTitle = trim((string) ($card['title'] ?? ''));
    $cardSummary = trim((string) ($card['summary'] ?? ($card['description'] ?? '')));
    if ($cardTitle === '' || $cardSummary === '') {
        continue;
    }
    $cardHref = trim((string) ($card['href'] ?? ($card['url'] ?? '#')));
    $cardBadge = trim((string) ($card['badge'] ?? ''));
    $cardImage = trim((string) ($card['image'] ?? ($card['cover'] ?? '')));
    $relatedCards[] = [
        'title' => $cardTitle,
        'summary' => $cardSummary,
        'href' => $cardHref !== '' ? $cardHref : '#',
        'badge' => $cardBadge,
        'image' => $cardImage,
    ];
}

$itineraryDays = [];
foreach ($itinerary as $index => $day) {
    if (is_string($day)) {
        $titleDay = trim($day);
        if ($titleDay === '') {
            continue;
        }
        $itineraryDays[] = [
            'title' => $titleDay,
            'summary' => '',
            'activities' => [],
            'meta' => [],
            'schedule' => '',
        ];
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['descripcion'] ?? '')));
    $schedule = trim((string) ($day['schedule'] ?? ($day['time'] ?? ($day['horario'] ?? ''))));
    $activitiesList = $normalizeList($day['activities'] ?? ($day['actividades'] ?? []));

    $metaEntries = [];
    $potentialMetaKeys = ['keyValues', 'key_values', 'kv', 'facts', 'details', 'info'];
    foreach ($potentialMetaKeys as $metaKey) {
        if (!isset($day[$metaKey])) {
            continue;
        }
        $rawMeta = $day[$metaKey];
        if (is_array($rawMeta)) {
            foreach ($rawMeta as $metaItemKey => $metaItemValue) {
                if (is_array($metaItemValue)) {
                    $labelMeta = trim((string) ($metaItemValue['label'] ?? ($metaItemValue['title'] ?? '')));
                    $valueMeta = trim((string) ($metaItemValue['value'] ?? ($metaItemValue['description'] ?? '')));
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                    continue;
                }
                if (is_string($metaItemValue)) {
                    $parts = explode(':', $metaItemValue, 2);
                    if (count($parts) === 2) {
                        $labelMeta = trim($parts[0]);
                        $valueMeta = trim($parts[1]);
                        if ($labelMeta !== '' && $valueMeta !== '') {
                            $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                        }
                        continue;
                    }
                    $valueMeta = trim($metaItemValue);
                    if ($valueMeta !== '') {
                        $metaEntries[] = ['label' => 'Detalle', 'value' => $valueMeta];
                    }
                    continue;
                }
                if (is_string($metaItemKey) && (is_scalar($metaItemValue) || $metaItemValue === null)) {
                    $labelMeta = trim($metaItemKey);
                    $valueMeta = trim((string) $metaItemValue);
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                }
            }
        } elseif (is_string($rawMeta)) {
            $parts = preg_split('/\r\n|\r|\n/', trim($rawMeta)) ?: [];
            foreach ($parts as $part) {
                $metaValue = trim($part);
                if ($metaValue === '') {
                    continue;
                }
                $split = explode(':', $metaValue, 2);
                if (count($split) === 2) {
                    $labelMeta = trim($split[0]);
                    $valueMeta = trim($split[1]);
                    if ($labelMeta !== '' && $valueMeta !== '') {
                        $metaEntries[] = ['label' => $labelMeta, 'value' => $valueMeta];
                    }
                } else {
                    $metaEntries[] = ['label' => 'Detalle', 'value' => $metaValue];
                }
            }
        }
    }

    $itineraryDays[] = [
        'title' => $titleDay !== '' ? $titleDay : ('Día ' . ($index + 1)),
        'summary' => $summaryDay,
        'activities' => $activitiesList,
        'meta' => $metaEntries,
        'schedule' => $schedule,
    ];
}

$ctaNavPrimaryLabel = $ctaPrimaryLabel !== '' ? $ctaPrimaryLabel : 'Reservar ahora';
$ctaNavPrimaryHref = $ctaPrimaryHref !== '' ? $ctaPrimaryHref : '#reserva';
$ctaNavSecondaryHref = '#itinerario';
$ctaNavSecondaryLabel = 'Ver itinerario';

$sidebarChecks = [];
if (!empty($services)) {
    $sidebarChecks = array_slice($normalizeList($services), 0, 3);
}
if (empty($sidebarChecks)) {
    $sidebarChecks = [
        'Arma tu paquete favorito',
        'Hoteles a tu elección',
        'Guía local verificada',
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <?php if ($siteFavicon): ?>
        <link rel="icon" href="<?= htmlspecialchars($siteFavicon, ENT_QUOTES); ?>" />
    <?php endif; ?>
    <style>
        :root{
          --brand:#0ea5e9;
          --brand-2:#22c55e;
          --ink:#0f172a;
          --muted:#64748b;
          --bg:#f8fafc;
          --card:#ffffff;
          --accent:#fde68a;
          --danger:#ef4444;
          --ok:#10b981;
          --shadow: 0 8px 24px rgba(2,8,23,.08);
          --radius: 16px;
        }
        *{box-sizing:border-box}
        html,body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.55 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial}
        img{max-width:100%;display:block}
        a{color:var(--brand);text-decoration:none}
        .container{width:min(1200px,92vw);margin:auto}
        .grid{display:grid;gap:20px}
        .btn{display:inline-flex;align-items:center;gap:.5rem;background:var(--brand);color:#fff;padding:.85rem 1.15rem;border-radius:999px;border:0;font-weight:600;box-shadow:var(--shadow);cursor:pointer}
        .btn.secondary{background:#fff;color:var(--brand);border:2px solid var(--brand)}
        .badge{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .65rem;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:600;font-size:.8rem}
        .badge--price{background:#dcfce7;color:#065f46}
        .card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow)}
        .nav{position:sticky;top:0;z-index:50;background:#ffffffcc;backdrop-filter:saturate(180%) blur(10px);border-bottom:1px solid #e5e7eb}
        .nav .container{display:flex;align-items:center;justify-content:space-between;padding:10px 0}
        .logo{display:flex;align-items:center;gap:.6rem;font-weight:800}
        .logo .dot{width:10px;height:10px;border-radius:50%;background:var(--brand)}
        .nav .actions{display:flex;gap:.6rem}
        .hero{position:relative;padding:32px 0 10px}
        .hero-wrap{display:grid;grid-template-columns:1.1fr .6fr;gap:24px}
        .hero-left{display:grid;gap:18px}
        .hero h1{margin:0;font-size:clamp(28px,4vw,42px);line-height:1.15}
        .hero .meta{display:flex;flex-wrap:wrap;gap:10px}
        .hero .banner{overflow:hidden;border-radius:var(--radius)}
        .hero .banner img{height:340px;width:100%;object-fit:cover}
        .hero .banner-placeholder{height:340px;width:100%;display:grid;place-items:center;background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0f172ab3;font-weight:600;border-radius:var(--radius)}
        .sidebar{position:sticky;top:84px;align-self:start}
        .sidebox{padding:18px}
        .sidebox h3{margin:0 0 10px}
        .check{display:flex;align-items:flex-start;gap:.65rem;margin:.5rem 0;color:#065f46}
        .check i{width:22px;height:22px;border-radius:50%;background:var(--ok);display:inline-grid;place-items:center;color:#fff;font-style:normal;font-size:.9rem}
        .facts{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
        .fact{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;display:grid;gap:6px}
        .fact small{color:var(--muted)}
        .content{display:grid;grid-template-columns:1fr .45fr;gap:26px;margin:26px 0 80px}
        section{scroll-margin-top:92px}
        .section{padding:18px 18px 8px}
        .section h2{margin:6px 0 14px;font-size:1.35rem}
        .locations{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px}
        .loc{border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff}
        .loc img{height:120px;width:100%;object-fit:cover}
        .loc .info{padding:10px 12px;display:flex;justify-content:space-between;align-items:center}
        .chip{background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700}
        .list{display:grid;gap:10px}
        .item{display:flex;gap:.8rem;align-items:flex-start;background:#fff;border:1px dashed #cbd5e1;padding:12px;border-radius:12px}
        .item i{color:#059669}
        .accordion{display:grid;gap:10px}
        .day{border:1px solid #e5e7eb;border-radius:12px;background:#fff;overflow:hidden}
        .day summary{list-style:none;cursor:pointer;padding:14px 16px;font-weight:700;display:flex;align-items:center;justify-content:space-between}
        .day summary::-webkit-details-marker{display:none}
        .day .body{padding:2px 16px 16px;display:grid;gap:10px}
        .splits{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
        .kv{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:10px}
        .kv b{display:block;margin-bottom:6px}
        .map-box{height:340px;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;background:linear-gradient(135deg,#e0f2fe,#fef9c3)}
        .map-box .placeholder{height:100%;display:grid;place-items:center;color:#0f172a80;font-weight:700}
        .features{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .featcol{background:#fff;border-radius:14px;border:1px solid #e5e7eb;padding:14px}
        .featcol h3{margin:0 0 8px}
        .ul{display:grid;gap:8px}
        .ul .ok{color:#059669}
        .ul .no{color:var(--danger)}
        .brochure{display:grid;gap:14px}
        .banner{display:grid;grid-template-columns:1fr auto;align-items:center;gap:18px;background:linear-gradient(90deg,#0ea5e9,#22c55e);color:white;padding:16px;border-radius:16px}
        .banner small{opacity:.9}
        .banner.secondary{background:linear-gradient(90deg,#22c55e,#0ea5e9)}
        .notes{display:grid;gap:8px;padding-left:1.1rem}
        .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
        .gallery-grid img{height:160px;width:100%;object-fit:cover;border-radius:12px}
        .related{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px}
        .related-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;display:grid}
        .related-card__media img{height:150px;width:100%;object-fit:cover}
        .related-card__body{padding:16px;display:grid;gap:8px}
        .related-card__badge{font-size:.75rem;font-weight:700;color:#1d4ed8;background:#eff6ff;align-self:start;padding:2px 8px;border-radius:999px}
        footer{margin:50px 0 40px;color:var(--muted);text-align:center}
        @media (max-width: 1024px){
          .hero-wrap{grid-template-columns:1fr}
          .content{grid-template-columns:1fr}
          .facts{grid-template-columns:repeat(2,1fr)}
          .splits{grid-template-columns:repeat(2,1fr)}
        }
        @media (max-width: 560px){
          .features{grid-template-columns:1fr}
          .facts{grid-template-columns:1fr}
          .locations{grid-template-columns:repeat(2,1fr)}
          .hero .banner img,.hero .banner-placeholder{height:220px}
        }
    </style>
</head>
<body>
  <!-- NAV -->
  <nav class="nav">
    <div class="container">
      <div class="logo"><span class="dot"></span> <?= htmlspecialchars($siteTitle); ?> <span style="color:var(--muted);font-weight:600">/ <?= htmlspecialchars($typeLabel); ?></span></div>
      <div class="actions">
        <a href="<?= htmlspecialchars($ctaNavSecondaryHref); ?>" class="btn secondary"><?= htmlspecialchars($ctaNavSecondaryLabel); ?></a>
        <a href="<?= htmlspecialchars($ctaNavPrimaryHref, ENT_QUOTES); ?>" class="btn"><?= htmlspecialchars($ctaNavPrimaryLabel); ?></a>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="hero container">
    <div class="hero-wrap">
      <div class="hero-left">
        <h1><?= htmlspecialchars($title); ?></h1>
        <?php if (!empty($metaBadges)): ?>
          <div class="meta">
            <?php foreach ($metaBadges as $badge): ?>
              <?php $badgeClasses = 'badge' . (!empty($badge['variant']) && $badge['variant'] === 'price' ? ' badge--price' : ''); ?>
              <span class="<?= $badgeClasses; ?>"><?= htmlspecialchars($badge['text']); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($rating !== null || $reviews !== null): ?>
          <div style="display:flex;flex-wrap:wrap;gap:12px;color:var(--muted);font-weight:600">
            <?php if ($rating !== null): ?>
              <span>★ <?= htmlspecialchars(number_format($rating, 1)); ?></span>
            <?php endif; ?>
            <?php if ($reviews !== null): ?>
              <span><?= htmlspecialchars((string) $reviews); ?> opiniones</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if ($tagline !== ''): ?>
          <p style="margin:0;color:var(--muted)"><?= htmlspecialchars($tagline); ?></p>
        <?php endif; ?>
        <?php if ($primarySummaryParagraph !== ''): ?>
          <p style="margin:0;"><?= htmlspecialchars($primarySummaryParagraph); ?></p>
        <?php endif; ?>

        <div class="banner card">
          <?php if ($heroImage !== ''): ?>
            <img src="<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($title); ?>" loading="lazy" />
          <?php else: ?>
            <div class="banner-placeholder">Imagen del circuito próximamente</div>
          <?php endif; ?>
        </div>

        <?php if (!empty($facts)): ?>
          <div class="facts">
            <?php foreach ($facts as $fact): ?>
              <div class="fact">
                <small><?= htmlspecialchars($fact['label']); ?></small>
                <strong><?= htmlspecialchars($fact['value']); ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <aside class="sidebar">
        <div class="sidebox card">
          <h3>¡Personaliza tu viaje!</h3>
          <?php foreach ($sidebarChecks as $check): ?>
            <div class="check"><i>✓</i><div><?= htmlspecialchars($check); ?></div></div>
          <?php endforeach; ?>
          <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
            <a class="btn" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaSecondaryLabel); ?></a>
          <?php elseif ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
            <a class="btn" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaPrimaryLabel); ?></a>
          <?php else: ?>
            <a class="btn" href="#reserva">Consulta disponibilidad</a>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </header>

  <!-- CONTENT -->
  <main class="container content">
    <div class="grid">
      <?php if (!empty($locationsList)): ?>
        <section id="lugares" class="card section">
          <h2>Explora los lugares clave</h2>
          <div class="locations">
            <?php foreach ($locationsList as $loc): ?>
              <article class="loc">
                <?php if (!empty($loc['image'])): ?>
                  <img src="<?= htmlspecialchars($loc['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($loc['title']); ?>" loading="lazy" />
                <?php endif; ?>
                <div class="info">
                  <strong><?= htmlspecialchars($loc['title']); ?></strong>
                  <?php if (!empty($loc['duration'])): ?>
                    <span class="chip"><?= htmlspecialchars($loc['duration']); ?></span>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($highlights)): ?>
        <section id="destacados" class="card section">
          <h2>Momentos imperdibles del circuito</h2>
          <div class="list">
            <?php foreach ($highlights as $highlight):
                $titleHighlight = trim((string) ($highlight['title'] ?? ''));
                $descriptionHighlight = trim((string) ($highlight['description'] ?? ''));
                if ($titleHighlight === '' || $descriptionHighlight === '') {
                    continue;
                }
                $icon = trim((string) ($highlight['icon'] ?? '✔'));
            ?>
              <div class="item"><i><?= htmlspecialchars($icon); ?></i><div><strong><?= htmlspecialchars($titleHighlight); ?></strong> — <?= htmlspecialchars($descriptionHighlight); ?></div></div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($itineraryDays)): ?>
        <section id="itinerario" class="card section">
          <h2>Itinerario sugerido</h2>
          <div class="accordion">
            <?php foreach ($itineraryDays as $index => $day): ?>
              <details class="day"<?= $index === 0 ? ' open' : ''; ?>>
                <summary>
                  <?= htmlspecialchars($day['title']); ?>
                  <?php if ($day['schedule'] !== ''): ?>
                    <span style="color:var(--muted);font-weight:500"><?= htmlspecialchars($day['schedule']); ?></span>
                  <?php endif; ?>
                </summary>
                <div class="body">
                  <?php if ($day['summary'] !== ''): ?>
                    <p><?= htmlspecialchars($day['summary']); ?></p>
                  <?php endif; ?>
                  <?php if (!empty($day['activities'])): ?>
                    <ul>
                      <?php foreach ($day['activities'] as $activity): ?>
                        <li><?= htmlspecialchars($activity); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                  <?php if (!empty($day['meta'])): ?>
                    <div class="splits">
                      <?php foreach ($day['meta'] as $meta): ?>
                        <div class="kv"><b><?= htmlspecialchars($meta['label']); ?></b> <?= htmlspecialchars($meta['value']); ?></div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </details>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <section id="mapa" class="card section">
        <h2>Mapa del circuito</h2>
        <div class="map-box">
          <?php if ($mapImage !== ''): ?>
            <img src="<?= htmlspecialchars($mapImage, ENT_QUOTES); ?>" alt="<?= htmlspecialchars($mapLabel); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover" />
          <?php else: ?>
            <div class="placeholder">Mapa de referencia próximamente</div>
          <?php endif; ?>
        </div>
      </section>

      <?php if (!empty($includesList) || !empty($excludesList)): ?>
        <section id="caracteristicas" class="section" style="padding:0;gap:0">
          <div class="features">
            <?php if (!empty($includesList)): ?>
              <div class="featcol">
                <h3>Incluye</h3>
                <div class="ul">
                  <?php foreach ($includesList as $item): ?>
                    <div class="ok">✅ <?= htmlspecialchars($item); ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if (!empty($excludesList)): ?>
              <div class="featcol">
                <h3>No incluye</h3>
                <div class="ul">
                  <?php foreach ($excludesList as $item): ?>
                    <div class="no">❌ <?= htmlspecialchars($item); ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($brochures)): ?>
        <section id="brochure" class="card section brochure">
          <h2>Descargar material</h2>
          <?php foreach ($brochures as $index => $brochure): ?>
            <?php
              $bannerClass = 'banner' . ($index % 2 === 1 ? ' secondary' : '');
              if (!empty($brochure['primary'])) {
                  $bannerClass = 'banner';
              }
              $targetAttrs = preg_match('~^https?://~i', $brochure['href']) === 1 ? ' target="_blank" rel="noopener"' : '';
            ?>
            <div class="<?= $bannerClass; ?>">
              <div>
                <strong><?= htmlspecialchars($brochure['title']); ?></strong>
                <?php if ($brochure['description'] !== ''): ?>
                  <small><?= htmlspecialchars($brochure['description']); ?></small>
                <?php endif; ?>
              </div>
              <a class="btn<?= $index % 2 === 1 ? ' secondary' : ''; ?>" href="<?= htmlspecialchars($brochure['href'], ENT_QUOTES); ?>"<?= $targetAttrs; ?>>Descargar</a>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

      <?php if (!empty($notesList)): ?>
        <section id="info" class="card section">
          <h2>Información adicional</h2>
          <ul class="notes">
            <?php foreach ($notesList as $note): ?>
              <li><?= htmlspecialchars($note); ?></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($otherEssentialSections)): ?>
        <?php foreach ($otherEssentialSections as $extraSection): ?>
          <section class="card section">
            <h2><?= htmlspecialchars($extraSection['title']); ?></h2>
            <ul class="notes">
              <?php foreach ($extraSection['items'] as $item): ?>
                <li><?= htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($experiences)): ?>
        <section id="experiencias" class="card section">
          <h2>Experiencias que complementan tu viaje</h2>
          <div class="list">
            <?php foreach ($experiences as $experience):
                $experienceTitle = trim((string) ($experience['title'] ?? ''));
                $experienceDescription = trim((string) ($experience['description'] ?? ''));
                if ($experienceTitle === '' || $experienceDescription === '') {
                    continue;
                }
                $experienceIcon = trim((string) ($experience['icon'] ?? '✨'));
                $experiencePriceRaw = $experience['price'] ?? ($experience['precio'] ?? ($experience['priceFrom'] ?? null));
                $experienceCurrency = strtoupper((string) ($experience['currency'] ?? ($experience['moneda'] ?? 'PEN')));
                $experiencePriceText = '';
                if (is_numeric($experiencePriceRaw)) {
                    $symbol = match ($experienceCurrency) {
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        default => 'S/',
                    };
                    $experiencePriceText = sprintf('%s %s', $symbol, number_format((float) $experiencePriceRaw, 2, '.', ','));
                } elseif (is_string($experiencePriceRaw)) {
                    $experiencePriceText = trim($experiencePriceRaw);
                }
                $experiencePriceNote = '';
                if (!empty($experience['priceNote'] ?? null)) {
                    $experiencePriceNote = trim((string) $experience['priceNote']);
                } elseif (!empty($experience['notaPrecio'] ?? null)) {
                    $experiencePriceNote = trim((string) $experience['notaPrecio']);
                }
            ?>
              <div class="item">
                <i><?= htmlspecialchars($experienceIcon); ?></i>
                <div>
                  <strong><?= htmlspecialchars($experienceTitle); ?></strong>
                  <p style="margin:.25rem 0 0;"><?= htmlspecialchars($experienceDescription); ?></p>
                  <?php if ($experiencePriceText !== ''): ?>
                    <p style="margin:.35rem 0 0;font-size:.9rem;color:var(--muted)">
                      Tarifa referencial: <?= htmlspecialchars($experiencePriceText); ?>
                      <?php if ($experiencePriceNote !== ''): ?>
                        <span>· <?= htmlspecialchars($experiencePriceNote); ?></span>
                      <?php endif; ?>
                    </p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($galleryItems)): ?>
        <section id="galeria" class="card section">
          <h2>Galería del circuito</h2>
          <div class="gallery-grid">
            <?php foreach ($galleryItems as $image): ?>
              <img src="<?= htmlspecialchars($image['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($image['alt']); ?>" loading="lazy" />
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($relatedCards)): ?>
        <section id="relacionados" class="card section">
          <h2>También te puede interesar</h2>
          <div class="related">
            <?php foreach ($relatedCards as $card): ?>
              <article class="related-card">
                <?php if ($card['image'] !== ''): ?>
                  <div class="related-card__media">
                    <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($card['title']); ?>" loading="lazy" />
                  </div>
                <?php endif; ?>
                <div class="related-card__body">
                  <?php if ($card['badge'] !== ''): ?>
                    <span class="related-card__badge"><?= htmlspecialchars($card['badge']); ?></span>
                  <?php endif; ?>
                  <h3><?= htmlspecialchars($card['title']); ?></h3>
                  <p><?= htmlspecialchars($card['summary']); ?></p>
                  <a class="btn secondary" href="<?= htmlspecialchars($card['href'], ENT_QUOTES); ?>">Ver detalles</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>

    <aside class="grid" id="reserva" style="position:sticky;top:84px;height:fit-content">
      <div class="card section">
        <h2>Reserva rápida</h2>
        <div class="list">
          <label>
            <small>Fecha de salida</small><br />
            <input type="date" style="width:100%;padding:12px;border-radius:10px;border:1px solid #cbd5e1" />
          </label>
          <label>
            <small>Pasajeros</small><br />
            <input type="number" min="1" value="2" style="width:100%;padding:12px;border-radius:10px;border:1px solid #cbd5e1" />
          </label>
          <?php if ($ctaPrimaryLabel !== '' && $ctaPrimaryHref !== ''): ?>
            <a class="btn" href="<?= htmlspecialchars($ctaPrimaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaPrimaryLabel); ?></a>
          <?php else: ?>
            <button class="btn" type="button">Consultar disponibilidad</button>
          <?php endif; ?>
          <?php if ($ctaSecondaryLabel !== '' && $ctaSecondaryHref !== ''): ?>
            <a class="btn secondary" href="<?= htmlspecialchars($ctaSecondaryHref, ENT_QUOTES); ?>"><?= htmlspecialchars($ctaSecondaryLabel); ?></a>
          <?php else: ?>
            <button class="btn secondary" type="button">Personalizar paquete</button>
          <?php endif; ?>
        </div>
      </div>

      <div class="card section">
        <h2>Contacto</h2>
        <div class="list">
          <?php if (!empty($contactPhones)): ?>
            <?php foreach ($contactPhones as $phone): ?>
              <div>📞 <?= htmlspecialchars($phone); ?></div>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!empty($contactEmails)): ?>
            <?php foreach ($contactEmails as $email): ?>
              <div>✉️ <?= htmlspecialchars($email); ?></div>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!empty($contactAddresses)): ?>
            <?php foreach ($contactAddresses as $address): ?>
              <div>📍 <?= htmlspecialchars($address); ?></div>
            <?php endforeach; ?>
          <?php elseif (!empty($contactLocations)): ?>
            <?php foreach ($contactLocations as $contactLocation): ?>
              <div>📍 <?= htmlspecialchars($contactLocation); ?></div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </main>

  <footer class="container">© <?= date('Y'); ?> <?= htmlspecialchars($siteTitle); ?> — Circuitos y Paquetes Turísticos</footer>

  <script>
    document.querySelectorAll('.accordion details').forEach((detail) => {
      detail.addEventListener('toggle', () => {
        if (detail.open) {
          document.querySelectorAll('.accordion details').forEach((other) => {
            if (other !== detail) {
              other.removeAttribute('open');
            }
          });
        }
      });
    });
  </script>
</body>
</html>
