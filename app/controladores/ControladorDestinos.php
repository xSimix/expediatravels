<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Repositorios\RepositorioDestinos;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Servicios\ServicioUrls;
use Aplicacion\Vistas\Vista;

class ControladorDestinos
{
    public function show(): void
    {
        $slug = ServicioUrls::extraerSlugDesdeRequest('destino');

        $destinationRepository = new RepositorioDestinos();
        $destination = $destinationRepository->findBySlug($slug);

        $settingsRepository = new RepositorioConfiguracionSitio();
        $siteSettings = $settingsRepository->get();

        $authService = new ServicioAutenticacion();

        $pageTitle = 'Destino — Expediatravels';
        if (!empty($destination['nombre'] ?? $destination['title'] ?? null)) {
            $pageTitle = ($destination['nombre'] ?? $destination['title']) . ' — Expediatravels';
        }

        $view = new Vista('destino');
        $view->render([
            'title' => $pageTitle,
            'siteSettings' => $siteSettings,
            'currentUser' => $authService->currentUser(),
            'detail' => $destination,
        ]);
    }
}
