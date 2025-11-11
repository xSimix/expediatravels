<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDO;
use PDOException;

class RepositorioDestinos
{
    /**
     * Obtiene una lista curada de destinos destacados.
     */
    public function getHighlights(int $limit = 4): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->prepare(
                'SELECT id, nombre, descripcion, region, imagen, imagen_destacada, tagline, mostrar_en_buscador, mostrar_en_explorador
                 FROM destinos
                 WHERE estado = "activo"
                   AND mostrar_en_explorador = 1
                 ORDER BY id
                 LIMIT :limit'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();

            $destinations = $statement->fetchAll();
            if ($destinations) {
                return array_map(fn (array $destination) => $this->hydrateDestination($destination), $destinations);
            }
        } catch (PDOException $exception) {
            // Ignora la excepción para usar datos de respaldo en la página de inicio.
        }

        return array_slice($this->fallbackDestinations(), 0, $limit);
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        $destinations = $this->fallbackDestinations();

        if ($slug === '') {
            return $destinations[0] ?? null;
        }

        foreach ($destinations as $destination) {
            if (($destination['slug'] ?? '') === $slug) {
                return $destination;
            }
        }

        return $destinations[0] ?? null;
    }

    private function hydrateDestination(array $destination): array
    {
        $name = (string) ($destination['nombre'] ?? '');

        return [
            'id' => (int) ($destination['id'] ?? 0),
            'nombre' => $name,
            'descripcion' => $destination['descripcion'] ?? '',
            'region' => $destination['region'] ?? '',
            'imagen' => $destination['imagen'] ?? null,
            'imagen_destacada' => $destination['imagen_destacada'] ?? null,
            'tagline' => $destination['tagline'] ?? null,
            'slug' => $destination['slug'] ?? $this->generateSlug($name),
            'mostrar_en_buscador' => $this->normalizeVisibility($destination['mostrar_en_buscador'] ?? $destination['mostrarEnBuscador'] ?? true),
            'mostrar_en_explorador' => $this->normalizeVisibility($destination['mostrar_en_explorador'] ?? $destination['mostrarEnExplorador'] ?? true),
        ];
    }

    private function normalizeVisibility($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return false;
        }

        return in_array($text, ['1', 'true', 'si', 'sí', 'visible', 'on', 'activo'], true);
    }

    private function fallbackDestinations(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'oxapampa',
                'type' => 'Destino',
                'nombre' => 'Oxapampa',
                'descripcion' => 'Capital cafetalera y puerta de entrada a la Reserva de Biosfera Oxapampa-Ashaninka-Yanesha.',
                'region' => 'Pasco',
                'imagen' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Bosques nubosos, arquitectura austroalemana y cafés de autor.',
                'summary' => "Oxapampa combina paisajes de bosques nubosos con una identidad cultural única. Sus casonas de madera, la gastronomía austroalemana y las reservas naturales cercanas la convierten en la base perfecta para explorar la Selva Central con comodidad.\n\nDesde miradores panorámicos hasta talleres de chocolate bean-to-bar, cada rincón de la ciudad sorprende con experiencias auténticas guiadas por anfitriones locales.",
                'location' => 'Oxapampa — Pasco, Perú',
                'duration' => 'Estadía ideal de 3 a 4 días',
                'priceFrom' => 'Programas desde S/ 520',
                'heroImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Oxapampa, Pasco',
                'chips' => ['Reserva de biosfera', 'Cultura austroalemana', 'Café de altura'],
                'stats' => [
                    ['label' => 'Altitud', 'value' => '1,814 m s. n. m.'],
                    ['label' => 'Clima', 'value' => 'Templado húmedo'],
                    ['label' => 'Mejor época', 'value' => 'Abril a octubre'],
                ],
                'highlights' => [
                    [
                        'title' => 'Reserva Yanachaga-Chemillén',
                        'description' => 'Senderos interpretativos, observación de aves y orquídeas en bosques nubosos.',
                        'icon' => '🦜',
                        'accent' => 'jungle',
                    ],
                    [
                        'title' => 'Arquitectura patrimonial',
                        'description' => 'Casonas de madera, museos y herencia austroalemana en cada calle.',
                        'icon' => '🏡',
                        'accent' => 'sunrise',
                    ],
                    [
                        'title' => 'Sabores locales',
                        'description' => 'Café especialidad, lácteos artesanales y cocina de autor con insumos amazónicos.',
                        'icon' => '☕',
                        'accent' => 'aurora',
                    ],
                ],
                'itinerary' => [
                    [
                        'title' => 'Día 1 · Bienvenida y city tour',
                        'summary' => 'Recorrido por casonas históricas y degustación de postres coloniales.',
                        'activities' => [
                            'Ingreso al Parque Nacional de Oxapampa y mirador La Florida.',
                            'Visita al museo Settler y taller de cerámica local.',
                            'Cena de bienvenida con menú maridado de cerveza artesanal.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Naturaleza y café',
                        'summary' => 'Trekking ligero y experiencia cafetalera interactiva.',
                        'activities' => [
                            'Sendero San Alberto en Yanachaga con guardaparques.',
                            'Taller de catación y barismo en finca familiar.',
                            'Tarde libre para explorar galerías y chocolaterías.',
                        ],
                    ],
                    [
                        'title' => 'Día 3 · Cultura viva',
                        'summary' => 'Encuentro con comunidad yanesha y música tradicional.',
                        'activities' => [
                            'Ceremonia de bienvenida con líderes comunales.',
                            'Demostración de cestería y tintes naturales.',
                            'Show de música tirolesa en la plaza principal.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Ruta del cacao bean-to-bar',
                        'description' => 'Aprende el proceso completo del chocolate y crea tu propia barra gourmet.',
                        'icon' => '🍫',
                        'price' => 185.00,
                        'currency' => 'PEN',
                        'priceNote' => 'por viajero',
                    ],
                    [
                        'title' => 'Bike tour cafetalero',
                        'description' => 'Recorre fincas sostenibles en bicicleta eléctrica con guías expertos.',
                        'icon' => '🚲',
                        'price' => 210.00,
                        'currency' => 'PEN',
                        'priceNote' => 'incluye equipo y guía',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'No te olvides de',
                        'items' => [
                            'Reservar con anticipación visitas a reservas protegidas.',
                            'Empacar capas ligeras: días soleados y noches frescas.',
                            'Contratar guías locales para experiencias personalizadas.',
                        ],
                    ],
                    [
                        'title' => 'Sabores imperdibles',
                        'items' => [
                            'Café filtrado de micro lotes Villa Rica.',
                            'Quesos madurados y mantequillas artesanales.',
                            'Strudel de manzana y platos asháninkas.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Diseñar mi viaje',
                    'primaryHref' => 'explorar.php?categoria=destinos&slug=oxapampa',
                    'secondaryLabel' => 'Ver circuitos sugeridos',
                    'secondaryHref' => 'circuito.php?slug=selva-central-signature',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Paisaje montañoso en Oxapampa'],
                    ['src' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', 'alt' => 'Barista preparando café de especialidad'],
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Bosque nublado en la reserva'],
                ],
                'related' => [
                    [
                        'badge' => 'Circuito',
                        'title' => 'Esencia Selva Central',
                        'summary' => 'Circuito de cuatro días entre Oxapampa, Villa Rica y Perené.',
                        'href' => 'circuito.php?slug=selva-central-signature',
                        'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Paquete',
                        'title' => 'Oxapampa Slow Travel',
                        'summary' => 'Experiencia boutique con hospedajes con encanto.',
                        'href' => 'paquete.php?slug=oxapampa-slow',
                        'image' => 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
            [
                'id' => 2,
                'slug' => 'villa-rica',
                'type' => 'Destino',
                'nombre' => 'Villa Rica',
                'descripcion' => 'Tierra del café de altura y de la Laguna El Oconal.',
                'region' => 'Pasco',
                'imagen' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Capital cafetalera de la Selva Central y refugio de aves acuáticas.',
                'summary' => "Villa Rica seduce a los amantes del café con fincas familiares, laboratorios sensoriales y un paisaje agrícola rodeado de neblina. La Laguna El Oconal aporta serenidad con observatorios de aves y tratamientos de ictioterapia.\n\nEntre tostadores de café y talleres de barismo, el destino ofrece experiencias inmersivas que conectan directamente con productores comprometidos con prácticas sostenibles.",
                'location' => 'Villa Rica — Pasco, Perú',
                'duration' => 'Escapada ideal de 2 a 3 días',
                'priceFrom' => 'Experiencias desde S/ 360',
                'heroImage' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Villa Rica, Pasco',
                'chips' => ['Café de autor', 'Lagunas sagradas', 'Observación de aves'],
                'stats' => [
                    ['label' => 'Altitud', 'value' => '1,470 m s. n. m.'],
                    ['label' => 'Actividades', 'value' => 'Cataciones, kayak, ictioterapia'],
                    ['label' => 'Clima', 'value' => 'Templado lluvioso'],
                ],
                'highlights' => [
                    [
                        'title' => 'Laguna El Oconal',
                        'description' => 'Kayak, observación de aves y tratamientos de ictioterapia.',
                        'icon' => '🛶',
                        'accent' => 'lagoon',
                    ],
                    [
                        'title' => 'Laboratorio de café',
                        'description' => 'Catación guiada por Q-graders y tostiones experimentales.',
                        'icon' => '☕',
                        'accent' => 'sunrise',
                    ],
                    [
                        'title' => 'Mirador La Cumbre',
                        'description' => 'Atardeceres naranjas sobre plantaciones de café y nubes bajas.',
                        'icon' => '🌅',
                        'accent' => 'aurora',
                    ],
                ],
                'itinerary' => [
                    [
                        'title' => 'Día 1 · Mundo cafetalero',
                        'summary' => 'Experiencia de recolección, beneficio y catación.',
                        'activities' => [
                            'Recepción en finca boutique y recorrido por cafetales.',
                            'Taller de tostado y preparación en métodos filtrados.',
                            'Almuerzo maridado con cacao y frutas amazónicas.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Laguna El Oconal',
                        'summary' => 'Kayak al amanecer y spa natural de ictioterapia.',
                        'activities' => [
                            'Observación de aves desde hides fotográficos.',
                            'Kayak o paddle board en la laguna.',
                            'Relajación con pececillos terapéuticos y ritual de infusiones.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Brunch de productores',
                        'description' => 'Mesa compartida con agricultores locales y productos de temporada.',
                        'icon' => '🍽️',
                        'price' => 120.00,
                        'currency' => 'PEN',
                        'priceNote' => 'por invitado',
                    ],
                    [
                        'title' => 'Ruta del cacao nativo',
                        'description' => 'Visita cooperativas y crea bombones rellenos de frutas amazónicas.',
                        'icon' => '🍬',
                        'price' => 160.00,
                        'currency' => 'PEN',
                        'priceNote' => 'incluye taller y degustación',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Traslados desde Oxapampa o La Merced.',
                            'Guías catadores certificados.',
                            'Ingresos a la Laguna El Oconal.',
                        ],
                    ],
                    [
                        'title' => 'Tips locales',
                        'items' => [
                            'Reservar la salida en kayak antes del amanecer.',
                            'Comprar café directamente de productores certificados.',
                            'Visitar el festival del café en julio.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Planificar visita',
                    'primaryHref' => 'explorar.php?categoria=destinos&slug=villa-rica',
                    'secondaryLabel' => 'Ver experiencias de café',
                    'secondaryHref' => 'paquete.php?slug=villa-rica-cafe',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', 'alt' => 'Café de especialidad servido en taza'],
                    ['src' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80', 'alt' => 'Laguna rodeada de vegetación'],
                    ['src' => 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=800&q=80', 'alt' => 'Plantación de café en pendiente'],
                ],
                'related' => [
                    [
                        'badge' => 'Circuito',
                        'title' => 'Ruta del Café y la Niebla',
                        'summary' => 'Dos días combinando Villa Rica y Oxapampa.',
                        'href' => 'circuito.php?slug=selva-central-signature',
                        'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=600&q=80',
                    ],
                ],
            ],
            [
                'id' => 3,
                'slug' => 'perene',
                'type' => 'Destino',
                'nombre' => 'Perené',
                'descripcion' => 'Cataratas, mariposarios y experiencias culturales amazónicas.',
                'region' => 'Chanchamayo',
                'imagen' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1200&q=80',
                'tagline' => 'Cataratas turquesa y cultura asháninka en el corazón del valle.',
                'summary' => "El valle del Perené es una explosión de naturaleza tropical: cascadas accesibles, ríos ideales para actividades acuáticas y comunidades asháninkas que comparten su cosmovisión.\n\nEs el punto de partida perfecto para circuitos de aventura y bienestar, combinando rafting suave, caminatas interpretativas y experiencias gastronómicas con ingredientes nativos.",
                'location' => 'Valle del Perené — Junín, Perú',
                'duration' => 'Ideal para escapadas de 2 a 3 días',
                'priceFrom' => 'Experiencias desde S/ 280',
                'heroImage' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1600&q=80',
                'mapImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                'mapLabel' => 'Valle del Perené',
                'chips' => ['Cataratas', 'Comunidades asháninkas', 'Rafting'],
                'stats' => [
                    ['label' => 'Altitud', 'value' => '700 m s. n. m.'],
                    ['label' => 'Mejor temporada', 'value' => 'Abril a noviembre'],
                    ['label' => 'Imperdible', 'value' => 'Bayoz y Velo de la Novia'],
                ],
                'highlights' => [
                    [
                        'title' => 'Catarata Bayoz',
                        'description' => 'Tres niveles de caída con pozas de agua cristalina.',
                        'icon' => '💦',
                        'accent' => 'lagoon',
                    ],
                    [
                        'title' => 'Comunidad Marankiari Bajo',
                        'description' => 'Ceremonias, artesanías y gastronomía asháninka.',
                        'icon' => '🪶',
                        'accent' => 'aurora',
                    ],
                    [
                        'title' => 'Mariposario Zhaveta Yard',
                        'description' => 'Centro de conservación con especies amazónicas y jardín botánico.',
                        'icon' => '🦋',
                        'accent' => 'sunrise',
                    ],
                ],
                'itinerary' => [
                    [
                        'title' => 'Día 1 · Cataratas emblemáticas',
                        'summary' => 'Bayoz y Velo de la Novia con picnic tropical.',
                        'activities' => [
                            'Caminata guiada a Bayoz con interpretación ambiental.',
                            'Baño en pozas naturales y sesión fotográfica profesional.',
                            'Almuerzo campestre con productos locales.',
                        ],
                    ],
                    [
                        'title' => 'Día 2 · Cultura y aventura',
                        'summary' => 'Comunidad asháninka y rafting suave.',
                        'activities' => [
                            'Bienvenida ceremonial y taller de tejido tradicional.',
                            'Degustación de bebidas tradicionales como el masato.',
                            'Descenso en bote por el río Perené con guías especializados.',
                        ],
                    ],
                ],
                'experiences' => [
                    [
                        'title' => 'Chocolate amazónico',
                        'description' => 'Participa en la elaboración de chocolates artesanales con cacao nativo.',
                        'icon' => '🍫',
                        'price' => 140.00,
                        'currency' => 'PEN',
                        'priceNote' => 'workshop interactivo',
                    ],
                    [
                        'title' => 'Safari fotográfico',
                        'description' => 'Tour para captar aves y mariposas con acompañamiento naturalista.',
                        'icon' => '📸',
                        'price' => 95.00,
                        'currency' => 'PEN',
                        'priceNote' => 'grupos desde 6 viajeros',
                    ],
                ],
                'essentials' => [
                    [
                        'title' => 'Incluye',
                        'items' => [
                            'Traslados desde La Merced.',
                            'Entradas a cataratas y centros comunitarios.',
                            'Guías bilingües y seguro básico de viaje.',
                        ],
                    ],
                    [
                        'title' => 'Prepárate con',
                        'items' => [
                            'Ropa ligera, traje de baño y calzado antideslizante.',
                            'Repelente eco-friendly y protector solar.',
                            'Efectivo para artesanías y aportes comunitarios.',
                        ],
                    ],
                ],
                'cta' => [
                    'primaryLabel' => 'Quiero ir',
                    'primaryHref' => 'explorar.php?categoria=destinos&slug=perene',
                    'secondaryLabel' => 'Ver circuitos de aventura',
                    'secondaryHref' => 'circuito.php?slug=aventura-perene',
                ],
                'gallery' => [
                    ['src' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=800&q=80', 'alt' => 'Cascada del valle del Perené'],
                    ['src' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=800&q=80', 'alt' => 'Carretera selvática con neblina'],
                    ['src' => 'https://images.unsplash.com/photo-1499696010181-2cb40af7859d?auto=format&fit=crop&w=800&q=80', 'alt' => 'Comunidad asháninka compartiendo cultura'],
                ],
                'related' => [
                    [
                        'badge' => 'Circuito',
                        'title' => 'Aventura Cataratas del Perené',
                        'summary' => 'Programa activo de dos días con rafting y comunidad asháninka.',
                        'href' => 'circuito.php?slug=aventura-perene',
                        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
                    ],
                    [
                        'badge' => 'Paquete',
                        'title' => 'Full Day Selva Mágica',
                        'summary' => 'Tour guiado a Bayoz, Velo de la Novia y mariposario Zhaveta Yard.',
                        'href' => 'paquete.php?slug=selva-magica',
                        'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=600&q=80',
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

        return $normalized !== '' ? $normalized : 'destino';
    }
}
