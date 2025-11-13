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
        $regionsWithContent = [];
        $styles = [];
        $tourCategories = [];
        $difficulties = [];

        $collectRegion = function (&$bucket, $value) use (&$collectRegion): void {
            if (is_array($value)) {
                if (isset($value['nombre']) || isset($value['label'])) {
                    $candidate = $value['nombre'] ?? $value['label'];
                    $collectRegion($bucket, $candidate);

                    return;
                }

                foreach ($value as $entry) {
                    $collectRegion($bucket, $entry);
                }

                return;
            }

            $label = trim((string) $value);
            if ($label === '') {
                return;
            }

            $bucket[strtolower($label)] = $label;
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
            $collectRegion($regionsWithContent, $package['destino'] ?? null);
            $collectRegion($regionsWithContent, $package['region'] ?? null);
            $collectStyles($styles, $package);
        }

        foreach ($circuits as $circuit) {
            if (!is_array($circuit)) {
                continue;
            }

            $collectRegion($regions, $circuit['destino'] ?? null);
            $collectRegion($regions, $circuit['region'] ?? null);
            $collectRegion($regions, $circuit['location'] ?? null);
            $collectRegion($regionsWithContent, $circuit['destino'] ?? null);
            $collectRegion($regionsWithContent, $circuit['region'] ?? null);
            $collectRegion($regionsWithContent, $circuit['location'] ?? null);
            $collectStyles($styles, $circuit);

            $rawCategory = $circuit['categoria'] ?? $circuit['category'] ?? null;
            if (is_string($rawCategory)) {
                $categoryValue = strtolower(trim($rawCategory));
                if ($categoryValue !== '') {
                    $tourCategories[$categoryValue] = [
                        'value' => $categoryValue,
                        'label' => $this->formatFilterLabel($rawCategory),
                    ];
                }
            }

            $rawDifficulty = $circuit['dificultad'] ?? $circuit['experiencia'] ?? null;
            if (is_string($rawDifficulty)) {
                $difficultyValue = strtolower(trim($rawDifficulty));
                if ($difficultyValue !== '') {
                    $difficulties[$difficultyValue] = [
                        'value' => $difficultyValue,
                        'label' => $this->formatFilterLabel($rawDifficulty),
                    ];
                }
            }
        }

        foreach ($destinations as $destination) {
            if (!is_array($destination)) {
                continue;
            }

            if (!($destination['mostrar_en_buscador'] ?? true)) {
                continue;
            }

            $hasLinkedContent = $this->destinationHasLinkedContent($destination);

            $collectRegion($regions, $destination['nombre'] ?? null);
            $collectRegion($regions, $destination['region'] ?? null);

            if ($hasLinkedContent) {
                $collectRegion($regionsWithContent, $destination['nombre'] ?? null);
                $collectRegion($regionsWithContent, $destination['region'] ?? null);
            }

            $collectStyles($styles, $destination);
        }

        natcasesort($regions);
        natcasesort($styles);

        if ($regionsWithContent !== []) {
            natcasesort($regionsWithContent);
        }

        uasort($tourCategories, static fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));
        uasort($difficulties, static fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));

        $resolvedRegions = $regionsWithContent !== [] ? $regionsWithContent : $regions;

        return [
            'regions' => array_values($resolvedRegions),
            'styles' => array_values($styles),
            'durationOptions' => $this->durationOptions(),
            'budgetOptions' => $this->budgetOptions(),
            'tourCategories' => array_values($tourCategories),
            'difficultyLevels' => array_values($difficulties),
        ];
    }

    private function destinationHasLinkedContent(array $destination): bool
    {
        $packageCount = (int) ($destination['package_count'] ?? $destination['paquetes_publicados'] ?? 0);
        $circuitCount = (int) ($destination['circuit_count'] ?? $destination['circuitos_publicados'] ?? 0);

        if ($packageCount + $circuitCount > 0) {
            return true;
        }

        if (!empty($destination['experiences']) || !empty($destination['experiencias'])) {
            return true;
        }

        if (!empty($destination['related']) || !empty($destination['relacionados'])) {
            return true;
        }

        if (!empty($destination['cta']['primaryHref'] ?? null)) {
            return true;
        }

        return false;
    }

    private function formatFilterLabel(string $value): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return '';
        }

        $normalized = str_replace(['_', '-'], ' ', $normalized);

        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
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
