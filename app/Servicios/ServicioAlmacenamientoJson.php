<?php

declare(strict_types=1);

namespace Aplicacion\Servicios;

use RuntimeException;

class ServicioAlmacenamientoJson
{
    /**
     * Lee un archivo JSON y devuelve los datos como arreglo asociativo.
     *
     * @param array<int, mixed> $predeterminado
     * @return array<int, mixed>
     */
    public static function leer(string $ruta, array $predeterminado = []): array
    {
        if (!is_file($ruta)) {
            return $predeterminado;
        }

        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            throw new RuntimeException(sprintf('No se pudo leer el archivo %s.', $ruta));
        }

        $contenido = trim($contenido);
        if ($contenido === '') {
            return $predeterminado;
        }

        $datos = json_decode($contenido, true);
        if (!is_array($datos)) {
            return $predeterminado;
        }

        return $datos;
    }

    /**
     * Guarda un arreglo como JSON con formato legible.
     *
     * @param array<int, mixed> $datos
     */
    public static function guardar(string $ruta, array $datos): void
    {
        $directorio = dirname($ruta);
        if (!is_dir($directorio)) {
            $creado = @mkdir($directorio, 0775, true);
            if (!$creado && !is_dir($directorio)) {
                throw new RuntimeException(sprintf('No se pudo crear el directorio %s.', $directorio));
            }
        }

        $json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('No se pudo codificar los datos a JSON.');
        }

        $resultado = @file_put_contents($ruta, $json . PHP_EOL, LOCK_EX);
        if ($resultado === false) {
            throw new RuntimeException(sprintf('No se pudo guardar el archivo %s.', $ruta));
        }
    }
}
