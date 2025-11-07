<?php

declare(strict_types=1);

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

function paquetesCargarDestinos(string $archivo, array $predeterminados, array &$errores): array
{
    try {
        $destinos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudieron cargar los destinos asociados. Se usarán los de referencia.';
        $destinos = $predeterminados;
    }

    $resultado = [];
    foreach ($destinos as $destino) {
        $destino = paquetesNormalizarDestino($destino);
        $resultado[$destino['id']] = $destino;
    }

    ksort($resultado);

    return $resultado;
}

function paquetesNormalizarDestino(array $destino): array
{
    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
    ];
}

function paquetesCargarCircuitos(string $archivo, array $predeterminados, array $destinos, array &$errores): array
{
    try {
        $circuitos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudieron cargar los circuitos. Se usarán los de referencia.';
        $circuitos = $predeterminados;
    }

    $resultado = [];
    foreach ($circuitos as $circuito) {
        $id = (int) ($circuito['id'] ?? 0);
        $destinoId = isset($circuito['destino_id']) ? (int) $circuito['destino_id'] : null;
        $resultado[$id] = [
            'id' => $id,
            'nombre' => trim((string) ($circuito['nombre'] ?? '')),
            'duracion' => trim((string) ($circuito['duracion'] ?? '')),
            'destino_id' => $destinoId,
            'destino_nombre' => $destinoId !== null ? ($destinos[$destinoId]['nombre'] ?? '') : '',
        ];
    }

    ksort($resultado);

    return $resultado;
}

function paquetesCargarPaquetes(string $archivo, array $predeterminados, array &$errores): array
{
    try {
        $paquetes = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudo cargar el catálogo de paquetes. Se muestran los paquetes de referencia.';
        $paquetes = $predeterminados;
    }

    $normalizados = array_map(static fn (array $paquete): array => paquetesNormalizarPaquete($paquete), $paquetes);

    return paquetesOrdenarPaquetes($normalizados);
}

function paquetesNormalizarPaquete(array $paquete): array
{
    $estado = strtolower(trim((string) ($paquete['estado'] ?? 'borrador')));
    if (!in_array($estado, ['publicado', 'borrador', 'agotado', 'inactivo'], true)) {
        $estado = 'borrador';
    }

    $imagenPortada = trim((string) ($paquete['imagen_portada'] ?? ''));
    $imagenDestacada = trim((string) ($paquete['imagen_destacada'] ?? ''));
    $galeria = array_values(array_unique(array_filter(array_map('trim', (array) ($paquete['galeria'] ?? [])), static fn (string $valor): bool => $valor !== '')));
    $videoDestacado = trim((string) ($paquete['video_destacado_url'] ?? $paquete['video_destacado'] ?? ''));

    return [
        'id' => (int) ($paquete['id'] ?? 0),
        'nombre' => trim((string) ($paquete['nombre'] ?? '')),
        'estado' => $estado,
        'duracion' => trim((string) ($paquete['duracion'] ?? '')),
        'precio_desde' => isset($paquete['precio_desde']) ? (float) $paquete['precio_desde'] : null,
        'moneda' => strtoupper(trim((string) ($paquete['moneda'] ?? 'PEN'))),
        'descripcion_breve' => trim((string) ($paquete['descripcion_breve'] ?? '')),
        'descripcion_detallada' => trim((string) ($paquete['descripcion_detallada'] ?? '')),
        'beneficios' => array_values(array_filter(array_map('trim', (array) ($paquete['beneficios'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'incluye' => array_values(array_filter(array_map('trim', (array) ($paquete['incluye'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'no_incluye' => array_values(array_filter(array_map('trim', (array) ($paquete['no_incluye'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'salidas' => array_values(array_filter(array_map('trim', (array) ($paquete['salidas'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'cupos_min' => isset($paquete['cupos_min']) ? (int) $paquete['cupos_min'] : null,
        'cupos_max' => isset($paquete['cupos_max']) ? (int) $paquete['cupos_max'] : null,
        'destinos' => array_values(array_map('intval', (array) ($paquete['destinos'] ?? []))),
        'circuitos' => array_values(array_map('intval', (array) ($paquete['circuitos'] ?? []))),
        'imagen_portada' => $imagenPortada,
        'imagen_destacada' => $imagenDestacada,
        'galeria' => $galeria,
        'video_destacado_url' => $videoDestacado,
        'actualizado_en' => $paquete['actualizado_en'] ?? null,
    ];
}

function paquetesOrdenarPaquetes(array $paquetes): array
{
    usort($paquetes, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($paquetes);
}

if (!function_exists('obtenerSiguienteId')) {
    function obtenerSiguienteId(array $elementos): int
    {
        $maximo = 0;
        foreach ($elementos as $elemento) {
            $maximo = max($maximo, (int) ($elemento['id'] ?? 0));
        }

        return $maximo + 1;
    }
}

function paquetesParsearPrecio($valor, array &$errores): ?float
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_string($valor)) {
        $limpio = str_replace(',', '.', trim($valor));
        if ($limpio === '') {
            return null;
        }
        if (!is_numeric($limpio)) {
            $errores[] = 'El precio debe ser un número válido. Ejemplo: 599.00';
            return null;
        }
        return (float) $limpio;
    }

    if (is_numeric($valor)) {
        return (float) $valor;
    }

    $errores[] = 'El precio debe ser un número válido.';

    return null;
}

function paquetesParsearEntero($valor): ?int
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        return (int) $valor;
    }

    return null;
}

function paquetesConvertirIds($valor): array
{
    if (is_array($valor)) {
        $items = $valor;
    } else {
        $items = preg_split('/[,\s]+/', (string) $valor) ?: [];
    }

    return array_values(array_filter(array_map('intval', $items), static fn (int $id): bool => $id > 0));
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

function estadoPaqueteEtiqueta(string $estado): string
{
    return match ($estado) {
        'publicado' => 'Publicado',
        'agotado' => 'Agotado',
        'inactivo' => 'Inactivo',
        default => 'Borrador',
    };
}

function paquetesFormatearPrecioDesde(?float $precio, string $moneda, array $monedasPermitidas): string
{
    if ($precio === null) {
        return 'A consultar';
    }

    $simbolo = $monedasPermitidas[$moneda] ?? $moneda;

    return sprintf('%s %.2f', $simbolo, $precio);
}

function paquetesFormatearCupos(?int $minimo, ?int $maximo): string
{
    if ($minimo === null && $maximo === null) {
        return '—';
    }

    if ($minimo !== null && $maximo !== null) {
        return sprintf('%d - %d viajeros', $minimo, $maximo);
    }

    if ($minimo !== null) {
        return sprintf('Mín. %d viajeros', $minimo);
    }

    return sprintf('Máx. %d viajeros', $maximo);
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

