<?php
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Paquetes'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="max-w-5xl mx-auto py-12 px-6 space-y-12">
        <header class="space-y-4 text-center">
            <p class="text-sm uppercase tracking-widest text-sky-500">Paquetes flexibles</p>
            <h1 class="text-4xl font-bold text-slate-900">Paquetes armados para inspirarte</h1>
            <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                Selecciona un paquete sugerido y personalízalo según la temporada, actividades favoritas y tamaño de tu grupo. Todos incluyen soporte 24/7 del equipo Expediatravels.
            </p>
        </header>

        <section class="grid gap-8 md:grid-cols-2">
            <?php
            $paquetes = [
                [
                    'nombre' => 'Escapada Eco-Lodge',
                    'duracion' => '3 días / 2 noches',
                    'precio' => 'Desde $240 por persona',
                    'descripcion' => 'Perfecto para parejas o familias que buscan desconexión total en medio del bosque nuboso de Oxapampa.',
                    'beneficios' => ['Alojamiento en eco-lodge', 'Tours guiados diarios', 'Traslados aeropuerto - hotel'],
                ],
                [
                    'nombre' => 'Aventura Multiactividad',
                    'duracion' => '5 días / 4 noches',
                    'precio' => 'Desde $420 por persona',
                    'descripcion' => 'Incluye canopy, tubing, ciclismo y caminatas interpretativas para amantes de la adrenalina.',
                    'beneficios' => ['Equipo certificado', 'Guías bilingües', 'Seguros y snacks energéticos'],
                ],
                [
                    'nombre' => 'Sabores de la Selva Central',
                    'duracion' => '4 días / 3 noches',
                    'precio' => 'Desde $360 por persona',
                    'descripcion' => 'Gastronomía regional, talleres de café y cacao, visitas a productores locales y cenas temáticas.',
                    'beneficios' => ['Catas guiadas', 'Clases de cocina', 'Souvenirs artesanales'],
                ],
                [
                    'nombre' => 'Familia en Oxapampa',
                    'duracion' => '4 días / 3 noches',
                    'precio' => 'Desde $310 por persona',
                    'descripcion' => 'Actividades para todas las edades: fincas interactivas, caminatas ligeras y talleres de manualidades.',
                    'beneficios' => ['Habitaciones familiares', 'Menús especiales', 'Guía privado'],
                ],
            ];

            foreach ($paquetes as $paquete):
            ?>
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <header class="space-y-2">
                        <h2 class="text-2xl font-semibold text-slate-900"><?= htmlspecialchars($paquete['nombre']); ?></h2>
                        <p class="text-sm text-slate-500"><?= htmlspecialchars($paquete['duracion']); ?> · <?= htmlspecialchars($paquete['precio']); ?></p>
                    </header>
                    <p class="mt-4 text-sm text-slate-600"><?= htmlspecialchars($paquete['descripcion']); ?></p>
                    <ul class="mt-5 space-y-2 text-sm text-slate-600">
                        <?php foreach ($paquete['beneficios'] as $beneficio): ?>
                            <li class="flex items-start gap-2">
                                <span aria-hidden="true">✔️</span>
                                <span><?= htmlspecialchars($beneficio); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a class="inline-flex justify-center rounded-full bg-sky-500 px-5 py-2 text-sm font-semibold text-white hover:bg-sky-600" href="autenticacion.php">
                            Reservar ahora
                        </a>
                        <a class="inline-flex justify-center rounded-full border border-sky-200 px-5 py-2 text-sm font-semibold text-sky-600 hover:border-sky-300" href="circuito.php">
                            Ver itinerario sugerido
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Incluido en todos nuestros paquetes</h2>
            <ul class="mt-4 grid gap-4 text-sm text-slate-600 md:grid-cols-2">
                <li class="rounded-2xl bg-slate-50 p-4">Coordinador dedicado antes y durante el viaje.</li>
                <li class="rounded-2xl bg-slate-50 p-4">Soporte vía WhatsApp y teléfono las 24 horas.</li>
                <li class="rounded-2xl bg-slate-50 p-4">Recomendaciones gastronómicas y de experiencias adicionales.</li>
                <li class="rounded-2xl bg-slate-50 p-4">Flexibilidad para modificar fechas o participantes sin penalidades hasta 7 días antes.</li>
            </ul>
        </section>
    </main>
</body>
</html>
