<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioPaquetes
{
    /**
     * Devuelve los paquetes publicados más recientes para destacarlos en la página de inicio.
     */
    public function getFeatured(int $limit = 4): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT p.id, p.nombre, p.resumen, p.duracion, p.precio, p.moneda, p.itinerario,
                        COALESCE(p.imagen_destacada, p.imagen_portada) AS imagen,
                        destino_destacado.nombre AS destino, destino_destacado.region
                 FROM paquetes p
                 INNER JOIN (
                     SELECT pd.paquete_id,
                            MIN(d.nombre) AS nombre,
                            MIN(d.region) AS region
                     FROM paquete_destinos pd
                     INNER JOIN destinos d ON d.id = pd.destino_id
                         AND d.estado = "activo"
                         AND d.mostrar_en_buscador = 1
                     GROUP BY pd.paquete_id
                 ) AS destino_destacado ON destino_destacado.paquete_id = p.id
                 WHERE p.estado = "publicado"
                   AND p.estado_publicacion = "publicado"
                   AND p.visibilidad = "publico"
                   AND (p.vigencia_desde IS NULL OR p.vigencia_desde <= NOW())
                   AND (p.vigencia_hasta IS NULL OR p.vigencia_hasta >= NOW())
                 ORDER BY p.creado_en DESC
                 LIMIT :limit'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();

            $packages = $statement->fetchAll();
            if ($packages) {
                return array_map(fn (array $package) => $this->hydratePackage($package), $packages);
            }
        } catch (PDOException $exception) {
            // Usa datos de respaldo cuando la base de datos no está disponible.
        }

        return array_slice($this->fallbackPackages(), 0, $limit);
    }

    public function getSignatureExperiences(): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->query(
                'SELECT p.id, p.nombre, p.resumen, p.duracion, p.precio, p.moneda,
                        COALESCE(p.imagen_destacada, p.imagen_portada) AS imagen,
                        destino_destacado.nombre AS destino, destino_destacado.region
                 FROM paquetes p
                 INNER JOIN (
                     SELECT pd.paquete_id,
                            MIN(d.nombre) AS nombre,
                            MIN(d.region) AS region
                     FROM paquete_destinos pd
                     INNER JOIN destinos d ON d.id = pd.destino_id
                         AND d.estado = "activo"
                         AND d.mostrar_en_buscador = 1
                     GROUP BY pd.paquete_id
                 ) AS destino_destacado ON destino_destacado.paquete_id = p.id
                 WHERE p.estado = "publicado"
                   AND p.estado_publicacion = "publicado"
                   AND p.visibilidad = "publico"
                   AND (p.vigencia_desde IS NULL OR p.vigencia_desde <= NOW())
                   AND (p.vigencia_hasta IS NULL OR p.vigencia_hasta >= NOW())
                 ORDER BY p.precio DESC
                 LIMIT 6'
            );
            $packages = $statement->fetchAll();
            if ($packages) {
                return array_map(fn (array $package) => $this->hydratePackage($package), $packages);
            }
        } catch (PDOException $exception) {
            // Usa datos de respaldo en silencio.
        }

        return $this->fallbackPackages();
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        $packages = $this->fallbackPackages();

        if ($slug === '') {
            return $packages[0] ?? null;
        }

        foreach ($packages as $package) {
            if (($package['slug'] ?? '') === $slug) {
                return $package;
            }
        }

        return $packages[0] ?? null;
    }

    private function hydratePackage(array $package): array
    {
        $name = (string) ($package['nombre'] ?? '');
        $price = $package['precio'] ?? null;
        if (is_string($price)) {
            $price = str_replace(',', '', $price);
        }

        return [
            'id' => (int) ($package['id'] ?? 0),
            'nombre' => $name,
            'resumen' => $package['resumen'] ?? '',
            'duracion' => $package['duracion'] ?? '',
            'precio' => is_numeric($price) ? (float) $price : null,
            'moneda' => strtoupper((string) ($package['moneda'] ?? 'PEN')),
            'destino' => $package['destino'] ?? '',
            'region' => $package['region'] ?? '',
            'itinerario' => $package['itinerario'] ?? null,
            'imagen' => $package['imagen'] ?? null,
            'slug' => $package['slug'] ?? $this->generateSlug($name),
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

        return $normalized !== '' ? $normalized : 'paquete';
    }

    private function fallbackPackages(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'oxapampa-slow',
                'type' => 'Paquete',
                'nombre' => 'Oxapampa Slow Travel',
                'resumen' => 'Tres días con alojamiento boutique, talleres gastronómicos y caminatas suaves.',
                'duracion' => '3 días / 2 noches',
                'precio' => 890.00,
                'destino' => 'Oxapampa',
                'region' => 'Pasco',
                'itinerario' => 'City tour patrimonial, sendero Yanachaga, ruta del café y picnic gourmet.',
                'imagen' => 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Vive Oxapampa a ritmo pausado con experiencias curadas.',
                'summary' => "Hospédate en lodges boutique rodeados de bosques nubosos, degusta cocina de autor con insumos amazónicos y explora la reserva Yanachaga-Chemillén acompañado de guías especialistas.\n\nEl paquete Slow Travel prioriza el encuentro con productores locales, talleres gastronómicos y momentos de bienestar diseñados para reconectar con la naturaleza.",
                'location' => 'Oxapampa — Pasco, Perú',
                'duration' => '3 días / 2 noches',
                'priceFrom' => 'Desde S/ 890 por persona',
                'heroImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Oxapampa slow travel',
                'chips' => ['Bienestar', 'Gastronomía', 'Naturaleza'],
                'stats' => [
                    ['label' => 'Grupo', 'value' => 'Máx. 10 viajeros'],
                    ['label' => 'Operación', 'value' => 'Salidas diarias'],
                    ['label' => 'Flexibilidad', 'value' => 'Cambios hasta 48h antes'],
                ],
                'highlights' => [
                    [
                        'title' => 'Lodge boutique entre bosques',
                        'description' => 'Habitaciones con chimenea, piscina climatizada y spa natural.',
                        'icon' => '🏡',
                        'accent' => 'sunrise',
                    ],
                    [
                        'title' => 'Ruta gourmet amazónica',
                        'description' => 'Clases de cocina participativas y cenas maridadas con café especialidad.',
                        'icon' => '🍽️',
                        'accent' => 'aurora',
                    ],
                    [
                        'title' => 'Sendero Yanachaga a tu ritmo',
                        'description' => 'Caminata interpretativa con avistamiento de aves y orquídeas.',
                        'icon' => '🌿',
                        'accent' => 'jungle',
                    ],
                ],
                'itinerary_detallado' => [
                    [
                        'title' => 'Día 1 · Llegada y experiencias gourmet',
                        'summary' => 'Bienvenida con mixología amazónica y cena maridada.',
                        'activities' => [
                            'Traslado privado desde el aeropuerto y check-in en lodge boutique.',
                            'Taller de cocina amazónica con chef residente.',
                            'Cena de bienvenida con maridaje de café y cacao.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Reserva Yanachaga y bienestar',
                        'summary' => 'Trekking suave, picnic en cascada y ritual de bienestar.',
                        'activities' => [
                            'Sendero San Alberto con guía naturalista.',
                            'Picnic gourmet frente a cascada escondida.',
                            'Sesión de spa con productos orgánicos locales.',
                        ],
                    ],
                    [
                        'title' => 'Día 3 · Café y despedida',
                        'summary' => 'Catación profesional y visita a mercado de productores.',
                        'activities' => [
                            'Visita a finca cafetalera y taller de catación.',
                            'Tiempo libre en mercado de productores locales.',
                            'Traslado al aeropuerto o terminal.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Clase privada de barismo',
                        'description' => 'Aprende a preparar filtrados de autor junto a un campeón nacional.',
                        'icon' => '☕',
                    ],
                    [
                        'title' => 'Terapia de bosque',
                        'description' => 'Práctica de mindfulness guiada entre árboles centenarios.',
                        'icon' => '🌲',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Alojamiento boutique con desayuno.',
                            'Traslados privados y guía bilingüe.',
                            'Actividades y entradas mencionadas.',
                            'Seguro de viaje básico.',
                        ],
                    ],
                    [
                        'title' => 'Extras opcionales',
                        'items' => [
                            'Upgrade a suite con jacuzzi panorámico.',
                            'Sesión fotográfica profesional.',
                            'Extensión a Pozuzo o Villa Rica.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Reservar paquete',
                    'primaryHref' => 'explorar.php?categoria=paquetes&slug=oxapampa-slow',
                    'secondaryLabel' => 'Hablar con especialista',
                    'secondaryHref' => 'mailto:viajes@expediatravels.com',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1499696010181-2cb40af7859d?auto=format&fit=crop&w=800&q=80', 'alt' => 'Habitación boutique en lodge de montaña'],
                    ['src' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', 'alt' => 'Plato gourmet con ingredientes amazónicos'],
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Sendero boscoso en Oxapampa'],
                ],
                'related' => [
                    [
                        'badge' => 'Destino',
                        'title' => 'Oxapampa',
                        'summary' => 'Descubre el encanto patrimonial y natural de la capital cafetalera.',
                        'href' => 'destino.php?slug=oxapampa',
                        'image' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Circuito',
                        'title' => 'Esencia Selva Central',
                        'summary' => 'Circuito de 4 días enlazando Oxapampa, Villa Rica y Perené.',
                        'href' => 'circuito.php?slug=selva-central-signature',
                        'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
            [
                'id' => 2,
                'slug' => 'pozuzo-boutique',
                'type' => 'Paquete',
                'nombre' => 'Pozuzo Heritage Boutique',
                'resumen' => 'Fin de semana en la colonia austroalemana con experiencias gastronómicas únicas.',
                'duracion' => '2 días / 1 noche',
                'precio' => 650.00,
                'destino' => 'Pozuzo',
                'region' => 'Pasco',
                'itinerario' => 'City tour histórico, ruta cervecera y caminata a cascadas coloniales.',
                'imagen' => 'https://images.unsplash.com/photo-1499678329028-101435549a4e?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Historia, naturaleza y gastronomía austro-peruana en un mismo viaje.',
                'summary' => "Descubre la historia de la colonia austroalemana de Pozuzo con hospedaje en casonas patrimoniales, degustaciones de cerveza artesanal y caminatas por bosques templados. Incluye encuentros culturales con descendientes colonos y show musical tradicional.",
                'location' => 'Pozuzo — Pasco, Perú',
                'duration' => '2 días / 1 noche',
                'priceFrom' => 'Desde S/ 650 por persona',
                'heroImage' => 'https://images.unsplash.com/photo-1499678329028-101435549a4e?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1526401281623-359fff0ed1d3?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Pozuzo',
                'chips' => ['Historia viva', 'Cerveza artesanal', 'Cascadas'],
                'stats' => [
                    ['label' => 'Incluye', 'value' => 'Pensión completa'],
                    ['label' => 'Alojamiento', 'value' => 'Hacienda patrimonial'],
                    ['label' => 'Salidas', 'value' => 'Viernes a domingo'],
                ],
                'highlights' => [
                    [
                        'title' => 'Casonas patrimoniales',
                        'description' => 'Hospédate en habitaciones restauradas con mobiliario original.',
                        'icon' => '🏰',
                        'accent' => 'sunrise',
                    ],
                    [
                        'title' => 'Ruta cervecera',
                        'description' => 'Degustación guiada en micro cervecería con recetas familiares.',
                        'icon' => '🍺',
                        'accent' => 'aurora',
                    ],
                    [
                        'title' => 'Cascadas coloniales',
                        'description' => 'Caminata a Yulitunqui y Pozuzo Falls con picnic campestre.',
                        'icon' => '💦',
                        'accent' => 'lagoon',
                    ],
                ],
                'itinerary_detallado' => [
                    [
                        'title' => 'Día 1 · Historia viva',
                        'summary' => 'City tour, museo y degustación de strudel.',
                        'activities' => [
                            'Traslado desde Oxapampa por carretera panorámica.',
                            'City tour guiado por descendientes colonos.',
                            'Cena típica con espectáculo de danzas tirolesas.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Sabores y naturaleza',
                        'summary' => 'Ruta cervecera y caminata a cascadas.',
                        'activities' => [
                            'Desayuno buffet con productos artesanales.',
                            'Visita a cervecería y taller de embotellado.',
                            'Caminata guiada a cascadas y almuerzo campestre.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Clase de repostería alemana',
                        'description' => 'Prepara strudel tradicional con recetas familiares.',
                        'icon' => '🥨',
                    ],
                    [
                        'title' => 'Taller de música tirolesa',
                        'description' => 'Aprende ritmos y bailes con músicos locales.',
                        'icon' => '🎻',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Transporte privado ida y vuelta desde Oxapampa.',
                            'Pensión completa con bebidas artesanales.',
                            'Guía historiador y actividades descritas.',
                        ],
                    ],
                    [
                        'title' => 'Qué empacar',
                        'items' => [
                            'Chompa ligera para noches frescas.',
                            'Zapatos cómodos para caminatas.',
                            'Curiosidad por nuevas fusiones gastronómicas.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Reservar fin de semana',
                    'primaryHref' => 'explorar.php?categoria=paquetes&slug=pozuzo-boutique',
                    'secondaryLabel' => 'Ver mapa interactivo',
                    'secondaryHref' => '#galeria',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Paisaje natural alrededor de Pozuzo'],
                    ['src' => 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?auto=format&fit=crop&w=800&q=80', 'alt' => 'Strudel recién horneado'],
                    ['src' => 'https://images.unsplash.com/photo-1526401281623-359fff0ed1d3?auto=format&fit=crop&w=800&q=80', 'alt' => 'Arquitectura colonial en Pozuzo'],
                ],
                'related' => [
                    [
                        'badge' => 'Destino',
                        'title' => 'Pozuzo',
                        'summary' => 'Historia, cultura y naturaleza en la colonia austroalemana.',
                        'href' => 'destino.php?slug=oxapampa',
                        'image' => 'https://images.unsplash.com/photo-1499678329028-101435549a4e?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Circuito',
                        'title' => 'Esencia Selva Central',
                        'summary' => 'Incluye visita a Pozuzo y experiencias cafetaleras.',
                        'href' => 'circuito.php?slug=selva-central-signature',
                        'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
            [
                'id' => 3,
                'slug' => 'selva-magica',
                'type' => 'Paquete',
                'nombre' => 'Full Day Selva Mágica',
                'resumen' => 'Visita en un día Bayoz, Velo de la Novia y mariposario con almuerzo amazónico.',
                'duracion' => '1 día',
                'precio' => 280.00,
                'destino' => 'Perené',
                'region' => 'Junín',
                'itinerario' => 'Traslado desde La Merced, cataratas principales y experiencias comunitarias.',
                'imagen' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Un día intenso para reconectar con cataratas y cultura asháninka.',
                'summary' => "Ideal para viajeros con poco tiempo que desean vivir lo mejor del valle del Perené. Incluye caminatas accesibles, baños en pozas turquesa y almuerzo tradicional con ingredientes nativos.",
                'location' => 'Valle del Perené — Junín, Perú',
                'duration' => '1 día',
                'priceFrom' => 'Desde S/ 280 por persona',
                'heroImage' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Cataratas del Perené',
                'chips' => ['Full day', 'Cataratas', 'Cultura'],
                'stats' => [
                    ['label' => 'Horario', 'value' => '06:30 - 19:30'],
                    ['label' => 'Salidas', 'value' => 'Diarias'],
                    ['label' => 'Tamaño de grupo', 'value' => 'Máx. 14 pasajeros'],
                ],
                'highlights' => [
                    [
                        'title' => 'Catarata Bayoz',
                        'description' => 'Pozas naturales para nadar y tomar fotografías espectaculares.',
                        'icon' => '💧',
                        'accent' => 'lagoon',
                    ],
                    [
                        'title' => 'Comunidad asháninka',
                        'description' => 'Danzas tradicionales, artesanías y bebida masato.',
                        'icon' => '🪶',
                        'accent' => 'aurora',
                    ],
                    [
                        'title' => 'Mariposario Zhaveta Yard',
                        'description' => 'Recorrido guiado por viveros de mariposas y plantas medicinales.',
                        'icon' => '🦋',
                        'accent' => 'sunrise',
                    ],
                ],
                'itinerary_detallado' => [
                    [
                        'title' => 'Mañana · Cataratas icónicas',
                        'summary' => 'Bayoz y Velo de la Novia con guías naturalistas.',
                        'activities' => [
                            'Traslado desde La Merced con desayuno ligero.',
                            'Caminata a Bayoz y tiempo libre en las pozas.',
                            'Visita a Velo de la Novia y sesión fotográfica.',
                        ],
                    ],
                    [
                        'title' => 'Tarde · Cultura y sabores',
                        'summary' => 'Comunidad asháninka y mariposario.',
                        'activities' => [
                            'Almuerzo tradicional con ingredientes nativos.',
                            'Presentación cultural y taller de artesanías.',
                            'Recorrido guiado en mariposario Zhaveta Yard.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Picnic saludable',
                        'description' => 'Snacks locales, frutas frescas y bebidas naturales.',
                        'icon' => '🍍',
                    ],
                    [
                        'title' => 'Taller de tintes naturales',
                        'description' => 'Aprende sobre pigmentos amazónicos y crea tu souvenir.',
                        'icon' => '🎨',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Transporte turístico ida y vuelta.',
                            'Entradas, almuerzo y guías certificados.',
                            'Equipo de seguridad para caminatas.',
                        ],
                    ],
                    [
                        'title' => 'Qué llevar',
                        'items' => [
                            'Ropa ligera y traje de baño.',
                            'Calzado antideslizante.',
                            'Protector solar y repelente.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Reservar full day',
                    'primaryHref' => 'explorar.php?categoria=paquetes&slug=selva-magica',
                    'secondaryLabel' => 'Explorar circuitos cercanos',
                    'secondaryHref' => 'circuito.php?slug=aventura-perene',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=800&q=80', 'alt' => 'Catarata Bayoz'],
                    ['src' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=800&q=80', 'alt' => 'Río tropical'],
                    ['src' => 'https://images.unsplash.com/photo-1499696010181-2cb40af7859d?auto=format&fit=crop&w=800&q=80', 'alt' => 'Artesanías asháninkas'],
                ],
                'related' => [
                    [
                        'badge' => 'Destino',
                        'title' => 'Perené',
                        'summary' => 'Explora el valle completo con rutas de aventura.',
                        'href' => 'destino.php?slug=perene',
                        'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
        ];
    }
}
