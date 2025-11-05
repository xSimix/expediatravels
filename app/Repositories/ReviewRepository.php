<?php

namespace App\Repositories;

use App\Database\Connection;
use PDOException;

class ReviewRepository
{
    public function getLatest(int $limit = 3): array
    {
        try {
            $pdo = Connection::get();
            $statement = $pdo->prepare(
                'SELECT r.id, r.rating, r.comentario, r.fecha, u.nombre AS usuario, p.nombre AS paquete
                 FROM resenas r
                 INNER JOIN usuarios u ON u.id = r.usuario_id
                 INNER JOIN paquetes p ON p.id = r.paquete_id
                 ORDER BY r.fecha DESC
                 LIMIT :limit'
            );
            $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $statement->execute();

            $reviews = $statement->fetchAll();
            if ($reviews) {
                return array_map(fn (array $review) => $this->hydrateReview($review), $reviews);
            }
        } catch (PDOException $exception) {
            // Ignore database errors and fall back to sample content.
        }

        return array_slice($this->fallbackReviews(), 0, $limit);
    }

    private function hydrateReview(array $review): array
    {
        return [
            'id' => (int) ($review['id'] ?? 0),
            'usuario' => $review['usuario'] ?? 'Viajero',
            'paquete' => $review['paquete'] ?? '',
            'comentario' => $review['comentario'] ?? '',
            'rating' => (int) ($review['rating'] ?? 5),
            'fecha' => $review['fecha'] ?? null,
        ];
    }

    private function fallbackReviews(): array
    {
        return [
            [
                'id' => 1,
                'usuario' => 'Gabriela M.',
                'paquete' => 'Tour Oxapampa',
                'comentario' => 'La organización fue impecable. Nos encantó el contacto con la naturaleza y la calidez del equipo local.',
                'rating' => 5,
            ],
            [
                'id' => 2,
                'usuario' => 'Luis M.',
                'paquete' => 'Tour Pozuzo',
                'comentario' => 'Una experiencia inolvidable. La historia de la colonia y los paisajes superaron mis expectativas.',
                'rating' => 5,
            ],
            [
                'id' => 3,
                'usuario' => 'Johana M.',
                'paquete' => 'Tour Villa Rica',
                'comentario' => 'Excelente atención y transporte seguro. El café y la puesta de sol fueron lo mejor del viaje.',
                'rating' => 4,
            ],
        ];
    }
}
