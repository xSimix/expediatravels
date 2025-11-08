<?php

namespace Aplicacion\Controladores;

use Aplicacion\Repositorios\RepositorioCircuitos;
use Aplicacion\Repositorios\RepositorioConfiguracionSitio;
use Aplicacion\Servicios\ServicioAutenticacion;
use Aplicacion\Vistas\Vista;

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

        $view = new Vista('circuito');
        $view->render([
            'title' => $pageTitle,
            'siteSettings' => $siteSettings,
            'currentUser' => $authService->currentUser(),
            'detail' => $circuit,
        ]);
    }
}
