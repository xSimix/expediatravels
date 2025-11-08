<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorPaquetes
{
    public function show(): void
    {
        $packages = require __DIR__ . '/../configuracion/paquetes_predeterminados.php';
        $circuits = require __DIR__ . '/../configuracion/circuitos_predeterminados.php';
        $destinations = require __DIR__ . '/../configuracion/destinos_predeterminados.php';

        $selectedId = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $package = $this->findPackage($packages, $selectedId);

        $includedCircuits = $this->findCircuits($circuits, $package['circuitos'] ?? []);
        $includedDestinations = $this->findDestinations($destinations, $package['destinos'] ?? []);
        $otherPackages = array_values(array_filter($packages, function (array $item) use ($package): bool {
            return ($item['id'] ?? 0) !== ($package['id'] ?? 0);
        }));

        $view = new Vista('paquete');
        $view->render([
            'title' => ($package['nombre'] ?? 'Paquete') . ' — Expediatravels',
            'package' => $package,
            'includedCircuits' => $includedCircuits,
            'includedDestinations' => $includedDestinations,
            'otherPackages' => $otherPackages,
        ]);
    }

    private function findPackage(array $packages, ?int $requestedId): array
    {
        if ($requestedId !== null) {
            foreach ($packages as $package) {
                if ((int) ($package['id'] ?? 0) === $requestedId) {
                    return $package;
                }
            }
        }

        foreach ($packages as $package) {
            if (($package['estado'] ?? 'borrador') === 'publicado') {
                return $package;
            }
        }

        return $packages[0] ?? [
            'id' => 0,
            'nombre' => 'Paquete en preparación',
            'estado' => 'borrador',
            'duracion' => '',
            'precio_desde' => 0.0,
            'moneda' => 'PEN',
            'descripcion_breve' => 'Pronto conocerás todos los detalles de esta experiencia.',
            'descripcion_detallada' => '',
            'incluye' => [],
            'no_incluye' => [],
            'circuitos' => [],
            'destinos' => [],
            'salidas' => [],
            'beneficios' => [],
        ];
    }

    private function findCircuits(array $circuits, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $indexed = [];
        foreach ($circuits as $circuit) {
            $indexed[$circuit['id'] ?? null] = $circuit;
        }

        return array_values(array_filter(array_map(fn ($id) => $indexed[$id] ?? null, $ids)));
    }

    private function findDestinations(array $destinations, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $indexed = [];
        foreach ($destinations as $destination) {
            $indexed[$destination['id'] ?? null] = $destination;
        }

        return array_values(array_filter(array_map(fn ($id) => $indexed[$id] ?? null, $ids)));
    }
}
