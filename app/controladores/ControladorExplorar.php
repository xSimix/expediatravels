<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioCircuitos;
use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Repositorios\RepositorioDestinos;
use Aplicacion\Repositorios\RepositorioPaquetes;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;

class ControladorExplorar
{
    public function index(): void
    {
        $settingsRepository = new RepositorioConfiguracionSitio();
        $packagesRepository = new RepositorioPaquetes();
        $circuitsRepository = new RepositorioCircuitos();
        $destinationsRepository = new RepositorioDestinos();
        $authService = new ServicioAutenticacion();

        $siteSettings = $settingsRepository->get();

        $catalogue = $this->buildCatalogue(
            $packagesRepository->getSignatureExperiences(),
            $circuitsRepository->getFeatured(),
            $destinationsRepository->getHighlights(6)
        );

        $filters = $this->buildFilters($catalogue);
        $activeFilters = $this->resolveActiveFilters($_GET ?? [], $filters);
        $sortOptions = $this->sortOptions();
        $selectedSort = $this->resolveSort($_GET['sort'] ?? null, $sortOptions);

        $filteredCatalogue = $this->applyFilters($catalogue, $activeFilters);
        $sortedCatalogue = $this->applySorting($filteredCatalogue, $selectedSort);
        $stats = $this->catalogueStats($sortedCatalogue);

        $view = new Vista('explorar');
        $view->render([
            'title' => 'Explorar experiencias — Expediatravels',
            'siteSettings' => $siteSettings,
            'currentUser' => $authService->currentUser(),
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'sortOptions' => $sortOptions,
            'selectedSort' => $selectedSort,
            'results' => $sortedCatalogue,
            'stats' => $stats,
        ]);
    }

    private function buildCatalogue(array $packages, array $circuits, array $destinations): array
    {
        $items = [];

        foreach ($packages as $package) {
            $items[] = $this->normalisePackage($package);
        }

        foreach ($circuits as $circuit) {
            $items[] = $this->normaliseCircuit($circuit);
        }

        foreach ($destinations as $destination) {
            if (!($destination['mostrar_en_explorador'] ?? true)) {
                continue;
            }

            $items[] = $this->normaliseDestination($destination);
        }

        return $items;
    }

    private function buildFilters(array $catalogue): array
    {
        $regions = [];
        $styles = [];

        foreach ($catalogue as $item) {
            $region = trim((string) ($item['region'] ?? ''));
            if ($region !== '') {
                $regions[$region] = $region;
            }

            foreach ($item['styleTags'] ?? [] as $tag) {
                $normalized = trim((string) $tag);
                if ($normalized !== '') {
                    $styles[$normalized] = $normalized;
                }
            }
        }

        ksort($regions, SORT_NATURAL | SORT_FLAG_CASE);
        natcasesort($styles);

        return [
            'category' => [
                'label' => 'Categoría',
                'options' => [
                    ['value' => '', 'label' => 'Todas'],
                    ['value' => 'experiencias', 'label' => 'Experiencias'],
                    ['value' => 'circuitos', 'label' => 'Circuitos guiados'],
                    ['value' => 'destinos', 'label' => 'Destinos'],
                ],
            ],
            'region' => [
                'label' => 'Región',
                'options' => array_map(
                    static fn (string $label) => ['value' => $label, 'label' => $label],
                    array_values($regions)
                ),
            ],
            'duration' => [
                'label' => 'Duración',
                'options' => [
                    ['value' => 'short', 'label' => 'Escapadas cortas (1-2 días)'],
                    ['value' => 'medium', 'label' => 'Aventuras medias (3-4 días)'],
                    ['value' => 'long', 'label' => 'Rutas extendidas (5+ días)'],
                ],
            ],
            'budget' => [
                'label' => 'Presupuesto',
                'options' => [
                    ['value' => 'economic', 'label' => 'Hasta S/ 600'],
                    ['value' => 'mid', 'label' => 'Hasta S/ 1,000'],
                    ['value' => 'premium', 'label' => 'Más de S/ 1,000'],
                ],
            ],
            'style' => [
                'label' => 'Estilo de viaje',
                'options' => array_map(
                    static fn (string $label) => ['value' => $label, 'label' => $label],
                    array_values($styles)
                ),
            ],
        ];
    }

    private function resolveActiveFilters(array $query, array $filters): array
    {
        $active = [];

        foreach ($filters as $key => $definition) {
            $value = $query[$key] ?? null;

            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value === '') {
                continue;
            }

            $allowedValues = array_column($definition['options'] ?? [], 'value');
            if ($allowedValues === [] || in_array($value, $allowedValues, true)) {
                $active[$key] = $value;
            }
        }

        return $active;
    }

    private function sortOptions(): array
    {
        return [
            ['value' => '', 'label' => 'Recomendados'],
            ['value' => 'price_asc', 'label' => 'Precio: menor a mayor'],
            ['value' => 'price_desc', 'label' => 'Precio: mayor a menor'],
            ['value' => 'duration_desc', 'label' => 'Duración: más larga'],
            ['value' => 'duration_asc', 'label' => 'Duración: más corta'],
        ];
    }

    private function resolveSort(?string $value, array $options): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $allowed = array_column($options, 'value');

        return in_array($value, $allowed, true) ? $value : '';
    }

    private function applyFilters(array $catalogue, array $activeFilters): array
    {
        if ($activeFilters === []) {
            return $catalogue;
        }

        return array_values(array_filter($catalogue, function (array $item) use ($activeFilters): bool {
            if (isset($activeFilters['category']) && $activeFilters['category'] !== ($item['category'] ?? null)) {
                return false;
            }

            if (isset($activeFilters['region'])) {
                $region = strtolower($activeFilters['region']);
                $itemRegion = strtolower((string) ($item['region'] ?? ''));
                $itemDestination = strtolower((string) ($item['destination'] ?? ''));

                if ($itemRegion !== $region && $itemDestination !== $region) {
                    return false;
                }
            }

            if (isset($activeFilters['style'])) {
                $styles = array_map('strtolower', $item['styleTags'] ?? []);
                if (!in_array(strtolower($activeFilters['style']), $styles, true)) {
                    return false;
                }
            }

            if (isset($activeFilters['duration'])) {
                $days = $item['durationDays'] ?? null;
                if ($days === null) {
                    return false;
                }

                if ($activeFilters['duration'] === 'short' && $days > 2) {
                    return false;
                }

                if ($activeFilters['duration'] === 'medium' && ($days < 3 || $days > 4)) {
                    return false;
                }

                if ($activeFilters['duration'] === 'long' && $days < 5) {
                    return false;
                }
            }

            if (isset($activeFilters['budget'])) {
                $price = $item['price'] ?? null;
                if (!is_numeric($price)) {
                    return false;
                }

                switch ($activeFilters['budget']) {
                    case 'economic':
                        if ($price > 600) {
                            return false;
                        }
                        break;
                    case 'mid':
                        if ($price > 1000) {
                            return false;
                        }
                        break;
                    case 'premium':
                        if ($price <= 1000) {
                            return false;
                        }
                        break;
                }
            }

            return true;
        }));
    }

    private function applySorting(array $catalogue, string $sort): array
    {
        if ($sort === '') {
            return $catalogue;
        }

        $items = $catalogue;

        usort($items, function (array $a, array $b) use ($sort) {
            $priceA = $a['price'] ?? null;
            $priceB = $b['price'] ?? null;
            $durationA = $a['durationDays'] ?? null;
            $durationB = $b['durationDays'] ?? null;

            switch ($sort) {
                case 'price_asc':
                    return $this->compareNullable($priceA, $priceB, true);
                case 'price_desc':
                    return $this->compareNullable($priceA, $priceB, false);
                case 'duration_desc':
                    return $this->compareNullable($durationA, $durationB, false);
                case 'duration_asc':
                    return $this->compareNullable($durationA, $durationB, true);
                default:
                    return 0;
            }
        });

        return $items;
    }

    private function compareNullable($first, $second, bool $ascending): int
    {
        $direction = $ascending ? 1 : -1;

        if ($first === null && $second === null) {
            return 0;
        }

        if ($first === null) {
            return 1;
        }

        if ($second === null) {
            return -1;
        }

        if ($first === $second) {
            return 0;
        }

        return ($first <=> $second) * $direction;
    }

    private function catalogueStats(array $catalogue): array
    {
        $stats = [
            'total' => count($catalogue),
            'experiencias' => 0,
            'circuitos' => 0,
            'destinos' => 0,
        ];

        foreach ($catalogue as $item) {
            $category = $item['category'] ?? null;
            if ($category !== null && isset($stats[$category])) {
                $stats[$category]++;
            }
        }

        return $stats;
    }

    private function normalisePackage(array $package): array
    {
        $slug = $package['slug'] ?? $this->slugify((string) ($package['nombre'] ?? 'paquete'));

        return [
            'id' => 'package:' . $slug,
            'category' => 'experiencias',
            'typeLabel' => 'Paquete',
            'theme' => 'experience',
            'title' => (string) ($package['nombre'] ?? 'Experiencia'),
            'summary' => (string) ($package['resumen'] ?? ''),
            'destination' => (string) ($package['destino'] ?? ''),
            'region' => (string) ($package['region'] ?? ''),
            'duration' => (string) ($package['duracion'] ?? ''),
            'durationDays' => $this->parseDurationDays($package['duracion'] ?? ''),
            'price' => $this->parseFloat($package['precio'] ?? null),
            'currency' => strtoupper((string) ($package['moneda'] ?? 'PEN')),
            'image' => $this->resolveImagePath($package['imagen'] ?? null),
            'href' => 'paquete.php?slug=' . rawurlencode($slug),
            'styleTags' => $this->extractStyleTags($package),
            'highlights' => $this->extractHighlights($package),
            'rating' => null,
            'reviews' => null,
        ];
    }

    private function normaliseCircuit(array $circuit): array
    {
        $slug = $circuit['slug'] ?? $this->slugify((string) ($circuit['nombre'] ?? 'circuito'));

        return [
            'id' => 'circuit:' . $slug,
            'category' => 'circuitos',
            'typeLabel' => 'Circuito guiado',
            'theme' => 'circuit',
            'title' => (string) ($circuit['nombre'] ?? 'Circuito'),
            'summary' => (string) ($circuit['resumen'] ?? $circuit['descripcion'] ?? ''),
            'destination' => (string) ($circuit['destino'] ?? ''),
            'region' => (string) ($circuit['region'] ?? ''),
            'duration' => (string) ($circuit['duracion'] ?? ''),
            'durationDays' => $this->parseDurationDays($circuit['duracion'] ?? ''),
            'price' => $this->parseFloat($circuit['precio'] ?? null),
            'currency' => strtoupper((string) ($circuit['moneda'] ?? 'PEN')),
            'image' => $this->resolveImagePath($circuit['imagen'] ?? $circuit['heroImage'] ?? null),
            'href' => 'circuito.php?slug=' . rawurlencode($slug),
            'styleTags' => $this->extractStyleTags($circuit),
            'highlights' => $this->extractHighlights($circuit),
            'rating' => $this->parseFloat($circuit['ratingPromedio'] ?? null),
            'reviews' => isset($circuit['totalResenas']) && is_numeric($circuit['totalResenas']) ? (int) $circuit['totalResenas'] : null,
        ];
    }

    private function normaliseDestination(array $destination): array
    {
        $slug = $destination['slug'] ?? $this->slugify((string) ($destination['nombre'] ?? 'destino'));

        return [
            'id' => 'destination:' . $slug,
            'category' => 'destinos',
            'typeLabel' => 'Destino',
            'theme' => 'destination',
            'title' => (string) ($destination['nombre'] ?? 'Destino'),
            'summary' => (string) ($destination['descripcion'] ?? $destination['tagline'] ?? ''),
            'destination' => (string) ($destination['nombre'] ?? ''),
            'region' => (string) ($destination['region'] ?? ''),
            'duration' => (string) ($destination['duration'] ?? ''),
            'durationDays' => $this->parseDurationDays($destination['duration'] ?? ''),
            'price' => null,
            'currency' => 'PEN',
            'image' => $this->resolveImagePath($destination['imagen'] ?? $destination['imagen_destacada'] ?? null),
            'href' => 'destino.php?slug=' . rawurlencode($slug),
            'styleTags' => $this->extractStyleTags($destination),
            'highlights' => $this->extractHighlights($destination),
            'rating' => null,
            'reviews' => null,
        ];
    }

    private function extractStyleTags(array $item): array
    {
        $tags = [];

        foreach (['chips', 'tags', 'styleTags'] as $key) {
            if (!isset($item[$key])) {
                continue;
            }

            $value = $item[$key];
            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $tag) {
                if (is_string($tag)) {
                    $tag = trim($tag);
                    if ($tag !== '') {
                        $tags[$tag] = $tag;
                    }
                }
            }
        }

        if (isset($item['highlights']) && is_array($item['highlights'])) {
            foreach ($item['highlights'] as $highlight) {
                if (is_array($highlight) && isset($highlight['title'])) {
                    $title = trim((string) $highlight['title']);
                    if ($title !== '') {
                        $tags[$title] = $title;
                    }
                }
            }
        }

        return array_values($tags);
    }

    private function extractHighlights(array $item): array
    {
        $highlights = [];

        if (isset($item['highlights']) && is_array($item['highlights'])) {
            foreach ($item['highlights'] as $highlight) {
                if (is_array($highlight)) {
                    $title = $highlight['title'] ?? null;
                    if (is_string($title) && trim($title) !== '') {
                        $highlights[] = trim($title);
                    }
                } elseif (is_string($highlight) && trim($highlight) !== '') {
                    $highlights[] = trim($highlight);
                }
            }
        }

        if ($highlights === [] && isset($item['servicios']) && is_array($item['servicios'])) {
            foreach ($item['servicios'] as $service) {
                if (is_string($service) && trim($service) !== '') {
                    $highlights[] = trim($service);
                } elseif (is_array($service) && isset($service['title'])) {
                    $title = trim((string) $service['title']);
                    if ($title !== '') {
                        $highlights[] = $title;
                    }
                }
            }
        }

        if ($highlights === [] && isset($item['chips']) && is_array($item['chips'])) {
            foreach ($item['chips'] as $chip) {
                if (is_string($chip) && trim($chip) !== '') {
                    $highlights[] = trim($chip);
                }
            }
        }

        return array_slice(array_values(array_unique($highlights)), 0, 3);
    }

    private function parseDurationDays($value): ?int
    {
        if (is_numeric($value)) {
            $number = (int) $value;

            return $number > 0 ? $number : null;
        }

        if (!is_string($value)) {
            return null;
        }

        if (preg_match('/(\d+)\s*d[ií]a/i', $value, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*noches/i', $value, $matches)) {
            return (int) $matches[1] + 1;
        }

        if (stripos($value, 'full day') !== false || stripos($value, 'full-day') !== false) {
            return 1;
        }

        return null;
    }

    private function parseFloat($value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
        if ($normalized === null || $normalized === false || $normalized === '') {
            return null;
        }

        return (float) str_replace(',', '', (string) $normalized);
    }

    private function resolveImagePath($path): ?string
    {
        if (!is_string($path)) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        $normalized = ltrim($trimmed, '/');
        $webPath = __DIR__ . '/../../web/' . $normalized;
        $rootPath = __DIR__ . '/../../' . $normalized;

        if (is_file($webPath)) {
            return $normalized;
        }

        if (is_file($rootPath)) {
            return '../' . $normalized;
        }

        $resourcePath = __DIR__ . '/../../web/recursos/' . $normalized;
        if (is_file($resourcePath)) {
            return 'recursos/' . $normalized;
        }

        return $trimmed;
    }

    private function slugify(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $normalized = strtolower((string) $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/i', '-', $normalized ?? '');
        if (!is_string($normalized)) {
            $normalized = '';
        }

        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'item';
    }
}
