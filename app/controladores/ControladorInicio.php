<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
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

        $view = new Vista('inicio');
        $view->render([
            'title' => $pageTitle,
            'currentUser' => $authService->currentUser(),
            'accountDeleted' => $accountDeleted,
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
