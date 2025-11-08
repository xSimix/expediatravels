<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorDestinos
{
    public function show(): void
    {
        $view = new Vista('destino');

        $view->render([
            'title' => 'Descubre Oxapampa — Destino destacado',
            'detail' => [
                'type' => 'Destino imperdible',
                'title' => 'Oxapampa, Selva Central del Perú',
                'tagline' => 'Naturaleza, cultura austro-alemana y aventuras en la selva alta.',
                'themeGradient' => 'linear-gradient(135deg, #2d7ff9 0%, #22d3ee 35%, #34d399 70%, #f97316 100%)',
                'heroImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1548783307-f63adc1af7f6?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1529516239324-5943151258a0?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=1400&q=80',
                ],
                'quickFacts' => [
                    ['label' => 'Altitud', 'value' => '1,800 m s. n. m.', 'icon' => '⛰️'],
                    ['label' => 'Mejor época', 'value' => 'Abril a octubre', 'icon' => '☀️'],
                    ['label' => 'Clima', 'value' => 'Templado húmedo', 'icon' => '🌧️'],
                    ['label' => 'Ideal para', 'value' => 'Familias y aventureros', 'icon' => '🧭'],
                ],
                'overview' => [
                    'description' => 'Oxapampa vibra entre montañas verdes, casas de estilo tirolés y experiencias que mezclan la herencia austro-alemana con la calidez selvática peruana. Explora cascadas cristalinas, degusta café de altura y comparte tradiciones con comunidades asháninkas.',
                    'themes' => [
                        ['title' => 'Gastronomía local', 'description' => 'Sabores únicos que combinan ingredientes amazónicos con recetas europeas.'],
                        ['title' => 'Biodiversidad', 'description' => 'Bosques nubosos, orquídeas y aves emblemáticas en un mismo destino.'],
                        ['title' => 'Cultura viva', 'description' => 'Festivales, música y artesanía de la colonia austro-alemana.'],
                    ],
                ],
                'itinerary' => [
                    ['title' => 'Centro histórico y arquitectura bávara', 'description' => 'Recorre la plaza principal, visita la iglesia Santa Rosa y admira las casonas tradicionales construidas en madera.'],
                    ['title' => 'Catarata El Tigre', 'description' => 'Una caminata corta rodeada de bosque que termina en una caída de agua perfecta para relajarse.'],
                    ['title' => 'Pozuzo y Prusia', 'description' => 'Conoce el legado de los primeros colonos austro-alemanes a solo 2 horas de viaje panorámico.'],
                    ['title' => 'Reserva Yanachaga-Chemillén', 'description' => 'Senderos interpretativos, avistamiento de aves y paisajes que quitan el aliento.'],
                ],
                'highlights' => [
                    ['title' => 'Rutas escénicas en bicicleta', 'description' => 'Ciclovías entre bosques, miradores y granjas lecheras artesanales.', 'icon' => '🚴‍♀️'],
                    ['title' => 'Experiencias cafetaleras', 'description' => 'Aprende sobre el proceso del café y prueba variedades especiales directo de fincas locales.', 'icon' => '☕'],
                    ['title' => 'Refugios eco-lodge', 'description' => 'Hospédate en lodges sostenibles con vistas al valle de Oxapampa.', 'icon' => '🏡'],
                ],
                'faqs' => [
                    ['question' => '¿Cómo llegar a Oxapampa?', 'answer' => 'Puedes viajar por carretera desde Lima (10 horas) o combinar un vuelo a Jauja con un traslado de 5 horas.'],
                    ['question' => '¿Qué llevar?', 'answer' => 'Bloqueador solar, repelente, ropa ligera para el día y abrigo para las noches frescas.'],
                    ['question' => '¿Se necesita reserva previa?', 'answer' => 'Sí, especialmente en temporada alta (mayo-agosto) para asegurar hospedaje y tours.'],
                ],
                'reviews' => [
                    ['name' => 'Ana Lucía', 'rating' => 5, 'date' => 'Septiembre 2023', 'comment' => 'La mezcla cultural es increíble y la naturaleza se siente intacta. ¡Volvería sin dudarlo!'],
                    ['name' => 'Rodrigo', 'rating' => 4, 'date' => 'Julio 2023', 'comment' => 'Perfecto para un viaje en familia, los niños disfrutaron de las granjas y cascadas.'],
                ],
                'contact' => [
                    'agent' => 'Equipo de experiencias Expediatravels',
                    'email' => 'destinos@expediatravels.com',
                    'phone' => '+51 987 654 321',
                    'hours' => 'Lun - Vie, 9:00 - 18:00',
                ],
                'mapEmbed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8225308205167!2d-75.412!3d-10.575!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x910901b7a9874ef7%3A0x80eb0cbeed52b219!2sOxapampa!5e0!3m2!1ses-419!2spe!4v1700000000000!5m2!1ses-419!2spe',
            ],
        ]);
    }
}
