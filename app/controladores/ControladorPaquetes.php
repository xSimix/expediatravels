<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Repositorios\RepositorioPaquetes;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;

class ControladorPaquetes
{
    public function show(): void
    {
        $slug = (string) ($_GET['slug'] ?? '');

        $packagesRepository = new RepositorioPaquetes();
        $package = $packagesRepository->findBySlug($slug);

        $settingsRepository = new RepositorioConfiguracionSitio();
        $siteSettings = $settingsRepository->get();

        $authService = new ServicioAutenticacion();

        $pageTitle = 'Paquete — Expediatravels';
        if (!empty($package['title'] ?? $package['nombre'] ?? null)) {
            $pageTitle = ($package['title'] ?? $package['nombre']) . ' — Expediatravels';
        }

        $view = new Vista('paquete');
        $view->render([
            'title' => $pageTitle,
            'siteSettings' => $siteSettings,
            'currentUser' => $authService->currentUser(),
            'detail' => $package,
        ]);
    }
}
