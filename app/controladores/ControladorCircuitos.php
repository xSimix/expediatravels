<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioCircuitos;
use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;
use Aplicacion\Repositorios\RepositorioResenasCircuitos;

class ControladorCircuitos
{
    public function show(): void
    {
        $slug = (string) ($_GET['slug'] ?? '');

        $circuitRepository = new RepositorioCircuitos();
        $circuit = $circuitRepository->findBySlug($slug);

        $settingsRepository = new RepositorioConfiguracionSitio();
        $siteSettings = $settingsRepository->get();

        $authService = new ServicioAutenticacion();

        $pageTitle = 'Circuito — Expediatravels';
        if (!empty($circuit['title'] ?? null)) {
            $pageTitle = $circuit['title'] . ' — Expediatravels';
        }

        $reviewsRepository = new RepositorioResenasCircuitos();
        $reviewsData = [
            'reviews' => [],
            'average' => null,
            'count' => 0,
        ];

        if (!empty($circuit['slug'] ?? '')) {
            $reviewsData = $reviewsRepository->obtenerPorCircuito((string) $circuit['slug']);
        }

        $reviewsList = is_array($reviewsData['reviews'] ?? null) ? $reviewsData['reviews'] : [];
        $reviewsAverage = $reviewsData['average'] ?? null;
        $reviewsCount = (int) ($reviewsData['count'] ?? 0);

        if ($reviewsAverage !== null && $reviewsCount > 0) {
            $circuit['ratingPromedio'] = $reviewsAverage;
            $circuit['totalResenas'] = $reviewsCount;
        }

        if ($reviewsAverage === null && isset($circuit['ratingPromedio'])) {
            $reviewsAverage = is_numeric($circuit['ratingPromedio']) ? (float) $circuit['ratingPromedio'] : null;
        }

        if ($reviewsCount === 0 && isset($circuit['totalResenas']) && is_numeric($circuit['totalResenas'])) {
            $reviewsCount = (int) $circuit['totalResenas'];
        }

        $view = new Vista('circuito');
        $view->render([
            'title' => $pageTitle,
            'siteSettings' => $siteSettings,
            'currentUser' => $authService->currentUser(),
            'detail' => array_merge($circuit, [
                'reviewsList' => $reviewsList,
            ]),
            'reviewsSummary' => [
                'average' => $reviewsAverage,
                'count' => $reviewsCount,
            ],
        ]);
    }
}
