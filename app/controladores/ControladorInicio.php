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

        $featuredCircuits = $circuitRepository->getFeatured();
        $heroSlides = $this->buildHeroSlides($siteSettings, $featuredCircuits);

        $view = new Vista('inicio');
        $view->render([
            'title' => $pageTitle,
            'currentUser' => $authService->currentUser(),
            'accountDeleted' => $accountDeleted,
            'siteSettings' => $siteSettings,
            'heroSlides' => $heroSlides,
            'featuredPackages' => $packagesRepository->getFeatured(),
            'signatureExperiences' => $packagesRepository->getSignatureExperiences(),
            'featuredCircuits' => $featuredCircuits,
            'destinations' => $destinationRepository->getHighlights(),
            'testimonials' => $reviewRepository->getLatest(),
            'metrics' => $insightRepository->getMetrics(),
            'sellingPoints' => $this->sellingPoints(),
            'travelPillars' => $this->travelPillars(),
        ]);
    }

    private function buildHeroSlides(array $siteSettings, array $circuits): array
    {
        $slides = [];
        foreach ($circuits as $circuit) {
            if (!is_array($circuit)) {
                continue;
            }

            $images = $this->extractCircuitImages($circuit);
            if (empty($images)) {
                continue;
            }

            $label = trim((string) ($circuit['nombre'] ?? $circuit['title'] ?? ''));
            $description = trim((string) ($circuit['resumen'] ?? $circuit['descripcion'] ?? ''));
            $altText = $label !== '' ? $label : null;

            foreach ($images as $image) {
                $slides[] = [
                    'image' => $image,
                    'label' => $label !== '' ? $label : null,
                    'description' => $description !== '' ? $description : null,
                    'altText' => $altText,
                    'isVisible' => true,
                ];

                if (count($slides) >= 5) {
                    break 2;
                }
            }
        }

        if (empty($slides)) {
            $rawSlides = $siteSettings['heroSlides'] ?? [];
            if (is_array($rawSlides)) {
                foreach ($rawSlides as $slide) {
                    if (is_string($slide)) {
                        $slide = ['image' => $slide];
                    }

                    if (!is_array($slide)) {
                        continue;
                    }

                    $image = isset($slide['image']) ? trim((string) $slide['image']) : '';
                    if ($image === '') {
                        continue;
                    }

                    $slides[] = array_merge(
                        [
                            'image' => $image,
                            'isVisible' => !isset($slide['isVisible']) || (bool) $slide['isVisible'],
                        ],
                        $slide
                    );
                }
            }
        }

        return array_values(array_filter(
            $slides,
            static function ($slide): bool {
                if (!is_array($slide)) {
                    return false;
                }

                if (isset($slide['isVisible']) && !$slide['isVisible']) {
                    return false;
                }

                $image = isset($slide['image']) ? trim((string) $slide['image']) : '';

                return $image !== '';
            }
        ));
    }

    private function extractCircuitImages(array $circuit): array
    {
        $images = [];

        if (!empty($circuit['galeria']) && is_array($circuit['galeria'])) {
            foreach ($circuit['galeria'] as $item) {
                if (!is_string($item)) {
                    continue;
                }

                $trimmed = trim($item);
                if ($trimmed !== '') {
                    $images[] = $trimmed;
                }
            }
        }

        if (empty($images) && !empty($circuit['gallery']) && is_array($circuit['gallery'])) {
            foreach ($circuit['gallery'] as $entry) {
                if (is_string($entry)) {
                    $trimmed = trim($entry);
                    if ($trimmed !== '') {
                        $images[] = $trimmed;
                    }

                    continue;
                }

                if (!is_array($entry)) {
                    continue;
                }

                $candidate = $entry['src'] ?? $entry['url'] ?? $entry['image'] ?? $entry['path'] ?? null;
                if (!is_string($candidate)) {
                    continue;
                }

                $trimmed = trim($candidate);
                if ($trimmed !== '') {
                    $images[] = $trimmed;
                }
            }
        }

        if (empty($images)) {
            $fallbackImage = $circuit['imagen'] ?? $circuit['imagen_destacada'] ?? $circuit['imagen_portada'] ?? null;
            if (is_string($fallbackImage)) {
                $trimmed = trim($fallbackImage);
                if ($trimmed !== '') {
                    $images[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($images));
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
