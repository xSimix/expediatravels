<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Repositorios\RepositorioCircuitos;
use Aplicacion\Repositorios\RepositorioDestinos;
use Aplicacion\Repositorios\RepositorioIdeas;
use Aplicacion\Repositorios\RepositorioPaquetes;
use Aplicacion\Repositorios\RepositorioResenas;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;

class ControladorInicio
{
    public function index(): void
    {
        $packagesRepository = new RepositorioPaquetes();
        $destinationRepository = new RepositorioDestinos();
        $reviewRepository = new RepositorioResenas();
        $insightRepository = new RepositorioIdeas();
        $circuitRepository = new RepositorioCircuitos();
        $authService = new ServicioAutenticacion();

        $settingsRepository = new RepositorioConfiguracionSitio();
        $siteSettings = $settingsRepository->get();

        $accountDeleted = false;
        if (!empty($_COOKIE['account_deleted_notice'])) {
            $accountDeleted = true;
            setcookie('account_deleted_notice', '', time() - 3600, '/', '', false, true);
        }

        $pageTitle = $siteSettings['siteTitle'] ?? 'Expediatravels';
        if (!empty($siteSettings['siteTagline'])) {
            $pageTitle .= ' — ' . $siteSettings['siteTagline'];
        }

        $featuredPackages = $packagesRepository->getFeatured();
        $signatureExperiences = $packagesRepository->getSignatureExperiences();
        $featuredCircuits = $circuitRepository->getFeatured();
        $destinations = $destinationRepository->getHighlights();

        $searchMetadata = $this->buildSearchMetadata(
            array_merge($signatureExperiences, $featuredPackages),
            $featuredCircuits,
            $destinations
        );

        $view = new Vista('inicio');
        $view->render([
            'title' => $pageTitle,
            'currentUser' => $authService->currentUser(),
            'accountDeleted' => $accountDeleted,
            'siteSettings' => $siteSettings,
            'featuredPackages' => $featuredPackages,
            'signatureExperiences' => $signatureExperiences,
            'featuredCircuits' => $featuredCircuits,
            'destinations' => $destinations,
            'testimonials' => $reviewRepository->getLatest(),
            'metrics' => $insightRepository->getMetrics(),
            'sellingPoints' => $this->sellingPoints(),
            'travelPillars' => $this->travelPillars(),
            'searchMetadata' => $searchMetadata,
        ]);
    }

    private function buildSearchMetadata(array $packages, array $circuits, array $destinations): array
    {
        $regions = [];
        $styles = [];

        $collectRegion = static function (&$bucket, $value): void {
            if (is_array($value)) {
                foreach ($value as $entry) {
                    $collect = is_array($entry) ? ($entry['nombre'] ?? $entry['label'] ?? null) : $entry;
                    $label = trim((string) $collect);
                    if ($label !== '') {
                        $bucket[strtolower($label)] = $label;
                    }
                }

                return;
            }

            $label = trim((string) $value);
            if ($label !== '') {
                $bucket[strtolower($label)] = $label;
            }
        };

        $collectStyles = static function (&$bucket, array $item): void {
            foreach (['chips', 'tags', 'styleTags'] as $key) {
                if (!isset($item[$key])) {
                    continue;
                }

                $rawValues = $item[$key];
                if (is_string($rawValues)) {
                    $rawValues = [$rawValues];
                }

                if (!is_array($rawValues)) {
                    continue;
                }

                foreach ($rawValues as $value) {
                    $label = trim((string) $value);
                    if ($label !== '') {
                        $bucket[strtolower($label)] = $label;
                    }
                }
            }
        };

        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }

            $collectRegion($regions, $package['destino'] ?? null);
            $collectRegion($regions, $package['region'] ?? null);
            $collectStyles($styles, $package);
        }

        foreach ($circuits as $circuit) {
            if (!is_array($circuit)) {
                continue;
            }

            $collectRegion($regions, $circuit['destino'] ?? null);
            $collectRegion($regions, $circuit['region'] ?? null);
            $collectRegion($regions, $circuit['location'] ?? null);
            $collectStyles($styles, $circuit);
        }

        foreach ($destinations as $destination) {
            if (!is_array($destination)) {
                continue;
            }

            if (!($destination['mostrar_en_buscador'] ?? true)) {
                continue;
            }

            $collectRegion($regions, $destination['nombre'] ?? null);
            $collectRegion($regions, $destination['region'] ?? null);
            $collectStyles($styles, $destination);
        }

        natcasesort($regions);
        natcasesort($styles);

        return [
            'regions' => array_values($regions),
            'styles' => array_values($styles),
            'durationOptions' => $this->durationOptions(),
            'budgetOptions' => $this->budgetOptions(),
        ];
    }

    private function durationOptions(): array
    {
        return [
            ['value' => 'short', 'label' => 'Escapadas cortas (1-2 días)'],
            ['value' => 'medium', 'label' => 'Aventuras medias (3-4 días)'],
            ['value' => 'long', 'label' => 'Rutas extendidas (5+ días)'],
        ];
    }

    private function budgetOptions(): array
    {
        return [
            ['value' => 'economic', 'label' => 'Hasta S/ 600'],
            ['value' => 'mid', 'label' => 'Hasta S/ 1,000'],
            ['value' => 'premium', 'label' => 'Más de S/ 1,000'],
        ];
    }

    private function sellingPoints(): array
    {
        return [
            [
                'title' => 'Guías locales expertos',
                'description' => 'Recorre la Selva Central de la mano de guías certificados, conocedores de su biodiversidad y cultura.',
                'icon' => '🧭',
            ],
            [
                'title' => 'Viajes sostenibles',
                'description' => 'Impulsamos economías locales y minimizamos nuestra huella ambiental en cada itinerario.',
                'icon' => '🌿',
            ],
            [
                'title' => 'Reservas flexibles',
                'description' => 'Cambia la fecha de tu tour hasta 48 horas antes y recibe asistencia personalizada 24/7.',
                'icon' => '🕒',
            ],
        ];
    }

    private function travelPillars(): array
    {
        return [
            [
                'title' => 'Cultura viva',
                'copy' => 'Conecta con comunidades y tradiciones asháninkas, yaneshas y coloniales.',
            ],
            [
                'title' => 'Naturaleza inmersiva',
                'copy' => 'Cataratas, bosques nubosos y reservas naturales en circuitos listos para todos los niveles.',
            ],
            [
                'title' => 'Gastronomía de altura',
                'copy' => 'Degusta café de especialidad, chocolates artesanales y fusiones austro-alemanas.',
            ],
        ];
    }
}
