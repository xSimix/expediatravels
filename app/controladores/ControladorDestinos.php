<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorDestinos
{
    public function show(): void
    {
        $destinations = require __DIR__ . '/../configuracion/destinos_predeterminados.php';
        $circuits = require __DIR__ . '/../configuracion/circuitos_predeterminados.php';
        $packages = require __DIR__ . '/../configuracion/paquetes_predeterminados.php';

        $selectedId = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $destination = $this->findDestination($destinations, $selectedId);

        $relatedCircuits = $this->filterCircuitsForDestination($circuits, $destination['id']);
        $relatedPackages = $this->filterPackagesForDestination($packages, $destination['id']);
        $otherDestinations = array_values(array_filter($destinations, fn (array $item) => $item['id'] !== $destination['id']));

        $view = new Vista('destino');
        $view->render([
            'title' => ($destination['nombre'] ?? 'Destino') . ' — Expediatravels',
            'destination' => $destination,
            'relatedCircuits' => $relatedCircuits,
            'relatedPackages' => $relatedPackages,
            'otherDestinations' => $otherDestinations,
        ]);
    }

    private function findDestination(array $destinations, ?int $requestedId): array
    {
        if ($requestedId !== null) {
            foreach ($destinations as $destination) {
                if ((int) ($destination['id'] ?? 0) === $requestedId) {
                    return $destination;
                }
            }
        }

        foreach ($destinations as $destination) {
            if (($destination['estado'] ?? 'activo') === 'activo') {
                return $destination;
            }
        }

        return $destinations[0] ?? [
            'id' => 0,
            'nombre' => 'Destino en preparación',
            'region' => '',
            'descripcion' => 'Estamos preparando información inspiradora sobre este destino.',
            'tagline' => 'Pronto disponible.',
            'tags' => [],
            'estado' => 'borrador',
        ];
    }

    private function filterCircuitsForDestination(array $circuits, int $destinationId): array
    {
        $filtered = array_values(array_filter($circuits, function (array $circuit) use ($destinationId): bool {
            $matchesDestination = (int) ($circuit['destino']['id'] ?? 0) === $destinationId;
            $isActive = ($circuit['estado'] ?? 'activo') === 'activo';

            return $matchesDestination && $isActive;
        }));

        return $filtered;
    }

    private function filterPackagesForDestination(array $packages, int $destinationId): array
    {
        return array_values(array_filter($packages, function (array $package) use ($destinationId): bool {
            $matchesDestination = in_array($destinationId, $package['destinos'] ?? [], true);
            $isPublished = ($package['estado'] ?? 'borrador') === 'publicado';

            return $matchesDestination && $isPublished;
        }));
    }
}
