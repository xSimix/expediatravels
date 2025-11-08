<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorCircuitos
{
    public function show(): void
    {
        $circuits = require __DIR__ . '/../configuracion/circuitos_predeterminados.php';
        $destinations = require __DIR__ . '/../configuracion/destinos_predeterminados.php';
        $packages = require __DIR__ . '/../configuracion/paquetes_predeterminados.php';

        $selectedId = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $circuit = $this->findCircuit($circuits, $selectedId);

        $destination = $this->findDestination($destinations, $circuit['destino']['id'] ?? null);
        $relatedPackages = $this->filterPackagesForCircuit($packages, $circuit['id']);
        $otherCircuits = array_values(array_filter($circuits, function (array $item) use ($circuit): bool {
            if (($item['estado'] ?? 'activo') !== 'activo') {
                return false;
            }

            return ($item['id'] ?? 0) !== ($circuit['id'] ?? 0);
        }));

        $view = new Vista('circuito');
        $view->render([
            'title' => ($circuit['nombre'] ?? 'Circuito') . ' — Expediatravels',
            'circuit' => $circuit,
            'destination' => $destination,
            'relatedPackages' => $relatedPackages,
            'otherCircuits' => $otherCircuits,
        ]);
    }

    private function findCircuit(array $circuits, ?int $requestedId): array
    {
        if ($requestedId !== null) {
            foreach ($circuits as $circuit) {
                if ((int) ($circuit['id'] ?? 0) === $requestedId) {
                    return $circuit;
                }
            }
        }

        foreach ($circuits as $circuit) {
            if (($circuit['estado'] ?? 'activo') === 'activo') {
                return $circuit;
            }
        }

        return $circuits[0] ?? [
            'id' => 0,
            'nombre' => 'Circuito en preparación',
            'destino' => ['id' => 0, 'nombre' => ''],
            'duracion' => '',
            'descripcion' => 'Muy pronto revelaremos esta experiencia guiada.',
            'servicios' => [],
            'puntos_interes' => [],
            'frecuencia' => '',
            'dificultad' => 'relajado',
            'estado' => 'borrador',
        ];
    }

    private function findDestination(array $destinations, ?int $destinationId): ?array
    {
        if ($destinationId === null) {
            return null;
        }

        foreach ($destinations as $destination) {
            if ((int) ($destination['id'] ?? 0) === $destinationId) {
                return $destination;
            }
        }

        return null;
    }

    private function filterPackagesForCircuit(array $packages, int $circuitId): array
    {
        return array_values(array_filter($packages, function (array $package) use ($circuitId): bool {
            $matchesCircuit = in_array($circuitId, $package['circuitos'] ?? [], true);
            $isPublished = ($package['estado'] ?? 'borrador') === 'publicado';

            return $matchesCircuit && $isPublished;
        }));
    }
}
