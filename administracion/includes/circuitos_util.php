<?php

declare(strict_types=1);

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

function cargarDestinosDisponibles(string $archivoDestinos, array $predeterminados, array &$errores): array
{
    try {
        $destinos = ServicioAlmacenamientoJson::leer($archivoDestinos, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudieron cargar los destinos. Se usarán los de referencia.';
        $destinos = $predeterminados;
    }

    $resultado = [];
    foreach ($destinos as $destino) {
        $destino = normalizarDestinoCircuito($destino);
        $resultado[$destino['id']] = $destino;
    }

    ksort($resultado);

    return $resultado;
}

function normalizarDestinoCircuito(array $destino): array
{
    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
    ];
}

function cargarCircuitos(string $archivo, array $predeterminados, array $destinosDisponibles, array &$errores): array
{
    try {
        $circuitos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de circuitos. Se muestran los circuitos de referencia.';
        $circuitos = $predeterminados;
    }

    $circuitos = array_map(static fn (array $circuito): array => normalizarCircuito($circuito, $destinosDisponibles), $circuitos);

    return ordenarCircuitos($circuitos);
}

function normalizarCircuito(array $circuito, array $destinos): array
{
    $destino = $circuito['destino'] ?? [];
    $destinoId = isset($destino['id']) ? (int) $destino['id'] : null;

    if ($destinoId !== null && isset($destinos[$destinoId])) {
        $destinoNombre = $destinos[$destinoId]['nombre'];
        $destinoRegion = $destinos[$destinoId]['region'];
    } else {
        $destinoNombre = trim((string) ($destino['nombre'] ?? ''));
        $destinoRegion = trim((string) ($destino['region'] ?? ''));
    }

    $imagenPortada = trim((string) ($circuito['imagen_portada'] ?? ''));
    $imagenDestacada = trim((string) ($circuito['imagen_destacada'] ?? ''));
    $videoDestacado = trim((string) ($circuito['video_destacado_url'] ?? $circuito['video_destacado'] ?? ''));
    $galeria = array_values(array_unique(array_filter(array_map('trim', (array) ($circuito['galeria'] ?? [])), static fn (string $valor): bool => $valor !== '')));

    return [
        'id' => (int) ($circuito['id'] ?? 0),
        'nombre' => trim((string) ($circuito['nombre'] ?? '')),
        'destino' => [
            'id' => $destinoId,
            'nombre' => $destinoNombre,
            'personalizado' => trim((string) ($destino['personalizado'] ?? '')),
            'region' => $destinoRegion,
        ],
        'duracion' => trim((string) ($circuito['duracion'] ?? '')),
        'categoria' => strtolower(trim((string) ($circuito['categoria'] ?? 'naturaleza'))),
        'dificultad' => strtolower(trim((string) ($circuito['dificultad'] ?? 'relajado'))),
        'frecuencia' => trim((string) ($circuito['frecuencia'] ?? '')),
        'descripcion' => trim((string) ($circuito['descripcion'] ?? '')),
        'imagen_portada' => $imagenPortada,
        'imagen_destacada' => $imagenDestacada,
        'galeria' => $galeria,
        'video_destacado_url' => $videoDestacado,
        'puntos_interes' => array_values(array_filter(array_map('trim', (array) ($circuito['puntos_interes'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'servicios' => array_values(array_filter(array_map('trim', (array) ($circuito['servicios'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'estado' => strtolower(trim((string) ($circuito['estado'] ?? 'borrador'))),
        'actualizado_en' => $circuito['actualizado_en'] ?? null,
    ];
}

function ordenarCircuitos(array $circuitos): array
{
    usort($circuitos, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($circuitos);
}

function obtenerSiguienteId(array $circuitos): int
{
    $maximo = 0;
    foreach ($circuitos as $circuito) {
        $maximo = max($maximo, (int) ($circuito['id'] ?? 0));
    }

    return $maximo + 1;
}

if (!function_exists('convertirListado')) {
    function convertirListado($texto): array
    {
        if (is_array($texto)) {
            $items = $texto;
        } else {
            $items = preg_split('/\r?\n/', (string) $texto) ?: [];
        }

        return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
    }
}

function obtenerNombreDestinoCircuito(array $circuito): string
{
    if (!empty($circuito['destino']['personalizado'])) {
        return $circuito['destino']['personalizado'];
    }

    if (!empty($circuito['destino']['nombre'])) {
        return $circuito['destino']['nombre'];
    }

    return 'Sin destino asignado';
}

function mostrarDuracionCircuito(string $duracion): string
{
    return $duracion !== '' ? $duracion : '—';
}

function categoriaCircuitoEtiqueta(string $categoria): string
{
    return match ($categoria) {
        'cultural' => 'Cultural e histórico',
        'aventura' => 'Aventura y adrenalina',
        'gastronomico' => 'Gastronómico',
        'bienestar' => 'Bienestar y relajación',
        default => 'Naturaleza y aire libre',
    };
}

function dificultadCircuitoEtiqueta(string $dificultad): string
{
    return match ($dificultad) {
        'moderado' => 'Moderado',
        'intenso' => 'Intenso',
        default => 'Relajado',
    };
}

function estadoCircuitoEtiqueta(string $estado): string
{
    return match ($estado) {
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
        default => 'Borrador',
    };
}

if (!function_exists('formatearMarcaTiempo')) {
    function formatearMarcaTiempo(?string $marca): string
    {
        if ($marca === null || $marca === '') {
            return '—';
        }

        try {
            $fecha = new DateTimeImmutable($marca);
            return $fecha->format('d/m/Y H:i');
        } catch (Exception $exception) {
            return $marca;
        }
    }
}

