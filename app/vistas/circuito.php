<?php
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Circuitos'); ?></title>
    <link rel="stylesheet" href="estilos/aplicacion.css" />
</head>
<body class="bg-white text-slate-900">
    <main class="max-w-5xl mx-auto py-12 px-6 space-y-12">
        <header class="space-y-4">
            <p class="text-sm uppercase tracking-widest text-emerald-500">Itinerarios guiados</p>
            <h1 class="text-4xl font-bold text-slate-900">Circuitos recomendados</h1>
            <p class="text-lg text-slate-600">
                Diseñamos recorridos temáticos que combinan naturaleza, cultura y gastronomía. Cada circuito puede adaptarse a tu ritmo, duración y nivel de aventura deseado.
            </p>
        </header>

        <section class="space-y-6">
            <?php
            $circuitos = [
                [
                    'nombre' => 'Ruta del Café y las Cascadas',
                    'duracion' => '2 días / 1 noche',
                    'descripcion' => 'Visita Villa Rica para catar cafés especiales, explora cascadas escondidas y disfruta de una noche en lodge rural con fogata.',
                    'incluye' => ['Guía local', 'Transporte terrestre', 'Degustación de café', 'Seguro de viaje'],
                ],
                [
                    'nombre' => 'Herencia Austro-Alemana',
                    'duracion' => '3 días / 2 noches',
                    'descripcion' => 'Recorre Oxapampa, Pozuzo y Prusia descubriendo arquitectura típica, museos y gastronomía tradicional.',
                    'incluye' => ['Guías bilingües', 'Entradas a atractivos', 'Almuerzos temáticos'],
                ],
                [
                    'nombre' => 'Selva Profunda Yanesha',
                    'duracion' => '4 días / 3 noches',
                    'descripcion' => 'Convivencia comunitaria con pueblos Yanesha, navegación por el río y caminatas interpretativas en bosques primarios.',
                    'incluye' => ['Alojamiento comunitario', 'Alimentación completa', 'Talleres culturales'],
                ],
            ];

            foreach ($circuitos as $circuito):
            ?>
                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-7 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="space-y-3">
                            <h2 class="text-2xl font-semibold text-slate-900"><?= htmlspecialchars($circuito['nombre']); ?></h2>
                            <p class="text-sm text-emerald-600 font-medium">
                                <span aria-hidden="true">🗓</span>
                                <?= htmlspecialchars($circuito['duracion']); ?>
                            </p>
                            <p class="text-sm text-slate-600"><?= htmlspecialchars($circuito['descripcion']); ?></p>
                            <ul class="flex flex-wrap gap-2 text-xs text-slate-500">
                                <?php foreach ($circuito['incluye'] as $incluye): ?>
                                    <li class="rounded-full bg-white px-3 py-1 font-medium shadow-sm">
                                        <?= htmlspecialchars($incluye); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="flex flex-col gap-3 md:text-right">
                            <a class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-600" href="paquete.php">
                                Ver detalles
                            </a>
                            <a class="inline-flex items-center justify-center rounded-full border border-emerald-200 px-5 py-2 text-sm font-semibold text-emerald-600 hover:border-emerald-300" href="autenticacion.php">
                                Solicitar cotización
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Cómo personalizamos tu circuito</h2>
            <dl class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <dt class="text-sm font-semibold uppercase tracking-wide text-slate-500">1. Asesoría inicial</dt>
                    <dd class="text-sm text-slate-600">Cuéntanos tus intereses y fechas para proponer experiencias ajustadas a tu grupo.</dd>
                </div>
                <div class="space-y-2">
                    <dt class="text-sm font-semibold uppercase tracking-wide text-slate-500">2. Curaduría local</dt>
                    <dd class="text-sm text-slate-600">Seleccionamos guías, transportistas y alojamientos certificados en Oxapampa y alrededores.</dd>
                </div>
                <div class="space-y-2">
                    <dt class="text-sm font-semibold uppercase tracking-wide text-slate-500">3. Detalles logísticos</dt>
                    <dd class="text-sm text-slate-600">Coordinamos horarios, alimentación especial y actividades opcionales.</dd>
                </div>
                <div class="space-y-2">
                    <dt class="text-sm font-semibold uppercase tracking-wide text-slate-500">4. Acompañamiento</dt>
                    <dd class="text-sm text-slate-600">Monitoreamos tu experiencia en tiempo real para resolver cualquier imprevisto.</dd>
                </div>
            </dl>
        </section>
    </main>
</body>
</html>
