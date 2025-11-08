<?php
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Destinos'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="max-w-6xl mx-auto py-12 px-6 space-y-10">
        <header class="space-y-3 text-center">
            <p class="text-sm uppercase tracking-wide text-sky-600">Explora Oxapampa</p>
            <h1 class="text-4xl font-bold text-slate-800">Destinos imprescindibles</h1>
            <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                Inspírate con los lugares más emblemáticos de la Selva Central. Cada destino incluye datos prácticos, recomendaciones y experiencias locales para que disfrutes al máximo.
            </p>
        </header>

        <section class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
            <?php
            $destinosDestacados = [
                [
                    'nombre' => 'Oxapampa',
                    'descripcion' => 'Capital cafetalera rodeada de bosques nubosos, ideal para combinar aventura y cultura austro-alemana.',
                    'etiquetas' => ['Naturaleza', 'Cultura viva', 'Gastronomía'],
                ],
                [
                    'nombre' => 'Villa Rica',
                    'descripcion' => 'Ruta del café especial, cascadas y pozas naturales perfectas para relajarse entre fincas aromáticas.',
                    'etiquetas' => ['Café', 'Relajo', 'Cataratas'],
                ],
                [
                    'nombre' => 'Pozuzo',
                    'descripcion' => 'El primer asentamiento austro-alemán en el Perú, con tradiciones únicas y paisajes rurales fotogénicos.',
                    'etiquetas' => ['Historia', 'Arquitectura', 'Tradición'],
                ],
                [
                    'nombre' => 'Reserva Yanachaga-Chemillén',
                    'descripcion' => 'Santuario de biodiversidad para observar aves emblemáticas, orquídeas y bosques primarios.',
                    'etiquetas' => ['Biodiversidad', 'Senderismo', 'Observación de aves'],
                ],
                [
                    'nombre' => 'Huancabamba',
                    'descripcion' => 'Pueblos artesanales, gastronomía regional y aventuras suaves en torno al río Huancabamba.',
                    'etiquetas' => ['Tradiciones', 'Río', 'Artesanía'],
                ],
                [
                    'nombre' => 'Iscozacin',
                    'descripcion' => 'Experiencias comunitarias con pueblos Yanesha y reservas naturales casi inexploradas.',
                    'etiquetas' => ['Comunidad', 'Bosque primario', 'Cultura Yanesha'],
                ],
            ];

            foreach ($destinosDestacados as $destino):
            ?>
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-semibold text-slate-800"><?= htmlspecialchars($destino['nombre']); ?></h2>
                    <p class="mt-3 text-sm text-slate-600"><?= htmlspecialchars($destino['descripcion']); ?></p>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        <?php foreach ($destino['etiquetas'] as $tag): ?>
                            <li class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700"><?= htmlspecialchars($tag); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="mt-6 inline-flex items-center text-sm font-semibold text-sky-600 hover:text-sky-700" href="explorar.php?categoria=destinos&amp;destino=<?= urlencode($destino['nombre']); ?>">
                        Ver experiencias
                        <span aria-hidden="true" class="ml-1">→</span>
                    </a>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="rounded-3xl border border-dashed border-sky-300 bg-sky-50 p-8 text-slate-700">
            <h2 class="text-2xl font-semibold text-slate-800">¿Necesitas asesoría personalizada?</h2>
            <p class="mt-3 max-w-3xl">
                Nuestro equipo local puede ayudarte a combinar destinos, reservar alojamiento y organizar transporte según tu estilo de viaje. Escríbenos y recibe una propuesta en menos de 24 horas.
            </p>
            <a class="mt-5 inline-flex items-center rounded-full bg-sky-600 px-5 py-2 text-sm font-semibold text-white hover:bg-sky-700" href="autenticacion.php">
                Contactar a un asesor
            </a>
        </section>
    </main>
</body>
</html>
