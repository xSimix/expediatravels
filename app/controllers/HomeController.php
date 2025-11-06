<?php

namespace App\Controllers;

use App\Repositories\DestinationRepository;
use App\Repositories\InsightRepository;
use App\Repositories\PackageRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\SiteSettingsRepository;
use App\Services\AuthService;
use App\Views\View;

class HomeController
{
    public function index(): void
    {
        $packagesRepository = new PackageRepository();
        $destinationRepository = new DestinationRepository();
        $reviewRepository = new ReviewRepository();
        $insightRepository = new InsightRepository();
        $authService = new AuthService();

        $settingsRepository = new SiteSettingsRepository();
        $siteSettings = $settingsRepository->get();

        $pageTitle = $siteSettings['siteTitle'] ?? 'Expediatravels';
        if (!empty($siteSettings['siteTagline'])) {
            $pageTitle .= ' — ' . $siteSettings['siteTagline'];
        }

        $view = new View('home');
        $view->render([
            'title' => $pageTitle,
            'currentUser' => $authService->currentUser(),
            'siteSettings' => $siteSettings,
            'featuredPackages' => $packagesRepository->getFeatured(),
            'signatureExperiences' => $packagesRepository->getSignatureExperiences(),
            'destinations' => $destinationRepository->getHighlights(),
            'testimonials' => $reviewRepository->getLatest(),
            'metrics' => $insightRepository->getMetrics(),
            'sellingPoints' => $this->sellingPoints(),
            'travelPillars' => $this->travelPillars(),
        ]);
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
