<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorExplorar
{
    public function index(): void
    {
        $view = new Vista('explorar');
        $view->render([
            'title' => 'Explorar experiencias — Expediatravels',
        ]);
    }
}
