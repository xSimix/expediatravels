<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorDestinos
{
    public function index(): void
    {
        $view = new Vista('destino');
        $view->render([
            'title' => 'Destinos — Expediatravels',
        ]);
    }
}
