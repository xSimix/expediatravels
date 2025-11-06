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
            $statement = $pdo->prepare('SELECT id, nombre, descripcion, region, imagen FROM destinos ORDER BY id LIMIT :limit');
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

    private function hydrateDestination(array $destination): array
    {
        return [
            'id' => (int) ($destination['id'] ?? 0),
            'nombre' => $destination['nombre'] ?? '',
            'descripcion' => $destination['descripcion'] ?? '',
            'region' => $destination['region'] ?? '',
            'imagen' => $destination['imagen'] ?? null,
        ];
    }

    private function fallbackDestinations(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Oxapampa',
                'descripcion' => 'Capital cafetalera y puerta de entrada a la Reserva de Biosfera Oxapampa-Ashaninka-Yanesha.',
                'region' => 'Pasco',
                'imagen' => 'oxapampa.jpg',
            ],
            [
                'id' => 2,
                'nombre' => 'Villa Rica',
                'descripcion' => 'Tierra del café de altura y de la Laguna El Oconal.',
                'region' => 'Pasco',
                'imagen' => 'villa-rica.jpg',
            ],
            [
                'id' => 3,
                'nombre' => 'Pozuzo',
                'descripcion' => 'Colonia austro-alemana rodeada de paisajes naturales únicos.',
                'region' => 'Pasco',
                'imagen' => 'pozuzo.jpg',
            ],
            [
                'id' => 4,
                'nombre' => 'Perené',
                'descripcion' => 'Cataratas, mariposarios y experiencias culturales amazónicas.',
                'region' => 'Chanchamayo',
                'imagen' => 'perene.jpg',
            ],
            [
                'id' => 5,
                'nombre' => 'Yanachaga',
                'descripcion' => 'Reserva que resguarda bosques de neblina y fauna endémica.',
                'region' => 'Pasco',
                'imagen' => 'yanachaga.jpg',
            ],
        ];
    }
}
