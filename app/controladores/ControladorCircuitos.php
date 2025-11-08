<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorCircuitos
{
    public function index(): void
    {
        $view = new Vista('circuito');
        $view->render([
            'title' => 'Circuitos — Expediatravels',
        ]);
    }
}
