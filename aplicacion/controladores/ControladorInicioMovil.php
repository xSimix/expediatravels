<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorInicioMovil
{
    public function index(): void
    {
        $view = new Vista('mobile/inicio');
        $view->render([
            'title' => 'Expediatravels — App',
            'destinations' => [
                [
                    'name' => 'Oxapampa',
                    'description' => 'Bosques nublados, café y cultura asháninka.',
                    'background' => 'linear-gradient(135deg, #38bdf8 0%, #0ea5e9 50%, #0369a1 100%)',
                ],
                [
                    'name' => 'Pozuzo',
                    'description' => 'Arquitectura austro-alemana y cascadas únicas.',
                    'background' => 'linear-gradient(135deg, #fbbf24 0%, #f97316 50%, #c2410c 100%)',
                ],
                [
                    'name' => 'Villa Rica',
                    'description' => 'Capital del café peruano con paisajes increíbles.',
                    'background' => 'linear-gradient(135deg, #34d399 0%, #10b981 50%, #047857 100%)',
                ],
            ],
            'upcomingTours' => [
                [
                    'title' => 'Aventura Catarata Bayoz',
                    'date' => 'Sábado 24',
                    'tag' => 'Popular',
                    'color' => '#0ea5e9',
                ],
                [
                    'title' => 'Experiencia Tunqui Cueva',
                    'date' => 'Domingo 25',
                    'tag' => 'Nuevo',
                    'color' => '#10b981',
                ],
            ],
        ]);
    }
}
