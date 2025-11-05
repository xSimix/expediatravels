<?php

namespace App\Repositories;

use App\Database\Connection;
use PDOException;

class InsightRepository
{
    public function getMetrics(): array
    {
        try {
            $pdo = Connection::get();
            $destinos = (int) $pdo->query('SELECT COUNT(*) FROM destinos')->fetchColumn();
            $paquetes = (int) $pdo->query('SELECT COUNT(*) FROM paquetes WHERE estado = "publicado"')->fetchColumn();
            $experiencias = max($paquetes * 4, 12);
            $satisfaccion = 4.9;

            $promedioQuery = $pdo->query('SELECT AVG(rating) FROM resenas');
            if ($promedioQuery !== false) {
                $avg = $promedioQuery->fetchColumn();
                if ($avg !== false && $avg !== null) {
                    $satisfaccion = round((float) $avg, 1);
                }
            }

            return [
                'destinos' => $destinos,
                'paquetes' => $paquetes,
                'experiencias' => $experiencias,
                'satisfaccion' => $satisfaccion,
            ];
        } catch (PDOException $exception) {
            // Ignore connection issues and use curated fallback numbers.
        }

        return [
            'destinos' => 5,
            'paquetes' => 5,
            'experiencias' => 24,
            'satisfaccion' => 4.9,
        ];
    }
}
