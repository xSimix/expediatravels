<?php

namespace Aplicacion\Controladores;

use Aplicacion\Vistas\Vista;

class ControladorCircuitos
{
    public function show(): void
    {
        $view = new Vista('circuito');

        $view->render([
            'title' => 'Circuito Aventura Selva Alta — Expediatravels',
            'detail' => [
                'type' => 'Circuito recomendado',
                'title' => 'Circuito Aventura Selva Alta',
                'tagline' => 'Tres días de adrenalina, cultura cafetalera y cascadas en Oxapampa y Villa Rica.',
                'themeGradient' => 'linear-gradient(135deg, #14b8a6 0%, #6366f1 45%, #ec4899 100%)',
                'heroImage' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1542744173-05336fcc7ad4?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1559599101-37efab69cb0d?auto=format&fit=crop&w=1400&q=80',
                    'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1400&q=80',
                ],
                'quickFacts' => [
                    ['label' => 'Duración', 'value' => '3 días / 2 noches', 'icon' => '🗓️'],
                    ['label' => 'Dificultad', 'value' => 'Moderada', 'icon' => '🔥'],
                    ['label' => 'Grupo', 'value' => 'Mínimo 6 viajeros', 'icon' => '🧑‍🤝‍🧑'],
                    ['label' => 'Transporte', 'value' => 'Movilidad turística privada', 'icon' => '🚌'],
                ],
                'overview' => [
                    'description' => 'Vive un circuito dinámico que combina rutas panorámicas, rafting suave, pozas turquesa y degustaciones en haciendas cafetaleras certificadas. Cada día está diseñado para equilibrar aventura y relajación con guiado experto.',
                    'themes' => [
                        ['title' => 'Aventura acuática', 'description' => 'Rafting nivel II y canyoning ligero en el río Chorobamba.'],
                        ['title' => 'Rutas cafetaleras', 'description' => 'Visita fincas de Villa Rica con catas de cafés premiados.'],
                        ['title' => 'Sabores locales', 'description' => 'Menú degustación con insumos de productores amazónicos.'],
                    ],
                ],
                'itinerary' => [
                    ['title' => 'Día 1 · Rafting y cascadas', 'description' => 'Llegada a Oxapampa, briefing de seguridad y rafting suave. Por la tarde, caminata a la catarata Tunqui.'],
                    ['title' => 'Día 2 · Ruta cafetalera en Villa Rica', 'description' => 'Traslado panorámico, visita a finca certificada, almuerzo degustación y tarde libre en la laguna El Oconal.'],
                    ['title' => 'Día 3 · Bosque y cultura asháninka', 'description' => 'Senderismo interpretativo en el bosque Sho´llet y convivencia con comunidad nativa antes del retorno.'],
                ],
                'highlights' => [
                    ['title' => 'Guías locales certificados', 'description' => 'Equipo especializado en turismo de aventura y primeros auxilios.', 'icon' => '🧑‍✈️'],
                    ['title' => 'Equipamiento premium', 'description' => 'Todo el gear de rafting y canyoning está incluido y desinfectado.', 'icon' => '🎒'],
                    ['title' => 'Sostenibilidad', 'description' => 'Compensamos la huella de carbono del circuito con reforestación.', 'icon' => '🌱'],
                ],
                'faqs' => [
                    ['question' => '¿Necesito experiencia previa?', 'answer' => 'No, el circuito está diseñado para principiantes con condición física media.'],
                    ['question' => '¿Qué incluye el transporte?', 'answer' => 'Traslados desde/ hacia Oxapampa, movilidad a cada actividad y asistencia 24/7.'],
                    ['question' => '¿Qué pasa si llueve?', 'answer' => 'Las actividades se adaptan o reprograman garantizando la seguridad del grupo.'],
                ],
                'reviews' => [
                    ['name' => 'Gabriela', 'rating' => 5, 'date' => 'Agosto 2023', 'comment' => 'Increíble logística, los guías fueron muy atentos y el rafting estuvo buenazo.'],
                    ['name' => 'Jorge', 'rating' => 5, 'date' => 'Junio 2023', 'comment' => 'El circuito es súper completo y variado. ¡100% recomendado!'],
                ],
                'contact' => [
                    'agent' => 'Juan Torres · Especialista en circuitos',
                    'email' => 'circuitos@expediatravels.com',
                    'phone' => '+51 912 345 678',
                    'hours' => 'Todos los días, 8:00 - 20:00',
                ],
                'price' => [
                    'amount' => 799,
                    'currency' => 'USD',
                    'per' => 'por persona',
                    'notes' => 'Reserva con $120 y paga el resto hasta 15 días antes.',
                ],
                'mapEmbed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7958.848622355696!2d-75.416!3d-10.579!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x910901b7a9874ef7%3A0x80eb0cbeed52b219!2sOxapampa!5e0!3m2!1ses-419!2spe!4v1700000000000!5m2!1ses-419!2spe',
            ],
        ]);
    }
}
