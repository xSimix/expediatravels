<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorPaquetes
{
    public function show(): void
    {
        $view = new Vista('paquete');
        $view->render([
            'title' => 'Detalle del tour — Expediatravels',
        ]);
    }
}
