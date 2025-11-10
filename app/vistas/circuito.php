<?php
$detail = $detail ?? [];
$siteSettings = $siteSettings ?? [];
$siteTitle = (string) ($siteSettings['siteTitle'] ?? 'Expediatravels');
$siteFavicon = $siteSettings['siteFavicon'] ?? null;
if (!is_string($siteFavicon) || trim($siteFavicon) === '') {
    $siteFavicon = null;
}

$title = trim((string) ($detail['title'] ?? ($detail['nombre'] ?? '')));
if ($title === '') {
    $title = 'Tour sin nombre';
}
$typeLabel = trim((string) ($detail['type_tag'] ?? ($detail['type'] ?? ($detail['categoria'] ?? ''))));
if ($typeLabel === '') {
    $typeLabel = 'Circuito';
}
$tagline = trim((string) ($detail['tagline'] ?? ($detail['resumen'] ?? '')));
$summaryRaw = $detail['summary'] ?? ($detail['descripcion_larga'] ?? ($detail['descripcion'] ?? ''));
$summary = is_string($summaryRaw) ? trim($summaryRaw) : '';

$heroImage = trim((string) ($detail['heroImage'] ?? ($detail['imagen'] ?? '')));
if ($heroImage === '') {
    $heroImage = 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80';
}

$mapLabel = trim((string) ($detail['mapLabel'] ?? ($detail['location'] ?? 'Mapa del circuito')));
$mapImage = trim((string) ($detail['mapImage'] ?? ''));
$mapUrlRaw = $detail['mapUrl'] ?? ($detail['map_url'] ?? '');
$mapUrl = is_string($mapUrlRaw) ? trim($mapUrlRaw) : '';

$location = trim((string) ($detail['location'] ?? ($detail['destino'] ?? '')));
$region = trim((string) ($detail['region'] ?? ''));
if ($region !== '' && $location !== '') {
    $location .= ' — ' . $region;
} elseif ($location === '' && $region !== '') {
    $location = $region;
}

$chips = is_array($detail['chips'] ?? null) ? array_values(array_filter(array_map('trim', $detail['chips']))) : [];

$duration = trim((string) ($detail['duration'] ?? ($detail['duracion'] ?? '')));
$frequency = trim((string) ($detail['frecuencia'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? ''))));
$group = trim((string) ($detail['grupo'] ?? ($detail['grupo_maximo'] ?? '')));
$experienceLevel = trim((string) ($detail['experiencia'] ?? ($detail['dificultad'] ?? '')));

$stats = is_array($detail['stats'] ?? null) ? $detail['stats'] : [];
$facts = [];
foreach ($stats as $stat) {
    if (!is_array($stat)) {
        continue;
    }
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
    $facts['Nivel de experiencia'] = $experienceLevel;
}
if ($group !== '') {
    $facts['Grupo'] = $group;
}
if ($frequency !== '') {
    $facts['Próxima salida'] = $frequency;
}
if ($location !== '') {
    $facts['Destino'] = $location;
}
$factsList = array_map(
    static fn ($label, $value) => ['label' => $label, 'value' => $value],
    array_keys($facts),
    $facts
);

$featuredVideoRaw = $detail['featuredVideoUrl']
    ?? ($detail['videoDestacadoUrl']
        ?? ($detail['video_destacado_url']
            ?? ($detail['videoDestacado'] ?? ($detail['video_destacado'] ?? ''))));
$featuredVideoUrl = is_string($featuredVideoRaw) ? trim($featuredVideoRaw) : '';
$featuredVideoEmbedUrl = '';

if ($featuredVideoUrl !== '') {
    $youtubeId = null;
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|v/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $featuredVideoUrl, $matches)) {
        $youtubeId = $matches[1];
    } else {
        $parsedUrl = parse_url($featuredVideoUrl);
        if (is_array($parsedUrl) && isset($parsedUrl['host']) && str_contains($parsedUrl['host'], 'youtube.com')) {
            $query = $parsedUrl['query'] ?? '';
            if ($query !== '') {
                parse_str($query, $params);
                if (isset($params['v']) && is_string($params['v']) && $params['v'] !== '') {
                    $youtubeId = substr($params['v'], 0, 11);
                }
            }
        }
    }

    if ($youtubeId !== null) {
        $featuredVideoEmbedUrl = 'https://www.youtube.com/embed/' . $youtubeId . '?rel=0';
    } else {
        $featuredVideoEmbedUrl = $featuredVideoUrl;
    }
}

$cta = is_array($detail['cta'] ?? null) ? $detail['cta'] : [];
$ctaPrimaryLabel = trim((string) ($cta['primaryLabel'] ?? ''));
$ctaPrimaryHref = trim((string) ($cta['primaryHref'] ?? ''));
$ctaSecondaryLabel = trim((string) ($cta['secondaryLabel'] ?? ''));
$ctaSecondaryHref = trim((string) ($cta['secondaryHref'] ?? ''));

$bookingUrlRaw = $detail['booking_url'] ?? ($detail['bookingUrl'] ?? null);
$bookingUrl = is_string($bookingUrlRaw) ? trim($bookingUrlRaw) : '';
if ($bookingUrl === '' && $ctaPrimaryHref !== '') {
    $bookingUrl = $ctaPrimaryHref;
} elseif ($bookingUrl === '' && $ctaSecondaryHref !== '') {
    $bookingUrl = $ctaSecondaryHref;
}
$bookingUrl = $bookingUrl !== '' ? $bookingUrl : null;

$formatPeruvianCurrency = static function ($value) use (&$formatPeruvianCurrency): ?string {
    if ($value instanceof \Stringable) {
        $value = (string) $value;
    }

    if (is_array($value)) {
        foreach (['amount', 'precio', 'price', 'valor', 'from'] as $key) {
            if (array_key_exists($key, $value)) {
                $formatted = $formatPeruvianCurrency($value[$key]);
                if ($formatted !== null) {
                    return $formatted;
                }
            }
        }

        return null;
    }

    if (is_numeric($value)) {
        return 'S/ ' . number_format((float) $value, 2, '.', '');
    }

    if (is_string($value)) {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $normalizedLower = function_exists('mb_strtolower') ? mb_strtolower($normalized, 'UTF-8') : strtolower($normalized);
        $fragmentsToRemove = ['desde', 'aprox.', 'aprox', 's/.', 's/ ', 's/'];
        foreach ($fragmentsToRemove as $fragment) {
            $normalizedLower = str_replace($fragment, ' ', $normalizedLower);
        }
        $normalizedLower = preg_replace('/pen|usd|soles?|\s+/u', ' ', $normalizedLower) ?? '';
        $filtered = preg_replace('/[^0-9,.-]/', '', $normalizedLower) ?? '';
        $filtered = trim($filtered);
        if ($filtered === '') {
            return null;
        }

        if (str_contains($filtered, ',') && str_contains($filtered, '.')) {
            $filtered = str_replace(',', '', $filtered);
        } elseif (str_contains($filtered, ',')) {
            $filtered = str_replace(',', '.', $filtered);
        }

        if (!is_numeric($filtered)) {
            return null;
        }

        return 'S/ ' . number_format((float) $filtered, 2, '.', '');
    }

    if (is_int($value) || is_float($value)) {
        return 'S/ ' . number_format((float) $value, 2, '.', '');
    }

    return null;
};

$priceCandidates = [
    $detail['price_from'] ?? null,
    $detail['priceFrom'] ?? null,
    $detail['precio_desde'] ?? null,
    $detail['precio'] ?? null,
];
$priceFrom = '—';
foreach ($priceCandidates as $candidate) {
    if ($candidate === null || $candidate === '') {
        continue;
    }

    $formatted = $formatPeruvianCurrency($candidate);
    if ($formatted !== null) {
        $priceFrom = $formatted;
        break;
    }
}

$timezoneLima = new \DateTimeZone('America/Lima');
$nowInLima = new \DateTimeImmutable('now', $timezoneLima);
$dayNames = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo',
];

$normalizeAccents = static function (string $text): string {
    $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    return strtr($lower, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n',
    ]);
};

$parseDepartureDate = static function ($value) use ($timezoneLima, $normalizeAccents): ?\DateTimeImmutable {
    if ($value instanceof \DateTimeInterface) {
        return (new \DateTimeImmutable($value->format('Y-m-d H:i:s'), $timezoneLima))
            ->setTime((int) $value->format('H'), (int) $value->format('i'), (int) $value->format('s'));
    }

    if (is_numeric($value)) {
        try {
            return (new \DateTimeImmutable('@' . (int) $value))->setTimezone($timezoneLima);
        } catch (\Exception) {
            return null;
        }
    }

    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $value . ' 12:00', $timezoneLima);
        return $date ?: null;
    }

    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
        $date = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $value . ' 12:00', $timezoneLima);
        return $date ?: null;
    }

    try {
        return new \DateTimeImmutable($value, $timezoneLima);
    } catch (\Exception) {
        $normalizedMonths = $value;
        $months = [
            'enero' => 'january',
            'febrero' => 'february',
            'marzo' => 'march',
            'abril' => 'april',
            'mayo' => 'may',
            'junio' => 'june',
            'julio' => 'july',
            'agosto' => 'august',
            'setiembre' => 'september',
            'septiembre' => 'september',
            'octubre' => 'october',
            'noviembre' => 'november',
            'diciembre' => 'december',
            'ene' => 'jan',
            'feb' => 'feb',
            'mar' => 'mar',
            'abr' => 'apr',
            'may' => 'may',
            'jun' => 'jun',
            'jul' => 'jul',
            'ago' => 'aug',
            'set' => 'sep',
            'sep' => 'sep',
            'oct' => 'oct',
            'nov' => 'nov',
            'dic' => 'dec',
        ];
        foreach ($months as $spanish => $english) {
            if (stripos($normalizedMonths, $spanish) !== false) {
                $normalizedMonths = preg_replace('/' . preg_quote($spanish, '/') . '/i', $english, $normalizedMonths);
            }
        }

        try {
            return new \DateTimeImmutable($normalizedMonths, $timezoneLima);
        } catch (\Exception) {
            return null;
        }
    }
};

$resolveNextDepartureDay = static function ($departures) use (&$resolveNextDepartureDay, $parseDepartureDate, $dayNames, $nowInLima, $normalizeAccents): ?string {
    if ($departures === null || $departures === '') {
        return null;
    }

    if ($departures instanceof \DateTimeInterface) {
        $parsed = $parseDepartureDate($departures);
        if ($parsed !== null && $parsed >= $nowInLima) {
            return $dayNames[(int) $parsed->format('N')];
        }

        return null;
    }

    if (is_array($departures)) {
        $dates = [];
        foreach ($departures as $item) {
            $candidate = null;
            if (is_array($item)) {
                foreach (['date', 'fecha', 'datetime', 'fecha_salida', 'departure'] as $key) {
                    if (array_key_exists($key, $item)) {
                        $candidate = $parseDepartureDate($item[$key]);
                        if ($candidate !== null) {
                            break;
                        }
                    }
                }
            } else {
                $candidate = $parseDepartureDate($item);
            }

            if ($candidate !== null && $candidate >= $nowInLima) {
                $dates[] = $candidate;
            }
        }

        if (!empty($dates)) {
            usort($dates, static fn (\DateTimeImmutable $a, \DateTimeImmutable $b) => $a <=> $b);
            $next = $dates[0];

            return $dayNames[(int) $next->format('N')];
        }

        return null;
    }

    if (is_string($departures)) {
        $trimmed = trim($departures);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $resolveNextDepartureDay($decoded);
        }

        if (preg_match('/[,;\|\n]/', $trimmed)) {
            $parts = preg_split('/[,;\|\n]+/', $trimmed) ?: [];
            if (!empty($parts)) {
                return $resolveNextDepartureDay($parts);
            }
        }

        $parsed = $parseDepartureDate($trimmed);
        if ($parsed !== null && $parsed >= $nowInLima) {
            return $dayNames[(int) $parsed->format('N')];
        }

        $normalized = $normalizeAccents($trimmed);
        $dayKeywords = [
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sabado' => 6,
            'domingo' => 7,
        ];
        $todayNumber = (int) $nowInLima->format('N');
        $closestDay = null;
        $closestDiff = 8;
        foreach ($dayKeywords as $keyword => $dayNumber) {
            if (str_contains($normalized, $keyword)) {
                $diff = ($dayNumber - $todayNumber + 7) % 7;
                if ($diff === 0 && str_contains($normalized, 'proximo')) {
                    $diff = 7;
                }

                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $closestDay = $dayNumber;
                }
            }
        }

        if ($closestDay !== null) {
            return $dayNames[$closestDay];
        }
    }

    return null;
};

$rawDepartures = $detail['departures']
    ?? ($detail['salidas']
        ?? ($detail['fechas_salida']
            ?? ($detail['fechasSalidas']
                ?? ($detail['nextDeparture'] ?? null))));
if ($rawDepartures === null || $rawDepartures === '') {
    $rawDepartures = $detail['frecuencia'] ?? ($detail['proximaSalida'] ?? ($detail['proxima_salida'] ?? null));
}
$nextDepartureDay = $resolveNextDepartureDay($rawDepartures);
if ($nextDepartureDay === null && $frequency !== '') {
    $nextDepartureDay = $resolveNextDepartureDay($frequency);
}
if ($nextDepartureDay === null) {
    $nextDepartureDay = 'Consultar';
}

$reviewsSummaryData = is_array($reviewsSummary ?? null) ? $reviewsSummary : [];
$reviewsAverage = isset($reviewsSummaryData['average']) && is_numeric($reviewsSummaryData['average']) ? round((float) $reviewsSummaryData['average'], 1) : null;
$reviewsCountSummary = isset($reviewsSummaryData['count']) && is_numeric($reviewsSummaryData['count']) ? (int) $reviewsSummaryData['count'] : 0;
$rating = $detail['ratingPromedio'] ?? ($detail['rating'] ?? null);
if ($reviewsAverage === null && is_numeric($rating)) {
    $reviewsAverage = round((float) $rating, 1);
}
$reviewsRaw = $detail['totalResenas'] ?? ($detail['reviews'] ?? null);
if ($reviewsCountSummary === 0 && is_numeric($reviewsRaw)) {
    $reviewsCountSummary = (int) $reviewsRaw;
}
$reviewsAverageText = $reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '—';
$reviewsCountText = number_format($reviewsCountSummary);

$heroInfoBoxes = [
    ['icon' => '📆', 'label' => 'Duración', 'value' => $duration !== '' ? $duration : '—'],
    ['icon' => '⭐', 'label' => 'Experiencia', 'value' => $experienceLevel !== '' ? $experienceLevel : '—'],
    ['icon' => '📍', 'label' => 'Ubicación', 'value' => $location !== '' ? $location : '—'],
    ['icon' => '💰', 'label' => 'Desde', 'value' => $priceFrom !== '—' ? $priceFrom : '—'],
];

$heroRatingValue = $reviewsAverage !== null ? $reviewsAverage : 5.0;
$heroRatingCount = max(0, (int) $reviewsCountSummary);
$heroRatingText = sprintf('⭐ %s (%s opiniones)', number_format($heroRatingValue, 1, '.', ''), number_format($heroRatingCount));
$nextDepartureLabel = '📅 Próxima salida: ' . $nextDepartureDay;

$summaryParagraphs = [];
if ($summary !== '') {
    $summaryParagraphs = preg_split('/\n\s*\n/', $summary) ?: [];
}
$summaryParagraphs = array_values(array_filter(array_map('trim', $summaryParagraphs), static fn ($paragraph) => $paragraph !== ''));
$primarySummaryParagraph = $summaryParagraphs[0] ?? '';
$extraSummaryParagraphs = array_slice($summaryParagraphs, 1);

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
        $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn ($item) => $item !== ''));
    }

    return [];
};

$essentials = is_array($detail['essentials'] ?? null) ? $detail['essentials'] : [];
$services = is_array($detail['servicios'] ?? null) ? $detail['servicios'] : [];
$includesList = [];
$excludesList = [];
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
        $excludesList = array_merge($excludesList, $itemsEssential);
    } elseif (str_contains($titleEssential, 'inclu')) {
        $includesList = array_merge($includesList, $itemsEssential);
    }
}
if (empty($includesList) && !empty($services)) {
    $includesList = $normalizeList($services);
}
$includesList = array_values(array_unique($includesList));
$excludesList = array_values(array_unique($excludesList));

$highlightsRaw = is_array($detail['highlights'] ?? null) ? $detail['highlights'] : [];
$highlightItems = [];
foreach ($highlightsRaw as $highlight) {
    if (is_string($highlight)) {
        $label = trim($highlight);
        if ($label !== '') {
            $highlightItems[] = ['title' => $label, 'description' => ''];
        }
        continue;
    }
    if (!is_array($highlight)) {
        continue;
    }
    $titleHighlight = trim((string) ($highlight['title'] ?? ($highlight['label'] ?? '')));
    $descriptionHighlight = trim((string) ($highlight['summary'] ?? ($highlight['description'] ?? '')));
    if ($titleHighlight === '') {
        continue;
    }
    $highlightItems[] = [
        'title' => $titleHighlight,
        'description' => $descriptionHighlight,
    ];
}
if (empty($highlightItems) && !empty($includesList)) {
    foreach (array_slice($includesList, 0, 4) as $includeItem) {
        $highlightItems[] = ['title' => $includeItem, 'description' => ''];
    }
}

$locationsRaw = $detail['locations'] ?? ($detail['stops'] ?? ($detail['destinos'] ?? []));
if (is_string($locationsRaw)) {
    $locationsRaw = $normalizeList($locationsRaw);
}
$mapPoints = [];
if (is_array($locationsRaw)) {
    foreach ($locationsRaw as $item) {
        if (is_string($item)) {
            $label = trim($item);
            if ($label !== '') {
                $mapPoints[] = [
                    'title' => $label,
                    'duration' => '',
                ];
            }
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $titlePoint = trim((string) ($item['title'] ?? ($item['name'] ?? ($item['label'] ?? ''))));
        if ($titlePoint === '') {
            continue;
        }
        $durationPoint = trim((string) ($item['duration'] ?? ($item['dias'] ?? ($item['stay'] ?? ''))));
        $mapPoints[] = [
            'title' => $titlePoint,
            'duration' => $durationPoint,
        ];
    }
}

$mapDefaultUrl = $mapUrl !== '' ? $mapUrl : 'https://maps.google.com/maps?q=' . rawurlencode($location !== '' ? $location : $title) . '&output=embed';

$itineraryRaw = $detail['itinerary'] ?? [];
if (empty($itineraryRaw) && !empty($detail['itinerary_detallado'])) {
    $itineraryRaw = $detail['itinerary_detallado'];
}
$itineraryDays = [];
foreach ($itineraryRaw as $index => $day) {
    if (is_string($day)) {
        $labelDay = trim($day);
        if ($labelDay !== '') {
            $itineraryDays[] = [
                'title' => $labelDay,
                'summary' => '',
                'schedule' => '',
                'activities' => [],
            ];
        }
        continue;
    }
    if (!is_array($day)) {
        continue;
    }
    $titleDay = trim((string) ($day['title'] ?? ($day['nombre'] ?? '')));
    $summaryDay = trim((string) ($day['summary'] ?? ($day['descripcion'] ?? '')));
    $scheduleDay = trim((string) ($day['schedule'] ?? ($day['time'] ?? ($day['horario'] ?? ''))));
    $activitiesDay = $normalizeList($day['activities'] ?? ($day['actividades'] ?? []));
    if ($titleDay === '') {
        $titleDay = 'Día ' . ($index + 1);
    }
    $itineraryDays[] = [
        'title' => $titleDay,
        'summary' => $summaryDay,
        'schedule' => $scheduleDay,
        'activities' => $activitiesDay,
    ];
}

$galleryRaw = is_array($detail['gallery'] ?? null) ? $detail['gallery'] : [];
$galleryItems = [];
foreach ($galleryRaw as $image) {
    if (is_string($image)) {
        $src = trim($image);
        if ($src !== '') {
            $galleryItems[] = ['src' => $src, 'alt' => $title];
        }
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
    $galleryItems[] = ['src' => $src, 'alt' => $alt !== '' ? $alt : $title];
}
if (empty($galleryItems) && $heroImage !== '') {
    $galleryItems[] = ['src' => $heroImage, 'alt' => $title];
}

$relatedRaw = is_array($detail['related'] ?? null) ? $detail['related'] : [];
$relatedCards = [];
foreach ($relatedRaw as $card) {
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

$reviewsListRaw = is_array($detail['reviewsList'] ?? null) ? $detail['reviewsList'] : [];
$reviewsList = [];
foreach ($reviewsListRaw as $review) {
    if (!is_array($review)) {
        continue;
    }
    $nameReview = trim((string) ($review['nombre'] ?? ($review['usuario'] ?? ($review['name'] ?? ''))));
    if ($nameReview === '') {
        $nameReview = 'Viajero';
    }
    $ratingReview = $review['rating'] ?? ($review['calificacion'] ?? ($review['calificación'] ?? null));
    if (!is_numeric($ratingReview)) {
        $ratingReview = 5;
    }
    $ratingReview = max(1, min(5, (int) $ratingReview));
    $commentReview = trim((string) ($review['comentario'] ?? ($review['comment'] ?? '')));
    if ($commentReview === '') {
        $commentReview = 'Sin comentarios.';
    }
    $createdReview = $review['creado_en'] ?? ($review['fecha'] ?? null);
    if ($createdReview !== null && !is_string($createdReview)) {
        $createdReview = null;
    }
    $reviewsList[] = [
        'nombre' => $nameReview,
        'rating' => $ratingReview,
        'comentario' => $commentReview,
        'creado_en' => $createdReview,
    ];
}

$contact = $siteSettings['contact'] ?? [];
$contactPhones = $normalizeList($contact['phones'] ?? ($siteSettings['contactPhones'] ?? null));
$contactEmails = $normalizeList($contact['emails'] ?? ($siteSettings['contactEmails'] ?? null));
$contactAddresses = $normalizeList($contact['addresses'] ?? ($siteSettings['contactAddresses'] ?? null));
$contactLocations = $normalizeList($contact['locations'] ?? ($siteSettings['contactLocations'] ?? null));

$breadcrumbs = [
    ['label' => 'Inicio', 'href' => 'index.php'],
    ['label' => 'Circuitos', 'href' => 'explorar.php'],
    ['label' => $title, 'href' => '#'],
];

$slug = trim((string) ($detail['slug'] ?? ''));
$currentUser = $currentUser ?? null;

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

    <main class="circuit-page">
        <section class="tour-hero" style="--tour-hero-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES); ?>');">
            <div class="tour-hero__overlay" aria-hidden="true"></div>
            <div class="tour-hero__container">
                <div class="tour-hero__header">
                    <div class="tour-hero__title-group">
                        <h1><?= htmlspecialchars($title); ?></h1>
                        <span class="tour-hero__type-tag"><?= htmlspecialchars($typeLabel); ?></span>
                    </div>
                    <div class="tour-hero__labels">
                        <span class="tour-hero__label tour-hero__label--review"><?= htmlspecialchars($heroRatingText); ?></span>
                        <span class="tour-hero__label tour-hero__label--departure"><?= htmlspecialchars($nextDepartureLabel); ?></span>
                    </div>
                </div>
                <?php if ($tagline !== ''): ?>
                    <p class="tour-hero__tagline"><?= htmlspecialchars($tagline); ?></p>
                <?php endif; ?>
                <div class="info-boxes" role="list">
                    <?php foreach ($heroInfoBoxes as $infoBox): ?>
                        <div class="info-box" role="listitem">
                            <span class="info-box__icon" aria-hidden="true"><?= $infoBox['icon']; ?></span>
                            <div class="info-box__content">
                                <span class="info-box__label"><?= htmlspecialchars($infoBox['label']); ?></span>
                                <strong><?= htmlspecialchars($infoBox['value']); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="tour-hero__cta">
                    <?php if ($bookingUrl !== null): ?>
                        <a class="tour-hero__button" href="<?= htmlspecialchars($bookingUrl, ENT_QUOTES); ?>">🚌 Reservar Ahora</a>
                    <?php else: ?>
                        <button class="tour-hero__button tour-hero__button--disabled" type="button" disabled>🚌 Temporalmente no disponible</button>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="circuit-body">
            <nav class="circuit-breadcrumbs" aria-label="Ruta de navegación">
                <ol>
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li>
                            <?php if ($crumb['href'] !== '#' && $index !== count($breadcrumbs) - 1): ?>
                                <a href="<?= htmlspecialchars($crumb['href'], ENT_QUOTES); ?>"><?= htmlspecialchars($crumb['label']); ?></a>
                            <?php else: ?>
                                <span aria-current="page"><?= htmlspecialchars($crumb['label']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <div class="circuit-main">
                <section class="circuit-section" id="descripcion">
                    <header class="section-header">
                        <h2>Disfruta la aventura</h2>
                        <p>Descubre qué hace único a este circuito y cómo se adapta a tu estilo de viaje.</p>
                    </header>
                    <div class="section-content">
                        <?php foreach ($summaryParagraphs as $paragraph): ?>
                            <p><?= htmlspecialchars($paragraph); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($factsList)): ?>
                        <div class="feature-grid">
                            <?php foreach (array_slice($factsList, 0, 6) as $fact): ?>
                                <article class="feature-card">
                                    <span class="feature-card__label"><?= htmlspecialchars($fact['label']); ?></span>
                                    <strong><?= htmlspecialchars($fact['value']); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if (!empty($includesList) || !empty($excludesList)): ?>
                    <section class="circuit-section" id="incluye">
                        <header class="section-header">
                            <h2>Incluye &amp; No incluye</h2>
                            <p>Transparencia total para planificar con confianza.</p>
                        </header>
                        <div class="includes-grid">
                            <?php if (!empty($includesList)): ?>
                                <div class="includes-column">
                                    <h3>Incluye</h3>
                                    <ul>
                                        <?php foreach ($includesList as $item): ?>
                                            <li><?= htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($excludesList)): ?>
                                <div class="includes-column">
                                    <h3>No incluye</h3>
                                    <ul>
                                        <?php foreach ($excludesList as $item): ?>
                                            <li><?= htmlspecialchars($item); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="mapa">
                    <header class="section-header">
                        <h2><?= htmlspecialchars($mapLabel !== '' ? $mapLabel : 'Mapa del circuito'); ?></h2>
                        <p>Visualiza el recorrido y ubica los puntos clave del circuito.</p>
                    </header>
                    <div class="map-wrapper">
                        <iframe src="<?= htmlspecialchars($mapDefaultUrl, ENT_QUOTES); ?>" title="Mapa del circuito" loading="lazy" allowfullscreen></iframe>
                    </div>
                    <?php if (!empty($mapPoints)): ?>
                        <ul class="map-points">
                            <?php foreach (array_slice($mapPoints, 0, 6) as $point): ?>
                                <li>
                                    <strong><?= htmlspecialchars($point['title']); ?></strong>
                                    <?php if ($point['duration'] !== ''): ?>
                                        <span><?= htmlspecialchars($point['duration']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <?php if (!empty($itineraryDays)): ?>
                    <section class="circuit-section" id="itinerario">
                        <header class="section-header">
                            <h2>Itinerario detallado</h2>
                            <p>Una mirada día a día para que sepas exactamente qué esperar.</p>
                        </header>
                        <ol class="itinerary">
                            <?php foreach ($itineraryDays as $index => $day): ?>
                                <li>
                                    <div class="itinerary__day">Día <?= $index + 1; ?></div>
                                    <div class="itinerary__content">
                                        <h3><?= htmlspecialchars($day['title']); ?></h3>
                                        <?php if ($day['schedule'] !== ''): ?>
                                            <p class="itinerary__schedule"><strong>Horario:</strong> <?= htmlspecialchars($day['schedule']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($day['summary'] !== ''): ?>
                                            <p><?= htmlspecialchars($day['summary']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($day['activities'])): ?>
                                            <ul class="itinerary__activities">
                                                <?php foreach ($day['activities'] as $activity): ?>
                                                    <li><?= htmlspecialchars($activity); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if (!empty($galleryItems)): ?>
                    <section class="circuit-section" id="galeria">
                        <header class="section-header">
                            <h2>Galería de experiencias</h2>
                            <p>Inspírate con momentos capturados en el circuito.</p>
                        </header>
                        <div class="gallery-grid">
                            <?php foreach ($galleryItems as $image): ?>
                                <figure>
                                    <img src="<?= htmlspecialchars($image['src'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES); ?>" loading="lazy" />
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="circuit-section" id="opiniones">
                    <header class="section-header">
                        <h2>Opiniones de viajeros</h2>
                        <p>Historias reales de quienes ya vivieron esta experiencia.</p>
                    </header>
                    <div class="reviews">
                        <div class="reviews__summary">
                            <div class="rating-stars rating-stars--lg" data-review-stars-secondary style="--rating: <?= htmlspecialchars($reviewsAverage !== null ? number_format($reviewsAverage, 1, '.', '') : '0'); ?>;"></div>
                            <div>
                                <p class="reviews__average"><strong data-review-average-secondary><?= htmlspecialchars($reviewsAverageText); ?></strong> / 5</p>
                                <p class="reviews__count"><span data-review-count-secondary><?= htmlspecialchars($reviewsCountText); ?></span> opiniones totales</p>
                            </div>
                        </div>
                        <ul class="reviews__list" data-review-list>
                            <?php if (empty($reviewsList)): ?>
                                <li class="reviews__empty">Sé la primera persona en dejar una reseña sobre este circuito.</li>
                            <?php else: ?>
                                <?php foreach ($reviewsList as $review): ?>
                                    <li class="review-card">
                                        <div class="review-card__header">
                                            <strong><?= htmlspecialchars($review['nombre']); ?></strong>
                                            <div class="review-card__stars" style="--rating: <?= htmlspecialchars(number_format((float) $review['rating'], 1, '.', '')); ?>;" aria-label="<?= htmlspecialchars($review['rating']); ?> de 5"></div>
                                        </div>
                                        <p class="review-card__comment"><?= htmlspecialchars($review['comentario']); ?></p>
                                        <?php if (!empty($review['creado_en'])): ?>
                                            <small class="review-card__date">Publicado el <?= htmlspecialchars($review['creado_en']); ?></small>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <form class="reviews__form" method="post" action="api/resenas-circuitos.php" data-review-form>
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($slug, ENT_QUOTES); ?>" />
                            <input type="hidden" name="titulo" value="<?= htmlspecialchars($title, ENT_QUOTES); ?>" />
                            <div class="form-grid">
                                <label>
                                    <span>Nombre completo *</span>
                                    <input type="text" name="nombre" required autocomplete="name" />
                                </label>
                                <label>
                                    <span>Correo electrónico</span>
                                    <input type="email" name="correo" autocomplete="email" placeholder="Opcional" />
                                </label>
                                <label>
                                    <span>Calificación *</span>
                                    <select name="rating" required>
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?= $i; ?>"><?= $i; ?> estrella<?= $i === 1 ? '' : 's'; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                            </div>
                            <label>
                                <span>Tu reseña *</span>
                                <textarea name="comentario" rows="4" required placeholder="Comparte detalles de tu experiencia"></textarea>
                            </label>
                            <button class="button button--primary" type="submit" data-loading>Enviar reseña</button>
                            <div class="form-status" data-review-status></div>
                        </form>
                    </div>
                </section>

                <?php if (!empty($relatedCards)): ?>
                    <section class="circuit-section" id="relacionados">
                        <header class="section-header">
                            <h2>Otros circuitos recomendados</h2>
                            <p>Explora más aventuras seleccionadas especialmente para ti.</p>
                        </header>
                        <div class="card-grid">
                            <?php foreach ($relatedCards as $card): ?>
                                <article class="related-card">
                                    <?php if ($card['image'] !== ''): ?>
                                        <figure class="related-card__media">
                                            <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES); ?>" loading="lazy" />
                                        </figure>
                                    <?php endif; ?>
                                    <div class="related-card__body">
                                        <?php if ($card['badge'] !== ''): ?>
                                            <span class="related-card__badge"><?= htmlspecialchars($card['badge']); ?></span>
                                        <?php endif; ?>
                                        <h3><?= htmlspecialchars($card['title']); ?></h3>
                                        <p><?= htmlspecialchars($card['summary']); ?></p>
                                        <a class="button button--ghost" href="<?= htmlspecialchars($card['href'], ENT_QUOTES); ?>">Ver circuito</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="circuit-aside">
                <?php if (!empty($highlightItems)): ?>
                    <section class="aside-card">
                        <h3>Lo que amarás de este viaje</h3>
                        <ul>
                            <?php foreach ($highlightItems as $highlight): ?>
                                <li>
                                    <strong><?= htmlspecialchars($highlight['title']); ?></strong>
                                    <?php if ($highlight['description'] !== ''): ?>
                                        <span><?= htmlspecialchars($highlight['description']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <section class="aside-card aside-card--contact">
                    <h3>Contacto directo</h3>
                    <ul>
                        <?php if (!empty($contactPhones)): ?>
                            <?php foreach ($contactPhones as $phone): ?>
                                <li><span aria-hidden="true">📞</span> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $phone)); ?>"><?= htmlspecialchars($phone); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($contactEmails)): ?>
                            <?php foreach ($contactEmails as $email): ?>
                                <li><span aria-hidden="true">✉️</span> <a href="mailto:<?= htmlspecialchars($email); ?>"><?= htmlspecialchars($email); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($contactAddresses)): ?>
                            <?php foreach ($contactAddresses as $address): ?>
                                <li><span aria-hidden="true">📍</span> <?= htmlspecialchars($address); ?></li>
                            <?php endforeach; ?>
                        <?php elseif (!empty($contactLocations)): ?>
                            <?php foreach ($contactLocations as $contactLocation): ?>
                                <li><span aria-hidden="true">📍</span> <?= htmlspecialchars($contactLocation); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>

                <section class="aside-card aside-card--accent">
                    <h3>Garantía Expediatravels</h3>
                    <p>Estamos contigo en cada paso, desde la planificación hasta tu regreso a casa.</p>
                    <ul>
                        <li>Atención 24/7 durante el viaje.</li>
                        <li>Seguros y asistencia internacional disponible.</li>
                        <li>Expertos locales certificados.</li>
                    </ul>
                </section>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/partials/site-footer.php'; ?>
    <?php if ($featuredVideoEmbedUrl !== ''): ?>
        <div class="circuit-video-modal" data-video-modal hidden data-video-src="<?= htmlspecialchars($featuredVideoEmbedUrl, ENT_QUOTES); ?>">
            <div class="circuit-video-modal__backdrop" data-video-modal-close></div>
            <div class="circuit-video-modal__dialog" data-video-modal-dialog role="dialog" aria-modal="true" aria-labelledby="video-modal-title" tabindex="-1">
                <button type="button" class="circuit-video-modal__close" aria-label="Cerrar video" data-video-modal-close>×</button>
                <div class="circuit-video-modal__body">
                    <h2 id="video-modal-title">Video de <?= htmlspecialchars($title); ?></h2>
                    <div class="circuit-video-modal__frame">
                        <iframe
                            src=""
                            title="Video de <?= htmlspecialchars($title); ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            loading="lazy"
                            data-video-modal-frame
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php include __DIR__ . '/partials/auth-modal.php'; ?>
    <script>
        window.circuitoPageConfig = {
            reviewEndpoint: 'api/resenas-circuitos.php',
            reservationEndpoint: 'api/reservas-circuitos.php'
        };
    </script>
    <?php if ($featuredVideoEmbedUrl !== ''): ?>
        <script src="scripts/circuit-video-modal.js" defer></script>
    <?php endif; ?>
    <script src="scripts/circuito.js" defer></script>
    <script src="scripts/modal-autenticacion.js" defer></script>
    <?php include __DIR__ . '/partials/site-shell-scripts.php'; ?>
</body>
</html>
