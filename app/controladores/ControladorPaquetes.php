<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorPaquetes
{
    public function show(): void
    {
        $view = new Vista('paquete');
        $view->render([
            'title' => 'Selva Viva · 5 días en Oxapampa — Expediatravels',
            'detail' => [
                'type' => 'Paquete destacado',
                'title' => 'Selva Viva · 5 días en Oxapampa',
                'tagline' => 'Hospedaje boutique, experiencias con comunidades y naturaleza intensa en la selva central.',
                'themeGradient' => 'linear-gradient(135deg, #6366f1 0%, #22d3ee 40%, #facc15 75%, #f472b6 100%)',
                'heroImage' => 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=1900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1533636721434-0e2d61030955?auto=format&fit=crop&w=1400&q=80',
                ],
                'quickFacts' => [
                    ['label' => 'Duración', 'value' => '5 días / 4 noches', 'icon' => '🗓️'],
                    ['label' => 'Alojamiento', 'value' => 'Eco-lodge boutique', 'icon' => '🏡'],
                    ['label' => 'Nivel', 'value' => 'Suave - Moderado', 'icon' => '🌿'],
                    ['label' => 'Operador', 'value' => 'Expediatravels', 'icon' => '🧭'],
                ],
                'price' => [
                    'amount' => 1249,
                    'currency' => 'USD',
                    'per' => 'por persona',
                    'notes' => 'Incluye alojamiento, alimentación parcial, tours guiados y traslados internos.',
                ],
                'overview' => [
                    'description' => 'Diseñado para viajeros que buscan equilibrio entre descanso y cultura local. Descubre cafetales de altura, cascadas cristalinas y comunidades asháninkas en experiencias auténticas con guías certificados.',
                    'themes' => [
                        ['title' => 'Bienestar en la selva', 'description' => 'Sesión de sound healing y spa con insumos amazónicos.'],
                        ['title' => 'Sabores auténticos', 'description' => 'Cenas maridadas con café y cacao de productores locales.'],
                        ['title' => 'Conexión cultural', 'description' => 'Taller de cerámica y danza tradicional asháninka.'],
                    ],
                ],
                'itinerary' => [
                    ['title' => 'Día 1 · Llegada y ritual de bienvenida', 'description' => 'Recepción en el lodge, almuerzo fresco y caminata suave al mirador de Chontabamba.'],
                    ['title' => 'Día 2 · Cascadas y café de especialidad', 'description' => 'Trekking ligero hacia la catarata El Tigre y tarde de cata guiada en finca cafetalera.'],
                    ['title' => 'Día 3 · Cultura asháninka', 'description' => 'Intercambio cultural, talleres artesanales y navegación por el río Paucartambo.'],
                    ['title' => 'Día 4 · Aventura en pozas turquesa', 'description' => 'Tour a Pozuzo con canopy opcional y picnic campestre.'],
                    ['title' => 'Día 5 · Despedida gourmet', 'description' => 'Mañana libre en el lodge, brunch de despedida y traslado al aeropuerto o terminal.'],
                ],
                'highlights' => [
                    ['title' => 'Guías certificados', 'description' => 'Profesionales bilingües especialistas en turismo responsable.', 'icon' => '🗺️'],
                    ['title' => 'Impacto positivo', 'description' => 'El 12% de la tarifa se reinvierte en proyectos comunitarios.', 'icon' => '🤝'],
                    ['title' => 'Flexibilidad total', 'description' => 'Personalizamos actividades según intereses y ritmo del grupo.', 'icon' => '🧘‍♀️'],
                ],
                'faqs' => [
                    ['question' => '¿Está incluido el transporte desde Lima?', 'answer' => 'Podemos gestionarlo como servicio adicional en bus cama o vuelo + traslado.'],
                    ['question' => '¿Es apto para niños?', 'answer' => 'Sí, recomendamos a partir de 8 años con adaptación del itinerario.'],
                    ['question' => '¿Puedo extender mi estadía?', 'answer' => 'Claro, contamos con noches adicionales y experiencias a medida.'],
                ],
                'reviews' => [
                    ['name' => 'María Fernanda', 'rating' => 5, 'date' => 'Octubre 2023', 'comment' => '¡Superó mis expectativas! La atención del lodge y el contacto con la comunidad fueron memorables.'],
                    ['name' => 'Luis Alberto', 'rating' => 4, 'date' => 'Noviembre 2023', 'comment' => 'El itinerario está muy bien balanceado, la comida deliciosa y los guías excelentes.'],
                ],
                'contact' => [
                    'agent' => 'Laura Montes · Travel designer',
                    'email' => 'paquetes@expediatravels.com',
                    'phone' => '+51 901 223 344',
                    'hours' => 'Lun - Sáb, 9:00 - 19:00',
                ],
            ],
        ]);
    }
}
