<?php

namespace App\Repositories;

use App\Database\Connection;
use PDO;
use PDOException;

class PackageRepository
{
    /**
     * Returns the latest published packages ready to be featured on the landing page.
     */
    public function getFeatured(int $limit = 4): array
    {
        try {
            $pdo = Connection::get();
            $statement = $pdo->prepare(
                'SELECT p.id, p.nombre, p.resumen, p.duracion, p.precio, p.itinerario, d.nombre AS destino, d.region, d.imagen
                 FROM paquetes p
                 INNER JOIN destinos d ON d.id = p.destino_id
                 WHERE p.estado = "publicado"
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
            // Use fallback data when the database is unavailable.
        }

        return array_slice($this->fallbackPackages(), 0, $limit);
    }

    public function getSignatureExperiences(): array
    {
        try {
            $pdo = Connection::get();
            $statement = $pdo->query(
                'SELECT p.id, p.nombre, p.resumen, p.duracion, p.precio, d.nombre AS destino, d.region
                 FROM paquetes p
                 INNER JOIN destinos d ON d.id = p.destino_id
                 WHERE p.estado = "publicado"
                 ORDER BY p.precio DESC
                 LIMIT 6'
            );
            $packages = $statement->fetchAll();
            if ($packages) {
                return array_map(fn (array $package) => $this->hydratePackage($package), $packages);
            }
        } catch (PDOException $exception) {
            // Fall back silently.
        }

        return $this->fallbackPackages();
    }

    private function hydratePackage(array $package): array
    {
        return [
            'id' => (int) ($package['id'] ?? 0),
            'nombre' => $package['nombre'] ?? '',
            'resumen' => $package['resumen'] ?? '',
            'duracion' => $package['duracion'] ?? '',
            'precio' => (float) ($package['precio'] ?? 0),
            'destino' => $package['destino'] ?? '',
            'region' => $package['region'] ?? '',
            'itinerario' => $package['itinerario'] ?? null,
            'imagen' => $package['imagen'] ?? null,
        ];
    }

    private function fallbackPackages(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Tour Oxapampa',
                'resumen' => 'Tunqui Cueva, El Wharapo y Catarata Río Tigre en una experiencia full day.',
                'duracion' => '1 día',
                'precio' => 120.00,
                'destino' => 'Oxapampa',
                'region' => 'Pasco',
                'itinerario' => 'Visita Tunqui Cueva, degustación en El Wharapo, caminata a la Catarata Río Tigre y recorrido por el Parque Temático.',
                'imagen' => 'oxapampa.jpg',
            ],
            [
                'id' => 2,
                'nombre' => 'Tour Villa Rica',
                'resumen' => 'Laguna El Oconal, catación de café y mirador La Cumbre.',
                'duracion' => '1 día',
                'precio' => 110.00,
                'destino' => 'Villa Rica',
                'region' => 'Pasco',
                'itinerario' => 'Ingreso al Portal de Villa Rica, navegación en la laguna, ictioterapia, catación de café y puesta de sol en el mirador.',
                'imagen' => 'villa-rica.jpg',
            ],
            [
                'id' => 3,
                'nombre' => 'Tour Pozuzo',
                'resumen' => 'Descubre la colonia austro-alemana y sus cascadas.',
                'duracion' => '1 día',
                'precio' => 150.00,
                'destino' => 'Pozuzo',
                'region' => 'Pasco',
                'itinerario' => 'Recorrido histórico, visita a cervecería artesanal, caminata a cascadas y cruce por el puente colgante.',
                'imagen' => 'pozuzo.jpg',
            ],
            [
                'id' => 4,
                'nombre' => 'Tour Perené',
                'resumen' => 'Catarata Bayoz, Velo de la Novia y paseo en bote.',
                'duracion' => '1 día',
                'precio' => 95.00,
                'destino' => 'Perené',
                'region' => 'Chanchamayo',
                'itinerario' => 'Tour por Mariposario, caminata a las cataratas y navegación por el río Perené.',
                'imagen' => 'perene.jpg',
            ],
            [
                'id' => 5,
                'nombre' => 'Tour Yanachaga',
                'resumen' => 'Avistamiento de aves en Lluvias Eternas.',
                'duracion' => '1 día',
                'precio' => 130.00,
                'destino' => 'Yanachaga',
                'region' => 'Pasco',
                'itinerario' => 'Senderismo interpretativo, observación de flora y fauna, visita al centro de interpretación.',
                'imagen' => 'yanachaga.jpg',
            ],
        ];
    }
}
