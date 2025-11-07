<?php

declare(strict_types=1);

use Aplicacion\Servicios\ServicioAlmacenamientoJson;

/**
 * @return array<int, array<string, mixed>>
 */
function cargarDestinosCatalogo(string $archivo, array $predeterminados, array &$errores): array
{
    try {
        $destinos = ServicioAlmacenamientoJson::leer($archivo, $predeterminados);
    } catch (RuntimeException $exception) {
        $errores[] = 'No se pudo cargar el catálogo desde almacenamiento. Se muestran los destinos de referencia.';
        $destinos = $predeterminados;
    }

    $destinos = array_map('normalizarDestino', $destinos);

    return ordenarDestinos($destinos);
}

function obtenerDestinosPredeterminados(): array
{
    return require __DIR__ . '/../../app/configuracion/destinos_predeterminados.php';
}

function normalizarDestino(array $destino): array
{
    $latitud = isset($destino['latitud']) && is_numeric((string) $destino['latitud']) ? (float) $destino['latitud'] : null;
    $longitud = isset($destino['longitud']) && is_numeric((string) $destino['longitud']) ? (float) $destino['longitud'] : null;

    return [
        'id' => (int) ($destino['id'] ?? 0),
        'nombre' => trim((string) ($destino['nombre'] ?? '')),
        'region' => trim((string) ($destino['region'] ?? '')),
        'descripcion' => trim((string) ($destino['descripcion'] ?? '')),
        'tagline' => trim((string) ($destino['tagline'] ?? '')),
        'latitud' => $latitud,
        'longitud' => $longitud,
        'imagen' => trim((string) ($destino['imagen'] ?? '')),
        'tags' => array_values(array_filter(array_map('trim', (array) ($destino['tags'] ?? [])), static fn (string $valor): bool => $valor !== '')),
        'estado' => normalizarEstado($destino['estado'] ?? 'activo'),
        'actualizado_en' => $destino['actualizado_en'] ?? null,
    ];
}

function ordenarDestinos(array $destinos): array
{
    usort($destinos, static fn (array $a, array $b): int => strcmp(mb_strtolower($a['nombre'], 'UTF-8'), mb_strtolower($b['nombre'], 'UTF-8')));

    return array_values($destinos);
}

function convertirEtiquetas($valor): array
{
    if (is_array($valor)) {
        $items = $valor;
    } else {
        $items = preg_split('/[,\n]/', (string) $valor) ?: [];
    }

    return array_values(array_filter(array_map('trim', $items), static fn (string $item): bool => $item !== ''));
}

function normalizarEstado($valor): string
{
    $estado = strtolower(trim((string) $valor));
    $permitidos = ['activo', 'oculto', 'borrador'];

    return in_array($estado, $permitidos, true) ? $estado : 'activo';
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

function normalizarCoordenada($valor, string $campo, array &$errores): ?float
{
    if ($valor === null || trim((string) $valor) === '') {
        return null;
    }

    $valor = str_replace(',', '.', trim((string) $valor));
    if (!is_numeric($valor)) {
        $errores[] = sprintf('El campo %s debe contener un número válido.', $campo);
        return null;
    }

    return (float) $valor;
}

function estadoDestinoEtiqueta(string $estado): string
{
    return match ($estado) {
        'oculto' => 'Oculto',
        'borrador' => 'Borrador',
        default => 'Activo',
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

