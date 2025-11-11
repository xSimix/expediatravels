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
                'SELECT id, nombre, descripcion, region, imagen, imagen_destacada, tagline, visible_en_busqueda, visible_en_explorador
                 FROM destinos
                 WHERE estado = "activo" AND visible_en_explorador = 1
                 ORDER BY nombre
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

        return array_slice($this->filterFallbackByVisibility('visible_en_explorador'), 0, $limit);
    }

    public function getForSearch(): array
    {
        try {
            $pdo = Conexion::obtener();
            $statement = $pdo->query(
                'SELECT id, nombre, descripcion, region, imagen, imagen_destacada, tagline, visible_en_busqueda, visible_en_explorador
                 FROM destinos
                 WHERE estado = "activo" AND visible_en_busqueda = 1
                 ORDER BY nombre'
            );

            $destinations = $statement->fetchAll();
            if ($destinations) {
                return array_map(fn (array $destination) => $this->hydrateDestination($destination), $destinations);
            }
        } catch (PDOException $exception) {
            // Usa datos de respaldo si la base falla.
        }

        return $this->filterFallbackByVisibility('visible_en_busqueda');
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
            'visible_en_busqueda' => (bool) ($destination['visible_en_busqueda'] ?? true),
            'visible_en_explorador' => (bool) ($destination['visible_en_explorador'] ?? true),
            'slug' => $destination['slug'] ?? $this->generateSlug($name),
        ];
    }

    private function fallbackDestinations(): array
    {
        static $fallback = null;

        if ($fallback !== null) {
            return $fallback;
        }

        $configPath = __DIR__ . '/../configuracion/destinos_predeterminados.php';
        $defaults = [];

        if (is_file($configPath)) {
            $defaults = require $configPath;
        }

        $fallback = array_map(function (array $destination): array {
            $name = (string) ($destination['nombre'] ?? 'Destino');
            $region = (string) ($destination['region'] ?? '');
            $description = (string) ($destination['descripcion'] ?? '');
            $tagline = (string) ($destination['tagline'] ?? '');
            $slug = $destination['slug'] ?? $this->generateSlug($name);
            $image = $destination['imagen_destacada'] ?? $destination['imagen'] ?? null;

            $gallery = [];
            if (!empty($destination['galeria']) && is_array($destination['galeria'])) {
                foreach ($destination['galeria'] as $item) {
                    $url = trim((string) $item);
                    if ($url !== '') {
                        $gallery[] = ['src' => $url, 'alt' => $name];
                    }
                }
            }

            return [
                'id' => (int) ($destination['id'] ?? 0),
                'slug' => $slug,
                'type' => 'Destino',
                'nombre' => $name,
                'descripcion' => $description,
                'region' => $region,
                'imagen' => $image,
                'imagen_destacada' => $image,
                'tagline' => $tagline,
                'summary' => $description,
                'location' => $region !== '' ? $name . ' — ' . $region . ', Perú' : $name,
                'chips' => is_array($destination['tags'] ?? null) ? $destination['tags'] : [],
                'visible_en_busqueda' => (bool) ($destination['visible_en_busqueda'] ?? true),
                'visible_en_explorador' => (bool) ($destination['visible_en_explorador'] ?? true),
                'cta' => [
                    'primaryLabel' => 'Ver experiencias',
                    'primaryHref' => 'explorar.php?categoria=destinos&slug=' . rawurlencode($slug),
                ],
                'gallery' => $gallery,
            ];
        }, $defaults);

        return $fallback;
    }

    private function filterFallbackByVisibility(string $flag): array
    {
        return array_values(array_filter(
            $this->fallbackDestinations(),
            static fn (array $destination): bool => !empty($destination[$flag])
        ));
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
