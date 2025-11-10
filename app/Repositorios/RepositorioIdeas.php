<?php

namespace Aplicacion\Repositorios;

use Aplicacion\BaseDatos\Conexion;
use PDOException;

class RepositorioIdeas
{
    public function getMetrics(): array
    {
        try {
            $pdo = Conexion::obtener();
            $destinos = (int) $pdo->query('SELECT COUNT(*) FROM destinos')->fetchColumn();
            $paquetes = (int) $pdo->query('SELECT COUNT(*) FROM paquetes WHERE estado = "publicado"')->fetchColumn();
            $experiencias = max($paquetes * 4, 12);
            $satisfaccion = 9.8;

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
            // Ignora problemas de conexión y usa cifras de respaldo.
        }

        return [
            'destinos' => 5,
            'paquetes' => 5,
            'experiencias' => 24,
            'satisfaccion' => 9.8,
        ];
    }
}
