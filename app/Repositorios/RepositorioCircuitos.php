<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioCircuitos
{
    private array $extrasCache = [];

    public function getFeatured(int $limit = 6): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT c.id, c.nombre, c.descripcion, c.duracion, c.precio, c.dificultad, c.frecuencia, c.servicios,
                        c.galeria,
                        COALESCE(c.destino_personalizado, d.nombre) AS destino,
                        d.region,
                        COALESCE(c.imagen_destacada, c.imagen_portada) AS imagen
                 FROM circuitos c
                 LEFT JOIN destinos d ON d.id = c.destino_id
                 WHERE c.estado = "activo"
                 ORDER BY c.creado_en DESC, c.id DESC
                 LIMIT :limit'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();

            $circuits = $statement->fetchAll();
            if ($circuits) {
                return array_map(fn (array $circuit) => $this->hydrateCircuit($circuit), $circuits);
            }
        } catch (PDOException $exception) {
            // Silencia problemas de conexión para usar datos de respaldo.
        }

        return array_map(
            fn (array $circuit) => $this->hydrateCircuit($circuit),
            array_slice($this->fallbackCircuits(), 0, $limit)
        );
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));

        if ($slug === '') {
            $fallback = $this->fallbackCircuits();

            return $fallback[0] ?? null;
        }

        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->query(
                'SELECT c.id, c.nombre, c.descripcion, c.duracion, c.precio, c.dificultad, c.frecuencia, c.servicios,
                        c.galeria,
                        COALESCE(c.destino_personalizado, d.nombre) AS destino,
                        d.region,
                        COALESCE(c.imagen_destacada, c.imagen_portada) AS imagen
                 FROM circuitos c
                 LEFT JOIN destinos d ON d.id = c.destino_id
                 WHERE c.estado = "activo"'
            );

            if ($statement !== false) {
                while ($row = $statement->fetch()) {
                    $circuit = $this->hydrateCircuit($row);
                    if (($circuit['slug'] ?? '') === $slug) {
                        return $circuit;
                    }
                }
            }
        } catch (PDOException $exception) {
            // Recurre a los datos de respaldo cuando la base de datos no está disponible.
        }

        foreach ($this->fallbackCircuits() as $circuit) {
            if (($circuit['slug'] ?? '') === $slug) {
                return $this->hydrateCircuit($circuit);
            }
        }

        $fallback = $this->fallbackCircuits();

        return $fallback[0] ?? null;
    }

    private function hydrateCircuit(array $circuit): array
    {
        $name = $circuit['nombre'] ?? $circuit['title'] ?? '';
        $summary = $circuit['resumen'] ?? $circuit['summary'] ?? $circuit['descripcion'] ?? '';
        $duration = $circuit['duracion'] ?? $circuit['duration'] ?? '';
        $destination = $circuit['destino'] ?? $circuit['location'] ?? $circuit['region'] ?? '';
        $region = $circuit['region'] ?? '';
        $price = $circuit['precio'] ?? $circuit['precio_desde'] ?? null;
        $rawGroup = $circuit['grupo'] ?? $circuit['grupo_maximo'] ?? $circuit['capacidad'] ?? $circuit['capacidad_maxima'] ?? $circuit['group'] ?? null;
        $rawNextDeparture = $circuit['proxima_salida'] ?? $circuit['proximaSalida'] ?? $circuit['nextDeparture'] ?? $circuit['frecuencia'] ?? null;
        $rawRating = $circuit['calificacion'] ?? $circuit['calificación'] ?? $circuit['rating'] ?? $circuit['rating_promedio'] ?? $circuit['ratingAverage'] ?? $circuit['ratingPromedio'] ?? null;
        $rawReviews = $circuit['resenas'] ?? $circuit['reseñas'] ?? $circuit['reviews'] ?? $circuit['reviewsCount'] ?? $circuit['totalResenas'] ?? null;
        $rawIsNew = $circuit['es_nuevo'] ?? $circuit['esNuevo'] ?? $circuit['nuevo'] ?? $circuit['isNew'] ?? null;
        $rawIsExclusive = $circuit['es_exclusivo'] ?? $circuit['esExclusivo'] ?? $circuit['exclusivo'] ?? $circuit['isExclusive'] ?? null;

        if (is_string($price)) {
            $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            if ($price !== null && $price !== false) {
                $price = str_replace(',', '', (string) $price);
            }
        }

        $parseBoolean = static function ($value): bool {
            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value === 1;
            }

            if (is_string($value)) {
                $normalized = strtolower(trim($value));
                if ($normalized === '') {
                    return false;
                }

                return in_array($normalized, ['1', 'true', 'yes', 'on', 'si', 'sí', 'nuevo', 'exclusivo', 'activo'], true);
            }

            return false;
        };

        $groupText = '';
        if (is_string($rawGroup)) {
            $groupText = trim($rawGroup);
        } elseif (is_numeric($rawGroup)) {
            $groupText = 'Hasta ' . (int) $rawGroup . ' viajeros';
        }

        $nextDepartureText = '';
        if (is_string($rawNextDeparture)) {
            $nextDepartureText = trim($rawNextDeparture);
        } elseif ($rawNextDeparture instanceof \DateTimeInterface) {
            $nextDepartureText = $rawNextDeparture->format('d M Y');
        }

        $ratingValue = null;
        if (is_numeric($rawRating)) {
            $ratingValue = round((float) $rawRating, 1);
        }

        $reviewsCount = null;
        if (is_numeric($rawReviews)) {
            $reviewsCount = (int) $rawReviews;
        }

        $services = $this->parseJsonList($circuit['servicios'] ?? null);
        $gallery = $this->parseImageList($circuit['galeria'] ?? $circuit['gallery'] ?? null, (string) $name);
        $heroImage = $circuit['heroImage'] ?? $circuit['imagen'] ?? $circuit['imagen_destacada'] ?? $circuit['imagen_portada'] ?? null;
        if ($heroImage === null && !empty($gallery)) {
            $firstImage = $gallery[0];
            if (is_array($firstImage) && !empty($firstImage['src'])) {
                $heroImage = $firstImage['src'];
            }
        }

        $base = array_merge($circuit, [
            'id' => (int) ($circuit['id'] ?? 0),
            'slug' => $circuit['slug'] ?? $this->generateSlug((string) $name),
            'nombre' => (string) $name,
            'title' => $circuit['title'] ?? (string) $name,
            'resumen' => (string) $summary,
            'duracion' => (string) $duration,
            'destino' => (string) $destination,
            'region' => (string) $region,
            'precio' => is_numeric($price) ? (float) $price : null,
            'moneda' => strtoupper((string) ($circuit['moneda'] ?? 'PEN')),
            'imagen' => $heroImage,
            'heroImage' => $heroImage,
            'gallery' => $gallery,
            'frecuencia' => isset($circuit['frecuencia']) ? (string) $circuit['frecuencia'] : $nextDepartureText,
            'proximaSalida' => $nextDepartureText,
            'grupo' => $groupText,
            'experiencia' => (string) ($circuit['experiencia'] ?? $circuit['dificultad'] ?? ''),
            'ratingPromedio' => $ratingValue,
            'totalResenas' => $reviewsCount,
            'esNuevo' => $parseBoolean($rawIsNew),
            'esExclusivo' => $parseBoolean($rawIsExclusive),
            'servicios' => $services,
        ]);

        $circuitId = (int) ($base['id'] ?? 0);
        if ($circuitId > 0) {
            $extras = $this->loadCircuitExtras($circuitId);
            if (!empty($extras['includes'])) {
                $base['servicios'] = $extras['includes'];
            }
            if (!empty($extras['includes']) || !empty($extras['excludes'])) {
                $essentials = [];
                if (!empty($extras['includes'])) {
                    $essentials[] = ['title' => 'Incluye', 'items' => $extras['includes']];
                }
                if (!empty($extras['excludes'])) {
                    $essentials[] = ['title' => 'No incluye', 'items' => $extras['excludes']];
                }
                $base['essentials'] = $essentials;
            }
            if (!empty($extras['itinerary'])) {
                $base['itinerario'] = $extras['itinerary'];
            }
        }

        return $base;
    }

    private function loadCircuitExtras(int $circuitId): array
    {
        if (isset($this->extrasCache[$circuitId])) {
            return $this->extrasCache[$circuitId];
        }

        $extras = [
            'includes' => [],
            'excludes' => [],
            'itinerary' => [],
        ];

        try {
            $pdo = Conexion::obtener();

            $servicesStmt = $pdo->prepare(
                'SELECT cs.tipo, sc.nombre
                 FROM circuito_servicios cs
                 JOIN servicios_catalogo sc ON sc.id = cs.servicio_id
                 WHERE cs.circuito_id = :id
                 ORDER BY cs.tipo, sc.nombre'
            );
            $servicesStmt->execute([':id' => $circuitId]);
            foreach ($servicesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $nombre = trim((string) ($row['nombre'] ?? ''));
                if ($nombre === '') {
                    continue;
                }
                $tipo = ($row['tipo'] ?? '') === 'excluido' ? 'excludes' : 'includes';
                $extras[$tipo][] = $nombre;
            }

            $itineraryStmt = $pdo->prepare(
                'SELECT dia, hora, titulo, descripcion, ubicacion_maps
                 FROM circuito_itinerarios
                 WHERE circuito_id = :id
                 ORDER BY orden, id'
            );
            $itineraryStmt->execute([':id' => $circuitId]);
            foreach ($itineraryStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $titulo = trim((string) ($row['titulo'] ?? ''));
                $descripcion = trim((string) ($row['descripcion'] ?? ''));
                if ($titulo === '' && $descripcion === '') {
                    continue;
                }
                $extras['itinerary'][] = [
                    'dia' => trim((string) ($row['dia'] ?? '')),
                    'hora' => trim((string) ($row['hora'] ?? '')),
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'ubicacion_maps' => trim((string) ($row['ubicacion_maps'] ?? '')),
                ];
            }

        } catch (PDOException $exception) {
            // Mantiene silencioso el fallo para utilizar datos de respaldo si es necesario.
        }

        return $this->extrasCache[$circuitId] = $extras;
    }

    private function parseJsonList($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter(
                    array_map(
                        static fn ($item): ?string => is_string($item) ? trim($item) : null,
                        $decoded
                    ),
                    static fn ($item): bool => $item !== null && $item !== ''
                ));
            }
        }

        return [];
    }

    private function parseImageList($value, string $fallbackAlt = ''): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $items = $decoded;
            } else {
                $items = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];
            }
        }

        $images = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $src = trim($item);
                if ($src === '') {
                    continue;
                }
                $images[] = [
                    'src' => $src,
                    'alt' => $fallbackAlt,
                ];
                continue;
            }

            if (!is_array($item)) {
                continue;
            }

            $src = $item['src'] ?? $item['url'] ?? $item['image'] ?? null;
            if (is_string($src)) {
                $src = trim($src);
            } else {
                $src = '';
            }
            if ($src === '') {
                continue;
            }

            $alt = $item['alt'] ?? $item['label'] ?? $item['title'] ?? '';
            if (!is_string($alt)) {
                $alt = '';
            }
            $alt = trim($alt);
            if ($alt === '') {
                $alt = $fallbackAlt;
            }

            $images[] = [
                'src' => $src,
                'alt' => $alt,
            ];
        }

        return $images;
    }

    private function fallbackCircuits(): array
    {
        return [
            [
                'slug' => 'selva-central-signature',
                'type' => 'Circuito',
                'title' => 'Circuito Esencia Selva Central',
                'tagline' => 'De Oxapampa a Perené entre bosques nubosos, cataratas y pueblos cafeteros.',
                'summary' => "Conecta los imprescindibles de la Selva Central peruana en un circuito que combina aventura, cultura y gastronomía local. Inicia en Oxapampa con sus casonas austroalemanas y su reserva de biosfera, continúa hacia Villa Rica para descubrir sus fincas cafetaleras y culmina en los cañones y cataratas del valle del Perené.\n\nCada jornada equilibra actividades al aire libre con encuentros auténticos con comunidades asháninkas y yaneshas, degustaciones de café de especialidad y espacios para relajarse entre paisajes cubiertos de neblina.",
                'location' => 'Oxapampa, Villa Rica y Perené — Selva Central, Perú',
                'region' => 'Pasco y Junín',
                'duration' => '4 días / 3 noches',
                'dificultad' => 'Moderado',
                'frecuencia' => 'Salidas cada viernes',
                'grupo' => 'Hasta 12 viajeros',
                'proximaSalida' => '15 ago 2024',
                'ratingPromedio' => 4.9,
                'totalResenas' => 132,
                'esNuevo' => true,
                'esExclusivo' => true,
                'precio' => 1450.00,
                'moneda' => 'PEN',
                'priceFrom' => 'Desde S/ 1,450 por viajero',
                'heroImage' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Ruta Selva Central',
                'chips' => ['Aventura suave', 'Café de especialidad', 'Cultura viva'],
                'stats' => [
                    ['label' => 'Dificultad', 'value' => 'Moderada'],
                    ['label' => 'Altitud máxima', 'value' => '1,950 m s. n. m.'],
                    ['label' => 'Temporada ideal', 'value' => 'Abril a octubre'],
                ],
                'highlights' => [
                    [
                        'title' => 'Bosques nubosos de Oxapampa',
                        'description' => 'Senderos interpretativos en Yanachaga-Chemillén acompañados de guardaparques locales.',
                        'icon' => '🌿',
                        'accent' => 'jungle',
                    ],
                    [
                        'title' => 'Ruta del café en Villa Rica',
                        'description' => 'Catación de microlotes, tostado artesanal y maridaje con chocolates amazónicos.',
                        'icon' => '☕',
                        'accent' => 'sunrise',
                    ],
                    [
                        'title' => 'Cataratas del Perené',
                        'description' => 'Salto Bayoz y Velo de la Novia con baños turquesa y picnic de productos regionales.',
                        'icon' => '💦',
                        'accent' => 'lagoon',
                    ],
                ],
                'itinerary' => [
                    [
                        'title' => 'Día 1 · Oxapampa inmersiva',
                        'summary' => 'Llegada, city tour patrimonial y degustación de lácteos artesanales.',
                        'activities' => [
                            'Recepción en el aeropuerto de Oxapampa y traslado boutique.',
                            'City tour entre casonas austroalemanas y mirador La Florida.',
                            'Degustación de quesos madurados y cerveza artesanal local.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Reserva Yanachaga y Villa Rica',
                        'summary' => 'Observación de aves, navegacion en Laguna El Oconal y cata de cafés.',
                        'activities' => [
                            'Trekking ligero en el sector San Alberto guiado por especialistas.',
                            'Visita a finca cafetalera con experiencia de cosecha y beneficio húmedo.',
                            'Atardecer en el mirador La Cumbre con degustación de café filtrado.',
                        ],
                    ],
                    [
                        'title' => 'Día 3 · Valles del Perené',
                        'summary' => 'Cataratas, comunidades asháninkas y paseos en bote.',
                        'activities' => [
                            'Caminata y baño refrescante en Bayoz y Velo de la Novia.',
                            'Almuerzo tradicional en comunidad asháninka y presentación de danzas.',
                            'Paseo en bote por el río Perené con interpretación cultural.',
                        ],
                    ],
                    [
                        'title' => 'Día 4 · Despedida y relax',
                        'summary' => 'Mañana libre y traslado a la ciudad de origen.',
                        'activities' => [
                            'Sesión de bienestar con infusiones amazónicas y masajes con aceites nativos.',
                            'Tiempo libre para compras en ferias de productores y artesanos.',
                            'Traslado privado al aeropuerto de Oxapampa o terminal de buses.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Picnic de sabores amazónicos',
                        'description' => 'Degusta snacks saludables, frutas exóticas y chocolates bean-to-bar frente a una cascada.',
                        'icon' => '🍃',
                    ],
                    [
                        'title' => 'Taller de cestería yanesha',
                        'description' => 'Aprende a tejer con fibras naturales junto a maestras artesanas.',
                        'icon' => '🧺',
                    ],
                    [
                        'title' => 'Atardecer en la Laguna El Oconal',
                        'description' => 'Avistamiento de aves en kayak y sesión fotográfica dorada.',
                        'icon' => '🛶',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Traslados privados durante todo el circuito.',
                            'Alojamiento boutique con desayuno local.',
                            'Guías bilingües especializados en naturaleza y cultura.',
                            'Entradas a reservas, cataratas y experiencias comunitarias.',
                        ],
                    ],
                    [
                        'title' => 'Recomendaciones',
                        'items' => [
                            'Empacar ropa ligera de secado rápido y casaca impermeable.',
                            'Llevar repelente ecológico y protector solar biodegradable.',
                            'Respetar los protocolos de visita a comunidades originarias.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Reservar circuito',
                    'primaryHref' => 'explorar.php?categoria=circuitos&slug=selva-central-signature',
                    'secondaryLabel' => 'Consultar asesor experto',
                    'secondaryHref' => 'mailto:viajes@expediatravels.com',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Carretera selvática con neblina'],
                    ['src' => 'https://images.unsplash.com/photo-1529270291606-d01266d631de?auto=format&fit=crop&w=800&q=80', 'alt' => 'Barista preparando café filtrado'],
                    ['src' => 'https://images.unsplash.com/photo-1503249023995-51b0f3778ccf?auto=format&fit=crop&w=800&q=80', 'alt' => 'Viajera observando cascada tropical'],
                ],
                'related' => [
                    [
                        'badge' => 'Destino',
                        'title' => 'Oxapampa Esencial',
                        'summary' => 'Tres días entre casonas, cataratas y reservas de biosfera.',
                        'href' => 'destino.php?slug=oxapampa',
                        'image' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Paquete',
                        'title' => 'Escapada Pozuzo Boutique',
                        'summary' => 'Experiencia de fin de semana con hospedaje histórico y gastronomía fusión.',
                        'href' => 'paquete.php?slug=pozuzo-boutique',
                        'image' => 'https://images.unsplash.com/photo-1499678329028-101435549a4e?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Circuito',
                        'title' => 'Aventura Cataratas Perené',
                        'summary' => 'Recorrido lleno de adrenalina y contacto comunitario.',
                        'href' => 'circuito.php?slug=aventura-perene',
                        'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
            [
                'slug' => 'aventura-perene',
                'type' => 'Circuito',
                'title' => 'Aventura Cataratas del Perené',
                'tagline' => 'Rafting, caminatas y cultura asháninka en dos días vibrantes.',
                'summary' => "Explora el valle del Perené en un circuito exprés pensado para viajeros activos. Combina rápidos clase II en el río, caminatas a imponentes cataratas y experiencias vivas con comunidades originarias.",
                'location' => 'Valle del Perené — Junín, Perú',
                'region' => 'Junín',
                'duration' => '2 días / 1 noche',
                'dificultad' => 'Activo',
                'frecuencia' => 'Próxima salida 22 ago 2024',
                'grupo' => 'Hasta 10 viajeros',
                'proximaSalida' => '22 ago 2024',
                'ratingPromedio' => 4.7,
                'totalResenas' => 98,
                'esNuevo' => false,
                'esExclusivo' => false,
                'precio' => 680.00,
                'moneda' => 'PEN',
                'priceFrom' => 'Desde S/ 680 por viajero',
                'heroImage' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Valle del Perené',
                'chips' => ['Rafting', 'Cataratas', 'Cultura'],
                'stats' => [
                    ['label' => 'Dificultad', 'value' => 'Activa'],
                    ['label' => 'Altitud', 'value' => '720 m s. n. m.'],
                    ['label' => 'Salidas', 'value' => 'Martes, jueves y sábados'],
                ],
                'highlights' => [
                    [
                        'title' => 'Rafting río Perené',
                        'description' => 'Descenso guiado con seguridad integral y rescate especializado.',
                        'icon' => '🚣',
                        'accent' => 'lagoon',
                    ],
                    [
                        'title' => 'Catarata Velo de la Novia',
                        'description' => 'Cascada icónica con pozas naturales para nadar y fotografiar.',
                        'icon' => '💧',
                        'accent' => 'aurora',
                    ],
                    [
                        'title' => 'Noche en eco-lodge',
                        'description' => 'Hospédate en bungalows rodeados de jardines tropicales.',
                        'icon' => '🏡',
                        'accent' => 'sunrise',
                    ],
                ],
                'itinerary' => [
                    [
                        'title' => 'Día 1 · Agua y adrenalina',
                        'summary' => 'Rafting, caminata y atardecer en mirador comunitario.',
                        'activities' => [
                            'Briefing de seguridad y equipamiento profesional incluido.',
                            'Rafting nivel intermedio (2.5 horas) con fotografías digitales.',
                            'Caminata a la catarata Velo de la Novia y picnic saludable.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Cultura y bienestar',
                        'summary' => 'Experiencia asháninka y sesión de hidroterapia natural.',
                        'activities' => [
                            'Ceremonia de bienvenida y taller de pintura facial tradicional.',
                            'Recorrido por vivero de plantas medicinales y huertos comunitarios.',
                            'Tiempo libre en pozas termales naturales antes del retorno.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Yoga tropical al amanecer',
                        'description' => 'Sesión guiada en deck de madera con sonidos del bosque.',
                        'icon' => '🧘',
                    ],
                    [
                        'title' => 'Degustación de chocolates nativos',
                        'description' => 'Cacao chuncho y frutas deshidratadas con maridaje de infusiones.',
                        'icon' => '🍫',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Transporte turístico desde La Merced.',
                            'Equipamiento técnico para rafting.',
                            'Guías certificados en rescate acuático.',
                            'Una noche de alojamiento con desayuno.',
                        ],
                    ],
                    [
                        'title' => 'Que llevar',
                        'items' => [
                            'Sandalias de río o zapatillas de agua.',
                            'Bolsa estanca para pertenencias personales.',
                            'Cambio de ropa ligera y toalla de secado rápido.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Elegir fecha',
                    'primaryHref' => 'explorar.php?categoria=circuitos&slug=aventura-perene',
                    'secondaryLabel' => 'Ver más circuitos',
                    'secondaryHref' => 'explorar.php?categoria=circuitos',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=800&q=80', 'alt' => 'Rafting en río tropical'],
                    ['src' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=800&q=80', 'alt' => 'Catarata entre la vegetación'],
                    ['src' => 'https://images.unsplash.com/photo-1499696010181-2cb40af7859d?auto=format&fit=crop&w=800&q=80', 'alt' => 'Comunidad asháninka compartiendo artesanías'],
                ],
                'related' => [
                    [
                        'badge' => 'Paquete',
                        'title' => 'Full Day Selva Mágica',
                        'summary' => 'Un día para visitar Bayoz, Velo de la Novia y mariposario.',
                        'href' => 'paquete.php?slug=selva-magica',
                        'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Destino',
                        'title' => 'Perené',
                        'summary' => 'Puerta a cataratas y comunidades vibrantes.',
                        'href' => 'destino.php?slug=perene',
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
        ];
    }

    private function generateSlug(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $normalized = strtolower(trim((string) $normalized));
        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $normalized);
        if (!is_string($normalized)) {
            $normalized = '';
        }
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'circuito';
    }
}
