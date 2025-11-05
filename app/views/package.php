<?php
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Tour'); ?></title>
    <link rel="stylesheet" href="/css/app.css" />
</head>
<body class="bg-gray-50 text-slate-900">
    <main class="max-w-4xl mx-auto py-12 px-6 space-y-8">
        <header>
            <h1 class="text-3xl font-bold text-slate-800">Tour en detalle</h1>
            <p class="mt-2 text-slate-600">Las descripciones completas de itinerarios, precios y reseñas estarán disponibles pronto.</p>
        </header>
        <section class="rounded-3xl border border-slate-200 p-6">
            <p class="text-slate-500">Aquí se mostrará la galería, itinerario, reseñas y botón de reserva para cada experiencia.</p>
        </section>
    </main>
</body>
</html>
